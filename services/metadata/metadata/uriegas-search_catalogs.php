<?php
// TODO:
// should be able of searching with the following things:
    // propietario, 
    // tipo de datos  
    // fechas de creacion
    // nombre de usuario propietario

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Include necessary files
require_once dirname(__FILE__) . '/Connection.php';
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Log.php';

/**
 * SQLMap-powered SQL injection protection
 * Every input is validated directly with SQLMap
 */
function validateSQLInput($input, $paramName = 'input') {
    if (empty($input)) return '';
    
    // Clean and prepare input
    $cleanInput = trim($input);
    
    // Validate every input with SQLMap
    if (validateWithSQLMap($cleanInput, $paramName)) {
        logSQLMapDetection($paramName, $cleanInput, 'sqlmap_detected');
        throw new Exception("SQL injection detected by SQLMap");
    }
    
    return $cleanInput;
}

/**
 * Use SQLMap to validate all input for SQL injection
 */
function validateWithSQLMap($input, $paramName) {
    // Create a test URL with the input
    $testUrl = "http://localhost:20505/uriegas-search_catalogs.php?" . urlencode($paramName) . "=" . urlencode($input);
    
    // Add required parameters to make request valid
    if ($paramName !== 'q') {
        $testUrl .= "&q=test";
    }
    
    // Temporary file for SQLMap output
    $outputFile = "/tmp/sqlmap_validation_" . uniqid() . ".txt";
    
    // SQLMap command using local installation - optimized for speed
    //! TODO: make a script or add to the installation script the location of sqlmap
    $sqlmapCmd = "timeout 15 ~/.local/bin/sqlmap -u " . escapeshellarg($testUrl) . " " .
                 "--batch --level=1 --risk=1 --threads=1 --timeout=5 " .
                 "--technique=B --no-cast --flush-session " .
                 "--answers='crack=N,dict=N,continue=Y' --disable-coloring " .
                 "> " . escapeshellarg($outputFile) . " 2>&1";
    
    // Execute SQLMap with timeout
    exec($sqlmapCmd, $output, $returnCode);
    
    // Read SQLMap output
    $sqlmapOutput = '';
    if (file_exists($outputFile)) {
        $sqlmapOutput = file_get_contents($outputFile);
        unlink($outputFile); // Clean up
    }
    
    // Check SQLMap results - look for actual vulnerabilities found
    $isVulnerable = (
        strpos($sqlmapOutput, 'parameter.*is vulnerable') !== false ||
        strpos($sqlmapOutput, 'sqlmap identified the following injection point') !== false ||
        strpos($sqlmapOutput, 'Type: boolean-based') !== false ||
        strpos($sqlmapOutput, 'Type: error-based') !== false ||
        strpos($sqlmapOutput, 'Type: time-based') !== false ||
        strpos($sqlmapOutput, 'Type: UNION query') !== false
    );
    
    // SQLMap reporting "not injectable" or "not vulnerable" means input is SAFE
    if (strpos($sqlmapOutput, 'does not seem to be injectable') !== false ||
        strpos($sqlmapOutput, 'not appear to be injectable') !== false) {
        $isVulnerable = false;
    }
    
    // Log SQLMap analysis for monitoring
    logSQLMapAnalysis($paramName, $input, substr($sqlmapOutput, 0, 300), $isVulnerable);
    
    return $isVulnerable;
}

/**
 * Log SQL injection detection events
 */
function logSQLMapDetection($paramName, $input, $detectionMethod) {
    $logEntry = [
        'timestamp' => date('c'),
        'event' => 'sql_injection_detected',
        'parameter' => $paramName,
        'input' => substr($input, 0, 200), // Limit log size
        'detection_method' => $detectionMethod,
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ];
    
    // Try multiple log locations
    $logLocations = [
        '/tmp/sqlmap_security.log',
        dirname(__FILE__) . '/sqlmap_security.log',
        '/var/log/sqlmap_security.log'
    ];
    
    foreach ($logLocations as $logFile) {
        if (@file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX)) {
            break; // Successfully logged
        }
    }
}

