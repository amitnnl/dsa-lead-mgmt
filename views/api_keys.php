<?php
/**
 * API Keys Management View (Admin only)
 */
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-key"></i> API Integration</h2>
    </div>
</div>

<div class="grid-2">
    <!-- Generate New Key -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-plus-circle"></i> Generate API Key</h3></div>
        <div class="card-body">
            <form method="POST" action="index.php?page=settings&action=generate_api_key">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label>Key Name / Description</label>
                    <input type="text" name="key_name" class="form-input" placeholder="e.g., Python Enrichment Script" required>
                    <small class="form-hint">Give this key a descriptive name to identify its purpose</small>
                </div>
                <div class="form-group">
                    <label>Permissions</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="perms[]" value="read" checked> <span>Read Leads</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="perms[]" value="write" checked> <span>Create/Update Leads</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="perms[]" value="enrich"> <span>Bulk Enrichment</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="perms[]" value="delete"> <span>Delete Leads</span>
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Generate Key</button>
            </form>
        </div>
    </div>

    <!-- API Documentation -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-book"></i> API Quick Reference</h3></div>
        <div class="card-body">
            <div class="api-docs">
                <div class="api-endpoint">
                    <span class="api-method api-get">GET</span>
                    <code>index.php?page=api&action=v1_leads</code>
                    <p>List all leads with filters</p>
                </div>
                <div class="api-endpoint">
                    <span class="api-method api-post">POST</span>
                    <code>index.php?page=api&action=v1_leads</code>
                    <p>Create a new lead</p>
                </div>
                <div class="api-endpoint">
                    <span class="api-method api-put">PUT</span>
                    <code>index.php?page=api&action=v1_lead&id=123</code>
                    <p>Update a lead by ID</p>
                </div>
                <div class="api-endpoint">
                    <span class="api-method api-post">POST</span>
                    <code>index.php?page=api&action=v1_enrich</code>
                    <p>Bulk enrich leads with external data</p>
                </div>
                <div class="api-endpoint">
                    <span class="api-method api-get">GET</span>
                    <code>index.php?page=api&action=v1_stats</code>
                    <p>Get dashboard statistics</p>
                </div>

                <div class="api-auth-info">
                    <h4><i class="fas fa-shield-alt"></i> Authentication</h4>
                    <p>Include your API key in the request header:</p>
                    <code class="api-code-block">X-API-Key: your_api_key_here</code>
                </div>

                <div class="api-auth-info" style="margin-top:16px">
                    <h4><i class="fab fa-python"></i> Python Example</h4>
                    <pre class="api-code-block">import requests

API_URL = "<?= APP_URL ?>/index.php"
API_KEY = "your_api_key"
headers = {"X-API-Key": API_KEY}

# Get all leads
leads = requests.get(
    f"{API_URL}?page=api&action=v1_leads",
    headers=headers
).json()

# Enrich leads with external data
enrichment = requests.post(
    f"{API_URL}?page=api&action=v1_enrich",
    headers=headers,
    json={"leads": [
        {"phone_number": "9876543210",
         "credit_score": 750,
         "employer": "TCS"}
    ]}
).json()</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Existing Keys -->
<?php if (!empty($data['api_keys'])): ?>
<div class="card" style="margin-top:24px">
    <div class="card-header"><h3><i class="fas fa-list"></i> Active API Keys</h3></div>
    <div class="card-body table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>API Key</th>
                    <th>Permissions</th>
                    <th>Last Used</th>
                    <th>Requests</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['api_keys'] as $key): ?>
                <tr>
                    <td class="cell-name"><?= htmlspecialchars($key['key_name']) ?></td>
                    <td>
                        <div class="api-key-display">
                            <code class="api-key-masked" id="key-<?= $key['id'] ?>"><?= substr($key['api_key'], 0, 8) ?>...<?= substr($key['api_key'], -4) ?></code>
                            <button class="btn-icon" onclick="copyApiKey('<?= htmlspecialchars($key['api_key']) ?>')" title="Copy">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </td>
                    <td>
                        <?php $perms = json_decode($key['permissions'], true) ?? []; ?>
                        <?php foreach ($perms as $p): ?>
                        <span class="badge badge-info" style="margin-right:2px"><?= htmlspecialchars($p) ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td class="cell-date"><?= $key['last_used_at'] ? date('M j, g:i A', strtotime($key['last_used_at'])) : 'Never' ?></td>
                    <td><?= number_format($key['request_count']) ?></td>
                    <td class="cell-date"><?= date('M j, Y', strtotime($key['created_at'])) ?></td>
                    <td>
                        <a href="index.php?page=settings&action=revoke_api_key&id=<?= $key['id'] ?>" 
                           class="btn btn-danger btn-xs" onclick="return confirm('Revoke this API key?')">
                            <i class="fas fa-ban"></i> Revoke
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="card" style="margin-top:24px">
    <div class="card-body">
        <div class="empty-state">
            <i class="fas fa-key"></i>
            <h3>No API Keys Generated</h3>
            <p>Generate your first API key to integrate with Python scripts or external services</p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($data['new_key'])): ?>
<!-- Newly Generated Key Modal -->
<div class="key-reveal-overlay" id="keyReveal">
    <div class="key-reveal-card">
        <div class="key-reveal-icon"><i class="fas fa-check-circle"></i></div>
        <h3>API Key Generated!</h3>
        <p>Copy this key now — it won't be shown again in full.</p>
        <div class="key-reveal-value">
            <code id="newKeyValue"><?= htmlspecialchars($data['new_key']) ?></code>
            <button class="btn btn-primary btn-sm" onclick="copyApiKey('<?= htmlspecialchars($data['new_key']) ?>')">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
        <button class="btn btn-ghost" onclick="document.getElementById('keyReveal').remove()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
</div>
<?php endif; ?>

<script>
function copyApiKey(key) {
    navigator.clipboard.writeText(key).then(() => {
        showToast('API key copied to clipboard!', 'success');
    });
}
</script>
