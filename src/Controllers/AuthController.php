<?php

class AuthController
{
    public static function showLogin(): void
    {
        if (Auth::check()) { View::redirect('/app'); return; }
        View::render('auth/login', ['user' => null]);
    }

    public static function showRegister(): void
    {
        if (Auth::check()) { View::redirect('/app'); return; }
        View::render('auth/register', ['user' => null, 'ref' => $_GET['ref'] ?? '']);
    }

    public static function login(): void
    {
        if (!CSRF::verify()) {
            View::json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = Auth::login($email, $password);
        View::json($result, $result['success'] ? 200 : 401);
    }

    public static function register(): void
    {
        if (!CSRF::verify()) {
            View::json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $result = Auth::register(
            trim($_POST['username'] ?? ''),
            trim($_POST['email'] ?? ''),
            $_POST['password'] ?? '',
            $_POST['ref_code'] ?? ''
        );

        // Send verification email on successful registration
        if ($result['success'] && Auth::id()) {
            Auth::sendVerificationEmail(Auth::id());
        }

        View::json($result, $result['success'] ? 200 : 422);
    }

    public static function logout(): void
    {
        Auth::logout();
        View::redirect('/');
    }

    // ========== PASSWORD RESET ==========

    public static function showForgotPassword(): void
    {
        View::render('auth/forgot-password', ['user' => null]);
    }

    public static function forgotPassword(): void
    {
        if (!CSRF::verify()) {
            View::json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $result = Auth::requestPasswordReset($email);
        View::json($result);
    }

    public static function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        if (!$token) { View::redirect('/login'); return; }
        View::render('auth/reset-password', ['user' => null, 'token' => $token]);
    }

    public static function resetPassword(): void
    {
        if (!CSRF::verify()) {
            View::json(['success' => false, 'message' => 'Invalid request'], 403);
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if ($password !== $confirm) {
            View::json(['success' => false, 'message' => 'Passwords do not match'], 422);
            return;
        }

        $result = Auth::resetPassword($token, $password);
        View::json($result, $result['success'] ? 200 : 422);
    }

    // ========== EMAIL VERIFICATION ==========

    public static function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';
        $result = Auth::verifyEmail($token);

        // Render a simple verification result page
        View::render('auth/verify-result', ['user' => Auth::user(), 'result' => $result]);
    }

    public static function resendVerification(): void
    {
        if (!Auth::check()) {
            View::json(['success' => false, 'message' => 'Login required'], 401);
            return;
        }

        Auth::sendVerificationEmail(Auth::id());
        View::json(['success' => true, 'message' => 'Verification email sent']);
    }
}
