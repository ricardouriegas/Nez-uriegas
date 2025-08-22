<?php

require_once "http/Rest.php";
require_once "http/Curl.php";

define('URL_AUTH', getenv("AUTH_HOST"));
define('URL_PUBSUB', getenv("PUBSUB_HOST"));


class ValueChain extends REST
{

  public function home()
  {
    if ($this->getRequestMethod() != "GET") {
      $msg['message'] = 'Error';
      $this->response($this->json($msg), 406);
    }
    $msg['message'] = 'Home';
    $this->response($this->json($msg), 200);
  }


  public function users()
  {
    $msg['msg'] = 'Error';
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($msg), 400);
    }
    switch ($this->getRequestMethod()) {
      case 'POST':
        $this->usersPost();
        break;
      case 'GET':
        $this->usersGet();
        break;
      case 'PUT':
        $this->usersPut();
        break;
      case 'DELETE':
        $this->usersDelete();
        break;
      default:
        $this->response($this->json($msg), 406);
        break;
    }
  }

  public function usersPost()
  {
    require_once("db/handler.php");
    require_once("user.php");
    $u_h = new Users();
    $u = new User();
    $u->__SET('token', $_GET['access_token']);
    $count = $u_h->createUser($u);
    $status = 400;
    $res['msg'] = 'Error';
    if ($count > 0) {
      $status = 201;
      $res['msg'] = 'User created';
    }
    $this->response($this->json($res), $status);
  }

  public function usersGet()
  {
    require_once("db/handler.php");
    require_once("user.php");
    $u_h = new Users();
    $u = new User();
    $u->__SET('token', $_GET['access_token']);
    $data = $u_h->getUser($u);
    $status = 404;
    if (count($data) > 0) {
      $status = 200;
    }
    $this->response($this->json($data), $status);
  }

  public function usersPut()
  {
    require_once("db/handler.php");
    require_once("user.php");
    $u_h = new Users();
    $u = new User();
    $u->__SET('token', $_GET['access_token']);
    $u->__SET('newtoken', $this->_request['new_token']);
    $count = $u_h->updateUser($u);
    $status = 404;
    $res['msg'] = 'Error';
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'User updated';
    }
    $this->response($this->json($res), $status);
  }

  public function usersDelete()
  {
    require_once("db/handler.php");
    require_once("user.php");
    $u_h = new Users();
    $u = new User();
    $u->__SET('token', $_GET['access_token']);
    $count = $u_h->deleteUser($u);
    $status = 404;
    $res['msg'] = 'Error';
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'User deleted';
    }
    $this->response($this->json($res), $status);
  }

  public function platforms()
  {
    $msg['msg'] = 'Error';
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($msg), 400);
    }
    switch ($this->getRequestMethod()) {
      case 'GET':
        $this->getPlatforms();
        break;
    }
  }

  public function getPlatforms()
  {
    require_once("db/handler.php");
    $p = new Platforms();
    $status = 404;
    $res['msg'] = 'Error';
    $id = $this->getUserIdByToken($_GET['access_token']);
    if (!empty($id)) {
      $data = $p->getPlatforms($_GET['access_token']);
      $status = 404;
      if (count($data) > 0) {
        $res = $data;
        $status = 200;
      }
    }
    $this->response($this->json($res), $status);
  }

  public function deployments()
  {
    $msg['msg'] = 'Error';
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($msg), 400);
    }
    switch ($this->getRequestMethod()) {
      case 'GET':
        $this->getDeployments();
        break;
    }
  }

  public function getDeployments()
  {
    require_once("db/handler.php");
    $p = new Deployments();
    $status = 404;
    $res['msg'] = 'Error';
    $id = $this->getUserIdByToken($_GET['access_token']);

    if (!empty($id)) {
      $data = $p->getDeployments($_GET['id']);
      $status = 404;
      $res = $data;
      $status = 200;
    }
    $this->response($this->json($res), $status);
  }

  public function executions()
  {
    $msg['msg'] = 'Error';
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($msg), 400);
    }
    switch ($this->getRequestMethod()) {
      case 'GET':
        $this->getExecutions();
        break;
    }
  }

  public function getExecutions()
  {
    require_once("db/handler.php");
    $p = new Executions();
    $status = 404;
    $res['msg'] = 'Error';
    $id = $this->getUserIdByToken($_GET['access_token']);

    if (!empty($id)) {
      $data = $p->getExecutions($_GET['id']);
      $status = 404;
      $res = $data;
      $status = 200;
    }
    $this->response($this->json($res), $status);
  }


  public function buildingblocks()
  {
    $msg['msg'] = 'Error';
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($msg), 400);
    }
    switch ($this->getRequestMethod()) {
      case 'POST':
        $this->buildingblocksPost();
        break;
      case 'GET':
        $this->buildingblocksGet();
        break;
      case 'PUT':
        $this->buildingblocksPut();
        break;
      case 'DELETE':
        $this->buildingblocksDelete();
        break;
      default:
        $this->response($this->json($msg), 406);
        break;
    }
  }

  public function buildingblocksPost()
  {
    require_once("db/handler.php");
    require_once("buildingblock.php");
    $b_h = new BuildingBlocks();
    $b = new BuildingBlock();
    $timestamp = date('Y-m-d H:i:s');
    $status = 404;
    $res['msg'] = 'Error';
    $id = $this->getUserIdByToken($_GET['access_token']);
    if (!empty($id)) {

      $b->__SET('owner', $id);
      $b->__SET('name', $this->_request['name']);
      $b->__SET('command', $this->_request['command']);
      $b->__SET('image', $this->_request['image']);
      $b->__SET('description', $this->_request['description']);
      $b->__SET('port', $this->_request['port']);
      $b->__SET('created', $timestamp);
      $count = $b_h->createBuildingBlock($b);
      if ($count > 0) {
        $status = 201;
        $res['msg'] = 'BuildingBlock created';
      }
    } else {
      $res['msg'] = 'Token error';
    }
    $this->response($this->json($res), $status);
  }

  public function buildingblocksGet()
  {
    require_once("db/handler.php");
    require_once("buildingblock.php");
    $b_h = new BuildingBlocks();
    $data = $b_h->getBuildingBlocks($_GET['access_token']);
    $status = 404;
    if (count($data) > 0) {
      $status = 200;
    }
    $this->response($this->json($data), $status);
  }

  public function getNFRs()
  {
    require_once("db/handler.php");
    $b_h = new NFRs();
    $data = $b_h->getNFRs($_GET['access_token']);
    $status = 404;
    if (count($data) > 0) {
      $status = 200;
    }
    $this->response($this->json($data), $status);
  }

  public function buildingblocksPut()
  {
    require_once("db/handler.php");
    require_once("buildingblock.php");
    $b_h = new BuildingBlocks();
    $b = new BuildingBlock();
    $status = 404;
    $res['msg'] = 'Error';
    $b->__SET('id', $this->_request['id']);
    $b->__SET('name', $this->_request['name']);
    $b->__SET('command', $this->_request['command']);
    $b->__SET('image', $this->_request['image']);
    $b->__SET('port', $this->_request['port']);
    $count = $b_h->updateBuildingBlock($b);
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'BuildingBlock updated';
    }
    $this->response($this->json($res), $status);
  }

  public function buildingblocksDelete()
  {
    require_once("db/handler.php");
    require_once("buildingblock.php");
    $b_h = new BuildingBlocks();
    $status = 404;
    $res['msg'] = 'Error';
    $count = $b_h->deleteBuildingBlock($this->_request['id']);
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'BuildingBlock deleted';
    }
    $this->response($this->json($res), $status);
  }



  public function stages()
  {
    $msg['msg'] = 'Error';
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($msg), 400);
    }
    switch ($this->getRequestMethod()) {
      case 'POST':
        $this->stagesPost();
        break;
      case 'GET':
        $this->stagesGet();
        break;
      case 'PUT':
        $this->stagesPut();
        break;
      case 'DELETE':
        $this->stagesDelete();
        break;
      default:
        $this->response($this->json($msg), 406);
        break;
    }
  }

  public function stagesPost()
  {
    require_once("db/handler.php");
    require_once("stage.php");
    $s_h = new Stages();
    $s = new Stage();
    $timestamp = date('Y-m-d H:i:s');
    $status = 404;
    $res['msg'] = 'Error';
    $id = $this->getUserIdByToken($_GET['access_token']);
    if (!empty($id)) {
      $s->__SET('owner', $id);
      $s->__SET('name', $this->_request['name']);
      $s->__SET('source', $this->_request['source']);
      $s->__SET('sink', $this->_request['sink']);
      $s->__SET('transformation', $this->_request['transformation']);
      $s->__SET('created', $timestamp);
      $count = $s_h->createStage($s);
      if ($count > 0) {
        $status = 201;
        $res['msg'] = 'Stage created';
      }
    } else {
      $res['msg'] = 'Token error';
    }
    $this->response($this->json($res), $status);
  }

  public function stagesGet()
  {
    require_once("db/handler.php");
    require_once("stage.php");
    $s_h = new Stages();
    $data = $s_h->getStages($_GET['access_token']);
    $status = 404;
    if (count($data) > 0) {
      $status = 200;
      //here
      $id = $this->getUserIdByToken($_GET['access_token']);
      foreach ($data as $d => &$v) {
        if ($v['owner'] == $id) {
          $v['owner'] = True;
        } else {
          $v['owner'] = False;
        }
      }
    }
    $this->response($this->json($data), $status);
  }

  public function stagesPut()
  {
    require_once("db/handler.php");
    require_once("stage.php");
    $s_h = new Stages();
    $s = new Stage();
    $status = 404;
    $res['msg'] = 'Error';
    $s->__SET('id', $this->_request['id']);
    $s->__SET('name', $this->_request['name']);
    $s->__SET('sink', $this->_request['sink']);
    $s->__SET('source', $this->_request['source']);
    $s->__SET('transformation', $this->_request['transformation']);
    $count = $s_h->updateStage($s);
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'Stage updated';
    }
    $this->response($this->json($res), $status);
  }

  public function stagesDelete()
  {
    require_once("db/handler.php");
    require_once("stage.php");
    $s_h = new Stages();
    $status = 404;
    $res['msg'] = 'Error';
    $count = $s_h->deleteStage($this->_request['id']);
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'Stage deleted';
    }
    $this->response($this->json($res), $status);
  }




  public function workflows()
  {
    $msg['msg'] = 'Error';
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($msg), 400);
    }
    switch ($this->getRequestMethod()) {
      case 'POST':
        $this->workflowsPost();
        break;
      case 'GET':
        $this->workflowsGet();
        break;
      case 'PUT':
        $this->workflowsPut();
        break;
      case 'DELETE':
        $this->workflowsDelete();
        break;
      default:
        $this->response($this->json($msg), 406);
        break;
    }
  }

  public function insert_source_catalog($catalog)
  {
    require_once("db/handler.php");
  }

  public function workflowsPost()
  {
    require_once("db/handler.php");
    require_once("workflow.php");


    $timestamp = date('Y-m-d H:i:s');
    $status = 404;
    $res['msg'] = 'Error';
    $id = $this->getUserIdByToken($_GET['access_token']);

    $stgs = json_decode($this->_request['stages'], true);


    if (!empty($id)) {
      $sc = new SourceCatalog();
      $w = new Workflow();
      $w->__SET('owner', $id);
      $w->__SET('name', $this->_request['name']);
      $w->__SET('status', $this->_request['status']);
      $w->__SET('created', $timestamp);
      #echo "hola";

      $w_h = new Workflows();
      $id_w = $w_h->createWorkflow($w);


      foreach ($stgs as $s) {
        $id_s = $w_h->insertStage($id, "stage_" . $s["name"], $s["block_id"], $timestamp);
        $w_h->insertStageInWorkflow($id_w, $id_s);
        if ($s["parent"] == -1) {
          foreach ($this->_request['catalogs'] as $c) {
            $sc->insertStageSourceCatalog($c["token"], $id_s);
          }
        } else {
          $sc->insertStageSourceBB($stgs[$s["parent"]]["block_id"], $id_s);
        }
        foreach ($stgs as $s2) {
          if ($s2["parent"] == $s["id"]) {
            $sc->insertStageSinkBB($s2["block_id"], $id_s);
          }
        }
      }

      foreach ($this->_request['requirements'] as $r) {
        $w_h->insertReqInWorkflow($id_w, $r["id"]);
      }



      $status = 201;
      $res['msg'] = 'Workflow created';
      $res['workflow_id'] = $id_w;
    } else {
      $res['msg'] = 'Token error';
    }
    $this->response($this->json($res), $status);
  }

  public function workflowsGet()
  {
    require_once("db/handler.php");
    $w_h = new Workflows();
    $data = $w_h->getWorkflows($_GET['access_token']);
    $status = 404;
    if (count($data) > 0) {
      $status = 200;
    }
    $this->response($this->json($data), $status);
  }

  public function workflowsPut()
  {
    require_once("db/handler.php");
    require_once("workflow.php");
    $w_h = new Workflows();
    $w = new Workflow();
    $status = 404;
    $res['msg'] = 'Error';
    $args = json_decode(file_get_contents('php://input'), true);
    $w->__SET('id', $this->_request['id']);
    $w->__SET('name', $this->_request['name']);
    $w->__SET('status', $this->_request['status']);
    $w->__SET('stages', $this->_request['stages']);
    // $w->__SET('rawgraph', $this->_request['rawgraph']);
    $w->__SET('rawgraph', $args['rawgraph']);
    $count = $w_h->updateWorkflow($w);
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'Workflow updated';
    }
    $this->response($this->json($res), $status);
  }

  public function workflowsDelete()
  {
    require_once("db/handler.php");
    require_once("workflow.php");
    $w_h = new Workflows();
    $w = new Workflow();
    $status = 404;
    $res['msg'] = 'Error';
    $w->__SET('id', $this->_request['id']);
    $count = $w_h->deleteWorkflow($w);
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'Workflow deleted';
    }
    $this->response($this->json($res), $status);
  }


  public function stagesUpdateTransformation()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'PUT') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    require_once("stage.php");
    $s_h = new Stages();
    $s = new Stage();
    $s->__SET('id', $this->_request['id']);
    $s->__SET('transformation', $this->_request['transformation']);
    $count = $s_h->updateStageTransformation($s);
    if ($count > 0) {
      $status = 200;
      $res['msg'] = 'Stage transformation updated';
    }
    $this->response($this->json($res), $status);
  }

  public function workflowsGetSingleWF()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'GET') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    $w_h = new Workflows();
    $data = $w_h->getSingleWorkflow($_GET['id'], $_GET['access_token']);
    if (count($data) > 0) {
      $status = 200;
    }
    $this->response($this->json($data), $status);
  }

  public function getCatalogsInWorkflow()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'GET') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    $w_h = new Workflows();
    $w_h = new Workflows();
    $s = new Stages();
    $data = $w_h->getStagesInWorkflow($_GET['id'], $_GET['access_token']);
    $catalogs = array();

    foreach ($data as $key => $d) {
      $catalogs[] = $s->getCatalogSources($d["id"]);
    }


    if (count($data) > 0) {
      $status = 200;
    }
    $this->response($this->json($catalogs), $status);
  }

  public function getStagesInWorkflow()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'GET') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    $w_h = new Workflows();
    $w_h = new Workflows();
    $s = new Stages();
    $data = $w_h->getStagesInWorkflow($_GET['id'], $_GET['access_token']);

    foreach ($data as $key => $d) {
      $data[$key]["sources"] = $s->getBBSources($d["id"]) + $s->getCatalogSources($d["id"]);
      $data[$key]["sinks"] = $s->getBBSinks($d["id"]);
    }
    $results = array("requirements" => $w_h->getReqsInWorkflow($_GET['id']), "stages" => $data);

    if (count($data) > 0) {
      $status = 200;
    }
    $this->response($this->json($results), $status);
  }

  public function get_processor_cores_number()
  {
    $command = "cat /proc/cpuinfo | grep processor | wc -l";
    return (int) shell_exec($command);
  }

  public function getCatalogInfo($tokenCat, $tokenUsr)
  {
    $url = "http://" . URL_PUBSUB . "/subscription/v1/view/catalog/$tokenCat?access_token=" . $tokenUsr;
    $curl = new Curl();
    $response = $curl->get($url);
    if ($response["code"] == 200) {
      return $response;
    } else {
      return NULL;
    }
  }

  public function readConf()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'POST') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");

    $wfname = $this->_request['puzzle_name'];
    $idw = $this->_request['id'];

    // write cfg file for geb
    $dir = "/var/www/html/geb/cfg-files";
    $file_path = $dir . "/" . $wfname . ".cfg";
    $myfile = fopen($file_path, "r");

    if (!$myfile) {
      $res['msg'] = 'Unable to open configuration file ' . $file_path;
      $this->response($this->json($res), 500);
    }
    $data = ["conf" => fread($myfile, filesize($file_path))];
    $status = 200;
    fclose($myfile);

    $this->response($this->json($data), $status);
  }

  public function workflowsExecuteWF()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'POST') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");

    $wfname = $this->_request['puzzle_name'];
    $idw = $this->_request['id'];

    // write cfg file for geb
    $dir = "/var/www/html/geb/cfg-files";
    $myfile = fopen($dir . "/" . $wfname . ".cfg", "r");

    if (!$myfile) {
      $res['msg'] = 'Unable to open configuration file ' . $dir . "/" . $wfname . ".cfg";
      $this->response($this->json($res), 500);
    }


    $e = new Executions();
    $d = new Deployments();
    $last_d = $d->getLastDeployment($idw);
    if (count($last_d) > 0) {
      $last_d = $last_d[0];
      $res = $e->registExecution($idw, $last_d["platform_id"]);
      $id_execution = $res[0]["id"];
      $curl = new Curl();
      $vc_pair = getenv("DEPLOYER_HOST") . ":" . strval(getenv("DEPLOYER_PORT"));
      $url = 'http://' . $vc_pair . '/stacks/run';
      $data['wf_name'] = $wfname;
      $data['deployment_mode'] = $last_d["platform_id"] == 1 ? "compose" : "swarm";
      $data["id_execution"] = $id_execution;
      $data["access_token"] = $_GET['access_token'];
      $data["tokenuser"] = $_GET['tokenuser'];
      $data["apikey"] = $_GET['apikey'];
      //print_r($data);
      $r = $curl->post($url, $data);
      $status = $r['code'];
      if (isset($r["data"]["out"])) {
        $wff = "/var/www/html/logs/execution/" . $wfname . "_" . $id_execution . ".log";
        $wflog = fopen($wff, "w");
        if (!$wflog) {
          $res['msg'] = 'Unable to save logs';
          $res['path'] = $wff;
          $this->response($this->json($res), 500);
          $data['wf_log'] = true;
        }
        fwrite($wflog, $r['data']['out']);
        fclose($wflog);
      }
      $timestamp = date('Y-m-d H:i:s');
      $data["executed"] = $timestamp;

      $status_ex = $status == 200 ? 1 : 0;
      $data["status2"] = $status;
      $e->updateExecution($id_execution, $timestamp, $status_ex);
      $data['status'] = $status == 200 ? "Ok" : "Error";
      //print_r($r);
    } else {
      $res['msg'] = 'Puzzle not deployed';
    }


    fclose($myfile);

    $this->response($this->json($data), $status);
  }


  public function stopWF()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'POST') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");

    $wfname = $this->_request['puzzle_name'];
    $idw = $this->_request['id'];



    $e = new Executions();
    $d = new Deployments();
    $last_d = $d->getLastDeployment($idw);
    if (count($last_d) > 0) {
      $last_d = $last_d[0];
      
      $curl = new Curl();
      $vc_pair = getenv("DEPLOYER_HOST") . ":" . strval(getenv("DEPLOYER_PORT"));
      $url = 'http://' . $vc_pair . '/stacks/stop';
      $data['wf_name'] = $wfname;
      $data['deployment_mode'] = $last_d["platform_id"] == 1 ? "compose" : "swarm";
      $data["id_execution"] = $id_execution;
      $data["access_token"] = $_GET['access_token'];
      $data["tokenuser"] = $_GET['tokenuser'];
      $data["apikey"] = $_GET['apikey'];
      //print_r($data);
      $r = $curl->post($url, $data);
      $status = $r['code'];

      $timestamp = date('Y-m-d H:i:s');
      $data["executed"] = $timestamp;

      $status_ex = $status == 200 ? 1 : 0;
      $data["status2"] = $status;
      $data['status'] = $status == 200 ? "Ok" : "Error";
      //print_r($r);
    } else {
      $res['msg'] = 'Puzzle not deployed';
    }


    fclose($myfile);

    $this->response($this->json($data), $status);
  }

  public function workflowsDeployWF()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'POST') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    $w_h = new Workflows();
    $s = new Stages();
    $idw = $this->_request['id'];
    $stagesStr = "";
    $nfrsConf = [];
    $stagesConf = [];
    $patternsConf = [];
    $bbs = [];
    $stgs = [];
    $wfname = '';
    $flag = 0;
    $dwf = $w_h->getSingleWorkflow($idw, $_GET['tokenuser']);
    if (count($dwf) > 0) {
      $wfname = $dwf['name'];
      $flag = 1;
    }

    $stages = $w_h->getStagesInWorkflow($dwf['id'], $_GET['tokenuser']);
    $requirements = $w_h->getReqsInWorkflow($dwf['id']);

    $catalogs_str = "";
    // create cfg file
    foreach ($stages as $value) {
      //$dstg = $stageModel->readStageWithBlocks($value);
      //print_r($value);
      $stagesStr .= $value['name'] . " ";
      $sources = $s->getBBSources($value["id"]);
      $catalog_sources = $s->getCatalogSources($value["id"]);
      $sinks = $s->getBBSinks($value["id"]);
      $sources_str = "";
      $sinks_str = "";
      $pt_line = "";

      foreach ($sources as $stg) {
        $sources_str .= $stg["stage"] . " ";
      }

      foreach ($catalog_sources as $stg) {
        $cat = $this->getCatalogInfo($stg["catalog"], $_GET['access_token']);
        $sources_str .= "@PWD/$wfname/catalogs/" . $cat["data"]["data"]["namecatalog"] . " ";
        $catalogs_str .= $cat["data"]["data"]["namecatalog"] . ":" . $cat["data"]["data"]["tokencatalog"] . " ";
        //print_r($this->getCatalogInfo($stg["catalog"], $_GET['access_token']));
      }

      foreach ($sinks as $stg) {
        $sinks_str .= $stg["stage"] . " ";
      }

      $st_line = "[STAGE]" . PHP_EOL . "name = " . $value['name'] . PHP_EOL;
      $st_line .= "source = " . $sources_str . PHP_EOL;
      $st_line .= "sink = " . $sinks_str . PHP_EOL;

      $hasPattern = False;

      foreach ($requirements as $r) {
        if ($r["technique"] == "Manager/worker") {
          $hasPattern = True;
          break;
        }
      }

      if ($hasPattern) {
        $st_line .= "transformation = " . $value["buildingblock"] . "pattern" . PHP_EOL;
        $pt_line .= "[PATTERN]" . PHP_EOL . "name = " . $value["buildingblock"] . "pattern" . PHP_EOL;
        $pt_line .= "task = " . $value["buildingblock"] . PHP_EOL;
        $pt_line .= "pattern = MW" . PHP_EOL;
        //$pt_line .= "workers = " . $this->get_processor_cores_number() . PHP_EOL;
        $pt_line .= "workers = " . 2 . PHP_EOL;
        $pt_line .= "loadbalancer = TC:DL" . PHP_EOL;
        $pt_line .= "[END]" . PHP_EOL . PHP_EOL;
        $patternsConf[] = $pt_line;
      } else {
        $st_line .= "transformation = " . $value["buildingblock"] . PHP_EOL;
      }


      $st_line .= "[END]" . PHP_EOL . PHP_EOL;
      $stagesConf[] = $st_line;

      $bb_line = "[BB]" . PHP_EOL . "name = " . $value['buildingblock'] . PHP_EOL;
      $bb_line .= "command = " . $value['command'] . PHP_EOL;
      $bb_line .= "image = " . $value['image'] . PHP_EOL;
      $bb_line .= "[END]" . PHP_EOL . PHP_EOL;
      $bbs[] = $bb_line;
    }

    foreach ($requirements as $r) {
      $nfr_line = "[NFR]" . PHP_EOL . "name = " . $r['technique'] . PHP_EOL;
      $nfr_line .= "[END]" . PHP_EOL . PHP_EOL;
      $nfrsConf[] = $nfr_line;
    }


    $wf_line = "[WORKFLOW]" . PHP_EOL . "name = " . $wfname . PHP_EOL . "stages = " . $stagesStr . PHP_EOL . "catalogs = " . $catalogs_str . PHP_EOL . "[END]" . PHP_EOL;


    // write cfg file for geb
    $dir = "/var/www/html/geb/cfg-files";
    $myfile = fopen($dir . "/" . $wfname . ".cfg", "w");
    //echo $dir . "/" . $wfname . ".cfg";
    if (!$myfile) {
      $res['msg'] = 'Unable to save file2';
      $this->response($this->json($res), 500);
    }
    foreach ($bbs as $bb) {
      fwrite($myfile, $bb);
    }

    foreach ($patternsConf as $pt) {
      fwrite($myfile, $pt);
    }

    foreach ($stagesConf as $st) {
      fwrite($myfile, $st);
    }

    foreach ($nfrsConf as $nfr) {
      fwrite($myfile, $nfr);
    }

    fwrite($myfile, $wf_line);
    fclose($myfile);

    $d = new Deployments();
    $res = $d->registDeployment($idw, $this->_request['platform']);
    if (count($res) > 0) {
      $id_deployment = $res[0]["id"];
      $curl = new Curl();
      $vc_pair = getenv("DEPLOYER_HOST") . ":" . strval(getenv("DEPLOYER_PORT"));
      $url = 'http://' . $vc_pair . '/stacks/deploy';
      $data['wf_name'] = $wfname;
      $data['deployment_mode'] = $this->_request['platform'] == 1 ? "compose" : "swarm";
      $data["id_deployment"] = $id_deployment;
      $r = $curl->post($url, $data);
      $status = $r['code'];

      if ($status == 200) {
        $data['status'] = "Deployed";
        $wff = "/var/www/html/logs/deployment/" . $wfname . "_" . $id_deployment . ".log";
        $wflog = fopen($wff, "w");

        if (!$wflog) {
          $res['msg'] = 'Unable to save logs';
          $res['path'] = $wff;
          $this->response($this->json($res), 500);
        }

        if ($wflog && isset($r['data']) && isset($r['data']['out'])) {
          $data['wf_log'] = true;
          fwrite($wflog, $r['data']['out']);
          fclose($wflog);
        }

        $timestamp = date('Y-m-d H:i:s');
        $data["deployed"] = $timestamp;
        $d->updateDeployment($id_deployment, $timestamp, 1);
      } else {
        $d->updateDeployment($id_deployment, $timestamp, 2);
      }
    }

    $this->response($this->json($data), $status);
  }

  public function workflowsReadLog()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'POST') {
      $this->response($this->json($res), 406);
    }
    $wname = $this->_request['name'];
    $id = $this->_request['id'];
    $folder = $this->_request['folder'];
    $wfile = "/var/www/html/logs/$folder/$wname" . "_$id.log";

    if (file_exists($wfile)) {
      $status = 200;
      $res['msg'] = 'log found';
      $res['log'] = file_get_contents($wfile);
    } else {
      $res['msg'] = "Empty data $wname";
    }
    $this->response($this->json($res), $status);
  }

  public function workflowsPublishToUser()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'POST') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    require_once("publishworkflow.php");
    $p_h = new PublishSubscribeWorkflows();
    $p = new PublishWorkflow();
    $timestamp = date('Y-m-d H:i:s');
    $status = 404;
    $res['msg'] = 'Error';
    $idowner = $this->getUserIdByToken($_GET['access_token']);
    $id = $this->getUserIdByToken($this->_request['iduser']);
    if (!empty($idowner) && !empty($id)) {
      $p->__SET('idworkflow', $this->_request['idworkflow']);
      $p->__SET('iduser', $id);
      $p->__SET('created', $timestamp);
      // TODO: search for duplicated wf
      $count = $p_h->publishWorkflowToUser($p);
      if ($count > 0) {
        $status = 201;
        $res['msg'] = 'Workflow published';
      }
    } else {
      $res['msg'] = 'Token error';
    }
    $this->response($this->json($res), $status);
  }

  public function workflowsSubscribe()
  {
    $res['msg'] = 'Error';
    $status = 404;
    if (!isset($_GET['access_token'])) {
      $this->response($this->json($res), 400);
    }
    if ($this->getRequestMethod() != 'POST') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    $p_h = new PublishSubscribeWorkflows();
    if (!isset($this->_request['idpublish'])) {
      $this->response($this->json($res), 400);
    }
    $count = $p_h->subscribeToWorkflow($this->_request['idpublish']);
    if ($count > 0) {
      $status = 201;
      $res['msg'] = 'Workflow subscribed';
    }
    $this->response($this->json($res), $status);
  }

  public function workflowsPublishedFromMe()
  {
    $res['msg'] = 'Error';
    $s = 400;
    if (!isset($_GET['access_token'])) {
      $res['msg'] = 'Missing args';
      $this->response($this->json($res), $s);
    }
    if ($this->getRequestMethod() != 'GET') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    $p_h = new PublishSubscribeWorkflows();
    $iduser = $this->getUserIdByToken($_GET['access_token']);
    if (empty($iduser)) {
      $res['msg'] = 'Token error';
      $this->response($this->json($res), 401);
    }
    $data = $p_h->getWorkflowsPublishedFromMe($iduser);
    if (count($data) == 0) {
      $this->response($this->json($data), 404);
    }
    $this->response($this->json($data), 200);
  }


  public function workflowsSubscribedToMe()
  {
    $res['msg'] = 'Error';
    $s = 400;
    if (!isset($_GET['access_token'])) {
      $res['msg'] = 'Missing args';
      $this->response($this->json($res), $s);
    }
    if ($this->getRequestMethod() != 'GET') {
      $this->response($this->json($res), 406);
    }
    require_once("db/handler.php");
    $p_h = new PublishSubscribeWorkflows();
    $iduser = $this->getUserIdByToken($_GET['access_token']);
    if (empty($iduser)) {
      $res['msg'] = 'Token error';
      $this->response($this->json($res), 401);
    }
    $data = $p_h->getWorkflowsSubscribedToMe($iduser);
    if (count($data) == 0) {
      $this->response($this->json($data), 404);
    }
    $this->response($this->json($data), 200);
  }




  // UTILS
  public function getUserIdByToken($t)
  {

    $url = "http://" . URL_AUTH . '/auth/v1/user?tokenuser=' . $t;
    $curl = new Curl();
    $response = $curl->get($url);
    if ($response["code"] == 200) {
      return $t;
    } else {
      return NULL;
    }
  }
}