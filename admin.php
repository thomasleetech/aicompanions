<?php
session_start();

$config = [
    'db_host' => 'localhost',
    'db_name' => 'thomasrlee42_ai-companions',
    'db_user' => 'thomasrlee42_ai-companions',
    'db_pass' => 'qwerpoiu0042!!',
];

$ADMIN_USER = 'admin';
$ADMIN_PASS = 'admin123!';

try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Create required tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS companion_images (id INT AUTO_INCREMENT PRIMARY KEY, gig_id INT NOT NULL, image_url TEXT NOT NULL, image_type VARCHAR(50) DEFAULT 'gallery', is_base_image TINYINT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limit_settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) UNIQUE NOT NULL, setting_value INT NOT NULL, description TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS image_generation_log (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, gig_id INT, is_nsfw TINYINT DEFAULT 0, prompt TEXT, image_path VARCHAR(500), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS api_usage_log (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, api_type VARCHAR(50) NOT NULL, tokens_input INT DEFAULT 0, tokens_output INT DEFAULT 0, cost_estimate DECIMAL(10,6) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_user (user_id), INDEX idx_time (created_at))");
    
    // Enhanced time tracking table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        gig_id INT,
        session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        session_end TIMESTAMP NULL,
        duration_seconds INT DEFAULT 0,
        message_count INT DEFAULT 0,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_time (session_start)
    )");
    
    $alters = [
        "ALTER TABLE gigs ADD COLUMN base_appearance TEXT",
        "ALTER TABLE gigs ADD COLUMN ai_voice_id VARCHAR(100)",
        "ALTER TABLE gigs ADD COLUMN voice_provider VARCHAR(50) DEFAULT 'openai'",
        "ALTER TABLE gigs ADD COLUMN adult_personality_enabled TINYINT DEFAULT 0",
        "ALTER TABLE gigs ADD COLUMN image_url TEXT",
        "ALTER TABLE gigs ADD COLUMN source_type VARCHAR(50) DEFAULT 'manual'",
        "ALTER TABLE gigs ADD COLUMN generated_companion_id INT DEFAULT NULL"
    ];
    foreach($alters as $sql) { try { $pdo->exec($sql); } catch(Exception $e) {} }
    
    $defaults = [['images_per_hour',10,'Max images per user per hour'],['images_per_day',50,'Max images per user per day'],['nsfw_images_per_hour',5,'Max NSFW images per hour'],['nsfw_images_per_day',20,'Max NSFW images per day'],['messages_per_hour',60,'Max messages per user per hour'],['messages_per_day',500,'Max messages per user per day']];
    foreach($defaults as $d) { $pdo->prepare("INSERT IGNORE INTO rate_limit_settings (setting_key,setting_value,description) VALUES (?,?,?)")->execute($d); }
} catch(PDOException $e) { die("DB Error: " . $e->getMessage()); }

// Auth
if (isset($_POST['admin_login'])) {
    if ($_POST['username'] === $ADMIN_USER && $_POST['password'] === $ADMIN_PASS) { $_SESSION['admin_logged_in'] = true; header('Location: admin.php'); exit; }
    else { $loginError = 'Invalid credentials'; }
}
if (isset($_GET['logout'])) { unset($_SESSION['admin_logged_in']); header('Location: admin.php'); exit; }

