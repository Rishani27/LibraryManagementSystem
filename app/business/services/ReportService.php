<?php
// ============================================================
// ULMS — Report Service  (FR-11 — Member 5)
// Business Layer: business/services/ReportService.php
// ============================================================

require_once __DIR__ . '/../../data/repositories/LoanRepository.php';
require_once __DIR__ . '/../../data/repositories/FineRepository.php';
require_once __DIR__ . '/../../data/repositories/ReservationRepository.php';
require_once __DIR__ . '/../../data/repositories/LogRepository.php';

class ReportService {

    private LoanRepository        $loanRepo;
    private FineRepository        $fineRepo;
    private ReservationRepository $resRepo;
    private LogRepository         $logRepo;

    public function __construct() {
        $this->loanRepo = new LoanRepository();
        $this->fineRepo = new FineRepository();
        $this->resRepo  = new ReservationRepository();
        $this->logRepo  = new LogRepository();
    }

    /** Borrowed books report, optionally date-filtered */
    public function getBorrowedReport(?string $from = null, ?string $to = null): array {
        if ($from && $to) {
            return $this->loanRepo->findByDateRange($from, $to);
        }
        return $this->loanRepo->findAll(500);
    }

    /** Overdue books report */
    public function getOverdueReport(): array {
        return $this->loanRepo->findOverdue();
    }

    /** Fine report, optionally date-filtered */
    public function getFineReport(?string $from = null, ?string $to = null): array {
        if ($from && $to) {
            return $this->fineRepo->findByDateRange($from, $to);
        }
        return $this->fineRepo->findAll();
    }

    /** Activity log report, optionally date-filtered */
    public function getActivityReport(?string $from = null, ?string $to = null): array {
        if ($from && $to) {
            return $this->logRepo->findByDateRange($from, $to);
        }
        return $this->logRepo->findAll(500);
    }

    /** Summary stats for reports dashboard */
    public function getSummaryStats(): array {
        return [
            'total_loans'       => count($this->loanRepo->findAll(10000)),
            'active_loans'      => $this->loanRepo->countActive(),
            'overdue_loans'     => $this->loanRepo->countOverdue(),
            'total_fines'       => $this->fineRepo->countUnpaid() + $this->fineRepo->countPaid(),
            'unpaid_fines'      => $this->fineRepo->countUnpaid(),
            'fine_collected'    => $this->fineRepo->totalCollected(),
            'pending_reservations' => $this->resRepo->countPending(),
        ];
    }
}
