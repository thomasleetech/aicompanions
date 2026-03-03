<?php

class Auth
{
    public static function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) return null;

        return Database::fetch(
            "SELECT id, username, email, display_name, avatar_url, is_provider, referral_code, balance, created_at FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function login(string $email, string $password): array
    {
        $user = Database::fetch("SELECT * FROM users WHERE email = ?", [$email]);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_provider'] = $user['is_provider'];

        return ['success' => true, 'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'display_name' => $user['display_name'],
        ]];
    }

    public static function register(string $username, string $email, string $password, string $refCode = ''): array
    {
        if (strlen($username) < 3) return ['success' => false, 'message' => 'Username must be at least 3 characters'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['success' => false, 'message' => 'Invalid email address'];
        if (strlen($password) < 6) return ['success' => false, 'message' => 'Password must be at least 6 characters'];

        $referredBy = null;
        if ($refCode) {
            $referrer = Database::fetch("SELECT id FROM users WHERE referral_code = ?", [$refCode]);
            if ($referrer) $referredBy = $referrer['id'];
        }

        $referralCode = 'REF' . strtoupper(substr(md5(random_bytes(16)), 0, 8));

        try {
            $id = Database::insert('users', [
                'username'      => $username,
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'display_name'  => $username,
                'referral_code' => $referralCode,
                'referred_by'   => $referredBy,
            ]);

            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['is_provider'] = 0;

            return ['success' => true, 'referral_code' => $referralCode];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
    }

    public static function logout(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    public static function requireLogin(): bool
    {
        if (self::check()) return true;
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Login required']);
        return false;
    }

    public static function requireAdmin(): bool
    {
        if (!empty($_SESSION['admin_logged_in'])) return true;
        http_response_code(403);
        return false;
    }
}
