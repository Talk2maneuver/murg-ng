<?php
include('assets/mashaAllah/gyada.php');
session_start();
echo "SESSION facilityID: " . ($_SESSION['facilityID'] ?? 'NOT SET') . "\n";

echo "\nMOST RECENT STOCKS:\n";
$res = mysqli_query($con, 'SELECT * FROM stocks ORDER BY id DESC LIMIT 5');
while($row = mysqli_fetch_assoc($res)) { print_r($row); }

echo "\nBRANCHES:\n";
$res = mysqli_query($con, 'SELECT * FROM branch');
while($row = mysqli_fetch_assoc($res)) { print_r($row); }
?>
