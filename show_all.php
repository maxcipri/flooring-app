<?php
header("Content-Type: application/json");
include_once 'shopify.php';
include_once 'keys.php';
include_once 'connection.php';

$sc = new ShopifyClient($shop, $token, $api_key, $secret);
$update = '';
$count = 0; 

function printProducts($products){
    global $count;
    foreach ($products as $key => $value) {
        echo $count++;
        echo " ";
        echo $value->id;
        echo "\n";
    }
}

// Updated to use a stable API version
$url = "https://".$shop."/admin/api/2024-01/products.json?limit=250";

do{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "X-Shopify-Access-Token: ".$token.""
    ));
    
    $headers = array(); // Initialize headers array
    curl_setopt($ch, CURLOPT_HEADERFUNCTION,
        function($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2)
                return $len;

            $headers[strtolower(trim($header[0]))][] = trim($header[1]);

            return $len;
        }
    );

    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $header_size);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    $data = json_decode($body);

    if(isset($data->errors)){
        echo "Errors:\n";
        print_r($data->errors);
        break;
    }
    else {
        $products = $data->products;
        printProducts($products);
        
        // Check if there's a 'link' header for pagination
        if (isset($headers['link']) && is_array($headers['link'])) {
            $links = explode(',', $headers['link'][0]);
            if(sizeof($links)){
                if(strpos(end($links), 'next') > -1){
                    $link = explode('>', end($links));
                    $url = str_replace('<', '', $link[0]);   
                } else {
                    $url = false;
                }
            } else {
                $url = false;
            }
        } else {
            $url = false;
        }
    }
} while($url);
