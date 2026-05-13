<?php
/**
 * Settings Controller - User management, profile & API key management
 */
class SettingsController {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handle(string $action): void {
        switch ($action) {
            case 'users': $this->users(); break;
            case 'add_user': $this->addUser(); break;
            case 'toggle_user': $this->toggleUser(); break;
            case 'profile': $this->profile(); break;
            case 'update_profile': $this->updateProfile(); break;
            case 'change_password': $this->changePassword(); break;
            case 'api_keys': $this->apiKeys(); break;
            case 'generate_api_key': $this->generateApiKey(); break;
            case 'revoke_api_key': $this->revokeApiKey(); break;
            case 'commissions': $this->commissions(); break;
            case 'save_commission': $this->saveCommission(); break;
            case 'slabs': $this->slabs(); break;
            case 'save_slab': $this->saveSlab(); break;
            case 'login_history': $this->loginHistory(); break;
            default: $this->profile(); break;
        }
    }

    private function profile(): void {
        $data = ['page' => 'settings'];
        $data['user'] = $this->db->fetch("SELECT * FROM users WHERE id = ?", [Security::userId()]);
        require __DIR__ . '/../views/layout.php';
    }

    private function updateProfile(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=settings'); return;
        }
        $name = Security::sanitize($_POST['name'] ?? '');
        $phone = Security::sanitizePhone($_POST['phone'] ?? '');
        $this->db->update('users', ['name' => $name, 'phone' => $phone], 'id = ?', [Security::userId()]);
        $_SESSION['user_name'] = $name;
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Profile updated.'];
        header('Location: index.php?page=settings');
    }

    private function changePassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf()) {
            header('Location: index.php?page=settings'); return;
        }
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [Security::userId()]);
        if (!Security::verifyPassword($current, $user['password'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Current password is incorrect.'];
            header('Location: index.php?page=settings'); return;
        }
        if (strlen($new) < 6) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'New password must be at least 6 characters.'];
            header('Location: index.php?page=settings'); return;
        }
        if ($new !== $confirm) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Passwords do not match.'];
            header('Location: index.php?page=settings'); return;
        }
        $this->db->update('users', ['password' => Security::hashPassword($new)], 'id = ?', [Security::userId()]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Password changed successfully.'];
        header('Location: index.php?page=settings');
    }

    private function users(): void {
        if (!Security::isAdmin()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Unauthorized.'];
            header('Location: index.php?page=settings'); return;
        }
        $data = ['page' => 'users'];
        $data['users'] = $this->db->fetchAll(
            "SELECT u1.*, u2.name as manager_name 
             FROM users u1 
             LEFT JOIN users u2 ON u1.parent_id = u2.id 
             ORDER BY u1.role, u1.name"
        );
        $data['managers'] = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('admin','manager') AND is_active = 1");
        require __DIR__ . '/../views/layout.php';
    }

    private function addUser(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf() || !Security::isAdmin()) {
            header('Location: index.php?page=settings&action=users'); return;
        }
        $name = Security::sanitize($_POST['name'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $role = in_array($_POST['role'] ?? '', ['admin','manager','agent','partner']) ? $_POST['role'] : 'agent';
        $password = $_POST['password'] ?? 'agent123';
        $parentId = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

        $existing = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Email already exists.'];
            header('Location: index.php?page=settings&action=users'); return;
        }

        $this->db->insert('users', [
            'name' => $name, 'email' => $email, 'role' => $role,
            'password' => Security::hashPassword($password),
            'parent_id' => $parentId
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => "User '{$name}' created with password: {$password}"];
        header('Location: index.php?page=settings&action=users');
    }

    private function toggleUser(): void {
        if (!Security::isAdmin()) { header('Location: index.php?page=settings'); return; }
        $id = intval($_GET['id'] ?? 0);
        if ($id === Security::userId()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Cannot deactivate yourself.'];
            header('Location: index.php?page=settings&action=users'); return;
        }
        $user = $this->db->fetch("SELECT is_active FROM users WHERE id = ?", [$id]);
        if ($user) {
            $this->db->update('users', ['is_active' => $user['is_active'] ? 0 : 1], 'id = ?', [$id]);
        }
        header('Location: index.php?page=settings&action=users');
    }

    // ===== API Key Management =====

    private function apiKeys(): void {
        if (!Security::isAdmin()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Unauthorized.'];
            header('Location: index.php?page=settings'); return;
        }
        $data = ['page' => 'api_keys'];
        $data['api_keys'] = $this->db->fetchAll("SELECT * FROM api_keys WHERE is_active = 1 ORDER BY created_at DESC");
        $data['new_key'] = $_SESSION['new_api_key'] ?? null;
        unset($_SESSION['new_api_key']);
        require __DIR__ . '/../views/layout.php';
    }

    private function generateApiKey(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf() || !Security::isAdmin()) {
            header('Location: index.php?page=settings&action=api_keys'); return;
        }

        $keyName = Security::sanitize($_POST['key_name'] ?? 'Unnamed Key');
        $perms = $_POST['perms'] ?? ['read'];

        // Generate a secure API key
        $apiKey = 'dsa_' . bin2hex(random_bytes(24)); // 52 chars total

        $this->db->insert('api_keys', [
            'key_name' => $keyName,
            'api_key' => $apiKey,
            'permissions' => json_encode($perms),
            'user_id' => Security::userId(),
        ]);

        $_SESSION['new_api_key'] = $apiKey;
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'API key generated! Copy it now — it won\'t be shown again.'];
        header('Location: index.php?page=settings&action=api_keys');
    }

    private function revokeApiKey(): void {
        if (!Security::isAdmin()) { header('Location: index.php?page=settings'); return; }
        $id = intval($_GET['id'] ?? 0);
        $this->db->update('api_keys', ['is_active' => 0], 'id = ?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'API key revoked.'];
        header('Location: index.php?page=settings&action=api_keys');
    }

    // ===== Enterprise Commission Management =====

    private function commissions(): void {
        if (!Security::isAdmin()) { header('Location: index.php'); return; }
        $data = ['page' => 'commissions'];
        $data['rates'] = $this->db->fetchAll("SELECT * FROM commission_rates ORDER BY bank_name, loan_type");
        require __DIR__ . '/../views/layout.php';
    }

    private function saveCommission(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf() || !Security::isAdmin()) {
            header('Location: index.php?page=settings&action=commissions'); return;
        }
        $bank = Security::sanitize($_POST['bank_name'] ?? '');
        $type = Security::sanitize($_POST['loan_type'] ?? '');
        $rate = floatval($_POST['rate'] ?? 0);

        if ($bank && $type) {
            $this->db->query(
                "INSERT INTO commission_rates (bank_name, loan_type, commission_percentage) 
                 VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE commission_percentage = ?",
                [$bank, $type, $rate, $rate]
            );
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Commission rate updated.'];
        }
        header('Location: index.php?page=settings&action=commissions');
    }

    private function slabs(): void {
        if (!Security::isAdmin()) { header('Location: index.php'); return; }
        $data = ['page' => 'slabs'];
        $data['slabs'] = $this->db->fetchAll("SELECT * FROM payout_slabs ORDER BY min_volume ASC");
        require __DIR__ . '/../views/layout.php';
    }

    private function saveSlab(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf() || !Security::isAdmin()) {
            header('Location: index.php?page=settings&action=slabs'); return;
        }
        $min = floatval($_POST['min_volume'] ?? 0);
        $max = floatval($_POST['max_volume'] ?? 0);
        $share = floatval($_POST['agent_share'] ?? 0);
        $desc = Security::sanitize($_POST['description'] ?? '');

        $this->db->insert('payout_slabs', [
            'min_volume' => $min, 'max_volume' => $max, 
            'agent_share_percentage' => $share, 'description' => $desc
        ]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Payout slab added.'];
        header('Location: index.php?page=settings&action=slabs');
    }

    private function loginHistory(): void {
        if (!Security::isAdmin()) { header('Location: index.php'); return; }
        $data = ['page' => 'login_history'];
        $data['logs'] = $this->db->fetchAll(
            "SELECT l.*, u.name as user_name, u.role 
             FROM login_logs l 
             JOIN users u ON l.user_id = u.id 
             ORDER BY l.created_at DESC LIMIT 100"
        );
        require __DIR__ . '/../views/layout.php';
    }
}
