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

//User will be able to see their Purchase History


if (!has_role("Admin")){
    $db = getDB();
    $userID = get_user_id();
    $stmt = $db ->prepare("SELECT total_price, created, address, payment_method FROM Orders WHERE user_id=:id");
    $r= $stmt ->execute([":id" => $userID]);
    $resultOrder = $stmt->fetchAll(PDO::FETCH_ASSOC);


} elseif (has_role("Admin")){

    $db = getDB();
    $userID = get_user_id();
    $stmt = $db ->prepare("SELECT * FROM Orders");
    $r= $stmt ->execute([":id" => $userID]);
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
        $resultOrder =[];
        if(!has_role("Admin")):

            foreach ($resultOrder as $order):?>
                <div>
                    <div><h3>Order Confirmation:</h3></div>
                </div>
                <br>
                <div class="list-group-item">
                    <div>
                        <div>Order placed on: <?php safer_echo($order["created"]); ?></div>
                    </div>
                    <div>
                        <div>Address: <?php safer_echo($order["address"]); ?></div>
                    </div>
                    <div>
                        <div>Subtotal: $<?php safer_echo($order["total_price"]); ?></div>
                    </div>
                    <div>
                        <div>Status: Received</div>
                    </div>
                    <div>
                        <br>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    $resultAdmin =[];
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
                    <div>Total: $<?php safer_echo($order["total_price"]); $TotalPrice+=$order["total_price"];?></div>
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


<?php require(__DIR__ . "/partials/flash.php");
