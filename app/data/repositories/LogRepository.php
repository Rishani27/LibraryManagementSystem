<?php
// ULMS — Log Repository
// Data Layer: data/repositories/LogRepository.php

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class LogRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(int $limit = 200, int $offset = 0): array {
        $stmt = $this->db->prepare('SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$limit, $offset]);
        return array_map([ActivityLog::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findByUser(int $userId): array {
        $stmt = $this->db->prepare('SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return array_map([ActivityLog::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findByAction(string $action): array {
        $stmt = $this->db->prepare('SELECT * FROM activity_logs WHERE action LIKE ? ORDER BY created_at DESC');
        $stmt->execute(['%'.$action.'%']);
        return array_map([ActivityLog::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findByDateRange(string $from, string $to): array {
        $stmt = $this->db->prepare('SELECT * FROM activity_logs WHERE DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC');
        $stmt->execute([$from, $to]);
        return array_map([ActivityLog::class, 'fromArray'], $stmt->fetchAll());
    }

    public function count(): int {
        return (int)$this->db->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO activity_logs (user_id, username, role, action, description, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['user_id']     ?? null,
            $data['username']    ?? '',
            $data['role']        ?? '',
            $data['action'],
            $data['description'] ?? '',
            $data['ip_address']  ?? '',
        ]);
        return (int)Database::getInstance()->lastInsertId();
    }
}
