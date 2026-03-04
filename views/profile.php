<?php $pageTitle = 'My Profile - Lush'; ?>

<style>
.profile-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 40px 24px 80px;
    color: #e0e0e0;
    font-family: 'Inter', sans-serif;
}
.profile-page h1 {
    font-size: 2rem;
    color: #fff;
    margin-bottom: 8px;
}
.profile-page .profile-subtitle {
    color: #888;
    font-size: 0.95rem;
    margin-bottom: 32px;
}
.profile-card {
    background: #1a1a2e;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 24px;
    border: 1px solid rgba(224, 64, 251, 0.1);
}
.profile-card h2 {
    font-size: 1.3rem;
    color: #fff;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(224, 64, 251, 0.15);
}
.profile-header {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 28px;
}
.profile-avatar {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e040fb;
    background: #2a2a3e;
    flex-shrink: 0;
}
.profile-avatar-placeholder {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 3px solid #e040fb;
    background: linear-gradient(135deg, #e040fb33, #1a1a2e);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #e040fb;
    font-weight: 700;
    flex-shrink: 0;
}
.profile-header-info {
    flex: 1;
}
.profile-header-info h3 {
    font-size: 1.4rem;
    color: #fff;
    margin: 0 0 4px;
}
.profile-header-info .profile-username {
    color: #e040fb;
    font-size: 0.95rem;
    margin-bottom: 4px;
}
.profile-header-info .profile-member-since {
    color: #888;
    font-size: 0.85rem;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-size: 0.9rem;
    color: #aaa;
    margin-bottom: 6px;
    font-weight: 500;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 16px;
    background: #0f0f23;
    border: 1px solid rgba(224, 64, 251, 0.2);
    border-radius: 10px;
    color: #e0e0e0;
    font-size: 0.95rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s;
    box-sizing: border-box;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #e040fb;
}
.form-group textarea {
    resize: vertical;
    min-height: 80px;
}
.form-group select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23888' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 40px;
}
.form-group select option {
    background: #1a1a2e;
    color: #e0e0e0;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.btn-save {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 32px;
    background: #e040fb;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    font-family: 'Inter', sans-serif;
}
.btn-save:hover {
    background: #c030db;
}
.btn-save:active {
    transform: scale(0.98);
}
.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.save-status {
    display: inline-block;
    margin-left: 16px;
    font-size: 0.9rem;
    color: #4caf50;
    opacity: 0;
    transition: opacity 0.3s;
}
.save-status.visible {
    opacity: 1;
}
.save-status.error {
    color: #f44336;
}
.referral-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #0f0f23;
    border: 1px solid rgba(224, 64, 251, 0.2);
    border-radius: 10px;
    padding: 12px 16px;
}
.referral-box .referral-code {
    flex: 1;
    font-size: 1.1rem;
    font-weight: 600;
    color: #e040fb;
    letter-spacing: 1px;
    font-family: monospace;
}
.referral-box .btn-copy {
    padding: 8px 18px;
    background: rgba(224, 64, 251, 0.15);
    color: #e040fb;
    border: 1px solid rgba(224, 64, 251, 0.3);
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    font-family: 'Inter', sans-serif;
    white-space: nowrap;
}
.referral-box .btn-copy:hover {
    background: rgba(224, 64, 251, 0.25);
}
.referral-description {
    font-size: 0.85rem;
    color: #888;
    margin-top: 8px;
}
.account-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.account-actions a {
    padding: 10px 24px;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.2s;
    font-family: 'Inter', sans-serif;
}
.account-actions .btn-outline {
    background: transparent;
    color: #e0e0e0;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.account-actions .btn-outline:hover {
    background: rgba(255, 255, 255, 0.05);
}
.account-actions .btn-danger {
    background: transparent;
    color: #f44336;
    border: 1px solid rgba(244, 67, 54, 0.3);
}
.account-actions .btn-danger:hover {
    background: rgba(244, 67, 54, 0.1);
}
.inbox-section .inbox-loading {
    text-align: center;
    padding: 32px;
    color: #888;
}
.inbox-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.inbox-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.inbox-item:last-child {
    border-bottom: none;
}
.inbox-item-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e040fb33, #1a1a2e);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: #e040fb;
    font-weight: 600;
    flex-shrink: 0;
}
.inbox-item-content {
    flex: 1;
    min-width: 0;
}
.inbox-item-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 4px;
}
.inbox-item-sender {
    font-weight: 600;
    color: #fff;
    font-size: 0.9rem;
}
.inbox-item-time {
    font-size: 0.8rem;
    color: #666;
    flex-shrink: 0;
}
.inbox-item-preview {
    color: #999;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.inbox-item.unread .inbox-item-sender {
    color: #e040fb;
}
.inbox-item.unread .inbox-item-preview {
    color: #ccc;
}
.inbox-empty {
    text-align: center;
    padding: 32px;
    color: #666;
    font-size: 0.95rem;
}
@media (max-width: 640px) {
    .profile-page {
        padding: 24px 16px 60px;
    }
    .profile-card {
        padding: 20px;
    }
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    .form-row {
        grid-template-columns: 1fr;
    }
    .account-actions {
        flex-direction: column;
    }
    .account-actions a {
        text-align: center;
    }
}
</style>

