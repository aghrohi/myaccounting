<?php
// Transactions Page
requireLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_transaction') {
        try {
            $data = [
                'source_account_id' => $_POST['source_account_id'] === '0' ? null : $_POST['source_account_id'],
                'dest_account_id' => $_POST['dest_account_id'] === '0' ? null : $_POST['dest_account_id'],
                'category_id' => $_POST['category_id'],
                'transaction_date' => $_POST['transaction_date'],
                'amount' => $_POST['amount'],
                'details' => $_POST['details'],
                'notes' => $_POST['notes'] ?? null
            ];
            
            $transaction_id = createTransaction($data);
            $_SESSION['success'] = 'Transaction added successfully!';
            header('Location: index.php?page=transactions');
            exit;
        } catch (Exception $e) {
            $error = 'Error adding transaction: ' . $e->getMessage();
        }
    }
}

// Get filter parameters
$filter_account = $_GET['account'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_date_from = $_GET['date_from'] ?? date('Y-m-01');
$filter_date_to = $_GET['date_to'] ?? date('Y-m-d');
$page_num = max(1, intval($_GET['p'] ?? 1));

// Build query
$query = "
    SELECT t.*, c.category_name, c.category_type, c.icon, c.color,
           sa.account_name as source_account, 
           da.account_name as dest_account,
           u.username, u.full_name
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    JOIN users u ON t.user_id = u.user_id
    LEFT JOIN accounts sa ON t.source_account_id = sa.account_id
    LEFT JOIN accounts da ON t.dest_account_id = da.account_id
    WHERE 1=1
";

$params = [];

if ($filter_date_from) {
    $query .= " AND t.transaction_date >= ?";
    $params[] = $filter_date_from . ' 00:00:00';
}

if ($filter_date_to) {
    $query .= " AND t.transaction_date <= ?";
    $params[] = $filter_date_to . ' 23:59:59';
}

if ($filter_account) {
    $query .= " AND (t.source_account_id = ? OR t.dest_account_id = ?)";
    $params[] = $filter_account;
    $params[] = $filter_account;
}

if ($filter_category) {
    $query .= " AND t.category_id = ?";
    $params[] = $filter_category;
}

$query .= " ORDER BY t.transaction_date DESC, t.transaction_id DESC";

// Paginate results
$result = paginate($query, $params, $page_num, 20);
$transactions = $result['data'];

// Get accounts and categories for dropdowns
$accounts = getAccounts();
$categories = getCategories();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Transactions</h1>
    <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Transactions</span>
    </div>
</div>

<!-- Add Transaction Button -->
<div style="margin-bottom: var(--spacing-lg);">
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Add Transaction
    </button>
    <button class="btn btn-secondary" onclick="toggleFilters()">
        <i class="fas fa-filter"></i> Filters
    </button>
    <a href="?page=transactions&export=csv" class="btn btn-secondary">
        <i class="fas fa-download"></i> Export CSV
    </a>
</div>

<!-- Filters -->
<div id="filters" class="card" style="display: none; margin-bottom: var(--spacing-lg);">
    <div class="card-body">
        <form method="GET" action="">
            <input type="hidden" name="page" value="transactions">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" value="<?php echo $filter_date_from; ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" value="<?php echo $filter_date_to; ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Account</label>
                    <select name="account" class="form-control form-select">
                        <option value="">All Accounts</option>
                        <?php foreach ($accounts as $account): ?>
                        <option value="<?php echo $account['account_id']; ?>" 
                                <?php echo $filter_account == $account['account_id'] ? 'selected' : ''; ?>>
                            <?php echo clean($account['account_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"
                                <?php echo $filter_category == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo clean($category['category_name']); ?>
                            (<?php echo $category['category_type'] === 'credit' ? 'Income' : 'Expense'; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="index.php?page=transactions" class="btn btn-secondary">Clear</a>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ref #</th>
                        <th>Date</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Category</th>
                        <th>Details</th>
                        <th>Amount</th>
                        <th>By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="9" class="text-center">No transactions found</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td>
                                <code><?php echo clean($transaction['transaction_ref']); ?></code>
                            </td>
                            <td><?php echo formatDateTime($transaction['transaction_date']); ?></td>
                            <td>
                                <?php echo $transaction['source_account'] ? clean($transaction['source_account']) : '<span class="text-muted">External</span>'; ?>
                            </td>
                            <td>
                                <?php echo $transaction['dest_account'] ? clean($transaction['dest_account']) : '<span class="text-muted">External</span>'; ?>
                            </td>
                            <td>
                                <span class="badge" style="background-color: <?php echo $transaction['color']; ?>">
                                    <i class="fas fa-<?php echo $transaction['icon']; ?>"></i>
                                    <?php echo clean($transaction['category_name']); ?>
                                </span>
                            </td>
                            <td><?php echo clean($transaction['details']); ?></td>
                            <td class="<?php echo $transaction['category_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $transaction['category_type'] === 'credit' ? '+' : '-'; ?>
                                <?php echo formatMoney($transaction['amount']); ?>
                            </td>
                            <td>
                                <small><?php echo clean($transaction['full_name'] ?? $transaction['username']); ?></small>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="viewTransaction(<?php echo $transaction['transaction_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($transaction['is_reconciled'] == 0): ?>
                                    <button class="btn btn-sm btn-success" onclick="reconcileTransaction(<?php echo $transaction['transaction_id']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($result['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                Showing <?php echo (($page_num - 1) * 20) + 1; ?> to 
                <?php echo min($page_num * 20, $result['total']); ?> of 
                <?php echo $result['total']; ?> entries
            </div>
            <div style="display: flex; gap: var(--spacing-xs);">
                <?php if ($page_num > 1): ?>
                <a href="?page=transactions&p=<?php echo $page_num - 1; ?>" class="btn btn-sm btn-secondary">Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page_num - 2); $i <= min($result['total_pages'], $page_num + 2); $i++): ?>
                <a href="?page=transactions&p=<?php echo $i; ?>" 
                   class="btn btn-sm <?php echo $i == $page_num ? 'btn-primary' : 'btn-secondary'; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page_num < $result['total_pages']): ?>
                <a href="?page=transactions&p=<?php echo $page_num + 1; ?>" class="btn btn-sm btn-secondary">Next</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Add Transaction Modal -->
<div id="addModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add Transaction</h3>
            <button class="modal-close" onclick="closeAddModal()">×</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="add_transaction">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Transaction Date</label>
                        <input type="datetime-local" name="transaction_date" class="form-control" 
                               value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Category</label>
                        <select name="category_id" class="form-control form-select" required onchange="updateAccountFields(this)">
                            <option value="">Select Category</option>
                            <optgroup label="Income">
                                <?php foreach ($categories as $cat): ?>
                                    <?php if ($cat['category_type'] === 'credit'): ?>
                                    <option value="<?php echo $cat['category_id']; ?>">
                                        <?php echo clean($cat['category_name']); ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Expenses">
                                <?php foreach ($categories as $cat): ?>
                                    <?php if ($cat['category_type'] === 'debit'): ?>
                                    <option value="<?php echo $cat['category_id']; ?>" data-type="<?php echo $cat['category_type']; ?>">
                                        <?php echo clean($cat['category_name']); ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">From Account</label>
                        <select name="source_account_id" id="source_account" class="form-control form-select">
                            <option value="0">External (Income)</option>
                            <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['account_id']; ?>">
                                <?php echo clean($account['account_name']); ?> 
                                (<?php echo formatMoney($account['current_balance']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">To Account</label>
                        <select name="dest_account_id" id="dest_account" class="form-control form-select">
                            <option value="0">External (Expense)</option>
                            <?php foreach ($accounts as $account): ?>
                            <option value="<?php echo $account['account_id']; ?>">
                                <?php echo clean($account['account_name']); ?>
                                (<?php echo formatMoney($account['current_balance']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Amount</label>
                        <div class="input-group">
                            <div class="input-group-prepend">$</div>
                            <input type="number" name="amount" class="form-control" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Description</label>
                        <input type="text" name="details" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="notes" class="form-control form-textarea" rows="3"></textarea>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Transaction
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.add('active');
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('active');
}

function toggleFilters() {
    const filters = document.getElementById('filters');
    filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
}

function viewTransaction(id) {
    // Implement view transaction details
    alert('View transaction #' + id);
}

function reconcileTransaction(id) {
    if (confirm('Mark this transaction as reconciled?')) {
        // Implement reconciliation
        window.location.href = '?page=transactions&action=reconcile&id=' + id;
    }
}

function updateAccountFields(select) {
    // Auto-select account fields based on category type
    // This is a helper function to improve UX
}
</script>