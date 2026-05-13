<?php
/**
 * Vehicle Inventory Management View
 * @var array $data
 */
$stats = $data['stats'];
?>

<div class="page-header">
    <h2><i class="fas fa-car"></i> Vehicle Inventory</h2>
    <a href="index.php?page=vehicles&action=add" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Vehicle</a>
</div>

<!-- Inventory KPIs -->
<div class="kpi-grid" style="margin-bottom:24px">
    <div class="kpi-card kpi-accent-indigo">
        <div class="kpi-icon"><i class="fas fa-car"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $stats['total'] ?></div>
            <div class="kpi-label">Total Vehicles</div>
        </div>
    </div>
    <div class="kpi-card kpi-accent-green">
        <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $stats['available'] ?></div>
            <div class="kpi-label">Available</div>
        </div>
    </div>
    <div class="kpi-card kpi-accent-amber">
        <div class="kpi-icon"><i class="fas fa-clock"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $stats['reserved'] ?></div>
            <div class="kpi-label">Reserved</div>
        </div>
    </div>
    <div class="kpi-card kpi-accent-rose">
        <div class="kpi-icon"><i class="fas fa-rupee-sign"></i></div>
        <div class="kpi-body">
            <div class="kpi-value">₹<?= number_format($stats['total_value'] / 100000, 1) ?>L</div>
            <div class="kpi-label">Stock Value</div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="display:flex; gap:8px; margin-bottom:16px">
    <a href="index.php?page=vehicles" class="btn btn-ghost btn-xs <?= empty($_GET['status']) ? 'btn-primary' : '' ?>">All</a>
    <a href="index.php?page=vehicles&status=Available" class="btn btn-ghost btn-xs <?= ($_GET['status'] ?? '') === 'Available' ? 'btn-primary' : '' ?>">Available</a>
    <a href="index.php?page=vehicles&status=Reserved" class="btn btn-ghost btn-xs <?= ($_GET['status'] ?? '') === 'Reserved' ? 'btn-primary' : '' ?>">Reserved</a>
    <a href="index.php?page=vehicles&status=Sold" class="btn btn-ghost btn-xs <?= ($_GET['status'] ?? '') === 'Sold' ? 'btn-primary' : '' ?>">Sold</a>
</div>

<!-- Vehicle Grid -->
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:20px">
    <?php if (empty($data['vehicles'])): ?>
    <div class="empty-state" style="grid-column:1/-1">
        <i class="fas fa-car" style="font-size:48px; opacity:0.3"></i>
        <h3>No vehicles in inventory</h3>
        <p>Add your first vehicle to start the marketplace.</p>
    </div>
    <?php else: ?>
    <?php foreach ($data['vehicles'] as $v): 
        $statusColors = ['Available' => '#10b981', 'Reserved' => '#f59e0b', 'Sold' => '#6366f1', 'Delisted' => '#64748b'];
        $sc = $statusColors[$v['status']] ?? '#64748b';
    ?>
    <div class="card" style="overflow:hidden; transition:transform 0.2s; cursor:pointer" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
        <!-- Vehicle Image Placeholder -->
        <div style="height:160px; background:linear-gradient(135deg, var(--surface-2), var(--surface-3)); display:flex; align-items:center; justify-content:center; position:relative">
            <?php if ($v['photo_url']): ?>
            <img src="<?= htmlspecialchars($v['photo_url']) ?>" alt="<?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?>" style="width:100%; height:100%; object-fit:cover">
            <?php else: ?>
            <i class="fas fa-<?= $v['body_type'] === 'Bike' ? 'motorcycle' : 'car' ?>" style="font-size:48px; color:var(--text-muted); opacity:0.4"></i>
            <?php endif; ?>
            <span style="position:absolute; top:10px; right:10px; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:700; background:rgba(0,0,0,0.6); color:<?= $sc ?>"><?= $v['status'] ?></span>
            <span style="position:absolute; top:10px; left:10px; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:700; background:rgba(0,0,0,0.6); color:#fff"><?= $v['year'] ?></span>
        </div>
        <div class="card-body" style="padding:16px">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px">
                <div>
                    <div style="font-weight:700; font-size:15px"><?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?></div>
                    <div style="font-size:12px; color:var(--text-dim)"><?= htmlspecialchars($v['variant'] ?? '') ?> · <?= $v['fuel_type'] ?> · <?= $v['transmission'] ?></div>
                </div>
                <div style="font-size:18px; font-weight:800; color:var(--primary-hover)">₹<?= number_format($v['asking_price'] / 100000, 1) ?>L</div>
            </div>
            <div style="display:flex; gap:16px; font-size:12px; color:var(--text-dim); margin-bottom:12px; padding:8px 0; border-top:1px solid var(--border); border-bottom:1px solid var(--border)">
                <span><i class="fas fa-tachometer-alt"></i> <?= number_format($v['km_driven']) ?> km</span>
                <span><i class="fas fa-user"></i> <?= $v['owner_count'] ?> Owner</span>
                <span><i class="fas fa-palette"></i> <?= htmlspecialchars($v['color'] ?? '-') ?></span>
                <?php if ($v['registration_no']): ?>
                <span><i class="fas fa-id-card"></i> <?= htmlspecialchars($v['registration_no']) ?></span>
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:6px">
                <a href="index.php?page=vehicles&action=edit&id=<?= $v['id'] ?>" class="btn btn-ghost btn-xs"><i class="fas fa-pen"></i> Edit</a>
                <?php if ($v['status'] === 'Available'): ?>
                <a href="index.php?page=vehicles&action=toggle&id=<?= $v['id'] ?>&to=Reserved" class="btn btn-ghost btn-xs" style="color:#f59e0b"><i class="fas fa-bookmark"></i> Reserve</a>
                <a href="index.php?page=vehicles&action=toggle&id=<?= $v['id'] ?>&to=Sold" class="btn btn-ghost btn-xs" style="color:#10b981"><i class="fas fa-handshake"></i> Mark Sold</a>
                <?php elseif ($v['status'] === 'Reserved'): ?>
                <a href="index.php?page=vehicles&action=toggle&id=<?= $v['id'] ?>&to=Available" class="btn btn-ghost btn-xs"><i class="fas fa-undo"></i> Unreserve</a>
                <a href="index.php?page=vehicles&action=toggle&id=<?= $v['id'] ?>&to=Sold" class="btn btn-ghost btn-xs" style="color:#10b981"><i class="fas fa-handshake"></i> Mark Sold</a>
                <?php elseif ($v['status'] === 'Sold'): ?>
                <a href="index.php?page=vehicles&action=toggle&id=<?= $v['id'] ?>&to=Available" class="btn btn-ghost btn-xs"><i class="fas fa-undo"></i> Relist</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
