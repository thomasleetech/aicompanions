<?php
/**
 * AI Companions - White Label Installation Script
 * 
 * Upload this file to your server and run it via browser or CLI
 * php install.php
 */

// Prevent direct access after installation
if (file_exists('installed.lock')) {
    die('Installation already completed. Delete installed.lock to reinstall.');
}

$step = $_GET['step'] ?? ($_POST['step'] ?? 1);
$errors = [];
$success = [];

// License verification
function verifyLicense($licenseKey, $domain) {
    // In production, this would call your license server
    // For now, we'll use a simple validation
    $licenseServer = 'https://api.aicompanions.com/verify-license';
    
    // Simulated license check - replace with real API call
    $validPrefixes = ['AICWL-', 'AICP-', 'AICE-']; // White-label, Pro, Enterprise
    $isValidFormat = false;
    foreach ($validPrefixes as $prefix) {
        if (strpos($licenseKey, $prefix) === 0 && strlen($licenseKey) === 24) {
            $isValidFormat = true;
            break;
        }
    }
    
    if (!$isValidFormat) {
        return ['valid' => false, 'error' => 'Invalid license key format'];
    }
    
    // In production: call license server
    // $response = file_get_contents($licenseServer . '?key=' . urlencode($licenseKey) . '&domain=' . urlencode($domain));
    // return json_decode($response, true);
    
    // For demo, accept any properly formatted key
    return [
        'valid' => true,
        'type' => strpos($licenseKey, 'AICE-') === 0 ? 'enterprise' : (strpos($licenseKey, 'AICP-') === 0 ? 'pro' : 'white-label'),
        'expires' => date('Y-m-d', strtotime('+1 year')),
        'features' => ['chat', 'voice', 'images', 'admin']
    ];
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1: // License verification
            $licenseKey = trim($_POST['license_key'] ?? '');
            $domain = $_SERVER['HTTP_HOST'];
            
            if (empty($licenseKey)) {
                $errors[] = 'License key is required';
            } else {
                $licenseResult = verifyLicense($licenseKey, $domain);
                if ($licenseResult['valid']) {
                    $_SESSION['license'] = $licenseResult;
                    $_SESSION['license_key'] = $licenseKey;
                    $step = 2;
                } else {
                    $errors[] = $licenseResult['error'] ?? 'Invalid license key';
                }
            }
            break;
            
        case 2: // Database configuration
            $dbHost = trim($_POST['db_host'] ?? 'localhost');
            $dbName = trim($_POST['db_name'] ?? '');
            $dbUser = trim($_POST['db_user'] ?? '');
            $dbPass = $_POST['db_pass'] ?? '';
            
            if (empty($dbName) || empty($dbUser)) {
                $errors[] = 'Database name and user are required';
            } else {
                try {
                    $pdo = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Create database if not exists
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo->exec("USE `$dbName`");
                    
                    $_SESSION['db'] = compact('dbHost', 'dbName', 'dbUser', 'dbPass');
                    $success[] = 'Database connection successful';
                    $step = 3;
                } catch (PDOException $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }
            break;
            
        case 3: // Branding configuration
            $_SESSION['branding'] = [
                'site_name' => trim($_POST['site_name'] ?? 'AI Companions'),
                'site_tagline' => trim($_POST['site_tagline'] ?? 'Your AI Partner'),
                'primary_color' => trim($_POST['primary_color'] ?? '#10b981'),
                'logo_url' => trim($_POST['logo_url'] ?? ''),
                'support_email' => trim($_POST['support_email'] ?? ''),
                'admin_user' => trim($_POST['admin_user'] ?? 'admin'),
                'admin_pass' => $_POST['admin_pass'] ?? 'admin123!',
            ];
            $step = 4;
            break;
            
        case 4: // API Keys
            $_SESSION['api_keys'] = [
                'openai_key' => trim($_POST['openai_key'] ?? ''),
                'anthropic_key' => trim($_POST['anthropic_key'] ?? ''),
                'elevenlabs_key' => trim($_POST['elevenlabs_key'] ?? ''),
                'stripe_public' => trim($_POST['stripe_public'] ?? ''),
                'stripe_secret' => trim($_POST['stripe_secret'] ?? ''),
            ];
            $step = 5;
            break;
            
        case 5: // Install
            // Generate configuration file
            $config = generateConfig();
            
            // Create app.php
            if (createAppFile($config)) {
                $success[] = 'Application files created';
            } else {
                $errors[] = 'Failed to create application files';
            }
            
            // Create admin.php
            if (createAdminFile($config)) {
                $success[] = 'Admin panel created';
            } else {
                $errors[] = 'Failed to create admin panel';
            }
            
            // Initialize database
            if (initializeDatabase($config)) {
                $success[] = 'Database initialized';
            } else {
                $errors[] = 'Failed to initialize database';
            }
            
            // Create lock file
            if (empty($errors)) {
                file_put_contents('installed.lock', json_encode([
                    'installed_at' => date('Y-m-d H:i:s'),
                    'license_key' => $_SESSION['license_key'],
                    'version' => '2.1.0'
                ]));
                $step = 6;
            }
            break;
    }
}

