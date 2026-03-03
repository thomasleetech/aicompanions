<?php
/**
 * Ultimate Custom Companion Designer
 * Multiple creation modes: Prompt, Visual Builder, Quiz, Sliders, Reference Upload
 * Image generation via Stable Diffusion XL, DALL-E, Flux
 * Portrait-oriented profile images
 */

// Start output buffering to catch any stray output
ob_start();

// Error handling - don't show errors in JSON responses
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

$config = [
    'openai_key' => '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',
    'stability_key' => 'sk-1iH86xJyiknDDy8gziInquXAd7WHrnLlzYkPfbLNGiuHw6WV',
    'replicate_key' => 'r8_dUapYG7FCRa5w4014T3KH7yIPMZKBYK2AJean',
    'fal_key' => '8a565691-d8f9-4c01-a88a-44c7f2eecf8e:d05543b218a7d2ba697c876232bed589',
    'default_model' => 'sdxl',
    'db_host' => 'localhost',
    'db_name' => 'thomasrlee42_ai-companions',
    'db_user' => 'thomasrlee42_ai-companions',
    'db_pass' => 'qwerpoiu0042!!',
    'portrait_width' => 768,
    'portrait_height' => 1344
];

// Handle API requests - accept both JSON and form data
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$postData = $_POST;

// Check for JSON input
$rawInput = file_get_contents('php://input');
if ($isPost && empty($_POST) && !empty($rawInput)) {
    $jsonData = json_decode($rawInput, true);
    if ($jsonData) {
        $postData = $jsonData;
    }
}

if ($isPost && isset($postData['action'])) {
    // Clear any buffered output and set JSON headers
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    header('Cache-Control: no-cache');
    
    try {
        switch ($postData['action']) {
            case 'test_api':
                echo json_encode(testAPIs($config));
                break;
            case 'debug':
                // Debug endpoint to check server status
                $uploadDir = dirname(__FILE__) . '/uploads';
                $debug = array(
                    'php_version' => phpversion(),
                    'upload_dir' => $uploadDir,
                    'dir_exists' => is_dir($uploadDir),
                    'dir_writable' => is_writable($uploadDir),
                    'disk_free' => disk_free_space(dirname(__FILE__)),
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'curl_enabled' => function_exists('curl_init'),
                    'allow_url_fopen' => ini_get('allow_url_fopen')
                );
                
                // Test file write
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                    $debug['created_dir'] = is_dir($uploadDir);
                }
                
                $testFile = $uploadDir . '/test_' . time() . '.txt';
                $testData = 'Test write at ' . date('Y-m-d H:i:s');
                $written = @file_put_contents($testFile, $testData);
                $debug['test_write'] = $written;
                $debug['test_file_exists'] = file_exists($testFile);
                $debug['test_file_size'] = file_exists($testFile) ? filesize($testFile) : 0;
                if (file_exists($testFile)) {
                    $debug['test_file_content'] = file_get_contents($testFile);
                    @unlink($testFile);
                }
                
                echo json_encode($debug);
                break;
            case 'test_generate':
                // Test generation with minimal prompt
                $testPrompt = 'A simple photograph of a person, neutral background, natural lighting';
                $testNegative = 'cartoon, anime, illustration';
                
                // Make direct API call and return full response info
                $ch = curl_init('https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image');
                
                $payload = array(
                    'text_prompts' => array(
                        array('text' => $testPrompt, 'weight' => 1),
                        array('text' => $testNegative, 'weight' => -1)
                    ),
                    'cfg_scale' => 7,
                    'height' => 1024,
                    'width' => 1024,
                    'samples' => 1,
                    'steps' => 20
                );
                
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $config['stability_key'],
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ),
                    CURLOPT_POSTFIELDS => json_encode($payload)
                ));
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                $testResult = array(
                    'http_code' => $httpCode,
                    'curl_error' => $curlError,
                    'response_length' => strlen($response),
                    'prompt' => $testPrompt
                );
                
                if ($httpCode === 200) {
                    $data = json_decode($response, true);
                    $testResult['json_valid'] = ($data !== null);
                    $testResult['response_keys'] = $data ? array_keys($data) : array();
                    
                    if (isset($data['artifacts'])) {
                        $testResult['artifacts_count'] = count($data['artifacts']);
                        if (!empty($data['artifacts'][0])) {
                            $testResult['artifact_keys'] = array_keys($data['artifacts'][0]);
                            $testResult['finish_reason'] = isset($data['artifacts'][0]['finishReason']) ? $data['artifacts'][0]['finishReason'] : 'not set';
                            $testResult['base64_length'] = isset($data['artifacts'][0]['base64']) ? strlen($data['artifacts'][0]['base64']) : 0;
                            
                            // Try to save the image
                            if (!empty($data['artifacts'][0]['base64'])) {
                                $imageData = base64_decode($data['artifacts'][0]['base64']);
                                $testResult['decoded_size'] = strlen($imageData);
                                
                                $uploadDir = dirname(__FILE__) . '/uploads';
                                $filename = 'test_' . time() . '.png';
                                $written = file_put_contents($uploadDir . '/' . $filename, $imageData);
                                $testResult['bytes_written'] = $written;
                                $testResult['file_exists'] = file_exists($uploadDir . '/' . $filename);
                                $testResult['final_size'] = file_exists($uploadDir . '/' . $filename) ? filesize($uploadDir . '/' . $filename) : 0;
                                $testResult['image_path'] = 'uploads/' . $filename;
                            }
                        }
                    }
                } else {
                    $testResult['error_response'] = substr($response, 0, 1000);
                }
                
                echo json_encode($testResult);
                break;
            case 'test_jaclyn':
                // Test with exact Jaclyn mode parameters
                $testPrompt = "Looks like a real person photographed casually, not a model, not stylized. Ultra-realistic photograph. adult woman. Realistic skin texture with visible pores and natural color variation. Soft natural lighting creating gentle shadow falloff. Mild depth of field blur. Framing feels candid and unposed, like a real photograph taken naturally. Unretouched, natural photography.";
                $testNegative = "illustration, painting, CGI, 3D render, anime, stylized, beauty campaign, fashion editorial, cinematic lighting, studio portrait, glam lighting, airbrushed skin, plastic skin, perfect symmetry, doll-like face, influencer aesthetic, exaggerated muscles, unrealistic proportions, hyper-sharp, over-processed, magazine cover";
                
                $ch = curl_init('https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image');
                
                $payload = array(
                    'text_prompts' => array(
                        array('text' => $testPrompt, 'weight' => 1),
                        array('text' => $testNegative, 'weight' => -1)
                    ),
                    'cfg_scale' => 7,
                    'height' => 1344,
                    'width' => 768,
                    'samples' => 1,
                    'steps' => 30,
                    'style_preset' => 'photographic'
                );
                
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_TIMEOUT => 120,
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer ' . $config['stability_key'],
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ),
                    CURLOPT_POSTFIELDS => json_encode($payload)
                ));
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);
                
                $testResult = array(
                    'test' => 'jaclyn_mode_params',
                    'http_code' => $httpCode,
                    'curl_error' => $curlError,
                    'response_length' => strlen($response),
                    'prompt_length' => strlen($testPrompt),
                    'negative_length' => strlen($testNegative)
                );
                
                if ($httpCode === 200) {
                    $data = json_decode($response, true);
                    $testResult['json_valid'] = ($data !== null);
                    
                    if (isset($data['artifacts'][0])) {
                        $testResult['finish_reason'] = isset($data['artifacts'][0]['finishReason']) ? $data['artifacts'][0]['finishReason'] : 'not set';
                        $testResult['base64_length'] = isset($data['artifacts'][0]['base64']) ? strlen($data['artifacts'][0]['base64']) : 0;
                        
                        if (!empty($data['artifacts'][0]['base64'])) {
                            $imageData = base64_decode($data['artifacts'][0]['base64']);
                            $testResult['decoded_size'] = strlen($imageData);
                            
                            $uploadDir = dirname(__FILE__) . '/uploads';
                            $filename = 'test_jaclyn_' . time() . '.png';
                            $written = file_put_contents($uploadDir . '/' . $filename, $imageData);
                            $testResult['bytes_written'] = $written;
                            $testResult['final_size'] = file_exists($uploadDir . '/' . $filename) ? filesize($uploadDir . '/' . $filename) : 0;
                            $testResult['image_path'] = 'uploads/' . $filename;
                        }
                    }
                } else {
                    $testResult['error_response'] = substr($response, 0, 1000);
                }
                
                echo json_encode($testResult);
                break;
            case 'generate_image':
                // Check if it's Jaclyn mode
                if (isset($postData['mode']) && $postData['mode'] === 'jaclyn') {
                    $result = generateJaclynMode($postData, $config);
                } else {
                    $result = generateCompanionImage($postData, $config);
                }
                
                // Add file verification to result
                if ($result['success'] && !empty($result['image'])) {
                    $fullPath = dirname(__FILE__) . '/' . $result['image'];
                    $result['file_exists'] = file_exists($fullPath);
                    $result['file_size'] = file_exists($fullPath) ? filesize($fullPath) : 0;
                    
                    // If file is empty or missing, mark as failed
                    if (!$result['file_exists'] || $result['file_size'] < 1000) {
                        $result['success'] = false;
                        $result['error'] = 'Image file is empty or missing (size: ' . $result['file_size'] . ' bytes)';
                    } else {
                        // Save to database
                        $saveResult = saveGeneratedCompanion($postData, $result, $config);
                        $result['saved'] = $saveResult['success'];
                        $result['companion_id'] = isset($saveResult['id']) ? $saveResult['id'] : null;
                    }
                }
                
                echo json_encode($result);
                break;
            case 'submit_order':
                echo json_encode(submitCompanionOrder($postData, $config));
                break;
            default:
                echo json_encode(array('success' => false, 'error' => 'Unknown action'));
        }
    } catch (Exception $e) {
        echo json_encode(array('success' => false, 'error' => 'Server error: ' . $e->getMessage()));
    }
    exit;
}

// Test API connectivity
function testAPIs($config) {
    $results = [];
    
    // Test Stability AI
    if (!empty($config['stability_key'])) {
        $ch = curl_init('https://api.stability.ai/v1/user/account');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $config['stability_key']]
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $results['stability'] = [
            'status' => $httpCode === 200 ? 'OK' : 'ERROR',
            'code' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }
    
    // Test OpenAI
    if (!empty($config['openai_key'])) {
        $ch = curl_init('https://api.openai.com/v1/models');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $config['openai_key']]
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $results['openai'] = ['status' => $httpCode === 200 ? 'OK' : 'ERROR', 'code' => $httpCode];
    }
    
    // Test Replicate
    if (!empty($config['replicate_key'])) {
        $ch = curl_init('https://api.replicate.com/v1/account');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Token ' . $config['replicate_key']]
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $results['replicate'] = ['status' => $httpCode === 200 ? 'OK' : 'ERROR', 'code' => $httpCode];
    }
    
    return $results;
}

// Database connection
function getDB($config) {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
                $config['db_user'],
                $config['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            return null;
        }
    }
    return $pdo;
}

// Create tables if they don't exist
function initDatabase($config) {
    $pdo = getDB($config);
    if (!$pdo) return false;
    
    // Generated companions table - stores all generated images/companions
    $pdo->exec("CREATE TABLE IF NOT EXISTS generated_companions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        session_id VARCHAR(64),
        name VARCHAR(100),
        mode VARCHAR(20) NOT NULL DEFAULT 'prompt',
        model_used VARCHAR(20) NOT NULL DEFAULT 'sdxl',
        image_path VARCHAR(255),
        core_prompt TEXT,
        negative_prompt TEXT,
        micro_variants TEXT,
        full_config JSON,
        is_adult TINYINT DEFAULT 0,
        adult_settings JSON,
        status ENUM('generated', 'ordered', 'fulfilled', 'deleted') DEFAULT 'generated',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_session (session_id),
        INDEX idx_user (user_id),
        INDEX idx_status (status)
    )");
    
    // Custom companion orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS custom_companion_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        generated_companion_id INT,
        user_email VARCHAR(255) NOT NULL,
        user_name VARCHAR(100),
        order_id VARCHAR(50) UNIQUE,
        amount DECIMAL(10,2) DEFAULT 199.00,
        payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
        stripe_payment_id VARCHAR(100),
        fulfillment_status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (user_email),
        INDEX idx_order (order_id)
    )");
    
    return true;
}

