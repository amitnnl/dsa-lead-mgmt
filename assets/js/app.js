/**
 * DSA LeadFlow - Application JavaScript
 * Features: Sidebar, Search, Quick Actions, DataTables, Import Progress
 */

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initGlobalSearch();
    initQuickStatusChange();
    initQuickAssign();
    initUploadZone();
    initFlashDismiss();
    initSelectAll();
    initDataTables();
    initImportProgress();
    initTheme();
    initBulkActions();
});

/* ===== Sidebar Toggle ===== */
function initSidebar() {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
        // Close on outside click (mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')
                && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }
}

/* ===== Global Search ===== */
function initGlobalSearch() {
    const input = document.getElementById('globalSearch');
    const results = document.getElementById('searchResults');
    if (!input || !results) return;

    let debounceTimer;
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const q = input.value.trim();
        if (q.length < 2) { results.classList.remove('show'); return; }
        debounceTimer = setTimeout(async () => {
            try {
                const resp = await fetch(`index.php?page=api&action=search_leads&q=${encodeURIComponent(q)}`);
                const data = await resp.json();
                if (data.length === 0) {
                    results.innerHTML = '<div class="search-result-item" style="justify-content:center;color:var(--text-muted)">No results found</div>';
                } else {
                    results.innerHTML = data.map(l => `
                        <a href="index.php?page=leads&action=view&id=${l.id}" class="search-result-item">
                            <div>
                                <strong>${escapeHtml(l.customer_name)}</strong>
                                <span style="color:var(--text-muted);margin-left:8px;font-size:12px">${escapeHtml(l.phone_number || '')}</span>
                            </div>
                            <span class="status-pill" style="--pill-color:${getStatusColor(l.status)}">${l.status}</span>
                        </a>
                    `).join('');
                }
                results.classList.add('show');
            } catch (e) {
                console.error('Search error:', e);
            }
        }, 300);
    });

    // Close search results on outside click
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.classList.remove('show');
        }
    });
}

/* ===== Quick Status Change (Inline in table) ===== */
function initQuickStatusChange() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', async function() {
            const leadId = this.dataset.leadId;
            const newStatus = this.value;
            try {
                const form = new FormData();
                form.append('lead_id', leadId);
                form.append('status', newStatus);
                form.append('csrf_token', getCsrfToken());

                const resp = await fetch('index.php?page=api&action=quick_status', {
                    method: 'POST', body: form
                });
                const data = await resp.json();
                if (data.success) {
                    // Update pill color
                    this.style.setProperty('--pill-color', getStatusColor(newStatus));
                    showToast('Status updated to ' + newStatus, 'success');
                } else {
                    showToast('Failed to update status', 'error');
                }
            } catch (e) {
                showToast('Error updating status', 'error');
            }
        });
    });
}

/* ===== Quick Assign (Inline in table) ===== */
function initQuickAssign() {
    document.querySelectorAll('.assign-select').forEach(select => {
        select.addEventListener('change', async function() {
            const leadId = this.dataset.leadId;
            const agentId = this.value;
            try {
                const form = new FormData();
                form.append('lead_id', leadId);
                form.append('agent_id', agentId);
                form.append('csrf_token', getCsrfToken());

                const resp = await fetch('index.php?page=api&action=quick_assign', {
                    method: 'POST', body: form
                });
                const data = await resp.json();
                if (data.success) {
                    showToast('Assigned to ' + data.agent_name, 'success');
                }
            } catch (e) {
                showToast('Error assigning lead', 'error');
            }
        });
    });
}

/* ===== Upload Zone ===== */
function initUploadZone() {
    const zone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('importFile');
    const fileName = document.getElementById('fileName');
    if (!zone || !fileInput) return;

    ['dragenter', 'dragover'].forEach(ev => {
        zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(ev => {
        zone.addEventListener(ev, (e) => { e.preventDefault(); zone.classList.remove('dragover'); });
    });
    zone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            updateFileName();
        }
    });
    fileInput.addEventListener('change', updateFileName);

    function updateFileName() {
        if (fileInput.files.length && fileName) {
            fileName.textContent = '📄 ' + fileInput.files[0].name;
        }
    }
}

/* ===== Flash Dismiss ===== */
function initFlashDismiss() {
    const flash = document.getElementById('flashAlert');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-10px)';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    }
}

