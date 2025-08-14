<?php
/* File : Rest.inc.php
*/
class REST {
        
    public $_allow = array();
    public $_content_type = "application/json";
    public $_request = array();
        
    private $_method = "";      
    private $_code = 200;
        
    public function __construct() {
        $this->inputs();
    }
        
    public function getReferer() {
        return $_SERVER['HTTP_REFERER'];
    }
        
    public function response($data, $status) {
        $this->_code = ($status) ? $status : 200;
        $this->setHeaders();
        //print_r($data);
        echo $data;
        exit;
    }
        
    private function getStatusMessage() {
        $status = array(
                    100 => 'Continue',  
                    101 => 'Switching Protocols',  
                    200 => 'OK',
                    201 => 'Created',  
                    202 => 'Accepted',  
                    203 => 'Non-Authoritative Information',  
                    204 => 'No Content',  
                    205 => 'Reset Content',  
                    206 => 'Partial Content',  
                    300 => 'Multiple Choices',  
                    301 => 'Moved Permanently',  
                    302 => 'Found',  
                    303 => 'See Other',  
                    304 => 'Not Modified',  
                    305 => 'Use Proxy',  
                    306 => '(Unused)',  
                    307 => 'Temporary Redirect',  
                    400 => 'Bad Request',  
                    401 => 'Unauthorized',  
                    402 => 'Payment Required',  
                    403 => 'Forbidden',  
                    404 => 'Not Found',  
                    405 => 'Method Not Allowed',  
                    406 => 'Not Acceptable',  
                    407 => 'Proxy Authentication Required',  
                    408 => 'Request Timeout',  
                    409 => 'Conflict',  
                    410 => 'Gone',  
                    411 => 'Length Required',  
                    412 => 'Precondition Failed',  
                    413 => 'Request Entity Too Large',  
                    414 => 'Request-URI Too Long',  
                    415 => 'Unsupported Media Type',  
                    416 => 'Requested Range Not Satisfiable',  
                    417 => 'Expectation Failed',  
                    500 => 'Internal Server Error',  
                    501 => 'Not Implemented',  
                    502 => 'Bad Gateway',  
                    503 => 'Service Unavailable',  
                    504 => 'Gateway Timeout',  
                    505 => 'HTTP Version Not Supported');
        return ($status[$this->_code])?$status[$this->_code]:$status[500];
    }
        
    public function getRequestMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }
        
    private function inputs() {
        // echo file_get_contents('php://input');
        switch($this->getRequestMethod()) {
            case "POST":
            case "PUT":
            case "GET":
            case "DELETE":
                $query_str = $this->cleanInputs($_GET);
                $parameters = json_decode(file_get_contents('php://input'), true);
                $parameters = $this->cleanInputs($parameters);
                // echo $query_str;
                // echo $parameters;
                $this->_request = array_merge($query_str, $parameters);
                break;
            // case "POST":
            //     $_POST = json_decode(file_get_contents('php://input'), true);
            //     $this->_request = $this->cleanInputs($_POST);
            //     break;
            // case "GET":
            //     $this->_request = $this->cleanInputs($_GET);
            //     break;
            // case "DELETE":
            //     $_DELETE = $this->cleanInputs($_GET);
            //     $_REQUEST = json_decode(file_get_contents('php://input'), true);
            //     $_REQUEST = $this->cleanInputs($_REQUEST);
            //     $this->_request = array_merge($_DELETE, $_REQUEST);
            //     break;
            // case "PUT":
            //     $_PUT = json_decode(file_get_contents('php://input'), true);
            //     $this->_request = $this->cleanInputs($_PUT);
            //     break;
            default:
                $this->response('',406);
                break;
        }
    }       
        
    private function cleanInputs($data) {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->cleanInputs($v);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $data = trim(stripslashes($data));
            }
            $data = strip_tags($data);
            $clean_input = trim($data);
        }
        return $clean_input;
    } 
        
    private function setHeaders(){
        header("HTTP/1.1 " . $this->_code . " " . $this->getStatusMessage());
        header("Content-Type:" . $this->_content_type);
    }

    public function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        } 
        // else {
        //     return utf8_encode($data);
        // }
    }
}   

?>