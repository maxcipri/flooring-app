<?php
// Session must be started before headers
if (!session_id()) {
    session_start();
}

// CRITICAL: CSP headers for Shopify embedding
header_remove("Content-Security-Policy");
header_remove("X-Frame-Options");
header("Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com", true);

require_once 'keys.php';
require_once 'shopify.php';

define('SHOPIFY_API_KEY', $api_key);
define('SHOPIFY_SECRET', $secret);
define('SHOPIFY_SCOPE', 'read_products,write_products,write_themes,write_shipping');

// OAuth callback - Shopify sent us back with a code
if (isset($_GET['code']) && isset($_GET['shop'])) {
    $shopifyClient = new ShopifyClient($_GET['shop'], "", SHOPIFY_API_KEY, SHOPIFY_SECRET);
    
    try {
        $access_token = $shopifyClient->getAccessToken($_GET['code']);
        
        if (!empty($access_token)) {
            $_SESSION['token'] = $access_token;
            $_SESSION['shop'] = $_GET['shop'];
            
            // Success! Redirect to dashboard
            $redirect = "dashboard.php?token=" . $access_token . "&host=" . ($_REQUEST['host'] ?? '') . "&shop=" . $_GET['shop'];
            header("Location: " . $redirect);
            exit;
        } else {
            die("Failed to get access token from Shopify");
        }
    } catch (Exception $e) {
        die("OAuth Error: " . $e->getMessage());
    }
}

// Initial OAuth request - redirect to Shopify for authorization
if (isset($_GET['shop'])) {
    $shop = $_GET['shop'];
    $shopifyClient = new ShopifyClient($shop, "", SHOPIFY_API_KEY, SHOPIFY_SECRET);
    
    // Build redirect URL
    $redirect_url = "https://" . $_SERVER['HTTP_HOST'] . "/Shopify/flooring_new/login.php";
    
    // Redirect to Shopify OAuth page
    $auth_url = $shopifyClient->getAuthorizeUrl(SHOPIFY_SCOPE, $redirect_url);
    header("Location: " . $auth_url);
    exit;
}

// No shop parameter - show manual install form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Install Flooring Magic App</title>
    <style>
        body { font-family: Arial; padding: 40px; max-width: 500px; margin: 0 auto; }
        input[type="text"] { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { padding: 10px 20px; background: #5c6ac4; color: white; border: none; cursor: pointer; }
        button:hover { background: #4959bd; }
    </style>
</head>
<body>
    <h2>Install Flooring Magic App</h2>
    <form method="GET" action="">
        <label>Enter your shop domain:</label>
        <input type="text" name="shop" placeholder="your-store.myshopify.com" required>
        <button type="submit">Install App</button>
    </form>
</body>
</html>
