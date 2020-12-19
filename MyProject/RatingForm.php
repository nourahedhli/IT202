<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php

if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Products.id,name,quantity,price,description,category,user_id,Users.username,Users.id as uid FROM Products JOIN Users on Products.user_id = Users.id where Products.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
<?php
//check to see if the user purchased the product to allow them to rate it
$userID = get_user_id();
$db = getDB();
$stmt = $db->prepare("SELECT Orders.id,OrderItems.product_id FROM Orders JOIN OrderItems where Orders.user_id = :id AND OrderItems.order_id = Orders.id");
$r = $stmt->execute([":id"=>$userID]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$OR = false;
foreach($orderItems as $item):
    if($item["product_id"]==$_GET["id"]){
        $OR = true;
    }
endforeach;
?>
<?php
$page = 1;
$per_page = 5;
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}
$id = $_GET["id"];
$db = getDB();
$stmt = $db->prepare("SELECT count(*) as total from Ratings WHERE product_id=:id");
$stmt->execute([":id"=>$id]);
$ratingResult = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($ratingResult){
    $total = (int)$ratingResult["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;


$id = $_GET["id"];
$db = getDB();
$stmt = $db->prepare("SELECT Ratings.rating,Ratings.comment,Ratings.created,Users.username FROM Ratings JOIN Users where product_id=:id and Ratings.user_id = Users.id LIMIT :offset, :count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", $id);
$stmt->execute();
$ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ThisRatings = false;
if($ratings){
    $ThisRatings = true;
}
?>

<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <br>
            <div><h3><?php safer_echo($result["name"]); ?> </h3></div>
        </div>
        <div class="card-body">
            <div>
                <p>Item Information:</p>
                <div>Price: $<?php safer_echo($result["price"]); ?></div>
                <div>Quantity: <?php safer_echo($result["quantity"]); ?></div>
                <div>Category: <?php safer_echo($result["category"]); ?></div>
                <div>Owner ID: <?php safer_echo($result["username"]); ?></div>
               
            </div>
        </div>

      
    </div>
    <br><br>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>

<?php
if (isset($_POST["save"])) {
    $id = $_GET["id"];
    $quantity = $_POST["quantity"];
    $price = $result["price"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Cart (product_id, quantity, price, user_id) VALUES(:id, :quantity, :price, :user)");
    $r = $stmt->execute([
        ":id" => $id,
        ":quantity" => $quantity,
        ":price" => $price,
        ":user" => $user
    ]);
    if ($r) {
        flash("Successfully added to cart.");
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php
if(isset($_POST["rate"])){
    $rate = $_POST["rating"];
    $comment = $_POST["comment"];
    $userID = get_user_id();
    $pid = $_GET["id"];
    $created = date('Y-m-d H:i:s');

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Ratings(product_id,user_id,rating,comment,created) VALUES(:pid,:user,:rate,:comment,:created)");
    $r = $stmt->execute([":pid"=>$pid,":user"=>$userID,":rate"=>$rate,":comment"=>$comment,":created"=>$created]);

    if($r) {
        flash("Rating is submitted");
    }else{
        flash("Error rating.");
    }
}
?>

<?php if($OR):?>
    <br>
    <h3>Rate This Product</h3>
    <div>
        <form method="POST">
            <br>
            <label>Rating from 1 to 5:</label>
            <br>
            <select name="rating" required>
                <option value="5">5</option>
                <option value="4">4</option>
                <option value="3">3</option>
                <option value="2">2</option>
                <option value="1">1</option>
            </select>
            <br>
            <label>Add a Comment Here:</label>
            <br>
            <input type="text" name="comment" required/>
            <br>
            <button type="submit" name="rate" value="Rate Product">Submit</button>
        </form>
    </div>
<?php endif; ?>

<?php if($ThisRatings):?>
    <br>
    <h3>Product Reviews</h3>
    <?php foreach($ratings as $rating): ?>
        <div>Review by: <?php safer_echo($rating["username"])?></div>
        <div>Rating: <?php safer_echo($rating["rating"])?></div>
        <div>Comment: <?php safer_echo($rating["comment"])?></div>
        <div>Date: <?php safer_echo($rating["created"])?></div>
        <br>
    <?php endforeach; ?>
<?php endif; ?>
    <div>
        <nav aria-label="Pages">
            <ul class="pagination">
                <?php if(!(($page-1)<1)):?>
                    <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                        <a class="page-link" href="?id=<?php echo $result["id"];?>&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                    <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?id=<?php echo $result["id"];?>&page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <?php if($page<$total_pages):?>
                    <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                        <a class="page-link" href="?id=<?php echo $result["id"];?>&page=<?php echo $page+1;?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
