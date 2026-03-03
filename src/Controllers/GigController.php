<?php

class GigController
{
    public static function browse(): void
    {
        $type = $_GET['type'] ?? 'all';
        $category = $_GET['category'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'newest';

        $where = "WHERE g.is_active = 1";
        $params = [];

        if ($type !== 'all') {
            $where .= " AND g.companion_type = ?";
            $params[] = $type;
        }
        if ($category !== 'all') {
            $where .= " AND g.category = ?";
            $params[] = $category;
        }
        if ($search) {
            $where .= " AND (g.title LIKE ? OR g.description LIKE ? OR g.tags LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s]);
        }

        $sortMap = [
            'price_low'  => 'g.price_per_hour ASC',
            'price_high' => 'g.price_per_hour DESC',
            'rating'     => 'g.rating DESC',
            'popular'    => 'g.total_orders DESC',
            'newest'     => 'g.created_at DESC',
        ];
        $orderBy = $sortMap[$sort] ?? 'g.created_at DESC';

        $companions = Database::fetchAll(
            "SELECT g.*, u.display_name FROM gigs g JOIN users u ON g.user_id = u.id {$where} ORDER BY g.is_featured DESC, {$orderBy} LIMIT 50",
            $params
        );

        View::render('gigs/browse', [
            'companions' => $companions,
            'title'      => 'Browse Companions',
            'type'       => $type,
            'category'   => $category,
            'search'     => $search,
            'sort'       => $sort,
            'user'       => Auth::user(),
        ]);
    }

    public static function show(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $gig = Database::fetch(
            "SELECT g.*, u.display_name, u.bio as provider_bio FROM gigs g JOIN users u ON g.user_id = u.id WHERE g.id = ?",
            [$id]
        );

        if (!$gig) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $reviews = Database::fetchAll(
            "SELECT r.*, u.display_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.gig_id = ? ORDER BY r.created_at DESC LIMIT 10",
            [$id]
        );

        $hasAccess = false;
        if (Auth::check()) {
            $sub = Database::fetch(
                "SELECT id FROM subscriptions WHERE user_id = ? AND gig_id = ? AND status = 'active'",
                [Auth::id(), $id]
            );
            $time = Database::fetch(
                "SELECT id FROM time_purchases WHERE user_id = ? AND gig_id = ? AND status = 'active' AND minutes_remaining > 0",
                [Auth::id(), $id]
            );
            $hasAccess = $sub || $time;
        }

        View::render('gigs/detail', [
            'gig'        => $gig,
            'reviews'    => $reviews,
            'hasAccess'  => $hasAccess,
            'user'       => Auth::user(),
        ]);
    }

    public static function apiList(): void
    {
        $type = $_POST['companion_type'] ?? 'all';
        $category = $_POST['category'] ?? 'all';
        $search = $_POST['search'] ?? '';
        $sort = $_POST['sort'] ?? 'newest';

        $where = "WHERE g.is_active = 1";
        $params = [];

        if ($type !== 'all') { $where .= " AND g.companion_type = ?"; $params[] = $type; }
        if ($category !== 'all') { $where .= " AND g.category = ?"; $params[] = $category; }
        if ($search) {
            $where .= " AND (g.title LIKE ? OR g.description LIKE ? OR g.tags LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s]);
        }

        $sortMap = [
            'price_low' => 'g.price_per_hour ASC', 'price_high' => 'g.price_per_hour DESC',
            'rating' => 'g.rating DESC', 'popular' => 'g.total_orders DESC', 'newest' => 'g.created_at DESC',
        ];

        $gigs = Database::fetchAll(
            "SELECT g.*, u.display_name FROM gigs g JOIN users u ON g.user_id = u.id {$where} ORDER BY g.is_featured DESC, " . ($sortMap[$sort] ?? 'g.created_at DESC') . " LIMIT 50",
            $params
        );

        View::json(['success' => true, 'gigs' => $gigs]);
    }
}
