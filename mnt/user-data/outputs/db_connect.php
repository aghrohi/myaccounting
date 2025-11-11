<?php
/**
 * Enhanced Database Connection Configuration
 * 
 * This file handles the database connection using PDO
 * with improved error handling and security features.
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not permitted');
}

// Environment configuration (change to 'production' on live server)
define('ENVIRONMENT', 'development');

// Database configuration
$db_config = [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'accounting_app',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'charset'  => 'utf8mb4',
];

// Build DSN (Data Source Name)
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $db_config['host'],
    $db_config['port'],
    $db_config['database'],
    $db_config['charset']
);

// PDO options for better performance and security
$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

// Error handling based on environment
function handleDatabaseError($exception) {
    // Log the error (always)
    error_log('Database Error: ' . $exception->getMessage());
    
    if (ENVIRONMENT === 'development') {
        // Show detailed error in development
        die('<div style="padding: 20px; background: #f44336; color: white; font-family: sans-serif;">
             <h2>Database Connection Error</h2>
             <p>' . htmlspecialchars($exception->getMessage()) . '</p>
             <pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>
             </div>');
    } else {
        // Show generic error in production
        die('<div style="padding: 20px; background: #f44336; color: white; font-family: sans-serif;">
             <h2>System Error</h2>
             <p>We are experiencing technical difficulties. Please try again later.</p>
             </div>');
    }
}

// Establish database connection
try {
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $pdo_options);
    
    // Set additional attributes after connection
    $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    
    // Test the connection with a simple query
    $pdo->query('SELECT 1');
    
} catch (PDOException $e) {
    handleDatabaseError($e);
}

// Helper function for prepared statements with error handling
function db_query($query, $params = [], $fetch_all = true) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        if (stripos($query, 'SELECT') === 0) {
            return $fetch_all ? $stmt->fetchAll() : $stmt->fetch();
        }
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        if (ENVIRONMENT === 'development') {
            throw $e;
        }
        error_log('Query Error: ' . $e->getMessage());
        return false;
    }
}

// Helper function to get last insert ID
function db_insert_id() {
    global $pdo;
    return $pdo->lastInsertId();
}

// Helper function for transactions
function db_transaction($callback) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Function to sanitize output
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Function to format currency
function format_currency($amount, $symbol = '$') {
    return $symbol . number_format($amount, 2, '.', ',');
}

// Function to format date for display
function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Function to format datetime for display
function format_datetime($datetime, $format = 'M d, Y g:i A') {
    return date($format, strtotime($datetime));
}