<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($pageTitle ?? 'Companion - AI That Actually Gets You') ?></title>
    <meta name="description" content="<?= View::e($pageDesc ?? 'Meet your AI companion. Real conversations. Zero judgment. Available 24/7.') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('css/app.css') ?>">
    <?= CSRF::metaTag() ?>
    <script>const BASE = '<?= BASE_URL ?>';</script>
</head>
<body>
    <header id="header">
        <a href="<?= url('/') ?>" class="logo">
            <div class="logo-icon">C</div>
            <span>Companion</span>
        </a>
        <nav id="nav">
            <a href="<?= url('browse') ?>">Browse</a>
            <a href="<?= url('browse/ai-girlfriend') ?>">Girlfriends</a>
            <a href="<?= url('browse/ai-boyfriend') ?>">Boyfriends</a>
            <?php if (isset($user) && $user): ?>
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
                    <div class="logo-icon">C</div>
                    <span>Companion</span>
                </div>
                <p>AI companions for meaningful connection. Always here when you need someone to talk to.</p>
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
            &copy; <?= date('Y') ?> Companion. All rights reserved.
        </div>
    </footer>

    <script src="<?= url('js/app.js') ?>"></script>
</body>
</html>
