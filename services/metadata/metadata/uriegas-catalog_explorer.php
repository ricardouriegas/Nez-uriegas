<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Catalog Content Explorer - Shows what files and metadata are in a catalog

class CatalogExplorer {
    private $pubSubDb;
    private $metadataDb;
    
    public function __construct() {
        try {
            $this->pubSubDb = $this->getPubSubConnection();
            $this->metadataDb = $this->getMetadataConnection();
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function getPubSubConnection() {
        $host = 'db_pub_sub';
        $dbname = 'pub_sub';
        $username = 'muyalmanager';
        $password = 'sicuhowradRaxi5R2ke6';
        
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
    
    private function getMetadataConnection() {
        $host = 'db_metadata';
        $dbname = 'multi';
        $username = 'muyalmanager';
        $password = 'f0l34lraSoTRumoGitRo';
        
        $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
    
    public function getCatalogContents($catalogToken, $userId = null) {
        try {
            // Get catalog basic information
            $catalogInfo = $this->getCatalogInfo($catalogToken, $userId);
            
            // Get files in the catalog
            $files = $this->getCatalogFiles($catalogToken);
            
            // Get file details from metadata
            $fileDetails = $this->getFileDetails($files);
            
            // Get subcatalogs (children)
            $subcatalogs = $this->getSubcatalogs($catalogToken);
            
            // Calculate totals
            $totals = $this->calculateTotals($fileDetails);
            
            return [
                'catalog_info' => $catalogInfo,
                'files' => $fileDetails,
                'subcatalogs' => $subcatalogs,
                'summary' => [
                    'total_files' => count($fileDetails),
                    'total_subcatalogs' => count($subcatalogs),
                    'total_size_bytes' => $totals['size'],
                    'total_size_formatted' => $this->formatBytes($totals['size']),
                    'total_chunks' => $totals['chunks'],
                    'encrypted_files' => $totals['encrypted'],
                    'file_types' => $totals['types']
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception("Failed to get catalog contents: " . $e->getMessage());
        }
    }
    
    private function getCatalogInfo($catalogToken, $userId = null) {
        $query = "SELECT c.keycatalog, c.tokencatalog, c.namecatalog, c.created_at,
                         c.token_user, c.dispersemode, c.encryption, c.isprivate,
                         c.father, c.\"group\", c.processed
                  FROM catalogs c
                  WHERE c.tokencatalog = :catalogToken";
        
        // Add access control if user provided
        if ($userId) {
            $query .= " AND (c.isprivate = false OR c.token_user = :userId 
                           OR EXISTS (SELECT 1 FROM users_catalogs uc 
                                     WHERE uc.tokencatalog = c.tokencatalog 
                                     AND uc.token_user = :userId))";
        } else {
            $query .= " AND c.isprivate = false";
        }
        
        $stmt = $this->pubSubDb->prepare($query);
        $stmt->bindValue(':catalogToken', $catalogToken);
        if ($userId) {
            $stmt->bindValue(':userId', $userId);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new Exception("Catalog not found or access denied");
        }
        
        return $result;
    }
    
    private function getCatalogFiles($catalogToken) {
        $query = "SELECT cf.token_file, cf.status
                  FROM catalogs_files cf
                  WHERE cf.tokencatalog = :catalogToken
                  ORDER BY cf.token_file";
        
        $stmt = $this->pubSubDb->prepare($query);
        $stmt->bindValue(':catalogToken', $catalogToken);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getFileDetails($files) {
        if (empty($files)) {
            return [];
        }
        
        $fileTokens = array_column($files, 'token_file');
        $placeholders = str_repeat('?,', count($fileTokens) - 1) . '?';
        
        $query = "SELECT keyfile, namefile, sizefile, chunks, isciphered, 
                         hashfile, created_at
                  FROM files
                  WHERE keyfile IN ($placeholders)
                  ORDER BY namefile";
        
        $stmt = $this->metadataDb->prepare($query);
        $stmt->execute($fileTokens);
        $fileDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge with catalog file status
        $fileStatusMap = [];
        foreach ($files as $file) {
            $fileStatusMap[$file['token_file']] = $file['status'];
        }
        
        foreach ($fileDetails as &$file) {
            $file['catalog_status'] = $fileStatusMap[$file['keyfile']] ?? 'unknown';
            $file['size_formatted'] = $this->formatBytes($file['sizefile']);
            $file['file_extension'] = pathinfo($file['namefile'], PATHINFO_EXTENSION);
        }
        
        return $fileDetails;
    }
    
    private function getSubcatalogs($catalogToken) {
        $query = "SELECT c.keycatalog, c.tokencatalog, c.namecatalog, c.created_at,
                         c.dispersemode, c.encryption, c.isprivate, c.processed,
                         COUNT(cf.token_file) as file_count
                  FROM catalogs c
                  LEFT JOIN catalogs_files cf ON c.tokencatalog = cf.tokencatalog
                  WHERE c.father = :catalogToken
                  GROUP BY c.keycatalog, c.tokencatalog, c.namecatalog, c.created_at,
                           c.dispersemode, c.encryption, c.isprivate, c.processed
                  ORDER BY c.namecatalog";
        
        $stmt = $this->pubSubDb->prepare($query);
        $stmt->bindValue(':catalogToken', $catalogToken);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function calculateTotals($files) {
        $totals = [
            'size' => 0,
            'chunks' => 0,
            'encrypted' => 0,
            'types' => []
        ];
        
        foreach ($files as $file) {
            $totals['size'] += $file['sizefile'] ?? 0;
            $totals['chunks'] += $file['chunks'] ?? 0;
            
            if ($file['isciphered']) {
                $totals['encrypted']++;
            }
            
            $ext = strtolower($file['file_extension'] ?? 'unknown');
            $totals['types'][$ext] = ($totals['types'][$ext] ?? 0) + 1;
        }
        
        return $totals;
    }
    
    private function formatBytes($size) {
        if ($size == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($size) - 1) / 3);
        
        return sprintf("%.2f %s", $size / pow(1024, $factor), $units[$factor]);
    }
}

// API endpoint
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $catalogToken = $_GET['catalog_token'] ?? '';
    $userId = $_GET['user_id'] ?? null;
    
    if (empty($catalogToken)) {
        echo json_encode(['error' => 'catalog_token parameter is required']);
        exit;
    }
    
    try {
        $explorer = new CatalogExplorer();
        $contents = $explorer->getCatalogContents($catalogToken, $userId);
        
        echo json_encode([
            'success' => true,
            'catalog_token' => $catalogToken,
            'contents' => $contents,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'catalog_token' => $catalogToken
        ]);
    }
} else {
    echo json_encode(['error' => 'Only GET method allowed']);
}
?>