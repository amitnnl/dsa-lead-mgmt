<?php
/**
 * Lead Form View (Create / Edit)
 */
$lead = $data['lead'] ?? null;
$mode = $data['mode'] ?? 'create';
$isEdit = $mode === 'edit';
?>

<div class="page-header">
    <div>
        <a href="index.php?page=leads" class="btn btn-ghost btn-xs"><i class="fas fa-arrow-left"></i> Back</a>
        <h2><?= $isEdit ? 'Edit Lead' : 'New Lead' ?></h2>
    </div>
</div>

<form method="POST" action="index.php?page=leads&action=<?= $isEdit ? 'update' : 'store' ?>" class="lead-form">
    <?= Security::csrfField() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= $lead['id'] ?>"><?php endif; ?>

    <div class="grid-2">
        <!-- Contact Info -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-user"></i> Contact Information</h3></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Customer Name <span class="required">*</span></label>
                        <input type="text" name="customer_name" class="form-input" value="<?= htmlspecialchars($lead['customer_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone_number" class="form-input" value="<?= htmlspecialchars($lead['phone_number'] ?? '') ?>" placeholder="9876543210">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Alt Phone</label>
                        <input type="tel" name="alt_phone" class="form-input" value="<?= htmlspecialchars($lead['alt_phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email_address" class="form-input" value="<?= htmlspecialchars($lead['email_address'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob" class="form-input" value="<?= $lead['dob'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Male','Female','Other'] as $g): ?>
                            <option value="<?= $g ?>" <?= ($lead['gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-map-marker-alt"></i> Address</h3></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-textarea" rows="2"><?= htmlspecialchars($lead['address'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-input" value="<?= htmlspecialchars($lead['city'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <input type="text" name="state" class="form-input" value="<?= htmlspecialchars($lead['state'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Pincode</label>
                    <input type="text" name="pincode" class="form-input" value="<?= htmlspecialchars($lead['pincode'] ?? '') ?>" maxlength="6">
                </div>
            </div>
        </div>

        <!-- Financial -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-rupee-sign"></i> Financial Details</h3></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Loan Type</label>
                        <select name="loan_type" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (LOAN_TYPES as $lt): ?>
                            <option value="<?= $lt ?>" <?= ($lead['loan_type'] ?? '') === $lt ? 'selected' : '' ?>><?= $lt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Loan Amount (₹)</label>
                        <input type="number" name="loan_amount" class="form-input" value="<?= $lead['loan_amount'] ?? '' ?>" step="1000">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Monthly Income (₹)</label>
                        <input type="number" name="monthly_income" class="form-input" value="<?= $lead['monthly_income'] ?? '' ?>" step="1000">
                    </div>
                    <div class="form-group">
                        <label>Employment Type</label>
                        <select name="employment_type" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Salaried','Self-Employed','Business','Retired','Other'] as $et): ?>
                            <option value="<?= $et ?>" <?= ($lead['employment_type'] ?? '') === $et ? 'selected' : '' ?>><?= $et ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Employer</label>
                        <input type="text" name="employer" class="form-input" value="<?= htmlspecialchars($lead['employer'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Credit Score</label>
                        <input type="number" name="credit_score" class="form-input" value="<?= $lead['credit_score'] ?? '' ?>" min="300" max="900">
                    </div>
                </div>
                <div class="form-group">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" class="form-input" value="<?= htmlspecialchars($lead['bank_name'] ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- Lead Management -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-cogs"></i> Lead Management</h3></div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Lead Source</label>
                        <select name="lead_source" class="form-select">
                            <?php foreach (LEAD_SOURCES as $src): ?>
                            <option value="<?= $src ?>" <?= ($lead['lead_source'] ?? 'Walk-in') === $src ? 'selected' : '' ?>><?= $src ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (LEAD_STATUSES as $s => $cfg): ?>
                            <option value="<?= $s ?>" <?= ($lead['status'] ?? 'New') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($data['agents'] as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= ($lead['assigned_to'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-input" value="<?= $lead['follow_up_date'] ?? '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-textarea" rows="3" placeholder="Any additional notes..."><?= htmlspecialchars($lead['remarks'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="index.php?page=leads" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update Lead' : 'Create Lead' ?></button>
    </div>
</form>
