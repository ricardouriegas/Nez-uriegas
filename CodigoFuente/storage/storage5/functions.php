<?php
/*
* Functions
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/

/**
 * Echoing json response to client
 * @param Array $response Json response
 */
function echoRespnse($response) {
	// setting response content type to json
	header('Content-Type: application/json');
	echo json_encode($response);
}

//http://unitstep.net/blog/2009/05/05/using-curl-in-php-to-access-https-ssltls-protected-sites/
/**
 * Send a GET request using curl
 * @param  String     $url To request
 * @param  array|null $get Values to send
 * @return json
 */
function curl_get($url) {
	//  Initiate curl
	$ch = curl_init();
	// Disable SSL verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// Will return the response, if false it print the response
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	// Execute
	$response = curl_exec($ch);
	// Closing
	curl_close($ch);
	// Return json
	return json_decode($response, true);
}

/**
 * Send a file using curl
 * @param  [type] $url    [description]
 * @param  [type] $fileIn [description]
 * @return [type]         [description]
 */
function curl_post_file($url, $fileIn) {
    //$cfile = realpath($fileIn);
	//$data = array('uploadedfile'=>'@'.$cfile);

 	$cfile = new CURLFile($fileIn);
	$data = array('uploadedfile' => $cfile);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	$response = curl_exec($ch);
	curl_close($ch);

	return json_decode($response, true);
}

/**
 * Retrieve a file using curl
 * @param  [type] $url     [description]
 * @param  [type] $fileOut [description]
 * @return [type]          [description]
 */
function curl_get_file($url, $fileOut) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	curl_close($ch);
	file_put_contents($fileOut, $data);
}


/**
 * get time in seconds
 * @return [type] [description]
 */
function microtime_float() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}
?>