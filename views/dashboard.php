<?php
/**
 * Dashboard View
 * @var array $data
 */
$statusCounts = [];
foreach ($data['status_counts'] as $s) { $statusCounts[$s['status']] = $s['count']; }
$gradeCounts = [];
foreach ($data['grade_counts'] as $g) { $gradeCounts[$g['lead_grade']] = $g['count']; }
?>

<!-- Financial KPIs -->
<div class="kpi-grid">
    <div class="kpi-card kpi-accent-indigo">
        <div class="kpi-icon"><i class="fas fa-wallet"></i></div>
        <div class="kpi-body">
            <div class="kpi-value">₹<?= number_format($data['estimated_commissions'] / 1000, 1) ?>K</div>
            <div class="kpi-label">Commission Pipeline</div>
        </div>
        <div class="kpi-trend"><span class="badge badge-dim">Est. at 1.5% avg</span></div>
    </div>
    <div class="kpi-card kpi-accent-green">
        <div class="kpi-icon"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="kpi-body">
            <div class="kpi-value">₹<?= number_format($data['earned_commissions'] / 1000, 1) ?>K</div>
            <div class="kpi-label">Earned Commission</div>
        </div>
        <div class="kpi-trend"><span class="badge badge-success"><?= $data['total_leads'] ?> active leads</span></div>
    </div>
    <div class="kpi-card kpi-accent-amber">
        <div class="kpi-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="kpi-body">
            <div class="kpi-value">₹<?= number_format($data['disbursed_value'] / 100000, 1) ?>L</div>
            <div class="kpi-label">Disbursed Value</div>
        </div>
        <div class="kpi-trend"><span class="badge badge-warning">₹<?= number_format($data['pipeline_value'] / 100000, 1) ?>L in pipeline</span></div>
    </div>
    <!-- Target Progress Card -->
    <div class="kpi-card kpi-accent-rose">
        <div class="kpi-body" style="width:100%">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px">
                <span class="kpi-label" style="margin:0">MONTHLY TARGET</span>
                <span class="kpi-value" style="font-size:18px"><?= $data['target_progress'] ?>%</span>
            </div>
            <div class="progress-track" style="height:8px; background:rgba(0,0,0,0.05)">
                <div class="progress-fill" style="width:<?= $data['target_progress'] ?>%; background:var(--accent-color)"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-top:8px; color:#64748b">
                <span>₹<?= number_format($data['disbursed_value'] / 100000, 1) ?>L</span>
                <span>Goal: ₹<?= number_format($data['monthly_target'] / 100000, 0) ?>L</span>
            </div>
        </div>
    </div>
</div>

<!-- Pipeline + Charts Row -->
<div class="grid-2">
    <!-- Lead Pipeline Funnel -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-filter"></i> Lead Pipeline</h3>
        </div>
        <div class="card-body">
            <div class="pipeline">
                <?php foreach (LEAD_STATUSES as $status => $cfg): ?>
                <div class="pipeline-stage">
                    <div class="pipeline-bar" style="--bar-color: <?= $cfg['color'] ?>; --bar-width: <?= $data['total_leads'] > 0 ? max(8, (($statusCounts[$status] ?? 0) / $data['total_leads']) * 100) : 8 ?>%">
                        <span class="pipeline-count"><?= $statusCounts[$status] ?? 0 ?></span>
                    </div>
                    <span class="pipeline-label">
                        <i class="<?= $cfg['icon'] ?>"></i> <?= $status ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Loan Type Distribution -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie"></i> Loan Types</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($data['loan_types'])): ?>
            <div class="distribution-list">
                <?php 
                $colors = ['#6366f1','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444','#ec4899','#14b8a6','#f97316','#64748b'];
                foreach ($data['loan_types'] as $i => $lt): 
                    $pct = $data['total_leads'] > 0 ? round(($lt['count'] / $data['total_leads']) * 100) : 0;
                ?>
                <div class="dist-item">
                    <div class="dist-info">
                        <span class="dist-dot" style="background:<?= $colors[$i % count($colors)] ?>"></span>
                        <span class="dist-name"><?= htmlspecialchars($lt['loan_type'] ?? 'Other') ?></span>
                        <span class="dist-count"><?= $lt['count'] ?></span>
                    </div>
                    <div class="dist-bar-track">
                        <div class="dist-bar-fill" style="width:<?= $pct ?>%; background:<?= $colors[$i % count($colors)] ?>"></div>
                    </div>
                    <span class="dist-value">₹<?= number_format(($lt['value'] ?? 0) / 100000, 1) ?>L</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state-sm"><i class="fas fa-chart-pie"></i><p>No loan data yet</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Agent Performance + Recent Activity Row -->
