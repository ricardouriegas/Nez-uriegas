<?php
/**
 * Validar que los datos no esten vacios
 */

include_once './Config.php';
include_once './functions.php';

// print_r($_FILES);/

function uploadFile($tokenuser,$file) {
        //obtener la clave principal del usuario
//      $keyuser = getkeyuser($tokenuser);
        $fileData = file_get_contents('php://input');
        $fhandle  = fopen($file, 'wb');
        fwrite($fhandle, $fileData);
        fclose($fhandle);
        //header('Content-type: application/json; charset=utf-8');
        //echo json_encode(array("status" => 200, "message" => "ok"));
        $response['status']  = 200;
        $response['message'] = 'ok';
        return $response;
}

function upload ($tokenuser,$file){
        if(isset($_FILES['uploadedfile']['tmp_name'])) {
                $tmp_name = $_FILES['uploadedfile']['tmp_name'];
                $name = $_FILES['uploadedfile']['name'];
                // $target_path = FOLDER_UPLOADS.$name;
                $target_path = $file;
                if(move_uploaded_file($tmp_name, $target_path)) {
                        $response['status']  = 200;
                        $response['message'] = 'File uploaded successfully';
                        $response['fileId'] = $name;
                } else{
                        $response['status'] = 'error';
                        $response['message'] = 'You can not write to the file';
                }
        } else {
                $response['status'] = 'error';
                $response['message'] = 'Fields empty';
        }
        return $response;
}

if (isset($_GET['operationId'])) {
        //original
        $timeStart = microtime_float();
        if(isset($_GET['tokenuser']) && isset($_GET['file'])) {
                if(!empty($_GET['tokenuser']) && !empty($_GET['file']))
                        $response = upload($_GET['tokenuser'], $_GET['file']);
        }
        // } else {
        //      $response = upload();
        // }
        if ($response['status'] == 200) {
                $operationId = $_GET['operationId'];
                $fileId = $_GET['file'];
                $url = URL_METADATA.'updateOperation.php?operationId='.$operationId.'&chunkId='.$fileId;
                $response1 = curl_get($url);
        }
        $timeEnd = microtime_float();
        $serviceTime1 = $timeEnd - $timeStart;
        echoRespnse($response);

        if ($response1['status'] == 'ok') {
                $timeStart = microtime_float();
            $urls = $response1['urls'];
            //enviar réplicas
            $file = dirname(__FILE__).'/'.FOLDER_UPLOADS.$fileId;
            foreach ($urls as $key => $value) {
                        $response2 = curl_post_file($value['url'], $file); //send file to url
                        if ($response2['status'] == 'ok') {
                                $url = URL_METADATA.'updateOperation.php?operationId='.$value['id'];
                                curl_get($url);
                        }
            }
                $timeEnd = microtime_float();
                $serviceTime2 = $timeEnd - $timeStart;

                // SERVICE TIME
                //$url = URL_METADATA.'registerServiceTime.php?fileId='.$fileId.'&nodeId='.NODE_ID.'&operationId='.$operationId.'&time='.$serviceTime1.'&type=first';
                //curl_get($url);

                // // SERVICE TIME
                //$url = URL_METADATA.'registerServiceTime.php?fileId='.$fileId.'&nodeId='.NODE_ID.'&operationId='.$operationId.'&time='.$serviceTime2.'&type=replication';
                //curl_get($url);
        }
} else {
        //copia
        //$response = upload();
        //echoRespnse($response);
        if(isset($_GET['tokenuser']) && isset($_GET['file'])) {
                if(!empty($_GET['tokenuser']) && !empty($_GET['file']))
                        $response = uploadFile($_GET['tokenuser'], $_GET['file']);
        }
}