/* ===== Select All Checkbox ===== */
function initSelectAll() {
    const selectAll = document.getElementById('selectAll');
    if (!selectAll) return;
    selectAll.addEventListener('change', () => {
        document.querySelectorAll('.lead-check').forEach(cb => {
            cb.checked = selectAll.checked;
        });
    });
}

/* ===== DataTables Integration ===== */
function initDataTables() {
    // Only init if jQuery and DataTables are loaded
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') return;

    // Leads Table
    const leadsTable = document.getElementById('leadsTable');
    if (leadsTable) {
        jQuery('#leadsTable').DataTable({
            paging: false,           // We use server-side pagination
            searching: false,        // We use server-side search
            info: false,
            ordering: true,
            responsive: true,
            order: [],               // No default sort (keep server order)
            dom: 'Brt',              // Buttons + table (no search/paging UI)
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-ghost btn-xs dt-export-btn',
                    title: 'DSA_Leads_' + new Date().toISOString().split('T')[0],
                    exportOptions: { columns: [1,2,3,4,5,6,7,8,9] }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-ghost btn-xs dt-export-btn',
                    title: 'DSA_Leads_' + new Date().toISOString().split('T')[0],
                    exportOptions: { columns: [1,2,3,4,5,6,7,8,9] }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-ghost btn-xs dt-export-btn',
                    title: 'DSA LeadFlow - Leads Report',
                    exportOptions: { columns: [1,2,3,4,5,6,7,8,9] }
                }
            ],
            columnDefs: [
                { orderable: false, targets: [0, 10] },  // Checkbox and Actions columns
                { type: 'num-fmt', targets: [5] },       // Amount column
            ],
            language: {
                emptyTable: 'No leads match your filters'
            }
        });
    }

    // Activity Log Table
    const activityTable = document.getElementById('activityTable');
    if (activityTable) {
        jQuery('#activityTable').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: true,
            responsive: true,
            order: [[0, 'desc']],
            dom: 'Brt',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    className: 'btn btn-ghost btn-xs dt-export-btn',
                    title: 'DSA_Activity_' + new Date().toISOString().split('T')[0],
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-ghost btn-xs dt-export-btn',
                    title: 'DSA LeadFlow - Activity Log',
                }
            ],
            language: {
                emptyTable: 'No activity recorded yet'
            }
        });
    }

    // Dashboard Recent Leads
    const dashTable = document.getElementById('dashRecentTable');
    if (dashTable) {
        jQuery('#dashRecentTable').DataTable({
            paging: false, searching: false, info: false,
            ordering: true, responsive: true, dom: 'rt',
            order: [],
            language: { emptyTable: 'No recent leads' }
        });
    }
}

/* ===== Import Job Progress Polling ===== */
function initImportProgress() {
    const progressEl = document.getElementById('importProgress');
    if (!progressEl) return;

    const jobId = progressEl.dataset.jobId;
    if (!jobId) return;

    const progressBar = progressEl.querySelector('.progress-fill');
    const progressText = progressEl.querySelector('.progress-text');
    const progressStats = progressEl.querySelector('.progress-stats');

    function pollStatus() {
        fetch(`index.php?page=api&action=job_status&id=${jobId}`)
            .then(r => r.json())
            .then(data => {
                if (data.error) return;

                const pct = data.progress || 0;
                if (progressBar) progressBar.style.width = pct + '%';
                if (progressText) progressText.textContent = `${pct}% — ${data.processed_items || 0} / ${data.total_items || '?'} rows`;

                if (progressStats) {
                    progressStats.innerHTML = `
                        <span class="text-success"><i class="fas fa-check"></i> ${data.imported_rows || 0} imported</span>
                        <span class="text-warning"><i class="fas fa-forward"></i> ${data.skipped_rows || 0} skipped</span>
                        <span class="text-danger"><i class="fas fa-times"></i> ${data.error_rows || 0} errors</span>
                    `;
                }

                if (data.status === 'completed') {
                    progressEl.classList.add('progress-done');
                    showToast(`Import complete! ${data.imported_rows} leads imported.`, 'success');
                } else if (data.status === 'failed') {
                    progressEl.classList.add('progress-failed');
                    showToast('Import failed: ' + (data.error_message || 'Unknown error'), 'error');
                } else {
                    setTimeout(pollStatus, 2000); // Poll every 2 seconds
                }
            })
            .catch(() => setTimeout(pollStatus, 5000));
    }

    pollStatus();
}

