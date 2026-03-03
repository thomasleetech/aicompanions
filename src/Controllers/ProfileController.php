<?php

class ProfileController
{
    public static function get(): void
    {
        if (!Auth::requireLogin()) return;

        $profile = Database::fetch(
            "SELECT id, username, email, display_name, avatar_url, bio, personal_facts, birthday, location, interests, relationship_status, occupation, referral_code, referral_earnings, created_at
             FROM users WHERE id = ?",
            [Auth::id()]
        );

        View::json(['success' => true, 'profile' => $profile]);
    }

    public static function update(): void
    {
        if (!Auth::requireLogin()) return;

        $allowed = ['display_name', 'bio', 'personal_facts', 'birthday', 'location', 'interests', 'relationship_status', 'occupation'];
        $data = [];

        foreach ($allowed as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = trim($_POST[$field]);
            }
        }

        if (!empty($data)) {
            Database::update('users', $data, 'id = ?', [Auth::id()]);
        }

        View::json(['success' => true]);
    }

    public static function uploadAvatar(): void
    {
        if (!Auth::requireLogin()) return;

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            View::json(['success' => false, 'message' => 'No file uploaded']);
            return;
        }

        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowed)) {
            View::json(['success' => false, 'message' => 'Invalid file type']);
            return;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            View::json(['success' => false, 'message' => 'File too large (max 5MB)']);
            return;
        }

        $dir = 'public/uploads/avatars/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . Auth::id() . '_' . time() . '.' . $ext;
        $path = $dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            Database::update('users', ['avatar_url' => $path], 'id = ?', [Auth::id()]);
            View::json(['success' => true, 'url' => $path]);
        } else {
            View::json(['success' => false, 'message' => 'Upload failed']);
        }
    }

    public static function memories(): void
    {
        if (!Auth::requireLogin()) return;

        $gigId = (int) ($_POST['gig_id'] ?? 0);
        $memories = Database::fetchAll(
            "SELECT id, memory_type, memory_key, memory_value, created_at FROM user_memories WHERE user_id = ? AND gig_id = ? ORDER BY created_at DESC",
            [Auth::id(), $gigId]
        );

        View::json(['success' => true, 'memories' => $memories]);
    }

    public static function deleteMemory(): void
    {
        if (!Auth::requireLogin()) return;

        $id = (int) ($_POST['memory_id'] ?? 0);
        Database::query("DELETE FROM user_memories WHERE id = ? AND user_id = ?", [$id, Auth::id()]);
        View::json(['success' => true]);
    }
}
