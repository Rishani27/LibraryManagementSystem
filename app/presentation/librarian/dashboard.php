<?php
// ============================================================
// ULMS — Librarian Dashboard
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

$loanStats = $borrowSvc->getDashboardStats();
$fineStats = $fineSvc->getDashboardStats();
$pendingRes= $resSvc->countPending();
$overdueList = $borrowSvc->getOverdueLoans();

$pageTitle  = 'Librarian Dashboard';
$activePage = 'dashboard';

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_librarian.php';

$flash = Session::getFlash();
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">🏠 Librarian Dashboard</span>
    <span class="text-muted" style="font-size:0.82rem"><?= date('l, d F Y') ?></span>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue">📤</div>
        <div><div class="stat-value"><?= $loanStats['active_loans'] ?></div><div class="stat-label">Active Loans</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">⚠️</div>
        <div><div class="stat-value"><?= $loanStats['overdue_count'] ?></div><div class="stat-label">Overdue Books</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow">🔖</div>
        <div><div class="stat-value"><?= $pendingRes ?></div><div class="stat-label">Pending Reservations</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">💰</div>
        <div><div class="stat-value"><?= $fineStats['unpaid_count'] ?></div><div class="stat-label">Unpaid Fines</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div><div class="stat-value"><?= $fineStats['paid_count'] ?></div><div class="stat-label">Fines Settled</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">💵</div>
        <div><div class="stat-value" style="font-size:1.3rem"><?= Helper::formatCurrency($fineStats['total_collected']) ?></div><div class="stat-label">Total Collected</div></div>
      </div>
    </div>

    <!-- Quick actions + Overdue -->
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">

      <div class="card">
        <div class="card-header"><h2 class="card-title">Quick Actions</h2></div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <a href="borrow.php"       class="btn btn-primary">📤 Issue a Book</a>
          <a href="return.php"       class="btn btn-success">📥 Return a Book</a>
          <a href="reservations.php" class="btn btn-ghost">🔖 Manage Reservations</a>
          <a href="fines.php"        class="btn btn-ghost">💰 Settle Fines</a>
          <a href="books.php"        class="btn btn-ghost">📖 Book Catalog</a>
          <a href="reports.php"      class="btn btn-ghost">📊 Reports</a>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h2 class="card-title">⚠️ Overdue Books</h2>
          <a href="return.php" class="btn btn-ghost btn-sm">Manage →</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Book</th><th>Student</th><th>Due Date</th><th>Days Overdue</th></tr>
            </thead>
            <tbody>
            <?php if (empty($overdueList)): ?>
              <tr><td colspan="4" class="text-center text-muted" style="padding:24px">✅ No overdue books!</td></tr>
            <?php else: ?>
            <?php foreach (array_slice($overdueList, 0, 8) as $loan): ?>
              <?php $days = Helper::calculateOverdueDays($loan->due_date); ?>
              <tr data-overdue="1">
                <td><?= Helper::e($loan->book_title) ?></td>
                <td><?= Helper::e($loan->student_name) ?></td>
                <td><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
                <td><span class="badge badge-overdue"><?= $days ?> day<?= $days > 1 ? 's' : '' ?></span></td>
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
