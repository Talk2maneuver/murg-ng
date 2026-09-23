<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "Checking for OrderID collisions...\n";
$res = mysqli_query($con, "SELECT orderID, COUNT(DISTINCT DATE(creation)) as dates FROM orders GROUP BY orderID HAVING dates > 1");
if (mysqli_num_rows($res) > 0) {
    while($row = mysqli_fetch_assoc($res)) {
        echo "OrderID {$row['orderID']} exists on {$row['dates']} different dates.\n";
    }
} else {
    echo "No OrderID collisions found (between different dates).\n";
}

echo "\nChecking for Cart persistence issues...\n";
$res = mysqli_query($con, "SELECT staffID, COUNT(*) as cnt FROM cart GROUP BY staffID");
while($row = mysqli_fetch_assoc($res)) {
    echo "StaffID {$row['staffID']} has {$row['cnt']} orphan items in cart.\n";
}
?>
