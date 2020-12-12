<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php


if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

?>
<?php
//move to helpers.php
function extractData($key){
	if(isset($_POST[$key])){
		$output = $_POST[$key];
		$_SESSION[$key] = $output;
	}
	else if (isset($_SESSION[$key])){
		$output = $_SESSION[$key];
	}
	else{
		$output = null;
	}
	return $output;
}
$db = getDB();
$page=1;
$per_page = 10;
//paginate query
$pQuery = "SELECT COUNT(*) as total from Products where quantity > 0";
//data query
$dQuery = "SELECT id,name, price, category, description as total from Products where quantity > 0";
//refer to function defined above (gets the value from POST or from SESSION)
$category = extractData("category");
$search = extractData("search");
$sort = extractData("sort");
$order = extractData("order");
$params = [];
//build and map queries dynamically
if(isset($category)){
	$pQuery .= " AND category = :cat";
	$dQuery .= " AND name LIKE :search";
	$params[":cat"] = $category;
}
if(isset($search)){
	$pQuery .= " AND name LIKE :search";
	$dQuery .= " AND name LIKE :search";
	$params[":search"] = "%$search%";
}
if(isset($sort) && isset($order)){
	if(in_array($sort,["price","category","name"])
	&& in_array($order, ["asc","desc"])){
		$dQuery .= " ORDERY BY $sort $order";
	}
}
$offset = ($page-1) * $per_page;


//process p query
$stmt = $db->prepare($pQuery);
$stmt->execute($params);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if($result){
	$total = (int)$result["total"];
}

$total_pages = ceil($total / $per_page);

//process data query 
if(isset($offset) && isset($per_page)){
	$dQuery .= " LIMIT :offset, :count";
	$params[":offset"] = $offset;
	$params[":count"] = $per_page;
}
$stmt = $db->prepare($dQuery);
foreach($param as $key=>$val){
	if($key == ":offset" || $key == ":count"){
		$stmt->bindValue($key, $val, PDO::INT);
	}
	else{
		$stmt->bindValue($key, $val);
	}
}
$stmt->execute();//don't pass params, we're mapping it to bind value above
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);



$stmt = $db->prepare("SELECT distinct category from Products");
$r = $stmt->execute();
if ($r){
    $cats= $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    flash("There was a problem fetching the results");

}
?>


    <script>

        function addToCart(itemId){
            //https://www.w3schools.com/xml/ajax_xmlhttprequest_send.asp
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {

            };
            xhttp.open("POST", "<?php echo getURL("api/add_to_cart.php");?>", true);
            //this is required for post ajax calls to submit it as a form
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            //map any key/value data similar to query params
            xhttp.send("itemId="+itemId);
        }
    </script>

    <h1>Shop</h1>


    <div>
        <form method="POST" style="float: left; margin-top: 3em; display: inline-flex; margin-left: 2em;" id = "form1">
			<input type="text" name="search" value="<?php echo isset($search)?$search:"";?>"/>
			<select name="category">
				<?php foreach($cats as $c):?>
					<option value="<?php echo $c["category"];?>"
					<?php echo ($c["category"] == $category?"selected='selected'":"");?>
					>
					<?php echo $c["category"];?>
					</option>
				<?php endforeach;?>
			</select>
			<select name="sort">
				<!-- todo add preselect like category options-->
				<option value="category">Category</option>
				<option value="price">Price</option>
				<option value="name">Name</option>
			</select>
			<select name="order">
				<!-- todo add preselect like category options-->
				<option value="asc">Ascending</option>
				<option value="desc">Descending</option>
			</select>
        </form>
    </div>


    <div class="container">

        <div class="row">
            <div class="card-deck">
                <?php foreach($items as $item):?>
                    <div class="col-auto mb-3">
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <div class="card-title">
                                    <?php echo $item["name"];?>
                                </div>
                                <div class="card-text">
                                    Product Description:
                                    <?php echo $item["description"];?>
                                </div>
                                <div class="card-footer">
                                    <button type="button" onclick="addToCart(<?php echo $item["id"];?>,<?php echo $item["price"];?>);" class="btn btn-primary btn-lg">Add to Cart
                                        (Cost: <?php echo $item["price"]; ?>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
	<!--todo add pagination-->
    </div>

    <?php require(__DIR__ . "/partials/flash.php");
