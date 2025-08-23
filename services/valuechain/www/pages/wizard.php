<?php

//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../includes/conf.php");
include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if(empty($_SESSION['idUser'])){
  header("Location: ". PROJECT_ROOT ."/pages/login.php");
}

//print_r($_SESSION);

$user = isset($_SESSION['id']) ? $_SESSION['id'] : -1;

$_SESSION['actual_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

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
            <h1 class="m-0">Service creation</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Service creation</li>
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
          <div class="col-md-12">
                <div class="bs-stepper">
                  <div class="bs-stepper-header" role="tablist">
                    <!-- your steps here -->
                    <div class="step" data-target="#choose-apps">
                      <button type="button" class="step-trigger" role="tab" aria-controls="choose-apps" id="choose-apps-trigger">
                        <span class="bs-stepper-circle">1</span>
                        <span class="bs-stepper-label">Choose your pieces</span>
                      </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#choose-reqs">
                      <button type="button" class="step-trigger" role="tab" aria-controls="choose-reqs" id="choose-reqs-trigger">
                        <span class="bs-stepper-circle">2</span>
                        <span class="bs-stepper-label">Choose your requirements</span>
                      </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#choose-data">
                      <button type="button" class="step-trigger" role="tab" aria-controls="choose-data" id="choose-data-trigger">
                        <span class="bs-stepper-circle">3</span>
                        <span class="bs-stepper-label">Choose your data</span>
                      </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#join-apps">
                      <button type="button" class="step-trigger" role="tab" aria-controls="join-apps" id="join-apps-trigger">
                        <span class="bs-stepper-circle">4</span>
                        <span class="bs-stepper-label">Join your apps</span>
                      </button>
                    </div>
                  </div>
                  <div class="bs-stepper-content">
                    <!-- your steps content here -->
                    <div id="choose-apps" class="content" role="tabpanel" aria-labelledby="choose-apps-trigger">
                    
                      <div>
                        <?php include_once("wizard_parts/choose_pieces.php"); ?>
                        <button class="btn btn-primary" onclick="stepper.next()">Next</button>
                      </div>
                      
                    </div>
                    <div id="choose-reqs" class="content" role="tabpanel" aria-labelledby="choose-reqs-trigger">
                      <div>
                          <?php include("wizard_parts/choose_requirements.php"); ?>
                      </div>
                      <button class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                      <button class="btn btn-primary" onclick="stepper.next()">Next</button>
                    </div>
                    <div id="choose-data" class="content" role="tabpanel" aria-labelledby="choose-data-trigger">
                      <div>
                        <div>
                          <?php include("wizard_parts/choose_data.php"); ?>
                        </div>
                        <button class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                        <button class="btn btn-primary" onclick="stepper.next()">Next</button>
                      </div>
                    </div>
                    <div id="join-apps" class="content" role="tabpanel" aria-labelledby="join-apps-trigger">
                      <div>
                        <div>
                          <?php include("wizard_parts/design_structure.php"); ?>
                        </div>
                        <button class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                      </div>
                      
                    </div>
                  </div>
                </div>

          </div>
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- /.control-sidebar -->

  <div class="modal fade" id="modal-default" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
      <div class="modal-content" id="confirmContent">
        <div class="modal-header">
          <h4 class="modal-title">Save service</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="frmSaveStructure">
          <div class="modal-body">
            <p>Please enter a name for your service. This name will help you to identify the service for its deployment or execution.</p>
              <div class="form-group">
                <label for="exampleInputEmail1">Service name</label>
                <input type="text" class="form-control" id="txtServiceName" name="serviceName" placeholder="Enter service name" required>
              </div>
              <div class="form-group">
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="deployAndExecute" checked>
                  <label class="custom-control-label" for="deployAndExecute">Deploy</label>
                </div>
              </div>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

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
<script src="/<?php echo PROJECT_HOME?>/views/js/wizard.js"></script>
<script src="/<?php echo PROJECT_HOME?>/views/plugins/toastr/toastr.min.js"></script>
<script src="/<?php echo PROJECT_HOME?>/views/js/flowy-master/flowy.min.js"></script>
<script src="/<?php echo PROJECT_HOME?>/views/js/functions.js"></script>

</body>
</html>