/* ===== Theme Switching ===== */
function initTheme() {
    const toggle = document.getElementById('themeToggle');
    if (!toggle) return;

    const icon = toggle.querySelector('i');
    
    // Set initial icon based on class applied by head script
    if (document.body.classList.contains('light-mode')) {
        icon.className = 'fas fa-sun';
    }

    toggle.addEventListener('click', () => {
        const isLight = document.body.classList.toggle('light-mode');
        document.documentElement.classList.toggle('light-mode');
        
        // Update icon
        icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
        
        // Persist
        localStorage.setItem('theme', isLight ? 'light' : 'dark');
        
        // Redraw DataTables to apply theme changes if needed
        if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
            jQuery('.dataTable').DataTable().draw();
        }
    });
}

/* ===== Helpers ===== */
function getCsrfToken() {
    // Try hidden input first
    const input = document.querySelector('input[name="csrf_token"]');
    if (input) return input.value;
    // Fallback to meta tag
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) return meta.getAttribute('content');
    return '';
}

function getStatusColor(status) {
    const colors = {
        'New': '#6366f1', 'Contacted': '#f59e0b', 'Documentation': '#3b82f6',
        'Submitted': '#8b5cf6', 'Approved': '#10b981', 'Disbursed': '#06d6a0',
        'Rejected': '#ef4444'
    };
    return colors[status] || '#6b7280';
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function showToast(message, type = 'success') {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:9999;
        padding:12px 20px; border-radius:10px; font-size:13px; font-weight:500;
        display:flex; align-items:center; gap:8px;
        animation: slideUp 0.3s ease;
        ${type === 'success'
            ? 'background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:#6ee7b7;'
            : 'background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#fca5a5;'
        }
    `;
    const icon = type === 'success' ? '✓' : '✕';
    toast.innerHTML = `<span>${icon}</span> ${escapeHtml(message)}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add animation keyframe for toast
const style = document.createElement('style');
style.textContent = `@keyframes slideUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }`;
document.head.appendChild(style);
/* ===== Bulk Actions ===== */
function initBulkActions() {
    const bar = document.getElementById('bulkActionBar');
    const countDisplay = document.getElementById('selectedCount');
    const applyBtn = document.getElementById('applyBulk');
    const cancelBtn = document.getElementById('cancelBulk');
    if (!bar) return;

    const updateSelection = () => {
        const checked = document.querySelectorAll('.lead-check:checked');
        if (checked.length > 0) {
            countDisplay.textContent = checked.length;
            bar.classList.add('active');
        } else {
            bar.classList.remove('active');
        }
    };

    // Listen for checkbox changes
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('lead-check') || e.target.id === 'selectAll') {
            updateSelection();
        }
    });

    cancelBtn.addEventListener('click', () => {
        document.querySelectorAll('.lead-check').forEach(cb => cb.checked = false);
        const selectAll = document.getElementById('selectAll');
        if (selectAll) selectAll.checked = false;
        updateSelection();
    });

    applyBtn.addEventListener('click', async () => {
        const leadIds = Array.from(document.querySelectorAll('.lead-check:checked')).map(cb => cb.value);
        const newStatus = document.getElementById('bulkStatus').value;
        const newAgent = document.getElementById('bulkAgent') ? document.getElementById('bulkAgent').value : '';

        if (!newStatus && !newAgent) {
            showToast('Please select a status or agent to update', 'error');
            return;
        }

        applyBtn.disabled = true;
        applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';

        try {
            const form = new FormData();
            form.append('lead_ids', JSON.stringify(leadIds));
            if (newStatus) form.append('status', newStatus);
            if (newAgent) form.append('agent_id', newAgent);
            form.append('csrf_token', getCsrfToken());

            const resp = await fetch('index.php?page=api&action=bulk_update', {
                method: 'POST', body: form
            });
            const data = await resp.json();

            if (data.success) {
                showToast(`Successfully updated ${leadIds.length} leads`, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showToast(data.error || 'Failed to apply bulk update', 'error');
                applyBtn.disabled = false;
                applyBtn.textContent = 'Apply';
            }
        } catch (e) {
            showToast('Error processing bulk update', 'error');
            applyBtn.disabled = false;
            applyBtn.textContent = 'Apply';
        }
    });
}
