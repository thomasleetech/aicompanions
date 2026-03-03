<?php $pageTitle = 'Admin Dashboard - Companion'; ?>

<section class="admin-section">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <a href="<?= url('admin/logout') ?>" class="btn btn-sm btn-ghost">Logout</a>
    </div>

    <!-- Stats -->
    <div class="admin-stats" id="statsGrid">
        <div class="stat-card"><div class="stat-value" id="statUsers">-</div><div class="stat-label">Users</div></div>
        <div class="stat-card"><div class="stat-value" id="statCompanions">-</div><div class="stat-label">Companions</div></div>
        <div class="stat-card"><div class="stat-value" id="statConversations">-</div><div class="stat-label">Conversations</div></div>
        <div class="stat-card"><div class="stat-value" id="statMessagesToday">-</div><div class="stat-label">Messages (24h)</div></div>
        <div class="stat-card"><div class="stat-value" id="statRevenue">-</div><div class="stat-label">Revenue (Total)</div></div>
        <div class="stat-card"><div class="stat-value" id="statRevenueMonth">-</div><div class="stat-label">Revenue (30d)</div></div>
        <div class="stat-card"><div class="stat-value" id="statApiCost">-</div><div class="stat-label">API Cost (24h)</div></div>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
        <button class="tab active" onclick="showTab('users')">Users</button>
        <button class="tab" onclick="showTab('companions')">Companions</button>
        <button class="tab" onclick="showTab('api')">API Usage</button>
    </div>

    <!-- Users -->
    <div class="admin-panel" id="panel-users">
        <div class="panel-header">
            <input type="text" id="userSearch" placeholder="Search users..." onkeyup="searchUsers()">
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Conversations</th><th>Joined</th></tr></thead>
                <tbody id="usersTable"></tbody>
            </table>
        </div>
    </div>

    <!-- Companions -->
    <div class="admin-panel" id="panel-companions" style="display:none">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Rating</th><th>Orders</th><th>Active</th><th>Featured</th></tr></thead>
                <tbody id="companionsTable"></tbody>
            </table>
        </div>
    </div>

    <!-- API Usage -->
    <div class="admin-panel" id="panel-api" style="display:none">
        <div class="panel-header">
            <select id="apiPeriod" onchange="loadApiUsage()">
                <option value="day">Last 24 Hours</option>
                <option value="week">Last 7 Days</option>
                <option value="month">Last 30 Days</option>
            </select>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>API Type</th><th>Calls</th><th>Tokens In</th><th>Tokens Out</th><th>Cost</th></tr></thead>
                <tbody id="apiTable"></tbody>
            </table>
            <div class="api-total">Total Cost: <strong id="apiTotalCost">$0.00</strong></div>
        </div>
    </div>
</section>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function adminPost(url, data = {}) {
    const fd = new FormData();
    fd.append('_token', CSRF);
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    const res = await fetch(BASE + url, { method: 'POST', body: fd });
    return res.json();
}

async function loadStats() {
    const data = await adminPost('/api/admin/stats');
    if (!data.success) return;
    const s = data.stats;
    document.getElementById('statUsers').textContent = s.users;
    document.getElementById('statCompanions').textContent = s.companions;
    document.getElementById('statConversations').textContent = s.conversations;
    document.getElementById('statMessagesToday').textContent = s.messages_today;
    document.getElementById('statRevenue').textContent = '$' + parseFloat(s.revenue_total).toFixed(2);
    document.getElementById('statRevenueMonth').textContent = '$' + parseFloat(s.revenue_month).toFixed(2);
    document.getElementById('statApiCost').textContent = '$' + parseFloat(s.api_cost_today).toFixed(4);
}

async function loadUsers(search = '') {
    const data = await adminPost('/api/admin/users', { search });
    if (!data.success) return;
    document.getElementById('usersTable').innerHTML = data.users.map(u => `
        <tr>
            <td>${u.id}</td>
            <td>${esc(u.username)}</td>
            <td>${esc(u.email)}</td>
            <td>${u.conv_count}</td>
            <td>${new Date(u.created_at).toLocaleDateString()}</td>
        </tr>
    `).join('');
}

async function loadCompanions() {
    const data = await adminPost('/api/admin/companions');
    if (!data.success) return;
    document.getElementById('companionsTable').innerHTML = data.companions.map(c => `
        <tr>
            <td>${c.id}</td>
            <td>${esc(c.display_name || c.title)}</td>
            <td>${c.companion_type}</td>
            <td>${parseFloat(c.rating).toFixed(1)}</td>
            <td>${c.total_orders}</td>
            <td><button class="btn btn-xs ${c.is_active ? 'btn-primary' : 'btn-ghost'}" onclick="toggleActive(${c.id}, ${c.is_active ? 0 : 1})">${c.is_active ? 'Active' : 'Inactive'}</button></td>
            <td><button class="btn btn-xs ${c.is_featured ? 'btn-primary' : 'btn-ghost'}" onclick="toggleFeatured(${c.id}, ${c.is_featured ? 0 : 1})">${c.is_featured ? 'Featured' : 'Regular'}</button></td>
        </tr>
    `).join('');
}

async function loadApiUsage() {
    const period = document.getElementById('apiPeriod').value;
    const data = await adminPost('/api/admin/api-usage', { period });
    if (!data.success) return;
    document.getElementById('apiTable').innerHTML = (data.by_type || []).map(a => `
        <tr>
            <td>${esc(a.api_type)}</td>
            <td>${a.calls}</td>
            <td>${parseInt(a.total_input || 0).toLocaleString()}</td>
            <td>${parseInt(a.total_output || 0).toLocaleString()}</td>
            <td>$${parseFloat(a.total_cost || 0).toFixed(4)}</td>
        </tr>
    `).join('') || '<tr><td colspan="5">No data</td></tr>';
    document.getElementById('apiTotalCost').textContent = '$' + parseFloat(data.total_cost || 0).toFixed(4);
}

async function toggleActive(id, val) {
    await adminPost('/api/admin/companion/toggle', { id, is_active: val });
    loadCompanions();
}

async function toggleFeatured(id, val) {
    await adminPost('/api/admin/companion/featured', { id, is_featured: val });
    loadCompanions();
}

let searchTimeout;
function searchUsers() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => loadUsers(document.getElementById('userSearch').value), 300);
}

function showTab(name) {
    document.querySelectorAll('.admin-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById('panel-' + name).style.display = 'block';
    event.target.classList.add('active');

    if (name === 'companions') loadCompanions();
    if (name === 'api') loadApiUsage();
}

function esc(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

loadStats();
loadUsers();
</script>
