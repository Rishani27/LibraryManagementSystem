<?php
// ============================================================
// ULMS — Admin Dashboard
// presentation/admin/dashboard.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/repositories/UserRepository.php';
require_once ROOT_URL . '/app/data/repositories/BookRepository.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/UserService.php';
require_once ROOT_URL . '/app/business/services/BookService.php';

Session::requireLogin('admin');

$userService = new UserService();
$bookService = new BookService();
$loanRepo    = new LoanRepository();
$fineRepo    = new FineRepository();
$logRepo     = new LogRepository();

$userStats   = $userService->getUserStats();
$bookStats   = $bookService->getBookStats();
$activeLoans = $loanRepo->countActive();
$overdueLoans= $loanRepo->countOverdue();
$unpaidFines = $fineRepo->countUnpaid();
$recentLogs  = $logRepo->findAll(10);

$pageTitle  = 'Admin Dashboard';
$activePage = 'dashboard';

// Pass current role states explicitly into session scope layout variables
$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
$flash = Session::getFlash();
?>

<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6" id="admin-dashboard-root">
  
  <!-- System Validation Notifications Alert Banner -->
  <?php if ($flash['message']): ?>
    <div class="p-4 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/30 rounded-2xl text-xs md:text-sm text-blue-700 dark:text-blue-400 font-medium flex items-center gap-2.5 shadow-sm">
      <i data-lucide="check-circle" class="w-5 h-5 text-blue-500 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <!-- Welcome Hub Header Banner -->
  <div class="p-6 md:p-8 rounded-2xl bg-gradient-to-r from-blue-700 via-indigo-800 to-indigo-950 text-white relative overflow-hidden shadow-lg shadow-indigo-900/10">
    <div class="absolute top-0 right-0 w-80 h-full bg-white/5 skew-x-12 translate-x-16"></div>
    <div class="absolute -bottom-12 -right-12 w-48 h-48 bg-blue-500/10 rounded-full blur-2xl"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <span class="text-xs uppercase tracking-wider font-semibold text-blue-200 block mb-1">
          Metropolis LMS Management
        </span>
        <h1 class="text-2xl md:text-3xl font-display font-bold tracking-tight">
          Welcome Back, <?= Helper::e(Session::getFullName()) ?>
        </h1>
        <p class="text-indigo-100 text-xs md:text-sm mt-1 max-w-lg leading-relaxed">
          You are logged into the central control deck. Monitor usage matrices, manage student registrar clearances, and audit security transaction logs.
        </p>
      </div>
      <div class="flex gap-2 shrink-0">
        <a href="users.php" class="px-4 py-2 bg-white text-indigo-900 font-semibold text-xs md:text-sm rounded-xl shadow-md hover:bg-slate-50 transition flex items-center gap-1.5 no-underline">
          <i data-lucide="plus" class="w-4 h-4"></i> Add Academic User
        </a>
      </div>
    </div>
  </div>

  <!-- Responsive Statistics Cards Matrix Grid Layout -->
  <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
    <!-- Stat Item 1 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm relative overflow-hidden hover-premium cursor-pointer">
      <div class="flex items-start justify-between mb-3">
        <span class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase tracking-wider block leading-tight max-w-[80%]">Total Registry Users</span>
        <div class="p-1.5 rounded-lg text-blue-500 bg-blue-50 dark:bg-blue-950/20"><i data-lucide="users" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block"><?= $userStats['total'] ?></span>
        <span class="text-[10px] font-medium mt-1 flex items-center gap-1 text-emerald-500"><i data-lucide="trending-up" class="w-3 h-3"></i> Active Database</span>
      </div>
    </div>

    <!-- Stat Item 2 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm relative overflow-hidden hover-premium cursor-pointer">
      <div class="flex items-start justify-between mb-3">
        <span class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase tracking-wider block leading-tight max-w-[80%]">Total Books Cataloged</span>
        <div class="p-1.5 rounded-lg text-indigo-500 bg-indigo-50 dark:bg-indigo-950/20"><i data-lucide="book-open" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block"><?= $bookStats['total'] ?></span>
        <span class="text-[10px] font-medium mt-1 flex items-center gap-1 text-emerald-500"><i data-lucide="trending-up" class="w-3 h-3"></i> System Verified</span>
      </div>
    </div>

    <!-- Stat Item 3 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm relative overflow-hidden hover-premium cursor-pointer">
      <div class="flex items-start justify-between mb-3">
        <span class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase tracking-wider block leading-tight max-w-[80%]">Active Catalog Loans</span>
        <div class="p-1.5 rounded-lg text-emerald-500 bg-emerald-50 dark:bg-emerald-950/20"><i data-lucide="file-text" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block"><?= $activeLoans ?></span>
        <span class="text-[10px] font-medium mt-1 flex items-center gap-1 text-emerald-500"><i data-lucide="trending-up" class="w-3 h-3"></i> Out on Circulation</span>
      </div>
    </div>

    <!-- Stat Item 4 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm relative overflow-hidden hover-premium cursor-pointer">
      <div class="flex items-start justify-between mb-3">
        <span class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase tracking-wider block leading-tight max-w-[80%]">Total Students Registered</span>
        <div class="p-1.5 rounded-lg text-purple-500 bg-purple-50 dark:bg-purple-950/20"><i data-lucide="users" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block"><?= $userStats['students'] ?></span>
        <span class="text-[10px] font-medium mt-1 flex items-center gap-1 text-emerald-500"><i data-lucide="trending-up" class="w-3 h-3"></i> Active Access Slots</span>
      </div>
    </div>

    <!-- Stat Item 5 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm relative overflow-hidden hover-premium cursor-pointer">
      <div class="flex items-start justify-between mb-3">
        <span class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase tracking-wider block leading-tight max-w-[80%]">Outstanding Student Fees</span>
        <div class="p-1.5 rounded-lg text-amber-500 bg-amber-50 dark:bg-amber-950/20"><i data-lucide="alert-circle" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block"><?= $unpaidFines ?></span>
        <span class="text-[10px] font-medium mt-1 flex items-center gap-1 text-amber-500"><i data-lucide="trending-down" class="w-3 h-3"></i> Pending Fines Count</span>
      </div>
    </div>

    <!-- Stat Item 6 -->
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl flex flex-col justify-between shadow-sm relative overflow-hidden hover-premium cursor-pointer">
      <div class="flex items-start justify-between mb-3">
        <span class="text-slate-400 dark:text-slate-500 font-semibold text-[10px] uppercase tracking-wider block leading-tight max-w-[80%]">Critically Overdue Books</span>
        <div class="p-1.5 rounded-lg text-rose-500 bg-rose-50 dark:bg-rose-950/20"><i data-lucide="shield-alert" class="w-4.5 h-4.5"></i></div>
      </div>
      <div>
        <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block"><?= $overdueLoans ?></span>
        <span class="text-[10px] font-medium mt-1 flex items-center gap-1 text-rose-500"><i data-lucide="trending-down" class="w-3 h-3"></i> Action Required</span>
      </div>
    </div>
  </div>

  <!-- Operational Audit Trace & Side Controls Grid Row Content Layout -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Operations Panel: Quick System Operations + Role Breakdown (1/3 Width) -->
    <div class="bg-white dark:bg-slate-900 p-5 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm flex flex-col justify-between space-y-5">
      <div>
        <h3 class="font-display font-bold text-sm text-slate-800 dark:text-white mb-1">Central Pinned Operations</h3>
        <p class="text-[10px] text-slate-400 mb-4">Quick shortcuts to critical administration links</p>
        
        <div class="flex flex-col gap-2.5">
          <a href="users.php" class="p-3 bg-slate-50 dark:bg-slate-950 hover:bg-blue-50 dark:hover:bg-blue-950/20 border border-slate-200/50 dark:border-slate-850/50 hover:border-blue-200 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl text-xs font-semibold transition no-underline flex items-center justify-center gap-2 shadow-xs">
            <i data-lucide="users" class="w-4 h-4"></i> Central Users Registry
          </a>
          <a href="logs.php" class="p-3 bg-slate-50 dark:bg-slate-950 hover:bg-indigo-50 dark:hover:bg-indigo-950/20 border border-slate-200/50 dark:border-slate-850/50 hover:border-indigo-200 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-xl text-xs font-semibold transition no-underline flex items-center justify-center gap-2 shadow-xs">
            <i data-lucide="history" class="w-4 h-4"></i> View Security Audit Logs
          </a>
        </div>
      </div>

      <div class="pt-4 border-t border-slate-100 dark:border-slate-850/80">
        <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider block mb-3">LMS Role Badge Breakdown</span>
        <div class="space-y-2.5">
          <div class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-300">
            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Administrators</span>
            <span class="font-mono font-bold"><?= $userStats['admins'] ?></span>
          </div>
          <div class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-300">
            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Librarians</span>
            <span class="font-mono font-bold"><?= $userStats['librarians'] ?></span>
          </div>
          <div class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-slate-300">
            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> Students</span>
            <span class="font-mono font-bold"><?= $userStats['students'] ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Operations Panel: System Audit Logs Data Table (2/3 Width) -->
    <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-5 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="font-display font-bold text-sm text-slate-800 dark:text-white">Recent System Audit Logs</h3>
            <p class="text-[10px] text-slate-400">Live administrator and librarian transaction traces</p>
          </div>
          <a href="logs.php" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold no-underline">
            View All Logs &rarr;
          </a>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-850 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
                <th class="pb-2">Time Stamp</th>
                <th class="pb-2">Trigger User</th>
                <th class="pb-2">Action</th>
                <th class="pb-2">Trace Details</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs">
              <?php if (empty($recentLogs)): ?>
                <tr>
                  <td colspan="4" class="py-12 text-center text-slate-400">
                    <i data-lucide="shield-alert" class="w-10 h-10 text-slate-300 mx-auto mb-2 block"></i>
                    No activity recorded yet.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($recentLogs as $log): ?>
                  <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/20 transition-colors">
                    <td class="py-3 font-mono text-slate-400 text-[11px] whitespace-nowrap">
                      <?= Helper::e(Helper::formatDate($log->created_at, 'd M H:i')) ?>
                    </td>
                    <td class="py-3 font-medium text-slate-700 dark:text-slate-300">
                      <?= Helper::e($log->username) ?> 
                      <span class="inline-block text-[9px] px-1.5 py-0.2 ml-1 rounded font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 uppercase tracking-wide">
                        <?= Helper::e($log->role) ?>
                      </span>
                    </td>
                    <td class="py-3">
                      <span class="px-2 py-0.5 rounded-full text-[9px] font-bold font-mono uppercase bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30">
                        <?= Helper::e($log->action) ?>
                      </span>
                    </td>
                    <td class="py-3 text-slate-500 dark:text-slate-400 max-w-xs truncate" title="<?= Helper::e($log->description) ?>">
                      <?= Helper::e($log->description) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-850/80 text-center flex items-center justify-between">
        <span class="text-[10px] text-slate-400 font-medium">LMS Core Status: Online</span>
        <span class="text-[10px] text-slate-400 font-medium font-mono">v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?></span>
      </div>
    </div>

  </div>
</div>

<?php 
include ROOT_URL . '/app/includes/footer.php'; 
?>