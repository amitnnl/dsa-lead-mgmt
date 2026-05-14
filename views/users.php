<?php
/**
 * Team / Users Management View (Admin only)
 */
?>

<div class="page-header">
    <h2><i class="fas fa-users-cog"></i> Team Management</h2>
</div>

<div class="grid-2">
    <!-- Add User -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-user-plus"></i> Add Team Member</h3></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=settings&action=add_user">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-select">
                        <option value="agent">Agent</option>
                        <option value="partner">Partner (Connector)</option>
                        <option value="dealer">Dealer</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reporting To (Manager)</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- No Manager (Top Level) --</option>
                        <?php foreach ($data['managers'] as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">Used for override commission calculations.</small>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-input" required minlength="8" placeholder="Min 8 chars, uppercase, lowercase, digit">
                    <small class="form-hint">Must have 8+ characters, at least 1 uppercase, 1 lowercase, 1 digit.</small>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add User</button>
            </form>
        </div>
    </div>

    <!-- Team List -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-users"></i> Team Members</h3></div>
        <div class="card-body">
            <div class="team-list">
                <?php foreach ($data['users'] as $u): ?>
                <div class="team-member <?= $u['is_active'] ? '' : 'inactive' ?>">
                    <div class="team-avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                    <div class="team-info">
                        <div class="team-name"><?= htmlspecialchars($u['name']) ?></div>
                        <div class="team-email"><?= htmlspecialchars($u['email']) ?></div>
                        <?php if ($u['manager_name']): ?>
                        <div class="team-manager" style="font-size:11px; color:#64748b; margin-top:2px">
                            <i class="fas fa-level-up-alt" style="transform:rotate(90deg)"></i> Under: <?= htmlspecialchars($u['manager_name']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
                    <?php if ($u['id'] != Security::userId()): ?>
                    <a href="index.php?page=settings&action=toggle_user&id=<?= $u['id'] ?>" 
                       class="btn btn-ghost btn-xs" title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>">
                        <i class="fas fa-<?= $u['is_active'] ? 'toggle-on text-success' : 'toggle-off text-danger' ?>"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
