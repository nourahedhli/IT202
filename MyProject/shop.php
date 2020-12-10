<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php


if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

?>
<?php
$db = getDB();
//fetch and update latest user's balance
$stmt = $db->prepare("SELECT points from Users where id = :id");
$r = $stmt->execute([":id"=>get_user_id()]);
if($r){
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        $balance = $result["points"];
        $_SESSION["user"]["balance"] = $balance;
    }
}
//pagination stuff
$page=1;
$per_page = 10;
$query = "SELECT count(*) as total FROM Products WHERE quantity > 0 ORDER BY CREATED DESC";

$offset = ($page-1) * $per_page;
/*
$stmt = $db->prepare("SELECT count(*) as total FROM F20_Products WHERE quantity > 0 ORDER BY CREATED DESC");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result){
    $total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;*/
//fetch item list
$stmt = $db->prepare("SELECT * FROM Products WHERE quantity > 0 ORDER BY CREATED DESC LIMIT :offset,:count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);


$balance = getBalance();
$cost = calcNextProductCost();


$category = null;
$search = null;
$price = null;

if (isset($_POST["search"])){
    $cat = $_POST["category"];
    
}
// by category 
$stmt = $db->prepare("SELECT DISTINCT category From Products");
$stmt->execute();
$cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db-> prepare("SELECT * FROM Products LIMIT 10");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["search"])){
    $pri = $_POST["price"];
    
}
// search by prices
$stmt = $db->prepare("SELECT DISTINCT price From Products");
$stmt->execute();
$prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db-> prepare("SELECT * FROM Products LIMIT 10");
$prod = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


    <script>
        //php will exec first so just the value will be visible on js side
        let balance = <?php echo $balance;?>;
        let cost = <?php echo $cost;?>;

        function makePurchase() {
            //todo client side balance check
            if (cost > balance) {
                alert("You can't afford this right now");
                return;
            }
            //https://www.w3schools.com/xml/ajax_xmlhttprequest_send.asp
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    let json = JSON.parse(this.responseText);
                    if (json) {
                        if (json.status == 200) {
                            alert("Congrats you received 1 " + json.egg.name);
                            location.reload();
                        } else {
                            alert(json.error);
                        }
                    }
                }
            };
            xhttp.open("POST", "<?php echo getURL("api/purchase_product.php");?>", true);
            //this is required for post ajax calls to submit it as a form
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            //map any key/value data similar to query params
            xhttp.send();

        }
        function addToCart(itemId, cost){
            if (cost > balance) {
                alert("You can't afford this right now");
                return;
            }
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

 

    <form method="POST">
        <select>
        <?php foreach($prices as $p): ?>
        <input name="query" placeholder="Search" value="<?php safer_echo(($p["price"] == $price?"selected='selected'":"")); ?>"/>

        <input type="submit" value="Search" name="search"/>
        <?php endforeach; ?>
        </select>
        
      
            <select name="category">
               <?php foreach($cats as $c): ?>
                <option value="<?php echo $c["category"];?>"
                        <?php echo ($c["category"] == $category?"selected='selected'":""); ?>
                        >
                        <?php echo $c["category"];?>
                        </option>
                        <?php endforeach; ?>
            </select>

    </form>

<div>

    <?php foreach($products as $p): ?>
    <dix>Product: <?php echo $p["name"]; ?> </dix>
    <?php endforeach;?>
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
        <?php include(__DIR__ . "/partials/pagination.php"); ?>
    </div>

    <?php require(__DIR__ . "/partials/flash.php");
