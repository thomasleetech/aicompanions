<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($pageTitle ?? 'Lush - AI That Actually Gets You') ?></title>
    <meta name="description" content="<?= View::e($pageDesc ?? 'Meet your AI companion on Lush. Real conversations. Zero judgment. Available 24/7.') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('css/app.css') ?>">
    <?= CSRF::metaTag() ?>
    <script>const BASE = '<?= BASE_URL ?>';</script>
</head>
<body>
    <header id="header">
        <a href="<?= url('/') ?>" class="logo">
            <div class="logo-icon">L</div>
            <span>Lush</span>
        </a>
        <nav id="nav">
            <a href="<?= url('browse') ?>">Browse</a>
            <a href="<?= url('browse/ai-girlfriend') ?>">Girlfriends</a>
            <a href="<?= url('browse/ai-boyfriend') ?>">Boyfriends</a>
            <?php if (isset($user) && $user): ?>
                <div class="notif-bell" id="notifBell" onclick="toggleNotifications()" title="Notifications">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span class="notif-badge" id="notifBadge" style="display:none">0</span>
                </div>
                <a href="<?= url('profile') ?>" class="btn btn-ghost">Profile</a>
                <a href="<?= url('app') ?>" class="btn btn-primary">Open App</a>
                <a href="<?= url('logout') ?>" class="btn btn-ghost">Logout</a>
            <?php else: ?>
                <a href="<?= url('login') ?>" class="btn btn-ghost">Log In</a>
                <a href="<?= url('register') ?>" class="btn btn-primary">Get Started</a>
            <?php endif; ?>
        </nav>
        <button class="mobile-menu-btn" onclick="document.getElementById('nav').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo">
                    <div class="logo-icon">L</div>
                    <span>Lush</span>
                </div>
                <p>AI companions that actually get you. Always here when you need someone.</p>
            </div>
            <div class="footer-col">
                <h4>Product</h4>
                <a href="<?= url('browse') ?>">Browse Companions</a>
                <a href="<?= url('#pricing') ?>">Pricing</a>
                <a href="<?= url('#features') ?>">Features</a>
            </div>
            <div class="footer-col">
                <h4>Explore</h4>
                <a href="<?= url('browse/ai-girlfriend') ?>">AI Girlfriends</a>
                <a href="<?= url('browse/ai-boyfriend') ?>">AI Boyfriends</a>
                <a href="<?= url('browse/someone-to-talk-to') ?>">Someone to Talk To</a>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <a href="<?= url('terms') ?>">Terms of Service</a>
                <a href="<?= url('privacy') ?>">Privacy Policy</a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> Lush. All rights reserved.
        </div>
    </footer>

    <!-- Notification Dropdown -->
    <div class="notif-dropdown" id="notifDropdown" style="display:none">
        <div class="notif-dropdown-header">
            <strong>Notifications</strong>
            <button class="btn btn-xs btn-ghost" onclick="markAllRead()">Mark all read</button>
        </div>
        <div class="notif-list" id="notifList">
            <div class="notif-empty">No notifications</div>
        </div>
    </div>

    <style>
    .notif-bell { position:relative;cursor:pointer;display:flex;align-items:center;padding:6px;color:var(--text2,#aaa);transition:color 0.2s }
    .notif-bell:hover { color:var(--text,#e0e0e0) }
    .notif-badge { position:absolute;top:0;right:0;background:#e040fb;color:#fff;font-size:9px;font-weight:700;min-width:16px;height:16px;border-radius:8px;display:flex;align-items:center;justify-content:center;padding:0 4px }
    .notif-dropdown { position:fixed;top:56px;right:16px;width:340px;max-height:400px;background:var(--bg2,#1a1a2e);border:1px solid var(--border,#333);border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,0.4);z-index:999;overflow:hidden }
    .notif-dropdown-header { display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-bottom:1px solid var(--border,#333) }
    .notif-dropdown-header strong { font-size:14px }
    .notif-list { max-height:340px;overflow-y:auto }
    .notif-item { padding:12px 16px;border-bottom:1px solid var(--border,#222);cursor:pointer;transition:background 0.2s }
    .notif-item:hover { background:var(--bg1,#0f0f23) }
    .notif-item.unread { border-left:3px solid var(--accent,#e040fb) }
    .notif-item-title { font-size:13px;font-weight:600;margin-bottom:2px }
    .notif-item-body { font-size:12px;color:var(--text2,#888);line-height:1.4 }
    .notif-item-time { font-size:10px;color:var(--text2,#666);margin-top:4px }
    .notif-empty { padding:24px;text-align:center;color:var(--text2,#666);font-size:13px }
    </style>

    <script>
    // Notification system
    function toggleNotifications() {
        const dd = document.getElementById('notifDropdown');
        dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        if (dd.style.display === 'block') loadNotifications();
    }

    async function loadNotifications() {
        try {
            const fd = new FormData();
            fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
            const res = await fetch(BASE + '/api/notifications', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                const list = document.getElementById('notifList');
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = '<div class="notif-empty">No notifications</div>';
                } else {
                    list.innerHTML = data.notifications.map(n => {
                        const d = new Date(n.created_at);
                        const time = d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
                        return `<div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" onclick="readNotif(${n.id}, this, '${n.link || ''}')">
                            <div class="notif-item-title">${escNotif(n.title)}</div>
                            <div class="notif-item-body">${escNotif(n.content)}</div>
                            <div class="notif-item-time">${time}</div>
                        </div>`;
                    }).join('');
                }
                const badge = document.getElementById('notifBadge');
                if (data.unread > 0) { badge.style.display = 'flex'; badge.textContent = data.unread; }
                else { badge.style.display = 'none'; }
            }
        } catch(e) {}
    }

    async function readNotif(id, el, link) {
        el.classList.remove('unread');
        const fd = new FormData();
        fd.append('id', id);
        fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        try { await fetch(BASE + '/api/notifications/read', { method: 'POST', body: fd }); } catch(e) {}
        const badge = document.getElementById('notifBadge');
        const c = parseInt(badge.textContent) || 0;
        if (c > 1) badge.textContent = c - 1; else badge.style.display = 'none';
        if (link) window.location.href = BASE + link;
    }

    async function markAllRead() {
        const fd = new FormData();
        fd.append('all', '1');
        fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        try { await fetch(BASE + '/api/notifications/read', { method: 'POST', body: fd }); } catch(e) {}
        document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
        document.getElementById('notifBadge').style.display = 'none';
    }

    function escNotif(str) { const d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML; }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('notifDropdown');
        const bell = document.getElementById('notifBell');
        if (dd && bell && !dd.contains(e.target) && !bell.contains(e.target)) {
            dd.style.display = 'none';
        }
    });

    // Check for unread notifications on page load
    <?php if (isset($user) && $user): ?>
    (async function() {
        try {
            const fd = new FormData();
            fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
            const res = await fetch(BASE + '/api/notifications', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success && data.unread > 0) {
                const badge = document.getElementById('notifBadge');
                if (badge) { badge.style.display = 'flex'; badge.textContent = data.unread; }
            }
        } catch(e) {}
    })();
    <?php endif; ?>
    </script>

    <script src="<?= url('js/app.js') ?>"></script>
</body>
</html>
