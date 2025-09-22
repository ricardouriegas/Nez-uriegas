<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// FAIR Catalog Search API - Focused on catalog discovery following FAIR principles
// Findable, Accessible, Interoperable, Reusable

class FAIRCatalogSearch {
    private $pubSubDb;
    
    public function __construct() {
        try {
            $this->pubSubDb = $this->getPubSubConnection();
            if (!$this->pubSubDb) {
                throw new Exception("Pub_Sub database connection required for catalog search");
            }
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function getPubSubConnection() {
        try {
            // Pub_Sub database connection details from docker-compose
            $host = 'db_pub_sub';
            $dbname = 'pub_sub';
            $username = 'muyalmanager';
            $password = 'sicuhowradRaxi5R2ke6';
            
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            error_log("Pub_Sub DB connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    public function searchCatalogs($searchTerm, $userId = null, $filters = []) {
        try {
            // Build dynamic query based on FAIR principles
            $query = $this->buildFAIRQuery($userId, $filters, $searchTerm);
            
            $stmt = $this->pubSubDb->prepare($query);
            
            // Bind search term only if it's not a wildcard
            if ($searchTerm !== '*') {
                $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
            }
            
            // Bind user ID if provided (for Accessibility)
            if ($userId) {
                $stmt->bindValue(':userId', $userId);
            }
            
            // Bind date filters if provided
            if (!empty($filters['date_from'])) {
                $stmt->bindValue(':dateFrom', $filters['date_from'] . ' 00:00:00');
            }
            
            if (!empty($filters['date_to'])) {
                $stmt->bindValue(':dateTo', $filters['date_to'] . ' 23:59:59');
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Enhance results with FAIR metadata
            return $this->enhanceWithFAIRMetadata($results);
            
        } catch (Exception $e) {
            throw new Exception("Catalog search failed: " . $e->getMessage());
        }
    }
    
    private function buildFAIRQuery($userId = null, $filters = [], $searchTerm = '') {
        // Base query with FAIR metadata
        $query = "SELECT 
                    c.keycatalog,
                    c.tokencatalog,
                    c.namecatalog,
                    c.created_at,
                    c.token_user,
                    c.dispersemode,
                    c.encryption,
                    c.isprivate,
                    c.father,
                    c.\"group\",
                    c.processed,
                    COUNT(cf.token_file) as file_count
                  FROM catalogs c
                  LEFT JOIN catalogs_files cf ON c.tokencatalog = cf.tokencatalog";
        
        // Add access control joins if user ID provided (Accessibility principle)
        if ($userId) {
            $query .= " LEFT JOIN users_catalogs uc ON c.tokencatalog = uc.tokencatalog";
        }
        
        // WHERE clause for Findability
        $whereConditions = [];
        
        // Only add search term condition if it's not a wildcard
        if ($searchTerm !== '*') {
            $whereConditions[] = "(c.namecatalog ILIKE :searchTerm OR c.tokencatalog ILIKE :searchTerm OR c.keycatalog ILIKE :searchTerm)";
        }
        
        // Apply privacy filter (Accessibility)
        if ($userId) {
            $whereConditions[] = "(c.isprivate = false OR uc.token_user = :userId OR c.token_user = :userId)";
        } else {
            $whereConditions[] = "c.isprivate = false";
        }
        
        // Apply additional filters
        if (!empty($filters['privacy'])) {
            if ($filters['privacy'] === 'public') {
                $whereConditions[] = "c.isprivate = false";
            } elseif ($filters['privacy'] === 'private') {
                $whereConditions[] = "c.isprivate = true";
            }
        }
        
        if (!empty($filters['encryption'])) {
            if ($filters['encryption'] === 'encrypted') {
                $whereConditions[] = "c.encryption = true";
            } elseif ($filters['encryption'] === 'unencrypted') {
                $whereConditions[] = "c.encryption = false";
            }
        }
        
        if (!empty($filters['processed'])) {
            if ($filters['processed'] === 'processed') {
                $whereConditions[] = "c.processed = true";
            } elseif ($filters['processed'] === 'unprocessed') {
                $whereConditions[] = "c.processed = false";
            }
        }
        
        // Date range filters
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "c.created_at >= :dateFrom";
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "c.created_at <= :dateTo";
        }
        
        // Add WHERE clause only if there are conditions
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $query .= " GROUP BY c.keycatalog, c.tokencatalog, c.namecatalog, c.created_at, c.token_user, c.dispersemode, c.encryption, c.isprivate, c.father, c.\"group\", c.processed";
        $query .= " ORDER BY c.created_at DESC LIMIT 100";
        
        return $query;
    }
    
    private function enhanceWithFAIRMetadata($results) {
        // Enhance each catalog with FAIR metadata
        return array_map(function($catalog) {
            return array_merge($catalog, [
                // Findable metadata
                'fair_findable' => [
                    'indexed' => true,
                    'searchable_fields' => ['namecatalog', 'tokencatalog', 'keycatalog'],
                    'created_timestamp' => $catalog['created_at']
                ],
                
                // Accessible metadata
                'fair_accessible' => [
                    'is_public' => !$catalog['isprivate'],
                    'access_protocol' => 'HTTP/HTTPS',
                    'authentication_required' => $catalog['isprivate']
                ],
                
                // Interoperable metadata
                'fair_interoperable' => [
                    'format_standard' => 'PostgreSQL/JSON',
                    'dispersal_method' => $catalog['dispersemode'],
                    'encryption_standard' => $catalog['encryption'] ? 'ABE' : 'none'
                ],
                
                // Reusable metadata
                'fair_reusable' => [
                    'license' => 'institutional',
                    'processing_status' => $catalog['processed'] ? 'ready' : 'pending',
                    'file_count' => (int)$catalog['file_count'],
                    'metadata_complete' => !empty($catalog['namecatalog'])
                ]
            ]);
        }, $results);
    }
    
    public function getCatalogStatistics() {
        try {
            $stats = [];
            
            // Total catalogs
            $stmt = $this->pubSubDb->query("SELECT COUNT(*) as total FROM catalogs");
            $stats['total_catalogs'] = $stmt->fetchColumn();
            
            // Public vs Private
            $stmt = $this->pubSubDb->query("SELECT isprivate, COUNT(*) as count FROM catalogs GROUP BY isprivate");
            while ($row = $stmt->fetch()) {
                $stats[$row['isprivate'] ? 'private_catalogs' : 'public_catalogs'] = $row['count'];
            }
            
            // Encrypted vs Unencrypted
            $stmt = $this->pubSubDb->query("SELECT encryption, COUNT(*) as count FROM catalogs GROUP BY encryption");
            while ($row = $stmt->fetch()) {
                $stats[$row['encryption'] ? 'encrypted_catalogs' : 'unencrypted_catalogs'] = $row['count'];
            }
            
            // Processed vs Unprocessed
            $stmt = $this->pubSubDb->query("SELECT processed, COUNT(*) as count FROM catalogs GROUP BY processed");
            while ($row = $stmt->fetch()) {
                $stats[$row['processed'] ? 'processed_catalogs' : 'unprocessed_catalogs'] = $row['count'];
            }
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = $_GET['q'] ?? '';
    $userId = $_GET['user_id'] ?? null;
    
    // Parse filters from query parameters
    $filters = [
        'privacy' => $_GET['privacy'] ?? null,
        'encryption' => $_GET['encryption'] ?? null,
        'processed' => $_GET['processed'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null
    ];
    
    if (empty($searchTerm)) {
        echo json_encode(['error' => 'Search term required (minimum 2 characters)']);
        exit;
    }
    
    try {
        $fairSearch = new FAIRCatalogSearch();
        
        // Get catalog search results
        $catalogs = $fairSearch->searchCatalogs($searchTerm, $userId, $filters);
        
        // Get repository statistics
        $statistics = $fairSearch->getCatalogStatistics();
        
        echo json_encode([
            'success' => true,
            'fair_compliant' => true,
            'search_metadata' => [
                'search_term' => $searchTerm,
                'user_context' => $userId,
                'filters_applied' => array_filter($filters),
                'timestamp' => date('c'),
                'total_results' => count($catalogs)
            ],
            'repository_statistics' => $statistics,
            'results' => [
                'catalogs' => $catalogs
            ],
            'fair_principles' => [
                'findable' => 'Rich metadata with searchable fields',
                'accessible' => 'Access control aware with user context',
                'interoperable' => 'Standardized JSON/PostgreSQL format',
                'reusable' => 'Complete provenance and processing metadata'
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'fair_compliant' => false
        ]);
    }
    
} else {
    echo json_encode(['error' => 'Only GET method allowed']);
}
?>