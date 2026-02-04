<?php
include_once 'connection.php';
$limit=50;
if(isset($_POST['searching'])){
  if (isset($_POST["page"])) { 
  $page  = $_POST["page"]; 
} else { 
  $page=1; 
};
    $start_from = ($page-1) * $limit; 
		$title = $_POST['input_search'];
    $vendor = $_POST['input_vendor'];
    $type = $_POST['input_type'];
    $tag = $_POST['input_tag'];
    $collection = $_POST['input_collection'];
    $like = [];
    if($type != '' && $type != 'undefined'){
      $like[] = "type LIKE ('%".$type."%')";
    }
    if($vendor != '' && $vendor != 'undefined'){
      $like[] = "vendor LIKE ('%".$vendor."%')";
    }
    
      $like [] = "Product_title LIKE ('%".$title."%')";  
  
      
    if($tag != '' && $tag != 'undefined'){
      $like [] = "tags LIKE ('%".$tag."%')";
    }
    if($collection != '' && $collection != 'undefined'){
      $like [] = "collections LIKE ('%".$collection."%')";
    }
    $select = "SELECT * from Products WHERE ".implode(' and ', $like)." ORDER BY Id ASC LIMIT $start_from, $limit";
$select_rs = mysqli_query($conn,$select);
if(mysqli_num_rows($select_rs)>0){ 
    echo "";   ?>
    <div class='product_div'>
  <form action="bulk.php" class="bulk-form" method="get">
    <input type="hidden" value="input_search=<?php echo $title; ?>&input_type=<?php echo $type; ?>&input_vendor=<?php echo $vendor; ?>&input_tag=<?php echo $tag; ?>&input_collection=<?php echo $collection; ?>" name="filters" class="hidden-filters">
    <input type="submit" value="edit" class="btn">
    <?php    
    echo "<table class='products_table'>";    
    echo "<tr><th><label class='select-label select-all'><input type='checkbox' class='select-checkbox' value='all' ><span class='check'></span></label></th><th></th><th>Title</th><th>Box Price</th><th colspan='2'>Action</th></tr>";
    $id = 1;
    while($row = mysqli_fetch_assoc($select_rs)){
      $image = mysqli_real_escape_string($conn,$row['Product_image']);
      $title = mysqli_real_escape_string($conn,$row['Product_title']);
      $handle = mysqli_real_escape_string($conn,$row['Product_handle']);
      $price = mysqli_real_escape_string($conn,$row['Price']);
      $product_id = $row['Product_Id'];
      echo "<tr><td><label class='select-label select-individual'><input type='checkbox' class='select-checkbox' name='product_id[]' value='".$product_id."' name='vendor'><span class='check'></span></label></td><td><img src='".$image."' class='product_image'/></td><td><a  data-id='".$product_id."' class='base_title'>".$title."</a></td><td>$".$price."</td><td><a href='https://www.factoryflooringliquidators.com/products/".$handle."' target='_blank' class='btn'>View</a></td><td><a data-id='".$product_id."' href='edit_products.php?product_id=".$product_id."' class='edit_product btn'>Edit Product</a></td></tr>";  
      $id++;
    }
    echo "</table>";
    echo "</div>";
    ?>
  </form>
  </div>
 <?php
include_once 'pagination_function.php';
?>
<div align="center">
<ul class='pagination text-center index_pagination' id="pagination">
<?php

$quersy = "`Products` WHERE ".implode(' and ', $like);

echo pagination($conn,$quersy,"50",$page);
?>

<?php
}
  else{
  echo "<h4>There is no products.</h4>";
  }

}

?>