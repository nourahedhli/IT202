<?php require_once(__DIR__ . "/../partials/nav.php"); ?>

<?php

if (!has_role("Admin")) {

    //this will redirect to login and kill the rest of this script (prevent it from executing)

    flash("You don't have permission to access this page");

    die(header("Location: login.php"));

}

?>


    <div class=”container-fluid”>
        <h3> Create Cart </h3>
        <form method="POST">

            <div class=”form-group”>
               <label>Product Id </label>
              <input name="product_id" placeholder="product_id"/>
            </div>


            <div class=”form-group”>
                <label>Quantity</label>

                <input type="int" min="1" name="quantity"/>
           </div>

            <div class=”form-group”>
                <label>Price</label>

                <input type="decimal" min="1" name="price"/>

            </div>

            <input class=”btn btn-primary” type="submit" name="save" value="Create"/>




        </form>

    </div>

<?php


if(isset($_POST["save"])){

    //TODO add proper validation/checks

    $product_id = $_POST["product_id"];

    $quantity = $_POST["quantity"];

    $price = $_POST["price"];


    $created = date('Y-m-d H:i:s');//calc
    $modified = date('Y-m-d H:i:s');

    $user = get_user_id();

    $db = getDB();

    $stmt = $db->prepare("INSERT INTO Cart (product_id, quantity, price, created, modified, user_id) VALUES( :product_id, :quantity, :price, :created, :modified, :user)");

    $r = $stmt->execute([

        ":product_id "=>$product_id,


        ":quantity"=>$quantity,

        ":price"=>$price,

       
	":created"=>$created,
        ":modified"=>$modified,
        
        ":user_id"=>$user


	]);


	if($r){

        flash("Created successfully with id: " . $db->lastInsertId());

    }


    else{

        $e = $stmt->errorInfo();

        flash("Error creating: " . var_export($e, true));


    }


}

?>

<?php require(__DIR__ . "/../partials/flash.php");
