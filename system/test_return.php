<?php 
error_reporting(E_ALL); 
ini_set('display_errors', 1); 
include('../assets/mashaAllah/gyada.php'); 

$orderID = '177616256769';
echo "--- Items in Order $orderID ---\n";
$q = mysqli_query($con, "SELECT * FROM orders WHERE orderID='$orderID'");
while($row = mysqli_fetch_assoc($q)) {
    print_r($row);
}
