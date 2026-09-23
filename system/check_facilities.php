<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
$did = 6;
$res = mysqli_query($con, "SELECT facilityID, subtotal FROM orders WHERE customerID='$did'");
while($row = mysqli_fetch_assoc($res)) {
    echo "Facility: {$row['facilityID']} | Subtotal: {$row['subtotal']}\n";
}
?>
