<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>

<?php
$isValid = true;
$address = "";
$PM = "";
if (isset($_POST["place"])){

    if( isset($_POST["address"])){
        $address = $_POST["address"];
    }

    if( isset($_POST["address2"])){
        $address = $address . ", " .$_POST["address2"];
    }
    if( isset($_POST["city"])){
        $address = $address . ", " .$_POST["city"];
    }
    if( isset($_POST["state"])){
        $address = $address . ", " .$_POST["state"];
    }
    if( isset($_POST["zip"])){
        $address = $address . ", " .$_POST["zip"];
    }
    if( isset($_POST["payment"])){
        $PM = $_POST["payment"];
    }

    if (!isset($_POST["address"])  || !isset($_POST["city"]) || !isset($_POST["state"]) ||
        !isset($_POST["zip"]) || !isset($_POST["payment"]) ){
        $isValid = false;

    }


    if ($isValid){
        //pull data from user's cart
        $db = getDB();
        $id = get_user_id();
        $results = [];

        if (isset($id)) {
            $stmt = $db->prepare("SELECT Cart.product_id, Cart.quantity, Cart.user_id, Cart.price, Products.name, Products.quantity as originalQ,
      (Products.price * Cart.quantity) as sub from Cart JOIN Users on Users.id = Cart.user_id JOIN Products on Products.id = Cart.product_id
       WHERE Users.id = :q AND Products.visibility = 1 ");

            $r = $stmt->execute([":q" => $id]);
            if ($r) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            else {
                flash("There was a problem fetching the results ");
            }
        } else{flash("You do not have a valid ID");}

        //var_export($results);
        // calculating total and checking for quantity
        $quantityCheck = true;
        $total = 0;
        foreach($results as $a){
            if ($a["sub"]){
                $total += $a["sub"];
            }

            if($a["quantity"] > $a["originalQ"]){
                $originalQ = $a["originalQ"];
                $name = $a["name"];
                $quantityCheck = false;
            }
        }

        if ($quantityCheck){
            // creating an order for user
            $stmt = $db->prepare("INSERT INTO Orders(user_id, total_price, address, Payment) VALUES(:user_id,:total_price, :address, :Payment)");

            $r = $stmt->execute([":user_id" => $id,
                ":total_price" => $total,
                ":address" => $address,
                ":Payment" => $PM
            ]);
            $e = $stmt->errorInfo();
            if ($e[0] == "00000") {
                echo "processing...";
            }else{
                flash("oops, something went wrong");
            }

            //fetching last order entered in table by MAX(id)
            $orderid = [];
            $stmt = $db->prepare("SELECT MAX(id) as last_order_id from Orders where user_id = :id ");
            $r = $stmt->execute([":id" => $id]);
            if ($r) {
                $orderid = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            else {
                flash("There was a problem fetching the results ");
            }


            // populating order items that are confirmed

            foreach($results as $data){
                $stmt = $db->prepare("INSERT INTO ordersItems(product_id, user_id, quantity, price, order_id) VALUES(:product_id,:user_id, :quantity, :price, :order_id)");
                $r = $stmt->execute([":product_id" => $data["product_id"],
                    ":user_id" => $id,
                    ":quantity" => $data["quantity"],
                    ":price" => $data["price"],
                    ":order_id" => $orderid["last_order_id"]
                ]);

                //update quantity in products.

                $stmt = $db->prepare("UPDATE Products set  quantity =quantity - :desired where id = :id");
                $r = $stmt->execute([":desired" => $data["quantity"], ":id" => $data["product_id"]]);
                if ($r) {
                    echo "Updated quantity";
                }
                else {
                    echo "Error updating quantity";
                }
            }

            //when all said and done, delete all items from cart...

            if(isset($_POST["place"])){
                $stmt = $db->prepare("DELETE FROM Cart where user_id = :uid");
                $r = $stmt->execute([":uid"=>get_user_id()]);
                if($r){
                    echo "items were deleted";
                }
            }

            //redirect to Confirmation
            echo "<script> location.href='confirmation.php'; </script>";
            exit;

        }
        else{ //if quantitycheck is false
            flash ("for item: $name, the desired quantity should be less that $originalQ since that is what we have in stock");
        }


    }else{ //if missing fields
        flash("missing fields, please fill out form");
    }
}


?>

    <form method = "POST">
        <div class="form-group">
            <h1> ADDRESS </h1>
            <label for="inputAddress">Address</label>
            <input type="text" name="address" class="form-control" id="inputAddress" placeholder="1234 Main St" required>
        </div>
        <div class="form-group">
            <label for="inputAddress2">Address 2</label>
            <input type="text" name="address2" class="form-control" id="inputAddress2" placeholder="Apartment, studio, or floor">
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="inputCity">City</label>
                <input type="text" name="city" class="form-control" id="inputCity" required>
            </div>
            <div class="form-group col-md-4">
                <label for="inputState">State</label>
                <select  name = "state" id="inputState" class="form-control" required>
                    <option value="AL">Alabama</option>
                    <option value="AK">Alaska</option>
                    <option value="AZ">Arizona</option>
                    <option value="AR">Arkansas</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="CT">Connecticut</option>
                    <option value="DE">Delaware</option>
                    <option value="DC">District Of Columbia</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="ME">Maine</option>
                    <option value="MD">Maryland</option>
                    <option value="MA">Massachusetts</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MS">Mississippi</option>
                    <option value="MO">Missouri</option>
                    <option value="MT">Montana</option>
                    <option value="NE">Nebraska</option>
                    <option value="NV">Nevada</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NM">New Mexico</option>
                    <option value="NY">New York</option>
                    <option value="NC">North Carolina</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="OK">Oklahoma</option>
                    <option value="OR">Oregon</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="SC">South Carolina</option>
                    <option value="SD">South Dakota</option>
                    <option value="TN">Tennessee</option>
                    <option value="TX">Texas</option>
                    <option value="UT">Utah</option>
                    <option value="VT">Vermont</option>
                    <option value="VA">Virginia</option>
                    <option value="WA">Washington</option>
                    <option value="WV">West Virginia</option>
                    <option value="WI">Wisconsin</option>
                    <option value="WY">Wyoming</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="inputZip">Zip</label>
                <input type="text" name="zip" class="form-control" id="inputZip" required>
            </div>
        </div>
        <h1> PAYMENT METHOD </h1>
        <div class="form-group">
            <select name = "payment" id="PAYMENT" class="form-control" required>
                <option value="visa">Visa</option>
                <option value="discover">Discover</option>
                <option value="mastercard">MasterCard</option>
                <option value="AMEX">AMEX</option>
                <option value="cash">Cash</option>
            </select>
        </div>
        <br>
        <input id="placeOrder" type="submit" name="submit" value="Submit" />
        <br>
    </form>

<?php require_once(__DIR__ . "/partials/flash.php"); ?>
