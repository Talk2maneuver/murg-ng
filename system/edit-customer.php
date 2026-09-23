<?php
session_start();

error_reporting(0);
$did=intval($_GET['id']);
include('../assets/mashaAllah/gyada.php');
if(strlen($_SESSION['email'])==0)
  {
header('location:../index.php');
}
else{

if (isset($_POST['submit'])) {
 
$name = $_POST['name'];
$address = $_POST['address'];
$email = $_POST['emailid'];
$phone = $_POST['phone'];
$gender = $_POST['sex'];
$facilityID = $_POST['branch'];


$sql=mysqli_query($con,"Update customers set name='$name',email='$email',phone='$phone',gender='$gender',address='$address', facilityID = '$facilityID' where id='$did'");
if($sql)
{
  echo "<script>window.location.href ='customer'</script>";
}
else
{
$error="Something went wrong. Please try again";
}
}
 }
 ?>
<!DOCT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>MURG TEXTILE ENTERPRISES</title>
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

                <div class="row layout-spacing">

                    <!-- Content -->
                    <div class="col-xl-6 col-lg-6 col-md-5 col-sm-12 layout-top-spacing">

                        <div class="skills layout-spacing ">
                            <div class="widget-content widget-content-area">
                                <h3 class="">Update Customer Info</h3>
                                <form method="POST">
                               <?php
$hosID = $_SESSION['hosID'];
 $sql=mysqli_query($con,"select * from customers where id='$did'");
while($data=mysqli_fetch_array($sql))
{
?>
                     <div class="form-group">
                    <label for="exampleInputEmail1">Customer Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlentities($data['name']);?>">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail1">Email Address</label>
                    <input type="text" class="form-control" name="emailid" value="<?php echo htmlentities($data['email']);?>">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputEmail1">Phone Number</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo htmlentities($data['phone']);?>">
                  </div>
                 
                  <div class="form-group">
                      <label>Gender</label>
                      <span style="color:red">*</span><br>
                       <select class="form-control select2" style="width: 100%;" name="sex" required>
                                            <option value="<?php echo htmlentities($data['gender']);?>"><?php echo htmlentities($data['gender']);?></option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            
                                            
                      </select>
                    </div>
                      <div class="form-group">
                    <label for="exampleInputEmail1">Address</label>
                    <input type="text" class="form-control" name="address" value="<?php echo htmlentities($data['address']);?>">
                  </div>
                  <div class="form-group">
                      <label>Branch</label>
                      <span style="color:red">*</span><br>
                      <select name="branch" id="" class="form-control">
                          <?php 
                          $categorySql = mysqli_query($con,"select * from branch");
                          $cnt=1;
                          while($crow=mysqli_fetch_array($categorySql))
                          {
                          $selected = $crow['facilityID'] == $data['facilityID'] ? "selected" : "";
                          echo "<option value='{$crow['facilityID']}' $selected>{$crow['name']}</option>";
                          }
                          ?>
                      </select>
                  </div>
                  

                        <div class="modal-footer">
                  <button type="submit" class="btn btn-primary" name="submit">Save</button>
                </div>
                        </div>
                        </form>
                    </div>

                        
                    </div>

           


                            

                        
                    
                </div>
                </div>
       <?php include('footer.php'); ?>
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
    <!-- END PAGE LEVEL CUSTOM SCRIPTS -->


    <script src="../plugins/highlight/highlight.pack.js"></script>
    
    <!-- END GLOBAL MANDATORY SCRIPTS -->

    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    
    <script src="../plugins/select2/select2.min.js"></script>
    <script src="../plugins/select2/custom-select2.js"></script>
</body>
</html>
<?php } ?>




