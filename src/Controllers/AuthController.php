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

        View::json($result, $result['success'] ? 200 : 422);
    }

    public static function logout(): void
    {
        Auth::logout();
        View::redirect('/');
    }
}
