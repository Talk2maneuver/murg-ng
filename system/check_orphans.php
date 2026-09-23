<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "ORDERS FOR 'MAN' WITHOUT customerID:\n";
$res = mysqli_query($con, "SELECT id, orderID, item, subtotal, amount_paid FROM orders WHERE customer_name='MAN' AND (customerID IS NULL OR customerID=0)");
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: {$row['id']} | OrderID: {$row['orderID']} | Item: {$row['item']} | Sub: {$row['subtotal']} | Paid: {$row['amount_paid']}\n";
}
?>
