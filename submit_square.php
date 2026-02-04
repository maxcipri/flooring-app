<?php
if(isset($_POST['square_submit'])){
    include_once 'connection.php';
    include_once 'shopify.php';
    include_once 'keys.php';

    // Sanitize all inputs
    $persquare_foot = floatval($_POST['per_square_foot']);
    $product_type = mysqli_real_escape_string($conn, $_POST['product_type']);
    $square_foot_total = floatval($_POST['square_foot_total']);
    $product_id = intval($_POST['product_id']);
    $price = $persquare_foot * $square_foot_total;
    $weight = floatval($_POST['weight']);
    $count_timer = mysqli_real_escape_string($conn, $_POST['count_down']);
    $discounted_price = floatval($_POST['discounted_price']);
    $special_order = mysqli_real_escape_string($conn, $_POST['special_order']);
    $shipping_1_3 = mysqli_real_escape_string($conn, $_POST['shipping_1_3']);
    $shipping_3_7 = mysqli_real_escape_string($conn, $_POST['shipping_3_7']);
    $shipping_7_1 = mysqli_real_escape_string($conn, $_POST['shipping_7_1']);
    
    if($discounted_price <= 0){
        $discounted_price = 0;
    }
    $hide_price = mysqli_real_escape_string($conn, $_POST['hide_price']);
    $quantity = intval($_POST['quantity']);
    $selected_collection = mysqli_real_escape_string($conn, $_POST['selected_collection']);
    
    $sc = new ShopifyClient($shop, $token, $api_key, $secret);

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
    
    try{
        // Updated to use a stable API version (2024-01 is a recent stable version)
        $x = $sc->call("PUT", "/admin/api/2024-01/products/".$product_id.".json", $data);
        $price = $x['variants'][0]['price'];
 
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("UPDATE Products SET Product_type=?, Square_ft=?, Price=?, per_square_price=?, weight=?, countdown=?, discounted_price=?, hide_price=?, quantity=?, accessories_collection=?, shipping_1_3=?, shipping_3_7=?, shipping_7_1=?, special_order=? WHERE Product_Id=?");
        
        $stmt->bind_param("sddddsdsissssi", 
            $product_type, 
            $square_foot_total, 
            $price, 
            $persquare_foot, 
            $weight, 
            $count_timer, 
            $discounted_price, 
            $hide_price, 
            $quantity, 
            $selected_collection, 
            $shipping_1_3, 
            $shipping_3_7, 
            $shipping_7_1, 
            $special_order, 
            $product_id
        );
        
        if(!$stmt->execute()){
            echo "fail";
            echo $stmt->error;
        } else {
            echo "Success";
        }
        $stmt->close();
        
    }
    catch(ShopifyApiException $e){
        echo "<pre>";
        print_r($e);
        echo "</pre>";
    }
}
