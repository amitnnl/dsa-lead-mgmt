<?php
/**
 * Public Vehicle Marketplace
 * No login required — customers browse available vehicles and apply for finance.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/classes/Database.php';

$db = new Database();
$make = $_GET['make'] ?? '';
$fuel = $_GET['fuel'] ?? '';
$priceMax = $_GET['price_max'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$where = "status = 'Available'";
$params = [];
if ($make) { $where .= " AND make = ?"; $params[] = $make; }
if ($fuel) { $where .= " AND fuel_type = ?"; $params[] = $fuel; }
if ($priceMax) { $where .= " AND asking_price <= ?"; $params[] = intval($priceMax); }

$orderBy = match($sort) {
    'price_low' => 'asking_price ASC',
    'price_high' => 'asking_price DESC',
    'km_low' => 'km_driven ASC',
    default => 'created_at DESC'
};

$vehicles = $db->fetchAll("SELECT * FROM vehicles WHERE $where ORDER BY $orderBy", $params);
$makes = $db->fetchAll("SELECT DISTINCT make FROM vehicles WHERE status = 'Available' ORDER BY make");
$totalCount = count($vehicles);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Used Vehicles for Sale | DSA LeadFlow</title>
    <meta name="description" content="Browse <?= $totalCount ?>+ verified used cars, bikes & commercial vehicles. Get instant finance with lowest EMI. No middlemen.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        :root { --primary:#6366f1; --primary-hover:#818cf8; --bg:#0a0a1a; --surface:#12122a; --surface-2:#1a1a3e; --text:#e2e8f0; --text-dim:#94a3b8; --text-muted:#64748b; --border:rgba(255,255,255,0.07); --success:#10b981; --warning:#f59e0b; }
        body { font-family:'Inter',system-ui,sans-serif; background:var(--bg); color:var(--text); line-height:1.6; }

        .navbar { padding:16px 24px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); background:var(--surface); position:sticky; top:0; z-index:50; }
        .navbar .brand { font-size:18px; font-weight:800; color:var(--text); text-decoration:none; display:flex; align-items:center; gap:8px; }
        .navbar .brand i { color:var(--warning); }
        .nav-links { display:flex; gap:12px; }
        .nav-links a { color:var(--text-dim); text-decoration:none; font-size:13px; font-weight:600; padding:8px 16px; border-radius:8px; transition:all 0.2s; }
        .nav-links a:hover, .nav-links a.active { background:var(--surface-2); color:var(--text); }

        .hero { text-align:center; padding:50px 20px 30px; background:linear-gradient(135deg, rgba(99,102,241,0.08), rgba(245,158,11,0.05)); }
        .hero h1 { font-size:clamp(24px, 4vw, 36px); font-weight:800; margin-bottom:8px; }
        .hero h1 span { color:var(--warning); }
        .hero p { color:var(--text-dim); font-size:15px; }
        .hero-stats { display:flex; justify-content:center; gap:40px; margin-top:20px; }
        .hero-stats div { text-align:center; }
        .hero-stats .hs-val { font-size:24px; font-weight:800; color:var(--primary-hover); }
        .hero-stats .hs-lbl { font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; }

        .container { max-width:1200px; margin:0 auto; padding:0 20px; }

        .filters { display:flex; gap:10px; padding:20px 0; flex-wrap:wrap; align-items:center; }
        .filter-select { padding:10px 14px; background:var(--surface); border:1px solid var(--border); border-radius:10px; color:var(--text); font-size:13px; font-family:inherit; outline:none; cursor:pointer; }
        .filter-select:focus { border-color:var(--primary); }
        .filter-count { font-size:13px; color:var(--text-dim); margin-left:auto; }

        .vehicle-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:20px; padding-bottom:60px; }

        .v-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; transition:all 0.3s ease; }
        .v-card:hover { transform:translateY(-6px); border-color:rgba(99,102,241,0.3); box-shadow:0 12px 40px rgba(0,0,0,0.3); }
        .v-img { height:180px; background:linear-gradient(135deg, var(--surface-2), #222250); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; }
        .v-img img { width:100%; height:100%; object-fit:cover; }
        .v-img .v-badge { position:absolute; top:10px; right:10px; padding:4px 10px; background:rgba(0,0,0,0.7); border-radius:20px; font-size:10px; font-weight:700; }
        .v-img .v-year { position:absolute; top:10px; left:10px; padding:4px 10px; background:rgba(0,0,0,0.7); border-radius:20px; font-size:10px; font-weight:700; color:#fff; }
        .v-body { padding:18px; }
        .v-title { font-size:16px; font-weight:700; margin-bottom:2px; }
        .v-sub { font-size:12px; color:var(--text-dim); margin-bottom:12px; }
        .v-price { font-size:22px; font-weight:800; color:var(--primary-hover); margin-bottom:12px; }
        .v-specs { display:flex; gap:14px; font-size:11px; color:var(--text-muted); padding:10px 0; border-top:1px solid var(--border); border-bottom:1px solid var(--border); margin-bottom:14px; }
        .v-specs span { display:flex; align-items:center; gap:4px; }
        .v-actions { display:flex; gap:8px; }
        .v-btn { flex:1; padding:10px; text-align:center; border-radius:10px; font-size:13px; font-weight:700; text-decoration:none; transition:all 0.2s; cursor:pointer; border:none; font-family:inherit; }
        .v-btn-primary { background:linear-gradient(135deg, var(--primary), #8b5cf6); color:#fff; }
        .v-btn-primary:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(99,102,241,0.3); }
        .v-btn-ghost { background:var(--surface-2); color:var(--text-dim); }
        .v-btn-ghost:hover { color:var(--text); }

        .empty { text-align:center; padding:80px 20px; color:var(--text-muted); }
        .empty i { font-size:48px; margin-bottom:16px; display:block; opacity:0.3; }

        .footer { text-align:center; padding:30px; color:var(--text-muted); font-size:12px; border-top:1px solid var(--border); }
        .footer a { color:var(--primary-hover); }

        /* Inquiry Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:999; align-items:center; justify-content:center; padding:20px; }
        .modal-overlay.active { display:flex; }
        .modal { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:32px; max-width:420px; width:100%; animation:slideUp 0.3s ease; }
        @keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }
        .modal h3 { font-size:18px; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
        .modal h3 i { color:var(--warning); }
        .m-field { margin-bottom:14px; }
        .m-field label { display:block; font-size:11px; font-weight:600; color:var(--text-dim); margin-bottom:4px; text-transform:uppercase; }
        .m-field input { width:100%; padding:10px 14px; background:var(--surface-2); border:1px solid var(--border); border-radius:8px; color:var(--text); font-size:14px; font-family:inherit; outline:none; }
        .m-field input:focus { border-color:var(--primary); }

        @media (max-width:768px) { .vehicle-grid { grid-template-columns:1fr; } .hero-stats { gap:20px; } .filters { flex-direction:column; } .nav-links { display:none; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="browse.php" class="brand"><i class="fas fa-car"></i> DSA AutoMart</a>
        <div class="nav-links">
            <a href="browse.php" class="active">Browse Vehicles</a>
            <a href="emi-calculator.php">EMI Calculator</a>
            <a href="index.php?page=login">Agent Login</a>
        </div>
    </nav>

    <div class="hero">
        <h1>Find Your Perfect <span>Used Vehicle</span></h1>
        <p>Verified vehicles with instant finance at the lowest EMI. No middlemen.</p>
        <div class="hero-stats">
            <div><div class="hs-val"><?= $totalCount ?></div><div class="hs-lbl">Vehicles Available</div></div>
            <div><div class="hs-val">9.8%</div><div class="hs-lbl">Rate Starting</div></div>
            <div><div class="hs-val">100%</div><div class="hs-lbl">On-Road Finance</div></div>
        </div>
    </div>

    <div class="container">
        <div class="filters">
            <select class="filter-select" onchange="applyFilter('make', this.value)">
                <option value="">All Makes</option>
                <?php foreach ($makes as $m): ?>
                <option value="<?= htmlspecialchars($m['make']) ?>" <?= $make === $m['make'] ? 'selected' : '' ?>><?= htmlspecialchars($m['make']) ?></option>
                <?php endforeach; ?>
            </select>
            <select class="filter-select" onchange="applyFilter('fuel', this.value)">
                <option value="">All Fuel Types</option>
                <?php foreach (FUEL_TYPES as $ft): ?>
                <option value="<?= $ft ?>" <?= $fuel === $ft ? 'selected' : '' ?>><?= $ft ?></option>
                <?php endforeach; ?>
            </select>
            <select class="filter-select" onchange="applyFilter('price_max', this.value)">
                <option value="">Any Budget</option>
                <option value="300000" <?= $priceMax == '300000' ? 'selected' : '' ?>>Under ₹3 Lakh</option>
                <option value="500000" <?= $priceMax == '500000' ? 'selected' : '' ?>>Under ₹5 Lakh</option>
                <option value="1000000" <?= $priceMax == '1000000' ? 'selected' : '' ?>>Under ₹10 Lakh</option>
                <option value="2000000" <?= $priceMax == '2000000' ? 'selected' : '' ?>>Under ₹20 Lakh</option>
            </select>
            <select class="filter-select" onchange="applyFilter('sort', this.value)">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low → High</option>
                <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High → Low</option>
                <option value="km_low" <?= $sort === 'km_low' ? 'selected' : '' ?>>Lowest KM</option>
            </select>
            <span class="filter-count"><?= $totalCount ?> vehicles found</span>
        </div>

        <div class="vehicle-grid">
            <?php if (empty($vehicles)): ?>
            <div class="empty" style="grid-column:1/-1">
                <i class="fas fa-search"></i>
                <h3>No vehicles found</h3>
                <p>Try adjusting your filters.</p>
            </div>
            <?php else: ?>
            <?php foreach ($vehicles as $v): 
                $r = 10.5 / 12 / 100; $n = 36;
                $emi = $v['asking_price'] * $r * pow(1+$r, $n) / (pow(1+$r, $n) - 1);
            ?>
            <div class="v-card">
                <div class="v-img">
                    <?php if ($v['photo_url']): ?>
                    <img src="<?= htmlspecialchars($v['photo_url']) ?>" alt="<?= htmlspecialchars($v['make'].' '.$v['model']) ?>" loading="lazy">
                    <?php else: ?>
                    <i class="fas fa-<?= $v['body_type'] === 'Bike' || $v['body_type'] === 'Scooter' ? 'motorcycle' : 'car' ?>" style="font-size:52px; color:var(--text-muted); opacity:0.3"></i>
                    <?php endif; ?>
                    <span class="v-year"><?= $v['year'] ?></span>
                    <span class="v-badge" style="color:var(--success)">EMI ₹<?= number_format(round($emi)) ?>/mo</span>
                </div>
                <div class="v-body">
                    <div class="v-title"><?= htmlspecialchars($v['make'].' '.$v['model']) ?></div>
                    <div class="v-sub"><?= htmlspecialchars($v['variant'] ?? '') ?> · <?= $v['fuel_type'] ?> · <?= $v['transmission'] ?></div>
                    <div class="v-price">₹<?= number_format($v['asking_price']) ?></div>
                    <div class="v-specs">
                        <span><i class="fas fa-tachometer-alt"></i> <?= number_format($v['km_driven']) ?> km</span>
                        <span><i class="fas fa-user"></i> <?= $v['owner_count'] ?> Owner</span>
                        <span><i class="fas fa-palette"></i> <?= htmlspecialchars($v['color'] ?? '-') ?></span>
                    </div>
                    <div class="v-actions">
                        <button class="v-btn v-btn-primary" onclick="openInquiry('<?= htmlspecialchars($v['make'].' '.$v['model'].' '.$v['variant'], ENT_QUOTES) ?>', <?= $v['asking_price'] ?>)">
                            <i class="fas fa-paper-plane"></i> Get Finance
                        </button>
                        <a href="emi-calculator.php" class="v-btn v-btn-ghost"><i class="fas fa-calculator"></i> EMI</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inquiry Modal -->
    <div class="modal-overlay" id="inquiryModal">
        <div class="modal">
            <h3><i class="fas fa-car"></i> Get Vehicle Finance</h3>
            <div id="modalVehicle" style="font-size:14px; font-weight:600; margin-bottom:4px"></div>
            <div id="modalPrice" style="font-size:13px; color:var(--text-dim); margin-bottom:16px"></div>
            <div class="m-field"><label>Your Name *</label><input type="text" id="mName" placeholder="Full Name"></div>
            <div class="m-field"><label>Phone Number *</label><input type="tel" id="mPhone" placeholder="9876543210" maxlength="10"></div>
            <input type="hidden" id="mVehicle">
            <input type="hidden" id="mAmount">
            <div style="display:flex; gap:8px; margin-top:16px">
                <button class="v-btn v-btn-ghost" onclick="closeInquiry()" style="flex:1">Cancel</button>
                <button class="v-btn v-btn-primary" id="mSubmitBtn" onclick="submitVehicleInquiry()" style="flex:2"><i class="fas fa-check-circle"></i> Submit</button>
            </div>
            <div id="mMessage" style="display:none; margin-top:12px; padding:10px; border-radius:8px; text-align:center; font-size:12px; font-weight:600"></div>
        </div>
    </div>

    <div class="footer">
        Powered by <a href="index.php">DSA LeadFlow</a> · <a href="emi-calculator.php">EMI Calculator</a>
    </div>

    <script>
    function applyFilter(key, value) {
        const url = new URL(window.location);
        if (value) url.searchParams.set(key, value);
        else url.searchParams.delete(key);
        window.location = url;
    }

    function openInquiry(vehicle, price) {
        document.getElementById('modalVehicle').textContent = vehicle;
        document.getElementById('modalPrice').textContent = '₹' + price.toLocaleString('en-IN');
        document.getElementById('mVehicle').value = vehicle;
        document.getElementById('mAmount').value = price;
        document.getElementById('inquiryModal').classList.add('active');
    }
    function closeInquiry() {
        document.getElementById('inquiryModal').classList.remove('active');
    }

    function submitVehicleInquiry() {
        const name = document.getElementById('mName').value.trim();
        const phone = document.getElementById('mPhone').value.trim();
        const vehicle = document.getElementById('mVehicle').value;
        const amount = document.getElementById('mAmount').value;
        const msg = document.getElementById('mMessage');
        const btn = document.getElementById('mSubmitBtn');

        if (!name || !phone) {
            msg.style.display = 'block'; msg.style.background = 'rgba(239,68,68,0.1)'; msg.style.color = '#ef4444';
            msg.textContent = 'Please enter your name and phone.'; return;
        }

        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        const parts = vehicle.split(' ');
        const fd = new FormData();
        fd.append('name', name); fd.append('phone', phone);
        fd.append('loan_type', 'Used Car Loan'); fd.append('amount', amount);
        fd.append('vehicle_make', parts[0] || ''); fd.append('vehicle_model', parts.slice(1).join(' ') || '');

        fetch('index.php?page=api&action=public_inquiry', { method:'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                msg.style.display = 'block';
                if (data.success) {
                    msg.style.background = 'rgba(16,185,129,0.1)'; msg.style.color = '#10b981';
                    msg.textContent = '✅ ' + data.message;
                    btn.innerHTML = '<i class="fas fa-check"></i> Done!';
                    btn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                } else {
                    msg.style.background = 'rgba(239,68,68,0.1)'; msg.style.color = '#ef4444';
                    msg.textContent = '❌ ' + (data.error || 'Error.'); btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Submit';
                }
            })
            .catch(() => { msg.style.display='block'; msg.style.color='#ef4444'; msg.textContent='Network error.'; btn.disabled=false; btn.innerHTML='<i class="fas fa-check-circle"></i> Submit'; });
    }
    </script>
</body>
</html>
