<?php
include('assets/mashaAllah/gyada.php');
echo "ALL FACILITY RECORDS:\n";
$res = mysqli_query($con, 'SELECT id, facilityID, name, email, role FROM facility');
while($row = mysqli_fetch_assoc($res)) { print_r($row); }
?>
