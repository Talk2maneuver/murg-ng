<?php
include('assets/mashaAllah/gyada.php');
echo "BRANCH TABLE:\n";
$res = $con->query('SELECT * FROM branch');
while($row = $res->fetch_assoc()) {
    print_r($row);
}
echo "\nFACILITY TABLE:\n";
$res = $con->query('SELECT * FROM facility');
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
