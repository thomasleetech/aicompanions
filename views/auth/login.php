<?php $pageTitle = 'Log In - Amorai'; ?>

<section class="auth-section">
    <div class="auth-card">
        <h1>Welcome back</h1>
        <p class="auth-subtitle">Log in to continue your conversations</p>

        <form id="loginForm" class="auth-form">
            <?= CSRF::field() ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="you@example.com" autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Your password" autocomplete="current-password">
            </div>
            <div id="loginError" class="form-error" style="display:none"></div>
            <button type="submit" class="btn btn-primary btn-block">Log In</button>
        </form>

        <p class="auth-footer">
            <a href="<?= url('forgot-password') ?>">Forgot password?</a>
        </p>
        <p class="auth-footer">
            Don't have an account? <a href="<?= url('register') ?>">Sign up free</a>
        </p>
    </div>
</section>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const errorEl = document.getElementById('loginError');
    errorEl.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Logging in...';

    try {
        const res = await fetch(BASE + '/api/auth/login', { method: 'POST', body: new FormData(form) });
        const data = await res.json();

        if (data.success) {
            window.location.href = BASE + '/app';
        } else {
            errorEl.textContent = data.message || 'Login failed';
            errorEl.style.display = 'block';
        }
    } catch (err) {
        errorEl.textContent = 'Connection error. Please try again.';
        errorEl.style.display = 'block';
    }

    btn.disabled = false;
    btn.textContent = 'Log In';
});
</script>
