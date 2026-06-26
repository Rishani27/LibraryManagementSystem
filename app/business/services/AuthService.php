<?php
// ============================================================
// ULMS — Auth Service
// Business Layer: business/services/AuthService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/UserRepository.php';
require_once __DIR__ . '/../../core/Session.php';
require_once __DIR__ . '/LogService.php';

class AuthService {

    private UserRepository $userRepo;
    private LogService     $logService;

    public function __construct() {
        $this->userRepo   = new UserRepository();
        $this->logService = new LogService();
    }

    /**
     * Attempt login. Returns array with 'success', 'message', optional 'role'.
     */
    public function login(string $username, string $password): array {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required.'];
        }

        $user = $this->userRepo->findByUsername(trim($username));

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        if (!$user->is_active) {
            return ['success' => false, 'message' => 'Your account has been deactivated. Contact admin.'];
        }

        if (!password_verify($password, $user->password)) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        // Store session
        Session::login($user->toArray());

        // Log the login event
        $this->logService->log(
            'LOGIN',
            "User '{$user->username}' logged in as {$user->role}.",
            $user->id, $user->username, $user->role
        );

        return ['success' => true, 'role' => $user->role, 'message' => 'Login successful.'];
    }

    /** Logout current user */
    public function logout(): void {
        $username = Session::getUsername();
        $userId   = Session::getUserId();
        $role     = Session::getRole();

        $this->logService->log('LOGOUT', "User '{$username}' logged out.", $userId, $username, $role);

        Session::destroy();
    }

    /** Get redirect URL based on role */
    public function getDashboardUrl(string $role): string {
        return match($role) {
            'admin'     => APP_URL . '/../app/presentation/admin/dashboard.php',
            'librarian' => APP_URL . '/../app/presentation/librarian/dashboard.php',
            'student'   => APP_URL . '/../app/presentation/student/dashboard.php',
            default     => APP_URL . '/login.php',
        };
    }
}
