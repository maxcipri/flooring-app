<?php
include_once 'shopify.php';
include_once 'keys.php';

$sc = new ShopifyClient($shop, $token, $api_key, $secret);

$data['carrier_service'] = array(
    "name" => "Shipping Rate Provider Carrier",
    "callback_url" => "https://factoryflooringliquidators.biz/Shopify/flooring/carrier_ship.php",
    "service_discovery" => true,
    "carrier_service_type" => "api"
);

try{
    // Updated to use a stable API version
    $x = $sc->call("POST", "/admin/api/2024-01/carrier_services.json", $data);

    echo "<pre>";
    print_r($x);
    echo "</pre>";
}
catch(ShopifyApiException $e){
    echo "<pre>";
    print_r($e);
    echo "</pre>";
}
