<?php
session_start();
error_reporting(0);
include('../assets/mashaAllah/gyada.php');

if (strlen($_SESSION['email']) == 0) {
    header('location:../index.php');
} else {
    // Add new branch
    if (isset($_POST['submit'])) {
        $branch_name = $_POST['branch_name'];
        $branch_address = $_POST['branch_address'];

        // Fetch the lastID from the "conca" table
        $result = mysqli_query($con, "SELECT lastID FROM conca WHERE id=1");
        $row = mysqli_fetch_assoc($result);
        $lastID = $row['lastID'];

        // Generate the new facilityID
        $newID = $lastID + 1;
        $facilityID = "MURG/" . str_pad($newID, 3, '0', STR_PAD_LEFT);

        // Update the lastID in the "conca" table
        mysqli_query($con, "UPDATE conca SET lastID='$newID' WHERE id=1");

        // Insert the new branch into the "branches" table
        $sql = mysqli_query($con, "INSERT INTO branch(facilityID, name, address) VALUES('$facilityID', '$branch_name', '$branch_address')");
        if ($sql) {
            $msg = "Branch added successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }

    // Delete branch
    if (isset($_GET['del'])) {
        mysqli_query($con, "DELETE FROM branch WHERE id='" . $_GET['id'] . "'");
    }

    // Fetch branch details for editing
    if (isset($_GET['edit'])) {
        $branch_id = $_GET['id'];
        $branch_query = $con->query("SELECT * FROM branch WHERE id='$branch_id'");
        $branch_row = $branch_query->fetch_array();
    }

    // Update branch details
    if (isset($_POST['update'])) {
        $branch_id = $_POST['branch_id'];
        $branch_name = $_POST['branch_name'];
        $branch_address = $_POST['branch_address'];

        $sql = mysqli_query($con, "UPDATE branch SET name='$branch_name', address='$branch_address' WHERE id='$branch_id'");
        if ($sql) {
            $msg = "Branch updated successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>MURG TEXTILE ENTERPRISES</title>
     <!-- <link href="../assets/img/Icon.jpg" rel="shortcut icon"> -->
    <link href="../assets/css/loader.css" rel="stylesheet" type="text/css" />
    <script src="../assets/js/loader.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,500,600,700&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/plugins.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/dataTables.responsive.min.css">
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/custom_dt_html5.css">
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/dt-global_style.css">
</head>
<body class="sidebar-noneoverflow">
    <div id="load_screen"> <div class="loader"> <div class="loader-content">
        <div class="spinner-grow align-self-center"></div>
    </div></div></div>
    <?php include('header.php'); ?>
    <div class="main-container" id="container">
        <div class="overlay"></div>
        <div class="search-overlay"></div>
        <?php include('sidebar.php'); ?>
        <div id="content" class="main-content">
            <div class="layout-px-spacing">
                <div class="row layout-top-spacing">
                    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                        <button type="button" class="btn btn-primary mb-4 mr-2" data-toggle="modal" data-target="#addBranchModal">
                            Add New Branch
                        </button>
                        <div class="widget-content widget-content-area br-6">
                            <?php if ($error) { ?><strong style="color:red; font-size:18px; margin-top: 15px;"><?php echo $error; ?></strong><?php } 
                            else if ($msg) { ?><strong style="color:green; font-size:18px; margin-top: 15px;"><?php echo $msg; ?></strong><?php } ?>
                            <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Branch Name</th>
                                        <th>Branch Address</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $facilityID = $_SESSION['facilityID'];
                                    $sql = mysqli_query($con, "SELECT * FROM branch");
                                    $cnt = 1;
                                    while ($row = mysqli_fetch_array($sql)) {
                                    ?>
                                        <tr>
                                            <td class="center"><?php echo $cnt; ?>.</td>
                                            <td class="hidden-xs">
                                                <a href="./?switch_branch=<?php echo $row['facilityID']; ?>">
                                                    <?php echo $row['name']; ?>
                                                </a>
                                            </td>
                                            <td class="hidden-xs"><?php echo $row['address']; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editBranchModal<?php echo $row['id']; ?>">Edit</button>
                                                <a href="branch?id=<?php echo $row['id']; ?>&del=delete" onClick="return confirm('Are you sure you want to delete?')" class="btn btn-danger">Delete</a>
                                            </td>
                                        </tr>

                                        <!-- Edit Branch Modal -->
                                        <div class="modal fade" id="editBranchModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editBranchModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="branch_id" value="<?php echo $row['id']; ?>">
                                                            <div class="form-group">
                                                                <label>Branch Name</label>
                                                                <input type="text" class="form-control" name="branch_name" value="<?php echo $row['name']; ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Branch Address</label>
                                                                <input type="text" class="form-control" name="branch_address" value="<?php echo $row['address']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary" name="update">Save changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    <?php 
                                        $cnt++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Branch Modal -->
            <div class="modal fade" id="addBranchModal" tabindex="-1" role="dialog" aria-labelledby="addBranchModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addBranchModalLabel">Add New Branch</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Branch Name</label>
                                    <input type="text" class="form-control" name="branch_name" required>
                                </div>
                                <div class="form-group">
                                    <label>Branch Address</label>
                                    <input type="text" class="form-control" name="branch_address" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" name="submit">Add Branch</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include('footer.php'); ?>
        </div>
        <!--  END CONTENT AREA  -->
    </div>
    <!-- END MAIN CONTAINER -->

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
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
    <!-- END GLOBAL MANDATORY SCRIPTS -->

    <!-- BEGIN PAGE LEVEL CUSTOM SCRIPTS -->
    <script src="../plugins/table/datatable/datatables.js"></script>
    <script src="../plugins/table/datatable/dataTables.responsive.min.js"></script>
    <script>
        $('#html5-extension').DataTable({
            responsive: true,
            "dom": "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            buttons: {
                buttons: [
                    { extend: 'copy', className: 'btn btn-sm' },
                    { extend: 'csv', className: 'btn btn-sm' },
                    { extend: 'excel', className: 'btn btn-sm' },
                    { extend: 'print', className: 'btn btn-sm' }
                ]
            },
            "oLanguage": {
                "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                "sSearchPlaceholder": "Search...",
                "sLengthMenu": "Results :  _MENU_",
            },
            "stripeClasses": [],
            "lengthMenu": [7, 10, 20, 50],
            "pageLength": 7 
        });
    </script>
    <!-- END PAGE LEVEL CUSTOM SCRIPTS -->
</body>
</html>


