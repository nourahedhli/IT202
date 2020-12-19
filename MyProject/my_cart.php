<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();

if(isset($_POST["delete"])){
    $stmt = $db->prepare("DELETE FROM Cart where id = :id");
    $r = $stmt->execute([":id"=>$_POST["cartId"]]);
    //fix for example bug
    //$stmt = $db->prepare("DELETE FROM F20_Cart where id = :id AND user_id = :uid");
    //$r = $stmt->execute([":id"=>$_POST["cartId"], ":uid"=>get_user_id()]);
    if($r){
        flash("Deleted item from cart", "success");
    }
}
if(isset($_POST["update"])){
    $stmt = $db->prepare("UPDATE Cart set quantity = :q where id = :id");
    $r = $stmt->execute([":id"=>$_POST["cartId"], ":q"=>$_POST["quantity"]]);
    if($r){
        flash("Updated quantity", "success");
    }
}


$stmt = $db->prepare("SELECT c.id, p.name, c.price, c.quantity, (c.price * c.quantity) as sub from Cart c JOIN Products p on c.product_id = p.id where c.user_id = :id");
$stmt->execute([":id"=>get_user_id()]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <div class="container-fluid">
        <h3>My Cart</h3>
        <div class="list-group">
        <?php if($results && count($results) > 0):?>
            <div class="list-group-item">
                <div class="row">
                    <div class="col">
                       Name
                    </div>
                    <div class="col">
                        Price
                    </div>
                    <div class="col">
                        Quantity
                    </div>
                    <div class="col">
                        Total
                    </div>
                    <div class="col">
                        Actions
                    </div>
                </div>
            </div>

            <?php
            $cartTotal = 0;
            foreach($results as $r):?>
            <div class="list-group-item">
                <form method="POST">
                <div class="row">
                    <div class="col">
                        <?php echo $r["name"];?>
                    </div>
                    <div class="col">
                        <?php echo $r["price"];?>
                    </div>
                    <div class="col">

                            <input type="number" min="0" name="quantity" value="<?php echo $r["quantity"];?>"/>
                            <input type="hidden" name="cartId" value="<?php echo $r["id"];?>"/>

                    </div>
                    <div class="col">
                        <?php echo $r["sub"] ;
                        $cartTotal+=(float)$r["sub"];;
                        ;?>
                    </div>
                    <div class="col">
                        <!-- form split was on purpose-->
                        <input type="submit" class="btn btn-success" name="update" value="Update"/>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="cartId" value="<?php echo $r["id"];?>"/>
                            <input type="submit" class="btn btn-danger" name="delete" value="Delete Cart Item"/>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach;?>
    <div>
        <div><b>Total Cart Value: $<?php safer_echo($cartTotal); ?></b></div>
    </div>
        <?php else:?>
        <div class="list-group-item">
            No items in cart
        </div>
        <?php endif;?>
        </div>
    </div>
    <br>
<?php if(!empty($results)):?>
    <a type="button" href="checkout.php">Checkout</a>
<?php endif;?>
    <br><br>
<?php require(__DIR__ . "/partials/flash.php");

