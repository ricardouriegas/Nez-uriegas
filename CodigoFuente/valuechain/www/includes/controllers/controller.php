<?php
include_once("../conf.php");
include_once(SESIONES);
include_once(CLASES . "/class.Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("puzzlemesh");


class controller
{

	public function statusLogin()
	{
		if (!isset($_SESSION['connected'])) {
			// require_once("views/login.php");
			header("Location: ../");
		} else {
			require_once("views/dashboard.php");
		}
	}
}

#print_r($_POST);

if (isset($_POST['type'])) {
	if ($_POST['type'] == "createBB") {
		$curl = new Curl();
		$data = array("name" => $_POST["nameBB"], "command" => $_POST["commandBB"], "image" => $_POST["imageBB"], "description" => $_POST["descriptionBB"]);
		$access_token = $_SESSION["tokenuser"];
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/buildingblocks?access_token=$access_token";
		$response = $curl->post($url, $data);

		echo json_encode($response);
	} else if ($_POST['type'] == "readBBs") {
		$access_token = $_SESSION["tokenuser"];
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/buildingblocks?access_token=$access_token";
		$curl = new Curl();
		$response = $curl->get($url);
		echo json_encode(array("pieces" => $_SESSION["pieces"], "response" => $response));
	} else if ($_POST['type'] == "addBB" && isset($_POST["id"]) && isset($_POST["name"]) && isset($_POST["image"])) {
		if (!isset($_SESSION["pieces"]))
			$_SESSION["pieces"] = array();
		$_SESSION["pieces"][$_POST["id"]] = array("id" => $_POST["id"], "name" => $_POST["name"], "image" => $_POST["image"]);
		echo json_encode(array("message" => "Piece added!"));
	} else if ($_POST['type'] == "removeBB" && isset($_POST["id"])) {
		if (isset($_SESSION["pieces"][$_POST["id"]])) {
			unset($_SESSION["pieces"][$_POST["id"]]);
			echo json_encode(array("message" => "Piece removed", "code" => 0));
		} else {
			echo json_encode(array("message" => "Error removing piece", "code" => 1));
		}
	} else if ($_POST['type'] == "getCatalogs") {
		if (!isset($_SESSION["data"]))
			$_SESSION["data"] = array();
		$access_token = $_SESSION["access_token"];
		$tokenuser = $_SESSION["tokenuser"];

		$curl = new Curl();
		$url = "http://" . APIGATEWAY_HOST . "/pub_sub/v1/view/catalogs/user/$tokenuser/subscribed?access_token=$access_token";
		$response_sus = $curl->get($url);

		$curl = new Curl();
		$url = "http://" . APIGATEWAY_HOST . "/pub_sub/v1/view/catalogs/user/$tokenuser/published?access_token=$access_token";
		$response_pub = $curl->get($url);

		echo json_encode(array("suscribed" => $response_sus, "published" => $response_pub, "data_added" => $_SESSION["data"]));
	} else if ($_POST['type'] == "addCat" && isset($_POST["token"]) && isset($_POST["namecatalog"]) && isset($_POST["created_at"])) {
		if (!isset($_SESSION["data"]))
			$_SESSION["data"] = array();
		$_SESSION["data"][$_POST["token"]] = array("token" => $_POST["token"], "namecatalog" => $_POST["namecatalog"], "created_at" => $_POST["created_at"]);
		echo json_encode(array("message" => "Data added!"));
	} else if ($_POST['type'] == "removeCat" && isset($_POST["token"])) {
		if (isset($_SESSION["data"][$_POST["token"]])) {
			unset($_SESSION["data"][$_POST["token"]]);
			echo json_encode(array("message" => "Data removed", "code" => 0));
		} else {
			echo json_encode(array("message" => "Error removing data", "code" => 1));
		}
	} else if ($_POST["type"] == "createCatalog" && isset($_POST["name_C"]) && isset($_POST["group"])) {
		$access_token = $_SESSION["access_token"];
		$data = array("catalogname" => $_POST["name_C"], "dispersemode" => "IDA", "encryption" => True, "fathers_token" => "/", "group" => $_POST["group"], "processed" => "false");
		$curl = new Curl();
		$url = "http://" . APIGATEWAY_HOST . "/pub_sub/v1/catalogs/create?access_token=$access_token";
		$response = $curl->post($url, $data);
		//print_r($response);
		if ($response["code"] == 201) {
			$_SESSION["data"][$response["data"]["tokencatalog"]] = array("token" => $response["data"]["tokencatalog"], "namecatalog" => $_POST["name_c"], "created_at" => date("Y-m-d H:i:s"));
		}

		echo json_encode($response);
	} else if ($_POST["type"] == "nfrs") {
		if (!isset($_SESSION["reqs"]))
			$_SESSION["reqs"] = array();
		$curl = new Curl();
		$access_token = $_SESSION["tokenuser"];
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/nfrs?access_token=$access_token";
		$response = $curl->get($url);
		echo json_encode(array("nfrs" => $response, "added" => $_SESSION["reqs"]));
	} else if ($_POST['type'] == "addReq" && isset($_POST["id"]) && isset($_POST["technique"]) && isset($_POST["typeNFR"]) && isset($_POST["requirement"])) {
		if (!isset($_SESSION["reqs"]))
			$_SESSION["reqs"] = array();
		$_SESSION["reqs"][$_POST["id"]] = array("id" => $_POST["id"], "technique" => $_POST["technique"], "type" => $_POST["type"], "requirement" => $_POST["requirement"]);
		echo json_encode(array("message" => "Requirement added!"));
	} else if ($_POST['type'] == "removeReq" && isset($_POST["id"]) && isset($_POST["technique"]) && isset($_POST["typeNFR"]) && isset($_POST["requirement"])) {
		if (isset($_SESSION["reqs"][$_POST["id"]])) {
			unset($_SESSION["reqs"][$_POST["id"]]);
			echo json_encode(array("message" => "Data removed", "code" => 0));
		} else {
			echo json_encode(array("message" => "Error removing data", "code" => 1));
		}
	} else if ($_POST['type'] == "createProcessingStructure" && isset($_POST["nameWorkflow"]) && isset($_POST["statusWorkflow"]) && isset($_POST["stages"]) && isset($_POST["deploy_and_execute"])) {
		$curl = new Curl();
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows?access_token=" . $_SESSION['tokenuser'];
		$requirements = $_SESSION["reqs"];
		$catalogs = $_SESSION["data"];
		$pieces = $_SESSION["pieces"];
		$data = array("name" => $_POST["nameWorkflow"], "status" => $_POST["statusWorkflow"], "stages" => $_POST["stages"], "pieces" => $pieces, "catalogs" => $catalogs, "requirements" => $requirements, "deploy_and_execute" => $_POST["deploy_and_execute"]);
		//echo json_encode($data);
		$response = $curl->post($url, $data);
		//print_r($response);
		if ($response["code"] == 201) {
			$_SESSION["reqs"] = [];
			$_SESSION["data"] = [];
			$_SESSION["pieces"] = [];
			echo json_encode($response);
		}
	} else if ($_POST['type'] == "getWorkflowData" && isset($_POST["id"])) {
		$curl = new Curl();
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows" . $_GET["id"] . "?access_token=" . $_SESSION['tokenuser'];
		$response = $curl->get($url);
		echo json_encode($response);
	} else if ($_POST['type'] == "getStages" && isset($_POST["id"])) {
		$curl = new Curl();
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/" . $_POST["id"] . "/stages?access_token=" . $_SESSION['tokenuser'];
		$response = $curl->get($url);
		$access_token = $_SESSION["access_token"];
		$tokenuser = $_SESSION["tokenuser"];

		$curl = new Curl();
		$url = "http://" . APIGATEWAY_HOST . "/pub_sub/v1/view/catalogs/user/$tokenuser/subscribed?access_token=$access_token";
		$response_sus = $curl->get($url);

		echo json_encode(["workflow_data" => $response, "catalogs" => $response_sus]);
	} else if ($_POST['type'] == "getPuzzles") {
		$curl = new Curl();
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows?access_token=" . $_SESSION['tokenuser'];
		$response = $curl->get($url);
		echo json_encode(["workflow_data" => $response, "catalogs" => $response_sus]);
	} else if ($_POST["type"] == "deployPuzzle" && isset($_POST["id"])) {
		$curl = new Curl();
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/run?access_token=" . $_SESSION['access_token'] . "&tokenuser=" . $_SESSION['tokenuser'];
		#echo $url;
		$response = $curl->post($url, ["id" => $_POST["id"], "platform" => $_POST["platform"]]);
		#echo json_encode(["id" => $_POST["id"], "platform" => $_POST["platform"]]);
		//print_r($response);
		echo json_encode($response);
	} else if ($_POST["type"] == "getLogsDeployment" && isset($_POST["id"]) && isset($_POST["puzzle_name"]) && isset($_POST["folder"])) {
		$curl = new Curl();
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/log?access_token=" . $_SESSION['access_token'] . "&tokenuser=" . $_SESSION['tokenuser'];
		$response = $curl->post($url, ["id" => $_POST["id"], "name" => $_POST["puzzle_name"], "folder" => $_POST["folder"]]);
		echo json_encode($response);
	} else if ($_POST["type"] == "executePuzzle" && isset($_POST["id"]) && isset($_POST["puzzle_name"])) {
		$curl = new Curl();
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/execute?access_token=" . $_SESSION['access_token'] . "&tokenuser=" . $_SESSION['tokenuser'] . "&apikey=" . $_SESSION['apikey'];
		//echo $url;
		$response = $curl->post($url, ["id" => $_POST["id"], "puzzle_name" => $_POST["puzzle_name"]]);
		echo json_encode($response);
	} else if ($_POST["type"] == "stopPuzzle" && isset($_POST["id"]) && isset($_POST["puzzle_name"])) {
		$curl = new Curl();
		$url = "http://" . VALUE_CHAIN_API . "/api/v1/workflows/stop?access_token=" . $_SESSION['access_token'] . "&tokenuser=" . $_SESSION['tokenuser'] . "&apikey=" . $_SESSION['apikey'];
		//echo $url;
		$response = $curl->post($url, ["id" => $_POST["id"], "puzzle_name" => $_POST["puzzle_name"]]);
		echo json_encode($response);
	} else if ($_POST["type"] == "publishCatalog" && isset($_POST["user"]) && isset($_POST["catalog"])) {
		$url = "http://" . APIGATEWAY_HOST . "/pub_sub/v1/publish/catalog/user" . "?access_token=" . $_SESSION['access_token'];
		$data['tokencatalog'] = $_POST['catalog'];
		$data['tokenuser'] = $_POST['user'];
		$curl = new Curl();
		$response = $curl->post($url, $data);
		//echo $url;
		//echo json_encode($data);
		echo json_encode($response);
	} else if ($_POST["type"] == "getDirsInShared") {
		include_once(CLASES . "/class.Files.php");
		//print_r(Files::getDirContents(getenv("SHAREDVOLUME")));
		$files =  array("text" => getenv("SHAREDVOLUME"), "children" => array());
		echo json_encode(Files::getDirContents(getenv("SHAREDVOLUME"), $files));
	}
}