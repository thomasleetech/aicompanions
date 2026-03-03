<?php $pageTitle = View::e($gig['display_name'] ?? $gig['title']) . ' - Companion'; ?>

<section class="detail-section">
    <div class="detail-grid">
        <div class="detail-main">
            <div class="detail-hero">
                <img src="<?= View::e($gig['image_url']) ?>" alt="<?= View::e($gig['display_name'] ?? '') ?>" class="detail-image">
                <div class="detail-hero-info">
                    <span class="companion-type-badge"><?= View::e($gig['companion_type']) ?></span>
                    <h1><?= View::e($gig['display_name'] ?? $gig['title']) ?></h1>
                    <div class="detail-stats">
                        <span class="companion-rating">&#9733; <?= number_format($gig['rating'], 1) ?> (<?= $gig['review_count'] ?> reviews)</span>
                        <span><?= $gig['total_orders'] ?> conversations</span>
                        <span><?= View::e($gig['response_time']) ?></span>
                    </div>
                </div>
            </div>

            <div class="detail-description">
                <h3>About</h3>
                <p><?= nl2br(View::e($gig['description'])) ?></p>
            </div>

            <div class="detail-tags">
                <?php foreach (explode(',', $gig['tags'] ?? '') as $tag): ?>
                    <?php if (trim($tag)): ?>
                        <span class="tag"><?= View::e(trim($tag)) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($reviews)): ?>
            <div class="detail-reviews">
                <h3>Reviews</h3>
                <?php foreach ($reviews as $r): ?>
                <div class="review-card">
                    <div class="review-header">
                        <strong><?= View::e($r['display_name'] ?? 'User') ?></strong>
                        <span class="review-rating">
                            <?= str_repeat('&#9733;', $r['rating']) ?><?= str_repeat('&#9734;', 5 - $r['rating']) ?>
                        </span>
                    </div>
                    <p><?= View::e($r['comment']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="detail-sidebar">
            <div class="detail-pricing-card">
                <h3>Start Chatting</h3>
                <div class="pricing-options">
                    <div class="pricing-option">
                        <span class="pricing-label">Per message</span>
                        <span class="pricing-value">$<?= number_format($gig['price_per_message'] ?? 0, 2) ?></span>
                    </div>
                    <div class="pricing-option">
                        <span class="pricing-label">Per hour</span>
                        <span class="pricing-value">$<?= number_format($gig['price_per_hour'], 2) ?></span>
                    </div>
                    <?php if ($gig['monthly_price']): ?>
                    <div class="pricing-option featured">
                        <span class="pricing-label">Monthly (unlimited)</span>
                        <span class="pricing-value">$<?= number_format($gig['monthly_price'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($user) && $user): ?>
                    <a href="<?= url('chat/' . $gig['id']) ?>" class="btn btn-primary btn-block">
                        <?= $hasAccess ? 'Continue Chatting' : 'Try 3 Free Messages' ?>
                    </a>
                <?php else: ?>
                    <a href="<?= url('register') ?>" class="btn btn-primary btn-block">Sign Up to Chat</a>
                <?php endif; ?>

                <div class="detail-info-list">
                    <div class="info-item">
                        <span>Languages</span>
                        <span><?= View::e($gig['languages'] ?? 'English') ?></span>
                    </div>
                    <div class="info-item">
                        <span>Availability</span>
                        <span><?= View::e($gig['availability'] ?? 'Flexible') ?></span>
                    </div>
                    <div class="info-item">
                        <span>Response time</span>
                        <span><?= View::e($gig['response_time'] ?? 'Within 1 hour') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
