<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Use the existing database connection
require_once 'models/db/Connection.php';

try {
    $connection = new Connection();
    $db = $connection->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $searchTerm = $_GET['q'] ?? '';
        
        if (empty($searchTerm)) {
            echo json_encode(['error' => 'Search term required']);
            exit;
        }
        
        // Search in actual files table
        $query = "SELECT keyfile, namefile, sizefile, created_at 
                  FROM files 
                  WHERE namefile ILIKE :searchTerm 
                  ORDER BY created_at DESC 
                  LIMIT 20";
        
        $stmt = $db->prepare($query);
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        $stmt->execute();
        
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $files,
            'count' => count($files),
            'search_term' => $searchTerm
        ]);
        
    } else {
        echo json_encode(['error' => 'Only GET method allowed']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>