<?php
/**
 * Settings / Profile View
 */
$user = $data['user'] ?? [];
?>

<div class="page-header">
    <h2><i class="fas fa-user-cog"></i> My Profile</h2>
</div>

<div class="grid-2">
    <!-- Profile Info -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-user"></i> Profile Information</h3></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=settings&action=update_profile">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                    <small class="form-hint">Email cannot be changed</small>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" class="form-input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" class="form-input" value="<?= ucfirst($user['role'] ?? 'agent') ?>" disabled>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-lock"></i> Change Password</h3></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=settings&action=change_password">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-input" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Change Password</button>
            </form>
        </div>
    </div>
</div>
