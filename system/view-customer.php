<?php
session_start();
// error_reporting(0);
$did = intval($_GET['id']);
include('../assets/mashaAllah/gyada.php');
if (strlen($_SESSION['email']) == 0) {
    header('location:../index.php');
}

// Get customer information
$customer_query = mysqli_query($con, "SELECT * FROM customers WHERE id='$did' LIMIT 1");
$customer_data = mysqli_fetch_array($customer_query);

// Get outstanding balance information
$outstanding_query = mysqli_query($con, "SELECT * FROM outstand WHERE customerID='$did' LIMIT 1");
$outstanding_data = mysqli_fetch_array($outstanding_query);

// Calculate correct total deposits from history
$total_deposits_query = mysqli_query($con, "SELECT SUM(amount) as total FROM deposit_history WHERE customerID='$did'");
$total_deposits_data = mysqli_fetch_array($total_deposits_query);
$actual_total_deposits = $total_deposits_data['total'] ?? 0;

// Date range filter logic
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$active_tab = $_GET['tab'] ?? 'orders'; // Default to orders tab
$date_filter_orders = "";
$date_filter_deposits = "";
if (!empty($from_date) && !empty($to_date)) {
    $date_filter_orders = " AND DATE(creation) BETWEEN '$from_date' AND '$to_date' ";
    $date_filter_deposits = " AND DATE(deposit_date) BETWEEN '$from_date' AND '$to_date' ";
}

// Calculate accurate outstanding balance
$facilityID = $_SESSION['facilityID'];

// 1. Total Subtotal (Gross) considering unit discount
$sales_query = mysqli_query($con, "SELECT SUM(CAST(subtotal AS DECIMAL(10,2)) - (CAST(item_discount AS DECIMAL(10,2)) * CAST(quantity AS SIGNED))) as total_sales FROM orders WHERE customerID='$did' AND facilityID='$facilityID'");
$sales_data = mysqli_fetch_array($sales_query);
$total_sales = $sales_data['total_sales'] ?? 0;

// 2. Total Discounts (at order level) for current facility
$discount_query = mysqli_query($con, "SELECT SUM(CAST(discount AS DECIMAL(10,2))) as total_discount FROM (SELECT orderID, discount FROM orders WHERE customerID='$did' AND facilityID='$facilityID' GROUP BY orderID) as t");
$discount_data = mysqli_fetch_array($discount_query);
$total_discount = $discount_data['total_discount'] ?? 0;

// 3. Total Initial Payments (at order level) for current facility
$initial_payment_query = mysqli_query($con, "SELECT SUM(CAST(amount_paid AS DECIMAL(10,2))) as total_initial_paid FROM (SELECT orderID, amount_paid FROM orders WHERE customerID='$did' AND facilityID='$facilityID' GROUP BY orderID) as t");
$initial_payment_data = mysqli_fetch_array($initial_payment_query);
$total_initial_paid = $initial_payment_data['total_initial_paid'] ?? 0;

// 4. Actual Outstanding Balance
$actual_balance = $total_sales - $total_discount - $total_initial_paid - $actual_total_deposits;

// Delete order item logic
if(isset($_GET['del_order'])) {
    $item_id = intval($_GET['item_id']);
    $item_query = mysqli_query($con, "SELECT * FROM orders WHERE id='$item_id'");
    if($row = mysqli_fetch_array($item_query)) {
        $subtotal = floatval($row['subtotal']);
        $quantity = intval($row['quantity']);
        $item_name = $row['item'];
        $facilityID = $row['facilityID'];
        $custID = $row['customerID'];
        
        // Update outstand balance (debt decreases by subtotal of removed item)
        mysqli_query($con, "UPDATE outstand SET balance = balance - $subtotal WHERE customerID='$custID'");
        
        // Restore stock
        mysqli_query($con, "UPDATE stocks SET quantity = quantity + $quantity WHERE name='$item_name' AND facilityID='$facilityID'");
        
        // Delete the item
        mysqli_query($con, "DELETE FROM orders WHERE id='$item_id'");
        
        echo "<script>alert('Order item deleted successfully'); window.location.href='view-customer?id=$did';</script>";
        exit;
    }
}

