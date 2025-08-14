<?php 
include_once("../conf.php");
include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");
//echo MODELOS;

if (isset($_POST['readStages'])) {
	
	require_once(MODELOS . "/puzzlemesh/model.php");
	$stageModel = new Stages();
	$data = $stageModel->readStage();
	echo json_encode($data);
} else if (isset($_POST['readBBStage'])) {
	require_once(MODELOS . "/puzzlemesh/model.php");
	$stageModel = new Stages();
	$data = $stageModel->readTreeStage($_POST['readBBStage']);

	//print_r($data);
	echo json_encode($data);
	//require_once("../views2/stagesBB.php"); 
	//echo "hola";
}else if (isset($_POST['BBStage'])) {

	require_once(MODELOS . "/puzzlemesh/model.php");
	$relationModel = new Relations();
	$data = $relationModel->createRelationStageBB($_POST['BBStage'], $_POST['idStageBB']);

	$stageModel = new Stages();

	$asd = $stageModel->updateStageTrans($_POST['BBname'], $_POST['idStageBB']);

	//echo $_POST['BBStage'].$_POST['idStageBB'];
	echo json_encode($asd);

} else if (isset($_POST['readBlackBoxe'])) {

	require_once(MODELOS . "/puzzlemesh/model.php");
	$blackBoxModel = new BlackBoxes();
	$data = $blackBoxModel->readBlackBoxe();
	
	require_once("../views2/bbView.php"); 
} else if (isset($_POST['newBlackBox'])) {

	require_once(MODELOS . "/puzzlemesh/model.php");
	require_once("../models/blackBox.php");

	$blackBoxModel = new BlackBoxes();
	$blackBox = new BlackBox();
	$timestamp = date('Y-m-d H:i:s');

	$blackBox->__SET('owner', $_SESSION['idUser']);
	$blackBox->__SET('name', $_POST['nameBB']);
	$blackBox->__SET('command', $_POST['commandBB']);
	$blackBox->__SET('image', $_POST['imageBB']);
	$blackBox->__SET('created', $timestamp);

	$asd = $blackBoxModel->createBlackBoxe($blackBox);
	

	$data = $blackBoxModel->readBlackBoxe();
	
	require_once("../views2/tableBlackBoxesView.php"); 


}else if (isset($_POST['updateBlackBox'])) {

	require_once(MODELOS . "/puzzlemesh/model.php");
	require_once("../models/blackBox.php");

	$blackBoxModel = new BlackBoxes();
	$blackBox = new BlackBox();
	$timestamp = date('Y-m-d H:i:s');

		$port = "";
	if (isset($_POST['updatePortBB'])) {
		$port = $_POST['updatePortBB'];
	}else {
		$port = "";

	}

	$blackBox->__SET('id', $_POST['updateIdBB']);
	$blackBox->__SET('owner', $_SESSION['idUser']);
	$blackBox->__SET('name', $_POST['updateNameBB']);
	$blackBox->__SET('command', $_POST['updateCommandBB']);
	$blackBox->__SET('image', $_POST['updateImageBB']);
	$blackBox->__SET('port', $port);
	$blackBox->__SET('created', $timestamp);

	$asd = $blackBoxModel->updateBlackBox($blackBox);
	

	$data = $blackBoxModel->readBlackBoxe();
	
	require_once("../views2/tableBlackBoxesView.php"); 


}else  if (isset($_POST['deleteBlackBox'])) {
	require_once(MODELOS . "/puzzlemesh/model.php");
	$blackBoxModel = new BlackBoxes();
	$asd = $blackBoxModel->deleteBlackBox($_POST['idBlackBox']);

	$data = $blackBoxModel->readBlackBoxe();
	
	require_once("../../views/puzzlemesh/tableBlackBoxesView.php"); 

} else if (isset($_POST['createWorkflow'])) {

	require_once(MODELOS . "/puzzlemesh/model.php");
	require_once(MODELOS .  "/puzzlemesh/workflow.php");

	$workflowModel = new Workflows();
	$workflow = new Workflow();
	$timestamp = date('Y-m-d H:i:s');
	$stgs = json_decode($_POST['stages'], true);
	$stagesStr = "";

	foreach ($stgs as $key => $value) {
		$stagesStr .= $value['name'] . " ";
	}

	// save workflow to db
	$workflow->__SET('owner', $_SESSION['idUser']);
	$workflow->__SET('name', $_POST['nameWorkflow']);
	$workflow->__SET('status', $_POST['statusWorkflow']);
	$workflow->__SET('stages', $stagesStr);
	$workflow->__SET('created', $timestamp);
	$asd = $workflowModel->createWorkflow($workflow);
	
	echo json_encode($asd);
	// $data = $workflowModel->readWorkflow($_SESSION['idUser']);
}

?>