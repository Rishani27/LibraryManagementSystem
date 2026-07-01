
<?php
// ============================================================
// ULMS — Reservation Service  (FR-08 — Member 4)
// Business Layer: business/services/ReservationService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/ReservationRepository.php';
require_once __DIR__ . '/../../data/repositories/BookRepository.php';
require_once __DIR__ . '/../../data/repositories/UserRepository.php';
require_once __DIR__ . '/LogService.php';

class ReservationService {

    private ReservationRepository $repo;
    private BookRepository        $bookRepo;
    private LogService            $logService;

    public function __construct() {
        $this->repo       = new ReservationRepository();
        $this->bookRepo   = new BookRepository();
        $this->logService = new LogService();
    }

    /** Student places a reservation on an unavailable book */
    public function reserve(int $bookId, int $studentId): array {
        $book = $this->bookRepo->findById($bookId);
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found.'];
        }

        if ($book->isAvailable()) {
            return ['success' => false, 'message' => "'{$book->title}' is currently available — you can borrow it directly."];
        }

        if ($this->repo->hasActiveReservation($bookId, $studentId)) {
            return ['success' => false, 'message' => "You already have a pending reservation for '{$book->title}'."];
        }

        $id = $this->repo->create($bookId, $studentId);

        $this->logService->log(
            'RESERVATION_PLACED',
            "Student (ID: $studentId) reserved book '{$book->title}' (ID: $bookId).",
            $studentId
        );

        return ['success' => true, 'message' => "Reservation placed for '{$book->title}'.", 'id' => $id];
    }

    /** Librarian fulfils a reservation (book becomes available) */
    public function fulfil(int $reservationId): array {
        $res = $this->repo->findById($reservationId);
        if (!$res) {
            return ['success' => false, 'message' => 'Reservation not found.'];
        }

        if ($res->status !== 'pending') {
            return ['success' => false, 'message' => 'This reservation is no longer pending.'];
        }

        $this->repo->updateStatus($reservationId, 'fulfilled');

        $this->logService->log(
            'RESERVATION_FULFILLED',
            "Reservation ID $reservationId fulfilled for student {$res->student_name} — book '{$res->book_title}'."
        );

        return ['success' => true, 'message' => 'Reservation marked as fulfilled.'];
    }

    /** Cancel a reservation */
    public function cancel(int $reservationId): array {
        $res = $this->repo->findById($reservationId);
        if (!$res) {
            return ['success' => false, 'message' => 'Reservation not found.'];
        }

        $this->repo->updateStatus($reservationId, 'cancelled');

        $this->logService->log(
            'RESERVATION_CANCELLED',
            "Reservation ID $reservationId cancelled for book '{$res->book_title}'."
        );

        return ['success' => true, 'message' => 'Reservation cancelled.'];
    }

    public function getAllReservations(): array { return $this->repo->findAll(); }
    public function getPendingReservations(): array { return $this->repo->findPending(); }
    public function getStudentReservations(int $studentId): array { return $this->repo->findByStudent($studentId); }
    public function countPending(): int { return $this->repo->countPending(); }
}
