<?php
include('../assets/mashaAllah/gyada.php');
$res = mysqli_query($con, 'DESCRIBE stocks');
while($row = mysqli_fetch_assoc($res)) {
    echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "\n";
}
?>
