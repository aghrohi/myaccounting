<?php
/**
 * Transaction CSV Export Handler
 *
 * This script fetches transactions based on filters and exports them as a CSV file.
 */

// Start session (required for auth functions)
session_start();

// Include core files
require_once 'db_connect.php'; // Provides $db_config
require_once 'functions.php'; // Provides requireLogin() and exportToCSV()

// --- Security Check ---
requireLogin();

// --- Get filter parameters from URL ---
$filter_account = $_GET['account'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';

// --- Build Query (based on transactions.php) ---
$query = "
    SELECT 
           t.transaction_date as Date,
           t.details as Description,
           c.category_name as Category,
           c.category_type as Type,
           sa.account_name as Source_Account, 
           da.account_name as Destination_Account,
           t.amount as Amount,
           u.full_name as User,
           t.transaction_ref as Reference_ID,
           IF(t.is_reconciled = 1, 'Yes', 'No') as Reconciled
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    JOIN users u ON t.user_id = u.user_id
    LEFT JOIN accounts sa ON t.source_account_id = sa.account_id
    LEFT JOIN accounts da ON t.dest_account_id = da.account_id
    WHERE 1=1
";

$params = [];

if ($filter_date_from) {
    $query .= " AND t.transaction_date >= ?";
    $params[] = $filter_date_from . ' 00:00:00';
}

if ($filter_date_to) {
    $query .= " AND t.transaction_date <= ?";
    $params[] = $filter_date_to . ' 23:59:59';
}

if ($filter_account) {
    $query .= " AND (t.source_account_id = ? OR t.dest_account_id = ?)";
    $params[] = $filter_account;
    $params[] = $filter_account;
}

if ($filter_category) {
    $query .= " AND t.category_id = ?";
    $params[] = $filter_category;
}

$query .= " ORDER BY t.transaction_date DESC";

// --- Fetch Data ---
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Set Filename and Export ---
$filename = "transactions_export_" . date("Y-m-d") . ".csv";

// Use the existing function from functions.php
exportToCSV($transactions, $filename);

exit;
?>
