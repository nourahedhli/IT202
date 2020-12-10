<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$per_page = 10;

$db = getDB();
$query = "SELECT count(*) as total from Products e LEFT JOIN Cart i on e.id = i.product_id where e.user_id = :id";
$params = [":id"=>get_user_id()];
paginate($query, $params, $per_page);
/

$stmt = $db->prepare("SELECT e.*, i.name as inc from Products e LEFT JOIN Cart i on e.id = i.product_id where e.user_id = :id LIMIT :offset, :count");
//need to use bindValue to tell PDO to create these as ints
//otherwise it fails when being converted to strings (the default behavior)
//$offset is defined in paginate(), safe to ignore IDE error
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", get_user_id());
$stmt->execute();
$e = $stmt->errorInfo();
if($e[0] != "00000"){
    flash(var_export($e, true), "alert");
}
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<?php

$queryString = null;
$cat= null;
$items = [];
$param=[];
$selectedCat='';


// load query string
if (isset($_POST["query"])) {

    $queryString = $_POST["query"];
    $_SESSION["query"] = $a;

}
else if (isset($_SESSION["query"])){

    $queryString=$_SESSION["query"];


}

//load category

if (isset($_POST["cat"])) {

    $queryString = $_POST["cat"];
    $_SESSION["cat"] = $a;

}
else if (isset($_SESSION["cat"])){

    $queryString=$_SESSION["cat"];


}
$query = "SELECT name, id,price,category,quantity,description, user_id from Products WHERE 1 = 1";


$db = getDB();

    if (isset($queryString)){
        $query .= " And name like :q";
        $param[":q"]= "%$queryString%";

    }
if (isset($cat)){
    $query .= " And category like :q";
    $param[":cat"]= "$cat";

}

$stmt = $db->prepare($query);
$r = $stmt->execute($param);



?>
    <script>
        //php will exec first so just the value will be visible on js side



        function addToCart(itemId, cost){

            //https://www.w3schools.com/xml/ajax_xmlhttprequest_send.asp
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    let json = JSON.parse(this.responseText);
                    if (json) {
                        if (json.status == 200) {
                            alert(json.message);
                        } else {
                            alert(json.error);
                        }
                    }
                }
            };
            xhttp.open("POST", "<?php echo getURL("api/add_to_cart.php");?>", true);
            //this is required for post ajax calls to submit it as a form
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            //map any key/value data similar to query params
            xhttp.send("itemId="+itemId);
        }
    </script>

    <div class="container-fluid">
        <?php foreach($items as $item):?>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title">
                            <?php echo $item["name"];?>
                        </div>
                        <div class="card-text">
                            <?php echo $item["description"];?>
                        </div>
                        <div class="card-footer">
                            <button type="button" onclick="addToCart(<?php echo $item["id"];?>,<?php echo $item["price"];?>);" class="btn btn-primary btn-lg">Add to Cart
                                (Cost: <?php echo $item["price"]; ?>)
                            </button>
                        </div>
                    </div>
                </div>
                <?php include(__DIR__."/partials/pagination.php");?>
            </div>
        <?php endforeach;?>










    </div>
<?php require(__DIR__ . "/partials/flash.php");
