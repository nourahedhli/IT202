<?php
//since API is 100% server, we won't include navbar or flash
require_once(__DIR__ . "/../lib/helpers.php");
if (!is_logged_in()) {
    die(header(':', true, 403));
}
$testing = false;
if (isset($_GET["test"])) {
    $testing = true;
}


$cost = calcNextProductCost();
if ($cost < 0) {
    $response = ["status" => 400, "error" => "Error calculating cost"];
    echo json_encode($response);
    die();
}
if ($cost > getBalance()) {
    $response = ["status" => 400, "error" => "You can't afford this right now"];
    echo json_encode($response);
    die();
}
//super secret egg-generator
$product = [
    "name" => "Product",
    "price" => "price",
    "quantity" => "quantity",
    "user_id" => get_user_id()
];
//since this value depends on mod_min we can't quite initialized it all at once
$product["mod_max"] = mt_rand($product["mod_min"], 20);
$total=0;
//https://www.w3schools.com/php/func_math_mt_rand.asp
$total = $product["price"] + $total;
$max = 45;
$percent = $total / $max;
//TODO egg base_rate, mod min/max should increase the time of hatching
//Incubator stats should reduce time of hatching
//$eggTypes = ["Ancient", "Legendary", "Rare", "Uncommon", "Common"];


//https://www.delftstack.com/howto/php/how-to-add-days-to-date-in-php/
//https://stackoverflow.com/a/1286272



$nst = new DateTime();
$nst->add(new DateInterval('P1D'));
$nst = $nst->format("Y-m-d H:i:s");


$product["created"] = $nst;
if (!$testing) {
    $db = getDB();
    $stmt = $db->prepare("SELECT MAX(id) as max from Products");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max = (int)$result["max"];
    $max++;
    $product["name"] .= " #$max";//forgot name was unique so just appending the "expected" id
    $stmt = $db->prepare("INSERT INTO Products (name, quantity, price,category, description, modified, created, user_id) VALUES(:name, :quantity, :price, :description,:modified,:created,:user)");
    $r = $stmt->execute([
        ":name" => $product["name"],
        ":quantity" => $product["quantity"],
        ":price" => $product["price"],
        ":category" => $product["category"],
        ":description" => $product["description"],
        ":modified" => $product["modified"],
        ":created" => $product["created"],
        ":user" => $product["user_id"]
    ]);
    if ($r) {
        $response = ["product" => $product];
        echo json_encode($response);
        die();
    }
    else {
        $e = $stmt->errorInfo();
        $response = [ "error" => $e];
        echo json_encode($response);
        die();
    }
}
else {
    echo "<pre>" . var_export($product, true) . "</pre>";
}

?>

