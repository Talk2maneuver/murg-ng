<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "TABLE: orders\n";
$res = mysqli_query($con, 'DESCRIBE orders');
while($row = mysqli_fetch_assoc($res)) { echo $row['Field'] . " - " . $row['Type'] . "\n"; }
?>
