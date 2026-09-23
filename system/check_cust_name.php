<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
$did = 6;
$res = mysqli_query($con, "SELECT name FROM customers WHERE id='$did'");
$row = mysqli_fetch_assoc($res);
echo "Customer 6 Name: " . $row['name'] . "\n";
?>
