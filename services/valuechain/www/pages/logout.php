<?php 

include_once("../includes/conf.php");
include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

unset($_SESSION["idUser"]);
session_unset();
session_destroy();
header("Location: ../index.php");