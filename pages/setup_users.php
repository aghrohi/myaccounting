<?php
// pages/setup_users.php - User Management (Admin Only)

// Security check - only admins can access this page
if (!$_SESSION['is_admin']) {
    header("Location: index.php?page=dashboard");
    exit;
}

// Fetch all users
$stmt = $pdo->query("
    SELECT user_id, username, is_admin, last_login, created_at 
    FROM users 
    ORDER BY username
");
$all_users = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <i class="fas fa-users"></i>
        <h1>User Management</h1>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Add New User Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-plus"></i> Add New User
            </h3>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_user">
            
            <div class="form-group">
                <label for="username">Username</label>
                <div class="form-icon-group">
                    <i class="fas fa-user"></i>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter username"
                           required 
                           pattern="[a-zA-Z0-9_]{3,50}"
                           title="Username must be 3-50 characters, letters, numbers, and underscores only">
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
                           placeholder="Enter password"
                           required
                           minlength="6">
                </div>
            </div>

            <div class="form-group">
                <label class="form-check-label">
                    <input type="checkbox" name="is_admin" value="1">
                    <span style="margin-left: 8px;">Grant Admin Privileges</span>
                </label>
                <small class="text-muted" style="display: block; margin-top: 8px;">
                    Admin users can manage other users, currencies, and account holders
                </small>
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center;">
                <i class="fas fa-plus"></i> Create User
            </button>
        </form>
    </div>

    <!-- Users List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Current Users
            </h3>
            <span class="badge" style="background: var(--primary-light); color: var(--primary-color);">
                <?php echo count($all_users); ?> users
            </span>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-light); color: var(--primary-color); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user" style="font-size: 0.875rem;"></i>
                                </div>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($user['user_id'] == $_SESSION['user_id']): ?>
                                    <span class="badge" style="background: var(--success-bg); color: var(--success-color);">You</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($user['is_admin']): ?>
                                <span class="badge badge-admin">Admin</span>
                            <?php else: ?>
                                <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary);">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if ($user['last_login']) {
                                $last_login = new DateTime($user['last_login']);
                                $now = new DateTime();
                                $diff = $now->diff($last_login);
                                
                                if ($diff->days == 0) {
                                    if ($diff->h == 0) {
                                        echo $diff->i . ' minutes ago';
                                    } else {
                                        echo $diff->h . ' hours ago';
                                    }
                                } elseif ($diff->days == 1) {
                                    echo 'Yesterday';
                                } elseif ($diff->days < 7) {
                                    echo $diff->days . ' days ago';
                                } else {
                                    echo date('M d, Y', strtotime($user['last_login']));
                                }
                            } else {
                                echo '<span class="text-muted">Never</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td>
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-info-circle"></i> Security Information
        </h3>
    </div>
    <ul style="padding-left: 20px;">
        <li>Passwords are encrypted using PHP's password_hash() function</li>
        <li>Admin users have full access to all system settings</li>
        <li>Regular users can only manage transactions and view reports</li>
        <li>You cannot delete your own user account</li>
    </ul>
</div>
