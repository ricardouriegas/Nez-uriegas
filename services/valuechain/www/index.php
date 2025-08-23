<?php

//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("includes/conf.php");
include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if(empty($_SESSION['idUser'])){
  header("Location: pages/login.php");
}

//print_r($_SESSION);

$user = isset($_SESSION['id']) ? $_SESSION['id'] : -1;

$_SESSION['actual_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

header("Location: /pages/wizard.php");
die();
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
    //include_once(VISTAS . "/indexelements/notifications.php");
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
            <h1 class="m-0">Dashboard v3</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Dashboard v3</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-6">
            
            <!-- /.card -->
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <?php
  include_once(VISTAS . "/indexelements/footer.php");
  ?>
</div>
<!-- ./wrapper -->

<?php
include_once(VISTAS . "/indexelements/scripts.php");
?>

</body>
</html>
