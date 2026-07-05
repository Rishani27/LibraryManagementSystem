<?php
// ============================================================
// ULMS — Librarian Dashboard
// presentation/librarian/dashboard.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';
require_once ROOT_URL . '/app/business/services/BorrowService.php';
require_once ROOT_URL . '/app/business/services/FineService.php';
require_once ROOT_URL . '/app/business/services/ReservationService.php';

Session::requireLogin('librarian');

$borrowSvc = new BorrowService();
$fineSvc   = new FineService();
$resSvc    = new ReservationService();

$loanStats = $borrowSvc->getDashboardStats();
$fineStats = $fineSvc->getDashboardStats();
$pendingRes= $resSvc->countPending();
$overdueList = $borrowSvc->getOverdueLoans();

$pageTitle  = 'Librarian Dashboard';
$activePage = 'dashboard';

// Setup core session layout hooks for header.php
$_SESSION['role'] = 'librarian';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
$flash = Session::getFlash();
?>

<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6" id="librarian-dashboard-root">
  
  <!-- System Validation Notifications Alert Banner -->
  <?php if ($flash['message']): ?>
    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 rounded-2xl text-xs md:text-sm text-emerald-700 dark:text-emerald-400 font-medium flex items-center gap-2.5 shadow-sm">
      <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <!-- Welcome Banner -->
  <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-700 via-teal-800 to-slate-900 text-white relative overflow-hidden shadow-lg shadow-teal-900/10">
    <div class="absolute top-0 right-0 w-80 h-full bg-white/5 skew-x-12 translate-x-16"></div>
    <div class="relative z-10">
      <span class="text-xs uppercase tracking-wider font-semibold text-emerald-200 block mb-1">
        Librarian Control Desk
      </span>
      <h1 class="text-2xl md:text-3xl font-display font-bold">
        Welcome back, <?= Helper::e(Session::getFullName()) ?>
      </h1>
      <p class="text-emerald-100 text-xs md:text-sm mt-1 max-w-lg leading-relaxed">
        Approve student reservation requests, process physical book returns, record outstanding library fines, and log manual collections from here.
      </p>
    </div>
  </div>

  <!-- Librarian Stat Cards Matrix Grid Layout -->
  <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
    <!-- Stat Item 1 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-emerald-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Active Loans</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $loanStats['active_loans'] ?></span>
    </div>

    <!-- Stat Item 2 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-indigo-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Overdue Books</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $loanStats['overdue_count'] ?></span>
    </div>

    <!-- Stat Item 3 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-amber-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Pending Reservations</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $pendingRes ?></span>
    </div>

    <!-- Stat Item 4 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-rose-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Unpaid Fines</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $fineStats['unpaid_count'] ?></span>
    </div>

    <!-- Stat Item 5 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-blue-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Fines Settled</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $fineStats['paid_count'] ?></span>
    </div>

    <!-- Stat Item 6 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-purple-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Total Collected</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5 font-mono text-[1.3rem]"><?= Helper::formatCurrency($fineStats['total_collected']) ?></span>
    </div>
  </div>

  <!-- Main Management Rows Grid Setup -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Panel: Task Shortcuts (1/3 Width) -->
    <div class="bg-white dark:bg-slate-900 p-5 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm flex flex-col justify-between">
      <div>
        <h3 class="font-display font-bold text-sm text-slate-800 dark:text-white mb-1">Task Shortcuts</h3>
        <p class="text-[10px] text-slate-400 mb-4">Core librarian services shortcuts</p>
        
        <div class="space-y-2.5">
          <a href="borrow.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-emerald-50 dark:bg-slate-950 dark:hover:bg-emerald-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-emerald-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2">
              <i data-lucide="bookmark-plus" class="w-4 h-4 text-emerald-500"></i> Issue Physical Book
            </span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="return.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-blue-50 dark:bg-slate-950 dark:hover:bg-blue-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-blue-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2">
              <i data-lucide="refresh-cw" class="w-4 h-4 text-blue-500"></i> Process Student Return
            </span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="reservations.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-amber-50 dark:bg-slate-950 dark:hover:bg-amber-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-amber-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2">
              <i data-lucide="calendar-check" class="w-4 h-4 text-amber-500"></i> Manage Reservations
            </span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="fines.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-rose-50 dark:bg-slate-950 dark:hover:bg-rose-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-rose-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2">
              <i data-lucide="dollar-sign" class="w-4 h-4 text-rose-500"></i> Settle Library Fines
            </span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="books.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-purple-50 dark:bg-slate-950 dark:hover:bg-purple-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-purple-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2">
              <i data-lucide="book-open" class="w-4 h-4 text-purple-500"></i> Open Book Catalog
            </span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="reports.php" class="w-full flex items-center justify-between p-3 bg-slate-50 hover:bg-teal-50 dark:bg-slate-950 dark:hover:bg-teal-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-teal-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2">
              <i data-lucide="bar-chart-3" class="w-4 h-4 text-teal-500"></i> Analytical Reports
            </span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
        </div>
      </div>

      <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-850 text-center text-[10px] text-slate-400 font-medium">
        LMS Core Agent Node Status: Active Clearance
      </div>
    </div>

    <!-- Right Panel: Overdue Active Alerts System Table (2/3 Width) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm flex flex-col justify-between overflow-hidden">
      <div>
        <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <i data-lucide="shield-alert" class="w-4.5 h-4.5 text-rose-500 animate-pulse"></i>
            <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white">Active Overdue Loans</h2>
          </div>
          <a href="return.php" class="text-xs text-rose-500 font-bold hover:underline no-underline flex items-center gap-1">
            Manage Check-Ins &rarr;
          </a>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-850 text-slate-400 text-[10px] font-bold uppercase tracking-wider bg-slate-50/20 dark:bg-slate-950/10">
                <th class="py-3 px-4">Book Asset Title</th>
                <th class="py-3 px-4">Enrolled Student</th>
                <th class="py-3 px-4">Due Date</th>
                <th class="py-3 px-4 text-right">Violation Delays</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
              <?php if (empty($overdueList)): ?>
                <tr>
                  <td colspan="4" class="py-12 text-center text-slate-400 font-medium">
                    <i data-lucide="check" class="w-10 h-10 text-emerald-500 mx-auto mb-2 block"></i>
                    Excellent! No outstanding circulation records are overdue.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach (array_slice($overdueList, 0, 8) as $loan): ?>
                  <?php $days = Helper::calculateOverdueDays($loan->due_date); ?>
                  <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                    <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">
                      "<?= Helper::e($loan->book_title) ?>"
                    </td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400 font-medium">
                      <?= Helper::e($loan->student_name) ?>
                    </td>
                    <td class="py-3 px-4 font-mono text-slate-500 dark:text-slate-500 text-xs">
                      <?= Helper::e(Helper::formatDate($loan->due_date)) ?>
                    </td>
                    <td class="py-3 px-4 text-right">
                      <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                        <?= $days ?> day<?= $days > 1 ? 's' : '' ?> late
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="p-3.5 bg-slate-50/50 dark:bg-slate-950/10 border-t border-slate-100 dark:border-slate-850 text-right text-[10px] text-slate-400 font-medium">
        Circulation Limit Term: 14 Business Days Allocation Rule
      </div>
    </div>

  </div>
</div>

<?php 
include ROOT_URL . '/app/includes/footer.php'; 
?>