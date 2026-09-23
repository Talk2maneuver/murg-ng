<?php
include('assets/mashaAllah/gyada.php');

echo "--- Branch Table ---\n";
$res = mysqli_query($con, "SELECT * FROM branch");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}

echo "\n--- Facility Table ---\n";
$res = mysqli_query($con, "SELECT * FROM facility WHERE name LIKE '%Alh Yasir%' OR facilityID LIKE '%MURG%'");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
