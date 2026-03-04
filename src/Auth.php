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

    // ========== PASSWORD RESET ==========

    public static function requestPasswordReset(string $email): array
    {
        $user = Database::fetch("SELECT id, email FROM users WHERE email = ?", [$email]);
        if (!$user) return ['success' => true]; // Prevent email enumeration

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        try {
            Database::query(
                "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?",
                [$token, $expires, $user['id']]
            );

            $resetUrl = (Env::get('APP_URL') ?: '') . url('reset-password') . '?token=' . $token;
            $subject = "Amorai - Reset Your Password";
            $body = "Hi,\n\nYou requested a password reset. Click the link below:\n\n{$resetUrl}\n\nThis link expires in 1 hour.\n\nIf you didn't request this, ignore this email.\n\n- Amorai";
            @mail($user['email'], $subject, $body, "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'amorai.app') . "\r\nContent-Type: text/plain; charset=UTF-8");
        } catch (Exception $e) {
            // Column might not exist yet
        }

        return ['success' => true];
    }

    public static function resetPassword(string $token, string $password): array
    {
        if (strlen($password) < 6) return ['success' => false, 'message' => 'Password must be at least 6 characters'];

        try {
            $user = Database::fetch(
                "SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()",
                [$token]
            );
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Password reset not available yet. Run the database migration.'];
        }

        if (!$user) return ['success' => false, 'message' => 'Invalid or expired reset link'];

        Database::query(
            "UPDATE users SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?",
            [password_hash($password, PASSWORD_DEFAULT), $user['id']]
        );

        return ['success' => true, 'message' => 'Password has been reset. You can now log in.'];
    }

    // ========== EMAIL VERIFICATION ==========

    public static function sendVerificationEmail(int $userId): void
    {
        $user = Database::fetch("SELECT email FROM users WHERE id = ?", [$userId]);
        if (!$user) return;

        $token = bin2hex(random_bytes(32));

        try {
            Database::query("UPDATE users SET email_verify_token = ? WHERE id = ?", [$token, $userId]);
        } catch (Exception $e) {
            return;
        }

        $verifyUrl = (Env::get('APP_URL') ?: '') . url('verify-email') . '?token=' . $token;
        $subject = "Amorai - Verify Your Email";
        $body = "Welcome to Amorai!\n\nClick the link below to verify your email:\n\n{$verifyUrl}\n\n- Amorai";
        @mail($user['email'], $subject, $body, "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'amorai.app') . "\r\nContent-Type: text/plain; charset=UTF-8");
    }

    public static function verifyEmail(string $token): array
    {
        try {
            $user = Database::fetch("SELECT id FROM users WHERE email_verify_token = ?", [$token]);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Email verification not available yet.'];
        }

        if (!$user) return ['success' => false, 'message' => 'Invalid verification link'];

        Database::query("UPDATE users SET email_verified = 1, email_verify_token = NULL WHERE id = ?", [$user['id']]);
        return ['success' => true, 'message' => 'Email verified successfully!'];
    }
}
