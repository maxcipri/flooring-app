<?php
// CRITICAL: CSP headers MUST be first
header_remove("Content-Security-Policy");
header_remove("X-Frame-Options");
header("Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com", true);

include_once 'keys.php';
include_once 'shopify.php';
include_once 'connection.php';

$shop = $_GET['shop'] ?? ($_SESSION['shop'] ?? '');
$host = $_GET['host'] ?? ($_SESSION['host'] ?? '');
$token = $_GET['token'] ?? ($_SESSION['token'] ?? '');

if ($shop) $_SESSION['shop'] = $shop;
if ($host) $_SESSION['host'] = $host;
if ($token) $_SESSION['token'] = $token;

if (!$shop || !$token) {
    http_response_code(401);
    echo "Missing shop/token. Please open the app from Shopify Admin.";
    exit;
}

$sc = new ShopifyClient($shop, $token, $api_key, $secret);

// If DB is missing, run in LIVE MODE (Shopify API only)
if ($conn === null) {
    $count = null;
    $products = [];

    try {
        $respCount = $sc->call('GET', "/admin/api/2024-01/products/count.json", []);
        if (is_array($respCount) && isset($respCount['count'])) {
            $count = (int)$respCount['count'];
        }

        $respProducts = $sc->call('GET', "/admin/api/2024-01/products.json", ['limit' => 20]);
        if (is_array($respProducts) && isset($respProducts['products']) && is_array($respProducts['products'])) {
            $products = $respProducts['products'];
        }
    } catch (Exception $e) {
        $products = [];
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Flooring App, Live Mode</title>
        <style>
            body { font-family: Arial; padding: 24px; }
            .box { border: 1px solid #ddd; padding: 16px; border-radius: 8px; margin-bottom: 16px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
            .muted { color: #666; }
            a { color: #005bd3; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="box">
            <h2 style="margin:0 0 8px 0;">Live mode, DB disabled</h2>
            <div class="muted">Shop: <?php echo htmlspecialchars($shop); ?></div>
            <?php if ($count !== null): ?>
                <div style="margin-top:8px;">Products in store: <strong><?php echo $count; ?></strong></div>
            <?php else: ?>
                <div style="margin-top:8px;" class="muted">Could not read product count.</div>
            <?php endif; ?>

            <div style="margin-top:12px;" class="muted">
                DB features (filters, saved products table) are temporarily disabled. OAuth is OK.
