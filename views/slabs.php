<?php
/**
 * Payout Slabs Management View
 */
?>

<div class="page-header">
    <h2><i class="fas fa-layer-group"></i> Payout Slabs</h2>
    <p>Define how much percentage of the total commission is shared with the agents based on their monthly volume.</p>
</div>

<div class="grid-2">
    <!-- Existing Slabs -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-chart-line"></i> Active Slabs</h3></div>
        <div class="card-body table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Min Volume</th>
                        <th>Max Volume</th>
                        <th>Agent Share (%)</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['slabs'])): ?>
                        <?php foreach ($data['slabs'] as $s): ?>
                        <tr>
                            <td>₹<?= number_format($s['min_volume']) ?></td>
                            <td>₹<?= number_format($s['max_volume']) ?></td>
                            <td class="text-success" style="font-weight:600"><?= $s['agent_share_percentage'] ?>%</td>
                            <td><small><?= htmlspecialchars($s['description'] ?? '-') ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No slabs configured yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top:16px; padding:12px; background:rgba(34,110,84,0.1); border-radius:8px; border:1px solid rgba(34,110,84,0.2)">
                <p style="font-size:12px; color:#226e54"><i class="fas fa-info-circle"></i> Example: If a slab is set to <strong>50%</strong>, and the bank pays the company ₹10,000 commission, the agent receives ₹5,000.</p>
            </div>
        </div>
    </div>

    <!-- Add Slab Form -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-plus-circle"></i> Create New Slab</h3></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=settings&action=save_slab">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label>Min Monthly Volume (₹)</label>
                    <input type="number" name="min_volume" class="form-control" placeholder="0" required>
                </div>
                <div class="form-group">
                    <label>Max Monthly Volume (₹)</label>
                    <input type="number" name="max_volume" class="form-control" placeholder="999999999" required>
                </div>
                <div class="form-group">
                    <label>Agent Share Percentage (%)</label>
                    <input type="number" name="agent_share" class="form-control" step="0.1" min="0" max="100" placeholder="e.g. 40" required>
                </div>
                <div class="form-group">
                    <label>Description (Optional)</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g. Silver Tier">
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Add Slab
                </button>
            </form>
        </div>
    </div>
</div>
