
<?php
// ============================================================
// ULMS — Fine Service  (FR-09 — Member 4)
// Business Layer: business/services/FineService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/FineRepository.php';
require_once __DIR__ . '/../../data/repositories/LoanRepository.php';
require_once __DIR__ . '/../../core/Helper.php';
require_once __DIR__ . '/LogService.php';

class FineService {

    private FineRepository $repo;
    private LoanRepository $loanRepo;
    private LogService     $logService;

    public function __construct() {
        $this->repo       = new FineRepository();
        $this->loanRepo   = new LoanRepository();
        $this->logService = new LogService();
    }

    /** Settle (pay) a fine */
    public function settleFine(int $fineId, int $settledBy): array {
        $fine = $this->repo->findById($fineId);
        if (!$fine) {
            return ['success' => false, 'message' => 'Fine record not found.'];
        }

        if ($fine->status === 'paid') {
            return ['success' => false, 'message' => 'This fine has already been paid.'];
        }

        $this->repo->markPaid($fineId, $settledBy);

        $this->logService->log(
            'FINE_SETTLED',
            "Fine ID $fineId settled for {$fine->student_name}. Amount: Rs. {$fine->amount}."
        );

        return ['success' => true, 'message' => "Fine of Rs. {$fine->amount} settled successfully."];
    }

    public function getAllFines(): array { return $this->repo->findAll(); }
    public function getUnpaidFines(): array { return $this->repo->findUnpaid(); }
    public function getStudentFines(int $studentId): array { return $this->repo->findByStudent($studentId); }

    public function getDashboardStats(): array {
        return [
            'unpaid_count'   => $this->repo->countUnpaid(),
            'paid_count'     => $this->repo->countPaid(),
            'unpaid_total'   => $this->repo->sumUnpaid(),
            'total_collected'=> $this->repo->totalCollected(),
        ];
    }

    /** Check for and create fines on overdue active loans (called by a cron or admin action) */
    public function processOverdueFines(): int {
        $overdue = $this->loanRepo->findOverdue();
        $count   = 0;

        foreach ($overdue as $loan) {
            // Only create fine if one doesn't already exist for this loan
            if (!$this->repo->findByLoan($loan->id)) {
                $days   = Helper::calculateOverdueDays($loan->due_date);
                $amount = Helper::calculateFine($days);

                $this->repo->create([
                    'loan_id'      => $loan->id,
                    'student_id'   => $loan->student_id,
                    'amount'       => $amount,
                    'overdue_days' => $days,
                ]);
                $count++;
            }
        }

        return $count;
    }

    public function getFinesByDateRange(string $from, string $to): array {
        return $this->repo->findByDateRange($from, $to);
    }
}
