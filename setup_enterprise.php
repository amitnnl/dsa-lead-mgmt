<?php
/**
 * DSA LeadFlow - Complete Database Setup
 * Creates ALL required tables for the Enterprise platform.
 * Safe to run multiple times (uses IF NOT EXISTS).
 */
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔧 DSA LeadFlow - Database Setup</h2><pre>";

$pdo = getDB();
$errors = 0;

$tables = [
    'commission_rates' => "CREATE TABLE IF NOT EXISTS `commission_rates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `bank_name` VARCHAR(100) NOT NULL,
        `loan_type` VARCHAR(100) NOT NULL,
        `commission_percentage` DECIMAL(5,2) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `bank_loan` (`bank_name`, `loan_type`)
    ) ENGINE=InnoDB",

    'payout_slabs' => "CREATE TABLE IF NOT EXISTS `payout_slabs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `min_volume` DECIMAL(15,2) DEFAULT 0,
        `max_volume` DECIMAL(15,2) DEFAULT 0,
        `agent_share_percentage` DECIMAL(5,2) DEFAULT 0,
        `description` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    'client_payouts' => "CREATE TABLE IF NOT EXISTS `client_payouts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `bank_name` VARCHAR(100) DEFAULT NULL,
        `loan_type` VARCHAR(100) DEFAULT NULL,
        `disbursed_amount` DECIMAL(15,2) DEFAULT 0,
        `commission_rate` DECIMAL(5,2) DEFAULT 0,
        `commission_amount` DECIMAL(15,2) DEFAULT 0,
        `agent_share` DECIMAL(15,2) DEFAULT 0,
        `manager_override` DECIMAL(15,2) DEFAULT 0,
        `status` ENUM('Pending','Paid','Cancelled') DEFAULT 'Pending',
        `paid_at` TIMESTAMP NULL DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    'lead_documents' => "CREATE TABLE IF NOT EXISTS `lead_documents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `document_type` VARCHAR(100) NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `file_path` VARCHAR(500) NOT NULL,
        `uploaded_by` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    'login_logs' => "CREATE TABLE IF NOT EXISTS `login_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `ip_address` VARCHAR(45) NOT NULL,
        `user_agent` TEXT DEFAULT NULL,
        `status` ENUM('Success', 'Failed') DEFAULT 'Success',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_user_id` (`user_id`)
    ) ENGINE=InnoDB"
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Table '{$name}' — OK\n";
    } catch (PDOException $e) {
        echo "❌ Table '{$name}' — ERROR: " . $e->getMessage() . "\n";
        $errors++;
    }
}

// Check if parent_id column exists on users table
try {
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'parent_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN `parent_id` INT DEFAULT NULL AFTER `role`");
        echo "✅ Column 'users.parent_id' — Added\n";
    } else {
        echo "✅ Column 'users.parent_id' — Already exists\n";
    }
} catch (PDOException $e) {
    echo "⚠️ Column 'users.parent_id' — " . $e->getMessage() . "\n";
}

echo "\n" . ($errors === 0 ? "🎉 ALL DONE! Database is fully set up." : "⚠️ Completed with {$errors} error(s).") . "\n";
echo "\n⚠️ DELETE THIS FILE (setup_enterprise.php) AFTER USE!\n";
echo "</pre>";
