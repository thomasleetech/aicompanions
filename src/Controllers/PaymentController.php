<?php

class PaymentController
{
    // ========== STRIPE CHECKOUT ==========
    public static function stripeCheckout(): void
    {
        if (!Auth::requireLogin()) return;

        $stripeKey = Env::get('STRIPE_SECRET_KEY');
        if (!$stripeKey) {
            View::json(['success' => false, 'message' => 'Stripe not configured']);
            return;
        }

        $gigId = (int) ($_POST['gig_id'] ?? 0);
        $type = $_POST['purchase_type'] ?? 'upgrade'; // upgrade, time, subscription
        $upgradeType = $_POST['upgrade_type'] ?? '';
        $minutes = (int) ($_POST['minutes'] ?? 60);

        $gig = Database::fetch("SELECT * FROM gigs WHERE id = ?", [$gigId]);
        if (!$gig) {
            View::json(['success' => false, 'message' => 'Companion not found']);
            return;
        }

        // Determine price and description
        $price = 0;
        $description = '';

        if ($type === 'upgrade') {
            $prices = [
                'voice' => 4.99, 'voice_input' => 5.99, 'email' => 7.99,
                'photos' => 9.99, 'videos' => 14.99, 'web_search' => 9.99,
                'creative' => 12.99, 'realtime_vision' => 19.99,
                'spicy_personality' => 14.99, 'spicy' => 19.99, 'spicy_videos' => 24.99,
                'premium' => 29.99, 'premium_plus' => 99.99,
            ];
            $price = $prices[$upgradeType] ?? 0;
            $description = ucwords(str_replace('_', ' ', $upgradeType)) . ' for ' . ($gig['title'] ?? 'Companion');
        } elseif ($type === 'time') {
            $price = ($minutes / 60) * ($gig['price_per_hour'] ?? 25);
            $description = "{$minutes} minutes with " . ($gig['title'] ?? 'Companion');
        } elseif ($type === 'subscription') {
            $price = $gig['monthly_price'] ?? 79;
            $description = "Monthly subscription - " . ($gig['title'] ?? 'Companion');
        }

        if ($price <= 0) {
            View::json(['success' => false, 'message' => 'Invalid purchase']);
            return;
        }

        // Create Stripe checkout session
        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => $stripeKey . ':',
            CURLOPT_POSTFIELDS     => http_build_query([
                'mode' => 'payment',
                'success_url' => (Env::get('APP_URL') ?: 'https://' . $_SERVER['HTTP_HOST']) . '/chat/' . $gigId . '?payment=success',
                'cancel_url'  => (Env::get('APP_URL') ?: 'https://' . $_SERVER['HTTP_HOST']) . '/chat/' . $gigId . '?payment=cancelled',
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][product_data][name]' => $description,
                'line_items[0][price_data][unit_amount]' => (int) round($price * 100),
                'line_items[0][quantity]' => 1,
                'metadata[user_id]'      => Auth::id(),
                'metadata[gig_id]'       => $gigId,
                'metadata[type]'         => $type,
                'metadata[upgrade_type]' => $upgradeType,
                'metadata[minutes]'      => $minutes,
                'client_reference_id'    => Auth::id() . '_' . $gigId . '_' . time(),
            ]),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $session = json_decode($response, true);

        if ($httpCode !== 200 || empty($session['url'])) {
            View::json(['success' => false, 'message' => 'Failed to create checkout session']);
            return;
        }

        // Record pending payment
        Database::insert('payments', [
            'user_id'          => Auth::id(),
            'gig_id'           => $gigId,
            'amount'           => $price,
            'payment_type'     => 'one_time',
            'stripe_payment_id' => $session['id'] ?? '',
            'status'           => 'pending',
        ]);

