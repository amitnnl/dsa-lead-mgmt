<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['pageTitle'] ?? 'DSA LeadFlow') ?></title>
    <meta name="description" content="DSA Lead Management System - Track, manage, and convert leads efficiently">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="assets/css/app.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#226e54">
    <link rel="apple-touch-icon" href="assets/img/icon-192.png">
    <script>
        // Immediate theme check to prevent flash
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.add('light-mode');
            document.addEventListener('DOMContentLoaded', () => document.body.classList.add('light-mode'));
        }
    </script>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-bolt"></i></div>
            <span class="brand-text">DSA LeadFlow</span>
        </div>

        <nav class="sidebar-nav">
            <?php if ($_SESSION['user_role'] === 'partner'): ?>
            <a href="index.php?page=partner" class="nav-item <?= ($data['page'] ?? '') === 'partner_dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Partner Dashboard</span>
            </a>
            <a href="index.php?page=partner&action=submit_lead" class="nav-item <?= ($data['page'] ?? '') === 'partner_submit' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i><span>Submit Lead</span>
            </a>
            <a href="index.php?page=partner&action=payouts" class="nav-item <?= ($data['page'] ?? '') === 'partner_payouts' ? 'active' : '' ?>">
                <i class="fas fa-hand-holding-usd"></i><span>My Payouts</span>
            </a>
            <?php elseif ($_SESSION['user_role'] === 'dealer'): ?>
            <a href="index.php?page=dealer" class="nav-item <?= ($data['page'] ?? '') === 'dealer_dashboard' ? 'active' : '' ?>">
                <i class="fas fa-store"></i><span>Dealer Dashboard</span>
            </a>
            <a href="index.php?page=dealer&action=my_vehicles" class="nav-item <?= ($data['page'] ?? '') === 'dealer_vehicles' ? 'active' : '' ?>">
                <i class="fas fa-car"></i><span>My Vehicles</span>
            </a>
            <a href="index.php?page=dealer&action=add_vehicle" class="nav-item <?= ($data['page'] ?? '') === 'dealer_vehicle_form' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i><span>List Vehicle</span>
            </a>
            <a href="index.php?page=dealer&action=inquiries" class="nav-item <?= ($data['page'] ?? '') === 'dealer_inquiries' ? 'active' : '' ?>">
                <i class="fas fa-envelope-open"></i><span>Inquiries</span>
            </a>
            <?php else: ?>
            <a href="index.php?page=dashboard" class="nav-item <?= ($data['page'] ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i><span>Dashboard</span>
            </a>
            <a href="index.php?page=leads" class="nav-item <?= in_array($data['page'] ?? '', ['leads','lead_form','lead_view']) ? 'active' : '' ?>">
                <i class="fas fa-users"></i><span>Leads</span>
            </a>
            <a href="index.php?page=import" class="nav-item <?= in_array($data['page'] ?? '', ['import','import_map']) ? 'active' : '' ?>">
                <i class="fas fa-file-import"></i><span>Smart Import</span>
            </a>
            <a href="index.php?page=activity" class="nav-item <?= ($data['page'] ?? '') === 'activity' ? 'active' : '' ?>">
                <i class="fas fa-history"></i><span>Activity Log</span>
            </a>
            <a href="index.php?page=vehicles" class="nav-item <?= in_array($data['page'] ?? '', ['vehicles','vehicle_form']) ? 'active' : '' ?>">
                <i class="fas fa-car"></i><span>Vehicle Inventory</span>
            </a>
            <?php endif; ?>

            <div class="nav-section">Account</div>
            <a href="index.php?page=settings" class="nav-item <?= ($data['page'] ?? '') === 'settings' ? 'active' : '' ?>">
                <i class="fas fa-user-cog"></i><span>My Profile</span>
            </a>
            <?php if (Security::isAdmin()): ?>
            <div class="nav-section">Administration</div>
            <a href="index.php?page=settings&action=users" class="nav-item <?= ($data['page'] ?? '') === 'users' ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i><span>Team Management</span>
            </a>
            <a href="index.php?page=settings&action=commissions" class="nav-item <?= ($data['page'] ?? '') === 'commissions' ? 'active' : '' ?>">
                <i class="fas fa-percentage"></i><span>Commission Rates</span>
            </a>
            <a href="index.php?page=settings&action=slabs" class="nav-item <?= ($data['page'] ?? '') === 'slabs' ? 'active' : '' ?>">
                <i class="fas fa-layer-group"></i><span>Payout Slabs</span>
            </a>
            <a href="index.php?page=settings&action=api_keys" class="nav-item <?= ($data['page'] ?? '') === 'api_keys' ? 'active' : '' ?>">
                <i class="fas fa-plug"></i><span>API Integration</span>
            </a>
            <a href="index.php?page=settings&action=bank_rates" class="nav-item <?= ($data['page'] ?? '') === 'bank_rates' ? 'active' : '' ?>">
                <i class="fas fa-university"></i><span>Bank Rates</span>
            </a>
            <a href="index.php?page=settings&action=login_history" class="nav-item <?= ($data['page'] ?? '') === 'login_history' ? 'active' : '' ?>">
                <i class="fas fa-shield-alt"></i><span>Login History</span>
            </a>
            <?php endif; ?>
            <a href="index.php?page=logout" class="nav-item nav-logout">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-badge">
                <div class="user-avatar"><?= strtoupper(substr(Security::userName(), 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars(Security::userName()) ?></div>
                    <div class="user-role"><?= ucfirst($_SESSION['user_role'] ?? 'agent') ?></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Bar -->
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search leads by name or phone..." autocomplete="off">
                <div class="search-results" id="searchResults"></div>
            </div>
            <div class="topbar-actions">
                <button id="themeToggle" class="btn-icon" title="Toggle Theme" style="margin-right:8px">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="index.php?page=leads&action=create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Lead
                </a>
                <a href="index.php?page=api&action=export_csv" class="btn btn-ghost btn-sm" title="Export CSV">
                    <i class="fas fa-download"></i>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?>" id="flashAlert">
            <i class="fas fa-<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <!-- Page Content -->
        <div class="page-content">
            <?php
            $viewFile = __DIR__ . '/' . ($data['page'] ?? 'dashboard') . '.php';
            if (file_exists($viewFile)) {
                require $viewFile;
            } else {
                echo '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><h3>Page not found</h3></div>';
            }
            ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('SW Registered'))
                    .catch(err => console.log('SW Registration Failed', err));
            });
        }
    </script>
</body>
</html>
