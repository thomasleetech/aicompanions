<?php
$pageTitle = View::e($gig['display_name'] ?? 'Chat') . ' - Companion';
$pageLayout = 'chat'; // Signal to use minimal layout
?>

<section class="chat-section">
    <div class="chat-layout">
        <!-- Chat Header -->
        <div class="chat-header">
            <a href="/app" class="chat-back">&larr;</a>
            <img src="<?= View::e($gig['image_url'] ?? '') ?>" alt="" class="chat-avatar">
            <div class="chat-header-info">
                <h3><?= View::e($gig['display_name'] ?? $gig['title']) ?></h3>
                <span class="status-online" id="statusText">Online</span>
            </div>
            <div class="chat-header-actions">
                <button class="btn btn-sm btn-ghost" onclick="toggleVoice()" id="voiceBtn" title="Toggle voice replies">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>
                </button>
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

<script>
const GIG_ID = <?= $gig['id'] ?>;
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
let voiceEnabled = false;
let sending = false;

// Load chat history
async function loadHistory() {
    try {
        const fd = new FormData();
        fd.append('gig_id', GIG_ID);
        fd.append('_token', CSRF);

        const res = await fetch('/api/chat/history', { method: 'POST', body: fd });
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

// Send message
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

    // Show typing indicator
    const typingEl = showTyping();

    const fd = new FormData();
    fd.append('gig_id', GIG_ID);
    fd.append('message', msg);
    fd.append('voice', voiceEnabled ? 'true' : 'false');
    fd.append('_token', CSRF);

    try {
        const res = await fetch('/api/chat/send', { method: 'POST', body: fd });
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

function addMessage(role, content, audioUrl) {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'message message-' + role;

    let html = '<div class="message-bubble">' + escapeHtml(content) + '</div>';

    if (audioUrl) {
        html += '<audio controls class="message-audio"><source src="/' + audioUrl + '" type="audio/mpeg"></audio>';
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

function scrollToBottom() {
    const el = document.getElementById('chatMessages');
    el.scrollTop = el.scrollHeight;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Auto-resize textarea
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

// Load on page ready
loadHistory();
</script>
