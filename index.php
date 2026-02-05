<?php
// --- Session cookie settings required for Shopify Admin iframe (SameSite=None; Secure) ---
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None',
    ]);
} else {
    // Fallback for old PHP
    session_set_cookie_params(0, '/; samesite=None', '', true, true);
}

session_start();

// CRITICAL: CSP headers for Shopify embedding
header_remove("Content-Security-Policy");
header_remove("X-Frame-Options");
header("Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com", true);

require_once 'keys.php';

// Log all access
@file_put_contents(
    __DIR__ . "/_access.log",
    date("Y-m-d H:i:s") . " | " . ($_SERVER["REQUEST_URI"] ?? "") . " | shop=" . ($_GET["shop"] ?? "none") . " | host=" . ($_GET["host"] ?? "none") . "\n",
    FILE_APPEND
);

// Check if called from Shopify with required parameters
if (!empty($_GET["shop"]) && !empty($_GET["host"])) {
    $_SESSION['shop'] = $_GET['shop'];
    $_SESSION['host'] = $_GET['host'];

    // Check if already authenticated
    if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
        // Already have token, go to dashboard
        header("Location: dashboard.php?token=" . $_SESSION['token'] . "&host=" . $_GET['host'] . "&shop=" . $_GET['shop']);
        exit;
    }

    // Not authenticated, go to login
    require __DIR__ . '/login.php';
    exit;
}

// Accessed without Shopify parameters
?>
<!DOCTYPE html>
<html>
<head>
    <title>Flooring Magic App</title>
</head>
<body style="font-family: Arial; padding: 40px; text-align: center;">
    <h1>Flooring Magic App</h1>
    <p>This app must be opened from your Shopify Admin.</p>
    <p>Go to: <strong>Apps → Flooring Magic App</strong></p>
</body>
</html>
