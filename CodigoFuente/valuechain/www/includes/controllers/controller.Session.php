<?php
include_once("../../resources/conf.php");
include_once(SESIONES);
include_once(CLASES . "/class.DB.php");
include_once(CLASES . "/class.Logs.php");

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

/**
* ARCHIVO PARA EL MANEJO DE LAS SESIONES
*/

if(isset($_POST['action'])){
  $action = $_POST['action'];
  $session = new Sessions();
  switch($action) {
    case 'check': //checa la sesion
      $x = isset($_SESSION['user']) ? true : false;
      echo json_encode($x);
      break;
    case 'login': //inicio de sesion
      if(isset($_POST['username']) && isset($_POST['password'])){
        $res = $session->login($_POST['username'],$_POST['password']);
        $_SESSION['user'] = $res['username'];
        $_SESSION['email'] = $res['email'];
        $_SESSION['id'] = $res['id'];
        
        if($res['codigo'] == 0){
          $s = Logs::saveSession($res['id'],$ip,$lat,$lon);
          $_SESSION['session_id'] = $s;
        }
        echo json_encode($res);
      }
      break;
    case 'signup': //registro
      if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])){
        $res = $session->signup($_POST['email'],$_POST['username'],$_POST['password']);
        echo json_encode($res);
      }
      break;
  }
}else{ //cierre de sesion
  unset($_SESSION["user"]);
	unset($_SESSION["id"]);
  unset($_SESSION["email"]);
  session_unset();
	session_destroy();
	header("Location: ../../index.php");
}
