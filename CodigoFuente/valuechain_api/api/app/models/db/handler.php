<?php 

require_once dirname(__FILE__) . '/connection.php';
require_once dirname(__FILE__) . '/../Log.php';

class Users{

	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	}

	public function createUser(User $data){
		try{
			$sql = "INSERT INTO users (token) VALUES (:tk);";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":tk", $data->__GET('token'), PDO::PARAM_STR);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function getUser(User $data){
		$res = [];
		try{
			$sql = "SELECT id FROM users WHERE token=:tk;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":tk", $data->__GET('token'), PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function updateUser(User $data){
		try{
			$sql = "UPDATE users SET token=:ntk WHERE token=:tk;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":ntk", $data->__GET('newtoken'), PDO::PARAM_STR);
			$stmt->bindParam(":tk", $data->__GET('token'), PDO::PARAM_STR);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function deleteUser(User $data){
		try{
			$sql = "DELETE FROM users WHERE token=:tk;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":tk", $data->__GET('token'), PDO::PARAM_STR);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	/**
	* Close connection
	*/
	public function __destruct() {
		$this->db = null;
	}
}

class SourceCatalog{
	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	}

	public function insertStageSinkBB($token_catalog, $stage_id){
		$res = [];
		try{
			$sql = "INSERT INTO stage_sink(sink_type, stage_id) VALUES(3, :wi) RETURNING id";
			// TODO: get also subscribed BB
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":wi", $stage_id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$id_ds = $res[0]["id"];
				$sql = "INSERT INTO workflows_sink_bb(id, id_bb) VALUES(:id, :bb)";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(":id", $id_ds, PDO::PARAM_INT);
				$stmt->bindParam(":bb", $token_catalog, PDO::PARAM_INT);
				$stmt->execute();
				return $id_ds;
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return null;
	}

	public function insertStageSourceBB($token_catalog, $stage_id){
		$res = [];
		try{
			$sql = "INSERT INTO stage_source(source_type, stage_id) VALUES(3, :wi) RETURNING id";
			// TODO: get also subscribed BB
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":wi", $stage_id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$id_ds = $res[0]["id"];
				$sql = "INSERT INTO workflows_source_bb(id, id_bb) VALUES(:id, :bb)";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(":id", $id_ds, PDO::PARAM_INT);
				$stmt->bindParam(":bb", $token_catalog, PDO::PARAM_INT);
				$stmt->execute();
				return $id_ds;
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return null;
	}

	public function insertStageSourceCatalog($token_catalog, $stage_id){
		$res = [];
		try{
			$sql = "INSERT INTO stage_source(source_type, stage_id) VALUES(1, :wi) RETURNING id";
			// TODO: get also subscribed BB
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":wi", $stage_id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$id_ds = $res[0]["id"];
				$sql = "INSERT INTO workflows_source_catalog(id, catalog) VALUES(:id, :sc)";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(":id", $id_ds, PDO::PARAM_INT);
				$stmt->bindParam(":sc", $token_catalog, PDO::PARAM_STR);
				$stmt->execute();
				return $id_ds;
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return null;
	}

	public function insertSource($token_catalog, $workflow_id){
		$res = [];
		try{
			$sql = "INSERT INTO workflows_source(source_type, workflow_id) VALUES(1, :wi) RETURNING id";
			// TODO: get also subscribed BB
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":wi", $workflow_id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$id_ds = $res[0]["id"];
				$sql = "INSERT INTO workflows_source_catalog(id, catalog) VALUES(:id, :sc)";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(":id", $id_ds, PDO::PARAM_INT);
				$stmt->bindParam(":sc", $token_catalog, PDO::PARAM_STR);
				$stmt->execute();
				return $id_ds;
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return null;
	}
}

class NFRs{
	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	}

	public function getNFRs(){
		$res = [];
		try{
			$sql = "SELECT t.id, t.name as technique, t.type, r.name as requirement, t.description  FROM non_functional_technique AS t inner join non_functional_requirement as r on r.id = t.type";
			// TODO: get also subscribed BB
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}
}


class Platforms{
	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	}

	public function getPlatforms(){
		$res = [];
		try{
			$sql = "SELECT *  FROM platforms";
			// TODO: get also subscribed BB
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}
}

class Executions{
	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	}

	public function getExecutions($idworkflow){
		$res = [];
		try{
			$sql = "SELECT d.id as execution_id, final_status, executed, p.platform, ds.description as status from executions as d inner join platforms as p on p.id = d.platform inner join deployments_status as ds on ds.id = d.final_status WHERE id_structure=:iw ORDER BY executed desc;
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":iw", $idworkflow, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function registExecution($idworkflow, $platform){
		$res = [];
		try{
			$timestamp = date('Y-m-d H:i:s');
			$sql = "INSERT INTO executions(executed, final_status, platform, id_structure) VALUES(:ex,1,:pl,:ids) returning id;
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":ex", $timestamp, PDO::PARAM_INT);
			$stmt->bindParam(":pl", $platform, PDO::PARAM_INT);
			$stmt->bindParam(":ids", $idworkflow, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		}catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}catch (Exception $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function updateExecution($idDeployment, $timestamp, $status){
		$res = [];
		try{
			$timestamp = date('Y-m-d H:i:s');
			$sql = "UPDATE executions SET final_status = :st, executed = :ex where id = :id;
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":st", $status, PDO::PARAM_INT);
			$stmt->bindParam(":ex", $timestamp, PDO::PARAM_INT);
			$stmt->bindParam(":id", $idDeployment, PDO::PARAM_INT);
			$stmt->execute();
		}catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			$this->log->lwrite($timestamp);
			$this->log->lwrite($status);
			$this->log->lwrite($sql);
		}catch (Exception $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}
}

class Deployments{
	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	}

	public function getLastDeployment($idworkflow){
		$res = [];
		try{
			$sql = "SELECT d.id as execution_id, p.id as platform_id, p.platform from deployments as d inner join platforms as p on p.id = d.platform inner join deployments_status as ds on ds.id = d.final_status WHERE id_structure=:iw and d.final_status = 1 ORDER BY executed desc LIMIT 1;
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":iw", $idworkflow, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function getDeployments($idworkflow){
		$res = [];
		try{
			$sql = "SELECT d.id as execution_id, final_status, executed, p.platform, ds.description as status from deployments as d inner join platforms as p on p.id = d.platform inner join deployments_status as ds on ds.id = d.final_status WHERE id_structure=:iw ORDER BY executed desc;
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":iw", $idworkflow, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function registDeployment($idworkflow, $platform){
		$res = [];
		try{
			$timestamp = date('Y-m-d H:i:s');
			$sql = "INSERT INTO deployments(executed, final_status, platform, id_structure) VALUES(:ex,1,:pl,:ids) returning id;
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":ex", $timestamp, PDO::PARAM_INT);
			$stmt->bindParam(":pl", $platform, PDO::PARAM_INT);
			$stmt->bindParam(":ids", $idworkflow, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		}catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}catch (Exception $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function updateDeployment($idDeployment, $timestamp, $status){
		$res = [];
		try{
			$timestamp = date('Y-m-d H:i:s');
			$sql = "UPDATE deployments SET final_status = :st, executed = :ex where id = :id;
			";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":st", $status, PDO::PARAM_INT);
			$stmt->bindParam(":ex", $timestamp, PDO::PARAM_INT);
			$stmt->bindParam(":id", $idDeployment, PDO::PARAM_INT);
			$stmt->execute();
		}catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			$this->log->lwrite($timestamp);
			$this->log->lwrite($status);
			$this->log->lwrite($sql);
		}catch (Exception $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}
}



class BuildingBlocks{

	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	} 

	public function createBuildingBlock(BuildingBlock $data){
		try{
			$sql = "INSERT INTO buildingblock (owner, name, command, image, port, created, description) VALUES (:ow, :nm, :cm, :im, :pt, :cr, :de);";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":ow", $data->__GET('owner'), PDO::PARAM_STR);
			$stmt->bindParam(":nm", $data->__GET('name'), PDO::PARAM_STR);
			$stmt->bindParam(":cm", $data->__GET('command'), PDO::PARAM_STR);
			$stmt->bindParam(":im", $data->__GET('image'), PDO::PARAM_STR);
			$stmt->bindParam(":pt", $data->__GET('port'), PDO::PARAM_STR);
			$stmt->bindParam(":cr", $data->__GET('created'), PDO::PARAM_INT);
			$stmt->bindParam(":de", $data->__GET('description'), PDO::PARAM_STR);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			echo $e->getMessage();
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function getBuildingBlocks($token){
		$res = [];
		try{
			$sql = "SELECT b.id, b.owner, b.name, b.command, b.image, b.port, b.description FROM buildingblock AS b 
				WHERE b.owner=:tk";
			// TODO: get also subscribed BB
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":tk", $token, PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function updateBuildingBlock(BuildingBlock $data){
		try{
			$sql = "UPDATE buildingblock SET name=:nm, command=:cm, image=:im, port=:pt WHERE id=:id;";
			$stmt = $this->db->prepare($sql);
			// $stmt->bindParam(":ow", $data->__GET('owner'), PDO::PARAM_INT);
			$stmt->bindParam(":nm", $data->__GET('name'), PDO::PARAM_STR);
			$stmt->bindParam(":cm", $data->__GET('command'), PDO::PARAM_STR);
			$stmt->bindParam(":im", $data->__GET('image'), PDO::PARAM_STR);
			$stmt->bindParam(":pt", $data->__GET('port'), PDO::PARAM_STR);
			$stmt->bindParam(":id", $data->__GET('id'), PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function deleteBuildingBlock($id){
		try{
			$sql = "DELETE FROM buildingblock WHERE id=:id;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt=null;
		return $count;
	}

	/**
	* Close connection
	*/
	public function __destruct() {
		$this->db = null;
	}
}



class Stages{

	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	} 

	public function getBBSources($id){
		$res = [];
		try{
			$sql = "SELECT distinct ss.stage_id, bb.name, wsb.id_bb as source, s.name as stage, ss.source_type  from stage_source as ss inner join source_type as st on ss.source_type = st.id inner join workflows_source_bb as wsb on wsb.id = ss.id inner join buildingblock as bb on bb.id = wsb.id_bb inner join stages as s on s.buildingblock = bb.id where ss.stage_id = :si;";
			// TODO: get also subscribed stages
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":si", $id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function getCatalogSources($id){
		$res = [];
		try{
			$sql = "SELECT distinct ss.stage_id, ss.source_type, catalog  from stage_source as ss inner join source_type as st on ss.source_type = st.id inner join workflows_source_catalog as wsb on wsb.id = ss.id where ss.stage_id = :si;";
			// TODO: get also subscribed stages
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":si", $id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function getBBSinks($id){
		$res = [];
		try{
			$sql = "SELECT distinct ss.stage_id, bb.name, wsb.id_bb as sink, s.name as stage  from stage_sink as ss inner join source_type as st on ss.sink_type = st.id inner join workflows_sink_bb as wsb on wsb.id = ss.id inner join buildingblock as bb on bb.id = wsb.id_bb inner join stages as s on s.buildingblock = bb.id where ss.stage_id = :si;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":si", $id, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function createStage(Stage $data){
		try{
			$sql = "INSERT INTO stages (owner, name, source, sink, transformation, created) VALUES (:ow, :nm, :sr, :sk, :tr, :cr);";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":ow", $data->__GET('owner'), PDO::PARAM_INT);
			$stmt->bindParam(":nm", $data->__GET('name'), PDO::PARAM_STR);
			$stmt->bindParam(":sr", $data->__GET('source'), PDO::PARAM_STR);
			$stmt->bindParam(":sk", $data->__GET('sink'), PDO::PARAM_STR);
			$stmt->bindParam(":tr", $data->__GET('transformation'), PDO::PARAM_STR);
			$stmt->bindParam(":cr", $data->__GET('created'), PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function getStages($token){
		$res = [];
		try{
			$sql = "SELECT s.id, s.owner, s.name, s.source, s.sink, s.transformation FROM stages AS s
				JOIN users AS u ON s.owner=u.id WHERE u.token=:tk";
			// TODO: get also subscribed stages
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":tk", $token, PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function updateStage(Stage $data){
		try{
			$sql = "UPDATE stages SET name=:nm, source=:sr, sink=:sk, transformation=:tr WHERE id=:id;";
			$stmt = $this->db->prepare($sql);
			// $stmt->bindParam(":ow", $data->__GET('owner'), PDO::PARAM_INT);
			$stmt->bindParam(":nm", $data->__GET('name'), PDO::PARAM_STR);
			$stmt->bindParam(":sr", $data->__GET('source'), PDO::PARAM_STR);
			$stmt->bindParam(":sk", $data->__GET('sink'), PDO::PARAM_STR);
			$stmt->bindParam(":tr", $data->__GET('transformation'), PDO::PARAM_STR);
			$stmt->bindParam(":id", $data->__GET('id'), PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function deleteStage($id){
		try{
			$sql = "DELETE FROM stages WHERE id=:id;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt=null;
		return $count;
	}

	public function updateStageTransformation(Stage $data){
		try{
			$sql = "UPDATE stages SET transformation=:tr WHERE id=:id;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":tr", $data->__GET('transformation'), PDO::PARAM_STR);
			$stmt->bindParam(":id", $data->__GET('id'), PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function readStageWithBlocks($name){
		$res = [];
		try{
			$sql = "SELECT s.name, s.source, s.sink, s.transformation, b.command, b.image, b.port 
				FROM stages AS s JOIN buildingblock AS b ON s.transformation=b.name WHERE s.name=:nm;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":nm", $name, PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetch(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	/**
	* Close connection
	*/
	public function __destruct() {
		$this->db = null;
	}
}

class Workflows{

	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	} 

	public function insertReqInWorkflow($id_workflow, $id_req){
		try{
			
			$sql = "INSERT INTO workflows_requirements (id_workflow, id_requirement) VALUES (:iw, :ir);";
			$stmt = $this->db->prepare($sql);
			// $this->log->lwrite($data->__GET('rawgraph'));
			$stmt->bindParam(":iw", $id_workflow, PDO::PARAM_INT);
			$stmt->bindParam(":ir", $id_req, PDO::PARAM_INT);
			$stmt->execute();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}catch (Exception $e) {
			echo "hola";
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return null;
	}

	public function insertStageInWorkflow($id_workflow, $id_stage){
		try{
			
			$sql = "INSERT INTO workflow_stages (id_workflow, id_stage) VALUES (:iw, :is);";
			$stmt = $this->db->prepare($sql);
			// $this->log->lwrite($data->__GET('rawgraph'));
			$stmt->bindParam(":iw", $id_workflow, PDO::PARAM_INT);
			$stmt->bindParam(":is", $id_stage, PDO::PARAM_INT);
			$stmt->execute();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}catch (Exception $e) {
			echo "hola";
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return null;
	}

	public function insertStage($owner, $name, $buildingblock, $created){
		try{
			
			$sql = "INSERT INTO stages (owner, name, buildingblock, created) VALUES (:ow, :nm, :bb, :cr) RETURNING id;";
			$stmt = $this->db->prepare($sql);
			// $this->log->lwrite($data->__GET('rawgraph'));
			$stmt->bindParam(":ow", $owner, PDO::PARAM_INT);
			$stmt->bindParam(":nm", $name, PDO::PARAM_STR);
			$stmt->bindParam(":bb", $buildingblock, PDO::PARAM_INT);
			$stmt->bindParam(":cr", $created, PDO::PARAM_INT);
			$stmt->execute();
			
			$count = $stmt->rowCount();
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$id_w = $res[0]["id"];
			return $id_w;
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}catch (Exception $e) {
			echo "hola";
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return null;
	}

	public function createWorkflow(Workflow $data){
		try{
			$sql = "INSERT INTO workflows (owner, name, status, created) VALUES (:ow, :nm, :st, :cr) RETURNING id;";
			$stmt = $this->db->prepare($sql);
			// $this->log->lwrite($data->__GET('rawgraph'));
			$stmt->bindParam(":ow", $data->__GET('owner'), PDO::PARAM_INT);
			$stmt->bindParam(":nm", $data->__GET('name'), PDO::PARAM_STR);
			$stmt->bindParam(":st", $data->__GET('status'), PDO::PARAM_INT);
			$stmt->bindParam(":cr", $data->__GET('created'), PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$id_w = $res[0]["id"];
			return $id_w;
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return null;
	}

	public function getWorkflows($token){
		$res = [];
		try{
			$sql = "SELECT w.id, w.owner, w.name, w.status, w.created FROM workflows AS w 
				 WHERE w.owner=:tk;";
			// TODO: get also subscribed workflows
			// $sql = "SELECT * FROM workflows WHERE owner ='$token' 
			// 	UNION SELECT w.id, w.owner, w.name, w.status, w.stages, w.rawgraph, w.created FROM workflows AS w 
			// 	JOIN pubsub AS p ON w.id=p.idworkflow AND p.iduser='$token';";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":tk", $token, PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function updateWorkflow(Workflow $data){
		try {
			$sql = "UPDATE workflows SET name=:na, status=:st, stages=:sg, rawgraph=:rg WHERE id=:id;";
			$stmt = $this->db->prepare($sql);
			// $stmt->bindParam(":ow", $data->__GET('owner'), PDO::PARAM_STR);
			$stmt->bindParam(":na", $data->__GET('name'), PDO::PARAM_STR);
			$stmt->bindParam(":st", $data->__GET('status'), PDO::PARAM_INT);
			$stmt->bindParam(":sg", $data->__GET('stages'), PDO::PARAM_STR);
			$stmt->bindParam(":rg", $data->__GET('rawgraph'), PDO::PARAM_STR);
			$stmt->bindParam(":id", $data->__GET('id'), PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt=null;		
		return $count;
	}

	public function deleteWorkflow(Workflow $data){
		try{
			$sql = "DELETE FROM workflows WHERE id=:id;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":id", $data->__GET('id'), PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$count = 0;
			$this->log->lwrite($e->getMessage());
		}
		$stmt=null;
		return $count;
	}

	public function getSingleWorkflow($wid, $token){
		$res = [];
		try{
			$sql = "SELECT w.id, w.owner, w.name, w.status, w.created FROM workflows AS w 
				WHERE w.id=:id AND w.owner=:tk;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":id", $wid, PDO::PARAM_INT);
			$stmt->bindParam(":tk", $token, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetch(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt=null;
		return $res;
	}

	public function getReqsInWorkflow($wid){
		$res = [];
		try{
			$sql = "SELECT nfr.name as requirement, nft.name as technique, type from non_functional_requirement as nfr inner join non_functional_technique as nft on nfr.id = nft.type inner join workflows_requirements as wr on  wr.id_requirement = nft.id where id_workflow =:id";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":id", $wid, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return $res;
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt=null;
		return $res;
	}

	public function getStagesInWorkflow($wid, $token){
		$res = [];
		try{
			$sql = "SELECT s.name, bb.name as buildingblock, bb.command, bb.image, s.id, bb.created from stages as s inner join workflow_stages as ws on ws.id_stage = s.id inner join buildingblock as bb on bb.id = s.buildingblock  where ws.id_workflow=:id and s.owner=:tk";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":id", $wid, PDO::PARAM_INT);
			$stmt->bindParam(":tk", $token, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt=null;
		return $res;
	}

	public function readWorkflowPublish($owner){
		$sql = "SELECT id, owner, name, status FROM workflows WHERE owner='$owner' AND status='0'";
		foreach($this->db->query($sql) as $res){
			$wf[] = $res;
		}
		return $wf;
	}

	public function updateWorkflowPublish($id){
		$sql = "UPDATE workflows SET status='1' WHERE id = ? ";
		$this->db->prepare($sql)->execute(array($id));
	}

	public function readWorkflowSubscribe($owner){
		$sql = "SELECT * FROM workflows w WHERE NOT EXISTS (SELECT null FROM pubsub p WHERE w.id=p.idworkflow AND p.iduser='$owner') AND owner!='$owner' AND status='1';";
		foreach($this->db->query($sql) as $res){
			$wf[] = $res;
		}
		return $wf;
	}

	public function updateWorkflowSubscribe($user, $idw){
		$sql = "INSERT INTO pubsub (idworkflow, iduser,status,c,r,u,d) VALUES (?,?,?,?,?,?,?)";
		$this->db->prepare($sql)->execute(array($idw,$user,0,0,0,0,0));
	}

	/**
	* Close connection
	*/
	public function __destruct() {
		$this->db = null;
	}
}



class PublishSubscribeWorkflows{
	private $db;
	private $log;

	public function __construct(){
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	}

	public function publishWorkflowToUser(PublishWorkflow $data){
		$count = 0;
		try{
			$sql = "INSERT INTO pub_wf_to_user(idworkflow, iduser, created) VALUES (:iw, :iu, :cr);";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":iw", $data->__GET('idworkflow'), PDO::PARAM_STR);
			$stmt->bindParam(":iu", $data->__GET('iduser'), PDO::PARAM_STR);
			$stmt->bindParam(":cr", $data->__GET('created'), PDO::PARAM_STR);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function subscribeToWorkflow($idpub){
		$count = 0;
		try{
			$sql = "UPDATE pub_wf_to_user SET subscribed=:sb WHERE id=:idp;";
			$stmt = $this->db->prepare($sql);
			$t = true;
			$stmt->bindParam(":sb", $t, PDO::PARAM_BOOL);
			$stmt->bindParam(":idp", $idpub, PDO::PARAM_INT);
			$stmt->execute();
			$count = $stmt->rowCount();
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $count;
	}

	public function getWorkflowsPublishedFromMe($user){
		$res = [];
		try{
			$sql = "SELECT p.id AS idpub, p.idworkflow, p.iduser, p.subscribed, w.name AS wfname, w.owner, w.stages
				FROM pub_wf_to_user AS p 
				JOIN workflows AS w ON p.idworkflow=w.id
				WHERE w.owner=:us;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":us", $user, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	public function getWorkflowsSubscribedToMe($user){
		$res = [];
		try{
			$sql = "SELECT p.id AS idpub, p.idworkflow, p.iduser, p.subscribed, w.name AS wfname, w.owner, w.stages
				FROM pub_wf_to_user AS p 
				JOIN workflows AS w ON p.idworkflow=w.id
				WHERE p.iduser=:us;";
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(":us", $user, PDO::PARAM_INT);
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
		}
		$stmt = null;
		return $res;
	}

	/**
	* Close connection
	*/
	public function __destruct() {
		$this->db = null;
	}
}
