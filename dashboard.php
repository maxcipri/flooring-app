<?php
declare(strict_types=1);

// Allow Shopify Admin embedding
header_remove("Content-Security-Policy");
header_remove("X-Frame-Options");
header("Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com", true);

// Pull credentials + session values
require_once __DIR__ . '/keys.php';
require_once __DIR__ . '/shopify.php';
require_once __DIR__ . '/connection.php';

// Resolve shop/host/token
$shop  = $_GET['shop']  ?? ($_SESSION['shop']  ?? '');
$host  = $_GET['host']  ?? ($_SESSION['host']  ?? '');
$token = $_GET['token'] ?? ($_SESSION['token'] ?? '');

if ($shop)  $_SESSION['shop'] = $shop;
if ($host)  $_SESSION['host'] = $host;
if ($token) $_SESSION['token'] = $token;

if ($shop === '' || $token === '') {
    http_response_code(401);
    echo "Missing shop/token. Open the app from Shopify Admin.";
    exit;
}

$sc = new ShopifyClient($shop, $token, $api_key, $secret);

// LIVE MODE if DB is missing
if ($conn === null) {
    $count = null;
    $products = [];
    $error = null;

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
        $error = $e->getMessage();
    }

    echo '<!DOCTYPE html>';
    echo '<html><head><meta charset="utf-8"><title>Flooring App - Live Mode</title>';
    echo '<style>
        body{font-family:Arial;padding:24px;}
        .box{border:1px solid #ddd;padding:16px;border-radius:8px;margin-bottom:16px;}
        table{width:100%;border-collapse:collapse;}
        th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;}
        .muted{color:#666;}
    </style>';
    echo '</head><body>';

    echo '<div class="box">';
    echo '<h2 style="margin:0 0 8px 0;">Live mode (DB disabled)</h2>';
    echo '<div class="muted">Shop: ' . htmlspecialchars($shop) . '</div>';
    if ($count !== null) {
        echo '<div style="margin-top:8px;">Products in store: <strong>' . $count . '</strong></div>';
    } else {
        echo '<div style="margin-top:8px;" class="muted">Could not read product count.</div>';
    }
    echo '<div style="margin-top:12px;" class="muted">DB features are temporarily disabled.</div>';
    if ($error) {
        echo '<div style="margin-top:12px;" class="muted">API error: ' . htmlspecialchars($error) . '</div>';
    }
    echo '</div>';

    echo '<div class="box">';
    echo '<h3 style="margin:0 0 12px 0;">First 20 products (from Shopify)</h3>';

    if (empty($products)) {
        echo '<div class="muted">No products returned (or API call failed).</div>';
    } else {
        echo '<table><thead><tr><th>ID</th><th>Title</th><th>Handle</th><th>Status</th></tr></thead><tbody>';
        foreach ($products as $p) {
            $id = htmlspecialchars((string)($p['id'] ?? ''));
            $title = htmlspecialchars((string)($p['title'] ?? ''));
            $handle = htmlspecialchars((string)($p['handle'] ?? ''));
            $status = htmlspecialchars((string)($p['status'] ?? ''));
            echo "<tr><td>{$id}</td><td>{$title}</td><td>{$handle}</td><td>{$status}</td></tr>";
        }
        echo '</tbody></table>';
    }

    echo '</div>';

    echo '<div class="box">';
    echo '<h3 style="margin:0 0 8px 0;">DB later</h3>';
    echo '<div class="muted">W
