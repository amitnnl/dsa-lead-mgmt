<?php
/**
 * Bank Rates Management View (Admin only)
 * @var array $data
 */
$vehicleLoanTypes = ['Used Car Loan', 'Used Bike Loan', 'Used Commercial Vehicle Loan', 'New Car Loan', 'New Bike Loan'];
?>

<div class="page-header">
    <h2><i class="fas fa-university"></i> Bank Rate Management</h2>
</div>

<div class="grid-2">
    <!-- Add/Edit Rate -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-plus-circle"></i> Add Bank Rate</h3></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=settings&action=save_bank_rate">
                <?= Security::csrfField() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" class="form-input" required placeholder="e.g. HDFC Bank">
                    </div>
                    <div class="form-group">
                        <label>Loan Type</label>
                        <select name="loan_type" class="form-select" required>
                            <?php foreach ($vehicleLoanTypes as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Interest Rate (% p.a.)</label>
                        <input type="number" name="interest_rate" class="form-input" step="0.25" min="5" max="30" required placeholder="e.g. 10.50">
                    </div>
                    <div class="form-group">
                        <label>Max Tenure (Years)</label>
                        <input type="number" name="max_tenure_years" class="form-input" value="5" min="1" max="10">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Max LTV (%)</label>
                        <input type="number" name="max_ltv" class="form-input" value="80" min="50" max="100">
                        <small class="form-hint">Loan-to-Value ratio</small>
                    </div>
                    <div class="form-group">
                        <label>Processing Fee</label>
                        <input type="text" name="processing_fee" class="form-input" value="1%" placeholder="e.g. 1% or ₹5000">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Rate</button>
            </form>
        </div>
    </div>

    <!-- Existing Rates -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-list"></i> Current Rates</h3></div>
        <div class="card-body" style="max-height:500px; overflow-y:auto">
            <?php if (empty($data['rates'])): ?>
            <div class="empty-state-sm">
                <i class="fas fa-university"></i>
                <p>No bank rates configured yet. Add your first rate to enable comparisons.</p>
            </div>
            <?php else: ?>
            <?php 
            $grouped = [];
            foreach ($data['rates'] as $r) { $grouped[$r['loan_type']][] = $r; }
            foreach ($grouped as $type => $rates): 
            ?>
            <div style="margin-bottom:20px">
                <div style="font-size:12px; font-weight:700; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; padding-bottom:6px; border-bottom:1px solid var(--border)">
                    <?= htmlspecialchars($type) ?>
                </div>
                <?php foreach ($rates as $r): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; margin-bottom:4px; background:var(--surface-2); border-radius:8px">
                    <div>
                        <span style="font-weight:600; font-size:13px"><?= htmlspecialchars($r['bank_name']) ?></span>
                        <span style="color:var(--primary-hover); font-weight:700; margin-left:8px"><?= $r['interest_rate'] ?>%</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px">
                        <span style="font-size:11px; color:var(--text-muted)"><?= $r['max_tenure_years'] ?>yr · LTV <?= $r['max_ltv'] ?>% · <?= $r['processing_fee'] ?></span>
                        <a href="index.php?page=settings&action=delete_bank_rate&id=<?= $r['id'] ?>" class="btn btn-ghost btn-xs" onclick="return confirm('Remove this rate?')" style="color:#ef4444">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
