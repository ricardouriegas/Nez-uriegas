<?php
require "DbHandler.php";
/**
 * Validar que los datos no esten vacios
 */
if (isset($_GET['tokenuser']) && isset($_GET['keyfile'])) {
	if (!empty($_GET['tokenuser']) && !empty($_GET['keyfile'])) {

		if (isset($_GET['sizefile']) && !empty($_GET['sizefile'])) {

			setFileInformation($_GET['tokenuser'], $_GET['keyfile'], $_GET['sizefile']);
		} else {
			getFileInformation($_GET['tokenuser'], $_GET['keyfile']);
		}
	}
} else {
	getFileInformation($argv[1], $argv[2]);
}




function getFileInformation($tokenuser, $keyfile)
{
	$db = new DbHandler();
	$keyuser = $db->getkeyuser($tokenuser);

	$strArray = explode('/',$keyfile);
	$keyfile = end($strArray);

	//try{
	$data = $db->getFile($keyfile);

	if (!$data) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403, "message" => "mx File Not Autorized"));
		return;
	}

	foreach ($data as $row) {
		$sizefile =	$data["sizefile"];
		$namefile =	$data["namefile"];
		$chunks = $data["chunks"];
		$cip    = $data["isciphered"];
		$disperse   = $data["disperse"];
	}

	if ($chunks > 1) {
		$chunks_info = $db->getInfoChunksFile($keyfile);
	}


	header('Content-type: application/json; charset=utf-8');
	$data = array(
		"sizefile" => "$sizefile", "namefile" => "$namefile",
		"chunks" => "$chunks", "isciphered" => $cip, "chunks_info" => $chunks_info, "disperse" => $disperse
	);
	echo json_encode(array("status" => 200, "message" => $data));
	/*}
	catch(PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403, "message" => "Forbidden"));
	} */
}


function setFileInformation($tokenuser, $keyfile, $sizefile)
{
	$db = new DbHandler();
	$keyuser = $db->getkeyuser($tokenuser);
	try {

		$db->setFileInfo($keyfile, $sizefile);

		//curl_exec(curl_init("163.117.148.139/multi2/file.php?keyfile=".$keyfile."&sizefile=".$sizefile."&keyuser=".$keyuser));

		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 200, "message" => "olakeaze"));
	} catch (PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403, "message" => "Forbidden"));
	}
}
