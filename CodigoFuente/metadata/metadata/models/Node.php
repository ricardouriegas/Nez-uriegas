<?php 

require_once "Rest.php";
require_once "db/DbHandler.php";
require_once "Curl.php";

class Node extends REST {

    public function all(){    
        if ($this->getRequestMethod() != "GET"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        $db = new DbHandler();
        $data = $db->getAllNodes();
        if ($data) {
            $this->response($this->json($data), 200);     
        } else {
            $error = array("message" => "No data.");
            $this->response($this->json($error), 404);
        } 
    }

    public function getNodesActive(){    
        if ($this->getRequestMethod() != "GET"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        $db = new DbHandler();
        $data = $db->getNodesActive();
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
        $data = $db->getNode($id);
        if ($data) {
            $this->response($this->json($data), 200);     
        } else {
            $error = array("message" => "No such node: ". $id);
            $this->response($this->json($error), 404);
        }      
    }

    public function create() {
        if ($this->getRequestMethod() != "POST"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        if (isset($this->_request['url']) && isset($this->_request['capacity']) && isset($this->_request['memory'])) {
            $db = new DbHandler();
            $data = $db->createNode($this->_request);
            if ($data) {
                $data = array("message" => "Node successfully registered.");
                $this->response($this->json($data), 201);    
            } else {
                $error['message'] = "Node not registered.";
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
        $data = $db->deleteNode($id);
        if ($data) {
            $message = array("Deleted" => $id);
            $this->response($this->json($message), 200);    
        } else {
            $error = array("message" => "No node found.");
            $this->response($this->json($error), 404); 
        }    
    }

}

 ?>