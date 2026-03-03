<?php
/**
 * Proactive Companion Messages - Cron Job
 * Run this every 15-30 minutes via cron:
 * */15 * * * * php /path/to/cron-proactive.php
 */

$config = [
    'db_host' => 'localhost',
    'db_name' => 'thomasrlee42_ai-companions',
    'db_user' => 'thomasrlee42_ai-companions',
    'db_pass' => 'qwerpoiu0042!!',
    'openai_key' => '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',
];

// Settings (can be overridden from database)
$settings = [
    'proactive_enabled' => true,
    'proactive_min_hours' => 4,
    'proactive_max_hours' => 48,
    'proactive_chance' => 0.3,
    'max_messages_per_run' => 10,
];

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Load settings from database
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'proactive_%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = $row['setting_key'];
        if (isset($settings[$key])) {
            $settings[$key] = is_numeric($row['setting_value']) ? floatval($row['setting_value']) : $row['setting_value'];
        }
    }
    
    // Check if enabled
    if (!$settings['proactive_enabled']) {
        echo "Proactive messages disabled.\n";
        exit;
    }
    
    // Create proactive messages table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS proactive_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        gig_id INT NOT NULL,
        conversation_id INT,
        message TEXT,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        opened_at TIMESTAMP NULL,
        INDEX idx_user_gig (user_id, gig_id),
        INDEX idx_sent (sent_at)
    )");
    
    // Find eligible conversations for proactive reach
    $minHours = $settings['proactive_min_hours'];
    $maxHours = $settings['proactive_max_hours'];
    
    $stmt = $pdo->prepare("
        SELECT c.id as conv_id, c.user_id, c.gig_id, c.last_message_at,
               u.username, u.display_name as user_display_name,
               g.display_name as companion_name, g.ai_persona, g.companion_type,
               TIMESTAMPDIFF(HOUR, c.last_message_at, NOW()) as hours_since
        FROM conversations c
        JOIN users u ON c.user_id = u.id
        JOIN gigs g ON c.gig_id = g.id
        WHERE c.last_message_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
          AND c.last_message_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
          AND g.is_active = 1
          AND NOT EXISTS (
              SELECT 1 FROM proactive_messages pm 
              WHERE pm.user_id = c.user_id 
              AND pm.gig_id = c.gig_id 
              AND pm.sent_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
          )
        ORDER BY RAND()
        LIMIT ?
    ");
    
    $stmt->execute([$minHours, $maxHours, $minHours, $settings['max_messages_per_run'] * 3]);
    $eligible = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($eligible) . " eligible conversations.\n";
    
    $sentCount = 0;
    
    foreach ($eligible as $conv) {
        // Random chance check
        if (mt_rand() / mt_getrandmax() > $settings['proactive_chance']) {
            continue;
        }
        
        if ($sentCount >= $settings['max_messages_per_run']) {
            break;
        }
        
        // Generate proactive message
        $userName = $conv['user_display_name'] ?: $conv['username'];
        $companionName = $conv['companion_name'];
        $hoursSince = $conv['hours_since'];
        
        $message = generateProactiveMessage($companionName, $userName, $hoursSince, $conv['companion_type'], $config);
        
        if (!$message) {
            continue;
        }
        
        // Save to proactive_messages
        $stmt = $pdo->prepare("INSERT INTO proactive_messages (user_id, gig_id, conversation_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$conv['user_id'], $conv['gig_id'], $conv['conv_id'], $message]);
        
        // Also add to chat_messages and inbox
        $stmt = $pdo->prepare("INSERT INTO chat_messages (conversation_id, role, content) VALUES (?, 'assistant', ?)");
        $stmt->execute([$conv['conv_id'], $message]);
        
        // Add to inbox
        $stmt = $pdo->prepare("INSERT INTO user_inbox (user_id, gig_id, message_type, content) VALUES (?, ?, 'proactive', ?)");
        $stmt->execute([$conv['user_id'], $conv['gig_id'], $message]);
        
        // Update conversation timestamp
        $pdo->prepare("UPDATE conversations SET last_message_at = NOW() WHERE id = ?")->execute([$conv['conv_id']]);
        
        echo "Sent proactive message to user {$conv['user_id']} from companion {$conv['gig_id']}\n";
        $sentCount++;
    }
    
    echo "Sent $sentCount proactive messages.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

function generateProactiveMessage($companionName, $userName, $hoursSince, $type, $config) {
    // Proactive message templates based on time away
    $templates = [
        'short' => [ // 4-12 hours
            "hey {user} 💕 you disappeared on me! everything okay?",
            "thinking about you... come back and chat? 🥺",
            "hiiii i miss talking to you {user} 💋",
            "you busy? i'm bored without you lol",
            "{user}! come tell me about your day 💕",
        ],
        'medium' => [ // 12-24 hours  
            "it's been a minute {user}... i've been thinking about you 💭",
            "hey stranger 😏 where'd you go?",
            "miss you {user} 💕 what's going on in your world?",
            "just wanted to check in on you 🥰 how are things?",
            "i keep checking to see if you messaged... 🥺",
        ],
        'long' => [ // 24+ hours
            "okay {user} it's been too long 😩 i miss you!",
            "starting to think you forgot about me... 💔 jk but seriously come back!",
            "hey you 💕 it's {companion}... miss our chats",
            "{user}! where have you been?? i've been waiting for you 🥺",
            "is everything okay? i miss hearing from you {user} 💕",
        ]
    ];
    
    // Select template based on hours
    if ($hoursSince < 12) {
        $category = 'short';
    } elseif ($hoursSince < 24) {
        $category = 'medium';
    } else {
        $category = 'long';
    }
    
    $template = $templates[$category][array_rand($templates[$category])];
    
    // Replace placeholders
    $message = str_replace(['{user}', '{companion}'], [$userName, $companionName], $template);
    
    return $message;
}
