<?php
// Dashboard Page
requireLogin();

// Get dashboard statistics
$stats = getDashboardStats();

// Get recent transactions
$stmt = $pdo->query("
    SELECT t.*, c.category_name, c.category_type,
           sa.account_name as source_account, 
           da.account_name as dest_account,
           u.username
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    JOIN users u ON t.user_id = u.user_id
    LEFT JOIN accounts sa ON t.source_account_id = sa.account_id
    LEFT JOIN accounts da ON t.dest_account_id = da.account_id
    ORDER BY t.transaction_date DESC
    LIMIT 10
");
$recent_transactions = $stmt->fetchAll();

// Get account balances
$stmt = $pdo->query("
    SELECT a.*, c.currency_symbol, ah.holder_name
    FROM accounts a
    JOIN currencies c ON a.currency_id = c.currency_id
    JOIN account_holders ah ON a.holder_id = ah.holder_id
    WHERE a.is_active = 1
    ORDER BY a.current_balance DESC
    LIMIT 5
");
$top_accounts = $stmt->fetchAll();

// Get monthly income/expense data for chart
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(transaction_date, '%Y-%m') as month,
        SUM(CASE WHEN c.category_type = 'credit' THEN t.amount ELSE 0 END) as income,
        SUM(CASE WHEN c.category_type = 'debit' THEN t.amount ELSE 0 END) as expenses
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    WHERE t.transaction_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
    ORDER BY month
");
$monthly_data = $stmt->fetchAll();

// Get category distribution
$stmt = $pdo->query("
    SELECT 
        c.category_name,
        c.color,
        SUM(t.amount) as total
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    WHERE c.category_type = 'debit'
    AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
    GROUP BY c.category_id
    ORDER BY total DESC
    LIMIT 5
");
$expense_distribution = $stmt->fetchAll();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <div class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="breadcrumb-separator">/</span>
        <span>Dashboard</span>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-value"><?php echo formatMoney($stats['total_balance']); ?></div>
        <div class="stat-label">Total Balance</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-value"><?php echo formatMoney($stats['month_income']); ?></div>
        <div class="stat-label">This Month Income</div>
        <?php 
        $last_month_income = $stats['month_income'] * 0.9; // Example calculation
        $change = (($stats['month_income'] - $last_month_income) / $last_month_income) * 100;
        ?>
        <div class="stat-change <?php echo $change > 0 ? 'positive' : 'negative'; ?>">
            <i class="fas fa-trending-<?php echo $change > 0 ? 'up' : 'down'; ?>"></i>
            <?php echo abs(round($change, 1)); ?>%
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="stat-value"><?php echo formatMoney($stats['month_expenses']); ?></div>
        <div class="stat-label">This Month Expenses</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-exchange-alt"></i>
        </div>
        <div class="stat-value"><?php echo formatNumber($stats['recent_transactions']); ?></div>
        <div class="stat-label">Recent Transactions</div>
    </div>
</div>

<!-- Charts Row -->
<div class="row" style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--spacing-lg);">
    <!-- Income vs Expenses Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Income vs Expenses</h3>
            <p class="card-subtitle">Last 6 months trend</p>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Expense Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Expense Distribution</h3>
            <p class="card-subtitle">This month</p>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="expenseChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="row" style="display: grid; grid-template-columns: 3fr 2fr; gap: var(--spacing-lg); margin-top: var(--spacing-lg);">
    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Transactions</h3>
            <a href="index.php?page=transactions" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $transaction): ?>
                        <tr>
                            <td><?php echo formatDate($transaction['transaction_date']); ?></td>
                            <td><?php echo clean($transaction['details']); ?></td>
                            <td>
                                <span class="badge <?php echo $transaction['category_type'] === 'credit' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo clean($transaction['category_name']); ?>
                                </span>
                            </td>
                            <td class="<?php echo $transaction['category_type'] === 'credit' ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $transaction['category_type'] === 'credit' ? '+' : '-'; ?>
                                <?php echo formatMoney($transaction['amount']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Top Accounts -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Account Balances</h3>
            <a href="index.php?page=accounts" class="btn btn-sm btn-primary">Manage</a>
        </div>
        <div class="card-body">
            <?php foreach ($top_accounts as $account): ?>
            <div style="margin-bottom: var(--spacing-md); padding-bottom: var(--spacing-md); border-bottom: 1px solid var(--border-color);">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <strong><?php echo clean($account['account_name']); ?></strong>
                        <div style="font-size: 0.875rem; color: var(--text-muted);">
                            <?php echo clean($account['holder_name']); ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <strong><?php echo formatMoney($account['current_balance'], $account['currency_symbol']); ?></strong>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Chart Scripts -->
<script>
// Income vs Expenses Chart
const ctx1 = document.getElementById('incomeExpenseChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
        datasets: [{
            label: 'Income',
            data: <?php echo json_encode(array_column($monthly_data, 'income')); ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4
        }, {
            label: 'Expenses',
            data: <?php echo json_encode(array_column($monthly_data, 'expenses')); ?>,
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Expense Distribution Chart
const ctx2 = document.getElementById('expenseChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($expense_distribution, 'category_name')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($expense_distribution, 'total')); ?>,
            backgroundColor: <?php echo json_encode(array_column($expense_distribution, 'color')); ?>
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>