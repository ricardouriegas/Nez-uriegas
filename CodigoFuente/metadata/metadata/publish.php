<?php 
require "DBconnect.php";
/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET['tokenuser']) && isset($_GET['nameresource']) && isset($_GET['typeresource'])){
	if(!empty($_GET['tokenuser']) && !empty($_GET['nameresource']) && !empty($_GET['typeresource'])){
		addResource($_GET['tokenuser'],$_GET['nameresource'],$_GET['typeresource']);
	}
}
else{
	addResource($argv[1],$argv[2], 1);
}
/**
 * **
 * @param [String] $tokenuser    [token del usuario]
 * @param [String] $nameresource [nombre del recurso que a agregar]
 * @param [String] $typeresource [tipo de recurso catalogo(1),grupo(2)]
 */
function addResource($tokenuser,$nameresource,$typeresource) {
	$keyresource   = sha1(join('',array(time(),rand())));
	//obtener la clave principal del usuario
	$keyuser = getkeyuser($tokenuser);
	try{
		$connection = getConnection();
		$connection->beginTransaction();

		//inserta recurso
		$dbh = $connection->prepare("INSERT INTO resources VALUES(?, ?, ?)");
		$dbh->bindParam(1, $keyresource);
		$dbh->bindParam(3, $nameresource);
		$dbh->bindParam(2, $typeresource);
		$dbh->execute();

		//relaciona usuario-recurso
		$dbh = $connection->prepare("INSERT INTO users_resources VALUES(?, ?)");
		$dbh->bindParam(1, $keyresource);
		$dbh->bindParam(2, $keyuser);
		$dbh->execute();

		//subscribe al usuario dentro del recurso
		$dbh = $connection->prepare("INSERT INTO subscribe VALUES(?,?)");
		$dbh->bindParam(1, $keyuser);
		$dbh->bindParam(2, $keyresource);
		$dbh->execute();
		
		//curl_exec(curl_init('163.117.148.139/multi2/publish.php?keyresource='.$keyresource.'&keyuser='.$keyuser.'&nameresource='.$nameresource.'&typeresource='.$typeresource));	
		$connection->commit();

		$connection = null;
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 200, "message" => "OK"));
	}
	catch(PDOException $e) {
		$connection->rollBack();
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403 ,"message" => "Forbidden"));
	}
}
?>
