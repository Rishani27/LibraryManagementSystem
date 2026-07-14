<?php
// ============================================================
// ULMS — Admin Activity Logs  (FR-12 — Member 5)
// presentation/admin/logs.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/LogService.php';

Session::requireLogin('admin');

$logService = new LogService();

$fromDate = $_GET['from'] ?? '';
$toDate   = $_GET['to']   ?? '';
$action   = $_GET['action'] ?? '';

if ($fromDate && $toDate) {
    $logs = $logService->getByDateRange($fromDate, $toDate);
} elseif ($action) {
    $logs = $logService->getByAction($action);
} else {
    $logs = $logService->getAll(300);
}

$pageTitle  = 'Activity Logs';
$activePage = 'logs';

$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <!-- Top Title Header View Row -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
        Centralized System Activity Logs
      </h1>
      <p class="text-xs text-slate-500">Chronological checklist audit logs of active authentication entries, system updates, and database modifications.</p>
    </div>
    <button
      onclick="window.print();"
      class="no-print px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shadow-sm border border-slate-200/50 dark:border-slate-700/50 cursor-pointer flex items-center gap-1.5 self-start transition-all"
    >
      <i data-lucide="download" class="w-4 h-4"></i> Print Systems Log
    </button>
  </div>

  <!-- Filter & Parameter Search Fields Bar Forms (Hidden during Printing profiles) -->
  <div class="no-print bg-white dark:bg-slate-900 p-5 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm">
    <form method="GET" action="logs.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">From Date</label>
        <input name="from" type="date" value="<?= Helper::e($fromDate) ?>" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-blue-500" />
      </div>
      
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">To Date</label>
        <input name="to" type="date" value="<?= Helper::e($toDate) ?>" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-blue-500" />
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Action Keyword</label>
        <input name="action" type="text" placeholder="e.g. LOGIN, UPDATE" value="<?= Helper::e($action) ?>" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-blue-500 placeholder:text-slate-400" />
      </div>

      <div class="flex gap-2.5">
        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition cursor-pointer text-center">
          Filter Matrix
        </button>
        <?php if ($fromDate || $toDate || $action): ?>
          <a href="logs.php" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition text-center no-underline flex items-center justify-center">
            Clear
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Historical Trace Datatable Records Card Container -->
  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80 flex items-center justify-between">
      <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white">Security & Operational Audit Records</h2>
      <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-500 px-2.5 py-0.5 rounded-full font-semibold"><?= count($logs) ?> entries</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/20 dark:bg-slate-950/10 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
            <th class="py-3 px-5 text-center w-16">No</th>
            <th class="py-3 px-4">System Timestamp</th>
            <th class="py-3 px-4">Trigger Operator</th>
            <th class="py-3 px-4">Profile Access Role</th>
            <th class="py-3 px-4">Action Token</th>
            <th class="py-3 px-4">Audit Trace Details</th>
            <th class="py-3 px-5 text-right">Network IP Node</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
          <?php if (empty($logs)): ?>
            <tr>
              <td colSpan="7" class="py-12 text-center text-slate-400">
                <i data-lucide="shield-alert" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                No historical transaction logs found tracking those parameters.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs as $i => $log): ?>
              <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <td class="py-3.5 px-5 text-center font-mono text-slate-400 font-semibold"><?= $i + 1 ?></td>
                <td class="py-3.5 px-4 font-mono text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                  <?= Helper::e(Helper::formatDate($log->created_at, DATETIME_FORMAT)) ?>
                </td>
                <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                  @<?= Helper::e($log->username ?: 'system_node') ?>
                </td>
                <td class="py-3.5 px-4">
                  <?php if ($log->role): ?>
                    <span class="inline-block text-[10px] px-2 py-0.5 rounded font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 uppercase tracking-wide border border-slate-200/30 dark:border-slate-700/30">
                      <?= Helper::e($log->role) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-slate-400 font-mono">—</span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono uppercase bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30">
                    <?= Helper::e($log->action) ?>
                  </span>
                </td>
                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300 font-medium max-w-sm truncate" title="<?= Helper::e($log->description) ?>">
                  <?= Helper::e($log->description) ?>
                </td>
                <td class="py-3.5 px-5 text-right font-mono text-xs text-slate-400 tracking-tight"><?= Helper::e($log->ip_address) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Injected Print Media Styling Override -->
<style>
  @media print {
      .no-print { display: none !important; }
      body { background: white !important; color: black !important; }
      #app-sidebar, #app-topbar { display: none !important; }
      main { padding: 0 !important; margin: 0 !important; }
      .bg-white { background: transparent !important; border: none !important; box-shadow: none !important; }
  }
</style>

<?php 
include ROOT_URL . '/app/includes/footer.php'; 
?>