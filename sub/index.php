<?php
session_start();
error_reporting(E_ALL); // Enable error reporting for debugging
include('../assets/mashaAllah/gyada.php'); // Database connection

// Check if user is logged in
if (empty($_SESSION['email'])) {
    header('location:../index.php');
    exit;
}

// Get facility ID from session
$facilityID = $_SESSION['facilityID'] ?? null;
if (!$facilityID) {
    die("Error: Facility ID not set in session");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>MURG - Administrative Panel</title>
    <link rel="icon" href="../assets/img/murglogo.jpg">
    
    <!-- Global Mandatory Styles -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,500,600,700&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/plugins.css" rel="stylesheet">
    
    <!-- Page Level Styles -->
    <link href="../plugins/apex/apexcharts.css" rel="stylesheet">
    <link href="../assets/css/dashboard/dash_1.css" rel="stylesheet">
    <link href="../plugins/table/datatable/datatables.css" rel="stylesheet">
    <link href="../plugins/table/datatable/custom_dt_html5.css" rel="stylesheet">
    <link href="../plugins/table/datatable/dt-global_style.css" rel="stylesheet">
    <link href="../assets/css/widgets/modules-widgets.css" rel="stylesheet">

    <!-- Loader Styles -->
    <link href="../assets/css/loader.css" rel="stylesheet">
    <script src="../assets/js/loader.js"></script>
</head>
<body class="sidebar-noneoverflow">
    <!-- Loader -->
    <div id="load_screen">
        <div class="loader">
            <div class="loader-content">
                <div class="spinner-grow align-self-center"></div>
            </div>
        </div>
    </div>

    <?php include('header.php'); ?>

    <div class="main-container" id="container">
        <div class="overlay"></div>
        <div class="search-overlay"></div>

        <?php include('sidebar.php'); ?>

        <div id="content" class="main-content">
            <div class="layout-px-spacing">
                <div class="row layout-top-spacing">
                    <!-- Total Stocks Widget -->
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
                        <div class="widget widget-account-invoice-three">
                            <div class="widget-heading">
                                <div class="wallet-usr-info">
                                    <div class="usr-name">
                                        <span><img src="../assets/images/stock.png" alt="stocks" class="img-fluid"> Total Stocks</span>
                                    </div>
                                </div>
                                <div class="wallet-balance">
                                    <p>Total Stocks</p>
                                    <?php
                                    $stock_query = $con->query("SELECT COUNT(id) as total FROM stocks");
                                    $stock_row = $stock_query->fetch_assoc();
                                    ?>
                                    <h5><?php echo $stock_row['total'] ?? 0; ?></h5>
                                </div>
                            </div>
                            <div class="widget-amount">
                                <?php
                                $out_stock_query = $con->query("SELECT COUNT(id) as total FROM stocks WHERE quantity <= 0");
                                $out_stock_row = $out_stock_query->fetch_assoc();
                                ?>
                                <div class="w-a-info funds-spent" style="width: 100%">
                                    <span>Out of Stock</span>
                                    <p><?php echo $out_stock_row['total'] ?? 0; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Yearly Sales Widget -->
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
                        <div class="widget widget-account-invoice-three">
                            <div class="widget-heading">
                                <div class="wallet-usr-info">
                                    <div class="usr-name">
                                        <span><img src="../assets/images/sales.png" alt="sales" class="img-fluid"> Yearly Sales</span>
                                    </div>
                                </div>
                                <div class="wallet-balance">
                                    <p>Total Sales</p>
                                    <?php
                                    $yearly_sales_query = $con->query("SELECT SUM(net_total) as total FROM orders");
                                    $yearly_sales_row = $yearly_sales_query->fetch_assoc();
                                    $yearly_expense_query = $con->query("SELECT SUM(price) as total FROM expense");
                                    $yearly_expense_row = $yearly_expense_query->fetch_assoc();
                                    ?>
                                    <h5><span class="w-currency">₦</span><?php echo number_format(($yearly_sales_row['total'] ?? 0) - ($yearly_expense_row['total'] ?? 0)); ?></h5>
                                </div>
                            </div>
                            <div class="widget-amount">
                                <?php
                                // Yearly Payment Breakdown
                                $yearly_payment_query = $con->query("SELECT SUM(cash) as cash_total, SUM(pos) as pos_total, SUM(transfer) as transfer_total FROM orders");
                                $yearly_payment_row = $yearly_payment_query->fetch_assoc();
                                
                                $month = date('m');
                                $monthly_sales_query = $con->query("SELECT SUM(subtotal) as total FROM orders WHERE MONTH(creation) = '$month'");
                                $monthly_sales_row = $monthly_sales_query->fetch_assoc();
                                $monthly_expense_query = $con->query("SELECT SUM(price) as total FROM expense WHERE MONTH(creation) = '$month'");
                                $monthly_expense_row = $monthly_expense_query->fetch_assoc();
                                // Monthly Payment Breakdown
                                $monthly_payment_query = $con->query("SELECT SUM(cash) as cash_total, SUM(pos) as pos_total, SUM(transfer) as transfer_total FROM orders WHERE MONTH(creation) = '$month'");
                                $monthly_payment_row = $monthly_payment_query->fetch_assoc();
                                
                                $today = date('Y-m-d');
                                $daily_sales_query = $con->query("SELECT SUM(subtotal) as total FROM orders WHERE DATE(creation) = '$today'");
                                $daily_sales_row = $daily_sales_query->fetch_assoc();
                                $daily_expense_query = $con->query("SELECT SUM(price) as total FROM expense WHERE DATE(creation) = '$today'");
                                $daily_expense_row = $daily_expense_query->fetch_assoc();
                                // Daily Payment Breakdown
                                $daily_payment_query = $con->query("SELECT SUM(cash) as cash_total, SUM(pos) as pos_total, SUM(transfer) as transfer_total FROM orders WHERE DATE(creation) = '$today'");
                                $daily_payment_row = $daily_payment_query->fetch_assoc();
                                ?>
                                <div class="w-a-info funds-received">
                                    <span>Sales For <b><?php echo date('F Y'); ?></b></span>
                                    <p>₦<?php echo number_format(($monthly_sales_row['total'] ?? 0) - ($monthly_expense_row['total'] ?? 0)); ?></p>
                                </div>
                                <div class="w-a-info funds-spent">
                                    <span>Sales For <b><?php echo date('l (d-m-Y)'); ?></b></span>
                                    <p>₦<?php echo number_format(($daily_sales_row['total'] ?? 0) - ($daily_expense_row['total'] ?? 0)); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Yearly Expense Widget -->
                    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
                        <div class="widget widget-account-invoice-three">
                            <div class="widget-heading">
                                <div class="wallet-usr-info">
                                    <div class="usr-name">
                                        <span><img src="../assets/images/expen.png" alt="expense" class="img-fluid"> Yearly Expense</span>
                                    </div>
                                </div>
                                <div class="wallet-balance">
                                    <p>Total Expense</p>
                                    <h5><span class="w-currency">₦</span><?php echo number_format($yearly_expense_row['total'] ?? 0); ?></h5>
                                </div>
                            </div>
                            <div class="widget-amount">
                                <div class="w-a-info funds-received">
                                    <span>Expense For <b><?php echo date('F Y'); ?></b></span>
                                    <p>₦<?php echo number_format($monthly_expense_row['total'] ?? 0); ?></p>
                                </div>
                                <div class="w-a-info funds-spent">
                                    <span>Expense For <b><?php echo date('l (d-m-Y)'); ?></b></span>
                                    <p>₦<?php echo number_format($daily_expense_row['total'] ?? 0); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Sales Table -->
                <h3>Today's Sales</h3>
                <div class="row layout-top-spacing" id="cancel-row">
                    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                        <div class="widget-content widget-content-area br-6">
                            <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Staff</th>
                                        <th>Customer</th>
                                        <th>OrderID</th>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Cash</th>
                                        <th>POS</th>
                                        <th>Transfer</th>
                                        <th>Creation Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $today = date('Y-m-d');
                                    $sql = $con->query("SELECT * FROM orders WHERE DATE(creation) = '$today' ORDER BY creation DESC");
                                    $cnt = 1;
                                    $currentOrderID = null;
                                    $discountShown = false;

                                    while ($row = $sql->fetch_assoc()) {
                                        if ($currentOrderID != $row['orderID']) {
                                            $currentOrderID = $row['orderID'];
                                            $discountShown = false;
                                        }
                                        $displayDiscount = (!$discountShown && $row['discount'] > 0) ? $row['discount'] : 0;
                                        if (!$discountShown && $row['discount'] > 0) {
                                            $discountShown = true;
                                        }
                                    ?>
                                    <tr>
                                        <td class="center"><?php echo $cnt; ?>.</td>
                                        <td><?php echo htmlspecialchars($row['staff']); ?></td>
                                        <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['orderID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['item']); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td>₦<?php echo number_format($row['price']); ?></td>
                                        <td>₦<?php echo number_format($row['subtotal']); ?></td>
                                        <td>₦<?php echo number_format($displayDiscount); ?></td>
                                        <td>₦<?php echo number_format($row['subtotal'] - $displayDiscount); ?></td>
                                        <td><?php echo htmlspecialchars($row['payment']); ?></td>
                                        <td>₦<?php echo number_format($row['cash'] ?? 0); ?></td>
                                        <td>₦<?php echo number_format($row['pos'] ?? 0); ?></td>
                                        <td>₦<?php echo number_format($row['transfer'] ?? 0); ?></td>
                                        <td><?php echo htmlspecialchars($row['creation']); ?></td>
                                    </tr>
                                    <?php
                                        $cnt++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php include('footer.php'); ?>
            </div>
        </div>
    </div>

    <!-- Global Mandatory Scripts -->
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

    <!-- Page Level Scripts -->
    <script src="../plugins/table/datatable/datatables.js"></script>
    <script src="../plugins/table/datatable/button-ext/dataTables.buttons.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/jszip.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/buttons.html5.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#html5-extension').DataTable({
                dom: "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
                buttons: {
                    buttons: [
                        { extend: 'copy', className: 'btn btn-sm' },
                        { extend: 'csv', className: 'btn btn-sm' },
                        { extend: 'excel', className: 'btn btn-sm' },
                        { extend: 'print', className: 'btn btn-sm' }
                    ]
                },
                oLanguage: {
                    oPaginate: {
                        sPrevious: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                        sNext: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                    },
                    sInfo: "Showing page _PAGE_ of _PAGES_",
                    sSearch: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                    sSearchPlaceholder: "Search...",
                    sLengthMenu: "Results : _MENU_"
                },
                stripeClasses: [],
                lengthMenu: [7, 10, 20, 50],
                pageLength: 7
            });
        });
    </script>
</body>
</html>






