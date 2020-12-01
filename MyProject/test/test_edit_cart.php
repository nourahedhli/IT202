<?php require_once(__DIR__ . "/../partials/nav.php"); ?>




<?php




if (!has_role("Admin")) {




    //this will redirect to login and kill the rest of this script (prevent it from executing)




    flash("You don't have permission to access this page");




    die(header("Location: login.php"));




}




?>




<?php




//we'll put this at the top so both php block have access to it




if(isset($_GET["id"])){




	$id = $_GET["id"];




}




?>




<?php




//saving




if(isset($_POST["save"])){




	//TODO add proper validation/checks




	$product_id = $_POST["product_id"];




	$quantity = $_POST["quantity"];




	$price = $_POST["price"];




	




$created = date('Y-m-d H:i:s');//calc	
$modified = date('Y-m-d H:i:s');




	




	$user = get_user_id();




	$db = getDB();




	if(isset($id)){




		$stmt = $db->prepare("UPDATE Cart set product_id=:product_id, quantity=:quantity, price=:price, created=:created, modified=:modified  where id=:id");




		//$stmt = $db->prepare("INSERT INTO Products (name, quantity, price, description, modified, created, user_id) VALUES(:name, :quantity, :br, :min,:max,:nst,:user)");




		$r = $stmt->execute([




			":product_id"=>$product_id,




			":quantity"=>$quantity,




			":price"=>$price,




			




":created"=>$created,			
":modified"=>$modified,




			




			":id"=>$id




		]);




		if($r){




			flash("Updated successfully with id: " . $id);




		}




		else{




			$e = $stmt->errorInfo();




			flash("Error updating: " . var_export($e, true));




		}




	}




	else{




		flash("ID isn't set, we need an ID in order to update");




	}




}




?>




<?php




//fetching




$result = [];




if(isset($id)){




	$id = $_GET["id"];




	$db = getDB();




	$stmt = $db->prepare("SELECT * FROM Cart where id = :id");




	$r = $stmt->execute([":id"=>$id]);




	$result = $stmt->fetch(PDO::FETCH_ASSOC);




}

// get products for dropdown 

$db = getDB();




$stmt = $db->prepare("SELECT id,name from Products LIMIT 10");




$r = $stmt->execute();




$products = $stmt->fetchAll(PDO::FETCH_ASSOC);





?>


<div class="container-fluid">




        <h3>Edit Cart</h3>




        <form method="POST">




            <div class="form-group">




                <label>Name</label>




                <input class="form-control" name="name" placeholder="Name" value="<?php echo $result["name"]; ?>"/>




            </div>




            <div class="form-group">




                <label>Product</label>




                <select class="form-control" name="product_id" value="<?php echo $result["product_id"]; ?>">




                    <option value="-1">None</option>




                    <?php foreach ($products as $product): ?>




                        <option value="<?php safer_echo($product["id"]); ?>" <?php echo($result["product_id"] == $egg["id"] ? 'selected="selected"' : ''); ?>




                        ><?php safer_echo($product["name"]); ?></option>




                    <?php endforeach; ?>




                </select>




            </div>




            <div class="form-group">




                <label>price</label>




                <input class="form-control" type="number" min="1" name="price"




value="<?php echo $result["price"]; ?>"/>




            </div>




            <input class="btn btn-primary" type="submit" name="save" value="Update"/>




        </form>




    </div>













































<?php require(__DIR__ . "/../partials/flash.php");

