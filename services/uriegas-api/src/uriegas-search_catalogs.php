<?php

// Enhanced CORS headers for browser compatibility
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Origin, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Handle CORS preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // This is a preflight request, just return success
    http_response_code(200);
    exit;
}

// Include necessary files
require_once dirname(__FILE__) . '/Connection.php';
require_once dirname(__FILE__) . '/Config.php';
require_once dirname(__FILE__) . '/Log.php';

/**
 * Simple DoS Protection Class
 * Basic protection since server already has comprehensive defenses
 */
class SimpleDoSProtection {
    private $log;
    private $maxRequestSize = 4096; // 4KB max request size
    private $minRequestInterval = 1; // Minimum 1 second between requests
    
    public function __construct() {
        $this->log = new Log();
    }
    
    /**
     * Basic DoS checks - simple and fast
     */
    public function isValidRequest() {
        // Check request size
        if ($this->isRequestTooLarge()) {
            $this->log->lwrite("Large request blocked from: " . $this->getClientIP());
            return false;
        }
        
        // Check for rapid fire requests (basic bot detection)
        if ($this->isTooFast()) {
            $this->log->lwrite("Rapid requests blocked from: " . $this->getClientIP());
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if request size exceeds limits
     */
    private function isRequestTooLarge() {
        $requestSize = strlen($_SERVER['REQUEST_URI'] ?? '') + 
                      strlen($_SERVER['QUERY_STRING'] ?? '') +
                      strlen(serialize(getallheaders()));
        
        return $requestSize > $this->maxRequestSize;
    }
    
    /**
     * Simple rate limiting using session
     */
    private function isTooFast() {
        session_start();
        $now = time();
        $lastRequest = $_SESSION['last_request'] ?? 0;
        
        if (($now - $lastRequest) < $this->minRequestInterval) {
            return true;
        }
        
        $_SESSION['last_request'] = $now;
        return false;
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
               $_SERVER['HTTP_X_REAL_IP'] ?? 
               $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// Initialize Simple DoS Protection
$dosProtection = new SimpleDoSProtection();

// Check if request is valid
if (!$dosProtection->isValidRequest()) {
    http_response_code(429); // Too Many Requests
    echo json_encode([
        'success' => false,
        'error' => 'Request blocked due to abuse prevention',
        'code' => 'REQUEST_BLOCKED'
    ]);
    exit;
}

/**
 * Secure input validation for prepared statements
 * Basic sanitization - main protection comes from prepared statements
 */
function validateAndSanitizeInput($input) {
    if (empty($input)) return '';
    
    // Trim whitespace and basic sanitization
    $cleanInput = trim($input);
    
    // Limit input length to prevent excessive resource usage
    if (strlen($cleanInput) > 1000) {
        throw new Exception("Input too long");
    }
    
    // Log input validation for monitoring
    logInputValidation($cleanInput);
    
    return $cleanInput;
}

/**
 * Log input validation events for security monitoring
 */
function logInputValidation($input) {
    $logEntry = [
        'timestamp' => date('c'),
        'event' => 'input_validation',
        'input_length' => strlen($input),
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 100),
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
    ];
    
    // Try multiple log locations
    $logLocations = [
        '/tmp/search_security.log',
        dirname(__FILE__) . '/search_security.log',
        '/var/log/search_security.log'
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
            
            // Validate and sanitize username
            $username = trim($username);
            if (empty($username) || strlen($username) < 2) {
                return [];
            }
            
            // Validate username format (alphanumeric, underscore, dash, dot only)
            if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
                $this->log->lwrite("Invalid username format: " . substr($username, 0, 20));
                return [];
            }
            
            // SECURITY: Use prepared statement for exact username match
            $query = "SELECT tokenuser FROM users WHERE username = :username";
            $stmt = $this->authDb->prepare($query);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->log->lwrite("Username search for '$username' found " . count($tokens) . " tokens");
            
            return $tokens;
        } catch (Exception $e) {
            $this->log->lwrite("Username search failed: " . $e->getMessage());
            return []; // Return empty array instead of throwing exception
        }
    }
    
    public function getAllAvailableUsernames($searchTerm = '', $limit = null) {
        try {
            // Check if auth database is available
            if (!$this->authDb) {
                $this->log->lwrite("Warning: Auth database not available - cannot get usernames list");
                return [];
            }
            
            // Build query with optional search term
            if (!empty($searchTerm)) {
                // Search usernames that contain the search term (case insensitive)
                $query = "SELECT DISTINCT username FROM users 
                         WHERE username IS NOT NULL AND username != '' 
                         AND LOWER(username) LIKE LOWER(:searchTerm)
                         ORDER BY username";
                if ($limit !== null && $limit > 0) {
                    $query .= " LIMIT :limit";
                }
                $stmt = $this->authDb->prepare($query);
                $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
                if ($limit !== null && $limit > 0) {
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                }
            } else {
                // Get unique usernames from users table
                $query = "SELECT DISTINCT username FROM users WHERE username IS NOT NULL AND username != '' ORDER BY username";
                if ($limit !== null && $limit > 0) {
                    $query .= " LIMIT :limit";
                }
                $stmt = $this->authDb->prepare($query);
                if ($limit !== null && $limit > 0) {
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $this->log->lwrite("Get all usernames failed: " . $e->getMessage());
            return []; // Return empty array instead of throwing exception
        }
    }
    
    // this function tranforms user tokens to usernames
    private function getUsernamesByTokens($userTokens) {
        try {
            // Check if auth database is available
            if (!$this->authDb || empty($userTokens)) {
                return [];
            }
            
            // SECURITY: Use individual parameter binding for array values
            $placeholders = [];
            $paramArray = [];
            for ($i = 0; $i < count($userTokens); $i++) {
                $paramKey = ":token_$i";
                $placeholders[] = $paramKey;
                $paramArray[$paramKey] = $userTokens[$i];
            }
            
            $query = "SELECT tokenuser, username FROM users WHERE tokenuser IN (" . implode(',', $placeholders) . ")";
            $stmt = $this->authDb->prepare($query);
            
            // Bind each parameter individually for maximum security
            foreach ($paramArray as $param => $value) {
                $stmt->bindValue($param, $value, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            
            // Return associative array with tokenuser as key and username as value
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $usernameMap = [];
            foreach ($results as $row) {
                $usernameMap[$row['tokenuser']] = $row['username'];
            }
            
            $this->log->lwrite("Retrieved usernames for " . count($usernameMap) . " tokens");
            return $usernameMap;
        } catch (Exception $e) {
            $this->log->lwrite("Getting usernames by tokens failed: " . $e->getMessage());
            return [];
        }
    }
    
    public function searchCatalogs($searchTerm, $filters = [], $limit = null, $offset = 0) {
        try {
            // Handle username filter separately due to cross-database query
            $catalogsWithUsername = [];
            if (!empty($filters['username'])) {
                $userTokens = $this->getUserTokensByUsername($filters['username']);
                if (empty($userTokens)) {
                    // No users found with this username
                    return ['catalogs' => [], 'total_count' => 0];
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
                    return ['catalogs' => [], 'total_count' => 0];
                }
                // Remove file_type from filters for main query
                $tempFilters = $filters;
                unset($tempFilters['file_type']);
                $filters = $tempFilters;
            }

            // First, get the total count without LIMIT
            $countQuery = $this->buildFAIRCountQuery($filters, $searchTerm);
            $countStmt = $this->pubSubDb->prepare($countQuery);
            
            // Bind parameters for count query
            if ($searchTerm !== '*' && !empty($searchTerm)) {
                $countStmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
            }
            if (!empty($filters['date_from'])) {
                $countStmt->bindValue(':dateFrom', $filters['date_from'] . ' 00:00:00', PDO::PARAM_STR);
            }
            if (!empty($filters['date_to'])) {
                $countStmt->bindValue(':dateTo', $filters['date_to'] . ' 23:59:59', PDO::PARAM_STR);
            }
            
            $countStmt->execute();
            $totalBeforeFiltering = $countStmt->fetchColumn();

            // Build dynamic query based on FAIR principles
            $query = $this->buildFAIRQuery($filters, $searchTerm, $limit, $offset);
            
            $stmt = $this->pubSubDb->prepare($query);
            
            // Bind search term only if it's not a wildcard and not empty
            if ($searchTerm !== '*' && !empty($searchTerm)) {
                $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
            }
            
            // Bind date filters if provided with proper parameter types
            if (!empty($filters['date_from'])) {
                $stmt->bindValue(':dateFrom', $filters['date_from'] . ' 00:00:00', PDO::PARAM_STR);
            }
            
            if (!empty($filters['date_to'])) {
                $stmt->bindValue(':dateTo', $filters['date_to'] . ' 23:59:59', PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate total count after username/filetype filtering
            $totalCount = $totalBeforeFiltering;
            
            // Filter by username if needed
            if (!empty($catalogsWithUsername)) {
                $results = array_filter($results, function($catalog) use ($catalogsWithUsername) {
                    return in_array($catalog['token_user'], $catalogsWithUsername);
                });
                // Re-index array to ensure sequential keys (prevents object in JSON)
                $results = array_values($results);
                
                // Recalculate total count after username filtering
                $totalCount = count($results);
            }
            
            // Filter by file type if needed
            if (!empty($catalogsWithFileType)) {
                $results = array_filter($results, function($catalog) use ($catalogsWithFileType) {
                    return in_array($catalog['tokencatalog'], $catalogsWithFileType);
                });
                // Re-index array to ensure sequential keys (prevents object in JSON)
                $results = array_values($results);
                
                // Recalculate total count after file type filtering
                $totalCount = count($results);
            }
            
            // For username and file type filtering, we need to count before applying LIMIT
            // So we'll estimate based on the filtered results
            if (!empty($catalogsWithUsername) || !empty($catalogsWithFileType)) {
                // If we have cross-database filtering, the count is the actual filtered count
                $actualTotalCount = count($results);
                
                // Apply manual limit if specified
                if ($limit !== null && $limit > 0) {
                    $limitedResults = array_slice($results, $offset, $limit);
                } else {
                    $limitedResults = $results;
                }
                
                return [
                    'catalogs' => $this->enhanceWithFAIRMetadata($limitedResults),
                    'total_count' => $actualTotalCount
                ];
            }
            
            // For regular queries, use the count from the count query
            return [
                'catalogs' => $this->enhanceWithFAIRMetadata($results),
                'total_count' => (int)$totalCount
            ];
            
        } catch (Exception $e) {
            throw new Exception("Catalog search failed: " . $e->getMessage());
        }
    }

    private function buildFAIRCountQuery($filters = [], $searchTerm = '') {
        // Build count query (similar to buildFAIRQuery but without SELECT fields and LIMIT)
        $query = "SELECT COUNT(DISTINCT c.tokencatalog) 
                  FROM catalogs c";
        
        // WHERE clause for Findability (FAIR principle)
        $whereConditions = [];
        
        // Only add search term condition if it's not a wildcard
        if ($searchTerm !== '*' && !empty($searchTerm)) {
            $whereConditions[] = "(c.namecatalog ILIKE :searchTerm OR c.tokencatalog ILIKE :searchTerm OR c.keycatalog ILIKE :searchTerm)";
        }
        
        // Apply privacy filter (Accessibility - FAIR principle)
        // Default to public catalogs only for security
        if (!empty($filters['privacy'])) {
            if ($filters['privacy'] === 'public') {
                $whereConditions[] = "c.isprivate = false";
            } elseif ($filters['privacy'] === 'private') {
                $whereConditions[] = "c.isprivate = true";
            }
        } else {
            // Default: only show public catalogs
            $whereConditions[] = "c.isprivate = false";
        }
        
        // Apply encryption filter
        if (!empty($filters['encryption'])) {
            if ($filters['encryption'] === 'encrypted') {
                $whereConditions[] = "c.encryption = true";
            } elseif ($filters['encryption'] === 'unencrypted') {
                $whereConditions[] = "c.encryption = false";
            }
        }
        
        // Apply processing status filter
        if (!empty($filters['processed'])) {
            if ($filters['processed'] === 'processed') {
                $whereConditions[] = "c.processed = true";
            } elseif ($filters['processed'] === 'unprocessed') {
                $whereConditions[] = "c.processed = false";
            }
        }
        
        // Date range filters - parameters bound in calling function
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
        
        return $query;
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
            
            // Log security validation for file type search
            $this->log->lwrite("File type search initiated for extension: " . $fileType);
            
            // SECURITY: Use only prepared statements with no string concatenation
            // Search for files ending with the specified extension using PostgreSQL functions
            $query = "SELECT DISTINCT keyfile FROM files 
                      WHERE LOWER(namefile) LIKE CONCAT('%', '.', LOWER(:fileType))";
            $stmt = $this->metadataDb->prepare($query);
            $stmt->bindValue(':fileType', $fileType, PDO::PARAM_STR);
            $stmt->execute();
            $fileTokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($fileTokens)) {
                $this->log->lwrite("No files found with extension: " . $fileType);
                return [];
            }
            
            $this->log->lwrite("Found " . count($fileTokens) . " files with extension: " . $fileType);
            
            // Get catalogs that contain these files using prepared statements only
            // Use array parameter binding for maximum security
            $placeholders = [];
            $paramArray = [];
            for ($i = 0; $i < count($fileTokens); $i++) {
                $paramKey = ":token_$i";
                $placeholders[] = $paramKey;
                $paramArray[$paramKey] = $fileTokens[$i];
            }
            
            $query = "SELECT DISTINCT tokencatalog FROM catalogs_files 
                      WHERE token_file IN (" . implode(',', $placeholders) . ")";
            $stmt = $this->pubSubDb->prepare($query);
            
            // Bind each parameter individually for maximum security
            foreach ($paramArray as $param => $value) {
                $stmt->bindValue($param, $value, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $catalogTokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $this->log->lwrite("File type search completed. Found " . count($catalogTokens) . " catalogs with extension: " . $fileType);
            
            return $catalogTokens;
        } catch (Exception $e) {
            $this->log->lwrite("File type search failed for extension '$fileType': " . $e->getMessage());
            throw new Exception("File type search failed: " . $e->getMessage());
        }
    }
    
    private function buildFAIRQuery($filters = [], $searchTerm = '', $limit = null, $offset = 0) {
        // SECURITY: All dynamic content uses prepared statement parameters
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
        
        // WHERE clause for Findability (FAIR principle)
        $whereConditions = [];
        
        // Only add search term condition if it's not a wildcard
        if ($searchTerm !== '*' && !empty($searchTerm)) {
            $whereConditions[] = "(c.namecatalog ILIKE :searchTerm OR c.tokencatalog ILIKE :searchTerm OR c.keycatalog ILIKE :searchTerm)";
        }
        
        // Apply privacy filter (Accessibility - FAIR principle)
        // Default to public catalogs only for security
        if (!empty($filters['privacy'])) {
            if ($filters['privacy'] === 'public') {
                $whereConditions[] = "c.isprivate = false";
            } elseif ($filters['privacy'] === 'private') {
                $whereConditions[] = "c.isprivate = true";
            }
        } else {
            // Default: only show public catalogs
            $whereConditions[] = "c.isprivate = false";
        }
        
        // Apply encryption filter
        if (!empty($filters['encryption'])) {
            if ($filters['encryption'] === 'encrypted') {
                $whereConditions[] = "c.encryption = true";
            } elseif ($filters['encryption'] === 'unencrypted') {
                $whereConditions[] = "c.encryption = false";
            }
        }
        
        // Apply processing status filter
        if (!empty($filters['processed'])) {
            if ($filters['processed'] === 'processed') {
                $whereConditions[] = "c.processed = true";
            } elseif ($filters['processed'] === 'unprocessed') {
                $whereConditions[] = "c.processed = false";
            }
        }
        
        // Date range filters - parameters bound in calling function
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
        
        // GROUP BY and ORDER BY for proper FAIR compliance
        $query .= " GROUP BY c.keycatalog, c.tokencatalog, c.namecatalog, c.created_at, c.token_user, c.dispersemode, c.encryption, c.isprivate, c.father, c.\"group\", c.processed";
        $query .= " ORDER BY c.created_at DESC";
        
        // Add optional LIMIT and OFFSET
        if ($limit !== null && $limit > 0) {
            $query .= " LIMIT " . (int)$limit;
            if ($offset > 0) {
                $query .= " OFFSET " . (int)$offset;
            }
        }
        
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
    // Simple endpoint to check DoS protection status
    if (isset($_GET['action']) && $_GET['action'] === 'dos_status') {
        echo json_encode([
            'success' => true,
            'dos_protection' => [
                'active' => true,
                'type' => 'Simple Session-based',
                'max_request_size_kb' => 4,
                'min_request_interval_seconds' => 1
            ],
            'server_info' => [
                'timestamp' => date('c'),
                'protection_level' => 'Basic'
            ]
        ]);
        exit;
    }
    
    // Special endpoint to get available usernames for selection
    if (isset($_GET['action']) && $_GET['action'] === 'get_usernames') {
        try {
            $fairSearch = new FAIRCatalogSearch();
            $searchTerm = $_GET['search'] ?? '';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
            
            // Validate limit parameter
            if ($limit !== null && $limit < 0) $limit = null;
            
            // Validate and sanitize search term
            if (!empty($searchTerm)) {
                $searchTerm = validateAndSanitizeInput($searchTerm);
            }
            
            $usernames = $fairSearch->getAllAvailableUsernames($searchTerm, $limit);
            
            echo json_encode([
                'success' => true,
                'usernames' => $usernames,
                'total_count' => count($usernames),
                'search_term' => $searchTerm,
                'limit_applied' => $limit
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
    
    // Parse pagination parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Validate pagination parameters
    if ($limit !== null && $limit < 0) $limit = null;
    if ($offset < 0) $offset = 0;
    
    // Basic input validation - security provided by prepared statements
    try {
        $searchTerm = validateAndSanitizeInput($searchTerm);
        foreach ($filters as $key => $value) {
            if ($value) $filters[$key] = validateAndSanitizeInput($value);
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input', 'success' => false]);
        exit;
    }
    
    // Allow wildcard searches and empty terms for browsing
    if (empty($searchTerm)) {
        $searchTerm = '*'; // Convert empty to wildcard for browsing
    }
    
    try {
        $fairSearch = new FAIRCatalogSearch();
        
        // Get catalog search results with total count
        $searchResult = $fairSearch->searchCatalogs($searchTerm, $filters, $limit, $offset);
        
        // Extract catalogs and total count
        $catalogs = $searchResult['catalogs'] ?? [];
        $totalCount = $searchResult['total_count'] ?? 0;
        
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
                'total_results' => $totalCount,
                'returned_results' => count($catalogs),
                'limited' => $totalCount > count($catalogs),
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($limit !== null) ? ($offset + count($catalogs) < $totalCount) : false
                ]
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