/**
 * Log SQLMap analysis results
 */
function logSQLMapAnalysis($paramName, $input, $sqlmapOutput, $isVulnerable) {
    $logEntry = [
        'timestamp' => date('c'),
        'event' => 'sqlmap_analysis',
        'parameter' => $paramName,
        'input' => substr($input, 0, 100),
        'vulnerable' => $isVulnerable,
        'sqlmap_output_snippet' => substr($sqlmapOutput, 0, 500),
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // Try multiple log locations
    $logLocations = [
        '/tmp/sqlmap_analysis.log',
        dirname(__FILE__) . '/sqlmap_analysis.log',
        '/var/log/sqlmap_analysis.log'
    ];
    
    foreach ($logLocations as $logFile) {
        if (@file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX)) {
            break; // Successfully logged
        }
    }
}

// Findable, Accessible, Interoperable, Reusable

class FAIRCatalogSearch {
    private $pubSubDb;
    private $metadataDb;
    private $authDb;
    private $log;
    
    // functions to connect to all databases 
    public function __construct() {
        try {
            $this->log = new Log();
            
            // Use the standard Connection class for metadata DB (current service)
            $metadataConnection = new Connection();
            $this->metadataDb = $metadataConnection->getConnection();
            
            // Connect to other databases using environment variables
            $this->pubSubDb = $this->getPubSubConnection();
            $this->authDb = $this->getAuthConnection();
            
            if (!$this->pubSubDb) {
                throw new Exception("Pub_Sub database connection required for catalog search");
            }
            if (!$this->metadataDb) {
                throw new Exception("Metadata database connection required for file type search");
            }
            // Auth database is optional - only warn if not available
            if (!$this->authDb) {
                $this->log->lwrite("Warning: Auth database connection failed - username search will be disabled");
            }
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function getPubSubConnection() {
        try {
            // Pub_Sub database connection using constants from Config.php
            $host = 'db_pub_sub';
            $dbname = 'pub_sub';
            $username = DB_USER; // Same username for all databases
            $password = PUB_SUB_DB_PASSWORD; // Specific password from environment
            
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            $this->log->lwrite("Pub_Sub DB connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    // Note: Metadata connection is now handled in constructor using the standard Connection class
    
    private function getAuthConnection() {
        try {
            // Auth database connection using constants from Config.php
            $host = 'db_auth';
            $dbname = 'auth';
            $username = DB_USER; // Same username for all databases
            $password = AUTH_DB_PASSWORD; // Specific password from environment
            
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Test the connection by running a simple query
            $stmt = $pdo->query("SELECT 1");
            if ($stmt) {
                $this->log->lwrite("Auth DB connection successful");
                return $pdo;
            }
        } catch (PDOException $e) {
            $this->log->lwrite("Auth DB connection failed: " . $e->getMessage());
            return null;
        }
    }
    
    private function getUserTokensByUsername($username) {
        try {
            // Check if auth database is available
            if (!$this->authDb) {
                $this->log->lwrite("Warning: Auth database not available - username search skipped");
                return [];
            }
            
            // Sanitize and validate username
            $username = trim($username);
            if (empty($username)) {
                return [];
            }
            
            // Get user tokens that match the username (exact match)
            $query = "SELECT tokenuser FROM users WHERE username = :username";
            $stmt = $this->authDb->prepare($query);
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $this->log->lwrite("Username search failed: " . $e->getMessage());
            return []; // Return empty array instead of throwing exception
        }
    }
    
    public function getAllAvailableUsernames() {
        try {
            // Check if auth database is available
            if (!$this->authDb) {
                $this->log->lwrite("Warning: Auth database not available - cannot get usernames list");
                return [];
            }
            
            // Get all unique usernames from users table
            $query = "SELECT DISTINCT username FROM users WHERE username IS NOT NULL AND username != '' ORDER BY username";
            $stmt = $this->authDb->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $this->log->lwrite("Get all usernames failed: " . $e->getMessage());
            return []; // Return empty array instead of throwing exception
        }
    }
    
    private function getUsernamesByTokens($userTokens) {
        try {
            // Check if auth database is available
            if (!$this->authDb || empty($userTokens)) {
                return [];
            }
            
            // Create placeholders for the IN clause
            $placeholders = str_repeat('?,', count($userTokens) - 1) . '?';
            $query = "SELECT tokenuser, username FROM users WHERE tokenuser IN ($placeholders)";
            $stmt = $this->authDb->prepare($query);
            $stmt->execute($userTokens);
            
            // Return associative array with tokenuser as key and username as value
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $usernameMap = [];
            foreach ($results as $row) {
                $usernameMap[$row['tokenuser']] = $row['username'];
            }
            
            return $usernameMap;
        } catch (Exception $e) {
            $this->log->lwrite("Getting usernames by tokens failed: " . $e->getMessage());
            return [];
        }
    }
    
    public function searchCatalogs($searchTerm, $filters = []) {
        try {
            // Handle username filter separately due to cross-database query
            $catalogsWithUsername = [];
            if (!empty($filters['username'])) {
                $userTokens = $this->getUserTokensByUsername($filters['username']);
                if (empty($userTokens)) {
                    // No users found with this username
                    return [];
                }
                // Remove username from filters for main query
                $tempFilters = $filters;
                unset($tempFilters['username']);
                $filters = $tempFilters;
                $catalogsWithUsername = $userTokens;
            }
            
            // Handle file type filter separately due to cross-database query
            $catalogsWithFileType = [];
            if (!empty($filters['file_type'])) {
                $catalogsWithFileType = $this->getCatalogsWithFileType($filters['file_type']);
                if (empty($catalogsWithFileType)) {
                    // No catalogs have this file type
                    return [];
                }
                // Remove file_type from filters for main query
                $tempFilters = $filters;
                unset($tempFilters['file_type']);
                $filters = $tempFilters;
            }
            
            // Build dynamic query based on FAIR principles
            $query = $this->buildFAIRQuery($filters, $searchTerm);
            
            $stmt = $this->pubSubDb->prepare($query);
            
            // Bind search term only if it's not a wildcard
            if ($searchTerm !== '*') {
                $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
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
            
            // Filter by username if needed
            if (!empty($catalogsWithUsername)) {
                $results = array_filter($results, function($catalog) use ($catalogsWithUsername) {
                    return in_array($catalog['token_user'], $catalogsWithUsername);
                });
                // Re-index array to ensure sequential keys (prevents object in JSON)
                $results = array_values($results);
            }
            
            // Filter by file type if needed
            if (!empty($catalogsWithFileType)) {
                $results = array_filter($results, function($catalog) use ($catalogsWithFileType) {
                    return in_array($catalog['tokencatalog'], $catalogsWithFileType);
                });
                // Re-index array to ensure sequential keys (prevents object in JSON)
                $results = array_values($results);
            }
            
            // Enhance results with FAIR metadata
            return $this->enhanceWithFAIRMetadata($results);
            
        } catch (Exception $e) {
            throw new Exception("Catalog search failed: " . $e->getMessage());
        }
    }
    
    private function getCatalogsWithFileType($fileType) {
        try {
            // Sanitize and validate file type
            $fileType = trim(strtolower($fileType));
            if (empty($fileType)) {
                return [];
            }
            
            // Remove leading dot if present
            $fileType = ltrim($fileType, '.');
            
            // Validate file extension (only alphanumeric characters)
            if (!preg_match('/^[a-z0-9]+$/', $fileType)) {
                throw new Exception("Invalid file type format");
            }
            
            // Get file tokens that match the file type
            // Search for files ending with .extension
            $query = "SELECT DISTINCT keyfile FROM files 
                      WHERE LOWER(namefile) LIKE :fileType";
            $stmt = $this->metadataDb->prepare($query);
            $stmt->bindValue(':fileType', '%.' . $fileType);
            $stmt->execute();
            $fileTokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($fileTokens)) {
                return [];
            }
            
            // Get catalogs that contain these files
            $placeholders = str_repeat('?,', count($fileTokens) - 1) . '?';
            $query = "SELECT DISTINCT tokencatalog FROM catalogs_files 
                      WHERE token_file IN ($placeholders)";
            $stmt = $this->pubSubDb->prepare($query);
            $stmt->execute($fileTokens);
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            throw new Exception("File type search failed: " . $e->getMessage());
        }
    }
    
    private function buildFAIRQuery($filters = [], $searchTerm = '') {
        // Base query with FAIR metadata
        $query = "SELECT DISTINCT
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
                    COUNT(DISTINCT cf.token_file) as file_count
                  FROM catalogs c
                  LEFT JOIN catalogs_files cf ON c.tokencatalog = cf.tokencatalog";
        
        // Add metadata joins for file type search
        $needsMetadataJoin = !empty($filters['file_type']);
        if ($needsMetadataJoin) {
            // Note: This requires a connection to the metadata database
            // We'll handle this in a subquery to avoid cross-database joins
        }
        

        
        // WHERE clause for Findability
        $whereConditions = [];
        
        // Only add search term condition if it's not a wildcard
        if ($searchTerm !== '*') {
            $whereConditions[] = "(c.namecatalog ILIKE :searchTerm OR c.tokencatalog ILIKE :searchTerm OR c.keycatalog ILIKE :searchTerm)";
        }
        
        // Apply privacy filter (Accessibility) - only show public catalogs for security
        $whereConditions[] = "c.isprivate = false";
        
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
        // Get usernames for all unique token_users in the results
        $userTokens = array_unique(array_column($results, 'token_user'));
        $usernameMap = $this->getUsernamesByTokens($userTokens);
        
        // Enhance each catalog with FAIR metadata and username
        return array_map(function($catalog) use ($usernameMap) {
            // Create a secure catalog response without any sensitive IDs/tokens
            $secureCatalog = [
                'namecatalog' => $catalog['namecatalog'],
                'created_at' => $catalog['created_at'],
                'owner_username' => $usernameMap[$catalog['token_user']] ?? 'Unknown User',
                'dispersemode' => $catalog['dispersemode'],
                'encryption' => $catalog['encryption'],
                'isprivate' => $catalog['isprivate'],
                'processed' => $catalog['processed'],
                'file_count' => (int)$catalog['file_count'],
                'group' => $catalog['group']
            ];
            
            return array_merge($secureCatalog, [
                // Findable metadata
                'fair_findable' => [
                    'indexed' => true,
                    'searchable_fields' => ['namecatalog'],
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
    // Special endpoint to get available usernames for selection
    if (isset($_GET['action']) && $_GET['action'] === 'get_usernames') {
        try {
            $fairSearch = new FAIRCatalogSearch();
            $usernames = $fairSearch->getAllAvailableUsernames();
            
            echo json_encode([
                'success' => true,
                'usernames' => $usernames,
                'total_count' => count($usernames)
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    $searchTerm = $_GET['q'] ?? '';
    
    // Parse filters from query parameters
    $filters = [
        'privacy' => $_GET['privacy'] ?? null,
        'encryption' => $_GET['encryption'] ?? null,
        'processed' => $_GET['processed'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
        'file_type' => $_GET['file_type'] ?? null,
        'username' => $_GET['username'] ?? null
    ];
    
    // SQL injection protection
    try {
        $searchTerm = validateSQLInput($searchTerm);
        foreach ($filters as $key => $value) {
            if ($value) $filters[$key] = validateSQLInput($value);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input detected', 'success' => false]);
        exit;
    }
    
    if (empty($searchTerm)) {
        echo json_encode(['error' => 'Search term required (minimum 2 characters)']);
        exit;
    }
    
    try {
        $fairSearch = new FAIRCatalogSearch();
        
        // Get catalog search results
        $catalogs = $fairSearch->searchCatalogs($searchTerm, $filters);
        
        // Ensure catalogs is always a proper array with sequential keys
        $catalogs = array_values($catalogs);
        
        // Get repository statistics
        $statistics = $fairSearch->getCatalogStatistics();
        
        echo json_encode([
            'success' => true,
            'fair_compliant' => true,
            'search_metadata' => [
                'search_term' => htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'),
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