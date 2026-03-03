<?php

class UpgradeController
{
    private static array $validTypes = [
        'voice', 'voice_input', 'email', 'photos', 'videos',
        'web_search', 'creative', 'realtime_vision',
        'spicy_personality', 'spicy', 'spicy_videos',
        'premium', 'premium_plus',
    ];

    private static array $prices = [
        'voice'             => 4.99,
        'voice_input'       => 5.99,
        'email'             => 7.99,
        'photos'            => 9.99,
        'videos'            => 14.99,
        'web_search'        => 9.99,
        'creative'          => 12.99,
        'realtime_vision'   => 19.99,
        'spicy_personality' => 14.99,
        'spicy'             => 19.99,
        'spicy_videos'      => 24.99,
        'premium'           => 29.99,
        'premium_plus'      => 99.99,
    ];

    // What each bundle includes
    private static array $bundleIncludes = [
        'premium'      => ['voice', 'voice_input', 'photos', 'email', 'creative'],
        'premium_plus' => ['voice', 'voice_input', 'photos', 'videos', 'email', 'creative', 'web_search', 'realtime_vision', 'spicy_personality', 'spicy', 'spicy_videos'],
    ];

    // Prerequisites
    private static array $requires = [
        'spicy'        => ['photos', 'premium', 'premium_plus'],
        'spicy_videos' => ['videos', 'premium_plus'],
    ];

    public static function purchase(): void
    {
        if (!Auth::requireLogin()) return;

        $gigId = (int) ($_POST['gig_id'] ?? 0);
        $upgradeType = trim($_POST['upgrade_type'] ?? '');

        if (!in_array($upgradeType, self::$validTypes)) {
            View::json(['success' => false, 'message' => 'Invalid upgrade type']);
            return;
        }

        $gig = Database::fetch("SELECT id, title FROM gigs WHERE id = ?", [$gigId]);
        if (!$gig) {
            View::json(['success' => false, 'message' => 'Companion not found']);
            return;
        }

        // Check if already owned
        $existing = Database::fetch(
            "SELECT id FROM companion_upgrades WHERE user_id = ? AND gig_id = ? AND upgrade_type = ? AND status = 'active'",
            [Auth::id(), $gigId, $upgradeType]
        );
        if ($existing) {
            View::json(['success' => false, 'message' => 'You already own this upgrade']);
            return;
        }

        // Check prerequisites
        if (isset(self::$requires[$upgradeType])) {
            $hasPrereq = false;
            foreach (self::$requires[$upgradeType] as $req) {
                $check = Database::fetch(
                    "SELECT id FROM companion_upgrades WHERE user_id = ? AND gig_id = ? AND upgrade_type = ? AND status = 'active'",
                    [Auth::id(), $gigId, $req]
                );
                if ($check) { $hasPrereq = true; break; }
            }
            if (!$hasPrereq) {
                $needed = self::$requires[$upgradeType][0];
                View::json(['success' => false, 'message' => "You need the {$needed} pack first"]);
                return;
            }
        }

        $price = self::$prices[$upgradeType] ?? 0;

        // For now: direct purchase (Stripe/PayPal integration will gate this later)
        // Record the upgrade
        Database::insert('companion_upgrades', [
            'user_id'      => Auth::id(),
            'gig_id'       => $gigId,
            'upgrade_type' => $upgradeType,
            'price_paid'   => $price,
            'status'       => 'active',
        ]);

        // Record payment
        Database::insert('payments', [
            'user_id'      => Auth::id(),
            'gig_id'       => $gigId,
            'amount'       => $price,
            'payment_type' => 'one_time',
            'status'       => 'completed',
        ]);

        // If it's a bundle, also activate included upgrades
        if (isset(self::$bundleIncludes[$upgradeType])) {
            foreach (self::$bundleIncludes[$upgradeType] as $included) {
                $alreadyHas = Database::fetch(
                    "SELECT id FROM companion_upgrades WHERE user_id = ? AND gig_id = ? AND upgrade_type = ? AND status = 'active'",
                    [Auth::id(), $gigId, $included]
                );
                if (!$alreadyHas) {
                    Database::insert('companion_upgrades', [
                        'user_id'      => Auth::id(),
                        'gig_id'       => $gigId,
                        'upgrade_type' => $included,
                        'price_paid'   => 0,
                        'status'       => 'active',
                    ]);
                }
            }
        }

        $names = [
            'voice' => 'Voice Pack', 'voice_input' => 'Voice Input', 'email' => 'Email Access',
            'photos' => 'Photo Pack', 'videos' => 'Video Pack', 'web_search' => 'Internet Access',
            'creative' => 'Creative Mode', 'realtime_vision' => 'Real-Time Vision',
            'spicy_personality' => 'Spicy Personality', 'spicy' => 'Spicy Photos',
            'spicy_videos' => 'Spicy Videos', 'premium' => 'Premium Bundle', 'premium_plus' => 'VIP Bundle',
        ];

        View::json([
            'success' => true,
            'message' => ($names[$upgradeType] ?? $upgradeType) . ' unlocked!',
            'upgrade' => $upgradeType,
        ]);
    }
}
