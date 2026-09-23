<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE);
$did = intval($_GET['customerid']);
include('../assets/mashaAllah/gyada.php');

if (strlen($_SESSION['email']) == 0) {
    header('location:../index.php');
    exit();
}

// Add to cart
if (isset($_POST['cart'])) {
    $price = floatval($_POST['available']);
    $stockId = intval($_POST['speciality']);
    $quantity = intval($_POST['quantity']);
    $subtotal = $price * $quantity;
    $staffID = $_SESSION['id'];
    $facilityID = $_SESSION['facilityID'];
    
    // Fetch customer name
    $order_query = $con->query("SELECT name FROM customers WHERE id='$did'");
    $z_row = $order_query->fetch_array();
    $name = $z_row['name'];

    // Check stock
    $stock_query = mysqli_query($con, "SELECT quantity, name FROM stocks WHERE id='$stockId' AND facilityID='$facilityID'");
    $stock_row = mysqli_fetch_assoc($stock_query);
    $available_stock = $stock_row['quantity'];
    $item = $stock_row['name'];

    if ($quantity > $available_stock) {
        echo "<script>alert('Requested quantity exceeds available stock! Only $available_stock available.');</script>";
    } else {
        $sql = mysqli_query($con, "INSERT INTO debt_cart(facilityID,customerID,staffID,stockID,name,item,price,quantity,subtotal,status) 
                                 VALUES('$facilityID','$did','$staffID','$stockId','$name','$item','$price','$quantity','$subtotal','0')");
        
        if ($sql) {
            // Update stock: decrement quantity and increment out_stocks
            $new_quantity = $available_stock - $quantity;
            mysqli_query($con, "UPDATE stocks SET quantity='$new_quantity', out_stocks = out_stocks + '$quantity' WHERE id='$stockId' AND facilityID='$facilityID'");
            
            echo "<script>window.location.href='cart?customerid=$did'</script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again');</script>";
        }
    }
}

// Delete from cart
if (isset($_GET['del'])) {
    $cart_id = intval($_GET['id']);
    $order_query = $con->query("SELECT * FROM debt_cart WHERE id='$cart_id'");
    $o_row = $order_query->fetch_array();
    
    $quantity = $o_row['quantity'];
    $item = $o_row['item'];
    $facilityID = $_SESSION['facilityID'];
    
    // Restore stock and decrement out_stocks
    $stock_id = $o_row['stockID'];
    mysqli_query($con, "UPDATE stocks SET quantity = quantity + '$quantity', out_stocks = out_stocks - '$quantity' WHERE id='$stock_id' AND facilityID='$facilityID'");
    mysqli_query($con, "DELETE FROM debt_cart WHERE id='$cart_id'");
    
    echo "<script>window.location.href='cart?customerid=$did'</script>";
}

$order_query = $con->query("SELECT name FROM customers WHERE id='$did'");
$q_row = $order_query->fetch_array();
$customer_name = $q_row['name'];

// Get current dynamic balance for credit info
$facilityID = $_SESSION['facilityID'];
$sales_q = mysqli_query($con, "SELECT SUM(CAST(subtotal AS DECIMAL(10,2))) as total_sales FROM orders WHERE customerID='$did' AND facilityID='$facilityID'");
$sales_d = mysqli_fetch_array($sales_q);
$total_sales = $sales_d['total_sales'] ?? 0;

$disc_q = mysqli_query($con, "SELECT SUM(CAST(discount AS DECIMAL(10,2))) as total_discount FROM (SELECT orderID, discount FROM orders WHERE customerID='$did' AND facilityID='$facilityID' GROUP BY orderID) as t");
$disc_d = mysqli_fetch_array($disc_q);
$total_discount = $disc_d['total_discount'] ?? 0;

$init_p_q = mysqli_query($con, "SELECT SUM(CAST(amount_paid AS DECIMAL(10,2))) as total_initial_paid FROM (SELECT orderID, amount_paid FROM orders WHERE customerID='$did' AND facilityID='$facilityID' GROUP BY orderID) as t");
$init_p_d = mysqli_fetch_array($init_p_q);
$total_initial_paid = $init_p_d['total_initial_paid'] ?? 0;

$dep_q = mysqli_query($con, "SELECT SUM(amount) as total FROM deposit_history WHERE customerID='$did'");
$dep_d = mysqli_fetch_array($dep_q);
$actual_total_deposits = $dep_d['total'] ?? 0;

$current_full_balance = $total_sales - $total_discount - $total_initial_paid - $actual_total_deposits;

// Checkout process
if (isset($_POST['checkout'])) {
    $facilityID = $_SESSION['facilityID'];
    $staffID = $_SESSION['id'];
    $staff = $_SESSION['name'];
    $orderID = rand(00000, 99999);
    $_SESSION['orderID'] = $orderID;
    
    $isCreditOrder = true; // Since we're in debt_cart context
    $customerID = $did;
    $customer_name = null;
    
    // Fetch customer details
    $order_query = $con->query("SELECT name FROM customers WHERE id='$customerID'");
    if ($order_query->num_rows > 0) {
        $q_row = $order_query->fetch_array();
        $customer_name = $q_row['name'];
    } else {
        echo "<script>alert('Invalid customer ID!'); window.location.href='order';</script>";
        exit();
    }
    
    // Calculate totals
    $total_query = $con->query("SELECT SUM(subtotal) as total FROM debt_cart WHERE staffID='$staffID' AND facilityID='$facilityID'");
    $total_row = $total_query->fetch_array();
    $total = floatval($total_row['total'] ?? 0);
    $discount = floatval($_POST['discount'] ?? 0);
    $net_total = $total - $discount;
    
    // Payment details
    $cash_amount = floatval($_POST['cash_amount'] ?? 0);
    $pos_amount = floatval($_POST['pos_amount'] ?? 0);
    $transfer_amount = floatval($_POST['transfer_amount'] ?? 0);
    $bank_name = $_POST['bank_name'] ?? null;
    $amount_paid = $cash_amount + $pos_amount + $transfer_amount;
    $payment_type = ($amount_paid >= $net_total) ? 'Split Payment' : 'Credit';
    
    // Determine if order is paid (either by new payment OR existing credit)
    $is_fully_paid = ($amount_paid >= $net_total || ($current_full_balance + $net_total <= 0)) ? 1 : 0;
    
    // Validate payment for non-credit orders (not applicable here as all are treated as credit/debt initially, but keeping for logic)
    if ($amount_paid < $net_total && !$isCreditOrder && $current_full_balance + $net_total > 0) {
        echo "<script>alert('Total payments (₦".number_format($amount_paid).") is less than net total (₦".number_format($net_total).")!'); window.location.href='cart?customerid=$did';</script>";
        exit();
    }
    
    $change_given = ($cash_amount > ($net_total - $pos_amount - $transfer_amount)) ? 
        ($cash_amount - ($net_total - $pos_amount - $transfer_amount)) : 0;
    
    // Insert order
    $sql = "INSERT INTO orders (
        facilityID, staffID, customerID, customer_name, item, price, quantity, subtotal,
        staff, payment, orderID, discount, amount_paid, change_given, net_total, bank_name,
        cash, pos, transfer, creation, status
    ) SELECT 
        facilityID, staffID, '$customerID', '$customer_name', item, price, quantity, subtotal,
        '$staff', '$payment_type', '$orderID', '$discount', '$amount_paid', '$change_given', '$net_total',
        " . ($bank_name ? "'$bank_name'" : "NULL") . ",
        '$cash_amount', '$pos_amount', '$transfer_amount', NOW(), $is_fully_paid
    FROM debt_cart WHERE staffID='$staffID' AND facilityID='$facilityID'";
    
    $order_result = mysqli_query($con, $sql);
    
    if ($order_result) {
        // Handle outstanding balance
        $balance = $net_total - $amount_paid;
        
        if ($balance > 0) {
            $outstand_query = $con->query("SELECT amount, balance FROM outstand WHERE customerID='$customerID' AND facilityID='$facilityID'");
            
            if ($outstand_query->num_rows > 0) {
                $row = $outstand_query->fetch_assoc();
                $new_amount = floatval($row['amount']) + $amount_paid;
                $new_balance = floatval($row['balance']) + $balance;
                
                mysqli_query($con, "UPDATE outstand SET amount='$new_amount', balance='$new_balance' 
                                  WHERE customerID='$customerID'");
            } else {
                mysqli_query($con, "INSERT INTO outstand(facilityID, customerID, staffID, Customer, staff, amount, balance)
                                  VALUES ('$facilityID', '$customerID', '$staffID', '$customer_name', '$staff', '$amount_paid', '$balance')");
            }
        } else {
            // Deduct from credit if balance is negative or zero (surplus utilized)
            mysqli_query($con, "UPDATE outstand SET balance = balance + $balance WHERE customerID='$customerID'");
        }
        
        // Clear cart
        mysqli_query($con, "DELETE FROM debt_cart WHERE staffID='$staffID' AND facilityID='$facilityID'");
        
        echo "<script>
            window.open('invoice?id=$staffID&orderid=$orderID');
            window.location.href='credit';
        </script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again'); window.location.href='cart?customerid=$did';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MURG TEXTILE ENTERPRISES</title>
    <link href="../assets/img/murglogo.jpg" rel="shortcut icon">
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,500,600,700&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/plugins.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/users/user-profile.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="../plugins/select2/select2.min.css">
    
    <script>
        function getdoctor(val) {
            $.ajax({
                type: "POST",
                url: "get_price",
                data: 'specilizationid=' + val,
                success: function(data) {
                    $("#available").html(data);
                }
            });
        }
    </script>
</head>
<body class="sidebar-noneoverflow">
    <div id="load_screen">
        <div class="loader">
            <div class="loader-content">
                <div class="spinner-grow align-self-center"></div>
            </div>
        </div>
    </div>
    
    <?php include('header.php'); ?>
    <!--  BEGIN MAIN CONTAINER  -->
    <div class="main-container" id="container">
        <div class="overlay"></div>
        <div class="search-overlay"></div>
        <!--  BEGIN SIDEBAR  -->
        <?php include('sidebar.php'); ?>
        <!--  END SIDEBAR  -->
        
        <!--  BEGIN CONTENT AREA  -->
        <div id="content" class="main-content">
            <div class="layout-px-spacing">
              <div class="row layout-spacing">
                  <div class="col-xl-6 col-lg-6 col-md-5 col-sm-12 layout-top-spacing">
                      <div class="skills layout-spacing">
                          <div class="widget-content widget-content-area">
                              <h3>Credit Order</h3>
                              <form method="POST">
                                  <div class="form-group">
                                      <label>Stock Name</label>
                                      <span style="color:red">*</span>
                                      <select class="form-control basic" required name="speciality" onchange="getdoctor(this.value)">
                                          <option value="">Select from list</option>
                                          <?php
                                          $facilityID = $_SESSION['facilityID'];
                                          $ret = mysqli_query($con, "SELECT * FROM orders stocks WHERE quantity > 0 AND facilityID='$facilityID'");
                                          while ($row = mysqli_fetch_array($ret)) {
                                            ?>
                                                <option value="<?php echo htmlentities($row['id']); ?>"><?php echo htmlentities($row['name']); ?></option>
                                            <?php } ?>
                                      </select>
                                  </div>
                                  <div class="form-group">
                                      <label>Stock price</label>
                                      <span style="color:red">*</span>
                                      <select class="form-control select2" style="width: 100%;" required name="available" id="available">
                                      </select>
                                  </div>
                                  <div class="form-group">
                                      <label>Quantity</label>
                                      <span style="color:red">*</span>
                                      <input type="number" class="form-control" required name="quantity" value="1">
                                  </div>
                                  <div class="modal-footer">
                                      <button type="submit" class="btn btn-primary" name="cart">Add To Cart</button>
                                  </div>
                              </form>
                          </div>
                      </div>
                  </div>

                  <div class="col-xl-6 col-lg-6 col-md-5 col-sm-12 layout-top-spacing">
                      <div class="skills layout-spacing">
                          <div class="widget-content widget-content-area">
                              <h3>Cart</h3>
                              <form method="POST">
                                  <div class="table-responsive">
                                      <table id="sample-table-1" class="table table-hover">
                                          <thead>
                                              <tr>
                                                  <th>S/N</th>
                                                  <th>Item</th>
                                                  <th>Price</th>
                                                  <th>Quantity</th>
                                                  <th>Subtotal</th>
                                                  <th>Action</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                              <?php
                                              $staffID = $_SESSION['id'];
                                              $facilityID = $_SESSION['facilityID'];
                                              $sql = mysqli_query($con, "SELECT * FROM debt_cart WHERE customerID='$did' AND staffID='$staffID' AND facilityID='$facilityID'");
                                              $cnt = 1;
                                              while ($row = mysqli_fetch_array($sql)) {
                                              ?>
                                                  <tr>
                                                      <td><?php echo $cnt; ?>.</td>
                                                      <td><?php echo htmlspecialchars($row['item']); ?></td>
                                                      <td><?php echo number_format($row['price'], 2); ?></td>
                                                      <td><?php echo $row['quantity']; ?></td>
                                                      <td><?php echo number_format($row['subtotal'], 2); ?></td>
                                                      <td>
                                                          <a href="cart?customerid=<?php echo $did ?>&id=<?php echo $row['id'] ?>&del=delete" 
                                                            onclick="return confirm('Are you sure you want to delete?')"
                                                            class="btn btn-danger btn-xs">Remove</a>
                                                      </td>
                                                  </tr>
                                              <?php
                                                  $cnt++;
                                              }
                                              ?>
                                          </tbody>
                                      </table>
                                  </div>

                                  <?php
                                  $total_query = $con->query("SELECT SUM(subtotal) as total FROM debt_cart WHERE staffID='$staffID' AND facilityID='$facilityID'");
                                  $total_row = $total_query->fetch_array();
                                  $total = floatval($total_row['total'] ?? 0);
                                  ?>
                                  <h2>Total Price: ₦<?php echo number_format($total, 2); ?></h2>

                                  <div class="form-group">
                                      <label>Customer Name</label>
                                      <span style="color:red">*</span>
                                      <input type="text" class="form-control" required name="customer_name" value="<?php echo htmlspecialchars($customer_name); ?>" readonly>
                                      <?php if($current_full_balance < 0): ?>
                                      <div class="alert alert-success mt-2">
                                          Available Credit: <strong>₦<?php echo number_format(abs($current_full_balance), 2); ?></strong>
                                          <br><small>This credit will be automatically applied to the order balance.</small>
                                      </div>
                                      <?php endif; ?>
                                  </div>
                                  
                                  <div class="form-group">
                                      <label>Discount</label>
                                      <span style="color:red">*</span>
                                      <input type="number" class="form-control" required name="discount" value="0" id="discount" oninput="calculatePayments()">
                                  </div>
                                  
                                  <h4>Payment Methods</h4>
                                  
                                  <div class="row">
                                      <div class="col-md-6 payment-method">
                                          <div class="form-group">
                                              <label>Cash</label>
                                              <input type="number" class="form-control" name="cash_amount" id="cash_amount" value="0" oninput="calculatePayments()">
                                          </div>
                                      </div>
                                      
                                      <div class="col-md-6 payment-method">
                                          <div class="form-group">
                                              <label>POS</label>
                                              <input type="number" class="form-control" name="pos_amount" id="pos_amount" value="0" oninput="calculatePayments()">
                                          </div>
                                      </div>
                                  </div>
                                  
                                  <div class="payment-method">
                                      <div class="form-group">
                                          <label>Bank Transfer</label>
                                          <input type="number" class="form-control" name="transfer_amount" id="transfer_amount" value="0" oninput="calculatePayments()">
                                      </div>
                                      <div class="form-group">
                                          <label>Bank Name</label>
                                          <input type="text" class="form-control" name="bank_name" id="bank_name">
                                      </div>
                                  </div>
                                  
                                  <div class="summary-box">
                                      <div class="form-group">
                                          <label>Subtotal</label>
                                          <input type="number" class="form-control" id="subtotal" value="<?php echo $total; ?>" readonly>
                                      </div>
                                      
                                      <div class="form-group">
                                          <label>Net Total (After Discount)</label>
                                          <input type="number" class="form-control" id="net_total" readonly>
                                      </div>
                                      
                                      <div class="form-group">
                                          <label>Total Paid</label>
                                          <input type="number" class="form-control" id="total_paid" readonly>
                                      </div>
                                      
                                      <div class="form-group">
                                          <label>Balance</label>
                                          <input type="number" class="form-control" id="balance" readonly>
                                      </div>
                                      
                                      <div class="form-group">
                                          <label>Change Due</label>
                                          <input type="number" class="form-control" name="change" id="change_due" readonly>
                                      </div>
                                  </div>
                                  
                                  <div class="modal-footer">
                                      <button type="submit" class="btn btn-primary" name="checkout">Check Out</button>
                                  </div>
                              </form>
                          </div>
                      </div>
                  </div>
              </div>
            </div>
            <?php include('footer.php'); ?>
        </div>
    </div>

    <script src="../assets/js/libs/jquery-3.1.1.min.js"></script>
    <script src="../bootstrap/js/popper.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/app.js"></script>
    <script>
        $(document).ready(function() {
            App.init();
        });
    </script>
    <script src="../assets/js/custom.js"></script>
    <script src="../plugins/select2/select2.min.js"></script>
    <script src="../plugins/select2/custom-select2.js"></script>
    <script>
        function calculatePayments() {
            var subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
            var discount = parseFloat(document.getElementById('discount').value) || 0;
            var cash = parseFloat(document.getElementById('cash_amount').value) || 0;
            var pos = parseFloat(document.getElementById('pos_amount').value) || 0;
            var transfer = parseFloat(document.getElementById('transfer_amount').value) || 0;
            
            var netTotal = subtotal - discount;
            document.getElementById('net_total').value = netTotal.toFixed(2);
            
            var totalPaid = cash + pos + transfer;
            document.getElementById('total_paid').value = totalPaid.toFixed(2);
            
            var balance = netTotal - totalPaid;
            var balanceField = document.getElementById('balance');
            balanceField.value = Math.abs(balance).toFixed(2);
            balanceField.className = 'form-control ' + (balance > 0 ? 'negative' : 'positive');
            
            var remainingAfterOtherPayments = netTotal - pos - transfer;
            var changeDue = (cash > remainingAfterOtherPayments) ? (cash - remainingAfterOtherPayments) : 0;
            document.getElementById('change_due').value = changeDue.toFixed(2);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            calculatePayments();
        });
    </script>
</body>
</html>





