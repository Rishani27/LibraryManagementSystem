<?php
// ============================================================
// ULMS — Librarian Dashboard (Fixed Mismatched Array Keys)
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

// Run background expirations loop on load
$resSvc->processExpirations();

$loanStats = $borrowSvc->getDashboardStats();
$fineStats = $fineSvc->getDashboardStats();
$pendingRes= $resSvc->countPending();
$overdueList = $borrowSvc->getOverdueLoans();

$pageTitle  = 'Librarian Dashboard';
$activePage = 'dashboard';

$_SESSION['role'] = 'librarian';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
$flash = Session::getFlash();
?>

<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6" id="librarian-dashboard-root">
  
  <?php if ($flash['message']): ?>
    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 rounded-2xl text-xs md:text-sm text-emerald-700 dark:text-emerald-400 font-medium flex items-center gap-2.5 shadow-sm">
      <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <div class="p-6 md:p-8 rounded-2xl bg-gradient-to-r from-emerald-700 via-teal-800 to-slate-900 text-white relative overflow-hidden shadow-xl shadow-teal-900/20">
    <div class="absolute top-0 right-0 w-80 h-full bg-white/5 skew-x-12 translate-x-16"></div>
    <div class="relative z-10">
      <span class="text-xs uppercase tracking-wider font-semibold text-emerald-200 block mb-1">Librarian Control Desk</span>
      <h1 class="text-2xl md:text-3xl font-display font-bold tracking-tight">Welcome back, <?= Helper::e(Session::getFullName()) ?></h1>
      <p class="text-emerald-100 text-xs md:text-sm mt-1 max-w-lg leading-relaxed">Approve student reservation requests, process physical book returns, record outstanding library fines, and log manual collections from here.</p>
    </div>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-[0_4px_20px_rgba(15,23,42,0.05)] dark:shadow-none hover:shadow-lg transition-all duration-300 group cursor-pointer">
      <div class="flex items-start justify-between mb-2">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Active Loans Out</span>
        <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/20 text-blue-500 group-hover:scale-110 transition-transform"><i data-lucide="book-open" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block leading-none">
          <?= isset($loanStats['active_loans']) ? $loanStats['active_loans'] : ($loanStats['active'] ?? 0) ?>
        </span>
        <span class="text-[10px] text-slate-400 font-medium mt-1.5 block">Materials out in circulation</span>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-[0_4px_20px_rgba(15,23,42,0.05)] dark:shadow-none hover:shadow-lg transition-all duration-300 group cursor-pointer">
      <div class="flex items-start justify-between mb-2">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Overdue Deadlines</span>
        <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/20 text-rose-500 group-hover:scale-110 transition-transform"><i data-lucide="alert-circle" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-rose-600 block leading-none">
          <?= isset($loanStats['overdue_count']) ? $loanStats['overdue_count'] : ($loanStats['overdue'] ?? 0) ?>
        </span>
        <span class="text-[10px] text-rose-400 font-medium mt-1.5 block">Return schedules exceeded</span>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-[0_4px_20px_rgba(15,23,42,0.05)] dark:shadow-none hover:shadow-lg transition-all duration-300 group cursor-pointer">
      <div class="flex items-start justify-between mb-2">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Pending Pre-Orders</span>
        <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-amber-500 group-hover:scale-110 transition-transform"><i data-lucide="layers" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block leading-none"><?= $pendingRes ?></span>
        <span class="text-[10px] text-amber-500 font-medium mt-1.5 block">Holds awaiting action</span>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-[0_4px_20px_rgba(15,23,42,0.05)] dark:shadow-none hover:shadow-lg transition-all duration-300 group cursor-pointer">
      <div class="flex items-start justify-between mb-2">
        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Unpaid Fine Balances</span>
        <div class="p-2 rounded-xl bg-purple-50 dark:bg-purple-950/20 text-purple-500 group-hover:scale-110 transition-transform"><i data-lucide="banknote" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-lg md:text-xl font-display font-bold text-slate-800 dark:text-white block leading-none font-mono text-[1.2rem]">
          <?= isset($fineStats['unpaid_total']) ? Helper::formatCurrency($fineStats['unpaid_total']) : (isset($fineStats['total_collected']) ? Helper::formatCurrency($fineStats['total_collected']) : 'Rs. 0.00') ?>
        </span>
        <span class="text-[10px] text-slate-400 font-medium mt-1.5 block">Accumulated penalties ledger</span>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white dark:bg-slate-900 p-5 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl shadow-[0_4px_25px_rgba(15,23,42,0.06)] flex flex-col justify-between">
      <div>
        <h3 class="font-display font-bold text-sm text-slate-800 dark:text-white mb-1">Task Shortcuts</h3>
        <p class="text-[10px] text-slate-400 mb-4">Core librarian services links</p>
        <div class="space-y-2.5">
          <a href="borrow.php" class="w-full flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-950/50 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-emerald-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2"><i data-lucide="bookmark-plus" class="w-4 h-4 text-emerald-500"></i> Issue Physical Book</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="return.php" class="w-full flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-950/50 hover:bg-blue-50 dark:hover:bg-blue-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-blue-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2"><i data-lucide="refresh-cw" class="w-4 h-4 text-blue-500"></i> Process Student Return</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="reservations.php" class="w-full flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-950/50 hover:bg-amber-50 dark:hover:bg-amber-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-amber-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2"><i data-lucide="calendar-check" class="w-4 h-4 text-amber-500"></i> Manage Reservations</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
          <a href="fines.php" class="w-full flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-950/50 hover:bg-rose-50 dark:hover:bg-rose-950/20 border border-slate-200/40 dark:border-slate-850/40 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-rose-600 transition shadow-xs no-underline">
            <span class="flex items-center gap-2"><i data-lucide="dollar-sign" class="w-4 h-4 text-rose-500"></i> Settle Library Fines</span>
            <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400"></i>
          </a>
        </div>
      </div>
    </div>

    <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl shadow-[0_4px_25px_rgba(15,23,42,0.06)] flex flex-col justify-between overflow-hidden">
      <div>
        <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80 flex items-center justify-between">
          <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white flex items-center gap-2"><i data-lucide="shield-alert" class="w-4 h-4 text-rose-500"></i> Active Overdue Loans</h2>
          <a href="return.php" class="text-xs text-rose-500 font-bold hover:underline no-underline">Manage Check-Ins &rarr;</a>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-850 text-slate-400 text-[10px] font-bold uppercase tracking-wider bg-slate-50/20">
                <th class="py-3 px-4">Book Asset Title</th>
                <th class="py-3 px-4">Enrolled Student</th>
                <th class="py-3 px-4 text-right">Violation Delays</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs">
              <?php if (empty($overdueList)): ?>
                <tr><td colspan="3" class="py-12 text-center text-slate-400 font-medium">No outstanding circulation records are overdue.</td></tr>
              <?php else: ?>
                <?php foreach (array_slice($overdueList, 0, 5) as $loan): ?>
                  <?php $days = Helper::calculateOverdueDays($loan->due_date); ?>
                  <tr class="hover:bg-slate-50/40 transition-colors">
                    <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-200">"<?= Helper::e($loan->book_title) ?>"</td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400"><?= Helper::e($loan->student_name) ?></td>
                    <td class="py-3 px-4 text-right"><span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-600 border border-rose-100"><?= $days ?> days late</span></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>