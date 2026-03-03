<?php
session_start();

$config = [
    'db_host' => 'localhost',
    'db_name' => 'thomasrlee42_ai-companions',
    'db_user' => 'thomasrlee42_ai-companions',
    'db_pass' => 'qwerpoiu0042!!',
    'openai_key' => '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',
];

$ADMIN_USER = 'admin';
$ADMIN_PASS = 'admin123!';

try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch(PDOException $e) { die("DB Error"); }

// Auth check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

// API Actions
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'generate_profile':
            $type = $_POST['type'] ?? 'girlfriend';
            $category = $_POST['category'] ?? 'companionship';
            $customName = $_POST['name'] ?? '';
            $customAge = $_POST['age'] ?? '';
            $customTraits = $_POST['traits'] ?? '';
            $generateImage = $_POST['generate_image'] === 'true';
            
            // Generate profile using AI
            $profile = generateCompanionProfile($type, $category, $customName, $customAge, $customTraits, $config['openai_key']);
            
            // Generate image if requested
            $imageUrl = '';
            if ($generateImage && !empty($profile['base_appearance'])) {
                $imageUrl = generateProfileImage($profile['base_appearance'], $config['openai_key']);
            }
            $profile['image_url'] = $imageUrl;
            
            echo json_encode(['success' => true, 'profile' => $profile]);
            exit;
            
        case 'generate_batch':
            $count = min(intval($_POST['count'] ?? 5), 20); // Max 20 at a time
            $types = ['girlfriend', 'boyfriend', 'non-binary'];
            $categories = ['emotional-support', 'companionship', 'entertainment', 'motivation', 'conversation'];
            
            $profiles = [];
            for ($i = 0; $i < $count; $i++) {
                $type = $types[array_rand($types)];
                $category = $categories[array_rand($categories)];
                $profile = generateCompanionProfile($type, $category, '', '', '', $config['openai_key']);
                
                // Generate image
                if (!empty($profile['base_appearance'])) {
                    $profile['image_url'] = generateProfileImage($profile['base_appearance'], $config['openai_key']);
                }
                
                $profiles[] = $profile;
                
                // Small delay to avoid rate limits
                usleep(500000); // 0.5 second
            }
            
            echo json_encode(['success' => true, 'profiles' => $profiles]);
            exit;
            
        case 'save_profile':
            $profile = json_decode($_POST['profile'], true);
            
            // Create user account for companion
            $username = strtolower(preg_replace('/[^a-z0-9]/', '', $profile['name'])) . '_' . rand(100, 999);
            $email = $username . '@companion.local';
            $hash = password_hash('demo123', PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, display_name, bio, is_provider) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$username, $email, $hash, $profile['name'], $profile['tagline']]);
            $userId = $pdo->lastInsertId();
            
            // Create gig
            $stmt = $pdo->prepare("INSERT INTO gigs (user_id, title, description, companion_type, category, price_per_hour, monthly_price, languages, availability, response_time, image_url, tags, ai_persona, base_appearance, rating, review_count, total_orders, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 'English', '24/7 Available', 'Instant', ?, ?, ?, ?, ?, ?, ?, 1)");
            $stmt->execute([
                $userId,
                $profile['title'],
                $profile['description'],
                $profile['type'],
                $profile['category'],
                $profile['price_per_hour'],
                $profile['monthly_price'],
                $profile['image_url'],
                $profile['tags'],
                $profile['persona'],
                $profile['base_appearance'],
                rand(45, 50) / 10, // Rating 4.5-5.0
                rand(20, 150),     // Review count
                rand(50, 300)      // Orders
            ]);
            
            echo json_encode(['success' => true, 'gig_id' => $pdo->lastInsertId()]);
            exit;
    }
    exit;
}

