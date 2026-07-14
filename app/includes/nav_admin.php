<?php 
$webBase = defined('WEB_BASE') ? WEB_BASE : '/library-system'; 

// Identify the currently open page file name to toggle active styles cleanly
$currentPage = basename($_SERVER['SCRIPT_NAME']);

// Establish the hide styles explicitly if the sidebar is set to collapsed state
$hiddenClass = '';
if (isset($_COOKIE['lms_sidebar']) && $_COOKIE['lms_sidebar'] === 'collapsed') {
    $hiddenClass = 'style="display: none !important;"';
}
?>
<aside id="app-sidebar" class="bg-white dark:bg-slate-950 text-slate-800 dark:text-white border-r border-slate-200 dark:border-slate-800/60 h-screen sticky top-0 flex flex-col justify-between transition-all duration-300 z-30 w-64" <?= $sidebarStyle ?? '' ?>>
  <div>
    <!-- Branding Top Drawer Layer - KEPT AS ROUNDED SQUARE AND BIGGER ICON -->
    <div class="px-3 py-4 border-b border-slate-100 dark:border-slate-900 flex items-center justify-between min-h-[73px]">
      <div class="flex items-center gap-2.5 overflow-hidden w-full justify-start pl-1">
        <div class="w-9 h-9 shrink-0 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-lg">
          <i data-lucide="book-open" class="w-5 h-5"></i>
        </div>
        <!-- Wrap branding strings in the text suppression container logic -->
        <div class="flex flex-col whitespace-nowrap" <?= $hiddenClass ?>>
          <span class="font-display font-bold text-sm tracking-tight text-slate-800 dark:text-white">METROPOLIS</span>
          <span class="text-[10px] font-semibold text-blue-500 dark:text-blue-400 tracking-wider uppercase">Library System</span>
        </div>
      </div>
    </div>

    <!-- Active Navigation Module Items -->
    <nav class="px-3 py-4 space-y-2">
      
      <!-- Dashboard Link -->
      <?php $isDash = ($currentPage == 'dashboard.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/admin/dashboard.php" 
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isDash ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isDash ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="layout-dashboard" class="w-4 h-4"></i> 
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Dashboard</span>
      </a>
      
      <!-- Users Registry Link -->
      <?php $isUsers = ($currentPage == 'users.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/admin/users.php" 
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isUsers ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isUsers ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="users" class="w-4 h-4"></i> 
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Users Registry</span>
      </a>
      
      <!-- Activity Logs Link -->
      <?php $isLogs = ($currentPage == 'logs.php'); ?>
      <a href="<?= $webBase ?>/app/presentation/admin/logs.php" 
         class="w-full flex items-center justify-start rounded-xl text-sm font-medium transition-all group p-1.5 <?= $isLogs ? 'text-blue-600 dark:text-blue-400 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white' ?>">
        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all <?= $isLogs ? 'bg-blue-500 text-white shadow-md shadow-blue-500/20' : 'bg-transparent text-slate-400 group-hover:bg-slate-100 dark:group-hover:bg-white/5 group-hover:text-slate-600 dark:group-hover:text-slate-300' ?>">
          <i data-lucide="history" class="w-4 h-4"></i> 
        </div>
        <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Activity Logs</span>
      </a>
      
    </nav>
  </div>

  <!-- Sign Out Session Action Module Link -->
  <div class="p-3 border-t border-slate-100 dark:border-slate-900">
    <a href="<?= $webBase ?>/public/logout.php" class="w-full flex items-center justify-start rounded-xl text-sm font-semibold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all group p-1.5">
      <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 bg-transparent text-rose-500 group-hover:bg-rose-50 dark:group-hover:bg-rose-500/10">
        <i data-lucide="log-out" class="w-4 h-4"></i> 
      </div>
      <span class="sidebar-text-target pl-2" <?= $hiddenClass ?>>Log Out Portal</span>
    </a>
  </div>
</aside>