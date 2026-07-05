<?php
// ============================================================
// ULMS — Issue Books (Borrowing)  (FR-06 — Member 3)
// presentation/librarian/borrow.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Loan.php';
require_once ROOT_URL . '/app/data/models/Book.php';
require_once ROOT_URL . '/app/data/models/User.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/BookRepository.php';
require_once ROOT_URL . '/app/data/repositories/UserRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/validators/BorrowValidator.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/BorrowService.php';

Session::requireLogin('librarian');

$borrowService = new BorrowService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $borrowService->issueBook(
        ['book_id' => $_POST['book_id'] ?? 0, 'student_id' => $_POST['student_id'] ?? 0],
        Session::getUserId()
    );
    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: borrow.php');
    exit;
}

$activeLoans = $borrowService->getActiveLoans();
$students    = $borrowService->getAllStudents();
$bookRepo    = new BookRepository();
$availBooks  = array_filter($bookRepo->findAll(500), fn($b) => $b->available_copies > 0);

$pageTitle  = 'Issue Books';
$activePage = 'borrow';
$flash      = Session::getFlash();

$_SESSION['role'] = 'librarian';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <div>
    <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
      Issue Physical Catalog Loans
    </h1>
    <p class="text-xs text-slate-500 font-medium">Link student identities with catalog resources. Due schedules are assessed automatically (14 days circulation limit).</p>
  </div>

  <!-- Validation Flash Alert Messages Section Wrapper -->
  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'file-check' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    
    <!-- Left column: Issue Form Block (1/3 Width Layout) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm space-y-4">
      <div class="border-b border-slate-50 dark:border-slate-850 pb-2">
        <h2 class="font-display font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
          <i data-lucide="bookmark-plus" class="w-4.5 h-4.5 text-emerald-500"></i> New Catalog Loan
        </h2>
      </div>

      <form method="POST" action="borrow.php" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Select Catalog Book *</label>
          <select name="book_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-700 dark:text-slate-300 cursor-pointer">
            <option value="">-- Choose available asset --</option>
            <?php foreach ($availBooks as $book): ?>
              <option value="<?= $book->id ?>">
                <?= Helper::e($book->title) ?> (<?= Helper::e($book->author) ?>) — [<?= $book->available_copies ?> available]
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Select Target Student *</label>
          <select name="student_id" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-700 dark:text-slate-300 cursor-pointer">
            <option value="">-- Choose student clear record --</option>
            <?php foreach ($students as $s): ?>
              <option value="<?= $s->id ?>"><?= Helper::e($s->full_name) ?> (@<?= Helper::e($s->username) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3 font-mono text-xs">
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Issue Date</label>
            <input type="text" value="<?= date('d M Y') ?>" disabled class="w-full px-2 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-lg border border-transparent text-center" />
          </div>
          <div>
            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wide mb-1">Due Date</label>
            <input type="text" value="<?= date('d M Y', strtotime('+14 days')) ?>" disabled class="w-full px-2 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-lg border border-transparent text-center" />
          </div>
        </div>

        <div class="p-3 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 rounded-xl text-[11px] text-blue-700 dark:text-blue-300 leading-normal">
          Loans allocate stock copies for 14 continuous days. Late turn-ins assess a <strong class="font-mono">$1.00 per-day penalty fee</strong> automatically.
        </div>

        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md cursor-pointer transition-all flex items-center justify-center gap-1.5">
          <i data-lucide="check-circle" class="w-4 h-4"></i> Approve & Issue Loan
        </button>
      </form>
    </div>

    <!-- Right column: Active Loans Datatable Array List (2/3 Width Layout) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
      <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80 flex items-center justify-between">
        <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white">Active Borrowing Allocations Ledger</h2>
        <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-500 px-2.5 py-0.5 rounded-full font-semibold"><?= count($activeLoans) ?> Active</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-slate-850 text-slate-400 text-[10px] font-bold uppercase tracking-wider bg-slate-50/20 dark:bg-slate-950/10">
              <th class="py-3 px-4 text-center w-12">No</th>
              <th class="py-3 px-4">Book Asset Title</th>
              <th class="py-3 px-4">Student Name</th>
              <th class="py-3 px-4">Issue Date</th>
              <th class="py-3 px-4">Due Date</th>
              <th class="py-3 px-4 text-right">Circulation Clearance</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
            <?php if (empty($activeLoans)): ?>
              <tr>
                <td colSpan="6" class="py-12 text-center text-slate-400 font-medium">
                  <i data-lucide="book-open" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                  No active resource loans currently out on external circulation.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($activeLoans as $i => $loan): ?>
                <?php $isOverdue = $loan->isOverdue(); ?>
                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                  <td class="py-3 px-4 text-center font-mono text-slate-400 font-semibold"><?= $i + 1 ?></td>
                  <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">"<?= Helper::e($loan->book_title) ?>"</td>
                  <td class="py-3 px-4 text-slate-600 dark:text-slate-400 font-medium"><?= Helper::e($loan->student_name) ?></td>
                  <td class="py-3 px-4 font-mono text-slate-400 text-xs"><?= Helper::e(Helper::formatDate($loan->issue_date)) ?></td>
                  <td class="py-3 px-4 font-mono text-slate-400 text-xs"><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
                  <td class="py-3 px-4 text-right">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border <?= $isOverdue ? 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-500/10' : 'bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 border-indigo-500/10' ?>">
                      <span class="w-1.5 h-1.5 rounded-full <?= $isOverdue ? 'bg-rose-500 animate-pulse' : 'bg-indigo-500' ?>"></span>
                      <?= $isOverdue ? 'Overdue Allocation' : 'Clear Allocation' ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>