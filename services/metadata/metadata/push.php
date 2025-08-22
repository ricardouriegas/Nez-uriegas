<?php

require_once 'functions.php';
require_once "Node.php";
require_once 'DbHandler.php';
require_once 'uf.php';
//require "random.php";
require_once "models/Curl.php";

/**
 * Validar que los datos no esten vacios
 */

if (
	isset($_GET['tokenuser']) && isset($_GET['keyresource']) && isset($_GET['namefile']) && isset($_GET['sizefile']) &&
	isset($_GET['dispersemode']) && isset($_GET["isciphered"])  && isset($_GET["chunks"]) && isset($_GET["hashfile"])
) {

	push(
		$_GET['tokenuser'],
		$_GET['keyresource'],
		$_GET['namefile'],
		$_GET['sizefile'],
		$_GET["dispersemode"],
		$_GET["isciphered"],
		$_GET["chunks"],
		$_GET["hashfile"]
	);
}
/**
 * *
 * @param  [String] $tokenuser   	 [token del usuario]
 * @param  [String] $keyresource 	 [c6315fae8d30575901a44a0e8cfde3375be50e433 llave del recurso catálogo o grupo]
 * @param  [String] $namefile    	 [nombre del archivo]
 * @param  [String] $sizefile    	 [peso del archivo]
 * @param  [String] $dispersemode    [algoritmo de dispersion (IDA, RAID5, SINGLE )]
 */
function push($tokenuser, $keyresource, $namefile, $sizefile, $dispersemode, $isciphered, $chunks, $hashfile)
{
	//obtener la clave principal del usuario
	//$url = 'http://'.AUTH_HOST.'/auth/v1/users?tokenuser='.$tokenuser;
	$curl = new Curl();
	//valida el resource
	$url = 'http://' . PUB_SUB_HOST . '/subscription/v1/catalogs/' . $keyresource;
	//$url = 'http://'.$gateway.'/pub_sub/v1/catalogs/'.$keyresource.'?access_token='.$tokenuser;
	//echo $url;
	$response = $curl->get($url);
	//print_r($response);
	if ($response['code'] == 200) {

		$chunks = intval($chunks);

		$table = array();
		$db = new DbHandler();
		$table = $db->getNodesActive();
		
		foreach ($table as $row) {
			$contenedores[] = $row->url;
		}
		$upload_script = "upload.php";
		$keyfile = $db->generateToken();


		header('Content-type: application/json; charset=utf-8');

		$data = array();
		$reg = $db->registerFile($keyfile, $namefile, $sizefile, $chunks, $isciphered, $hashfile,$dispersemode);
		switch ($dispersemode) {
			case "IDA":
			case "SIDA":
				
				// upload with chunks
				$nodes = uploadChunksReliability($tokenuser, $keyfile, $namefile, $sizefile, $chunks);
				//print_r($nodes);
				if(!$nodes){
					echo json_encode(array("status" => 404, "message" => "Number of nodes available must be equals to $chunks. Active nodes = " . count($table) ));
					exit();
				}
				
				foreach ($nodes as $row) {
					$data[] = array("ruta" => $row);
				}


				break;
			case "RAID0":
			case "RAID5":
				// upload with chunks
				$nodes = uploadChunks($tokenuser, $keyfile, $namefile, $sizefile, $chunks);
				//print_r($nodes);
				if(!$nodes){
					echo json_encode(array("status" => 404, "message" => "Number of nodes available must be equals to $chunks. Active nodes = " . count($table) ));
					exit();
				}
				
				foreach ($nodes as $row) {
					$data[] = array("ruta" => $row);
				}


				break;

			case "SINGLE":
				// upload
				//$nodes = upload($user['keyuser'], 0.2, $keyfile, $namefile, 1, $sizefile);	
				$nodes = upload($tokenuser, 0.2, $keyfile, $namefile, 1, $sizefile);
				//print_r($nodes);	
				$data[] = array("ruta" => $nodes['url']);

				break;
		}
		if ($isciphered == true) {
			#print_r($contenedores);
			$servers3 = emplazador(1, $contenedores, NODES_REQUIRED_PUSH);
			#print_r($servers3);
			while (count($servers3[0]) == 0) {
				$servers3 = emplazador(1, $contenedores, NODES_REQUIRED_PUSH);
			}
			$temp = $servers3[0] . $upload_script . "?file=abekeys/" . $keyfile;
			$down_link = $servers3[0] . "abekeys/" . $keyfile;

			$db->saveAbekeys($keyfile, $down_link);

			$keys = array();
			//print_r($servers3);	
			$keys[] = array("ruta" => $temp);
		} else {
			$keys = array();
		}

		//array("status" => 200, "message" => $data, "key" => $keys )
		$res = array("status" => 200, "message" => $data, "key" => $keys);
		echo json_encode($res);
	} else {
		echo json_encode(array("status" => 404, "message" => "Not Found"));
		exit();
	}

}

function emplazador($esferas, $contenedores, $total_contenedores)
{
	$indice_contenedores = rand(0, $total_contenedores - 1);
	$resultado_contenedores = array();

	for ($i = 0; $i < $esferas; $i++) {
		$resultado_contenedores[] = $contenedores[$indice_contenedores++];
		if ($indice_contenedores >= $total_contenedores) {
			$indice_contenedores = 0;
		}
	}
	return $resultado_contenedores;
}
