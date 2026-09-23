<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
echo "TABLE: deposit_history\n";
$res = mysqli_query($con, 'DESCRIBE deposit_history');
while($row = mysqli_fetch_assoc($res)) { echo $row['Field'] . " - " . $row['Type'] . "\n"; }

echo "\nTABLE: outstand\n";
$res = mysqli_query($con, 'DESCRIBE outstand');
while($row = mysqli_fetch_assoc($res)) { echo $row['Field'] . " - " . $row['Type'] . "\n"; }
?>
