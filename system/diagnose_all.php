<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "Checking for ALL Duplicate OrderIDs...\n";
$res = mysqli_query($con, "SELECT orderID, COUNT(*) as cnt, MIN(DATE(creation)) as first, MAX(DATE(creation)) as last FROM orders GROUP BY orderID HAVING cnt > 1");
while($row = mysqli_fetch_assoc($res)) {
    // Check if these are from the same checkout (same time basically) or different times
    if(abs(strtotime($row['first']) - strtotime($row['last'])) > 3600*24) {
        echo "OrderID {$row['orderID']} shows up in both {$row['first']} and {$row['last']}.\n";
    }
}
?>
