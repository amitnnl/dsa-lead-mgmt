<?php
/**
 * Migration: Vehicle Inventory tables
 */
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain');
echo "=== Phase 4 Migration: Vehicle Inventory ===\n\n";

$pdo = getDB();

$tables = [
    'vehicles' => "CREATE TABLE IF NOT EXISTS `vehicles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `dealer_id` INT DEFAULT NULL,
        `added_by` INT DEFAULT NULL,
        `make` VARCHAR(50) NOT NULL,
        `model` VARCHAR(100) NOT NULL,
        `variant` VARCHAR(100) DEFAULT NULL,
        `year` INT NOT NULL,
        `registration_no` VARCHAR(20) DEFAULT NULL,
        `color` VARCHAR(30) DEFAULT NULL,
        `fuel_type` ENUM('Petrol','Diesel','CNG','Electric','Hybrid') DEFAULT 'Petrol',
        `transmission` ENUM('Manual','Automatic') DEFAULT 'Manual',
        `km_driven` INT DEFAULT 0,
        `owner_count` TINYINT DEFAULT 1,
        `asking_price` DECIMAL(12,2) DEFAULT 0,
        `market_value` DECIMAL(12,2) DEFAULT 0,
        `body_type` VARCHAR(50) DEFAULT NULL,
        `insurance_valid` DATE DEFAULT NULL,
        `hypothecated` TINYINT DEFAULT 0,
        `description` TEXT DEFAULT NULL,
        `photo_url` VARCHAR(500) DEFAULT NULL,
        `status` ENUM('Available','Reserved','Sold','Delisted') DEFAULT 'Available',
        `views_count` INT DEFAULT 0,
        `inquiries_count` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_status` (`status`),
        INDEX `idx_make` (`make`),
        INDEX `idx_price` (`asking_price`)
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

// Seed some sample vehicles
$samples = [
    ['Maruti Suzuki', 'Swift', 'VXI', 2021, 'DL 01 AB 1234', 'Silver', 'Petrol', 'Manual', 32000, 1, 550000, 520000, 'Hatchback'],
    ['Hyundai', 'i20', 'Asta', 2020, 'HR 26 CD 5678', 'Red', 'Petrol', 'Manual', 45000, 1, 680000, 650000, 'Hatchback'],
    ['Honda', 'City', 'ZX CVT', 2019, 'MH 02 EF 9012', 'White', 'Petrol', 'Automatic', 55000, 2, 850000, 820000, 'Sedan'],
    ['Tata', 'Nexon', 'XZA+', 2022, 'UP 14 GH 3456', 'Blue', 'Diesel', 'Automatic', 18000, 1, 1050000, 1020000, 'SUV'],
    ['Mahindra', 'XUV700', 'AX5', 2023, 'RJ 14 IJ 7890', 'Black', 'Diesel', 'Manual', 12000, 1, 1650000, 1600000, 'SUV'],
    ['Royal Enfield', 'Classic 350', 'Halcyon', 2022, 'DL 05 KL 1122', 'Green', 'Petrol', 'Manual', 8000, 1, 165000, 155000, 'Bike'],
];

$stmt = $pdo->prepare("INSERT INTO vehicles (make, model, variant, year, registration_no, color, fuel_type, transmission, km_driven, owner_count, asking_price, market_value, body_type, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, 'Available')");

$seeded = 0;
$existing = $pdo->query("SELECT COUNT(*) as cnt FROM vehicles")->fetch()['cnt'];
if ($existing == 0) {
    foreach ($samples as $v) {
        $stmt->execute($v);
        $seeded++;
    }
    echo "\n✅ Seeded {$seeded} sample vehicles\n";
} else {
    echo "\n✅ Vehicles table already has data, skipping seed.\n";
}

echo "\n🎉 Phase 4 migration complete!\n";
echo "⚠️ DELETE this file after use.\n";
