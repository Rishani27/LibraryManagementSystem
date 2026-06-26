<?php
// ============================================================
// ULMS — Session Manager
// Core Layer: core/Session.php
// ============================================================

require_once __DIR__ . '/../config/config.php';

class Session {

    private static bool $started = false;

    /** Start session securely */
    public static function start(): void {
        if (!self::$started) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,    // set true on HTTPS
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
            self::$started = true;
        }
    }

    /** Store session value */
    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    /** Get session value */
    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /** Check if a key exists */
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    /** Remove a session key */
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    /** Destroy entire session */
    public static function destroy(): void {
        self::start();
        session_unset();
        session_destroy();
        self::$started = false;
    }

    /** Regenerate session ID (prevents fixation) */
    public static function regenerate(): void {
        self::start();
        session_regenerate_id(true);
    }

    // ── Auth helpers ─────────────────────────────────────────

    /** Store logged-in user info */
    public static function login(array $user): void {
        self::regenerate();
        self::set('user_id',   $user['id']);
        self::set('username',  $user['username']);
        self::set('full_name', $user['full_name']);
        self::set('role',      $user['role']);
    }

    /** Check if user is logged in */
    public static function isLoggedIn(): bool {
        return self::has('user_id');
    }

    /** Get current user's role */
    public static function getRole(): ?string {
        return self::get('role');
    }

    /** Get current user's ID */
    public static function getUserId(): ?int {
        return self::get('user_id') ? (int) self::get('user_id') : null;
    }

    /** Get current user's username */
    public static function getUsername(): ?string {
        return self::get('username');
    }

    /** Get current user's full name */
    public static function getFullName(): ?string {
        return self::get('full_name');
    }

    /**
     * Enforce authentication + optional role check.
     * Redirects to login if not authenticated.
     */
    public static function requireLogin(string ...$allowedRoles): void {
        self::start();
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . '/login.php?msg=login_required');
            exit;
        }
        if (!empty($allowedRoles) && !in_array(self::getRole(), $allowedRoles, true)) {
            header('Location: ' . APP_URL . '/login.php?msg=unauthorized');
            exit;
        }
    }

    /** Set a flash message */
    public static function flash(string $type, string $message): void {
        self::set('flash_type',    $type);
        self::set('flash_message', $message);
    }

    /** Get and clear flash message */
    public static function getFlash(): array {
        $flash = [
            'type'    => self::get('flash_type'),
            'message' => self::get('flash_message'),
        ];
        self::remove('flash_type');
        self::remove('flash_message');
        return $flash;
    }
}
