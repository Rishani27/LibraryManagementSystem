<?php
// ============================================================
// ULMS — Reservation Management  (FR-08 — Member 4)
// presentation/librarian/reservations.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Reservation.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';
require_once ROOT_URL . '/app/data/repositories/BookRepository.php';
require_once ROOT_URL . '/app/data/repositories/UserRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/ReservationService.php';

Session::requireLogin('librarian');

$resSvc = new ReservationService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['reservation_id'] ?? 0);

    if ($action === 'fulfil') {
        $result = $resSvc->fulfil($id);
    } elseif ($action === 'cancel') {
        $result = $resSvc->cancel($id);
    } else {
        $result = ['success' => false, 'message' => 'Unknown action.'];
    }

    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: reservations.php');
    exit;
}

$filter       = $_GET['filter'] ?? 'all';
$reservations = $filter === 'pending'
    ? $resSvc->getPendingReservations()
    : $resSvc->getAllReservations();

$pendingCount = $resSvc->countPending();

$pageTitle  = 'Reservations';
$activePage = 'reservations';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_librarian.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">🔖 Reservation Management</span>
    <span class="badge badge-warning"><?= $pendingCount ?> pending</span>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Filter tabs -->
    <div style="display:flex;gap:8px;margin-bottom:16px">
      <a href="?filter=all"     class="btn <?= $filter!=='pending'?'btn-primary':'btn-ghost' ?> btn-sm">All</a>
      <a href="?filter=pending" class="btn <?= $filter==='pending' ?'btn-primary':'btn-ghost' ?> btn-sm">Pending Only</a>
    </div>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Reservations (<?= count($reservations) ?>)</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Book</th><th>Student</th><th>Reserved At</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php if (empty($reservations)): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:32px">No reservations found.</td></tr>
          <?php else: ?>
          <?php foreach ($reservations as $i => $res): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><strong><?= Helper::e($res->book_title) ?></strong></td>
              <td><?= Helper::e($res->student_name) ?>
                <span class="text-muted" style="font-size:0.78rem">(<?= Helper::e($res->student_username) ?>)</span>
              </td>
              <td class="text-muted"><?= Helper::e(Helper::formatDate($res->reserved_at, DATETIME_FORMAT)) ?></td>
              <td><?= Helper::reservationStatusBadge($res->status) ?></td>
              <td>
                <?php if ($res->status === 'pending'): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                  <button type="submit" name="action" value="fulfil" class="btn btn-success btn-sm">✅ Fulfil</button>
                </form>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                  <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm"
                          data-confirm="Cancel this reservation?">✕ Cancel</button>
                </form>
                <?php else: ?>
                <span class="text-muted">—</span>
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
