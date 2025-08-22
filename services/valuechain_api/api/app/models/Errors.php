<?php 

require_once "http/Rest.php";
require_once "http/Curl.php";

class Errors extends REST {

    public function notFound() {
        $msg['message'] = 'Resource Not Found';
        $this->response($this->json($msg), 404);
    }
    
}

?>