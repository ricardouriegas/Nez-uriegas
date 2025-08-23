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
$curl = new Curl();

if (isset($_GET["puzzle"]) && isset($_GET["name"]) && isset($_GET["father"]) && $_GET["father"] != "/") {

    $catalog = $_GET["father"];


    $url = APIGATEWAY_HOST . '/pub_sub/v1/view/files/catalog/' . $catalog . '?access_token=' . $_SESSION['access_token'];
    $response = $curl->get($url);
    //print_r($response);
    if ($response["code"] == 200) {
        $files = $response["data"]["data"];
    }

    //print_r($files);

    $url = APIGATEWAY_HOST . '/pub_sub/v1/view/catalog/' . $catalog . '?access_token=' . $_SESSION['access_token'];
    $catalog_info =  $curl->get($url)["data"]["data"];

    $granpa = $catalog_info["father"];

    $url = APIGATEWAY_HOST . '/pub_sub/v1/view/catalogs/user/' . $_SESSION['tokenuser'] . '/results/' . $_GET["name"] . '?access_token=' . $_SESSION['access_token'] . "&father=$catalog";
    $response = $curl->get($url);
    if ($response["code"] == 200) {
        $catalogs = $response["data"]["data"];
    }
} else if (isset($_GET["puzzle"]) && isset($_GET["name"])) {

    $url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/" . $_GET["puzzle"] . "/catalogs?access_token=" . $_SESSION['tokenuser'];
    $response = $curl->get($url);
    $catalog = $response["data"][0][0]["catalog"];

    $url = APIGATEWAY_HOST . '/pub_sub/v1/view/catalog/' . $catalog . '?access_token=' . $_SESSION['access_token'];
    $catalog_info =  $curl->get($url)["data"]["data"];
    $granpa = $catalog_info["father"];
    $url = APIGATEWAY_HOST . '/pub_sub/v1/view/catalogs/user/' . $_SESSION['tokenuser'] . '/results/' . $_GET["name"] . '?access_token=' . $_SESSION['access_token'] . "&father=$catalog";
    $response = $curl->get($url);
    if ($response["code"] == 200) {
        $catalogs = $response["data"]["data"];
    }
} else {
    header("Location: " . PROJECT_ROOT . "/pages/401.php");
}

$url = APIGATEWAY_HOST . '/auth/v1/view/users/all?access_token=' . $_SESSION['access_token'];

$response = $curl->get($url);
if ($response['code'] == 200 && isset($response['data']['data'])) {
    //print_r($response['data']);
    $users = $response['data']['data'];
    /*
    echo json_encode($lista);*/
    //echo 'ok';
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
                            <h1 class="m-0">Results of puzzle <strong><?php echo $_GET["name"]; ?></strong> </h1>
                            <p class="text-info">Catalog processed: <?php echo $catalog_info["namecatalog"]; ?> </p>
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
                    <div class="col-sm-1">
                        <a type="button" class="btn btn-outline-primary btn-block" href="list.php?puzzle=<?php echo $_GET["puzzle"] ?>&name=<?php echo $_GET["name"] ?>&father=<?php echo $granpa; ?>"><i class="fa fa-arrow-left"></i> Back</a>
                    </div>
                    <div class="col-sm-9">

                    </div>
                    <div class="col-sm-2">
                        <button type="button" data-toggle="modal" data-target="#publishCatalog" class="btn btn-primary btn-block"><i class="fa fa-share"></i> Publish</button>
                    </div>
                </div>
                <br>
                <p><a href="#">/<?php echo $catalog_info["namecatalog"]; ?></a></p>
                <table id="executionsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Created</th>
                            <th>Type</th>
                            <th>Labels</th>
                        </tr>
                    </thead>
                    <tbody id="tblexecutionsBody">
                        <?php foreach ($catalogs as $c) : ?>

                            <tr>
                                <td><a href="list.php?puzzle=<?php echo $_GET["puzzle"] ?>&name=<?php echo $_GET["name"] ?>&father=<?php echo $c["tokencatalog"] ?>"><?php echo str_replace($catalog_info["namecatalog"] . "/", "",  $c["namecatalog"]); ?></a></td>
                                <td><?php echo $c["created_at"]; ?></td>
                                <td>Catalog</td>
                                <td><span class="badge badge-secondary"><?php echo explode("-", explode("stage_", $c["namecatalog"])[1])[0]; ?></span></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php foreach ($files as $f) : ?>

                            <tr>
                                <td>
                                    <p><?php echo $f["namefile"]; ?></p>
                                </td>
                                <td><?php echo $f["created_at"]; ?></td>
                                <td>File</td>
                                <td><span class="badge badge-secondary"><?php echo explode("-", explode("stage_", $catalog_info["namecatalog"])[1])[0]; ?></span></td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Name</th>
                            <th>Created</th>
                            <th>Type</th>
                            <th>Labels</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.modal-dialog -->
        </div>


        <div class="modal fade" id="publishCatalog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="overlay" id="divOverlayPublish">
                        <i class="fas fa-2x fa-sync fa-spin"></i>
                    </div>
                    <div class="modal-header">
                        <h4 class="modal-title">Publish catalog</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="dropdown bootstrap-select input-group-btn form-control open">
                                <label for="imageBB">Select a user:</label>
                                <input id="txtCatalog" type="hidden" value="<?php echo $catalog; ?>" ></input>
                                <select class="form-control input-group-btn selectpicker" style="z-index: 99999;" data-live-search="true" name="userSl" id="userSl" required>
                                    <?php
                                    foreach ($response['data']['data'] as $key) :
                                        if ($key['tokenuser'] != $_SESSION['tokenuser']) :
                                            echo '<option value="' . $key['tokenuser'] . '">' . $key['username'] . '</option>';
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="publishCatalog()">Publish</button>
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
    <script src="/<?php echo PROJECT_HOME ?>/views/js/functions.js"></script>

</body>

</html>