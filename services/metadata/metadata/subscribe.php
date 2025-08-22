<?php 
require "DBconnect.php";
/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET['tokenuser']) && isset($_GET['keyresource'])) {
	if(!empty($_GET['tokenuser']) && !empty($_GET['keyresource']))
		subscribe($_GET['tokenuser'],$_GET['keyresource']);
}
 /**
  * *
  * @param  [String] $tokenuser   [token del usuario]
  * @param  [String] $keyresource [clave del recurso catálogo o grupo]
  */
function subscribe($tokenuser,$keyresource)  {
	//obtener la clave principal del usuario
	$keyuser = getkeyuser($tokenuser);
	try{
		$connection = getConnection();
		
		$res = $connection->query("SELECT COUNT(*) FROM subscribe WHERE keyuser='$keyuser' and keyresource='$keyresource'");

		if ( $res->fetchColumn() > 0){
			header('Content-type: application/json; charset=utf-8');
			echo json_encode(array("status" => 200, "message" => "OK!"));
			return ;
		}

		
		$dbh = $connection->prepare("INSERT INTO subscribe VALUES(?,?)");
		$dbh->bindParam(1, $keyuser);
		$dbh->bindParam(2, $keyresource);

		$dbh->execute();

		//curl_exec(curl_init('163.117.148.139/multi2/subscribe.php?keyuser='.$keyuser.'&keyresource='.$keyresource));

		$connection = null;
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 200, "message" => "OK"));
	}
	catch(PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403 ,"message" => "Forbidden"));
	}
}
?>
