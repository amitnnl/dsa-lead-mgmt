<?php
/**
 * Migration: Insurance Policies & RC Transfer tables
 */
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain');
echo "=== Phase 3 Migration: Insurance & RC Transfer ===\n\n";

$pdo = getDB();

$tables = [
    'insurance_policies' => "CREATE TABLE IF NOT EXISTS `insurance_policies` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `provider` VARCHAR(100) DEFAULT NULL,
        `policy_type` ENUM('Comprehensive','Third-Party','Zero-Dep','Standalone OD') DEFAULT 'Comprehensive',
        `premium` DECIMAL(10,2) DEFAULT 0,
        `commission_earned` DECIMAL(10,2) DEFAULT 0,
        `policy_number` VARCHAR(50) DEFAULT NULL,
        `start_date` DATE DEFAULT NULL,
        `end_date` DATE DEFAULT NULL,
        `status` ENUM('Quoted','Active','Expired','Cancelled') DEFAULT 'Quoted',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_lead_id` (`lead_id`)
    ) ENGINE=InnoDB",

    'rc_transfers' => "CREATE TABLE IF NOT EXISTS `rc_transfers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `seller_name` VARCHAR(100) DEFAULT NULL,
        `buyer_name` VARCHAR(100) DEFAULT NULL,
        `form29_status` ENUM('Pending','Submitted','Completed') DEFAULT 'Pending',
        `form30_status` ENUM('Pending','Submitted','Completed') DEFAULT 'Pending',
        `noc_status` ENUM('Not Required','Pending','Received') DEFAULT 'Not Required',
        `hypothecation_status` ENUM('None','Pending','Endorsed') DEFAULT 'None',
        `transfer_fee` DECIMAL(10,2) DEFAULT 0,
        `rto_name` VARCHAR(100) DEFAULT NULL,
        `completed_at` TIMESTAMP NULL DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_lead_id` (`lead_id`)
    ) ENGINE=InnoDB",

    'bank_rates' => "CREATE TABLE IF NOT EXISTS `bank_rates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `bank_name` VARCHAR(100) NOT NULL,
        `loan_type` VARCHAR(50) NOT NULL,
        `interest_rate` DECIMAL(5,2) NOT NULL,
        `max_tenure_years` INT DEFAULT 5,
        `max_ltv` INT DEFAULT 80,
        `processing_fee` VARCHAR(50) DEFAULT '1%',
        `min_amount` DECIMAL(12,2) DEFAULT 100000,
        `max_amount` DECIMAL(12,2) DEFAULT 5000000,
        `is_active` TINYINT DEFAULT 1,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `bank_type` (`bank_name`, `loan_type`)
    ) ENGINE=InnoDB"
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Table '{$name}' — OK\n";
    } catch (PDOException $e) {
        echo "❌ Table '{$name}' — " . $e->getMessage() . "\n";
    }
}

// Seed default bank rates for used vehicles
$defaultRates = [
    ['HDFC Bank', 'Used Car Loan', 10.50, 7, 85, '0.5%'],
    ['ICICI Bank', 'Used Car Loan', 11.00, 5, 80, '1%'],
    ['SBI', 'Used Car Loan', 9.80, 7, 85, '0.5%'],
    ['Axis Bank', 'Used Car Loan', 11.25, 5, 80, '1%'],
    ['IndusInd Bank', 'Used Car Loan', 12.00, 5, 90, '1.5%'],
    ['Bajaj Finance', 'Used Car Loan', 13.00, 5, 90, '2%'],
    ['Kotak Mahindra', 'Used Car Loan', 11.50, 5, 80, '1%'],
    ['Tata Capital', 'Used Car Loan', 12.50, 5, 85, '1.5%'],
    ['HDFC Bank', 'Used Bike Loan', 13.50, 3, 80, '1%'],
    ['Bajaj Finance', 'Used Bike Loan', 14.00, 3, 85, '1.5%'],
    ['IDFC First', 'Used Bike Loan', 14.50, 3, 80, '1%'],
    ['SBI', 'Used Commercial Vehicle Loan', 10.00, 7, 75, '0.5%'],
    ['HDFC Bank', 'Used Commercial Vehicle Loan', 11.00, 7, 80, '1%'],
    ['Shriram Finance', 'Used Commercial Vehicle Loan', 14.00, 5, 90, '2%'],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO bank_rates (bank_name, loan_type, interest_rate, max_tenure_years, max_ltv, processing_fee) VALUES (?,?,?,?,?,?)");
$seeded = 0;
foreach ($defaultRates as $r) {
    $stmt->execute($r);
    if ($stmt->rowCount() > 0) $seeded++;
}
echo "\n✅ Seeded {$seeded} bank rates\n";

echo "\n🎉 Phase 3 migration complete!\n";
echo "⚠️ DELETE this file after use.\n";
