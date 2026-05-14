<?php
/**
 * Security Helper
 * CSRF protection, input sanitization, and access control
 */
class Security {

    /**
     * Generate CSRF token field
     */
    public static function csrfField(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'] ?? '') . '">';
    }

    /**
     * Validate CSRF token
     */
    public static function validateCsrf(): bool {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    /**
     * Sanitize input string
     */
    public static function sanitize(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize phone number
     */
    public static function sanitizePhone(string $phone): string {
        return preg_replace('/[^0-9+\-\s()]/', '', trim($phone));
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Check if current user is admin
     */
    public static function isAdmin(): bool {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }

    /**
     * Get current user ID
     */
    public static function userId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user name
     */
    public static function userName(): string {
        return $_SESSION['user_name'] ?? 'Guest';
    }

    /**
     * Mask sensitive data (phone, email) for non-admins
     */
    public static function mask(string $input, string $type = 'phone'): string {
        if (self::isAdmin()) return $input;
        
        if ($type === 'phone') {
            return substr($input, 0, 2) . '*****' . substr($input, -3);
        } else {
            $parts = explode('@', $input);
            if (count($parts) < 2) return '*****';
            return substr($parts[0], 0, 1) . '***@' . $parts[1];
        }
    }

    /**
     * Check if current user has permission for an action
     */
    public static function can(string $action): bool {
        if (self::isAdmin()) return true;
        switch ($action) {
            case 'export': return false;
            case 'manage_users': return false;
            case 'view_commissions': return false;
            default: return true;
        }
    }

    // ===== BRUTE-FORCE PROTECTION =====

    private static string $lockoutDir = '';

    /**
     * Get the lockout tracking directory (creates if needed)
     */
    private static function getLockoutDir(): string {
        if (self::$lockoutDir) return self::$lockoutDir;
        self::$lockoutDir = sys_get_temp_dir() . '/dsa_lockout';
        if (!is_dir(self::$lockoutDir)) {
            @mkdir(self::$lockoutDir, 0700, true);
        }
        return self::$lockoutDir;
    }

    /**
     * Check if an IP address is currently locked out
     * @return array ['locked' => bool, 'attempts' => int, 'remaining_seconds' => int]
     */
    public static function checkLockout(string $ip): array {
        $file = self::getLockoutDir() . '/' . md5($ip) . '.json';
        if (!file_exists($file)) {
            return ['locked' => false, 'attempts' => 0, 'remaining_seconds' => 0];
        }

        $data = json_decode(file_get_contents($file), true);
        if (!$data) return ['locked' => false, 'attempts' => 0, 'remaining_seconds' => 0];

        $lockoutDuration = 900; // 15 minutes
        $maxAttempts = 5;

        // Clean up expired entries
        if (isset($data['locked_at']) && (time() - $data['locked_at']) > $lockoutDuration) {
            @unlink($file);
            return ['locked' => false, 'attempts' => 0, 'remaining_seconds' => 0];
        }

        $remaining = 0;
        if (isset($data['locked_at'])) {
            $remaining = $lockoutDuration - (time() - $data['locked_at']);
        }

        return [
            'locked' => ($data['attempts'] ?? 0) >= $maxAttempts,
            'attempts' => $data['attempts'] ?? 0,
            'remaining_seconds' => max(0, $remaining)
        ];
    }

    /**
     * Record a failed login attempt for an IP
     */
    public static function recordFailedAttempt(string $ip): int {
        $file = self::getLockoutDir() . '/' . md5($ip) . '.json';
        $data = ['attempts' => 0];

        if (file_exists($file)) {
            $existing = json_decode(file_get_contents($file), true);
            if ($existing) $data = $existing;
        }

        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        $data['last_attempt'] = time();

        if ($data['attempts'] >= 5) {
            $data['locked_at'] = time();
        }

        file_put_contents($file, json_encode($data));
        return $data['attempts'];
    }

    /**
     * Clear lockout data for an IP (on successful login)
     */
    public static function clearLockout(string $ip): void {
        $file = self::getLockoutDir() . '/' . md5($ip) . '.json';
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // ===== PASSWORD COMPLEXITY =====

    /**
     * Validate password complexity
     * @return array ['valid' => bool, 'errors' => string[]]
     */
    public static function validatePasswordComplexity(string $password): array {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one digit';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Regenerate session ID safely (prevents session fixation attacks)
     */
    public static function regenerateSession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
