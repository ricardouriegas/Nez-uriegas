<?php


$fecha = $_GET['url'];
$ip = explode(",", $fecha, 6);
//echo $arrayFecha[1]."<br>";



$timeBeginPublishFed = microtime(true);
require "DBconnectFed.php";
echo $_GET['url'];
if(isset($_GET['keyFile']) && isset($_GET['url']) && isset($_GET['sizeFile'])  && isset($_GET['organization']) && isset($_GET['idCatalog'])){
 if(!empty($_GET['keyFile']) && !empty($_GET['url']) && !empty($_GET['sizeFile']) && !empty($_GET['organization']) && !empty($_GET['idCatalog'])){
			pushFed($_GET['keyFile'], $_GET['url'], $timeBeginPublishFed, $_GET['sizeFile'], $_GET['organization'], $_GET['idCatalog']);
        }else{echo "empty";}
}


function pushFed( $id_ob, $url, $timeBeginPublishFed, $sizeFile, $organization, $idCatalog){

	$fecha = $_GET['url'];
	$ip = explode(",", $fecha, 6);
	
	for($position=0;$position<6;$position++){
//		echo "<br>".$ip[$position]." IP<br>";
		$obt=0;
		$pub=0;
	        $idChunk=md5(rand());
	        $connection = getConnection();
        	$dbh = $connection->prepare("INSERT INTO data VALUES(?, ?, ?)");
	        $dbh->bindParam(1, $idChunk);
        	$dbh->bindParam(2, $idCatalog);
	        $dbh->bindParam(3, $pub);
        	$dbh->execute();
                $connection = getConnection();
                $dbh = $connection->prepare("INSERT INTO publishfed VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
                $dbh->bindParam(1, $id_ob);
                $dbh->bindParam(2, $ip[$position]);
                $dbh->bindParam(3, $obt);
                $dbh->bindParam(4, $position);
                $dbh->bindParam(5, $organization);
                $dbh->bindParam(6, $idChunk);
                $dbh->bindParam(7, $idCatalog);
                $dbh->bindParam(8, $sizeFile);
                $dbh->execute();
	        $timeEndPublishFed = microtime(true);
	        $timeTotalPublishFed = ($timeEndPublishFed-$timeBeginPublishFed);
        //	echo $timeTotalPublishFed;
	        insertLogs($timeTotalPublishFed, "publishFed",  $idCatalog, $sizeFile);
		
        //	echo ("ok");
	}
}
function insertLogs($time,$typeOper, $idFile, $sizeFile){
        $idLog=md5(rand());
        //insert logs
        $connection = getConnection();
        $dbh = $connection->prepare("INSERT INTO logsfed VALUES(?, ?, ?, ?, ?)");
        $dbh->bindParam(1, $idLog);
        $dbh->bindParam(2, $idFile);
        $dbh->bindParam(3, $typeOper);
        $dbh->bindParam(4, $time);
        $dbh->bindParam(5, $sizeFile);
        $dbh->execute();
	return;
}
?>
