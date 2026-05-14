<?php
/**
 * Authentication Controller
 */
class AuthController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'login':
                $this->login();
                break;
            default:
                $this->showLogin();
                break;
        }
    }

    private function showLogin(): void {
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        require __DIR__ . '/../views/login.php';
    }

    private function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=login');
            return;
        }

        if (!Security::validateCsrf()) {
            $_SESSION['login_error'] = 'Invalid security token. Please try again.';
            header('Location: index.php?page=login');
            return;
        }

        $email = Security::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter both email and password.';
            header('Location: index.php?page=login');
            return;
        }

        // Brute-force protection: check if IP is locked out
        $lockout = Security::checkLockout($ip);
        if ($lockout['locked']) {
            $mins = ceil($lockout['remaining_seconds'] / 60);
            $_SESSION['login_error'] = "Too many failed attempts. Account locked for {$mins} minute(s). Please try again later.";
            
            // Log the blocked attempt
            try {
                $this->db->insert('login_logs', [
                    'user_id' => 0,
                    'ip_address' => $ip,
                    'user_agent' => $ua,
                    'status' => 'Blocked (Lockout)'
                ]);
            } catch (Exception $e) { /* ignore */ }
            
            header('Location: index.php?page=login');
            return;
        }

        $user = $this->db->fetch("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            // Record the failed attempt
            $attempts = Security::recordFailedAttempt($ip);
            $remaining = 5 - $attempts;
            
            if ($remaining <= 0) {
                $_SESSION['login_error'] = 'Account locked due to too many failed attempts. Please wait 15 minutes.';
            } elseif ($remaining <= 2) {
                $_SESSION['login_error'] = "Invalid email or password. {$remaining} attempt(s) remaining before lockout.";
            } else {
                $_SESSION['login_error'] = 'Invalid email or password.';
            }
            
            // Log the failed attempt
            try {
                $this->db->insert('login_logs', [
                    'user_id' => $user['id'] ?? 0,
                    'ip_address' => $ip,
                    'user_agent' => $ua,
                    'status' => 'Failed'
                ]);
            } catch (Exception $e) { /* ignore */ }
            
            header('Location: index.php?page=login');
            return;
        }

        // Successful login — clear lockout and regenerate session
        Security::clearLockout($ip);
        Security::regenerateSession();

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Regenerate CSRF token for the new session
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Update last login & Record Security Log
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);
        
        try {
            $this->db->insert('login_logs', [
                'user_id' => $user['id'],
                'ip_address' => $ip,
                'user_agent' => $ua,
                'status' => 'Success'
            ]);
        } catch (Exception $e) {
            // Silently fail if login_logs table is not yet created
            error_log("Login logging failed: " . $e->getMessage());
        }

        // Role-based redirect after login
        $redirectMap = [
            'partner' => 'index.php?page=partner',
            'dealer'  => 'index.php?page=dealer',
        ];
        $redirect = $redirectMap[$user['role']] ?? 'index.php?page=dashboard';
        header("Location: $redirect");
    }
}
