<?php 
require "DbHandler.php";

/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET['password']) && isset($_GET['nameuser'])) {
	if(!empty($_GET['password']) && !empty($_GET['nameuser']))
		register($_GET['nameuser'], $_GET['password']);
}
else{
	register($argv[1], $argv[2]);
}
/**
 * *
 * @param  [String] $password [contraseña del usuario]
 * @param  [String] $nameuser [nombre del usuario]
 */
function register($nameuser, $password) {
	try{
		$db= new DbHandler();
		$db->register($nameuser, $password);
		//curl_exec(curl_init('163.117.148.139/multi2/register.php?keyuser='.$keyuser.'&password='.$password.'&nameuser='.$nameuser.'&tokenuser='.$tokenuser.'&apikey='.$apikey));

		header('Content-type: application/json; charset=utf-8');
		$data = array(array("nameuser" => $nameuser,"tokenuser" => $tokenuser,"apikey" => $apikey));
		echo json_encode(array("status" => 200, "message" => $data));

	}
	catch(PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 401 ,"message" => "Unauthorized"));
	}
}
?>
