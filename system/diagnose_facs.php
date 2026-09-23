<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "Checking for OrderID across facilities...\n";
$res = mysqli_query($con, "SELECT orderID, COUNT(DISTINCT facilityID) as facs FROM orders GROUP BY orderID HAVING facs > 1");
while($row = mysqli_fetch_assoc($res)) {
    echo "OrderID {$row['orderID']} exists in {$row['facs']} different facilities.\n";
}
?>
