<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "Checking cart for items without facilityID...\n";
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM cart WHERE facilityID='' OR facilityID IS NULL");
$row = mysqli_fetch_assoc($res);
echo "Orphan cart items: " . $row['cnt'] . "\n";
?>
