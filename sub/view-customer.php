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

// Date range filter logic
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$date_filter_orders = "";
$date_filter_deposits = "";
if (!empty($from_date) && !empty($to_date)) {
    $date_filter_orders = " AND DATE(creation) BETWEEN '$from_date' AND '$to_date' ";
    $date_filter_deposits = " AND DATE(deposit_date) BETWEEN '$from_date' AND '$to_date' ";
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
                                            <?php if($outstanding_data) { ?>
                                            <p><strong>Total Deposits:</strong> ₦<?php echo number_format($outstanding_data['amount'], 2); ?></p>
                                            <p><strong>Outstanding Balance:</strong> ₦<?php echo number_format($outstanding_data['balance'], 2); ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="widget-content widget-content-area mb-3">
                            <form action="" method="GET" class="form-row align-items-end">
                                <input type="hidden" name="id" value="<?php echo $did; ?>">
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
                                    <a class="nav-link active" id="orders-tab" data-toggle="tab" href="#orders" role="tab" aria-controls="orders" aria-selected="true">Orders History</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="deposit-tab" data-toggle="tab" href="#deposit" role="tab" aria-controls="deposit" aria-selected="false">Deposit History</a>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="myTabContent">
                                <!-- Orders Tab -->
                                <div class="tab-pane fade show active" id="orders" role="tabpanel" aria-labelledby="orders-tab">
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
                                                </tr>
                                            </thead>
                                        <tbody>
                                        <?php
                                        $date = date('Y-m-d');
                                            $facilityID = $_SESSION['facilityID'];
                                        $sql=mysqli_query($con,"select * from orders where customerID='$did'  and facilityID ='$facilityID' $date_filter_orders ORDER BY creation DESC");
                                        $cnt = 1;
                                        $currentOrderID = null;
                                        $discountShown = false;

                                        while($row = mysqli_fetch_array($sql)) {
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
                                            <td class="hidden-xs">₦<?php echo ($displayDiscount > 0) ? $displayDiscount : '0'; ?></td>
                                            <td class="hidden-xs">₦<?php echo $row['subtotal'] - (($displayDiscount > 0) ? $displayDiscount : '0');?></td>
                                            <td class="hidden-xs">₦<?php echo $row['amount_paid'];?></td>
                                            <td class="hidden-xs"><?php echo $row['payment'];?></td>
                                            <td class="hidden-xs"><?php echo $row['creation'];?></td>
                                        </tr>
                                        <?php 
                                            $cnt=$cnt+1; 
                                        }
                                        ?>
                                   
                                            </tbody>
                                        </table>
                                        
                                    </div>
                                </div>
                                
                                <!-- Deposit History Tab -->
                                <div class="tab-pane fade" id="deposit" role="tabpanel" aria-labelledby="deposit-tab">
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
                                                    <!-- <th>Invoice</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                 $deposit_query = mysqli_query($con, "SELECT * FROM deposit_history WHERE customerID='$did' $date_filter_deposits ORDER BY deposit_date DESC");
                                                while ($deposit = mysqli_fetch_array($deposit_query)) {
                                                ?>
                                                <tr>
                                                    <td><?php echo date('d M Y h:i A', strtotime($deposit['deposit_date'])); ?></td>
                                                    <td><?php echo htmlentities($deposit['transaction_id']); ?></td>
                                                    <td class="text-success">+₦<?php echo number_format($deposit['amount'], 2); ?></td>
                                                    <td><?php echo htmlentities($deposit['payment_method'] ?: 'N/A'); ?></td>
                                                    <td><?php echo htmlentities($deposit['description'] ?: '-'); ?></td>
                                                    <td>₦<?php echo number_format($deposit['previous_balance'], 2); ?></td>
                                                    <td class="text-primary">₦<?php echo number_format($deposit['new_balance'], 2); ?></td>
                                                    <td><?php echo htmlentities($deposit['processed_by']); ?></td>
                                                    
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2">Total Deposits:</th>
                                                     <th>₦<?php 
                                                        $total_query = mysqli_query($con, "SELECT SUM(amount) as total FROM deposit_history WHERE customerID='$did' $date_filter_deposits");
                                                        $total = mysqli_fetch_array($total_query);
                                                        echo number_format($total['total'], 2);
                                                    ?></th>
                                                    <th colspan="4"></th>
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
    <script src="../plugins/table/datatable/button-ext/dataTables.buttons.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/jszip.min.js"></script>    
    <script src="../plugins/table/datatable/button-ext/buttons.html5.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/buttons.print.min.js"></script>
    
    <script>
        $(document).ready(function() {
            App.init();
            
            // Initialize orders datatable
            $('#orders-table').DataTable({
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
                "order": [[2, "desc"]],
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





