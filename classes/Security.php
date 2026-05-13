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
}
