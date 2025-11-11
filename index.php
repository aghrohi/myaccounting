<?php
// Start the session at the very beginning
session_start();

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Include the database connection
require 'db_connect.php';

// --- Global Data Fetching ---
$accounts = [];
$categories = [];
$users = [];
$account_holders = [];
$currencies = [];

// Fetch data ONLY if the user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        $accounts = $pdo->query("SELECT a.*, ah.holder_name, c.currency_code 
                                FROM accounts a 
                                LEFT JOIN account_holders ah ON a.holder_id = ah.holder_id
                                LEFT JOIN currencies c ON a.currency_id = c.currency_id
                                ORDER BY a.account_name")->fetchAll();
        $categories = $pdo->query("SELECT category_id, category_name, category_type FROM categories ORDER BY category_type, category_name")->fetchAll();
        $users = $pdo->query("SELECT user_id, username, is_admin FROM users ORDER BY username")->fetchAll();
        $account_holders = $pdo->query("SELECT holder_id, holder_name FROM account_holders ORDER BY holder_name")->fetchAll();
        $currencies = $pdo->query("SELECT currency_id, currency_code, currency_name FROM currencies ORDER BY currency_code")->fetchAll();
    } catch (PDOException $e) {
        $error = "Error fetching data: " . $e->getMessage();
    }
}

// --- Simple Router ---
$page = $_GET['page'] ?? 'login';
if (isset($_SESSION['user_id']) && $page === 'login') {
    $page = 'dashboard';
}

// --- Action Handler (POST requests) ---
$message = '';
$error = '';
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'login':
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                if (empty($username) || empty($password)) {
                    $error = "Please enter both username and password.";
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password_hash'])) {
                        // Password is correct!
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['is_admin'] = $user['is_admin'];
                        header("Location: index.php?page=dashboard");
                        exit;
                    } else {
                        $error = "Invalid username or password.";
                    }
                }
                $page = 'login';
                break;

            case 'add_user':
                if ($_SESSION['is_admin']) {
                    $username = trim($_POST['username'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
                    
                    if (strlen($password) < 6) {
                        $error = "Password must be at least 6 characters long.";
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, is_admin) VALUES (?, ?, ?)");
                        $stmt->execute([$username, $hash, $is_admin]);
                        $message = "User '$username' created successfully.";
                    }
                    $page = 'setup_users';
                }
                break;

            case 'add_currency':
                if ($_SESSION['is_admin']) {
                    $stmt = $pdo->prepare("INSERT INTO currencies (currency_code, currency_name) VALUES (?, ?)");
                    $stmt->execute([
                        strtoupper(trim($_POST['currency_code'] ?? '')),
                        trim($_POST['currency_name'] ?? '')
                    ]);
                    $message = "Currency added successfully.";
                    $page = 'setup_currency';
                    
                    // Refresh currencies list
                    $currencies = $pdo->query("SELECT currency_id, currency_code, currency_name FROM currencies ORDER BY currency_code")->fetchAll();
                }
                break;

            case 'add_holder':
                if ($_SESSION['is_admin']) {
                    $stmt = $pdo->prepare("INSERT INTO account_holders (holder_name) VALUES (?)");
                    $stmt->execute([trim($_POST['holder_name'] ?? '')]);
                    $message = "Account holder added successfully.";
                    $page = 'setup_holders';
                    
                    // Refresh holders list
                    $account_holders = $pdo->query("SELECT holder_id, holder_name FROM account_holders ORDER BY holder_name")->fetchAll();
                }
                break;

            case 'add_account':
                $stmt = $pdo->prepare("INSERT INTO accounts (account_name, holder_id, account_details, starting_balance, creation_date, currency_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    trim($_POST['account_name'] ?? ''),
                    $_POST['holder_id'],
                    trim($_POST['details'] ?? ''),
                    $_POST['starting_amount'] ?? 0,
                    $_POST['creation_date'],
                    $_POST['currency_id']
                ]);
                $message = "Account created successfully.";
                $page = 'accounts';
                
                // Refresh accounts list
                $accounts = $pdo->query("SELECT a.*, ah.holder_name, c.currency_code 
                                        FROM accounts a 
                                        LEFT JOIN account_holders ah ON a.holder_id = ah.holder_id
                                        LEFT JOIN currencies c ON a.currency_id = c.currency_id
                                        ORDER BY a.account_name")->fetchAll();
                break;

            case 'add_category':
                $stmt = $pdo->prepare("INSERT INTO categories (category_name, category_type) VALUES (?, ?)");
                $stmt->execute([
                    trim($_POST['category_name'] ?? ''),
                    $_POST['category_type']
                ]);
                $message = "Category created successfully.";
                $page = 'categories';
                
                // Refresh categories list
                $categories = $pdo->query("SELECT category_id, category_name, category_type FROM categories ORDER BY category_type, category_name")->fetchAll();
                break;

            case 'add_transaction':
                $source = $_POST['source_account_id'] ?? '0';
                $dest = $_POST['dest_account_id'] ?? '0';
                
                // Validation: Can't have both source and dest as external
                if ($source == '0' && $dest == '0') {
                    $error = "A transaction must have at least one account (source or destination).";
                } else if ($source == $dest && $source != '0') {
                    $error = "Source and destination accounts cannot be the same.";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO transactions (source_account_id, dest_account_id, category_id, user_id, transaction_date, amount, details) VALUES (NULLIF(?, '0'), NULLIF(?, '0'), ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $source,
                        $dest,
                        $_POST['category_id'],
                        $_SESSION['user_id'],
                        $_POST['transaction_date'],
                        abs($_POST['amount']), // Ensure positive amount
                        trim($_POST['details'] ?? '')
                    ]);
                    $message = "Transaction added successfully.";
                }
                $page = 'transactions';
                break;

            case 'delete_transaction':
                if (isset($_POST['transaction_id'])) {
                    $stmt = $pdo->prepare("DELETE FROM transactions WHERE transaction_id = ?");
                    $stmt->execute([$_POST['transaction_id']]);
                    $message = "Transaction deleted successfully.";
                }
                $page = 'transactions';
                break;

            case 'delete_account':
                if ($_SESSION['is_admin'] && isset($_POST['account_id'])) {
                    // Check if account has transactions
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE source_account_id = ? OR dest_account_id = ?");
                    $stmt->execute([$_POST['account_id'], $_POST['account_id']]);
                    $count = $stmt->fetchColumn();
                    
                    if ($count > 0) {
                        $error = "Cannot delete account with existing transactions.";
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM accounts WHERE account_id = ?");
                        $stmt->execute([$_POST['account_id']]);
                        $message = "Account deleted successfully.";
                    }
                }
                $page = 'accounts';
                break;
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// --- Logout Action ---
if ($page === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}

