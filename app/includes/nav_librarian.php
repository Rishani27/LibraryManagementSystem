<?php 
$webBase = defined('WEB_BASE') ? WEB_BASE : '/library-system';

$currentPage = basename($_SERVER['SCRIPT_NAME']);

$hiddenClass = '';
if (isset($_COOKIE['lms_sidebar']) && $_COOKIE['lms_sidebar'] === 'collapsed') {
    $hiddenClass = 'style="display: none !important;"';
}
?>
<aside id="app-sidebar" class="bg-white dark:bg-slate-950 text-slate-800 dark:text-white border-r border-slate-200 dark:border-slate-800/60 h-screen sticky top-0 flex flex-col justify-between transition-all duration-300 z-30 w-64" <?= $sidebarStyle ?? '' ?>>
  <div>
    <div class="px-3 py-4 border-b border-slate-100 dark:border-slate-900 flex items-center justify-between min-h-[73px]">
      <div class="flex items-center gap-2.5 overflow-hidden w-full justify-start pl-1">
        <div class="w-9 h-9 shrink-0 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
          <i data-lucide="book-open" class="w-5 h-5"></i>
        </div>
        <div class="flex flex-col whitespace-nowrap" <?= $hiddenClass ?>>
          <span class="font-display font-bold text-sm tracking-tight text-slate-800 dark:text-white">ULMS</span>
          <span class="text-[10px] font-semibold text-blue-500 dark:text-blue-400 tracking-wider uppercase">Library System</span>
        </div>
      </div>
    </div>

    <nav class="px-3 py-4 space-y-2">
      <?php $isDash = ($currentPage == 'dashboard.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/librarian/dashboard.php"
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isDash ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isDash ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Dashboard</span>
      </a>
      
      <?php $isBooks = ($currentPage == 'books.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/librarian/books.php"
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isBooks ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isBooks ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="book-open" class="w-4 h-4"></i>
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Book Catalog</span>
      </a>
      
      <?php $isBorrow = ($currentPage == 'borrow.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/librarian/borrow.php"
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isBorrow ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isBorrow ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="bookmark-plus" class="w-4 h-4"></i>
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Issue Books</span>
      </a>
      
      <?php $isReturn = ($currentPage == 'return.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/librarian/return.php"
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isReturn ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isReturn ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="refresh-cw" class="w-4 h-4"></i>
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Return Books</span>
      </a>
      
      <?php $isReservations = ($currentPage == 'reservations.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/librarian/reservations.php"
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isReservations ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isReservations ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="calendar-check" class="w-4 h-4"></i>
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Reservations</span>
      </a>
      
      <?php $isFines = ($currentPage == 'fines.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/librarian/fines.php"
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isFines ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isFines ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="dollar-sign" class="w-4 h-4"></i>
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Manage Fines</span>
      </a>
      
      <?php $isReports = ($currentPage == 'reports.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/librarian/reports.php"
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isReports ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isReports ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Reports</span>
      </a>
    </nav>
  </div>

  <div class="p-3 border-t border-slate-100 dark:border-slate-900">
    <a href="<?= $webBase ?>/public/logout.php" class="w-full flex items-center justify-start rounded-xl text-sm font-semibold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all group p-1.5">
      <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 bg-transparent text-rose-500 group-hover:bg-rose-50 dark:group-hover:bg-rose-500/10">
        <i data-lucide="log-out" class="w-4 h-4"></i>
      </div>
      <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Log Out Portal</span>
    </a>
  </div>
</aside>