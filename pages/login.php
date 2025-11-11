<?php
// pages/login.php - Login Page
?>
<div class="login-container">
    <div class="login-card animate-fadeIn">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-chart-line"></i>
            </div>
            <h2 class="login-title">Welcome Back</h2>
            <p class="login-subtitle">Sign in to your accounting dashboard</p>
        </div>

        <form method="POST" action="index.php?page=login">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="username">Username</label>
                <div class="form-icon-group">
                    <i class="fas fa-user"></i>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter your username"
                           required 
                           autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="form-icon-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Enter your password"
                           required>
                </div>
            </div>

            <button type="submit" class="btn" style="width: 100%; justify-content: center;">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>

        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);">
            <p class="text-muted text-center" style="font-size: 0.875rem;">
                <strong>Demo Credentials:</strong><br>
                Username: admin | Password: password123
            </p>
        </div>
    </div>
</div>
