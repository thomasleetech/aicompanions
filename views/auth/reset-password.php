<?php $pageTitle = 'Reset Password - Lush'; ?>

<style>
.auth-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0f0f23;
    padding: 2rem 1rem;
}
.auth-card {
    background: #1a1a2e;
    border-radius: 16px;
    padding: 2.5rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}
.auth-card h1 {
    color: #fff;
    font-size: 1.75rem;
    margin: 0 0 0.5rem;
}
.auth-subtitle {
    color: #aaa;
    margin: 0 0 2rem;
    font-size: 0.95rem;
}
.form-group {
    margin-bottom: 1.25rem;
}
.form-group label {
    display: block;
    color: #ccc;
    margin-bottom: 0.4rem;
    font-size: 0.9rem;
}
.form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    background: #16213e;
    border: 1px solid #2a2a4a;
    border-radius: 8px;
    color: #fff;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.2s;
    box-sizing: border-box;
}
.form-group input:focus {
    border-color: #e040fb;
}
.btn-primary {
    width: 100%;
    padding: 0.8rem;
    background: #e040fb;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-primary:hover {
    opacity: 0.9;
}
.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.form-error {
    background: rgba(255, 71, 87, 0.15);
    border: 1px solid rgba(255, 71, 87, 0.3);
    color: #ff6b81;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}
.form-success {
    background: rgba(46, 213, 115, 0.15);
    border: 1px solid rgba(46, 213, 115, 0.3);
    color: #7bed9f;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}
.form-success a {
    color: #e040fb;
    text-decoration: none;
    font-weight: 600;
}
.form-success a:hover {
    text-decoration: underline;
}
.auth-footer {
    text-align: center;
    color: #aaa;
    margin-top: 1.5rem;
    font-size: 0.9rem;
}
.auth-footer a {
    color: #e040fb;
    text-decoration: none;
}
.auth-footer a:hover {
    text-decoration: underline;
}
</style>

<section class="auth-section">
    <div class="auth-card">
        <h1>Reset Password</h1>
        <p class="auth-subtitle">Enter your new password below</p>

        <form id="resetForm" class="auth-form">
            <?= CSRF::field() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6" placeholder="Repeat your password" autocomplete="new-password">
            </div>
            <div id="resetError" class="form-error" style="display:none"></div>
            <div id="resetSuccess" class="form-success" style="display:none"></div>
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>

        <p class="auth-footer">
            <a href="<?= url('login') ?>">Back to login</a>
        </p>
    </div>
</section>

<script>
document.getElementById('resetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const errorEl = document.getElementById('resetError');
    const successEl = document.getElementById('resetSuccess');
    errorEl.style.display = 'none';
    successEl.style.display = 'none';

    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;

    if (password.length < 6) {
        errorEl.textContent = 'Password must be at least 6 characters.';
        errorEl.style.display = 'block';
        return;
    }

    if (password !== passwordConfirm) {
        errorEl.textContent = 'Passwords do not match.';
        errorEl.style.display = 'block';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Resetting...';

    try {
        const res = await fetch(BASE + '/api/auth/reset-password', { method: 'POST', body: new FormData(form) });
        const data = await res.json();

        if (data.success) {
            successEl.innerHTML = 'Your password has been reset successfully. <a href="' + BASE + '/login">Log in now</a>';
            successEl.style.display = 'block';
            btn.style.display = 'none';
        } else {
            errorEl.textContent = data.message || 'Reset failed. The link may have expired.';
            errorEl.style.display = 'block';
        }
    } catch (err) {
        errorEl.textContent = 'Connection error. Please try again.';
        errorEl.style.display = 'block';
    }

    btn.disabled = false;
    btn.textContent = 'Reset Password';
});
</script>
