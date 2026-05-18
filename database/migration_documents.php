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

$sql = "CREATE TABLE IF NOT EXISTS `lead_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lead_id` INT NOT NULL,
    `document_type` VARCHAR(100) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` INT DEFAULT 0,
    `file_type` VARCHAR(50) DEFAULT NULL,
    `uploaded_by` INT DEFAULT NULL,
    `status` ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_lead_id` (`lead_id`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB";

try {
    $pdo->exec($sql);
    echo "✅ Digital Vault (Document Management) table initialized successfully.\n";
} catch (PDOException $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
}
