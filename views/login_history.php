<?php
/**
 * Login History / Security Logs View (Admin only)
 */
?>

<div class="page-header">
    <h2><i class="fas fa-shield-alt"></i> Login History & Security</h2>
    <p>Monitor your team's access to ensure account security and prevent unauthorized logins.</p>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Recent Access Logs</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>IP Address</th>
                    <th>Device / Browser</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['logs'] as $log): ?>
                <tr>
                    <td><?= date('M j, Y g:i A', strtotime($log['created_at'])) ?></td>
                    <td><strong><?= htmlspecialchars($log['user_name']) ?></strong></td>
                    <td><span class="role-badge role-<?= $log['role'] ?>"><?= ucfirst($log['role']) ?></span></td>
                    <td><code class="text-primary"><?= $log['ip_address'] ?></code></td>
                    <td title="<?= htmlspecialchars($log['user_agent']) ?>">
                        <small class="text-muted" style="display:block; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap">
                            <?= htmlspecialchars($log['user_agent']) ?>
                        </small>
                    </td>
                    <td>
                        <?php 
                        $statusClass = match($log['status'] ?? 'Success') {
                            'Success' => 'badge-success',
                            'Failed' => 'badge-danger',
                            'Blocked (Lockout)' => 'badge-warning',
                            default => 'badge-secondary'
                        };
                        ?>
                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($log['status']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($data['logs'])): ?>
                <tr><td colspan="6" class="text-center">No login logs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top:24px; padding:16px; background:rgba(239,68,68,0.05); border-radius:8px; border:1px solid rgba(239,68,68,0.1)">
    <p style="font-size:12px; color:#ef4444; margin:0">
        <i class="fas fa-exclamation-triangle"></i> <strong>Security Tip:</strong> If you see a login from an unknown IP address or a strange device, we recommend deactivating the user account and resetting their password immediately.
    </p>
</div>
