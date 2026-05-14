<?php
/**
 * Migration: Add account_number and ifsc_code to leads table
 * Run this once: http://yourdomain.com/database/migration_bank_details.php
 */
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $migrations = [
        "ALTER TABLE leads ADD COLUMN IF NOT EXISTS account_number VARCHAR(30) DEFAULT NULL AFTER bank_name",
        "ALTER TABLE leads ADD COLUMN IF NOT EXISTS ifsc_code VARCHAR(15) DEFAULT NULL AFTER account_number",
        "ALTER TABLE leads ADD COLUMN IF NOT EXISTS father_name VARCHAR(150) DEFAULT NULL AFTER gender",
    ];

    echo "<h2>🔧 Lead Fields Migration</h2>";
    foreach ($migrations as $sql) {
        try {
            $pdo->exec($sql);
            echo "<p>✅ " . htmlspecialchars($sql) . "</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "<p>⚠️ Column already exists — skipping: " . htmlspecialchars($sql) . "</p>";
            } else {
                echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }

    echo "<br><p><strong>✅ Migration Complete!</strong> You can delete this file now.</p>";
    echo "<p><a href='../index.php?page=leads'>← Back to Leads</a></p>";

} catch (PDOException $e) {
    echo "<p>❌ Connection Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
