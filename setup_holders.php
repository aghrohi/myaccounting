<?php
// pages/setup_holders.php - Account Holders Management (Admin Only)

// Security check
if (!$_SESSION['is_admin']) {
    header("Location: index.php?page=dashboard");
    exit;
}

// Fetch all account holders with account count
$stmt = $pdo->query("
    SELECT ah.*, COUNT(a.account_id) as account_count 
    FROM account_holders ah
    LEFT JOIN accounts a ON ah.holder_id = a.holder_id
    GROUP BY ah.holder_id
    ORDER BY ah.holder_name
");
$all_holders = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <i class="fas fa-id-card"></i>
        <h1>Account Holders Management</h1>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Add New Holder Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-plus"></i> Add Account Holder
            </h3>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_holder">
            
            <div class="form-group">
                <label for="holder_name">Holder Name</label>
                <div class="form-icon-group">
                    <i class="fas fa-user"></i>
                    <input type="text" 
                           id="holder_name" 
                           name="holder_name" 
                           class="form-control" 
                           placeholder="e.g., Personal, Business, Joint"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="holder_type">Holder Type</label>
                <div class="form-icon-group">
                    <i class="fas fa-tag"></i>
                    <select id="holder_type" name="holder_type" class="form-control" required>
                        <option value="personal">Personal</option>
                        <option value="business">Business</option>
                        <option value="joint">Joint Account</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center;">
                <i class="fas fa-plus"></i> Add Holder
            </button>
        </form>

        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);">
            <h4 style="font-size: 1rem; margin-bottom: 12px; color: var(--text-secondary);">
                <i class="fas fa-info-circle"></i> What are Account Holders?
            </h4>
            <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6;">
                Account holders represent the ownership entity of your accounts. They help you organize accounts by owner, 
                such as separating personal accounts from business accounts or joint accounts.
            </p>
            <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6;">
                <strong>Examples:</strong><br>
                • Personal - Your individual accounts<br>
                • Spouse - Your spouse's accounts<br>
                • Joint - Shared family accounts<br>
                • Business LLC - Company accounts<br>
                • Freelance - Side business accounts
            </p>
        </div>
    </div>

    <!-- Holders List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Current Account Holders
            </h3>
            <span class="badge" style="background: var(--primary-light); color: var(--primary-color);">
                <?php echo count($all_holders); ?> holders
            </span>
        </div>

        <?php if (empty($all_holders)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No account holders yet</h3>
                <p>Add your first account holder to organize your accounts</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Holder Name</th>
                            <th>Type</th>
                            <th>Accounts</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_holders as $holder): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 36px; height: 36px; border-radius: 8px; background: var(--primary-light); color: var(--primary-color); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-<?php 
                                            echo $holder['holder_type'] == 'business' ? 'briefcase' : 
                                                ($holder['holder_type'] == 'joint' ? 'users' : 'user'); 
                                        ?>" style="font-size: 1rem;"></i>
                                    </div>
                                    <strong><?php echo htmlspecialchars($holder['holder_name']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: <?php 
                                    echo $holder['holder_type'] == 'business' ? '#fef3c7; color: #f59e0b' : 
                                        ($holder['holder_type'] == 'joint' ? '#dbeafe; color: #3b82f6' : '#d1fae5; color: #10b981'); 
                                ?>;">
                                    <?php echo ucfirst($holder['holder_type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($holder['account_count'] > 0): ?>
                                    <span class="badge" style="background: var(--success-bg); color: var(--success-color);">
                                        <?php echo $holder['account_count']; ?> account<?php echo $holder['account_count'] != 1 ? 's' : ''; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">No accounts</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted">
                                <?php echo date('M d, Y', strtotime($holder['created_at'])); ?>
                            </td>
                            <td>
                                <?php if ($holder['account_count'] == 0): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this account holder?');">
                                        <input type="hidden" name="action" value="delete_holder">
                                        <input type="hidden" name="holder_id" value="<?php echo $holder['holder_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted" title="Cannot delete - has associated accounts">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-lightbulb"></i> Tips for Organization
        </h3>
    </div>
    <ul style="padding-left: 20px;">
        <li>Use account holders to separate different aspects of your finances</li>
        <li>This helps in generating reports for specific entities (e.g., business vs personal expenses)</li>
        <li>Account holders cannot be deleted if they have associated accounts</li>
        <li>You can have multiple accounts under the same holder (e.g., multiple business accounts)</li>
    </ul>
</div>
