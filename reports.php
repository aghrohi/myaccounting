<?php
// Reports Page
requireLogin();

$report_type = $_GET['report'] ?? '';
?>

<div class="page-header">
    <h1 class="page-title">Financial Reports</h1>
</div>

<div class="stats-grid">
    <a href="?page=reports&report=income_expense" class="stat-card" style="cursor: pointer;">
        <div class="stat-icon success">
            <i class="fas fa-chart-pie"></i>
        </div>
        <div class="stat-value">Income & Expense</div>
        <div class="stat-label">Monthly breakdown</div>
    </a>
    
    <a href="?page=reports&report=cash_flow" class="stat-card" style="cursor: pointer;">
        <div class="stat-icon primary">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-value">Cash Flow</div>
        <div class="stat-label">Money movement analysis</div>
    </a>
    
    <a href="?page=reports&report=balance_sheet" class="stat-card" style="cursor: pointer;">
        <div class="stat-icon warning">
            <i class="fas fa-balance-scale"></i>
        </div>
        <div class="stat-value">Balance Sheet</div>
        <div class="stat-label">Assets vs Liabilities</div>
    </a>
    
    <a href="?page=reports&report=tax" class="stat-card" style="cursor: pointer;">
        <div class="stat-icon danger">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-value">Tax Report</div>
        <div class="stat-label">Tax deductible expenses</div>
    </a>
</div>

<?php if ($report_type === 'income_expense'): ?>
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">Income & Expense Report</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="form-row mb-3">
            <input type="hidden" name="page" value="reports">
            <input type="hidden" name="report" value="income_expense">
            <div class="form-group">
                <label class="form-label">From Date</label>
                <input type="date" name="from" class="form-control" value="<?php echo $_GET['from'] ?? date('Y-m-01'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">To Date</label>
                <input type="date" name="to" class="form-control" value="<?php echo $_GET['to'] ?? date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </form>
        
        <?php if (isset($_GET['from']) && isset($_GET['to'])): ?>
            <?php $report_data = getIncomeExpenseReport($_GET['from'], $_GET['to']); ?>
            <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-lg);">
                <div>
                    <h4>Income</h4>
                    <table class="table">
                        <?php foreach ($report_data['income'] as $item): ?>
                        <tr>
                            <td><?php echo clean($item['category_name']); ?></td>
                            <td class="text-right"><?php echo formatMoney($item['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <th>Total Income</th>
                            <th class="text-right text-success"><?php echo formatMoney($report_data['totals']['income']); ?></th>
                        </tr>
                    </table>
                </div>
                <div>
                    <h4>Expenses</h4>
                    <table class="table">
                        <?php foreach ($report_data['expenses'] as $item): ?>
                        <tr>
                            <td><?php echo clean($item['category_name']); ?></td>
                            <td class="text-right"><?php echo formatMoney($item['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <th>Total Expenses</th>
                            <th class="text-right text-danger"><?php echo formatMoney($report_data['totals']['expenses']); ?></th>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="alert <?php echo $report_data['totals']['net'] >= 0 ? 'alert-success' : 'alert-warning'; ?>">
                <strong>Net Income: <?php echo formatMoney($report_data['totals']['net']); ?></strong>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
