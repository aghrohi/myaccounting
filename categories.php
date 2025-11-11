<?php
// pages/categories.php - Category Management

// Fetch all categories with transaction count
$stmt = $pdo->query("
    SELECT c.*, 
           COUNT(t.transaction_id) as transaction_count,
           COALESCE(SUM(t.amount), 0) as total_amount
    FROM categories c
    LEFT JOIN transactions t ON c.category_id = t.category_id
    GROUP BY c.category_id
    ORDER BY c.category_type, c.category_name
");
$all_categories = $stmt->fetchAll();

// Separate by type
$income_categories = array_filter($all_categories, function($c) { return $c['category_type'] == 'credit'; });
$expense_categories = array_filter($all_categories, function($c) { return $c['category_type'] == 'debit'; });

// Available icons for categories
$available_icons = [
    'Income' => [
        'fa-money-check' => 'Salary',
        'fa-laptop' => 'Freelance',
        'fa-chart-line' => 'Investment',
        'fa-percentage' => 'Interest',
        'fa-dollar-sign' => 'Other Income',
        'fa-gift' => 'Gift',
        'fa-coins' => 'Bonus',
        'fa-handshake' => 'Business'
    ],
    'Expense' => [
        'fa-home' => 'Housing',
        'fa-car' => 'Transportation',
        'fa-utensils' => 'Food & Dining',
        'fa-shopping-bag' => 'Shopping',
        'fa-heartbeat' => 'Healthcare',
        'fa-film' => 'Entertainment',
        'fa-graduation-cap' => 'Education',
        'fa-piggy-bank' => 'Savings',
        'fa-exchange-alt' => 'Transfer',
        'fa-bolt' => 'Utilities',
        'fa-plane' => 'Travel',
        'fa-child' => 'Kids',
        'fa-paw' => 'Pets',
        'fa-dumbbell' => 'Fitness'
    ]
];

// Color palette
$colors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16'
];
?>

<div class="page-header">
    <div class="page-title">
        <i class="fas fa-tags"></i>
        <h1>Category Management</h1>
    </div>
    <button onclick="document.getElementById('addCategoryForm').style.display='block'" class="btn btn-success">
        <i class="fas fa-plus"></i> Add Category
    </button>
</div>

<!-- Add Category Form (Initially Hidden) -->
<div id="addCategoryForm" class="card" style="display: none; margin-bottom: 24px;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-plus-circle"></i> Add New Category
        </h3>
        <button onclick="this.parentElement.parentElement.style.display='none'" class="btn btn-sm btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </button>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="add_category">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group">
                <label for="category_name">Category Name *</label>
                <input type="text" 
                       id="category_name" 
                       name="category_name" 
                       class="form-control" 
                       placeholder="e.g., Groceries, Salary"
                       required>
            </div>

            <div class="form-group">
                <label for="category_type">Category Type *</label>
                <select id="category_type" name="category_type" class="form-control" required onchange="updateIconOptions(this.value)">
                    <option value="">Select type...</option>
                    <option value="credit">Income (Credit)</option>
                    <option value="debit">Expense (Debit)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="icon">Icon (Optional)</label>
                <select id="icon" name="icon" class="form-control">
                    <option value="">Select an icon...</option>
                </select>
            </div>

            <div class="form-group">
                <label for="color">Color (Optional)</label>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <input type="color" 
                           id="color" 
                           name="color" 
                           class="form-control"
                           value="#3b82f6"
                           style="height: 42px; padding: 4px;">
                    <div style="display: flex; gap: 4px;">
                        <?php foreach ($colors as $color): ?>
                            <button type="button" 
                                    onclick="document.getElementById('color').value='<?php echo $color; ?>'"
                                    style="width: 24px; height: 24px; background: <?php echo $color; ?>; border: none; border-radius: 4px; cursor: pointer;"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Create Category
        </button>
    </form>
</div>

