<?php
/**
 * app/helpers/Auth.php — Session & Role Helper
 * Owner: All members (common)
 *
 * login()       — store user in session after successful login
 * logout()      — destroy session
 * check()       — is anyone logged in?
 * isAdmin()     — is the logged-in user an admin?
 * requireLogin()  — redirect to /auth/login if not logged in
 * requireAdmin()  — redirect or abort if not admin (used by admin.php gate)
 * csrfToken()   — generate / return token for forms
 * verifyCsrf()  — validate POST token, die on mismatch
 */
class Auth {
    public static function login(array $user): void {
        session_regenerate_id(true);
        $_SESSION['uid']   = $user['id'];
        $_SESSION['uname'] = $user['name'];
        $_SESSION['urole'] = $user['role'];
    }
    public static function logout(): void { session_unset(); session_destroy(); }
    public static function check(): bool  { return !empty($_SESSION['uid']); }
    public static function id(): ?int     { return $_SESSION['uid']   ?? null; }
    public static function name(): string { return $_SESSION['uname'] ?? ''; }
    public static function role(): string { return $_SESSION['urole'] ?? ''; }
    public static function isAdmin(): bool { return self::role() === 'admin'; }

    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . 'auth/login'); exit;
        }
    }
    public static function requireAdmin(): void {
        if (!self::check() || !self::isAdmin()) {
            if (!self::check()) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
            http_response_code(403); die('Access denied.');
        }
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf'];
    }
    public static function verifyCsrf(): void {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf'] ?? '')) {
            http_response_code(403); die('Invalid CSRF token.');
        }
    }
}
