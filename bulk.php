<?php
if (!isset($_GET['product_id']) || !is_array($_GET['product_id']) || count($_GET['product_id']) == 0) {
    die('No products selected');
}

include_once 'connection.php';

// Helper function for HTML escaping
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$products = [];
$ids = array_map('intval', $_GET['product_id']); // Sanitize all IDs

foreach ($ids as $id) {
    $stmt = $conn->prepare("SELECT * FROM Products WHERE Product_Id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $fetch = $result->fetch_assoc();
            
            $products[$id] = array(
                'image' => $fetch['Product_image'],
                'title' => $fetch['Product_title'],
                'product_type' => $fetch['Product_type'],
                'per_sq_ft' => $fetch['per_square_price'],
                'total_sq_ft' => $fetch['Square_ft'],
                'weight' => $fetch['weight'],
                'quantity' => $fetch['quantity'],
                'hide_price' => $fetch['hide_price'],
                'shipping_1_3' => $fetch['shipping_1_3'],
                'shipping_3_7' => $fetch['shipping_3_7'],
                'shipping_7_1' => $fetch['shipping_7_1'],
                'special_order' => $fetch['special_order']
            );
        }
        $stmt->close();
    }
}

if (count($products) == 0) {
    die('No products found');
}
?>   
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bulk Edit Products</title>
    <link href="//fonts.googleapis.com/css?family=Poppins:300italic,400italic,500italic,600italic,700italic,800italic,100,200,300,400,500,600,700,800&amp;subset=cyrillic-ext,greek-ext,latin,latin-ext,cyrillic,greek,vietnamese" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <style type="text/css">
        * {
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body { font-family: sans-serif; padding: 20px; }
        .edit_product_bulk {
            width: 100%;
            display: inline-block;
            border: 1px solid #ccc;
        }
        .edit-product-row {
            display: flex;
            width: 100%;
            border-bottom: 1px solid #ccc;
            padding: 10px 0px;
        }
        .edit-title h4 {
            margin: 0;
            padding: 0;
            font-size: 14px;
            font-weight: normal;
        }
        .edit-image img {
            width: 80%;
        }
        .edit-image {
            width: 6%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .edit-title {
            width: 24%;
            display: flex;
            align-items: center;
            padding-left: 20px;
        }
        .edit-type, .edit-per_sq_ft, .edit-square_total, .edit-square_weight, .edit-square_quantity, .edit-shipping, .special_order {
            padding: 5px 0;
            width: 17.5%;
            display: flex;
            align-items: center;
        }
        .edit-product-row input, .edit-product-row select {
            padding: 10px 10px;
            width: 90% !important;
        }
        .edit-product-row.headrow h4 {
            font-size: 15px;
            font-weight: normal;
            margin: 0;
            padding: 0;
        }
        .submit_field {
            position: relative;
            width: 200px !important;
            float: right;
            margin: 20px 60px;
            height: 40px;
            background: black;
            color: white;
            font-size: 18px;
            cursor: pointer;
            border: none;
        }
        @keyframes rotateAnimation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading-btn {
            text-indent: -1400px;
            overflow: hidden;
        }
        .loading-btn:after {
            font-size: 0;
            width: 15px;
            height: 15px;
            border-radius: 15px;
            padding: 0;
            border: 2px solid #ffffff;
            border-bottom: 2px solid rgba(255, 255, 255, 0);
            border-left: 2px solid rgba(255, 255, 255, 0);
            background-color: transparent !important;
            animation: rotateAnimation infinite linear 0.5s;
            position: absolute;
            left: 0;
            right: 0;
            top: calc(50% - 7.5px);
            margin: 0 auto;
            opacity: 1;
            transition: opacity 300ms ease;
            content: "";
        }
    </style>
</head>
<body>
<form action="submitbulk.php" class="submitbulk" method="post">
    <button type="submit" name="square_submit" class="submit_field" value="submit">Submit</button>

    <div class="edit_product_bulk">
        <div class="edit-product-row headrow">
            <div class="edit-image"><h4>Image</h4></div>
            <div class="edit-title"><h4>Title</h4></div>
            <div class="edit-type"><h4>Type</h4></div>
            <div class="edit-per_sq_ft"><h4>Rate per sqft</h4></div>
            <div class="edit-square_total"><h4>Per box</h4></div>
            <div class="edit-square_weight"><h4>Weight</h4></div>
            <div class="edit-square_quantity"><h4>Quantity</h4></div>
            <div class="edit-square_quantity"><h4>Hide Price</h4></div>
            <div class="edit-square_quantity"><h4>Ship 1-300</h4></div>
            <div class="edit-square_quantity"><h4>Ship 301-700</h4></div>
            <div class="edit-square_quantity"><h4>Ship 701-1000</h4></div>
            <div class="special_order"><h4>Special Order</h4></div>
        </div>
        <?php
        foreach ($products as $key => $product) {
            ?>
            <input type="hidden" name="product_id[]" value="<?php echo h($key); ?>">
            <div class="edit-product-row">
                <div class="edit-image"><img src="<?php echo h($product['image']); ?>" alt="Product"></div>
                <div class="edit-title"><h4><?php echo h($product['title']); ?></h4></div>
                <div class="edit-type">
                    <select class='select_product_type' name="<?php echo h($key); ?>-type">
                        <option <?php if($product['product_type'] == 'Box') echo 'selected'; ?> value='Box'>BOX</option>    
                        <option <?php if($product['product_type'] == 'Lineal') echo 'selected'; ?> value='Lineal'>Lineal</option>  
                        <option <?php if($product['product_type'] == 'Roll') echo 'selected'; ?> value='Roll'>Roll</option>  
                        <option <?php if($product['product_type'] == 'Piece') echo 'selected'; ?> value='Piece'>Piece</option>  
                    </select>
                </div>
                <div class="edit-per_sq_ft"><input type="text" name="<?php echo h($key); ?>-per_sq_ft" value="<?php echo h($product['per_sq_ft']); ?>"></div>
                <div class="edit-square_total"><input type="text" name="<?php echo h($key); ?>-total_sq_ft" value="<?php echo h($product['total_sq_ft']); ?>"></div>
                <div class="edit-square_weight"><input type="text" name="<?php echo h($key); ?>-weight" value="<?php echo h($product['weight']); ?>"></div>
                <div class="edit-square_quantity"><input type="text" name="<?php echo h($key); ?>-quantity" value="<?php echo h($product['quantity']); ?>"></div>
                <div class="edit-square_quantity">
                    <input type="checkbox" name="<?php echo h($key); ?>-hide_price" class='hide_price' <?php if($product['hide_price'] == 'on'){ ?> checked <?php } ?> data-toggle="toggle">  
                </div>
                <div class='edit-shipping'>
                    <input type="text" name="<?php echo h($key); ?>-shipping_1_3" value="<?php echo h($product['shipping_1_3']); ?>">
                </div>	
                <div class='edit-shipping'>
                    <input type="text" name="<?php echo h($key); ?>-shipping_3_7" value="<?php echo h($product['shipping_3_7']); ?>">
                </div>
                <div class='edit-shipping'>
                    <input type="text" name="<?php echo h($key); ?>-shipping_7_1" value="<?php echo h($product['shipping_7_1']); ?>">
                </div>
                <div class='edit-shipping'>
                    <input type="text" name="<?php echo h($key); ?>-special_order" value="<?php echo h($product['special_order']); ?>">
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</form>

<?php
$filters = [];
if (isset($_GET['filters'])) {
    $filter = $_GET['filters'];
    $filter = explode('&', $filter);
    foreach ($filter as $key => $value) {
        $thisFilter = explode('=', $value);
        if (count($thisFilter) == 2) {
            $filters[$thisFilter[0]] = $thisFilter[1] == 'undefined' ? '' : $thisFilter[1];
        }
    }
}
?>

<form style="display: none;" class="filtersBack" action="dashboard.php" method="get">
    <?php if(sizeof($filters)){ ?>
        <input type="hidden" name="search" value="<?php echo h($filters['input_search'] ?? ''); ?>">
        <input type="hidden" name="type" value="<?php echo h($filters['input_type'] ?? ''); ?>">
        <input type="hidden" name="tag" value="<?php echo h($filters['input_tag'] ?? ''); ?>">
        <input type="hidden" name="vendor" value="<?php echo h($filters['input_vendor'] ?? ''); ?>">
        <input type="hidden" name="collection" value="<?php echo h($filters['input_collection'] ?? ''); ?>">
    <?php } ?>
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
<script>
$(document).on("click", ".submit_field", function(e) {
    e.preventDefault();
    $(this).addClass('loading-btn');
    
    var data = $('.submitbulk').serialize();
    
    $.ajax({
        url: 'submitbulk.php',
        type: 'POST',
        data: data,
        dataType: 'JSON',
        success: function(res) {
            console.log(res);
            $('.submit_field').removeClass('loading-btn');
            $('.filtersBack').submit();
        },
        error: function(rr) {
            console.log(rr);                    
            $('.submit_field').removeClass('loading-btn');
            $('.filtersBack').submit();
        }
    });
});
</script>
</body>
</html>
