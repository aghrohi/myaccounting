<?php
// Currencies Management Page (Admin Only)
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_currency') {
        $stmt = $pdo->prepare("INSERT INTO currencies (currency_code, currency_name, currency_symbol, exchange_rate) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['currency_code'],
            $_POST['currency_name'],
            $_POST['currency_symbol'],
            $_POST['exchange_rate']
        ]);
        $_SESSION['success'] = 'Currency added successfully!';
        header('Location: index.php?page=currencies');
        exit;
    }
}

$currencies = getCurrencies(false);
?>

<div class="page-header">
    <h1 class="page-title">Currency Management</h1>
</div>

<button class="btn btn-primary mb-3" onclick="document.getElementById('addForm').style.display='block'">
    <i class="fas fa-plus"></i> Add Currency
</button>

<div id="addForm" style="display:none;" class="card mb-3">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="add_currency">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Currency Code</label>
                    <input type="text" name="currency_code" class="form-control" maxlength="5" required placeholder="USD">
                </div>
                <div class="form-group">
                    <label class="form-label required">Currency Name</label>
                    <input type="text" name="currency_name" class="form-control" required placeholder="US Dollar">
                </div>
                <div class="form-group">
                    <label class="form-label">Symbol</label>
                    <input type="text" name="currency_symbol" class="form-control" placeholder="$">
                </div>
                <div class="form-group">
                    <label class="form-label required">Exchange Rate</label>
                    <input type="number" name="exchange_rate" class="form-control" step="0.0001" value="1.0000" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Add Currency</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Exchange Rate</th>
                        <th>Base Currency</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currencies as $currency): ?>
                    <tr>
                        <td><strong><?php echo clean($currency['currency_code']); ?></strong></td>
                        <td><?php echo clean($currency['currency_name']); ?></td>
                        <td><?php echo clean($currency['currency_symbol']); ?></td>
                        <td><?php echo number_format($currency['exchange_rate'], 4); ?></td>
                        <td>
                            <?php if ($currency['is_base_currency']): ?>
                            <span class="badge bg-success">Base</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $currency['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $currency['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
