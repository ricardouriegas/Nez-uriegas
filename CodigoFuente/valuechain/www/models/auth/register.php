<?php 
include_once("../../includes/conf.php");
include_once(SESIONES);
include_once(CLASES . "/class.Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");


$url = APIGATEWAY_HOST.'/auth/v1/users/create';
$curl = new Curl();
$response = $curl->post($url, $_POST);

//print_r($response);

if (isset($response['data'])) {
    echo json_encode($response['data']);
}else{
    echo 'Error';
}
?>