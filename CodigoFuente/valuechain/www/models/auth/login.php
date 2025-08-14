<?php
include_once("../../includes/conf.php");
include_once(SESIONES);
include_once(CLASES . "/class.Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");

$url = APIGATEWAY_HOST.'/auth/v1/users/login';
$curl = new Curl();
$response = $curl->post($url, $_POST);


if ($response['code']==200) {
        $_SESSION['connected'] = 1;
        foreach ($response['data']['data'] as $key => $value) {
                $_SESSION[$key] = $value;
        }
        $_SESSION['idUser'] = $response['data']['data']['tokenuser'];
        $_SESSION["pieces"] = array();
        $_SESSION["data"] = array();
        $_SESSION["reqs"] = array();
        /*$_SESSION['keyuser'] = $response['data']['keyuser'];
        $_SESSION['username'] = $response['data']['username'];
        $_SESSION['tokenuser'] = $response['data']['tokenuser'];
        $_SESSION['apikey'] = $response['data']['apikey'];
        //$_SESSION['email'] = $response['data']['email'];
        $_SESSION['tokenorg'] = $response['data']['tokenorg'];
        $_SESSION['access_token'] = $response['data']['access_token'];
        //$_SESSION['fecha'] = $response['data']['fecha'];
        $_SESSION['ip'] = $ip;
        */
        echo json_encode($response);
}elseif (isset($response['data']['message'])) {
        echo json_encode($response);
}else{
        echo 'Error';
}


?>
