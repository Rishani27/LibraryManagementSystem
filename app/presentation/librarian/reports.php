<?php
// ============================================================
// ULMS — Reports  (FR-11 — Member 5)
// presentation/librarian/reports.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/ReportService.php';

Session::requireLogin('librarian');

$reportSvc = new ReportService();

$type   = $_GET['type']  ?? 'borrowed';
$from   = $_GET['from']  ?? date('Y-m-01');
$to     = $_GET['to']    ?? date('Y-m-d');

$data  = [];
$title = '';

switch ($type) {
    case 'borrowed':
        $data  = $reportSvc->getBorrowedReport($from, $to);
        $title = 'Borrowed Books Report';
        break;
    case 'overdue':
        $data  = $reportSvc->getOverdueReport();
        $title = 'Overdue Books Report';
        break;
    case 'fines':
        $data  = $reportSvc->getFineReport($from, $to);
        $title = 'Fine Report';
        break;
    case 'activity':
        $data  = $reportSvc->getActivityReport($from, $to);
        $title = 'Activity Log Report';
        break;
}

$summary = $reportSvc->getSummaryStats();

$pageTitle  = 'Reports';
$activePage = 'reports';

$_SESSION['role'] = 'librarian';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <!-- Header Title View -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
        Analytical Library Reports
      </h1>
      <p class="text-xs text-slate-500">View performance metrics, fine compilation data, and seasonal borrowing trends.</p>
    </div>
    <button
      onclick="window.print();"
      class="no-print px-4 py-2.5 bg-slate-900 hover:bg-black dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5 self-start cursor-pointer"
    >
      <i data-lucide="download" class="w-4 h-4"></i> Print Summary Report
    </button>
  </div>

  <!-- Operational Statistics Layout Grid -->
  <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-blue-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Active Loans</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $summary['active_loans'] ?></span>
    </div>
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-rose-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Overdue</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $summary['overdue_loans'] ?></span>
    </div>
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-amber-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Unpaid Fines</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $summary['unpaid_fines'] ?></span>
    </div>
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-emerald-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Collected</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5 font-mono text-base"><?= Helper::formatCurrency($summary['fine_collected']) ?></span>
    </div>
    <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-purple-500 rounded-xl shadow-sm">
      <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider block">Reservations</span>
      <span class="text-xl md:text-2xl font-display font-bold text-slate-800 dark:text-white block mt-1.5"><?= $summary['pending_reservations'] ?></span>
    </div>
  </div>

  <!-- Filter Controls Form -->
  <div class="no-print bg-white dark:bg-slate-900 p-5 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm">
    <form method="GET" action="reports.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Report Compilation Type</label>
        <select name="type" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 outline-none cursor-pointer">
          <option value="borrowed" <?= $type==='borrowed'?'selected':'' ?>>Borrowed Books Ledger</option>
          <option value="overdue"  <?= $type==='overdue' ?'selected':'' ?>>Overdue Exception List</option>
          <option value="fines"    <?= $type==='fines'   ?'selected':'' ?>>Fines Collections</option>
          <option value="activity" <?= $type==='activity'?'selected':'' ?>>Activity System Log</option>
        </select>
      </div>

      <?php if ($type !== 'overdue'): ?>
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">From Date</label>
          <input name="from" type="date" value="<?= Helper::e($from) ?>" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">To Date</label>
          <input name="to" type="date" value="<?= Helper::e($to) ?>" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-blue-500" />
        </div>
      <?php endif; ?>

      <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition cursor-pointer text-center">
        Compile Report
      </button>
    </form>
  </div>

  <!-- Output Data Card Layout -->
  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80 flex items-center justify-between">
      <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white"><?= Helper::e($title) ?></h2>
      <span class="text-xs text-slate-400 font-medium"><?= count($data) ?> record(s) matching baseline parameters</span>
    </div>

    <div class="overflow-x-auto">
      <?php if ($type === 'borrowed' || $type === 'overdue'): ?>
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/20 dark:bg-slate-950/10 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
              <th class="py-3 px-4 w-12 text-center">No</th>
              <th class="py-3 px-4">Book Title</th>
              <th class="py-3 px-4">Author</th>
              <th class="py-3 px-4">Enrolled Student</th>
              <th class="py-3 px-4">Issue Date</th>
              <th class="py-3 px-4">Due Date</th>
              <th class="py-3 px-4">Check-In Date</th>
              <th class="py-3 px-4 text-right">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs">
            <?php if (empty($data)): ?>
              <tr><td colSpan="8" class="py-12 text-center text-slate-400">No matching tracking data logs found.</td></tr>
            <?php else: ?>
              <?php foreach ($data as $i => $loan): ?>
                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                  <td class="py-3 px-4 text-center font-mono text-slate-400 font-semibold"><?= $i+1 ?></td>
                  <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">"<?= Helper::e($loan->book_title) ?>"</td>
                  <td class="py-3 px-4 text-slate-500"><?= Helper::e($loan->book_author) ?></td>
                  <td class="py-3 px-4 font-medium text-slate-700 dark:text-slate-300"><?= Helper::e($loan->student_name) ?></td>
                  <td class="py-3 px-4 font-mono text-slate-450"><?= Helper::e(Helper::formatDate($loan->issue_date)) ?></td>
                  <td class="py-3 px-4 font-mono text-slate-450"><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
                  <td class="py-3 px-4 font-mono text-slate-450"><?= $loan->return_date ? Helper::e(Helper::formatDate($loan->return_date)) : '—' ?></td>
                  <td class="py-3 px-4 text-right"><?= Helper::loanStatusBadge($loan->status) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

      <?php elseif ($type === 'fines'): ?>
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/20 dark:bg-slate-950/10 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
              <th class="py-3 px-4 w-12 text-center">No</th>
              <th class="py-3 px-4">Student Name</th>
              <th class="py-3 px-4">Overdue Resource</th>
              <th class="py-3 px-4 text-center">Overdue Limit Delays</th>
              <th class="py-3 px-4">Fine Amount</th>
              <th class="py-3 px-4 text-right">Dues Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs">
            <?php if (empty($data)): ?>
              <tr><td colSpan="6" class="py-12 text-center text-slate-400">No outstanding dynamic dues metrics recorded.</td></tr>
            <?php else: ?>
              <?php foreach ($data as $i => $fine): ?>
                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                  <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-semibold"><?= $i+1 ?></td>
                  <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200"><?= Helper::e($fine->student_name) ?></td>
                  <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">"<?= Helper::e($fine->book_title) ?>"</td>
                  <td class="py-3.5 px-4 text-center font-mono font-semibold text-rose-500"><?= $fine->overdue_days ?> days</td>
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-700 dark:text-slate-300"><?= Helper::formatCurrency($fine->amount) ?></td>
                  <td class="py-3.5 px-4 text-right"><?= Helper::fineStatusBadge($fine->status) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

      <?php elseif ($type === 'activity'): ?>
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/20 dark:bg-slate-950/10 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-[10px] font-bold uppercase tracking-wider">
              <th class="py-3 px-4 w-12 text-center">No</th>
              <th class="py-3 px-4">System Timestamp</th>
              <th class="py-3 px-4">Operator</th>
              <th class="py-3 px-4">Access Badge</th>
              <th class="py-3 px-4">Action Token</th>
              <th class="py-3 px-4 text-right">Audit Trace Description</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs">
            <?php if (empty($data)): ?>
              <tr><td colSpan="6" class="py-12 text-center text-slate-400">No centralized transactions logs traced.</td></tr>
            <?php else: ?>
              <?php foreach ($data as $i => $log): ?>
                <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                  <td class="py-3.5 px-4 text-center font-mono text-slate-400 font-semibold"><?= $i+1 ?></td>
                  <td class="py-3.5 px-4 font-mono text-slate-450"><?= Helper::e(Helper::formatDate($log->created_at, DATETIME_FORMAT)) ?></td>
                  <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">@<?= Helper::e($log->username) ?></td>
                  <td class="py-3.5 px-4">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 uppercase">
                      <?= Helper::e($log->role) ?>
                    </span>
                  </td>
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border"><?= Helper::e($log->action) ?></span>
                  </td>
                  <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 text-right"><?= Helper::e($log->description) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

</div>

<style>
  @media print {
      .no-print { display: none !important; }
      body { background: white !important; color: black !important; }
      #app-sidebar, #app-topbar { display: none !important; }
      main { padding: 0 !important; margin: 0 !important; }
  }
</style>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>