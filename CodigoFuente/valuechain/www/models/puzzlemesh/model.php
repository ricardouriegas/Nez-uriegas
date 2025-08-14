<?php

class Users{

	private $user;
	private $db;

	public function __construct(){
		require_once "configdb.php";
		$this->user = array();
		$this->db = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME."", DB_USER, DB_PASS);
	}

	public function createUser(User $data){
		$sql = "INSERT INTO users (name, email, password, typeuser) VALUES (?,?,?,?)";
		$sth = $this->db->prepare($sql)->execute(
			array(
				$data->__GET('name'),
				$data->__GET('email'),
				$data->__GET('password'),
				$data->__GET('typeuser'),
			)
		);
		return $sth;
	}

	public function readUser($email, $password){
		$sql = "SELECT id, name, email, password, typeUser FROM users WHERE email = '$email' AND password = '$password' ";
		foreach($this->db->query($sql) as $res){
			$this->user[] = $res;
		}
		return $this->user;
		$this->db = null;
	}

	public function updateUser(User $data){}

	public function deleteUser($id){}


}

class BlackBoxes{
	private $blackBox;
	private $db;

	public function __construct(){
		require_once "configdb.php";
		$this->blackBox = array();
		$this->db = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME."", DB_USER, DB_PASS);
	}

	public function createBlackBoxe(BlackBox $dataBB){
		$sql = "INSERT INTO buildingblock (owner, name, command, image, port, created) VALUES (?,?,?,?,?,?)";
		$this->db->prepare($sql)->execute(
			array(
				$dataBB->__GET('owner'),
				$dataBB->__GET('name'),
				$dataBB->__GET('command'),
				$dataBB->__GET('image'),
				$dataBB->__GET('port'),
				$dataBB->__GET('created'),
			)
		);
	}

	public function readBlackBoxe(){
		$sql = "SELECT id, owner, name, command, image , port FROM buildingblock";
		if($stm = $this->db->query($sql))
		{
            $bbs = $stm->fetchAll();

            foreach($bbs as $res){
                $this->blackBox[] = $res;
            }
            return $this->blackBox;

		}
		$this->db = null;
	}

	public function updateBlackBox(BlackBox $dataBB){
		$sql = "UPDATE buildingblock SET owner=?, name=?, command=?, image=?, port=?, created=?  WHERE id = ? ";
		$this->db->prepare($sql)->execute(
			array(
				$dataBB->__GET('owner'),
				$dataBB->__GET('name'),
				$dataBB->__GET('command'),
				$dataBB->__GET('image'),
				$dataBB->__GET('port'),
				$dataBB->__GET('created'),
				$dataBB->__GET('id')
			)
		);
	}

	public function deleteBlackBox($id){
		$sql = $this->db->prepare("DELETE FROM buildingblock WHERE id = ?");
		$sql->execute(array($id));
	}






}

class Relations{
	private $relation;
	private $db;

	public function __construct(){
		require_once "configdb.php";
		$this->relation = array();
		$this->db = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME."", DB_USER, DB_PASS);
	}

	public function createRelationStageBB($data, $id){
		$sql = "UPDATE stagepaternbb SET buildingblock = ? where stage = ?";
		$this->db->prepare($sql)->execute(
			array(
				$data, $id

			)
		);

	}

}

class Patterns{
	private $pattern;
	private $db;

	public function __construct(){
		require_once "configdb.php";
		$this->pattern = array();
		$this->db = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME."", DB_USER, DB_PASS);
	}

	public function createPattern(Pattern $data){
		$sql = "INSERT INTO patterns (owner, name, task, pattern, workers, loadbalancer, created) VALUES (?,?,?,?,?,?,?)";
		$this->db->prepare($sql)->execute(
			array(
				$data->__GET('owner'),
				$data->__GET('name'),
				$data->__GET('task'),
				$data->__GET('pattern'),
				$data->__GET('workers'),
				$data->__GET('loadbalancer'),
				$data->__GET('created'),
			)
		);

	}

	public function readPattern(){
		$sql = "SELECT * FROM patterns";
		foreach($this->db->query($sql) as $res){
			$this->pattern[] = $res;
		}
		return $this->pattern;
		$this->db = null;
	}

	public function updatePattern(Pattern $data){}

	public function deletePattern($id){
		$sql = $this->db->prepare("DELETE FROM patterns WHERE id = ?");
		$sql->execute(array($id));
	}
}

