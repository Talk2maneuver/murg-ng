<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "SAMPLE MULTI-ITEM ORDER:\n";
$res = mysqli_query($con, "SELECT orderID, COUNT(*) as items FROM orders GROUP BY orderID HAVING items > 1 LIMIT 5");
while($group = mysqli_fetch_assoc($res)) {
    $oid = $group['orderID'];
    echo "OrderID: $oid\n";
    $items = mysqli_query($con, "SELECT item, subtotal, discount, amount_paid FROM orders WHERE orderID='$oid'");
    while($row = mysqli_fetch_assoc($items)) {
        echo "  Item: {$row['item']} | Sub: {$row['subtotal']} | Disc: {$row['discount']} | Paid: {$row['amount_paid']}\n";
    }
}
?>
