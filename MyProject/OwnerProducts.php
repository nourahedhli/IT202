<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php

$results = [];
$cat = 0;
$total = 0;
$db = getDB();
$params = [];
$per_page = 5;
$cat = 0;
$page = 1;
$total_pages = ceil($total / $per_page);
$quantity = extractData("quantity");
$query = "SELECT id,name, price, description, quantity FROM Products";
$q  ="SELECT COUNT(*) as total FROM Products";

if (isset($quantity)) {
    $query .= " WHERE quantity <= :q";
    $params[":q"] = $quantity;
    $q  = "SELECT COUNT(*) as total FROM Products WHERE quantity <= :q";
}


$query .= " LIMIT :offset, :count";
echo $query;
paginate($q, $params, $per_page);
$offset = ($page - 1) * $per_page;
$stmt = $db->prepare($query);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
foreach ($params as $key=>$val){
    $stmt->bindValue($key, $val);
}
$r = $stmt->execute();


if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem fetching the products " . var_export($stmt->errorInfo(), true));
}




$stmt = $db->prepare("SELECT DISTINCT category  FROM Products ");
$r = $stmt->execute();
if ($r) {
    $category = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

    <form method="POST" style="float: right; margin-top: 3em; margin-right: 2em;" id = "form1">
        <label for="input">quantity check</label>
        <input type="input" name="quantity" class="form-control" id="quantity" aria-describedby="emailHelp" required>
        <button style= "margin-right: 2em;"type="submit" name="quantitycheck" value="quantitycheck"  class="btn btn-primary">submit</button>
    </form>





    <h1>Owner's List of Products</h1>
    <div class="row" style= "margin-left: 4em;">
        <?php if (count($results) > 0): ?>
            <?php foreach ($results as $r): ?>
                <div   class="card" style="width: 20rem; margin: 1em;">

                    <div class="card-body">
                        <a href = "RatingForm.php?id=<?php safer_echo($r['id']); ?>" <h5 class="card-title"><?php safer_echo($r["name"]); ?></h5></a>
                        <h6 class="card-title"> Price =<?php safer_echo($r["price"]); ?></h6>
                        <p class="card-text">Description =<?php safer_echo($r["description"]); ?></p>
                        <p class="card-text"> Quantity = <?php safer_echo($r["quantity"]); ?></p>
                        <?php if (isset($_POST["sort"])): ?>
                            <p class="card-text">Rating: <?php safer_echo($r["rating"]); ?></p>
                        <?php endif?>
                        <?php if (has_role("Admin")): ?>
                            <a href="test/test_edit_product.php?id=<?php safer_echo($r['id']); ?>" class="btn btn-primary">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <nav aria-label="bla">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
            </li>
            <?php for($i = 0; $i < $total_pages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
            </li>
        </ul>
    </nav>

<?php require_once(__DIR__ . "/partials/flash.php"); ?>
