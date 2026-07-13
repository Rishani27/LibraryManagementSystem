<?php
// ============================================================
// ULMS — User Service  (FR-03 — Member 1)
// Business Layer: business/services/UserService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/UserRepository.php';
require_once __DIR__ . '/../validators/UserValidator.php';
require_once __DIR__ . '/LogService.php';

class UserService {

    private UserRepository $repo;
    private UserValidator  $validator;
    private LogService     $logService;

    public function __construct() {
        $this->repo       = new UserRepository();
        $this->validator  = new UserValidator();
        $this->logService = new LogService();
    }

    /** Create a new user account (admin manually adding via modal) */
    public function createUser(array $data): array {
        if (!$this->validator->validate($data)) {
            return ['success' => false, 'message' => $this->validator->getFirstError()];
        }

        if ($this->repo->findByUsername($data['username'])) {
            return ['success' => false, 'message' => "Username/Index '{$data['username']}' already exists."];
        }

        if ($this->repo->findByEmail($data['email'])) {
            return ['success' => false, 'message' => "Email '{$data['email']}' is already registered."];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $id = $this->repo->create($data);

        $this->logService->log('USER_CREATED', "Created {$data['role']} account for '{$data['username']}' (ID: $id).");
        return ['success' => true, 'message' => 'User account created successfully.', 'id' => $id];
    }

    /** Student self-registration portal submission script */
    public function registerPendingStudent(array $data): array {
        if (empty(trim($data['username'] ?? ''))) {
            return ['success' => false, 'message' => 'University Index Number is required.'];
        }
        if (empty(trim($data['full_name'] ?? ''))) {
            return ['success' => false, 'message' => 'Full name is required.'];
        }
        if (empty(trim($data['email'] ?? ''))) {
            return ['success' => false, 'message' => 'Email address is required.'];
        }
        if (empty(trim($data['faculty'] ?? '')) || empty(trim($data['course'] ?? ''))) {
            return ['success' => false, 'message' => 'Faculty and Course selections are required.'];
        }
        
        if ($this->repo->findByUsername($data['username'])) {
            return ['success' => false, 'message' => "This university index number is already registered or pending approval."];
        }
        if ($this->repo->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'This email address is already registered.'];
        }
        if (strlen($data['password'] ?? '') < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        $db = Database::getInstance()->getConnection();
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Safely package faculty and course metadata inside the email column string
        $metaEmail = trim($data['email']) . '##FAC:' . $data['faculty'] . '##CRS:' . $data['course'];

        $stmt = $db->prepare(
            "INSERT INTO users (username, password, full_name, email, role, is_active) 
             VALUES (?, ?, ?, ?, 'student', 0)"
        );
        $stmt->execute([$data['username'], $hashedPassword, $data['full_name'], $metaEmail]);
        
        return ['success' => true, 'message' => 'Registration recorded.'];
    }

    /** Administrative Approval Engine for pending student sign-ups */
    public function approveStudentRegistration(int $userId): array {
        $user = $this->repo->findById($userId);
        if (!$user || $user->is_active) {
            return ['success' => false, 'message' => 'Invalid or already processed student profile record.'];
        }

        $db = Database::getInstance()->getConnection();
        
        // Strip custom tracking strings out cleanly back to normal operational formats
        $cleanEmail = $user->email;
        if (strpos($user->email, '##FAC:') !== false) {
            $parts = explode('##FAC:', $user->email);
            $cleanEmail = $parts[0];
        }

        $stmtUpdate = $db->prepare("UPDATE users SET email = ?, is_active = 1 WHERE id = ?");
        $stmtUpdate->execute([$cleanEmail, $userId]);

        $this->logService->log('REGISTRATION_APPROVED', "Approved student account Index [{$user->username}].");
        return ['success' => true, 'message' => "Student account index '{$user->username}' activated successfully."];
    }

    /** Administrative profile modifier script for editing existing users */
    public function modifyUserDetails(int $id, array $data): array {
        $user = $this->repo->findById($id);
        if (!$user) {
            return ['success' => false, 'message' => 'Target profile not found.'];
        }

        if (empty(trim($data['full_name']))) {
            return ['success' => false, 'message' => 'Full name cannot be left blank.'];
        }
        if (empty(trim($data['email']))) {
            return ['success' => false, 'message' => 'Email cannot be left blank.'];
        }

        $this->repo->update($id, [
            'full_name' => trim($data['full_name']),
            'email'     => trim($data['email']),
            'role'      => $user->role, 
            'is_active' => (int)$data['is_active']
        ]);

        $this->logService->log('USER_UPDATED', "Administrator modified profile metrics for index '{$user->username}'.");
        return ['success' => true, 'message' => 'User registry modifications saved successfully.'];
    }

    public function deleteUser(int $userId): array {
        $user = $this->repo->findById($userId);
        if (!$user) return ['success' => false, 'message' => 'User not found.'];
        if ($user->role === 'admin') return ['success' => false, 'message' => 'Admin accounts cannot be deleted.'];

        $this->repo->delete($userId);
        $this->logService->log('USER_DELETED', "Deleted user '{$user->username}' (Role: {$user->role}).");
        return ['success' => true, 'message' => "User '{$user->username}' deleted successfully."];
    }

    public function getAllUsers(?string $role = null): array { return $this->repo->findAll(role: $role); }
    public function getUserById(int $id): ?object { return $this->repo->findById($id); }
    public function searchUsers(string $query, ?string $role = null): array { return $this->repo->search($query, $role); }
    
    public function getUserStats(): array {
        return [
            'total'     => $this->repo->count(),
            'admins'    => $this->repo->count('admin'),
            'librarians'=> $this->repo->count('librarian'),
            'students'  => $this->repo->count('student'),
        ];
    }
}