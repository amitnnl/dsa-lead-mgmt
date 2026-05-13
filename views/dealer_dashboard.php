<?php
/**
 * Dealer Dashboard View
 * @var array $data
 */
?>

<div class="page-header">
    <h2><i class="fas fa-store"></i> Dealer Dashboard</h2>
    <a href="index.php?page=dealer&action=add_vehicle" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> List New Vehicle</a>
</div>

<!-- Dealer KPIs -->
<div class="kpi-grid" style="margin-bottom:24px">
    <div class="kpi-card kpi-accent-indigo">
        <div class="kpi-icon"><i class="fas fa-car"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $data['total_vehicles'] ?></div>
            <div class="kpi-label">Total Listings</div>
        </div>
    </div>
    <div class="kpi-card kpi-accent-green">
        <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $data['available'] ?></div>
            <div class="kpi-label">Available Now</div>
        </div>
    </div>
    <div class="kpi-card kpi-accent-amber">
        <div class="kpi-icon"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="kpi-body">
            <div class="kpi-value">₹<?= number_format($data['sold_value'] / 100000, 1) ?>L</div>
            <div class="kpi-label">Total Sold</div>
        </div>
    </div>
    <div class="kpi-card kpi-accent-rose">
        <div class="kpi-icon"><i class="fas fa-envelope"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $data['total_inquiries'] ?></div>
            <div class="kpi-label">Inquiries</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Quick Stats -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-chart-bar"></i> Inventory Summary</h3></div>
        <div class="card-body">
            <div style="display:flex; gap:16px; margin-bottom:16px">
                <div style="flex:1; text-align:center; padding:20px; background:rgba(99,102,241,0.08); border-radius:12px; border:1px solid rgba(99,102,241,0.15)">
                    <div style="font-size:28px; font-weight:800; color:var(--primary-hover)">₹<?= number_format($data['inventory_value'] / 100000, 1) ?>L</div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px">Current Stock Value</div>
                </div>
                <div style="flex:1; text-align:center; padding:20px; background:rgba(16,185,129,0.08); border-radius:12px; border:1px solid rgba(16,185,129,0.15)">
                    <div style="font-size:28px; font-weight:800; color:#10b981"><?= $data['sold'] ?></div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px">Vehicles Sold</div>
                </div>
            </div>
            <div style="display:flex; gap:8px">
                <a href="index.php?page=dealer&action=my_vehicles" class="btn btn-ghost btn-sm" style="flex:1; text-align:center"><i class="fas fa-list"></i> View All Vehicles</a>
                <a href="index.php?page=dealer&action=inquiries" class="btn btn-ghost btn-sm" style="flex:1; text-align:center"><i class="fas fa-envelope-open"></i> View Inquiries</a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-bolt"></i> Quick Actions</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
                <a href="index.php?page=dealer&action=add_vehicle" style="display:flex; align-items:center; gap:12px; padding:16px; background:var(--surface-2); border-radius:10px; text-decoration:none; color:var(--text); transition:all 0.2s" onmouseover="this.style.background='var(--surface-3)'" onmouseout="this.style.background='var(--surface-2)'">
                    <i class="fas fa-plus-circle" style="font-size:20px; color:var(--primary)"></i>
                    <div><div style="font-weight:600; font-size:13px">List Vehicle</div><div style="font-size:11px; color:var(--text-muted)">Add to marketplace</div></div>
                </a>
                <a href="browse.php" target="_blank" style="display:flex; align-items:center; gap:12px; padding:16px; background:var(--surface-2); border-radius:10px; text-decoration:none; color:var(--text); transition:all 0.2s" onmouseover="this.style.background='var(--surface-3)'" onmouseout="this.style.background='var(--surface-2)'">
                    <i class="fas fa-globe" style="font-size:20px; color:#f59e0b"></i>
                    <div><div style="font-weight:600; font-size:13px">Public Page</div><div style="font-size:11px; color:var(--text-muted)">View marketplace</div></div>
                </a>
                <a href="index.php?page=dealer&action=inquiries" style="display:flex; align-items:center; gap:12px; padding:16px; background:var(--surface-2); border-radius:10px; text-decoration:none; color:var(--text); transition:all 0.2s" onmouseover="this.style.background='var(--surface-3)'" onmouseout="this.style.background='var(--surface-2)'">
                    <i class="fas fa-envelope" style="font-size:20px; color:#10b981"></i>
                    <div><div style="font-weight:600; font-size:13px">Inquiries</div><div style="font-size:11px; color:var(--text-muted)">Customer interest</div></div>
                </a>
                <a href="index.php?page=settings&action=profile" style="display:flex; align-items:center; gap:12px; padding:16px; background:var(--surface-2); border-radius:10px; text-decoration:none; color:var(--text); transition:all 0.2s" onmouseover="this.style.background='var(--surface-3)'" onmouseout="this.style.background='var(--surface-2)'">
                    <i class="fas fa-user-cog" style="font-size:20px; color:#8b5cf6"></i>
                    <div><div style="font-weight:600; font-size:13px">Profile</div><div style="font-size:11px; color:var(--text-muted)">Update details</div></div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Vehicles -->
<?php if (!empty($data['recent_vehicles'])): ?>
<div class="card" style="margin-top:24px">
    <div class="card-header">
        <h3><i class="fas fa-clock"></i> Recent Listings</h3>
        <a href="index.php?page=dealer&action=my_vehicles" class="btn btn-ghost btn-xs">View All</a>
    </div>
    <div class="card-body" style="padding:0">
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr>
                    <th>Vehicle</th><th>Year</th><th>KM</th><th>Price</th><th>Status</th><th>Action</th>
                </tr></thead>
                <tbody>
                <?php foreach ($data['recent_vehicles'] as $v): 
                    $sc = ['Available' => '#10b981', 'Reserved' => '#f59e0b', 'Sold' => '#6366f1', 'Delisted' => '#64748b'];
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($v['make'].' '.$v['model']) ?></strong><br><small style="color:var(--text-dim)"><?= $v['fuel_type'] ?> · <?= $v['transmission'] ?></small></td>
                    <td><?= $v['year'] ?></td>
                    <td><?= number_format($v['km_driven']) ?></td>
                    <td style="font-weight:700">₹<?= number_format($v['asking_price'] / 100000, 1) ?>L</td>
                    <td><span class="status-pill" style="--pill-color:<?= $sc[$v['status']] ?? '#64748b' ?>"><?= $v['status'] ?></span></td>
                    <td><a href="index.php?page=dealer&action=edit_vehicle&id=<?= $v['id'] ?>" class="btn btn-ghost btn-xs"><i class="fas fa-pen"></i></a></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
