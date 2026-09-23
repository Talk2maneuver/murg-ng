<?php
include('assets/mashaAllah/gyada.php');
$email = 'abdulmaleek@gmail.com';
$password = '12345';
$pass = md5($password);
$query = $con->query("SELECT * FROM facility where email='$email' And password='$pass'");
$row = $query->fetch_assoc();
echo "id: " . $row['id'] . "\n";
echo "name: " . $row['name'] . "\n";
echo "email: " . $row['email'] . "\n";
echo "facilityID: " . $row['facilityID'] . "\n";
?>
