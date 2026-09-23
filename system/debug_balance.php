<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
$did = 6;
echo "ORDERS FOR CUSTOMER $did:\n";
$res = mysqli_query($con, "SELECT id, orderID, item, subtotal, discount, amount_paid FROM orders WHERE customerID='$did'");
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: {$row['id']} | OrderID: {$row['orderID']} | Item: {$row['item']} | Sub: {$row['subtotal']} | Disc: {$row['discount']} | Paid: {$row['amount_paid']}\n";
}

echo "\nDEPOSITS FOR CUSTOMER $did:\n";
$res = mysqli_query($con, "SELECT id, amount FROM deposit_history WHERE customerID='$did'");
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: {$row['id']} | Amount: {$row['amount']}\n";
}

echo "\nOUTSTAND TABLE FOR CUSTOMER $did:\n";
$res = mysqli_query($con, "SELECT * FROM outstand WHERE customerID='$did'");
while($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
    print_r($row);
}
?>
