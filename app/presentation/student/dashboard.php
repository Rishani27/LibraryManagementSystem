<?php
// ============================================================
// ULMS — Student Dashboard (Polished UI & Key Mapping Layout)
// presentation/student/dashboard.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Loan.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';

Session::requireLogin('student');

$studentId   = Session::getUserId();
$username    = Session::getUsername(); 
$loanRepo    = new LoanRepository();
$fineRepo    = new FineRepository();
$resRepo     = new ReservationRepository();

$myLoans     = $loanRepo->findByStudent($studentId);
$activeLoans = array_filter($myLoans, fn($l) => $l->status === 'active');
$myFines     = $fineRepo->findByStudent($studentId);
$unpaidFines = array_filter($myFines, fn($f) => $f->status === 'unpaid');
$myRes       = $resRepo->findByStudent($studentId);
$pendingRes  = array_filter($myRes, fn($r) => $r->status === 'pending');

$overdueLoans = array_filter($activeLoans, fn($l) => $l->isOverdue());

$totalFinesSum = 0;
foreach ($unpaidFines as $f) {
    $totalFinesSum += $f->amount;
}

$pageTitle  = 'My Dashboard';
$activePage = 'dashboard';
$flash      = Session::getFlash();

$_SESSION['role'] = 'student';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6" id="student-dashboard-root">
  
  <div class="p-6 md:p-8 rounded-2xl bg-gradient-to-r from-blue-700 via-indigo-700 to-violet-850 text-white relative overflow-hidden shadow-xl shadow-indigo-950/10">
    <div class="absolute top-0 right-0 w-80 h-full bg-white/5 skew-x-12 translate-x-16"></div>
    <div class="relative z-10">
      <span class="text-xs uppercase tracking-wider font-semibold text-blue-200 block mb-1 flex items-center gap-1">
        <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i> Central Registrar Clearances
      </span>
      <h1 class="text-2xl md:text-3xl font-display font-bold tracking-tight">Welcome back, <?= Helper::e(Session::getFullName()) ?></h1>
      <p class="text-indigo-100 text-xs md:text-sm mt-1 max-w-lg leading-relaxed">Search millions of academic journals, reserve required reading lists, and check remaining days left on active catalog loans.</p>
    </div>
  </div>

  <?php if (!empty($overdueLoans) || $flash['message']): ?>
    <div class="space-y-3">
      <?php if ($flash['message']): ?>
        <div class="p-4 rounded-xl border text-xs font-semibold flex items-center gap-2.5 bg-blue-50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900/30 text-blue-700 dark:text-blue-400 shadow-sm">
          <i data-lucide="info" class="w-5 h-5 text-blue-500 shrink-0"></i>
          <span><?= Helper::e($flash['message']) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($overdueLoans)): ?>
        <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-xl flex items-center gap-2.5 shadow-sm animate-pulse">
          <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shrink-0"></i>
          <div>You have <strong><?= count($overdueLoans) ?></strong> overdue book(s). Please return them immediately to prevent penalty fees.</div>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-[0_4px_20px_rgba(15,23,42,0.05)] dark:shadow-none hover:shadow-md transition-shadow group cursor-pointer">
      <div class="flex items-start justify-between">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Active Loans</span>
        <div class="p-2 rounded-xl text-blue-500 bg-blue-50 dark:bg-blue-950/20 group-hover:scale-110 transition-transform"><i data-lucide="book-marked" class="w-4.5 h-4.5"></i></div>
      </div>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-3"><?= count($activeLoans) ?></span>
    </div>

    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-[0_4px_20px_rgba(15,23,42,0.05)] dark:shadow-none hover:shadow-md transition-shadow group cursor-pointer">
      <div class="flex items-start justify-between">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Overdue Books</span>
        <div class="p-2 rounded-xl text-rose-500 bg-rose-50 dark:bg-rose-950/20 group-hover:scale-110 transition-transform"><i data-lucide="alert-circle" class="w-4.5 h-4.5"></i></div>
      </div>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-3"><?= count($overdueLoans) ?></span>
    </div>

    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-[0_4px_20px_rgba(15,23,42,0.05)] dark:shadow-none hover:shadow-md transition-shadow group cursor-pointer">
      <div class="flex items-start justify-between">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Active Reservations</span>
        <div class="p-2 rounded-xl text-indigo-500 bg-indigo-50 dark:bg-indigo-950/20 group-hover:scale-110 transition-transform"><i data-lucide="calendar-check" class="w-4.5 h-4.5"></i></div>
      </div>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-3"><?= count($pendingRes) ?></span>
    </div>

    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-[0_4px_20px_rgba(15,23,42,0.05)] dark:shadow-none hover:shadow-md transition-shadow group cursor-pointer">
      <div class="flex items-start justify-between">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Outstanding Fines</span>
        <div class="p-2 rounded-xl <?= $totalFinesSum > 0 ? 'text-rose-500 bg-rose-50 dark:bg-rose-950/20' : 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/20' ?> group-hover:scale-110 transition-transform"><i data-lucide="dollar-sign" class="w-4.5 h-4.5"></i></div>
      </div>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-3 font-mono text-[1.2rem]"><?= Helper::formatCurrency($totalFinesSum) ?></span>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-5 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl shadow-[0_4px_25px_rgba(15,23,42,0.06)]">
      <div class="flex items-center justify-between mb-4 border-b border-slate-50 dark:border-slate-850 pb-2">
        <div>
          <h3 class="font-display font-bold text-sm text-slate-800 dark:text-white">My Active Physical Loans</h3>
          <p class="text-[10px] text-slate-400">Check remaining timeline metrics and return checking horizons</p>
        </div>
        <a href="catalog.php" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs no-underline flex items-center gap-1.5 transition-all">
          <i data-lucide="search" class="w-3.5 h-3.5"></i> Browse Catalog
        </a>
      </div>
      
      <div class="space-y-4">
        <?php if (empty($activeLoans)): ?>
          <div class="py-12 text-center text-slate-400 text-xs">
            <i data-lucide="book-open" class="w-10 h-10 text-slate-300 mx-auto mb-2 block"></i>
            You currently hold zero active materials on check-out allocation.
          </div>
        <?php else: ?>
          <?php foreach ($activeLoans as $loan): ?>
            <?php
              $overdue = Helper::calculateOverdueDays($loan->due_date);
              $fine    = Helper::calculateFine($overdue);
              
              if ($overdue > 0) {
                  $pct = 100; $barColor = 'bg-rose-500'; $label = 'Overdue Fine Liability Accruing'; $textClass = 'text-rose-500 font-extrabold';
              } else {
                  $daysLeft = max(0, (strtotime($loan->due_date) - time()) / 86400);
                  $pct = ($daysLeft / 14) * 100;
                  $barColor = $daysLeft <= 3 ? 'bg-amber-500' : 'bg-emerald-500';
                  $label = $daysLeft <= 3 ? 'Return Window Closing' : 'Clear Allocation Term';
                  $textClass = $daysLeft <= 3 ? 'text-amber-500 font-bold' : 'text-emerald-500';
              }
            ?>
            <div class="p-3.5 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/30 dark:border-slate-850/40 rounded-xl flex flex-col sm:flex-row sm:items-center gap-4">
              <div class="w-10 h-14 rounded bg-gradient-to-br from-indigo-500 to-purple-800 text-white flex items-center justify-center p-1 text-[8px] font-bold shadow-xs shrink-0 font-display uppercase truncate"><?= htmlspecialchars(substr($loan->book_title, 0, 8)) ?></div>
              <div class="flex-1 min-w-0">
                <span class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate block">"<?= Helper::e($loan->book_title) ?>"</span>
                <div class="flex items-center gap-3 text-[10px] text-slate-400 mt-1">
                  <span>Issued: <?= Helper::e(Helper::formatDate($loan->issue_date)) ?></span>
                  <span class="font-semibold text-slate-500">Due Schedule: <?= Helper::e(Helper::formatDate($loan->due_date)) ?></span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mt-3"><div class="h-full <?= $barColor ?> transition-all" style="width: <?= $pct ?>%"></div></div>
              </div>
              <div class="shrink-0 text-left sm:text-right">
                <span class="text-xs block <?= $textClass ?> uppercase tracking-wide"><?= $overdue > 0 ? "$overdue Days Late" : 'Active Allocation' ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5"><?= $label ?></span>
                <?php if ($fine > 0): ?><span class="text-xs font-mono font-bold text-rose-500 block mt-1">Rs. <?= number_format($fine, 2) ?> assessed</span><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-5 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl shadow-[0_4px_25px_rgba(15,23,42,0.06)] flex flex-col justify-between">
      <div>
        <h3 class="font-display font-bold text-sm text-slate-800 dark:text-white mb-1">Student Desk Operations</h3>
        <p class="text-[10px] text-slate-400 mb-4">Manage catalog asset routing links</p>
        <div class="space-y-2.5">
          <a href="catalog.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-indigo-50 dark:bg-slate-950 dark:hover:bg-indigo-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-indigo-600 no-underline transition shadow-xs">
            <span class="flex items-center gap-2"><i data-lucide="search" class="w-4 h-4 text-indigo-500"></i> Browse General Catalog</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="my_reservations.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-blue-50 dark:bg-slate-950 dark:hover:bg-blue-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-blue-600 no-underline transition shadow-xs">
            <span class="flex items-center gap-2"><i data-lucide="calendar-check" class="w-4 h-4 text-blue-500"></i> My Reserved Assets</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
        </div>

        <?php if (!empty($unpaidFines)): ?>
          <div class="mt-4 p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200/50 rounded-xl text-rose-700 space-y-2">
            <span class="text-xs font-bold flex items-center gap-1"><i data-lucide="alert-circle" class="w-4.5 h-4.5 text-rose-500"></i> Outstanding Fines Flags</span>
            <div class="max-h-28 overflow-y-auto divide-y divide-rose-100/50 text-[11px] font-medium opacity-90 pr-1">
              <?php foreach ($unpaidFines as $fine): ?>
                <div class="py-1.5 flex justify-between items-center gap-2">
                  <span class="truncate">"<?= Helper::e($fine->book_title) ?>"</span>
                  <span class="font-mono font-bold text-rose-600 shrink-0">Rs. <?= number_format($fine->amount, 2) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>