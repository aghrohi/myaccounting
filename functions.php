<?php
/**
 * Helper Functions and Utilities
 * 
 * Common functions used throughout the application
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not permitted');
}

/**
 * Authentication Functions
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = 'Access denied. Administrator privileges required.';
        header('Location: index.php?page=dashboard');
        exit;
    }
}

/**
 * Data Validation Functions
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateAmount($amount) {
    return is_numeric($amount) && $amount >= 0;
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

/**
 * Security Functions
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Database Helper Functions
 */
function getAccountBalance($account_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            a.starting_balance,
            COALESCE(SUM(CASE 
                WHEN t.dest_account_id = a.account_id THEN t.amount
                WHEN t.source_account_id = a.account_id THEN -t.amount
                ELSE 0 
            END), 0) as transaction_total
        FROM accounts a
        LEFT JOIN transactions t ON (t.source_account_id = a.account_id OR t.dest_account_id = a.account_id)
        WHERE a.account_id = ?
        GROUP BY a.account_id
    ");
    
    $stmt->execute([$account_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['starting_balance'] + $result['transaction_total'];
    }
    
    return 0;
}

function getCategoryTotal($category_id, $start_date = null, $end_date = null) {
    global $pdo;
    
    $query = "SELECT SUM(amount) as total FROM transactions WHERE category_id = ?";
    $params = [$category_id];
    
    if ($start_date) {
        $query .= " AND transaction_date >= ?";
        $params[] = $start_date;
    }
    
    if ($end_date) {
        $query .= " AND transaction_date <= ?";
        $params[] = $end_date;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch();
    
    return $result['total'] ?? 0;
}

/**
 * Audit Logging
 */
function logActivity($action, $table = null, $record_id = null, $old_values = null, $new_values = null) {
    global $pdo;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $action,
        $table,
        $record_id,
        $old_values ? json_encode($old_values) : null,
        $new_values ? json_encode($new_values) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
    
    return true;
}

/**
 * Formatting Functions
 */
function formatMoney($amount, $currency_symbol = '$', $decimals = 2) {
    $negative = $amount < 0;
    $amount = abs($amount);
    $formatted = $currency_symbol . number_format($amount, $decimals, '.', ',');
    
    if ($negative) {
        return '<span class="text-danger">-' . $formatted . '</span>';
    }
    
    return $formatted;
}

function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'M d, Y g:i A') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, '.', ',');
}

/**
 * Data Fetching Functions
 */
function getAccounts($active_only = true) {
    global $pdo;
    
    $query = "
        SELECT a.*, ah.holder_name, c.currency_symbol
        FROM accounts a
        JOIN account_holders ah ON a.holder_id = ah.holder_id
        JOIN currencies c ON a.currency_id = c.currency_id
    ";
    
    if ($active_only) {
        $query .= " WHERE a.is_active = 1";
    }
    
    $query .= " ORDER BY a.account_name";
    
    return $pdo->query($query)->fetchAll();
}

