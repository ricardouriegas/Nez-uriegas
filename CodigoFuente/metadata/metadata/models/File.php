<?php 

require_once "Rest.php";
//require_once "db/DbHandler.php";
require_once "DbHandler.php";
require_once "Curl.php";

$subscription = $_ENV['URL_SUBSCRIPTION'];
define('url_subscription', $subscription);

class File extends REST {
    
    /*private function setCatalog($id, $data) {
        $url = url_subscription . '/subscription/catalogs/'. $id . '/publish/';
        $curl = new Curl();
        $response = $curl->post($url, $data);
        return $response;
    }*/

    public function all(){    
        if ($this->getRequestMethod() != "GET"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        $db = new DbHandler();
        $data = $db->getAllFiles();
        if ($data) {
            $this->response($this->json($data), 200);     
        } else {
            $error = array("message" => "No data.");
            $this->response($this->json($error), 404);
        } 
    }

    public function get($id) {
        if ($this->getRequestMethod() != "GET"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        $db = new DbHandler();
        $data = $db->getFile($id);
        //print_r($data);
        if ($data) {
            $this->response($this->json($data), 200);     
        } else {
            $error = array("message" => "No such file: ". $id);
            $this->response($this->json($error), 404);
        }      
    }

    public function create() {
        if ($this->getRequestMethod() != "POST"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        if (isset($this->_request['id']) && isset($this->_request['name']) && isset($this->_request['size']) && isset($this->_request['userId'])) {
            $db = new DbHandler();
            $data = $db->createFile($this->_request);
            if ($data) {
                //$send = array("user_id" => $this->_request['userId'], "file_id" => $this->_request['id']);
                //$response =  $this->setCatalog($this->_request['userId'], $send);
                //if ($response['code'] == 201) {
                    $data = array("message" => "File successfully registered.");
                    $this->response($this->json($data), 201);
                //}    
            } else {
                $error['message'] = "File not registered.";
                $this->response($this->json($error), 400);
            }
        } else {
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 400);
        }
    }

    public function delete($id) {
        if ($this->getRequestMethod() != "DELETE"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        $db = new DbHandler();
        $data = $db->deleteFile($id);
        if ($data) {
            $message = array("deleted" => $id);
            $this->response($this->json($message), 200);    
        } else {
            $error = array("message" => "No file found.");
            $this->response($this->json($error), 404); 
        }    
    }

    public function deleteAll() {
        if ($this->getRequestMethod() != "DELETE"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        $file = new DbHandler();
        $file->deleteFiles();
        if ($file) {
            $message = array("message" => "deleted files");
            $this->response($this->json($message), 200);    
        } else {
            $error = array("message" => "No files found.");
            $this->response($this->json($error), 404); 
        }
    }

}

 ?>