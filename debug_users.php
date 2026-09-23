<?php
include('assets/mashaAllah/gyada.php');

$query = $con->query("SELECT * FROM facility");
if ($query) {
    echo "Users in 'facility' table:\n";
    while ($row = $query->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Email: " . $row['email'] . " | Password (MD5): " . $row['password'] . " | Role: " . $row['role'] . " | FacilityID: " . $row['facilityID'] . " | Status: " . $row['status'] . "\n";
    }
} else {
    echo "Error querying 'facility' table: " . $con->error;
}
?>