function generateConfig() {
    return [
        'license_key' => $_SESSION['license_key'],
        'db_host' => $_SESSION['db']['dbHost'],
        'db_name' => $_SESSION['db']['dbName'],
        'db_user' => $_SESSION['db']['dbUser'],
        'db_pass' => $_SESSION['db']['dbPass'],
        'site_name' => $_SESSION['branding']['site_name'],
        'site_tagline' => $_SESSION['branding']['site_tagline'],
        'primary_color' => $_SESSION['branding']['primary_color'],
        'logo_url' => $_SESSION['branding']['logo_url'],
        'support_email' => $_SESSION['branding']['support_email'],
        'admin_user' => $_SESSION['branding']['admin_user'],
        'admin_pass' => $_SESSION['branding']['admin_pass'],
        'openai_key' => $_SESSION['api_keys']['openai_key'],
        'anthropic_key' => $_SESSION['api_keys']['anthropic_key'],
        'elevenlabs_key' => $_SESSION['api_keys']['elevenlabs_key'],
        'stripe_public' => $_SESSION['api_keys']['stripe_public'],
        'stripe_secret' => $_SESSION['api_keys']['stripe_secret'],
    ];
}

function createAppFile($config) {
    // In production, this would modify a template file
    // For now, create a config.php that app.php includes
    $configContent = "<?php\n// Auto-generated configuration - DO NOT EDIT\n";
    $configContent .= "define('LICENSE_KEY', " . var_export($config['license_key'], true) . ");\n";
    $configContent .= "\$config = [\n";
    $configContent .= "    'db_host' => " . var_export($config['db_host'], true) . ",\n";
    $configContent .= "    'db_name' => " . var_export($config['db_name'], true) . ",\n";
    $configContent .= "    'db_user' => " . var_export($config['db_user'], true) . ",\n";
    $configContent .= "    'db_pass' => " . var_export($config['db_pass'], true) . ",\n";
    $configContent .= "    'site_name' => " . var_export($config['site_name'], true) . ",\n";
    $configContent .= "    'site_tagline' => " . var_export($config['site_tagline'], true) . ",\n";
    $configContent .= "    'primary_color' => " . var_export($config['primary_color'], true) . ",\n";
    $configContent .= "    'logo_url' => " . var_export($config['logo_url'], true) . ",\n";
    $configContent .= "    'support_email' => " . var_export($config['support_email'], true) . ",\n";
    $configContent .= "    'openai_key' => " . var_export($config['openai_key'], true) . ",\n";
    $configContent .= "    'anthropic_key' => " . var_export($config['anthropic_key'], true) . ",\n";
    $configContent .= "    'elevenlabs_key' => " . var_export($config['elevenlabs_key'], true) . ",\n";
    $configContent .= "    'stripe_public' => " . var_export($config['stripe_public'], true) . ",\n";
    $configContent .= "    'stripe_secret' => " . var_export($config['stripe_secret'], true) . ",\n";
    $configContent .= "    'upload_dir' => 'uploads/',\n";
    $configContent .= "    'audio_dir' => 'audio/',\n";
    $configContent .= "];\n";
    $configContent .= "\n// License validation\n";
    $configContent .= "function validateLicense() {\n";
    $configContent .= "    \$lockFile = 'installed.lock';\n";
    $configContent .= "    if (!file_exists(\$lockFile)) return false;\n";
    $configContent .= "    \$data = json_decode(file_get_contents(\$lockFile), true);\n";
    $configContent .= "    return \$data['license_key'] === LICENSE_KEY;\n";
    $configContent .= "}\n";
    $configContent .= "if (!validateLicense()) { die('Invalid license. Please reinstall.'); }\n";
    
    return file_put_contents('config.php', $configContent) !== false;
}

