<?php
/**
 * Enhanced PHP Accounting Application
 * Version 2.0 - Main Controller
 * * This file handles routing, authentication, and page rendering
 */

// Start session with secure configuration
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// Include database connection
require_once 'db_connect.php';

// Include helper functions
require_once 'functions.php';

// Initialize variables
$page = $_GET['page'] ?? 'login';
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';

// Clear session messages after displaying
unset($_SESSION['message'], $_SESSION['error'], $_SESSION['success']);

// Authentication check
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $_SESSION['is_admin'] ?? false;

// Redirect to login if not authenticated
if (!$is_logged_in && $page !== 'login') {
    header('Location: index.php?page=login');
    exit;
}

// Redirect to dashboard if already logged in and trying to access login
if ($is_logged_in && $page === 'login') {
    header('Location: index.php?page=dashboard');
    exit;
}

// Handle logout
if ($page === 'logout') {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

// Load user data if logged in
$current_user = null;
if ($is_logged_in) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
    
    // Update last login
    if ($current_user && !isset($_SESSION['last_login_updated'])) {
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['last_login_updated'] = true;
    }
}

// Define allowed pages based on user role
// MODIFICATION: Added 'reconcile' to allowed pages for AJAX
$public_pages = ['login'];
$user_pages = ['dashboard', 'transactions', 'accounts', 'categories', 'reports', 'profile', 'reconcile'];
$admin_pages = ['users', 'currencies', 'holders', 'audit', 'settings'];

// Combine allowed pages
$allowed_pages = $public_pages;
if ($is_logged_in) {
    $allowed_pages = array_merge($allowed_pages, $user_pages);
    if ($is_admin) {
        $allowed_pages = array_merge($allowed_pages, $admin_pages);
    }
}

// Validate page request
if (!in_array($page, $allowed_pages)) {
    $page = '404';
}

// Handle AJAX requests
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    // --- MODIFICATION: Added AJAX Reconcile Handler ---
    if ($page === 'reconcile') {
        if (!$is_logged_in) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }
        try {
            $id = $_POST['id'] ?? 0;
            // Toggle the is_reconciled flag and set/unset the reconciled_date
            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET 
                    is_reconciled = NOT is_reconciled, 
                    reconciled_date = IF(is_reconciled = 1, NULL, NOW()) 
                WHERE transaction_id = ?
            ");
            $stmt->execute([$id]);
            
            // Get the new status
            $stmt = $pdo->prepare("SELECT is_reconciled FROM transactions WHERE transaction_id = ?");
            $stmt->execute([$id]);
            $new_status = $stmt->fetchColumn();
            
            echo json_encode(['success' => true, 'new_status' => $new_status]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    // --- End Modification ---
    
    $ajax_file = "ajax/{$page}.php";
    if (file_exists($ajax_file)) {
        include $ajax_file;
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
    exit;
}

// Get user initials for avatar
function getUserInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 2);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Professional PHP Accounting Application">
    <title><?php echo ucfirst($page); ?> - Accounting System</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : ''; ?>">

<?php if ($page === 'login'): ?>
    <div class="login-page">
        <div class="login-container">
            <?php include "pages/login.php"; ?>
        </div>
    </div>
<?php else: ?>
    <div class="app-layout">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="fas fa-chart-line"></i>
                    <span>AccuBooks</span>
                </div>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="index.php?page=dashboard" class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>">
                                <i class="fas fa-home"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=transactions" class="nav-link <?php echo $page === 'transactions' ? 'active' : ''; ?>">
                                <i class="fas fa-exchange-alt"></i>
                                <span>Transactions</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=accounts" class="nav-link <?php echo $page === 'accounts' ? 'active' : ''; ?>">
                                <i class="fas fa-wallet"></i>
                                <span>Accounts</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=categories" class="nav-link <?php echo $page === 'categories' ? 'active' : ''; ?>">
                                <i class="fas fa-tags"></i>
                                <span>Categories</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=reports" class="nav-link <?php echo $page === 'reports' ? 'active' : ''; ?>">
                                <i class="fas fa-chart-bar"></i>
                                <span>Reports</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <?php if ($is_admin): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Administration</div>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="index.php?page=users" class="nav-link <?php echo $page === 'users' ? 'active' : ''; ?>">
                                <i class="fas fa-users"></i>
                                <span>Users & Rights</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=currencies" class="nav-link <?php echo $page === 'currencies' ? 'active' : ''; ?>">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Currencies</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=holders" class="nav-link <?php echo $page === 'holders' ? 'active' : ''; ?>">
                                <i class="fas fa-user-tie"></i>
                                <span>Account Holders</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=audit" class="nav-link <?php echo $page === 'audit' ? 'active' : ''; ?>">
                                <i class="fas fa-history"></i>
                                <span>Audit Log</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=settings" class="nav-link <?php echo $page === 'settings' ? 'active' : ''; ?>">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </nav>
        
        <div class="main-content">
            <nav class="top-nav">
                <div class="nav-left">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search transactions, accounts...">
                    </div>
                </div>
                
                <div class="nav-right">
                    <button class="btn-icon">
                        <i class="fas fa-bell"></i>
                    </button>
                    
                    <button class="theme-toggle" id="themeToggle">
                        <i class="fas fa-sun sun-icon"></i>
                        <i class="fas fa-moon moon-icon"></i>
                    </button>
                    
                    <div class="nav-user">
                        <div class="user-info">
                            <span class="user-name"><?php echo clean($current_user['full_name'] ?? $current_user['username']); ?></span>
                            <span class="user-role"><?php echo $is_admin ? 'Administrator' : 'User'; ?></span>
                        </div>
                        <div class="user-avatar">
                            <?php echo getUserInitials($current_user['full_name'] ?? $current_user['username']); ?>
                        </div>
                        <div class="dropdown">
                            <button class="btn-icon">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="index.php?page=profile" class="dropdown-item">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                                <a href="index.php?page=settings" class="dropdown-item">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <hr class="dropdown-divider">
                                <a href="index.php?page=logout" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            
            <div class="content">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <div class="alert-content">
                        <div class="alert-message"><?php echo $error; ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <div class="alert-content">
                        <div class="alert-message"><?php echo $success; ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle alert-icon"></i>
                    <div class="alert-content">
                        <div class="alert-message"><?php echo $message; ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php
                $page_file = "pages/{$page}.php";
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    include "pages/404.php";
                }
                ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
// Theme Toggle
document.getElementById('themeToggle')?.addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    const isDarkMode = document.body.classList.contains('dark-mode');
    document.cookie = `theme=${isDarkMode ? 'dark' : 'light'};path=/;max-age=31536000`;
});

// Mobile Menu Toggle
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');
    
    if (window.innerWidth <= 1024 && 
        sidebar && menuToggle &&
        !sidebar.contains(event.target) && 
        !menuToggle.contains(event.target) &&
        sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
    }
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 300);
    });
}, 5000);

// Add active class to current page in navigation
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = '<?php echo $page; ?>';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(function(link) {
        if (link.href.includes('page=' + currentPage)) {
            link.classList.add('active');
        }
    });
});
</script>

</body>
</html>
