<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "TABLE: cart\n";
$res = mysqli_query($con, 'DESCRIBE cart');
while($row = mysqli_fetch_assoc($res)) { echo $row['Field'] . " - " . $row['Type'] . "\n"; }
?>
