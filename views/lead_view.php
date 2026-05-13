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
                        <div class="detail-row">
                            <span class="detail-label">Phone</span>
                            <span class="detail-value">
                                <?= htmlspecialchars(Security::mask($lead['phone_number'] ?? '-', 'phone')) ?>
                                <?php if ($lead['phone_number'] && Security::isAdmin()): ?>
                                    <a href="tel:<?= $lead['phone_number'] ?>"><i class="fas fa-phone"></i></a>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detail-row"><span class="detail-label">Alt Phone</span><span class="detail-value"><?= htmlspecialchars(Security::mask($lead['alt_phone'] ?? '-', 'phone')) ?></span></div>
                        <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value"><?= htmlspecialchars(Security::mask($lead['email_address'] ?? '-', 'email')) ?></span></div>
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

        <!-- Vehicle Details (if applicable) -->
        <?php 
        $isVehicleLoan = in_array($lead['loan_type'] ?? '', ['Used Car Loan','Used Bike Loan','Used Commercial Vehicle Loan','New Car Loan','New Bike Loan']);
        if ($isVehicleLoan && !empty($lead['vehicle_make'])): 
        ?>
        <div class="card" style="margin-top:20px; border-left:4px solid #f59e0b">
            <div class="card-header"><h3><i class="fas fa-car" style="color:#f59e0b"></i> Vehicle Details</h3></div>
            <div class="card-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0">
                    <div class="detail-grid">
                        <div class="detail-row"><span class="detail-label">Make</span><span class="detail-value"><?= htmlspecialchars($lead['vehicle_make']) ?></span></div>
                        <div class="detail-row"><span class="detail-label">Model</span><span class="detail-value"><?= htmlspecialchars($lead['vehicle_model'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Year</span><span class="detail-value"><?= $lead['vehicle_year'] ?? '-' ?></span></div>
                        <div class="detail-row"><span class="detail-label">Reg. No.</span><span class="detail-value"><strong><?= htmlspecialchars($lead['vehicle_reg_no'] ?? '-') ?></strong></span></div>
                    </div>
                    <div class="detail-grid">
                        <div class="detail-row"><span class="detail-label">KM Driven</span><span class="detail-value"><?= $lead['vehicle_km'] ? number_format($lead['vehicle_km']) . ' km' : '-' ?></span></div>
                        <div class="detail-row"><span class="detail-label">Fuel</span><span class="detail-value"><?= htmlspecialchars($lead['vehicle_fuel'] ?? '-') ?></span></div>
                        <div class="detail-row"><span class="detail-label">Owner</span><span class="detail-value"><?= $lead['vehicle_owner'] ? $lead['vehicle_owner'] . ' Owner' : '-' ?></span></div>
                        <div class="detail-row"><span class="detail-label">Vehicle Price</span><span class="detail-value">₹<?= number_format($lead['vehicle_price'] ?? 0) ?></span></div>
                    </div>
                </div>
                <?php if (($lead['vehicle_hypothecated'] ?? 'No') === 'Yes'): ?>
                <div style="margin-top:12px; padding:10px 14px; background:rgba(239,68,68,0.08); border-radius:8px; border:1px solid rgba(239,68,68,0.2)">
                    <span style="color:#ef4444; font-size:12px; font-weight:600"><i class="fas fa-exclamation-triangle"></i> Vehicle is Hypothecated — NOC required from previous financer</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Multi-Bank Rate Comparison (Vehicle Loans Only) -->
        <?php if ($isVehicleLoan && !empty($data['bank_rates'])): ?>
        <div class="card" style="margin-top:20px; border-left:4px solid #6366f1">
            <div class="card-header"><h3><i class="fas fa-university" style="color:#6366f1"></i> Bank Rate Comparison</h3></div>
            <div class="card-body" style="overflow-x:auto">
                <table style="width:100%; border-collapse:collapse; font-size:13px">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border)">
                            <th style="text-align:left; padding:10px 8px; color:var(--text-dim); font-weight:600">Bank</th>
                            <th style="text-align:center; padding:10px 8px; color:var(--text-dim); font-weight:600">Rate (p.a.)</th>
                            <th style="text-align:center; padding:10px 8px; color:var(--text-dim); font-weight:600">Max Tenure</th>
                            <th style="text-align:center; padding:10px 8px; color:var(--text-dim); font-weight:600">LTV</th>
                            <th style="text-align:center; padding:10px 8px; color:var(--text-dim); font-weight:600">Processing</th>
                            <th style="text-align:right; padding:10px 8px; color:var(--text-dim); font-weight:600">EMI (3yr)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $loanAmt = $lead['loan_amount'] ?: 500000;
                        $bestRate = PHP_FLOAT_MAX;
                        foreach ($data['bank_rates'] as $br) {
                            $bestRate = min($bestRate, $br['interest_rate']);
                        }
                        foreach ($data['bank_rates'] as $br): 
                            $r = $br['interest_rate'] / 12 / 100;
                            $n = 36;
                            $emi = $r > 0 ? $loanAmt * $r * pow(1+$r, $n) / (pow(1+$r, $n) - 1) : $loanAmt / $n;
                            $isBest = $br['interest_rate'] == $bestRate;
                        ?>
                        <tr style="border-bottom:1px solid var(--border); <?= $isBest ? 'background:rgba(16,185,129,0.05)' : '' ?>">
                            <td style="padding:10px 8px; font-weight:600">
                                <?= htmlspecialchars($br['bank_name']) ?>
                                <?php if ($isBest): ?><span style="color:#10b981; font-size:10px; margin-left:4px">★ BEST</span><?php endif; ?>
                            </td>
                            <td style="text-align:center; padding:10px 8px; color:<?= $isBest ? '#10b981' : 'var(--text)' ?>; font-weight:700"><?= $br['interest_rate'] ?>%</td>
                            <td style="text-align:center; padding:10px 8px"><?= $br['max_tenure_years'] ?> yrs</td>
                            <td style="text-align:center; padding:10px 8px"><?= $br['max_ltv'] ?>%</td>
                            <td style="text-align:center; padding:10px 8px; color:var(--text-dim)"><?= $br['processing_fee'] ?></td>
                            <td style="text-align:right; padding:10px 8px; font-weight:700; color:<?= $isBest ? '#10b981' : '#f59e0b' ?>">₹<?= number_format(round($emi)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:12px; font-size:11px; color:var(--text-muted)">
                    <i class="fas fa-info-circle"></i> EMI calculated on ₹<?= number_format($loanAmt) ?> loan for 3 years. Actual rates may vary.
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Insurance Cross-Sell (for Approved/Disbursed vehicle leads) -->
        <?php if ($isVehicleLoan && in_array($lead['status'], ['Approved','Disbursed'])): ?>
        <div class="card" style="margin-top:20px; border-left:4px solid #10b981">
            <div class="card-header">
                <h3><i class="fas fa-shield-alt" style="color:#10b981"></i> Insurance Cross-Sell</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($data['insurance'])): ?>
                <div style="display:grid; gap:12px">
                    <?php foreach ($data['insurance'] as $ins): ?>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:14px; background:var(--surface-2); border-radius:10px">
                        <div>
                            <div style="font-weight:600; font-size:14px"><?= htmlspecialchars($ins['provider']) ?></div>
                            <div style="font-size:12px; color:var(--text-dim)"><?= $ins['policy_type'] ?> · <?= $ins['policy_number'] ?: 'Pending' ?></div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-weight:700; color:#10b981">₹<?= number_format($ins['premium']) ?></div>
                            <span class="status-pill" style="--pill-color:<?= $ins['status'] === 'Active' ? '#10b981' : '#f59e0b' ?>; font-size:10px"><?= $ins['status'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div style="margin-top:16px; padding:16px; background:linear-gradient(135deg, rgba(16,185,129,0.08), rgba(6,214,160,0.05)); border-radius:12px; border:1px solid rgba(16,185,129,0.15)">
                    <div style="font-weight:700; font-size:14px; margin-bottom:8px; color:#10b981"><i class="fas fa-lightbulb"></i> Upsell Opportunity</div>
                    <div style="font-size:12px; color:var(--text-dim); margin-bottom:12px">
                        Vehicle loan approved! Offer insurance to earn 15-20% commission on the premium.
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap">
                        <span style="padding:6px 12px; background:rgba(16,185,129,0.12); color:#10b981; border-radius:20px; font-size:11px; font-weight:600">Comprehensive ~₹<?= number_format(round(($lead['vehicle_price'] ?: $lead['loan_amount']) * 0.028)) ?></span>
                        <span style="padding:6px 12px; background:rgba(245,158,11,0.12); color:#f59e0b; border-radius:20px; font-size:11px; font-weight:600">Zero-Dep +₹<?= number_format(round(($lead['vehicle_price'] ?: $lead['loan_amount']) * 0.008)) ?></span>
                        <span style="padding:6px 12px; background:rgba(139,92,246,0.12); color:#8b5cf6; border-radius:20px; font-size:11px; font-weight:600">PA Cover ~₹500</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- RC Transfer Tracker (for Disbursed vehicle leads) -->
        <?php if ($isVehicleLoan && $lead['status'] === 'Disbursed' && !empty($data['rc_transfer'])): ?>
        <?php $rc = $data['rc_transfer']; ?>
        <div class="card" style="margin-top:20px; border-left:4px solid #8b5cf6">
            <div class="card-header"><h3><i class="fas fa-exchange-alt" style="color:#8b5cf6"></i> RC Transfer Status</h3></div>
            <div class="card-body">
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-bottom:16px">
                    <?php 
                    $steps = [
                        ['Form 29', $rc['form29_status'], 'fa-file-alt'],
                        ['Form 30', $rc['form30_status'], 'fa-file-contract'],
                        ['NOC', $rc['noc_status'] === 'Not Required' ? 'N/A' : $rc['noc_status'], 'fa-stamp'],
                        ['Hypothecation', $rc['hypothecation_status'] === 'None' ? 'N/A' : $rc['hypothecation_status'], 'fa-university'],
                    ];
                    foreach ($steps as $step):
                        $color = $step[1] === 'Completed' || $step[1] === 'Received' || $step[1] === 'Endorsed' ? '#10b981' : ($step[1] === 'Pending' ? '#f59e0b' : '#64748b');
                    ?>
                    <div style="text-align:center; padding:16px 8px; background:var(--surface-2); border-radius:10px; border-top:3px solid <?= $color ?>">
                        <i class="fas <?= $step[2] ?>" style="font-size:20px; color:<?= $color ?>; margin-bottom:8px; display:block"></i>
                        <div style="font-size:12px; font-weight:600"><?= $step[0] ?></div>
                        <div style="font-size:11px; color:<?= $color ?>; font-weight:700; margin-top:4px"><?= $step[1] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; gap:16px; font-size:13px">
                    <div><span style="color:var(--text-dim)">Seller:</span> <strong><?= htmlspecialchars($rc['seller_name'] ?? '-') ?></strong></div>
                    <div><span style="color:var(--text-dim)">Buyer:</span> <strong><?= htmlspecialchars($rc['buyer_name'] ?? '-') ?></strong></div>
                    <?php if ($rc['rto_name']): ?>
                    <div><span style="color:var(--text-dim)">RTO:</span> <strong><?= htmlspecialchars($rc['rto_name']) ?></strong></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Vehicle Inspection Report (if exists) -->
        <?php if ($isVehicleLoan && !empty($data['inspection'])): ?>
        <?php $insp = $data['inspection']; 
            $avg = round((($insp['exterior_score'] ?? 0) + ($insp['interior_score'] ?? 0) + ($insp['engine_score'] ?? 0) + ($insp['electrical_score'] ?? 0)) / 4, 1);
            $verdictColors = ['Approved' => '#10b981', 'Conditional' => '#f59e0b', 'Rejected' => '#ef4444'];
            $vc = $verdictColors[$insp['verdict']] ?? '#64748b';
        ?>
        <div class="card" style="margin-top:20px; border-left:4px solid #06b6d4">
            <div class="card-header">
                <h3><i class="fas fa-clipboard-check" style="color:#06b6d4"></i> Inspection Report</h3>
                <span class="status-pill" style="--pill-color:<?= $vc ?>"><?= $insp['verdict'] ?></span>
            </div>
            <div class="card-body">
                <div style="display:flex; gap:12px; margin-bottom:16px">
                    <div style="font-size:12px; color:var(--text-dim)"><i class="fas fa-user"></i> <?= htmlspecialchars($insp['inspector_name'] ?? 'N/A') ?></div>
                    <div style="font-size:12px; color:var(--text-dim)"><i class="fas fa-calendar"></i> <?= $insp['inspection_date'] ? date('M j, Y', strtotime($insp['inspection_date'])) : '-' ?></div>
                    <?php if ($insp['estimated_value']): ?>
                    <div style="font-size:12px; color:var(--text-dim)"><i class="fas fa-tag"></i> Estimated: ₹<?= number_format($insp['estimated_value']) ?></div>
                    <?php endif; ?>
                </div>
                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px; margin-bottom:16px">
                    <?php 
                    $scores = [
                        ['Exterior', $insp['exterior_score'], 'fa-car', '#6366f1'],
                        ['Interior', $insp['interior_score'], 'fa-couch', '#8b5cf6'],
                        ['Engine', $insp['engine_score'], 'fa-cog', '#f59e0b'],
                        ['Electrical', $insp['electrical_score'], 'fa-bolt', '#06b6d4'],
                    ];
                    foreach ($scores as $s):
                        $scoreColor = $s[1] >= 7 ? '#10b981' : ($s[1] >= 5 ? '#f59e0b' : '#ef4444');
                    ?>
                    <div style="text-align:center; padding:16px 8px; background:var(--surface-2); border-radius:10px">
                        <i class="fas <?= $s[2] ?>" style="font-size:18px; color:<?= $s[3] ?>; margin-bottom:8px; display:block"></i>
                        <div style="font-size:24px; font-weight:800; color:<?= $scoreColor ?>"><?= $s[1] ?? '-' ?></div>
                        <div style="font-size:11px; color:var(--text-dim)"><?= $s[0] ?> /10</div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; gap:16px; flex-wrap:wrap; font-size:12px">
                    <div><span style="color:var(--text-dim)">Tyres:</span> <strong><?= $insp['tyre_condition'] ?></strong></div>
                    <div><span style="color:var(--text-dim)">A/C:</span> <strong><?= $insp['ac_working'] ? '✅ Working' : '❌ Not Working' ?></strong></div>
                    <div><span style="color:var(--text-dim)">Accident:</span> <strong style="color:<?= $insp['accident_history'] === 'None' ? '#10b981' : '#ef4444' ?>"><?= $insp['accident_history'] ?></strong></div>
                    <?php if ($insp['flood_affected']): ?><div><strong style="color:#ef4444">⚠️ Flood Affected</strong></div><?php endif; ?>
                    <?php if ($insp['odometer_tampered']): ?><div><strong style="color:#ef4444">⚠️ Odometer Tampered</strong></div><?php endif; ?>
                </div>
                <?php if ($insp['remarks']): ?>
                <div style="margin-top:12px; padding:10px 14px; background:var(--surface-2); border-radius:8px; font-size:12px; color:var(--text-dim)">
                    <strong>Inspector Notes:</strong> <?= htmlspecialchars($insp['remarks']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Digital Vault (Documents) -->
        <div class="card" style="margin-top:24px">
            <div class="card-header">
                <div style="display:flex; justify-content:space-between; align-items:center; width:100%">
                    <h3><i class="fas fa-folder-open"></i> Digital Vault (KYC Documents)</h3>
                    <button class="btn btn-primary btn-xs" onclick="document.getElementById('uploadForm').style.display='block'; this.style.display='none'">
                        <i class="fas fa-plus"></i> Upload Document
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Quick Upload Form (Hidden by default) -->
                <div id="uploadForm" style="display:none; background:rgba(0,0,0,0.03); padding:16px; border-radius:8px; margin-bottom:20px; border:1px dashed #cbd5e1">
                    <form method="POST" action="index.php?page=document&action=upload" enctype="multipart/form-data">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="lead_id" value="<?= $lead['id'] ?>">
                        <div class="grid-2" style="gap:12px">
                            <div class="form-group">
                                <label style="font-size:12px">Document Type</label>
                                <select name="document_type" class="form-select" required>
                                    <option value="Aadhar Card">Aadhar Card</option>
                                    <option value="PAN Card">PAN Card</option>
                                    <option value="Salary Slips">Salary Slips</option>
                                    <option value="Bank Statement">Bank Statement</option>
                                    <option value="Income Tax Return">Income Tax Return (ITR)</option>
                                    <option value="Business Registration">Business Registration</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label style="font-size:12px">Select File</label>
                                <input type="file" name="document_file" class="form-control" required>
                            </div>
                        </div>
                        <div style="margin-top:12px; display:flex; gap:8px">
                            <button type="submit" class="btn btn-primary btn-sm">Upload File</button>
                            <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('uploadForm').style.display='none';">Cancel</button>
                        </div>
                    </form>
                </div>

                <?php if (!empty($data['documents'])): ?>
                <div class="document-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:16px">
                    <?php foreach ($data['documents'] as $doc): 
                        $icon = 'fa-file';
                        if (strpos($doc['file_type'], 'pdf') !== false) $icon = 'fa-file-pdf text-danger';
                        elseif (strpos($doc['file_type'], 'image') !== false) $icon = 'fa-file-image text-primary';
                        elseif (strpos($doc['file_type'], 'word') !== false) $icon = 'fa-file-word text-info';
                    ?>
                    <div class="document-item" style="border:1px solid #e2e8f0; border-radius:8px; padding:12px; position:relative; background:#fff; transition:all 0.2s hover:shadow-md">
                        <div style="font-size:24px; margin-bottom:8px"><i class="fas <?= $icon ?>"></i></div>
                        <div style="font-weight:600; font-size:13px; margin-bottom:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap" title="<?= htmlspecialchars($doc['document_type']) ?>">
                            <?= htmlspecialchars($doc['document_type']) ?>
                        </div>
                        <div style="font-size:11px; color:#64748b; margin-bottom:12px"><?= htmlspecialchars($doc['file_name']) ?></div>
                        
                        <div style="display:flex; justify-content:space-between; align-items:center">
                            <span class="status-pill" style="font-size:9px; --pill-color:<?= $doc['status'] === 'Verified' ? '#10b981' : ($doc['status'] === 'Rejected' ? '#ef4444' : '#f59e0b') ?>">
                                <?= $doc['status'] ?>
                            </span>
                            <div class="doc-actions">
                                <a href="index.php?page=document&action=download&id=<?= $doc['id'] ?>" class="btn-icon" title="Download"><i class="fas fa-download"></i></a>
                                <?php if (Security::isAdmin() || $doc['uploaded_by'] == Security::userId()): ?>
                                <a href="index.php?page=document&action=delete&id=<?= $doc['id'] ?>" class="btn-icon text-danger" onclick="return confirm('Delete document?')" title="Delete"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state-sm">
                    <i class="fas fa-cloud-upload-alt" style="opacity:0.2"></i>
                    <p>No documents uploaded yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payout History -->
        <div class="card" style="margin-top:24px">
            <div class="card-header">
                <div style="display:flex; justify-content:space-between; align-items:center; width:100%">
                    <h3><i class="fas fa-money-bill-wave"></i> Payout History</h3>
                    <span class="badge badge-info"><?= count($data['payouts'] ?? []) ?> records</span>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($data['payouts'])): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Bank</th>
                                <th>Account</th>
                                <th>Transaction ID</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['payouts'] as $p): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($p['payout_date'])) ?></td>
                                <td class="text-success" style="font-weight:600">₹<?= number_format($p['payout_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($p['bank_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['account_number'] ?? '-') ?></td>
                                <td><code style="font-size:11px"><?= htmlspecialchars($p['transaction_id'] ?? '-') ?></code></td>
                                <td><small><?= htmlspecialchars($p['remarks'] ?? '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state-sm">
                    <i class="fas fa-receipt" style="opacity:0.3"></i>
                    <p>No payout records found for this client.</p>
                </div>
                <?php endif; ?>
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

        <!-- WhatsApp Quick Connect -->
        <div class="card" style="margin-top:16px; background:rgba(37,211,102,0.05); border:1px solid rgba(37,211,102,0.2)">
            <div class="card-header" style="border-bottom-color:rgba(37,211,102,0.2)">
                <h3 style="color:#128c7e"><i class="fab fa-whatsapp"></i> WhatsApp Quick Actions</h3>
            </div>
            <div class="card-body">
                <div style="display:flex; flex-direction:column; gap:8px">
                    <a href="<?= WhatsAppHelper::getLink($lead['phone_number'], WhatsAppHelper::getTemplate('welcome', $lead)) ?>" target="_blank" class="btn btn-sm" style="background:#25d366; color:white">
                        <i class="fas fa-paper-plane"></i> Send Welcome Msg
                    </a>
                    <a href="<?= WhatsAppHelper::getLink($lead['phone_number'], WhatsAppHelper::getTemplate('docs_pending', $lead)) ?>" target="_blank" class="btn btn-sm" style="background:#128c7e; color:white">
                        <i class="fas fa-file-upload"></i> Ask for Documents
                    </a>
                    <a href="<?= WhatsAppHelper::getLink($lead['phone_number'], WhatsAppHelper::getTemplate('followup', $lead)) ?>" target="_blank" class="btn btn-ghost btn-sm" style="color:#128c7e; border-color:#128c7e">
                        <i class="fas fa-phone-slash"></i> "Couldn't Connect"
                    </a>
                    <?php if ($lead['status'] === 'Approved'): ?>
                    <a href="<?= WhatsAppHelper::getLink($lead['phone_number'], WhatsAppHelper::getTemplate('approved', $lead)) ?>" target="_blank" class="btn btn-sm" style="background:#075e54; color:white">
                        <i class="fas fa-trophy"></i> Send Approval Msg
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header"><h3><i class="fas fa-history"></i> Activity Timeline</h3></div>
            <div class="card-body">
                <div class="lead-meta">
                    <span><i class="fas fa-phone"></i> <?= htmlspecialchars(Security::mask($lead['phone_number'] ?? '-', 'phone')) ?></span>
                    <?php if ($lead['email_address']): ?>
                    <span><i class="fas fa-envelope"></i> <?= htmlspecialchars(Security::mask($lead['email_address'], 'email')) ?></span>
                    <?php endif; ?>
                </div>
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
