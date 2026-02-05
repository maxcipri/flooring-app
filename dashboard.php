<?php
declare(strict_types=1);

// Allow Shopify Admin embedding
header_remove("Content-Security-Policy");
header_remove("X-Frame-Options");
header("Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com", true);

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
    ?>
    <!DOCTYPE html>
    <html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Flooring App - Live Mode</title>
        <style>
            body { font-family: Arial; padding: 24px; }
            .box { border: 1px solid #ddd; padding: 16px; border-radius: 8px; margin-bottom: 16px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
            .muted { color: #666; }
        </style>
    </head>
    <body>
        <div class="box">
            <h2 style="margin:0 0 8px 0;">Live mode (DB disabled)</h2>
            <div class="muted">Shop: <?= htmlspecialchars($shop) ?></div>

            <?php if ($count !== null): ?>
                <div style="margin-top:8px;">Products in store: <strong><?= (int)$count ?></strong></div>
            <?php else: ?>
                <div style="margin-top:8px;" class="muted">Could not read product count.</div>
            <?php endif; ?>

            <div style="margin-top:12px;" class="muted">DB features are temporarily disabled.</div>

            <?php if ($error): ?>
                <div style="margin-top:12px;" class="muted">API error: <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>

        <div class="box">
            <h3 style="margin:0 0 12px 0;">First 20 products (from Shopify)</h3>

            <?php if (empty($products)): ?>
                <div class="muted">No products returned (or API call failed).</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Handle</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($p['id'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($p['title'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($p['handle'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($p['status'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="box">
            <h3 style="margin:0 0 8px 0;">DB later</h3>
            <div class="muted">When you want DB back, set MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT in Render.</div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If DB is available, keep app alive for now
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
    <title>Flooring App</title>
    <style>body{font-family:Arial;padding:24px}.muted{color:#666}</style>
</head>
<body>
    <h2>DB connected</h2>
    <div class="muted">Spune-mi și îți reactivez dashboard-ul original, cu DB.</div>
</body>
</html>
