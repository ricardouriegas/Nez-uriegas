<?php 
require "DbHandler.php";



/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET['tokenuser']) && isset($_GET['keyresource']) && isset($_GET['proportion']) ) {
	 
		get_new_files($_GET['tokenuser'], $_GET['keyresource'],  $_GET['proportion']);
	
}else{
echo "error";
	get_new_files($argv[1], $argv[2], $argv[3]);

}
/**
 * *
 * @param  [String] $tokenuser   	 [token del usuario]
 * @param  [String] $keyresource 	 [c6315fae8d30575901a44a0e8cfde3375be50e433lave del recurso catálogo o grupo]
 * @param  [String] $namefile    	 [nombre del archivo]
 * @param  [String] $sizefile    	 [peso del archivo]
 * @param  [String] $dispersemode    [algoritmo de dispersion (IDA, RAID5, SINGLE )]
 */
 
function get_new_files($tokenuser,$keyresource, $proportion){
	header('Content-type: application/json; charset=utf-8');
	$db = new DbHandler();
	try{
		$current_time = time();
		//$files = $db->getKeyFile();
		
		require_once "models/Curl.php";

		$url = 'http://'.AUTH_HOST.'/auth/v1/users?access_token='.$tokenuser;
		$curl = new Curl();
		$response1 = $curl->get($url);
		

		//$url = 'http://'.PUB_SUB_HOST.'/subscription/v1/catalogs/'.$keyresource.'/files_key';
		$url = 'http://'.$_ENV['PUB_SUB_HOST'].'/subscription/v1/visualization?access_token='.$tokenuser;
		$data['show'] = 'FILES';
		$data['by'] = 'CATALOG';
		$data['tokencatalog'] = $keyresource;
		//echo json_encode($data);
		$curl = new Curl();
		$response = $curl->post($url,$data);
		//print_r($response);
		if ($response['code']==200) {
			$res['status'] = 200;
			$res['message'] = $response['data']['data'];
			$res['time'] = $current_time;
			echo json_encode($res);
		}else{
			echo json_encode(array("status" => 403 ,"message" => "Forbidden"));
		}
	
	}
	catch(PDOException $e) {
		print_r($e);
		echo json_encode(array("status" => 403 ,"message" => "Forbidden"));
	}
}
?>
