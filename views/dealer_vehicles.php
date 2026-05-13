<?php
/**
 * Dealer Vehicle Listing View
 * @var array $data
 */
?>
<div class="page-header">
    <h2><i class="fas fa-list"></i> My Vehicles</h2>
    <a href="index.php?page=dealer&action=add_vehicle" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add New</a>
</div>

<?php if (empty($data['vehicles'])): ?>
<div class="card"><div class="card-body" style="text-align:center; padding:60px">
    <i class="fas fa-car" style="font-size:48px; color:var(--text-muted); opacity:0.3; margin-bottom:16px; display:block"></i>
    <h3>No vehicles listed yet</h3>
    <p style="color:var(--text-dim)">Start by adding your first vehicle to the marketplace.</p>
    <a href="index.php?page=dealer&action=add_vehicle" class="btn btn-primary" style="margin-top:16px"><i class="fas fa-plus"></i> List Your First Vehicle</a>
</div></div>
<?php else: ?>
<div class="card"><div class="card-body" style="padding:0; overflow-x:auto">
    <table class="data-table">
        <thead><tr>
            <th>Vehicle</th><th>Reg. No.</th><th>Year</th><th>KM</th><th>Fuel</th><th>Price</th><th>Views</th><th>Inquiries</th><th>Status</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($data['vehicles'] as $v): 
            $sc = ['Available' => '#10b981', 'Reserved' => '#f59e0b', 'Sold' => '#6366f1', 'Delisted' => '#64748b'];
        ?>
        <tr>
            <td><strong><?= htmlspecialchars($v['make'].' '.$v['model']) ?></strong><br><small style="color:var(--text-dim)"><?= htmlspecialchars($v['variant'] ?? '') ?> · <?= $v['transmission'] ?></small></td>
            <td style="font-family:monospace; font-size:12px"><?= htmlspecialchars($v['registration_no'] ?? '-') ?></td>
            <td><?= $v['year'] ?></td>
            <td><?= number_format($v['km_driven']) ?></td>
            <td><?= $v['fuel_type'] ?></td>
            <td style="font-weight:700; color:var(--primary-hover)">₹<?= number_format($v['asking_price']) ?></td>
            <td><?= $v['views_count'] ?></td>
            <td><?= $v['inquiries_count'] ?></td>
            <td><span class="status-pill" style="--pill-color:<?= $sc[$v['status']] ?? '#64748b' ?>"><?= $v['status'] ?></span></td>
            <td>
                <a href="index.php?page=dealer&action=edit_vehicle&id=<?= $v['id'] ?>" class="btn btn-ghost btn-xs"><i class="fas fa-pen"></i></a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div></div>
<?php endif; ?>
