<?php
// CRITICAL: CSP headers MUST be first
header_remove("Content-Security-Policy");
header_remove("X-Frame-Options");
header("Content-Security-Policy: frame-ancestors https://*.myshopify.com https://admin.shopify.com", true);

include_once 'keys.php';
?>
<script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
<script type="text/javascript">
// 	ShopifyApp.init({
// 		apiKey: '<?php echo $api_key; ?>',
// 		shopOrigin:"https://factory-flooring-liquidators.myshopify.com",    
// 	});
// 	ShopifyApp.ready(function(){
// 		ShopifyApp.Bar.initialize({
// 			title:'Dashboard'

// 		});
// 	});
</script>
<?php
$page = (int) (!isset($_GET["page"]) ? 1 : $_GET["page"]);
$limit = 50;
$startpoint = ($page * $limit) - $limit;
echo "<input type='hidden' class='page_num' value='".$page."'/>";
include_once 'connection.php';

$types = [];
$vendors = [];
$collections = [];
$search = "SELECT DISTINCT type from Products";
$search_rs = mysqli_query($conn,$search);
if(mysqli_num_rows($search_rs) > 0){
	while($thisrow = mysqli_fetch_assoc($search_rs)){
		$types[] = mysqli_real_escape_string($conn,$thisrow['type']);
	}
}
$search = "SELECT DISTINCT vendor from Products";
$search_rs = mysqli_query($conn,$search);
if(mysqli_num_rows($search_rs) > 0){
	while($thisrow = mysqli_fetch_assoc($search_rs)){
		$vendors[] = mysqli_real_escape_string($conn,$thisrow['vendor']);
	}
}
$search = "SELECT DISTINCT collections from Products";
$search_rs = mysqli_query($conn,$search);
if(mysqli_num_rows($search_rs) > 0){
	while($thisrow = mysqli_fetch_assoc($search_rs)){
	    
	    $explode = explode(",",mysqli_real_escape_string($conn,$thisrow['collections']));
	    
	    if(sizeOf($explode)>=1){
	    
	    for($i=0;$i<sizeOf($explode);$i++){
	    
	    $collections[] = $explode[$i];
	        
	    }
	    }
	    else{
	    $collections[] = $explode[0];
	    }
	    
	//	$collections[] = mysqli_real_escape_string($conn,$thisrow['collections']//);
	}

    $collection_unqiue = array_unique($collections);
    $collections = $collection_unqiue;
}
$select = "SELECT * from Products LIMIT {$startpoint} , {$limit}";
$select_rs = mysqli_query($conn,$select);
// print_r($_GET);
if(mysqli_num_rows($select_rs) > 0){
	echo "<div class='base-product-sec'>";    echo "<div class='app_logo'><img src='https://factoryflooringliquidators.biz/Shopify/flooring/app_banner.jpg'></div>";

	?>
	<link href="//fonts.googleapis.com/css?family=Poppins:300italic,400italic,500italic,600italic,700italic,800italic,100,200,300,400,500,600,700,800&amp;subset=cyrillic-ext,greek-ext,latin,latin-ext,cyrillic,greek,vietnamese" rel="stylesheet" type="text/css">
	<div class='filter-wrapper'>
		<div class="search-wrapper"><input type='text' value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>" name='search' class='input_search' placeholder='Search'/></div>
		<div class="type-wrapper clickToAdd"><span>Type</span><span class="svg"><svg viewBox="0 0 20 20" class="v3ASA" focusable="false" aria-hidden="true"><path d="M5 8l5 5 5-5z" fill-rule="evenodd"></path></svg></span>
			<div class="dropdown-list">
				<ul>
					<?php foreach ($types as $key => $value) {
						if($value !=''){
							?>
							<li><label><input type="radio" class="search-radio" <?php if(isset($_GET['type']) && $_GET['type'] == $value) echo 'checked'; ?> value="<?php echo $value;?>" name="type"><span class="check"></span> <span><?php echo $value;?></span></label></li>
							<?	
						}
					}
					?>
				</ul>
			</div>
		</div>
		<div class="tag-wrapper clickToAdd"><span>Tag</span><span class="svg"><svg viewBox="0 0 20 20" class="v3ASA" focusable="false" aria-hidden="true"><path d="M5 8l5 5 5-5z" fill-rule="evenodd"></path></svg></span>
			<div class="dropdown-list">
				<div class="tag-input-wrapper">
					<input type="text" class="tag-input" name="tag" value="<?php if(isset($_GET['tag'])) echo $_GET['tag']; ?>">
				</div>
			</div>
		</div>
		<div class="vendor-wrapper clickToAdd"><span>Vendor</span><span class="svg"><svg viewBox="0 0 20 20" class="v3ASA" focusable="false" aria-hidden="true"><path d="M5 8l5 5 5-5z" fill-rule="evenodd"></path></svg></span>
			<div class="dropdown-list">
				<ul>
					<?php foreach ($vendors as $key => $value) {
						if($value !=''){
							?>
							<li><label><input type="radio" class="search-radio" <?php if(isset($_GET['vendor']) && $_GET['vendor'] == $value) echo 'checked'; ?> value="<?php echo $value;?>" name="vendor"><span class="check"></span> <span><?php echo $value;?></span></label></li>
							<?	
						}
					}
					?>
				</ul>
			</div>
		</div>
		<div class="collection-wrapper clickToAdd"><span>Collection</span><span class="svg"><svg viewBox="0 0 20 20" class="v3ASA" focusable="false" aria-hidden="true"><path d="M5 8l5 5 5-5z" fill-rule="evenodd"></path></svg></span>
		<div class="dropdown-list">
				<ul>
					<?php foreach ($collections as $key => $value) {
						if($value !=''){
							?>
							<li><label><input type="radio" class="search-radio" <?php if(isset($_GET['collection']) && $_GET['collection'] == $value) echo 'checked'; ?> value="<?php echo $value;?>" name="collection"><span class="check"></span> <span><?php echo $value;?></span></label></li>
							<?	
						}
					}
					?>
				</ul>
			</div>
		</div>
	</div> 
<div class='product_div'>
	<form action="bulk.php" class="bulk-form" method="get">
		<input type="hidden" value="" name="filters" class="hidden-filters">
		<input type="submit" value="Edit Bulk" class="btn">
		<?php    
		echo "";  	
		echo "<table class='products_table'>";    
		echo "<tr><th><label class='select-label select-all'><input type='checkbox' class='select-checkbox' value='all'><span class='check'></span></label></th><th></th><th>Title</th><th>Box Price</th><th colspan='2'>Action</th></tr>";
		$id = 1;
		while($row = mysqli_fetch_assoc($select_rs)){
			$image = mysqli_real_escape_string($conn,$row['Product_image']);
			$title = mysqli_real_escape_string($conn,$row['Product_title']);
			$handle = mysqli_real_escape_string($conn,$row['Product_handle']);
			$price = mysqli_real_escape_string($conn,$row['Price']);
			$product_id = $row['Product_Id'];
			echo "<tr><td><label class='select-label select-individual'><input name='product_id[]' type='checkbox' class='select-checkbox' value='".$product_id."' name='vendor'><span class='check'></span></label></td><td><img src='".$image."' class='product_image'/></td><td><a  data-id='".$product_id."' class='base_title'>".$title."</a></td><td>$".$price."</td><td><a href='https://www.factoryflooringliquidators.com/products/".$handle."' target='_blank' class='btn'>View</a></td><td><a data-id='".$product_id."' href='edit_products.php?product_id=".$product_id."' class='edit_product btn'>Edit Product</a></td></tr>";	
			$id++;
		}
		echo "</table>";
		echo "</div>";
		?>
	</form>
	</div>
	<?php
}

