<?php 

require_once "http/Rest.php";
require_once "http/Curl.php";

class Auth extends REST {

    public function home() {
        if ($this->getRequestMethod() != "GET"){
            $msg['message'] = 'Error.';
            $this->response($this->json($msg), 406);
        }
        $msg['message'] = 'Auth home';
        $this->response($this->json($msg), 200);
    }

    public function notFound() {
        $msg['message'] = 'Not Found.';
        $this->response($this->json($msg), 404);
    }
    
}

?>