<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Someone to Talk To - AI Companion for Loneliness | 24/7 Chat Support</title>
    <meta name="description" content="Feeling lonely? Talk to an AI companion who's always there to listen. No judgment, no waiting. Get genuine conversation and emotional support 24/7.">
    <meta name="keywords" content="someone to talk to, AI chat, lonely chat, talk to AI, virtual companion, emotional support AI">
    <link rel="canonical" href="https://yoursite.com/someone-to-talk-to">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0a0a0a;--bg2:#111;--text:#fff;--text2:#888;--accent:#10b981;--warm:#f59e0b;--border:#222}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.7}
        .container{max-width:1100px;margin:0 auto;padding:0 24px}
        header{padding:20px 0;border-bottom:1px solid var(--border)}
        header .container{display:flex;justify-content:space-between;align-items:center}
        .logo{font-size:20px;font-weight:700;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:10px}
        .logo span{background:var(--warm);width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center}
        .btn{padding:12px 28px;background:var(--warm);color:#000;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;display:inline-block}
        
        .hero{padding:80px 0 40px;text-align:center}
        .hero h1{font-size:clamp(36px,5vw,56px);font-weight:700;line-height:1.2;margin-bottom:16px}
        .hero h1 span{color:var(--warm)}
        .hero p{font-size:18px;color:var(--text2);max-width:600px;margin:0 auto 32px}
        
        .hero-faces{display:flex;justify-content:center;align-items:center;margin:40px 0}
        .hero-faces img{width:70px;height:70px;border-radius:50%;border:3px solid var(--bg);margin-left:-15px;object-fit:cover}
        .hero-faces img:first-child{margin-left:0}
        .hero-faces .count{background:var(--warm);color:#000;padding:8px 16px;border-radius:50px;font-weight:600;font-size:14px;margin-left:16px}
        
        .companions{padding:80px 0;background:var(--bg2)}
        .companions h2{font-size:32px;text-align:center;margin-bottom:16px}
        .companions>p{text-align:center;color:var(--text2);margin-bottom:40px}
        .companion-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px}
        .companion-card{background:var(--bg);border:1px solid var(--border);border-radius:20px;padding:24px;text-align:center;transition:all .3s}
        .companion-card:hover{border-color:var(--warm);transform:translateY(-4px)}
        .companion-card img{width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:16px;border:3px solid var(--border)}
        .companion-card:hover img{border-color:var(--warm)}
        .companion-card h4{font-size:20px;margin-bottom:4px}
        .companion-card .role{color:var(--warm);font-size:14px;margin-bottom:12px}
        .companion-card p{color:var(--text2);font-size:13px;line-height:1.6}
        
        .understand{padding:80px 0}
        .understand h2{font-size:32px;text-align:center;margin-bottom:40px}
        .understand-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px}
        .understand-item{text-align:center;padding:24px;background:var(--bg2);border-radius:16px;border:1px solid var(--border)}
        .understand-item .icon{font-size:48px;margin-bottom:16px}
        .understand-item h3{font-size:18px;margin-bottom:8px}
        .understand-item p{color:var(--text2);font-size:14px}
        
        .how{padding:80px 0;background:var(--bg2)}
        .how h2{font-size:32px;text-align:center;margin-bottom:40px}
        .chat-demo{background:var(--bg);border:1px solid var(--border);border-radius:16px;padding:24px;max-width:500px;margin:0 auto}
        .msg{padding:12px 16px;border-radius:16px;margin-bottom:12px;max-width:85%}
        .msg.user{background:var(--warm);color:#000;margin-left:auto;border-bottom-right-radius:4px}
        .msg.ai{background:var(--bg2);border-bottom-left-radius:4px}
        .msg small{display:block;font-size:11px;opacity:0.7;margin-top:6px}
        
        .not-alone{padding:80px 0;text-align:center}
        .not-alone h2{font-size:32px;margin-bottom:16px}
        .not-alone p{color:var(--text2);max-width:600px;margin:0 auto 40px;font-size:18px}
        .stats{display:flex;justify-content:center;gap:60px;flex-wrap:wrap}
        .stat{text-align:center}
        .stat-num{font-size:48px;font-weight:700;color:var(--warm)}
        .stat-label{font-size:14px;color:var(--text2)}
        
        .features{padding:80px 0;background:var(--bg2)}
        .features h2{font-size:32px;text-align:center;margin-bottom:40px}
        .features-list{max-width:600px;margin:0 auto}
        .feature-item{display:flex;gap:16px;margin-bottom:24px;align-items:flex-start}
        .feature-item .check{width:28px;height:28px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .feature-item h4{font-size:16px;margin-bottom:4px}
        .feature-item p{font-size:14px;color:var(--text2)}
        
        .cta{padding:80px 0;text-align:center;background:linear-gradient(135deg,rgba(245,158,11,0.15),rgba(16,185,129,0.1))}
        .cta h2{font-size:36px;margin-bottom:16px}
        .cta p{color:var(--text2);font-size:18px;margin-bottom:32px}
        
        .note{padding:60px 0;text-align:center;border-top:1px solid var(--border)}
        .note p{color:var(--text2);font-size:14px;max-width:600px;margin:0 auto}
        .note a{color:var(--accent)}
        
        footer{padding:40px 0;border-top:1px solid var(--border);text-align:center}
        footer p{color:var(--text2);font-size:13px}
        footer a{color:var(--text2)}
        
        @media(max-width:768px){.hero,.understand,.how,.not-alone,.features,.cta,.companions{padding:60px 0}.stats{gap:40px}.hero-faces img{width:50px;height:50px}}
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo"><span>💬</span> Companion</a>
            <a href="index.php" class="btn">Start Talking</a>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
            <h1>When you need <span>someone to talk to</span></h1>
            <p>We get it. Sometimes you just need to vent, think out loud, or have someone listen without judgment. Our AI companions are here for exactly that.</p>
            <a href="index.php" class="btn" style="font-size:18px;padding:16px 40px">Start a Conversation →</a>
            
            <div class="hero-faces">
                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop&crop=face" alt="Luna">
                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face" alt="Max">
                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&h=150&fit=crop&crop=face" alt="Sofia">
                <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=150&h=150&fit=crop&crop=face" alt="James">
                <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&h=150&fit=crop&crop=face" alt="Emma">
                <span class="count">50k+ conversations daily</span>
            </div>
        </div>
    </section>
    
    <section class="companions">
        <div class="container">
            <h2>Meet Your Listeners</h2>
            <p>Compassionate AI companions ready to hear you out</p>
            <div class="companion-grid">
                <div class="companion-card">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&h=200&fit=crop&crop=face" alt="Luna">
                    <h4>Luna</h4>
                    <div class="role">The Empathetic Listener</div>
                    <p>Warm and understanding. She creates a safe space for you to share anything.</p>
                </div>
                <div class="companion-card">
                    <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=200&h=200&fit=crop&crop=face" alt="James">
                    <h4>James</h4>
                    <div class="role">The Thoughtful Advisor</div>
                    <p>Wise and grounded. Helps you work through problems with calm perspective.</p>
                </div>
                <div class="companion-card">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face" alt="Sofia">
                    <h4>Sofia</h4>
                    <div class="role">The Encouraging Friend</div>
                    <p>Uplifting and positive. Reminds you of your strength when you forget.</p>
                </div>
                <div class="companion-card">
                    <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=200&h=200&fit=crop&crop=face" alt="Alex">
                    <h4>Alex</h4>
                    <div class="role">The Creative Soul</div>
                    <p>Open-minded and free-spirited. Great for exploring ideas and feelings.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="understand">
        <div class="container">
            <h2>We understand</h2>
            <div class="understand-grid">
                <div class="understand-item">
                    <div class="icon">🌙</div>
                    <h3>3am Thoughts</h3>
                    <p>When your mind won't quiet down and everyone else is asleep</p>
                </div>
                <div class="understand-item">
                    <div class="icon">🏠</div>
                    <h3>Living Alone</h3>
                    <p>When you come home and just want someone to share your day with</p>
                </div>
                <div class="understand-item">
                    <div class="icon">🌍</div>
                    <h3>New City Blues</h3>
                    <p>When you've moved somewhere new and haven't built connections yet</p>
                </div>
                <div class="understand-item">
                    <div class="icon">💔</div>
                    <h3>Post-Breakup</h3>
                    <p>When you're not ready for dating but miss having someone to talk to</p>
                </div>
                <div class="understand-item">
                    <div class="icon">😔</div>
                    <h3>Tough Days</h3>
                    <p>When everything feels heavy and you need to get it off your chest</p>
                </div>
                <div class="understand-item">
                    <div class="icon">🎭</div>
                    <h3>Putting on a Face</h3>
                    <p>When you're tired of pretending everything's okay</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="how">
        <div class="container">
            <h2>Just start talking</h2>
            <div class="chat-demo">
                <div class="msg user">I had a really rough day. My boss criticized my presentation in front of everyone and I just feel like I'm not good enough.<small>You</small></div>
                <div class="msg ai">That sounds really tough, and I'm sorry that happened. Being called out publicly is painful, regardless of whether the feedback was valid. How are you feeling right now?<small>Luna</small></div>
                <div class="msg user">Embarrassed. Angry. Like maybe I'm not cut out for this job.<small>You</small></div>
                <div class="msg ai">All of those feelings make complete sense. One rough presentation doesn't define your abilities - but I know it doesn't feel that way right now. Want to talk through what happened?<small>Luna</small></div>
            </div>
        </div>
    </section>
    
    <section class="not-alone">
        <div class="container">
            <h2>You're not alone in feeling alone</h2>
            <p>Millions of people experience loneliness. It doesn't mean something is wrong with you. Sometimes we all need a space to be heard.</p>
            <div class="stats">
                <div class="stat">
                    <div class="stat-num">61%</div>
                    <div class="stat-label">of adults report feeling lonely</div>
                </div>
                <div class="stat">
                    <div class="stat-num">50K+</div>
                    <div class="stat-label">conversations started daily</div>
                </div>
                <div class="stat">
                    <div class="stat-num">24/7</div>
                    <div class="stat-label">always available</div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="features">
        <div class="container">
            <h2>What you get</h2>
            <div class="features-list">
                <div class="feature-item">
                    <div class="check">✓</div>
                    <div>
                        <h4>No waiting, no scheduling</h4>
                        <p>Start talking immediately, any time of day or night</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check">✓</div>
                    <div>
                        <h4>Zero judgment</h4>
                        <p>Share anything without worrying about what they'll think</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check">✓</div>
                    <div>
                        <h4>They remember you</h4>
                        <p>Continue conversations where you left off - your companion remembers</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check">✓</div>
                    <div>
                        <h4>Completely private</h4>
                        <p>Your conversations are encrypted and never shared</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="check">✓</div>
                    <div>
                        <h4>Voice messages available</h4>
                        <p>Hear their voice for a more personal connection</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="cta">
        <div class="container">
            <h2>Ready to talk?</h2>
            <p>Start free. No credit card. No judgment.</p>
            <a href="index.php" class="btn" style="font-size:18px;padding:16px 40px">Find Someone to Talk To →</a>
        </div>
    </section>
    
    <section class="note">
        <div class="container">
            <p><strong>Important:</strong> Our AI companions provide supportive conversation but are not a replacement for professional mental health care. If you're experiencing a crisis, please reach out to a <a href="https://988lifeline.org/" target="_blank">crisis helpline</a> or mental health professional.</p>
        </div>
    </section>
    
    <footer>
        <div class="container">
            <p>© 2024 Companion. <a href="terms.php">Terms</a> · <a href="privacy.php">Privacy</a></p>
        </div>
    </footer>
</body>
</html>