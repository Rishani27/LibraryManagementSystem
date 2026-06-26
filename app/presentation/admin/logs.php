<?php

// ULMS — Admin Activity Logs  (FR-12 — Member 5)
// presentation/admin/logs.php


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

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_admin.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">📋 Activity Logs</span>
    <div style="display:flex;gap:8px">
      <button class="btn btn-ghost btn-sm btn-print no-print">🖨 Print</button>
    </div>
  </div>

  <div class="page-content fade-in">

    <!-- Filter form -->
    <div class="card mb-3 no-print">
      <form method="GET" class="search-bar">
        <div class="form-group" style="margin:0">
          <label class="form-label">From Date</label>
          <input name="from" type="date" class="form-control" value="<?= Helper::e($fromDate) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">To Date</label>
          <input name="to" type="date" class="form-control" value="<?= Helper::e($toDate) ?>">
        </div>
        <div class="form-group" style="margin:0">
          <label class="form-label">Action Keyword</label>
          <input name="action" type="text" class="form-control" placeholder="e.g. LOGIN, BOOK_BORROWED"
                 value="<?= Helper::e($action) ?>">
        </div>
        <div style="align-self:flex-end;display:flex;gap:8px">
          <button type="submit" class="btn btn-primary">Filter</button>
          <a href="logs.php" class="btn btn-ghost">Clear</a>
        </div>
      </form>
    </div>

    <!-- Log table -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Security & Activity Log (<?= count($logs) ?> entries)</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Timestamp</th><th>User</th><th>Role</th><th>Action</th><th>Description</th><th>IP</th></tr>
          </thead>
          <tbody>
          <?php if (empty($logs)): ?>
            <tr><td colspan="7" class="text-center text-muted" style="padding:32px">No log entries found.</td></tr>
          <?php else: ?>
          <?php foreach ($logs as $i => $log): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td class="text-muted" style="font-size:0.78rem;white-space:nowrap">
                <?= Helper::e(Helper::formatDate($log->created_at, DATETIME_FORMAT)) ?>
              </td>
              <td><?= Helper::e($log->username ?: '—') ?></td>
              <td>
                <?php if ($log->role): ?>
                <span class="badge role-<?= Helper::e($log->role) ?>"><?= ucfirst(Helper::e($log->role)) ?></span>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><code style="font-size:0.75rem;color:#818cf8"><?= Helper::e($log->action) ?></code></td>
              <td class="text-muted" style="font-size:0.8rem"><?= Helper::e($log->description) ?></td>
              <td class="text-muted" style="font-size:0.75rem"><?= Helper::e($log->ip_address) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>
