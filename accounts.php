<?php
// pages/accounts.php - Account Management

// Calculate account balances
$stmt = $pdo->query("
    SELECT a.*, 
           ah.holder_name, ah.holder_type,
           c.currency_code, c.symbol,
           COALESCE(income.total, 0) as total_income,
           COALESCE(expense.total, 0) as total_expense,
           (a.starting_balance + COALESCE(income.total, 0) - COALESCE(expense.total, 0)) as current_balance,
           COUNT(DISTINCT t.transaction_id) as transaction_count
    FROM accounts a
    LEFT JOIN account_holders ah ON a.holder_id = ah.holder_id
    LEFT JOIN currencies c ON a.currency_id = c.currency_id
    LEFT JOIN (
        SELECT dest_account_id, SUM(amount) as total 
        FROM transactions 
        WHERE dest_account_id IS NOT NULL 
        GROUP BY dest_account_id
    ) income ON a.account_id = income.dest_account_id
    LEFT JOIN (
        SELECT source_account_id, SUM(amount) as total 
        FROM transactions 
        WHERE source_account_id IS NOT NULL 
        GROUP BY source_account_id
    ) expense ON a.account_id = expense.source_account_id
    LEFT JOIN transactions t ON (a.account_id = t.source_account_id OR a.account_id = t.dest_account_id)
    GROUP BY a.account_id
    ORDER BY a.account_name
");
$all_accounts = $stmt->fetchAll();

// Account type icons and colors
$account_types = [
    'checking' => ['icon' => 'fa-money-check', 'color' => '#3b82f6'],
    'savings' => ['icon' => 'fa-piggy-bank', 'color' => '#10b981'],
    'credit_card' => ['icon' => 'fa-credit-card', 'color' => '#ef4444'],
    'cash' => ['icon' => 'fa-wallet', 'color' => '#f59e0b'],
    'investment' => ['icon' => 'fa-chart-line', 'color' => '#8b5cf6']
];
?>

<div class="page-header">
    <div class="page-title">
        <i class="fas fa-wallet"></i>
        <h1>Account Management</h1>
    </div>
    <button onclick="document.getElementById('addAccountForm').style.display='block'" class="btn btn-success">
        <i class="fas fa-plus"></i> Add Account
    </button>
</div>

<!-- Add Account Form (Initially Hidden) -->
<div id="addAccountForm" class="card" style="display: none; margin-bottom: 24px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-plus-circle"></i> Add New Account
        </h3>
        <button onclick="this.parentElement.parentElement.style.display='none'" class="btn btn-sm btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </button>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="add_account">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="account_name">Account Name *</label>
                <input type="text" 
                       id="account_name" 
                       name="account_name" 
                       class="form-control" 
                       placeholder="e.g., Chase Checking"
                       required>
            </div>

            <div class="form-group">
                <label for="account_type">Account Type *</label>
                <select id="account_type" name="account_type" class="form-control" required>
                    <option value="checking">Checking Account</option>
                    <option value="savings">Savings Account</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="cash">Cash</option>
                    <option value="investment">Investment</option>
                </select>
            </div>

            <div class="form-group">
                <label for="holder_id">Account Holder *</label>
                <select id="holder_id" name="holder_id" class="form-control" required>
                    <option value="">Select holder...</option>
                    <?php foreach ($account_holders as $holder): ?>
                        <option value="<?php echo $holder['holder_id']; ?>">
                            <?php echo htmlspecialchars($holder['holder_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="currency_id">Currency *</label>
                <select id="currency_id" name="currency_id" class="form-control" required>
                    <?php foreach ($currencies as $currency): ?>
                        <option value="<?php echo $currency['currency_id']; ?>" <?php echo $currency['currency_code'] == 'USD' ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($currency['currency_code'] . ' - ' . $currency['currency_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="starting_amount">Starting Balance *</label>
                <input type="number" 
                       id="starting_amount" 
                       name="starting_amount" 
                       class="form-control" 
                       placeholder="0.00"
                       step="0.01"
                       value="0"
                       required>
            </div>

            <div class="form-group">
                <label for="creation_date">Opening Date *</label>
                <input type="date" 
                       id="creation_date" 
                       name="creation_date" 
                       class="form-control" 
                       value="<?php echo date('Y-m-d'); ?>"
                       required>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="details">Account Details (Optional)</label>
                <textarea id="details" 
                          name="details" 
                          class="form-control" 
                          rows="2"
                          placeholder="Additional information about this account..."></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Create Account
        </button>
    </form>
</div>

<!-- Accounts Grid -->
<div class="dashboard-grid">
    <?php foreach ($all_accounts as $account): 
        $type_info = $account_types[$account['account_type']] ?? ['icon' => 'fa-wallet', 'color' => '#64748b'];
    ?>
    <div class="card" style="position: relative; overflow: hidden;">
        <!-- Account Type Badge -->
        <div style="position: absolute; top: 16px; right: 16px;">
            <span class="badge" style="background: <?php echo $type_info['color']; ?>20; color: <?php echo $type_info['color']; ?>;">
                <?php echo ucwords(str_replace('_', ' ', $account['account_type'])); ?>
            </span>
        </div>

        <!-- Account Icon -->
        <div style="width: 48px; height: 48px; border-radius: 12px; background: <?php echo $type_info['color']; ?>20; color: <?php echo $type_info['color']; ?>; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
            <i class="fas <?php echo $type_info['icon']; ?>" style="font-size: 1.5rem;"></i>
        </div>

        <!-- Account Name -->
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 8px;">
            <?php echo htmlspecialchars($account['account_name']); ?>
        </h3>

        <!-- Holder Info -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
            <i class="fas fa-<?php echo $account['holder_type'] == 'business' ? 'briefcase' : ($account['holder_type'] == 'joint' ? 'users' : 'user'); ?>" style="font-size: 0.875rem; color: var(--text-muted);"></i>
            <span style="font-size: 0.875rem; color: var(--text-muted);">
                <?php echo htmlspecialchars($account['holder_name']); ?>
            </span>
        </div>

        <!-- Balance Info -->
        <div style="padding: 16px; background: var(--bg-tertiary); border-radius: 8px; margin-bottom: 16px;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px;">
                Current Balance
            </div>
            <div style="font-size: 1.75rem; font-weight: 700; color: <?php echo $account['current_balance'] < 0 ? 'var(--danger-color)' : 'var(--text-primary)'; ?>;">
                <?php echo $account['symbol'] . number_format($account['current_balance'], 2); ?>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 8px;">
                <?php echo $account['transaction_count']; ?> transaction<?php echo $account['transaction_count'] != 1 ? 's' : ''; ?>
            </div>
        </div>

        <!-- Account Details -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.875rem;">
            <div>
                <span style="color: var(--text-muted);">Starting:</span><br>
                <strong><?php echo $account['symbol'] . number_format($account['starting_balance'], 2); ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted);">Currency:</span><br>
                <strong><?php echo htmlspecialchars($account['currency_code']); ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted);">Income:</span><br>
                <strong style="color: var(--success-color);">+<?php echo $account['symbol'] . number_format($account['total_income'], 2); ?></strong>
            </div>
            <div>
                <span style="color: var(--text-muted);">Expense:</span><br>
                <strong style="color: var(--danger-color);">-<?php echo $account['symbol'] . number_format($account['total_expense'], 2); ?></strong>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-light);">
            <a href="index.php?page=transactions&account=<?php echo $account['account_id']; ?>" class="btn btn-sm btn-secondary" style="flex: 1; justify-content: center;">
                <i class="fas fa-list"></i> View Transactions
            </a>
            <?php if ($account['transaction_count'] == 0): ?>
                <form method="POST" style="flex: 1;" onsubmit="return confirm('Delete this account?');">
                    <input type="hidden" name="action" value="delete_account">
                    <input type="hidden" name="account_id" value="<?php echo $account['account_id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; justify-content: center;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if ($account['account_details']): ?>
            <div style="margin-top: 12px; padding: 8px; background: var(--bg-tertiary); border-radius: 6px;">
                <small style="color: var(--text-muted);">
                    <?php echo htmlspecialchars($account['account_details']); ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($all_accounts)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-wallet"></i>
            <h3>No accounts created yet</h3>
            <p>Create your first account to start tracking your finances</p>
            <button onclick="document.getElementById('addAccountForm').style.display='block'" class="btn btn-success mt-3">
                <i class="fas fa-plus"></i> Add Your First Account
            </button>
        </div>
    </div>
<?php endif; ?>
