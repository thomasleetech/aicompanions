<?php $pageTitle = 'Admin Login - Companion'; ?>

<section class="auth-section">
    <div class="auth-card">
        <h1>Admin Login</h1>
        <form id="adminLoginForm" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div id="loginError" class="form-error" style="display:none"></div>
            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>
    </div>
</section>

<script>
document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('/api/admin/login', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) window.location.reload();
    else {
        document.getElementById('loginError').textContent = data.message || 'Invalid credentials';
        document.getElementById('loginError').style.display = 'block';
    }
});
</script>
