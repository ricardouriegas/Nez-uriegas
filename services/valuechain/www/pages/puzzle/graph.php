<?php

//INCLUYE LOS ARCHIVOS NECESARIOS
include_once("../../includes/conf.php");
include_once(SESIONES);
include_once(CLASES . "/class.Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

if(empty($_SESSION['idUser'])){
  header("Location: ". PROJECT_ROOT ."/pages/login.php");
}

//print_r($_SESSION);

$user = isset($_SESSION['id']) ? $_SESSION['id'] : -1;


if(isset($_GET["id"])){
    $curl = new Curl();
    $url = "http://".VALUE_CHAIN_API."/api/v1/workflows/".$_GET["id"]."?access_token=".$_SESSION['tokenuser'];
    $workflow_metadata = $curl->get($url);
    if($workflow_metadata["code"] != 200){
      header("Location: ". PROJECT_ROOT ."/pages/401.php");
    }
  }else{
    header("Location: ". PROJECT_ROOT ."/pages/401.php");
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graph</title>
</head>
<body>
    <svg></svg>
    
    <?php
    include_once(VISTAS . "/indexelements/scripts.php");
    ?>
    <script src="/<?php echo PROJECT_HOME?>/views/js/raphael.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/graphdracula/1.0.3/dracula.min.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://unpkg.com/d3-dag@0.8.1"></script>
    <script src="/<?php echo PROJECT_HOME?>/views/js/Graph.js"></script>
    <script src="/<?php echo PROJECT_HOME?>/views/js/puzzle.js"></script>
</body>
</html>