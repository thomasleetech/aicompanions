<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gift Cards - Give the Gift of Connection | Companion</title>
    <meta name="description" content="Buy Companion gift cards for friends. Give the gift of AI companionship and connection.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0a0a0a;--bg2:#111;--text:#fff;--text2:#888;--accent:#10b981;--border:#222}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.7}
        .container{max-width:900px;margin:0 auto;padding:0 24px}
        header{padding:20px 0;border-bottom:1px solid var(--border)}
        header .container{display:flex;justify-content:space-between;align-items:center}
        .logo{font-size:20px;font-weight:700;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:10px}
        .logo span{background:var(--accent);width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center}
        .btn{padding:12px 28px;background:var(--accent);color:#000;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;display:inline-block;border:none;cursor:pointer}
        .btn:hover{background:#0d9f72}
        .btn:disabled{opacity:0.5;cursor:not-allowed}
        
        .hero{padding:60px 0;text-align:center}
        .hero h1{font-size:40px;font-weight:800;margin-bottom:16px}
        .hero p{color:var(--text2);font-size:18px}
        
        .tabs{display:flex;justify-content:center;gap:8px;margin:40px 0}
        .tab{padding:12px 32px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;cursor:pointer;font-weight:600}
        .tab.active{background:var(--accent);color:#000;border-color:var(--accent)}
        
        .section{display:none}
        .section.active{display:block}
        
        .card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:32px;margin-bottom:24px}
        .card h2{font-size:24px;margin-bottom:24px}
        
        .amounts{display:grid;grid-template-columns:repeat(auto-fit,minmax(100px,1fr));gap:12px;margin-bottom:24px}
        .amount-btn{padding:20px;background:var(--bg);border:2px solid var(--border);border-radius:12px;text-align:center;cursor:pointer;transition:all .2s}
        .amount-btn:hover,.amount-btn.selected{border-color:var(--accent);background:rgba(16,185,129,0.1)}
        .amount-btn .value{font-size:24px;font-weight:700}
        .amount-btn .label{font-size:12px;color:var(--text2)}
        
        .custom-amount{margin-bottom:24px}
        .custom-amount label{display:block;font-size:14px;margin-bottom:8px;color:var(--text2)}
        .custom-amount input{width:100%;padding:14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:18px;font-weight:600}
        .custom-amount input:focus{outline:none;border-color:var(--accent)}
        
        .form-group{margin-bottom:20px}
        .form-group label{display:block;font-size:14px;margin-bottom:8px}
        .form-group input,.form-group textarea{width:100%;padding:12px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px;font-family:inherit}
        .form-group input:focus,.form-group textarea:focus{outline:none;border-color:var(--accent)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        
        .gift-preview{background:linear-gradient(135deg,var(--accent),#059669);border-radius:16px;padding:32px;margin:24px 0;text-align:center;color:#000}
        .gift-preview h3{font-size:20px;margin-bottom:8px}
        .gift-preview .amount{font-size:48px;font-weight:800}
        .gift-preview p{opacity:0.8;margin-top:8px}
        
        .redeem-section{max-width:500px;margin:0 auto}
        .redeem-input{display:flex;gap:12px}
        .redeem-input input{flex:1;padding:16px;font-size:18px;text-transform:uppercase;letter-spacing:2px;text-align:center}
        
        .result{padding:24px;border-radius:12px;margin-top:24px;text-align:center}
        .result.success{background:rgba(16,185,129,0.1);border:1px solid var(--accent)}
        .result.error{background:rgba(239,68,68,0.1);border:1px solid #ef4444}
        
        .balance-display{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:32px;text-align:center;margin-bottom:40px}
        .balance-display .label{color:var(--text2);font-size:14px}
        .balance-display .amount{font-size:48px;font-weight:800;color:var(--accent)}
        
        footer{padding:40px 0;border-top:1px solid var(--border);text-align:center}
        footer p{color:var(--text2);font-size:13px}
        footer a{color:var(--text2)}
        
        @media(max-width:600px){.form-row{grid-template-columns:1fr}.redeem-input{flex-direction:column}}
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo"><span>🎁</span> Gift Cards</a>
            <a href="index.php" class="btn" style="background:var(--bg2);color:var(--text);border:1px solid var(--border)">← Back to App</a>
        </div>
    </header>
    
    <div class="container">
        <section class="hero">
            <h1>🎁 Gift Cards</h1>
            <p>Give the gift of connection - or redeem a card you received</p>
        </section>
        
        <div class="tabs">
            <div class="tab active" onclick="showTab('buy')">Buy a Gift Card</div>
            <div class="tab" onclick="showTab('redeem')">Redeem a Code</div>
        </div>
        
        <!-- Buy Section -->
        <div class="section active" id="buy-section">
            <div class="card">
                <h2>Choose Amount</h2>
                <div class="amounts">
                    <div class="amount-btn" onclick="selectAmount(25, this)">
                        <div class="value">$25</div>
                        <div class="label">~1 month</div>
                    </div>
                    <div class="amount-btn selected" onclick="selectAmount(50, this)">
                        <div class="value">$50</div>
                        <div class="label">~2 months</div>
                    </div>
                    <div class="amount-btn" onclick="selectAmount(100, this)">
                        <div class="value">$100</div>
                        <div class="label">~4 months</div>
                    </div>
                    <div class="amount-btn" onclick="selectAmount(200, this)">
                        <div class="value">$200</div>
                        <div class="label">Best value</div>
                    </div>
                </div>
                
                <div class="custom-amount">
                    <label>Or enter custom amount ($10 - $500)</label>
                    <input type="number" id="customAmount" min="10" max="500" placeholder="$" oninput="updateCustom()">
                </div>
            </div>
            
            <div class="card">
                <h2>Recipient Details</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Recipient's Email *</label>
                        <input type="email" id="recipientEmail" required placeholder="friend@email.com">
                    </div>
                    <div class="form-group">
                        <label>Recipient's Name</label>
                        <input type="text" id="recipientName" placeholder="Their name">
                    </div>
                </div>
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" id="senderName" placeholder="So they know who it's from">
                </div>
                <div class="form-group">
                    <label>Personal Message (optional)</label>
                    <textarea id="personalMessage" rows="3" placeholder="Add a personal note..."></textarea>
                </div>
            </div>
            
            <div class="gift-preview">
                <h3>Gift Card Preview</h3>
                <div class="amount" id="previewAmount">$50</div>
                <p>Companion Credits</p>
            </div>
            
            <button class="btn" style="width:100%;padding:16px;font-size:16px" onclick="purchaseGiftCard()">
                Purchase Gift Card - <span id="purchaseAmount">$50</span>
            </button>
            <p style="text-align:center;font-size:12px;color:var(--text2);margin-top:12px">Secure payment via Stripe. Card delivered instantly via email.</p>
        </div>
        
        <!-- Redeem Section -->
        <div class="section" id="redeem-section">
            <div class="balance-display">
                <div class="label">Your Current Balance</div>
                <div class="amount" id="userBalance">$0.00</div>
            </div>
            
            <div class="card redeem-section">
                <h2>Redeem Gift Card</h2>
                <p style="color:var(--text2);margin-bottom:24px">Enter your gift card code to add credits to your account</p>
                <div class="redeem-input">
                    <input type="text" id="redeemCode" placeholder="XXXX-XXXX-XXXX" maxlength="14">
                    <button class="btn" onclick="redeemCard()">Redeem</button>
                </div>
                <div id="redeemResult"></div>
            </div>
        </div>
    </div>
    
    <footer>
        <p>© 2024 Companion. <a href="terms.php">Terms</a> · <a href="privacy.php">Privacy</a></p>
    </footer>
    
    <script>
    let selectedAmount = 50;
    
    function showTab(tab) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById(tab + '-section').classList.add('active');
        
        if (tab === 'redeem') loadBalance();
    }
    
    function selectAmount(amount, el) {
        selectedAmount = amount;
        document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('customAmount').value = '';
        updatePreview();
    }
    
    function updateCustom() {
        const val = parseInt(document.getElementById('customAmount').value);
        if (val >= 10 && val <= 500) {
            selectedAmount = val;
            document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('selected'));
            updatePreview();
        }
    }
    
    function updatePreview() {
        document.getElementById('previewAmount').textContent = '$' + selectedAmount;
        document.getElementById('purchaseAmount').textContent = '$' + selectedAmount;
    }
    
    async function purchaseGiftCard() {
        const email = document.getElementById('recipientEmail').value;
        if (!email) {
            alert('Please enter recipient email');
            return;
        }
        
        const fd = new FormData();
        fd.append('action', 'purchase_gift_card');
        fd.append('amount', selectedAmount);
        fd.append('recipient_email', email);
        fd.append('recipient_name', document.getElementById('recipientName').value);
        fd.append('sender_name', document.getElementById('senderName').value);
        fd.append('message', document.getElementById('personalMessage').value);
        
        try {
            const r = await fetch('index.php', { method: 'POST', body: fd });
            const data = await r.json();
            
            if (data.success) {
                alert('Gift card created!\n\nCode: ' + data.code + '\n\nThis code has been sent to ' + email);
                // In production: redirect to Stripe checkout first
            } else {
                alert(data.message || 'Error creating gift card');
            }
        } catch(e) {
            alert('Error: ' + e.message);
        }
    }
    
    async function loadBalance() {
        try {
            const fd = new FormData();
            fd.append('action', 'get_user_balance');
            const r = await fetch('index.php', { method: 'POST', body: fd });
            const data = await r.json();
            document.getElementById('userBalance').textContent = '$' + (data.balance || 0).toFixed(2);
        } catch(e) {}
    }
    
    async function redeemCard() {
        const code = document.getElementById('redeemCode').value.trim();
        if (!code) {
            alert('Please enter a gift card code');
            return;
        }
        
        const fd = new FormData();
        fd.append('action', 'redeem_gift_card');
        fd.append('code', code);
        
        try {
            const r = await fetch('index.php', { method: 'POST', body: fd });
            const data = await r.json();
            
            const resultDiv = document.getElementById('redeemResult');
            if (data.success) {
                resultDiv.innerHTML = '<div class="result success"><strong>Success!</strong><br>' + data.message + '</div>';
                loadBalance();
                document.getElementById('redeemCode').value = '';
            } else {
                resultDiv.innerHTML = '<div class="result error"><strong>Error</strong><br>' + data.message + '</div>';
            }
        } catch(e) {
            alert('Error: ' + e.message);
        }
    }
    
    // Format code input
    document.getElementById('redeemCode').addEventListener('input', function(e) {
        let val = e.target.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
        if (val.length > 4) val = val.slice(0,4) + '-' + val.slice(4);
        if (val.length > 9) val = val.slice(0,9) + '-' + val.slice(9);
        e.target.value = val.slice(0, 14);
    });
    </script>
</body>
</html>