class Stages{
	private $stage;
	private $db;

	public function __construct(){
		require_once "configdb.php";
		$this->stage = array();
		$this->db = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME."", DB_USER, DB_PASS);
	}

	public function createStage(Stage $data){
		$sql = "INSERT INTO stages (owner, name, source, sink, transformation, created) VALUES (?,?,?,?,?,?)";
		$this->db->prepare($sql)->execute(
			array(
				$data->__GET('owner'),
				$data->__GET('name'),
				$data->__GET('source'),
				$data->__GET('sink'),
				$data->__GET('transformation'),
				$data->__GET('created'),
			)
		);

		$sql2 = "INSERT INTO stagepaternbb (stage) VALUES (?)";
			$this->db->prepare($sql2)->execute(
				array($data->__GET('name'))
		);

	}

	public function readStage(){
		$sql = "SELECT * FROM stages";
		foreach($this->db->query($sql) as $res){
			$this->stage[] = $res;
		}
		return $this->stage;
		$this->db = null;
	}

	public function updateStage(Stage $data){}


	public function updateStageTrans($stage, $id){

		$sql = "UPDATE stages SET transformation = ? where name = ?";
		$this->db->prepare($sql)->execute(
			array(
				$stage, $id

			)
		);

	}

	public function deleteStage($id){
		$sql = $this->db->prepare("DELETE FROM stages WHERE id = ?");
		$sql->execute(array($id));

	}

	public function deleteStage2($name){
		$sql = $this->db->prepare("DELETE FROM stagepaternbb WHERE stage = ?");
		$sql->execute(array($name));

	}


	public function readTreeStage($name){
		//$sql = "SELECT s.id, s.name, s.transformation, p.task from stages as s join patterns as p on s.transformation = p.name where s.name = '$name';";
		//$sql = "SELECT * FROM stagepaternbb WHERE stage = '$name'";
		//
		$sql = "SELECT o.id, o.stage, o.buildingblock, b.name from stagepaternbb as o join buildingblock as b  on o.buildingblock=b.id where o.stage='$name';";
		//echo $sql;
		foreach($this->db->query($sql) as $res){
			$this->stage[] = $res;
		}
		return $this->stage;
		$this->db = null;
	}

	public function readStageWithBlocks($name){
		$sql = "SELECT s.name, s.source, s.sink, s.transformation, b.command, b.image, b.port
			FROM stages AS s JOIN buildingblock AS b ON s.transformation=b.name WHERE s.name='$name';";
		$stagewbb = [];
		foreach($this->db->query($sql) as $res){
			$stagewbb[] = $res;
		}
		return $stagewbb;
	}


}

