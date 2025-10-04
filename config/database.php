<?php
// Environment detection and database configuration
function detectEnvironment() {
    // Check if we're on localhost (XAMPP) or live hosting
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    $http_host = $_SERVER['HTTP_HOST'] ?? '';
    
    // Local development indicators
    $is_local = (
        $server_name === 'localhost' || 
        $server_name === '127.0.0.1' ||
        strpos($http_host, 'localhost') !== false ||
        strpos($http_host, '127.0.0.1') !== false ||
        strpos($http_host, '.local') !== false
    );
    
    return $is_local ? 'local' : 'production';
}

$environment = detectEnvironment();

// Database configuration based on environment
if ($environment === 'local') {
    // Local XAMPP configuration
    $host = 'localhost';
    $dbname = 'cursor1';
    $username = 'root';
    $password = '';
} else {
    // Live cPanel configuration
    $host = 'localhost';
    $dbname = 'a1e750tdxgba_cursor1';
    $username = 'a1e750tdxgba_15Crossways';
    $password = 'Crossways15!';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // More detailed error for debugging
    $error_message = "Database connection failed for " . $environment . " environment. ";
    $error_message .= "Host: $host, Database: $dbname, User: $username. ";
    $error_message .= "Error: " . $e->getMessage();
    die($error_message);
}
?>
