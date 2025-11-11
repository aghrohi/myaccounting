<?php
// pages/dashboard.php - Main Dashboard

// Calculate statistics
$stats = [];

try {
    // Current month income
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE c.category_type = 'credit' 
        AND MONTH(transaction_date) = MONTH(CURRENT_DATE())
        AND YEAR(transaction_date) = YEAR(CURRENT_DATE())
    ");
    $stats['month_income'] = $stmt->fetch()['total'];

    // Current month expenses
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(amount), 0) as total 
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE c.category_type = 'debit' 
        AND t.source_account_id IS NOT NULL
        AND t.dest_account_id IS NULL
        AND MONTH(transaction_date) = MONTH(CURRENT_DATE())
        AND YEAR(transaction_date) = YEAR(CURRENT_DATE())
    ");
    $stats['month_expense'] = $stmt->fetch()['total'];

    // Calculate net income
    $stats['net_income'] = $stats['month_income'] - $stats['month_expense'];

    // Total account balances
    $stmt = $pdo->query("
        SELECT a.account_id, a.account_name, a.starting_balance,
               COALESCE(income.total, 0) as total_income,
               COALESCE(expense.total, 0) as total_expense,
               (a.starting_balance + COALESCE(income.total, 0) - COALESCE(expense.total, 0)) as current_balance,
               c.symbol
        FROM accounts a
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
        WHERE a.is_active = 1
        ORDER BY current_balance DESC
    ");
    $account_balances = $stmt->fetchAll();
    
    $stats['total_balance'] = array_sum(array_column($account_balances, 'current_balance'));
    $stats['account_count'] = count($account_balances);

    // Recent transactions
    $stmt = $pdo->query("
        SELECT t.*, 
               c.category_name, c.category_type, c.icon, c.color,
               src.account_name as source_name,
               dst.account_name as dest_name,
               u.username
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        LEFT JOIN accounts src ON t.source_account_id = src.account_id
        LEFT JOIN accounts dst ON t.dest_account_id = dst.account_id
        JOIN users u ON t.user_id = u.user_id
        ORDER BY t.transaction_date DESC
        LIMIT 10
    ");
    $recent_transactions = $stmt->fetchAll();

    // Top expense categories this month
    $stmt = $pdo->query("
        SELECT c.category_name, c.icon, c.color, SUM(t.amount) as total
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE c.category_type = 'debit'
        AND t.dest_account_id IS NULL
        AND MONTH(t.transaction_date) = MONTH(CURRENT_DATE())
        AND YEAR(t.transaction_date) = YEAR(CURRENT_DATE())
        GROUP BY c.category_id
        ORDER BY total DESC
        LIMIT 5
    ");
    $top_expenses = $stmt->fetchAll();

    // Monthly trend (last 6 months)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(CASE WHEN c.category_type = 'credit' THEN t.amount ELSE 0 END) as income,
            SUM(CASE WHEN c.category_type = 'debit' AND t.dest_account_id IS NULL THEN t.amount ELSE 0 END) as expense
        FROM transactions t
        JOIN categories c ON t.category_id = c.category_id
        WHERE t.transaction_date >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month
    ");
    $monthly_trend = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Error loading dashboard data: " . $e->getMessage();
}

// Format currency
function formatMoney($amount, $symbol = '$') {
    return $symbol . number_format(abs($amount), 2);
}

// Calculate percentage change
function getPercentageChange($current, $previous) {
    if ($previous == 0) return 0;
    return round((($current - $previous) / $previous) * 100, 1);
}
?>

<div class="page-header">
    <div class="page-title">
        <i class="fas fa-home"></i>
        <h1>Dashboard</h1>
    </div>
    <div>
        <span class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
    </div>
</div>

<!-- Statistics Cards -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-icon income">
            <i class="fas fa-arrow-up"></i>
        </div>
        <div class="stat-value"><?php echo formatMoney($stats['month_income']); ?></div>
        <div class="stat-label">This Month Income</div>
        <div class="stat-change positive">
            <i class="fas fa-trending-up"></i>
            +12.5% from last month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon expense">
            <i class="fas fa-arrow-down"></i>
        </div>
        <div class="stat-value"><?php echo formatMoney($stats['month_expense']); ?></div>
        <div class="stat-label">This Month Expenses</div>
        <div class="stat-change negative">
            <i class="fas fa-trending-up"></i>
            +5.2% from last month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon balance">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-value"><?php echo formatMoney($stats['total_balance']); ?></div>
        <div class="stat-label">Total Balance</div>
        <div class="stat-change <?php echo $stats['net_income'] >= 0 ? 'positive' : 'negative'; ?>">
            <i class="fas fa-<?php echo $stats['net_income'] >= 0 ? 'plus' : 'minus'; ?>"></i>
            <?php echo formatMoney($stats['net_income']); ?> this month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon accounts">
            <i class="fas fa-credit-card"></i>
        </div>
        <div class="stat-value"><?php echo $stats['account_count']; ?></div>
        <div class="stat-label">Active Accounts</div>
        <div class="stat-change positive">
            <i class="fas fa-check-circle"></i>
            All accounts active
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Recent Transactions
            </h3>
            <a href="index.php?page=transactions" class="btn btn-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($recent_transactions)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No transactions yet</h3>
                <p>Start by adding your first transaction</p>
                <a href="index.php?page=transactions" class="btn mt-3">Add Transaction</a>
            </div>
        <?php else: ?>
            <div class="transaction-list">
                <?php foreach ($recent_transactions as $tx): ?>
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <div class="transaction-icon" style="background: <?php echo $tx['color']; ?>20; color: <?php echo $tx['color']; ?>;">
                                <i class="fas <?php echo $tx['icon'] ?: 'fa-exchange-alt'; ?>"></i>
                            </div>
                            <div class="transaction-details">
                                <h4><?php echo htmlspecialchars($tx['category_name']); ?></h4>
                                <span>
                                    <?php 
                                    if ($tx['source_name'] && $tx['dest_name']) {
                                        echo htmlspecialchars($tx['source_name']) . ' → ' . htmlspecialchars($tx['dest_name']);
                                    } elseif ($tx['dest_name']) {
                                        echo 'Income → ' . htmlspecialchars($tx['dest_name']);
                                    } else {
                                        echo htmlspecialchars($tx['source_name']) . ' → Expense';
                                    }
                                    ?>
                                    • <?php echo date('M d, H:i', strtotime($tx['transaction_date'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="transaction-amount <?php echo $tx['category_type'] == 'credit' ? 'income' : 'expense'; ?>">
                            <?php echo $tx['category_type'] == 'credit' ? '+' : '-'; ?>
                            <?php echo formatMoney($tx['amount']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Top Expense Categories -->
    <div>
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Top Expenses
                </h3>
            </div>
            <?php if (empty($top_expenses)): ?>
                <div class="empty-state">
                    <i class="fas fa-chart-pie"></i>
                    <p>No expenses this month</p>
                </div>
            <?php else: ?>
                <div style="padding: 8px 0;">
                    <?php foreach ($top_expenses as $expense): ?>
                        <div style="display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-light);">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: <?php echo $expense['color']; ?>20; color: <?php echo $expense['color']; ?>; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                <i class="fas <?php echo $expense['icon'] ?: 'fa-tag'; ?>" style="font-size: 0.875rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; font-size: 0.875rem;"><?php echo htmlspecialchars($expense['category_name']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?php 
                                    $percentage = ($expense['total'] / $stats['month_expense']) * 100;
                                    echo number_format($percentage, 1) . '% of total';
                                    ?>
                                </div>
                            </div>
                            <div style="font-weight: 600;">
                                <?php echo formatMoney($expense['total']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Account Balances -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-wallet"></i> Account Balances
                </h3>
            </div>
            <div style="padding: 8px 0;">
                <?php foreach ($account_balances as $account): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-light);">
                        <div>
                            <div style="font-weight: 500; font-size: 0.875rem;">
                                <?php echo htmlspecialchars($account['account_name']); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                <?php 
                                $change = $account['current_balance'] - $account['starting_balance'];
                                echo ($change >= 0 ? '+' : '') . formatMoney($change, $account['symbol']);
                                ?> from start
                            </div>
                        </div>
                        <div style="font-weight: 600; color: <?php echo $account['current_balance'] < 0 ? 'var(--danger-color)' : 'var(--text-primary)'; ?>">
                            <?php echo formatMoney($account['current_balance'], $account['symbol']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trend Chart (Simple HTML/CSS representation) -->
<?php if (!empty($monthly_trend)): ?>
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-line"></i> Income vs Expenses Trend
        </h3>
    </div>
    <div style="padding: 24px;">
        <div style="display: flex; gap: 32px; align-items: flex-end; height: 200px;">
            <?php foreach ($monthly_trend as $month): ?>
                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%;">
                    <div style="flex: 1; width: 100%; display: flex; gap: 8px; align-items: flex-end;">
                        <div style="flex: 1; background: var(--success-color); border-radius: 4px 4px 0 0; height: <?php echo ($month['income'] / max(array_column($monthly_trend, 'income'))) * 100; ?>%; min-height: 4px;"></div>
                        <div style="flex: 1; background: var(--danger-color); border-radius: 4px 4px 0 0; height: <?php echo ($month['expense'] / max(array_column($monthly_trend, 'expense'))) * 100; ?>%; min-height: 4px;"></div>
                    </div>
                    <div style="margin-top: 8px; font-size: 0.75rem; color: var(--text-muted);">
                        <?php echo date('M', strtotime($month['month'] . '-01')); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="display: flex; gap: 24px; margin-top: 16px; justify-content: center;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 12px; height: 12px; background: var(--success-color); border-radius: 2px;"></div>
                <span style="font-size: 0.875rem; color: var(--text-muted);">Income</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 12px; height: 12px; background: var(--danger-color); border-radius: 2px;"></div>
                <span style="font-size: 0.875rem; color: var(--text-muted);">Expenses</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