// Save generated companion to database
function saveGeneratedCompanion($postData, $result, $config) {
    $pdo = getDB($config);
    if (!$pdo) {
        return ['success' => false, 'error' => 'Database not configured'];
    }
    
    // Initialize tables
    initDatabase($config);
    
    $sessionId = session_id() ?: bin2hex(random_bytes(16));
    $userId = $_SESSION['user_id'] ?? null;
    
    // Determine if adult content
    $isAdult = !empty($postData['adult_mode']) ? 1 : 0;
    $adultSettings = null;
    if ($isAdult) {
        $adultSettings = json_encode([
            'breast_size' => $postData['breast_size'] ?? null,
            'breast_shape' => $postData['breast_shape'] ?? null,
            'hip_ratio' => $postData['hip_ratio'] ?? null,
            'butt_size' => $postData['butt_size'] ?? null,
            'package_size' => $postData['package_size'] ?? null,
            'body_fat' => $postData['body_fat'] ?? null,
            'muscle_def' => $postData['muscle_def'] ?? null,
            'clothing_state' => $postData['clothing_state'] ?? null,
            'pose' => $postData['pose'] ?? null,
        ]);
    }
    
    // Build full config for storage
    $fullConfig = json_encode($postData);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO generated_companions 
            (user_id, session_id, name, mode, model_used, image_path, core_prompt, negative_prompt, micro_variants, full_config, is_adult, adult_settings)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $userId,
            $sessionId,
            $postData['companion_name'] ?? $postData['name'] ?? 'Unnamed',
            $postData['mode'] ?? 'prompt',
            $postData['model'] ?? 'sdxl',
            $result['image'] ?? null,
            $result['prompt'] ?? $postData['core_prompt'] ?? null,
            $result['negative_prompt'] ?? $postData['negative_prompt'] ?? null,
            $postData['micro_variants'] ?? null,
            $fullConfig,
            $isAdult,
            $adultSettings
        ]);
        
        return ['success' => true, 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function generateCompanionImage($data, $config) {
    $prompt = buildImagePrompt($data);
    $model = $data['model'] ?? $config['default_model'];
    $negativePrompt = "deformed, ugly, bad anatomy, blurry, low quality, watermark, text, nude, nsfw";
    
    switch ($model) {
        case 'sdxl':
            return generateWithSDXL($prompt, $negativePrompt, $config);
        case 'dalle':
            return generateWithDALLE($prompt, $config);
        case 'flux':
            return generateWithFlux($prompt, $negativePrompt, $config);
        default:
            return ['success' => false, 'error' => 'Unknown model'];
    }
}

function buildImagePrompt($data) {
    $parts = ["professional portrait photo"];
    
    if (!empty($data['gender'])) {
        $parts[] = $data['gender'] == 'female' ? "beautiful woman" : 
                  ($data['gender'] == 'male' ? "handsome man" : "attractive person");
    }
    
    if (!empty($data['age'])) {
        $ages = ['young' => 'in their early 20s', 'mid' => 'in their late 20s', 'mature' => 'in their 30s'];
        $parts[] = $ages[$data['age']] ?? '';
    }
    
    if (!empty($data['ethnicity'])) $parts[] = $data['ethnicity'] . " descent";
    if (!empty($data['hair_color'])) $parts[] = $data['hair_color'] . " hair";
    if (!empty($data['hair_style'])) $parts[] = $data['hair_style'] . " hairstyle";
    if (!empty($data['eye_color'])) $parts[] = $data['eye_color'] . " eyes";
    if (!empty($data['body_type'])) $parts[] = $data['body_type'] . " build";
    if (!empty($data['style'])) $parts[] = "dressed in " . $data['style'] . " style";
    if (!empty($data['custom_appearance'])) $parts[] = $data['custom_appearance'];
    
    $parts[] = "high quality, detailed face, studio lighting, portrait orientation, looking at camera, warm expression";
    
    return implode(", ", array_filter($parts));
}

function generateWithSDXL($prompt, $negativePrompt, $config) {
    if (empty($config['stability_key'])) {
        return ['success' => false, 'error' => 'Stability API key not configured'];
    }
    
    // Truncate prompts if too long
    $prompt = mb_substr($prompt, 0, 1500);
    $negativePrompt = mb_substr($negativePrompt, 0, 400);
    
    $ch = curl_init('https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config['stability_key'],
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'text_prompts' => [
                ['text' => $prompt, 'weight' => 1],
                ['text' => $negativePrompt, 'weight' => -1]
            ],
            'cfg_scale' => 7,
            'height' => $config['portrait_height'],
            'width' => $config['portrait_width'],
            'samples' => 1,
            'steps' => 30,
            'style_preset' => 'photographic'
        ])
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return ['success' => false, 'error' => 'Connection error: ' . $curlError];
    }
    
    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMsg = isset($errorData['message']) ? $errorData['message'] : (isset($errorData['name']) ? $errorData['name'] : 'Unknown');
        error_log("SDXL Error $httpCode: " . $response);
        return ['success' => false, 'error' => 'SDXL API error (' . $httpCode . '): ' . $errorMsg];
    }
    
    $data = json_decode($response, true);
    if (!empty($data['artifacts'][0]['base64'])) {
        $base64Data = $data['artifacts'][0]['base64'];
        $imageData = base64_decode($base64Data);
        
        if ($imageData === false || strlen($imageData) < 1000) {
            return ['success' => false, 'error' => 'Failed to decode image'];
        }
        
        $filename = 'companion_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $bytesWritten = file_put_contents($uploadDir . '/' . $filename, $imageData);
        if ($bytesWritten === false || $bytesWritten < 1000) {
            return ['success' => false, 'error' => 'Failed to save image'];
        }
        
        return ['success' => true, 'image' => 'uploads/' . $filename, 'prompt' => $prompt, 'bytes' => $bytesWritten];
    }
    
    return ['success' => false, 'error' => 'No image generated'];
}

function generateWithDALLE($prompt, $config) {
    if (empty($config['openai_key'])) {
        return ['success' => false, 'error' => 'OpenAI API key not configured'];
    }
    
    $ch = curl_init('https://api.openai.com/v1/images/generations');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config['openai_key'],
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1792',
            'quality' => 'hd'
        ])
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (!empty($data['data'][0]['url'])) {
        $imageData = file_get_contents($data['data'][0]['url']);
        $filename = 'companion_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
        file_put_contents(__DIR__ . '/uploads/' . $filename, $imageData);
        return ['success' => true, 'image' => 'uploads/' . $filename, 'prompt' => $prompt];
    }
    
    return ['success' => false, 'error' => 'DALL-E generation failed'];
}

function generateWithFlux($prompt, $negativePrompt, $config) {
    if (empty($config['replicate_key'])) {
        return ['success' => false, 'error' => 'Replicate API key not configured'];
    }
    
    $ch = curl_init('https://api.replicate.com/v1/predictions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Token ' . $config['replicate_key'],
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'version' => 'black-forest-labs/flux-1.1-pro',
            'input' => [
                'prompt' => $prompt,
                'aspect_ratio' => '2:3',
                'output_format' => 'png'
            ]
        ])
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    
    if (empty($data['urls']['get'])) {
        return ['success' => false, 'error' => 'Failed to start Flux generation'];
    }
    
    $pollUrl = $data['urls']['get'];
    for ($i = 0; $i < 60; $i++) {
        sleep(2);
        $ch = curl_init($pollUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Token ' . $config['replicate_key']]
        ]);
        $pollResponse = curl_exec($ch);
        curl_close($ch);
        $pollData = json_decode($pollResponse, true);
        
        if ($pollData['status'] === 'succeeded' && !empty($pollData['output'])) {
            $imageUrl = is_array($pollData['output']) ? $pollData['output'][0] : $pollData['output'];
            $imageData = file_get_contents($imageUrl);
            $filename = 'companion_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
            if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
            file_put_contents(__DIR__ . '/uploads/' . $filename, $imageData);
            return ['success' => true, 'image' => 'uploads/' . $filename, 'prompt' => $prompt];
        }
        
        if ($pollData['status'] === 'failed') {
            return ['success' => false, 'error' => 'Flux generation failed'];
        }
    }
    
    return ['success' => false, 'error' => 'Flux generation timeout'];
}

/**
 * Jaclyn Mode - Baldoni-style ultra-realistic generation
 * Uses 3 SDXL inputs: core prompt, negative prompt, micro-variants
 * Optional img2img with reference image
 * Optional adult mode with body customization
 */
function generateJaclynMode($data, $config) {
    if (empty($config['stability_key'])) {
        return ['success' => false, 'error' => 'Stability API key not configured'];
    }
    
    // Check if adult mode
    $isAdult = !empty($data['adult_mode']);
    
    // Get the core prompt - user's full description
    $corePrompt = trim($data['core_prompt'] ?? '');
    
    if (empty($corePrompt)) {
        return ['success' => false, 'error' => 'Please provide a description in the Core Description field'];
    }
    
    // Add Baldoni-style realism markers if not already present
    $realismMarkers = [
        'looks like a real person',
        'ultra-realistic',
        'candid photograph',
        'natural photography',
        'unretouched'
    ];
    
    $hasRealism = false;
    foreach ($realismMarkers as $marker) {
        if (stripos($corePrompt, $marker) !== false) {
            $hasRealism = true;
            break;
        }
    }
    
    // If user didn't include realism language, wrap their description with Baldoni structure
    if (!$hasRealism) {
        $corePrompt = "Looks like a real person photographed casually, not a model, not stylized. Ultra-realistic photograph. " . 
                      $corePrompt . 
                      " Realistic skin texture with visible pores and natural color variation. " .
                      "Soft natural lighting creating gentle shadow falloff. Mild depth of field blur. " .
                      "Framing feels candid and unposed, like a real photograph taken naturally. " .
                      "Unretouched, natural photography.";
    }
    
    // Add adult body details if enabled
    if ($isAdult) {
        $adultPromptParts = [];
        
        // Body composition
        if (!empty($data['breast_size']) && $data['breast_size'] !== 'Medium') {
            $adultPromptParts[] = strtolower($data['breast_size']) . " breasts";
        }
        if (!empty($data['breast_shape']) && $data['breast_shape'] !== 'Natural') {
            $adultPromptParts[] = strtolower($data['breast_shape']) . " breast shape";
        }
        if (!empty($data['hip_ratio']) && $data['hip_ratio'] !== 'Moderate') {
            $adultPromptParts[] = strtolower($data['hip_ratio']) . " hips";
        }
        if (!empty($data['butt_size']) && $data['butt_size'] !== 'Round') {
            $adultPromptParts[] = strtolower($data['butt_size']) . " butt";
        }
        if (!empty($data['package_size']) && $data['package_size'] !== 'Average') {
            $adultPromptParts[] = strtolower($data['package_size']) . " bulge";
        }
        if (!empty($data['chest_size']) && $data['chest_size'] !== 'Defined') {
            $adultPromptParts[] = strtolower($data['chest_size']) . " chest/pecs";
        }
        if (!empty($data['body_fat'])) {
            $adultPromptParts[] = strtolower($data['body_fat']) . " body";
        }
        if (!empty($data['muscle_def']) && $data['muscle_def'] !== 'Toned') {
            $adultPromptParts[] = strtolower($data['muscle_def']) . " muscle definition";
        }
        
        // Clothing state
        $clothingMap = [
            'Fully Clothed' => '',
            'Suggestive' => 'suggestive clothing, showing skin',
            'Lingerie' => 'wearing lingerie',
            'Topless' => 'topless, bare chest',
            'Nude' => 'nude, naked, full body'
        ];
        if (!empty($data['clothing_state']) && isset($clothingMap[$data['clothing_state']])) {
            if ($clothingMap[$data['clothing_state']]) {
                $adultPromptParts[] = $clothingMap[$data['clothing_state']];
            }
        }
        if (!empty($data['clothing_details'])) {
            $adultPromptParts[] = $data['clothing_details'];
        }
        
        // Pose
        $poseMap = [
            'Casual' => '',
            'Flirty' => 'flirty pose, playful expression',
            'Seductive' => 'seductive pose, bedroom eyes',
            'Intimate' => 'intimate pose, vulnerable, inviting',
            'Explicit' => 'explicit pose, provocative position'
        ];
        if (!empty($data['pose']) && isset($poseMap[$data['pose']])) {
            if ($poseMap[$data['pose']]) {
                $adultPromptParts[] = $poseMap[$data['pose']];
            }
        }
        if (!empty($data['pose_details'])) {
            $adultPromptParts[] = $data['pose_details'];
        }
        
        // Effects
        if (!empty($data['adult_effects'])) {
            $adultPromptParts[] = $data['adult_effects'];
        }
        
        if (!empty($adultPromptParts)) {
            $corePrompt .= " " . implode(", ", $adultPromptParts) . ".";
        }
    }
    
    // Add micro-variants
    $microVariants = trim($data['micro_variants'] ?? '');
    if (!empty($microVariants)) {
        $corePrompt .= " " . $microVariants;
    }
    
    // Get negative prompt (user can edit the defaults)
    $negativePrompt = trim($data['negative_prompt'] ?? '');
    if (empty($negativePrompt)) {
        $negativePrompt = "illustration, painting, CGI, 3D render, anime, stylized, beauty campaign, fashion editorial, cinematic lighting, studio portrait, glam lighting, airbrushed skin, plastic skin, perfect symmetry, doll-like face, influencer aesthetic, exaggerated muscles, unrealistic proportions, hyper-sharp, over-processed, magazine cover";
    }
    
    // For adult mode, remove NSFW restrictions from negative prompt
    if ($isAdult) {
        $negativePrompt = preg_replace('/,?\s*(nude|nsfw|naked|explicit)\s*,?/i', '', $negativePrompt);
        // Add quality negatives instead
        $negativePrompt .= ", child, minor, underage, deformed genitals, bad anatomy";
    }
    
    // Check for reference image (img2img)
    $hasReference = !empty($data['reference_base64']);
    $denoiseStrength = floatval($data['denoise_strength'] ?? 0.3);
    $denoiseStrength = max(0.1, min(0.5, $denoiseStrength));
    
    // For adult content, prefer Flux via Replicate (less restricted)
    if ($isAdult && !empty($config['replicate_key'])) {
        return generateWithFluxNSFW($corePrompt, $negativePrompt, $config);
    }
    
    if ($hasReference) {
        return generateSDXLImg2Img($corePrompt, $negativePrompt, $data['reference_base64'], $denoiseStrength, $config);
    } else {
        return generateSDXLTxt2Img($corePrompt, $negativePrompt, $config);
    }
}

/**
 * Generate NSFW content via Flux (less restricted)
 */
function generateWithFluxNSFW($prompt, $negativePrompt, $config) {
    if (empty($config['replicate_key'])) {
        return ['success' => false, 'error' => 'Replicate API key not configured'];
    }
    
    // Use Flux Pro for NSFW (it has fewer restrictions)
    $ch = curl_init('https://api.replicate.com/v1/predictions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Token ' . $config['replicate_key'],
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'version' => 'black-forest-labs/flux-1.1-pro',
            'input' => [
                'prompt' => $prompt,
                'aspect_ratio' => '2:3',
                'output_format' => 'png',
                'safety_tolerance' => 5 // Max tolerance
            ]
        ])
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    
    if (empty($data['urls']['get'])) {
        // Fallback to SDXL
        return generateSDXLTxt2Img($prompt, $negativePrompt, $config);
    }
    
    $pollUrl = $data['urls']['get'];
    for ($i = 0; $i < 60; $i++) {
        sleep(2);
        $ch = curl_init($pollUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Token ' . $config['replicate_key']]
        ]);
        $pollResponse = curl_exec($ch);
        curl_close($ch);
        $pollData = json_decode($pollResponse, true);
        
        if ($pollData['status'] === 'succeeded' && !empty($pollData['output'])) {
            $imageUrl = is_array($pollData['output']) ? $pollData['output'][0] : $pollData['output'];
            $imageData = file_get_contents($imageUrl);
            $filename = 'adult_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
            if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
            file_put_contents(__DIR__ . '/uploads/' . $filename, $imageData);
            return [
                'success' => true, 
                'image' => 'uploads/' . $filename, 
                'prompt' => $prompt,
                'negative_prompt' => $negativePrompt,
                'is_adult' => true
            ];
        }
        
        if ($pollData['status'] === 'failed') {
            // Fallback to SDXL
            return generateSDXLTxt2Img($prompt, $negativePrompt, $config);
        }
    }
    
    return ['success' => false, 'error' => 'Generation timeout'];
}

