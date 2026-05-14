<?php
/**
 * Import Column Mapping View
 */
$headers = $data['headers'] ?? [];
$dbColumns = $data['db_columns'] ?? [];
?>

<div class="page-header">
    <div>
        <a href="index.php?page=import" class="btn btn-ghost btn-xs"><i class="fas fa-arrow-left"></i> Back</a>
        <h2>Map Columns — <?= htmlspecialchars($data['filename'] ?? '') ?></h2>
        <div style="display:flex;gap:10px;margin-top:6px;align-items:center">
            <span class="badge badge-info"><i class="fas fa-table"></i> <?= number_format($data['row_count'] ?? 0) ?> rows detected</span>
            <?php if (!empty($data['is_large'])): ?>
            <span class="badge badge-warning"><i class="fas fa-bolt"></i> Large file — background processing</span>
            <?php endif; ?>
            
            <?php if (count($data['sheets'] ?? []) > 1): ?>
            <div style="margin-left:auto; display:flex; align-items:center; gap:10px">
                <label style="font-size:12px; font-weight:600; color:#94a3b8">SELECT SHEET:</label>
                <form action="index.php?page=import&action=upload" method="POST" id="sheet-selector-form" style="margin:0">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="reselect_file" value="<?= htmlspecialchars($_SESSION['import_file'] ?? '') ?>">
                    <select name="sheet" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:150px">
                        <?php foreach ($data['sheets'] as $name => $idx): ?>
                        <option value="<?= $idx ?>" <?= ($data['current_sheet'] == $idx) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<form method="POST" action="index.php?page=import&action=process">
    <?= Security::csrfField() ?>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-columns"></i> Column Mapping</h3>
            <p class="card-desc">Match each column from your file to the corresponding system field</p>
        </div>
        <div class="card-body">
            <div class="mapping-grid">
                <?php foreach ($headers as $i => $header): ?>
                <div class="mapping-row">
                    <div class="mapping-source">
                        <span class="mapping-index"><?= $i + 1 ?></span>
                        <span class="mapping-header"><?= htmlspecialchars($header) ?></span>
                    </div>
                    <div class="mapping-arrow"><i class="fas fa-arrow-right"></i></div>
                    <div class="mapping-target">
                        <select name="mapping[<?= $i ?>]" class="form-select">
                            <?php
                            // Auto-match by header name similarity
                            $headerLower = strtolower(trim($header));
                            $autoMatch = 'skip';
                            $matchMap = [
                                'customer_name' => ['name', 'customer name', 'customer_name', 'full name', 'applicant name', 'applicant'],
                                'phone_number' => ['phone', 'mobile', 'contact', 'phone number', 'phone_number', 'mobile number', 'mobile no', 'contact no'],
                                'alt_phone' => ['alt phone', 'alternate phone', 'alt_phone', 'secondary phone'],
                                'email_address' => ['email', 'email address', 'email_address', 'e-mail'],
                                'city' => ['city', 'location'],
                                'state' => ['state', 'province'],
                                'pincode' => ['pincode', 'pin code', 'zip', 'postal code'],
                                'address' => ['address', 'residential address'],
                                'loan_type' => ['loan type', 'loan_type', 'product', 'product type'],
                                'loan_amount' => ['loan amount', 'loan_amount', 'amount', 'required amount'],
                                'monthly_income' => ['income', 'monthly income', 'monthly_income', 'salary'],
                                'employer' => ['employer', 'company', 'organization', 'company name'],
                                'employment_type' => ['employment', 'employment type', 'employment_type', 'emp type'],
                                'credit_score' => ['credit score', 'credit_score', 'cibil', 'cibil score'],
                                'bank_name' => ['bank', 'bank name', 'bank_name'],
                                'account_number' => ['account number', 'account_number', 'account no', 'a/c number', 'a/c no', 'acc number'],
                                'ifsc_code' => ['ifsc', 'ifsc code', 'ifsc_code', 'ifsc no'],
                                'dob' => ['dob', 'date of birth', 'birth date'],
                                'gender' => ['gender', 'sex'],
                                'father_name' => ['father name', 'father_name', "father's name", 's/o', 'son of', 'daughter of', 'd/o', 'guardian', 'guardian name'],
                                'remarks' => ['remarks', 'notes', 'comment', 'comments'],
                            ];
                            foreach ($matchMap as $dbCol => $keywords) {
                                foreach ($keywords as $kw) {
                                    if ($headerLower === $kw || str_contains($headerLower, $kw)) {
                                        $autoMatch = $dbCol; break 2;
                                    }
                                }
                            }
                            foreach ($dbColumns as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $autoMatch === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Import Options -->
    <div class="card" style="margin-top:20px">
        <div class="card-header"><h3><i class="fas fa-sliders-h"></i> Import Options</h3></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Default Lead Source</label>
                    <select name="default_source" class="form-select">
                        <?php foreach (LEAD_SOURCES as $src): ?>
                        <option value="<?= $src ?>" <?= $src === 'Excel Import' ? 'selected' : '' ?>><?= $src ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Default Status</label>
                    <select name="default_status" class="form-select">
                        <?php foreach (LEAD_STATUSES as $s => $cfg): ?>
                        <option value="<?= $s ?>" <?= $s === 'New' ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="index.php?page=import" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-file-import"></i> Import Leads</button>
    </div>
</form>
