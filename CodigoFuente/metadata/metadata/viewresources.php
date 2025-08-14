<?php 
require "DBconnect.php";
/**
 * Validar que los datos no esten vacios
 */
if(isset($_GET['tokenuser']) && isset($_GET['typeresource']) && isset($_GET['option']) ){
	if(!empty($_GET['tokenuser']) && !empty($_GET['typeresource']) && !empty($_GET['option']))
		viewResources($_GET['tokenuser'],$_GET['typeresource'],$_GET['option']);
}
/**
 * *
 * @param  [String] $tokenuser    [token del usuario]
 * @param  [String] $typeresource [tipo de recurso catálogo(1),grupo(2)]
 * @param  [String] $option       [opción de visualización: recursos personales(subscribe), todos(all)]
 */
function viewResources($tokenuser,$typeresource,$option) {
	//obtener la clave principal del usuario
	$keyuser = getkeyuser($tokenuser);
	try{
		$connection = getConnection();
		if($option === "all"){
			$dbh = $connection->prepare("SELECT * FROM resources WHERE typeresource = :type");
			$dbh->bindParam(":type", $typeresource);
       		        $dbh->execute();
	                $catalogs = $dbh->fetchAll(PDO::FETCH_ASSOC);
		}
		else if($option === "myresources"){
			$dbh = $connection->prepare("SELECT r.keyresource,r.nameresource,r.typeresource,r.time  FROM resources as r inner join subscribe as s on  s.keyresource = r.keyresource WHERE 
			s.keyuser =:keyuser  AND r.typeresource =:type");
			$dbh->bindParam(":keyuser", $keyuser);
			$dbh->bindParam(":type", $typeresource);
			$dbh->execute();
               		$catalogs = $dbh->fetchAll(PDO::FETCH_ASSOC);

		}
		else{
			$dbh = $connection->prepare("SELECT keyresource from subscribe where keyuser=:keyuser");
                        $dbh->bindParam(":keyuser", $keyuser);
			$dbh->execute();
			$re = array();
			while($cat =  $dbh->fetch()){
			  $re[] = $cat["keyresource"];
			}
			$dbh = $connection->prepare("SELECT keyresource from resources");
			$dbh->execute();
			$re2 = array();
			while($cat =  $dbh->fetch()){
                          $re2[] = $cat["keyresource"];
                        }
			$catalogs = array_merge(array_diff($re, $re2), array_diff($re2, $re));
		}
		$connection = null;

		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 200, "message" => $catalogs));
	}
	catch(PDOException $e) {
		header('Content-type: application/json; charset=utf-8');
		echo json_encode(array("status" => 403, "message" => "Forbidden"));
	}
}
?>