function createAdminFile($config) {
    $adminConfig = "<?php\n";
    $adminConfig .= "\$ADMIN_USER = " . var_export($config['admin_user'], true) . ";\n";
    $adminConfig .= "\$ADMIN_PASS = " . var_export($config['admin_pass'], true) . ";\n";
    return file_put_contents('admin_config.php', $adminConfig) !== false;
}

function initializeDatabase($config) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
            $config['db_user'],
            $config['db_pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create core tables
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                display_name VARCHAR(100),
                avatar_url TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS gigs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                description TEXT NOT NULL,
                companion_type ENUM('boyfriend', 'girlfriend', 'non-binary') NOT NULL,
                category VARCHAR(50) NOT NULL,
                price_per_hour DECIMAL(10,2) NOT NULL,
                image_url TEXT,
                ai_persona TEXT,
                is_active TINYINT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS conversations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                gig_id INT NOT NULL,
                last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS chat_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                role ENUM('user', 'assistant') NOT NULL,
                content TEXT NOT NULL,
                audio_url TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Companions - Installation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #0a0a0a; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .installer { background: #111; border: 1px solid #222; border-radius: 16px; width: 100%; max-width: 600px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #10b981, #059669); padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 8px; }
        .header p { opacity: 0.9; font-size: 14px; }
        .steps { display: flex; padding: 20px; border-bottom: 1px solid #222; }
        .step { flex: 1; text-align: center; font-size: 12px; color: #666; }
        .step.active { color: #10b981; }
        .step.done { color: #22c55e; }
        .step span { display: block; width: 30px; height: 30px; margin: 0 auto 8px; border: 2px solid currentColor; border-radius: 50%; line-height: 26px; font-weight: 600; }
        .step.active span, .step.done span { background: currentColor; color: #000; }
        .content { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; color: #888; }
        .form-group input, .form-group select { width: 100%; padding: 12px; background: #0a0a0a; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #10b981; }
        .form-group small { display: block; margin-top: 6px; font-size: 12px; color: #666; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btn { display: inline-block; padding: 14px 28px; background: #10b981; color: #000; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; width: 100%; }
        .btn:hover { background: #0d9f72; }
        .btn-secondary { background: #333; color: #fff; }
        .error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .complete { text-align: center; padding: 40px 20px; }
        .complete h2 { font-size: 28px; margin-bottom: 16px; }
        .complete p { color: #888; margin-bottom: 24px; }
        .links { display: flex; gap: 12px; justify-content: center; }
        .links a { padding: 12px 24px; background: #10b981; color: #000; text-decoration: none; border-radius: 8px; font-weight: 600; }
        .links a.secondary { background: #333; color: #fff; }
    </style>
</head>
<body>
    <div class="installer">
        <div class="header">
            <h1>AI Companions Installation</h1>
            <p>White-Label Platform Setup</p>
        </div>
        
        <div class="steps">
            <div class="step <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>"><span>1</span>License</div>
            <div class="step <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>"><span>2</span>Database</div>
            <div class="step <?= $step >= 3 ? ($step > 3 ? 'done' : 'active') : '' ?>"><span>3</span>Branding</div>
            <div class="step <?= $step >= 4 ? ($step > 4 ? 'done' : 'active') : '' ?>"><span>4</span>API Keys</div>
            <div class="step <?= $step >= 5 ? ($step > 5 ? 'done' : 'active') : '' ?>"><span>5</span>Install</div>
            <div class="step <?= $step >= 6 ? 'done' : '' ?>"><span>6</span>Done</div>
        </div>
        
        <div class="content">
            <?php foreach ($errors as $error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
            
            <?php foreach ($success as $msg): ?>
                <div class="success"><?= htmlspecialchars($msg) ?></div>
            <?php endforeach; ?>
            
            <?php if ($step == 1): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="1">
                    <div class="form-group">
                        <label>License Key</label>
                        <input type="text" name="license_key" placeholder="AICWL-XXXX-XXXX-XXXX" required>
                        <small>Enter your AI Companions white-label license key</small>
                    </div>
                    <button type="submit" class="btn">Verify License</button>
                </form>
                
            <?php elseif ($step == 2): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    <div class="form-group">
                        <label>Database Host</label>
                        <input type="text" name="db_host" value="localhost">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Database Name</label>
                            <input type="text" name="db_name" required>
                        </div>
                        <div class="form-group">
                            <label>Database User</label>
                            <input type="text" name="db_user" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Database Password</label>
                        <input type="password" name="db_pass">
                    </div>
                    <button type="submit" class="btn">Test Connection</button>
                </form>
                
            <?php elseif ($step == 3): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Site Name</label>
                            <input type="text" name="site_name" value="AI Companions" required>
                        </div>
                        <div class="form-group">
                            <label>Tagline</label>
                            <input type="text" name="site_tagline" value="Your AI Partner">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Primary Color</label>
                            <input type="color" name="primary_color" value="#10b981">
                        </div>
                        <div class="form-group">
                            <label>Logo URL (optional)</label>
                            <input type="url" name="logo_url" placeholder="https://...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Support Email</label>
                        <input type="email" name="support_email" placeholder="support@yoursite.com">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Admin Username</label>
                            <input type="text" name="admin_user" value="admin" required>
                        </div>
                        <div class="form-group">
                            <label>Admin Password</label>
                            <input type="password" name="admin_pass" value="admin123!" required>
                        </div>
                    </div>
                    <button type="submit" class="btn">Continue</button>
                </form>
                
            <?php elseif ($step == 4): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="4">
                    <div class="form-group">
                        <label>OpenAI API Key (Required)</label>
                        <input type="text" name="openai_key" placeholder="sk-..." required>
                        <small>Get from platform.openai.com</small>
                    </div>
                    <div class="form-group">
                        <label>Anthropic API Key (Optional)</label>
                        <input type="text" name="anthropic_key" placeholder="sk-ant-...">
                    </div>
                    <div class="form-group">
                        <label>ElevenLabs API Key (Optional - for premium voices)</label>
                        <input type="text" name="elevenlabs_key">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Stripe Public Key</label>
                            <input type="text" name="stripe_public" placeholder="pk_...">
                        </div>
                        <div class="form-group">
                            <label>Stripe Secret Key</label>
                            <input type="text" name="stripe_secret" placeholder="sk_...">
                        </div>
                    </div>
                    <button type="submit" class="btn">Continue</button>
                </form>
                
            <?php elseif ($step == 5): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="5">
                    <h3 style="margin-bottom: 20px;">Ready to Install</h3>
                    <p style="color: #888; margin-bottom: 24px;">Click below to create your AI Companions platform:</p>
                    <ul style="color: #888; margin-bottom: 24px; padding-left: 20px;">
                        <li>Generate configuration files</li>
                        <li>Create database tables</li>
                        <li>Set up admin panel</li>
                        <li>Initialize default companions</li>
                    </ul>
                    <button type="submit" class="btn">Install Now</button>
                </form>
                
            <?php elseif ($step == 6): ?>
                <div class="complete">
                    <div style="font-size: 4rem; margin-bottom: 16px;">✓</div>
                    <h2>Installation Complete!</h2>
                    <p>Your AI Companions platform is ready to use.</p>
                    <div class="links">
                        <a href="app.php">Launch App</a>
                        <a href="admin.php" class="secondary">Admin Panel</a>
                    </div>
                    <p style="margin-top: 24px; font-size: 13px; color: #666;">
                        Delete install.php for security after installation.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
