<?php
// ============================================================
// ULMS — Fine Repository
// Data Layer: data/repositories/FineRepository.php
// ============================================================

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/Fine.php';

class FineRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private const JOIN_SQL =
        'SELECT f.*,
                s.full_name  AS student_name,
                s.username   AS student_username,
                b.title      AS book_title,
                l.due_date   AS due_date
         FROM fines f
         JOIN loans l  ON l.id = f.loan_id
         JOIN books b  ON b.id = l.book_id
         JOIN users s  ON s.id = f.student_id';

    public function findById(int $id): ?Fine {
        $stmt = $this->db->prepare(self::JOIN_SQL . ' WHERE f.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Fine::fromArray($row) : null;
    }

    public function findAll(): array {
        $stmt = $this->db->prepare(self::JOIN_SQL . ' ORDER BY f.created_at DESC');
        $stmt->execute();
        return array_map([Fine::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findUnpaid(): array {
        $stmt = $this->db->prepare(self::JOIN_SQL . " WHERE f.status = 'unpaid' ORDER BY f.created_at DESC");
        $stmt->execute();
        return array_map([Fine::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findByStudent(int $studentId): array {
        $stmt = $this->db->prepare(self::JOIN_SQL . ' WHERE f.student_id = ? ORDER BY f.created_at DESC');
        $stmt->execute([$studentId]);
        return array_map([Fine::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findByLoan(int $loanId): ?Fine {
        $stmt = $this->db->prepare(self::JOIN_SQL . ' WHERE f.loan_id = ? LIMIT 1');
        $stmt->execute([$loanId]);
        $row = $stmt->fetch();
        return $row ? Fine::fromArray($row) : null;
    }

    public function sumUnpaid(): float {
        return (float)$this->db->query("SELECT COALESCE(SUM(amount), 0) FROM fines WHERE status = 'unpaid'")->fetchColumn();
    }

    public function countUnpaid(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM fines WHERE status = 'unpaid'")->fetchColumn();
    }

    public function countPaid(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM fines WHERE status = 'paid'")->fetchColumn();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare('INSERT INTO fines (loan_id, student_id, amount, overdue_days) VALUES (?,?,?,?)');
        $stmt->execute([$data['loan_id'], $data['student_id'], $data['amount'], $data['overdue_days']]);
        return (int)Database::getInstance()->lastInsertId();
    }

    public function markPaid(int $fineId, int $settledBy): bool {
        $stmt = $this->db->prepare("UPDATE fines SET status='paid', settled_at=NOW(), settled_by=? WHERE id=?");
        return $stmt->execute([$settledBy, $fineId]);
    }

    public function findByDateRange(string $from, string $to): array {
        $stmt = $this->db->prepare(self::JOIN_SQL . ' WHERE DATE(f.created_at) BETWEEN ? AND ? ORDER BY f.created_at DESC');
        $stmt->execute([$from, $to]);
        return array_map([Fine::class, 'fromArray'], $stmt->fetchAll());
    }

    public function totalCollected(): float {
        return (float)$this->db->query("SELECT COALESCE(SUM(amount), 0) FROM fines WHERE status = 'paid'")->fetchColumn();
    }
}
