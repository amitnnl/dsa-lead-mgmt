<?php
/**
 * Leads List View
 */
$filters = $data['filters'] ?? [];
?>

<!-- Leads Header -->
<div class="page-header">
    <div>
        <h2>Leads <span class="count-badge"><?= number_format($data['total'] ?? 0) ?></span></h2>
    </div>
    <div class="header-actions">
        <a href="index.php?page=import" class="btn btn-ghost btn-sm"><i class="fas fa-file-import"></i> Import</a>
        <a href="index.php?page=leads&action=create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Lead</a>
    </div>
</div>

<!-- Filters Bar -->
<div class="filters-bar">
    <form method="GET" action="index.php" class="filters-form" id="filterForm">
        <input type="hidden" name="page" value="leads">
        <div class="filter-group">
            <div class="filter-search">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search name, phone, email, city...">
            </div>
        </div>
        <div class="filter-group">
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <?php foreach (LEAD_STATUSES as $s => $cfg): ?>
                <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <select name="grade" onchange="this.form.submit()">
                <option value="">All Grades</option>
                <option value="Hot" <?= ($filters['grade'] ?? '') === 'Hot' ? 'selected' : '' ?>>🔥 Hot</option>
                <option value="Warm" <?= ($filters['grade'] ?? '') === 'Warm' ? 'selected' : '' ?>>🌤 Warm</option>
                <option value="Cold" <?= ($filters['grade'] ?? '') === 'Cold' ? 'selected' : '' ?>>❄️ Cold</option>
            </select>
        </div>
        <div class="filter-group">
            <select name="loan_type" onchange="this.form.submit()">
                <option value="">All Loan Types</option>
                <?php foreach (LOAN_TYPES as $lt): ?>
                <option value="<?= $lt ?>" <?= ($filters['loanType'] ?? '') === $lt ? 'selected' : '' ?>><?= $lt ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (!empty($filters['status']) || !empty($filters['grade']) || !empty($filters['search']) || !empty($filters['loanType'])): ?>
        <a href="index.php?page=leads" class="btn btn-ghost btn-xs"><i class="fas fa-times"></i> Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Leads Table -->
<div class="card">
    <div class="card-body table-responsive">
        <?php if (!empty($data['leads'])): ?>
        <table class="data-table" id="leadsTable">
            <thead>
                <tr>
                    <th class="th-check"><input type="checkbox" id="selectAll"></th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Loan Type</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Agent</th>
                    <th>Follow-up</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['leads'] as $lead): ?>
                <tr>
                    <td><input type="checkbox" class="lead-check" value="<?= $lead['id'] ?>"></td>
                    <td class="cell-name">
                        <a href="index.php?page=leads&action=view&id=<?= $lead['id'] ?>"><?= htmlspecialchars($lead['customer_name']) ?></a>
                        <?php if ($lead['email_address']): ?>
                        <div class="cell-sub"><?= htmlspecialchars($lead['email_address']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($lead['phone_number'] ?? '-') ?>
                        <?php if ($lead['phone_number']): ?>
                        <a href="tel:<?= $lead['phone_number'] ?>" class="cell-action" title="Call"><i class="fas fa-phone"></i></a>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($lead['city'] ?? '-') ?></td>
                    <td><span class="loan-type-badge"><?= htmlspecialchars($lead['loan_type'] ?? '-') ?></span></td>
                    <td class="cell-amount">₹<?= number_format($lead['loan_amount']) ?></td>
                    <td>
                        <select class="status-select" data-lead-id="<?= $lead['id'] ?>" style="--pill-color:<?= LEAD_STATUSES[$lead['status']]['color'] ?? '#6b7280' ?>">
                            <?php foreach (LEAD_STATUSES as $s => $cfg): ?>
                            <option value="<?= $s ?>" <?= $lead['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><span class="score-badge" style="--score-color:<?= LeadScorer::getColor($lead['lead_score']) ?>"><?= $lead['lead_score'] ?></span></td>
                    <td>
                        <select class="assign-select" data-lead-id="<?= $lead['id'] ?>">
                            <option value="">Unassigned</option>
                            <?php foreach ($data['agents'] as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $lead['assigned_to'] == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="cell-date">
                        <?php if ($lead['follow_up_date']): ?>
                            <?php $isOverdue = $lead['follow_up_date'] < date('Y-m-d') && !in_array($lead['status'], ['Disbursed','Rejected']); ?>
                            <span class="<?= $isOverdue ? 'text-danger' : '' ?>"><?= date('M j', strtotime($lead['follow_up_date'])) ?></span>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="index.php?page=leads&action=view&id=<?= $lead['id'] ?>" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                            <a href="index.php?page=leads&action=edit&id=<?= $lead['id'] ?>" class="btn-icon" title="Edit"><i class="fas fa-pen"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($data['totalPages'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
            <a href="index.php?page=leads&p=<?= $i ?>&<?= http_build_query($filters) ?>" 
               class="page-btn <?= $data['currentPage'] == $i ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h3>No leads found</h3>
            <p>Create your first lead or import from Excel/CSV</p>
            <div class="empty-actions">
                <a href="index.php?page=leads&action=create" class="btn btn-primary"><i class="fas fa-plus"></i> New Lead</a>
                <a href="index.php?page=import" class="btn btn-ghost"><i class="fas fa-file-import"></i> Import</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
