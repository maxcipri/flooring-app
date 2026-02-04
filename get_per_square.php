<?php
include_once 'connection.php';

// Expecting POST[products] as an array of product ids
$products_in = isset($_POST['products']) && is_array($_POST['products']) ? $_POST['products'] : [];
$product_ids = array_values(array_filter(array_map('intval', $products_in), function ($v) { return $v > 0; }));

if (count($product_ids) === 0) {
    echo json_encode(['status' => 'fail']);
    exit;
}

$placeholders = implode(',', array_fill(0, count($product_ids), '?'));
$select = "SELECT Product_Id, per_square_price, hide_price, discounted_price FROM Products WHERE Product_Id IN ($placeholders)";

// $select = "SELECT Product_Id,per_square_price from Products WHERE collections='".$collection_title."'";
$stmt = mysqli_prepare($conn, $select);
if (!$stmt) {
    echo json_encode(['status' => 'fail']);
    exit;
}

$types = str_repeat('i', count($product_ids));
mysqli_stmt_bind_param($stmt, $types, ...$product_ids);
mysqli_stmt_execute($stmt);
$select_rs = mysqli_stmt_get_result($stmt);

if($select_rs && mysqli_num_rows($select_rs)>0){
    $price_array = array();
    $hide_price = array();
    $discounted_price = array();
    while($row = mysqli_fetch_assoc($select_rs)){
            $price_array[$row['Product_Id']]=$row['per_square_price'];
            $hide_price[$row['Product_Id']] = $row['hide_price'];
            
            if($row['discounted_price'] === '' || $row['discounted_price'] === null){
                $row['discounted_price'] = 0;
            }
            
            $discounted_price[$row['Product_Id']] = $row['discounted_price'];
    }
    
    $response = array(
            "status"=>"success",
            "price"=>$price_array,
            "hide_price"=>$hide_price,
            "discounted_price"=>$discounted_price
        );
    
    
}
else{
    $response=array(
        'status'=>"fail"
        );
}
echo  json_encode($response);


?>