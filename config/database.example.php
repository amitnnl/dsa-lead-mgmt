<?php
/**
 * Database Configuration (TEMPLATE)
 * 
 * Rename this file to database.php and fill in your actual credentials.
 * DO NOT commit database.php to GitHub.
 */

// ===== CONFIGURATION SWITCH =====
$is_production = true; 

if ($is_production) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'your_cpanel_db_name'); 
    define('DB_USER', 'your_cpanel_db_user');
    define('DB_PASS', 'your_cpanel_db_password');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'dsa_lead_mgmt');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed. Please check your credentials in config/database.php");
        }
    }
    return $pdo;
}
