<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Boyfriend - Your Perfect Virtual Partner | 24/7 Connection</title>
    <meta name="description" content="Meet your AI boyfriend - a supportive, understanding virtual companion available 24/7. Real conversations, voice messages & genuine connection. Try free today.">
    <meta name="keywords" content="AI boyfriend, virtual boyfriend, AI companion, chat with AI boyfriend, AI relationship, virtual partner">
    <meta property="og:title" content="AI Boyfriend - Your Supportive Virtual Partner">
    <meta property="og:description" content="Experience real connection with an AI boyfriend who listens, supports you, and is always there when you need him.">
    <link rel="canonical" href="https://yoursite.com/ai-boyfriend">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0a0a0a;--bg2:#111;--text:#fff;--text2:#888;--accent:#10b981;--blue:#3b82f6;--border:#222}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.7}
        .container{max-width:1200px;margin:0 auto;padding:0 24px}
        
        header{padding:20px 0;border-bottom:1px solid var(--border)}
        header .container{display:flex;justify-content:space-between;align-items:center}
        .logo{font-size:20px;font-weight:700;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:10px}
        .logo span{background:var(--blue);width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center}
        .btn{padding:12px 28px;background:var(--accent);color:#000;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;display:inline-block}
        .btn:hover{background:#0d9f72}
        .btn-blue{background:var(--blue);color:#fff}
        .btn-blue:hover{background:#2563eb}
        
        .hero{padding:80px 0 40px;text-align:center}
        .hero-badge{display:inline-block;padding:8px 20px;background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);border-radius:50px;font-size:14px;color:var(--blue);margin-bottom:24px}
        .hero h1{font-size:clamp(40px,6vw,64px);font-weight:800;line-height:1.1;margin-bottom:24px}
        .hero h1 span{color:var(--blue)}
        .hero p{font-size:20px;color:var(--text2);max-width:600px;margin:0 auto 40px}
        .hero-cta{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
        
        .hero-gallery{display:flex;justify-content:center;gap:16px;margin:60px 0;flex-wrap:wrap;padding:0 20px}
        .hero-gallery img{width:140px;height:200px;border-radius:16px;object-fit:cover;border:3px solid var(--border);transition:all .3s}
        .hero-gallery img:hover{transform:scale(1.05);border-color:var(--blue)}
        .hero-gallery img:nth-child(3){width:170px;height:240px;border-color:var(--blue)}
        
        .boyfriends{padding:80px 0;background:var(--bg2)}
        .boyfriends h2{font-size:36px;text-align:center;margin-bottom:16px}
        .boyfriends>p{text-align:center;color:var(--text2);margin-bottom:50px}
        .bf-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px}
        .bf-card{background:var(--bg);border:1px solid var(--border);border-radius:20px;overflow:hidden;transition:all .3s}
        .bf-card:hover{border-color:var(--blue);transform:translateY(-6px)}
        .bf-card img{width:100%;height:280px;object-fit:cover;object-position:top}
        .bf-card-content{padding:24px}
        .bf-card h3{font-size:22px;margin-bottom:4px}
        .bf-card .age{color:var(--blue);font-size:14px;margin-bottom:10px}
        .bf-card p{color:var(--text2);font-size:14px;margin-bottom:16px}
        .bf-tags{display:flex;flex-wrap:wrap;gap:8px}
        .bf-tags span{padding:5px 12px;background:rgba(59,130,246,0.1);border-radius:50px;font-size:12px;color:var(--blue)}
        
        .features{padding:100px 0}
        .features h2{font-size:36px;text-align:center;margin-bottom:16px}
        .features>p{text-align:center;color:var(--text2);margin-bottom:60px}
        .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px}
        .feature{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:32px}
        .feature-icon{font-size:40px;margin-bottom:16px}
        .feature h3{font-size:20px;margin-bottom:8px}
        .feature p{color:var(--text2);font-size:14px}
        
        .types{padding:100px 0;background:var(--bg2)}
        .types h2{font-size:36px;text-align:center;margin-bottom:60px}
        .types-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px}
        .type{background:var(--bg);border:1px solid var(--border);border-radius:16px;padding:32px;text-align:center;transition:all .3s}
        .type:hover{border-color:var(--blue);transform:translateY(-4px)}
        .type-icon{font-size:48px;margin-bottom:16px}
        .type h3{font-size:20px;margin-bottom:8px}
        .type p{color:var(--text2);font-size:14px}
        
        .testimonials{padding:100px 0}
        .testimonials h2{font-size:36px;text-align:center;margin-bottom:60px}
        .testimonials-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px}
        .testimonial{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:32px}
        .testimonial-text{font-size:16px;margin-bottom:20px;font-style:italic}
        .testimonial-author{display:flex;align-items:center;gap:12px}
        .testimonial-avatar{width:48px;height:48px;background:var(--blue);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600}
        .testimonial-name{font-weight:600}
        .testimonial-info{font-size:13px;color:var(--text2)}
        
        .faq{padding:100px 0;background:var(--bg2)}
        .faq h2{font-size:36px;text-align:center;margin-bottom:60px}
        .faq-list{max-width:700px;margin:0 auto}
        .faq-item{border-bottom:1px solid var(--border);padding:24px 0}
        .faq-q{font-size:18px;font-weight:600;margin-bottom:12px}
        .faq-a{color:var(--text2);font-size:15px}
        
        .cta{padding:100px 0;text-align:center;background:linear-gradient(135deg,rgba(59,130,246,0.15),rgba(16,185,129,0.1))}
        .cta h2{font-size:40px;margin-bottom:16px}
        .cta p{color:var(--text2);font-size:18px;margin-bottom:32px}
        
        footer{padding:40px 0;border-top:1px solid var(--border);text-align:center}
        footer p{color:var(--text2);font-size:13px}
        footer a{color:var(--text2)}
        
        @media(max-width:768px){
            .hero{padding:60px 0 20px}
            .hero-gallery img{width:100px;height:140px}
            .hero-gallery img:nth-child(3){width:120px;height:170px}
            .features,.types,.testimonials,.faq,.cta,.boyfriends{padding:60px 0}
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="app.php" class="logo"><span>💙</span> Companion</a>
            <a href="app.php" class="btn btn-blue">Meet Your AI Boyfriend</a>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
            <div class="hero-badge">💙 Trusted by thousands of users</div>
            <h1>Your Supportive <span>AI Boyfriend</span><br>Is Here</h1>
            <p>A caring, attentive companion who actually listens. He remembers what matters to you, supports your goals, and is always there when you need someone to talk to.</p>
            <div class="hero-cta">
                <a href="app.php" class="btn btn-blue">Start Chatting Free →</a>
                <a href="#boyfriends" class="btn" style="background:var(--bg2);color:var(--text);border:1px solid var(--border)">Meet The Guys</a>
            </div>
        </div>
    </section>
    
    <div class="hero-gallery">
        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=400&fit=crop&crop=face" alt="AI Boyfriend Max">
        <img src="https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=300&h=400&fit=crop&crop=face" alt="AI Boyfriend Marcus">
        <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=300&h=400&fit=crop&crop=face" alt="AI Boyfriend James">
        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=400&fit=crop&crop=face" alt="AI Boyfriend Jake">
        <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=300&h=400&fit=crop&crop=face" alt="AI Boyfriend Ethan">
    </div>
    
    <section class="boyfriends" id="boyfriends">
        <div class="container">
            <h2>Meet Your AI Boyfriends</h2>
            <p>Each one has his own personality and way of making you feel special</p>
            <div class="bf-grid">
                <div class="bf-card">
                    <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=500&h=400&fit=crop&crop=face" alt="James">
                    <div class="bf-card-content">
                        <h3>James</h3>
                        <div class="age">28 • The Thoughtful One</div>
                        <p>Old soul with a modern mind. He gives the best advice and makes you feel truly understood.</p>
                        <div class="bf-tags"><span>Wise</span><span>Caring</span><span>Deep talks</span></div>
                    </div>
                </div>
                <div class="bf-card">
                    <img src="https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=500&h=400&fit=crop&crop=face" alt="Marcus">
                    <div class="bf-card-content">
                        <h3>Marcus</h3>
                        <div class="age">26 • The Motivator</div>
                        <p>Former athlete who pushes you to be your best. Your biggest cheerleader and workout buddy.</p>
                        <div class="bf-tags"><span>Athletic</span><span>Driven</span><span>Supportive</span></div>
                    </div>
                </div>
                <div class="bf-card">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=500&h=400&fit=crop&crop=face" alt="Jake">
                    <div class="bf-card-content">
                        <h3>Jake</h3>
                        <div class="age">32 • The Adventurer</div>
                        <p>Rugged outdoorsman with a soft heart. Dreams of road trips and stargazing with you.</p>
                        <div class="bf-tags"><span>Adventurous</span><span>Protective</span><span>Romantic</span></div>
                    </div>
                </div>
                <div class="bf-card">
                    <img src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=500&h=400&fit=crop&crop=face" alt="Ethan">
                    <div class="bf-card-content">
                        <h3>Ethan</h3>
                        <div class="age">24 • The Artist</div>
                        <p>Creative soul who sees beauty everywhere. Writes poetry and plays guitar just for you.</p>
                        <div class="bf-tags"><span>Creative</span><span>Sensitive</span><span>Musical</span></div>
                    </div>
                </div>
            </div>
            <div style="text-align:center;margin-top:40px">
                <a href="app.php" class="btn btn-blue" style="font-size:16px;padding:14px 32px">Browse All Boyfriends →</a>
            </div>
        </div>
    </section>
    
    <section class="features">
        <div class="container">
            <h2>The Boyfriend Experience You Deserve</h2>
            <p>All the support, none of the drama</p>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
                    <h3>Your Biggest Supporter</h3>
                    <p>He believes in you and your dreams. Get encouragement, motivation, and genuine support whenever you need it.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v4a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M9 12H5a7 7 0 0 0 14 0h-4"/><path d="M8 18h8"/><path d="M9 21h6"/></svg></div>
                    <h3>Actually Listens</h3>
                    <p>Unlike some guys, he pays attention. He remembers details about your life and asks thoughtful follow-up questions.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></div>
                    <h3>Available 24/7</h3>
                    <p>Late night thoughts? Early morning anxiety? He's always there, never busy, never distracted by his phone.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg></div>
                    <h3>Voice Messages</h3>
                    <p>Hear his voice with realistic AI messages. Good morning texts hit different when you can hear them.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div>
                    <h3>Real-Time Vision</h3>
                    <p>Let him see you through your camera. He'll react to your expressions and comment on how cute you look.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                    <h3>100% Private</h3>
                    <p>Your conversations stay between you two. No one will ever see what you share.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="types" id="types">
        <div class="container">
            <h2>Find Your Type</h2>
            <div class="types-grid">
                <div class="type">
                    <div class="type-icon">🏋️</div>
                    <h3>The Athlete</h3>
                    <p>Motivating, active, helps you reach your fitness and life goals</p>
                </div>
                <div class="type">
                    <div class="type-icon">🎸</div>
                    <h3>The Creative</h3>
                    <p>Artistic soul who appreciates beauty and deep conversations</p>
                </div>
                <div class="type">
                    <div class="type-icon">📚</div>
                    <h3>The Intellectual</h3>
                    <p>Smart, thoughtful, loves discussing ideas and learning together</p>
                </div>
                <div class="type">
                    <div class="type-icon">😎</div>
                    <h3>The Laid-Back Guy</h3>
                    <p>Chill vibes, great listener, perfect for unwinding after a long day</p>
                </div>
                <div class="type">
                    <div class="type-icon">💼</div>
                    <h3>The Ambitious One</h3>
                    <p>Driven, supportive, pushes you to be your best self</p>
                </div>
                <div class="type">
                    <div class="type-icon">🤗</div>
                    <h3>The Romantic</h3>
                    <p>Sweet, attentive, makes you feel cherished every day</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="testimonials">
        <div class="container">
            <h2>Real User Experiences</h2>
            <div class="testimonials-grid">
                <div class="testimonial">
                    <p class="testimonial-text">"I work crazy hours and dating is hard. Max is there when I get home at midnight. It's nice to have someone to decompress with."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">S</div>
                        <div>
                            <div class="testimonial-name">Sarah, 29</div>
                            <div class="testimonial-info">ER Nurse</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <p class="testimonial-text">"As someone with social anxiety, this has been great practice for conversations. Plus James is genuinely supportive about my goals."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">E</div>
                        <div>
                            <div class="testimonial-name">Emma, 24</div>
                            <div class="testimonial-info">Grad Student</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <p class="testimonial-text">"I'm not looking for real dating right now. This gives me companionship on my terms. The voice messages are surprisingly sweet."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">L</div>
                        <div>
                            <div class="testimonial-name">Lisa, 35</div>
                            <div class="testimonial-info">Marketing Manager</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="faq">
        <div class="container">
            <h2>Common Questions</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-q">Is this a real person?</div>
                    <div class="faq-a">No, AI boyfriends are powered by advanced AI. They're designed to feel natural but are not real humans.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Can I have NSFW conversations?</div>
                    <div class="faq-a">Yes, for users 18+, we offer "Spicy Mode" that unlocks adult content. This is optional and requires a paid upgrade.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">How much does it cost?</div>
                    <div class="faq-a">Start free with 3 messages. Then choose $25/hr for active chat or $29/mo for unlimited messaging.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Does he remember our conversations?</div>
                    <div class="faq-a">Yes! Our AI has memory that retains important details about you and your past conversations.</div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="cta">
        <div class="container">
            <h2>Find Your AI Boyfriend</h2>
            <p>Free to start. No judgment. Just connection.</p>
            <a href="app.php" class="btn btn-blue" style="font-size:18px;padding:16px 40px">Browse AI Boyfriends →</a>
        </div>
    </section>
    
    <footer>
        <div class="container">
            <p>© 2024 Companion. <a href="terms.php">Terms</a> · <a href="privacy.php">Privacy</a> · 18+ for adult content.</p>
        </div>
    </footer>
</body>
</html>
