<?php
/*
 * Database Connection File - PDO Implementation
 * Professional Accounting System
 */

// Database credentials - CHANGE THESE TO MATCH YOUR SETUP
$host = '127.0.0.1';      // or 'localhost'
$db   = 'accounting_app'; // Database name
$user = 'root';           // MySQL username
$pass = '';               // MySQL password
$charset = 'utf8mb4';

// Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options for security and performance
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Fetch as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements
    PDO::ATTR_STRINGIFY_FETCHES  => false,                    // Don't convert numbers to strings
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

// Try to connect
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Set timezone for MySQL session (optional - adjust as needed)
    $pdo->exec("SET time_zone = '+00:00'");
    
} catch (\PDOException $e) {
    // In production, log this error and show a generic message
    // For development, you can display the actual error
    
    // Development mode (remove in production)
    die("Database Connection Failed: " . $e->getMessage());
    
    // Production mode (uncomment for production)
    // error_log("Database Connection Failed: " . $e->getMessage());
    // die("System temporarily unavailable. Please try again later.");
}

// Helper function to check if database is connected
function isDatabaseConnected() {
    global $pdo;
    try {
        $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Helper function for debugging queries (remove in production)
function debugQuery($stmt) {
    ob_start();
    $stmt->debugDumpParams();
    $debug = ob_get_clean();
    error_log($debug);
}
?>