        View::json(['success' => true, 'url' => $session['url']]);
    }

    // ========== STRIPE WEBHOOK ==========
    public static function stripeWebhook(): void
    {
        $payload = file_get_contents('php://input');
        $event = json_decode($payload, true);

        if (!$event || ($event['type'] ?? '') !== 'checkout.session.completed') {
            http_response_code(200);
            echo 'ok';
            return;
        }

        $session = $event['data']['object'] ?? [];
        $meta = $session['metadata'] ?? [];
        $userId = (int) ($meta['user_id'] ?? 0);
        $gigId = (int) ($meta['gig_id'] ?? 0);
        $type = $meta['type'] ?? '';
        $upgradeType = $meta['upgrade_type'] ?? '';
        $minutes = (int) ($meta['minutes'] ?? 60);

        if (!$userId || !$gigId) {
            http_response_code(200);
            echo 'ok';
            return;
        }

        // Update payment record
        Database::query(
            "UPDATE payments SET status = 'completed' WHERE stripe_payment_id = ? AND status = 'pending'",
            [$session['id'] ?? '']
        );

        // Fulfill the purchase
        self::fulfillPurchase($userId, $gigId, $type, $upgradeType, $minutes);

        http_response_code(200);
        echo 'ok';
    }

    // ========== PAYPAL CREATE ORDER ==========
    public static function paypalCreate(): void
    {
        if (!Auth::requireLogin()) return;

        $clientId = Env::get('PAYPAL_CLIENT_ID');
        $secret = Env::get('PAYPAL_SECRET');
        if (!$clientId || !$secret) {
            View::json(['success' => false, 'message' => 'PayPal not configured']);
            return;
        }

        $gigId = (int) ($_POST['gig_id'] ?? 0);
        $type = $_POST['purchase_type'] ?? 'upgrade';
        $upgradeType = $_POST['upgrade_type'] ?? '';
        $minutes = (int) ($_POST['minutes'] ?? 60);

        $gig = Database::fetch("SELECT * FROM gigs WHERE id = ?", [$gigId]);
        if (!$gig) {
            View::json(['success' => false, 'message' => 'Companion not found']);
            return;
        }

        // Calculate price
        $prices = [
            'voice' => 4.99, 'voice_input' => 5.99, 'email' => 7.99,
            'photos' => 9.99, 'videos' => 14.99, 'web_search' => 9.99,
            'creative' => 12.99, 'realtime_vision' => 19.99,
            'spicy_personality' => 14.99, 'spicy' => 19.99, 'spicy_videos' => 24.99,
            'premium' => 29.99, 'premium_plus' => 99.99,
        ];

        $price = 0;
        if ($type === 'upgrade') $price = $prices[$upgradeType] ?? 0;
        elseif ($type === 'time') $price = ($minutes / 60) * ($gig['price_per_hour'] ?? 25);
        elseif ($type === 'subscription') $price = $gig['monthly_price'] ?? 79;

        if ($price <= 0) {
            View::json(['success' => false, 'message' => 'Invalid purchase']);
            return;
        }

        $baseUrl = Env::get('PAYPAL_MODE') === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        // Get access token
        $ch = curl_init($baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => $clientId . ':' . $secret,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        ]);
        $tokenResp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $token = $tokenResp['access_token'] ?? '';
        if (!$token) {
            View::json(['success' => false, 'message' => 'PayPal auth failed']);
            return;
        }

        // Create order
        $ch = curl_init($baseUrl . '/v2/checkout/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($price, 2, '.', ''),
                    ],
                    'custom_id' => json_encode([
                        'user_id' => Auth::id(),
                        'gig_id' => $gigId,
                        'type' => $type,
                        'upgrade_type' => $upgradeType,
                        'minutes' => $minutes,
                    ]),
                ]],
            ]),
        ]);

        $orderResp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (empty($orderResp['id'])) {
            View::json(['success' => false, 'message' => 'Failed to create PayPal order']);
            return;
        }

        // Record pending payment
        Database::insert('payments', [
            'user_id'          => Auth::id(),
            'gig_id'           => $gigId,
            'amount'           => $price,
            'payment_type'     => 'one_time',
            'stripe_payment_id' => 'pp_' . $orderResp['id'],
            'status'           => 'pending',
        ]);

        View::json(['success' => true, 'order_id' => $orderResp['id']]);
    }

    // ========== PAYPAL CAPTURE ORDER ==========
    public static function paypalCapture(): void
    {
        if (!Auth::requireLogin()) return;

        $orderId = $_POST['order_id'] ?? '';
        if (!$orderId) {
            View::json(['success' => false, 'message' => 'Missing order ID']);
            return;
        }

        $clientId = Env::get('PAYPAL_CLIENT_ID');
        $secret = Env::get('PAYPAL_SECRET');
        $baseUrl = Env::get('PAYPAL_MODE') === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        // Get token
        $ch = curl_init($baseUrl . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => $clientId . ':' . $secret,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        ]);
        $token = (json_decode(curl_exec($ch), true))['access_token'] ?? '';
        curl_close($ch);

        // Capture
        $ch = curl_init($baseUrl . "/v2/checkout/orders/{$orderId}/capture");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_POSTFIELDS => '{}',
        ]);

        $captureResp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (($captureResp['status'] ?? '') !== 'COMPLETED') {
            View::json(['success' => false, 'message' => 'Payment not completed']);
            return;
        }

        // Extract custom data
        $customId = $captureResp['purchase_units'][0]['payments']['captures'][0]['custom_id']
                  ?? $captureResp['purchase_units'][0]['custom_id'] ?? '{}';
        $meta = json_decode($customId, true) ?: [];

        $userId = (int) ($meta['user_id'] ?? Auth::id());
        $gigId = (int) ($meta['gig_id'] ?? 0);
        $type = $meta['type'] ?? '';
        $upgradeType = $meta['upgrade_type'] ?? '';
        $minutes = (int) ($meta['minutes'] ?? 60);

        // Update payment
        Database::query(
            "UPDATE payments SET status = 'completed' WHERE stripe_payment_id = ? AND status = 'pending'",
            ['pp_' . $orderId]
        );

        // Fulfill
        self::fulfillPurchase($userId, $gigId, $type, $upgradeType, $minutes);

        View::json(['success' => true, 'message' => 'Payment completed']);
    }

    // ========== FULFILLMENT ==========
    private static function fulfillPurchase(int $userId, int $gigId, string $type, string $upgradeType, int $minutes): void
    {
        if ($type === 'upgrade' && $upgradeType) {
            // Activate the upgrade
            $existing = Database::fetch(
                "SELECT id FROM companion_upgrades WHERE user_id = ? AND gig_id = ? AND upgrade_type = ?",
                [$userId, $gigId, $upgradeType]
            );

            if ($existing) {
                Database::update('companion_upgrades', ['status' => 'active'], 'id = ?', [$existing['id']]);
            } else {
                Database::insert('companion_upgrades', [
                    'user_id'      => $userId,
                    'gig_id'       => $gigId,
                    'upgrade_type' => $upgradeType,
                    'price_paid'   => 0,
                    'status'       => 'active',
                ]);
            }

            // Bundle activations
            $bundles = [
                'premium'      => ['voice', 'voice_input', 'photos', 'email', 'creative'],
                'premium_plus' => ['voice', 'voice_input', 'photos', 'videos', 'email', 'creative', 'web_search', 'realtime_vision', 'spicy_personality', 'spicy', 'spicy_videos'],
            ];

            if (isset($bundles[$upgradeType])) {
                foreach ($bundles[$upgradeType] as $inc) {
                    $has = Database::fetch(
                        "SELECT id FROM companion_upgrades WHERE user_id = ? AND gig_id = ? AND upgrade_type = ? AND status = 'active'",
                        [$userId, $gigId, $inc]
                    );
                    if (!$has) {
                        Database::insert('companion_upgrades', [
                            'user_id' => $userId, 'gig_id' => $gigId,
                            'upgrade_type' => $inc, 'price_paid' => 0, 'status' => 'active',
                        ]);
                    }
                }
            }
        } elseif ($type === 'time') {
            Database::insert('time_purchases', [
                'user_id'           => $userId,
                'gig_id'            => $gigId,
                'minutes_purchased' => $minutes,
                'minutes_remaining' => $minutes,
                'price_paid'        => 0,
                'status'            => 'active',
            ]);
        } elseif ($type === 'subscription') {
            $existing = Database::fetch(
                "SELECT id FROM subscriptions WHERE user_id = ? AND gig_id = ?",
                [$userId, $gigId]
            );
            if ($existing) {
                Database::update('subscriptions', [
                    'status'              => 'active',
                    'plan_type'           => 'monthly',
                    'current_period_start' => date('Y-m-d H:i:s'),
                    'current_period_end'  => date('Y-m-d H:i:s', strtotime('+30 days')),
                ], 'id = ?', [$existing['id']]);
            } else {
                Database::insert('subscriptions', [
                    'user_id'              => $userId,
                    'gig_id'               => $gigId,
                    'plan_type'            => 'monthly',
                    'status'               => 'active',
                    'current_period_start' => date('Y-m-d H:i:s'),
                    'current_period_end'   => date('Y-m-d H:i:s', strtotime('+30 days')),
                ]);
            }
        }
    }
}
