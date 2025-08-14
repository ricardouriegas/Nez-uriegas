<?php 
session_start();

require "DBconnect.php";



if(isset($_GET['nameuser']) && isset($_GET['password']))
{
		if(!empty($_GET['nameuser']) && !empty($_GET['password']))
		{
			login($_GET['nameuser'], $_GET	['password']);
		}
	
}
else{
			login($argv[1], $argv[2]);
}
/**
 * *
 * @param  [String] $tokenuser   [token del usuario]
 * @param  [String] $keyresource [clave del recurso catálogo o grupo]
 */
function login($nameuser,$password) {
	//obtener la clave principal del usuario
	try{
		$connection = getConnection();
		//validar que el usuario este suscrito al recurso
		$dbh = $connection->prepare("SELECT tokenuser, apikey FROM users WHERE nameuser = ? AND password = ?");
		$dbh->bindParam(1, $nameuser);
		$dbh->bindParam(2, $password);
		$dbh->execute();
		if(!$dbh->rowCount()){
			header('Content-type: application/json; charset=utf-8');
			echo json_encode(array("status" => 403, "message" => "Not Autorized"));
			return;
		}

		$keys = $dbh->fetchAll(PDO::FETCH_ASSOC);
		$tokenuser = "";
		$apikey = "";
		foreach($keys as $row){
			$tokenuser = $row["tokenuser"];
			$apikey = $row["apikey"];
			
			$_SESSION["tokenuser"] = $tokenuser;
			$_SESSION["tokenapi"] = $apikey;
		}
		
		$connection = null;

		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 200, "tokenuser" => $tokenuser, "apikey" =>$apikey ));
	}
	catch(PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403, "message" => "Forbidden"));
	}
}
?>
