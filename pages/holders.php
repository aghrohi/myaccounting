<?php
// Account Holders Management Page (Admin Only)
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_holder') {
        $stmt = $pdo->prepare("INSERT INTO account_holders (holder_name, holder_type, tax_id, address, phone, email, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['holder_name'],
            $_POST['holder_type'],
            $_POST['tax_id'],
            $_POST['address'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['notes']
        ]);
        $_SESSION['success'] = 'Account holder added successfully!';
        header('Location: index.php?page=holders');
        exit;
    }
}

$holders = getAccountHolders();
?>

<div class="page-header">
    <h1 class="page-title">Account Holders Management</h1>
</div>

<button class="btn btn-primary mb-3" onclick="document.getElementById('addForm').style.display='block'">
    <i class="fas fa-user-plus"></i> Add Account Holder
</button>

<div id="addForm" style="display:none;" class="card mb-3">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="add_holder">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label required">Holder Name</label>
                    <input type="text" name="holder_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Holder Type</label>
                    <select name="holder_type" class="form-control form-select" required>
                        <option value="Personal">Personal</option>
                        <option value="Joint">Joint</option>
                        <option value="Business">Business</option>
                        <option value="Trust">Trust</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Tax ID/SSN</label>
                    <input type="text" name="tax_id" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control form-textarea" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Holder</button>
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
                        <th>Tax ID</th>
                        <th>Contact</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($holders as $holder): ?>
                    <tr>
                        <td>
                            <strong><?php echo clean($holder['holder_name']); ?></strong>
                            <?php if ($holder['notes']): ?>
                            <br><small class="text-muted"><?php echo clean($holder['notes']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-primary"><?php echo $holder['holder_type']; ?></span>
                        </td>
                        <td><?php echo clean($holder['tax_id'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($holder['email']): ?>
                                <i class="fas fa-envelope"></i> <?php echo clean($holder['email']); ?><br>
                            <?php endif; ?>
                            <?php if ($holder['phone']): ?>
                                <i class="fas fa-phone"></i> <?php echo clean($holder['phone']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($holder['created_at']); ?></td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
