<?php
include('assets/mashaAllah/gyada.php');
echo "CUSTOMERS RECORDS:\n";
$res = mysqli_query($con, 'SELECT * FROM customers LIMIT 10');
while($row = mysqli_fetch_assoc($res)) { print_r($row); }

echo "\nSTOCKS RECORDS:\n";
$res = mysqli_query($con, 'SELECT * FROM stocks LIMIT 10');
while($row = mysqli_fetch_assoc($res)) { print_r($row); }
?>
