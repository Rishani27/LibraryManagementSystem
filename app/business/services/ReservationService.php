<?php
// ============================================================
// ULMS — Reservation Service (Hold Allocations Setup)
// Business Layer: business/services/ReservationService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/ReservationRepository.php';
require_once __DIR__ . '/../../data/repositories/BookRepository.php';
require_once __DIR__ . '/../../data/repositories/UserRepository.php';
require_once __DIR__ . '/../../data/repositories/LoanRepository.php';
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

    public function reserve(int $bookId, int $studentId): array {
        $book = $this->bookRepo->findById($bookId);
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found.'];
        }

        if ($this->repo->hasActiveReservation($bookId, $studentId)) {
            return ['success' => false, 'message' => "You already have a pending reservation request for this asset."];
        }

        $id = $this->repo->create($bookId, $studentId);
        $this->logService->log('RESERVATION_PLACED', "Student $studentId requested book '{$book->title}'.", $studentId);

        return ['success' => true, 'message' => "Reservation request placed successfully.", 'id' => $id];
    }

    /** Librarian Approves the Hold and sets Expiration Limit */
    public function approveHold(int $id, int $holdDays): array {
        // Run expiry checks to make sure stock is released before viewing
        $this->processExpirations();

        $res = $this->repo->findById($id);
        if (!$res || $res->status !== 'pending') {
            return ['success' => false, 'message' => 'Reservation request is not pending or valid.'];
        }

        $book = $this->bookRepo->findById($res->book_id);
        if ($book->available_copies < 1) {
            return ['success' => false, 'message' => 'No available stock copies remaining to allocate a hold.'];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Decrement stock copy count
            $this->bookRepo->decrementAvailable($res->book_id);
            
            // Set hold expiration timestamp into the fulfilled_at field
            $expiryTimestamp = date('Y-m-d H:i:s', strtotime("+$holdDays days"));
            $this->repo->updateStatus($id, 'pending', $expiryTimestamp);

            $db->commit();
            $this->logService->log('RESERVATION_APPROVED', "Approved hold for '{$book->title}' until $expiryTimestamp.");
            return ['success' => true, 'message' => 'Hold approved. Inventory copy allocated safely.'];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Failed to process hold allocation.'];
        }
    }

    /** Convert Approved Request Into Active General Circulation Loan */
    public function issueFromHold(int $id, int $librarianId): array {
        $res = $this->repo->findById($id);
        if (!$res || $res->status !== 'pending' || empty($res->fulfilled_at)) {
            return ['success' => false, 'message' => 'Invalid or expired pickup request.'];
        }

        // Verify deadline validity bounds
        if (strtotime($res->fulfilled_at) < time()) {
            $this->processExpirations();
            return ['success' => false, 'message' => 'This hold assignment has expired and returned to stock.'];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // Close reservation record state 
            $this->repo->updateStatus($id, 'fulfilled', date('Y-m-d H:i:s'));

            // Create standard circulation loan record entries directly
            $stmt = $db->getConnection()->prepare(
                'INSERT INTO loans (book_id, student_id, issued_by, issue_date, due_date, status) 
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $res->book_id,
                $res->student_id,
                $librarianId,
                date('Y-m-d'),
                date('Y-m-d', strtotime('+14 days')),
                'active'
            ]);

            $db->commit();
            $this->logService->log('LOAN_FROM_HOLD', "Book '{$res->book_title}' checked out from active reservation hold.");
            return ['success' => true, 'message' => 'Loan issued successfully from active hold template.'];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Failed to convert asset hold allocation into loan logs.'];
        }
    }

    /** Cancel request or active approved assignment */
    public function cancel(int $id): array {
        $res = $this->repo->findById($id);
        if (!$res) return ['success' => false, 'message' => 'Request logs missing.'];

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            // If it was already approved (has timestamp), restore stock copies
            if ($res->status === 'pending' && !empty($res->fulfilled_at) && strtotime($res->fulfilled_at) >= time()) {
                $this->bookRepo->incrementAvailable($res->book_id);
            }

            $this->repo->updateStatus($id, 'cancelled', null);
            $db->commit();
            return ['success' => true, 'message' => 'Reservation record cancelled.'];
        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Cancellation processes failed.'];
        }
    }

    /** Clean Up Expired Deadlines Automatically */
    public function processExpirations(): void {
        $db = Database::getInstance();
        // Look for pending statuses where the expiration timeline has passed
        $stmt = $db->getConnection()->query(
            "SELECT id, book_id FROM reservations 
             WHERE status = 'pending' AND fulfilled_at IS NOT NULL AND fulfilled_at < NOW()"
        );
        $expired = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($expired as $r) {
            $db->beginTransaction();
            try {
                // Update state status logs out to cancelled configuration paths
                $stmtUpdate = $db->getConnection()->prepare(
                    "UPDATE reservations SET status = 'cancelled' WHERE id = ?"
                );
                $stmtUpdate->execute([$r['id']]);

                // Return stock copy allocation bounds back to catalog pools
                $this->bookRepo->incrementAvailable($r['book_id']);
                $db->commit();
            } catch (Exception $e) {
                $db->rollBack();
            }
        }
    }

    // ============================================================
    // HELPER PASSTHROUGH ACCESS METHODS
    // ============================================================
    public function getAllReservations(): array { 
        $this->processExpirations();
        return $this->repo->findAll(); 
    }
    
    public function getPendingReservations(): array { 
        $this->processExpirations();
        return $this->repo->findPending(); 
    }
    
    /** MATCHES STUDENT CATALOG LOGIC */
    public function getStudentReservations(int $studentId): array {
        $this->processExpirations();
        return $this->repo->findByStudent($studentId);
    }
    
    public function countPending(): int { 
        return $this->repo->countPending(); 
    }
}