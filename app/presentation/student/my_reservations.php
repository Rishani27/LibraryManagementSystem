<?php
// ============================================================
// ULMS — Student My Reservations
// presentation/student/my_reservations.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/ReservationService.php';

Session::requireLogin('student');

$resSvc    = new ReservationService();
$studentId = Session::getUserId();

// Allow student to cancel own reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['reservation_id'] ?? 0);
    $result = $resSvc->cancel($id);
    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: my_reservations.php');
    exit;
}

$reservations = $resSvc->getStudentReservations($studentId);

$pageTitle  = 'My Reservations';
$activePage = 'my_reservations';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_student.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">🔖 My Reservations</span>
    <a href="catalog.php" class="btn btn-primary btn-sm">+ Browse Catalog</a>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">My Reservations (<?= count($reservations) ?>)</h2>
      </div>

      <?php if (empty($reservations)): ?>
        <div class="text-center text-muted" style="padding:40px">
          No reservations yet. <a href="catalog.php">Browse the catalog</a> to reserve books.
        </div>
      <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Book</th><th>Reserved At</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
          <?php foreach ($reservations as $i => $res): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><strong><?= Helper::e($res->book_title) ?></strong></td>
              <td class="text-muted"><?= Helper::e(Helper::formatDate($res->reserved_at, DATETIME_FORMAT)) ?></td>
              <td><?= Helper::reservationStatusBadge($res->status) ?></td>
              <td>
                <?php if ($res->status === 'pending'): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="Cancel reservation for '<?= Helper::e($res->book_title) ?>'?">
                    ✕ Cancel
                  </button>
                </form>
                <?php else: ?>
                <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>