<div class="profile-page">
    <h1>My Profile</h1>
    <p class="profile-subtitle">Manage your Lush account settings and preferences</p>

    <!-- Profile Header Card -->
    <div class="profile-card">
        <div class="profile-header">
            <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?= View::e($user['avatar_url']) ?>" alt="Profile avatar" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar-placeholder">
                    <?= strtoupper(mb_substr($user['display_name'] ?? $user['username'] ?? '?', 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div class="profile-header-info">
                <h3><?= View::e($user['display_name'] ?? $user['username'] ?? '') ?></h3>
                <div class="profile-username">@<?= View::e($user['username'] ?? '') ?></div>
                <div class="profile-member-since">Member since <?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?></div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Card -->
    <div class="profile-card">
        <h2>Edit Profile</h2>
        <form id="profileForm">
            <div class="form-group">
                <label for="display_name">Display Name</label>
                <input type="text" id="display_name" name="display_name" value="<?= View::e($user['display_name'] ?? '') ?>" placeholder="How others see you" maxlength="50">
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" placeholder="Tell us about yourself..." maxlength="500"><?= View::e($user['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" value="<?= View::e($user['location'] ?? '') ?>" placeholder="City, Country">
                </div>
                <div class="form-group">
                    <label for="occupation">Occupation</label>
                    <input type="text" id="occupation" name="occupation" value="<?= View::e($user['occupation'] ?? '') ?>" placeholder="What do you do?">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="interests">Interests</label>
                    <input type="text" id="interests" name="interests" value="<?= View::e($user['interests'] ?? '') ?>" placeholder="Music, gaming, travel...">
                </div>
                <div class="form-group">
                    <label for="relationship_status">Relationship Status</label>
                    <select id="relationship_status" name="relationship_status">
                        <option value="">Prefer not to say</option>
                        <option value="single" <?= ($user['relationship_status'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
                        <option value="in_a_relationship" <?= ($user['relationship_status'] ?? '') === 'in_a_relationship' ? 'selected' : '' ?>>In a Relationship</option>
                        <option value="married" <?= ($user['relationship_status'] ?? '') === 'married' ? 'selected' : '' ?>>Married</option>
                        <option value="complicated" <?= ($user['relationship_status'] ?? '') === 'complicated' ? 'selected' : '' ?>>It's Complicated</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;align-items:center;margin-top:8px;">
                <button type="submit" class="btn-save" id="saveBtn">Save Changes</button>
                <span class="save-status" id="saveStatus"></span>
            </div>
        </form>
    </div>

    <!-- Inbox Card -->
    <div class="profile-card inbox-section">
        <h2>Inbox</h2>
        <div id="inboxContainer">
            <div class="inbox-loading">Loading messages...</div>
        </div>
    </div>

    <!-- Referral Card -->
    <div class="profile-card">
        <h2>Referral Program</h2>
        <p style="color:#ccc;font-size:0.93rem;margin-bottom:16px;">Share your referral code with friends and earn rewards when they sign up.</p>
        <div class="referral-box">
            <span class="referral-code" id="referralCode"><?= View::e($user['referral_code'] ?? '') ?></span>
            <button class="btn-copy" id="copyReferralBtn" type="button">Copy Code</button>
        </div>
        <p class="referral-description">Your referral link: <?= url('register') ?>?ref=<?= View::e($user['referral_code'] ?? '') ?></p>
    </div>

    <!-- Account Actions Card -->
    <div class="profile-card">
        <h2>Account</h2>
        <p style="color:#ccc;font-size:0.93rem;margin-bottom:16px;">Manage your account security and settings.</p>
        <div class="account-actions">
            <a href="<?= url('profile/change-password') ?>" class="btn-outline">Change Password</a>
            <a href="<?= url('profile/delete-account') ?>" class="btn-danger">Delete Account</a>
        </div>
    </div>
</div>

<script>
(function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // --- Save Profile ---
    const profileForm = document.getElementById('profileForm');
    const saveBtn = document.getElementById('saveBtn');
    const saveStatus = document.getElementById('saveStatus');

    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
        saveStatus.className = 'save-status';
        saveStatus.textContent = '';

        const formData = new FormData(profileForm);

        try {
            const res = await fetch(BASE + '/api/profile/update', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData
            });

            const data = await res.json();

            if (data.success) {
                saveStatus.textContent = 'Profile saved successfully!';
                saveStatus.className = 'save-status visible';
            } else {
                saveStatus.textContent = data.message || 'Failed to save profile.';
                saveStatus.className = 'save-status visible error';
            }
        } catch (err) {
            saveStatus.textContent = 'Connection error. Please try again.';
            saveStatus.className = 'save-status visible error';
        }

        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Changes';

        setTimeout(function() {
            saveStatus.className = 'save-status';
        }, 4000);
    });

    // --- Copy Referral Code ---
    var copyBtn = document.getElementById('copyReferralBtn');
    var referralCode = document.getElementById('referralCode');

    copyBtn.addEventListener('click', function() {
        var code = referralCode.textContent.trim();
        if (!code) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(function() {
                copyBtn.textContent = 'Copied!';
                setTimeout(function() { copyBtn.textContent = 'Copy Code'; }, 2000);
            });
        } else {
            var textarea = document.createElement('textarea');
            textarea.value = code;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            copyBtn.textContent = 'Copied!';
            setTimeout(function() { copyBtn.textContent = 'Copy Code'; }, 2000);
        }
    });

    // --- Load Inbox ---
    var inboxContainer = document.getElementById('inboxContainer');

    async function loadInbox() {
        try {
            var res = await fetch(BASE + '/api/profile/inbox', {
                headers: {
                    'X-CSRF-Token': csrfToken
                }
            });
            var data = await res.json();

            if (data.success && data.messages && data.messages.length > 0) {
                var html = '<ul class="inbox-list">';
                data.messages.forEach(function(msg) {
                    var isUnread = !msg.read_at;
                    var initial = (msg.sender_name || '?').charAt(0).toUpperCase();
                    var timeStr = msg.created_at ? new Date(msg.created_at).toLocaleDateString() : '';
                    html += '<li class="inbox-item' + (isUnread ? ' unread' : '') + '">';
                    html += '<div class="inbox-item-avatar">' + initial + '</div>';
                    html += '<div class="inbox-item-content">';
                    html += '<div class="inbox-item-header">';
                    html += '<span class="inbox-item-sender">' + escapeHtml(msg.sender_name || 'Unknown') + '</span>';
                    html += '<span class="inbox-item-time">' + escapeHtml(timeStr) + '</span>';
                    html += '</div>';
                    html += '<div class="inbox-item-preview">' + escapeHtml(msg.preview || msg.subject || '') + '</div>';
                    html += '</div>';
                    html += '</li>';
                });
                html += '</ul>';
                inboxContainer.innerHTML = html;
            } else {
                inboxContainer.innerHTML = '<div class="inbox-empty">No messages yet. Start chatting with a companion!</div>';
            }
        } catch (err) {
            inboxContainer.innerHTML = '<div class="inbox-empty" style="color:#f44336;">Failed to load inbox. Please refresh the page.</div>';
        }
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    loadInbox();
})();
</script>