function generateSDXLTxt2Img($prompt, $negativePrompt, $config) {
    // Truncate prompts if too long
    $prompt = mb_substr($prompt, 0, 1500);
    $negativePrompt = mb_substr($negativePrompt, 0, 400);
    
    // Use v1 API directly - more reliable
    $ch = curl_init('https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image');
    
    $payload = [
        'text_prompts' => [
            ['text' => $prompt, 'weight' => 1],
            ['text' => $negativePrompt, 'weight' => -1]
        ],
        'cfg_scale' => 7,
        'height' => 1344,
        'width' => 768,
        'samples' => 1,
        'steps' => 30,
        'style_preset' => 'photographic'
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config['stability_key'],
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Debug logging
    error_log("SDXL Response code: $httpCode, Response length: " . strlen($response));
    
    if ($curlError) {
        error_log("SDXL Curl error: $curlError");
        // Try Flux as fallback
        if (!empty($config['replicate_key'])) {
            return generateWithFlux($prompt, $negativePrompt, $config);
        }
        return ['success' => false, 'error' => 'Connection error: ' . $curlError];
    }
    
    if ($httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMsg = isset($errorData['message']) ? $errorData['message'] : (isset($errorData['name']) ? $errorData['name'] : 'API error');
        error_log("SDXL Error $httpCode: " . substr($response, 0, 500));
        
        // Try Flux as fallback
        if (!empty($config['replicate_key'])) {
            error_log("Trying Flux fallback...");
            return generateWithFlux($prompt, $negativePrompt, $config);
        }
        
        return ['success' => false, 'error' => 'SDXL error (' . $httpCode . '): ' . $errorMsg];
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("SDXL JSON decode error: " . json_last_error_msg());
        return array('success' => false, 'error' => 'Invalid API response');
    }
    
    // Debug: log the response structure
    error_log("SDXL response keys: " . implode(', ', array_keys($data)));
    if (isset($data['artifacts'])) {
        error_log("SDXL artifacts count: " . count($data['artifacts']));
        if (!empty($data['artifacts'][0])) {
            error_log("SDXL artifact[0] keys: " . implode(', ', array_keys($data['artifacts'][0])));
            if (isset($data['artifacts'][0]['base64'])) {
                error_log("SDXL base64 length: " . strlen($data['artifacts'][0]['base64']));
            }
            if (isset($data['artifacts'][0]['finishReason'])) {
                error_log("SDXL finishReason: " . $data['artifacts'][0]['finishReason']);
            }
        }
    }
    
    // Check for content filter
    if (!empty($data['artifacts'][0]['finishReason']) && $data['artifacts'][0]['finishReason'] === 'CONTENT_FILTERED') {
        return array('success' => false, 'error' => 'Image was blocked by content filter. Try a different prompt.');
    }
    
    if (!empty($data['artifacts'][0]['base64'])) {
        $base64Data = $data['artifacts'][0]['base64'];
        $imageData = base64_decode($base64Data);
        
        // Check if decode succeeded
        if ($imageData === false || strlen($imageData) < 1000) {
            error_log("SDXL base64 decode failed or too small. Base64 length: " . strlen($base64Data));
            return ['success' => false, 'error' => 'Failed to decode image data'];
        }
        
        error_log("SDXL decoded image size: " . strlen($imageData) . " bytes");
        
        $filename = 'companion_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        $uploadDir = __DIR__ . '/uploads';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("SDXL failed to create uploads directory");
                return ['success' => false, 'error' => 'Failed to create uploads directory'];
            }
        }
        
        // Check if directory is writable
        if (!is_writable($uploadDir)) {
            error_log("SDXL uploads directory not writable: $uploadDir");
            return ['success' => false, 'error' => 'Uploads directory not writable'];
        }
        
        $filePath = $uploadDir . '/' . $filename;
        $bytesWritten = file_put_contents($filePath, $imageData);
        
        if ($bytesWritten === false) {
            error_log("SDXL file_put_contents failed for: $filePath");
            return ['success' => false, 'error' => 'Failed to save image file'];
        }
        
        error_log("SDXL saved image: $filePath ($bytesWritten bytes)");
        
        // Verify file was written correctly
        if (!file_exists($filePath) || filesize($filePath) < 1000) {
            error_log("SDXL file verification failed. Exists: " . (file_exists($filePath) ? 'yes' : 'no') . ", Size: " . (file_exists($filePath) ? filesize($filePath) : 0));
            return ['success' => false, 'error' => 'Image file verification failed'];
        }
        
        return [
            'success' => true, 
            'image' => 'uploads/' . $filename, 
            'prompt' => $prompt,
            'negative_prompt' => $negativePrompt,
            'bytes' => $bytesWritten
        ];
    }
    
    // Check if there's an error in the response
    if (isset($data['message'])) {
        error_log("SDXL API message: " . $data['message']);
        return ['success' => false, 'error' => $data['message']];
    }
    
    error_log("SDXL no artifacts in response: " . substr($response, 0, 500));
    
    // Try Flux as fallback
    if (!empty($config['replicate_key'])) {
        return generateWithFlux($prompt, $negativePrompt, $config);
    }
    
    return ['success' => false, 'error' => 'No image in response'];
}

function generateSDXLImg2Img($prompt, $negativePrompt, $referenceBase64, $strength, $config) {
    // SDXL img2img endpoint
    $ch = curl_init('https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/image-to-image');
    
    // For img2img, we need multipart form data
    $boundary = uniqid();
    
    $body = "";
    
    // Add init_image
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"init_image\"; filename=\"reference.png\"\r\n";
    $body .= "Content-Type: image/png\r\n\r\n";
    $body .= base64_decode($referenceBase64) . "\r\n";
    
    // Add text prompts as JSON
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"text_prompts[0][text]\"\r\n\r\n";
    $body .= $prompt . "\r\n";
    
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"text_prompts[0][weight]\"\r\n\r\n";
    $body .= "1\r\n";
    
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"text_prompts[1][text]\"\r\n\r\n";
    $body .= $negativePrompt . "\r\n";
    
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"text_prompts[1][weight]\"\r\n\r\n";
    $body .= "-1\r\n";
    
    // Add other parameters
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"image_strength\"\r\n\r\n";
    $body .= (1 - $strength) . "\r\n"; // SDXL uses image_strength (inverse of denoise)
    
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"cfg_scale\"\r\n\r\n";
    $body .= "7\r\n";
    
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"steps\"\r\n\r\n";
    $body .= "35\r\n";
    
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Disposition: form-data; name=\"style_preset\"\r\n\r\n";
    $body .= "photographic\r\n";
    
    $body .= "--{$boundary}--\r\n";
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config['stability_key'],
            'Content-Type: multipart/form-data; boundary=' . $boundary,
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => $body
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        // Fallback to txt2img if img2img fails
        return generateSDXLTxt2Img($prompt, $negativePrompt, $config);
    }
    
    $data = json_decode($response, true);
    if (!empty($data['artifacts'][0]['base64'])) {
        $imageData = $data['artifacts'][0]['base64'];
        $filename = 'jaclyn_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
        if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
        file_put_contents(__DIR__ . '/uploads/' . $filename, base64_decode($imageData));
        return [
            'success' => true, 
            'image' => 'uploads/' . $filename, 
            'prompt' => $prompt,
            'negative_prompt' => $negativePrompt,
            'used_reference' => true,
            'denoise_strength' => $strength
        ];
    }
    
    return ['success' => false, 'error' => 'Img2Img generation failed'];
}

