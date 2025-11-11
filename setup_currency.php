<?php
// pages/setup_currency.php - Currency Management (Admin Only)

// Security check
if (!$_SESSION['is_admin']) {
    header("Location: index.php?page=dashboard");
    exit;
}

// Fetch all currencies
$stmt = $pdo->query("
    SELECT c.*, COUNT(a.account_id) as account_count 
    FROM currencies c
    LEFT JOIN accounts a ON c.currency_id = a.currency_id
    GROUP BY c.currency_id
    ORDER BY c.currency_code
");
$all_currencies = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <i class="fas fa-dollar-sign"></i>
        <h1>Currency Management</h1>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
    <!-- Add New Currency Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-plus-circle"></i> Add New Currency
            </h3>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_currency">
            
            <div class="form-group">
                <label for="currency_code">Currency Code</label>
                <div class="form-icon-group">
                    <i class="fas fa-code"></i>
                    <input type="text" 
                           id="currency_code" 
                           name="currency_code" 
                           class="form-control" 
                           placeholder="e.g., USD, EUR"
                           required 
                           pattern="[A-Z]{3,5}"
                           maxlength="5"
                           style="text-transform: uppercase;"
                           title="Enter 3-5 letter currency code">
                </div>
            </div>

            <div class="form-group">
                <label for="currency_name">Currency Name</label>
                <div class="form-icon-group">
                    <i class="fas fa-font"></i>
                    <input type="text" 
                           id="currency_name" 
                           name="currency_name" 
                           class="form-control" 
                           placeholder="e.g., US Dollar"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="symbol">Symbol (Optional)</label>
                <div class="form-icon-group">
                    <i class="fas fa-money-bill"></i>
                    <input type="text" 
                           id="symbol" 
                           name="symbol" 
                           class="form-control" 
                           placeholder="e.g., $, €, £"
                           maxlength="5">
                </div>
            </div>

            <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center;">
                <i class="fas fa-plus"></i> Add Currency
            </button>
        </form>

        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);">
            <h4 style="font-size: 1rem; margin-bottom: 12px; color: var(--text-secondary);">
                <i class="fas fa-info-circle"></i> Common Currency Codes
            </h4>
            <div style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.8;">
                <strong>USD</strong> - US Dollar ($)<br>
                <strong>EUR</strong> - Euro (€)<br>
                <strong>GBP</strong> - British Pound (£)<br>
                <strong>JPY</strong> - Japanese Yen (¥)<br>
                <strong>CAD</strong> - Canadian Dollar (C$)<br>
                <strong>AUD</strong> - Australian Dollar (A$)<br>
                <strong>CHF</strong> - Swiss Franc (Fr)<br>
                <strong>CNY</strong> - Chinese Yuan (¥)
            </div>
        </div>
    </div>

    <!-- Currencies List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Available Currencies
            </h3>
            <span class="badge" style="background: var(--primary-light); color: var(--primary-color);">
                <?php echo count($all_currencies); ?> currencies
            </span>
        </div>

        <?php if (empty($all_currencies)): ?>
            <div class="empty-state">
                <i class="fas fa-dollar-sign"></i>
                <h3>No currencies added yet</h3>
                <p>Add your first currency to get started</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Used In</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_currencies as $currency): ?>
                        <tr>
                            <td>
                                <strong style="font-family: var(--font-mono);">
                                    <?php echo htmlspecialchars($currency['currency_code']); ?>
                                </strong>
                            </td>
                            <td><?php echo htmlspecialchars($currency['currency_name']); ?></td>
                            <td>
                                <span style="font-size: 1.25rem; color: var(--primary-color);">
                                    <?php echo htmlspecialchars($currency['symbol'] ?: '-'); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($currency['account_count'] > 0): ?>
                                    <span class="badge" style="background: var(--success-bg); color: var(--success-color);">
                                        <?php echo $currency['account_count']; ?> account<?php echo $currency['account_count'] != 1 ? 's' : ''; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Not used</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($currency['account_count'] == 0): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this currency?');">
                                        <input type="hidden" name="action" value="delete_currency">
                                        <input type="hidden" name="currency_id" value="<?php echo $currency['currency_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted" title="Cannot delete - currency in use">-</span>
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
            <i class="fas fa-exclamation-triangle"></i> Important Notes
        </h3>
    </div>
    <ul style="padding-left: 20px;">
        <li>Currencies cannot be deleted if they are being used by any account</li>
        <li>Currency codes should follow ISO 4217 standard (3-letter codes)</li>
        <li>The symbol field is optional and used for display purposes only</li>
        <li>All financial calculations are done in the account's designated currency</li>
    </ul>
</div>
