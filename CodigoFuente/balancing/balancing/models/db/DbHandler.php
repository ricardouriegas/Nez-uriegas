<?php

require_once dirname(__FILE__) . '/Connection.php';
require_once dirname(__FILE__) . '../../log/Log.php';

class DbHandler {
	/**
	* @var db
	* @var log
	*/
	private $db;
	private $log;

	/**
	* DbHandler constructor.
	*/
	public function __construct() {
		$db = new Connection();
		$this->db = $db->getConnection();
		$this->log = new Log;
	}

	/*------------- `uf`------------------*/

	/**
	* [getNodesPushFile description]
	* @param  [type] $fileId [description]
	* @return [type]          [description]
	*/
	public function getNodesPushFile($fileId) {
		try {
			$sql = 'SELECT o.id, n.url FROM nodes n INNER JOIN operations o ON o.node_id = n.id WHERE o.file_id = ? AND o.status = \'0\' AND o.type = \'w\' ORDER BY o.created_at;';
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $fileId, PDO::PARAM_STR);
			if ($stmt->execute()) {
				$pusheds = $stmt->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$pusheds = false;
			}
			$stmt = null;
			return $pusheds;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		}
	}
	
	/**
	* Fetching list active nodes
	* @return Array Nodes
	*/
	public function getNodesActive() {
		try{
			$sql = 'SELECT n.id, n.url, CASE WHEN used.sum is NULL THEN 0 ELSE used.sum END AS used, n.capacity AS total FROM nodes n LEFT JOIN (SELECT o.node_id, SUM(f.size) FROM operations o INNER JOIN files f ON o.file_id = f.id WHERE o.type = \'w\' GROUP BY (o.node_id)) used ON n.id = used.node_id WHERE n.status = \'1\';';
			$stmt = $this->db->prepare($sql);
			if ($stmt->execute()) {
				$nodes = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Node', array('id','url','used','total'));
			} else {
				$nodes = false;
			}
			$stmt = null;
			return $nodes;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		}
	}

	/**
	* Obtain the nodes that contain a given file
	* @param  String $fileId  File id
	* @return Array           Nodes
	*/
	public function getFileInNodes($fileId) {
		try {
			$sql = 'SELECT n.id, n.url, CASE WHEN used.sum is NULL THEN 0 ELSE used.sum END AS used, n.memory AS total FROM nodes n INNER JOIN (SELECT n.id FROM nodes n INNER JOIN operations o ON o.node_id = n.id WHERE o.file_id = ? AND o.type = \'w\' AND o.status = \'1\' AND n.status = \'1\') no ON no.id = n.id LEFT JOIN (SELECT o.node_id, SUM(f.size) FROM operations o INNER JOIN files f ON o.file_id = f.id WHERE o.status = \'0\' GROUP BY (o.node_id)) used ON n.id = used.node_id;';
			$stmt = $this->db->connect()->prepare($sql);
			$stmt->bindParam(1, $fileId, PDO::PARAM_STR);
			$stmt->execute();
			$nodes = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Node', array('id','url','used','total'));
			$this->db->close();
			return $nodes;
		} catch (PDOException $e) {
			$this->db->close();
			$this->log->insertDB($e);
			return false;
		}
	}

	/**
	* @desc Close connection
	*/
	public function __destruct() {
		$this->db = null;
	}

}