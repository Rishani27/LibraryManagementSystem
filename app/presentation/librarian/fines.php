<?php
// ============================================================
// ULMS — Fine Management  (FR-09 — Member 4)
// presentation/librarian/fines.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Fine.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/FineService.php';

Session::requireLogin('librarian');

$fineSvc = new FineService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'settle') {
        $fineId = (int)($_POST['fine_id'] ?? 0);
        $result = $fineSvc->settleFine($fineId, Session::getUserId());
        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

    } elseif ($action === 'process_overdue') {
        $count = $fineSvc->processOverdueFines();
        Session::flash('success', "Processed {$count} new overdue fine(s).");
    }

    header('Location: fines.php');
    exit;
}

$filter = $_GET['filter'] ?? 'unpaid';
$fines  = $filter === 'all'
    ? $fineSvc->getAllFines()
    : $fineSvc->getUnpaidFines();

$stats = $fineSvc->getDashboardStats();

$pageTitle  = 'Fine Management';
$activePage = 'fines';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_librarian.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">💰 Fine Management</span>
    <form method="POST" style="display:inline">
      <input type="hidden" name="action" value="process_overdue">
      <button type="submit" class="btn btn-warning btn-sm"
              data-confirm="Process overdue fines for all active overdue loans?">
        ⚡ Process Overdue Fines
      </button>
    </form>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
      <div class="stat-card">
        <div class="stat-icon red">⚠️</div>
        <div><div class="stat-value"><?= $stats['unpaid_count'] ?></div><div class="stat-label">Unpaid Fines</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">💰</div>
        <div>
          <div class="stat-value" style="font-size:1.1rem"><?= Helper::formatCurrency($stats['unpaid_total']) ?></div>
          <div class="stat-label">Outstanding</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div><div class="stat-value"><?= $stats['paid_count'] ?></div><div class="stat-label">Settled Fines</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">💵</div>
        <div>
          <div class="stat-value" style="font-size:1.1rem"><?= Helper::formatCurrency($stats['total_collected']) ?></div>
          <div class="stat-label">Total Collected</div>
        </div>
      </div>
    </div>

    <!-- Filter tabs -->
    <div style="display:flex;gap:8px;margin-bottom:16px">
      <a href="?filter=unpaid" class="btn <?= $filter!=='all'?'btn-primary':'btn-ghost' ?> btn-sm">Unpaid Only</a>
      <a href="?filter=all"    class="btn <?= $filter==='all' ?'btn-primary':'btn-ghost' ?> btn-sm">All Fines</a>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Fines (<?= count($fines) ?>)</h2>
        <span class="text-muted" style="font-size:0.8rem">Rs. <?= FINE_PER_DAY ?>/day overdue</span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Student</th><th>Book</th><th>Due Date</th>
                <th>Overdue Days</th><th>Amount</th><th>Status</th><th>Action</th></tr>
          </thead>
          <tbody>
          <?php if (empty($fines)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:32px">No fines found.</td></tr>
          <?php else: ?>
          <?php foreach ($fines as $i => $fine): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><?= Helper::e($fine->student_name) ?>
                <span class="text-muted" style="font-size:0.78rem">(<?= Helper::e($fine->student_username) ?>)</span>
              </td>
              <td><?= Helper::e($fine->book_title) ?></td>
              <td class="text-muted"><?= Helper::e(Helper::formatDate($fine->due_date)) ?></td>
              <td><span class="badge badge-overdue"><?= $fine->overdue_days ?> day<?= $fine->overdue_days > 1 ? 's' : '' ?></span></td>
              <td><strong style="color:#f87171"><?= Helper::formatCurrency($fine->amount) ?></strong></td>
              <td><?= Helper::fineStatusBadge($fine->status) ?></td>
              <td>
                <?php if ($fine->status === 'unpaid'): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="action"  value="settle">
                  <input type="hidden" name="fine_id" value="<?= $fine->id ?>">
                  <button type="submit" class="btn btn-success btn-sm"
                    data-confirm="Settle fine of <?= Helper::formatCurrency($fine->amount) ?> for <?= Helper::e($fine->student_name) ?>?">
                    💳 Settle
                  </button>
                </form>
                <?php else: ?>
                <span class="text-muted">Paid <?= Helper::e(Helper::formatDate($fine->settled_at ?? '')) ?></span>
                <?php endif; ?>
              </td>
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
