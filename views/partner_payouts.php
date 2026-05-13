<?php
/**
 * Partner Payouts History View
 */
?>

<div class="page-header">
    <h2><i class="fas fa-hand-holding-usd"></i> My Payout History</h2>
    <p>Track your commissions for all disbursed loans.</p>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <?php if (!empty($data['payouts'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Payout Amount</th>
                    <th>Transaction ID</th>
                    <th>Bank Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['payouts'] as $p): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($p['payout_date'])) ?></td>
                    <td><strong><?= htmlspecialchars($p['customer_name']) ?></strong></td>
                    <td class="text-success" style="font-weight:700">₹<?= number_format($p['payout_amount']) ?></td>
                    <td><small class="text-muted"><?= htmlspecialchars($p['transaction_id'] ?? 'Pending') ?></small></td>
                    <td><?= htmlspecialchars($p['bank_name'] ?? '-') ?></td>
                    <td><span class="badge badge-success">Disbursed</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-wallet" style="opacity:0.2"></i>
            <h3>No payouts recorded yet</h3>
            <p>Commissions are generated once your submitted leads reach the "Disbursed" status.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