<!-- Categories Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Income Categories -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-arrow-up" style="color: var(--success-color);"></i> Income Categories
            </h3>
            <span class="badge" style="background: var(--success-bg); color: var(--success-color);">
                <?php echo count($income_categories); ?> categories
            </span>
        </div>

        <?php if (empty($income_categories)): ?>
            <div class="empty-state">
                <i class="fas fa-plus-circle"></i>
                <p>No income categories yet</p>
            </div>
        <?php else: ?>
            <div style="max-height: 500px; overflow-y: auto;">
                <?php foreach ($income_categories as $category): ?>
                    <div style="display: flex; align-items: center; padding: 12px; border-bottom: 1px solid var(--border-light);">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: <?php echo $category['color'] ?: '#10b981'; ?>20; color: <?php echo $category['color'] ?: '#10b981'; ?>; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                            <i class="fas <?php echo $category['icon'] ?: 'fa-dollar-sign'; ?>"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600;">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </div>
                            <div style="font-size: 0.875rem; color: var(--text-muted);">
                                <?php echo $category['transaction_count']; ?> transactions 
                                • $<?php echo number_format($category['total_amount'], 2); ?> total
                            </div>
                        </div>
                        <?php if ($category['transaction_count'] == 0): ?>
                            <form method="POST" onsubmit="return confirm('Delete this category?');">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Expense Categories -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-arrow-down" style="color: var(--danger-color);"></i> Expense Categories
            </h3>
            <span class="badge" style="background: #fef2f2; color: var(--danger-color);">
                <?php echo count($expense_categories); ?> categories
            </span>
        </div>

        <?php if (empty($expense_categories)): ?>
            <div class="empty-state">
                <i class="fas fa-plus-circle"></i>
                <p>No expense categories yet</p>
            </div>
        <?php else: ?>
            <div style="max-height: 500px; overflow-y: auto;">
                <?php foreach ($expense_categories as $category): ?>
                    <div style="display: flex; align-items: center; padding: 12px; border-bottom: 1px solid var(--border-light);">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: <?php echo $category['color'] ?: '#ef4444'; ?>20; color: <?php echo $category['color'] ?: '#ef4444'; ?>; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                            <i class="fas <?php echo $category['icon'] ?: 'fa-tag'; ?>"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600;">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </div>
                            <div style="font-size: 0.875rem; color: var(--text-muted);">
                                <?php echo $category['transaction_count']; ?> transactions 
                                • $<?php echo number_format($category['total_amount'], 2); ?> total
                            </div>
                        </div>
                        <?php if ($category['transaction_count'] == 0): ?>
                            <form method="POST" onsubmit="return confirm('Delete this category?');">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Statistics -->
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-chart-pie"></i> Category Statistics
        </h3>
    </div>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
        <div style="text-align: center; padding: 16px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
                <?php echo count($all_categories); ?>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Total Categories</div>
        </div>
        <div style="text-align: center; padding: 16px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--success-color);">
                <?php echo count($income_categories); ?>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Income Categories</div>
        </div>
        <div style="text-align: center; padding: 16px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--danger-color);">
                <?php echo count($expense_categories); ?>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Expense Categories</div>
        </div>
        <div style="text-align: center; padding: 16px;">
            <div style="font-size: 2rem; font-weight: 700; color: var(--warning-color);">
                <?php echo array_sum(array_column($all_categories, 'transaction_count')); ?>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">Total Transactions</div>
        </div>
    </div>
</div>

<script>
function updateIconOptions(type) {
    const iconSelect = document.getElementById('icon');
    iconSelect.innerHTML = '<option value="">Select an icon...</option>';
    
    const icons = <?php echo json_encode($available_icons); ?>;
    const iconList = type === 'credit' ? icons['Income'] : (type === 'debit' ? icons['Expense'] : {});
    
    for (const [icon, label] of Object.entries(iconList)) {
        const option = document.createElement('option');
        option.value = icon;
        option.textContent = label;
        iconSelect.appendChild(option);
    }
}
</script>
