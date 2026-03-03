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
