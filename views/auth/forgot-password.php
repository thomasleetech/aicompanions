<?php $pageTitle = 'Forgot Password - Amorai'; ?>

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
        <h1>Forgot Password</h1>
        <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>

        <form id="forgotForm" class="auth-form">
            <?= CSRF::field() ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="you@example.com" autocomplete="email">
            </div>
            <div id="forgotError" class="form-error" style="display:none"></div>
            <div id="forgotSuccess" class="form-success" style="display:none"></div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>

        <p class="auth-footer">
            Remember your password? <a href="<?= url('login') ?>">Log in</a>
        </p>
    </div>
</section>

<script>
document.getElementById('forgotForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const errorEl = document.getElementById('forgotError');
    const successEl = document.getElementById('forgotSuccess');
    errorEl.style.display = 'none';
    successEl.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Sending...';

    try {
        const res = await fetch(BASE + '/api/auth/forgot-password', { method: 'POST', body: new FormData(form) });
        const data = await res.json();

        if (data.success) {
            successEl.textContent = "If an account exists with that email, we've sent a reset link.";
            successEl.style.display = 'block';
            form.reset();
        } else {
            errorEl.textContent = data.message || 'Something went wrong. Please try again.';
            errorEl.style.display = 'block';
        }
    } catch (err) {
        errorEl.textContent = 'Connection error. Please try again.';
        errorEl.style.display = 'block';
    }

    btn.disabled = false;
    btn.textContent = 'Send Reset Link';
});
</script>
