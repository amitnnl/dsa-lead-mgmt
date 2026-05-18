<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';

if (!function_exists('getDB')) {
    function getDB() {
        return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}

$db = new Database();
$pdo = $db->getPdo();

// 1. Commission Rates (Per Bank + Loan Type)
$sql1 = "CREATE TABLE IF NOT EXISTS `commission_rates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bank_name` VARCHAR(100) NOT NULL,
    `loan_type` VARCHAR(100) NOT NULL,
    `commission_percentage` DECIMAL(5,2) NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_bank_loan` (`bank_name`, `loan_type`)
) ENGINE=InnoDB";

// 2. Agent Payout Slabs (Based on disbursement volume)
$sql2 = "CREATE TABLE IF NOT EXISTS `payout_slabs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `min_volume` DECIMAL(15,2) NOT NULL,
    `max_volume` DECIMAL(15,2) NOT NULL,
    `agent_share_percentage` DECIMAL(5,2) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB";

// 3. User Hierarchy (Add parent_id to users)
$sql3 = "ALTER TABLE `users` ADD COLUMN `parent_id` INT DEFAULT NULL AFTER `role` ";
$sql4 = "ALTER TABLE `users` ADD FOREIGN KEY (`parent_id`) REFERENCES `users`(`id`) ON DELETE SET NULL";

try {
    $pdo->exec($sql1);
    $pdo->exec($sql2);
    try { $pdo->exec($sql3); } catch (Exception $e) {} // May already exist
    try { $pdo->exec($sql4); } catch (Exception $e) {} // May already exist
    
    echo "✅ Enterprise Foundation (Commissions & Hierarchy) initialized successfully.\n";
} catch (PDOException $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
}
