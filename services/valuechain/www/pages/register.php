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

$url = APIGATEWAY_HOST."/auth/v1/view/hierarchy/all";
$curl = new Curl();
$response = $curl->get($url);
//print_r($response);
if ($response['code']==200 && isset($response['data']['data'])) {
  $table = $response['data']['data'];
}else{
  $table = array();
}

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
      <p class="login-box-msg">Register a new user</p>

      <form id="frmSignUp" method="post">
        <!---<div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Full name" id="txtName" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>--->
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Username" id="txtUsername" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Email" id="txtEmail" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Password" id="txtPassword" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Retype password" id="txtPassword2" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <label for="idOrganization"> Organization  </label>
          <select class="custom-select form-control-border" id="idOrganization" required> 
          <?php
              foreach ($table as $row){
                  echo '<option value="'.$row['tokenorg'].'">'.$row['fullname']." (".$row['acronym'].")".'</option>';
              }
          ?>
          </select>
        </div>
        <div class="row">
          <div class="col-8">
            <div id="message" class="login-box-msg">
              
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <a href="login.php" class="text-center">I already have an account</a>
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
</div>
<!-- /.register-box -->

<?php
include_once(VISTAS . "/indexelements/scripts.php");
?>
<script src="/<?php echo PROJECT_HOME?>/views/js/login.js"></script>
</body>
</html>
