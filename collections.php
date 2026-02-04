<?php
include_once 'shopify.php';
include_once 'keys.php';
include_once 'connection.php';

$sc = new ShopifyClient($shop, $token, $api_key, $secret);

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$index = isset($_GET['index']) ? intval($_GET['index']) : 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Collections Update</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        a { margin: 10px; padding: 10px; background: #5563c1; color: white; text-decoration: none; border-radius: 3px; }
        a:hover { background: #6371c7; }
    </style>
</head>
<body>
    <a href="?index=<?php echo $index; ?>&page=<?php echo $page+1; ?>">Next Page</a>
    <a href="?index=<?php echo $index+10; ?>&page=<?php echo $page; ?>">Next Index</a>
    <br><br>
<?php

try {
    // Updated to use a stable API version
    $collection = $sc->call("GET", "/admin/api/2024-01/smart_collections.json?limit=1&page=".$page);
    
    if (isset($collection[0]['title'])) {
        $collection_title = mysqli_real_escape_string($conn, $collection[0]['title']);
        $collection_id = $collection[0]['id'];
        
        echo "<strong>Collection: " . htmlspecialchars($collection_title) . "</strong><br>";
        
        try {
            for ($i = $index; $i <= $index + 9; $i++) {
                // Updated to use a stable API version
                $x = $sc->call("GET", "/admin/api/2024-01/products.json?limit=250&page=".$i."&collection_id=".$collection_id);
                
                if (is_array($x) && count($x) > 0) {
                    echo "Page $i: " . count($x) . " products<br>";
                    
                    foreach ($x as $key => $value) {
                        $product_id = intval($value['id']);
                        
                        // Use prepared statement
                        $stmt = $conn->prepare("UPDATE Products SET collections = ? WHERE Product_Id = ?");
                        if ($stmt) {
                            $stmt->bind_param("si", $collection_title, $product_id);
                            if (!$stmt->execute()) {
                                echo "Error updating product $product_id: " . $stmt->error . "<br>";
                            }
                            $stmt->close();
                        }
                    }
                }
            }
            
            echo "<br><strong>Done processing collection!</strong>";
            
        } catch (ShopifyApiException $e) {
            echo "<pre>";
            print_r($e);
            echo "</pre>";
        }
    } else {
        echo "No collection found at page $page";
    }
    
} catch (ShopifyApiException $e) {
    echo "<pre>";
    print_r($e);
    echo "</pre>";
}
?>
</body>
</html>
