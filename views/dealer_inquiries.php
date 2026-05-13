<?php
/**
 * Dealer Inquiries View
 * @var array $data
 */
?>
<div class="page-header">
    <h2><i class="fas fa-envelope-open"></i> Vehicle Inquiries</h2>
</div>

<?php if (empty($data['leads'])): ?>
<div class="card"><div class="card-body" style="text-align:center; padding:60px">
    <i class="fas fa-envelope" style="font-size:48px; color:var(--text-muted); opacity:0.3; margin-bottom:16px; display:block"></i>
    <h3>No inquiries yet</h3>
    <p style="color:var(--text-dim)">Inquiries from customers will appear here when they express interest in your vehicles.</p>
</div></div>
<?php else: ?>
<div class="card"><div class="card-body" style="padding:0; overflow-x:auto">
    <table class="data-table">
        <thead><tr>
            <th>Customer</th><th>Phone</th><th>Vehicle Interest</th><th>Loan Amount</th><th>Source</th><th>Date</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php foreach ($data['leads'] as $l): 
            $sc = LEAD_STATUSES[$l['status']]['color'] ?? '#64748b';
        ?>
        <tr>
            <td><strong><?= htmlspecialchars($l['customer_name']) ?></strong></td>
            <td><?= htmlspecialchars($l['phone_number'] ?? '-') ?></td>
            <td><?= htmlspecialchars(($l['vehicle_make'] ?? '') . ' ' . ($l['vehicle_model'] ?? '')) ?></td>
            <td style="font-weight:600">₹<?= number_format($l['loan_amount']) ?></td>
            <td><span class="badge badge-dim"><?= htmlspecialchars($l['lead_source'] ?? '-') ?></span></td>
            <td style="font-size:12px; color:var(--text-dim)"><?= date('M j, Y', strtotime($l['created_at'])) ?></td>
            <td><span class="status-pill" style="--pill-color:<?= $sc ?>"><?= $l['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div></div>
<?php endif; ?>
