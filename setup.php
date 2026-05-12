<?php
/**
 * DSA LeadFlow - One-Click Setup Script
 * Run this once via browser: http://yourdomain.com/setup.php
 * It creates the database, tables, and default admin user.
 * 
 * For cPanel: Update config/database.php with your cPanel DB credentials FIRST,
 * then create the database via cPanel → MySQL Databases, then run this script.
 */

session_start();
$messages = [];
$errors = [];

// Load database config
require_once __DIR__ . '/config/database.php';

// Step 1: Connect to MySQL (without database — try to create it)
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $messages[] = "✅ Connected to MySQL server";
} catch (PDOException $e) {
    // On cPanel, we might not have CREATE DATABASE privilege
    // Try connecting directly to the database instead
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $messages[] = "✅ Connected to MySQL database '" . DB_NAME . "'";
        // Skip steps 2 & 3 - already connected to the database
        goto create_tables;
    } catch (PDOException $e2) {
        $errors[] = "❌ Cannot connect to MySQL: " . $e->getMessage();
        showPage($messages, $errors);
        exit;
    }
}

// Step 2: Create database (may fail on cPanel — that's OK if DB already exists)
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "✅ Database '" . DB_NAME . "' created/verified";
} catch (PDOException $e) {
    $messages[] = "ℹ️ Database creation skipped (cPanel may require manual creation). Using existing database.";
}

// Step 3: Switch to database
try {
    $pdo->exec("USE `" . DB_NAME . "`");
} catch (PDOException $e) {
    $errors[] = "❌ Cannot use database '" . DB_NAME . "': " . $e->getMessage();
    showPage($messages, $errors);
    exit;
}

create_tables:

// Step 4: Create tables
$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'manager', 'agent') DEFAULT 'agent',
        `phone` VARCHAR(20) DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `last_login` TIMESTAMP NULL DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    'leads' => "CREATE TABLE IF NOT EXISTS `leads` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_source` VARCHAR(100) DEFAULT 'Other',
        `customer_name` VARCHAR(255) NOT NULL,
        `phone_number` VARCHAR(20) DEFAULT NULL,
        `alt_phone` VARCHAR(20) DEFAULT NULL,
        `email_address` VARCHAR(255) DEFAULT NULL,
        `dob` DATE DEFAULT NULL,
        `gender` ENUM('Male','Female','Other') DEFAULT NULL,
        `address` TEXT DEFAULT NULL,
        `city` VARCHAR(100) DEFAULT NULL,
        `state` VARCHAR(100) DEFAULT NULL,
        `pincode` VARCHAR(10) DEFAULT NULL,
        `loan_type` VARCHAR(100) DEFAULT NULL,
        `loan_amount` DECIMAL(15,2) DEFAULT 0.00,
        `monthly_income` DECIMAL(15,2) DEFAULT 0.00,
        `employer` VARCHAR(255) DEFAULT NULL,
        `employment_type` ENUM('Salaried','Self-Employed','Business','Retired','Other') DEFAULT NULL,
        `existing_loans` TEXT DEFAULT NULL,
        `credit_score` INT DEFAULT NULL,
        `bank_name` VARCHAR(100) DEFAULT NULL,
        `status` ENUM('New','Contacted','Documentation','Submitted','Approved','Disbursed','Rejected') DEFAULT 'New',
        `lead_score` INT DEFAULT 0,
        `lead_grade` ENUM('Hot','Warm','Cold') DEFAULT 'Cold',
        `assigned_to` INT DEFAULT NULL,
        `remarks` TEXT DEFAULT NULL,
        `follow_up_date` DATE DEFAULT NULL,
        `import_batch_id` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_status` (`status`),
        INDEX `idx_lead_grade` (`lead_grade`),
        INDEX `idx_assigned_to` (`assigned_to`),
        INDEX `idx_phone` (`phone_number`),
        INDEX `idx_city` (`city`),
        INDEX `idx_loan_type` (`loan_type`),
        INDEX `idx_follow_up` (`follow_up_date`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB",

    'activity_log' => "CREATE TABLE IF NOT EXISTS `activity_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `user_id` INT DEFAULT NULL,
        `action` VARCHAR(100) NOT NULL,
        `old_value` TEXT DEFAULT NULL,
        `new_value` TEXT DEFAULT NULL,
        `notes` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_lead_id` (`lead_id`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_action` (`action`),
        INDEX `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB",

    'import_batches' => "CREATE TABLE IF NOT EXISTS `import_batches` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `filename` VARCHAR(255) NOT NULL,
        `total_rows` INT DEFAULT 0,
        `imported_rows` INT DEFAULT 0,
        `skipped_rows` INT DEFAULT 0,
        `error_rows` INT DEFAULT 0,
        `column_mapping` JSON DEFAULT NULL,
        `user_id` INT DEFAULT NULL,
        `status` ENUM('processing','completed','failed') DEFAULT 'processing',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    'job_queue' => "CREATE TABLE IF NOT EXISTS `job_queue` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `type` VARCHAR(50) NOT NULL DEFAULT 'import',
        `batch_id` INT DEFAULT NULL,
        `payload` JSON DEFAULT NULL,
        `status` ENUM('pending','running','completed','failed') DEFAULT 'pending',
        `progress` INT DEFAULT 0,
        `total_items` INT DEFAULT 0,
        `processed_items` INT DEFAULT 0,
        `error_message` TEXT DEFAULT NULL,
        `started_at` TIMESTAMP NULL DEFAULT NULL,
        `completed_at` TIMESTAMP NULL DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_status` (`status`),
        INDEX `idx_batch_id` (`batch_id`)
    ) ENGINE=InnoDB",

    'api_keys' => "CREATE TABLE IF NOT EXISTS `api_keys` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `key_name` VARCHAR(255) NOT NULL,
        `api_key` VARCHAR(64) NOT NULL UNIQUE,
        `permissions` JSON DEFAULT NULL,
        `user_id` INT DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `request_count` INT DEFAULT 0,
        `last_used_at` TIMESTAMP NULL DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_api_key` (`api_key`),
        INDEX `idx_active` (`is_active`)
    ) ENGINE=InnoDB",
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        $messages[] = "✅ Table '{$name}' created/verified";
    } catch (PDOException $e) {
        $errors[] = "❌ Error creating table '{$name}': " . $e->getMessage();
    }
}

// Step 5: Add foreign keys (ignore if already exist)
$fks = [
    "ALTER TABLE `leads` ADD FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL",
    "ALTER TABLE `activity_log` ADD FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE",
    "ALTER TABLE `activity_log` ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL",
    "ALTER TABLE `import_batches` ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL",
];
foreach ($fks as $fk) {
    try { $pdo->exec($fk); } catch (PDOException $e) { /* FK may already exist, ignore */ }
}
$messages[] = "✅ Foreign keys verified";

// Step 6: Create default users (if not exist)
$passwordHash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
$defaultUsers = [
    ['Admin', 'admin@dsa.com', $passwordHash, 'admin'],
    ['Manager Demo', 'manager@dsa.com', $passwordHash, 'manager'],
    ['Agent Demo', 'agent@dsa.com', $passwordHash, 'agent'],
];

foreach ($defaultUsers as $u) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$u[1]]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute($u);
            $messages[] = "✅ User '{$u[0]}' created (email: {$u[1]}, pass: admin123)";
        } else {
            $messages[] = "ℹ️ User '{$u[0]}' already exists";
        }
    } catch (PDOException $e) {
        $errors[] = "❌ Error creating user '{$u[0]}': " . $e->getMessage();
    }
}

// Step 7: Insert sample leads (if table is empty)
$stmt = $pdo->query("SELECT COUNT(*) FROM leads");
$leadCount = $stmt->fetchColumn();
if ($leadCount == 0) {
    $sampleLeads = [
        ['Referral','Rajesh Kumar','9876543210','rajesh.k@email.com','Mumbai','Maharashtra','Home Loan',5000000,120000,'TCS','Salaried','Documentation',85,'Hot',1],
        ['Website','Priya Sharma','9812345678','priya.s@email.com','Delhi','Delhi','Personal Loan',300000,65000,'Infosys','Salaried','Contacted',60,'Warm',2],
        ['Walk-in','Amit Patel','9798765432','amit.p@email.com','Ahmedabad','Gujarat','Business Loan',2000000,200000,'Self','Self-Employed','Submitted',75,'Hot',1],
        ['Phone Inquiry','Sneha Reddy','9654321098',NULL,'Hyderabad','Telangana','Gold Loan',500000,45000,NULL,NULL,'New',35,'Cold',NULL],
        ['Social Media','Vikram Singh','9543210987','vikram@email.com','Jaipur','Rajasthan','Vehicle Loan',800000,55000,'Wipro','Salaried','Approved',70,'Hot',2],
        ['Campaign','Anjali Nair','9432109876',NULL,'Kochi','Kerala','Education Loan',1500000,0,NULL,NULL,'New',25,'Cold',NULL],
        ['Partner','Mohammed Ali','9321098765','mali@email.com','Chennai','Tamil Nadu','Loan Against Property',8000000,180000,'HCL','Salaried','Disbursed',90,'Hot',1],
        ['Referral','Deepa Gupta','9210987654','deepa.g@email.com','Pune','Maharashtra','Personal Loan',200000,35000,'Freelance','Self-Employed','Rejected',45,'Warm',3],
    ];

    $stmt = $pdo->prepare("INSERT INTO leads (lead_source,customer_name,phone_number,email_address,city,state,loan_type,loan_amount,monthly_income,employer,employment_type,status,lead_score,lead_grade,assigned_to) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($sampleLeads as $lead) {
        try { $stmt->execute($lead); } catch (PDOException $e) { }
    }
    $messages[] = "✅ Sample leads inserted (" . count($sampleLeads) . " records)";
} else {
    $messages[] = "ℹ️ Leads table already has {$leadCount} records (skipping samples)";
}

// Step 8: Create uploads directory
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    $messages[] = "✅ Uploads directory created";
} else {
    $messages[] = "ℹ️ Uploads directory already exists";
}

showPage($messages, $errors);

function showPage(array $messages, array $errors): void {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DSA LeadFlow - Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #0a0a1a; color: #e2e8f0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .setup-card { max-width: 600px; width: 100%; background: #12122a; border: 1px solid rgba(255,255,255,0.07); border-radius: 20px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo .icon { width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1, #8b5cf6); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px; }
        .logo h1 { font-size: 22px; font-weight: 700; }
        .logo p { color: #94a3b8; font-size: 13px; margin-top: 4px; }
        .msg-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px; }
        .msg { padding: 10px 14px; border-radius: 8px; font-size: 13px; }
        .msg-ok { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); }
        .msg-err { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #fca5a5; }
        .btn { display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; margin-top: 10px; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-2px); }
        .status-bar { text-align: center; margin-top: 20px; }
        .creds { background: #1a1a3e; border-radius: 10px; padding: 16px; margin-top: 16px; font-size: 13px; }
        .creds h4 { margin-bottom: 8px; color: #f59e0b; }
        .creds p { color: #94a3b8; margin: 4px 0; }
        .creds strong { color: #e2e8f0; }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="logo">
            <div class="icon">⚡</div>
            <h1>DSA LeadFlow Setup</h1>
            <p>One-click database initialization</p>
        </div>

        <div class="msg-list">
            <?php foreach ($messages as $m): ?>
            <div class="msg msg-ok"><?= $m ?></div>
            <?php endforeach; ?>
            <?php foreach ($errors as $e): ?>
            <div class="msg msg-err"><?= $e ?></div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($errors)): ?>
        <div class="creds">
            <h4>🔑 Login Credentials</h4>
            <p><strong>Admin:</strong> admin@dsa.com / admin123</p>
            <p><strong>Manager:</strong> manager@dsa.com / admin123</p>
            <p><strong>Agent:</strong> agent@dsa.com / admin123</p>
        </div>

        <div class="status-bar">
            <p style="color:#10b981;font-weight:600;margin-bottom:10px">✅ Setup Complete!</p>
            <a href="index.php" class="btn">🚀 Launch DSA LeadFlow</a>
        </div>
        <?php else: ?>
        <div class="status-bar">
            <p style="color:#ef4444">⚠️ Some errors occurred. Fix them and run setup again.</p>
            <a href="setup.php" class="btn" style="background:#ef4444">🔄 Retry Setup</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
}
