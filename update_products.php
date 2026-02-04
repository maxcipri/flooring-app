<?php

header("Content-Type: application/json");
include_once 'shopify.php';
include_once 'keys.php';
include_once 'connection.php';
$sc = new ShopifyClient($shop,$token,$api_key,$secret);

try{
//     for($i=111;$i<=120;$i++){
//     $x = $sc->call("GET","/admin/api/2019-04/products.json?limit=250&fields=id&page=".$i);
//     // echo $i;
//         foreach($x as $key => $value){
//          $handle= $value['handle'];
//             $product_id = $value['id'];
   
//  $update = "UPDATE Products SET Action='true' WHERE Product_Id='".$product_id."'";
//  $update_rs = mysqli_query($conn,$update);
        
            
//         }
    
//     }
    
    
}
catch(ShopifyApiException $e){
    // echo "<pre>";
    // print_r($e);
}

?>