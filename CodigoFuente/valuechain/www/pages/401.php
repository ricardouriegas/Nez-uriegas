<?php

//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../includes/conf.php");
include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if(empty($_SESSION['idUser'])){
  header("Location: ". PROJECT_ROOT ."/pages/login.php");
}

?>


<!DOCTYPE html>
<html lang="en">
<!-- header -->
<?php
  $title = "Dashboard";
  include_once( VISTAS .  "/indexelements/head.php");
?>


<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <?php 
    include_once(VISTAS .  "/indexelements/navbar.php");
    //<!-- Right navbar links -->
    include_once(VISTAS . "/indexelements/notifications.php");
    ?>
    
    
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <?php
  include_once(VISTAS . "/indexelements/menu.php");
  ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">401 Unauthorized</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">401</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="error-page">
        <h2 class="headline text-warning"> 404</h2>

        <div class="error-content">
          <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Unauthorized.</h3>

          <p>
            You are not authorized to access to this page.
            Meanwhile, you may <a href="../index.php">return to dashboard</a>.
          </p>
        </div>
        <!-- /.error-content -->
      </div>
      <!-- /.error-page -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->


  <!-- Main Footer -->
  <?php
  include_once(VISTAS . "/indexelements/footer.php");
  ?>
</div>
<!-- ./wrapper -->

<?php
include_once(VISTAS . "/indexelements/scripts.php");
?>
<!-- BS-Stepper -->
<script src="/<?php echo PROJECT_HOME?>/views/plugins/bs-stepper/js/bs-stepper.min.js"></script>
<script src="/<?php echo PROJECT_HOME?>/views/plugins/toastr/toastr.min.js"></script>
<script src="/<?php echo PROJECT_HOME?>/views/js/functions.js"></script>

</body>
</html>