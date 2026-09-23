<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
$name = 'MAN';
echo "ORDERS FOR BUYER NAME '$name':\n";
$res = mysqli_query($con, "SELECT id, customerID, orderID, item, subtotal, amount_paid FROM orders WHERE buyer_name='$name'");
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: {$row['id']} | CustID: {$row['customerID']} | OrderID: {$row['orderID']} | Item: {$row['item']} | Sub: {$row['subtotal']} | Paid: {$row['amount_paid']}\n";
}
?>
