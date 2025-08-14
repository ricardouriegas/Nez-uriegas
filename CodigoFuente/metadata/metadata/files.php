<?php

require_once "models/File.php";

$file = new File;


if (isset($_GET['type'])) {
	$type = $_GET['type'];
	switch ($type) {
		case 1:
			$file->all();
			break;
		case 2:
			$file->get($file->_request['token']);
			break;
		case 3:
			$file->create();
			break;
		case 4:
			$file->delete($file->_request['token']);
            break;
        case 5:
			$file->deleteAll();
			break;
	}
} 
