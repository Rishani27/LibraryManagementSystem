<?php
// ============================================================
// ULMS — Borrow Service  (FR-06 — Member 3)
// Business Layer: business/services/BorrowService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/LoanRepository.php';
require_once __DIR__ . '/../../data/repositories/BookRepository.php';
require_once __DIR__ . '/../../data/repositories/UserRepository.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../validators/BorrowValidator.php';
require_once __DIR__ . '/LogService.php';

class BorrowService {

    private LoanRepository  $loanRepo;
    private BookRepository  $bookRepo;
    private UserRepository  $userRepo;
    private BorrowValidator $validator;
    private LogService      $logService;

    public function __construct() {
        $this->loanRepo   = new LoanRepository();
        $this->bookRepo   = new BookRepository();
        $this->userRepo   = new UserRepository();
        $this->validator  = new BorrowValidator();
        $this->logService = new LogService();
    }

    /** Issue a book to a student */
    public function issueBook(array $data, int $issuedBy): array {
        if (!$this->validator->validate($data)) {
            return ['success' => false, 'message' => $this->validator->getFirstError()];
        }

        $bookId    = (int)$data['book_id'];
        $studentId = (int)$data['student_id'];

        $book = $this->bookRepo->findById($bookId);
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found.'];
        }

        if (!$book->isAvailable()) {
            return ['success' => false, 'message' => "'{$book->title}' has no available copies."];
        }

        $student = $this->userRepo->findById($studentId);
        if (!$student || $student->role !== 'student') {
            return ['success' => false, 'message' => 'Invalid student selected.'];
        }

        // Check if student already has this book active
        $existing = $this->loanRepo->findByBookAndStudent($bookId, $studentId);
        if ($existing) {
            return ['success' => false, 'message' => "This student already has '{$book->title}' checked out."];
        }

        // Transactional: create loan + decrement copies
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $issueDate = date('Y-m-d');
            $dueDate   = date('Y-m-d', strtotime('+' . LOAN_PERIOD_DAYS . ' days'));

            $loanId = $this->loanRepo->create([
                'book_id'    => $bookId,
                'student_id' => $studentId,
                'issued_by'  => $issuedBy,
                'issue_date' => $issueDate,
                'due_date'   => $dueDate,
            ]);

            $this->bookRepo->decrementAvailable($bookId);

            $db->commit();

            $this->logService->log(
                'BOOK_BORROWED',
                "Book '{$book->title}' issued to {$student->full_name}. Due: $dueDate. Loan ID: $loanId."
            );

            return ['success' => true, 'message' => "Book issued successfully. Due date: $dueDate.", 'loan_id' => $loanId];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Failed to issue book. Please try again.'];
        }
    }

    public function getActiveLoans(): array {
        return $this->loanRepo->findActive();
    }

    public function getOverdueLoans(): array {
        return $this->loanRepo->findOverdue();
    }

    public function getAllStudents(): array {
        return $this->userRepo->findAll(role: 'student');
    }

    public function getDashboardStats(): array {
        return [
            'active_loans'  => $this->loanRepo->countActive(),
            'overdue_count' => $this->loanRepo->countOverdue(),
        ];
    }
}
