<?php
  // require_once "../models/curl.php";
  // $curl = new Curl();
  const EOL = "\r\n";

  class HttpUploadFile
  {
    private $url;
    private $fields;
    private $fp;
    private $fsize;
    private $fmime;
    private $cfile;
    private $fcontents;
    private $postfields;

    public function __construct($url, $fields=[], $pathfile, $filename){
      if (file_exists($pathfile)) {
        $this->url = $url;
        $this->fields = $fields;
        $this->fp = fopen($pathfile, "r");
        $this->fmime = mime_content_type($pathfile);
        $this->fsize = filesize($pathfile);
        $this->cfile = curl_file_create($pathfile, $this->fmime, $filename);
        $this->fcontents = file_get_contents($pathfile);
        $this->postfields = $this->post_fields();
      }
    }

    private function post_fields(){
      $data = array(
        'file' => $this->cfile,
      );
      return array_merge($this->fields, $data);
    }

    public function upload_w_infile(){
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_PUT => 1,
        CURLOPT_HTTPHEADER => array("Content-Type: " . $this->fmime),
        CURLOPT_UPLOAD => true,
        CURLOPT_INFILE => $this->file,
        CURLOPT_INFILESIZE => $this->fsize,
        CURLOPT_HEADER => false,
      ));
      $response = curl_exec($curl);
      $info = curl_getinfo($curl);
      $err = curl_error($curl);
      curl_close($curl);
      return $response;
    }

    public function upload_w_curlfile(){
      if (empty($this->url) || empty($this->fp)) {
        return false;
      }
      $curl = curl_init();
      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $this->postfields,
      ));
      $response = curl_exec($curl);
      $info = curl_getinfo($curl);
      $err = curl_error($curl);
      curl_close($curl);
      return $response;
    }
    
    public function __destruct(){
      if (!empty($this->fp)) {
        fclose($this->fp);
      }
    }

  }

?>