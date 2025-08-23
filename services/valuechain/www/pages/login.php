<?php 
//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../includes/conf.php");
include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if(!empty($_SESSION['idUser'])){
  header("Location:../index.php");
}


$title = "Log in | Nez";

?>

<!DOCTYPE html>
<html lang="en">
<?php
  include_once( VISTAS .  "/indexelements/head.php");
?>
<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <!-- <a class="h1"><b>Nez</b></a> -->
      <img src="/views/icons/nez2-logo-512.png" alt="Nez Logo"  style="opacity: .8; width: 300px; height: 100px;">
    </div>
    <div class="card-body">
      <p class="login-box-msg">Sign in to start your session</p>

      <form id="loginform" method="post">
        <div class="input-group mb-3">
          <input type="email" name="email" id="txtEmail" class="form-control" placeholder="Email" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" id="txtPassword" class="form-control" placeholder="Password" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8" id="message">
            
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <!-- /.social-auth-links -->

      <p class="mb-1">
        <a href="forgot-password.html">I forgot my password</a>
      </p>
      <p class="mb-0">
        <a href="register.php" class="text-center">Register a new user</a>
      </p>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->
<!-- AdminLTE App -->

<!-- jQuery -->
<?php
include_once(VISTAS . "/indexelements/scripts.php");
?>
<script src="/<?php echo PROJECT_HOME?>/views/js/login.js"></script>
</body>
</html>
