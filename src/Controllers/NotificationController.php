<?php

class NotificationController
{
    public static function list(): void
    {
        if (!Auth::requireLogin()) return;

        try {
            $notifications = Database::fetchAll(
                "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
                [Auth::id()]
            );
            $unread = Database::scalar(
                "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
                [Auth::id()]
            );
        } catch (Exception $e) {
            View::json(['success' => true, 'notifications' => [], 'unread' => 0]);
            return;
        }

        View::json(['success' => true, 'notifications' => $notifications, 'unread' => (int) $unread]);
    }

    public static function markRead(): void
    {
        if (!Auth::requireLogin()) return;

        $id = (int) ($_POST['id'] ?? 0);

        if ($id === 0) {
            // Mark all as read
            Database::query("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [Auth::id()]);
        } else {
            Database::query("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, Auth::id()]);
        }

        View::json(['success' => true]);
    }

    public static function settings(): void
    {
        if (!Auth::requireLogin()) return;

        try {
            $user = Database::fetch(
                "SELECT notification_email, notification_push, notification_frequency FROM users WHERE id = ?",
                [Auth::id()]
            );
        } catch (Exception $e) {
            $user = ['notification_email' => 1, 'notification_push' => 1, 'notification_frequency' => 'daily'];
        }

        View::json(['success' => true, 'settings' => $user]);
    }

    public static function updateSettings(): void
    {
        if (!Auth::requireLogin()) return;

        $email = (int) ($_POST['notification_email'] ?? 1);
        $push = (int) ($_POST['notification_push'] ?? 1);
        $freq = $_POST['notification_frequency'] ?? 'daily';

        $validFreqs = ['realtime', 'hourly', 'daily', 'weekly', 'never'];
        if (!in_array($freq, $validFreqs)) $freq = 'daily';

        try {
            Database::query(
                "UPDATE users SET notification_email = ?, notification_push = ?, notification_frequency = ? WHERE id = ?",
                [$email, $push, $freq, Auth::id()]
            );
        } catch (Exception $e) {
            // Columns may not exist yet
        }

        View::json(['success' => true]);
    }

    // ========== SEND NOTIFICATIONS (called internally) ==========

    public static function send(int $userId, string $type, string $title, string $content = '', string $link = ''): void
    {
        try {
            Database::insert('notifications', [
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'content' => $content,
                'link'    => $link,
            ]);
        } catch (Exception $e) {
            // Table may not exist yet
        }
    }
}
