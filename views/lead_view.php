<?php
/**
 * Lead Detail View
 */
$lead = $data['lead'];
$scoreColor = LeadScorer::getColor($lead['lead_score']);
$statusColor = LEAD_STATUSES[$lead['status']]['color'] ?? '#6b7280';
?>

<div class="page-header">
    <div>
        <a href="index.php?page=leads" class="btn btn-ghost btn-xs"><i class="fas fa-arrow-left"></i> Back</a>
        <h2><?= htmlspecialchars($lead['customer_name']) ?></h2>
    </div>
    <div class="header-actions">
        <a href="index.php?page=leads&action=edit&id=<?= $lead['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Edit</a>
        <?php if (Security::isAdmin()): ?>
        <a href="index.php?page=leads&action=delete&id=<?= $lead['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this lead?')"><i class="fas fa-trash"></i></a>
        <?php endif; ?>
    </div>
</div>

<div class="grid-3-1">
    <!-- Main Info -->
    <div class="lead-detail-main">
        <!-- Status + Score Banner -->
        <div class="detail-banner">
            <div class="banner-item">
                <span class="status-pill status-pill-lg" style="--pill-color:<?= $statusColor ?>"><?= $lead['status'] ?></span>
            </div>
            <div class="banner-item">
                <div class="score-ring" style="--score-pct:<?= $lead['lead_score'] ?>%; --score-color:<?= $scoreColor ?>">
                    <span><?= $lead['lead_score'] ?></span>
                </div>
                <div class="score-label"><?= $lead['lead_grade'] ?> Lead</div>
            </div>
            <div class="banner-item">
                <div class="banner-value">₹<?= number_format($lead['loan_amount']) ?></div>
                <div class="banner-label"><?= htmlspecialchars($lead['loan_type'] ?? 'Loan Amount') ?></div>
            </div>
            <div class="banner-item">
                <div class="banner-value"><?= htmlspecialchars($lead['agent_name'] ?? 'Unassigned') ?></div>
                <div class="banner-label">Assigned Agent</div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="grid-2">
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-user"></i> Contact Information</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value"><?= htmlspecialchars($lead['customer_name']) ?></span></div>
                        <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value"><?= htmlspecialchars($lead['phone_number'] ?? '-') ?> <?php if($lead['phone_number']): ?><a href="tel:<?= $lead['phone_number'] ?>"><i class="fas fa-phone"></i></a><?php endif; ?></span></div>
                        <div class="detail-row"><span class="detail-label">Alt Phone</span><span class="detail-value"><?= htmlspecialchars($lead['alt_phone'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value"><?= htmlspecialchars($lead['email_address'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">DOB</span><span class="detail-value"><?= $lead['dob'] ? date('M j, Y', strtotime($lead['dob'])) : '-' ?></span></div>
                        <div class="detail-row"><span class="detail-label">Gender</span><span class="detail-value"><?= htmlspecialchars($lead['gender'] ?? '-') ?></span></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-map-marker-alt"></i> Address</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-row"><span class="detail-label">Address</span><span class="detail-value"><?= htmlspecialchars($lead['address'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">City</span><span class="detail-value"><?= htmlspecialchars($lead['city'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">State</span><span class="detail-value"><?= htmlspecialchars($lead['state'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Pincode</span><span class="detail-value"><?= htmlspecialchars($lead['pincode'] ?? '-') ?></span></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-briefcase"></i> Financial Details</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-row"><span class="detail-label">Loan Type</span><span class="detail-value"><?= htmlspecialchars($lead['loan_type'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Loan Amount</span><span class="detail-value">₹<?= number_format($lead['loan_amount']) ?></span></div>
                        <div class="detail-row"><span class="detail-label">Monthly Income</span><span class="detail-value">₹<?= number_format($lead['monthly_income']) ?></span></div>
                        <div class="detail-row"><span class="detail-label">Employer</span><span class="detail-value"><?= htmlspecialchars($lead['employer'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Employment</span><span class="detail-value"><?= htmlspecialchars($lead['employment_type'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Credit Score</span><span class="detail-value"><?= $lead['credit_score'] ?? '-' ?></span></div>
                        <div class="detail-row"><span class="detail-label">Bank</span><span class="detail-value"><?= htmlspecialchars($lead['bank_name'] ?? '-') ?></span></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-info-circle"></i> Lead Info</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-row"><span class="detail-label">Source</span><span class="detail-value"><?= htmlspecialchars($lead['lead_source'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Follow-up</span><span class="detail-value"><?= $lead['follow_up_date'] ? date('M j, Y', strtotime($lead['follow_up_date'])) : '-' ?></span></div>
                        <div class="detail-row"><span class="detail-label">Created</span><span class="detail-value"><?= date('M j, Y g:i A', strtotime($lead['created_at'])) ?></span></div>
                        <div class="detail-row"><span class="detail-label">Updated</span><span class="detail-value"><?= date('M j, Y g:i A', strtotime($lead['updated_at'])) ?></span></div>
                        <?php if ($lead['remarks']): ?>
                        <div class="detail-row"><span class="detail-label">Remarks</span><span class="detail-value"><?= htmlspecialchars($lead['remarks']) ?></span></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar: Activity + Quick Actions -->
    <div class="lead-detail-sidebar">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-bolt"></i> Quick Actions</h3></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=activity&action=add">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                    <div class="form-group">
                        <select name="action_type" class="form-select" required>
                            <option value="">Select action...</option>
                            <option value="Phone Call">📞 Phone Call</option>
                            <option value="WhatsApp Message">💬 WhatsApp</option>
                            <option value="Email Sent">📧 Email Sent</option>
                            <option value="Meeting Scheduled">📅 Meeting</option>
                            <option value="Documents Collected">📋 Docs Collected</option>
                            <option value="Application Filed">📝 Application Filed</option>
                            <option value="Note">📌 Note</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea name="notes" class="form-textarea" placeholder="Add notes..." rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-plus"></i> Log Activity
                    </button>
                </form>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-history"></i> Activity Timeline</h3></div>
            <div class="card-body">
                <?php if (!empty($data['activities'])): ?>
                <div class="timeline">
                    <?php foreach ($data['activities'] as $act): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-action"><?= htmlspecialchars($act['action']) ?></div>
                            <?php if ($act['notes']): ?>
                            <div class="timeline-notes"><?= htmlspecialchars($act['notes']) ?></div>
                            <?php endif; ?>
                            <?php if ($act['old_value'] || $act['new_value']): ?>
                            <div class="timeline-change">
                                <?= htmlspecialchars($act['old_value'] ?? '') ?> → <?= htmlspecialchars($act['new_value'] ?? '') ?>
                            </div>
                            <?php endif; ?>
                            <div class="timeline-meta">
                                <?= htmlspecialchars($act['user_name'] ?? 'System') ?> · <?= date('M j, g:i A', strtotime($act['created_at'])) ?>
                            </div>
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
</div>
