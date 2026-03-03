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

        View::render('chat/room', [
            'gig'  => $gig,
            'user' => Auth::user(),
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

        $gig = Database::fetch("SELECT * FROM gigs WHERE id = ?", [$gigId]);
        if (!$gig) {
            View::json(['success' => false, 'message' => 'Companion not found']);
            return;
        }

        // Check access
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
                'title'   => 'Chat with ' . $gig['title'],
            ]);
        } else {
            $convId = $conv['id'];
        }

        // Save user message
        Database::insert('chat_messages', [
            'conversation_id' => $convId,
            'role'            => 'user',
            'content'         => $message,
        ]);

        // Get chat history
        $history = Database::fetchAll(
            "SELECT role, content FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC",
            [$convId]
        );

        // Get memories
        $memories = Database::fetchAll(
            "SELECT memory_key, memory_value, memory_type FROM user_memories WHERE user_id = ? AND gig_id = ? ORDER BY confidence DESC LIMIT 20",
            [Auth::id(), $gigId]
        );

        // Get user facts
        $userProfile = Database::fetch("SELECT personal_facts, interests, occupation, location FROM users WHERE id = ?", [Auth::id()]);
        $userFacts = array_filter([
            $userProfile['personal_facts'] ?? '',
            $userProfile['interests'] ? 'Interests: ' . $userProfile['interests'] : '',
            $userProfile['occupation'] ? 'Works as: ' . $userProfile['occupation'] : '',
        ]);

        // Call AI
        $persona = $gig['ai_persona'] ?: "You are {$gig['title']}. Be warm, friendly, and conversational.";
        $aiResult = AIService::chat($persona, $history, $memories, $userFacts);

        if (isset($aiResult['error'])) {
            View::json(['success' => false, 'message' => 'AI service error: ' . $aiResult['error']]);
            return;
        }

        $aiContent = $aiResult['content'];

        // Save AI response
        Database::insert('chat_messages', [
            'conversation_id' => $convId,
            'role'            => 'assistant',
            'content'         => $aiContent,
            'tokens_used'     => ($aiResult['tokens_in'] ?? 0) + ($aiResult['tokens_out'] ?? 0),
        ]);

        // Update conversation timestamp
        Database::update('conversations', ['last_message_at' => date('Y-m-d H:i:s')], 'id = ?', [$convId]);

        // Extract and save memories (async-like, non-blocking)
        self::saveMemories(Auth::id(), $gigId, $message, $aiContent);

        // Deduct time if applicable
        if ($access['time_purchase_id']) {
            Database::query(
                "UPDATE time_purchases SET minutes_remaining = GREATEST(0, minutes_remaining - 1) WHERE id = ?",
                [$access['time_purchase_id']]
            );
        }

        // Voice generation
        $audioUrl = null;
        if ($wantVoice) {
            $voiceId = $gig['ai_voice_id'] ?: 'alloy';
            $audioUrl = AIService::generateVoice($aiContent, $voiceId);
        }

        View::json([
            'success'        => true,
            'response'       => $aiContent,
            'audio_url'      => $audioUrl,
            'time_remaining'  => $access['time_remaining'],
            'is_demo'        => $access['is_demo'],
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
            "SELECT role, content, audio_url, created_at FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC",
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

    private static function checkAccess(int $userId, int $gigId): array
    {
        // Check time purchase
        $time = Database::fetch(
            "SELECT id, minutes_remaining FROM time_purchases WHERE user_id = ? AND gig_id = ? AND status = 'active' AND minutes_remaining > 0 ORDER BY purchased_at ASC LIMIT 1",
            [$userId, $gigId]
        );

        if ($time) {
            return ['allowed' => true, 'time_remaining' => $time['minutes_remaining'], 'time_purchase_id' => $time['id'], 'is_demo' => false];
        }

        // Check subscription
        $sub = Database::fetch(
            "SELECT id FROM subscriptions WHERE user_id = ? AND gig_id = ? AND status = 'active'",
            [$userId, $gigId]
        );

        if ($sub) {
            return ['allowed' => true, 'time_remaining' => -1, 'time_purchase_id' => null, 'is_demo' => false];
        }

        // Free demo (3 messages)
        $msgCount = Database::scalar(
            "SELECT COUNT(*) FROM chat_messages cm JOIN conversations c ON cm.conversation_id = c.id WHERE c.user_id = ? AND c.gig_id = ? AND cm.role = 'user'",
            [$userId, $gigId]
        );

        if ($msgCount < 3) {
            return ['allowed' => true, 'time_remaining' => 0, 'time_purchase_id' => null, 'is_demo' => true, 'message' => ''];
        }

        return ['allowed' => false, 'time_remaining' => 0, 'time_purchase_id' => null, 'is_demo' => false, 'message' => 'Free messages used. Purchase time to continue.'];
    }

    private static function saveMemories(int $userId, int $gigId, string $userMsg, string $aiMsg): void
    {
        try {
            $memories = AIService::extractMemories($userMsg, $aiMsg);
            foreach ($memories as $m) {
                if (empty($m['key']) || empty($m['value'])) continue;
                Database::query(
                    "INSERT INTO user_memories (user_id, gig_id, memory_type, memory_key, memory_value) VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE memory_value = VALUES(memory_value), last_referenced = NOW()",
                    [$userId, $gigId, $m['type'] ?? 'fact', $m['key'], $m['value']]
                );
            }
        } catch (Exception $e) {
            // Non-critical
        }
    }
}
