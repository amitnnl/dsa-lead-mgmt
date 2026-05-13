<?php
/**
 * Import View
 */
?>

<div class="page-header">
    <h2><i class="fas fa-file-import"></i> Smart Import</h2>
</div>

<?php if (!empty($_SESSION['import_job_id'])): ?>
<!-- Active Import Progress -->
<div class="import-progress" id="importProgress" data-job-id="<?= $_SESSION['import_job_id'] ?>">
    <div class="progress-header">
        <h4><div class="progress-spinner"></div> Import in Progress</h4>
    </div>
    <div class="progress-track"><div class="progress-fill"></div></div>
    <div class="progress-text">Starting...</div>
    <div class="progress-stats"></div>
</div>
<?php unset($_SESSION['import_job_id']); endif; ?>

<div class="grid-2">
    <!-- Upload Card -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-cloud-upload-alt"></i> Upload File</h3></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=import&action=upload" enctype="multipart/form-data" id="importForm">
                <?= Security::csrfField() ?>
                <div class="form-group" style="margin-bottom:16px">
                    <label style="font-weight:600; font-size:13px; color:#94a3b8">IMPORT TYPE</label>
                    <select name="import_type" class="form-select">
                        <option value="leads" selected>Leads / Customers</option>
                        <option value="payouts">Client Payouts</option>
                    </select>
                </div>
                <div class="upload-zone" id="uploadZone">
                    <div class="upload-icon"><i class="fas fa-file-excel"></i></div>
                    <h3>Drop your file here</h3>
                    <p>or click to browse. Supports <strong>.xlsx</strong>, <strong>.xls</strong>, and <strong>.csv</strong> files</p>
                    <input type="file" name="import_file" id="importFile" accept=".csv,.xlsx,.xls" required>
                    <div class="upload-filename" id="fileName"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-block" style="margin-top:16px">
                    <i class="fas fa-upload"></i> Upload & Map Columns
                </button>
            </form>
        </div>
    </div>

    <!-- Instructions -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-info-circle"></i> How It Works</h3></div>
        <div class="card-body">
            <div class="steps-list">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-text"><strong>Upload</strong> your Excel or CSV lead file</div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-text"><strong>Map columns</strong> from your file to system fields</div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-text"><strong>Import</strong> — leads are auto-scored and ready to work</div>
                </div>
            </div>
            <div class="import-tips">
                <h4>Tips</h4>
                <ul>
                    <li>First row should contain column headers</li>
                    <li>Phone numbers should be in standard format</li>
                    <li>Loan amounts should be numeric</li>
                    <li>Duplicate detection is based on phone number</li>
                    <li>Max file size: 10MB</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Import History -->
<?php if (!empty($data['batches'])): ?>
<div class="card" style="margin-top:24px">
    <div class="card-header"><h3><i class="fas fa-history"></i> Import History</h3></div>
    <div class="card-body table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Total Rows</th>
                    <th>Imported</th>
                    <th>Skipped</th>
                    <th>Errors</th>
                    <th>Status</th>
                    <th>By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['batches'] as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['filename']) ?></td>
                    <td><?= $b['total_rows'] ?></td>
                    <td class="text-success"><?= $b['imported_rows'] ?></td>
                    <td class="text-warning"><?= $b['skipped_rows'] ?></td>
                    <td class="text-danger"><?= $b['error_rows'] ?></td>
                    <td><span class="status-pill" style="--pill-color:<?= $b['status'] === 'completed' ? '#10b981' : '#ef4444' ?>"><?= ucfirst($b['status']) ?></span></td>
                    <td><?= htmlspecialchars($b['user_name'] ?? '-') ?></td>
                    <td><?= date('M j, g:i A', strtotime($b['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
