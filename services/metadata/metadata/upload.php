<?php
require "DBconnect.php";
//require "DbHandler.php";

/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET['tokenuser']) && isset($_GET['file'])) {
	if(!empty($_GET['tokenuser']) && !empty($_GET['file']))
		uploadFile($_GET['tokenuser'],$_GET['file']);
}
function uploadFile($tokenuser,$file) {
	//obtener la clave principal del usuario
	$keyuser = getkeyuser($tokenuser);
	
	$fileData = file_get_contents('php://input');
	$fhandle  = fopen($file, 'wb');
	fwrite($fhandle, $fileData);
	fclose($fhandle);
	header('Content-type: application/json; charset=utf-8');
	echo json_encode(array("status" => 200, "message" => "OK"));
}
?>


