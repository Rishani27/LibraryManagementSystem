<?php
// ============================================================
// ULMS — Loan Repository
// Data Layer: data/repositories/LoanRepository.php
// ============================================================

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/Loan.php';

class LoanRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private const JOIN_SQL =
        'SELECT l.*,
                b.title        AS book_title,
                b.author       AS book_author,
                s.full_name    AS student_name,
                s.username     AS student_username,
                lib.full_name  AS issued_by_name
         FROM loans l
         JOIN books b   ON b.id = l.book_id
         JOIN users s   ON s.id = l.student_id
         JOIN users lib ON lib.id = l.issued_by';

    public function findById(int $id): ?Loan {
        $stmt = $this->db->prepare(self::JOIN_SQL . ' WHERE l.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Loan::fromArray($row) : null;
    }

    public function findAll(int $limit = 200, int $offset = 0): array {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . ' ORDER BY l.created_at DESC LIMIT ? OFFSET ?'
        );
        $stmt->execute([$limit, $offset]);
        return array_map([Loan::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findByStudent(int $studentId): array {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . ' WHERE l.student_id = ? ORDER BY l.created_at DESC'
        );
        $stmt->execute([$studentId]);
        return array_map([Loan::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findActive(): array {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . " WHERE l.status = 'active' ORDER BY l.due_date ASC"
        );
        $stmt->execute();
        return array_map([Loan::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findOverdue(): array {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare(
            self::JOIN_SQL . " WHERE l.status = 'active' AND l.due_date < ? ORDER BY l.due_date ASC"
        );
        $stmt->execute([$today]);
        return array_map([Loan::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findByBookAndStudent(int $bookId, int $studentId): ?Loan {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . " WHERE l.book_id = ? AND l.student_id = ? AND l.status = 'active' LIMIT 1"
        );
        $stmt->execute([$bookId, $studentId]);
        $row = $stmt->fetch();
        return $row ? Loan::fromArray($row) : null;
    }

    public function countActive(): int {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM loans WHERE status = 'active'"
        )->fetchColumn();
    }

    public function countOverdue(): int {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM loans WHERE status = 'active' AND due_date < ?"
        );
        $stmt->execute([$today]);
        return (int)$stmt->fetchColumn();
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            'INSERT INTO loans (book_id, student_id, issued_by, issue_date, due_date, status)
             VALUES (:book_id, :student_id, :issued_by, :issue_date, :due_date, :status)'
        );
        $stmt->execute([
            ':book_id'    => $data['book_id'],
            ':student_id' => $data['student_id'],
            ':issued_by'  => $data['issued_by'],
            ':issue_date' => $data['issue_date'],
            ':due_date'   => $data['due_date'],
            ':status'     => 'active',
        ]);
        return (int)Database::getInstance()->lastInsertId();
    }

    public function markReturned(int $loanId, string $returnDate): bool {
        $stmt = $this->db->prepare(
            "UPDATE loans SET status = 'returned', return_date = ? WHERE id = ?"
        );
        return $stmt->execute([$returnDate, $loanId]);
    }

    public function markOverdue(int $loanId): bool {
        $stmt = $this->db->prepare(
            "UPDATE loans SET status = 'overdue' WHERE id = ?"
        );
        return $stmt->execute([$loanId]);
    }

    /** For reports — date range filter */
    public function findByDateRange(string $from, string $to): array {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . ' WHERE l.issue_date BETWEEN ? AND ? ORDER BY l.issue_date DESC'
        );
        $stmt->execute([$from, $to]);
        return array_map([Loan::class, 'fromArray'], $stmt->fetchAll());
    }
}