function generateCompanionProfile($type, $category, $customName, $customAge, $customTraits, $apiKey) {
    $prompt = "Generate a detailed AI companion profile. Return ONLY valid JSON with these exact fields:
{
    \"name\": \"First name only\",
    \"age\": number between 21-29,
    \"type\": \"$type\",
    \"category\": \"$category\",
    \"tagline\": \"One catchy sentence about them\",
    \"title\": \"Gig title like 'Caring girlfriend for deep conversations'\",
    \"description\": \"2-3 sentence description\",
    \"persona\": \"Detailed AI persona instructions (personality, background, speaking style, interests)\",
    \"base_appearance\": \"Physical description for consistent image generation (age, ethnicity, hair, eyes, features, style)\",
    \"tags\": \"comma separated tags\",
    \"price_per_hour\": number 20-35,
    \"monthly_price\": number 59-99
}";

    if ($customName) $prompt .= "\nUse name: $customName";
    if ($customAge) $prompt .= "\nUse age: $customAge";
    if ($customTraits) $prompt .= "\nIncorporate traits: $customTraits";
    
    $prompt .= "\n\nBe creative and diverse. Include varied ethnicities, body types, and personalities. Make them feel real and unique.";
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => 1000,
            'temperature' => 0.9
        ])
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? '{}';
    
    // Clean JSON
    $content = preg_replace('/```json\s*/', '', $content);
    $content = preg_replace('/```\s*/', '', $content);
    
    $profile = json_decode($content, true);
    if (!$profile) {
        return ['error' => 'Failed to parse profile', 'raw' => $content];
    }
    
    return $profile;
}

