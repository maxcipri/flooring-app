<?php
include_once 'shopify.php';
include_once 'keys.php';
include_once 'connection.php';

$sc = new ShopifyClient($shop, $token, $api_key, $secret);

$get_data = file_get_contents('php://input'); 
$file_name = time();

// Save input for debugging
file_put_contents($file_name."-input", $get_data);

$json_encode = json_decode($get_data, true);

if (!$json_encode || !isset($json_encode['rate']['items'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$items = $json_encode['rate']['items'];
$final_total = 0;

foreach ($items as $key => $value) {
    $variant_id = isset($value['variant_id']) ? intval($value['variant_id']) : 0;
    $product_id = isset($value['product_id']) ? intval($value['product_id']) : 0;
    $properties = isset($value['properties']) ? $value['properties'] : array();
    
    if (isset($properties['Square ft']) && $product_id > 0) {
        $val_Sq = floatval($properties['Square ft']);
        
        if ($val_Sq >= 1 && $val_Sq <= 300) {
            $column = 'shipping_1_3';
        } else if ($val_Sq >= 301 && $val_Sq <= 700) {
            $column = 'shipping_3_7';
        } else if ($val_Sq >= 701) {
            $column = 'shipping_7_1';
        } else {
            continue;
        }
        
        // Use prepared statement
        $stmt = $conn->prepare("SELECT {$column} FROM Products WHERE Product_Id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $fetch = $result->fetch_assoc();
                $ship_price = floatval($fetch[$column]);
                $final_total += $ship_price;
            }
            $stmt->close();
        }
    }
}

$total_price = intval($final_total * 100);

$response = array(
    "rates" => array(
        array(
            "service_name" => "Ground Freight",
            "service_code" => "expetied",
            "total_price" => strval($total_price),
            "description" => "2-5 Business days",
            "currency" => "USD"
        )
    )
);

header('Content-Type: application/json');
echo json_encode($response);
