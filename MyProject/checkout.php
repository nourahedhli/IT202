<?php require_once(__DIR__ . "/partials/nav.php");


//only let's users access if logged in
if (!is_logged_in()) {
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
$user_id = get_user_id();
//getting the items from the cart
?>


<?php
$userID = get_user_id();
$db = getDB();
$stmt = $db->prepare("SELECT Product.name,c.product_id, c.id,c.quantity,c.price as product FROM Cart as c JOIN Users on c.user_id = Users.id LEFT JOIN Products  on Product.id = c.product_id where c.user_id = :id ORDER by product");
$r= $stmt->execute([":id" => $userID]);
$results= $stmt->fetchAll(PDO::FETCH_ASSOC);
flash("results not working", var_export($stmt->errorInfo(), true));
?>

<div class="Items">

    <?php
    $totalItems=0;
    foreach ($results as $item):
    ?>
    <div> Product: <?php echo ($item["product"]) ?>


    </div>
    <div> Product Quantity: <?php echo ($item["quantity"]) ?> </div>
    <div> Product's Price: <?php echo ($item["price"]) ?> </div>

    <div> Order Total: <?php
        $xTotal= ((float)($item["price"] * (int)$item["quantity"]));
        echo ($xTotal);
    $totalItems = $totalItems + $xTotal ;
    ?> </div>
    <?php endforeach; ?>

</div>




<?php
// shipping information
// we need the id address and total also the payment method
//Calculate Cart Items
//Verify desired product and desired quantity are available in the Products table
// Cart table has the product_id quantity user_id price and created

if(isset($_POST["submit"])) {
    $address = null;
    $payment = null;
    $price = $totalItems;
    $created = date('Y-m-d H:i:s');
    $id = $user_id;

    if(isset($_POST["payment"])){
        $payment = $_POST["payment"];
        if($payment==-1){
            flash("Its not working you have to do a valid payment method.");
        }
    }

$db = getDB();
$stmt = $db->prepare("SELECT Cart.product_id,Cart.quantity as CartQ AND Products.name,Products.quantity as ProductQ FROM Cart Join Products on Cart.product_id = Products.id JOIN Users on Cart.user_id = Users.id where Cart.user_id=:id");
$r= $stmt->execute([":id" => $userID]);
$products= $stmt->fetchAll(PDO::FETCH_ASSOC);


$valid = true;
foreach($products as $product):
if($product["CartQ"]>$product["ProductQ"]){
    flash("Sorry, there are only ".$product["ProductQ"]." ".$product["name"]." left ");
    $valid = false;
}elseif($product["ProductQ"]==0){
    flash("Sorry! no more of, ".$product["name"]." item you have to update your cart.");
    $valid = false;
}
endforeach;








if ($valid == true && $payment != -1) {
    $db = getDB();
    $stmt = $db->prepare("SELECT product_id, quantity, price , created From Cart Join Products on Cart.product_id = Products.id JOIN Users on Cart.user_id = Users.id where Cart.user_id=:id ");
    $r = $stmt->execute([":id" => $id]);
    $OrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Orders (user_id,total_price,address,created,payment_method) VALUES (:user,:total,:add,:created,:pay)");
    $r = $stmt->execute([
        ":user"=>$id,
        ":total"=>$price,
        ":add"=>$address,
        ":created"=>$created,
        ":pay"=>$payment


    ]);

    if (!$r) {
        flash(var_export($stmt->errorInfo(), true));
        echo("Something is wrong with the order ");

    }
    $id = get_user_id();
//Get last Order ID from Orders table
    $db = getDB();
    $stmt = $db->prepare("SELECT id from Orders WHERE user_id = :id ORDER by created DESC ");
    $r = $stmt->execute([":id" => $id]);
    flash("its not working here", var_export($stmt->errorInfo(), true));
    $Last_order = $stmt->fetch(PDO::FETCH_ASSOC);

//Copy the cart details into the OrderItems tables with the Order ID from the previous step
    $order_id = $Last_order["id"];
    $id = get_user_id();

    foreach ($OrderItems as $item) {
        $db = getDB();
        $product_id = $item["product_id"];
        $item_quantity = $item["quantity"];
        $price = $item["price"];
        $created = $item["created"];
        $stmt = $db->prepare("INSERT INTO OrderItems order_id, product_id, quantity, price, created)VALUES (:order_id, :pid,:q,:p,:cr)");
        $r = $stmt->execute([
            ":order_id" => $order_id,
            ":pid" => $product_id,
            ":q" => $item_quantity,
            ":p" => $price,
            ":cr" => $created

        ]);
//Update the Products table Quantity for each item to deduct the Ordered Quantity
        $db = getDB();
        $stmt = $db->prepare("UPDATE Products set quantity= quantity-$item_quantity where id=:pid");
        $r = $stmt->execute([":pid" => $product_id, ":q" => $item_quantity]);

        //Clear out the user’s cart after successful order

        $userID = get_user_id();
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM Cart where user_id=:id");
        $r = $stmt->execute([":id" => $userID]);

        //Redirect user to Order Confirmation Page
        flash("Thank you. Now you will see your confirmation info");
        die(header("Location: orders.php"));

    }
}





}





//Make entry into Orders table

?>


    <form method="POST">
        <h4>Fill In the Form </h4>
        <br>
        <label>Choose Payment Type:</label>
        <br>
        <select name="payment" required>
            <option value="-1">None</option>
            <option value="cash">Cash</option>
            <option value="amex">Amex</option>
            <option value="discover">Discover</option>
            <option value="masterCard">MasterCard</option>
            <option value="paypal">PayPal</option>
            <option value="visa">Visa</option>
        </select>
        <br>
        <label>Street Address:</label>
        <br>
        <input name="add" type="text" required/>
        <br>
        <label>City:</label>
        <br>
        <input name="city" type="text" required/>
        <br>
        <label>State:</label>
        <br>
        <input name="state" type="text" required/>
        <br>
        <label>Zip: (5 Digits)</label>
        <br>
        <input name="zip" type="text" pattern="[0-9]{5}" required/>
        <br>

        <button id="placeOrder" type="submit" name="submit" value="Submit">Place Order</button>
    </form>
<?php require(__DIR__ . "/partials/flash.php");


