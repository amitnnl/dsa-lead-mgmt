<?php
/**
 * Application Configuration
 */

define('APP_NAME', 'DSA LeadFlow');
define('APP_VERSION', '1.0.0');

// Auto-detect URL (works on both localhost and cPanel)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('APP_URL', $protocol . '://' . $host . $scriptDir);

// Upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB

// Lead statuses
define('LEAD_STATUSES', [
    'New'           => ['color' => '#6366f1', 'icon' => 'fas fa-star'],
    'Contacted'     => ['color' => '#f59e0b', 'icon' => 'fas fa-phone'],
    'Documentation' => ['color' => '#3b82f6', 'icon' => 'fas fa-file-alt'],
    'Submitted'     => ['color' => '#8b5cf6', 'icon' => 'fas fa-paper-plane'],
    'Approved'      => ['color' => '#10b981', 'icon' => 'fas fa-check-circle'],
    'Disbursed'     => ['color' => '#06d6a0', 'icon' => 'fas fa-money-bill-wave'],
    'Rejected'      => ['color' => '#ef4444', 'icon' => 'fas fa-times-circle'],
]);

// Lead scores
define('LEAD_SCORES', [
    'Hot'   => ['color' => '#ef4444', 'min' => 70],
    'Warm'  => ['color' => '#f59e0b', 'min' => 40],
    'Cold'  => ['color' => '#6b7280', 'min' => 0],
]);

// Loan types
define('LOAN_TYPES', [
    'Personal Loan',
    'Home Loan',
    'Business Loan',
    'Gold Loan',
    'Vehicle Loan',
    'Education Loan',
    'Loan Against Property',
    'Credit Card',
    'Insurance',
    'Other',
]);

// Lead sources
define('LEAD_SOURCES', [
    'Walk-in',
    'Referral',
    'Phone Inquiry',
    'Website',
    'Social Media',
    'Excel Import',
    'Partner',
    'Campaign',
    'Other',
]);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
