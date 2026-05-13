<?php
/**
 * Migration Phase 5+6: Dealer Portal & Vehicle Inspections
 */
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain');
echo "=== Phase 5+6 Migration: Dealers & Inspections ===\n\n";

$pdo = getDB();

$tables = [
    'vehicle_inspections' => "CREATE TABLE IF NOT EXISTS `vehicle_inspections` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `vehicle_id` INT DEFAULT NULL,
        `lead_id` INT DEFAULT NULL,
        `inspector_name` VARCHAR(100) DEFAULT NULL,
        `inspection_date` DATE DEFAULT NULL,
        `exterior_score` TINYINT DEFAULT NULL COMMENT '1-10',
        `interior_score` TINYINT DEFAULT NULL COMMENT '1-10',
        `engine_score` TINYINT DEFAULT NULL COMMENT '1-10',
        `electrical_score` TINYINT DEFAULT NULL COMMENT '1-10',
        `tyre_condition` ENUM('Excellent','Good','Average','Poor') DEFAULT 'Good',
        `ac_working` TINYINT DEFAULT 1,
        `accident_history` ENUM('None','Minor','Major') DEFAULT 'None',
        `flood_affected` TINYINT DEFAULT 0,
        `odometer_tampered` TINYINT DEFAULT 0,
        `overall_rating` DECIMAL(3,1) DEFAULT NULL COMMENT 'Out of 10',
        `estimated_value` DECIMAL(12,2) DEFAULT NULL,
        `remarks` TEXT DEFAULT NULL,
        `verdict` ENUM('Approved','Conditional','Rejected') DEFAULT 'Conditional',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_vehicle` (`vehicle_id`),
        INDEX `idx_lead` (`lead_id`)
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

// Add dealer_id column to vehicles if not exists
try {
    $cols = $pdo->query("SHOW COLUMNS FROM vehicles LIKE 'dealer_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE vehicles ADD COLUMN dealer_id INT DEFAULT NULL AFTER id");
        echo "✅ Added dealer_id to vehicles\n";
    } else {
        echo "✅ vehicles.dealer_id already exists\n";
    }
} catch (Exception $e) { echo "⚠️ " . $e->getMessage() . "\n"; }

// Ensure 'dealer' role exists in users table
try {
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','agent','partner','dealer') NOT NULL DEFAULT 'agent'");
    echo "✅ Added 'dealer' role to users table\n";
} catch (Exception $e) { echo "⚠️ Role update: " . $e->getMessage() . "\n"; }

echo "\n🎉 Phase 5+6 migration complete!\n";
echo "⚠️ DELETE this file after use.\n";
