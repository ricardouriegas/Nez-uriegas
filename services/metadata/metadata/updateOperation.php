<?php
/*
* Update operation
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/
require_once 'functions.php';
require_once 'DbHandler.php';

if(isset($_GET['operationId'])) {
	$operationId = $_GET['operationId'];
	$db = new DbHandler();
	$result = $db->updateOperation($operationId);
	if ($result) {
		$response['status'] = 200;
		$response['message'] = 'ok';
	} else {
		$response['status'] = 'error';
		$response['message'] = 'Not update operation';
	}
} else {
	if(isset($_GET['chunkId'])) {
		$chunkId = $_GET['chunkId'];
		$db = new DbHandler();
		$result = $db->getOperationIdByChunkId($chunkId);
		$operationId = $result[0]['id'];
		$result = $db->updateOperation($operationId);
		if ($result) {
			$response['status'] = 200;
			$response['message'] = 'ok';
		} else {
			$response['status'] = 'error';
			$response['message'] = 'Not update operation';
		}
	} else {
		$response['status'] = 'error';
		$response['message'] = 'Fields empty';
	}
}
echoRespnse($response);
?>