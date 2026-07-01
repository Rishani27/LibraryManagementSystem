<?php
// ============================================================
// ULMS — Return Service  (FR-07 — Member 3)
// Business Layer: business/services/ReturnService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/LoanRepository.php';
require_once __DIR__ . '/../../data/repositories/BookRepository.php';
require_once __DIR__ . '/../../data/repositories/FineRepository.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Helper.php';
require_once __DIR__ . '/LogService.php';

class ReturnService {

    private LoanRepository $loanRepo;
    private BookRepository $bookRepo;
    private FineRepository $fineRepo;
    private LogService     $logService;

    public function __construct() {
        $this->loanRepo   = new LoanRepository();
        $this->bookRepo   = new BookRepository();
        $this->fineRepo   = new FineRepository();
        $this->logService = new LogService();
    }

    /** Process book return */
    public function returnBook(int $loanId): array {
        $loan = $this->loanRepo->findById($loanId);
        if (!$loan) {
            return ['success' => false, 'message' => 'Loan record not found.'];
        }

        if ($loan->status === 'returned') {
            return ['success' => false, 'message' => 'This book has already been returned.'];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $returnDate  = date('Y-m-d');
            $overdueDays = Helper::calculateOverdueDays($loan->due_date, $returnDate);
            $fineAmount  = Helper::calculateFine($overdueDays);

            // Mark loan returned
            $this->loanRepo->markReturned($loanId, $returnDate);

            // Restore available copies
            $this->bookRepo->incrementAvailable($loan->book_id);

            $fineMsg = '';
            if ($overdueDays > 0) {
                // Create fine record
                $this->fineRepo->create([
                    'loan_id'     => $loanId,
                    'student_id'  => $loan->student_id,
                    'amount'      => $fineAmount,
                    'overdue_days'=> $overdueDays,
                ]);
                $fineMsg = " Fine of Rs. {$fineAmount} has been applied ({$overdueDays} overdue day(s)).";
            }

            $db->commit();

            $this->logService->log(
                'BOOK_RETURNED',
                "Book '{$loan->book_title}' returned by {$loan->student_name}. Overdue: {$overdueDays} day(s). Fine: Rs. {$fineAmount}."
            );

            return [
                'success'      => true,
                'message'      => "Book returned successfully.{$fineMsg}",
                'overdue_days' => $overdueDays,
                'fine_amount'  => $fineAmount,
            ];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Return processing failed. Please try again.'];
        }
    }

    /** Lookup all currently active loans for return form */
    public function getActiveLoans(): array {
        return $this->loanRepo->findActive();
    }

    public function getLoanById(int $id): ?object {
        return $this->loanRepo->findById($id);
    }
}
