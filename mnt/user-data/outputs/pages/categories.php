<?php
// Categories Management Page
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_category') {
        $stmt = $pdo->prepare("INSERT INTO categories (category_name, category_type, parent_category_id, icon, color, budget_amount, is_tax_deductible) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['category_name'],
            $_POST['category_type'],
            $_POST['parent_category_id'] ?: null,
            $_POST['icon'],
            $_POST['color'],
            $_POST['budget_amount'] ?: null,
            $_POST['is_tax_deductible'] ?? 0
        ]);
        $_SESSION['success'] = 'Category added successfully!';
        header('Location: index.php?page=categories');
        exit;
    }
}

$categories = getCategories(null, false);
?>

<div class="page-header">
    <h1 class="page-title">Categories</h1>
</div>

<button class="btn btn-primary mb-3" onclick="document.getElementById('addForm').style.display='block'">
    <i class="fas fa-plus"></i> Add Category
</button>

<div id="addForm" style="display:none;" class="card mb-3">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="add_category">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Category Name</label>
                    <input type="text" name="category_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Type</label>
                    <select name="category_type" class="form-control form-select" required>
                        <option value="credit">Income (Credit)</option>
                        <option value="debit">Expense (Debit)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon</label>
                    <input type="text" name="icon" class="form-control" placeholder="e.g., shopping-cart">
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" class="form-control" value="#3b82f6">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Category</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Budget</th>
                        <th>Tax Deductible</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <span class="badge" style="background: <?php echo $cat['color']; ?>">
                                <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
                                <?php echo clean($cat['category_name']); ?>
                            </span>
                        </td>
                        <td><?php echo $cat['category_type'] === 'credit' ? 'Income' : 'Expense'; ?></td>
                        <td><?php echo $cat['budget_amount'] ? formatMoney($cat['budget_amount']) : '-'; ?></td>
                        <td><?php echo $cat['is_tax_deductible'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <span class="badge <?php echo $cat['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
