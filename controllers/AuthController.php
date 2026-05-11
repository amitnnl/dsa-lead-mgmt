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

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter both email and password.';
            header('Location: index.php?page=login');
            return;
        }

        $user = $this->db->fetch("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

        if (!$user || !Security::verifyPassword($password, $user['password'])) {
            $_SESSION['login_error'] = 'Invalid email or password.';
            header('Location: index.php?page=login');
            return;
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        // Update last login
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        header('Location: index.php?page=dashboard');
    }
}
