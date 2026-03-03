<?php $pageTitle = 'Companion - AI That Actually Gets You'; ?>

<section class="hero">
    <div class="hero-content">
        <div class="hero-badge">
            <span class="pulse-dot"></span> 10,000+ active users
        </div>
        <h1>An AI that<br><span class="gradient-text">actually gets you</span></h1>
        <p>No judgment. No waiting. Just real conversation with an AI companion who remembers you, supports you, and is always there.</p>
        <div class="hero-cta">
            <a href="/register" class="btn btn-primary btn-lg">Start Chatting Free &rarr;</a>
            <a href="#features" class="btn btn-ghost btn-lg">See How It Works</a>
        </div>

        <div class="hero-mockup">
            <div class="mockup-header">
                <div class="mockup-avatar"></div>
                <div class="mockup-info">
                    <h4>Luna</h4>
                    <span class="status-online">Online now</span>
                </div>
            </div>
            <div class="mockup-chat">
                <div class="chat-bubble ai">Hey! How's your day going? I was just thinking about that project you mentioned yesterday</div>
                <div class="chat-bubble user">It's been rough honestly. Work stress is getting to me.</div>
                <div class="chat-bubble ai">I hear you. Want to vent about it? Sometimes it helps just to get it all out. I'm here for you.</div>
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
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v4a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M9 12H5a7 7 0 0 0 14 0h-4"/><path d="M8 18h8"/><path d="M9 21h6"/></svg>
            </div>
            <h3>Actually Remembers You</h3>
            <p>Your companion remembers past conversations, your preferences, and important details. No more repeating yourself.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>
            </div>
            <h3>Voice Messages</h3>
            <p>Hear your companion's voice with realistic AI-generated messages. Have natural, flowing conversations.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <h3>AI Photos & Selfies</h3>
            <p>Receive personalized photos from your companion. Morning selfies, outfit checks, candid moments.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </div>
            <h3>Available 24/7</h3>
            <p>3am anxiety spiral? Lonely Sunday? Your companion is always there, ready to talk, no matter what time.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3>Diverse Personalities</h3>
            <p>Find someone who matches your vibe. Supportive partners, creative souls, fitness buddies, intellectual sparring partners.</p>
        </div>
        <div class="feature">
            <div class="feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
            </div>
            <h3>Creative Mode</h3>
            <p>Companions can write poetry, stories, love letters, and more. Unlock their artistic side.</p>
        </div>
    </div>
</section>

<section class="companions-section" id="companions">
    <div class="section-header">
        <h2>Meet a few companions</h2>
        <p>Each one is unique. Find someone you click with.</p>
    </div>
    <div class="companions-scroll">
        <?php foreach (array_slice($companions, 0, 6) as $c): ?>
        <a href="/companion/<?= $c['id'] ?>" class="companion-card">
            <img src="<?= View::e($c['image_url']) ?>" class="companion-img" alt="<?= View::e($c['display_name'] ?? $c['title']) ?>" loading="lazy">
            <div class="companion-info">
                <h4><?= View::e($c['display_name'] ?? '') ?></h4>
                <p><?= View::e(substr($c['description'], 0, 60)) ?></p>
                <div class="companion-tags">
                    <?php foreach (explode(',', $c['tags'] ?? '') as $tag): ?>
                        <?php if (trim($tag)): ?>
                            <span class="tag"><?= View::e(trim($tag)) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="companion-meta">
                    <span class="companion-rating">&#9733; <?= number_format($c['rating'], 1) ?></span>
                    <span class="companion-price">From $<?= number_format($c['price_per_message'], 2) ?>/msg</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:32px">
        <a href="/browse" class="btn btn-ghost">Browse All Companions &rarr;</a>
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
            <a href="/register" class="btn btn-ghost" style="width:100%">Get Started</a>
        </div>
        <div class="price-card featured">
            <div class="price-name">Monthly</div>
            <div class="price-amount">$29<span>/mo</span></div>
            <div class="price-desc">Unlimited everything</div>
            <ul class="price-features">
                <li>Unlimited messaging</li>
                <li>Voice messages</li>
                <li>AI photos & selfies</li>
                <li>Memory & context</li>
                <li>Priority response</li>
            </ul>
            <a href="/register" class="btn btn-primary" style="width:100%">Subscribe Now</a>
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
            <a href="/register" class="btn btn-ghost" style="width:100%">Buy Time</a>
        </div>
    </div>
</section>

<section class="cta-section">
    <h2>Ready to meet your companion?</h2>
    <p>Start free. No credit card required.</p>
    <a href="/register" class="btn btn-primary btn-lg">Get Started Free &rarr;</a>
</section>
