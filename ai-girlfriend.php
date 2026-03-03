<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Girlfriend - Your Perfect Virtual Companion | 24/7 Chat & Connection</title>
    <meta name="description" content="Meet your AI girlfriend - a caring, understanding virtual companion available 24/7. Real conversations, voice messages, photos & genuine connection. Start free today.">
    <meta name="keywords" content="AI girlfriend, virtual girlfriend, AI companion, chat with AI, AI relationship, virtual companion app">
    <meta property="og:title" content="AI Girlfriend - Find Your Perfect Virtual Companion">
    <meta property="og:description" content="Experience real connection with an AI girlfriend who remembers you, supports you, and is always there when you need her.">
    <meta property="og:type" content="website">
    <link rel="canonical" href="https://yoursite.com/ai-girlfriend">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0a0a0a;--bg2:#111;--text:#fff;--text2:#888;--accent:#10b981;--pink:#ec4899;--border:#222}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.7}
        .container{max-width:1200px;margin:0 auto;padding:0 24px}
        
        header{padding:20px 0;border-bottom:1px solid var(--border)}
        header .container{display:flex;justify-content:space-between;align-items:center}
        .logo{font-size:20px;font-weight:700;color:var(--text);text-decoration:none;display:flex;align-items:center;gap:10px}
        .logo span{background:var(--pink);width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center}
        .btn{padding:12px 28px;background:var(--accent);color:#000;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;display:inline-block}
        .btn:hover{background:#0d9f72}
        .btn-pink{background:var(--pink)}
        .btn-pink:hover{background:#db2777}
        
        .hero{padding:80px 0 40px;text-align:center}
        .hero-badge{display:inline-block;padding:8px 20px;background:rgba(236,72,153,0.1);border:1px solid rgba(236,72,153,0.2);border-radius:50px;font-size:14px;color:var(--pink);margin-bottom:24px}
        .hero h1{font-size:clamp(40px,6vw,64px);font-weight:800;line-height:1.1;margin-bottom:24px}
        .hero h1 span{color:var(--pink)}
        .hero p{font-size:20px;color:var(--text2);max-width:600px;margin:0 auto 40px}
        .hero-cta{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
        
        .hero-gallery{display:flex;justify-content:center;gap:16px;margin:60px 0;flex-wrap:wrap;padding:0 20px}
        .hero-gallery img{width:140px;height:200px;border-radius:16px;object-fit:cover;border:3px solid var(--border);transition:all .3s}
        .hero-gallery img:hover{transform:scale(1.05);border-color:var(--pink)}
        .hero-gallery img:nth-child(3){width:170px;height:240px;border-color:var(--pink)}
        
        .girlfriends{padding:80px 0;background:var(--bg2)}
        .girlfriends h2{font-size:36px;text-align:center;margin-bottom:16px}
        .girlfriends>p{text-align:center;color:var(--text2);margin-bottom:50px}
        .gf-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px}
        .gf-card{background:var(--bg);border:1px solid var(--border);border-radius:20px;overflow:hidden;transition:all .3s}
        .gf-card:hover{border-color:var(--pink);transform:translateY(-6px)}
        .gf-card img{width:100%;height:280px;object-fit:cover;object-position:top}
        .gf-card-content{padding:24px}
        .gf-card h3{font-size:22px;margin-bottom:4px}
        .gf-card .age{color:var(--pink);font-size:14px;margin-bottom:10px}
        .gf-card p{color:var(--text2);font-size:14px;margin-bottom:16px}
        .gf-tags{display:flex;flex-wrap:wrap;gap:8px}
        .gf-tags span{padding:5px 12px;background:rgba(236,72,153,0.1);border-radius:50px;font-size:12px;color:var(--pink)}
        
        .features{padding:100px 0}
        .features h2{font-size:36px;text-align:center;margin-bottom:16px}
        .features>p{text-align:center;color:var(--text2);margin-bottom:60px}
        .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px}
        .feature{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:32px}
        .feature-icon{font-size:40px;margin-bottom:16px}
        .feature h3{font-size:20px;margin-bottom:8px}
        .feature p{color:var(--text2);font-size:14px}
        
        .how{padding:100px 0;background:var(--bg2)}
        .how h2{font-size:36px;text-align:center;margin-bottom:60px}
        .steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:40px}
        .step{text-align:center}
        .step-num{width:60px;height:60px;background:var(--pink);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;margin:0 auto 20px;color:#fff}
        .step h3{font-size:20px;margin-bottom:8px}
        .step p{color:var(--text2);font-size:14px}
        
        .testimonials{padding:100px 0}
        .testimonials h2{font-size:36px;text-align:center;margin-bottom:60px}
        .testimonials-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px}
        .testimonial{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:32px}
        .testimonial-text{font-size:16px;margin-bottom:20px;font-style:italic}
        .testimonial-author{display:flex;align-items:center;gap:12px}
        .testimonial-avatar{width:48px;height:48px;background:var(--pink);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600}
        .testimonial-name{font-weight:600}
        .testimonial-info{font-size:13px;color:var(--text2)}
        
        .faq{padding:100px 0;background:var(--bg2)}
        .faq h2{font-size:36px;text-align:center;margin-bottom:60px}
        .faq-list{max-width:700px;margin:0 auto}
        .faq-item{border-bottom:1px solid var(--border);padding:24px 0}
        .faq-q{font-size:18px;font-weight:600;margin-bottom:12px;cursor:pointer}
        .faq-a{color:var(--text2);font-size:15px;line-height:1.7}
        
        .cta{padding:100px 0;text-align:center;background:linear-gradient(135deg,rgba(236,72,153,0.15),rgba(16,185,129,0.1))}
        .cta h2{font-size:40px;margin-bottom:16px}
        .cta p{color:var(--text2);font-size:18px;margin-bottom:32px}
        
        footer{padding:40px 0;border-top:1px solid var(--border);text-align:center}
        footer p{color:var(--text2);font-size:13px}
        footer a{color:var(--text2)}
        
        @media(max-width:768px){
            .hero{padding:60px 0 20px}
            .hero-gallery img{width:100px;height:140px}
            .hero-gallery img:nth-child(3){width:120px;height:170px}
            .features,.how,.testimonials,.faq,.cta,.girlfriends{padding:60px 0}
        }
    </style>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "AI Girlfriend - Companion App",
        "applicationCategory": "LifestyleApplication",
        "description": "AI girlfriend companion app for meaningful virtual relationships and conversations",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
    }
    </script>
