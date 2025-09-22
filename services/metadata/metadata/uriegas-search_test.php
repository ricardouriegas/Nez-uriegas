<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Include database connections
require_once 'models/db/Connection.php';

class UnifiedSearch {
    private $metadataDb;
    private $pubSubDb;
    
    public function __construct() {
        try {
            // Metadata database connection
            $metadataConnection = new Connection();
            $this->metadataDb = $metadataConnection->getConnection();
            
            // Pub_Sub database connection (assuming similar structure)
            $this->pubSubDb = $this->getPubSubConnection();
            
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function getPubSubConnection() {
        try {
            // Pub_Sub database connection details from docker-compose
            $host = 'db_pub_sub'; // Container name
            $dbname = 'pub_sub'; // Database name from docker-compose
            $username = 'muyalmanager';
            $password = 'sicuhowradRaxi5R2ke6'; // Updated password from docker-compose
            
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            // If pub_sub DB is not accessible, return null and skip pub_sub searches
            error_log("Pub_Sub DB connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    public function search($searchTerm, $userId = null) {
        $results = [
            'files' => [],
            'chunks' => [],
            'nodes' => [],
            'catalogs' => [],
            'groups' => [],
            'operations' => []
        ];
        
        try {
            // Search in Metadata Database
            $results['files'] = $this->searchFiles($searchTerm);
            $results['chunks'] = $this->searchChunks($searchTerm);
            $results['nodes'] = $this->searchNodes($searchTerm);
            $results['operations'] = $this->searchOperations($searchTerm);
            
            // Search in Pub_Sub Database (only if connection available)
            if ($this->pubSubDb) {
                $results['catalogs'] = $this->searchCatalogs($searchTerm, $userId);
                $results['groups'] = $this->searchGroups($searchTerm, $userId);
            }
            
        } catch (Exception $e) {
            throw new Exception("Search failed: " . $e->getMessage());
        }
        
        return $results;
    }
    
    private function searchFiles($searchTerm) {
        $query = "SELECT keyfile, namefile, sizefile, chunks, isciphered, hashfile, created_at 
                  FROM files 
                  WHERE namefile ILIKE :searchTerm 
                     OR keyfile ILIKE :searchTerm 
                     OR hashfile ILIKE :searchTerm
                  ORDER BY created_at DESC 
                  LIMIT 20";
        
        $stmt = $this->metadataDb->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function searchChunks($searchTerm) {
        $query = "SELECT c.id, c.name, c.size, c.status, c.created_at,
                         f.namefile, f.keyfile
                  FROM chunks c
                  LEFT JOIN chunks_file cf ON c.id = cf.chunk_id
                  LEFT JOIN files f ON cf.file_id = f.keyfile
                  WHERE c.name ILIKE :searchTerm 
                     OR c.id ILIKE :searchTerm
                  ORDER BY c.created_at DESC 
                  LIMIT 15";
        
        $stmt = $this->metadataDb->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function searchNodes($searchTerm) {
        $query = "SELECT id, url, capacity, memory, status, created_at
                  FROM nodes 
                  WHERE url ILIKE :searchTerm 
                     OR CAST(id AS TEXT) ILIKE :searchTerm
                  ORDER BY created_at DESC 
                  LIMIT 10";
        
        $stmt = $this->metadataDb->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function searchOperations($searchTerm) {
        $query = "SELECT o.id, o.user_id, o.file_id, o.chunk_id, o.node_id, 
                         o.type, o.status, o.created_at, f.namefile
                  FROM operations o
                  LEFT JOIN files f ON o.file_id = f.keyfile
                  WHERE o.user_id ILIKE :searchTerm 
                     OR o.file_id ILIKE :searchTerm 
                     OR o.id ILIKE :searchTerm
                  ORDER BY o.created_at DESC 
                  LIMIT 15";
        
        $stmt = $this->metadataDb->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function searchCatalogs($searchTerm, $userId = null) {
        if (!$this->pubSubDb) {
            return [];
        }
        
        $query = "SELECT c.keycatalog, c.tokencatalog, c.namecatalog, c.created_at,
                         c.token_user, c.dispersemode, c.encryption, c.isprivate,
                         c.father, c.\"group\", c.processed
                  FROM catalogs c";
        
        if ($userId) {
            $query .= " LEFT JOIN users_catalogs uc ON c.tokencatalog = uc.tokencatalog
                       WHERE (c.namecatalog ILIKE :searchTerm 
                          OR c.tokencatalog ILIKE :searchTerm 
                          OR c.keycatalog ILIKE :searchTerm)
                         AND (c.isprivate = false OR uc.token_user = :userId OR c.token_user = :userId)";
        } else {
            $query .= " WHERE (c.namecatalog ILIKE :searchTerm 
                          OR c.tokencatalog ILIKE :searchTerm 
                          OR c.keycatalog ILIKE :searchTerm)
                         AND c.isprivate = false";
        }
        
        $query .= " ORDER BY c.created_at DESC LIMIT 20";
        
        $stmt = $this->pubSubDb->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        if ($userId) {
            $stmt->bindValue(':userId', $userId);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function searchGroups($searchTerm, $userId = null) {
        if (!$this->pubSubDb) {
            return [];
        }
        
        $query = "SELECT g.keygroup, g.tokengroup, g.namegroup, g.created_at,
                         g.token_user, g.isprivate, g.father
                  FROM groups g";
        
        if ($userId) {
            $query .= " LEFT JOIN users_groups ug ON g.tokengroup = ug.tokengroup
                       WHERE (g.namegroup ILIKE :searchTerm 
                          OR g.tokengroup ILIKE :searchTerm 
                          OR g.keygroup ILIKE :searchTerm)
                         AND (g.isprivate = false OR ug.token_user = :userId OR g.token_user = :userId)";
        } else {
            $query .= " WHERE (g.namegroup ILIKE :searchTerm 
                          OR g.tokengroup ILIKE :searchTerm 
                          OR g.keygroup ILIKE :searchTerm)
                         AND g.isprivate = false";
        }
        
        $query .= " ORDER BY g.created_at DESC LIMIT 20";
        
        $stmt = $this->pubSubDb->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        if ($userId) {
            $stmt->bindValue(':userId', $userId);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = $_GET['q'] ?? '';
    $userId = $_GET['user_id'] ?? null;
    
    if (empty($searchTerm)) {
        echo json_encode(['error' => 'Search term required']);
        exit;
    }
    
    try {
        $search = new UnifiedSearch();
        $results = $search->search($searchTerm, $userId);
        
        // Calculate total count
        $totalCount = 0;
        foreach ($results as $category => $items) {
            $totalCount += count($items);
        }
        
        echo json_encode([
            'success' => true,
            'search_term' => $searchTerm,
            'user_id' => $userId,
            'total_count' => $totalCount,
            'results' => $results
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode(['error' => 'Only GET method allowed']);
}
?>