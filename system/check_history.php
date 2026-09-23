<?php
include('c:/xampp/htdocs/dansarki/assets/mashaAllah/gyada.php');
$did = 6;
echo "DEPOSIT HISTORY FOR CUSTOMER $did:\n";
$res = mysqli_query($con, "SELECT id, deposit_date, amount, previous_balance, new_balance FROM deposit_history WHERE customerID='$did' ORDER BY deposit_date ASC");
while($row = mysqli_fetch_assoc($res)) {
    echo "ID: {$row['id']} | Date: {$row['deposit_date']} | Amt: {$row['amount']} | Prev: {$row['previous_balance']} | New: {$row['new_balance']}\n";
}
?>