include_once 'pagination_function.php';
?>
<form action="edit_products.php" method="get" class="single-form">
	<input type="hidden" value="" name="filters" class="hidden-filters">
	<input type="hidden" value="" name="product_id" class="hidden-id">

</form>




<form style="display: none;" class="filtersBack" action="dashboard.php" method="get">

        <input type="hidden" name="search" value="">
        <input type="hidden" name="type" value="">
        <input type="hidden" name="tag" value="">
        <input type="hidden" name="vendor" value="">
        <input type="hidden" name="collection" value="">
        <input type='hidden' name='page' value=''>
        

</form>






<div align="center">
	<ul class='pagination text-center index_pagination' id="pagination">
		<?php
		echo pagination($conn,"Products","50",$page);
		?>




		<style>
			.bulk-form > .btn {
				border: 0;
				margin: 20px 10px;
				opacity: 0;
				transition: all .3s ease;
			}
			*{
				box-sizing: border-box;
				font-family: "Poppins", sans-serif;
			}
			.btn {
				text-decoration: none;
				color: #fff;
				background: linear-gradient(180deg,#6371c7,#5563c1);
				font-size: 14px;
				padding: 6px 12px;
				border-radius: 3px;
			}

			.filter-wrapper {
				display: flex;
				flex-wrap: wrap;
			}
			.svg svg {
				width: 20px;
				height: 20px;
			}
			.select-label .select-checkbox {
				display: none;
			}
			.select-label{
				display: inline-block;
				cursor: pointer;
			}
			.select-label .check {
				display: inline-block;
				width: 20px;
				height: 20px;
				margin: 0 10px;
				background: transparent;
				border: 2px solid #ccc;
				position: relative;
				border-radius: 3px;
			}

			.select-label .select-checkbox:checked+span {
				border-color: #5c6ac4;
			}
			.select-label .select-checkbox:checked+span:after {
				position: absolute;
				content: '';
				width: 12px;
				height: 12px;
				background: #5c6ac4;
				top: 2px;
				left: 2px;
			}
			span.svg {
				margin-left: 10px;
			}
			.search-wrapper {
				width: 60%;
				border: 1px solid #ccc;
				border-radius: 4px 0px 0px 4px;
				border-right: 0;
			}

			.search-wrapper input.input_search {
				width: 100%;
				padding: 10px 20px;
				border-radius: 4px 0px 0px 4px;
				border: 0;
				font-size: 15px;
			}
			.tag-wrapper .dropdown-list {
				overflow: hidden;
				width: 200px;
			}

			.tag-wrapper .dropdown-list .tag-input-wrapper {
				margin: 20px 0;
			}

			.tag-wrapper .dropdown-list .tag-input-wrapper .tag-input {
				width: 90%;
				padding: 6px 15px;
				font-size: 13px;
				border-radius: 3px;
				border: 1px solid #ccc;
			}
			.type-wrapper, .tag-wrapper, .vendor-wrapper, .collection-wrapper {
				width: 10%;
				/* height: 100%; */
				text-align: center;
				background: #fbfbfc;
				border: 1px solid #ccc;
				border-right: 0;
				display: flex;
				align-items: center;
				cursor: pointer;
				justify-content: center;
				position: relative;
			}
			.collection-wrapper {
				border-radius: 0px 4px 4px 0px;
				border-right: 1px solid #ccc;
			}
			.dropdown-list {
				position: absolute;
				max-height: 300px;
				overflow-y: scroll;
				background: #fbfbfc;
				top: 104%;
				width: 250px;
				left: 0;
				display: none;
				box-shadow: 0px 2px 8px 2px #0000002b;
				border-radius: 2px;
			}

			.dropdown-list ul {
				list-style: none;
				padding: 0;
				text-align: left;
			}

			.dropdown-list ul li {
				font-size: 13px;
				margin: 8px 0;
			}

			.dropdown-list ul li input {
				display: none;
			}
			.dropdown-list ul li label span {
				width: 80%;
			}
			.dropdown-list ul li input:checked+span {
				border-color: #5765c2;
			}

			.dropdown-list ul li input:checked+span::after {
				width: 7px;
				height: 7px;
				content: '';
				display: inline-block;
				background: #5765c2;
				top: 2px;
				left: 2px;
				position: absolute;
				border-radius: 100%;
			}
			.collection-wrapper .dropdown-list {
				right: 0;
				left: unset;
			}
			.dropdown-list ul li span.check {
				display: inline-block;
				width: 15px;
				height: 15px;
				margin: 0 10px;
				border-radius: 100%;
				background: transparent;
				border: 2px solid #ccc;
				position: relative;
			}

			.dropdown-list ul li label {
				display: flex;
				align-items: center;
				flex-wrap: wrap;
				cursor: pointer;
			}
			.clickedHere .dropdown-list {
				display: block;
			}




			.base-product-sec {
				display: inline-block;
				width: 100%;
				padding: 0 10px;
				box-sizing: border-box;
			}
			.app_logo {
				display: inline-block;
				width: 100%;
				margin: 20px 0;
			}
			.app_logo img {
				max-width: 150px;
				width: 100%;
			}
			.search_div {
				display: inline-block;
				font-family: -apple-system,BlinkMacSystemFont,San Francisco,Roboto,Segoe UI,Helvetica Neue,sans-serif;
				width: 100%;
				padding: 10px 0 30px;
				box-sizing: border-box;
				border-bottom: 1px solid #ccc;
			}
			.search_div label {
				margin-bottom: 6px;
				display: block;
			}
			.search_div label {
				margin-bottom: 6px;
				display: block;
				font-weight: 400;
				color: #212b36;
				font-size: 18px;
			}
			.search_div input.input_search {
				font-size: 17px;
				height: 40px;
				padding: 0 10px;
				width: 100%;
				font-family: -apple-system,BlinkMacSystemFont,San Francisco,Roboto,Segoe UI,Helvetica Neue,sans-serif;
				border: 1px solid #ccc;
				transition: .3s all ease-in-out;
				color: #c15031;
			}
			.search_div input.input_search:focus {
				border: 1px solid #c15031;
				outline: 0;
			}
			h4.main_heading {
				font-size: 20px;
				font-family: -apple-system,BlinkMacSystemFont,San Francisco,Roboto,Segoe UI,Helvetica Neue,sans-serif;
				font-weight: 600;
				color: #c15031;
			}
			.product_div {
				font-family: -apple-system,BlinkMacSystemFont,San Francisco,Roboto,Segoe UI,Helvetica Neue,sans-serif;
				padding: 0 10px;
			}
			table.products_table {
				border-collapse: collapse;
				width: 100%;
				background: #fff;
			}
			table.products_table td, table.products_table th {
				/*border: 1px solid #dddddd;*/
				text-align: left;
				padding: 15px 8px;
				/*text-align: center;*/
			}
			.products_table tr {
				border-bottom: 1px solid #ccc;
			}
			table.products_table th {
				font-weight: 500;
				color: #212b36;
			}
			.product_div .product_image {
				width: 80px;
				height: auto;
			}
			.product_div h4 {
				font-size: 18px;
				font-weight: 500;
				color: #2f3943;
			}
			table.products_table a.base_title {
				text-decoration: none;
				color: #1c2260;
				padding-bottom: 4px;
				position: relative;    display: inline-block;
			}
			table.products_table a.base_title:after {
				content: "";
				width: 0;
				height: 1px;
				position: absolute;
				background: #1c2260;
				left: 0;
				bottom: 0;
				right: 0;
				margin: auto;
				transition: .3s all ease-in-out;
			}
			table.products_table a.base_title:hover:after {
				width: 100%;
				}ul#pagination {    padding: 0;    margin-bottom: 70px;}ul#pagination li {    display: inline-block;    padding: 5px 10px;    border: 1px solid #c15031;    margin: 0 3px;	transition: .3s all ease-in-out;	cursor: pointer;}ul#pagination li:hover {    background: #c15031;  }ul#pagination li:hover a {    color: #fff;  }ul#pagination li a {	color: #c15031;	text-decoration: none;	transition: .3s all ease-in-out;}ul#pagination li.active {    background: #c15031;}ul#pagination li.active a {	color: #fff;}

			</style>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

			<script>


            $(document).on("click","ul.pagination li a",function(e){
                        e.preventDefault();
                var href = $(this).attr("href");
                if(href != undefined && href != ''){
                    
                    	var input_search = $("input.input_search").val();
						        if(input_search != ''){
						            $(".filtersBack input[name='search']").val(input_search);
						        }
						var tag = $(".tag-input").val();
						
						if(tag != ''){
						            $(".filtersBack input[name='tag']").val(tag);
						}
						
						var type = $('[name="type"]:checked').val();
						
						if(type != undefined && type != ''){
						     $(".filtersBack input[name='type']").val(type);
						}
						
						var vendor = $('[name="vendor"]:checked').val();
						if(vendor != undefined && vendor != ''){
						    $(".filtersBack input[name='vendor']").val(vendor);
						}
					var collection = $('[name="collection"]:checked').val();
					
					if(collection != undefined && collection != ''){
					    $(".filtersBack input[name='collection']").val(collection);
					}
					
					
                        var page = $(this).html();
                        
                        
                        
                        $(".filtersBack input[name='page']").val(page);
                    
                    $(".filtersBack").submit();
                }
                        
            });


				$(document).on("blur","input.square_feet",function(){
					var data_id = $(this).attr("data-id");
					var value  = $(this).val();
					if($.trim(value) != ''){
						var data = 'data_id='+data_id+'&feet_val='+value;
						$(this).attr("disabled","disabled");
						$.ajax({
							url:'submit_square.php',
							type:'POST',
							data:data,
							success:function(res){
								$("input.square_feet").removeAttr("disabled");
							}
						});
					}

				});

				function searchAjax(){
					setTimeout(function(){
						$(".index_pagination").hide();
						var input_search = $("input.input_search").val();
						var tag = $(".tag-input").val();
						var type = $('[name="type"]:checked').val();
						var vendor = $('[name="vendor"]:checked').val();
						var collection = $('[name="collection"]:checked').val();
		// var collection = $('[name="collection"]:checked').val();
		$('.hidden-filters').val('input_search='+input_search+'&input_type='+type+'&input_vendor='+vendor+'&input_tag='+tag+'&input_collection='+collection);
		var product_id =0;
		var page_num = $(".page_num").val();
		var data = 'input_search='+input_search+'&input_type='+type+'&input_vendor='+vendor+'&input_tag='+tag+'&input_collection='+collection+'&product_id='+product_id+'&page='+page_num+'&searching';
		
		$.ajax({
			url:'search_products_main.php',
			type:'POST',
			data:data,
			dataType:'HTML',
			success:function(res){
				$(".product_div").html('');
				$(".product_div").html(res);
			}
		});
	},100);

				}
				$(document).on("change",".search-radio",function(){
					searchAjax();
				});
				$(document).on("keyup","input.input_search",function(){
					searchAjax();
				});
				$(document).on("keyup",".tag-input",function(){
					searchAjax();
				});
				$(document).on("click",function(e){
					if($(e.target).closest(".clickToAdd").length === 0){
					    
						$('.clickToAdd').removeClass('clickedHere');		
					}
				});
				$(document).on("click",'.edit_product',function(e){
					e.preventDefault();
					var id = $(this).data('id');
					$('.hidden-id').val(id);
					$('.single-form').submit();
				});
				$('.clickToAdd').click(function(e){
				  if($(e.target).hasClass('tag-input')){
				    return false; 
				 
				  }
				  
					if($(this).hasClass('clickedHere')){
						$('.clickToAdd').removeClass('clickedHere');
					}
					else{
						$('.clickToAdd').removeClass('clickedHere');
						$(this).addClass('clickedHere');
					}
				});
				$(document).on("change",".select-all .select-checkbox",function(){
					if($(this).prop('checked') == true){
						$('.select-individual .select-checkbox').prop('checked',true).trigger('change');
					}
					else{
						$('.select-individual .select-checkbox').prop('checked',false).trigger('change');
					}
				});
				$(document).on("change",".select-individual .select-checkbox",function(){
					if($('.select-individual .select-checkbox:checked').length){
						$('.bulk-form > .btn').css('opacity','1');
					}
					else{
						$('.bulk-form > .btn').css('opacity','0');	
					}
				});

$(document).ready(function() {
	searchAjax();
// $(".product_div").load("pagination.php?page=1&product_id=0");
//     $(".index_pagination li").on('click',function(e){
// 	e.preventDefault();
// 		$("#pagination li").removeClass('active');
// 		$(this).addClass('active');
//         var pageNum = this.id;
//         $(".product_div").load("pagination.php?page=" + pageNum+"&product_id=0");
//     });
//     });
//   $(document).on("click",".search_pagination li",function(e){
// 	e.preventDefault();
// 		$("#pagination li").removeClass('active');
// 		$(this).addClass('active');
//         var pageNum = this.id;



//             var input_search = $("input.input_search").val();
//         var product_id = $(".base_product_title").attr('data-id');
//           var data = 'input_search='+input_search+'&product_id='+product_id+'&searching&page='+pageNum;
//           $.ajax({
//           		url:'pagination_search_main_products.php',
// 				type:'POST',
// 				data:data,
// 				dataType:'HTML',
// 				success:function(res){
//                   $(".table_replace").html('');
//                   $(".table_replace").html(res);
//                 	}

//           });
                  });
</script>