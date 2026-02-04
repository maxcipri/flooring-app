<?php
// Router for Railway's PHP server
// This ensures all requests get proper headers

// Set CSP headers for ALL requests
header_remove("Content-Security-Policy");
header_remove("X-Frame-Options");
header("Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com", true);
header("X-Content-Type-Options: nosniff", true);

// Get the requested file
$request_uri = $_SERVER['REQUEST_URI'];
$file_path = __DIR__ . parse_url($request_uri, PHP_URL_PATH);

// If it's a directory, look for index.php
if (is_dir($file_path)) {
    $file_path = rtrim($file_path, '/') . '/index.php';
}

// If file exists and is PHP, execute it
if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'php') {
    require $file_path;
    return;
}

// If it's a static file, let PHP server handle it
return false;
