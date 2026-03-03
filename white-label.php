<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>White Label AI Companion Platform | Launch Your Own AI Girlfriend/Boyfriend App</title>
    <meta name="description" content="Launch your own AI companion platform with our white-label solution. Fully branded, ready to deploy. $499/month. No development needed.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{--bg:#0a0a0a;--bg2:#111;--bg3:#1a1a1a;--text:#fff;--text2:#888;--accent:#10b981;--border:#222}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.7}
        .container{max-width:1100px;margin:0 auto;padding:0 24px}
        
        header{padding:20px 0;border-bottom:1px solid var(--border)}
        header .container{display:flex;justify-content:space-between;align-items:center}
        .logo{font-size:20px;font-weight:700;color:var(--text);text-decoration:none}
        .btn{padding:12px 28px;background:var(--accent);color:#000;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;display:inline-block;border:none;cursor:pointer}
        .btn:hover{background:#0d9f72}
        .btn-outline{background:transparent;border:1px solid var(--border);color:var(--text)}
        
        .hero{padding:100px 0;text-align:center}
        .hero-badge{display:inline-block;padding:8px 20px;background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);border-radius:50px;font-size:14px;color:var(--accent);margin-bottom:24px}
        .hero h1{font-size:clamp(36px,5vw,56px);font-weight:800;line-height:1.1;margin-bottom:24px}
        .hero h1 span{color:var(--accent)}
        .hero p{font-size:20px;color:var(--text2);max-width:700px;margin:0 auto 40px}
        .hero-cta{display:flex;gap:16px;justify-content:center;flex-wrap:wrap}
        
        .proof{padding:60px 0;background:var(--bg2);text-align:center}
        .proof p{color:var(--text2);font-size:14px;margin-bottom:24px}
        .logos{display:flex;justify-content:center;gap:48px;flex-wrap:wrap;opacity:0.6}
        .logos span{font-size:20px;font-weight:600;color:var(--text2)}
        
        .features{padding:100px 0}
        .features h2{font-size:36px;text-align:center;margin-bottom:16px}
        .features>p{text-align:center;color:var(--text2);margin-bottom:60px}
        .features-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px}
        .feature{background:var(--bg2);border:1px solid var(--border);border-radius:16px;padding:32px}
        .feature-icon{font-size:36px;margin-bottom:16px}
        .feature h3{font-size:20px;margin-bottom:8px}
        .feature p{color:var(--text2);font-size:14px}
        
        .included{padding:100px 0;background:var(--bg2)}
        .included h2{font-size:36px;text-align:center;margin-bottom:60px}
        .included-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;max-width:900px;margin:0 auto}
        .included-item{display:flex;align-items:center;gap:12px;padding:16px;background:var(--bg);border-radius:8px}
        .included-item .check{color:var(--accent);font-size:20px}
        
        .pricing{padding:100px 0}
        .pricing h2{font-size:36px;text-align:center;margin-bottom:60px}
        .price-card{max-width:500px;margin:0 auto;background:var(--bg2);border:2px solid var(--accent);border-radius:24px;padding:48px;text-align:center}
        .price-card h3{font-size:24px;margin-bottom:8px}
        .price-card .price{font-size:64px;font-weight:800;margin:24px 0}
        .price-card .price span{font-size:20px;color:var(--text2);font-weight:400}
        .price-card .includes{text-align:left;margin:32px 0}
        .price-card .includes li{padding:8px 0;display:flex;align-items:center;gap:12px}
        .price-card .includes li::before{content:'✓';color:var(--accent);font-weight:700}
        
        .process{padding:100px 0;background:var(--bg2)}
        .process h2{font-size:36px;text-align:center;margin-bottom:60px}
        .steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:32px}
        .step{text-align:center}
        .step-num{width:48px;height:48px;background:var(--accent);color:#000;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 16px}
        .step h3{font-size:18px;margin-bottom:8px}
        .step p{color:var(--text2);font-size:14px}
        
        .faq{padding:100px 0}
        .faq h2{font-size:36px;text-align:center;margin-bottom:60px}
        .faq-list{max-width:700px;margin:0 auto}
        .faq-item{border-bottom:1px solid var(--border);padding:24px 0}
        .faq-q{font-size:18px;font-weight:600;margin-bottom:12px}
        .faq-a{color:var(--text2);font-size:15px}
        
        .cta{padding:100px 0;text-align:center;background:linear-gradient(135deg,rgba(16,185,129,0.1),transparent)}
        .cta h2{font-size:40px;margin-bottom:16px}
        .cta p{color:var(--text2);font-size:18px;margin-bottom:32px}
        
        .form-section{padding:100px 0;background:var(--bg2)}
        .form-section h2{font-size:36px;text-align:center;margin-bottom:40px}
        .contact-form{max-width:500px;margin:0 auto;background:var(--bg);border:1px solid var(--border);border-radius:16px;padding:32px}
        .form-group{margin-bottom:20px}
        .form-group label{display:block;font-size:14px;margin-bottom:8px;color:var(--text2)}
        .form-group input,.form-group textarea,.form-group select{width:100%;padding:14px;background:var(--bg2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px}
        .form-group input:focus,.form-group textarea:focus{outline:none;border-color:var(--accent)}
        
        footer{padding:40px 0;border-top:1px solid var(--border);text-align:center}
        footer p{color:var(--text2);font-size:13px}
        footer a{color:var(--text2)}
        
        @media(max-width:768px){.hero,.features,.included,.pricing,.process,.faq,.cta,.form-section{padding:60px 0}}
    </style>
</head>
<body>
    <header>
        <div class="container">
            <a href="index.php" class="logo">Companion for Business</a>
            <a href="#apply" class="btn">Apply Now</a>
        </div>
    </header>
    
    <section class="hero">
        <div class="container">
            <div class="hero-badge">🏢 Enterprise Solution</div>
            <h1>Launch Your Own <span>AI Companion Platform</span></h1>
            <p>White-label our proven AI girlfriend/boyfriend platform. Your brand, your customers, your revenue. We handle the tech, you handle the growth.</p>
            <div class="hero-cta">
                <a href="#apply" class="btn">Apply for White Label →</a>
                <a href="#features" class="btn btn-outline">See What's Included</a>
            </div>
        </div>
    </section>
    
    <section class="proof">
        <div class="container">
            <p>Trusted by entrepreneurs and businesses worldwide</p>
            <div class="logos">
                <span>Dating Apps</span>
                <span>Adult Platforms</span>
                <span>Mental Wellness</span>
                <span>Entertainment</span>
            </div>
        </div>
    </section>
    
    <section class="features" id="features">
        <div class="container">
            <h2>Everything You Need</h2>
            <p>A complete, production-ready platform</p>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon">🎨</div>
                    <h3>Fully Branded</h3>
                    <p>Your logo, colors, domain. Customers never see our name. It's completely yours.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🤖</div>
                    <h3>Advanced AI</h3>
                    <p>GPT-4 powered conversations with memory, personality, and emotional intelligence.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🎤</div>
                    <h3>Voice Messages</h3>
                    <p>Realistic AI voice generation included. Multiple voices per companion.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">📸</div>
                    <h3>AI Image Generation</h3>
                    <p>Generate photos and selfies from companions. SFW and NSFW options.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">💳</div>
                    <h3>Payments Built-In</h3>
                    <p>Stripe integration ready. Subscriptions, one-time purchases, upgrades - all handled.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">📱</div>
                    <h3>Mobile Responsive</h3>
                    <p>Works beautifully on all devices. PWA-ready for app-like experience.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="included">
        <div class="container">
            <h2>What's Included</h2>
            <div class="included-grid">
                <div class="included-item"><span class="check">✓</span> Complete web application</div>
                <div class="included-item"><span class="check">✓</span> Admin dashboard</div>
                <div class="included-item"><span class="check">✓</span> User management</div>
                <div class="included-item"><span class="check">✓</span> Companion management</div>
                <div class="included-item"><span class="check">✓</span> Chat system with memory</div>
                <div class="included-item"><span class="check">✓</span> Voice message system</div>
                <div class="included-item"><span class="check">✓</span> Photo generation</div>
                <div class="included-item"><span class="check">✓</span> Payment processing</div>
                <div class="included-item"><span class="check">✓</span> Subscription management</div>
                <div class="included-item"><span class="check">✓</span> Gift card system</div>
                <div class="included-item"><span class="check">✓</span> Email system</div>
                <div class="included-item"><span class="check">✓</span> Rate limiting</div>
                <div class="included-item"><span class="check">✓</span> NSFW content controls</div>
                <div class="included-item"><span class="check">✓</span> Custom domain</div>
                <div class="included-item"><span class="check">✓</span> SSL certificate</div>
                <div class="included-item"><span class="check">✓</span> Technical support</div>
            </div>
        </div>
    </section>
    
    <section class="pricing">
        <div class="container">
            <h2>Simple Pricing</h2>
            <div class="price-card">
                <h3>White Label License</h3>
                <div class="price">$499<span>/month</span></div>
                <p style="color:var(--text2)">Everything you need to launch and scale</p>
                <ul class="includes">
                    <li>Full platform access</li>
                    <li>Unlimited users</li>
                    <li>Unlimited companions</li>
                    <li>Custom branding</li>
                    <li>Your own domain</li>
                    <li>Priority support</li>
                    <li>Monthly updates</li>
                    <li>No revenue share</li>
                </ul>
                <a href="#apply" class="btn" style="width:100%;margin-top:24px">Apply Now</a>
                <p style="font-size:12px;color:var(--text2);margin-top:16px">API costs (OpenAI, etc.) billed separately based on usage</p>
            </div>
        </div>
    </section>
    
    <section class="process">
        <div class="container">
            <h2>Launch in Days, Not Months</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <h3>Apply</h3>
                    <p>Fill out our application form. We review within 24 hours.</p>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <h3>Setup</h3>
                    <p>We configure your platform with your branding and domain.</p>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <h3>Customize</h3>
                    <p>Add your companions, set pricing, configure features.</p>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <h3>Launch</h3>
                    <p>Go live and start acquiring customers. We handle the tech.</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="faq">
        <div class="container">
            <h2>Questions</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-q">Do I need technical skills?</div>
                    <div class="faq-a">No. We handle all the technical setup and maintenance. You just need to focus on marketing and customer acquisition.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">What about API costs?</div>
                    <div class="faq-a">You'll need your own OpenAI API key. Costs are typically $0.01-0.05 per conversation depending on length. We help you optimize.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Can I customize the companions?</div>
                    <div class="faq-a">Yes! Full control over companion profiles, personalities, appearances, and pricing through the admin panel.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Is adult content allowed?</div>
                    <div class="faq-a">Yes, the platform supports both SFW and NSFW content with configurable age gates and content controls.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">Do you take a revenue cut?</div>
                    <div class="faq-a">No. 100% of the revenue from your customers goes to you. You just pay the flat monthly fee.</div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="form-section" id="apply">
        <div class="container">
            <h2>Apply for White Label</h2>
            <form class="contact-form" onsubmit="submitApplication(event)">
                <div class="form-group">
                    <label>Company/Brand Name</label>
                    <input type="text" name="company" required placeholder="Your brand name">
                </div>
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="name" required placeholder="Full name">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="you@company.com">
                </div>
                <div class="form-group">
                    <label>Desired Domain</label>
                    <input type="text" name="domain" placeholder="mycompanions.com (optional)">
                </div>
                <div class="form-group">
                    <label>Target Market</label>
                    <select name="market">
                        <option>AI Girlfriends/Dating</option>
                        <option>AI Boyfriends/Dating</option>
                        <option>Mental Wellness/Support</option>
                        <option>Adult Entertainment</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tell us about your plans</label>
                    <textarea name="plans" rows="4" placeholder="What's your vision for the platform?"></textarea>
                </div>
                <button type="submit" class="btn" style="width:100%">Submit Application</button>
            </form>
        </div>
    </section>
    
    <footer>
        <div class="container">
            <p>© 2024 Companion. <a href="terms.php">Terms</a> · <a href="privacy.php">Privacy</a> · Questions? Email business@companion.ai</p>
        </div>
    </footer>
    
    <script>
    function submitApplication(e) {
        e.preventDefault();
        // In production: send to your backend
        alert('Application submitted! We\'ll be in touch within 24 hours.');
        e.target.reset();
    }
    </script>
</body>
</html>