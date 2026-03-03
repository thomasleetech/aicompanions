<?php
$pageTitle = View::e($gig['display_name'] ?? 'Chat') . ' - Companion';
$pageLayout = 'chat';
$companionName = View::e($gig['display_name'] ?? 'Companion');
$hasPhotos = in_array('photos', $upgrades ?? []) || in_array('premium', $upgrades ?? []) || in_array('premium_plus', $upgrades ?? []);
$hasVoice = in_array('voice', $upgrades ?? []) || in_array('premium', $upgrades ?? []) || in_array('premium_plus', $upgrades ?? []);
?>

<section class="chat-section">
    <div class="chat-layout">
        <!-- Chat Header -->
        <div class="chat-header">
            <a href="<?= url('app') ?>" class="chat-back">&larr;</a>
            <img src="<?= View::e($gig['image_url'] ?? '') ?>" alt="" class="chat-avatar">
            <div class="chat-header-info">
                <h3><?= $companionName ?></h3>
                <span class="status-online" id="statusText">Online</span>
            </div>
            <div class="chat-header-actions">
                <button class="btn btn-sm btn-ghost" onclick="toggleVoice()" id="voiceBtn" title="Toggle voice replies">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>
                </button>
                <button class="btn btn-sm btn-ghost" onclick="toggleGiftShop()" id="giftBtn" title="Gift Shop">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                </button>
            </div>
        </div>

        <!-- Gift Shop Drawer -->
        <div class="gift-shop-drawer" id="giftShopDrawer" style="display:none">
            <div class="gift-shop-header">
                <h4>Gift Shop</h4>
                <button class="btn btn-sm btn-ghost" onclick="toggleGiftShop()">&times;</button>
            </div>
            <div class="gift-shop-items">
                <div class="gift-category">Communication</div>
                <div class="gift-item <?= in_array('voice', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="voice" data-price="4.99">
                    <span class="gift-icon">🎤</span>
                    <div class="gift-info">
                        <strong>Voice Pack</strong>
                        <small>Hear <?= $companionName ?>'s voice</small>
                    </div>
                    <span class="gift-price"><?= in_array('voice', $upgrades ?? []) ? 'Owned' : '$4.99' ?></span>
                </div>
                <div class="gift-item <?= in_array('voice_input', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="voice_input" data-price="5.99">
                    <span class="gift-icon">🎙️</span>
                    <div class="gift-info">
                        <strong>Voice Input</strong>
                        <small>Send voice messages to <?= $companionName ?></small>
                    </div>
                    <span class="gift-price"><?= in_array('voice_input', $upgrades ?? []) ? 'Owned' : '$5.99' ?></span>
                </div>
                <div class="gift-item <?= in_array('email', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="email" data-price="7.99">
                    <span class="gift-icon">💌</span>
                    <div class="gift-info">
                        <strong>Email Access</strong>
                        <small>Get emails &amp; love letters</small>
                    </div>
                    <span class="gift-price"><?= in_array('email', $upgrades ?? []) ? 'Owned' : '$7.99' ?></span>
                </div>

                <div class="gift-category">Media</div>
                <div class="gift-item <?= in_array('photos', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="photos" data-price="9.99">
                    <span class="gift-icon">📸</span>
                    <div class="gift-info">
                        <strong>Photo Pack</strong>
                        <small>Get selfies from <?= $companionName ?></small>
                    </div>
                    <span class="gift-badge">Most Popular</span>
                    <span class="gift-price"><?= in_array('photos', $upgrades ?? []) ? 'Owned' : '$9.99' ?></span>
                </div>
                <div class="gift-item <?= in_array('videos', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="videos" data-price="14.99">
                    <span class="gift-icon">🎬</span>
                    <div class="gift-info">
                        <strong>Video Pack</strong>
                        <small>Short video clips &amp; messages</small>
                    </div>
                    <span class="gift-badge new">NEW</span>
                    <span class="gift-price"><?= in_array('videos', $upgrades ?? []) ? 'Owned' : '$14.99' ?></span>
                </div>

                <div class="gift-category">Intelligence</div>
                <div class="gift-item <?= in_array('web_search', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="web_search" data-price="9.99">
                    <span class="gift-icon">🌐</span>
                    <div class="gift-info">
                        <strong>Internet Access</strong>
                        <small>Real-time web search &amp; current events</small>
                    </div>
                    <span class="gift-badge new">NEW</span>
                    <span class="gift-price"><?= in_array('web_search', $upgrades ?? []) ? 'Owned' : '$9.99' ?></span>
                </div>
                <div class="gift-item <?= in_array('creative', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="creative" data-price="12.99">
                    <span class="gift-icon">🎨</span>
                    <div class="gift-info">
                        <strong>Creative Mode</strong>
                        <small>Art, poetry, stories &amp; love letters</small>
                    </div>
                    <span class="gift-badge new">NEW</span>
                    <span class="gift-price"><?= in_array('creative', $upgrades ?? []) ? 'Owned' : '$12.99' ?></span>
                </div>
                <div class="gift-item <?= in_array('realtime_vision', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="realtime_vision" data-price="19.99">
                    <span class="gift-icon">👁️</span>
                    <div class="gift-info">
                        <strong>Real-Time Vision</strong>
                        <small>Live video — <?= $companionName ?> reacts to you</small>
                    </div>
                    <span class="gift-badge hot">HOT</span>
                    <span class="gift-price"><?= in_array('realtime_vision', $upgrades ?? []) ? 'Owned' : '$19.99' ?></span>
                </div>

                <div class="gift-category">18+ Adult</div>
                <div class="gift-item <?= in_array('spicy_personality', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="spicy_personality" data-price="14.99">
                    <span class="gift-icon">💋</span>
                    <div class="gift-info">
                        <strong>Spicy Personality</strong>
                        <small>Unlock explicit adult conversations</small>
                    </div>
                    <span class="gift-badge adult">18+</span>
                    <span class="gift-price"><?= in_array('spicy_personality', $upgrades ?? []) ? 'Owned' : '$14.99' ?></span>
                </div>
                <div class="gift-item <?= in_array('spicy', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="spicy" data-price="19.99" data-requires="photos">
                    <span class="gift-icon">🔥</span>
                    <div class="gift-info">
                        <strong>Spicy Photos</strong>
                        <small>Intimate &amp; explicit pics</small>
                    </div>
                    <span class="gift-badge adult">18+</span>
                    <span class="gift-price"><?= in_array('spicy', $upgrades ?? []) ? 'Owned' : '$19.99' ?></span>
                </div>
                <div class="gift-item <?= in_array('spicy_videos', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="spicy_videos" data-price="24.99" data-requires="videos">
                    <span class="gift-icon">🍑</span>
                    <div class="gift-info">
                        <strong>Spicy Videos</strong>
                        <small>Intimate &amp; explicit video clips</small>
                    </div>
                    <span class="gift-badge adult">18+</span>
                    <span class="gift-price"><?= in_array('spicy_videos', $upgrades ?? []) ? 'Owned' : '$24.99' ?></span>
                </div>

                <div class="gift-category">Bundles</div>
                <div class="gift-item bundle <?= in_array('premium', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="premium" data-price="29.99">
                    <span class="gift-icon">⭐</span>
                    <div class="gift-info">
                        <strong>Premium Bundle</strong>
                        <small>Voice + Photos + Email + Creative</small>
                    </div>
                    <span class="gift-badge best">Best Value</span>
                    <span class="gift-price"><?= in_array('premium', $upgrades ?? []) ? 'Owned' : '<s>$47.99</s> $29.99' ?></span>
                </div>
                <div class="gift-item bundle vip <?= in_array('premium_plus', $upgrades ?? []) ? 'owned' : '' ?>" data-upgrade="premium_plus" data-price="99.99">
                    <span class="gift-icon">👑</span>
                    <div class="gift-info">
                        <strong>VIP Bundle</strong>
                        <small>EVERYTHING unlocked — lifetime access</small>
                    </div>
                    <span class="gift-price"><?= in_array('premium_plus', $upgrades ?? []) ? 'Owned' : '<s>$149.99</s> $99.99' ?></span>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="chat-loading" id="chatLoading">Loading messages...</div>
        </div>

        <!-- Input -->
        <div class="chat-input-area">
            <form id="chatForm" class="chat-form">
                <input type="hidden" name="gig_id" value="<?= $gig['id'] ?>">
                <input type="hidden" name="voice" value="false" id="voiceInput">
                <div class="chat-input-wrap">
                    <textarea id="messageInput" name="message" placeholder="Type a message..." rows="1" maxlength="2000"></textarea>
                    <button type="submit" class="chat-send-btn" id="sendBtn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    </button>
                </div>
            </form>
            <div class="chat-footer-info">
                <span id="demoNotice" style="display:none">Free trial &middot; <span id="freeLeft">3</span> messages left</span>
                <span id="timeNotice" style="display:none"></span>
            </div>
        </div>
    </div>
