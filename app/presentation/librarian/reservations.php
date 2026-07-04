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

$_SESSION['role'] = 'librarian';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <!-- Header Section Controls -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
        Pending Student Reservations
      </h1>
      <p class="text-xs text-slate-500">Approve allocation queues. Fulfilling reservations holds dynamic copy stock for student check-ins.</p>
    </div>
    <div class="px-3.5 py-1.5 rounded-xl border border-amber-200 bg-amber-50/50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 font-semibold text-xs flex items-center gap-2 self-start shadow-xs">
      <i data-lucide="clock" class="w-4 h-4 animate-spin"></i>
      <span><?= $pendingCount ?> Request Queue Pending</span>
    </div>
  </div>

  <!-- Session Flash Updates Alert Container Wrapper -->
  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <!-- Toggle Selector Segment Filter Tabs -->
  <div class="p-1 bg-slate-100 dark:bg-slate-950 border border-slate-200/30 dark:border-slate-850/40 rounded-xl flex max-w-xs self-start shadow-xs">
    <a href="?filter=all" class="flex-1 text-center py-2 px-4 rounded-lg text-xs font-bold transition-all no-underline <?= $filter !== 'pending' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800' ?>">
      All Allocation Queues
    </a>
    <a href="?filter=pending" class="flex-1 text-center py-2 px-4 rounded-lg text-xs font-bold transition-all no-underline <?= $filter === 'pending' ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800' ?>">
      Pending Only
    </a>
  </div>

  <!-- Main Reservations Queue Ledger Table Content -->
  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
            <th class="py-3 px-5 text-center w-16">No</th>
            <th class="py-3 px-4">Book Title Asset</th>
            <th class="py-3 px-4">Requesting Student</th>
            <th class="py-3 px-4">Reserved Date Timestamp</th>
            <th class="py-3 px-4">Dues Status</th>
            <th class="py-3 px-5 text-right">Moderation Controls</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
          <?php if (empty($reservations)): ?>
            <tr>
              <td colSpan="6" class="py-12 text-center text-slate-400 font-medium">
                <i data-lucide="calendar-x" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                No reservation assets tracked under current sorting conditions.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($reservations as $i => $res): ?>
              <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <td class="py-3.5 px-5 text-center font-mono text-slate-400 font-semibold"><?= $i + 1 ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-100">"<?= Helper::e($res->book_title) ?>"</td>
                <td class="py-3.5 px-4 font-medium text-slate-700 dark:text-slate-300">
                  <?= Helper::e($res->student_name) ?>
                  <span class="text-slate-400 font-mono text-[11px] block mt-0.5">@<?= Helper::e($res->student_username) ?></span>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-450 text-xs"><?= Helper::e(Helper::formatDate($res->reserved_at, DATETIME_FORMAT)) ?></td>
                <td class="py-3.5 px-4">
                  <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border <?= $res->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-450 border-amber-500/20' : ($res->status === 'ready' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-slate-50 dark:bg-slate-800 text-slate-500 border-slate-500/20') ?>">
                    <?= $res->status === 'pending' ? 'Pending Approval' : ($res->status === 'ready' ? 'Ready for Pickup' : 'Expired / Closed') ?>
                  </span>
                </td>
                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                  <?php if ($res->status === 'pending'): ?>
                    <div class="inline-flex gap-1.5">
                      <form method="POST" action="reservations.php">
                        <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                        <input type="hidden" name="action" value="fulfil">
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition cursor-pointer flex items-center gap-1">
                          <i data-lucide="check" class="w-3.5 h-3.5"></i> Fulfil Request
                        </button>
                      </form>
                      <form method="POST" action="reservations.php" onsubmit="return confirm('Cancel reservation queue entry?');">
                        <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 rounded-lg transition cursor-pointer" title="Cancel Request">
                          <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                      </form>
                    </div>
                  <?php else: ?>
                    <span class="text-slate-400 block text-xs pr-4 font-medium">—</span>
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

<?php include ROOT_URL . '/app/includes/footer.php'; ?>