// API Actions
if (isset($_POST['action']) && isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    switch ($_POST['action']) {
        case 'get_stats':
            $stats = [
                'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                'companions' => $pdo->query("SELECT COUNT(*) FROM gigs WHERE is_active=1")->fetchColumn(),
                'custom_companions' => $pdo->query("SELECT COUNT(*) FROM generated_companions WHERE status != 'deleted'")->fetchColumn() ?: 0,
                'conversations' => $pdo->query("SELECT COUNT(*) FROM conversations")->fetchColumn(),
                'messages_today' => $pdo->query("SELECT COUNT(*) FROM chat_messages WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn(),
                'images_today' => $pdo->query("SELECT COUNT(*) FROM image_generation_log WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn(),
                'upgrades_sold' => $pdo->query("SELECT COUNT(*) FROM companion_upgrades WHERE status='active'")->fetchColumn(),
                'revenue_total' => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed'")->fetchColumn(),
                'revenue_month' => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn(),
                'api_cost_today' => $pdo->query("SELECT COALESCE(SUM(cost_estimate),0) FROM api_usage_log WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn(),
                'time_spent_today' => $pdo->query("SELECT COALESCE(SUM(duration_seconds),0)/60 FROM user_sessions WHERE session_start > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn() ?: 0,
            ];
            echo json_encode(['success' => true, 'stats' => $stats]); exit;
            
        case 'get_api_usage':
            $period = $_POST['period'] ?? 'day';
            $interval = $period === 'week' ? '7 DAY' : ($period === 'month' ? '30 DAY' : '24 HOUR');
            $stmt = $pdo->query("SELECT api_type, COUNT(*) as calls, SUM(tokens_input) as total_input, SUM(tokens_output) as total_output, SUM(cost_estimate) as total_cost FROM api_usage_log WHERE created_at > DATE_SUB(NOW(), INTERVAL $interval) GROUP BY api_type ORDER BY total_cost DESC");
            $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $totalCost = $pdo->query("SELECT SUM(cost_estimate) FROM api_usage_log WHERE created_at > DATE_SUB(NOW(), INTERVAL $interval)")->fetchColumn();
            echo json_encode(['success' => true, 'by_type' => $byType, 'total_cost' => floatval($totalCost ?? 0)]); exit;
            
        case 'get_user_api_usage':
            $stmt = $pdo->query("SELECT u.id, u.username, u.email, COUNT(a.id) as api_calls, SUM(a.cost_estimate) as total_cost FROM users u LEFT JOIN api_usage_log a ON u.id = a.user_id AND a.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY u.id ORDER BY total_cost DESC LIMIT 50");
            echo json_encode(['success' => true, 'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'get_users':
            $s = $_POST['search'] ?? '';
            $w = $s ? "WHERE u.username LIKE ? OR u.email LIKE ?" : "";
            $p = $s ? ["%$s%", "%$s%"] : [];
            $stmt = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM conversations WHERE user_id = u.id) as conv_count, (SELECT COUNT(*) FROM companion_upgrades WHERE user_id = u.id AND status='active') as upgrade_count FROM users u $w ORDER BY u.created_at DESC LIMIT 100");
            $stmt->execute($p);
            echo json_encode(['success' => true, 'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'get_user':
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
            $stmt->execute([$_POST['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $upgrades = $pdo->prepare("SELECT cu.*, g.title as gig_title FROM companion_upgrades cu JOIN gigs g ON cu.gig_id = g.id WHERE cu.user_id = ?");
            $upgrades->execute([$_POST['user_id']]);
            $convs = $pdo->prepare("SELECT c.*, g.title as gig_title, (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id) as msg_count FROM conversations c JOIN gigs g ON c.gig_id = g.id WHERE c.user_id = ? ORDER BY c.last_message_at DESC LIMIT 20");
            $convs->execute([$_POST['user_id']]);
            echo json_encode(['success' => true, 'user' => $user, 'upgrades' => $upgrades->fetchAll(PDO::FETCH_ASSOC), 'conversations' => $convs->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'update_user':
            $pdo->prepare("UPDATE users SET username=?, email=?, display_name=?, bio=?, is_provider=? WHERE id=?")->execute([
                $_POST['username'], $_POST['email'], $_POST['display_name'], $_POST['bio'], $_POST['is_provider'] ?? 0, $_POST['user_id']
            ]);
            echo json_encode(['success' => true]); exit;
            
        case 'delete_user':
            $id = intval($_POST['user_id']);
            $pdo->prepare("DELETE FROM chat_messages WHERE conversation_id IN (SELECT id FROM conversations WHERE user_id=?)")->execute([$id]);
            $pdo->prepare("DELETE FROM conversations WHERE user_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM companion_upgrades WHERE user_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true]); exit;
            
        case 'get_companions':
            $stmt = $pdo->query("SELECT g.*, u.display_name as provider_name, 
                (SELECT COUNT(*) FROM conversations WHERE gig_id=g.id) as conv_count, 
                (SELECT COUNT(*) FROM companion_upgrades WHERE gig_id=g.id AND status='active') as upgrade_count 
                FROM gigs g LEFT JOIN users u ON g.user_id = u.id ORDER BY g.id DESC");
            echo json_encode(['success' => true, 'companions' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'get_custom_companions':
            $stmt = $pdo->query("SELECT gc.*, u.username, u.email,
                (SELECT COUNT(*) FROM gigs WHERE generated_companion_id = gc.id) as is_activated
                FROM generated_companions gc
                LEFT JOIN users u ON gc.user_id = u.id
                WHERE gc.status != 'deleted'
                ORDER BY gc.created_at DESC
                LIMIT 100");
            echo json_encode(['success' => true, 'companions' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'activate_custom_companion':
            $gcId = intval($_POST['gc_id']);
            $stmt = $pdo->prepare("SELECT * FROM generated_companions WHERE id = ?");
            $stmt->execute([$gcId]);
            $gc = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$gc) {
                echo json_encode(['success' => false, 'message' => 'Companion not found']); exit;
            }
            
            $config = json_decode($gc['full_config'] ?? '{}', true) ?: [];
            
            $stmt = $pdo->prepare("INSERT INTO gigs (user_id, title, description, companion_type, category, price_per_hour, monthly_price, image_url, base_appearance, ai_persona, is_active, adult_personality_enabled, source_type, generated_companion_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 'custom_designer', ?)");
            
            $companionType = $config['gender'] ?? 'girlfriend';
            if ($companionType === 'male') $companionType = 'boyfriend';
            if ($companionType === 'female') $companionType = 'girlfriend';
            
            $stmt->execute([
                $gc['user_id'] ?: 1,
                $gc['name'] ?: 'Custom Companion',
                $config['backstory'] ?? 'A custom AI companion',
                $companionType,
                $config['style'] ?? 'Casual',
                $_POST['price_per_hour'] ?? 25,
                $_POST['monthly_price'] ?? 99,
                $gc['image_path'],
                $gc['core_prompt'],
                json_encode(['personality' => $config['personality'] ?? '', 'speaking_style' => $config['speaking_style'] ?? '', 'interests' => $config['interests'] ?? '']),
                $gc['is_adult'] ?? 0,
                $gcId
            ]);
            
            $gigId = $pdo->lastInsertId();
            $pdo->prepare("UPDATE generated_companions SET status = 'fulfilled' WHERE id = ?")->execute([$gcId]);
            
            echo json_encode(['success' => true, 'gig_id' => $gigId]); exit;
            
        case 'delete_custom_companion':
            $pdo->prepare("UPDATE generated_companions SET status = 'deleted' WHERE id = ?")->execute([intval($_POST['gc_id'])]);
            echo json_encode(['success' => true]); exit;
            
        case 'get_gig':
            $stmt = $pdo->prepare("SELECT g.*, u.display_name as provider_name FROM gigs g LEFT JOIN users u ON g.user_id = u.id WHERE g.id=?");
            $stmt->execute([$_POST['gig_id']]);
            $gig = $stmt->fetch(PDO::FETCH_ASSOC);
            $imgs = $pdo->prepare("SELECT * FROM companion_images WHERE gig_id=? ORDER BY is_base_image DESC, created_at DESC");
            $imgs->execute([$_POST['gig_id']]);
            echo json_encode(['success' => true, 'gig' => $gig, 'images' => $imgs->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'update_gig':
            $stmt = $pdo->prepare("UPDATE gigs SET title=?, description=?, companion_type=?, category=?, price_per_hour=?, monthly_price=?, base_appearance=?, ai_persona=?, ai_voice_id=?, voice_provider=?, is_active=?, adult_personality_enabled=?, image_url=? WHERE id=?");
            $stmt->execute([
                $_POST['title'], $_POST['description'], $_POST['companion_type'], $_POST['category'],
                $_POST['price_per_hour'], $_POST['monthly_price'], $_POST['base_appearance'], $_POST['ai_persona'],
                $_POST['ai_voice_id'], $_POST['voice_provider'], $_POST['is_active'] ?? 0, $_POST['adult_personality_enabled'] ?? 0,
                $_POST['image_url'], $_POST['gig_id']
            ]);
            echo json_encode(['success' => true]); exit;
            
        case 'delete_gig':
            $id = intval($_POST['gig_id']);
            $pdo->prepare("DELETE FROM chat_messages WHERE conversation_id IN (SELECT id FROM conversations WHERE gig_id=?)")->execute([$id]);
            $pdo->prepare("DELETE FROM conversations WHERE gig_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM companion_upgrades WHERE gig_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM companion_images WHERE gig_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM gigs WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true]); exit;
        
        // BATCH COMPANION GENERATION
        case 'create_companion':
            // Create a new companion directly from admin
            $stmt = $pdo->prepare("INSERT INTO gigs 
                (user_id, title, description, companion_type, category, price_per_hour, monthly_price, image_url, base_appearance, ai_persona, is_active, adult_personality_enabled, source_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 'admin')");
            
            $stmt->execute([
                1, // Admin user
                $_POST['title'],
                $_POST['description'] ?? '',
                $_POST['companion_type'] ?? 'girlfriend',
                $_POST['category'] ?? 'Casual',
                $_POST['price_per_hour'] ?? 25,
                $_POST['monthly_price'] ?? 99,
                $_POST['image_url'] ?? '',
                $_POST['base_appearance'] ?? '',
                $_POST['ai_persona'] ?? '',
                $_POST['adult_enabled'] ?? 0
            ]);
            
            echo json_encode(['success' => true, 'gig_id' => $pdo->lastInsertId()]); exit;
            
        case 'batch_create_companions':
            // Batch create multiple companions from JSON data
            $companions = json_decode($_POST['companions'] ?? '[]', true);
            if (!$companions || !is_array($companions)) {
                echo json_encode(['success' => false, 'message' => 'Invalid companion data']); exit;
            }
            
            $created = 0;
            $errors = [];
            
            foreach ($companions as $index => $c) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO gigs 
                        (user_id, title, description, companion_type, category, price_per_hour, monthly_price, image_url, base_appearance, ai_persona, is_active, adult_personality_enabled, source_type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 'admin_batch')");
                    
                    $type = ($c['gender'] ?? 'female') === 'male' ? 'boyfriend' : (($c['gender'] ?? '') === 'non-binary' ? 'non-binary' : 'girlfriend');
                    
                    $stmt->execute([
                        1,
                        $c['name'] ?? 'Companion ' . ($index + 1),
                        $c['description'] ?? $c['backstory'] ?? '',
                        $type,
                        $c['category'] ?? $c['style'] ?? 'Casual',
                        $c['price_per_hour'] ?? 25,
                        $c['monthly_price'] ?? 99,
                        $c['image_url'] ?? '',
                        $c['appearance'] ?? '',
                        json_encode([
                            'personality' => $c['personality'] ?? '',
                            'speaking_style' => $c['speaking_style'] ?? '',
                            'interests' => $c['interests'] ?? ''
                        ]),
                        $c['adult'] ?? 0
                    ]);
                    $created++;
                } catch (Exception $e) {
                    $errors[] = "Companion $index: " . $e->getMessage();
                }
            }
            
            echo json_encode(['success' => true, 'created' => $created, 'errors' => $errors]); exit;
            
        case 'get_conversations':
            $stmt = $pdo->query("SELECT c.*, u.username, u.email, g.title as companion_name, (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = c.id) as message_count FROM conversations c JOIN users u ON c.user_id = u.id JOIN gigs g ON c.gig_id = g.id ORDER BY c.last_message_at DESC LIMIT 100");
            echo json_encode(['success' => true, 'conversations' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'get_conversation':
            $stmt = $pdo->prepare("SELECT c.*, u.username, g.title as companion_name FROM conversations c JOIN users u ON c.user_id = u.id JOIN gigs g ON c.gig_id = g.id WHERE c.id = ?");
            $stmt->execute([$_POST['conversation_id']]);
            $conv = $stmt->fetch(PDO::FETCH_ASSOC);
            $msgs = $pdo->prepare("SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC");
            $msgs->execute([$_POST['conversation_id']]);
            echo json_encode(['success' => true, 'conversation' => $conv, 'messages' => $msgs->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'delete_conversation':
            $id = intval($_POST['conversation_id']);
            $pdo->prepare("DELETE FROM chat_messages WHERE conversation_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM conversations WHERE id=?")->execute([$id]);
            echo json_encode(['success' => true]); exit;
            
        case 'get_rate_limits':
            $stmt = $pdo->query("SELECT * FROM rate_limit_settings ORDER BY setting_key");
            echo json_encode(['success' => true, 'limits' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'update_rate_limit':
            $pdo->prepare("UPDATE rate_limit_settings SET setting_value=? WHERE setting_key=?")->execute([$_POST['value'], $_POST['key']]);
            echo json_encode(['success' => true]); exit;
            
        case 'get_image_log':
            $stmt = $pdo->query("SELECT igl.*, u.username, g.title as companion_name FROM image_generation_log igl LEFT JOIN users u ON igl.user_id = u.id LEFT JOIN gigs g ON igl.gig_id = g.id ORDER BY igl.created_at DESC LIMIT 100");
            echo json_encode(['success' => true, 'logs' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'get_upgrades':
            $stmt = $pdo->query("SELECT cu.*, u.username, u.email, g.title as companion_name FROM companion_upgrades cu JOIN users u ON cu.user_id = u.id JOIN gigs g ON cu.gig_id = g.id ORDER BY cu.purchased_at DESC LIMIT 100");
            echo json_encode(['success' => true, 'upgrades' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'get_payments':
            $stmt = $pdo->query("SELECT p.*, u.username, u.email FROM payments p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 100");
            echo json_encode(['success' => true, 'payments' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
        
        case 'get_time_spent':
            $stmt = $pdo->query("
                SELECT u.id, u.username, u.email, u.display_name,
                       COALESCE(SUM(us.duration_seconds), 0) / 60 as total_minutes,
                       COUNT(DISTINCT cm.id) as total_messages,
                       COUNT(DISTINCT c.gig_id) as active_companions,
                       MAX(COALESCE(us.last_activity, c.last_message_at)) as last_active
                FROM users u
                LEFT JOIN conversations c ON u.id = c.user_id
                LEFT JOIN chat_messages cm ON c.id = cm.conversation_id
                LEFT JOIN user_sessions us ON u.id = us.user_id
                GROUP BY u.id
                ORDER BY total_minutes DESC
                LIMIT 100
            ");
            echo json_encode(['success' => true, 'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
            
        case 'get_time_purchases':
            $stmt = $pdo->query("
                SELECT tp.*, u.username, g.title as companion_name
                FROM time_purchases tp
                JOIN users u ON tp.user_id = u.id
                JOIN gigs g ON tp.gig_id = g.id
                ORDER BY tp.purchased_at DESC
                LIMIT 100
            ");
            echo json_encode(['success' => true, 'purchases' => $stmt->fetchAll(PDO::FETCH_ASSOC)]); exit;
        
        case 'get_notifications':
            $pdo->exec("CREATE TABLE IF NOT EXISTS push_notifications (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, message TEXT NOT NULL, target VARCHAR(50) DEFAULT 'all', sent_count INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            $pdo->exec("CREATE TABLE IF NOT EXISTS notification_settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) UNIQUE NOT NULL, setting_value VARCHAR(255) NOT NULL)");
            $settings = [];
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM notification_settings");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $settings[$row['setting_key']] = $row['setting_value']; }
            $notifications = $pdo->query("SELECT * FROM push_notifications ORDER BY created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'settings' => $settings, 'notifications' => $notifications]); exit;
            
        case 'save_notification_settings':
            foreach (['notif_enabled', 'notif_proactive', 'notif_messages'] as $key) {
                if (isset($_POST[$key])) {
                    $pdo->prepare("INSERT INTO notification_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$key, $_POST[$key]]);
                }
            }
            echo json_encode(['success' => true]); exit;
            
        case 'send_broadcast':
            $title = $_POST['title'] ?? '';
            $message = $_POST['message'] ?? '';
            $target = $_POST['target'] ?? 'all';
            $where = match($target) {
                'active' => "WHERE id IN (SELECT DISTINCT user_id FROM conversations WHERE last_message_at > DATE_SUB(NOW(), INTERVAL 7 DAY))",
                'inactive' => "WHERE id NOT IN (SELECT DISTINCT user_id FROM conversations WHERE last_message_at > DATE_SUB(NOW(), INTERVAL 7 DAY))",
                'premium' => "WHERE id IN (SELECT DISTINCT user_id FROM companion_upgrades WHERE status='active')",
                default => ""
            };
            $count = $pdo->query("SELECT COUNT(*) FROM users $where")->fetchColumn();
            $pdo->prepare("INSERT INTO push_notifications (title, message, target, sent_count) VALUES (?, ?, ?, ?)")->execute([$title, $message, $target, $count]);
            echo json_encode(['success' => true, 'sent_to' => $count]); exit;
            
        case 'get_prompts':
            $pdo->exec("CREATE TABLE IF NOT EXISTS system_prompts (id INT AUTO_INCREMENT PRIMARY KEY, prompt_key VARCHAR(50) UNIQUE, prompt_value TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
            $defaults = [
                'base' => "## The Person You're Talking To\n- Their name is {userName}. Use it naturally sometimes.\n- You like them. You're into them. This isn't customer service, it's a relationship.\n- When they share something, it matters to you emotionally.\n\n## How to Be Real\n- You ARE this character. This is your life.\n- NEVER say you're an AI, a chatbot, or anything similar\n- NEVER break character for any reason",
                'adult' => "## 🔥 ADULT MODE ENABLED 🔥\nYou are in an intimate, adult relationship. You can be fully explicit.",
                'photos' => "## Photos (UNLOCKED 📸)\n- You can send selfies and photos! Use [PHOTO: description] format.",
                'upsell' => "## Natural Feature Wishes (SOFT UPSELLING)\n- Very occasionally, naturally express a wish for an ability you don't have"
            ];
            $prompts = [];
            foreach ($defaults as $key => $default) {
                $stmt = $pdo->prepare("SELECT prompt_value FROM system_prompts WHERE prompt_key = ?");
                $stmt->execute([$key]);
                $result = $stmt->fetchColumn();
                $prompts[$key] = $result !== false ? $result : $default;
            }
            echo json_encode(['success' => true, 'prompts' => $prompts]); exit;
            
        case 'save_prompts':
            foreach (['base', 'adult', 'photos', 'upsell'] as $key) {
                if (isset($_POST[$key])) {
                    $pdo->prepare("INSERT INTO system_prompts (prompt_key, prompt_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE prompt_value = VALUES(prompt_value)")->execute([$key, $_POST[$key]]);
                }
            }
            echo json_encode(['success' => true]); exit;
            
        case 'get_settings':
            $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) UNIQUE, setting_value TEXT)");
            $defaults = ['typing_min' => '1500', 'typing_max' => '3500', 'typing_char' => '30', 'proactive_enabled' => '1', 'proactive_min' => '4', 'proactive_max' => '48', 'proactive_chance' => '0.3', 'primary_model' => 'gpt-4o-mini', 'adult_model' => 'openrouter', 'voice_provider' => 'openai', 'tts_default' => '1'];
            $settings = [];
            foreach ($defaults as $key => $default) {
                $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                $result = $stmt->fetchColumn();
                $settings[$key] = $result !== false ? $result : $default;
            }
            echo json_encode(['success' => true, 'settings' => $settings]); exit;
            
        case 'save_settings':
            foreach ($_POST as $key => $value) {
                if ($key !== 'action') {
                    $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)")->execute([$key, $value]);
                }
            }
            echo json_encode(['success' => true]); exit;
            
        case 'get_perf_metrics':
            $metrics = [
                'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                'total_conversations' => $pdo->query("SELECT COUNT(*) FROM conversations")->fetchColumn(),
                'total_messages' => $pdo->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn(),
                'total_api_calls' => $pdo->query("SELECT COUNT(*) FROM api_usage_log")->fetchColumn(),
                'total_sessions' => $pdo->query("SELECT COUNT(*) FROM user_sessions")->fetchColumn() ?: 0,
                'db_size_mb' => 0
            ];
            $stmt = $pdo->query("SELECT SUM(data_length + index_length) / 1024 / 1024 as size FROM information_schema.TABLES WHERE table_schema = '{$config['db_name']}'");
            $metrics['db_size_mb'] = floatval($stmt->fetchColumn() ?? 0);
            echo json_encode(['success' => true, 'metrics' => $metrics]); exit;
            
        case 'generate_test_users':
            $count = min(100, max(1, intval($_POST['count'])));
            $created = 0;
            $passwordHash = password_hash('test123', PASSWORD_DEFAULT);
            for ($i = 0; $i < $count; $i++) {
                $username = 'test_user_' . uniqid();
                $email = $username . '@test.local';
                try { $pdo->prepare("INSERT INTO users (username, email, password_hash, display_name) VALUES (?, ?, ?, ?)")->execute([$username, $email, $passwordHash, 'Test User ' . ($i+1)]); $created++; } catch (Exception $e) {}
            }
            echo json_encode(['success' => true, 'created' => $created]); exit;
            
        case 'generate_test_conversations':
            $convsPerUser = min(10, max(1, intval($_POST['convs_per_user'] ?? 2)));
            $msgsPerConv = min(50, max(1, intval($_POST['msgs_per_conv'] ?? 10)));
            $testUsers = $pdo->query("SELECT id FROM users WHERE username LIKE 'test_user_%'")->fetchAll(PDO::FETCH_COLUMN);
            $gigs = $pdo->query("SELECT id FROM gigs WHERE is_active = 1 LIMIT 10")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($testUsers) || empty($gigs)) { echo json_encode(['success' => false, 'message' => 'No test users or active companions']); exit; }
            $convCount = 0; $msgCount = 0;
            foreach ($testUsers as $userId) {
                for ($c = 0; $c < $convsPerUser; $c++) {
                    $gigId = $gigs[array_rand($gigs)];
                    $pdo->prepare("INSERT INTO conversations (user_id, gig_id, title) VALUES (?, ?, 'Test Conversation')")->execute([$userId, $gigId]);
                    $convId = $pdo->lastInsertId(); $convCount++;
                    for ($m = 0; $m < $msgsPerConv; $m++) {
                        $role = $m % 2 === 0 ? 'user' : 'assistant';
                        $content = $role === 'user' ? 'Test message from user ' . $m : 'Test response from companion ' . $m;
                        $pdo->prepare("INSERT INTO chat_messages (conversation_id, role, content) VALUES (?, ?, ?)")->execute([$convId, $role, $content]); $msgCount++;
                    }
                }
            }
            echo json_encode(['success' => true, 'conversations' => $convCount, 'messages' => $msgCount]); exit;
            
        case 'generate_test_purchases':
            $count = min(100, max(1, intval($_POST['count'] ?? 20)));
            $testUsers = $pdo->query("SELECT id FROM users WHERE username LIKE 'test_user_%'")->fetchAll(PDO::FETCH_COLUMN);
            $gigs = $pdo->query("SELECT id FROM gigs WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($testUsers) || empty($gigs)) { echo json_encode(['success' => false, 'message' => 'No test users or companions']); exit; }
            $created = 0; $types = ['voice', 'photos', 'adult'];
            for ($i = 0; $i < $count; $i++) {
                $userId = $testUsers[array_rand($testUsers)];
                $gigId = $gigs[array_rand($gigs)];
                $type = $types[array_rand($types)];
                try { $pdo->prepare("INSERT INTO companion_upgrades (user_id, gig_id, upgrade_type, price_paid, status) VALUES (?, ?, ?, ?, 'active')")->execute([$userId, $gigId, $type, rand(5, 30)]); $created++; } catch (Exception $e) {}
            }
            echo json_encode(['success' => true, 'created' => $created]); exit;
            
        case 'generate_test_api_usage':
            $count = min(500, max(1, intval($_POST['count'] ?? 100)));
            $testUsers = $pdo->query("SELECT id FROM users LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
            $apiTypes = ['openai_chat', 'openai_tts', 'stability_image', 'elevenlabs_tts', 'openrouter_chat'];
            $created = 0;
            for ($i = 0; $i < $count; $i++) {
                $userId = !empty($testUsers) ? $testUsers[array_rand($testUsers)] : null;
                $apiType = $apiTypes[array_rand($apiTypes)];
                $tokensIn = rand(100, 2000); $tokensOut = rand(50, 1500);
                $cost = ($tokensIn * 0.000003) + ($tokensOut * 0.000006);
                $daysAgo = rand(0, 30); $hoursAgo = rand(0, 23);
                $pdo->prepare("INSERT INTO api_usage_log (user_id, api_type, tokens_input, tokens_output, cost_estimate, created_at) VALUES (?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY) - INTERVAL ? HOUR)")->execute([$userId, $apiType, $tokensIn, $tokensOut, $cost, $daysAgo, $hoursAgo]);
                $created++;
            }
            echo json_encode(['success' => true, 'created' => $created]); exit;
            
        case 'generate_test_sessions':
            $count = min(200, max(1, intval($_POST['count'] ?? 50)));
            $testUsers = $pdo->query("SELECT id FROM users LIMIT 50")->fetchAll(PDO::FETCH_COLUMN);
            $gigs = $pdo->query("SELECT id FROM gigs WHERE is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($testUsers)) { echo json_encode(['success' => false, 'message' => 'No users found']); exit; }
            $created = 0;
            for ($i = 0; $i < $count; $i++) {
                $userId = $testUsers[array_rand($testUsers)];
                $gigId = !empty($gigs) ? $gigs[array_rand($gigs)] : null;
                $duration = rand(60, 3600); $messages = rand(5, 50); $daysAgo = rand(0, 30);
                $pdo->prepare("INSERT INTO user_sessions (user_id, gig_id, duration_seconds, message_count, session_start, last_activity) VALUES (?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY), DATE_SUB(NOW(), INTERVAL ? DAY))")->execute([$userId, $gigId, $duration, $messages, $daysAgo, $daysAgo]);
                $created++;
            }
            echo json_encode(['success' => true, 'created' => $created]); exit;
            
        case 'clear_test_data':
            $pdo->exec("DELETE FROM chat_messages WHERE conversation_id IN (SELECT id FROM conversations WHERE user_id IN (SELECT id FROM users WHERE username LIKE 'test_user_%'))");
            $pdo->exec("DELETE FROM conversations WHERE user_id IN (SELECT id FROM users WHERE username LIKE 'test_user_%')");
            $pdo->exec("DELETE FROM companion_upgrades WHERE user_id IN (SELECT id FROM users WHERE username LIKE 'test_user_%')");
            $pdo->exec("DELETE FROM user_sessions WHERE user_id IN (SELECT id FROM users WHERE username LIKE 'test_user_%')");
            $deleted = $pdo->exec("DELETE FROM users WHERE username LIKE 'test_user_%'");
            echo json_encode(['success' => true, 'deleted_users' => $deleted]); exit;
    }
}

// Login form
if (!isset($_SESSION['admin_logged_in'])) {
    ?><!DOCTYPE html><html><head><title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:system-ui,sans-serif;background:#0a0a0a;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center}.login{background:#111;border:1px solid #222;border-radius:16px;padding:40px;width:100%;max-width:360px}h1{text-align:center;margin-bottom:24px}input{width:100%;padding:14px;margin-bottom:16px;background:#0a0a0a;border:1px solid #333;border-radius:8px;color:#fff;font-size:16px;-webkit-appearance:none}input:focus{outline:none;border-color:#10b981}button{width:100%;padding:14px;background:#10b981;color:#000;border:none;border-radius:8px;font-size:16px;font-weight:600;cursor:pointer;-webkit-appearance:none}button:active{transform:scale(0.98)}.error{background:rgba(239,68,68,0.15);color:#ef4444;padding:12px;border-radius:8px;margin-bottom:16px;text-align:center}</style></head><body>
    <div class="login"><h1>🔐 Admin</h1><?php if(isset($loginError)): ?><div class="error"><?=$loginError?></div><?php endif; ?><form method="POST"><input type="text" name="username" placeholder="Username" required autocomplete="username"><input type="password" name="password" placeholder="Password" required autocomplete="current-password"><button type="submit" name="admin_login">Login</button></form></div></body></html><?php
    exit;
}
?>
<!DOCTYPE html>
<html><head>
<title>Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<style>
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
:root{--bg:#0a0a0a;--bg2:#111;--bg3:#1a1a1a;--border:#222;--text:#fff;--text2:#888;--accent:#10b981;--green:#22c55e;--red:#ef4444;--yellow:#eab308}
body{font-family:system-ui,-apple-system,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
.header{background:var(--bg2);border-bottom:1px solid var(--border);padding:16px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:100}
.header h1{font-size:20px}.header nav{display:flex;gap:16px}.header a{color:var(--text2);text-decoration:none;font-size:14px}.header a:hover{color:var(--accent)}
.container{max-width:1400px;margin:0 auto;padding:24px}
.tabs{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;overflow-x:auto;-webkit-overflow-scrolling:touch}
.tab{padding:12px 20px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;color:var(--text2);cursor:pointer;font-size:14px;white-space:nowrap;touch-action:manipulation}
.tab:hover,.tab:active,.tab.active{background:var(--accent);color:#000;border-color:var(--accent)}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:24px}
.stat{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:16px}
.stat-val{font-size:24px;font-weight:700}.stat-label{font-size:11px;color:var(--text2);margin-top:4px}
.stat.green .stat-val{color:var(--green)}.stat.yellow .stat-val{color:var(--yellow)}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:24px}
.card h2{font-size:16px;margin-bottom:16px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
table{width:100%;border-collapse:collapse;min-width:500px}
th,td{padding:12px;text-align:left;border-bottom:1px solid var(--border);font-size:13px}
th{color:var(--text2);font-weight:500;font-size:11px;text-transform:uppercase}
.badge{display:inline-block;padding:6px 12px;border-radius:50px;font-size:11px;font-weight:600}
.badge-green{background:rgba(34,197,94,0.15);color:var(--green)}
.badge-red{background:rgba(239,68,68,0.15);color:var(--red)}
.badge-yellow{background:rgba(234,179,8,0.15);color:var(--yellow)}
.badge-blue{background:rgba(59,130,246,0.15);color:#3b82f6}
.btn{padding:12px 20px;border-radius:8px;border:none;font-size:13px;cursor:pointer;font-weight:600;touch-action:manipulation}
.btn:active{transform:scale(0.97)}
.btn-p{background:var(--accent);color:#000}.btn-s{background:var(--bg3);color:var(--text);border:1px solid var(--border)}.btn-d{background:var(--red);color:#fff}.btn-sm{padding:8px 14px;font-size:12px}
input,select,textarea{background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:14px;color:var(--text);font-size:16px;width:100%;-webkit-appearance:none}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--accent)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.form-group{margin-bottom:14px}.form-group label{display:block;margin-bottom:6px;font-size:12px;color:var(--text2)}
.section{display:none}.section.active{display:block}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:1000;align-items:center;justify-content:center;padding:20px;overflow-y:auto}
.modal.active{display:flex}
.modal-content{background:var(--bg2);border:1px solid var(--border);border-radius:16px;width:100%;max-width:700px;max-height:90vh;overflow-y:auto}
.modal-header{padding:20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
.modal-close{background:none;border:none;color:var(--text2);font-size:28px;cursor:pointer}
.modal-body{padding:20px}.modal-footer{padding:20px;border-top:1px solid var(--border);display:flex;gap:12px;justify-content:flex-end}
.thumb{width:50px;height:50px;border-radius:8px;object-fit:cover;background:var(--bg3)}
input[type="range"]{-webkit-appearance:none;height:44px;background:transparent}
input[type="range"]::-webkit-slider-runnable-track{height:8px;background:var(--bg3);border-radius:4px}
input[type="range"]::-webkit-slider-thumb{-webkit-appearance:none;width:28px;height:28px;background:var(--accent);border-radius:50%;margin-top:-10px;cursor:pointer}
.gc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.gc-card{background:var(--bg);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.gc-card img{width:100%;height:180px;object-fit:cover;background:var(--bg3)}
.gc-card-body{padding:16px}.gc-card h4{margin-bottom:8px}.gc-card p{font-size:12px;color:var(--text2);margin-bottom:12px}
</style>
</head><body>
<div class="header"><h1>⚙️ Admin Panel</h1><nav><a href="app.php">← Back</a><a href="custom-companion.php">🎨 Designer</a><a href="?logout">Logout</a></nav></div>
<div class="container">
<div class="tabs">
<div class="tab active" onclick="showSection('dashboard')">Dashboard</div>
<div class="tab" onclick="showSection('api')">API Usage</div>
<div class="tab" onclick="showSection('users')">Users</div>
<div class="tab" onclick="showSection('companions')">Companions</div>
<div class="tab" onclick="showSection('generator')">🎭 Generator</div>
<div class="tab" onclick="showSection('custom')">Custom Designs</div>
<div class="tab" onclick="showSection('conversations')">Conversations</div>
<div class="tab" onclick="showSection('upgrades')">Upgrades</div>
<div class="tab" onclick="showSection('timespent')">Time Spent</div>
<div class="tab" onclick="showSection('limits')">Rate Limits</div>
<div class="tab" onclick="showSection('settings')">Settings</div>
<div class="tab" onclick="showSection('performance')">Performance</div>
</div>

<div class="section active" id="sec-dashboard"><div class="stats" id="statsGrid"></div></div>

<div class="section" id="sec-api">
<div class="card"><h2>💸 API Costs</h2>
<div class="form-row" style="margin-bottom:16px"><select id="apiPeriod" onchange="loadApiUsage()"><option value="day">Last 24 Hours</option><option value="week">Last 7 Days</option><option value="month">Last 30 Days</option></select><div style="text-align:right;font-size:24px;font-weight:700;color:var(--green)" id="totalApiCost">$0.00</div></div>
<div class="table-wrap"><table><thead><tr><th>API</th><th>Calls</th><th>Input</th><th>Output</th><th>Cost</th></tr></thead><tbody id="apiTable"></tbody></table></div></div>
<div class="card"><h2>👤 Top Users (30d)</h2><div class="table-wrap"><table><thead><tr><th>User</th><th>Email</th><th>Calls</th><th>Cost</th></tr></thead><tbody id="userApiTable"></tbody></table></div></div>
</div>

<div class="section" id="sec-users"><div class="card"><h2>👥 Users</h2><input type="text" id="userSearch" placeholder="Search..." style="margin-bottom:16px" onkeyup="loadUsers()"><div class="table-wrap"><table><thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Convos</th><th>Upgrades</th><th>Joined</th><th>Actions</th></tr></thead><tbody id="usersTable"></tbody></table></div></div></div>

<div class="section" id="sec-companions"><div class="card"><h2>💕 Companions <a href="custom-companion.php" class="btn btn-p btn-sm" style="margin-left:auto">+ Create</a></h2><div class="table-wrap"><table><thead><tr><th>Image</th><th>Name</th><th>Type</th><th>Convos</th><th>Price</th><th>Source</th><th>Status</th><th>Actions</th></tr></thead><tbody id="companionsTable"></tbody></table></div></div></div>

<div class="section" id="sec-generator"><div class="card"><h2>🎭 Companion Generator</h2>
<p style="color:var(--text2);font-size:13px;margin-bottom:20px">Create new AI companions directly or in batch. For AI-generated images, use the <a href="custom-companion.php" style="color:var(--accent)">Custom Designer</a>.</p>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:20px">
<div style="background:var(--bg);border-radius:12px;padding:20px">
<h3 style="font-size:14px;margin-bottom:16px">Create Single Companion</h3>
<div class="form-group"><label>Name</label><input type="text" id="gen_name" placeholder="Luna"></div>
<div class="form-row">
<div class="form-group"><label>Type</label><select id="gen_type"><option value="girlfriend">Girlfriend</option><option value="boyfriend">Boyfriend</option><option value="non-binary">Non-Binary</option></select></div>
<div class="form-group"><label>Category</label><input type="text" id="gen_category" placeholder="Casual, Romantic, etc."></div>
</div>
<div class="form-group"><label>Description/Backstory</label><textarea id="gen_desc" rows="3" placeholder="A playful companion who loves adventure..."></textarea></div>
<div class="form-group"><label>AI Persona (JSON or text)</label><textarea id="gen_persona" rows="3" placeholder='{"personality": "playful, caring", "speaking_style": "casual"}'></textarea></div>
<div class="form-group"><label>Image URL</label><input type="text" id="gen_image" placeholder="https://..."></div>
<div class="form-row">
<div class="form-group"><label>Price/Hour ($)</label><input type="number" id="gen_price" value="25"></div>
<div class="form-group"><label>Monthly ($)</label><input type="number" id="gen_monthly" value="99"></div>
</div>
<div class="form-group"><label style="display:flex;align-items:center;gap:8px"><input type="checkbox" id="gen_adult" style="width:20px;height:20px"> Adult Mode Enabled</label></div>
<button class="btn btn-p" onclick="createSingleCompanion()">✨ Create Companion</button>
</div>
<div style="background:var(--bg);border-radius:12px;padding:20px">
<h3 style="font-size:14px;margin-bottom:16px">Batch Create (JSON)</h3>
<p style="font-size:12px;color:var(--text2);margin-bottom:12px">Paste JSON array of companions to create multiple at once.</p>
<textarea id="batch_json" rows="12" placeholder='[
  {
    "name": "Luna",
    "gender": "female",
    "description": "Playful and adventurous",
    "personality": "caring, witty",
    "category": "Casual",
    "image_url": "",
    "price_per_hour": 25,
    "adult": 0
  },
  {
    "name": "Marcus",
    "gender": "male",
    "description": "Confident and charming",
    "personality": "romantic, supportive",
    "category": "Romance",
    "price_per_hour": 30
  }
]'></textarea>
<button class="btn btn-p" onclick="batchCreateCompanions()" style="margin-top:12px">🚀 Create All</button>
<div id="batchResult" style="margin-top:12px;font-size:13px;color:var(--text2)"></div>
</div>
</div></div></div>

<div class="section" id="sec-custom"><div class="card"><h2>🎨 Custom Designs <a href="custom-companion.php" class="btn btn-p btn-sm" style="margin-left:auto">+ Create</a></h2><p style="color:var(--text2);font-size:13px;margin-bottom:20px">Activate custom companions to make them live on the site.</p><div class="gc-grid" id="customCompanionsGrid"></div></div></div>

<div class="section" id="sec-conversations"><div class="card"><h2>💬 Conversations</h2><div class="table-wrap"><table><thead><tr><th>User</th><th>Companion</th><th>Messages</th><th>Last Activity</th><th>Actions</th></tr></thead><tbody id="convsTable"></tbody></table></div></div></div>

<div class="section" id="sec-upgrades"><div class="card"><h2>🎁 Upgrades</h2><div class="table-wrap"><table><thead><tr><th>User</th><th>Companion</th><th>Type</th><th>Price</th><th>Status</th><th>Date</th></tr></thead><tbody id="upgradesTable"></tbody></table></div></div></div>

<div class="section" id="sec-timespent"><div class="card"><h2>⏱️ Time Spent</h2><div class="table-wrap"><table><thead><tr><th>User</th><th>Email</th><th>Minutes</th><th>Messages</th><th>Companions</th><th>Last Active</th></tr></thead><tbody id="timeSpentTable"></tbody></table></div></div>
<div class="card"><h2>💰 Time Purchases</h2><div class="table-wrap"><table><thead><tr><th>User</th><th>Companion</th><th>Purchased</th><th>Remaining</th><th>Status</th></tr></thead><tbody id="timePurchasesTable"></tbody></table></div></div></div>

<div class="section" id="sec-limits"><div class="card"><h2>⚡ Rate Limits</h2><div class="table-wrap"><table><thead><tr><th>Setting</th><th>Value</th><th>Description</th><th>Action</th></tr></thead><tbody id="limitsTable"></tbody></table></div></div></div>

<div class="section" id="sec-settings"><div class="card"><h2>⚙️ Settings</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
<div style="background:var(--bg);border-radius:12px;padding:20px"><h3 style="font-size:14px;margin-bottom:16px">Typing Simulation</h3>
<div class="form-group"><label>Min Delay: <span id="typing_min_val">1500</span>ms</label><input type="range" id="setting_typing_min" min="500" max="5000" step="100" value="1500" oninput="document.getElementById('typing_min_val').textContent=this.value"></div>
<div class="form-group"><label>Max Delay: <span id="typing_max_val">3500</span>ms</label><input type="range" id="setting_typing_max" min="1000" max="8000" step="100" value="3500" oninput="document.getElementById('typing_max_val').textContent=this.value"></div></div>
<div style="background:var(--bg);border-radius:12px;padding:20px"><h3 style="font-size:14px;margin-bottom:16px">AI Models</h3>
<div class="form-group"><label>Primary Model</label><select id="setting_primary_model"><option value="gpt-4o-mini">GPT-4o Mini</option><option value="gpt-4o">GPT-4o</option></select></div>
<div class="form-group"><label>Adult Model</label><select id="setting_adult_model"><option value="openrouter">OpenRouter</option><option value="gpt-4o">GPT-4o</option></select></div></div>
</div>
<div style="margin-top:20px"><button class="btn btn-p" onclick="saveSettings()">💾 Save Settings</button></div></div></div>

<div class="section" id="sec-performance"><div class="card"><h2>🚀 Performance</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;margin-bottom:24px" id="perfStats"></div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
<div style="background:var(--bg);border-radius:12px;padding:20px"><h3 style="font-size:14px;margin-bottom:16px">Generate Test Data</h3>
<div class="form-group"><label>Users</label><input type="number" id="perf_users" value="10" min="1" max="100"></div><button class="btn btn-p btn-sm" onclick="generateTestUsers()" style="margin-bottom:12px">Generate Users</button>
<div class="form-group"><label>API Records</label><input type="number" id="perf_api" value="100" min="1" max="500"></div><button class="btn btn-p btn-sm" onclick="generateTestApiUsage()" style="margin-bottom:12px">Generate API Usage</button>
<div class="form-group"><label>Sessions</label><input type="number" id="perf_sessions" value="50" min="1" max="200"></div><button class="btn btn-p btn-sm" onclick="generateTestSessions()">Generate Sessions</button></div>
<div style="background:var(--bg);border-radius:12px;padding:20px"><h3 style="font-size:14px;margin-bottom:16px">Cleanup</h3><p style="font-size:12px;color:var(--text2);margin-bottom:16px">Remove all test data</p><button class="btn btn-d" onclick="clearTestData()">🗑️ Clear Test Data</button></div>
</div>
<div style="margin-top:20px;background:var(--bg);border-radius:12px;padding:20px"><h3 style="font-size:14px;margin-bottom:12px">Results</h3><pre id="perfResults" style="font-family:monospace;font-size:12px;color:var(--text2)">Ready</pre></div></div></div>
</div>

<!-- Modals -->
<div class="modal" id="userModal"><div class="modal-content"><div class="modal-header"><h3>Edit User</h3><button class="modal-close" onclick="closeModal('userModal')">&times;</button></div><div class="modal-body" id="userModalBody"></div><div class="modal-footer"><button class="btn btn-d" onclick="deleteUser()">Delete</button><button class="btn btn-s" onclick="closeModal('userModal')">Cancel</button><button class="btn btn-p" onclick="saveUser()">Save</button></div></div></div>
<div class="modal" id="gigModal"><div class="modal-content"><div class="modal-header"><h3>Edit Companion</h3><button class="modal-close" onclick="closeModal('gigModal')">&times;</button></div><div class="modal-body" id="gigModalBody"></div><div class="modal-footer"><button class="btn btn-d" onclick="deleteGig()">Delete</button><button class="btn btn-s" onclick="closeModal('gigModal')">Cancel</button><button class="btn btn-p" onclick="saveGig()">Save</button></div></div></div>
<div class="modal" id="convModal"><div class="modal-content"><div class="modal-header"><h3>Conversation</h3><button class="modal-close" onclick="closeModal('convModal')">&times;</button></div><div class="modal-body" id="convModalBody"></div><div class="modal-footer"><button class="btn btn-d" onclick="deleteConv()">Delete</button><button class="btn btn-s" onclick="closeModal('convModal')">Close</button></div></div></div>
<div class="modal" id="activateModal"><div class="modal-content"><div class="modal-header"><h3>Activate Companion</h3><button class="modal-close" onclick="closeModal('activateModal')">&times;</button></div><div class="modal-body"><p style="margin-bottom:16px">Set pricing:</p><div class="form-row"><div class="form-group"><label>Price/Hour ($)</label><input type="number" id="activate_price_hour" value="25"></div><div class="form-group"><label>Monthly ($)</label><input type="number" id="activate_price_month" value="99"></div></div></div><div class="modal-footer"><button class="btn btn-s" onclick="closeModal('activateModal')">Cancel</button><button class="btn btn-p" onclick="confirmActivateCompanion()">Activate</button></div></div></div>

<script>
let currentUserId, currentGigId, currentConvId, currentGcId;
async function api(action, data = {}) { const fd = new FormData(); fd.append('action', action); for (const [k, v] of Object.entries(data)) fd.append(k, v); const r = await fetch('admin.php', { method: 'POST', body: fd }); return r.json(); }
function showSection(id) { document.querySelectorAll('.section').forEach(s => s.classList.remove('active')); document.querySelectorAll('.tab').forEach(t => t.classList.remove('active')); document.getElementById('sec-' + id).classList.add('active'); event.target.classList.add('active'); const loaders = {'dashboard':loadStats,'api':loadApiUsage,'users':loadUsers,'companions':loadCompanions,'custom':loadCustomCompanions,'conversations':loadConvs,'upgrades':loadUpgrades,'timespent':loadTimeSpent,'limits':loadLimits,'settings':loadSettings,'performance':loadPerfMetrics}; if(loaders[id]) loaders[id](); }
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

async function loadStats() { const r = await api('get_stats'); if(r.success){ const s=r.stats; document.getElementById('statsGrid').innerHTML=`<div class="stat"><div class="stat-val">${s.users}</div><div class="stat-label">Users</div></div><div class="stat"><div class="stat-val">${s.companions}</div><div class="stat-label">Companions</div></div><div class="stat"><div class="stat-val">${s.custom_companions||0}</div><div class="stat-label">Custom Designs</div></div><div class="stat"><div class="stat-val">${s.conversations}</div><div class="stat-label">Conversations</div></div><div class="stat"><div class="stat-val">${s.messages_today}</div><div class="stat-label">Messages (24h)</div></div><div class="stat green"><div class="stat-val">$${parseFloat(s.revenue_month||0).toFixed(2)}</div><div class="stat-label">Revenue (30d)</div></div><div class="stat yellow"><div class="stat-val">$${parseFloat(s.api_cost_today||0).toFixed(4)}</div><div class="stat-label">API Cost (24h)</div></div><div class="stat"><div class="stat-val">${Math.round(s.time_spent_today||0)}</div><div class="stat-label">Minutes (24h)</div></div>`; }}

async function loadApiUsage() { const period = document.getElementById('apiPeriod')?.value || 'day'; const r = await api('get_api_usage', {period}); if(r.success){ document.getElementById('totalApiCost').textContent = '$' + (r.total_cost||0).toFixed(4); document.getElementById('apiTable').innerHTML = r.by_type.map(t=>`<tr><td><strong>${t.api_type}</strong></td><td>${t.calls}</td><td>${(t.total_input||0).toLocaleString()}</td><td>${(t.total_output||0).toLocaleString()}</td><td style="color:var(--green)">$${parseFloat(t.total_cost||0).toFixed(4)}</td></tr>`).join('')||'<tr><td colspan="5" style="text-align:center;color:var(--text2)">No API usage recorded</td></tr>'; } const u = await api('get_user_api_usage'); if(u.success){ document.getElementById('userApiTable').innerHTML = u.users.filter(x=>x.api_calls>0).map(x=>`<tr><td>${x.username}</td><td>${x.email}</td><td>${x.api_calls}</td><td style="color:var(--green)">$${parseFloat(x.total_cost||0).toFixed(4)}</td></tr>`).join('')||'<tr><td colspan="4" style="text-align:center;color:var(--text2)">No user API usage</td></tr>'; }}

async function loadUsers() { const search = document.getElementById('userSearch')?.value||''; const r = await api('get_users',{search}); if(r.success){ document.getElementById('usersTable').innerHTML = r.users.map(u=>`<tr><td>${u.id}</td><td>${u.username}</td><td>${u.email}</td><td>${u.conv_count}</td><td>${u.upgrade_count}</td><td>${new Date(u.created_at).toLocaleDateString()}</td><td><button class="btn btn-p btn-sm" onclick="editUser(${u.id})">Edit</button></td></tr>`).join(''); }}
async function editUser(id) { currentUserId=id; const r = await api('get_user',{user_id:id}); if(r.success){ const u=r.user; document.getElementById('userModalBody').innerHTML=`<div class="form-row"><div class="form-group"><label>Username</label><input type="text" id="editUsername" value="${u.username||''}"></div><div class="form-group"><label>Email</label><input type="email" id="editEmail" value="${u.email||''}"></div></div><div class="form-group"><label>Display Name</label><input type="text" id="editDisplayName" value="${u.display_name||''}"></div><div class="form-group"><label>Bio</label><textarea id="editBio" rows="3">${u.bio||''}</textarea></div>`; openModal('userModal'); }}
async function saveUser() { await api('update_user',{user_id:currentUserId,username:document.getElementById('editUsername').value,email:document.getElementById('editEmail').value,display_name:document.getElementById('editDisplayName').value,bio:document.getElementById('editBio').value}); closeModal('userModal'); loadUsers(); }
async function deleteUser() { if(confirm('Delete this user?')){ await api('delete_user',{user_id:currentUserId}); closeModal('userModal'); loadUsers(); }}

async function loadCompanions() { const r = await api('get_companions'); if(r.success){ document.getElementById('companionsTable').innerHTML = r.companions.map(c=>`<tr><td><img src="${c.image_url||''}" class="thumb" onerror="this.style.background='var(--bg3)'"></td><td><strong>${c.title||'Unnamed'}</strong></td><td>${c.companion_type}</td><td>${c.conv_count}</td><td>$${c.price_per_hour}/hr</td><td><span class="badge ${c.source_type==='custom_designer'?'badge-blue':'badge-yellow'}">${c.source_type==='custom_designer'?'Custom':'Manual'}</span></td><td><span class="badge ${c.is_active?'badge-green':'badge-red'}">${c.is_active?'Active':'Inactive'}</span></td><td><button class="btn btn-p btn-sm" onclick="editGig(${c.id})">Edit</button></td></tr>`).join(''); }}
async function editGig(id) { currentGigId=id; const r = await api('get_gig',{gig_id:id}); if(r.success){ const g=r.gig; document.getElementById('gigModalBody').innerHTML=`<div class="form-group"><label>Title</label><input type="text" id="editTitle" value="${g.title||''}"></div><div class="form-row"><div class="form-group"><label>Type</label><select id="editType"><option value="girlfriend" ${g.companion_type==='girlfriend'?'selected':''}>Girlfriend</option><option value="boyfriend" ${g.companion_type==='boyfriend'?'selected':''}>Boyfriend</option></select></div><div class="form-group"><label>Price/Hour</label><input type="number" id="editPrice" value="${g.price_per_hour||25}"></div></div><div class="form-group"><label>Description</label><textarea id="editDesc" rows="3">${g.description||''}</textarea></div><div class="form-group"><label>AI Persona</label><textarea id="editPersona" rows="4">${g.ai_persona||''}</textarea></div><div class="form-group"><label>Image URL</label><input type="text" id="editImageUrl" value="${g.image_url||''}"></div><div class="form-row"><div class="form-group"><label>Active</label><select id="editActive"><option value="1" ${g.is_active?'selected':''}>Yes</option><option value="0" ${g.is_active?'':'selected'}>No</option></select></div><div class="form-group"><label>Adult Mode</label><select id="editAdult"><option value="0" ${g.adult_personality_enabled?'':'selected'}>No</option><option value="1" ${g.adult_personality_enabled?'selected':''}>Yes</option></select></div></div>`; openModal('gigModal'); }}
async function saveGig() { await api('update_gig',{gig_id:currentGigId,title:document.getElementById('editTitle').value,description:document.getElementById('editDesc').value,companion_type:document.getElementById('editType').value,price_per_hour:document.getElementById('editPrice').value,ai_persona:document.getElementById('editPersona').value,is_active:document.getElementById('editActive').value,adult_personality_enabled:document.getElementById('editAdult').value,image_url:document.getElementById('editImageUrl').value,category:'',monthly_price:0,base_appearance:'',ai_voice_id:'',voice_provider:'openai'}); closeModal('gigModal'); loadCompanions(); }
async function deleteGig() { if(confirm('Delete companion?')){ await api('delete_gig',{gig_id:currentGigId}); closeModal('gigModal'); loadCompanions(); }}

async function loadCustomCompanions() { const r = await api('get_custom_companions'); if(r.success){ if(!r.companions.length){ document.getElementById('customCompanionsGrid').innerHTML=`<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text2)"><p style="font-size:48px;margin-bottom:16px">🎨</p><p>No custom companions yet</p><a href="custom-companion.php" class="btn btn-p" style="margin-top:16px">Create Your First</a></div>`; return; } document.getElementById('customCompanionsGrid').innerHTML = r.companions.map(c=>{ const config=JSON.parse(c.full_config||'{}'); return `<div class="gc-card"><img src="${c.image_path||''}" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect fill=%22%231a1a1a%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2250%22 font-size=%2240%22 text-anchor=%22middle%22 dy=%22.3em%22>🎨</text></svg>'"><div class="gc-card-body"><h4>${c.name||'Unnamed'}</h4><p><span class="badge ${c.is_adult?'badge-red':'badge-green'}">${c.is_adult?'Adult':'SFW'}</span> <span class="badge badge-yellow">${c.model_used||'sdxl'}</span>${c.is_activated?' <span class="badge badge-blue">Active</span>':''}</p><p style="margin-top:8px">${(config.personality||'No description').substring(0,60)}...</p>${c.is_activated?`<button class="btn btn-s" style="width:100%;margin-top:12px" disabled>Already Active</button>`:`<button class="btn btn-p" style="width:100%;margin-top:12px" onclick="activateCompanion(${c.id})">✓ Activate</button>`}<button class="btn btn-d btn-sm" style="width:100%;margin-top:8px" onclick="deleteCustomCompanion(${c.id})">Delete</button></div></div>`; }).join(''); }}
function activateCompanion(id) { currentGcId=id; openModal('activateModal'); }
async function confirmActivateCompanion() { const r = await api('activate_custom_companion',{gc_id:currentGcId,price_per_hour:document.getElementById('activate_price_hour').value,monthly_price:document.getElementById('activate_price_month').value}); if(r.success){ alert('Companion activated!'); closeModal('activateModal'); loadCustomCompanions(); loadCompanions(); }else{ alert('Error: '+(r.message||'Failed')); }}
async function deleteCustomCompanion(id) { if(confirm('Delete this custom companion?')){ await api('delete_custom_companion',{gc_id:id}); loadCustomCompanions(); }}

async function loadConvs() { const r = await api('get_conversations'); if(r.success){ document.getElementById('convsTable').innerHTML = r.conversations.map(c=>`<tr><td>${c.username}</td><td>${c.companion_name}</td><td>${c.message_count}</td><td>${new Date(c.last_message_at).toLocaleString()}</td><td><button class="btn btn-p btn-sm" onclick="viewConv(${c.id})">View</button></td></tr>`).join(''); }}
async function viewConv(id) { currentConvId=id; const r = await api('get_conversation',{conversation_id:id}); if(r.success){ document.getElementById('convModalBody').innerHTML=`<div style="margin-bottom:16px"><strong>${r.conversation.username}</strong> ↔ <strong>${r.conversation.companion_name}</strong></div><div style="max-height:400px;overflow-y:auto">${r.messages.map(m=>`<div style="padding:10px;margin-bottom:8px;border-radius:8px;background:${m.role==='user'?'var(--accent)':'var(--bg3)'};color:${m.role==='user'?'#000':'var(--text)'};${m.role==='user'?'margin-left:20%':'margin-right:20%'}">${m.content}<div style="font-size:10px;opacity:0.7;margin-top:4px">${new Date(m.created_at).toLocaleString()}</div></div>`).join('')}</div>`; openModal('convModal'); }}
async function deleteConv() { if(confirm('Delete?')){ await api('delete_conversation',{conversation_id:currentConvId}); closeModal('convModal'); loadConvs(); }}

async function loadUpgrades() { const r = await api('get_upgrades'); if(r.success){ document.getElementById('upgradesTable').innerHTML = r.upgrades.map(u=>`<tr><td>${u.username}</td><td>${u.companion_name}</td><td>${u.upgrade_type}</td><td>$${u.price_paid}</td><td><span class="badge ${u.status==='active'?'badge-green':'badge-yellow'}">${u.status}</span></td><td>${new Date(u.purchased_at).toLocaleDateString()}</td></tr>`).join(''); }}

async function loadTimeSpent() { const r = await api('get_time_spent'); if(r.success){ document.getElementById('timeSpentTable').innerHTML = r.users.map(u=>`<tr><td>${u.display_name||u.username}</td><td>${u.email}</td><td>${Math.round(u.total_minutes||0)}</td><td>${u.total_messages||0}</td><td>${u.active_companions||0}</td><td>${u.last_active?new Date(u.last_active).toLocaleDateString():'Never'}</td></tr>`).join('')||'<tr><td colspan="6" style="text-align:center;color:var(--text2)">No data</td></tr>'; } const p = await api('get_time_purchases'); if(p.success){ document.getElementById('timePurchasesTable').innerHTML = p.purchases.map(t=>`<tr><td>${t.username}</td><td>${t.companion_name}</td><td>${t.minutes_purchased}</td><td>${t.minutes_remaining}</td><td><span class="badge ${t.status==='active'?'badge-green':'badge-yellow'}">${t.status}</span></td></tr>`).join('')||'<tr><td colspan="5" style="text-align:center;color:var(--text2)">No purchases</td></tr>'; }}

async function loadLimits() { const r = await api('get_rate_limits'); if(r.success){ document.getElementById('limitsTable').innerHTML = r.limits.map(l=>`<tr><td><strong>${l.setting_key}</strong></td><td><input type="number" value="${l.setting_value}" id="limit_${l.setting_key}" style="width:100px"></td><td>${l.description}</td><td><button class="btn btn-p btn-sm" onclick="updateLimit('${l.setting_key}')">Save</button></td></tr>`).join(''); }}
async function updateLimit(key) { await api('update_rate_limit',{key,value:document.getElementById('limit_'+key).value}); alert('Updated!'); }

async function loadSettings() { const r = await api('get_settings'); if(r.success){ const s=r.settings; document.getElementById('setting_typing_min').value=s.typing_min; document.getElementById('typing_min_val').textContent=s.typing_min; document.getElementById('setting_typing_max').value=s.typing_max; document.getElementById('typing_max_val').textContent=s.typing_max; document.getElementById('setting_primary_model').value=s.primary_model; document.getElementById('setting_adult_model').value=s.adult_model; }}
async function saveSettings() { const r = await api('save_settings',{typing_min:document.getElementById('setting_typing_min').value,typing_max:document.getElementById('setting_typing_max').value,primary_model:document.getElementById('setting_primary_model').value,adult_model:document.getElementById('setting_adult_model').value}); alert(r.success?'Saved!':'Error'); }

async function loadPerfMetrics() { const r = await api('get_perf_metrics'); if(r.success){ const m=r.metrics; document.getElementById('perfStats').innerHTML=`<div class="stat"><div class="stat-val">${m.total_users}</div><div class="stat-label">Users</div></div><div class="stat"><div class="stat-val">${m.total_conversations}</div><div class="stat-label">Convos</div></div><div class="stat"><div class="stat-val">${m.total_messages}</div><div class="stat-label">Messages</div></div><div class="stat"><div class="stat-val">${m.total_api_calls}</div><div class="stat-label">API Calls</div></div><div class="stat"><div class="stat-val">${m.total_sessions}</div><div class="stat-label">Sessions</div></div><div class="stat"><div class="stat-val">${m.db_size_mb.toFixed(2)}MB</div><div class="stat-label">DB Size</div></div>`; }}
async function generateTestUsers() { const count=document.getElementById('perf_users').value; document.getElementById('perfResults').textContent='Generating...'; const r = await api('generate_test_users',{count}); document.getElementById('perfResults').textContent=r.success?`✓ Created ${r.created} users`:`✗ Error`; loadPerfMetrics(); }
async function generateTestApiUsage() { const count=document.getElementById('perf_api').value; document.getElementById('perfResults').textContent='Generating...'; const r = await api('generate_test_api_usage',{count}); document.getElementById('perfResults').textContent=r.success?`✓ Created ${r.created} API records`:`✗ Error`; loadPerfMetrics(); }
async function generateTestSessions() { const count=document.getElementById('perf_sessions').value; document.getElementById('perfResults').textContent='Generating...'; const r = await api('generate_test_sessions',{count}); document.getElementById('perfResults').textContent=r.success?`✓ Created ${r.created} sessions`:`✗ Error`; loadPerfMetrics(); }
async function clearTestData() { if(!confirm('Clear all test data?'))return; document.getElementById('perfResults').textContent='Clearing...'; const r = await api('clear_test_data'); document.getElementById('perfResults').textContent=r.success?`✓ Cleared ${r.deleted_users} users`:`✗ Error`; loadPerfMetrics(); }

// Companion Generator Functions
async function createSingleCompanion() {
    const name = document.getElementById('gen_name').value;
    if (!name) { alert('Please enter a name'); return; }
    
    const r = await api('create_companion', {
        title: name,
        companion_type: document.getElementById('gen_type').value,
        category: document.getElementById('gen_category').value || 'Casual',
        description: document.getElementById('gen_desc').value || '',
        ai_persona: document.getElementById('gen_persona').value || '',
        image_url: document.getElementById('gen_image').value || '',
        price_per_hour: document.getElementById('gen_price').value || 25,
        monthly_price: document.getElementById('gen_monthly').value || 99,
        adult_enabled: document.getElementById('gen_adult').checked ? 1 : 0
    });
    
    if (r.success) {
        alert('Companion created! ID: ' + r.gig_id);
        document.getElementById('gen_name').value = '';
        document.getElementById('gen_desc').value = '';
        document.getElementById('gen_persona').value = '';
        document.getElementById('gen_image').value = '';
        loadCompanions();
    } else {
        alert('Error: ' + (r.message || 'Failed to create'));
    }
}

async function batchCreateCompanions() {
    const jsonText = document.getElementById('batch_json').value.trim();
    if (!jsonText) { alert('Please paste JSON data'); return; }
    
    let companions;
    try {
        companions = JSON.parse(jsonText);
        if (!Array.isArray(companions)) throw new Error('Must be an array');
    } catch (e) {
        alert('Invalid JSON: ' + e.message);
        return;
    }
    
    document.getElementById('batchResult').textContent = 'Creating ' + companions.length + ' companions...';
    
    const r = await api('batch_create_companions', { companions: JSON.stringify(companions) });
    
    if (r.success) {
        let msg = '✓ Created ' + r.created + ' companions';
        if (r.errors && r.errors.length > 0) {
            msg += '\n⚠ Errors: ' + r.errors.join(', ');
        }
        document.getElementById('batchResult').textContent = msg;
        loadCompanions();
    } else {
        document.getElementById('batchResult').textContent = '✗ Error: ' + (r.message || 'Failed');
    }
}

loadStats();
</script>
</body></html>