<div class="grid-2">
    <!-- Agent Performance -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-trophy"></i> Agent Performance</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($data['agent_performance'])): ?>
            <div class="agent-list">
                <?php foreach ($data['agent_performance'] as $i => $agent): ?>
                <div class="agent-row">
                    <div class="agent-rank">#<?= $i + 1 ?></div>
                    <div class="agent-avatar"><?= strtoupper(substr($agent['name'], 0, 1)) ?></div>
                    <div class="agent-info">
                        <div class="agent-name"><?= htmlspecialchars($agent['name']) ?></div>
                        <div class="agent-stats">
                            <span><?= $agent['total_leads'] ?> leads</span>
                            <span class="text-success"><?= $agent['converted'] ?> converted</span>
                        </div>
                    </div>
                    <div class="agent-revenue">₹<?= number_format(($agent['revenue'] ?? 0) / 100000, 1) ?>L</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state-sm"><i class="fas fa-users"></i><p>No agent data</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <a href="index.php?page=activity" class="btn btn-ghost btn-xs">View All</a>
        </div>
        <div class="card-body">
            <?php if (!empty($data['recent_activities'])): ?>
            <div class="activity-feed">
                <?php foreach ($data['recent_activities'] as $act): ?>
                <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div class="activity-content">
                        <div class="activity-text">
                            <strong><?= htmlspecialchars($act['user_name'] ?? 'System') ?></strong>
                            <?= htmlspecialchars($act['action']) ?>
                            <?php if ($act['customer_name']): ?>
                            on <a href="index.php?page=leads&action=view&id=<?= $act['lead_id'] ?>"><?= htmlspecialchars($act['customer_name']) ?></a>
                            <?php endif; ?>
                        </div>
                        <?php if ($act['old_value'] || $act['new_value']): ?>
                        <div class="activity-change">
                            <?php if ($act['old_value']): ?><span class="badge badge-dim"><?= htmlspecialchars($act['old_value']) ?></span> → <?php endif; ?>
                            <span class="badge badge-info"><?= htmlspecialchars($act['new_value'] ?? '') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="activity-time"><?= date('M j, g:i A', strtotime($act['created_at'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state-sm"><i class="fas fa-history"></i><p>No activity yet</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Leads Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-clock"></i> Recent Leads</h3>
        <a href="index.php?page=leads" class="btn btn-ghost btn-xs">View All</a>
    </div>
    <div class="card-body table-responsive">
        <table class="data-table" id="dashRecentTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Loan Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Agent</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['recent_leads'] as $lead): ?>
                <tr class="clickable-row" onclick="window.location='index.php?page=leads&action=view&id=<?= $lead['id'] ?>'">
                    <td class="cell-name"><?= htmlspecialchars($lead['customer_name']) ?></td>
                    <td><?= htmlspecialchars($lead['phone_number'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($lead['loan_type'] ?? '-') ?></td>
                    <td>₹<?= number_format($lead['loan_amount']) ?></td>
                    <td><span class="status-pill" style="--pill-color:<?= LEAD_STATUSES[$lead['status']]['color'] ?? '#6b7280' ?>"><?= $lead['status'] ?></span></td>
                    <td><span class="score-badge" style="--score-color:<?= LeadScorer::getColor($lead['lead_score']) ?>"><?= $lead['lead_score'] ?> <small><?= $lead['lead_grade'] ?></small></span></td>
                    <td><?= htmlspecialchars($lead['agent_name'] ?? 'Unassigned') ?></td>
                    <td class="cell-date"><?= date('M j', strtotime($lead['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
