<?php

class HomeController
{
    public static function index(): void
    {
        $featured = Database::fetchAll(
            "SELECT g.*, u.display_name FROM gigs g JOIN users u ON g.user_id = u.id WHERE g.is_active = 1 ORDER BY g.is_featured DESC, g.rating DESC LIMIT 12"
        );

        View::render('home', [
            'companions' => $featured,
            'user'       => Auth::user(),
        ]);
    }

    public static function landing(array $params): void
    {
        $type = $params['type'] ?? 'all';
        $typeMap = [
            'ai-girlfriend'     => 'girlfriend',
            'ai-boyfriend'      => 'boyfriend',
            'someone-to-talk-to' => 'all',
        ];

        $companionType = $typeMap[$type] ?? 'all';
        $where = $companionType !== 'all' ? "AND g.companion_type = ?" : "";
        $queryParams = $companionType !== 'all' ? [$companionType] : [];

        $companions = Database::fetchAll(
            "SELECT g.*, u.display_name FROM gigs g JOIN users u ON g.user_id = u.id WHERE g.is_active = 1 {$where} ORDER BY g.rating DESC LIMIT 20",
            $queryParams
        );

        $titles = [
            'ai-girlfriend'      => 'AI Girlfriends',
            'ai-boyfriend'       => 'AI Boyfriends',
            'someone-to-talk-to' => 'Someone to Talk To',
        ];

        View::render('gigs/browse', [
            'companions' => $companions,
            'title'      => $titles[$type] ?? 'Browse Companions',
            'type'       => $companionType,
            'user'       => Auth::user(),
        ]);
    }
}
