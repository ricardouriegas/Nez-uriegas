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
                $this->response($this->json($data), 201);    
            } else {
                $error['message'] = "Not register node.";
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



    /////////////////////////////////////////////
    public function getUploadNodes() {
        //validacion de solicitud post get put
        if ($this->getRequestMethod() != "POST"){
            $error = array("message" => "Something went wrong.");
            $this->response($this->json($error), 406);
        }
        //validacion de los datos en la solicitud
        if (isset($this->_request['userid']) && isset($this->_request['fileid']) && isset($this->_request['filename']) && isset($this->_request['filesize']) && isset($this->_request['nodes'])) {
            //funcionalidad
            $data = $createNode($this->_request);


            //validacion de funcionalidad y respuestas (correcta e incorrecta)
            if ($data) {
                $this->response($this->json($data), 201);
            } else {
                $error['message'] = "Not register node.";
                $this->response($this->json($error), 400);
            }
        //fallo validacion de datos
        } else {
            $error = array("message" => "Error datos.");
            $this->response($this->json($error), 400);
        }
    }

}

 ?>