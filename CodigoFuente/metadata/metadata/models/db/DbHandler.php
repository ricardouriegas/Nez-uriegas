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



	public function getNodesActive() {
      try{
         $sql = 'SELECT n.id, n.url, CASE WHEN used.sum is NULL THEN 0 ELSE used.sum END AS used, n.capacity AS total FROM nodes n LEFT JOIN (SELECT o.node_id, SUM(f.sizefile) FROM operations o INNER JOIN files f ON o.file_id = f.keyfile WHERE o.type = \'w\' GROUP BY (o.node_id)) used ON n.id = used.node_id WHERE n.status = \'1\';';
         #echo $sql;
		 $stmt = $this->db->prepare($sql);
         $stmt->execute();
         //$nodes = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Node', array('id','url','used','total'));
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         //$nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }




	/*------------- `files` table method ------------------*/

	/**
	* Register a new file
	* @param  String $id     File id
	* @param  String $name   File name
	* @param  String $size   File size
	* @return Boolean
	*/
	public function createFile($file) {
		try {
			$sql = 'INSERT INTO files(id, name, size) VALUES(?, ?, ?) returning id;';
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $file['id'], PDO::PARAM_STR);
			$stmt->bindParam(2, $file['name'], PDO::PARAM_STR);
			$stmt->bindParam(3, $file['size'], PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$file = $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				$file = false;
			}
			$stmt = null;
			return $file;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		}
	}

	/**
	* @return array|mixed
	*/
	public function getAllFiles() {
		try {
			$sql = 'SELECT * FROM files;';
			$stmt = $this->db->prepare($sql);
			if ($stmt->execute()) {
				$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
			} else {
				$files = false;
			}
			$stmt = null;
			return $files;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		}
	}

	/**
	* @param $id
	* @return array|mixed
	*/
	public function getFile($keyfile) {
        try{
            $stmt = $this->conn->prepare("SELECT sizefile, namefile, chunks, isciphered  FROM files WHERE keyfile=:keyfile");
            $stmt->bindParam(":keyfile", $keyfile, PDO::PARAM_STR);
            $stmt->execute();
			if ($stmt->rowCount() == 1) {
				$file = $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				$file = false;
			}
            $stmt = NULL;
            return $file;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }

	/**
	* Register relation files-user
	* @param  String $idChunk  Chunk id
	* @param  String $idFile   File id
	* @return Boolean
	*/
	public function registerFilesUser($fileId, $userId) {
		try {
			$sql = 'INSERT INTO files_user(file_id, user_id) VALUES(?, ?);';
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $fileId, PDO::PARAM_STR);
			$stmt->bindParam(2, $userId, PDO::PARAM_STR);
			if ($stmt->execute()) {
				$file = true;
			} else {
				$file = false;
			}
			$stmt = null;
			return $file;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		}
	}

	/**
	* @param $id
	* @return bool
	*/
	public function deleteFile($id) {
		try {
			$sql = 'DELETE FROM files WHERE id = ?;';
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $id, PDO::PARAM_INT);
			$stmt->execute();
			$numRows = $stmt->rowCount();
			if ($numRows > 0) {
				return true;
			} else {
				return false;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		} 
	}

    public function deleteFiles() {
		try {
			$sql = 'DELETE FROM files;';
			$stmt = $this->db->prepare($sql);
			$stmt->execute();
			$numRows = $stmt->rowCount();
			if ($numRows > 0) {
				return true;
			} else {
				return false;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		} 
    }

	/*------------- `nodes` table method ------------------*/

	/**
	* Register a new node
	* @param  Array $node
	* @return Boolean
	*/
	public function createNode($node) {
		try {
			$sql = 'INSERT INTO nodes(url, capacity, memory) VALUES(?, ?, ?) returning id;';
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $node['url'], PDO::PARAM_STR);
			$stmt->bindParam(2, $node['capacity'], PDO::PARAM_STR);
			$stmt->bindParam(3, $node['memory'], PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$node = $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				$node = false;
			}
			$stmt = null;
			return $node;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		}
	}

	/**
	* @return array|mixed
	*/
	public function getAllNodes() {
		try {
			$sql = 'SELECT * FROM nodes;';
			$stmt = $this->db->prepare($sql);
			if ($stmt->execute()) {
				$nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
	* @param $id
	* @return array|mixed
	*/
	public function getNode($id) {
		//get one topic
		try {
			$sql = 'SELECT * FROM nodes WHERE id = ?;';
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $id, PDO::PARAM_STR);
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$node = $stmt->fetch(PDO::FETCH_ASSOC);
			} else {
				$node = false;
			}
			$stmt = null;
			return $node;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
			return false;
		}    
	}

	/**
	* @param $id
	* @return bool
	*/
	public function deleteNode($id) {
		try {
			$sql = 'DELETE FROM nodes WHERE id = ?;';
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(1, $id, PDO::PARAM_INT);
			$stmt->execute();
			$numRows = $stmt->rowCount();
			if ($numRows > 0) {
				return true;
			} else {
				return false;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$this->log->lwrite($e->getMessage());
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