</section>

<style>
/* Gift Shop Drawer */
.gift-shop-drawer {
    background: var(--bg-card, #1a1a2e);
    border-bottom: 1px solid var(--border, #333);
    padding: 12px 16px;
    max-height: 300px;
    overflow-y: auto;
}
.gift-shop-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.gift-shop-header h4 { margin: 0; font-size: 14px; }
.gift-shop-items { display: flex; flex-direction: column; gap: 8px; }
.gift-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: var(--bg-input, #252540);
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.2s;
}
.gift-item:hover:not(.owned) { background: var(--bg-hover, #303050); }
.gift-item.owned { opacity: 0.6; cursor: default; }
.gift-icon { font-size: 20px; }
.gift-info { flex: 1; }
.gift-info strong { display: block; font-size: 13px; }
.gift-info small { color: var(--text-muted, #888); font-size: 11px; }
.gift-price { font-size: 13px; font-weight: 600; color: var(--accent, #e040fb); }
.gift-item.owned .gift-price { color: var(--text-muted, #888); }
.gift-category { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted, #888); margin-top: 10px; margin-bottom: 2px; padding-left: 4px; }
.gift-category:first-child { margin-top: 0; }
.gift-badge { font-size: 9px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: var(--accent, #e040fb); color: #fff; text-transform: uppercase; white-space: nowrap; }
.gift-badge.new { background: #00c853; }
.gift-badge.hot { background: #ff5722; }
.gift-badge.adult { background: #e91e63; }
.gift-badge.best { background: linear-gradient(135deg, #ff9800, #f44336); }
.gift-item.bundle { border: 1px solid var(--accent, #e040fb); }
.gift-item.vip { border-color: gold; background: linear-gradient(135deg, rgba(255,215,0,0.05), rgba(255,215,0,0.02)); }
.gift-price s { color: var(--text-muted, #666); font-size: 11px; margin-right: 2px; }

/* Chat images */
.message-bubble img {
    max-width: 280px;
    max-height: 350px;
    border-radius: 12px;
    margin-top: 8px;
    display: block;
    cursor: pointer;
    transition: transform 0.2s;
}
.message-bubble img:hover { transform: scale(1.02); }

/* Italics in messages */
.message-bubble em {
    color: var(--text-muted, #888);
    font-style: italic;
}

/* Image lightbox */
.lightbox-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.9);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.lightbox-overlay img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 8px;
}
</style>

<script>
const GIG_ID = <?= $gig['id'] ?>;
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
let voiceEnabled = false;
let sending = false;

async function loadHistory() {
    try {
        const fd = new FormData();
        fd.append('gig_id', GIG_ID);
        fd.append('_token', CSRF);

        const res = await fetch(BASE + '/api/chat/history', { method: 'POST', body: fd });
        const data = await res.json();

        document.getElementById('chatLoading').style.display = 'none';

        if (data.success && data.messages) {
            data.messages.forEach(m => addMessage(m.role, m.content, m.audio_url));
            scrollToBottom();
        }
    } catch (e) {
        document.getElementById('chatLoading').textContent = 'Failed to load messages';
    }
}

document.getElementById('chatForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (sending) return;

    const input = document.getElementById('messageInput');
    const msg = input.value.trim();
    if (!msg) return;

    sending = true;
    input.value = '';
    autoResize(input);

    addMessage('user', msg);
    scrollToBottom();

    const typingEl = showTyping();

    const fd = new FormData();
    fd.append('gig_id', GIG_ID);
    fd.append('message', msg);
    fd.append('voice', voiceEnabled ? 'true' : 'false');
    fd.append('_token', CSRF);

    try {
        const res = await fetch(BASE + '/api/chat/send', { method: 'POST', body: fd });
        const data = await res.json();

        removeTyping(typingEl);

        if (data.success) {
            addMessage('assistant', data.response, data.audio_url);

            if (data.is_demo) {
                document.getElementById('demoNotice').style.display = 'inline';
            }
            if (data.time_remaining > 0) {
                document.getElementById('timeNotice').style.display = 'inline';
                document.getElementById('timeNotice').textContent = data.time_remaining + ' min remaining';
            }
        } else {
            if (data.requires_purchase) {
                addSystemMessage('Your free messages are used up. Purchase time to continue chatting.');
            } else {
                addSystemMessage(data.message || 'Failed to send message');
            }
        }
    } catch (err) {
        removeTyping(typingEl);
        addSystemMessage('Connection error. Please try again.');
    }

    sending = false;
    scrollToBottom();
});

// Render message content with markdown-like support (images, italics, bold)
function renderContent(content) {
    // Escape HTML first
    let html = escapeHtml(content);

    // Convert markdown images ![alt](url) to actual images
    html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, function(match, alt, url) {
        return '<img src="' + BASE + '/' + url + '" alt="' + alt + '" onclick="openLightbox(this.src)" loading="lazy">';
    });

    // Convert *italic* text (for companion action text)
    html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');

    // Convert **bold** text
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

    // Convert newlines to <br>
    html = html.replace(/\n/g, '<br>');

    return html;
}

function addMessage(role, content, audioUrl) {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'message message-' + role;

    let html = '<div class="message-bubble">' + renderContent(content) + '</div>';

    if (audioUrl) {
        html += '<audio controls class="message-audio"><source src="' + BASE + '/' + audioUrl + '" type="audio/mpeg"></audio>';
    }

    div.innerHTML = html;
    container.appendChild(div);
}

function addSystemMessage(text) {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'message message-system';
    div.innerHTML = '<div class="message-bubble system">' + escapeHtml(text) + '</div>';
    container.appendChild(div);
}

function showTyping() {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'message message-assistant typing-indicator';
    div.innerHTML = '<div class="message-bubble"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>';
    container.appendChild(div);
    scrollToBottom();
    document.getElementById('statusText').textContent = 'Typing...';
    return div;
}

function removeTyping(el) {
    if (el && el.parentNode) el.parentNode.removeChild(el);
    document.getElementById('statusText').textContent = 'Online';
}

function toggleVoice() {
    voiceEnabled = !voiceEnabled;
    document.getElementById('voiceInput').value = voiceEnabled ? 'true' : 'false';
    document.getElementById('voiceBtn').classList.toggle('active', voiceEnabled);
}

function toggleGiftShop() {
    const drawer = document.getElementById('giftShopDrawer');
    drawer.style.display = drawer.style.display === 'none' ? 'block' : 'none';
}

function openLightbox(src) {
    const overlay = document.createElement('div');
    overlay.className = 'lightbox-overlay';
    overlay.innerHTML = '<img src="' + src + '">';
    overlay.onclick = function() { overlay.remove(); };
    document.body.appendChild(overlay);
}

function scrollToBottom() {
    const el = document.getElementById('chatMessages');
    el.scrollTop = el.scrollHeight;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

const textarea = document.getElementById('messageInput');
textarea.addEventListener('input', () => autoResize(textarea));
textarea.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
});

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// Gift shop purchase handler
document.querySelectorAll('.gift-item:not(.owned)').forEach(el => {
    el.addEventListener('click', async () => {
        const upgrade = el.dataset.upgrade;
        const price = el.dataset.price;
        const requires = el.dataset.requires;

        // Check prerequisites
        if (requires) {
            const owned = <?= json_encode($upgrades ?? []) ?>;
            if (!owned.includes(requires) && !owned.includes('premium') && !owned.includes('premium_plus')) {
                addSystemMessage('You need the ' + requires.replace('_', ' ') + ' pack first before unlocking this.');
                return;
            }
        }

        if (!confirm('Purchase ' + el.querySelector('strong').textContent + ' for $' + price + '?')) return;

        // Try Stripe first, then PayPal, then direct (free/dev mode)
        const fd = new FormData();
        fd.append('gig_id', GIG_ID);
        fd.append('upgrade_type', upgrade);
        fd.append('purchase_type', 'upgrade');
        fd.append('_token', CSRF);

        try {
            // Attempt Stripe checkout
            let res = await fetch(BASE + '/api/payment/stripe-checkout', { method: 'POST', body: fd });
            let data = await res.json();
            if (data.success && data.url) {
                window.location.href = data.url;
                return;
            }

            // Fallback: direct purchase (dev mode / no payment provider)
            fd.delete('purchase_type');
            res = await fetch(BASE + '/api/upgrade/purchase', { method: 'POST', body: fd });
            data = await res.json();
            if (data.success) {
                el.classList.add('owned');
                el.querySelector('.gift-price').textContent = 'Owned';
                addSystemMessage(data.message || 'Upgrade unlocked!');
            } else {
                addSystemMessage(data.message || 'Purchase failed');
            }
        } catch (e) {
            addSystemMessage('Connection error');
        }
    });
});

loadHistory();
</script>
