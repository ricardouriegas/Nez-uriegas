<?php 
//require "DbConnect.php";



/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET['tokenuser']) && isset($_GET['keyuser'])  ) {
	if(!empty($_GET['tokenuser']) && !empty($_GET['keyuser'])  )		
		getCert($_GET['tokenuser'],$_GET['keyuser']);
}
else{
	getCert($argv[1],$argv[2]);
}
/**
 * *
 * @param  [String] $tokenuser   	 [token del usuario]
 * @param  [String] $keyresource 	 [c6315fae8d30575901a44a0e8cfde3375be50e433
lave del recurso catálogo o grupo]
 * @param  [String] $namefile    	 [nombre del archivo]
 * @param  [String] $sizefile    	 [peso del archivo]
 * @param  [String] $dispersemode    [algoritmo de dispersion (IDA, RAID5, SINGLE )]
 */
 
function getCert($tokenuser,$keyuser){

	$certsFolder = "pubcerts";

	$servers = array($_ENV['URL'],
					  "http://localhost", "http://localhost",
				      "http://localhost", "http://localhost");
 
	//$keyuser = getkeyuser($tokenuser);
	
	header('Content-type: application/json; charset=utf-8');
		
	$data = array();
	$data[] = $servers[0] . "/" . $certsFolder . "/" . $keyuser . ".cer"; 
	echo json_encode(array("status" => 200, "message" => $data));
	
}
?>
