<?php
session_start();

error_reporting(0);
include('../assets/mashaAllah/gyada.php');
if(strlen($_SESSION['email'])==0)
  {
header('location:../index.php');
}
else 
{
  if (isset($_POST['submit'])) {

$role = $_POST['role'];
$name = $_POST['name'];
$email = $_POST['emailid'];
$phone = $_POST['phone'];
$gender = $_POST['sex'];
$facilityID = $_SESSION['facilityID'];
$fname = $_SESSION['fname'];
$address = $_SESSION['address'];
$confirm=md5($_POST['confirm']);
$sql=mysqli_query($con,"insert into facility(facilityID,name,email,phone,gender,fname,address,role,password,status) values('$facilityID','$name','$email','$phone','$gender','$fname','$address','$role','$confirm','1')");

if($sql)
{
   $msg="Billing Item Added successfully";
}
else
{
$error="Something went wrong. Please try again";
}



}
if(isset($_GET['del']))
      {
              mysqli_query($con,"delete from facility where id = '".$_GET['id']."'");
            

      }

      if(isset($_GET['sus']))

      {
        $order_query = $con->query("SELECT * FROM facility where id='".$_GET['id']."'");
$order_row = $order_query->fetch_array();

        if ($order_row['status'] == 0){
          mysqli_query($con,"Update facility set status='1' where id='".$_GET['id']."'");
        }else if($order_row['status'] == 1){

          mysqli_query($con,"Update facility set status='0' where id='".$_GET['id']."'");
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
  <link href="../assets/img/murglogo.jpg" rel="shortcut icon">
    <link href="../assets/css/loader.css" rel="stylesheet" type="text/css" />
    <script src="../assets/js/loader.js"></script>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Quicksand:400,500,600,700&display=swap" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/plugins.css" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->
        
    <!-- BEGIN PAGE LEVEL CUSTOM STYLES -->
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/custom_dt_html5.css">
    <link rel="stylesheet" type="text/css" href="../plugins/table/datatable/dt-global_style.css">
    <!-- END PAGE LEVEL CUSTOM STYLES -->
    <!-- END PAGE LEVEL PLUGINS/CUSTOM STYLES -->
    <script>
function checkAvailability() {
$("#loaderIcon").show();
jQuery.ajax({
url: "check_staff.php",
data:'emailid='+$("#emailid").val(),
type: "POST",
success:function(data){
$("#user-availability-status").html(data);
$("#loaderIcon").hide();
},
error:function (){}
});
}
</script>
<script type="text/javascript">
function valid()
{
 if(document.edituser.password.value!= document.edituser.confirm.value)
{
alert("Password and Confirm Password Field do not match  !!");
document.edituser.confirm.focus();
return false;
}
return true;
}
</script>

</head>
<body class="sidebar-noneoverflow">
    <!-- BEGIN LOADER -->
    <div id="load_screen"> <div class="loader"> <div class="loader-content">
        <div class="spinner-grow align-self-center"></div>
    </div></div></div>
    <!--  END LOADER -->
 <?php include('header.php'); ?>
    <!--  BEGIN NAVBAR  -->
   
    <!--  END NAVBAR  -->

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
                
                <div class="row layout-top-spacing" id="cancel-row">
                
                    <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                        <button type="button" class="btn btn-primary mb-4 mr-2" data-toggle="modal" data-target="#exampleModal">
                                         Add New Staff
                                        </button>
                        <div class="widget-content widget-content-area br-6">

                             <?php if($error){?><strong style="color:red; font-size:18px; margin-top: 15px;">Something Went Wrong Try again later</strong> <?php } 
        else if($msg){?><strong style="color:green; font-size:18px;  margin-top: 15px;"> Staff Added Successfully</strong><?php }?>

                            <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                                <thead>
                                    <tr>
                                       <th>S/N</th>
                     <th>Staff Name</th>
                      <th>Email Address</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Creation Date</th>
                    <th>Updation Date</th>
                    <th>Action</th>
                   
                                       
                                    </tr>
                                </thead>
                                <tbody>
                                  <?php
                                  $facilityID = $_SESSION['facilityID'];
$sql = mysqli_query($con, "SELECT * FROM facility WHERE role != 'admin'");
$cnt=1;
while($row=mysqli_fetch_array($sql))
{
?>
                         <tr>
                        <td class="center"><?php echo $cnt;?>.</td>
                        <td class="hidden-xs"><?php echo $row['name'];?></td>
                        <td class="hidden-xs"><?php echo $row['email'];?></td>
                        <td class="hidden-xs"><?php echo $row['role'];?></td>
                         <td class="hidden-xs"><?php if ($row['status'] == 0) {
                           ?><b style="color:red" >Suspended</b><?php
                         }else if ($row['status'] == 1){

                         ?><b style="color:green">Active</b><?php }?></td>
                         <td class="hidden-xs"><?php echo $row['creation'];?></td>
                         <td class="hidden-xs"><?php echo $row['updation'];?></td>
                         
                       
                        </td>
                        
                         <td>
                        <div class="visible-md visible-lg hidden-sm hidden-xs">
                        
                        <?php if ($row['status'] == 0){
                          ?><a href="staff?id=<?php echo $row['id']?>&sus=delete" onClick="return confirm('Are you sure you want to acivate account?')"class="btn btn-danger" tooltip-placement="top" tooltip="Remove">Activate</a><?php
                        }else if ($row['status'] == 1){
                          ?> <a href="staff?id=<?php echo $row['id']?>&sus=delete" onClick="return confirm('Are you sure you want to suspend account?')"class="btn btn-danger" tooltip-placement="top" tooltip="Remove">Suspend</a><?php
                        }?>
                        <a href="edit-staff?id=<?php echo $row['id'];?>" class="btn btn-primary" tooltip-placement="top" tooltip="Edit">Edit</a>

                    
  <a href="staff?id=<?php echo $row['id']?>&del=delete" onClick="return confirm('Are you sure you want to delete?')"class="btn btn-danger" tooltip-placement="top" tooltip="Remove">Delete</a>
                        </div>
                       
                        </div></td>
                      </tr>

                      <?php 
$cnt=$cnt+1; 
}
?>        
                                </tbody>
                            </table>
                        </div>
                    </div>

                   <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Staff Information</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                      <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                    </button>
                                                </div>
                                               <form method="post" id="edituser" name="edituser"  onSubmit="return valid();">
                                                <div class="modal-body">
                                                    <div class="form-group">
                        <label>Staff Name</label>
                        <span style="color:red">*</span><br>
                        <input type="text" class="form-control" required name="name">
                       

                      </div>
                      <div class="form-group">

                        <label>Email Address</label>
                        <span style="color:red">*</span><br>
                        <input type="email" class="form-control" required name="emailid" id="emailid" onBlur="checkAvailability()">
                       <span id="user-availability-status" style="font-size:12px;"></span> 

                      </div>
                      <div class="form-group">
                        <label>Staff Phone</label>
                        <span style="color:red">*</span><br>
                        <input type="number" class="form-control" required name="phone">

                      </div>
                      <div class="form-group">
                      <label>Gender</label>
                      <span style="color:red">*</span><br>
                       <select class="form-control select2" style="width: 100%;" name="sex" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            
                                            
                      </select>
                    </div>
                      <div class="form-group">
                      <label>Select Role</label>
                      <span style="color:red">*</span><br>
                       <select class="form-control select2" style="width: 100%;" name="role" required>
                                            <option value="">Select Role</option>
                                           <!-- <option value="Sub-admin">Manager</option> -->
                                            <option value="Staff">Cashier</option>
                                            
                                            
                      </select>
                    </div>
                      
                      <div class="form-group">
                        <label>New Password</label>
                        <span style="color:red">*</span><br>
                        <input type="text" class="form-control" required name="password">
                       

                      </div>
                      
                       <div class="form-group">
                        <label>Confirm Password</label>
                        <span style="color:red">*</span><br>
                        <input type="text" class="form-control" required name="confirm">
                       

                      </div>

                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn" data-dismiss="modal"><i class="flaticon-cancel-12"></i> Discard</button>
                                                    <button type="submit" class="btn btn-primary" name="submit">Save</button>
                                                </div>
                                                </form>
                                            </div>
                                        </div>
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
    <!-- NOTE TO Use Copy CSV Excel PDF Print Options You Must Include These Files  -->
    <script src="../plugins/table/datatable/button-ext/dataTables.buttons.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/jszip.min.js"></script>    
    <script src="../plugins/table/datatable/button-ext/buttons.html5.min.js"></script>
    <script src="../plugins/table/datatable/button-ext/buttons.print.min.js"></script>
    <script>
        $('#html5-extension').DataTable( {
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
        } );
    </script>
    <!-- END PAGE LEVEL CUSTOM SCRIPTS -->
</body>
</html>




