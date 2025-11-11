<?php
// Settings Page
requireLogin();
$is_admin = isAdmin();
?>

<div class="page-header">
    <h1 class="page-title">Settings</h1>
</div>

<div class="row" style="display: grid; grid-template-columns: 1fr 2fr; gap: var(--spacing-lg);">
    <div class="card">
        <div class="card-body">
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: var(--spacing-sm);">
                    <a href="#profile" class="nav-link">Profile Settings</a>
                </li>
                <li style="margin-bottom: var(--spacing-sm);">
                    <a href="#preferences" class="nav-link">Preferences</a>
                </li>
                <?php if ($is_admin): ?>
                <li style="margin-bottom: var(--spacing-sm);">
                    <a href="#system" class="nav-link">System Settings</a>
                </li>
                <li style="margin-bottom: var(--spacing-sm);">
                    <a href="#backup" class="nav-link">Backup & Restore</a>
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
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo clean($current_user['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo clean($current_user['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Change Password</label>
                        <input type="password" class="form-control" placeholder="Leave blank to keep current">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
        
        <div id="preferences" class="card">
            <div class="card-header">
                <h3 class="card-title">Preferences</h3>
            </div>
            <div class="card-body">
                <div class="form-check">
                    <input type="checkbox" class="form-checkbox" id="email_notifications">
                    <label for="email_notifications" class="form-check-label">Email notifications</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-checkbox" id="two_factor">
                    <label for="two_factor" class="form-check-label">Two-factor authentication</label>
                </div>
            </div>
        </div>
    </div>
</div>
