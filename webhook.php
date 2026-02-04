<?php
header('Content-Type: application/json');

$headers = function_exists('getallheaders') ? getallheaders() : [];
$shopDomain = $headers['X-Shopify-Shop-Domain'] ?? '';
$hmacHeader = $headers['X-Shopify-Hmac-Sha256'] ?? '';

$raw = file_get_contents('php://input');
if ($raw === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unable to read request body']);
    exit;
}

// Log raw payload for debugging (append, jsonl)
@file_put_contents(
    __DIR__ . '/webhook_logs.jsonl',
    json_encode(['ts' => gmdate('c'), 'shop' => $shopDomain, 'bytes' => strlen($raw)]) . "\n",
    FILE_APPEND
);

// Optional: verify webhook signature if secret is available.
// Define SHOPIFY_SHARED_SECRET (constant) or $SHOPIFY_SHARED_SECRET in connection.php.
$secret = defined('SHOPIFY_SHARED_SECRET') ? SHOPIFY_SHARED_SECRET : null;
if ($secret === null && isset($SHOPIFY_SHARED_SECRET) && is_string($SHOPIFY_SHARED_SECRET)) {
    $secret = $SHOPIFY_SHARED_SECRET;
}
if (is_string($secret) && $secret !== '' && $hmacHeader !== '') {
    $calculated = base64_encode(hash_hmac('sha256', $raw, $secret, true));
    if (!hash_equals($calculated, $hmacHeader)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid webhook signature']);
        exit;
    }
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

include_once 'connection.php';

$productId = isset($data['id']) ? (int)$data['id'] : 0;
if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid product id']);
    exit;
}

$title = isset($data['title']) ? (string)$data['title'] : '';
$vendor = isset($data['vendor']) ? (string)$data['vendor'] : '';
$productType = isset($data['product_type']) ? (string)$data['product_type'] : '';
$createdAt = isset($data['created_at']) ? (string)$data['created_at'] : '';
$updatedAt = isset($data['updated_at']) ? (string)$data['updated_at'] : '';
$status = isset($data['status']) ? (string)$data['status'] : '';
$tags = isset($data['tags']) ? (string)$data['tags'] : '';

$image = '';
if (isset($data['image']['src']) && is_string($data['image']['src'])) {
    $image = $data['image']['src'];
} elseif (isset($data['images']) && is_array($data['images']) && isset($data['images'][0]['src'])) {
    $image = (string)$data['images'][0]['src'];
}

$collections = '';
if (isset($data['collections']) && is_array($data['collections'])) {
    $titles = [];
    foreach ($data['collections'] as $c) {
        if (is_array($c) && isset($c['title']) && is_string($c['title']) && $c['title'] !== '') {
            $titles[] = $c['title'];
        }
    }
    $collections = implode(', ', $titles);
}

function getProductSize(array $variants): string {
    foreach ($variants as $variant) {
        if (!is_array($variant)) {
            continue;
        }
        $title = isset($variant['title']) ? (string)$variant['title'] : '';
        if ($title === '') {
            continue;
        }
        // Common patterns: 7 1/2" x 48" or 7.5" x 48"
        if (preg_match('/\b\d+(?:\.\d+)?(?:\s+\d+\/\d+)?\"\s*x\s*\d+(?:\.\d+)?(?:\s+\d+\/\d+)?\"\b/i', $title, $matches)) {
            return $matches[0];
        }
    }
    return '';
}

$size = '';
if (isset($data['variants']) && is_array($data['variants'])) {
    $size = getProductSize($data['variants']);
}

// Check if product exists
$exists = false;
$check = $conn->prepare('SELECT 1 FROM Products WHERE Product_Id = ? LIMIT 1');
if ($check) {
    $check->bind_param('i', $productId);
    $check->execute();
    $check->store_result();
    $exists = $check->num_rows > 0;
    $check->close();
}

if ($exists) {
    $stmt = $conn->prepare('UPDATE Products SET title = ?, collections = ?, image = ?, vendor = ?, product_type = ?, created_at = ?, updated_at = ?, status = ?, size = ?, tags = ? WHERE Product_Id = ?');
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
        exit;
    }
    $stmt->bind_param('ssssssssssi', $title, $collections, $image, $vendor, $productType, $createdAt, $updatedAt, $status, $size, $tags, $productId);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Update failed']);
        exit;
    }

    echo json_encode(['success' => true, 'action' => 'updated', 'id' => $productId]);
    exit;
}

// Insert new product (keep existing numeric defaults)
$perSquarePrice = 0;
$hidePrice = 0;
$discountedPrice = 0;

$stmt = $conn->prepare('INSERT INTO Products (Product_Id, title, collections, per_square_price, hide_price, discounted_price, image, vendor, product_type, created_at, updated_at, status, size, tags) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
    exit;
}

$stmt->bind_param('issiiissssssss', $productId, $title, $collections, $perSquarePrice, $hidePrice, $discountedPrice, $image, $vendor, $productType, $createdAt, $updatedAt, $status, $size, $tags);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['success' => true, 'action' => 'inserted', 'id' => $productId]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Insert failed']);
}
