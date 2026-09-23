<?php 
error_reporting(E_ALL); 
ini_set('display_errors', 1); 
include('../assets/mashaAllah/gyada.php'); 

echo "--- Credit Sales Samples ---\n";
$q=mysqli_query($con, "SELECT orderID, payment, buyer_name, customer_name, customerID FROM orders WHERE payment LIKE '%credit%' OR payment LIKE '%split%' LIMIT 10"); 
if (!$q) {
    echo mysqli_error($con);
} else {
    while($row = mysqli_fetch_assoc($q)) {
        print_r($row);
    }
}

echo "\n--- Customers Table Sample ---\n";
$q3=mysqli_query($con, "SELECT * FROM customers LIMIT 3");
if ($q3) {
    while($row = mysqli_fetch_assoc($q3)) {
        print_r($row);
    }
}
