<?php
/*
* DbHandler
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/

require_once dirname(__FILE__) . '/Connection.php';
require_once dirname(__FILE__) . '/Node.php';
require_once dirname(__FILE__) . '/Log.php';

class DbHandler {

   private $conn;
   private $log;

   function __construct() {
      // opening db connection
      $db = new Connection();
	  $this->conn = $db->getConnection();
      $this->log = new Log;
   }

   /**
	* Generating random Unique hash String 
	* @return String    key
	*/
	public function generateToken() {
		return sha1(join('',array(time(),rand())));
    }

    public function getAllFiles() {
		try {
			$sql = 'SELECT * FROM files;';
			$stmt = $this->conn->prepare($sql);
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

    public function getAbekeys($keyfile) {
        try{
            $stmt = $this->conn->prepare("SELECT url FROM abekeys WHERE keyfile=:kf;");
            $stmt->bindParam(":kf", $keyfile, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = NULL;
            return $rows;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }

    public function saveAbekeys($keyfile, $abekey) {
        try{
            $stmt = $this->conn->prepare("INSERT INTO abekeys(keyfile, url)
                VALUES(:kf, :ak);");
            $stmt->bindParam(":kf", $keyfile, PDO::PARAM_STR);
            $stmt->bindParam(":ak", $abekey, PDO::PARAM_STR);
            $stmt->execute();
        
            $stmt = NULL;
            return true;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }
    
    public function register($nameuser, $password) {
        try{
            $keyuser   = $this->generateToken();
            $tokenuser = $this->generateToken();
            $apikey = $this->generateToken();

            $stmt = $this->conn->prepare("INSERT INTO users VALUES(?,?,?,?,?)");
            $stmt->bindParam(1, $keyuser, PDO::PARAM_STR);
            $stmt->bindParam(2, $password, PDO::PARAM_STR);
            $stmt->bindParam(3, $nameuser, PDO::PARAM_STR);
            $stmt->bindParam(4, $tokenuser, PDO::PARAM_STR);
            $stmt->bindParam(5, $apikey, PDO::PARAM_STR);
            $stmt->execute();
        
            $stmt = NULL;
            return true;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }

    /**
     * *
     * @param  [String] $tokenuser   	 [token del usuario]
     * @param  [String] $keyresource 	 [c6315fae8d30575901a44a0e8cfde3375be50e433 llave del recurso catálogo o grupo]
     * @param  [String] $namefile    	 [nombre del archivo]
     * @param  [String] $sizefile    	 [peso del archivo]
     * @param  [String] $dispersemode    [algoritmo de dispersion (IDA, RAID5, SINGLE )]
     */
    public function push($tokenuser,$keyfile,$keyresource) {
        try{
            $keyuser = $this->getkeyuser($tokenuser);
            $unixtime= time();

            $stmt = $this->conn->prepare("INSERT INTO push VALUES(?, ?, ?, ?)");
            $stmt->bindParam(1, $keyuser, PDO::PARAM_STR);
            $stmt->bindParam(2, $keyfile, PDO::PARAM_STR);
            $stmt->bindParam(3, $keyresource, PDO::PARAM_STR);
            $stmt->bindParam(4, $unixtime, PDO::PARAM_INT);
            $stmt->execute();
        
            $stmt = NULL;
            return true;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }

    public function getLastInsertId() {
        try{
            return $this->conn->lastInsertId();
        }catch(PDOException $e) {
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }

    public function statsOps($get, $id, $o, $id_root) {
        try{
            $stmt = $this->conn->prepare("INSERT INTO stats(tokenuser, sizefile, chunks, time, type, id_root, keyfile, organization)
                VALUES(:tokenuser, :sizefile, :chunks, :time, :type, :id_root, :keyfile, :organization)");

            $stmt->bindParam(":tokenuser", array_values($get)[0], PDO::PARAM_STR);
            $stmt->bindParam(":sizefile", array_values($get)[1], PDO::PARAM_INT);
            $stmt->bindParam(":chunks", array_values($get)[2], PDO::PARAM_INT);
            $stmt->bindParam(":time", $o, PDO::PARAM_INT);
            $stmt->bindParam(":type", $id, PDO::PARAM_STR);
            $stmt->bindParam(":keyfile", array_values($get)[3], PDO::PARAM_STR);
            $stmt->bindParam(":id_root", $id_root, PDO::PARAM_STR);
            $stmt->bindParam(":organization", array_values($get)[5], PDO::PARAM_STR);
            $stmt->execute();
 
            $stmt = NULL;
            return true;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }

    public function stats_d($get) {
        try{
            $oo = "pull";
            $stmt = $this->conn->prepare("INSERT INTO stats(tokenuser, sizefile, chunks, time, type, keyfile, organization)
            VALUES(:tokenuser, :sizefile, :chunks, :time, :type, :keyfile, :organization)");
            
            $stmt->bindParam(":tokenuser", array_values($get)[0], PDO::PARAM_STR);
            $stmt->bindParam(":sizefile", array_values($get)[1], PDO::PARAM_INT);
            $stmt->bindParam(":chunks", array_values($get)[2], PDO::PARAM_INT);
            $stmt->bindParam(":time", array_values($get)[5], PDO::PARAM_INT);
            $stmt->bindParam(":type", $oo, PDO::PARAM_STR);
            $stmt->bindParam(":keyfile", array_values($get)[3], PDO::PARAM_STR);
            $stmt->bindParam(":organization", array_values($get)[4], PDO::PARAM_STR);
            $stmt->execute();
            $id_root= $this->getLastInsertId();
        
            $stmt = NULL;
            return $id_root;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }
   
    public function stats($get) {
        try{
            $oo = "sync";
                $stmt = $this->conn->prepare("INSERT INTO stats(tokenuser, sizefile, chunks, time, type, keyfile, organization)
                VALUES(:tokenuser, :sizefile, :chunks, :time, :type, :keyfile, :organization);");

                $stmt->bindParam(":tokenuser", array_values($get)[0], PDO::PARAM_STR);
                $stmt->bindParam(":sizefile", array_values($get)[1], PDO::PARAM_INT);
                $stmt->bindParam(":chunks", array_values($get)[2], PDO::PARAM_INT);
                $stmt->bindParam(":time", array_values($get)[6], PDO::PARAM_INT);
                $stmt->bindParam(":type", $oo, PDO::PARAM_STR, PDO::PARAM_STR);
                $stmt->bindParam(":organization", array_values($get)[5], PDO::PARAM_STR);
                $stmt->bindParam(":keyfile", array_values($get)[3], PDO::PARAM_STR);
                $stmt->execute();
                $id_root= $this->getLastInsertId();
        
            $stmt = NULL;
            return $id_root;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }

    public function setFileInfo($keyfile,$sizefile) {
        try{
            $stmt = $this->conn->prepare("UPDATE files set sizefile=:sizefile WHERE keyfile=:keyfile");
            $stmt->bindParam(":keyfile", $keyfile, PDO::PARAM_STR);
            $stmt->bindParam(":sizefile", $sizefile, PDO::PARAM_INT);
            $rtn=$stmt->execute();
        
            $stmt = NULL;
            return $rtn;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
    }

    public function getFile($keyfile) {
         try{
            //$sql = 'SELECT sizefile, namefile, chunks, isciphered FROM files WHERE keyfile= :keyfile;';
            $sql = 'SELECT * FROM files WHERE keyfile= :keyfile;';
            $stmt = $this->conn->prepare($sql);
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

   public function getKeyFile() {
        try{
            $stmt = $this->conn->prepare("SELECT keyfile FROM push");
            $stmt->execute();
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = NULL;
            return $files;
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
        
    }

   /**
     * *
     * @param  [String] $tokenuser [token del usuario]
     * @return [String] $keyuser   [la clave principal del usuario]
     */
    public function getkeyuser($tokenuser) {
        try{
            $stmt = $this->conn->prepare("SELECT keyuser FROM users WHERE tokenuser = ?");
            $stmt->bindParam(1, $tokenuser, PDO::PARAM_STR);
            $stmt->execute();
            $infouser = $stmt->fetch(PDO::FETCH_ASSOC);
            if($stmt->rowCount()){
                $keyuser = $infouser['keyuser'];
                $stmt = null;   
                return $keyuser;
            }
        }catch(PDOException $e) {
            $stmt = NULL;
            $this->log->lwrite($e->getMessage());
            return false;
        }
        
    }

   /*------------- `users` table method ------------------*/

  /**
    * Get file info
    * @param String $id  File id
    * @return Array      File info
    */
   public function getUserId($token) {
      try {
         $sql = 'SELECT keyuser FROM users WHERE tokenuser = ?;';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $token, PDO::PARAM_STR);
         $stmt->execute();
         if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
         } else {
            $user = false;
         }
         $stmt = NULL;
         return $user;
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
   public function registerFile($id, $name, $size, $chunks, $isciphered, $hashfile,$dispersemode) {
      try {
         $sql = 'INSERT INTO files(keyfile, namefile, sizefile, chunks, isciphered, hashfile, disperse) VALUES(?, ?, ?, ?, ?, ?, ?);';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $id, PDO::PARAM_STR);
         $stmt->bindParam(2, $name, PDO::PARAM_STR);
         $stmt->bindParam(3, $size, PDO::PARAM_STR);
         $stmt->bindParam(4, $chunks, PDO::PARAM_INT);
         $stmt->bindParam(5, $isciphered, PDO::PARAM_BOOL);
         $stmt->bindParam(6, $hashfile, PDO::PARAM_STR);
         $stmt->bindParam(7, $dispersemode, PDO::PARAM_STR);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * Get file info
    * @param String $id  File id
    * @return Array      File info
    */
   public function getInfoFile($id) {
      try {
         $sql = 'SELECT namefile, sizefile, chunks, isciphered FROM files WHERE keyfile = ?;';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $id, PDO::PARAM_STR);
         $stmt->execute();
         if ($stmt->rowCount() == 1) {
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
         } else {
            $file = false;
         }
         $stmt = NULL;
         return $file;
      } catch (PDOException $e) {
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
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $fileId, PDO::PARAM_STR);
         $stmt->bindParam(2, $userId, PDO::PARAM_STR);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   

   /*------------- `chunks` table method ------------------*/
   
   /**
    * Register a new chunk
    * @param  String $id     File id
    * @param  String $name   File name
    * @param  String $size   File size
    * @return Boolean
    */
   public function registerChunk($id, $name, $size) {
      try {
         $sql = 'INSERT INTO chunks(id, name, size) VALUES(?, ?, ?);';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $id, PDO::PARAM_STR);
         $stmt->bindParam(2, $name, PDO::PARAM_STR);
         $stmt->bindParam(3, $size, PDO::PARAM_STR);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * Get chunk info
    * @param String $id  Chunk id
    * @return Array      Chunk info
    */
   public function getInfoChunk($id) {
      try {
         $sql = 'SELECT id, name, size, status, created_at FROM chunks WHERE id = ?;';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $id, PDO::PARAM_STR);
         $stmt->execute();
         if ($stmt->rowCount() == 1) {
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
         } else {
            $file = false;
         }
         $stmt = NULL;
         return $file;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * Get all chunks info from a given file
    * @param String $fileId  File id
    * @return Array          Chunks info
    */
   public function getInfoChunksFile($fileId) {
      try {
        $i = 0;
        //$sql = "SELECT c.id, c.name, c.size, c.status, c.created_at FROM chunks c INNER JOIN chunks_file cf ON cf.file_id = '".$fileId."' AND cf.chunk_id = c.id";
        $sql = "SELECT c.id, c.name, c.size, c.status, c.created_at, n.url FROM chunks c INNER JOIN chunks_file cf ON cf.file_id = '".$fileId."' AND cf.chunk_id = c.id inner join operations as o on o.chunk_id = c.id and o.type = 'w' inner join nodes as n on o.node_id = n.id;";
        foreach ($this->conn->query($sql) as $row) {
            $result[$i]['id'] = $row['id'];
            $result[$i]['name'] = $row['name'];
            $result[$i]['size'] = $row['size'];
            $result[$i]['url'] = $row['url'];
            
            $i = $i + 1;
        }
         return $result;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getOperationIdByChunkId($chunkId) {
      try {
        $i = 0;
        $sql = "SELECT o.id FROM operations AS o INNER JOIN chunks AS c ON o.chunk_id = c.id WHERE c.id = '".$chunkId."' AND o.type = 'w'";
        foreach ($this->conn->query($sql) as $row) {
            $result[$i]['id'] = $row['id'];
            $i = $i + 1;
        }
         return $result;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * Register relation chunks-file
    * @param  String $idChunk  Chunk id
    * @param  String $idFile   File id
    * @return Boolean
    */
   public function registerChunksFile($chunkId, $fileId) {
      try {
         $sql = 'INSERT INTO chunks_file(chunk_id, file_id) VALUES(?, ?);';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $chunkId, PDO::PARAM_STR);
         $stmt->bindParam(2, $fileId, PDO::PARAM_STR);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * Delete files used only for statistics.php
    * @return Boolean
    */
   public function deleteFiles() {
      try {
         $sql = 'DELETE FROM files;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * Delete chunks used only for statistics.php
    * @return Boolean
    */
   public function deleteChunks() {
      try {
         $sql = 'DELETE FROM chunks;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * Delete abekeys used only for statistics.php
    * @return Boolean
    */
   public function deleteAbekeys() {
      try {
         $sql = 'DELETE FROM abekeys;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /*------------- `nodes` table method ------------------*/

   /**
    * Register a new node
    * @param  String $id        Node id
    * @param  String $url       Node url
    * @param  String $capacity  Node capacity
    * @param  String $memory    Node memory
    * @return Boolean
    */
   public function registerNode($url, $capacity, $memory) {
      try {
         $sql = 'INSERT INTO nodes(url, capacity, memory) VALUES(?, ?, ?);';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $url, PDO::PARAM_STR);
         $stmt->bindParam(2, $capacity, PDO::PARAM_STR);
         $stmt->bindParam(3, $memory, PDO::PARAM_STR);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function deleteAllNodes() {
      try {
         $sql = 'DELETE FROM nodes;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }


   /**
    * Fetching list nodes
    * @return Array Nodes
    */
   public function getAllNodes() {
      try{
         $sql = 'SELECT * FROM nodes;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * [getNodesPushFile description]
    * @param  [type] $fileId [description]
    * @return [type]          [description]
    */
   public function getNodesPushFile($fileId) {
      try {
         $sql = 'SELECT o.id, n.url FROM nodes n INNER JOIN operations o ON o.node_id = n.id WHERE o.file_id = ? AND o.status = \'0\' AND o.type = \'w\' ORDER BY o.created_at;';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $fileId, PDO::PARAM_STR);
         $stmt->execute();
         $pusheds = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $pusheds;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /*------------- uf ------------------*/

   /**
    * Fetching list active nodes
    * @return Array Nodes
    */
   public function getNodesActive() {
      try{
         $sql = 'SELECT n.id, n.url, CASE WHEN used.sum is NULL THEN 0 ELSE used.sum END AS used, n.capacity AS total FROM nodes n LEFT JOIN (SELECT o.node_id, SUM(f.sizefile) FROM operations o INNER JOIN files f ON o.file_id = f.keyfile WHERE o.type = \'w\' GROUP BY (o.node_id)) used ON n.id = used.node_id WHERE n.status = \'1\';';
         //echo $sql;
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Node', array('id','url','used','total'));
         //$nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

  /**
    * Obtain the nodes that contain a given chunk
    * @param  String $fileId  Chunk id
    * @return Array           Nodes
    */
   public function getChunkInNodes($chunkId) {
      try {
         $sql = "SELECT n.id, n.url FROM nodes n INNER JOIN operations o ON o.node_id = n.id  WHERE o.file_id = ?  AND o.type = 'w';";
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $chunkId, PDO::PARAM_STR);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Node', array('id','url','used','total'));
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         return false;
      }
   }

   /**
    * Obtain the nodes that contain a given chunk
    * @param  String $fileId  Chunk id
    * @return Array           Nodes
    */
    public function getChunkInNodesReliability($chunkId) {
      try {
         $sql = 'SELECT n.id, n.url, CASE WHEN used.sum is NULL THEN 0 ELSE used.sum END AS used, n.memory AS total FROM nodes n INNER JOIN (SELECT n.id FROM nodes n INNER JOIN operations o ON o.node_id = n.id WHERE o.file_id = ? AND o.type = \'w\' AND o.status = \'1\' AND n.status = \'1\') no ON no.id = n.id LEFT JOIN (SELECT o.node_id, SUM(c.size) FROM operations o INNER JOIN chunks c ON o.chunk_id = c.id WHERE o.status = \'0\' GROUP BY (o.node_id)) used ON n.id = used.node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $chunkId, PDO::PARAM_STR);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Node', array('id','url','used','total'));
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         return false;
      }
   }

   public function getChunkNode($chunkId) {
      try {
        $i = 0;
        $sql = "select node_id from operations where chunk_id = '".$chunkId."' and type = 'w';";
        foreach ($this->conn->query($sql) as $row) {
            $result = $row['chunk_id'];
            $i = $i + 1;
        }
         return $result;
      } catch (PDOException $e) {
         $stmt = NULL;
         return false;
      }
   }
   
   public function getChunkInNode($nodeId, $fileId) {
      try {
        $i = 0;
        $sql = "select chunk_id from operations where node_id = '".$nodeId."' and file_id = '".$fileId."' and type = 'w';";
        foreach ($this->conn->query($sql) as $row) {
            $result = $row['chunk_id'];
            $i = $i + 1;
        }
         return $result;
      } catch (PDOException $e) {
         $stmt = NULL;
         return false;
      }
   }
   
   /*------------- `operations` table method ------------------*/
   /**
    * Files uploaded by users to the nodes
    * @param  String $userId User id
    * @param  String $fileId File id
    * @param  String $nodeId Node id
    * @param  String $type   Type operation write(w) or read(r)
    * @return [type]         [description]
    */
   public function registerOperation($id, $userId, $fileId, $chunkId, $nodeId, $type) {
      try {
         $sql = 'INSERT INTO operations(id, user_id, file_id, chunk_id, node_id, type, status) VALUES(?, ?, ?, ?, ?, ?, \'0\');';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $id, PDO::PARAM_STR);
         $stmt->bindParam(2, $userId, PDO::PARAM_STR);
         $stmt->bindParam(3, $fileId, PDO::PARAM_STR);
         $stmt->bindParam(4, $chunkId, PDO::PARAM_STR);
         $stmt->bindParam(5, $nodeId, PDO::PARAM_INT);
         $stmt->bindParam(6, $type, PDO::PARAM_STR);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /**
    * [updateOperation description]
    * @param  [type] $id [description]
    * @return [type]     [description]
    */
   public function updateOperation($id) {
      try {
         $sql = 'UPDATE operations SET status = 1 WHERE id = ?;';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $id, PDO::PARAM_STR);
         $stmt->execute();
         $rows = $stmt->rowCount();
         $stmt = NULL;
         return $rows > 0;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   /*------------- `response_time` table method ------------------*/
   /**
    * [updateOperationTime description]
    * @param  [type] $operationId [description]
    * @param  [type] $time        [description]
    * @return [type]              [description]
    */
   public function registerResponseTime($id, $userId, $fileId, $nodeId, $time, $type) {
      try {
         $sql = 'INSERT INTO response_time(id, user_id, file_id, node_id, resp_time, type) VALUES(?, ?, ?, ?, ?, ?);';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $id, PDO::PARAM_STR);
         $stmt->bindParam(2, $userId, PDO::PARAM_STR);
         $stmt->bindParam(3, $fileId, PDO::PARAM_STR);
         $stmt->bindParam(4, $nodeId, PDO::PARAM_STR);
         $stmt->bindParam(5, $time, PDO::PARAM_STR);
         $stmt->bindParam(6, $type, PDO::PARAM_STR);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }


   /*------------- `service_time` table method ------------------*/
   
   public function registerServiceTime($id, $fileId, $nodeId, $operationId, $time, $type) {
      try {
         $sql = 'INSERT INTO service_time(id, file_id, node_id, operation_id, serv_time, type) VALUES(?, ?, ?, ?, ?, ?);';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $id, PDO::PARAM_STR);
         $stmt->bindParam(2, $fileId, PDO::PARAM_STR);
         $stmt->bindParam(3, $nodeId, PDO::PARAM_STR);
         $stmt->bindParam(4, $operationId, PDO::PARAM_STR);
         $stmt->bindParam(5, $time, PDO::PARAM_STR);
         $stmt->bindParam(6, $type, PDO::PARAM_STR);
         $stmt->execute();
         $stmt = NULL;
         return true;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getAllOperationsUploads() {
      try{
         $sql = 'SELECT DISTINCT file_id FROM operations WHERE type = \'w\';';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $operations;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getOperationsUploads($fileId) {
      try{
         $sql = 'SELECT COUNT(file_id) FROM operations WHERE type = \'w\' AND file_id = ?;';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $fileId, PDO::PARAM_STR);
         $stmt->execute();
         $operations = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $operations;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getOperationsDownloads($fileId, $nodeId) {
      try{
         $sql = 'SELECT file_id, node_id, user_id FROM operations WHERE type = \'r\' AND file_id = ? AND node_id = ? ;';
         $stmt = $this->conn->prepare($sql);
         $stmt->bindParam(1, $fileId, PDO::PARAM_STR);
         $stmt->bindParam(2, $nodeId, PDO::PARAM_STR);
         $stmt->execute();
         $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $topics;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

    public function getAllOperationsDownloads() {
      try{
         $sql = 'SELECT cube.node_id, cube.file_id, COUNT(cube.type) AS count, SUM(cube.size) AS mb FROM (SELECT o.file_id, o.node_id, f.size, o.type FROM operations o INNER JOIN nodes n ON o.node_id = n.id INNER JOIN files f ON o.file_id = f.id WHERE o.type = \'r\') cube GROUP BY cube.file_id, cube.node_id ORDER BY cube.node_id ASC;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $topics;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getSumSizeG() {
      try{
         $sql = 'SELECT rt.node_id, SUM(f.size) FROM response_time rt INNER JOIN files f ON rt.file_id = f.id GROUP BY rt.node_id ORDER BY rt.node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         return false;
      }
   }

   public function getSumRespTimeG() {
      try{
         $sql = 'SELECT node_id, SUM(resp_time) FROM response_time GROUP BY node_id ORDER BY node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getAvgRespTimeG() {
      try{
         $sql = 'SELECT node_id, AVG(resp_time) FROM response_time GROUP BY node_id ORDER BY node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getSumSizeW() {
      try{
         $sql = 'SELECT rt.node_id, SUM(f.size) FROM response_time rt INNER JOIN files f ON rt.file_id = f.id WHERE rt.type = \'w\' GROUP BY rt.node_id ORDER BY rt.node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getSumRespTimeW() {
      try{
         $sql = 'SELECT node_id, SUM(resp_time) FROM response_time WHERE type = \'w\' GROUP BY node_id ORDER BY node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getAvgRespTimeW() {
      try{
         $sql = 'SELECT node_id, AVG(resp_time) FROM response_time WHERE type = \'w\' GROUP BY node_id ORDER BY node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getSumSizeR() {
      try{
         $sql = 'SELECT rt.node_id, SUM(f.size) FROM response_time rt INNER JOIN files f ON rt.file_id = f.id WHERE rt.type = \'r\' GROUP BY rt.node_id ORDER BY rt.node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getSumRespTimeR() {
      try{
         $sql = 'SELECT node_id, SUM(resp_time) FROM response_time WHERE type = \'r\' GROUP BY node_id ORDER BY node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }

   public function getAvgRespTimeR() {
      try{
         $sql = 'SELECT node_id, AVG(resp_time) FROM response_time WHERE type = \'r\' GROUP BY node_id ORDER BY node_id;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }


   public function getBalancingTopics() {
      try{
         $sql = 'SELECT SUM(f.size) AS file, t.id AS topic, n.id AS node FROM files f INNER JOIN files_topic ft ON f.id = ft.file_id INNER JOIN topics t ON t.id = ft.topic_id INNER JOIN operations op ON op.file_id = ft.file_id INNER JOIN nodes n ON n.id = op.node_id WHERE op.type = \'w\' GROUP BY topic,node ORDER BY topic;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }


  public function getBalancingTopK() {
      try{
         $sql = 'SELECT SUM(f.size) AS file, t.class AS class, n.id AS node FROM files f INNER JOIN files_topic ft ON f.id = ft.file_id INNER JOIN topics t ON t.id = ft.topic_id INNER JOIN operations op ON op.file_id = ft.file_id INNER JOIN nodes n ON n.id = op.node_id WHERE op.type = \'w\' GROUP BY class,node ORDER BY class;';
         $stmt = $this->conn->prepare($sql);
         $stmt->execute();
         $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $stmt = NULL;
         return $nodes;
      } catch (PDOException $e) {
         $stmt = NULL;
         $this->log->lwrite($e->getMessage());
         return false;
      }
   }   

}