// Delete deposit logic
if(isset($_GET['del_dep'])) {
    $dep_id = intval($_GET['dep_id']);
    $dep_query = mysqli_query($con, "SELECT * FROM deposit_history WHERE id='$dep_id'");
    if($row = mysqli_fetch_array($dep_query)) {
        $amount = floatval($row['amount']);
        $custID = $row['customerID'];
        
        // Update outstand (Total deposits decrease, balance increases)
        mysqli_query($con, "UPDATE outstand SET amount = amount - $amount, balance = balance + $amount WHERE customerID='$custID'");
        
        // Delete the deposit
        mysqli_query($con, "DELETE FROM deposit_history WHERE id='$dep_id'");
        
        echo "<script>alert('Deposit deleted successfully'); window.location.href='view-customer?id=$did';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>MURG TEXTILE ENTERPRISES - Customer Details</title>
    <link href="../assets/img/murglogo.jpg" rel="shortcut icon">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,500,600,700&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/plugins.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
    
    <!--  BEGIN CUSTOM STYLE FILE  -->
    <link href="../assets/css/users/user-profile.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="../plugins/select2/select2.min.css">
    <!--  END CUSTOM STYLE FILE  -->
    
    <!-- BEGIN PAGE LEVEL CUSTOM STYLES -->
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/dataTables.responsive.min.css">
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/custom_dt_html5.css">
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/dt-global_style.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/widgets/modules-widgets.css">    
</head>
<body class="sidebar-noneoverflow">
    <!-- BEGIN LOADER -->
    <div id="load_screen"> <div class="loader"> <div class="loader-content">
        <div class="spinner-grow align-self-center"></div>
    </div></div></div>
    <!--  END LOADER -->
    
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
                    <!-- Content -->
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 layout-top-spacing">
                        <div class="skills layout-spacing">
                            <div class="p-3 widget-content widget-content-area">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="">Customer Information</h3>
                                    <div>
                                        <a href="customer.php" class="btn btn-secondary btn-sm mr-2">Back</a>
                                        <a href="deposit.php?id=<?php echo $did; ?>" class="btn btn-primary btn-sm">Add Deposit</a>
                                    </div>
                                </div>
                                <div class="customer-info-card mt-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Name:</strong> <?php echo htmlentities($customer_data['name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlentities($customer_data['email']); ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlentities($customer_data['phone']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Gender:</strong> <?php echo htmlentities($customer_data['gender']); ?></p>
                                            <p><strong>Address:</strong> <?php echo htmlentities($customer_data['address']); ?></p>
                                            <p><strong>Total Deposits:</strong> ₦<?php echo number_format($actual_total_deposits, 2); ?></p>
                                            <p><strong>Outstanding Balance:</strong> 
                                                <?php if ($actual_balance == 0): ?>
                                                    <span class="badge badge-success">Clear</span>
                                                <?php elseif ($actual_balance < 0): ?>
                                                    <span class="badge badge-info">Credit: ₦<?php echo number_format(abs($actual_balance), 2); ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger" style="font-weight: bold;">₦<?php echo number_format($actual_balance, 2); ?></span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="widget-content widget-content-area mb-3">
                            <form action="" method="GET" class="form-row align-items-end" id="filterForm">
                                <input type="hidden" name="id" value="<?php echo $did; ?>">
                                <input type="hidden" name="tab" id="activeTab" value="orders">
                                <div class="col-md-3 mb-2">
                                    <label for="from_date">From Date</label>
                                    <input type="date" name="from_date" id="from_date" class="form-control" value="<?php echo $from_date; ?>">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="to_date">To Date</label>
                                    <input type="date" name="to_date" id="to_date" class="form-control" value="<?php echo $to_date; ?>">
                                </div>
                                <div class="col-md-4 mb-2">
                                    <button type="submit" class="btn btn-primary">Filter History</button>
                                    <?php if(!empty($from_date)): ?>
                                        <a href="view-customer?id=<?php echo $did; ?>" class="btn btn-secondary">Clear</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <!-- Tabs Section -->
                        <div class="widget-content widget-content-area">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $active_tab == 'orders' ? 'active' : ''; ?>" id="orders-tab" data-toggle="tab" href="#orders" role="tab" aria-controls="orders" aria-selected="<?php echo $active_tab == 'orders' ? 'true' : 'false'; ?>">Orders History</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link <?php echo $active_tab == 'deposit' ? 'active' : ''; ?>" id="deposit-tab" data-toggle="tab" href="#deposit" role="tab" aria-controls="deposit" aria-selected="<?php echo $active_tab == 'deposit' ? 'true' : 'false'; ?>">Deposit History</a>
                                </li>
                            </ul>
                             
                            <div class="tab-content" id="myTabContent">
                                <!-- Orders Tab -->
                                <div class="tab-pane fade <?php echo $active_tab == 'orders' ? 'show active' : ''; ?>" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                                    <div class="table-responsive mt-3">
                                        <table id="orders-table" class="table table-hover non-hover" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Staff</th>
                                                    <th>OrderID</th>
                                                    <th>item</th>
                                                    <th>Quantity</th>
                                                    <th>Price</th>
                                                    <th>Subtotal</th>
                                                    <th>Discount</th>
                                                    <th>Total</th>
                                                    <th>Amount Paid</th>
                                                    <th>Payment</th>
                                                    <th>Creation Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                        <tbody>
                                        <?php
                                         $date = date('Y-m-d');
                                         $facilityID = $_SESSION['facilityID'];
                                         $limit_sql = "";
                                         if (empty($from_date) || empty($to_date)) {
                                             $order_ids_q = mysqli_query($con, "SELECT DISTINCT orderID FROM orders WHERE customerID='$did' AND facilityID='$facilityID' ORDER BY creation DESC LIMIT 5");
                                             $order_ids = [];
                                             while ($r = mysqli_fetch_assoc($order_ids_q)) {
                                                 $order_ids[] = "'" . mysqli_real_escape_string($con, $r['orderID']) . "'";
                                             }
                                             if (!empty($order_ids)) {
                                                 $limit_sql = " AND orderID IN (" . implode(",", $order_ids) . ") ";
                                             } else {
                                                 $limit_sql = " AND 1=0 ";
                                             }
                                         }
                                         $sql=mysqli_query($con,"select * from orders where customerID='$did'  and facilityID ='$facilityID' $date_filter_orders $limit_sql ORDER BY creation DESC");
                                        $cnt = 1;
                                        $currentOrderID = null;
                                        $discountShown = false;
                                        $grand_total_history = 0; // New variable for sum
                                        $has_orders = false;

                                        while($row = mysqli_fetch_array($sql)) {
                                            $has_orders = true;
                                            // Check if this is a new order
                                            if ($currentOrderID != $row['orderID']) {
                                                $currentOrderID = $row['orderID'];
                                                $discountShown = false;
                                            }
                                            
                                            // Only show discount for the first item of the order
                                            $displayDiscount = (!$discountShown) ? $row['discount'] : 0;
                                            if (!$discountShown && $row['discount'] > 0) {
                                                $discountShown = true;
                                            }
                                        ?>
                                        <tr>
                                            <td class="center"><?php echo $cnt;?>.</td>
                                            <td class="hidden-xs"><?php echo $row['staff'];?></td>
                                            <td class="hidden-xs"><?php echo $row['orderID'];?></td>
                                            <td class="hidden-xs"><?php echo $row['item'];?></td>
                                            <td class="hidden-xs"><?php echo $row['quantity'];?></td>
                                            <td class="hidden-xs">₦<?php echo number_format($row['price']);?></td>
                                            <td class="hidden-xs">₦<?php echo $row['subtotal'];?></td>
                                            <td class="hidden-xs">₦<?php echo number_format(($row['item_discount'] * $row['quantity']) + (($displayDiscount > 0) ? $displayDiscount : 0), 2); ?></td>
                                            <td class="hidden-xs">₦<?php 
                                                $item_net = $row['subtotal'] - ($row['item_discount'] * $row['quantity']);
                                                $row_total = $item_net - (($displayDiscount > 0) ? $displayDiscount : 0);
                                                echo number_format($row_total, 2);
                                                $grand_total_history += $row_total;
                                            ?></td>
                                            <td class="hidden-xs">₦<?php echo number_format($row['amount_paid'], 2);?></td>
                                            <td class="hidden-xs"><?php echo $row['payment'];?></td>
                                            <td class="hidden-xs" data-order="<?php echo strtotime($row['creation']); ?>"><?php echo htmlspecialchars($row['creation']);?></td>
                                            <td>
                                                <a href="edit-order-item?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                                <a href="view-customer?id=<?php echo $did; ?>&item_id=<?php echo $row['id']; ?>&del_order=1" onclick="return confirm('Are you sure you want to delete this order item?')" class="btn btn-danger btn-sm">Delete</a>
                                            </td>
                                        </tr>
                                        <?php 
                                            $cnt=$cnt+1; 
                                        }
                                        
                                        if (!$has_orders) {
                                            ?>
                                            <tr>
                                                <td colspan="13" class="text-center py-4">
                                                    <div class="alert alert-info mb-0">
                                                        <i class="feather feather-info"></i> No order records found for this customer.
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                   
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: #f1f2f3; font-weight: bold;">
                                                    <td colspan="8" class="text-right">Grand Total:</td>
                                                    <td>₦<?php echo number_format($grand_total_history, 2); ?></td>
                                                    <td colspan="4"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        
                                    </div>
                                </div>
                                
                                <!-- Deposit History Tab -->
                                <div class="tab-pane fade <?php echo $active_tab == 'deposit' ? 'show active' : ''; ?>" id="deposit" role="tabpanel" aria-labelledby="deposit-tab">
                                    <div class="table-responsive mt-3">
                                        <table id="deposit-history" class="table table-bordered table-hover table-striped mb-4">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Transaction ID</th>
                                                    <th>Amount (₦)</th>
                                                    <th>Method</th>
                                                    <th>Description</th>
                                                    <th>Previous Balance (₦)</th>
                                                    <th>New Balance (₦)</th>
                                                    <th>Processed By</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Calculate running balances dynamically
                                                $history_deposits = [];
                                                // We must fetch all deposits in ASC order to calculate running balance forward correctly
                                                // Apply date filter if set
                                                $deposit_filter = "";
                                                if (!empty($from_date) && !empty($to_date)) {
                                                    $deposit_filter = " AND DATE(deposit_date) BETWEEN '$from_date' AND '$to_date' ";
                                                }
                                                $running_q = mysqli_query($con, "SELECT * FROM deposit_history WHERE customerID='$did' $deposit_filter ORDER BY deposit_date ASC, id ASC");
                                                
                                                // Starting point: Total debt before ANY deposits were made in this facility
                                                $temp_running_balance = $total_sales - $total_discount - $total_initial_paid;
                                                
                                                while ($d = mysqli_fetch_array($running_q)) {
                                                    $d['calculated_prev'] = $temp_running_balance;
                                                    $temp_running_balance -= floatval($d['amount']);
                                                    $d['calculated_new'] = $temp_running_balance;
                                                    $history_deposits[] = $d;
                                                }
                                                
                                                // Reverse to display newest first
                                                $history_deposits = array_reverse($history_deposits);
                                                
                                                // If no date filter is applied, only show the last 5
                                                $filtered_total_amount = 0;
                                                if (empty($from_date) && empty($to_date)) {
                                                    $history_deposits = array_slice($history_deposits, 0, 5);
                                                }
                                                
                                                // Sum the amount of deposits displayed
                                                foreach ($history_deposits as $deposit) {
                                                    $filtered_total_amount += floatval($deposit['amount']);
                                                }
                                                
                                                if (empty($history_deposits)) {
                                                    ?>
                                                    <tr>
                                                        <td colspan="9" class="text-center py-4">
                                                            <div class="alert alert-info mb-0">
                                                                <i class="feather feather-info"></i> No deposit records found for this customer.
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                
                                                foreach ($history_deposits as $deposit) {
                                                ?>
                                                <tr>
                                                    <td data-order="<?php echo strtotime($deposit['deposit_date']); ?>"><?php echo date('d M Y h:i A', strtotime($deposit['deposit_date'])); ?></td>
                                                    <td><?php echo htmlentities($deposit['transaction_id']); ?></td>
                                                    <td class="text-success">+₦<?php echo number_format($deposit['amount'], 2); ?></td>
                                                    <td><?php echo htmlentities($deposit['payment_method'] ?: 'N/A'); ?></td>
                                                    <td><?php echo htmlentities($deposit['description'] ?: '-'); ?></td>
                                                    <td>₦<?php echo number_format($deposit['calculated_prev'], 2); ?></td>
                                                    <td class="text-primary">₦<?php echo number_format($deposit['calculated_new'], 2); ?></td>
                                                    <td><?php echo htmlentities($deposit['processed_by']); ?></td>
                                                    <td>
                                                        <a href="deposit-receipt?id=<?php echo $deposit['id']; ?>" class="btn btn-info btn-sm">Receipt</a>
                                                        <a href="edit-deposit?id=<?php echo $deposit['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                                        <a href="view-customer?id=<?php echo $did; ?>&dep_id=<?php echo $deposit['id']; ?>&del_dep=1" onclick="return confirm('Are you sure you want to delete this deposit?')" class="btn btn-danger btn-sm">Delete</a>
                                                    </td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                             <tfoot>
                                                 <tr>
                                                     <th colspan="2">Total Deposits:</th>
                                                      <th>₦<?php echo number_format($filtered_total_amount, 2); ?></th>
                                                     <th colspan="6"></th>
                                                 </tr>
                                             </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--  END CONTENT AREA  -->
    </div>
    <!-- END MAIN CONTAINER -->

    <?php include('footer.php'); ?>

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="../assets/js/libs/jquery-3.1.1.min.js"></script>
    <script src="../bootstrap/js/popper.min.js"></script>
    <script src="../bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/app.js"></script>
    
    <!-- BEGIN PAGE LEVEL SCRIPTS -->
    <script src="../plugins/table/datatable/datatables.js"></script>
    <script src="../plugins/table/datatable/dataTables.responsive.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/dataTables.buttons.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/jszip.min.js"></script>    
    <script src="../plugins/table/datatable/button-ext/buttons.html5.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/buttons.print.min.js"></script>
    
    <script>
        $(document).ready(function() {
            App.init();
            
            // Set initial tab from URL parameter
            var urlParams = new URLSearchParams(window.location.search);
            var tabParam = urlParams.get('tab');
            if (tabParam) {
                $('#activeTab').val(tabParam);
                $('.nav-tabs a[href="#' + tabParam + '"]').tab('show');
            }
            
            // Tab state preservation
            var hash = window.location.hash;
            if (hash) {
                $('.nav-tabs a[href="' + hash + '"]').tab('show');
            }
             $('.nav-tabs a').on('shown.bs.tab', function (e) {
                 var targetTab = e.target.hash.replace('#', '');
                 $('#activeTab').val(targetTab);
                 window.location.hash = e.target.hash;
                 // Adjust DataTable columns to prevent layout collapse in hidden tab
                 if ($.fn.DataTable.isDataTable('#orders-table')) {
                     $('#orders-table').DataTable().columns.adjust();
                 }
                 if ($.fn.DataTable.isDataTable('#deposit-history')) {
                     $('#deposit-history').DataTable().columns.adjust();
                 }
             });
            
            // Append tab parameter to form action on submit
            $('#filterForm').on('submit', function() {
                var activeTab = $('#activeTab').val();
                var action = $(this).attr('action') || '';
                var separator = action.indexOf('?') === -1 ? '?' : '&';
                $(this).attr('action', action + separator + 'tab=' + activeTab);
            });
            
            // Initialize orders datatable
            $('#orders-table').DataTable({
                responsive: true,
                "dom": "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                "buttons": {
                    "buttons": [
                        { extend: 'copy', className: 'btn btn-sm' },
                        { extend: 'csv', className: 'btn btn-sm' },
                        { extend: 'excel', className: 'btn btn-sm' },
                        { extend: 'print', className: 'btn btn-sm' }
                    ]
                },
                "order": [[11, "desc"]],
                "oLanguage": {
                    "oPaginate": { 
                        "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', 
                        "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' 
                    },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                    "sSearchPlaceholder": "Search...",
                    "sLengthMenu": "Results :  _MENU_",
                },
                "stripeClasses": [],
                "lengthMenu": [10, 20, 50, 100],
                "pageLength": 10 
            });
            
            // Initialize deposit history datatable
            $('#deposit-history').DataTable({
                responsive: true,
                "dom": "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                "buttons": {
                    "buttons": [
                        { extend: 'copy', className: 'btn btn-sm' },
                        { extend: 'csv', className: 'btn btn-sm' },
                        { extend: 'excel', className: 'btn btn-sm' },
                        { extend: 'print', className: 'btn btn-sm' }
                    ]
                },
                "order": [[0, "desc"]],
                "oLanguage": {
                    "oPaginate": { 
                        "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', 
                        "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' 
                    },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                    "sSearchPlaceholder": "Search...",
                    "sLengthMenu": "Results :  _MENU_",
                },
                "stripeClasses": [],
                "lengthMenu": [10, 20, 50, 100],
                "pageLength": 10 
            });
        });
    </script>
    <!-- END PAGE LEVEL SCRIPTS -->

    <script src="../plugins/highlight/highlight.pack.js"></script>
    <script src="../plugins/select2/select2.min.js"></script>
    <script src="../plugins/select2/custom-select2.js"></script>
</body>
</html>




