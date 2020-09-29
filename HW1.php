<?php
$arr = [1, 2, 4, 5, 8, 8, 2];


$count = count($arr);
echo "The array has $count elements \n";
foreach ($arr as $i => $num){
    if ($num %2==0){
      echo "$num \n"; 
 //using the foreach-loop to loop over each number and then using the if to test if it's even or not

    }
}
?>