class Workflows{
	private $workflow;
	private $db;
	public function __construct(){
		require_once "configdb.php";
		$this->workflow= array();
		$this->db = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME."", DB_USER, DB_PASS);
	}

	public function createWorkflow(Workflow $data){
		$sql = "INSERT INTO workflows (owner, name, status, stages, created) VALUES (?,?,?,?,?)";

		$this->db->prepare($sql)->execute(
			array(
				$data->__GET('owner'),
				$data->__GET('name'),
				$data->__GET('status'),
				$data->__GET('stages'),
				$data->__GET('created'),
			)
		);

	}

	public function readWorkflow($owner){
		//$sql = "SELECT id, owner, name, status FROM workflows";
		$sql = "SELECT * from workflows where owner ='$owner' union select w.id, w.owner, w.name, w.status, w.stages, w.created from workflows as w join pubsub as p on w.id=p.idworkflow and p.iduser='$owner';";
		//echo $sql;
		$workflows = $this->db->query($sql) ;
		if($workflows){
            foreach($workflows as $res){
                $this->workflow[] = $res;
            }
		}
		return $this->workflow;
		$this->db = null;
	}

	public function updateWorkflow(Workflow $data){}

	public function deleteWorkflow($id){
		$sql = $this->db->prepare("DELETE FROM workflows WHERE id = ?");
		$sql->execute(array($id));
	}


	public function readWorkflowPublish($owner){
		$sql = "SELECT id, owner, name, status FROM workflows WHERE owner='$owner' AND status='0'";
		foreach($this->db->query($sql) as $res){
			$this->workflow[] = $res;
		}
		return $this->workflow;
		$this->db = null;
	}



	public function updateWorkflowPublish($id){
		$sql = "UPDATE workflows SET status='1' WHERE id = ? ";
		$this->db->prepare($sql)->execute(array($id));
	}





	public function readWorkflowSubscribe($owner){
		$sql = "SELECT * from workflows w where NOT EXISTS (SELECT null from pubsub p where w.id=p.idworkflow and p.iduser='$owner') and owner!='$owner' and status='1';";
		foreach($this->db->query($sql) as $res){
			$this->workflow[] = $res;
		}
		return $this->workflow;
		$this->db = null;
	}



	public function updateWorkflowSubscribe($user, $idw){
		$sql = "INSERT INTO pubsub (idworkflow, iduser,status,c,r,u,d) VALUES (?,?,?,?,?,?,?)";
		$this->db->prepare($sql)->execute(array($idw,$user,0,0,0,0,0));
	}



	public function readSingleWorkflow($wfname, $owner){
		$sql = "SELECT * FROM workflows WHERE owner='$owner' AND name='$wfname' UNION SELECT w.id, w.owner, w.name, w.status, w.stages, w.created FROM workflows AS w JOIN pubsub AS p ON w.id=p.idworkflow AND p.iduser='$owner';";
		$singlewf = [];
		foreach($this->db->query($sql) as $res){
			$singlewf[] = $res;
		}
		return $singlewf;
		$this->db = null;
	}

}

class Request{
	private $request;
	private $db;
	public function __construct(){
		require_once "configdb.php";
		$this->request= array();
		$this->db = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME."", DB_USER, DB_PASS);
	}


	public function readRequest($iduser){
		$sql = "SELECT w.name as workflow, p.iduser, u.name as user, p.id from workflows as w join pubsub as p on w.id=p.idworkflow join users as u on p.iduser=u.id where owner='$iduser' and p.status='0'";
		foreach($this->db->query($sql) as $res){
			$this->request[] = $res;
		}
		return $this->request;
		$this->db = null;
	}


	public function updateRequest($idR){
		$sql = "INSERT INTO pubsub (status,c,r,u,d) VALUES (?,?,?,?,?) where id='$idR'";
		$this->db->prepare($sql)->execute(array(1,1,1,1,1));
	}


}


class YmlFile{
	private $yml;
	private $db;
	public function __construct(){
		require_once "configdb.php";
		$this->yml= array();
		$this->db = new PDO("pgsql:host=".DB_HOST.";dbname=".DB_NAME."", DB_USER, DB_PASS);
	}


	public function createYml(Yml $data){
		$sql = "INSERT INTO ymlFiles (owner, name, pathFile, description, created) VALUES (?,?,?,?,?)";
		$this->db->prepare($sql)->execute(
			array(
				$data->__GET('owner'),
				$data->__GET('name'),
				$data->__GET('pathFile'),
				$data->__GET('description'),
				$data->__GET('created'),
			)
		);

	}



	public function readYml($iduser){
		$sql = "SELECT * from ymlfiles where owner='$iduser'";
		foreach($this->db->query($sql) as $res){
			$this->yml[] = $res;
		}
		return $this->yml;
		$this->db = null;
	}


	public function updateRequest($idR){
		$sql = "INSERT INTO pubsub (status,c,r,u,d) VALUES (?,?,?,?,?) where id='$idR'";
		$this->db->prepare($sql)->execute(array(1,1,1,1,1));
	}


}


?>
