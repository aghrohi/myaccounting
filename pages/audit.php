<?php
// Audit Log Page (Admin Only)
requireAdmin();

$stmt = $pdo->query("
    SELECT al.*, u.username, u.full_name
    FROM audit_log al
    JOIN users u ON al.user_id = u.user_id
    ORDER BY al.created_at DESC
    LIMIT 100
");
$logs = $stmt->fetchAll();
?>

<div class="page-header">
    <h1 class="page-title">Audit Log</h1>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo formatDateTime($log['created_at']); ?></td>
                        <td><?php echo clean($log['full_name'] ?? $log['username']); ?></td>
                        <td>
                            <span class="badge bg-primary"><?php echo $log['action']; ?></span>
                        </td>
                        <td><?php echo clean($log['table_name'] ?? '-'); ?></td>
                        <td><?php echo $log['record_id'] ?? '-'; ?></td>
                        <td><code><?php echo clean($log['ip_address'] ?? 'Unknown'); ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
