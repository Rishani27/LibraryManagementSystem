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

    /** Create a new user account (admin only) */
    public function createUser(array $data): array {
        if (!$this->validator->validate($data)) {
            return ['success' => false, 'message' => $this->validator->getFirstError()];
        }

        // Check username uniqueness
        if ($this->repo->findByUsername($data['username'])) {
            return ['success' => false, 'message' => "Username '{$data['username']}' already exists."];
        }

        // Check email uniqueness
        if ($this->repo->findByEmail($data['email'])) {
            return ['success' => false, 'message' => "Email '{$data['email']}' is already registered."];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $id = $this->repo->create($data);

        $this->logService->log(
            'USER_CREATED',
            "Created {$data['role']} account for '{$data['username']}' (ID: $id)."
        );

        return ['success' => true, 'message' => 'User account created successfully.', 'id' => $id];
    }

    /** Delete a user account */
    public function deleteUser(int $userId): array {
        $user = $this->repo->findById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        if ($user->role === 'admin') {
            return ['success' => false, 'message' => 'Admin accounts cannot be deleted.'];
        }

        $this->repo->delete($userId);

        $this->logService->log(
            'USER_DELETED',
            "Deleted user '{$user->username}' (Role: {$user->role})."
        );

        return ['success' => true, 'message' => "User '{$user->username}' deleted successfully."];
    }

    /** Get all users, optionally filtered by role */
    public function getAllUsers(?string $role = null): array {
        return $this->repo->findAll(role: $role);
    }

    /** Get a single user by ID */
    public function getUserById(int $id): ?object {
        return $this->repo->findById($id);
    }

    /** Search users */
    public function searchUsers(string $query, ?string $role = null): array {
        return $this->repo->search($query, $role);
    }

    /** Dashboard stats */
    public function getUserStats(): array {
        return [
            'total'     => $this->repo->count(),
            'admins'    => $this->repo->count('admin'),
            'librarians'=> $this->repo->count('librarian'),
            'students'  => $this->repo->count('student'),
        ];
    }
}
