<?php

//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../includes/conf.php");
include_once(SESIONES);
include_once(CLASES . "/class.Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if (empty($_SESSION['idUser'])) {
  header("Location: " . PROJECT_ROOT . "/pages/login.php");
}

//print_r($_SESSION);

$user = isset($_SESSION['id']) ? $_SESSION['id'] : -1;

$_SESSION['actual_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$curl = new Curl();
$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows?access_token=" . $_SESSION['tokenuser'];
$response = $curl->get($url);
$puzzles = $response["data"];

?>


<!DOCTYPE html>
<html lang="en">
<!-- header -->
<?php
$title = "Dashboard";
include_once(VISTAS .  "/indexelements/head.php");
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
              <h1 class="m-0">Puzzles</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Puzzles</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <div class="content">
        <div class="container-fluid">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($puzzles as $p) { ?>
                <tr>
                  <td><?php echo $p["id"]; ?></td>
                  <td><?php echo $p["name"]; ?></td>
                  <td><?php echo $p["created"]; ?></td>
                  <td>
                    <div class="btn-group">
                      <a type="button" class="btn btn-default" href="puzzle/puzzle.php?id=<?php echo $p["id"]; ?>">See puzzle</a>
                      <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown">
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="puzzle/puzzle.php?id=<?php echo $p["id"]; ?>">See puzzle</a>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </tfoot>
          </table>
        </div>
        <!-- /.container-fluid -->
      </div>
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
  <script src="/<?php echo PROJECT_HOME ?>/views/plugins/bs-stepper/js/bs-stepper.min.js"></script>
  <script src="/<?php echo PROJECT_HOME ?>/views/js/functions.js"></script>

</body>

</html>