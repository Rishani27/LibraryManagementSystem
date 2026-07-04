<?php
// ============================================================
// ULMS — Student Dashboard
// presentation/student/dashboard.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';

Session::requireLogin('student');

$studentId   = Session::getUserId();
$loanRepo    = new LoanRepository();
$fineRepo    = new FineRepository();
$resRepo     = new ReservationRepository();

$myLoans     = $loanRepo->findByStudent($studentId);
$activeLoans = array_filter($myLoans, fn($l) => $l->status === 'active');
$myFines     = $fineRepo->findByStudent($studentId);
$unpaidFines = array_filter($myFines, fn($f) => $f->status === 'unpaid');
$myRes       = $resRepo->findByStudent($studentId);
$pendingRes  = array_filter($myRes, fn($r) => $r->status === 'pending');

// Overdue notifications
$overdueLoans = array_filter($activeLoans, fn($l) => $l->isOverdue());

// Compute exact balances
$totalFinesSum = 0;
foreach ($unpaidFines as $f) {
    $totalFinesSum += $f->amount;
}

$pageTitle  = 'My Dashboard';
$activePage = 'dashboard';

$_SESSION['role'] = 'student';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
$flash = Session::getFlash();
?>

<div class="space-y-6" id="student-dashboard-root">
  
  <!-- Welcome Hub Header Banner -->
  <div class="p-6 rounded-2xl bg-gradient-to-r from-blue-700 via-indigo-700 to-violet-850 text-white relative overflow-hidden shadow-lg shadow-indigo-900/10">
    <div class="absolute top-0 right-0 w-80 h-full bg-white/5 skew-x-12 translate-x-16"></div>
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-xs uppercase tracking-wider font-semibold text-blue-200 block mb-1 flex items-center gap-1">
          <i data-lucide="award" class="w-4 h-4 text-amber-400 shrink-0"></i> Central Registrar Clearances
        </span>
        <h1 class="text-2xl md:text-3xl font-display font-bold">
          Welcome back, <?= Helper::e(Session::getFullName()) ?>
        </h1>
        <p class="text-indigo-100 text-xs md:text-sm mt-1 max-w-lg leading-relaxed">
          Search millions of academic journals, reserve required reading lists, and check remaining days left on active catalog loans.
        </p>
      </div>
      <span class="px-3 py-1.5 bg-white/10 border border-white/20 text-xs font-mono rounded-xl shrink-0">
        ST-ID: <?= sprintf("USR-%03d", $studentId) ?>
      </span>
    </div>
  </div>

  <!-- Critical Overdue Warning Alerts Container -->
  <?php if (!empty($overdueLoans) || $flash['message']): ?>
    <div class="space-y-3">
      <?php if ($flash['message']): ?>
        <div class="p-4 rounded-xl border text-xs font-semibold flex items-center gap-2.5 bg-blue-50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900/30 text-blue-700 dark:text-blue-400 shadow-xs">
          <i data-lucide="info" class="w-5 h-5 text-blue-500 shrink-0"></i>
          <span><?= Helper::e($flash['message']) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($overdueLoans)): ?>
        <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-xl flex items-center gap-2.5 shadow-xs animate-pulse">
          <i data-lucide="alert-circle" class="w-5 h-5 text-rose-500 shrink-0"></i>
          <div>
            You have <strong><?= count($overdueLoans) ?></strong> overdue book(s). Please return them immediately to the library desk to prevent ongoing fee accruals.
          </div>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Student Matrix Stats Row Grid Layout -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Stat Item 1 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm">
      <div class="flex items-start justify-between">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Active Loans</span>
        <div class="p-1.5 rounded-lg text-blue-500 bg-blue-50 dark:bg-blue-950/20"><i data-lucide="book-marked" class="w-4 h-4"></i></div>
      </div>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-3"><?= count($activeLoans) ?></span>
    </div>

    <!-- Stat Item 2 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm">
      <div class="flex items-start justify-between">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Overdue Books</span>
        <div class="p-1.5 rounded-lg text-rose-500 bg-rose-50 dark:bg-rose-950/20"><i data-lucide="alert-circle" class="w-4 h-4"></i></div>
      </div>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-3"><?= count($overdueLoans) ?></span>
    </div>

    <!-- Stat Item 3 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm">
      <div class="flex items-start justify-between">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Active Reservations</span>
        <div class="p-1.5 rounded-lg text-indigo-500 bg-indigo-50 dark:bg-indigo-950/20"><i data-lucide="calendar-check" class="w-4 h-4"></i></div>
      </div>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-3"><?= count($pendingRes) ?></span>
    </div>

    <!-- Stat Item 4 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm">
      <div class="flex items-start justify-between">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Outstanding Fines</span>
        <div class="p-1.5 rounded-lg <?= $totalFinesSum > 0 ? 'text-rose-500 bg-rose-50 dark:bg-rose-950/20' : 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/20' ?>"><i data-lucide="dollar-sign" class="w-4 h-4"></i></div>
      </div>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-3 font-mono"><?= Helper::formatCurrency($totalFinesSum) ?></span>
    </div>
  </div>

  <!-- Allocation Panels & Active Columns Grid Setup -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column Panel: My Active Physical Loans Tracking Progress (2/3 Width) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-5 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm">
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
              
              // Progress metric scaling calculation bars
              if ($overdue > 0) {
                  $pct = 100;
                  $barColor = 'bg-rose-500';
                  $label = 'Overdue Fine Liability Accruing';
                  $textClass = 'text-rose-500 font-extrabold';
              } else {
                  $daysLeft = max(0, (strtotime($loan->due_date) - time()) / 86400);
                  $pct = ($daysLeft / 14) * 100;
                  $barColor = $daysLeft <= 3 ? 'bg-amber-500' : 'bg-emerald-500';
                  $label = $daysLeft <= 3 ? 'Return Window Closing' : 'Clear Allocation Term';
                  $textClass = $daysLeft <= 3 ? 'text-amber-500 font-bold' : 'text-emerald-500';
              }
            ?>
            <div class="p-3.5 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/30 dark:border-slate-850/40 rounded-xl flex flex-col sm:flex-row sm:items-center gap-4">
              <div class="w-10 h-14 rounded bg-gradient-to-br from-indigo-500 to-purple-800 text-white flex items-center justify-center p-1 text-[8px] font-bold shadow-xs shrink-0 font-display uppercase tracking-tight overflow-hidden">
                <?= htmlspecialchars(substr($loan->book_title, 0, 10)) ?>...
              </div>
              
              <div class="flex-1 min-w-0">
                <span class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate block">"<?= Helper::e($loan->book_title) ?>"</span>
                <div class="flex items-center gap-3 text-[10px] text-slate-400 mt-1">
                  <span>Issued: <?= Helper::e(Helper::formatDate($loan->issue_date)) ?></span>
                  <span>&bull;</span>
                  <span class="font-semibold text-slate-500">Due Schedule: <?= Helper::e(Helper::formatDate($loan->due_date)) ?></span>
                </div>

                <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mt-3">
                  <div class="h-full <?= $barColor ?> transition-all" style="width: <?= $pct ?>%"></div>
                </div>
              </div>

              <div class="shrink-0 text-left sm:text-right">
                <span class="text-xs block <?= $textClass ?> uppercase tracking-wide">
                  <?php if ($overdue > 0): ?>
                    <?= $overdue ?> Days Late
                  <?php else: ?>
                    Active Allocation
                  <?php endif; ?>
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5"><?= $label ?></span>
                <?php if ($fine > 0): ?>
                  <span class="text-xs font-mono font-bold text-rose-500 block mt-1"><?= Helper::formatCurrency($fine) ?> assessed</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right Column Panel: Task Links & Liability Disclosures (1/3 Width) -->
    <div class="bg-white dark:bg-slate-900 p-5 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm flex flex-col justify-between">
      <div>
        <h3 class="font-display font-bold text-sm text-slate-800 dark:text-white mb-1">Student Desk Operations</h3>
        <p class="text-[10px] text-slate-400 mb-4">Manage catalog asset routing links</p>
        
        <div class="space-y-2.5">
          <a href="catalog.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-indigo-50 dark:bg-slate-950 dark:hover:bg-indigo-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-indigo-600 no-underline transition shadow-xs">
            <span class="flex items-center gap-2"><i data-lucide="search" class="w-4 h-4 text-indigo-500"></i> Browse General Catalog</span>
            <i data-lucide="undo-2" class="w-4 h-4 text-slate-400 rotate-180"></i>
          </a>
          <a href="my_reservations.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-blue-50 dark:bg-slate-950 dark:hover:bg-blue-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-blue-600 no-underline transition shadow-xs">
            <span class="flex items-center gap-2"><i data-lucide="calendar-check" class="w-4 h-4 text-blue-500"></i> My Reserved Assets</span>
            <i data-lucide="undo-2" class="w-4 h-4 text-slate-400 rotate-180"></i>
          </a>
        </div>

        <!-- Fines Blocking Alert Flag Block Display -->
        <?php if (!empty($unpaidFines)): ?>
          <div class="mt-4 p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200/50 rounded-xl text-rose-700 space-y-2">
            <span class="text-xs font-bold flex items-center gap-1"><i data-lucide="alert-circle" class="w-4.5 h-4.5 text-rose-500"></i> Outstanding Fines Flags</span>
            <div class="max-h-28 overflow-y-auto divide-y divide-rose-100/50 text-[11px] font-medium opacity-90 pr-1">
              <?php foreach ($unpaidFines as $fine): ?>
                <div class="py-1.5 flex justify-between items-center gap-2">
                  <span class="truncate">"<?= Helper::e($fine->book_title) ?>"</span>
                  <span class="font-mono font-bold text-rose-600 shrink-0"><?= Helper::formatCurrency($fine->amount) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="text-[10px] text-rose-500/80 font-medium pt-1.5 border-t border-rose-200/30">Please visit the central university librarian desk to clear pending currency settlements.</p>
          </div>
        <?php endif; ?>
      </div>

      <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-850 text-center">
        <span class="text-[10px] text-slate-400 flex items-center justify-center gap-1 font-medium">
          <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500 animate-pulse"></i> Metropolis University Information Gateway Nodes Online
        </span>
      </div>
    </div>

  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>