<?php
/**
 * Migration: Add vehicle fields to leads table
 */
require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/plain');
echo "=== Vehicle Finance Migration ===\n\n";

$pdo = getDB();

$columns = [
    'vehicle_make'         => "VARCHAR(100) DEFAULT NULL",
    'vehicle_model'        => "VARCHAR(150) DEFAULT NULL",
    'vehicle_year'         => "INT DEFAULT NULL",
    'vehicle_reg_no'       => "VARCHAR(20) DEFAULT NULL",
    'vehicle_km'           => "INT DEFAULT NULL",
    'vehicle_fuel'         => "VARCHAR(20) DEFAULT NULL",
    'vehicle_owner'        => "TINYINT DEFAULT NULL",
    'vehicle_price'        => "DECIMAL(12,2) DEFAULT 0",
    'vehicle_hypothecated' => "VARCHAR(5) DEFAULT 'No'",
];

foreach ($columns as $col => $definition) {
    try {
        $exists = $pdo->query("SHOW COLUMNS FROM leads LIKE '{$col}'")->fetchAll();
        if (empty($exists)) {
            $pdo->exec("ALTER TABLE leads ADD COLUMN `{$col}` {$definition}");
            echo "✅ Added column: leads.{$col}\n";
        } else {
            echo "✅ Column leads.{$col} already exists\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error on {$col}: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Vehicle Finance migration complete!\n";
echo "⚠️ DELETE this file after use.\n";