// --- Security Check ---
if (!isset($_SESSION['user_id']) && $page !== 'login') {
    header("Location: index.php?page=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting System - Professional Edition</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Main App Navigation -->
        <nav class="main-nav">
            <div class="nav-wrapper">
                <div class="nav-brand">
                    <i class="fas fa-chart-line"></i>
                    <span>Accounting Pro</span>
                </div>
                <ul class="nav-links">
                    <li class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                        <a href="index.php?page=dashboard">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <?php if ($_SESSION['is_admin']): ?>
                    <li class="dropdown <?php echo strpos($page, 'setup') !== false ? 'active' : ''; ?>">
                        <a href="#">
                            <i class="fas fa-cogs"></i>
                            <span>Setup</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="index.php?page=setup_users"><i class="fas fa-users"></i> Users/Rights</a></li>
                            <li><a href="index.php?page=setup_currency"><i class="fas fa-coins"></i> Currency</a></li>
                            <li><a href="index.php?page=setup_holders"><i class="fas fa-id-card"></i> Account Holders</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="dropdown <?php echo in_array($page, ['accounts', 'categories']) ? 'active' : ''; ?>">
                        <a href="#">
                            <i class="fas fa-wallet"></i>
                            <span>Accounts</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="index.php?page=accounts"><i class="fas fa-bank"></i> Manage Accounts</a></li>
                            <li><a href="index.php?page=categories"><i class="fas fa-tags"></i> Manage Categories</a></li>
                        </ul>
                    </li>
                    <li class="<?php echo $page === 'transactions' ? 'active' : ''; ?>">
                        <a href="index.php?page=transactions">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transactions</span>
                        </a>
                    </li>
                    <li class="<?php echo in_array($page, ['reports', 'run_report']) ? 'active' : ''; ?>">
                        <a href="index.php?page=reports">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                </ul>
                <div class="nav-user">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <?php if ($_SESSION['is_admin']): ?>
                            <span class="badge badge-admin">Admin</span>
                        <?php endif; ?>
                    </div>
                    <button id="theme-toggle" class="btn-icon" title="Toggle theme">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </button>
                    <a href="index.php?page=logout" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Main Content Area -->
    <main class="container">
        <!-- Message/Error Display -->
        <?php if ($message): ?>
            <div class="alert alert-success animate-in">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error animate-in">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php
        // Page Content Switch
        $allowed_pages = [
            'login',
            'dashboard',
            'setup_users',
            'setup_currency',
            'setup_holders',
            'accounts',
            'categories',
            'transactions',
            'reports',
            'run_report'
        ];

        $page_file = "pages/{$page}.php";

        if (in_array($page, $allowed_pages) && file_exists($page_file)) {
            include $page_file;
        } else {
            echo '<div class="error-page">';
            echo '<h2><i class="fas fa-exclamation-triangle"></i> 404 - Page Not Found</h2>';
            echo '<p>The page you are looking for does not exist.</p>';
            echo '<a href="index.php?page=dashboard" class="btn btn-primary">Go to Dashboard</a>';
            echo '</div>';
        }
        ?>
    </main>

    <!-- Footer -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2025 Accounting Pro. All rights reserved.</p>
            <p class="footer-info">
                <i class="fas fa-server"></i> Version 1.0.0 | 
                <i class="fas fa-clock"></i> <?php echo date('l, F j, Y g:i A'); ?>
            </p>
        </div>
    </footer>
    <?php endif; ?>

    <!-- JavaScript -->
    <script>
        // Theme Switcher
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const body = document.body;
        
        // Load saved theme
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            body.classList.add('dark-mode');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }

        // Toggle theme
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                body.classList.toggle('dark-mode');
                
                if (body.classList.contains('dark-mode')) {
                    localStorage.setItem('theme', 'dark');
                    themeIcon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    localStorage.setItem('theme', 'light');
                    themeIcon.classList.replace('fa-sun', 'fa-moon');
                }
            });
        }

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
