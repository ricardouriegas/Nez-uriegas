<?php

	header("Access-Control-Allow-Origin:*");
	header("Access-Control-Allow-Methods:GET");
	header("Access-Control-Allow-Headers:Content-Type");
	header("Access-Control-Allow-Credentials:true");

	require_once "models/Log.php";
	$log = new Log(dirname(__FILE__) . '/logs/server.log');
	// $log->lwrite('into api');

	// require_once "models/Auth.php";
	require_once "models/ValueChain.php";
	require_once "models/Errors.php";

	// $api = new Auth;
	$api = new ValueChain;
	$api_error = new Errors;

	if (!isset($_GET['type'])) {
		$api_error->notFound();
	}
	$type = $_GET['type'];
	
	switch ($type) {
		case 1:
			$api->home();
			break;
		case 1001:
			$api->users();
			break;
		case 2001:
			$api->buildingblocks();
			break;
		case 3001:
			$api->stages();
			break;
		case 4001:
			$api->workflows();
			break;
		case 3002:
			$api->stagesUpdateTransformation();
			break;
		case 4002:
			$api->workflowsGetSingleWF();
			break;
		case 4003:
			$api->workflowsDeployWF();
			break;
		case 4004:
			$api->workflowsReadLog();
			break;
		case 4005:
			$api->workflowsPublishToUser();
			break;
		case 4006:
			$api->workflowsSubscribe();
			break;
		case 4007:
			$api->workflowsPublishedFromMe();
			break;
		case 4008:
			$api->workflowsSubscribedToMe();
			break;
		case 4009:
			$api->workflowsExecuteWF();
			break;
		case 4010:
			$api->stopWF();
			break;
		case 8000:
			$api->getNFRs();
			break;
		case 5002:
			$api->getStagesInWorkflow();
			break;
		case 5004:
			$api->getCatalogsInWorkflow();
			break;
		case 9000:
			$api->platforms();
			break;
		case 9001:
			$api->deployments();
			break;
		case 9002:
			$api->executions();
			break;
		case 9003:
			$api->readConf();
			break;
		default:
			$api_error->notFound();
			break;
	}
	

?>