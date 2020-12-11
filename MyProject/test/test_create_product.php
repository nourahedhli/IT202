<?php require_once(__DIR__ . "/../partials/nav.php"); ?>

<?php

if (!has_role("Admin")) {

    //this will redirect to login and kill the rest of this script (prevent it from executing)

    flash("You don't have permission to access this page");

    die(header("Location: login.php"));

}

?>




<form method="POST">

	<label>Name</label>

	<input name="name" placeholder="Name"/>

	<label>Quantity</label>

	<input type="int" min="1" name="quantity"/>

	<label>Price</label>

	<input type="decimal" min="1" name="price"/>

	<label>Description</label>

	<input type="text" min="1" name="description"/>

    <label>Category</label>

    <input type="text" min="1" name="category"/>

	<input type="submit" name="save" value="Create"/>

</form>




<?php

if(isset($_POST["save"])){

	//TODO add proper validation/checks

	$name = $_POST["name"];

	$quantity = $_POST["quantity"];

	$price = $_POST["price"];

	$category = $_POST["category"];

	$description = $_POST["description"];


	$modified = date('Y-m-d H:i:s');

	$created = date('Y-m-d H:i:s');//calc

	$user = get_user_id();

	$db = getDB();

	$stmt = $db->prepare("INSERT INTO Products (name, quantity, price,category, description, modified, created, user_id) VALUES(:name, :quantity, :price, :category, :description, :modified, :created,:user)");

	$r = $stmt->execute([

		":name"=>$name,

		":quantity"=>$quantity,

		":price"=>$price,
		":category"=>$category,

		":description"=>$description,

		"modified"=>$modified,

		":created"=>$created,

		":user"=>$user

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
