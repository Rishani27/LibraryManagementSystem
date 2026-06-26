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

$userStats  = $userService->getUserStats();
$bookStats  = $bookService->getBookStats();
$activeLoans = $loanRepo->countActive();
$overdueLoans= $loanRepo->countOverdue();
$unpaidFines = $fineRepo->countUnpaid();
$recentLogs  = $logRepo->findAll(10);

$pageTitle  = 'Admin Dashboard';
$activePage = 'dashboard';

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_admin.php';

$flash = Session::getFlash();
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">🏠 Admin Dashboard</span>
    <span class="text-muted" style="font-size:0.82rem">Welcome back, <?= Helper::e(Session::getFullName()) ?></span>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>">
      <?= Helper::e($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon blue">👥</div>
        <div>
          <div class="stat-value"><?= $userStats['total'] ?></div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">📖</div>
        <div>
          <div class="stat-value"><?= $bookStats['total'] ?></div>
          <div class="stat-label">Total Books</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon cyan">📤</div>
        <div>
          <div class="stat-value"><?= $activeLoans ?></div>
          <div class="stat-label">Active Loans</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow">📋</div>
        <div>
          <div class="stat-value"><?= $userStats['students'] ?></div>
          <div class="stat-label">Students</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">💰</div>
        <div>
          <div class="stat-value"><?= $unpaidFines ?></div>
          <div class="stat-label">Pending Fines</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">⚠️</div>
        <div>
          <div class="stat-value"><?= $overdueLoans ?></div>
          <div class="stat-label">Overdue Books</div>
        </div>
      </div>
    </div>

    <!-- Quick Links + Recent Logs -->
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">

      <!-- Quick links -->
      <div class="card">
        <div class="card-header"><h2 class="card-title">Quick Actions</h2></div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <a href="users.php" class="btn btn-primary">👥 Manage Users</a>
          <a href="logs.php"  class="btn btn-ghost">📋 View Security Logs</a>
        </div>
        <div class="mt-3">
          <div class="text-muted">Role Breakdown</div>
          <table style="margin-top:8px">
            <tr><td>Administrators</td><td><strong><?= $userStats['admins'] ?></strong></td></tr>
            <tr><td>Librarians</td><td><strong><?= $userStats['librarians'] ?></strong></td></tr>
            <tr><td>Students</td><td><strong><?= $userStats['students'] ?></strong></td></tr>
          </table>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Recent Activity</h2>
          <a href="logs.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Time</th><th>User</th><th>Action</th><th>Details</th></tr>
            </thead>
            <tbody>
            <?php if (empty($recentLogs)): ?>
              <tr><td colspan="4" class="text-center text-muted">No activity recorded yet.</td></tr>
            <?php else: ?>
              <?php foreach ($recentLogs as $log): ?>
              <tr>
                <td class="text-muted" style="font-size:0.78rem;white-space:nowrap">
                  <?= Helper::e(Helper::formatDate($log->created_at, 'd M H:i')) ?>
                </td>
                <td><?= Helper::e($log->username) ?>
                  <span class="badge badge-default" style="font-size:0.65rem"><?= Helper::e($log->role) ?></span>
                </td>
                <td><code style="font-size:0.75rem;color:#818cf8"><?= Helper::e($log->action) ?></code></td>
                <td class="text-muted" style="font-size:0.78rem"><?= Helper::e(Helper::truncate($log->description, 60)) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </div><!-- /.page-content -->
</div><!-- /.main-content -->

<?php include ROOT_URL . '/app/includes/footer.php'; ?>
