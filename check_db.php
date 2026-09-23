<?php
include('assets/mashaAllah/gyada.php');
echo "BRANCH TABLE:\n";
$res = mysqli_query($con, 'SELECT * FROM branch LIMIT 10');
while($row = mysqli_fetch_assoc($res)) { print_r($row); }

echo "\nFACILITY TABLE:\n";
$res = mysqli_query($con, 'SELECT * FROM facility LIMIT 10');
while($row = mysqli_fetch_assoc($res)) { print_r($row); }
?>
