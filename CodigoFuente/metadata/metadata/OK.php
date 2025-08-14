<?php
require "DBconnect.php";




function getkeyuser3($tokenuser) {
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

                        $connection = getConnection2();
                        $dbh = $connection->prepare("SELECT keyuser FROM users WHERE tokenuser = ?");
                        $dbh->bindParam(1, $tokenuser);
                        $dbh->execute();
                        $infouser = $dbh->fetch(PDO::FETCH_ASSOC);
                        $connection = null;
                        if($dbh->rowCount()){
                                header('Content-type: application/json; charset=utf-8');
                                echo json_encode(array("status" => 401, "message" => "Pertenece ala Organizacion B"));
                                exit();
                        }
                        else{
                                header('Content-type: application/json; charset=utf-8');
                         echo json_encode(array("status" => 401, "message" => "Not Authorized"));
                        }

                }
        }
        catch(PDOException $e) {
                header('Content-type: application/json; charset=utf-8');
                echo json_encode(array("status" => 403 ,"message" => "Forbidden"));
                exit();
        }
}

getkeyuser3($argv[1]);
?>

