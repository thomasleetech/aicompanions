<?php

class ChatController
{
    public static function app(): void
    {
        if (!Auth::check()) { View::redirect('/login'); return; }

        $conversations = Database::fetchAll(
            "SELECT c.*, g.title, g.image_url, g.companion_type, g.tags,
                    (SELECT content FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
             FROM conversations c
             JOIN gigs g ON c.gig_id = g.id
             WHERE c.user_id = ?
             ORDER BY c.last_message_at DESC",
            [Auth::id()]
        );

        $gigs = Database::fetchAll(
            "SELECT g.*, u.display_name FROM gigs g JOIN users u ON g.user_id = u.id WHERE g.is_active = 1 ORDER BY g.is_featured DESC, g.rating DESC LIMIT 20"
        );

        View::render('chat/app', [
            'conversations' => $conversations,
            'gigs'          => $gigs,
            'user'          => Auth::user(),
        ]);
    }

    public static function room(array $params): void
    {
        if (!Auth::check()) { View::redirect('/login'); return; }

        $gigId = (int) ($params['id'] ?? 0);
        $gig = Database::fetch("SELECT g.*, u.display_name FROM gigs g JOIN users u ON g.user_id = u.id WHERE g.id = ?", [$gigId]);

        if (!$gig) { View::redirect('/app'); return; }

        // Get user's upgrades for this companion
        $upgrades = self::getUserUpgrades(Auth::id(), $gigId);

        // Get user-companion relationship settings
        $companionSettings = Database::fetch(
            "SELECT * FROM user_companions WHERE user_id = ? AND gig_id = ?",
            [Auth::id(), $gigId]
        );

        View::render('chat/room', [
            'gig'       => $gig,
            'user'      => Auth::user(),
            'upgrades'  => $upgrades,
            'settings'  => $companionSettings,
        ]);
    }

    public static function sendMessage(): void
    {
        if (!Auth::requireLogin()) return;

        $gigId = (int) ($_POST['gig_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $wantVoice = ($_POST['voice'] ?? '') === 'true';

        if (!$message) {
            View::json(['success' => false, 'message' => 'Message cannot be empty']);
            return;
        }

        $gig = Database::fetch("SELECT g.*, u.display_name FROM gigs g JOIN users u ON g.user_id = u.id WHERE g.id = ?", [$gigId]);
        if (!$gig) {
            View::json(['success' => false, 'message' => 'Companion not found']);
            return;
        }

        // Check access (time purchase, subscription, or free demo)
        $access = self::checkAccess(Auth::id(), $gigId);
        if (!$access['allowed']) {
            View::json(['success' => false, 'message' => $access['message'], 'requires_purchase' => true]);
            return;
        }

        // Get or create conversation
        $conv = Database::fetch(
            "SELECT id FROM conversations WHERE user_id = ? AND gig_id = ?",
            [Auth::id(), $gigId]
        );

        if (!$conv) {
            $convId = Database::insert('conversations', [
                'user_id' => Auth::id(),
                'gig_id'  => $gigId,
                'title'   => 'Chat with ' . ($gig['display_name'] ?? $gig['title']),
            ]);
        } else {
            $convId = $conv['id'];
        }

        // Save user message
        $userMsgId = Database::insert('chat_messages', [
            'conversation_id' => $convId,
            'role'            => 'user',
            'content'         => $message,
        ]);

        // Get chat history (last 20 messages for context)
        $history = Database::fetchAll(
            "SELECT role, content FROM chat_messages WHERE conversation_id = ? ORDER BY created_at DESC LIMIT 20",
            [$convId]
        );
        $history = array_reverse($history);

        // Get memories for this user+companion pair
        $memories = Database::fetchAll(
            "SELECT memory_key, memory_value, memory_type FROM user_memories WHERE user_id = ? AND gig_id = ? ORDER BY last_referenced DESC, confidence DESC LIMIT 20",
            [Auth::id(), $gigId]
        );

        // Get user profile facts
        $userProfile = Database::fetch("SELECT personal_facts, interests, occupation, location FROM users WHERE id = ?", [Auth::id()]);
        $userFacts = array_filter([
            $userProfile['personal_facts'] ?? '',
            $userProfile['interests'] ? 'Interests: ' . $userProfile['interests'] : '',
            $userProfile['occupation'] ? 'Works as: ' . $userProfile['occupation'] : '',
            $userProfile['location'] ? 'Lives in: ' . $userProfile['location'] : '',
        ]);

        // Get user's upgrades for this companion
        $upgrades = self::getUserUpgrades(Auth::id(), $gigId);

        // Call AI with full context
        $aiResult = AIService::chat($gig, $history, $memories, $userFacts, $upgrades, Auth::id());

        if (isset($aiResult['error'])) {
            View::json(['success' => false, 'message' => 'AI service error: ' . $aiResult['error']]);
            return;
        }

        $aiContent = $aiResult['content'];

        // Generate voice if requested
        $audioUrl = null;
        if ($wantVoice) {
            $audioUrl = AIService::generateVoice($aiContent, $gig['ai_voice_id'] ?: '', $gig);
        }

        // Save AI response
        Database::insert('chat_messages', [
            'conversation_id' => $convId,
            'role'            => 'assistant',
            'content'         => $aiContent,
            'tokens_used'     => ($aiResult['tokens_in'] ?? 0) + ($aiResult['tokens_out'] ?? 0),
            'audio_url'       => $audioUrl,
        ]);

        // Update conversation timestamp
        Database::update('conversations', ['last_message_at' => date('Y-m-d H:i:s')], 'id = ?', [$convId]);

        // Extract and save memories (non-blocking)
        self::saveMemories(Auth::id(), $gigId, $message, $aiContent);

        // Track session activity
        AIService::updateSessionActivity(Auth::id(), $gigId);

        // Deduct time if applicable
        $timeRemaining = $access['time_remaining'];
        if ($access['time_purchase_id']) {
            Database::query(
                "UPDATE time_purchases SET minutes_remaining = GREATEST(0, minutes_remaining - 1), status = CASE WHEN minutes_remaining <= 1 THEN 'exhausted' ELSE status END WHERE id = ?",
                [$access['time_purchase_id']]
            );
            $timeRemaining = max(0, $timeRemaining - 1);
        }

        // Update affection level (slight boost per interaction)
        self::updateAffection(Auth::id(), $gigId);

        View::json([
            'success'         => true,
            'response'        => $aiContent,
            'audio_url'       => $audioUrl,
            'time_remaining'  => $timeRemaining,
            'is_demo'         => $access['is_demo'],
        ]);
    }

    public static function getHistory(): void
    {
        if (!Auth::requireLogin()) return;

        $gigId = (int) ($_POST['gig_id'] ?? 0);
        $conv = Database::fetch(
            "SELECT id FROM conversations WHERE user_id = ? AND gig_id = ?",
            [Auth::id(), $gigId]
        );

        if (!$conv) {
            View::json(['success' => true, 'messages' => []]);
            return;
        }

        $messages = Database::fetchAll(
            "SELECT role, content, audio_url, created_at FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC LIMIT 100",
            [$conv['id']]
        );

        View::json(['success' => true, 'messages' => $messages]);
    }

    public static function getConversations(): void
    {
        if (!Auth::requireLogin()) return;

        $conversations = Database::fetchAll(
            "SELECT c.*, g.title, g.image_url, g.companion_type,
                    (SELECT content FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
             FROM conversations c
             JOIN gigs g ON c.gig_id = g.id
             WHERE c.user_id = ?
             ORDER BY c.last_message_at DESC",
            [Auth::id()]
        );

        View::json(['success' => true, 'conversations' => $conversations]);
    }

    // ========== ACCESS CHECK ==========

    private static function checkAccess(int $userId, int $gigId): array
    {
        // Check time purchase
        $time = Database::fetch(
            "SELECT id, minutes_remaining FROM time_purchases WHERE user_id = ? AND gig_id = ? AND status = 'active' AND minutes_remaining > 0 ORDER BY purchased_at ASC LIMIT 1",
            [$userId, $gigId]
        );

        if ($time) {
            return ['allowed' => true, 'time_remaining' => $time['minutes_remaining'], 'time_purchase_id' => $time['id'], 'is_demo' => false, 'message' => ''];
        }

        // Check subscription
        $sub = Database::fetch(
            "SELECT id FROM subscriptions WHERE user_id = ? AND gig_id = ? AND status = 'active'",
            [$userId, $gigId]
        );

        if ($sub) {
            return ['allowed' => true, 'time_remaining' => -1, 'time_purchase_id' => null, 'is_demo' => false, 'message' => ''];
        }

        // Free demo (3 messages)
        $msgCount = Database::scalar(
            "SELECT COUNT(*) FROM chat_messages cm JOIN conversations c ON cm.conversation_id = c.id WHERE c.user_id = ? AND c.gig_id = ? AND cm.role = 'user'",
            [$userId, $gigId]
        );

        if ($msgCount < 3) {
            return ['allowed' => true, 'time_remaining' => 0, 'time_purchase_id' => null, 'is_demo' => true, 'message' => ''];
        }

        return ['allowed' => false, 'time_remaining' => 0, 'time_purchase_id' => null, 'is_demo' => false, 'message' => 'Free messages used. Purchase time to continue chatting.'];
    }

    // ========== UPGRADES ==========

    private static function getUserUpgrades(int $userId, int $gigId): array
    {
        try {
            $rows = Database::fetchAll(
                "SELECT upgrade_type FROM companion_upgrades WHERE user_id = ? AND gig_id = ? AND status = 'active'",
                [$userId, $gigId]
            );
            return array_column($rows, 'upgrade_type');
        } catch (Exception $e) {
            return [];
        }
    }

    // ========== MEMORY MANAGEMENT ==========

    private static function saveMemories(int $userId, int $gigId, string $userMsg, string $aiMsg): void
    {
        try {
            $memories = AIService::extractMemories($userMsg, $aiMsg);
            foreach ($memories as $m) {
                if (empty($m['key']) || empty($m['value'])) continue;
                Database::query(
                    "INSERT INTO user_memories (user_id, gig_id, memory_type, memory_key, memory_value, confidence)
                     VALUES (?, ?, ?, ?, ?, 0.85)
                     ON DUPLICATE KEY UPDATE
                        memory_value = VALUES(memory_value),
                        confidence = LEAST(confidence + 0.05, 1.0),
                        last_referenced = NOW()",
                    [$userId, $gigId, $m['type'] ?? 'fact', $m['key'], $m['value']]
                );
            }
        } catch (Exception $e) {
            // Non-critical
        }
    }

    // ========== AFFECTION TRACKING ==========

    private static function updateAffection(int $userId, int $gigId): void
    {
        try {
            $exists = Database::fetch(
                "SELECT id, affection_level FROM user_companions WHERE user_id = ? AND gig_id = ?",
                [$userId, $gigId]
            );

            if ($exists) {
                // Small affection boost per interaction, cap at 100
                $newLevel = min(100, $exists['affection_level'] + 1);
                Database::query(
                    "UPDATE user_companions SET affection_level = ?, last_interaction = NOW() WHERE id = ?",
                    [$newLevel, $exists['id']]
                );
            } else {
                Database::insert('user_companions', [
                    'user_id'          => $userId,
                    'gig_id'           => $gigId,
                    'affection_level'  => 51,
                    'last_interaction' => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (Exception $e) {
            // Non-critical
        }
    }
}
