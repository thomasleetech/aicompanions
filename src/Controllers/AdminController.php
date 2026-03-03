<?php

class AdminController
{
    public static function dashboard(): void
    {
        if (!empty($_SESSION['admin_logged_in'])) {
            View::render('admin/dashboard', ['user' => Auth::user()]);
            return;
        }

        View::render('admin/login', ['user' => null]);
    }

    public static function login(): void
    {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        if ($user === Env::get('ADMIN_USER') && $pass === Env::get('ADMIN_PASS')) {
            $_SESSION['admin_logged_in'] = true;
            View::json(['success' => true]);
        } else {
            View::json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_logged_in']);
        View::redirect('/admin');
    }

    public static function stats(): void
    {
        if (!Auth::requireAdmin()) return;

        $stats = [
            'users'          => Database::scalar("SELECT COUNT(*) FROM users"),
            'companions'     => Database::scalar("SELECT COUNT(*) FROM gigs WHERE is_active=1"),
            'conversations'  => Database::scalar("SELECT COUNT(*) FROM conversations"),
            'messages_today' => Database::scalar("SELECT COUNT(*) FROM chat_messages WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"),
            'revenue_total'  => Database::scalar("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed'"),
            'revenue_month'  => Database::scalar("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)"),
            'api_cost_today' => Database::scalar("SELECT COALESCE(SUM(cost_estimate),0) FROM api_usage_log WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"),
        ];

        View::json(['success' => true, 'stats' => $stats]);
    }

    public static function users(): void
    {
        if (!Auth::requireAdmin()) return;

        $search = $_POST['search'] ?? '';
        $where = $search ? "WHERE u.username LIKE ? OR u.email LIKE ?" : "";
        $params = $search ? ["%{$search}%", "%{$search}%"] : [];

        $users = Database::fetchAll(
            "SELECT u.*, (SELECT COUNT(*) FROM conversations WHERE user_id = u.id) as conv_count
             FROM users u {$where} ORDER BY u.created_at DESC LIMIT 100",
            $params
        );

        View::json(['success' => true, 'users' => $users]);
    }

    public static function companions(): void
    {
        if (!Auth::requireAdmin()) return;

        $gigs = Database::fetchAll(
            "SELECT g.*, u.display_name FROM gigs g JOIN users u ON g.user_id = u.id ORDER BY g.created_at DESC"
        );

        View::json(['success' => true, 'companions' => $gigs]);
    }

    public static function toggleCompanion(): void
    {
        if (!Auth::requireAdmin()) return;

        $id = (int) ($_POST['id'] ?? 0);
        $active = (int) ($_POST['is_active'] ?? 0);

        Database::update('gigs', ['is_active' => $active], 'id = ?', [$id]);
        View::json(['success' => true]);
    }

    public static function toggleFeatured(): void
    {
        if (!Auth::requireAdmin()) return;

        $id = (int) ($_POST['id'] ?? 0);
        $featured = (int) ($_POST['is_featured'] ?? 0);

        Database::update('gigs', ['is_featured' => $featured], 'id = ?', [$id]);
        View::json(['success' => true]);
    }

    // ========== COMPANION MANAGEMENT ==========

    public static function getCompanion(): void
    {
        if (!Auth::requireAdmin()) return;

        $id = (int) ($_POST['id'] ?? 0);
        $gig = Database::fetch(
            "SELECT g.*, u.display_name, u.username FROM gigs g JOIN users u ON g.user_id = u.id WHERE g.id = ?",
            [$id]
        );

        if (!$gig) {
            View::json(['success' => false, 'message' => 'Not found']);
            return;
        }

        // Parse persona_traits as JSON if it's stored that way, otherwise return raw
        $traits = json_decode($gig['persona_traits'] ?? '{}', true) ?: [];

        View::json(['success' => true, 'companion' => $gig, 'traits' => $traits]);
    }

    public static function saveCompanion(): void
    {
        if (!Auth::requireAdmin()) return;

        $id = (int) ($_POST['id'] ?? 0);
        $gig = Database::fetch("SELECT id FROM gigs WHERE id = ?", [$id]);
        if (!$gig) {
            View::json(['success' => false, 'message' => 'Not found']);
            return;
        }

        $fields = [
            'title'                => trim($_POST['title'] ?? ''),
            'description'          => trim($_POST['description'] ?? ''),
            'companion_type'       => $_POST['companion_type'] ?? 'girlfriend',
            'category'             => $_POST['category'] ?? 'companionship',
            'price_per_hour'       => (float) ($_POST['price_per_hour'] ?? 25),
            'price_per_message'    => (float) ($_POST['price_per_message'] ?? 0.50),
            'monthly_price'        => (float) ($_POST['monthly_price'] ?? 79),
            'languages'            => trim($_POST['languages'] ?? 'English'),
            'image_url'            => trim($_POST['image_url'] ?? ''),
            'tags'                 => trim($_POST['tags'] ?? ''),
            'ai_persona'           => trim($_POST['ai_persona'] ?? ''),
            'ai_voice_id'          => trim($_POST['ai_voice_id'] ?? ''),
            'voice_provider'       => $_POST['voice_provider'] ?? 'openai',
            'base_appearance'      => trim($_POST['base_appearance'] ?? ''),
            'persona_traits'       => $_POST['persona_traits'] ?? '{}',
            'persona_background'   => trim($_POST['persona_background'] ?? ''),
            'persona_speaking_style' => trim($_POST['persona_speaking_style'] ?? ''),
        ];

        Database::update('gigs', $fields, 'id = ?', [$id]);

        View::json(['success' => true, 'message' => 'Companion updated']);
    }

    public static function createCompanion(): void
    {
        if (!Auth::requireAdmin()) return;

        $name = trim($_POST['name'] ?? 'New Companion');
        $type = $_POST['companion_type'] ?? 'girlfriend';

        // Create a provider user for this companion
        $username = strtolower(preg_replace('/[^a-z0-9]/', '', $name)) . '_' . rand(100, 999);
        $userId = Database::insert('users', [
            'username'     => $username,
            'email'        => $username . '@companion.local',
            'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'display_name' => $name,
            'is_provider'  => 1,
        ]);

        $gigId = Database::insert('gigs', [
            'user_id'        => $userId,
            'title'          => "Your {$type} {$name}",
            'description'    => "Hey! I'm {$name}. Nice to meet you!",
            'companion_type' => $type,
            'category'       => 'companionship',
            'price_per_hour' => 25.00,
            'price_per_message' => 0.50,
            'monthly_price'  => 79.00,
            'languages'      => 'English',
            'ai_persona'     => "You are {$name}, a warm and engaging {$type}.",
            'persona_traits' => json_encode([
                'flirtiness' => 50, 'clinginess' => 30, 'shyness' => 20,
                'horniness' => 30, 'jealousy' => 20, 'humor' => 60,
                'empathy' => 70, 'dominance' => 40, 'intelligence' => 60,
                'adventurousness' => 50, 'upsell_aggressiveness' => 30,
            ]),
            'is_active'      => 0,
        ]);

        View::json(['success' => true, 'id' => $gigId, 'message' => "Companion '{$name}' created. Configure personality and activate."]);
    }

    public static function deleteCompanion(): void
    {
        if (!Auth::requireAdmin()) return;

        $id = (int) ($_POST['id'] ?? 0);
        Database::update('gigs', ['is_active' => 0], 'id = ?', [$id]);
        View::json(['success' => true]);
    }

    public static function previewPrompt(): void
    {
        if (!Auth::requireAdmin()) return;

        $id = (int) ($_POST['id'] ?? 0);
        $gig = Database::fetch("SELECT * FROM gigs WHERE id = ?", [$id]);
        if (!$gig) {
            View::json(['success' => false, 'message' => 'Not found']);
            return;
        }

        // Generate the full system prompt as it would appear with various upgrade combos
        $noUpgrades = AIService::buildEnhancedPersona($gig, [], 'User', false);
        $withAdult = AIService::buildEnhancedPersona($gig, ['spicy_personality', 'photos', 'spicy', 'voice', 'premium_plus'], 'User', true);

        View::json([
            'success' => true,
            'prompt_base' => $noUpgrades,
            'prompt_adult' => $withAdult,
        ]);
    }

    public static function apiUsage(): void
    {
        if (!Auth::requireAdmin()) return;

        $period = $_POST['period'] ?? 'day';
        $intervals = ['day' => '24 HOUR', 'week' => '7 DAY', 'month' => '30 DAY'];
        $interval = $intervals[$period] ?? '24 HOUR';

        $byType = Database::fetchAll(
            "SELECT api_type, COUNT(*) as calls, SUM(tokens_input) as total_input, SUM(tokens_output) as total_output, SUM(cost_estimate) as total_cost
             FROM api_usage_log WHERE created_at > DATE_SUB(NOW(), INTERVAL {$interval}) GROUP BY api_type ORDER BY total_cost DESC"
        );

        $totalCost = Database::scalar(
            "SELECT COALESCE(SUM(cost_estimate),0) FROM api_usage_log WHERE created_at > DATE_SUB(NOW(), INTERVAL {$interval})"
        );

        View::json(['success' => true, 'by_type' => $byType, 'total_cost' => (float) $totalCost]);
    }
}
