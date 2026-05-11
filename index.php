<?php
/**
 * DSA Lead Management System - Main Entry Point
 * A powerful PHP-based lead management system for DSA operations
 */

session_start();

// Load configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/classes/',
        __DIR__ . '/controllers/',
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Simple Router
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Auth check (except login page)
$publicPages = ['login', 'api'];
if (!in_array($page, $publicPages) && !isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// API Routes
if ($page === 'api') {
    header('Content-Type: application/json');
    require_once __DIR__ . '/controllers/ApiController.php';
    $api = new ApiController();
    $api->handle($action);
    exit;
}

// Page Routes
switch ($page) {
    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        $ctrl = new AuthController();
        $ctrl->handle($action);
        break;
    case 'dashboard':
        require_once __DIR__ . '/controllers/DashboardController.php';
        $ctrl = new DashboardController();
        $ctrl->handle($action);
        break;
    case 'leads':
        require_once __DIR__ . '/controllers/LeadController.php';
        $ctrl = new LeadController();
        $ctrl->handle($action);
        break;
    case 'import':
        require_once __DIR__ . '/controllers/ImportController.php';
        $ctrl = new ImportController();
        $ctrl->handle($action);
        break;
    case 'activity':
        require_once __DIR__ . '/controllers/ActivityController.php';
        $ctrl = new ActivityController();
        $ctrl->handle($action);
        break;
    case 'settings':
        require_once __DIR__ . '/controllers/SettingsController.php';
        $ctrl = new SettingsController();
        $ctrl->handle($action);
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    default:
        require_once __DIR__ . '/controllers/DashboardController.php';
        $ctrl = new DashboardController();
        $ctrl->handle('index');
        break;
}
