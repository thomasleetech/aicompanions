<?php $pageTitle = 'Sign Up - Lush'; ?>

<section class="auth-section">
    <div class="auth-card">
        <h1>Create your account</h1>
        <p class="auth-subtitle">Start chatting with AI companions for free</p>

        <form id="registerForm" class="auth-form">
            <?= CSRF::field() ?>
            <?php if (!empty($ref)): ?>
                <input type="hidden" name="ref_code" value="<?= View::e($ref) ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required minlength="3" placeholder="Choose a username" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="you@example.com" autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6" placeholder="At least 6 characters" autocomplete="new-password">
            </div>
            <div id="registerError" class="form-error" style="display:none"></div>
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>

        <p class="auth-footer">
            Already have an account? <a href="<?= url('login') ?>">Log in</a>
        </p>
    </div>
</section>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    const errorEl = document.getElementById('registerError');
    errorEl.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Creating account...';

    try {
        const res = await fetch(BASE + '/api/auth/register', { method: 'POST', body: new FormData(form) });
        const data = await res.json();

        if (data.success) {
            window.location.href = BASE + '/app';
        } else {
            errorEl.textContent = data.message || 'Registration failed';
            errorEl.style.display = 'block';
        }
    } catch (err) {
        errorEl.textContent = 'Connection error. Please try again.';
        errorEl.style.display = 'block';
    }

    btn.disabled = false;
    btn.textContent = 'Create Account';
});
</script>
