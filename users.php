<?php
// Users Management Page (Admin Only)
requireAdmin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_user') {
        if (!validatePassword($_POST['password'])) {
            $error = 'Password must be at least 8 characters with uppercase, lowercase, and numbers.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, is_admin) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['full_name'],
                $_POST['is_admin'] ?? 0
            ]);
            $_SESSION['success'] = 'User created successfully!';
            header('Location: index.php?page=users');
            exit;
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY username")->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">Users Management</h1>
</div>

<button class="btn btn-primary mb-3" onclick="document.getElementById('addUserForm').style.display='block'">
    <i class="fas fa-user-plus"></i> Add User
</button>

<div id="addUserForm" style="display:none;" class="card mb-3">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="add_user">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="form-check">
                <input type="checkbox" name="is_admin" value="1" class="form-checkbox" id="is_admin">
                <label for="is_admin" class="form-check-label">Administrator</label>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Create User</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo clean($user['username']); ?></td>
                        <td><?php echo clean($user['full_name']); ?></td>
                        <td><?php echo clean($user['email']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['is_admin'] ? 'bg-danger' : 'bg-primary'; ?>">
                                <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                            </span>
                        </td>
                        <td><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></td>
                        <td>
                            <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
