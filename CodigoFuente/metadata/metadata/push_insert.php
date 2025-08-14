<?php 
require "DbHandler.php";


$db = new DbHandler();

/**
 * Validar que los datos no esten vacios
 */



if(isset($_GET['tokenuser']) && isset($_GET['keyfile']) && isset($_GET['keyresource'])  ){
	
	require_once "models/Curl.php";
	
	$url = 'http://'.AUTH_HOST.'/auth/v1/users?access_token='.$_GET['tokenuser'];
	$curl = new Curl();
	$response1 = $curl->get($url);
	//$user = $response1['data']['data']['tokenuser'];
	//$url = 'http://'.$_ENV['GEOPORTAL'].'/resources/manager/get_services.php?tokenOrg=' . $response1['data']['data']['tokenorg'];
	//$services = $curl->get($url);
	//echo $services;
	//$gateway = $services['data']['gateway']['ip'] . ":" .$services['data']['gateway']['port'];
	//$gateway = "192.168.1.73" . ":" .$services['data']['gateway']['port'];
	//echo $gateway;
	//$url = 'http://disys1.tamps.cinvestav.mx:20500/pub_sub/v1/catalogs/'.$_GET["keyresource"].'/files/upload';
	$url = 'http://'. GATEWAY  . '/pub_sub/v1/catalogs/'.$_GET["keyresource"].'/files/upload';
	//echo $url;
	$curl = new Curl();
	$data = array('keyfile' => $_GET['keyfile']);
	//echo $url;
	//print_r($url);
	
    $response = $curl->post($url, $data);
	//print_r($response);
	//echo "<pre>"; print_r($response); echo "</pre>";

		//$psh=$db->push($_GET['tokenuser'], $_GET["keyfile"], $_GET["keyresource"]);
		// Registrar archivo en catalogo
		// http://127.0.0.1:47012/subscription/v1/catalogs/$_GET["keyresource"]/publish
		// $_GET["keyfile"]
		if ($response['code'] == 200) {
			echo json_encode(array("status" => 200, "message" => "OK" ));
		}else{
			//header('Content-type: application/json; charset=utf-8');
			echo json_encode(array("status" => 403 ,"message" => "Forbidden"));
		}
}else{
	/*$psh=$db->push($argv[1],$argv[2],$argv[3],$argv[4], $argv[5], $argv[6]);
	if ($psh) {
			echo json_encode(array("status" => 200, "message" => "OK" ));
	}else{
			header('Content-type: application/json; charset=utf-8');
		}*/
	echo json_encode(array("status" => 403 ,"message" => "Forbidden1"));
}



?>
