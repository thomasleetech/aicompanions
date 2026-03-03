<?php
$pageTitle = "Privacy Policy";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$pageTitle?> - Companion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#09090b;--bg2:#18181b;--text:#fafafa;--text2:#a1a1aa;--accent:#6366f1;--border:rgba(255,255,255,0.08)}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.7}
        header{background:var(--bg2);border-bottom:1px solid var(--border);padding:16px 24px;position:sticky;top:0;z-index:100}
        .header-inner{max-width:900px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
        .logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--text)}
        .logo-icon{width:32px;height:32px;background:linear-gradient(135deg,var(--accent),#8b5cf6);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px}
        nav a{color:var(--text2);text-decoration:none;font-size:14px;margin-left:24px}
        nav a:hover{color:var(--text)}
        .container{max-width:800px;margin:0 auto;padding:48px 24px}
        h1{font-size:36px;font-weight:700;margin-bottom:8px}
        .subtitle{color:var(--text2);margin-bottom:32px}
        .updated{color:var(--text2);font-size:13px;margin-bottom:48px;padding-bottom:24px;border-bottom:1px solid var(--border)}
        h2{font-size:22px;font-weight:600;margin:40px 0 16px;color:var(--accent)}
        h2:first-of-type{margin-top:0}
        h3{font-size:18px;font-weight:600;margin:24px 0 12px}
        p{color:var(--text2);margin-bottom:16px}
        ul,ol{color:var(--text2);margin:16px 0 24px 24px}
        li{margin-bottom:8px}
        .data-table{width:100%;border-collapse:collapse;margin:24px 0}
        .data-table th,.data-table td{padding:12px;text-align:left;border-bottom:1px solid var(--border)}
        .data-table th{color:var(--text);font-weight:600;font-size:14px}
        .data-table td{color:var(--text2);font-size:14px}
        .info-box{background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);border-radius:12px;padding:20px;margin:24px 0}
        .info-box h4{color:var(--accent);margin-bottom:8px;font-size:16px}
        a{color:var(--accent)}
        footer{border-top:1px solid var(--border);padding:24px;text-align:center;color:var(--text2);font-size:13px;margin-top:48px}
    </style>
</head>
<body>
    <header>
        <div class="header-inner">
            <a href="index.php" class="logo">
                <div class="logo-icon">💬</div>
                <span>Companion</span>
            </a>
            <nav>
                <a href="terms.php">Terms</a>
                <a href="safety.php">Safety</a>
                <a href="support.php">Support</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1>Privacy Policy</h1>
        <p class="subtitle">Your privacy is important to us. This policy explains how we collect, use, and protect your data.</p>
        <p class="updated">Last Updated: January 2025</p>
        
        <h2>1. Information We Collect</h2>
        
        <h3>Account Information</h3>
        <table class="data-table">
            <tr><th>Data Type</th><th>Purpose</th><th>Retention</th></tr>
            <tr><td>Email address</td><td>Account login, notifications</td><td>Until account deletion</td></tr>
            <tr><td>Username</td><td>Account identification</td><td>Until account deletion</td></tr>
            <tr><td>Password (hashed)</td><td>Authentication</td><td>Until account deletion</td></tr>
            <tr><td>Payment info</td><td>Processed by Stripe</td><td>Per Stripe's policy</td></tr>
        </table>
        
        <h3>Conversation Data</h3>
        <table class="data-table">
            <tr><th>Data Type</th><th>Purpose</th><th>Retention</th></tr>
            <tr><td>Chat messages</td><td>Service delivery, memory features</td><td>Until account deletion</td></tr>
            <tr><td>AI memories</td><td>Contextual conversations</td><td>Until cleared by user</td></tr>
            <tr><td>Generated images</td><td>Photo features</td><td>30 days or until deleted</td></tr>
            <tr><td>Voice messages</td><td>Voice features</td><td>30 days or until deleted</td></tr>
        </table>
        
        <h3>Technical Data</h3>
        <table class="data-table">
            <tr><th>Data Type</th><th>Purpose</th><th>Retention</th></tr>
            <tr><td>IP address</td><td>Security, fraud prevention</td><td>90 days</td></tr>
            <tr><td>Device info</td><td>Service optimization</td><td>90 days</td></tr>
            <tr><td>Usage analytics</td><td>Service improvement</td><td>Aggregated indefinitely</td></tr>
        </table>
        
        <h2>2. How We Use Your Data</h2>
        <p>We use your information to:</p>
        <ul>
            <li>Provide and maintain the AI companion service</li>
            <li>Enable memory and context features in conversations</li>
            <li>Generate AI images and voice messages</li>
            <li>Process payments and manage subscriptions</li>
            <li>Improve our AI models and service quality</li>
            <li>Detect and prevent fraud or abuse</li>
            <li>Comply with legal obligations</li>
        </ul>
        
        <div class="info-box">
            <h4>AI Training Disclosure</h4>
            <p style="margin-bottom:0">We may use anonymized conversation data to improve our AI systems. Personal identifiers are removed before any training use. You can opt out of training data use in your account settings.</p>
        </div>
        
        <h2>3. Data Sharing</h2>
        <p>We share data with:</p>
        <ul>
            <li><strong>AI Service Providers:</strong> OpenAI, Anthropic, ElevenLabs (for AI features)</li>
            <li><strong>Payment Processor:</strong> Stripe (for payments)</li>
            <li><strong>Cloud Infrastructure:</strong> Secure hosting providers</li>
            <li><strong>Law Enforcement:</strong> When legally required</li>
        </ul>
        <p>We do NOT sell your personal data to third parties.</p>
        
        <h2>4. Adult Content Data</h2>
        <p>If you use adult features (18+):</p>
        <ul>
            <li>Adult conversations are stored with additional encryption</li>
            <li>NSFW images are stored separately with restricted access</li>
            <li>Age verification data is retained for compliance</li>
            <li>Adult content is never used for AI training without consent</li>
        </ul>
        
        <h2>5. Your Rights</h2>
        <p>You have the right to:</p>
        <ul>
            <li><strong>Access:</strong> Request a copy of your data</li>
            <li><strong>Correction:</strong> Update inaccurate information</li>
            <li><strong>Deletion:</strong> Request deletion of your account and data</li>
            <li><strong>Export:</strong> Download your conversation history</li>
            <li><strong>Opt-out:</strong> Disable AI training use of your data</li>
            <li><strong>Restrict:</strong> Limit certain data processing</li>
        </ul>
        <p>To exercise these rights, contact us at privacy@companion-ai.com or use the account settings page.</p>
        
        <h2>6. Data Security</h2>
        <p>We protect your data with:</p>
        <ul>
            <li>Encryption in transit (HTTPS/TLS)</li>
            <li>Encryption at rest for sensitive data</li>
            <li>Access controls and authentication</li>
            <li>Regular security audits</li>
            <li>Secure cloud infrastructure</li>
        </ul>
        
        <h2>7. Cookies and Tracking</h2>
        <p>We use:</p>
        <ul>
            <li><strong>Essential cookies:</strong> For authentication and security</li>
            <li><strong>Functional cookies:</strong> For preferences and settings</li>
            <li><strong>Analytics:</strong> Aggregated usage statistics</li>
        </ul>
        <p>We do not use third-party advertising trackers.</p>
        
        <h2>8. International Users</h2>
        <p>Data is stored in the United States. By using our service, you consent to data transfer to the US. We comply with GDPR for EU users and provide equivalent protections globally.</p>
        
        <h2>9. Children's Privacy</h2>
        <p>Our service is NOT intended for anyone under 18. We do not knowingly collect data from minors. If we discover a minor's account, it will be immediately terminated and data deleted.</p>
        
        <h2>10. Changes to This Policy</h2>
        <p>We may update this policy periodically. Material changes will be communicated via email or service notification. Continued use constitutes acceptance of changes.</p>
        
        <h2>11. Contact Us</h2>
        <p>For privacy questions or requests:</p>
        <ul>
            <li>Email: privacy@companion-ai.com</li>
            <li>Data Protection Officer: dpo@companion-ai.com</li>
            <li>Support: <a href="support.php">Support Center</a></li>
        </ul>
    </div>
    
    <footer>
        <p>© 2024 Companion. All rights reserved.</p>
        <p style="margin-top:8px"><a href="privacy.php">Privacy Policy</a> · <a href="terms.php">Terms of Service</a> · <a href="support.php">Contact Us</a></p>
    </footer>
</body>
</html>
