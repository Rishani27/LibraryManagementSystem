<?php
// ============================================================
// ULMS — Student Dashboard
// presentation/student/dashboard.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';

Session::requireLogin('student');

$studentId   = Session::getUserId();
$loanRepo    = new LoanRepository();
$fineRepo    = new FineRepository();
$resRepo     = new ReservationRepository();

$myLoans     = $loanRepo->findByStudent($studentId);
$activeLoans = array_filter($myLoans, fn($l) => $l->status === 'active');
$myFines     = $fineRepo->findByStudent($studentId);
$unpaidFines = array_filter($myFines, fn($f) => $f->status === 'unpaid');
$myRes       = $resRepo->findByStudent($studentId);
$pendingRes  = array_filter($myRes, fn($r) => $r->status === 'pending');

// Overdue notifications
$overdueLoans = array_filter($activeLoans, fn($l) => $l->isOverdue());

$pageTitle  = 'My Dashboard';
$activePage = 'dashboard';

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_student.php';

$flash = Session::getFlash();
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">🏠 Student Dashboard</span>
    <span class="text-muted" style="font-size:0.82rem">Welcome, <?= Helper::e(Session::getFullName()) ?></span>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Overdue alert -->
    <?php if (!empty($overdueLoans)): ?>
    <div class="alert alert-error">
      ⚠️ You have <strong><?= count($overdueLoans) ?></strong> overdue book(s).
      Please return them immediately to avoid additional fines.
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue">📤</div>
        <div><div class="stat-value"><?= count($activeLoans) ?></div><div class="stat-label">Active Loans</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">⚠️</div>
        <div><div class="stat-value"><?= count($overdueLoans) ?></div><div class="stat-label">Overdue</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow">🔖</div>
        <div><div class="stat-value"><?= count($pendingRes) ?></div><div class="stat-label">Active Reservations</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">💰</div>
        <div><div class="stat-value"><?= count($unpaidFines) ?></div><div class="stat-label">Unpaid Fines</div></div>
      </div>
    </div>

    <!-- My current loans -->
    <div class="card mt-3">
      <div class="card-header">
        <h2 class="card-title">My Current Loans</h2>
        <a href="catalog.php" class="btn btn-primary btn-sm">Browse Catalog →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Book</th><th>Author</th><th>Issue Date</th><th>Due Date</th><th>Status</th><th>Est. Fine</th></tr></thead>
          <tbody>
          <?php if (empty($activeLoans)): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:24px">No active loans.</td></tr>
          <?php else: ?>
          <?php foreach ($activeLoans as $loan): ?>
            <?php
              $overdue = Helper::calculateOverdueDays($loan->due_date);
              $fine    = Helper::calculateFine($overdue);
            ?>
            <tr <?= $overdue > 0 ? 'data-overdue="1"' : '' ?>>
              <td><strong><?= Helper::e($loan->book_title) ?></strong></td>
              <td><?= Helper::e($loan->book_author) ?></td>
              <td><?= Helper::e(Helper::formatDate($loan->issue_date)) ?></td>
              <td><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
              <td>
                <?= $overdue > 0
                    ? '<span class="badge badge-overdue">Overdue ' . $overdue . ' day(s)</span>'
                    : '<span class="badge badge-active">Active</span>' ?>
              </td>
              <td><?= $fine > 0 ? '<span style="color:#f87171">'.Helper::formatCurrency($fine).'</span>' : '—' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- My unpaid fines -->
    <?php if (!empty($unpaidFines)): ?>
    <div class="card mt-3">
      <div class="card-header"><h2 class="card-title">⚠️ My Outstanding Fines</h2></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Book</th><th>Overdue Days</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($unpaidFines as $fine): ?>
          <tr>
            <td><?= Helper::e($fine->book_title) ?></td>
            <td><?= $fine->overdue_days ?></td>
            <td><strong style="color:#f87171"><?= Helper::formatCurrency($fine->amount) ?></strong></td>
            <td><?= Helper::fineStatusBadge($fine->status) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="text-muted mt-2" style="font-size:0.8rem">
        Please visit the library to settle your fines. Contact your librarian.
      </p>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>
