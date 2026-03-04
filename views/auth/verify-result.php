<?php $pageTitle = 'Email Verification - Lush'; ?>

<section class="auth-section">
    <div class="auth-card">
        <h2>Email Verification</h2>
        <?php if ($result['success']): ?>
            <p style="color:#4caf50;margin:20px 0"><?= View::e($result['message']) ?></p>
            <a href="<?= url('app') ?>" class="btn btn-primary" style="width:100%">Open App</a>
        <?php else: ?>
            <p style="color:#f44336;margin:20px 0"><?= View::e($result['message']) ?></p>
            <a href="<?= url('login') ?>" class="btn btn-ghost" style="width:100%">Back to Login</a>
        <?php endif; ?>
    </div>
</section>
