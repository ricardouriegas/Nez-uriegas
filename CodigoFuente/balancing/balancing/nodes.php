<?php

require_once "models/Node.php";

$node = new Node;

if (isset($_GET['type'])) {
	$type = $_GET['type'];
	switch ($type) {
		case 1:
			$node->all();
			break;
		case 2:
			$node->get($node->_request['id']);
			break;
		case 3:
			$node->create();
			break;
		case 4:
			$node->delete($node->_request['id']);
			break;

		case 10:
			$node->getUploadNodes();
			break;
	}
} 
