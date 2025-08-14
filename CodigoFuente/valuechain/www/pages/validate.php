<?php 
//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../includes/conf.php");
include_once(SESIONES);
include_once(CLASES . "/class.Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if(!empty($_SESSION['idUser'])){
  header("Location:../index.php");
}

if(!isset($_GET["code"]) && !isset( $_GET["keyuser"])){
    header("Location:../index.php");
}

$url = APIGATEWAY_HOST."/auth/v1/users/a/".$_GET["code"]."/" . $_GET["keyuser"];
$curl = new Curl();
$response = $curl->get($url);
?>

<!DOCTYPE html>
<html lang="en">
<?php
  include_once( VISTAS .  "/indexelements/head.php");
?>
<body class="hold-transition register-page">
<div class="register-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="../../index2.html" class="h1"><b>Puzzle</b>MESH</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">User validation</p>

      <p class="login-box-msg" > <code><?php echo $response["data"]["message"]  ?></code></p>

      <?php if($response["code"] == 200){
      ?>
        <p class="login-box-msg">
         <a href="login.php" class="text-center">Go to login.</a>
      </p>
      <?php
      } ?>
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
</div>
<!-- /.register-box -->

<?php
include_once(VISTAS . "/indexelements/scripts.php");
?>
</body>
</html>
