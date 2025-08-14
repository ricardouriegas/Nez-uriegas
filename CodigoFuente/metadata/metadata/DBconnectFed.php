<?php 
/**
 * @return $conexion [una referencia a la conexión con la BD para realizar operaciones]
 */
function getConnection() {
	try {
		$dbhost = "163.117.148.139";
		//"127.0.0.1";
		$dbuser = "postgres";
		$dbpass = "";
		$dbname = "multi";
		$connection = new PDO("pgsql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
		$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $connection;
	} catch (PDOException $e) {
		echo $e->getMessage();
		exit();
	}
}

/**
 * *
 * @param  [String] $tokenuser [token del usuario]
 * @return [String] $keyuser   [la clave principal del usuario]
 */
function getkeyuser($tokenuser) {
	try{
		$connection = getConnection();
		$dbh = $connection->prepare("SELECT keyuser FROM users WHERE tokenuser = ?");
		$dbh->bindParam(1, $tokenuser);
		$dbh->execute();
		$infouser = $dbh->fetch(PDO::FETCH_ASSOC);
		$connection = null;
		if($dbh->rowCount()){
			$keyuser = $infouser['keyuser'];
			return $keyuser;
		}
		else{
			header('Content-type: application/json; charset=utf-8');
			echo json_encode(array("status" => 401, "message" => "Not Authorized"));
			exit();
		}
	}
	catch(PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403 ,"message" => "Forbidden"));
		exit();
	}
}
?>
