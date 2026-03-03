<?php $pageTitle = ($title ?? 'Browse Companions') . ' - Companion'; ?>

<section class="browse-section">
    <div class="browse-header">
        <h1><?= View::e($title ?? 'Browse Companions') ?></h1>
        <p>Find someone who matches your vibe</p>
    </div>

    <div class="browse-filters">
        <div class="filter-group">
            <select id="filterType" onchange="applyFilters()">
                <option value="all" <?= ($type ?? '') === 'all' ? 'selected' : '' ?>>All Types</option>
                <option value="girlfriend" <?= ($type ?? '') === 'girlfriend' ? 'selected' : '' ?>>Girlfriends</option>
                <option value="boyfriend" <?= ($type ?? '') === 'boyfriend' ? 'selected' : '' ?>>Boyfriends</option>
                <option value="non-binary" <?= ($type ?? '') === 'non-binary' ? 'selected' : '' ?>>Non-Binary</option>
            </select>
            <select id="filterCategory" onchange="applyFilters()">
                <option value="all">All Categories</option>
                <option value="emotional-support">Emotional Support</option>
                <option value="companionship">Companionship</option>
                <option value="conversation">Conversation</option>
                <option value="entertainment">Entertainment</option>
                <option value="motivation">Motivation</option>
            </select>
            <select id="filterSort" onchange="applyFilters()">
                <option value="newest">Newest</option>
                <option value="rating">Top Rated</option>
                <option value="popular">Most Popular</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
            </select>
        </div>
        <div class="filter-search">
            <input type="text" id="filterSearch" placeholder="Search companions..." value="<?= View::e($search ?? '') ?>" onkeyup="debounceFilter()">
        </div>
    </div>

    <div class="companions-grid" id="companionsGrid">
        <?php if (empty($companions)): ?>
            <div class="empty-state">
                <h3>No companions found</h3>
                <p>Try adjusting your filters</p>
            </div>
        <?php endif; ?>
        <?php foreach ($companions as $c): ?>
        <a href="<?= url('companion/' . $c['id']) ?>" class="companion-card">
            <img src="<?= View::e($c['image_url']) ?>" class="companion-img" alt="<?= View::e($c['display_name'] ?? '') ?>" loading="lazy">
            <div class="companion-info">
                <div class="companion-header">
                    <h4><?= View::e($c['display_name'] ?? '') ?></h4>
                    <span class="companion-type-badge"><?= View::e($c['companion_type']) ?></span>
                </div>
                <p><?= View::e(substr($c['description'], 0, 80)) ?>...</p>
                <div class="companion-tags">
                    <?php foreach (array_slice(explode(',', $c['tags'] ?? ''), 0, 3) as $tag): ?>
                        <?php if (trim($tag)): ?>
                            <span class="tag"><?= View::e(trim($tag)) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="companion-meta">
                    <span class="companion-rating">&#9733; <?= number_format($c['rating'], 1) ?> <small>(<?= $c['review_count'] ?>)</small></span>
                    <span class="companion-price">$<?= number_format($c['price_per_hour'], 0) ?>/hr</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<script>
let filterTimeout;
function debounceFilter() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(applyFilters, 300);
}

function applyFilters() {
    const params = new URLSearchParams({
        type: document.getElementById('filterType').value,
        category: document.getElementById('filterCategory').value,
        sort: document.getElementById('filterSort').value,
        search: document.getElementById('filterSearch').value,
    });
    window.location.href = BASE + '/browse?' + params.toString();
}
</script>
