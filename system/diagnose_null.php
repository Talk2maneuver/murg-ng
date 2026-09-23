<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "Checking for orders with empty or 0 orderID...\n";
$res = mysqli_query($con, "SELECT COUNT(*) as cnt FROM orders WHERE orderID='' OR orderID='0' OR orderID IS NULL");
$row = mysqli_fetch_assoc($res);
echo "Orphan orders: " . $row['cnt'] . "\n";
if($row['cnt'] > 0) {
    echo "Sample data:\n";
    $res = mysqli_query($con, "SELECT item, creation FROM orders WHERE orderID='' OR orderID='0' OR orderID IS NULL LIMIT 5");
    while($row = mysqli_fetch_assoc($res)) {
        echo " - Item: {$row['item']} added on {$row['creation']}\n";
    }
}
?>
