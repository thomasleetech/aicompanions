<?php
/**
 * AI Companions - Front Controller
 * All requests route through here.
 */

session_start();

// Base paths
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Autoloader
spl_autoload_register(function (string $class) {
    $paths = [
        BASE_PATH . '/src/' . $class . '.php',
        BASE_PATH . '/src/Controllers/' . $class . '.php',
        BASE_PATH . '/src/Services/' . $class . '.php',
        BASE_PATH . '/src/Models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) { require_once $path; return; }
    }
});

// Boot
Env::load(BASE_PATH);
Database::connect();
View::init(BASE_PATH . '/views');

// Run migrations on first load (creates tables if needed)
try {
    $tables = Database::scalar("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?", [Env::get('DB_NAME')]);
    if ($tables < 5) {
        $sql = file_get_contents(BASE_PATH . '/migrations/001_schema.sql');
        Database::pdo()->exec($sql);

        // Seed if empty
        $userCount = Database::scalar("SELECT COUNT(*) FROM users");
        if ($userCount == 0) {
            // Generate proper password hashes for seed data
            $hash = password_hash('demo123', PASSWORD_DEFAULT);
            $seed = file_get_contents(BASE_PATH . '/migrations/002_seed.sql');
            $seed = str_replace('$2y$10$placeholder', $hash, $seed);
            Database::pdo()->exec($seed);
        }
    }
} catch (Exception $e) {
    if (Env::get('APP_DEBUG') === 'true') {
        error_log("Migration: " . $e->getMessage());
    }
}

// Router
$router = new Router();

// -- Pages --
$router->get('/', [HomeController::class, 'index']);
$router->get('/browse', [GigController::class, 'browse']);
$router->get('/browse/{type}', [HomeController::class, 'landing']);
$router->get('/companion/{id}', [GigController::class, 'show']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/app', [ChatController::class, 'app']);
$router->get('/chat/{id}', [ChatController::class, 'room']);
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/logout', [AdminController::class, 'logout']);

// -- API --
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/chat/send', [ChatController::class, 'sendMessage']);
$router->post('/api/chat/history', [ChatController::class, 'getHistory']);
$router->post('/api/chat/conversations', [ChatController::class, 'getConversations']);
$router->post('/api/gigs', [GigController::class, 'apiList']);
$router->post('/api/profile', [ProfileController::class, 'get']);
$router->post('/api/profile/update', [ProfileController::class, 'update']);
$router->post('/api/profile/avatar', [ProfileController::class, 'uploadAvatar']);
$router->post('/api/profile/memories', [ProfileController::class, 'memories']);
$router->post('/api/profile/memories/delete', [ProfileController::class, 'deleteMemory']);
$router->post('/api/admin/login', [AdminController::class, 'login']);
$router->post('/api/admin/stats', [AdminController::class, 'stats']);
$router->post('/api/admin/users', [AdminController::class, 'users']);
$router->post('/api/admin/companions', [AdminController::class, 'companions']);
$router->post('/api/admin/companion/toggle', [AdminController::class, 'toggleCompanion']);
$router->post('/api/admin/companion/featured', [AdminController::class, 'toggleFeatured']);
$router->post('/api/admin/api-usage', [AdminController::class, 'apiUsage']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
