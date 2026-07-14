<?php
// ============================================================
// ULMS — Return Books with Dynamic Ledger Filtering Search
// presentation/librarian/return.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Loan.php';
require_once ROOT_URL . '/app/data/models/Book.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/BookRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/ReturnService.php';
require_once ROOT_URL . '/app/business/services/BorrowService.php';

Session::requireLogin('librarian');

$returnService = new ReturnService();
$borrowService = new BorrowService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = (int)($_POST['loan_id'] ?? 0);
    $result = $returnService->returnBook($loanId);
    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: return.php');
    exit;
}

$activeLoans = $returnService->getActiveLoans();

$pageTitle  = 'Return Books';
$activePage = 'return';
$flash      = Session::getFlash();

$_SESSION['role'] = 'librarian';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <div>
    <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
      Process Student Book Returns
    </h1>
    <p class="text-xs text-slate-500 font-medium">Register library check-ins. Penalty values and system fines accrue automatically if circulation terms are exceeded.</p>
  </div>

  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'file-check' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
    
    <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white">Circulating Assets Awaiting Return Check-In</h2>
      <div class="relative flex items-center max-w-xs w-full">
        <div class="absolute left-3 text-slate-400 pointer-events-none">
          <i data-lucide="search" class="w-4 h-4"></i>
        </div>
        <input type="text" id="return_ledger_search" placeholder="Search book title or student..." class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-950 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-700 dark:text-slate-300 focus:border-blue-500">
      </div>
    </div>

    <?php if (empty($activeLoans)): ?>
      <div class="text-center text-slate-400 py-16 text-xs md:text-sm font-medium">
        <i data-lucide="check-circle" class="w-12 h-12 text-emerald-500 mx-auto mb-2 block"></i>
        Perfect! All checked-out materials have been fully registered back into inventory.
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="return_ledger_table">
          <thead>
            <tr class="border-b border-slate-100 dark:border-slate-850 text-slate-400 text-[10px] font-bold uppercase tracking-wider bg-slate-50/20 dark:bg-slate-950/10">
              <th class="py-3 px-5 text-center w-12">No</th>
              <th class="py-3 px-4">Book Title / Asset Info</th>
              <th class="py-3 px-4">Student Identification</th>
              <th class="py-3 px-4">Issue Date</th>
              <th class="py-3 px-4">Due Date</th>
              <th class="py-3 px-4">Term Remaining Status</th>
              <th class="py-3 px-4">Assessed Fine</th>
              <th class="py-3 px-5 text-right">Operational Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
            <?php foreach ($activeLoans as $i => $loan): ?>
              <?php
                $overdue = Helper::calculateOverdueDays($loan->due_date);
                $fine    = Helper::calculateFine($overdue);
              ?>
              <tr class="return-ledger-row hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <td class="py-3.5 px-5 text-center font-mono text-slate-400 font-semibold row-number"><?= $i + 1 ?></td>
                <td class="py-3.5 px-4 search-book-cell">
                  <strong class="text-slate-800 dark:text-slate-100 block leading-tight">"<?= Helper::e($loan->book_title) ?>"</strong>
                  <span class="text-slate-400 text-[11px] block mt-0.5">By <?= Helper::e($loan->book_author) ?></span>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300 search-student-cell">
                  <?= Helper::e($loan->student_name) ?>
                  <span class="text-slate-400 text-[11px] font-mono block mt-0.5">@<?= Helper::e($loan->student_username) ?></span>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-500 dark:text-slate-500 text-xs"><?= Helper::e(Helper::formatDate($loan->issue_date)) ?></td>
                <td class="py-3.5 px-4 font-mono text-slate-500 dark:text-slate-500 text-xs"><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
                <td class="py-3.5 px-4">
                  <?php if ($overdue > 0): ?>
                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 font-mono">
                      <?= $overdue ?> day<?= $overdue > 1 ? 's' : '' ?> overdue
                    </span>
                  <?php else: ?>
                    <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/10">
                      On Time
                    </span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-4 font-mono font-bold text-xs">
                  <?php if ($fine > 0): ?>
                    <span class="text-rose-500 font-extrabold">Rs. <?= number_format($fine, 2) ?></span>
                  <?php else: ?>
                    <span class="text-slate-400 font-medium">—</span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-5 text-right">
                  <form method="POST" action="return.php" onsubmit="return confirm('Register return check-in for \'<?= addslashes(Helper::e($loan->book_title)) ?>\'? <?= $fine > 0 ? 'Fine parameters apply.' : '' ?>');">
                    <input type="hidden" name="loan_id" value="<?= $loan->id ?>">
                    <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1 cursor-pointer ml-auto">
                      <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Check-In Return
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            
            <tr id="return_ledger_empty_row" class="hidden">
              <td colSpan="8" class="py-12 text-center text-slate-400 font-medium">
                <i data-lucide="search-code" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                No matching circulating records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('return_ledger_search');
    const rows = document.querySelectorAll('.return-ledger-row');
    const emptySearchRow = document.getElementById('return_ledger_empty_row');

    if (searchInput) {
        searchInput.addEventListener('input', filterReturnLedger);
        
        // Prevent layout shift/form capture anomalies on Enter key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterReturnLedger();
            }
        });
    }

    function filterReturnLedger() {
        const keyword = searchInput.value.trim().toLowerCase();
        let displayIndex = 1;
        let visibleCount = 0;

        rows.forEach(row => {
            const titleText = row.querySelector('.search-book-cell').textContent.toLowerCase();
            const studentText = row.querySelector('.search-student-cell').textContent.toLowerCase();

            if (keyword === '' || titleText.includes(keyword) || studentText.includes(keyword)) {
                row.classList.remove('hidden');
                
                // Recalculate numbering indices in real-time
                const indexCell = row.querySelector('.row-number');
                if (indexCell) indexCell.textContent = displayIndex++;
                visibleCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        if (emptySearchRow) {
            if (visibleCount === 0 && keyword !== '') {
                emptySearchRow.classList.remove('hidden');
            } else {
                emptySearchRow.classList.add('hidden');
            }
        }
    }
});
</script>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>