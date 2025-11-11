<?php
// Login Page
// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Log the login
            logActivity('LOGIN', 'users', $user['user_id']);
            
            // Update last login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
            
            // Redirect to dashboard
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<div class="login-card">
    <div class="login-header">
        <div class="login-logo">
            <i class="fas fa-chart-line"></i>
        </div>
        <h1 class="login-title">Welcome Back</h1>
        <p class="login-subtitle">Sign in to your account to continue</p>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle alert-icon"></i>
        <div class="alert-content">
            <div class="alert-message"><?php echo $error; ?></div>
        </div>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username" class="form-label">Username</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <i class="fas fa-user"></i>
                </div>
                <input type="text" id="username" name="username" class="form-control" 
                       placeholder="Enter your username" required autofocus>
            </div>
        </div>
        
        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <i class="fas fa-lock"></i>
                </div>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required>
            </div>
        </div>
        
        <div class="form-check mb-3">
            <input type="checkbox" id="remember" name="remember" class="form-checkbox">
            <label for="remember" class="form-check-label">Remember me</label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
    </form>
    
    <div class="mt-3 text-center">
        <small class="text-muted">
            Demo Credentials:<br>
            Admin: admin / Admin@123<br>
            User: user1 / User@123
        </small>
    </div>
</div>