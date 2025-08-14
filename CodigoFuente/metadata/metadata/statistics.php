<?php
/*
* Statistics
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/
require_once 'DbHandler.php';
require_once 'functions.php';
require_once "models/Curl.php";


$response = array();
$response['nodes'] = array();
$delete = 'false';

$db = new DbHandler();
$nodesList = $db->getAllNodes();
$nodesTotal = count($nodesList);

if(isset($_GET['delete'])){
	if ($_GET['delete'] == 'true') {
		$delete = 'true';
		$db->deleteFiles();
		$db->deleteChunks();
		$db->deleteAbekeys();
		$url = 'http://pub_sub/subscription/v1/catalogs/files';
		$curl = new Curl();
		$res = $curl->delete($url,array());
		 

	} 
}

$filesTotal = 0;
$sizeTotal = 0;

for ($i = 0; $i < $nodesTotal; $i++) { 
	// request
	$url = $nodesList[$i]['url'];
	$req = curl_get($url .'statistics.php?count=true' . '&delete=' . $delete);

	// response
	$tmp['id']        = $nodesList[$i]['id'];
	$tmp['url']       = $url;
	$tmp['f_stored']  = $req['count'];
	$sizeTotal       += $req['size'];
	$tmp['size']      = ($req['size']/1000000).' Mb';
	$filesTotal      += $tmp['f_stored'];
	$tmp['f_deleted'] = $req['deleted'];
	array_push($response['nodes'], $tmp);
}
$response['f_total'] = $filesTotal;
$response['s_total'] = ($sizeTotal/1000000). ' Mb';

echo "<pre>";
print_r($response);
echo "</pre>";
?>