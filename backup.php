<?php
/**
 * Database Backup Handler
 *
 * This script uses mysqldump to create a database backup and force-downloads it.
 */

// Start session (required for auth functions)
session_start();

// Include core files
require_once 'db_connect.php'; // Provides $db_config
require_once 'functions.php'; // Provides requireAdmin()

// --- Security Check ---
// Only logged-in admins can run this
requireAdmin();

// Get database credentials from the global config
global $db_config;
$db_host = $db_config['host'];
$db_user = $db_config['username'];
$db_pass = $db_config['password'];
$db_name = $db_config['database'];
$db_port = $db_config['port']; // Get port from config

// Set the filename for the download
$filename = "accounting_backup_" . date("Y-m-d_H-i-s") . ".sql";

// Set headers to force download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// --- Create the mysqldump command ---
// This command securely passes the password and includes table structures and data.
// It explicitly specifies the port.
$command = sprintf(
    'mysqldump --host=%s --port=%s --user=%s --password=%s %s',
    escapeshellarg($db_host),
    escapeshellarg($db_port),
    escapeshellarg($db_user),
    escapeshellarg($db_pass),
    escapeshellarg($db_name)
);

// --- Execute the command ---
// passthru() executes the command and passes the raw output directly to the browser
// This avoids loading the entire SQL file into server memory
passthru($command, $return_var);

// Optional: Check for errors
if ($return_var !== 0) {
    // If you get an error, it's often due to mysqldump not being in the server's PATH
    error_log("mysqldump command failed with return code: $return_var");
}

exit;
?>
