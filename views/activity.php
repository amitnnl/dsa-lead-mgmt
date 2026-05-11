<?php
/**
 * Activity Log View
 */
?>

<div class="page-header">
    <h2><i class="fas fa-history"></i> Activity Log</h2>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($data['activities'])): ?>
        <div class="activity-table">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Lead</th>
                        <th>Action</th>
                        <th>Change</th>
                        <th>Notes</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['activities'] as $act): ?>
                    <tr>
                        <td class="cell-date"><?= date('M j, g:i A', strtotime($act['created_at'])) ?></td>
                        <td>
                            <a href="index.php?page=leads&action=view&id=<?= $act['lead_id'] ?>">
                                <?= htmlspecialchars($act['customer_name'] ?? 'Lead #' . $act['lead_id']) ?>
                            </a>
                            <?php if ($act['phone_number']): ?>
                            <div class="cell-sub"><?= htmlspecialchars($act['phone_number']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="action-badge"><?= htmlspecialchars($act['action']) ?></span></td>
                        <td>
                            <?php if ($act['old_value']): ?>
                            <span class="badge badge-dim"><?= htmlspecialchars($act['old_value']) ?></span> →
                            <?php endif; ?>
                            <?php if ($act['new_value']): ?>
                            <span class="badge badge-info"><?= htmlspecialchars($act['new_value']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="cell-notes"><?= htmlspecialchars($act['notes'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($act['user_name'] ?? 'System') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($data['totalPages'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
            <a href="index.php?page=activity&p=<?= $i ?>" class="page-btn <?= $data['currentPage'] == $i ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state"><i class="fas fa-history"></i><h3>No activity recorded yet</h3><p>Activity will appear here as you work with leads</p></div>
        <?php endif; ?>
    </div>
</div>
