<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//only let's users access this page if logged in
//depending on the users role, they will either see their orders or all orders
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>

<?php


if (isset($_POST["submit"])) {

    flash(" Order Received. Thank You! "); }


    $page = 1;
$per_page = 5;
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}
$db = getDB();
if(!has_role("Admin")) {
    $stmt = $db->prepare("SELECT count(*) as total from Orders where user_id=:id");
}elseif(has_role("Admin")){
    $stmt = $db->prepare("SELECT count(*) as total from Orders");
}
$stmt->execute([":id"=>get_user_id()]);
$orderResult = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($orderResult){
    $total = (int)$orderResult["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;
$resultOrder =[];
$resultAdmin =[];
//User will be able to see their Purchase History


if (!has_role("Admin")){
    $db = getDB();
    $userID = get_user_id();
    $stmt = $db->prepare("SELECT total_price,created,address FROM Orders where user_id=:id ORDER by created DESC LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt ->bindValue (":id", $userID);
    $r= $stmt ->execute();
    $resultOrder = $stmt->fetchAll(PDO::FETCH_ASSOC);


} elseif (has_role("Admin")){

    $db = getDB();
    $userID = get_user_id();
    $stmt = $db->prepare("SELECT * FROM Orders where user_id=:id ORDER by created DESC LIMIT :offset, :count");
    $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
    $stmt ->bindValue (":id", $userID);
    $r= $stmt ->execute();
    $resultAdmin = $stmt->fetchAll(PDO::FETCH_ASSOC);


}




//For now limit to 10 most recent orders
//Store Owner will be able to see all Purchase History
//For now limit to 10 most recent orders


?>

<div class="results">
    <div class="list-group">

        <div>

        </div>
        <?php

        if(!has_role("Admin")):

            foreach ($resultOrder as $order):?>
                <div>
                    <div><h3>Order Confirmation:</h3></div>
                </div>
                <br>

                <div class="list-group-item">
                    <div>
                        <div>If your information is correct click on submit</div>
                    </div>
                    <div>
                        <div>Order placed on: <?php safer_echo($order["created"]); ?></div>
                    </div>
                    <div>
                        <div>Address: <?php safer_echo($order["address"]); ?></div>
                    </div>
                    <div>
                        <div>Total: $<?php safer_echo($order["total_price"]); ?></div>
                    </div>

                    <div>
                        <br>
                    </div>
                    <div>

                        <div> <a type="button" href="#">Submit</a>


                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php

    if(has_role("Admin")):
        $TotalPrice = 0;
        foreach ($resultAdmin as $order):?>
            <div class="list-group-item">
                <div>
                    <div><h3>Order History:</h3></div>
                </div>
                <br>
                <div>
                    <div>Order ID: <?php safer_echo($order["id"]); ?></div>
                </div>
                <div>
                    <div>User ID: <?php safer_echo($order["user_id"]); ?><?php echo " ";?><a type="button" href="profile.php?id=<?php safer_echo($order["user_id"]); ?>">View Profile</a></div>
                </div>

                <div>
                    <div>Address: <?php safer_echo($order["address"]); ?></div>
                </div>

                <div>
                    <div>Payment Method: <?php safer_echo($order["payment_method"]); ?></div>
                </div>
                <div>
                    <div>Order Date: <?php safer_echo($order["created"]); ?></div>
                </div>
                <div>
                    <div>Total: $<?php safer_echo($order["total_price"]);
                    $TotalPrice+=$order["total_price"];
                    ?></div>
                </div>
                <div>
                    <br>
                </div>
            </div>
        <?php endforeach; ?>
        <div><b>Total : <?php safer_echo($TotalPrice);?></b></div>
    <?php endif; ?>
    <div>
        <div><br></div>
    </div>
</div>
    <div>
        <nav aria-label="Pages">
            <ul class="pagination">
                <?php if(!(($page-1)<1)):?>
                    <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                        <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                    <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <?php if($page<$total_pages):?>
                    <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                        <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

<?php require(__DIR__ . "/partials/flash.php");
