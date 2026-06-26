<?php
// presentation/librarian/reports.ph

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

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_librarian.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">📊 Reports</span>
    <div class="no-print" style="display:flex;gap:8px">
      <button class="btn btn-ghost btn-sm btn-print">🖨 Print</button>
    </div>
  </div>

  <div class="page-content fade-in">

    <!-- Summary stats -->
    <div class="stats-grid" style="margin-bottom:20px">
      <div class="stat-card"><div class="stat-icon blue">📤</div>
        <div><div class="stat-value"><?= $summary['active_loans'] ?></div><div class="stat-label">Active Loans</div></div></div>
      <div class="stat-card"><div class="stat-icon red">⚠️</div>
        <div><div class="stat-value"><?= $summary['overdue_loans'] ?></div><div class="stat-label">Overdue</div></div></div>
      <div class="stat-card"><div class="stat-icon yellow">💰</div>
        <div><div class="stat-value"><?= $summary['unpaid_fines'] ?></div><div class="stat-label">Unpaid Fines</div></div></div>
      <div class="stat-card"><div class="stat-icon green">💵</div>
        <div><div class="stat-value" style="font-size:1rem"><?= Helper::formatCurrency($summary['fine_collected']) ?></div><div class="stat-label">Collected</div></div></div>
      <div class="stat-card"><div class="stat-icon yellow">🔖</div>
        <div><div class="stat-value"><?= $summary['pending_reservations'] ?></div><div class="stat-label">Reservations</div></div></div>
    </div>

    <!-- Filter form -->
    <div class="card mb-3 no-print">
      <form method="GET" class="search-bar">
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
          <div class="form-group" style="margin:0">
            <label class="form-label">Report Type</label>
            <select name="type" class="form-control">
              <option value="borrowed" <?= $type==='borrowed'?'selected':'' ?>>Borrowed Books</option>
              <option value="overdue"  <?= $type==='overdue' ?'selected':'' ?>>Overdue Books</option>
              <option value="fines"    <?= $type==='fines'   ?'selected':'' ?>>Fines</option>
              <option value="activity" <?= $type==='activity'?'selected':'' ?>>Activity Log</option>
            </select>
          </div>
          <?php if ($type !== 'overdue'): ?>
          <div class="form-group" style="margin:0">
            <label class="form-label">From</label>
            <input name="from" type="date" class="form-control" value="<?= Helper::e($from) ?>">
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">To</label>
            <input name="to" type="date" class="form-control" value="<?= Helper::e($to) ?>">
          </div>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary">Generate</button>
        </div>
      </form>
    </div>

    <!-- Report output -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><?= Helper::e($title) ?></h2>
        <span class="text-muted"><?= count($data) ?> record(s) | <?= Helper::e($from) ?> – <?= Helper::e($to) ?></span>
      </div>

      <div class="table-wrap">
        <?php if ($type === 'borrowed' || $type === 'overdue'): ?>
        <table>
          <thead><tr><th>#</th><th>Book</th><th>Author</th><th>Student</th>
            <th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:32px">No records found.</td></tr>
          <?php else: ?>
          <?php foreach ($data as $i => $loan): ?>
          <tr <?= $loan->isOverdue() ? 'data-overdue="1"' : '' ?>>
            <td><?= $i+1 ?></td>
            <td><?= Helper::e($loan->book_title) ?></td>
            <td><?= Helper::e($loan->book_author) ?></td>
            <td><?= Helper::e($loan->student_name) ?></td>
            <td><?= Helper::e(Helper::formatDate($loan->issue_date)) ?></td>
            <td><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
            <td><?= Helper::e($loan->return_date ? Helper::formatDate($loan->return_date) : '—') ?></td>
            <td><?= Helper::loanStatusBadge($loan->status) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>

        <?php elseif ($type === 'fines'): ?>
        <table>
          <thead><tr><th>#</th><th>Student</th><th>Book</th><th>Overdue Days</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:32px">No records.</td></tr>
          <?php else: ?>
          <?php foreach ($data as $i => $fine): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= Helper::e($fine->student_name) ?></td>
            <td><?= Helper::e($fine->book_title) ?></td>
            <td><?= $fine->overdue_days ?></td>
            <td><?= Helper::formatCurrency($fine->amount) ?></td>
            <td><?= Helper::fineStatusBadge($fine->status) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>

        <?php elseif ($type === 'activity'): ?>
        <table>
          <thead><tr><th>#</th><th>Timestamp</th><th>User</th><th>Role</th><th>Action</th><th>Description</th></tr></thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:32px">No records.</td></tr>
          <?php else: ?>
          <?php foreach ($data as $i => $log): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td class="text-muted"><?= Helper::e(Helper::formatDate($log->created_at, DATETIME_FORMAT)) ?></td>
            <td><?= Helper::e($log->username) ?></td>
            <td><span class="badge role-<?= Helper::e($log->role) ?>"><?= ucfirst(Helper::e($log->role)) ?></span></td>
            <td><code style="font-size:0.75rem"><?= Helper::e($log->action) ?></code></td>
            <td class="text-muted"><?= Helper::e($log->description) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>
