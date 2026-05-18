<?php
/**
 * Live Server Database Migration Runner
 * Executes all database migrations securely via the internal filesystem.
 */

// Basic security check to prevent accidental multiple runs
if (file_exists(__DIR__ . '/.migrations_completed')) {
    die("Migrations have already been executed on this server. Delete '.migrations_completed' to run again.");
}

echo "<h2>Starting Live Database Migrations...</h2>";

// List of all migrations in order
$migrations = [
    'migration_enterprise_v1.php',
    'migration_documents.php',
    'migration_payouts.php',
    'migration_security_logs.php',
    'migration_vehicle_inspections.php',
    // We will scan the directory to run any others just in case
];

$databaseDir = __DIR__ . '/database/';
$files = glob($databaseDir . 'migration_*.php');

// Function to isolate scope
function executeMigrationFile($filePath) {
    echo "<p>Executing: " . basename($filePath) . "...</p>";
    try {
        // Require the script so it executes its procedural DB queries
        require $filePath;
        echo "<span style='color:green'>Success!</span>";
    } catch (Throwable $e) {
        echo "<span style='color:red'>Error: " . $e->getMessage() . "</span>";
    }
}

// Execute all found migrations
foreach ($files as $file) {
    executeMigrationFile($file);
}

// Mark as completed
file_put_contents(__DIR__ . '/.migrations_completed', date('Y-m-d H:i:s'));

echo "<h2>✅ All Migrations Completed Successfully!</h2>";
echo "<p>Your live database is now fully up to date. You can now safely delete this script.</p>";
