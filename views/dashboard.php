<?php
/**
 * Dashboard View
 */
$statusCounts = [];
foreach ($data['status_counts'] as $s) { $statusCounts[$s['status']] = $s['count']; }
$gradeCounts = [];
foreach ($data['grade_counts'] as $g) { $gradeCounts[$g['lead_grade']] = $g['count']; }
?>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card kpi-accent-indigo">
        <div class="kpi-icon"><i class="fas fa-users"></i></div>
        <div class="kpi-body">
            <div class="kpi-value" data-count="<?= $data['total_leads'] ?>"><?= number_format($data['total_leads']) ?></div>
            <div class="kpi-label">Total Leads</div>
        </div>
        <div class="kpi-trend"><span class="badge badge-info"><?= $data['today_leads'] ?> today</span></div>
    </div>
    <div class="kpi-card kpi-accent-green">
        <div class="kpi-icon"><i class="fas fa-rupee-sign"></i></div>
        <div class="kpi-body">
            <div class="kpi-value">₹<?= number_format($data['pipeline_value'] / 100000, 1) ?>L</div>
            <div class="kpi-label">Pipeline Value</div>
        </div>
        <div class="kpi-trend"><span class="badge badge-success"><?= $data['month_leads'] ?> this month</span></div>
    </div>
    <div class="kpi-card kpi-accent-amber">
        <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $data['conversion_rate'] ?>%</div>
            <div class="kpi-label">Conversion Rate</div>
        </div>
        <div class="kpi-trend"><span class="badge badge-warning">₹<?= number_format($data['disbursed_value'] / 100000, 1) ?>L disbursed</span></div>
    </div>
    <div class="kpi-card kpi-accent-rose">
        <div class="kpi-icon"><i class="fas fa-bell"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= $data['followups_today'] ?></div>
            <div class="kpi-label">Follow-ups Today</div>
        </div>
        <div class="kpi-trend">
            <span class="badge badge-hot"><?= $gradeCounts['Hot'] ?? 0 ?> hot</span>
            <span class="badge badge-warm"><?= $gradeCounts['Warm'] ?? 0 ?> warm</span>
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
