<?php
// backup.php - Database Backup Handler
// Include core files
require_once 'db_connect.php';
require_once 'functions.php';

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
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

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

// Optional: Check for errors (if $return_var is not 0)
if ($return_var !== 0) {
    // If you get an error, it's often due to mysqldump not being in the server's PATH
    // or permissions issues.
    error_log("mysqldump command failed with return code: $return_var");
    // You can't send a file AND an error, but you can log it for debugging.
}

exit;
?>
