<?php
// Session cookie settings for Shopify Admin iframe
if (!session_id()) {
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None',
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=None', '', true, true);
    }
    session_start();
}

// Read credentials from environment
$api_key = getenv('SHOPIFY_API_KEY') ?: '';
$secret  = getenv('SHOPIFY_API_SECRET') ?: '';

if ($api_key === '' || $secret === '') {
    http_response_code(500);
    echo "Missing SHOPIFY_API_KEY / SHOPIFY_API_SECRET in environment.";
    exit;
}

// Resolve shop/host/token consistently
$shop = $_GET['shop'] ?? ($_SESSION['shop'] ?? '');
$host = $_GET['host'] ?? ($_SESSION['host'] ?? '');
$token = $_GET['token'] ?? ($_SESSION['token'] ?? '');

// Persist for later pages
if ($shop) $_SESSION['shop'] = $shop;
if ($host) $_SESSION['host'] = $host;
if ($token) $_SESSION['token'] = $token;
