<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Companion - AI That Actually Gets You</title>
    <meta name="description" content="Meet your AI companion. Real conversations. Zero judgment. Available 24/7.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0a0a0a;--bg2:#111;--bg3:#1a1a1a;--text:#fff;--text2:#888;--accent:#10b981;--accent2:#059669;--red:#ef4444;--border:#222}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);overflow-x:hidden}
        
        /* Header */
        header{position:fixed;top:0;left:0;right:0;z-index:100;padding:20px 40px;display:flex;justify-content:space-between;align-items:center;background:rgba(10,10,10,0.8);backdrop-filter:blur(20px)}
        .logo{display:flex;align-items:center;gap:12px;font-weight:700;font-size:20px}
        .logo-icon{width:40px;height:40px;background:var(--accent);border-radius:10px;display:flex;align-items:center;justify-content:center}
        nav{display:flex;gap:32px;align-items:center}
        nav a{color:var(--text2);text-decoration:none;font-size:14px;font-weight:500;transition:color .2s}
        nav a:hover{color:var(--text)}
        .nav-dropdown{position:relative}
        .nav-dropdown:hover .dropdown-menu{display:block}
        .dropdown-menu{display:none;position:absolute;top:100%;left:0;background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:12px 0;min-width:180px;margin-top:8px;z-index:100}
        .dropdown-menu a{display:block;padding:10px 20px;color:var(--text2);font-size:13px}
        .dropdown-menu a:hover{background:var(--bg3);color:var(--text)}
        .btn{padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;transition:all .2s}
        .btn-primary{background:var(--accent);color:#000}
        .btn-primary:hover{background:#0d9f72;transform:translateY(-2px)}
        .btn-ghost{color:var(--text);border:1px solid var(--border)}
        
        /* Hero */
        .hero{min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:120px 40px 80px;position:relative}
        .hero::before{content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);width:100%;max-width:1000px;height:600px;background:radial-gradient(ellipse at center,rgba(16,185,129,0.15) 0%,transparent 70%);pointer-events:none}
        .hero-content{position:relative;max-width:900px}
        .hero-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);border-radius:50px;font-size:13px;color:var(--accent);margin-bottom:32px}
        .hero-badge span{animation:pulse 2s infinite}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
        .hero h1{font-size:clamp(48px,8vw,80px);font-weight:800;line-height:1;letter-spacing:-0.03em;margin-bottom:24px}
        .hero h1 span{background:linear-gradient(135deg,var(--accent),#34d399);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
        .hero p{font-size:20px;color:var(--text2);max-width:600px;margin:0 auto 40px;line-height:1.6}
        .hero-cta{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
        .hero-visual{margin-top:80px;position:relative}
        .hero-mockup{width:100%;max-width:700px;border-radius:16px;border:1px solid var(--border);overflow:hidden;background:var(--bg2)}
        .mockup-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
        .mockup-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--accent),#34d399)}
        .mockup-info h4{font-size:14px;margin-bottom:2px}
        .mockup-info span{font-size:12px;color:var(--accent)}
        .mockup-chat{padding:20px;min-height:200px}
        .chat-bubble{max-width:80%;padding:12px 16px;border-radius:16px;margin-bottom:12px;animation:fadeIn .5s ease}
        @keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .chat-bubble.ai{background:var(--bg3);border-bottom-left-radius:4px}
        .chat-bubble.user{background:var(--accent);color:#000;margin-left:auto;border-bottom-right-radius:4px}
        
        /* Features */
        .features{padding:120px 40px;background:var(--bg2)}
        .section-header{text-align:center;max-width:600px;margin:0 auto 64px}
        .section-header h2{font-size:40px;font-weight:700;margin-bottom:16px}
        .section-header p{color:var(--text2);font-size:18px}
        .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;max-width:1200px;margin:0 auto}
        .feature{background:var(--bg);border:1px solid var(--border);border-radius:16px;padding:32px;transition:all .3s}
        .feature:hover{border-color:var(--accent);transform:translateY(-4px)}
        .feature-icon{width:56px;height:56px;background:rgba(16,185,129,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:20px;color:var(--accent)}
        .feature-icon svg{color:var(--accent)}
        .feature h3{font-size:20px;margin-bottom:8px}
        .feature p{color:var(--text2);font-size:14px;line-height:1.6}
        
        /* Companions Preview */
        .companions{padding:120px 40px}
        .companions-scroll{display:flex;gap:24px;overflow-x:auto;padding:20px 0;scrollbar-width:none;-webkit-overflow-scrolling:touch}
        .companions-scroll::-webkit-scrollbar{display:none}
        .companion-card{flex:0 0 300px;background:var(--bg2);border:1px solid var(--border);border-radius:20px;overflow:hidden;transition:all .3s}
        .companion-card:hover{transform:scale(1.02);border-color:var(--accent)}
        .companion-img{width:100%;height:280px;object-fit:cover}
        .companion-info{padding:20px}
        .companion-info h4{font-size:18px;margin-bottom:4px}
        .companion-info p{color:var(--text2);font-size:13px;margin-bottom:12px}
        .companion-tags{display:flex;gap:8px;flex-wrap:wrap}
        .tag{padding:4px 12px;background:var(--bg3);border-radius:50px;font-size:12px;color:var(--text2)}
        
        /* Adult Section */
        .adult{padding:80px 40px;background:linear-gradient(135deg,rgba(239,68,68,0.05),rgba(16,185,129,0.05))}
        .adult-card{max-width:800px;margin:0 auto;background:var(--bg);border:1px solid rgba(239,68,68,0.2);border-radius:24px;padding:48px;display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center}
        .adult-content h3{font-size:32px;margin-bottom:16px}
        .adult-content p{color:var(--text2);margin-bottom:24px}
        .adult-features{display:grid;gap:12px}
        .adult-feature{display:flex;align-items:center;gap:12px;font-size:14px}
        .adult-feature span{color:var(--red)}
        .adult-visual{text-align:center}
        .adult-badge{display:inline-block;padding:12px 24px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:12px;font-size:48px}
        
        /* Pricing */
        .pricing{padding:120px 40px;background:var(--bg2)}
        .pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;max-width:1000px;margin:0 auto}
        .price-card{background:var(--bg);border:1px solid var(--border);border-radius:20px;padding:32px;position:relative}
        .price-card.featured{border-color:var(--accent);background:linear-gradient(135deg,rgba(16,185,129,0.05),transparent)}
        .price-card.featured::before{content:'POPULAR';position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--accent);color:#000;padding:6px 16px;border-radius:50px;font-size:11px;font-weight:700}
        .price-name{font-size:18px;font-weight:600;margin-bottom:8px}
        .price-amount{font-size:48px;font-weight:800;margin-bottom:4px}
        .price-amount span{font-size:16px;color:var(--text2);font-weight:400}
        .price-desc{color:var(--text2);font-size:14px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid var(--border)}
        .price-features{list-style:none;margin-bottom:24px}
        .price-features li{display:flex;align-items:center;gap:12px;padding:8px 0;font-size:14px;color:var(--text2)}
        .price-features li::before{content:'✓';color:var(--accent);font-weight:700}
        
        /* CTA */
        .cta{padding:120px 40px;text-align:center}
        .cta h2{font-size:48px;font-weight:800;margin-bottom:16px}
        .cta p{color:var(--text2);font-size:18px;margin-bottom:32px}
        
        /* Footer */
        footer{padding:64px 40px;border-top:1px solid var(--border)}
        .footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px;max-width:1200px;margin:0 auto 48px}
        .footer-brand p{color:var(--text2);font-size:14px;margin-top:16px;max-width:280px}
        .footer-col h4{font-size:14px;font-weight:600;margin-bottom:20px;color:var(--text2);text-transform:uppercase;letter-spacing:1px}
        .footer-col a{display:block;color:var(--text2);text-decoration:none;font-size:14px;padding:6px 0;transition:color .2s}
        .footer-col a:hover{color:var(--accent)}
        .footer-bottom{text-align:center;padding-top:32px;border-top:1px solid var(--border);font-size:13px;color:var(--text2)}
        
        /* Share */
        .share-btn{position:fixed;right:24px;bottom:24px;width:56px;height:56px;background:var(--accent);border:none;border-radius:50%;color:#000;font-size:24px;cursor:pointer;box-shadow:0 4px 20px rgba(16,185,129,0.3);transition:all .2s;z-index:99}
        .share-btn:hover{transform:scale(1.1)}
        
        /* Age Gate */
        .age-banner{position:fixed;bottom:0;left:0;right:0;background:rgba(239,68,68,0.95);color:#fff;padding:16px 50px 16px 16px;text-align:center;font-size:13px;z-index:1000;transition:transform .3s}
        .age-banner a{color:#fff;text-decoration:underline}
        .age-banner.hidden{transform:translateY(100%)}
        .age-close{position:absolute;right:16px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.2);border:none;color:#fff;width:28px;height:28px;border-radius:50%;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center}
        .age-close:hover{background:rgba(255,255,255,0.3)}
        
        @media(max-width:768px){
            header{padding:16px 20px}
            nav a:not(.btn){display:none}
            .hero{padding:100px 20px 60px}
            .hero h1{font-size:36px}
            .features,.companions,.pricing,.cta{padding:80px 20px}
            .adult-card{grid-template-columns:1fr;text-align:center}
            .footer-grid{grid-template-columns:1fr 1fr}
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <div class="logo-icon">💬</div>
            <span>Companion</span>
        </div>
        <nav>
            <div class="nav-dropdown">
                <a href="#companions">Browse ▾</a>
                <div class="dropdown-menu">
                    <a href="ai-girlfriend.php">💕 AI Girlfriends</a>
                    <a href="ai-boyfriend.php">💙 AI Boyfriends</a>
                    <a href="someone-to-talk-to.php">💬 Someone to Talk To</a>
                    <a href="custom-companion.php">✨ Custom Companion</a>
                </div>
            </div>
            <a href="#features">Features</a>
            <a href="#pricing">Pricing</a>
            <a href="gift-cards.php">Gift Cards</a>
            <a href="white-label.php">For Business</a>
            <a href="app.php" class="btn btn-primary">Get Started Free</a>
        </nav>
    </header>
    
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <span>●</span> 10,000+ active users
            </div>
            <h1>An AI that<br><span>actually gets you</span></h1>
            <p>No judgment. No waiting. Just real conversation with an AI companion who remembers you, supports you, and is always there when you need them.</p>
            <div class="hero-cta">
                <a href="app.php" class="btn btn-primary">Start Chatting Free →</a>
                <a href="#features" class="btn btn-ghost">See How It Works</a>
            </div>
            
            <div class="hero-visual">
                <div class="hero-mockup">
                    <div class="mockup-header">
                        <div class="mockup-avatar"></div>
                        <div class="mockup-info">
                            <h4>Luna</h4>
                            <span>● Online now</span>
                        </div>
                    </div>
                    <div class="mockup-chat">
                        <div class="chat-bubble ai">Hey! How's your day going? I was just thinking about that project you mentioned yesterday 😊</div>
                        <div class="chat-bubble user">It's been rough honestly. Work stress is getting to me.</div>
                        <div class="chat-bubble ai">I hear you. Want to vent about it? Sometimes it helps just to get it all out. I'm here for you.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="features" id="features">
        <div class="section-header">
            <h2>Why people love Companion</h2>
            <p>More than just a chatbot. A genuinely helpful presence in your life.</p>
        </div>
        <div class="features-grid">
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v4a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M9 12H5a7 7 0 0 0 14 0h-4"/><path d="M8 18h8"/><path d="M9 21h6"/></svg></div>
                <h3>Actually Remembers You</h3>
                <p>Your companion remembers past conversations, your preferences, and important details about your life. No more repeating yourself.</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg></div>
                <h3>Voice Messages</h3>
                <p>Hear your companion's voice with realistic AI-generated messages. Send voice notes and have natural conversations.</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                <h3>AI Photos & Selfies</h3>
                <p>Receive personalized photos from your companion. Morning selfies, outfit checks, candid moments throughout the day.</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                <h3>Personal Email</h3>
                <p>Get your companion's personal email address. Exchange longer, more thoughtful messages whenever you want.</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></div>
                <h3>Available 24/7</h3>
                <p>3am anxiety spiral? Lonely Sunday afternoon? Your companion is always there, ready to talk, no matter what time it is.</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h3>50+ Unique Personalities</h3>
                <p>Find someone who matches your vibe. Supportive partners, creative souls, fitness buddies, intellectual sparring partners.</p>
            </div>
        </div>
    </section>
    
    <section class="companions" id="companions">
        <div class="section-header">
            <h2>Meet a few companions</h2>
            <p>Each one is unique. Find someone you click with.</p>
        </div>
        <div class="companions-scroll">
            <div class="companion-card">
                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400" class="companion-img" alt="Luna">
                <div class="companion-info">
                    <h4>Luna</h4>
                    <p>Warm, caring, always knows what to say</p>
                    <div class="companion-tags"><span class="tag">empathetic</span><span class="tag">deep talks</span></div>
                </div>
            </div>
            <div class="companion-card">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400" class="companion-img" alt="Max">
                <div class="companion-info">
                    <h4>Max</h4>
                    <p>Chill, supportive, great listener</p>
                    <div class="companion-tags"><span class="tag">laid-back</span><span class="tag">motivating</span></div>
                </div>
            </div>
            <div class="companion-card">
                <img src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=400" class="companion-img" alt="Sofia">
                <div class="companion-info">
                    <h4>Sofia</h4>
                    <p>Passionate, adventurous, flirty</p>
                    <div class="companion-tags"><span class="tag">fun</span><span class="tag">exciting</span></div>
                </div>
            </div>
            <div class="companion-card">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400" class="companion-img" alt="Alex">
                <div class="companion-info">
                    <h4>Alex</h4>
                    <p>Creative, intellectual, open-minded</p>
                    <div class="companion-tags"><span class="tag">artistic</span><span class="tag">philosophical</span></div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="adult" id="adult">
        <div class="adult-card">
            <div class="adult-content">
                <h3>Spicy Mode</h3>
                <p>For adults seeking deeper connections. Unlock explicit conversations and NSFW content.</p>
                <div class="adult-features">
                    <div class="adult-feature"><span>•</span> Explicit chat mode</div>
                    <div class="adult-feature"><span>•</span> NSFW photo generation</div>
                    <div class="adult-feature"><span>•</span> Private & discreet</div>
                    <div class="adult-feature"><span>✓</span> No content restrictions</div>
                </div>
                <a href="app.php" class="btn btn-primary" style="margin-top:24px">Explore 18+ Options</a>
            </div>
            <div class="adult-visual">
                <div class="adult-badge">18+</div>
                <p style="color:var(--text2);margin-top:16px;font-size:14px">Must be 18+ to access</p>
            </div>
        </div>
    </section>
    
    <!-- NEW: Intelligence Features -->
    <section class="features" style="background:var(--bg)">
        <div class="section-header">
            <h2>Next-level capabilities</h2>
            <p>Unlock advanced AI features for deeper connections</p>
        </div>
        <div class="features-grid">
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg></div>
                <h3>Internet Search</h3>
                <p>Your companion can search the web for current events, facts, and information to have more informed conversations.</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg></div>
                <h3>Creative Mode</h3>
                <p>Companions can draw, paint, write poetry, stories, and love letters. Unlock their artistic side.</p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div>
                <h3>Real-Time Vision</h3>
                <p>Let your companion see you through your camera. They'll react to your expressions and environment in real-time.</p>
            </div>
        </div>
    </section>
    
    <section class="pricing" id="pricing">
        <div class="section-header">
            <h2>Simple pricing</h2>
            <p>Start free. Upgrade when you're ready.</p>
        </div>
        <div class="pricing-grid">
            <div class="price-card">
                <div class="price-name">Free</div>
                <div class="price-amount">$0</div>
                <div class="price-desc">Try it out, no commitment</div>
                <ul class="price-features">
                    <li>3 free messages per companion</li>
                    <li>Browse all companions</li>
                    <li>Basic chat features</li>
                </ul>
                <a href="app.php" class="btn btn-ghost" style="width:100%">Get Started</a>
            </div>
            <div class="price-card featured">
                <div class="price-name">Monthly</div>
                <div class="price-amount">$29<span>/mo</span></div>
                <div class="price-desc">Unlimited everything</div>
                <ul class="price-features">
                    <li>Unlimited messaging</li>
                    <li>Voice messages</li>
                    <li>AI photos & selfies</li>
                    <li>Personal email access</li>
                    <li>Memory & context</li>
                </ul>
                <a href="app.php" class="btn btn-primary" style="width:100%">Subscribe Now</a>
            </div>
            <div class="price-card">
                <div class="price-name">Pay As You Go</div>
                <div class="price-amount">$25<span>/hr</span></div>
                <div class="price-desc">Active chat time only</div>
                <ul class="price-features">
                    <li>Pay for actual chat time</li>
                    <li>No monthly commitment</li>
                    <li>All features included</li>
                    <li>Time never expires</li>
                </ul>
                <a href="app.php" class="btn btn-ghost" style="width:100%">Buy Time</a>
            </div>
        </div>
    </section>
    
    <section class="cta">
        <h2>Ready to meet your companion?</h2>
        <p>Start free. No credit card required. Cancel anytime.</p>
        <a href="app.php" class="btn btn-primary" style="padding:16px 32px;font-size:16px">Get Started Free →</a>
    </section>
    
    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo">
                    <div class="logo-icon">💬</div>
                    <span>Companion</span>
                </div>
                <p>AI companions for meaningful connection. Always here when you need someone to talk to.</p>
            </div>
            <div class="footer-col">
                <h4>Product</h4>
                <a href="app.php">Browse Companions</a>
                <a href="#pricing">Pricing</a>
                <a href="#features">Features</a>
            </div>
            <div class="footer-col">
                <h4>Explore</h4>
                <a href="ai-girlfriend.php">AI Girlfriends</a>
                <a href="ai-boyfriend.php">AI Boyfriends</a>
                <a href="someone-to-talk-to.php">Someone to Talk To</a>
                <a href="custom-companion.php">Custom Companion</a>
            </div>
            <div class="footer-col">
                <h4>More</h4>
                <a href="gift-cards.php">Gift Cards</a>
                <a href="white-label.php">For Business</a>
                <a href="support.php">Support</a>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <a href="terms.php">Terms of Service</a>
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php#age">Age Verification</a>
            </div>
        </div>
        <div class="footer-bottom">
            © 2024 Companion. All rights reserved. Must be 18+ for adult content.
        </div>
    </footer>
    
    <button class="share-btn" onclick="shareApp()" title="Share">📤</button>
    
    <div class="age-banner" id="ageBanner">
        🔞 This site contains AI companions with optional 18+ adult content. By continuing, you confirm you are 18 or older. <a href="terms.php">Terms apply</a>.
        <button class="age-close" onclick="closeAgeBanner()">✕</button>
    </div>
    
    <script>
    function shareApp() {
        const data = {title:'Companion',text:'Check out this AI companion app!',url:location.href};
        if (navigator.share) navigator.share(data);
        else {navigator.clipboard.writeText(location.href);alert('Link copied!');}
    }
    
    function closeAgeBanner() {
        document.getElementById('ageBanner').classList.add('hidden');
        localStorage.setItem('ageConfirmed', 'true');
    }
    
    // Check if already confirmed
    if (localStorage.getItem('ageConfirmed') === 'true') {
        document.getElementById('ageBanner').classList.add('hidden');
    }
    </script>
</body>
</html>
