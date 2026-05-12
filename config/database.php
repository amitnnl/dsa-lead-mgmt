<?php
/**
 * Database Configuration
 * 
 * For cPanel shared hosting:
 * 1. Create a MySQL database via cPanel → MySQL Databases
 * 2. Create a database user and assign it to the database (ALL PRIVILEGES)
 * 3. Update the credentials below
 * 
 * cPanel database names are usually prefixed: cpanel_username_dbname
 * cPanel db users are usually prefixed: cpanel_username_dbuser
 */

// ===== EDIT THESE FOR YOUR SERVER =====
define('DB_HOST', 'localhost');
define('DB_NAME', 'dsa_lead_mgmt');       // cPanel: youruser_dsa_lead_mgmt
define('DB_USER', 'root');                 // cPanel: youruser_dbuser
define('DB_PASS', '');                     // cPanel: your_db_password
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
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
