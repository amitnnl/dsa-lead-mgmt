<?php
/**
 * Partner Dashboard View
 */
?>

<div class="page-header">
    <h2>Welcome Back, <?= htmlspecialchars(Security::userName()) ?>!</h2>
    <p>Track your submitted leads and earned commissions in real-time.</p>
</div>

<!-- Partner KPIs -->
<div class="kpi-grid">
    <div class="kpi-card kpi-accent-indigo">
        <div class="kpi-icon"><i class="fas fa-file-invoice"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= number_format($data['total_submitted']) ?></div>
            <div class="kpi-label">Leads Submitted</div>
        </div>
    </div>
    <div class="kpi-card kpi-accent-green">
        <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="kpi-body">
            <div class="kpi-value"><?= number_format($data['disbursed_count']) ?></div>
            <div class="kpi-label">Successful Disbursements</div>
        </div>
    </div>
    <div class="kpi-card kpi-accent-amber">
        <div class="kpi-icon"><i class="fas fa-wallet"></i></div>
        <div class="kpi-body">
            <div class="kpi-value">₹<?= number_format($data['total_payouts']) ?></div>
            <div class="kpi-label">Total Commission Earned</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- Recent Submissions -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> Your Recent Submissions</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($data['recent_leads'])): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Loan Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['recent_leads'] as $lead): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($lead['customer_name']) ?></strong></td>
                            <td><?= htmlspecialchars($lead['loan_type']) ?></td>
                            <td>₹<?= number_format($lead['loan_amount']) ?></td>
                            <td><span class="status-pill" style="--pill-color:<?= LEAD_STATUSES[$lead['status']]['color'] ?? '#6b7280' ?>"><?= $lead['status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state-sm">
                <p>No leads submitted yet.</p>
                <a href="index.php?page=partner&action=submit_lead" class="btn btn-primary btn-sm">Submit Your First Lead</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Submit Info -->
    <div class="card bg-gradient-emerald text-white">
        <div class="card-body">
            <h3>Ready to grow?</h3>
            <p style="margin-bottom:20px; opacity:0.9">Submit a new lead today and track the progress in real-time. Our team typically processes new leads within 24 hours.</p>
            <a href="index.php?page=partner&action=submit_lead" class="btn btn-white btn-block" style="color:#226e54; font-weight:700">
                <i class="fas fa-plus-circle"></i> SUBMIT NEW LEAD
            </a>
        </div>
    </div>
</div>
