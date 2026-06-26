<?php
// ============================================================
// ULMS — User Repository
// Data Layer: data/repositories/UserRepository.php
// Responsibility: ALL user-related SQL queries via PDO ONLY
// ============================================================

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Find user by ID */
    public function findById(int $id): ?User {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    /** Find user by username */
    public function findByUsername(string $username): ?User {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    /** Find user by email */
    public function findByEmail(string $email): ?User {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    /** Get all users, optional role filter */
    public function findAll(?string $role = null, int $limit = 100, int $offset = 0): array {
        if ($role) {
            $stmt = $this->db->prepare(
                'SELECT * FROM users WHERE role = ? ORDER BY created_at DESC LIMIT ? OFFSET ?'
            );
            $stmt->execute([$role, $limit, $offset]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?'
            );
            $stmt->execute([$limit, $offset]);
        }
        return array_map([User::class, 'fromArray'], $stmt->fetchAll());
    }

    /** Count all users, optional role filter */
    public function count(?string $role = null): int {
        if ($role) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
            $stmt->execute([$role]);
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users');
            $stmt->execute();
        }
        return (int)$stmt->fetchColumn();
    }

    /** Create a new user */
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password, full_name, email, role)
             VALUES (:username, :password, :full_name, :email, :role)'
        );
        $stmt->execute([
            ':username'  => $data['username'],
            ':password'  => $data['password'],
            ':full_name' => $data['full_name'],
            ':email'     => $data['email'],
            ':role'      => $data['role'],
        ]);
        return (int)Database::getInstance()->lastInsertId();
    }

    /** Update user details */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE users SET full_name = :full_name, email = :email,
                              role = :role, is_active = :is_active
             WHERE id = :id'
        );
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email'     => $data['email'],
            ':role'      => $data['role'],
            ':is_active' => $data['is_active'] ?? 1,
            ':id'        => $id,
        ]);
    }

    /** Delete user by ID */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /** Search users by name or username */
    public function search(string $query, ?string $role = null): array {
        $q = '%' . $query . '%';
        if ($role) {
            $stmt = $this->db->prepare(
                'SELECT * FROM users
                 WHERE role = ? AND (full_name LIKE ? OR username LIKE ? OR email LIKE ?)
                 ORDER BY full_name'
            );
            $stmt->execute([$role, $q, $q, $q]);
        } else {
            $stmt = $this->db->prepare(
                'SELECT * FROM users
                 WHERE full_name LIKE ? OR username LIKE ? OR email LIKE ?
                 ORDER BY full_name'
            );
            $stmt->execute([$q, $q, $q]);
        }
        return array_map([User::class, 'fromArray'], $stmt->fetchAll());
    }
}