function getCategories($type = null, $active_only = true) {
    global $pdo;
    
    $query = "SELECT * FROM categories WHERE 1=1";
    $params = [];
    
    if ($type) {
        $query .= " AND category_type = ?";
        $params[] = $type;
    }
    
    if ($active_only) {
        $query .= " AND is_active = 1";
    }
    
    $query .= " ORDER BY sort_order, category_name";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

function getCurrencies($active_only = true) {
    global $pdo;
    
    $query = "SELECT * FROM currencies";
    
    if ($active_only) {
        $query .= " WHERE is_active = 1";
    }
    
    $query .= " ORDER BY currency_code";
    
    return $pdo->query($query)->fetchAll();
}

function getAccountHolders() {
    global $pdo;
    
    return $pdo->query("
        SELECT * FROM account_holders 
        ORDER BY holder_name
    ")->fetchAll();
}

/**
 * Transaction Functions
 */
function createTransaction($data) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Generate transaction reference
        $prefix = 'TXN';
        $date = date('Ymd');
        $rand = strtoupper(substr(md5(uniqid()), 0, 4));
        $transaction_ref = "{$prefix}-{$date}-{$rand}";
        
        // Insert transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (transaction_ref, source_account_id, dest_account_id, category_id, 
             user_id, transaction_date, amount, details, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $transaction_ref,
            $data['source_account_id'] ?: null,
            $data['dest_account_id'] ?: null,
            $data['category_id'],
            $_SESSION['user_id'],
            $data['transaction_date'],
            $data['amount'],
            $data['details'],
            $data['notes'] ?? null
        ]);
        
        $transaction_id = $pdo->lastInsertId();
        
        // Update account balances
        if ($data['source_account_id']) {
            $stmt = $pdo->prepare("
                UPDATE accounts 
                SET current_balance = current_balance - ? 
                WHERE account_id = ?
            ");
            $stmt->execute([$data['amount'], $data['source_account_id']]);
        }
        
        if ($data['dest_account_id']) {
            $stmt = $pdo->prepare("
                UPDATE accounts 
                SET current_balance = current_balance + ? 
                WHERE account_id = ?
            ");
            $stmt->execute([$data['amount'], $data['dest_account_id']]);
        }
        
        // Log activity
        logActivity('CREATE', 'transactions', $transaction_id, null, $data);
        
        $pdo->commit();
        
        return $transaction_id;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Report Generation Functions
 */
function getIncomeExpenseReport($start_date, $end_date) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            c.category_type,
            c.category_name,
            SUM(t.amount) as total
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE t.transaction_date BETWEEN ? AND ?
        GROUP BY c.category_id
        ORDER BY c.category_type, total DESC
    ");
    
    $stmt->execute([$start_date, $end_date]);
    
    $results = ['income' => [], 'expenses' => [], 'totals' => ['income' => 0, 'expenses' => 0]];
    
    while ($row = $stmt->fetch()) {
        if ($row['category_type'] === 'credit') {
            $results['income'][] = $row;
            $results['totals']['income'] += $row['total'];
        } else {
            $results['expenses'][] = $row;
            $results['totals']['expenses'] += $row['total'];
        }
    }
    
    $results['totals']['net'] = $results['totals']['income'] - $results['totals']['expenses'];
    
    return $results;
}

function getCashFlowReport($start_date, $end_date) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE(t.transaction_date) as date,
            SUM(CASE WHEN c.category_type = 'credit' THEN t.amount ELSE 0 END) as inflow,
            SUM(CASE WHEN c.category_type = 'debit' THEN t.amount ELSE 0 END) as outflow
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE t.transaction_date BETWEEN ? AND ?
        GROUP BY DATE(t.transaction_date)
        ORDER BY date
    ");
    
    $stmt->execute([$start_date, $end_date]);
    
    return $stmt->fetchAll();
}

/**
 * Dashboard Statistics
 */
function getDashboardStats() {
    global $pdo;
    
    $stats = [];
    
    // Total accounts balance
    $stmt = $pdo->query("
        SELECT SUM(current_balance) as total 
        FROM accounts 
        WHERE is_active = 1
    ");
    $stats['total_balance'] = $stmt->fetch()['total'] ?? 0;
    
    // This month's income
    $stmt = $pdo->prepare("
        SELECT SUM(t.amount) as total
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE c.category_type = 'credit'
        AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
        AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute();
    $stats['month_income'] = $stmt->fetch()['total'] ?? 0;
    
    // This month's expenses
    $stmt = $pdo->prepare("
        SELECT SUM(t.amount) as total
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE c.category_type = 'debit'
        AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
        AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute();
    $stats['month_expenses'] = $stmt->fetch()['total'] ?? 0;
    
    // Recent transactions count
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM transactions
        WHERE transaction_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
    ");
    $stats['recent_transactions'] = $stmt->fetch()['total'] ?? 0;
    
    return $stats;
}

/**
 * Pagination Helper
 */
function paginate($query, $params = [], $page = 1, $per_page = 20) {
    global $pdo;
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM ($query) as count_table";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Calculate pagination
    $total_pages = ceil($total / $per_page);
    $page = max(1, min($page, $total_pages));
    $offset = ($page - 1) * $per_page;
    
    // Get paginated results
    $paginated_query = "$query LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($paginated_query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
    
    return [
        'data' => $results,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => $total_pages
    ];
}

/**
 * Export Functions
 */
function exportToCSV($data, $filename = 'export.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Add data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Notification Functions
 */
function setNotification($message, $type = 'info') {
    $_SESSION['notifications'][] = [
        'message' => $message,
        'type' => $type,
        'timestamp' => time()
    ];
}

function getNotifications() {
    $notifications = $_SESSION['notifications'] ?? [];
    unset($_SESSION['notifications']);
    return $notifications;
}