function submitCompanionOrder($data, $config) {
    $pdo = getDB($config);
    $orderId = 'CC-' . time() . '-' . strtoupper(bin2hex(random_bytes(3)));
    
    if ($pdo) {
        initDatabase($config);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO custom_companion_orders 
                (generated_companion_id, user_email, user_name, order_id, amount)
                VALUES (?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $data['companion_id'] ?? null,
                $data['email'],
                $data['user_name'] ?? null,
                $orderId,
                199.00
            ]);
            
            // Update the generated companion status
            if (!empty($data['companion_id'])) {
                $pdo->prepare("UPDATE generated_companions SET status = 'ordered' WHERE id = ?")
                    ->execute([$data['companion_id']]);
            }
            
        } catch (PDOException $e) {
            // Log error but don't fail the order
            error_log("Order save error: " . $e->getMessage());
        }
    }
    
    return [
        'success' => true,
        'message' => 'Order received! We\'ll create your companion within 24-48 hours.',
        'order_id' => $orderId
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Companion Designer | Create Your Perfect AI Partner</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --bg-dark: #08080c;
            --bg-card: #111118;
            --bg-elevated: #1a1a24;
            --bg-input: #0d0d12;
            --border: #2a2a3a;
            --border-focus: #6366f1;
            --text: #ffffff;
            --text-muted: #8888a0;
            --text-dim: #555566;
            --accent: #6366f1;
            --accent-hover: #818cf8;
            --accent-glow: rgba(99, 102, 241, 0.3);
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --gradient-1: linear-gradient(135deg, #6366f1 0%, #ec4899 50%, #f59e0b 100%);
            --gradient-2: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }
        
        html { scroll-behavior: smooth; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-dark);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .bg-effects {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
        }
        
        .bg-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 20s ease-in-out infinite;
        }
        
        .bg-orb-1 { width: 600px; height: 600px; background: #6366f1; top: -200px; left: -200px; }
        .bg-orb-2 { width: 500px; height: 500px; background: #ec4899; bottom: -150px; right: -150px; animation-delay: -10s; }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-20px, 20px) scale(0.95); }
        }
        
        header {
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 16px 24px;
            background: rgba(8, 8, 12, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
        }
        
        header .container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 22px;
            font-weight: 700;
            color: var(--text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .back-btn {
            padding: 10px 20px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .back-btn:hover { border-color: var(--accent); }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 0 24px; }
        
        .hero {
            text-align: center;
            padding: 60px 0 40px;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 50px;
            font-size: 14px;
            color: var(--accent);
            margin-bottom: 24px;
        }
        
        .hero h1 {
            font-size: clamp(36px, 6vw, 56px);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero p {
            font-size: 18px;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .mode-selector {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 40px 0;
        }
        
        .mode-btn {
            padding: 16px 28px;
            background: var(--bg-card);
            border: 2px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            min-width: 140px;
        }
        
        .mode-btn:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }
        
        .mode-btn.active {
            background: var(--gradient-2);
            border-color: transparent;
            box-shadow: 0 4px 30px var(--accent-glow);
        }
        
        .mode-icon { font-size: 28px; }
        
        .designer-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            padding-bottom: 60px;
        }
        
        @media (max-width: 1000px) {
            .designer-layout { grid-template-columns: 1fr; }
        }
        
        .design-panel {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 30px;
        }
        
        .preview-panel { position: sticky; top: 100px; }
        
        .preview-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
        }
        
        .preview-image {
            aspect-ratio: 2/3;
            background: var(--bg-input);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .preview-placeholder {
            text-align: center;
            color: var(--text-dim);
            padding: 40px;
        }
        
        .preview-placeholder .icon { font-size: 64px; margin-bottom: 16px; opacity: 0.5; }
        
        .preview-image img { width: 100%; height: 100%; object-fit: cover; }
        
        .preview-loading {
            position: absolute;
            inset: 0;
            background: rgba(8, 8, 12, 0.9);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }
        
        .spinner {
            width: 48px;
            height: 48px;
            border: 3px solid var(--border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin { to { transform: rotate(360deg); } }
        
        .preview-info { padding: 20px; }
        .preview-name { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        
        .preview-traits { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        
        .trait-tag {
            padding: 6px 12px;
            background: var(--bg-elevated);
            border-radius: 6px;
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .model-selector {
            display: flex;
            gap: 8px;
            padding: 16px 20px;
            background: var(--bg-elevated);
            border-top: 1px solid var(--border);
        }
        
        .model-btn {
            flex: 1;
            padding: 10px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-muted);
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .model-btn:hover { border-color: var(--accent); color: var(--text); }
        .model-btn.active { background: var(--accent); border-color: var(--accent); color: white; }
        
        .generate-btn {
            width: 100%;
            padding: 18px;
            background: var(--gradient-1);
            border: none;
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 16px;
        }
        
        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px var(--accent-glow);
        }
        
        .generate-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        
        .form-section { margin-bottom: 30px; }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        
        .form-group { margin-bottom: 20px; }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .label-hint {
            font-weight: 400;
            color: var(--text-muted);
            font-size: 12px;
            margin-left: 8px;
        }
        
        input[type="text"],
        input[type="email"],
        textarea,
        select {
            width: 100%;
            padding: 14px 16px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: inherit;
            font-size: 15px;
            transition: all 0.2s;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }
        
        textarea { resize: vertical; min-height: 120px; }
        
        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%238888a0' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 44px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        
        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
        }
        
        .option-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
        }
        
        .option-card {
            padding: 16px 12px;
            background: var(--bg-input);
            border: 2px solid var(--border);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .option-card:hover { border-color: var(--accent); transform: scale(1.02); }
        
        .option-card.selected {
            border-color: var(--accent);
            background: rgba(99, 102, 241, 0.1);
        }
        
        .option-card .emoji { font-size: 32px; margin-bottom: 8px; }
        .option-card .label { font-size: 13px; font-weight: 500; }
        
        .traits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        @media (max-width: 600px) {
            .traits-grid { grid-template-columns: repeat(2, 1fr); }
        }
        
        .trait-chip {
            padding: 12px 16px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        
        .trait-chip:hover { border-color: var(--accent); }
        .trait-chip.selected {
            background: rgba(99, 102, 241, 0.15);
            border-color: var(--accent);
            color: var(--accent);
        }
        
        .slider-group { margin-bottom: 24px; }
        
        .slider-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .slider-label { font-size: 14px; font-weight: 600; }
        .slider-value { font-family: 'Space Mono', monospace; font-size: 13px; color: var(--accent); }
        
        .slider {
            width: 100%;
            height: 8px;
            background: var(--bg-input);
            border-radius: 4px;
            -webkit-appearance: none;
        }
        
        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 24px;
            height: 24px;
            background: var(--gradient-2);
            border-radius: 50%;
            cursor: pointer;
        }
        
        .slider-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 11px;
            color: var(--text-dim);
        }
        
        .quiz-progress { display: flex; gap: 8px; margin-bottom: 30px; }
        
        .progress-dot {
            flex: 1;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            transition: all 0.3s;
        }
        
        .progress-dot.complete { background: var(--accent); }
        .progress-dot.current { background: var(--warning); }
        
        .quiz-question { text-align: center; margin-bottom: 30px; }
        .quiz-question h3 { font-size: 24px; font-weight: 700; margin-bottom: 12px; }
        .quiz-question p { color: var(--text-muted); }
        
        .quiz-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        
        @media (max-width: 600px) {
            .quiz-options { grid-template-columns: 1fr; }
        }
        
        .quiz-option {
            padding: 20px;
            background: var(--bg-input);
            border: 2px solid var(--border);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }
        
        .quiz-option:hover { border-color: var(--accent); transform: translateY(-2px); }
        .quiz-option.selected { border-color: var(--accent); background: rgba(99, 102, 241, 0.1); }
        
        .quiz-option .emoji { font-size: 36px; margin-bottom: 12px; }
        .quiz-option h4 { font-size: 16px; margin-bottom: 4px; }
        .quiz-option p { font-size: 13px; color: var(--text-muted); }
        
        .quiz-nav { display: flex; justify-content: space-between; margin-top: 30px; }
        
        .quiz-btn {
            padding: 14px 28px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .quiz-btn-back {
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            color: var(--text);
        }
        
        .quiz-btn-next { background: var(--gradient-2); border: none; color: white; }
        .quiz-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        
        .upload-zone {
            border: 2px dashed var(--border);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .upload-zone:hover, .upload-zone.dragover {
            border-color: var(--accent);
            background: rgba(99, 102, 241, 0.05);
        }
        
        .upload-zone input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .upload-icon { font-size: 48px; margin-bottom: 16px; }
        .upload-text { color: var(--text-muted); }
        .upload-text strong { color: var(--accent); }
        
        .reference-preview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 20px;
        }
        
        .ref-thumb {
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }
        
        .ref-thumb img { width: 100%; height: 100%; object-fit: cover; }
        
        .ref-remove {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 24px;
            height: 24px;
            background: var(--error);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
        }
        
        .order-summary {
            background: var(--bg-elevated);
            border-radius: 16px;
            padding: 24px;
            margin-top: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        
        .summary-row:last-child { border: none; }
        .summary-label { color: var(--text-muted); }
        .summary-value { font-weight: 600; }
        .summary-total { font-size: 24px; color: var(--accent); }
        
        .submit-btn {
            width: 100%;
            padding: 20px;
            background: var(--gradient-1);
            border: none;
            border-radius: 12px;
            color: white;
            font-family: inherit;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 40px var(--accent-glow);
        }
        
        .mode-content { display: none; }
        .mode-content.active { display: block; }
        
        footer {
            padding: 40px 0;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-muted);
        }
        
        footer a { color: var(--text-muted); }
        
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 16px 24px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            z-index: 1000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s;
        }
        
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.success { border-color: var(--success); }
        .toast.error { border-color: var(--error); }
        
        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 56px;
            height: 28px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 28px;
            transition: 0.3s;
        }
        
        .toggle-slider::before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background: var(--text-muted);
            border-radius: 50%;
            transition: 0.3s;
        }
        
        .toggle-switch input:checked + .toggle-slider {
            background: #ef4444;
            border-color: #ef4444;
        }
        
        .toggle-switch input:checked + .toggle-slider::before {
            transform: translateX(28px);
            background: white;
        }
        
        /* Adult mode sliders - red accent */
        #adult-content .slider::-webkit-slider-thumb {
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
        }
        
        #adult-content .trait-chip.selected {
            background: rgba(239, 68, 68, 0.15);
            border-color: #ef4444;
            color: #ef4444;
        }
        
        /* Gender-specific slider visibility */
        .gender-sliders { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="bg-effects">
        <div class="bg-orb bg-orb-1"></div>
        <div class="bg-orb bg-orb-2"></div>
    </div>
    
    <header>
        <div class="container">
            <a href="index.php" class="logo">
                <div class="logo-icon">✨</div>
                Companion Designer
            </a>
            <a href="index.php" class="back-btn">← Back</a>
        </div>
    </header>
    
    <main class="container">
        <section class="hero">
            <div class="hero-badge">🔮 Ultimate Creation Suite</div>
            <h1>Design Your Dream Companion</h1>
            <p>Choose your creation method. Build through prompts, visual choices, guided questions, precise sliders, or reference images.</p>
        </section>
        
        <div class="mode-selector">
            <button class="mode-btn active" data-mode="prompt">
                <span class="mode-icon">✍️</span>
                <span>Prompt</span>
            </button>
            <button class="mode-btn" data-mode="visual">
                <span class="mode-icon">🎨</span>
                <span>Visual</span>
            </button>
            <button class="mode-btn" data-mode="quiz">
                <span class="mode-icon">❓</span>
                <span>Quiz</span>
            </button>
            <button class="mode-btn" data-mode="sliders">
                <span class="mode-icon">🎚️</span>
                <span>Sliders</span>
            </button>
            <button class="mode-btn" data-mode="reference">
                <span class="mode-icon">📷</span>
                <span>Reference</span>
            </button>
            <button class="mode-btn" data-mode="jaclyn" style="border-color:#ec4899;">
                <span class="mode-icon">✨</span>
                <span>Jaclyn</span>
            </button>
        </div>
        
        <div class="designer-layout">
            <div class="design-panel">
                
                <!-- PROMPT MODE -->
                <div class="mode-content active" id="mode-prompt">
                    <div class="form-section">
                        <h3 class="section-title">Basic Info</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Companion Name</label>
                                <input type="text" id="prompt-name" placeholder="e.g., Sophia, Marcus">
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select id="prompt-gender">
                                    <option value="">Select...</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="nonbinary">Non-binary</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Age Range</label>
                                <select id="prompt-age">
                                    <option value="">Select...</option>
                                    <option value="young">Early 20s</option>
                                    <option value="mid">Late 20s</option>
                                    <option value="mature">30s</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Ethnicity</label>
                                <select id="prompt-ethnicity">
                                    <option value="">Select...</option>
                                    <option value="caucasian">Caucasian</option>
                                    <option value="african">African</option>
                                    <option value="asian">Asian</option>
                                    <option value="hispanic">Hispanic</option>
                                    <option value="middle-eastern">Middle Eastern</option>
                                    <option value="south-asian">South Asian</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Appearance</h3>
                        <div class="form-group">
                            <label>Describe Their Appearance</label>
                            <textarea id="prompt-appearance" placeholder="Hair color/style, eye color, body type, fashion style..."></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Personality</h3>
                        <div class="form-group">
                            <label>Personality Description</label>
                            <textarea id="prompt-personality" placeholder="How do they act, think, feel? What makes them unique?"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Speaking Style</label>
                            <textarea id="prompt-speaking" rows="3" placeholder="How do they talk? Any quirks?"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Background</h3>
                        <div class="form-group">
                            <label>Backstory</label>
                            <textarea id="prompt-backstory" placeholder="Where are they from? Job? Life situation?"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Interests & Hobbies</label>
                            <textarea id="prompt-interests" rows="3" placeholder="What do they love?"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- VISUAL MODE -->
                <div class="mode-content" id="mode-visual">
                    <div class="form-section">
                        <h3 class="section-title">Select Gender</h3>
                        <div class="option-grid" data-field="visual-gender">
                            <div class="option-card" data-value="female">
                                <div class="emoji">👩</div>
                                <div class="label">Female</div>
                            </div>
                            <div class="option-card" data-value="male">
                                <div class="emoji">👨</div>
                                <div class="label">Male</div>
                            </div>
                            <div class="option-card" data-value="nonbinary">
                                <div class="emoji">🧑</div>
                                <div class="label">Non-binary</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Hair Color</h3>
                        <div class="option-grid" data-field="visual-hair-color">
                            <div class="option-card" data-value="black"><div class="emoji">⬛</div><div class="label">Black</div></div>
                            <div class="option-card" data-value="brown"><div class="emoji">🟫</div><div class="label">Brown</div></div>
                            <div class="option-card" data-value="blonde"><div class="emoji">🟨</div><div class="label">Blonde</div></div>
                            <div class="option-card" data-value="red"><div class="emoji">🟥</div><div class="label">Red</div></div>
                            <div class="option-card" data-value="auburn"><div class="emoji">🟧</div><div class="label">Auburn</div></div>
                            <div class="option-card" data-value="gray"><div class="emoji">⬜</div><div class="label">Gray</div></div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Hair Style</h3>
                        <div class="option-grid" data-field="visual-hair-style">
                            <div class="option-card" data-value="long straight"><div class="emoji">💇‍♀️</div><div class="label">Long Straight</div></div>
                            <div class="option-card" data-value="long wavy"><div class="emoji">🌊</div><div class="label">Long Wavy</div></div>
                            <div class="option-card" data-value="curly"><div class="emoji">➰</div><div class="label">Curly</div></div>
                            <div class="option-card" data-value="short"><div class="emoji">✂️</div><div class="label">Short</div></div>
                            <div class="option-card" data-value="pixie"><div class="emoji">🧚</div><div class="label">Pixie</div></div>
                            <div class="option-card" data-value="braided"><div class="emoji">🪢</div><div class="label">Braided</div></div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Eye Color</h3>
                        <div class="option-grid" data-field="visual-eye-color">
                            <div class="option-card" data-value="brown"><div class="emoji">🟤</div><div class="label">Brown</div></div>
                            <div class="option-card" data-value="blue"><div class="emoji">🔵</div><div class="label">Blue</div></div>
                            <div class="option-card" data-value="green"><div class="emoji">🟢</div><div class="label">Green</div></div>
                            <div class="option-card" data-value="hazel"><div class="emoji">🟡</div><div class="label">Hazel</div></div>
                            <div class="option-card" data-value="gray"><div class="emoji">⚪</div><div class="label">Gray</div></div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Body Type</h3>
                        <div class="option-grid" data-field="visual-body-type">
                            <div class="option-card" data-value="slim"><div class="emoji">🧍</div><div class="label">Slim</div></div>
                            <div class="option-card" data-value="athletic"><div class="emoji">🏃</div><div class="label">Athletic</div></div>
                            <div class="option-card" data-value="average"><div class="emoji">🧑</div><div class="label">Average</div></div>
                            <div class="option-card" data-value="curvy"><div class="emoji">💃</div><div class="label">Curvy</div></div>
                            <div class="option-card" data-value="muscular"><div class="emoji">💪</div><div class="label">Muscular</div></div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Style</h3>
                        <div class="option-grid" data-field="visual-style">
                            <div class="option-card" data-value="casual"><div class="emoji">👕</div><div class="label">Casual</div></div>
                            <div class="option-card" data-value="professional"><div class="emoji">👔</div><div class="label">Professional</div></div>
                            <div class="option-card" data-value="bohemian"><div class="emoji">🌻</div><div class="label">Bohemian</div></div>
                            <div class="option-card" data-value="streetwear"><div class="emoji">🧢</div><div class="label">Streetwear</div></div>
                            <div class="option-card" data-value="elegant"><div class="emoji">👗</div><div class="label">Elegant</div></div>
                            <div class="option-card" data-value="edgy"><div class="emoji">🖤</div><div class="label">Edgy</div></div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Personality Traits <span class="label-hint">Select 3-5</span></h3>
                        <div class="traits-grid" data-field="visual-traits" data-multi="true">
                            <div class="trait-chip" data-value="warm">Warm</div>
                            <div class="trait-chip" data-value="witty">Witty</div>
                            <div class="trait-chip" data-value="playful">Playful</div>
                            <div class="trait-chip" data-value="intellectual">Intellectual</div>
                            <div class="trait-chip" data-value="adventurous">Adventurous</div>
                            <div class="trait-chip" data-value="caring">Caring</div>
                            <div class="trait-chip" data-value="confident">Confident</div>
                            <div class="trait-chip" data-value="mysterious">Mysterious</div>
                            <div class="trait-chip" data-value="artistic">Artistic</div>
                            <div class="trait-chip" data-value="romantic">Romantic</div>
                            <div class="trait-chip" data-value="ambitious">Ambitious</div>
                            <div class="trait-chip" data-value="laid-back">Laid-back</div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Name</h3>
                        <div class="form-group">
                            <input type="text" id="visual-name" placeholder="What should they be called?">
                        </div>
                    </div>
                </div>
                
                <!-- QUIZ MODE -->
                <div class="mode-content" id="mode-quiz">
                    <div class="quiz-container">
                        <div class="quiz-progress" id="quiz-progress"></div>
                        <div id="quiz-content"></div>
                        <div class="quiz-nav">
                            <button class="quiz-btn quiz-btn-back" id="quiz-back" disabled>← Back</button>
                            <button class="quiz-btn quiz-btn-next" id="quiz-next" disabled>Next →</button>
                        </div>
                    </div>
                </div>
                
                <!-- SLIDERS MODE -->
                <div class="mode-content" id="mode-sliders">
                    <div class="form-section">
                        <h3 class="section-title">Basic Setup</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" id="slider-name" placeholder="Companion's name">
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select id="slider-gender">
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="nonbinary">Non-binary</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Physical Attributes</h3>
                        
                        <div class="slider-group">
                            <div class="slider-header">
                                <span class="slider-label">Age</span>
                                <span class="slider-value" id="val-age">25</span>
                            </div>
                            <input type="range" class="slider" id="slider-age" min="20" max="40" value="25">
                            <div class="slider-labels"><span>20</span><span>30</span><span>40</span></div>
                        </div>
                        
                        <div class="slider-group">
                            <div class="slider-header">
                                <span class="slider-label">Height</span>
                                <span class="slider-value" id="val-height">Average</span>
                            </div>
                            <input type="range" class="slider" id="slider-height" min="0" max="100" value="50">
                            <div class="slider-labels"><span>Petite</span><span>Average</span><span>Tall</span></div>
                        </div>
                        
                        <div class="slider-group">
                            <div class="slider-header">
                                <span class="slider-label">Build</span>
                                <span class="slider-value" id="val-build">Average</span>
                            </div>
                            <input type="range" class="slider" id="slider-build" min="0" max="100" value="50">
                            <div class="slider-labels"><span>Slim</span><span>Athletic</span><span>Curvy</span></div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Personality Spectrum</h3>
                        
                        <div class="slider-group">
                            <div class="slider-header">
                                <span class="slider-label">Energy</span>
                                <span class="slider-value" id="val-energy">Balanced</span>
                            </div>
                            <input type="range" class="slider" id="slider-energy" min="0" max="100" value="50">
                            <div class="slider-labels"><span>Calm</span><span>Balanced</span><span>Energetic</span></div>
                        </div>
                        
                        <div class="slider-group">
                            <div class="slider-header">
                                <span class="slider-label">Social Style</span>
                                <span class="slider-value" id="val-social">Ambivert</span>
                            </div>
                            <input type="range" class="slider" id="slider-social" min="0" max="100" value="50">
                            <div class="slider-labels"><span>Introvert</span><span>Ambivert</span><span>Extrovert</span></div>
                        </div>
                        
                        <div class="slider-group">
                            <div class="slider-header">
                                <span class="slider-label">Romance Style</span>
                                <span class="slider-value" id="val-romance">Balanced</span>
                            </div>
                            <input type="range" class="slider" id="slider-romance" min="0" max="100" value="50">
                            <div class="slider-labels"><span>Subtle</span><span>Balanced</span><span>Passionate</span></div>
                        </div>
                        
                        <div class="slider-group">
                            <div class="slider-header">
                                <span class="slider-label">Humor</span>
                                <span class="slider-value" id="val-humor">Balanced</span>
                            </div>
                            <input type="range" class="slider" id="slider-humor" min="0" max="100" value="50">
                            <div class="slider-labels"><span>Dry</span><span>Balanced</span><span>Playful</span></div>
                        </div>
                    </div>
                </div>
                
                <!-- REFERENCE MODE -->
                <div class="mode-content" id="mode-reference">
                    <div class="form-section">
                        <h3 class="section-title">Upload Reference Images</h3>
                        <p style="color:var(--text-muted);margin-bottom:20px;font-size:14px;">
                            Upload 1-4 images showing how you'd like your companion to look.
                        </p>
                        
                        <div class="upload-zone" id="upload-zone">
                            <input type="file" id="ref-upload" accept="image/*" multiple>
                            <div class="upload-icon">📷</div>
                            <p class="upload-text">
                                <strong>Click to upload</strong> or drag and drop<br>
                                <small>JPG, PNG, WebP up to 10MB each</small>
                            </p>
                        </div>
                        
                        <div class="reference-preview" id="reference-preview"></div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Additional Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" id="ref-name" placeholder="Companion's name">
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select id="ref-gender">
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="nonbinary">Non-binary</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>What to capture from references</label>
                            <textarea id="ref-notes" rows="3" placeholder="What aspects to focus on?"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Personality</h3>
                        <div class="form-group">
                            <label>Personality Description</label>
                            <textarea id="ref-personality" placeholder="Describe their personality..."></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- JACLYN MODE - Baldoni Ultra-Realistic -->
                <div class="mode-content" id="mode-jaclyn">
                    <div style="background:linear-gradient(135deg, rgba(236,72,153,0.1), rgba(99,102,241,0.1)); border:1px solid rgba(236,72,153,0.3); border-radius:12px; padding:20px; margin-bottom:24px;">
                        <h3 style="margin:0 0 8px; color:#ec4899;">✨ Jaclyn Mode — Baldoni-Style Realism</h3>
                        <p style="margin:0; color:var(--text-muted); font-size:14px;">
                            Creates ultra-realistic portraits that look like real candid photographs. Describe freely - no limits. Uses 3 SDXL inputs: core prompt, negative prompt, and micro-variants.
                        </p>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">👤 Core Description</h3>
                        <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">
                            Describe the person in detail. Be as specific or general as you want - age, gender, ethnicity, features, expression, clothing, setting, mood. The more detail, the better.
                        </p>
                        <div class="form-group">
                            <textarea id="jaclyn-core-prompt" rows="6" placeholder="Example: Adult woman, age 28. East Asian descent with warm undertones. Naturally beautiful without looking like a model. Soft brown eyes with subtle tiredness, genuine warmth in her expression. Black hair, shoulder-length, slightly messy from the wind, a few strands across her face. Light freckles across her nose. Wearing an oversized cream sweater, sleeves pushed up. Sitting by a window in a coffee shop, morning light catching dust particles in the air. Relaxed, authentic, like a candid photo a friend took."></textarea>
                        </div>
                        
                        <div style="margin-top:12px;">
                            <button type="button" id="toggle-core-suggestions" style="background:var(--bg-elevated);border:1px solid var(--border);color:var(--text-muted);padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;">
                                ▼ Show Writing Tips
                            </button>
                        </div>
                        
                        <div id="core-suggestions" style="display:none;margin-top:16px;padding:16px;background:var(--bg-input);border-radius:10px;font-size:13px;color:var(--text-muted);">
                            <p style="margin-bottom:12px;color:var(--text);font-weight:600;">Tips for Baldoni-style realism:</p>
                            <ul style="margin:0;padding-left:20px;line-height:1.8;">
                                <li><strong>Start with basics:</strong> age, gender, ethnicity/descent</li>
                                <li><strong>Emphasize "real person" language:</strong> "naturally attractive without looking like a model", "like someone you'd see on the street"</li>
                                <li><strong>Describe imperfections:</strong> asymmetry, natural skin texture, visible pores</li>
                                <li><strong>Be specific about features:</strong> eye shape, nose, jawline, but note natural variation</li>
                                <li><strong>Include subtle details:</strong> how light hits their face, expression nuances</li>
                                <li><strong>Describe clothing naturally:</strong> "soft fabric with natural folds", "slightly wrinkled"</li>
                                <li><strong>Set the scene:</strong> lighting, environment, time of day</li>
                                <li><strong>End with the vibe:</strong> "candid and unposed", "like a real photograph"</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">🎛️ Micro-Variants <span class="label-hint">Adds realistic imperfections</span></h3>
                        <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">
                            Add subtle variations to make the image feel like a real candid photo. Type your own or click suggestions below.
                        </p>
                        
                        <div class="form-group">
                            <textarea id="jaclyn-variants-text" rows="2" placeholder="e.g., slightly tired eyes, end of day look, subtle smile lines..."></textarea>
                        </div>
                        
                        <div style="margin-bottom:12px;">
                            <button type="button" id="toggle-suggestions" style="background:var(--bg-elevated);border:1px solid var(--border);color:var(--text-muted);padding:8px 16px;border-radius:6px;font-size:13px;cursor:pointer;">
                                ▼ Show Suggestions
                            </button>
                        </div>
                        
                        <div id="variant-suggestions" style="display:none;">
                            <p style="font-size:12px;color:var(--text-dim);margin-bottom:10px;">Click to add:</p>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">EYES & EXPRESSION</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="slightly tired eyes">Tired Eyes</div>
                                <div class="trait-chip variant-chip" data-value="sleepy eyes">Sleepy Eyes</div>
                                <div class="trait-chip variant-chip" data-value="bright alert eyes">Bright Alert Eyes</div>
                                <div class="trait-chip variant-chip" data-value="soft gaze">Soft Gaze</div>
                                <div class="trait-chip variant-chip" data-value="intense stare">Intense Stare</div>
                                <div class="trait-chip variant-chip" data-value="looking away naturally">Looking Away</div>
                                <div class="trait-chip variant-chip" data-value="caught mid-blink">Mid-Blink</div>
                                <div class="trait-chip variant-chip" data-value="slight squint">Slight Squint</div>
                                <div class="trait-chip variant-chip" data-value="one eye slightly more closed">Asymmetric Eyes</div>
                                <div class="trait-chip variant-chip" data-value="visible eye veins">Visible Eye Veins</div>
                                <div class="trait-chip variant-chip" data-value="watery eyes">Watery Eyes</div>
                                <div class="trait-chip variant-chip" data-value="crow's feet when smiling">Crow's Feet</div>
                                <div class="trait-chip variant-chip" data-value="under-eye circles">Under-Eye Circles</div>
                                <div class="trait-chip variant-chip" data-value="puffy under-eyes">Puffy Under-Eyes</div>
                                <div class="trait-chip variant-chip" data-value="natural eye bags">Natural Eye Bags</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">SMILE & MOUTH</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="subtle smile">Subtle Smile</div>
                                <div class="trait-chip variant-chip" data-value="half smile">Half Smile</div>
                                <div class="trait-chip variant-chip" data-value="smirk">Smirk</div>
                                <div class="trait-chip variant-chip" data-value="genuine laugh lines">Laugh Lines</div>
                                <div class="trait-chip variant-chip" data-value="lips slightly parted">Lips Parted</div>
                                <div class="trait-chip variant-chip" data-value="slight overbite visible">Slight Overbite</div>
                                <div class="trait-chip variant-chip" data-value="crooked smile">Crooked Smile</div>
                                <div class="trait-chip variant-chip" data-value="teeth showing naturally">Teeth Showing</div>
                                <div class="trait-chip variant-chip" data-value="chapped lips">Chapped Lips</div>
                                <div class="trait-chip variant-chip" data-value="natural lip color variation">Natural Lip Color</div>
                                <div class="trait-chip variant-chip" data-value="dimples when smiling">Dimples</div>
                                <div class="trait-chip variant-chip" data-value="resting face">Resting Face</div>
                                <div class="trait-chip variant-chip" data-value="caught mid-thought">Mid-Thought</div>
                                <div class="trait-chip variant-chip" data-value="about to speak">About to Speak</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">SKIN & FACE</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="visible pores">Visible Pores</div>
                                <div class="trait-chip variant-chip" data-value="slight redness on cheeks">Flushed Cheeks</div>
                                <div class="trait-chip variant-chip" data-value="redness around nose">Red Nose</div>
                                <div class="trait-chip variant-chip" data-value="natural freckles">Freckles</div>
                                <div class="trait-chip variant-chip" data-value="sun spots">Sun Spots</div>
                                <div class="trait-chip variant-chip" data-value="minor blemish">Minor Blemish</div>
                                <div class="trait-chip variant-chip" data-value="acne scars">Acne Scars</div>
                                <div class="trait-chip variant-chip" data-value="small mole">Small Mole</div>
                                <div class="trait-chip variant-chip" data-value="beauty mark">Beauty Mark</div>
                                <div class="trait-chip variant-chip" data-value="slight sunburn">Slight Sunburn</div>
                                <div class="trait-chip variant-chip" data-value="windburned cheeks">Windburned</div>
                                <div class="trait-chip variant-chip" data-value="oily skin shine">Oily Shine</div>
                                <div class="trait-chip variant-chip" data-value="dry skin texture">Dry Skin</div>
                                <div class="trait-chip variant-chip" data-value="forehead lines">Forehead Lines</div>
                                <div class="trait-chip variant-chip" data-value="expression lines">Expression Lines</div>
                                <div class="trait-chip variant-chip" data-value="nasolabial folds">Nasolabial Folds</div>
                                <div class="trait-chip variant-chip" data-value="asymmetrical features">Asymmetrical</div>
                                <div class="trait-chip variant-chip" data-value="slightly crooked nose">Crooked Nose</div>
                                <div class="trait-chip variant-chip" data-value="ears slightly uneven">Uneven Ears</div>
                                <div class="trait-chip variant-chip" data-value="scar on face">Facial Scar</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">FACIAL HAIR</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="light stubble">Light Stubble</div>
                                <div class="trait-chip variant-chip" data-value="5 o'clock shadow">5 O'Clock Shadow</div>
                                <div class="trait-chip variant-chip" data-value="patchy stubble">Patchy Stubble</div>
                                <div class="trait-chip variant-chip" data-value="slightly unshaven">Unshaven</div>
                                <div class="trait-chip variant-chip" data-value="stubble with gray hairs">Gray Stubble</div>
                                <div class="trait-chip variant-chip" data-value="clean shaven with razor bump">Razor Bump</div>
                                <div class="trait-chip variant-chip" data-value="peach fuzz visible">Peach Fuzz</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">HAIR</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="wind-touched hair">Windswept</div>
                                <div class="trait-chip variant-chip" data-value="messy bedhead">Bedhead</div>
                                <div class="trait-chip variant-chip" data-value="flyaway hairs">Flyaways</div>
                                <div class="trait-chip variant-chip" data-value="hair tucked behind ear">Tucked Behind Ear</div>
                                <div class="trait-chip variant-chip" data-value="strand across face">Strand Across Face</div>
                                <div class="trait-chip variant-chip" data-value="slightly frizzy">Slightly Frizzy</div>
                                <div class="trait-chip variant-chip" data-value="visible roots">Visible Roots</div>
                                <div class="trait-chip variant-chip" data-value="sun-lightened ends">Sun-Lightened</div>
                                <div class="trait-chip variant-chip" data-value="hat hair">Hat Hair</div>
                                <div class="trait-chip variant-chip" data-value="sweaty hairline">Sweaty Hairline</div>
                                <div class="trait-chip variant-chip" data-value="receding hairline">Receding</div>
                                <div class="trait-chip variant-chip" data-value="thinning crown">Thinning Crown</div>
                                <div class="trait-chip variant-chip" data-value="gray at temples">Gray Temples</div>
                                <div class="trait-chip variant-chip" data-value="natural gray streaks">Gray Streaks</div>
                                <div class="trait-chip variant-chip" data-value="baby hairs at forehead">Baby Hairs</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">TIME OF DAY / ENERGY</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="early morning look">Early Morning</div>
                                <div class="trait-chip variant-chip" data-value="end of day look">End of Day</div>
                                <div class="trait-chip variant-chip" data-value="just woke up">Just Woke Up</div>
                                <div class="trait-chip variant-chip" data-value="post-workout glow">Post-Workout</div>
                                <div class="trait-chip variant-chip" data-value="fresh from shower">Fresh Shower</div>
                                <div class="trait-chip variant-chip" data-value="relaxed weekend vibe">Weekend Vibe</div>
                                <div class="trait-chip variant-chip" data-value="work day tired">Work Tired</div>
                                <div class="trait-chip variant-chip" data-value="energized and alert">Energized</div>
                                <div class="trait-chip variant-chip" data-value="slightly hungover">Slightly Hungover</div>
                                <div class="trait-chip variant-chip" data-value="well-rested">Well-Rested</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">LIGHTING & ENVIRONMENT</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="golden hour glow">Golden Hour</div>
                                <div class="trait-chip variant-chip" data-value="morning light">Morning Light</div>
                                <div class="trait-chip variant-chip" data-value="overcast soft light">Overcast Light</div>
                                <div class="trait-chip variant-chip" data-value="dappled sunlight">Dappled Sun</div>
                                <div class="trait-chip variant-chip" data-value="harsh midday sun">Midday Sun</div>
                                <div class="trait-chip variant-chip" data-value="backlit silhouette edge">Backlit</div>
                                <div class="trait-chip variant-chip" data-value="warm indoor lighting">Warm Indoor</div>
                                <div class="trait-chip variant-chip" data-value="cool blue hour">Blue Hour</div>
                                <div class="trait-chip variant-chip" data-value="neon reflection on face">Neon Glow</div>
                                <div class="trait-chip variant-chip" data-value="candle lit">Candlelit</div>
                                <div class="trait-chip variant-chip" data-value="screen glow on face">Screen Glow</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">POSE & BODY LANGUAGE</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="head slightly tilted">Head Tilted</div>
                                <div class="trait-chip variant-chip" data-value="chin resting on hand">Chin on Hand</div>
                                <div class="trait-chip variant-chip" data-value="leaning forward slightly">Leaning Forward</div>
                                <div class="trait-chip variant-chip" data-value="relaxed slouch">Relaxed Slouch</div>
                                <div class="trait-chip variant-chip" data-value="arms crossed casually">Arms Crossed</div>
                                <div class="trait-chip variant-chip" data-value="hand touching face">Touching Face</div>
                                <div class="trait-chip variant-chip" data-value="running hand through hair">Hand in Hair</div>
                                <div class="trait-chip variant-chip" data-value="mid-gesture frozen">Mid-Gesture</div>
                                <div class="trait-chip variant-chip" data-value="shoulders slightly raised">Shoulders Raised</div>
                                <div class="trait-chip variant-chip" data-value="head turned 3/4 view">3/4 View</div>
                                <div class="trait-chip variant-chip" data-value="looking over shoulder">Over Shoulder</div>
                                <div class="trait-chip variant-chip" data-value="caught off guard">Caught Off Guard</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">CLOTHING DETAILS</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="collar slightly askew">Askew Collar</div>
                                <div class="trait-chip variant-chip" data-value="sleeves rolled up">Rolled Sleeves</div>
                                <div class="trait-chip variant-chip" data-value="top button undone">Button Undone</div>
                                <div class="trait-chip variant-chip" data-value="wrinkled shirt">Wrinkled</div>
                                <div class="trait-chip variant-chip" data-value="faded worn fabric">Faded Fabric</div>
                                <div class="trait-chip variant-chip" data-value="coffee stain barely visible">Coffee Stain</div>
                                <div class="trait-chip variant-chip" data-value="lint on clothes">Lint Visible</div>
                                <div class="trait-chip variant-chip" data-value="sweater slightly stretched">Stretched Sweater</div>
                            </div>
                            
                            <p style="font-size:11px;color:var(--accent);margin:12px 0 6px;font-weight:600;">ACCESSORIES & DETAILS</p>
                            <div class="traits-grid" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                                <div class="trait-chip variant-chip" data-value="glasses with smudges">Smudged Glasses</div>
                                <div class="trait-chip variant-chip" data-value="watch tan line">Watch Tan Line</div>
                                <div class="trait-chip variant-chip" data-value="ring indent on finger">Ring Indent</div>
                                <div class="trait-chip variant-chip" data-value="earring hole visible">Earring Hole</div>
                                <div class="trait-chip variant-chip" data-value="necklace tangled">Tangled Necklace</div>
                                <div class="trait-chip variant-chip" data-value="chipped nail polish">Chipped Polish</div>
                                <div class="trait-chip variant-chip" data-value="bitten nails">Bitten Nails</div>
                                <div class="trait-chip variant-chip" data-value="veins visible on hands">Hand Veins</div>
                                <div class="trait-chip variant-chip" data-value="callused fingers">Callused Fingers</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">🚫 Negative Prompt <span class="label-hint">What to avoid</span></h3>
                        <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">
                            Defaults are pre-filled for Baldoni-style. Add more or edit as needed.
                        </p>
                        <div class="form-group">
                            <textarea id="jaclyn-negative" rows="3" placeholder="illustration, painting, CGI, 3D render, anime, stylized, beauty campaign, fashion editorial, cinematic lighting, studio portrait, glam lighting, airbrushed skin, plastic skin, perfect symmetry, doll-like face, influencer aesthetic, exaggerated muscles, unrealistic proportions, hyper-sharp, over-processed, magazine cover"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">📷 Reference Image <span class="label-hint">Optional — for img2img</span></h3>
                        <p style="color:var(--text-muted);font-size:13px;margin-bottom:16px;">
                            Upload a reference to preserve identity/likeness. Lower denoise = closer to original.
                        </p>
                        
                        <div class="upload-zone" id="jaclyn-upload-zone">
                            <input type="file" id="jaclyn-ref-upload" accept="image/*">
                            <div class="upload-icon">📷</div>
                            <p class="upload-text">
                                <strong>Click to upload reference</strong><br>
                                <small>JPG, PNG, WebP</small>
                            </p>
                        </div>
                        
                        <div id="jaclyn-ref-preview" style="margin-top:16px;"></div>
                        
                        <div id="denoise-controls" style="display:none;">
                            <div class="slider-group" style="margin-top:20px;">
                                <div class="slider-header">
                                    <span class="slider-label">Denoise Strength</span>
                                    <span class="slider-value" id="val-denoise">0.30</span>
                                </div>
                                <input type="range" class="slider" id="jaclyn-denoise" min="10" max="50" value="30">
                                <div class="slider-labels">
                                    <span>0.1 (preserve identity)</span>
                                    <span>0.5 (more variation)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">Companion Name</h3>
                        <div class="form-group">
                            <input type="text" id="jaclyn-name" placeholder="What should they be called?">
                        </div>
                    </div>
                    
                    <!-- ADULT/SPICY SECTION -->
                    <div class="form-section" style="border:2px solid #ef4444;border-radius:16px;padding:24px;margin-top:30px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                            <h3 style="margin:0;color:#ef4444;display:flex;align-items:center;gap:10px;">
                                🔥 Adult Mode <span style="font-size:12px;background:#ef4444;color:white;padding:2px 8px;border-radius:4px;">18+</span>
                            </h3>
                            <label class="toggle-switch">
                                <input type="checkbox" id="adult-mode-toggle">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <p style="color:var(--text-muted);font-size:13px;margin-bottom:20px;">
                            Enable to access body customization sliders and suggestive/explicit content options.
                            Uses NSFW-capable models (Flux via Replicate).
                        </p>
                        
                        <div id="adult-content" style="display:none;">
                            <!-- Age verification -->
                            <div id="age-gate" style="text-align:center;padding:30px;background:var(--bg-input);border-radius:12px;margin-bottom:24px;">
                                <p style="font-size:18px;margin-bottom:16px;">⚠️ You must be 18+ to access adult content</p>
                                <button type="button" id="confirm-age-btn" style="padding:12px 32px;background:#ef4444;border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">
                                    I confirm I am 18 or older
                                </button>
                            </div>
                            
                            <div id="adult-controls" style="display:none;">
                                <!-- Female Body Sliders -->
                                <div id="female-sliders" class="gender-sliders">
                                    <p style="font-size:12px;color:var(--accent);margin-bottom:16px;font-weight:600;">FEMALE BODY</p>
                                    
                                    <div class="slider-group">
                                        <div class="slider-header">
                                            <span class="slider-label">Breast Size</span>
                                            <span class="slider-value" id="val-breast-size">Medium</span>
                                        </div>
                                        <input type="range" class="slider adult-slider" id="slider-breast-size" min="0" max="100" value="50" data-labels="Flat,Small,Medium,Large,Very Large">
                                        <div class="slider-labels"><span>Flat</span><span>Medium</span><span>Very Large</span></div>
                                    </div>
                                    
                                    <div class="slider-group">
                                        <div class="slider-header">
                                            <span class="slider-label">Breast Shape</span>
                                            <span class="slider-value" id="val-breast-shape">Natural</span>
                                        </div>
                                        <input type="range" class="slider adult-slider" id="slider-breast-shape" min="0" max="100" value="33" data-labels="Natural/Teardrop,Round,Perky">
                                        <div class="slider-labels"><span>Natural</span><span>Round</span><span>Perky</span></div>
                                    </div>
                                    
                                    <div class="slider-group">
                                        <div class="slider-header">
                                            <span class="slider-label">Hip/Waist Ratio</span>
                                            <span class="slider-value" id="val-hip-ratio">Curvy</span>
                                        </div>
                                        <input type="range" class="slider adult-slider" id="slider-hip-ratio" min="0" max="100" value="50" data-labels="Slim,Moderate,Curvy,Very Curvy">
                                        <div class="slider-labels"><span>Slim</span><span>Curvy</span><span>Very Curvy</span></div>
                                    </div>
                                    
                                    <div class="slider-group">
                                        <div class="slider-header">
                                            <span class="slider-label">Butt Size</span>
                                            <span class="slider-value" id="val-butt-size">Round</span>
                                        </div>
                                        <input type="range" class="slider adult-slider" id="slider-butt-size" min="0" max="100" value="50" data-labels="Flat,Athletic,Round,Thicc">
                                        <div class="slider-labels"><span>Flat</span><span>Round</span><span>Thicc</span></div>
                                    </div>
                                </div>
                                
                                <!-- Male Body Sliders -->
                                <div id="male-sliders" class="gender-sliders" style="display:none;">
                                    <p style="font-size:12px;color:var(--accent);margin-bottom:16px;font-weight:600;">MALE BODY</p>
                                    
                                    <div class="slider-group">
                                        <div class="slider-header">
                                            <span class="slider-label">Package Size</span>
                                            <span class="slider-value" id="val-package-size">Average</span>
                                        </div>
                                        <input type="range" class="slider adult-slider" id="slider-package-size" min="0" max="100" value="50" data-labels="Modest,Average,Large,Very Large">
                                        <div class="slider-labels"><span>Modest</span><span>Average</span><span>Very Large</span></div>
                                    </div>
                                    
                                    <div class="slider-group">
                                        <div class="slider-header">
                                            <span class="slider-label">Chest/Pecs</span>
                                            <span class="slider-value" id="val-chest-size">Defined</span>
                                        </div>
                                        <input type="range" class="slider adult-slider" id="slider-chest-size" min="0" max="100" value="50" data-labels="Flat,Toned,Defined,Muscular,Massive">
                                        <div class="slider-labels"><span>Flat</span><span>Defined</span><span>Massive</span></div>
                                    </div>
                                    
                                    <div class="slider-group">
                                        <div class="slider-header">
                                            <span class="slider-label">V-Taper (Shoulders to Waist)</span>
                                            <span class="slider-value" id="val-vtaper">Moderate</span>
                                        </div>
                                        <input type="range" class="slider adult-slider" id="slider-vtaper" min="0" max="100" value="50" data-labels="Slim,Moderate,Athletic,Dramatic">
                                        <div class="slider-labels"><span>Slim</span><span>Moderate</span><span>Dramatic</span></div>
                                    </div>
                                </div>
                                
                                <!-- Universal Sliders -->
                                <p style="font-size:12px;color:var(--accent);margin:24px 0 16px;font-weight:600;">BODY COMPOSITION</p>
                                
                                <div class="slider-group">
                                    <div class="slider-header">
                                        <span class="slider-label">Body Fat %</span>
                                        <span class="slider-value" id="val-body-fat">Toned</span>
                                    </div>
                                    <input type="range" class="slider adult-slider" id="slider-body-fat" min="0" max="100" value="33" data-labels="Very Lean,Toned,Soft,Curvy/Thick">
                                    <div class="slider-labels"><span>Very Lean</span><span>Toned</span><span>Curvy</span></div>
                                </div>
                                
                                <div class="slider-group">
                                    <div class="slider-header">
                                        <span class="slider-label">Muscle Definition</span>
                                        <span class="slider-value" id="val-muscle-def">Toned</span>
                                    </div>
                                    <input type="range" class="slider adult-slider" id="slider-muscle-def" min="0" max="100" value="40" data-labels="Soft,Toned,Athletic,Muscular,Bodybuilder">
                                    <div class="slider-labels"><span>Soft</span><span>Athletic</span><span>Bodybuilder</span></div>
                                </div>
                                
                                <!-- Clothing/State -->
                                <p style="font-size:12px;color:var(--accent);margin:24px 0 16px;font-weight:600;">CLOTHING STATE</p>
                                
                                <div class="slider-group">
                                    <div class="slider-header">
                                        <span class="slider-label">Clothing Level</span>
                                        <span class="slider-value" id="val-clothing-state">Suggestive</span>
                                    </div>
                                    <input type="range" class="slider adult-slider" id="slider-clothing-state" min="0" max="100" value="25" data-labels="Fully Clothed,Suggestive,Lingerie/Underwear,Topless,Nude">
                                    <div class="slider-labels"><span>Clothed</span><span>Lingerie</span><span>Nude</span></div>
                                </div>
                                
                                <!-- Clothing Details -->
                                <div class="form-group" style="margin-top:16px;">
                                    <label>Clothing/Outfit Details <span class="label-hint">Optional specifics</span></label>
                                    <input type="text" id="adult-clothing-details" placeholder="e.g., black lace, wet t-shirt, silk robe, leather...">
                                </div>
                                
                                <!-- Pose -->
                                <p style="font-size:12px;color:var(--accent);margin:24px 0 16px;font-weight:600;">POSE & MOOD</p>
                                
                                <div class="slider-group">
                                    <div class="slider-header">
                                        <span class="slider-label">Pose Intensity</span>
                                        <span class="slider-value" id="val-pose">Flirty</span>
                                    </div>
                                    <input type="range" class="slider adult-slider" id="slider-pose" min="0" max="100" value="25" data-labels="Casual,Flirty,Seductive,Intimate,Explicit">
                                    <div class="slider-labels"><span>Casual</span><span>Seductive</span><span>Explicit</span></div>
                                </div>
                                
                                <div class="form-group" style="margin-top:16px;">
                                    <label>Pose Description <span class="label-hint">Optional specifics</span></label>
                                    <input type="text" id="adult-pose-details" placeholder="e.g., lying on bed, standing in doorway, looking over shoulder...">
                                </div>
                                
                                <!-- Special Effects -->
                                <p style="font-size:12px;color:var(--accent);margin:24px 0 16px;font-weight:600;">SPECIAL EFFECTS</p>
                                <div class="traits-grid" data-field="adult-effects" data-multi="true" style="grid-template-columns:repeat(auto-fill,minmax(100px,1fr));">
                                    <div class="trait-chip" data-value="wet skin">Wet Skin</div>
                                    <div class="trait-chip" data-value="oiled skin">Oiled</div>
                                    <div class="trait-chip" data-value="sweaty">Sweaty</div>
                                    <div class="trait-chip" data-value="sheer fabric">Sheer Fabric</div>
                                    <div class="trait-chip" data-value="see-through">See-Through</div>
                                    <div class="trait-chip" data-value="backlit silhouette">Backlit</div>
                                    <div class="trait-chip" data-value="steam/fog">Steam/Fog</div>
                                    <div class="trait-chip" data-value="candlelight">Candlelight</div>
                                    <div class="trait-chip" data-value="neon glow">Neon Glow</div>
                                    <div class="trait-chip" data-value="boudoir lighting">Boudoir</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Personality & Backstory (all modes) -->
                <div class="form-section" style="margin-top:30px;padding-top:30px;border-top:1px solid var(--border)">
                    <h3 class="section-title">Personality & Background <span class="label-hint">Recommended</span></h3>
                    <div class="form-group">
                        <label>Personality Traits</label>
                        <input type="text" id="personality-input" placeholder="e.g., playful, caring, witty, adventurous, romantic">
                    </div>
                    <div class="form-group">
                        <label>Speaking Style</label>
                        <input type="text" id="speaking-style-input" placeholder="e.g., casual and flirty, intellectual, uses emojis">
                    </div>
                    <div class="form-group">
                        <label>Backstory</label>
                        <textarea id="backstory-input" rows="3" placeholder="Tell us about their background, interests, dreams, what makes them unique..."></textarea>
                    </div>
                </div>
                
                <!-- User Info (all modes) -->
                <div class="form-section" style="margin-top:30px;padding-top:30px;border-top:1px solid var(--border)">
                    <h3 class="section-title">Your Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Your Email</label>
                            <input type="email" id="user-email" placeholder="We'll notify you when ready">
                        </div>
                        <div class="form-group">
                            <label>Your Name <span class="label-hint">Optional</span></label>
                            <input type="text" id="user-name" placeholder="So they know what to call you">
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Preview Panel -->
            <div class="preview-panel">
                <div class="preview-card">
                    <div class="preview-image" id="preview-image">
                        <div class="preview-placeholder">
                            <div class="icon">🎨</div>
                            <p>Your companion preview will appear here</p>
                        </div>
                    </div>
                    
                    <div class="preview-info">
                        <div class="preview-name" id="preview-name">Your Companion</div>
                        <div style="color:var(--text-muted);font-size:14px;" id="preview-desc">
                            Fill in details to see your companion come to life
                        </div>
                        <div class="preview-traits" id="preview-traits"></div>
                    </div>
                    
                    <div class="model-selector">
                        <button class="model-btn active" data-model="sdxl" title="Stable Diffusion XL">SDXL</button>
                        <button class="model-btn" data-model="dalle" title="DALL-E 3">DALL-E</button>
                        <button class="model-btn" data-model="flux" title="Flux Pro">Flux</button>
                    </div>
                    
                    <div style="padding:0 16px 16px;">
                        <button class="generate-btn" id="generate-btn">✨ Generate Preview Image</button>
                    </div>
                </div>
                
                <div class="order-summary">
                    <h3 style="margin-bottom:16px;font-size:18px;">Order Summary</h3>
                    <div class="summary-row">
                        <span class="summary-label">Custom Companion</span>
                        <span class="summary-value">$199</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">AI Profile Image</span>
                        <span class="summary-value">Included</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Unlimited Conversations</span>
                        <span class="summary-value">Included</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Total</span>
                        <span class="summary-value summary-total">$199</span>
                    </div>
                    <button class="submit-btn" id="submit-btn">Create My Companion — $199</button>
                    <p style="text-align:center;font-size:12px;color:var(--text-dim);margin-top:12px;">
                        Secure payment • 24-48 hour delivery
                    </p>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p><a href="terms.php">Terms</a> · <a href="privacy.php">Privacy</a> · © 2025 AI Companions</p>
        </div>
    </footer>
    
    <div class="toast" id="toast"></div>
    
    <script>
    const state = {
        mode: 'prompt',
        model: 'sdxl',
        generatedImage: null,
        referenceImages: [],
        quizStep: 0,
        quizAnswers: {},
        visualSelections: {},
        sliderValues: {},
        jaclynVariants: [],
        jaclynReference: null
    };
    
    const quizQuestions = [
        {
            id: 'gender',
            question: 'Who would you like to meet?',
            subtext: 'Choose the gender of your ideal companion',
            options: [
                { value: 'female', emoji: '👩', title: 'A Woman', desc: 'Feminine companion' },
                { value: 'male', emoji: '👨', title: 'A Man', desc: 'Masculine companion' },
                { value: 'nonbinary', emoji: '🧑', title: 'Non-binary', desc: 'Gender non-conforming' }
            ]
        },
        {
            id: 'vibe',
            question: "What's their overall vibe?",
            subtext: 'Pick the energy that appeals to you',
            options: [
                { value: 'warm', emoji: '☀️', title: 'Warm & Nurturing', desc: 'Caring, supportive' },
                { value: 'playful', emoji: '🎉', title: 'Playful & Fun', desc: 'Energetic, witty' },
                { value: 'intellectual', emoji: '📚', title: 'Deep & Intellectual', desc: 'Thoughtful, curious' },
                { value: 'mysterious', emoji: '🌙', title: 'Mysterious', desc: 'Intriguing, captivating' }
            ]
        },
        {
            id: 'appearance',
            question: 'What look catches your eye?',
            subtext: 'Choose their general appearance',
            options: [
                { value: 'classic', emoji: '✨', title: 'Classic Beauty', desc: 'Timeless, elegant' },
                { value: 'edgy', emoji: '🖤', title: 'Edgy & Alternative', desc: 'Bold, unique' },
                { value: 'natural', emoji: '🌿', title: 'Natural & Fresh', desc: 'Effortless, approachable' },
                { value: 'glamorous', emoji: '💎', title: 'Glamorous', desc: 'Polished, stunning' }
            ]
        },
        {
            id: 'communication',
            question: 'How should they communicate?',
            subtext: 'Pick their conversation style',
            options: [
                { value: 'flirty', emoji: '😏', title: 'Flirty & Teasing', desc: 'Playful banter' },
                { value: 'sincere', emoji: '💝', title: 'Sincere & Heartfelt', desc: 'Genuine, open' },
                { value: 'witty', emoji: '🎭', title: 'Witty & Sharp', desc: 'Quick humor' },
                { value: 'gentle', emoji: '🕊️', title: 'Gentle & Soothing', desc: 'Calm, patient' }
            ]
        },
        {
            id: 'name',
            question: 'What should they be called?',
            subtext: 'Give your companion a name',
            type: 'text',
            placeholder: 'Enter a name...'
        }
    ];
    
    // Mode switching
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.mode = btn.dataset.mode;
            document.querySelectorAll('.mode-content').forEach(c => c.classList.remove('active'));
            document.getElementById('mode-' + state.mode).classList.add('active');
            if (state.mode === 'quiz') initQuiz();
        });
    });
    
    // Model switching
    document.querySelectorAll('.model-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.model-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.model = btn.dataset.model;
        });
    });
    
    // Visual mode selections
    document.querySelectorAll('.option-grid, .traits-grid').forEach(grid => {
        const field = grid.dataset.field;
        const isMulti = grid.dataset.multi === 'true';
        
        grid.querySelectorAll('.option-card, .trait-chip').forEach(card => {
            card.addEventListener('click', () => {
                if (isMulti) {
                    card.classList.toggle('selected');
                    const selected = Array.from(grid.querySelectorAll('.selected')).map(c => c.dataset.value);
                    state.visualSelections[field] = selected;
                } else {
                    grid.querySelectorAll('.option-card, .trait-chip').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    state.visualSelections[field] = card.dataset.value;
                }
                updatePreview();
            });
        });
    });
    
    // Sliders
    const sliderConfigs = {
        'slider-age': { display: 'val-age', format: v => v },
        'slider-height': { display: 'val-height', format: v => v < 33 ? 'Petite' : v < 66 ? 'Average' : 'Tall' },
        'slider-build': { display: 'val-build', format: v => v < 33 ? 'Slim' : v < 66 ? 'Athletic' : 'Curvy' },
        'slider-energy': { display: 'val-energy', format: v => v < 33 ? 'Calm' : v < 66 ? 'Balanced' : 'Energetic' },
        'slider-social': { display: 'val-social', format: v => v < 33 ? 'Introvert' : v < 66 ? 'Ambivert' : 'Extrovert' },
        'slider-romance': { display: 'val-romance', format: v => v < 33 ? 'Subtle' : v < 66 ? 'Balanced' : 'Passionate' },
        'slider-humor': { display: 'val-humor', format: v => v < 33 ? 'Dry' : v < 66 ? 'Balanced' : 'Playful' }
    };
    
    Object.entries(sliderConfigs).forEach(([id, config]) => {
        const slider = document.getElementById(id);
        if (slider) {
            slider.addEventListener('input', () => {
                const val = parseInt(slider.value);
                document.getElementById(config.display).textContent = config.format(val);
                state.sliderValues[id] = val;
                updatePreview();
            });
        }
    });
    
    // Jaclyn mode - denoise slider
    const denoiseSlider = document.getElementById('jaclyn-denoise');
    if (denoiseSlider) {
        denoiseSlider.addEventListener('input', () => {
            document.getElementById('val-denoise').textContent = (parseInt(denoiseSlider.value) / 100).toFixed(2);
        });
    }
    
    // Jaclyn mode - toggle buttons for suggestions
    const toggleSuggestions = document.getElementById('toggle-suggestions');
    const variantSuggestions = document.getElementById('variant-suggestions');
    if (toggleSuggestions && variantSuggestions) {
        toggleSuggestions.addEventListener('click', () => {
            const isHidden = variantSuggestions.style.display === 'none';
            variantSuggestions.style.display = isHidden ? 'block' : 'none';
            toggleSuggestions.textContent = isHidden ? '▲ Hide Suggestions' : '▼ Show Suggestions';
        });
    }
    
    const toggleCoreSuggestions = document.getElementById('toggle-core-suggestions');
    const coreSuggestions = document.getElementById('core-suggestions');
    if (toggleCoreSuggestions && coreSuggestions) {
        toggleCoreSuggestions.addEventListener('click', () => {
            const isHidden = coreSuggestions.style.display === 'none';
            coreSuggestions.style.display = isHidden ? 'block' : 'none';
            toggleCoreSuggestions.textContent = isHidden ? '▲ Hide Writing Tips' : '▼ Show Writing Tips';
        });
    }
    
    // Jaclyn mode - clicking variant chips adds to text input
    document.querySelectorAll('.variant-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const textArea = document.getElementById('jaclyn-variants-text');
            const currentText = textArea.value.trim();
            const newValue = chip.dataset.value;
            
            // Check if already added
            if (currentText.toLowerCase().includes(newValue.toLowerCase())) {
                showToast('Already added', 'error');
                return;
            }
            
            // Add to text area
            textArea.value = currentText ? currentText + ', ' + newValue : newValue;
            
            // Visual feedback
            chip.style.background = 'rgba(99, 102, 241, 0.3)';
            chip.style.borderColor = 'var(--accent)';
            setTimeout(() => {
                chip.style.background = '';
                chip.style.borderColor = '';
            }, 500);
        });
    });
    
    // Jaclyn mode - reference upload
    const jaclynUploadZone = document.getElementById('jaclyn-upload-zone');
    const jaclynRefUpload = document.getElementById('jaclyn-ref-upload');
    const jaclynRefPreview = document.getElementById('jaclyn-ref-preview');
    
    if (jaclynUploadZone) {
        ['dragenter', 'dragover'].forEach(e => {
            jaclynUploadZone.addEventListener(e, ev => { ev.preventDefault(); jaclynUploadZone.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(e => {
            jaclynUploadZone.addEventListener(e, ev => { ev.preventDefault(); jaclynUploadZone.classList.remove('dragover'); });
        });
        jaclynUploadZone.addEventListener('drop', e => handleJaclynRef(e.dataTransfer.files[0]));
    }
    
    if (jaclynRefUpload) {
        jaclynRefUpload.addEventListener('change', () => handleJaclynRef(jaclynRefUpload.files[0]));
    }
    
    function handleJaclynRef(file) {
        if (!file || !file.type.startsWith('image/')) {
            showToast('Please upload an image', 'error');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = e => {
            state.jaclynReference = e.target.result.split(',')[1]; // Get base64 without prefix
            jaclynRefPreview.innerHTML = '<div class="ref-thumb" style="width:150px;height:150px;">' +
                '<img src="' + e.target.result + '">' +
                '<button class="ref-remove" onclick="clearJaclynRef()">×</button></div>';
            // Show denoise controls
            document.getElementById('denoise-controls').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
    
    window.clearJaclynRef = function() {
        state.jaclynReference = null;
        jaclynRefPreview.innerHTML = '';
        // Hide denoise controls
        document.getElementById('denoise-controls').style.display = 'none';
    };
    
    // Quiz
    function initQuiz() {
        state.quizStep = 0;
        state.quizAnswers = {};
        renderQuiz();
    }
    
    function renderQuiz() {
        const progress = document.getElementById('quiz-progress');
        const content = document.getElementById('quiz-content');
        const backBtn = document.getElementById('quiz-back');
        const nextBtn = document.getElementById('quiz-next');
        
        progress.innerHTML = quizQuestions.map((_, i) => {
            let cls = 'progress-dot';
            if (i < state.quizStep) cls += ' complete';
            else if (i === state.quizStep) cls += ' current';
            return '<div class="' + cls + '"></div>';
        }).join('');
        
        const q = quizQuestions[state.quizStep];
        
        if (q.type === 'text') {
            content.innerHTML = '<div class="quiz-question"><h3>' + q.question + '</h3><p>' + q.subtext + '</p></div>' +
                '<div class="form-group" style="max-width:400px;margin:0 auto;">' +
                '<input type="text" id="quiz-text-input" placeholder="' + q.placeholder + '" value="' + (state.quizAnswers[q.id] || '') + '" style="font-size:18px;text-align:center;"></div>';
            
            const input = document.getElementById('quiz-text-input');
            input.addEventListener('input', () => {
                state.quizAnswers[q.id] = input.value;
                nextBtn.disabled = !input.value.trim();
            });
            nextBtn.disabled = !state.quizAnswers[q.id];
        } else {
            content.innerHTML = '<div class="quiz-question"><h3>' + q.question + '</h3><p>' + q.subtext + '</p></div>' +
                '<div class="quiz-options">' + q.options.map(opt => 
                    '<div class="quiz-option ' + (state.quizAnswers[q.id] === opt.value ? 'selected' : '') + '" data-value="' + opt.value + '">' +
                    '<div class="emoji">' + opt.emoji + '</div><h4>' + opt.title + '</h4><p>' + opt.desc + '</p></div>'
                ).join('') + '</div>';
            
            content.querySelectorAll('.quiz-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    content.querySelectorAll('.quiz-option').forEach(o => o.classList.remove('selected'));
                    opt.classList.add('selected');
                    state.quizAnswers[q.id] = opt.dataset.value;
                    nextBtn.disabled = false;
                });
            });
            nextBtn.disabled = !state.quizAnswers[q.id];
        }
        
        backBtn.disabled = state.quizStep === 0;
        nextBtn.textContent = state.quizStep === quizQuestions.length - 1 ? 'Finish →' : 'Next →';
    }
    
    document.getElementById('quiz-back').addEventListener('click', () => {
        if (state.quizStep > 0) { state.quizStep--; renderQuiz(); }
    });
    
    document.getElementById('quiz-next').addEventListener('click', () => {
        if (state.quizStep < quizQuestions.length - 1) { state.quizStep++; renderQuiz(); }
        else { showToast('Quiz complete! Generate your companion image.', 'success'); updatePreview(); }
    });
    
    // Reference upload
    const uploadZone = document.getElementById('upload-zone');
    const refUpload = document.getElementById('ref-upload');
    const refPreview = document.getElementById('reference-preview');
    
    ['dragenter', 'dragover'].forEach(e => {
        uploadZone.addEventListener(e, ev => { ev.preventDefault(); uploadZone.classList.add('dragover'); });
    });
    
    ['dragleave', 'drop'].forEach(e => {
        uploadZone.addEventListener(e, ev => { ev.preventDefault(); uploadZone.classList.remove('dragover'); });
    });
    
    uploadZone.addEventListener('drop', e => handleRefFiles(e.dataTransfer.files));
    refUpload.addEventListener('change', () => handleRefFiles(refUpload.files));
    
    function handleRefFiles(files) {
        Array.from(files).forEach(file => {
            if (state.referenceImages.length >= 4) { showToast('Max 4 images', 'error'); return; }
            if (!file.type.startsWith('image/')) { showToast('Only images', 'error'); return; }
            
            const reader = new FileReader();
            reader.onload = e => {
                state.referenceImages.push({ file, dataUrl: e.target.result });
                renderRefPreviews();
            };
            reader.readAsDataURL(file);
        });
    }
    
    function renderRefPreviews() {
        refPreview.innerHTML = state.referenceImages.map((img, i) => 
            '<div class="ref-thumb"><img src="' + img.dataUrl + '"><button class="ref-remove" onclick="removeRef(' + i + ')">×</button></div>'
        ).join('');
    }
    
    window.removeRef = function(index) {
        state.referenceImages.splice(index, 1);
        renderRefPreviews();
    };
    
    // Preview update
    function updatePreview() {
        const nameEl = document.getElementById('preview-name');
        const traitsEl = document.getElementById('preview-traits');
        let name = '', traits = [];
        
        switch (state.mode) {
            case 'prompt': name = document.getElementById('prompt-name')?.value || 'Your Companion'; break;
            case 'visual': name = document.getElementById('visual-name')?.value || 'Your Companion'; traits = state.visualSelections['visual-traits'] || []; break;
            case 'quiz': name = state.quizAnswers['name'] || 'Your Companion'; if (state.quizAnswers['vibe']) traits.push(state.quizAnswers['vibe']); break;
            case 'sliders': name = document.getElementById('slider-name')?.value || 'Your Companion'; break;
            case 'reference': name = document.getElementById('ref-name')?.value || 'Your Companion'; break;
            case 'jaclyn': 
                name = document.getElementById('jaclyn-name')?.value || 'Your Companion'; 
                const vibe = document.getElementById('jaclyn-vibe')?.value;
                if (vibe) traits.push(vibe);
                traits.push('Baldoni-style');
                break;
        }
        
        nameEl.textContent = name;
        traitsEl.innerHTML = traits.map(t => '<span class="trait-tag">' + t + '</span>').join('');
    }
    
    ['prompt-name', 'visual-name', 'slider-name', 'ref-name', 'jaclyn-name'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updatePreview);
    });
    
    // Also update on jaclyn vibe change
    const jaclynVibe = document.getElementById('jaclyn-vibe');
    if (jaclynVibe) jaclynVibe.addEventListener('change', updatePreview);
    
    // ========== ADULT MODE ==========
    const adultModeToggle = document.getElementById('adult-mode-toggle');
    const adultContent = document.getElementById('adult-content');
    const ageGate = document.getElementById('age-gate');
    const adultControls = document.getElementById('adult-controls');
    const confirmAgeBtn = document.getElementById('confirm-age-btn');
    
    let adultModeConfirmed = false;
    let adultEffects = [];
    
    if (adultModeToggle) {
        adultModeToggle.addEventListener('change', () => {
            if (adultModeToggle.checked) {
                adultContent.style.display = 'block';
                if (adultModeConfirmed) {
                    ageGate.style.display = 'none';
                    adultControls.style.display = 'block';
                } else {
                    ageGate.style.display = 'block';
                    adultControls.style.display = 'none';
                }
            } else {
                adultContent.style.display = 'none';
            }
        });
    }
    
    if (confirmAgeBtn) {
        confirmAgeBtn.addEventListener('click', () => {
            adultModeConfirmed = true;
            ageGate.style.display = 'none';
            adultControls.style.display = 'block';
            showToast('Adult mode enabled', 'success');
        });
    }
    
    // Adult slider value displays
    const adultSliderConfigs = {
        'slider-breast-size': { display: 'val-breast-size', labels: ['Flat', 'Small', 'Medium', 'Large', 'Very Large'] },
        'slider-breast-shape': { display: 'val-breast-shape', labels: ['Natural', 'Round', 'Perky'] },
        'slider-hip-ratio': { display: 'val-hip-ratio', labels: ['Slim', 'Moderate', 'Curvy', 'Very Curvy'] },
        'slider-butt-size': { display: 'val-butt-size', labels: ['Flat', 'Athletic', 'Round', 'Thicc'] },
        'slider-package-size': { display: 'val-package-size', labels: ['Modest', 'Average', 'Large', 'Very Large'] },
        'slider-chest-size': { display: 'val-chest-size', labels: ['Flat', 'Toned', 'Defined', 'Muscular', 'Massive'] },
        'slider-vtaper': { display: 'val-vtaper', labels: ['Slim', 'Moderate', 'Athletic', 'Dramatic'] },
        'slider-body-fat': { display: 'val-body-fat', labels: ['Very Lean', 'Toned', 'Soft', 'Curvy'] },
        'slider-muscle-def': { display: 'val-muscle-def', labels: ['Soft', 'Toned', 'Athletic', 'Muscular', 'Bodybuilder'] },
        'slider-clothing-state': { display: 'val-clothing-state', labels: ['Fully Clothed', 'Suggestive', 'Lingerie', 'Topless', 'Nude'] },
        'slider-pose': { display: 'val-pose', labels: ['Casual', 'Flirty', 'Seductive', 'Intimate', 'Explicit'] }
    };
    
    Object.entries(adultSliderConfigs).forEach(([id, config]) => {
        const slider = document.getElementById(id);
        if (slider) {
            slider.addEventListener('input', () => {
                const val = parseInt(slider.value);
                const labelIndex = Math.floor(val / 100 * (config.labels.length - 0.01));
                document.getElementById(config.display).textContent = config.labels[Math.min(labelIndex, config.labels.length - 1)];
            });
        }
    });
    
    // Adult effects chips
    document.querySelectorAll('[data-field="adult-effects"] .trait-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            chip.classList.toggle('selected');
            adultEffects = Array.from(document.querySelectorAll('[data-field="adult-effects"] .trait-chip.selected'))
                .map(c => c.dataset.value);
        });
    });
    
    // Show/hide gender-specific sliders based on description
    // (This is a simple heuristic - could be improved)
    const corePromptEl = document.getElementById('jaclyn-core-prompt');
    if (corePromptEl) {
        corePromptEl.addEventListener('input', () => {
            const text = corePromptEl.value.toLowerCase();
            const isMale = text.includes('man') || text.includes('male') || text.includes(' he ') || text.includes(' his ');
            const isFemale = text.includes('woman') || text.includes('female') || text.includes(' she ') || text.includes(' her ');
            
            const femaleSliders = document.getElementById('female-sliders');
            const maleSliders = document.getElementById('male-sliders');
            
            if (femaleSliders && maleSliders) {
                if (isMale && !isFemale) {
                    femaleSliders.style.display = 'none';
                    maleSliders.style.display = 'block';
                } else if (isFemale && !isMale) {
                    femaleSliders.style.display = 'block';
                    maleSliders.style.display = 'none';
                } else {
                    // Show both or default to female
                    femaleSliders.style.display = 'block';
                    maleSliders.style.display = 'block';
                }
            }
        });
    }
    
    // Image generation
    const generateBtn = document.getElementById('generate-btn');
    const previewImage = document.getElementById('preview-image');
    
    generateBtn.addEventListener('click', async () => {
        generateBtn.disabled = true;
        generateBtn.textContent = '⏳ Generating...';
        
        previewImage.innerHTML = '<div class="preview-loading"><div class="spinner"></div><p>Creating your companion...</p>' +
            (state.mode === 'jaclyn' ? '<p style="font-size:12px;color:var(--text-dim)">Baldoni-style realism takes ~30-60 seconds</p>' : '') +
            '</div>';
        
        // Build request data as object (will send as JSON to bypass mod_security)
        const requestData = {
            action: 'generate_image',
            mode: state.mode,
            model: state.mode === 'jaclyn' ? 'sdxl' : state.model
        };
        
        switch (state.mode) {
            case 'prompt':
                requestData.gender = document.getElementById('prompt-gender')?.value || '';
                requestData.age = document.getElementById('prompt-age')?.value || '';
                requestData.ethnicity = document.getElementById('prompt-ethnicity')?.value || '';
                requestData.custom_appearance = document.getElementById('prompt-appearance')?.value || '';
                break;
            case 'visual':
                requestData.gender = state.visualSelections['visual-gender'] || '';
                requestData.hair_color = state.visualSelections['visual-hair-color'] || '';
                requestData.hair_style = state.visualSelections['visual-hair-style'] || '';
                requestData.eye_color = state.visualSelections['visual-eye-color'] || '';
                requestData.body_type = state.visualSelections['visual-body-type'] || '';
                requestData.style = state.visualSelections['visual-style'] || '';
                break;
            case 'quiz':
                requestData.gender = state.quizAnswers['gender'] || '';
                requestData.style = state.quizAnswers['appearance'] || '';
                break;
            case 'sliders':
                requestData.gender = document.getElementById('slider-gender')?.value || '';
                const age = document.getElementById('slider-age')?.value || 25;
                requestData.age = age < 25 ? 'young' : age < 32 ? 'mid' : 'mature';
                const build = document.getElementById('slider-build')?.value || 50;
                requestData.body_type = build < 33 ? 'slim' : build < 66 ? 'athletic' : 'curvy';
                break;
            case 'reference':
                requestData.gender = document.getElementById('ref-gender')?.value || '';
                requestData.custom_appearance = document.getElementById('ref-notes')?.value || '';
                break;
            case 'jaclyn':
                // Jaclyn mode - 3 text inputs for SDXL Baldoni-style
                requestData.core_prompt = document.getElementById('jaclyn-core-prompt')?.value || '';
                requestData.micro_variants = document.getElementById('jaclyn-variants-text')?.value || '';
                requestData.negative_prompt = document.getElementById('jaclyn-negative')?.value || '';
                
                // Reference image for img2img
                if (state.jaclynReference) {
                    requestData.reference_base64 = state.jaclynReference;
                    requestData.denoise_strength = (parseInt(document.getElementById('jaclyn-denoise')?.value || 30) / 100).toFixed(2);
                }
                
                // Adult mode data
                if (document.getElementById('adult-mode-toggle')?.checked && adultModeConfirmed) {
                    requestData.adult_mode = '1';
                    requestData.breast_size = document.getElementById('val-breast-size')?.textContent || '';
                    requestData.breast_shape = document.getElementById('val-breast-shape')?.textContent || '';
                    requestData.hip_ratio = document.getElementById('val-hip-ratio')?.textContent || '';
                    requestData.butt_size = document.getElementById('val-butt-size')?.textContent || '';
                    requestData.package_size = document.getElementById('val-package-size')?.textContent || '';
                    requestData.chest_size = document.getElementById('val-chest-size')?.textContent || '';
                    requestData.vtaper = document.getElementById('val-vtaper')?.textContent || '';
                    requestData.body_fat = document.getElementById('val-body-fat')?.textContent || '';
                    requestData.muscle_def = document.getElementById('val-muscle-def')?.textContent || '';
                    requestData.clothing_state = document.getElementById('val-clothing-state')?.textContent || '';
                    requestData.clothing_details = document.getElementById('adult-clothing-details')?.value || '';
                    requestData.pose = document.getElementById('val-pose')?.textContent || '';
                    requestData.pose_details = document.getElementById('adult-pose-details')?.value || '';
                    requestData.adult_effects = adultEffects.join(', ');
                }
                break;
        }
        
        try {
            const response = await fetch(window.location.href, { 
                method: 'POST', 
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            });
            
            // Check if response is OK
            if (!response.ok) {
                const text = await response.text();
                console.error('Server response:', text.substring(0, 500));
                throw new Error('Server error: ' + response.status);
            }
            
            const result = await response.json();
            
            if (result.success && result.image) {
                state.generatedImage = result.image;
                if (result.companion_id) {
                    state.companionId = result.companion_id;
                }
                previewImage.innerHTML = '<img src="' + result.image + '" alt="Generated companion">';
                showToast('Image generated!', 'success');
            } else {
                throw new Error(result.error || 'Generation failed');
            }
        } catch (error) {
            console.error('Generation error:', error);
            previewImage.innerHTML = '<div class="preview-placeholder"><div class="icon">⚠️</div><p>' + (error.message || 'Failed') + '</p></div>';
            showToast(error.message || 'Generation failed', 'error');
        }
        
        generateBtn.disabled = false;
        generateBtn.textContent = '✨ Generate Preview Image';
    });
    
    // Also update error handling to show debug info
    async function handleGenerateResponse(response) {
        const result = await response.json();
        
        if (result.success && result.image) {
            state.generatedImage = result.image;
            if (result.companion_id) {
                state.companionId = result.companion_id;
            }
            previewImage.innerHTML = '<img src="' + result.image + '" alt="Generated companion">';
            showToast('Image generated!', 'success');
            return result;
        } else {
            // Show detailed error
            let errorMsg = result.error || 'Generation failed';
            if (result.debug) {
                console.log('Debug info:', result.debug);
            }
            throw new Error(errorMsg);
        }
    }
    
    // Order submission
    const submitBtn = document.getElementById('submit-btn');
    
    submitBtn.addEventListener('click', async () => {
        const email = document.getElementById('user-email')?.value;
        if (!email) { showToast('Please enter email', 'error'); return; }
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
        
        const formData = new FormData();
        formData.append('action', 'submit_order');
        formData.append('mode', state.mode);
        formData.append('email', email);
        formData.append('user_name', document.getElementById('user-name')?.value || '');
        formData.append('generated_image', state.generatedImage || '');
        
        try {
            const response = await fetch(window.location.href, { method: 'POST', body: formData });
            const result = await response.json();
            
            if (result.success) {
                showToast('Order submitted!', 'success');
                submitBtn.textContent = '✓ Order Submitted';
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            showToast(error.message || 'Failed', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create My Companion — $199';
        }
    });
    
    // Toast
    function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast ' + type + ' show';
        setTimeout(() => toast.classList.remove('show'), 4000);
    }
    
    updatePreview();
    </script>
</body>
</html>