<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
$did = 6;
$res = mysqli_query($con, "SELECT COUNT(*) as count FROM outstand WHERE customerID='$did'");
$row = mysqli_fetch_assoc($res);
echo "Outstand records for customer 6: " . $row['count'] . "\n";
?>