function generateProfileImage($appearance, $apiKey) {
    $prompt = "Professional portrait photo for a dating/companion profile. ";
    $prompt .= "Person: $appearance. ";
    $prompt .= "Style: High quality headshot or casual selfie, natural lighting, warm and approachable expression, ";
    $prompt .= "looking at camera, modern and attractive, instagram-worthy, photorealistic. ";
    $prompt .= "Safe for work, tasteful, no nudity.";
    
    $ch = curl_init('https://api.openai.com/v1/images/generations');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'quality' => 'standard'
        ])
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data['data'][0]['url'] ?? '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companion Generator - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0f0f0f;--bg2:#1a1a1a;--bg3:#252525;--text:#fff;--text2:#888;--accent:#10b981;--red:#ef4444;--border:#333}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);padding:24px;min-height:100vh}
        .container{max-width:1400px;margin:0 auto}
        .back{color:var(--accent);text-decoration:none;display:inline-block;margin-bottom:24px}
        h1{font-size:28px;margin-bottom:8px}
        .subtitle{color:var(--text2);margin-bottom:32px}
        .grid{display:grid;grid-template-columns:400px 1fr;gap:24px}
        .card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:24px}
        .card h2{font-size:16px;color:var(--text2);margin-bottom:20px;text-transform:uppercase;letter-spacing:1px}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-size:13px;color:var(--text2);margin-bottom:6px}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px;font-family:inherit}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--accent)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .btn{padding:12px 24px;border-radius:8px;border:none;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s}
        .btn-primary{background:var(--accent);color:#000;width:100%}
        .btn-primary:hover{background:#0d9f72}
        .btn-primary:disabled{opacity:0.5;cursor:not-allowed}
        .btn-secondary{background:var(--bg3);color:var(--text)}
        .btn-danger{background:var(--red);color:#fff}
        .checkbox{display:flex;align-items:center;gap:8px;cursor:pointer}
        .checkbox input{width:auto}
        .preview{margin-top:24px}
        .preview-card{background:var(--bg);border:1px solid var(--border);border-radius:16px;overflow:hidden}
        .preview-img{width:100%;height:300px;background:var(--bg3);display:flex;align-items:center;justify-content:center;color:var(--text2)}
        .preview-img img{width:100%;height:100%;object-fit:cover}
        .preview-content{padding:20px}
        .preview-name{font-size:20px;font-weight:600;margin-bottom:4px}
        .preview-meta{font-size:13px;color:var(--text2);margin-bottom:12px}
        .preview-desc{font-size:14px;color:var(--text2);line-height:1.6;margin-bottom:16px}
        .preview-tags{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
        .tag{padding:4px 12px;background:var(--bg3);border-radius:50px;font-size:12px}
        .profiles-list{display:grid;gap:16px;max-height:600px;overflow-y:auto}
        .profile-item{display:flex;gap:16px;padding:16px;background:var(--bg);border:1px solid var(--border);border-radius:12px}
        .profile-item img{width:80px;height:80px;border-radius:8px;object-fit:cover}
        .profile-info{flex:1}
        .profile-info h4{margin-bottom:4px}
        .profile-info p{font-size:13px;color:var(--text2)}
        .profile-actions{display:flex;gap:8px;margin-top:8px}
        .profile-actions button{padding:6px 12px;font-size:12px}
        .loading{display:none;align-items:center;gap:8px;color:var(--text2)}
        .loading.active{display:flex}
        .spinner{width:20px;height:20px;border:2px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin 1s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        .batch-section{margin-top:24px;padding-top:24px;border-top:1px solid var(--border)}
        @media(max-width:900px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="container">
        <a href="admin.php" class="back">← Back to Admin</a>
        <h1>🎭 Companion Generator</h1>
        <p class="subtitle">Create new AI companion profiles with AI-generated details and images</p>
        
        <div class="grid">
            <div>
                <div class="card">
                    <h2>Generate Single Profile</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Type</label>
                            <select id="type">
                                <option value="girlfriend">Girlfriend</option>
                                <option value="boyfriend">Boyfriend</option>
                                <option value="non-binary">Non-Binary</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select id="category">
                                <option value="companionship">Companionship</option>
                                <option value="emotional-support">Emotional Support</option>
                                <option value="entertainment">Entertainment</option>
                                <option value="motivation">Motivation</option>
                                <option value="conversation">Conversation</option>
                                <option value="roleplay">Roleplay</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Custom Name (optional)</label>
                        <input type="text" id="customName" placeholder="Leave blank for AI to generate">
                    </div>
                    <div class="form-group">
                        <label>Custom Age (optional)</label>
                        <input type="number" id="customAge" placeholder="21-29" min="21" max="29">
                    </div>
                    <div class="form-group">
                        <label>Custom Traits (optional)</label>
                        <textarea id="customTraits" rows="2" placeholder="e.g., Latina, athletic, loves hiking, nerdy..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="checkbox">
                            <input type="checkbox" id="generateImage" checked>
                            Generate profile image (uses DALL-E 3)
                        </label>
                    </div>
                    <button class="btn btn-primary" onclick="generateProfile()" id="genBtn">
                        <span id="genBtnText">Generate Profile</span>
                    </button>
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        <span>Generating profile...</span>
                    </div>
                    
                    <div class="batch-section">
                        <h2>Batch Generate</h2>
                        <div class="form-row" style="margin-top:16px">
                            <div class="form-group">
                                <label>Number of Profiles</label>
                                <input type="number" id="batchCount" value="5" min="1" max="20">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-secondary" onclick="generateBatch()" id="batchBtn" style="width:100%">Generate Batch</button>
                            </div>
                        </div>
                        <p style="font-size:12px;color:var(--text2);margin-top:8px">Generates random diverse profiles with images. Max 20 at a time.</p>
                    </div>
                </div>
            </div>
            
            <div>
                <div class="card">
                    <h2>Preview</h2>
                    <div id="previewArea">
                        <p style="color:var(--text2);text-align:center;padding:40px">Generated profile will appear here</p>
                    </div>
                </div>
                
                <div class="card" style="margin-top:24px">
                    <h2>Generated Profiles (Ready to Save)</h2>
                    <div class="profiles-list" id="profilesList">
                        <p style="color:var(--text2);text-align:center;padding:20px">No profiles generated yet</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    let generatedProfiles = [];
    
    async function api(action, data = {}) {
        const fd = new FormData();
        fd.append('action', action);
        for (let k in data) fd.append(k, data[k]);
        const r = await fetch('generator.php', { method: 'POST', body: fd });
        return r.json();
    }
    
    async function generateProfile() {
        const btn = document.getElementById('genBtn');
        const loading = document.getElementById('loading');
        btn.disabled = true;
        loading.classList.add('active');
        
        try {
            const r = await api('generate_profile', {
                type: document.getElementById('type').value,
                category: document.getElementById('category').value,
                name: document.getElementById('customName').value,
                age: document.getElementById('customAge').value,
                traits: document.getElementById('customTraits').value,
                generate_image: document.getElementById('generateImage').checked
            });
            
            if (r.success && r.profile) {
                showPreview(r.profile);
                generatedProfiles.unshift(r.profile);
                updateProfilesList();
            } else {
                alert('Error generating profile');
                console.error(r);
            }
        } catch (e) {
            alert('Error: ' + e.message);
        }
        
        btn.disabled = false;
        loading.classList.remove('active');
    }
    
    async function generateBatch() {
        const btn = document.getElementById('batchBtn');
        const count = document.getElementById('batchCount').value;
        btn.disabled = true;
        btn.textContent = 'Generating...';
        
        try {
            const r = await api('generate_batch', { count });
            
            if (r.success && r.profiles) {
                r.profiles.forEach(p => generatedProfiles.unshift(p));
                updateProfilesList();
                if (r.profiles.length > 0) showPreview(r.profiles[0]);
                alert(`Generated ${r.profiles.length} profiles!`);
            }
        } catch (e) {
            alert('Error: ' + e.message);
        }
        
        btn.disabled = false;
        btn.textContent = 'Generate Batch';
    }
    
    function showPreview(profile) {
        document.getElementById('previewArea').innerHTML = `
            <div class="preview-card">
                <div class="preview-img">
                    ${profile.image_url ? `<img src="${profile.image_url}" alt="${profile.name}">` : '📷 No image'}
                </div>
                <div class="preview-content">
                    <div class="preview-name">${profile.name}, ${profile.age}</div>
                    <div class="preview-meta">${profile.type} • ${profile.category} • $${profile.price_per_hour}/hr</div>
                    <div class="preview-desc">${profile.tagline}</div>
                    <div class="preview-tags">${(profile.tags || '').split(',').map(t => `<span class="tag">${t.trim()}</span>`).join('')}</div>
                    <button class="btn btn-primary" onclick="saveProfile(0)">Save to Database</button>
                </div>
            </div>
        `;
    }
    
    function updateProfilesList() {
        if (generatedProfiles.length === 0) {
            document.getElementById('profilesList').innerHTML = '<p style="color:var(--text2);text-align:center;padding:20px">No profiles generated yet</p>';
            return;
        }
        
        document.getElementById('profilesList').innerHTML = generatedProfiles.map((p, i) => `
            <div class="profile-item">
                <img src="${p.image_url || 'https://via.placeholder.com/80?text=?'}" alt="${p.name}">
                <div class="profile-info">
                    <h4>${p.name}, ${p.age}</h4>
                    <p>${p.type} • ${p.category}</p>
                    <div class="profile-actions">
                        <button class="btn btn-secondary" onclick="showPreview(generatedProfiles[${i}])">Preview</button>
                        <button class="btn btn-primary" onclick="saveProfile(${i})">Save</button>
                        <button class="btn btn-danger" onclick="removeProfile(${i})">Remove</button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    async function saveProfile(index) {
        const profile = generatedProfiles[index];
        if (!profile) return;
        
        const r = await api('save_profile', { profile: JSON.stringify(profile) });
        if (r.success) {
            alert('Profile saved! Gig ID: ' + r.gig_id);
            generatedProfiles.splice(index, 1);
            updateProfilesList();
        } else {
            alert('Error saving profile');
        }
    }
    
    function removeProfile(index) {
        generatedProfiles.splice(index, 1);
        updateProfilesList();
    }
    </script>
</body>
</html>
