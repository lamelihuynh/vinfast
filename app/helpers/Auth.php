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
class Auth
{
    private const REMEMBER_COOKIE = 'vf_remember';
    private const REMEMBER_TTL = 2592000; // 30 days

    public static function login(array $user, bool $remember = false): void
    {
        session_regenerate_id(true);
        $_SESSION['uid']   = $user['id'];
        $_SESSION['uname'] = $user['name'];
        $_SESSION['urole'] = $user['role'];
        $_SESSION['uavatar'] = $user['avatar'];
        if ($remember) {
            self::issueRememberCookie($user);
        } else {
            self::clearRememberCookie();
        }
    }
    public static function logout(): void
    {
        self::clearRememberCookie();
        session_unset();
        session_destroy();
    }
    public static function check(): bool
    {
        if (!empty($_SESSION['uid'])) {
            return true;
        }
        return self::attemptRememberLogin();
    }
    public static function id(): ?int
    {

    return $_SESSION['uid']   ?? null;
    }
    public static function name(): string
    {
        return $_SESSION['uname'] ?? '';
    }
    public static function role(): string
    {
        return $_SESSION['urole'] ?? '';
    }
    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }
    public static function avatar(): string
    {
        $avatar = (string)($_SESSION['uavatar'] ?? '');
        if ($avatar !== '') {
            return $avatar;
        }

        $uid = self::id();
        if (empty($uid)) {
            return '';
        }

        $user = (new User())->findById((int)$uid);
        if (!$user || empty($user['avatar'])) {
            return '';
        }

        $avatar = (string)$user['avatar'];
        $_SESSION['uavatar'] = $avatar;

        return $avatar;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }
    public static function requireAdmin(): void
    {
        if (!self::check() || !self::isAdmin()) {
            if (!self::check()) {
                header('Location: ' . BASE_URL . 'auth/login');
                exit;
            }
            http_response_code(403);
            die('Access denied.');
        }
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf'];
    }
    public static function verifyCsrf(): void
    {
        // Try to get CSRF token from POST, JSON body, or header
        $token = $_POST['_csrf'] ?? null;
        if ($token === null) {
            $input = file_get_contents('php://input');
            if ($input) {
                $data = json_decode($input, true);
                $token = $data['_csrf'] ?? null;
            }
        }
        if ($token === null) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (!hash_equals($_SESSION['csrf'] ?? '', $token ?? '')) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }
    }

    private static function issueRememberCookie(array $user): void
    {
        $uid = (int)($user['id'] ?? 0);
        $passwordHash = (string)($user['password'] ?? '');
        if ($uid <= 0 || $passwordHash === '') {
            return;
        }

        $expiresAt = time() + self::REMEMBER_TTL;
        $base = $uid . '|' . $expiresAt;
        $sig = hash_hmac('sha256', $base . '|' . $passwordHash, AUTH_COOKIE_SECRET);
        $value = $base . '|' . $sig;

        self::setCookie(self::REMEMBER_COOKIE, $value, $expiresAt);
    }

    private static function attemptRememberLogin(): bool
    {
        $raw = (string)($_COOKIE[self::REMEMBER_COOKIE] ?? '');
        if ($raw === '') {
            return false;
        }

        $parts = explode('|', $raw);
        if (count($parts) !== 3) {
            self::clearRememberCookie();
            return false;
        }

        [$uidRaw, $expiresRaw, $sigRaw] = $parts;
        if (!ctype_digit($uidRaw) || !ctype_digit($expiresRaw)) {
            self::clearRememberCookie();
            return false;
        }

        $uid = (int)$uidRaw;
        $expiresAt = (int)$expiresRaw;
        if ($uid <= 0 || $expiresAt < time()) {
            self::clearRememberCookie();
            return false;
        }

        $user = (new User())->findById($uid);
        if (!$user || (int)($user['is_locked'] ?? 0) === 1) {
            self::clearRememberCookie();
            return false;
        }

        $passwordHash = (string)($user['password'] ?? '');
        $base = $uid . '|' . $expiresAt;
        $expected = hash_hmac('sha256', $base . '|' . $passwordHash, AUTH_COOKIE_SECRET);
        if (!hash_equals($expected, (string)$sigRaw)) {
            self::clearRememberCookie();
            return false;
        }

        self::login($user, true);
        return true;
    }

    private static function clearRememberCookie(): void
    {
        self::setCookie(self::REMEMBER_COOKIE, '', time() - 3600);
        unset($_COOKIE[self::REMEMBER_COOKIE]);
    }

    private static function setCookie(string $name, string $value, int $expires): void
    {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');

        if (PHP_VERSION_ID >= 70300) {
            setcookie($name, $value, [
                'expires' => $expires,
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            return;
        }

        setcookie($name, $value, $expires, '/; samesite=Lax', '', $isHttps, true);
    }
}