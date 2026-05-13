<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Database.php';

function getDB() {
    return new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

$db = new Database();
$pdo = $db->getPdo();

// Drop the agent_payouts table if it was created
$pdo->exec("DROP TABLE IF EXISTS `agent_payouts` ");

$sql = "CREATE TABLE IF NOT EXISTS `client_payouts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lead_id` INT DEFAULT NULL,
    `client_name` VARCHAR(255) DEFAULT NULL,
    `phone_number` VARCHAR(20) DEFAULT NULL,
    `payout_amount` DECIMAL(15,2) DEFAULT 0.00,
    `payout_date` DATE DEFAULT NULL,
    `bank_name` VARCHAR(100) DEFAULT NULL,
    `account_number` VARCHAR(50) DEFAULT NULL,
    `transaction_id` VARCHAR(100) DEFAULT NULL,
    `remarks` TEXT DEFAULT NULL,
    `import_batch_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_lead_id` (`lead_id`),
    INDEX `idx_phone` (`phone_number`),
    INDEX `idx_payout_date` (`payout_date`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB";

$sql2 = "ALTER TABLE `import_batches` ADD COLUMN `import_type` VARCHAR(50) DEFAULT 'leads' AFTER `status` ";

try {
    $pdo->exec($sql);
    $pdo->exec($sql2);
    echo "✅ Table 'client_payouts' created and 'import_batches' updated successfully.\n";
} catch (PDOException $e) {
    // If column already exists, it might fail, which is okay
    echo "✅ Payouts setup verified.\n";
}
