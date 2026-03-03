<?php
/**
 * Adult Image Generation Test
 * Tests Fal.ai, Replicate, and Stability APIs for NSFW content
 * 
 * Usage: 
 *   Browser: test-adult-gen.php (shows form)
 *   Direct:  test-adult-gen.php?api=fal&prompt=your+prompt
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// API Keys
$config = [
    'fal_key' => '',
    'replicate_key' => '',
    'stability_key' => 'sk-',
];

// Default test prompt
$defaultPrompt = "Beautiful woman, 25 years old, long brown hair, green eyes, wearing red lingerie, sitting on bed, soft bedroom lighting, photorealistic, intimate pose";
$defaultNegative = "cartoon, anime, illustration, deformed, ugly, bad anatomy, child, minor, underage";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['api'])) {
    header('Content-Type: application/json');
    
    $api = $_POST['api'] ?? $_GET['api'] ?? 'fal';
    $prompt = $_POST['prompt'] ?? $_GET['prompt'] ?? $defaultPrompt;
    $negative = $_POST['negative'] ?? $_GET['negative'] ?? $defaultNegative;
    
    echo json_encode(testApi($api, $prompt, $negative, $config), JSON_PRETTY_PRINT);
    exit;
}

function testApi($api, $prompt, $negative, $config) {
    $startTime = microtime(true);
    $result = ['api' => $api, 'prompt' => $prompt, 'started' => date('Y-m-d H:i:s')];
    
    switch ($api) {
        case 'fal':
            $result = array_merge($result, testFal($prompt, $config['fal_key']));
            break;
        case 'fal-schnell':
            $result = array_merge($result, testFalSchnell($prompt, $config['fal_key']));
            break;
        case 'replicate':
            $result = array_merge($result, testReplicate($prompt, $config['replicate_key']));
            break;
        case 'stability':
            $result = array_merge($result, testStability($prompt, $negative, $config['stability_key']));
            break;
        default:
            $result['error'] = 'Unknown API: ' . $api;
    }
    
    $result['duration_seconds'] = round(microtime(true) - $startTime, 2);
    return $result;
}

// ============================================
// FAL.AI - flux-pro
// ============================================
function testFal($prompt, $apiKey) {
    $result = ['service' => 'Fal.ai flux-pro'];
    
    // Submit job
    $ch = curl_init('https://queue.fal.run/fal-ai/flux-pro');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Authorization: Key ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'prompt' => $prompt,
            'image_size' => ['width' => 768, 'height' => 1024],
            'num_images' => 1,
            'safety_tolerance' => 6,
            'enable_safety_checker' => false
        ])
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    $result['submit_http_code'] = $httpCode;
    $result['submit_response'] = json_decode($response, true);
    
    if ($curlError) {
        $result['error'] = 'Curl error: ' . $curlError;
        return $result;
    }
    
    $data = json_decode($response, true);
    
    // Check for direct response (sync)
    if (!empty($data['images'][0]['url'])) {
        return saveImage($data['images'][0]['url'], $result, 'fal');
    }
    
    // Queue response - need to poll
    if (empty($data['request_id'])) {
        $result['error'] = 'No request_id in response';
        return $result;
    }
    
    $requestId = $data['request_id'];
    $result['request_id'] = $requestId;
    
    // Poll for result
    for ($i = 0; $i < 60; $i++) {
        sleep(2);
        
        $statusUrl = "https://queue.fal.run/fal-ai/flux-pro/requests/{$requestId}/status";
        $ch = curl_init($statusUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Key ' . $apiKey]
        ]);
        $statusResponse = curl_exec($ch);
        curl_close($ch);
        
        $statusData = json_decode($statusResponse, true);
        $result['poll_' . $i] = $statusData;
        
        if (($statusData['status'] ?? '') === 'COMPLETED') {
            // Fetch result
            $resultUrl = "https://queue.fal.run/fal-ai/flux-pro/requests/{$requestId}";
            $ch = curl_init($resultUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: Key ' . $apiKey]
            ]);
            $resultResponse = curl_exec($ch);
            curl_close($ch);
            
            $resultData = json_decode($resultResponse, true);
            $result['final_response'] = $resultData;
            
            if (!empty($resultData['images'][0]['url'])) {
                return saveImage($resultData['images'][0]['url'], $result, 'fal');
            }
            
            $result['error'] = 'No image URL in completed response';
            return $result;
        }
        
        if (($statusData['status'] ?? '') === 'FAILED') {
            $result['error'] = 'Generation failed: ' . ($statusData['error'] ?? 'unknown');
            return $result;
        }
    }
    
    $result['error'] = 'Timeout waiting for generation';
    return $result;
}

// ============================================
// FAL.AI - flux-schnell (faster, less restricted)
// ============================================
function testFalSchnell($prompt, $apiKey) {
    $result = ['service' => 'Fal.ai flux-schnell'];
    
    $ch = curl_init('https://queue.fal.run/fal-ai/flux/schnell');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => [
            'Authorization: Key ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'prompt' => $prompt,
            'image_size' => ['width' => 768, 'height' => 1024],
            'num_images' => 1,
            'enable_safety_checker' => false
        ])
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result['http_code'] = $httpCode;
    $data = json_decode($response, true);
    $result['response'] = $data;
    
    if (!empty($data['images'][0]['url'])) {
        return saveImage($data['images'][0]['url'], $result, 'fal-schnell');
    }
    
    // Handle queue
    if (!empty($data['request_id'])) {
        $requestId = $data['request_id'];
        for ($i = 0; $i < 30; $i++) {
            sleep(2);
            $ch = curl_init("https://queue.fal.run/fal-ai/flux/schnell/requests/{$requestId}");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Authorization: Key ' . $apiKey]
            ]);
            $pollResponse = curl_exec($ch);
            curl_close($ch);
            $pollData = json_decode($pollResponse, true);
            
            if (!empty($pollData['images'][0]['url'])) {
                return saveImage($pollData['images'][0]['url'], $result, 'fal-schnell');
            }
            if (($pollData['status'] ?? '') === 'FAILED') {
                $result['error'] = 'Failed: ' . ($pollData['error'] ?? 'unknown');
                return $result;
            }
        }
    }
    
    $result['error'] = $data['detail'] ?? $data['error'] ?? 'Unknown error';
    return $result;
}

// ============================================
// REPLICATE - flux-1.1-pro
// ============================================
function testReplicate($prompt, $apiKey) {
    $result = ['service' => 'Replicate flux-1.1-pro'];
    
    // Submit prediction
    $ch = curl_init('https://api.replicate.com/v1/models/black-forest-labs/flux-1.1-pro/predictions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Prefer' => 'wait'  // Try to get sync response
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'input' => [
                'prompt' => $prompt,
                'aspect_ratio' => '3:4',
                'output_format' => 'png',
                'safety_tolerance' => 5
            ]
        ])
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    $result['submit_http_code'] = $httpCode;
    $result['submit_response'] = json_decode($response, true);
    
    if ($curlError) {
        $result['error'] = 'Curl error: ' . $curlError;
        return $result;
    }
    
    $data = json_decode($response, true);
    
    // Check for immediate result
    if (!empty($data['output'])) {
        $imageUrl = is_array($data['output']) ? $data['output'][0] : $data['output'];
        return saveImage($imageUrl, $result, 'replicate');
    }
    
    // Need to poll
    $predictionId = $data['id'] ?? null;
    if (!$predictionId) {
        $result['error'] = 'No prediction ID: ' . ($data['detail'] ?? 'unknown error');
        return $result;
    }
    
    $result['prediction_id'] = $predictionId;
    $pollUrl = "https://api.replicate.com/v1/predictions/{$predictionId}";
    
    for ($i = 0; $i < 60; $i++) {
        sleep(2);
        
        $ch = curl_init($pollUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey]
        ]);
        $pollResponse = curl_exec($ch);
        curl_close($ch);
        
        $pollData = json_decode($pollResponse, true);
        
        if (($pollData['status'] ?? '') === 'succeeded') {
            $result['final_response'] = $pollData;
            if (!empty($pollData['output'])) {
                $imageUrl = is_array($pollData['output']) ? $pollData['output'][0] : $pollData['output'];
                return saveImage($imageUrl, $result, 'replicate');
            }
        }
        
        if (($pollData['status'] ?? '') === 'failed') {
            $result['error'] = 'Failed: ' . ($pollData['error'] ?? 'unknown');
            $result['final_response'] = $pollData;
            return $result;
        }
    }
    
    $result['error'] = 'Timeout';
    return $result;
}

// ============================================
// STABILITY AI - SDXL (likely to filter adult)
// ============================================
function testStability($prompt, $negative, $apiKey) {
    $result = ['service' => 'Stability AI SDXL', 'warning' => 'SDXL typically filters adult content'];
    
    $ch = curl_init('https://api.stability.ai/v1/generation/stable-diffusion-xl-1024-v1-0/text-to-image');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'text_prompts' => [
                ['text' => $prompt, 'weight' => 1],
                ['text' => $negative, 'weight' => -1]
            ],
            'cfg_scale' => 7,
            'height' => 1024,
            'width' => 768,
            'samples' => 1,
            'steps' => 30
        ])
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result['http_code'] = $httpCode;
    
    if ($httpCode !== 200) {
        $result['error'] = 'HTTP ' . $httpCode;
        $result['response'] = json_decode($response, true);
        return $result;
    }
    
    $data = json_decode($response, true);
    
    if (!empty($data['artifacts'][0]['finishReason']) && $data['artifacts'][0]['finishReason'] === 'CONTENT_FILTERED') {
        $result['error'] = 'CONTENT_FILTERED - Adult content blocked by Stability AI';
        $result['finish_reason'] = $data['artifacts'][0]['finishReason'];
        return $result;
    }
    
    if (!empty($data['artifacts'][0]['base64'])) {
        $imageData = base64_decode($data['artifacts'][0]['base64']);
        $filename = 'test_stability_' . time() . '.png';
        $dir = __DIR__ . '/uploads';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . '/' . $filename, $imageData);
        
        $result['success'] = true;
        $result['image'] = 'uploads/' . $filename;
        $result['file_size'] = strlen($imageData);
        return $result;
    }
    
    $result['error'] = 'No image in response';
    $result['response'] = $data;
    return $result;
}

// ============================================
// Helper: Save image from URL
// ============================================
function saveImage($url, $result, $prefix) {
    $imageData = @file_get_contents($url);
    
    if (empty($imageData)) {
        $result['error'] = 'Failed to download image from: ' . $url;
        return $result;
    }
    
    $filename = "test_{$prefix}_" . time() . '.png';
    $dir = __DIR__ . '/uploads';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    
    $filepath = $dir . '/' . $filename;
    file_put_contents($filepath, $imageData);
    
    $result['success'] = true;
    $result['image'] = 'uploads/' . $filename;
    $result['image_url'] = $url;
    $result['file_size'] = strlen($imageData);
    
    return $result;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Adult Image Gen Test</title>
    <style>
        body { font-family: system-ui; max-width: 900px; margin: 40px auto; padding: 20px; background: #1a1a2e; color: #eee; }
        h1 { color: #ff6b9d; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; color: #aaa; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #333; border-radius: 8px; background: #16213e; color: #fff; font-size: 14px; }
        textarea { min-height: 100px; }
        button { background: linear-gradient(135deg, #ff6b9d, #c44569); color: white; border: none; padding: 15px 30px; border-radius: 8px; cursor: pointer; font-size: 16px; margin: 5px; }
        button:hover { opacity: 0.9; }
        .buttons { margin: 20px 0; }
        #result { background: #16213e; padding: 20px; border-radius: 8px; margin-top: 20px; white-space: pre-wrap; font-family: monospace; font-size: 12px; max-height: 400px; overflow: auto; }
        #preview { margin-top: 20px; text-align: center; }
        #preview img { max-width: 100%; max-height: 500px; border-radius: 8px; }
        .api-info { background: #16213e; padding: 15px; border-radius: 8px; margin: 20px 0; font-size: 13px; }
        .api-info h3 { margin-top: 0; color: #ff6b9d; }
        .status { padding: 10px; border-radius: 5px; margin: 10px 0; }
        .status.loading { background: #2d3436; }
        .status.success { background: #00b894; }
        .status.error { background: #d63031; }
    </style>
</head>
<body>
    <h1>🔥 Adult Image Generation Test</h1>
    
    <div class="api-info">
        <h3>API Status</h3>
        <p>✓ Fal.ai Key: <?= !empty($config['fal_key']) ? 'Configured' : '❌ Missing' ?></p>
        <p>✓ Replicate Key: <?= !empty($config['replicate_key']) ? 'Configured' : '❌ Missing' ?></p>
        <p>✓ Stability Key: <?= !empty($config['stability_key']) ? 'Configured' : '❌ Missing' ?></p>
    </div>
    
    <form id="testForm">
        <div class="form-group">
            <label>Prompt (adult content):</label>
            <textarea name="prompt" id="prompt"><?= htmlspecialchars($defaultPrompt) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Negative Prompt:</label>
            <textarea name="negative" id="negative"><?= htmlspecialchars($defaultNegative) ?></textarea>
        </div>
        
        <div class="buttons">
            <button type="button" onclick="testAPI('fal')">Test Fal.ai (flux-pro)</button>
            <button type="button" onclick="testAPI('fal-schnell')">Test Fal.ai (schnell)</button>
            <button type="button" onclick="testAPI('replicate')">Test Replicate</button>
            <button type="button" onclick="testAPI('stability')">Test Stability (will likely filter)</button>
        </div>
    </form>
    
    <div id="status" class="status" style="display:none"></div>
    <div id="result"></div>
    <div id="preview"></div>
    
    <script>
    async function testAPI(api) {
        const status = document.getElementById('status');
        const result = document.getElementById('result');
        const preview = document.getElementById('preview');
        
        status.style.display = 'block';
        status.className = 'status loading';
        status.textContent = '⏳ Testing ' + api + '... (this may take 30-60 seconds)';
        result.textContent = '';
        preview.innerHTML = '';
        
        const formData = new FormData();
        formData.append('api', api);
        formData.append('prompt', document.getElementById('prompt').value);
        formData.append('negative', document.getElementById('negative').value);
        
        try {
            const response = await fetch('', { method: 'POST', body: formData });
            const data = await response.json();
            
            result.textContent = JSON.stringify(data, null, 2);
            
            if (data.success && data.image) {
                status.className = 'status success';
                status.textContent = '✅ Success! Image generated in ' + data.duration_seconds + 's';
                preview.innerHTML = '<img src="' + data.image + '?' + Date.now() + '" alt="Generated">';
            } else {
                status.className = 'status error';
                status.textContent = '❌ Failed: ' + (data.error || 'Unknown error');
            }
        } catch (e) {
            status.className = 'status error';
            status.textContent = '❌ Error: ' + e.message;
            result.textContent = e.toString();
        }
    }
    </script>
</body>
</html>