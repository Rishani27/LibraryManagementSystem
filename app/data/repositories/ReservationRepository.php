<?php
// ============================================================
// ULMS — Reservation Repository
// Data Layer: data/repositories/ReservationRepository.php
// ============================================================

require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../models/Reservation.php';

class ReservationRepository {

    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    private const JOIN_SQL =
        'SELECT r.*,
                b.title     AS book_title,
                b.author    AS book_author,
                s.full_name AS student_name,
                s.username  AS student_username
         FROM reservations r
         JOIN books b ON b.id = r.book_id
         JOIN users s ON s.id = r.student_id';

    public function findById(int $id): ?Reservation {
        $stmt = $this->db->prepare(self::JOIN_SQL . ' WHERE r.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? Reservation::fromArray($row) : null;
    }

    public function findAll(): array {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . ' ORDER BY r.reserved_at DESC'
        );
        $stmt->execute();
        return array_map([Reservation::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findPending(): array {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . " WHERE r.status = 'pending' ORDER BY r.reserved_at ASC"
        );
        $stmt->execute();
        return array_map([Reservation::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findByStudent(int $studentId): array {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . ' WHERE r.student_id = ? ORDER BY r.reserved_at DESC'
        );
        $stmt->execute([$studentId]);
        return array_map([Reservation::class, 'fromArray'], $stmt->fetchAll());
    }

    public function hasActiveReservation(int $bookId, int $studentId): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reservations
             WHERE book_id = ? AND student_id = ? AND status = 'pending'"
        );
        $stmt->execute([$bookId, $studentId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function countPending(): int {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM reservations WHERE status = 'pending'"
        )->fetchColumn();
    }

    public function create(int $bookId, int $studentId): int {
        $stmt = $this->db->prepare(
            'INSERT INTO reservations (book_id, student_id) VALUES (?, ?)'
        );
        $stmt->execute([$bookId, $studentId]);
        return (int)Database::getInstance()->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?string $dateTime = null): bool {
        $stmt = $this->db->prepare(
            'UPDATE reservations SET status = ?, fulfilled_at = ? WHERE id = ?'
        );
        return $stmt->execute([$status, $dateTime, $id]);
    }

    public function findByDateRange(string $from, string $to): array {
        $stmt = $this->db->prepare(
            self::JOIN_SQL . ' WHERE DATE(r.reserved_at) BETWEEN ? AND ? ORDER BY r.reserved_at DESC'
        );
        $stmt->execute([$from, $to]);
        return array_map([Reservation::class, 'fromArray'], $stmt->fetchAll());
    }
}