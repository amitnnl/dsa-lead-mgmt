<?php
/**
 * Commission Rates Management View
 */
?>

<div class="page-header">
    <h2><i class="fas fa-percentage"></i> Commission Rates</h2>
    <p>Define the commission percentage your company receives from each bank per loan type.</p>
</div>

<div class="grid-3-1">
    <!-- Existing Rates -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-list"></i> Configured Rates</h3></div>
        <div class="card-body table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Bank Name</th>
                        <th>Loan Type</th>
                        <th>Commission (%)</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['rates'])): ?>
                        <?php foreach ($data['rates'] as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['bank_name']) ?></strong></td>
                            <td><?= htmlspecialchars($r['loan_type']) ?></td>
                            <td class="text-primary" style="font-weight:600"><?= $r['commission_percentage'] ?>%</td>
                            <td><small><?= date('M j, Y', strtotime($r['updated_at'])) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No rates configured yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Update Form -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-plus"></i> Add / Update Rate</h3></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=settings&action=save_commission">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" placeholder="e.g. HDFC Bank" required list="bankList">
                    <datalist id="bankList">
                        <option value="HDFC Bank">
                        <option value="ICICI Bank">
                        <option value="Axis Bank">
                        <option value="SBI">
                        <option value="Kotak Mahindra">
                    </datalist>
                </div>
                <div class="form-group">
                    <label>Loan Type</label>
                    <select name="loan_type" class="form-select" required>
                        <option value="">Select Type...</option>
                        <option value="Personal Loan">Personal Loan</option>
                        <option value="Business Loan">Business Loan</option>
                        <option value="Home Loan">Home Loan</option>
                        <option value="LAP">Loan Against Property</option>
                        <option value="Car Loan">Car Loan</option>
                        <option value="Education Loan">Education Loan</option>
                        <option value="Credit Card">Credit Card</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Commission Percentage (%)</label>
                    <div style="display:flex; align-items:center; gap:8px">
                        <input type="number" name="rate" class="form-control" step="0.01" min="0" max="100" placeholder="0.00" required>
                        <span style="font-weight:600">%</span>
                    </div>
                    <p style="font-size:11px; color:#64748b; margin-top:4px">Total payout the DSA receives from the bank.</p>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Save Rate
                </button>
            </form>
        </div>
    </div>
</div>
