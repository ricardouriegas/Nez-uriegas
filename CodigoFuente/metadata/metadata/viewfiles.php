<?php 
require "DBconnect.php";
/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET['tokenuser']) && isset($_GET['keyresource'])){
	if(!empty($_GET['tokenuser']) && !empty($_GET['keyresource']))
		viewFiles($_GET['tokenuser'], $_GET['keyresource']);
}
/**
 * *
 * @param  [String] $tokenuser   [token del usuario]
 * @param  [String] $keyresource [clave del recurso catálogo o grupo]
 */
function viewFiles($tokenuser,$keyresource) {
	//obtener la clave principal del usuario
	$keyuser = getkeyuser($tokenuser);
	try{
		$connection = getConnection();
		//validar que el usuario este suscrito al recurso
		$dbh = $connection->prepare("SELECT * FROM subscribe WHERE keyuser = ? AND keyresource = ?");
		$dbh->bindParam(1, $keyuser);
		$dbh->bindParam(2, $keyresource);
		$dbh->execute();
		if(!$dbh->rowCount()){
			header('Content-type: application/json; charset=utf-8');
			echo json_encode(array("status" => 403, "message" => "Not Autorized"));
			return;
		}

/*		$dbh = $connection->prepare("SELECT namefile,sizefile,files.time,files.keyfile FROM push,files WHERE push.keyfile = files.keyfile AND push.keyresource = ?");

	*/
	$dbh = $connection->prepare("SELECT f.namefile, f.keyfile FROM files as f inner join push as p on p.keyfile = f.keyfile WHERE  p.keyresource =:keyresource");	
	$dbh->bindParam(":keyresource", $keyresource);
		$dbh->execute();
		$files = $dbh->fetchAll(PDO::FETCH_ASSOC);
		$connection = null;

		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 200, "message" => $files));
	}
	catch(PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403, "message" => "Forbidden"));
	}
}
?>
