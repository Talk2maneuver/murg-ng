<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
$oid = '15163';
$res = mysqli_query($con, "SELECT * FROM orders WHERE orderID='$oid'");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
