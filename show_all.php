<?php
header("Content-Type: application/json");

include_once 'shopify.php';
include_once 'keys.php';
include_once 'connection.php';

$sc = new ShopifyClient($shop, $token, $api_key, $secret);
$count = 0;

function printProducts($products)
{
    global $count;
    foreach ($products as $value) {
        echo $count++;
        echo " ";
        echo $value->id;
        echo "\n";
    }
}

// Updated to use a stable API version
$url = "https://" . $shop . "/admin/api/2024-01/products.json?limit=250";

do {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Shopify-Access-Token: " . $token
    ]);

    $headers = [];
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers) {
        $len = strlen($header);
        $headerParts = explode(':', $header, 2);
        if (count($headerParts) < 2) return $len;
        $headers[strtolower(trim($headerParts[0]))][] = trim($headerParts[1]);
        return $len;
    });

    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $header_size);

    curl_close($ch);

    $data = json_decode($body);

    if (isset($data->errors)) {
        echo "Errors:\n";
        print_r($data->errors);
        break;
    }

    $products = $data->products ?? [];
    printProducts($products);

    // Robust pagination: explicitly find rel="next"
    $nextUrl = null;
    if (!empty($headers['link'][0])) {
        foreach (explode(',', $headers['link'][0]) as $part) {
            if (strpos($part, 'rel="next"') !== false) {
                if (preg_match('/<([^>]+)>/', $part, $m)) {
                    $nextUrl = $m[1];
                    break;
                }
            }
        }
    }

    $url = $nextUrl ?: false;

} while ($url);
