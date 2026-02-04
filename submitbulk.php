<?php
if(isset($_POST['product_id'])){
    include_once 'connection.php';
    include_once 'shopify.php';
    include_once 'keys.php';
    
    $product_id = $_POST['product_id'];
    
    $sc = new ShopifyClient($shop, $token, $api_key, $secret);
    
    try{
        foreach ($product_id as $key => $id) {
            $id = intval($id); // Sanitize product ID
            
            $persquare_foot = floatval($_POST[$id.'-per_sq_ft']);
            $product_type = mysqli_real_escape_string($conn, $_POST[$id.'-type']);
            $square_foot_total = floatval($_POST[$id.'-total_sq_ft']);
            $price = $persquare_foot * $square_foot_total;
            $weight = floatval($_POST[$id.'-weight']);
            $quantity = intval($_POST[$id.'-quantity']);
            $hide_price = mysqli_real_escape_string($conn, $_POST[$id.'-hide_price']);
            $shipping_1_3 = mysqli_real_escape_string($conn, $_POST[$id.'-shipping_1_3']);
            $shipping_3_7 = mysqli_real_escape_string($conn, $_POST[$id.'-shipping_3_7']);
            $shipping_7_1 = mysqli_real_escape_string($conn, $_POST[$id.'-shipping_7_1']);
            $special_order = mysqli_real_escape_string($conn, $_POST[$id.'-special_order']);
            
            $data['product'] = array(
                'variants' => array(
                    array(
                        'price' => $price,
                        'weight' => $weight,
                        'weight_unit' => "lb",
                        'inventory_quantity' => $quantity
                    )
                )
            );
            
            // Updated to use a stable API version
            $x = $sc->call("PUT", "/admin/api/2024-01/products/".$id.".json", $data);
        
            $price = $x['variants'][0]['price'];

            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("UPDATE Products SET Product_type=?, Square_ft=?, Price=?, per_square_price=?, weight=?, quantity=?, hide_price=?, shipping_1_3=?, shipping_3_7=?, shipping_7_1=?, special_order=? WHERE Product_Id=?");
            
            $stmt->bind_param("sddddissssi", 
                $product_type, 
                $square_foot_total, 
                $price, 
                $persquare_foot, 
                $weight, 
                $quantity, 
                $hide_price, 
                $shipping_1_3, 
                $shipping_3_7, 
                $shipping_7_1, 
                $special_order, 
                $id
            );
            
            if(!$stmt->execute()){
                echo "fail - Product ID: $id<br>";
                echo $stmt->error . "<br>";
            } else {
                echo "Success - Product ID: $id<br>";
            }
            $stmt->close();
        }
        
    }
    catch(ShopifyApiException $e){
        echo "<pre>";
        print_r($e);
        echo "</pre>";
    }
}