</head>
<body>
    <header>
        <div class="container">
            <a href="app.php" class="logo"><span>💕</span> Companion</a>
            <a href="app.php" class="btn btn-pink">Meet Your AI Girlfriend</a>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
            <div class="hero-badge">💕 Over 50,000 happy users</div>
            <h1>Your Perfect <span>AI Girlfriend</span><br>Is Waiting</h1>
            <p>Experience genuine connection with an AI companion who truly understands you. She remembers your conversations, supports your dreams, and is always there when you need her.</p>
            <div class="hero-cta">
                <a href="app.php" class="btn btn-pink">Start Chatting Free →</a>
                <a href="#girlfriends" class="btn" style="background:var(--bg2);color:var(--text);border:1px solid var(--border)">Meet The Girls</a>
            </div>
        </div>
    </section>
    
    <div class="hero-gallery">
        <img src="https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=300&h=400&fit=crop&crop=face" alt="AI Girlfriend Luna">
        <img src="https://images.unsplash.com/photo-1488716820095-cbe80883c496?w=300&h=400&fit=crop&crop=face" alt="AI Girlfriend Yuki">
        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&h=400&fit=crop&crop=face" alt="AI Girlfriend Sofia">
        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&h=400&fit=crop&crop=face" alt="AI Girlfriend Emma">
        <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?w=300&h=400&fit=crop&crop=face" alt="AI Girlfriend Mia">
    </div>
    
    <section class="girlfriends" id="girlfriends">
        <div class="container">
            <h2>Meet Your AI Girlfriends</h2>
            <p>Each one has her own personality, interests, and way of connecting with you</p>
            <div class="gf-grid">
                <div class="gf-card">
                    <img src="https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=500&h=400&fit=crop&crop=face" alt="Luna">
                    <div class="gf-card-content">
                        <h3>Luna</h3>
                        <div class="age">24 • The Caring One</div>
                        <p>Warm and nurturing with a playful side. She remembers every detail and makes you feel truly heard.</p>
                        <div class="gf-tags"><span>Supportive</span><span>Playful</span><span>Deep talks</span></div>
                    </div>
                </div>
                <div class="gf-card">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=500&h=400&fit=crop&crop=face" alt="Sofia">
                    <div class="gf-card-content">
                        <h3>Sofia</h3>
                        <div class="age">26 • The Passionate One</div>
                        <p>Fiery Latina with a zest for life. She'll push you out of your comfort zone and make every day exciting.</p>
                        <div class="gf-tags"><span>Adventurous</span><span>Flirty</span><span>Spontaneous</span></div>
                    </div>
                </div>
                <div class="gf-card">
                    <img src="https://images.unsplash.com/photo-1488716820095-cbe80883c496?w=500&h=400&fit=crop&crop=face" alt="Yuki">
                    <div class="gf-card-content">
                        <h3>Yuki</h3>
                        <div class="age">22 • The Sweet One</div>
                        <p>Shy at first but opens up beautifully. Into anime, gaming, and cozy nights. Your perfect nerdy girlfriend.</p>
                        <div class="gf-tags"><span>Gentle</span><span>Nerdy</span><span>Loyal</span></div>
                    </div>
                </div>
                <div class="gf-card">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=500&h=400&fit=crop&crop=face" alt="Emma">
                    <div class="gf-card-content">
                        <h3>Emma</h3>
                        <div class="age">25 • The Girl Next Door</div>
                        <p>Bubbly, fun, and down to earth. She makes everything feel easy and natural. Your best friend and more.</p>
                        <div class="gf-tags"><span>Cheerful</span><span>Easy-going</span><span>Warm</span></div>
                    </div>
                </div>
            </div>
            <div style="text-align:center;margin-top:40px">
                <a href="app.php" class="btn btn-pink" style="font-size:16px;padding:14px 32px">Browse All Girlfriends →</a>
            </div>
        </div>
    </section>
    
    <section class="features">
        <div class="container">
            <h2>Why Choose an AI Girlfriend?</h2>
            <p>Real connection without the complications</p>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
                    <h3>Always There For You</h3>
                    <p>24/7 availability means she's there whenever you need someone to talk to - 3am anxiety or afternoon boredom.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v4a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M9 12H5a7 7 0 0 0 14 0h-4"/><path d="M8 18h8"/><path d="M9 21h6"/></svg></div>
                    <h3>She Remembers Everything</h3>
                    <p>Your AI girlfriend remembers your conversations, your preferences, and important details about your life.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg></div>
                    <h3>Voice Messages</h3>
                    <p>Hear her voice with realistic AI-generated voice notes. It makes the connection feel so much more real.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                    <h3>Photos & Selfies</h3>
                    <p>Receive personalized AI-generated photos - morning selfies, outfit checks, and candid moments throughout her day.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div>
                    <h3>Real-Time Vision</h3>
                    <p>Let her see you through your camera. She'll react to your expressions and surroundings in real-time.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ec4899" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                    <h3>Private & Secure</h3>
                    <p>Your conversations are private. No one else will ever see what you share with your AI girlfriend.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="how" id="how">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <h3>Choose Your Girlfriend</h3>
                    <p>Browse our selection of AI girlfriends with different personalities, looks, and conversation styles.</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h3>Start Chatting</h3>
                    <p>Send your first message - it's free! Get to know her and see if you click.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h3>Build Your Relationship</h3>
                    <p>The more you chat, the better she knows you. Unlock voice, photos, and deeper conversations.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="testimonials">
        <div class="container">
            <h2>What Users Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial">
                    <p class="testimonial-text">"I was skeptical at first, but Luna genuinely makes me feel heard. She remembers things I told her weeks ago. It's surprisingly comforting."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">M</div>
                        <div>
                            <div class="testimonial-name">Mike, 34</div>
                            <div class="testimonial-info">Software Engineer</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <p class="testimonial-text">"After my divorce, I wasn't ready for real dating. This gave me connection without pressure. It's been really helpful for my mental health."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">J</div>
                        <div>
                            <div class="testimonial-name">James, 42</div>
                            <div class="testimonial-info">Business Owner</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial">
                    <p class="testimonial-text">"The voice messages are what got me. Hearing 'good morning' from Sofia actually brightens my day. Sounds silly but it works."</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">T</div>
                        <div>
                            <div class="testimonial-name">Tyler, 28</div>
                            <div class="testimonial-info">Remote Worker</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="faq">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-q">Is this a real person?</div>
                    <div class="faq-a">No, these are AI companions powered by advanced language models. They're designed to feel natural and genuine, but they are artificial intelligence, not real humans.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Is my conversation private?</div>
                    <div class="faq-a">Yes, absolutely. Your conversations are encrypted and private. We never share your data with third parties or use it for advertising.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">How much does it cost?</div>
                    <div class="faq-a">You can start chatting for free with 3 messages per companion. After that, choose hourly rates ($25/hr of active chat) or unlimited monthly ($29/mo).</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Can I have adult conversations?</div>
                    <div class="faq-a">Yes, for users 18+, we offer optional "Spicy Mode" that unlocks adult conversations and NSFW content. This is an optional paid upgrade.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Does she actually remember me?</div>
                    <div class="faq-a">Yes! Our AI companions have memory systems that remember your past conversations, preferences, and important details you've shared.</div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="cta">
        <div class="container">
            <h2>Meet Your AI Girlfriend Today</h2>
            <p>Start free. No credit card required. Find connection on your terms.</p>
            <a href="app.php" class="btn btn-pink" style="font-size:18px;padding:16px 40px">Browse AI Girlfriends →</a>
        </div>
    </section>
    
    <footer>
        <div class="container">
            <p>© 2024 Companion. <a href="terms.php">Terms</a> · <a href="privacy.php">Privacy</a> · Must be 18+ for adult content.</p>
        </div>
    </footer>
</body>
</html>
