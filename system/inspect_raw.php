<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
$did = 6;
$res = mysqli_query($con, "SELECT subtotal, discount, amount_paid FROM orders WHERE customerID='$did'");
while($row = mysqli_fetch_assoc($res)) {
    var_dump($row['subtotal']);
    var_dump($row['discount']);
    var_dump($row['amount_paid']);
}
?>
