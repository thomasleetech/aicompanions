<?php
/**
 * AI Companions - Front Controller
 * All requests route through here.
 */

session_start();

// Base paths - resolve symlinks to get the real filesystem path
define('PUBLIC_PATH', realpath(__DIR__) ?: __DIR__);
define('BASE_PATH', realpath(dirname(__DIR__)) ?: dirname(__DIR__));

// Load core classes explicitly (avoids autoloader path issues on shared hosts)
require_once BASE_PATH . '/src/Env.php';
require_once BASE_PATH . '/src/Database.php';
require_once BASE_PATH . '/src/Router.php';
require_once BASE_PATH . '/src/View.php';
require_once BASE_PATH . '/src/Auth.php';
require_once BASE_PATH . '/src/CSRF.php';

// Autoloader for controllers, services, models
spl_autoload_register(function (string $class) {
    $dirs = ['Controllers', 'Services', 'Models'];
    foreach ($dirs as $dir) {
        $path = BASE_PATH . '/src/' . $dir . '/' . $class . '.php';
        if (file_exists($path)) { require_once $path; return; }
    }
});

// Detect base URL path (e.g. '/aicompanions' when app lives in a subdirectory)
// SCRIPT_NAME is '/aicompanions/public/index.php' → we want '/aicompanions'
$scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', $scriptDir === '/' || $scriptDir === '\\' ? '' : rtrim($scriptDir, '/'));

// Helper function for views - generates correct URLs regardless of subdirectory
function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Boot
Env::load(BASE_PATH);

// Only connect to DB if .env exists (otherwise installer hasn't run yet)
if (!file_exists(BASE_PATH . '/.env') || !Env::get('DB_NAME')) {
    header('Location: ' . BASE_URL . '/install.php');
    exit;
}

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
    // Run additional migrations (003+) - safe to re-run (CREATE IF NOT EXISTS)
    $extraMigrations = glob(BASE_PATH . '/migrations/0[0-9][3-9]_*.sql');
    if ($extraMigrations) {
        sort($extraMigrations);
        foreach ($extraMigrations as $migFile) {
            $migSql = file_get_contents($migFile);
            $migStmts = preg_split('/;\s*$/m', $migSql);
            foreach ($migStmts as $stmt) {
                $stmt = trim($stmt);
                if ($stmt !== '') {
                    try { Database::pdo()->exec($stmt); } catch (Exception $e2) { /* ignore */ }
                }
            }
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
