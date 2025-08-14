<?php

require_once 'functions.php';
require_once "Node.php";
require_once 'DbHandler.php';
require_once 'uf.php';
//require "random.php";
require_once "models/Curl.php";

//header('Content-type: application/json; charset=utf-8');
//print_r($_GET);
if (isset($_GET['tokenuser']) && isset($_GET['keyresource']) && isset($_GET['keyfile']) &&  isset($_GET['dispersemode'])) {
	pull($_GET['tokenuser'], $_GET['keyresource'], $_GET['keyfile'], $_GET['dispersemode']);
}

function pull($tokenuser, $keyresource, $keyfile, $dispersemode)
{
	try {
		//obtener la clave principal del usuario
		//$url = 'http://'.AUTH_HOST.'/auth/v1/users?tokenuser='.$tokenuser;
		$curl = new Curl();


		$url = 'http://' . $_ENV['PUB_SUB_HOST'] . '/subscription/v1/catalogs/' . $keyresource;
		$response = $curl->get($url);
		if ($response['code'] == 200) {
			$db = new DbHandler();
			$servers = $db->getAbekeys($keyfile);
			if (!$servers) {
				header('Content-type: application/json; charset=utf-8');
				echo json_encode(array("status" => 401, "message" => "Unauthorized"));
				exit();
			}

			$file = $db->getInfoFile($keyfile);
			$data = array();
			switch ($dispersemode) {
				case "IDA":
				case "SIDA":
					$nodes = downloadChunksReliability($tokenuser, $keyfile, $file['namefile'], $file['sizefile']);
					//print_r($nodes);
					foreach ($nodes as $row) {
						$data[] = array("ruta" => $row);
					}
					break;
				case "RAID0":
				case "RAID5":
					$nodes = downloadChunks($tokenuser, $keyfile, $file['namefile'], $file['sizefile']);
					foreach ($nodes as $row) {
						$data[] = array("ruta" => $row);
					}
					break;
				case "SINGLE":
					$temp = download($tokenuser, $keyfile, $file['sizefile']);
					//print_r($temp);
					$data[] = array("ruta" => $temp);
					break;
			}
			$last_pos = count($servers) - 1;
			$temp = $servers[$last_pos]['url'];
			//print_r($servers[$last_pos]);
			//echo $temp;
			$keys = array();
			$keys[] = array("ruta" => $temp);
			header('Content-type: application/json; charset=utf-8');
			echo json_encode(array("status" => 200, "message" => $data, "key" => $keys));
		} else {
			echo json_encode(array("status" => 404, "message" => "Not Found"));
			exit();
		}
		//}else{
		//	echo json_encode(array("status" => 403, "message" => "Forbiden" ));
		//	exit();
		//}
	} catch (PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403, "message" => "Forbidden"));
	}
}
