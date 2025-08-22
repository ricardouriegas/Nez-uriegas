<?php 
require "DbHandler.php";

/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET)) {
    stats($_GET);
}else{
	stats($argv[1], $argv[2], $argv[3], $argv[4],$argv[5], $argv[6],$argv[7], $argv[8], $argv[9], $argv[10], $argv[11], $argv[12], $argv[13], $argv[14], $argv[15] );
}

/**
 * *
 * @param  [String] $tokenuser   	 [token del usuario]
 * @param  [String] $keyresource 	 [c6315fae8d30575901a44a0e8cfde3375be50e433 llave del recurso catálogo o grupo]
 * @param  [String] $namefile    	 [nombre del archivo]
 * @param  [String] $sizefile    	 [peso del archivo]
 * @param  [String] $dispersemode    [algoritmo de dispersion (IDA, RAID5, SINGLE )]
 */
 
//function stats($tokenuser,$sizefile, $chunks, $pull,$raid, $ida, $beg, $cpabe, $symkey, $readfile, $sym, $decrypt, $download, $retrieve, $keyfile, $organization, $sida, $raid0){

function stats($get){
	//$keyuser = getkeyuser($tokenuser);
	$tamaño= count($get);    
    $ops = array();
    for ($i=6; $i < $tamaño ; $i++) { 
        $ops[array_keys($get)[$i]] = array_values($get)[$i];
    }
	try{
		$db= new DbHandler();
		$id_root = $db->stats_d($get);

		foreach($ops as $id=>$o){
			$db->statsOps($get, $id , $o, $id_root);
		}
  
		$connection = null;
		header('Content-type: application/json; charset=utf-8');
		
		echo json_encode(array("status" => 200, "message"=> "OK"  ));
	}
	catch(PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403 ,"message" => "Forbidden"));
	}
}
?>
