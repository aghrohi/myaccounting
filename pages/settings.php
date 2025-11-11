<?php
// pages/settings.php
requireLogin();
$is_admin = isAdmin();

// Get DB config from db_connect.php
global $db_config;
?>

<div class="page-header">
    <h1 class="page-title">Settings</h1>
    <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Settings</span>
    </div>
</div>

<div class="row" style="display: grid; grid-template-columns: 1fr 3fr; gap: var(--spacing-lg);">
    <div class="card">
        <div class="card-body" style="padding: var(--spacing-sm);">
            <ul class="nav-menu" style="list-style: none;">
                <li class="nav-item">
                    <a href="#profile" class="nav-link active">
                        <i class="fas fa-user"></i>
                        <span>Profile Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#preferences" class="nav-link">
                        <i class="fas fa-sliders-h"></i>
                        <span>Preferences</span>
                    </a>
                </li>
                <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a href="#system" class="nav-link">
                        <i class="fas fa-cogs"></i>
                        <span>System Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#environment" class="nav-link">
                        <i class="fas fa-server"></i>
                        <span>Environment</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#backup" class="nav-link">
                        <i class="fas fa-download"></i>
                        <span>Backup & Restore</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <div>
        <div id="profile" class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Profile Settings</h3>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?php echo clean($current_user['full_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo clean($current_user['email'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Change Password</label>
                        <input type="password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
        
        <div id="preferences" class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Preferences</h3>
            </div>
            <div class="card-body">
                <div class="form-check">
                    <input type="checkbox" class="form-checkbox" id="email_notifications" checked>
                    <label for="email_notifications" class="form-check-label">Email notifications for reports</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-checkbox" id="two_factor">
                    <label for="two_factor" class="form-check-label">Two-factor authentication (Coming Soon)</label>
                </div>
            </div>
        </div>

        <?php if ($is_admin): ?>
        <div id="system" class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">System Settings (Admin)</h3>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label class="form-label">Base Currency</label>
                        <select class="form-control form-select">
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-checkbox" id="allow_registration">
                        <label for="allow_registration" class="form-check-label">Allow new user registration</label>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save System Settings</button>
                </form>
            </div>
        </div>

        <div id="environment" class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">System Environment Details (Admin)</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td><strong>PHP Version</strong></td>
                                <td><?php echo phpversion(); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Server Software</strong></td>
                                <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Database Host</strong></td>
                                <td><?php echo $db_config['host']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Database Name</strong></td>
                                <td><?php echo $db_config['database']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Database User</strong></td>
                                <td><?php echo $db_config['username']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>App Environment</strong></td>
                                <td>
                                    <span class="badge <?php echo ENVIRONMENT === 'development' ? 'bg-warning' : 'bg-success'; ?>">
                                        <?php echo ENVIRONMENT; ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="backup" class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Backup & Restore (Admin)</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Create a backup of your database. This will download a .sql file of the entire database.</p>
                <a href="backup.php" class="btn btn-secondary">
                    <i class="fas fa-download"></i> Download SQL Backup
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
