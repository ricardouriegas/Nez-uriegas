<?php

//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../../includes/conf.php");
include_once(SESIONES);
include_once(CLASES . "/class.Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if (empty($_SESSION['idUser'])) {
  header("Location: " . PROJECT_ROOT . "/pages/login.php");
}

//print_r($_SESSION);

$user = isset($_SESSION['id']) ? $_SESSION['id'] : -1;


if (isset($_GET["id"])) {
  $curl = new Curl();
  $url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/" . $_GET["id"] . "?access_token=" . $_SESSION['tokenuser'];
  $workflow_metadata = $curl->get($url);
  if ($workflow_metadata["code"] == 200) {
    $url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/" . $_GET["id"] . "/stages?access_token=" . $_SESSION['tokenuser'];
    #echo $url;
    $workflow_stages = $curl->get($url);

    /*foreach($workflow_stages["data"] as $ws){
      print_r($ws);
    }*/

    $url = "http://" . VALUE_CHAIN_API . "/api/v1/deployments/" . $_GET["id"] . "?access_token=" . $_SESSION['tokenuser'];
    $workflow_deployments = $curl->get($url);
    if ($workflow_deployments["code"] == 200) {
      $workflow_deployments = $workflow_deployments["data"];
    }

    $url = "http://" . VALUE_CHAIN_API . "/api/v1/executions/" . $_GET["id"] . "?access_token=" . $_SESSION['tokenuser'];
    $workflow_executions = $curl->get($url);
    if ($workflow_executions["code"] == 200) {
      $workflow_executions = $workflow_executions["data"];
    }
    

    $url = "http://" . VALUE_CHAIN_API . "/api/v1/platforms?access_token=" . $_SESSION['tokenuser'];
    $platforms = $curl->get($url);
    if ($platforms["code"] == 200) {
      $platforms = $platforms["data"];
    }

    $url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/conf?access_token=" . $_SESSION['tokenuser'];
    $conf_file = $curl->post($url, ["puzzle_name" => $workflow_metadata["data"]["name"], "id" =>  $_GET["id"]]);
    
    if ($conf_file["code"] == 200) {
      $conf = $conf_file["data"]["conf"];
    }
  } else {
    header("Location: " . PROJECT_ROOT . "/pages/401.php");
  }
} else {
  header("Location: " . PROJECT_ROOT . "/pages/401.php");
}
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
              <h1 class="m-0">Puzzle <strong><?php echo $workflow_metadata["data"]["name"]; ?></strong> </h1>
              <p class="text-info">Created: <?php echo $workflow_metadata["data"]["created"]; ?> </p>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Puzzle <?php echo $workflow_metadata["data"]["name"]; ?></li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <div class="content">

        <div class="row">
          <div class="col-sm-4"></div>
          <div class="col-sm-2">
            <button type="button" data-toggle="modal" data-target="#modal-executeStructure" class="btn btn-primary btn-block float-sm-right"><i class="fa fa-play"></i> Execute puzzle</button>
          </div>
          <div class="col-sm-2">
            <button type="button" data-toggle="modal" data-target="#modal-deployStructure" class="btn btn-info btn-block float-sm-right"><i class="fa fa-cubes"></i> Deploy puzzle</button>
          </div>
          <div class="col-sm-2">
            <button type="button" data-toggle="modal" data-target="#modal-stopStructure" class="btn btn-danger btn-block float-sm-right"><i class="fa fa-stop"></i> Stop puzzle</button>
          </div>
          <div class="col-sm-2">
            <a type="button" href="../catalogs/list.php?puzzle=<?php echo $_GET["id"]; ?>&name=<?php echo $workflow_metadata["data"]["name"]; ?>" class="btn btn-success btn-block float-sm-right"><i class="fa fa-eye"></i> See results</a>
          </div>
        </div>
        <br>

        <h3>Puzzle Information</h3>
        <div class="row">
          <div class="col-12 col-sm-12">
            <div class="card card-primary card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill" href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home" aria-selected="true">Graphical representation</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill" href="#custom-tabs-one-profile" role="tab" aria-controls="custom-tabs-one-profile" aria-selected="false">Pieces</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-one-messages-tab" data-toggle="pill" href="#custom-tabs-one-messages" role="tab" aria-controls="custom-tabs-one-messages" aria-selected="false">Requirements</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-one-settings-tab" data-toggle="pill" href="#custom-tabs-one-settings" role="tab" aria-controls="custom-tabs-one-settings" aria-selected="false">Sources</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-one-conf-tab" data-toggle="pill" href="#custom-tabs-one-conf" role="tab" aria-controls="custom-tabs-one-conf" aria-selected="false">Configuration file</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="custom-tabs-one-norms-tab" data-toggle="pill" href="#custom-tabs-one-norms" role="tab" aria-controls="custom-tabs-one-norms" aria-selected="false">Norms</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-one-tabContent">
                  <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel" aria-labelledby="custom-tabs-one-home-tab">
                    <svg height="400px" id="svgCanvas" style="width:100%;"></svg>
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel" aria-labelledby="custom-tabs-one-profile-tab">
                    <div class="row" id="listpieces"></div>
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-one-messages" role="tabpanel" aria-labelledby="custom-tabs-one-messages-tab">
                    <div class="row" id="listreqs"></div>
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-one-settings" role="tabpanel" aria-labelledby="custom-tabs-one-settings-tab">
                    <div class="row" id="listsources"></div>
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-one-conf" role="tabpanel" aria-labelledby="custom-tabs-one-conf-tab">
                    <div class="row" id="confDiv">
                      <?php echo str_replace("\n", "<br>", $conf); ?>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="custom-tabs-one-norms" role="tabpanel" aria-labelledby="custom-tabs-one-norms-tab">
                    <div class="row" id="normsDiv">
                      <img src="/resultsdeployment/<?php echo $workflow_metadata["data"]["name"]; ?>/COBIT 5_compliance_graph.png" alt="">
                      <img src="/resultsdeployment/<?php echo $workflow_metadata["data"]["name"]; ?>/ISO 27001-13_compliance_graph.png" alt="">
                      <img src="/resultsdeployment/<?php echo $workflow_metadata["data"]["name"]; ?>/NIST_compliance_graph.png" alt="">
                      <img src="/resultsdeployment/<?php echo $workflow_metadata["data"]["name"]; ?>/Norma Oficial Mexicana NOM-024-SSA3-2010_compliance_graph.png " alt="">
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card -->
            </div>
          </div>
        </div>

        <h3>Deployments and executions</h3>
        <div class="row">
          <div class="col-12 col-sm-12">
            <div class="card card-primary card-tabs">
              <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="executionsnddeployments-tab" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="tabs-executions-tab" data-toggle="pill" href="#tabs-executions" role="tab" aria-controls="tabs-executions" aria-selected="true">Executions</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="deployments-tab" data-toggle="pill" href="#deployments" role="tab" aria-controls="deployments" aria-selected="false">Deployments</a>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content" id="custom-tabs-one-tabContent">
                  <div class="tab-pane fade show active" id="tabs-executions" role="tabpanel" aria-labelledby="tabs-executions-tab">
                    <table id="executionsTable" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Deployed at</th>
                          <th>Platform</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody id="tblexecutionsBody">
                        <?php foreach ($workflow_executions as $wd) : ?>

                          <tr>
                            <td><?php echo $wd["execution_id"]; ?></td>
                            <td><?php echo $wd["executed"]; ?></td>
                            <td><?php echo $wd["platform"]; ?></td>
                            <td><?php echo $wd["status"]; ?></td>
                            <td>
                              <div class="btn-group"><a type="button" class="btn btn-default" onclick="showLogs(<?php echo $wd["execution_id"]; ?>, '<?php echo $workflow_metadata["data"]["name"]; ?>', 'execution')">See logs</a disabled><button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" disabled><span class="sr-only">Toggle Dropdown</span></button>
                                <div class="dropdown-menu" role="menu"><a class="dropdown-item" onclick="showLogs(<?php echo $wd["execution_id"]; ?>, '<?php echo $workflow_metadata["data"]["name"]; ?>', 'execution')">See logs</a></div>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>ID</th>
                          <th>Deployed at</th>
                          <th>Platform</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  <div class="tab-pane fade" id="deployments" role="tabpanel" aria-labelledby="deployments-tab">
                    <table id="deploymentsTable" class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Deployed at</th>
                          <th>Platform</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody id="tblDeploymentsBody">
                        <?php foreach ($workflow_deployments as $wd) : ?>

                          <tr>
                            <td><?php echo $wd["execution_id"]; ?></td>
                            <td><?php echo $wd["executed"]; ?></td>
                            <td><?php echo $wd["platform"]; ?></td>
                            <td><?php echo $wd["status"]; ?></td>
                            <td>
                              <div class="btn-group"><a type="button" class="btn btn-default" onclick="showLogs(<?php echo $wd["execution_id"]; ?>, '<?php echo $workflow_metadata["data"]["name"]; ?>', 'deployment')">See logs</a disabled><button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown" disabled><span class="sr-only">Toggle Dropdown</span></button>
                                <div class="dropdown-menu" role="menu"><a class="dropdown-item" onclick="showLogs(<?php echo $wd["execution_id"]; ?>, '<?php echo $workflow_metadata["data"]["name"]; ?>', 'deployment')">See logs</a></div>
                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                      <tfoot>
                        <tr>
                          <th>ID</th>
                          <th>Deployed at</th>
                          <th>Platform</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>
      </div>

    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->


  <div class="modal fade" id="modal-deployStructure">
    <div class="modal-dialog">
      <div class="modal-content" id="deployStructure">
        <div class="overlay" id="divOverlayDeploy">
          <i class="fas fa-2x fa-sync fa-spin"></i>
        </div>
        <div class="modal-header">
          <h4 class="modal-title">Deploy service</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="frmDeployStructure">
          <div class="modal-body">
            <p>Please choose the platform to deploy your service.</p>
            <div class="form-group">
              <select class="custom-select rounded-0" id="slPlatform">
                <?php foreach ($platforms as $p) : ?>
                  <option value="<?php echo $p["id"]; ?>"><?php echo $p["platform"]; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <input type="hidden" value="<?php echo $_GET["id"]; ?>" id="txtID" value="id"></input>
            <input type="hidden" value="<?php echo $workflow_metadata["data"]["name"]; ?>" id="txtPuzzleName" value="id"></input>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="btnDeploy">Deploy</button>
          </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
                
  <div class="modal fade" id="modal-stopStructure">
    <div class="modal-dialog">
      <div class="modal-content" id="stopStructure">
        <div class="overlay" id="divOverlayStop">
          <i class="fas fa-2x fa-sync fa-spin"></i>
        </div>
        <div class="modal-header">
          <h4 class="modal-title">Stop puzzle</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="frmstopStructure">
          <div class="modal-body">
            <p>Do you want to stop the puzzle?</p>
            <input type="hidden" value="<?php echo $_GET["id"]; ?>" id="txtID"></input>
            <input type="hidden" value="<?php echo $workflow_metadata["data"]["name"]; ?>" id="txtPuzzleName"></input>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="btnStop">Stop</button>
          </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

  <div class="modal fade" id="modal-executeStructure">
    <div class="modal-dialog">
      <div class="modal-content" id="executeStructure">
        <div class="overlay" id="divOverlayExecution">
          <i class="fas fa-2x fa-sync fa-spin"></i>
        </div>
        <div class="modal-header">
          <h4 class="modal-title">Execute puzzle</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form id="frmexecuteStructure">
          <div class="modal-body">
            <p>Do you want to start processing the service's source?</p>
            <input type="hidden" value="<?php echo $_GET["id"]; ?>" id="txtID"></input>
            <input type="hidden" value="<?php echo $workflow_metadata["data"]["name"]; ?>" id="txtPuzzleName"></input>
          </div>
          <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="btnExecute">Execute</button>
          </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

  <div class="modal fade" id="puzzlelogs">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="overlay" id="divOverlayLogs">
          <i class="fas fa-2x fa-sync fa-spin"></i>
        </div>
        <div class="modal-header">
          <h4 class="modal-title">Logs</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="divLogs"></div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="downloadLogs()">Download logs</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

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
  <script src="/<?php echo PROJECT_HOME ?>/views/plugins/toastr/toastr.min.js"></script>
  <script src="https://d3js.org/d3.v7.min.js"></script>
  <script src="https://unpkg.com/d3-dag@0.8.1"></script>
  <script src="/<?php echo PROJECT_HOME ?>/views/js/puzzle.js"></script>
  <script src="/<?php echo PROJECT_HOME ?>/views/js/functions.js"></script>

</body>

</html>
