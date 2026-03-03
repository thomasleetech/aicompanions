<?php
/**
 * AI Companions - Installation Wizard
 * Self-contained installer that configures .env, creates DB tables, and seeds data.
 */

// MUST be first - this was the bug in the old installer
session_start();

// Detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$baseUrl = $protocol . '://' . $host . $scriptDir;
$basePath = __DIR__;

// Initialize session storage for wizard
if (!isset($_SESSION['install'])) {
    $_SESSION['install'] = ['step' => 1];
}

// Handle form submissions
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'test_db':
            $dbHost = trim($_POST['db_host'] ?? 'localhost');
            $dbName = trim($_POST['db_name'] ?? '');
            $dbUser = trim($_POST['db_user'] ?? '');
            $dbPass = $_POST['db_pass'] ?? '';

            if (empty($dbName) || empty($dbUser)) {
                $error = 'Database name and user are required.';
                break;
            }

            try {
                $pdo = new PDO(
                    "mysql:host={$dbHost};charset=utf8mb4",
                    $dbUser,
                    $dbPass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                // Try to select or create database
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");

                $_SESSION['install']['db'] = compact('dbHost', 'dbName', 'dbUser', 'dbPass');
                $_SESSION['install']['step'] = 2;

                header('Location: install.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Database connection failed: ' . $e->getMessage();
            }
            break;

        case 'save_config':
            $openaiKey = trim($_POST['openai_key'] ?? '');
            $elevenLabsKey = trim($_POST['elevenlabs_key'] ?? '');
            $stripePublic = trim($_POST['stripe_public'] ?? '');
            $stripeSecret = trim($_POST['stripe_secret'] ?? '');
            $appName = trim($_POST['app_name'] ?? 'Companion');
            $adminUser = trim($_POST['admin_user'] ?? 'admin');
            $adminPass = $_POST['admin_pass'] ?? 'changeme';

            $_SESSION['install']['config'] = compact(
                'openaiKey', 'elevenLabsKey', 'stripePublic', 'stripeSecret',
                'appName', 'adminUser', 'adminPass'
            );
            $_SESSION['install']['step'] = 3;

            header('Location: install.php');
            exit;

        case 'finish':
            $db = $_SESSION['install']['db'] ?? null;
            $cfg = $_SESSION['install']['config'] ?? null;

            if (!$db || !$cfg) {
                $error = 'Missing configuration. Please start over.';
                $_SESSION['install'] = ['step' => 1];
                break;
            }

            // 1. Write .env
            $csrfSecret = bin2hex(random_bytes(32));
            $envContent = "# Application\n"
                . "APP_NAME={$cfg['appName']}\n"
                . "APP_URL={$baseUrl}\n"
                . "APP_DEBUG=false\n\n"
                . "# Database\n"
                . "DB_HOST={$db['dbHost']}\n"
                . "DB_NAME={$db['dbName']}\n"
                . "DB_USER={$db['dbUser']}\n"
                . "DB_PASS={$db['dbPass']}\n\n"
                . "# API Keys\n"
                . "OPENAI_API_KEY={$cfg['openaiKey']}\n"
                . "ELEVENLABS_API_KEY={$cfg['elevenLabsKey']}\n"
                . "STRIPE_PUBLIC_KEY={$cfg['stripePublic']}\n"
                . "STRIPE_SECRET_KEY={$cfg['stripeSecret']}\n\n"
                . "# Admin\n"
                . "ADMIN_USER={$cfg['adminUser']}\n"
                . "ADMIN_PASS={$cfg['adminPass']}\n\n"
                . "# Security\n"
                . "CSRF_SECRET={$csrfSecret}\n";

            $envWritten = file_put_contents($basePath . '/.env', $envContent);
            if ($envWritten === false) {
                $error = 'Could not write .env file. Check directory permissions (need write access to ' . $basePath . ').';
                break;
            }

            // 2. Run migrations
            try {
                $pdo = new PDO(
                    "mysql:host={$db['dbHost']};dbname={$db['dbName']};charset=utf8mb4",
                    $db['dbUser'],
                    $db['dbPass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

                $schemaFile = $basePath . '/migrations/001_schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $pdo->exec($sql);
                } else {
                    $error = 'Migration file not found: migrations/001_schema.sql';
                    break;
                }

                // 3. Seed data
                $seedFile = $basePath . '/migrations/002_seed.sql';
                if (file_exists($seedFile)) {
                    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    if ($userCount == 0) {
                        $hash = password_hash('demo123', PASSWORD_DEFAULT);
                        $seed = file_get_contents($seedFile);
                        $seed = str_replace('$2y$10$placeholder', $hash, $seed);
                        $pdo->exec($seed);
                    }
                }

                $_SESSION['install']['step'] = 4;
                header('Location: install.php');
                exit;

            } catch (PDOException $e) {
                $error = 'Migration error: ' . $e->getMessage();
            }
            break;

        case 'restart':
            $_SESSION['install'] = ['step' => 1];
            header('Location: install.php');
            exit;
    }
}

$step = $_SESSION['install']['step'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - AI Companions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --bg: #0a0a0a; --bg2: #111; --bg3: #1a1a1a; --text: #fff; --text2: #888; --accent: #10b981; --accent2: #0d9f72; --red: #ef4444; --border: #222; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; -webkit-font-smoothing: antialiased; }

        .installer { width: 100%; max-width: 520px; }
        .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 20px; margin-bottom: 32px; justify-content: center; }
        .logo-icon { width: 40px; height: 40px; background: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #000; font-weight: 800; font-size: 18px; }

        .steps { display: flex; justify-content: center; gap: 8px; margin-bottom: 32px; }
        .step-dot { width: 32px; height: 4px; border-radius: 4px; background: var(--bg3); transition: all 0.3s; }
        .step-dot.active { background: var(--accent); }
        .step-dot.done { background: rgba(16, 185, 129, 0.4); }

        .card { background: var(--bg2); border: 1px solid var(--border); border-radius: 16px; padding: 36px; }
        .card h2 { font-size: 22px; margin-bottom: 6px; }
        .card .subtitle { color: var(--text2); font-size: 14px; margin-bottom: 24px; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; color: var(--text2); margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 12px 14px; background: var(--bg3); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 14px; font-family: inherit; transition: border-color 0.2s; }
        .form-group input:focus { outline: none; border-color: var(--accent); }
        .form-group input::placeholder { color: #555; }
        .form-group small { display: block; margin-top: 4px; font-size: 12px; color: var(--text2); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; border: none; cursor: pointer; transition: all 0.2s; font-family: inherit; width: 100%; }
        .btn-primary { background: var(--accent); color: #000; }
        .btn-primary:hover { background: var(--accent2); transform: translateY(-1px); }
        .btn-ghost { background: transparent; color: var(--text); border: 1px solid var(--border); margin-top: 8px; }
        .btn-ghost:hover { background: var(--bg3); }

        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; line-height: 1.5; }
        .alert-error { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--red); }
        .alert-success { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--accent); }

        .success-screen { text-align: center; padding: 20px 0; }
        .success-icon { font-size: 48px; margin-bottom: 16px; }
        .success-screen h2 { margin-bottom: 12px; }
        .success-screen p { color: var(--text2); font-size: 14px; margin-bottom: 24px; }
        .success-links { display: flex; flex-direction: column; gap: 8px; }

        .section-label { font-size: 13px; font-weight: 600; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; margin-top: 20px; }
        .section-label:first-child { margin-top: 0; }

        @media (max-width: 480px) {
            .card { padding: 24px; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="installer">
        <div class="logo">
            <div class="logo-icon">C</div>
            <span>Companion</span>
        </div>

        <div class="steps">
            <div class="step-dot <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>"></div>
            <div class="step-dot <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>"></div>
            <div class="step-dot <?= $step >= 3 ? ($step > 3 ? 'done' : 'active') : '' ?>"></div>
            <div class="step-dot <?= $step >= 4 ? 'active' : '' ?>"></div>
        </div>

        <div class="card">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <!-- Step 1: Database -->
                <h2>Database Setup</h2>
                <p class="subtitle">Connect to your MySQL database</p>

                <form method="POST">
                    <input type="hidden" name="action" value="test_db">

                    <div class="form-group">
                        <label for="db_host">Host</label>
                        <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($_SESSION['install']['db']['dbHost'] ?? 'localhost') ?>" placeholder="localhost">
                    </div>
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($_SESSION['install']['db']['dbName'] ?? '') ?>" placeholder="aicompanions" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="db_user">Username</label>
                            <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($_SESSION['install']['db']['dbUser'] ?? '') ?>" placeholder="root" required>
                        </div>
                        <div class="form-group">
                            <label for="db_pass">Password</label>
                            <input type="password" id="db_pass" name="db_pass" value="<?= htmlspecialchars($_SESSION['install']['db']['dbPass'] ?? '') ?>" placeholder="Database password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Test Connection &amp; Continue</button>
                </form>

            <?php elseif ($step === 2): ?>
                <!-- Step 2: API Keys & Admin -->
                <h2>Configuration</h2>
                <p class="subtitle">Set up API keys and admin credentials</p>

                <form method="POST">
                    <input type="hidden" name="action" value="save_config">

                    <div class="section-label">Branding</div>
                    <div class="form-group">
                        <label for="app_name">App Name</label>
                        <input type="text" id="app_name" name="app_name" value="<?= htmlspecialchars($_SESSION['install']['config']['appName'] ?? 'Companion') ?>" placeholder="Companion">
                    </div>

                    <div class="section-label">API Keys</div>
                    <div class="form-group">
                        <label for="openai_key">OpenAI API Key</label>
                        <input type="password" id="openai_key" name="openai_key" value="<?= htmlspecialchars($_SESSION['install']['config']['openaiKey'] ?? '') ?>" placeholder="sk-...">
                        <small>Required for AI chat. Get one at platform.openai.com</small>
                    </div>
                    <div class="form-group">
                        <label for="elevenlabs_key">ElevenLabs API Key (optional)</label>
                        <input type="password" id="elevenlabs_key" name="elevenlabs_key" value="<?= htmlspecialchars($_SESSION['install']['config']['elevenLabsKey'] ?? '') ?>" placeholder="Optional - for voice messages">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="stripe_public">Stripe Public Key</label>
                            <input type="password" id="stripe_public" name="stripe_public" value="<?= htmlspecialchars($_SESSION['install']['config']['stripePublic'] ?? '') ?>" placeholder="pk_...">
                        </div>
                        <div class="form-group">
                            <label for="stripe_secret">Stripe Secret Key</label>
                            <input type="password" id="stripe_secret" name="stripe_secret" value="<?= htmlspecialchars($_SESSION['install']['config']['stripeSecret'] ?? '') ?>" placeholder="sk_...">
                        </div>
                    </div>

                    <div class="section-label">Admin Account</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin_user">Admin Username</label>
                            <input type="text" id="admin_user" name="admin_user" value="<?= htmlspecialchars($_SESSION['install']['config']['adminUser'] ?? 'admin') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="admin_pass">Admin Password</label>
                            <input type="password" id="admin_pass" name="admin_pass" value="<?= htmlspecialchars($_SESSION['install']['config']['adminPass'] ?? '') ?>" placeholder="Choose a strong password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save &amp; Continue</button>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="restart">
                        <button type="submit" class="btn btn-ghost">Back to Start</button>
                    </form>
                </form>

            <?php elseif ($step === 3): ?>
                <!-- Step 3: Install -->
                <h2>Ready to Install</h2>
                <p class="subtitle">We'll create your database tables and seed demo companions</p>

                <div style="background:var(--bg3);border-radius:8px;padding:16px;margin-bottom:20px;font-size:13px;color:var(--text2);line-height:1.8">
                    <div><strong style="color:var(--text)">Database:</strong> <?= htmlspecialchars($_SESSION['install']['db']['dbName'] ?? '') ?> @ <?= htmlspecialchars($_SESSION['install']['db']['dbHost'] ?? '') ?></div>
                    <div><strong style="color:var(--text)">App Name:</strong> <?= htmlspecialchars($_SESSION['install']['config']['appName'] ?? 'Companion') ?></div>
                    <div><strong style="color:var(--text)">OpenAI:</strong> <?= !empty($_SESSION['install']['config']['openaiKey']) ? 'Configured' : 'Not set' ?></div>
                    <div><strong style="color:var(--text)">Stripe:</strong> <?= !empty($_SESSION['install']['config']['stripePublic']) ? 'Configured' : 'Not set' ?></div>
                    <div><strong style="color:var(--text)">Admin:</strong> <?= htmlspecialchars($_SESSION['install']['config']['adminUser'] ?? 'admin') ?></div>
                </div>

                <p style="font-size:13px;color:var(--text2);margin-bottom:20px">This will create 18 database tables and seed 12 demo AI companions.</p>

                <form method="POST">
                    <input type="hidden" name="action" value="finish">
                    <button type="submit" class="btn btn-primary" id="installBtn" onclick="this.textContent='Installing...';this.disabled=true;this.form.submit();">Install Now</button>
                </form>
                <form method="POST">
                    <input type="hidden" name="action" value="restart">
                    <button type="submit" class="btn btn-ghost">Start Over</button>
                </form>

            <?php elseif ($step === 4): ?>
                <!-- Step 4: Done -->
                <div class="success-screen">
                    <div class="success-icon">&#10003;</div>
                    <h2>Installation Complete!</h2>
                    <p>Your AI Companions platform is ready to go.</p>

                    <div class="success-links">
                        <a href="<?= $baseUrl ?>/public/" class="btn btn-primary">Open Your App</a>
                        <a href="<?= $baseUrl ?>/public/admin" class="btn btn-ghost">Admin Dashboard</a>
                    </div>

                    <p style="margin-top:20px;font-size:12px;color:var(--text2)">You can safely delete install.php after setup.</p>
                </div>
                <?php
                // Clear install session
                unset($_SESSION['install']);
                ?>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>
