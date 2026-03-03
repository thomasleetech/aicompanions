<?php $pageTitle = 'Your Companions - Companion'; ?>

<section class="app-section">
    <div class="app-layout">
        <!-- Sidebar: Conversations -->
        <aside class="app-sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Chats</h2>
                <a href="<?= url('browse') ?>" class="btn btn-sm btn-ghost">+ New</a>
            </div>

            <div class="conversation-list" id="conversationList">
                <?php if (empty($conversations)): ?>
                    <div class="empty-state-small">
                        <p>No conversations yet</p>
                        <a href="<?= url('browse') ?>" class="btn btn-sm btn-primary">Find a Companion</a>
                    </div>
                <?php endif; ?>
                <?php foreach ($conversations as $c): ?>
                <a href="<?= url('chat/' . $c['gig_id']) ?>" class="conversation-item" data-gig="<?= $c['gig_id'] ?>">
                    <img src="<?= View::e($c['image_url'] ?? '') ?>" alt="" class="conv-avatar">
                    <div class="conv-info">
                        <h4><?= View::e($c['title'] ?? 'Chat') ?></h4>
                        <p><?= View::e(substr($c['last_message'] ?? '', 0, 50)) ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($gigs)): ?>
            <div class="sidebar-section">
                <h3>Discover</h3>
                <div class="discover-list">
                    <?php foreach (array_slice($gigs, 0, 5) as $g): ?>
                    <a href="<?= url('chat/' . $g['id']) ?>" class="discover-item">
                        <img src="<?= View::e($g['image_url'] ?? '') ?>" alt="" class="disc-avatar">
                        <div>
                            <h4><?= View::e($g['display_name'] ?? '') ?></h4>
                            <span class="tag-sm"><?= View::e($g['companion_type']) ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>

        <!-- Main content -->
        <div class="app-main">
            <div class="app-welcome">
                <h2>Welcome back, <?= View::e($user['display_name'] ?? $user['username'] ?? '') ?></h2>
                <p>Select a conversation or find a new companion to chat with.</p>
                <a href="<?= url('browse') ?>" class="btn btn-primary">Browse Companions</a>
            </div>
        </div>
    </div>
</section>
