<?php
// Accounts Management Page
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_account') {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO accounts 
                (account_name, account_number, account_type, holder_id, bank_name, 
                 account_details, starting_balance, current_balance, creation_date, currency_id, credit_limit)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $starting_balance = $_POST['starting_balance'];
            
            $stmt->execute([
                $_POST['account_name'],
                $_POST['account_number'],
                $_POST['account_type'],
                $_POST['holder_id'],
                $_POST['bank_name'],
                $_POST['account_details'],
                $starting_balance,
                $starting_balance, // Current balance starts as starting balance
                $_POST['creation_date'],
                $_POST['currency_id'],
                $_POST['credit_limit'] ?: null
            ]);
            
            $_SESSION['success'] = 'Account created successfully!';
            header('Location: index.php?page=accounts');
            exit;
        } catch (Exception $e) {
            $error = 'Error creating account: ' . $e->getMessage();
        }
    }
}

// Get all accounts with details
$stmt = $pdo->query("
    SELECT a.*, 
           ah.holder_name, ah.holder_type,
           c.currency_code, c.currency_symbol,
           (SELECT COUNT(*) FROM transactions WHERE source_account_id = a.account_id OR dest_account_id = a.account_id) as transaction_count
    FROM accounts a
    JOIN account_holders ah ON a.holder_id = ah.holder_id
    JOIN currencies c ON a.currency_id = c.currency_id
    ORDER BY a.account_name
");
$accounts = $stmt->fetchAll();

// Get holders and currencies for dropdowns
$holders = getAccountHolders();
$currencies = getCurrencies();

// Calculate total by account type
$totals_by_type = [];
foreach ($accounts as $account) {
    if (!isset($totals_by_type[$account['account_type']])) {
        $totals_by_type[$account['account_type']] = 0;
    }
    $totals_by_type[$account['account_type']] += $account['current_balance'];
}
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Accounts Management</h1>
    <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Accounts</span>
    </div>
</div>

<!-- Summary Cards -->
<div class="stats-grid">
    <?php
    $type_icons = [
        'Checking' => 'university',
        'Savings' => 'piggy-bank',
        'Credit Card' => 'credit-card',
        'Investment' => 'chart-line',
        'Cash' => 'money-bill-wave',
        'Loan' => 'hand-holding-usd'
    ];
    
    foreach ($totals_by_type as $type => $total):
    ?>
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-<?php echo $type_icons[$type] ?? 'wallet'; ?>"></i>
        </div>
        <div class="stat-value"><?php echo formatMoney(abs($total)); ?></div>
        <div class="stat-label"><?php echo $type; ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Account Button -->
<div style="margin-bottom: var(--spacing-lg); margin-top: var(--spacing-lg);">
    <button class="btn btn-primary" onclick="openAddAccountModal()">
        <i class="fas fa-plus"></i> Add New Account
    </button>
</div>

<!-- Accounts List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Accounts</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th>Holder</th>
                        <th>Bank</th>
                        <th>Account Number</th>
                        <th>Current Balance</th>
                        <th>Transactions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td>
                            <strong><?php echo clean($account['account_name']); ?></strong>
                            <?php if ($account['account_details']): ?>
                            <br><small class="text-muted"><?php echo clean($account['account_details']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo $account['account_type']; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo clean($account['holder_name']); ?>
                            <br><small class="text-muted"><?php echo $account['holder_type']; ?></small>
                        </td>
                        <td><?php echo clean($account['bank_name'] ?? 'N/A'); ?></td>
                        <td><code><?php echo clean($account['account_number'] ?? 'N/A'); ?></code></td>
                        <td class="<?php echo $account['current_balance'] < 0 ? 'text-danger' : 'text-success'; ?>">
                            <strong><?php echo formatMoney($account['current_balance'], $account['currency_symbol']); ?></strong>
                        </td>
                        <td><?php echo formatNumber($account['transaction_count']); ?></td>
                        <td>
                            <?php if ($account['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="index.php?page=transactions&account=<?php echo $account['account_id']; ?>" 
                                   class="btn btn-sm btn-secondary" title="View Transactions">
                                    <i class="fas fa-list"></i>
                                </a>
                                <button class="btn btn-sm btn-secondary" onclick="editAccount(<?php echo $account['account_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($account['transaction_count'] == 0): ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteAccount(<?php echo $account['account_id']; ?>)">
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

<!-- Add Account Modal -->
<div id="addAccountModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add New Account</h3>
            <button class="modal-close" onclick="closeAddAccountModal()">×</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_account">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Account Name</label>
                        <input type="text" name="account_name" class="form-control" required 
                               placeholder="e.g., Chase Checking">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Account Type</label>
                        <select name="account_type" class="form-control form-select" required>
                            <option value="">Select Type</option>
                            <option value="Checking">Checking</option>
                            <option value="Savings">Savings</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Investment">Investment</option>
                            <option value="Cash">Cash</option>
                            <option value="Loan">Loan</option>
                            <option value="Asset">Asset</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Account Holder</label>
                        <select name="holder_id" class="form-control form-select" required>
                            <option value="">Select Holder</option>
                            <?php foreach ($holders as $holder): ?>
                            <option value="<?php echo $holder['holder_id']; ?>">
                                <?php echo clean($holder['holder_name']); ?> 
                                (<?php echo $holder['holder_type']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" 
                               placeholder="e.g., Chase Bank">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control" 
                               placeholder="Last 4 digits recommended">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Currency</label>
                        <select name="currency_id" class="form-control form-select" required>
                            <?php foreach ($currencies as $currency): ?>
                            <option value="<?php echo $currency['currency_id']; ?>" 
                                    <?php echo $currency['is_base_currency'] ? 'selected' : ''; ?>>
                                <?php echo $currency['currency_code']; ?> - <?php echo $currency['currency_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Starting Balance</label>
                        <div class="input-group">
                            <div class="input-group-prepend">$</div>
                            <input type="number" name="starting_balance" class="form-control" 
                                   step="0.01" value="0.00" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Credit Limit (for credit cards)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">$</div>
                            <input type="number" name="credit_limit" class="form-control" 
                                   step="0.01" placeholder="Leave empty if not applicable">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Account Opening Date</label>
                    <input type="date" name="creation_date" class="form-control" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Account Details / Notes</label>
                    <textarea name="account_details" class="form-control form-textarea" 
                              rows="3" placeholder="Any additional information about this account"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddAccountModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddAccountModal() {
    document.getElementById('addAccountModal').classList.add('active');
}

function closeAddAccountModal() {
    document.getElementById('addAccountModal').classList.remove('active');
}

function editAccount(id) {
    // Implement edit functionality
    alert('Edit account #' + id);
}

function deleteAccount(id) {
    if (confirm('Are you sure you want to delete this account? This action cannot be undone.')) {
        window.location.href = '?page=accounts&action=delete&id=' + id;
    }
}
</script>