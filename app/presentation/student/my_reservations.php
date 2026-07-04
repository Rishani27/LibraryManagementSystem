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

$_SESSION['role'] = 'student';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <!-- Header Title parameters section -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
        My Book Holds & Reservations
      </h1>
      <p class="text-xs text-slate-500">Track pending clearances and cancellation pipelines. Approved "Ready" pickups remain saved for 3 calendar days.</p>
    </div>
    <a href="catalog.php" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs md:text-sm font-semibold rounded-xl shadow-md transition no-underline flex items-center gap-1.5 self-start transition-all">
      <i data-lucide="plus" class="w-4 h-4"></i> Browse Asset Catalog
    </a>
  </div>

  <!-- Feedback Messaging Display Area -->
  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <!-- Reservations Log Queue DataTable -->
  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80">
      <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white">Active Asset Hold Ledger</h2>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/20 dark:bg-slate-950/10 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
            <th class="py-3 px-5 text-center w-16">No</th>
            <th class="py-3 px-4">Book Title Asset</th>
            <th class="py-3 px-4">Reservation Timestamp</th>
            <th class="py-3 px-4">Pickup Status</th>
            <th class="py-3 px-5 text-right">Operational Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
          <?php if (empty($reservations)): ?>
            <tr>
              <td colSpan="5" class="py-16 text-center text-slate-400 font-medium">
                <i data-lucide="calendar-check" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                You have no active or pending holds. <a href="catalog.php" class="text-indigo-500 font-bold hover:underline">Browse the catalog</a> to request material acquisitions.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($reservations as $i => $res): ?>
              <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <td class="py-3.5 px-5 text-center font-mono text-slate-400 font-semibold"><?= $i + 1 ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-100">"<?= Helper::e($res->book_title) ?>"</td>
                <td class="py-3.5 px-4 font-mono text-slate-450 text-xs"><?= Helper::e(Helper::formatDate($res->reserved_at, DATETIME_FORMAT)) ?></td>
                <td class="py-3.5 px-4">
                  <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border <?= $res->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/20 text-amber-600 border-amber-500/10' : ($res->status === 'ready' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 border-emerald-500/10' : 'bg-slate-50 dark:bg-slate-800 text-slate-500 border-slate-500/10') ?>">
                    <?= $res->status === 'pending' ? 'Pending Approval' : ($res->status === 'ready' ? 'Ready for Pickup' : 'Expired / Closed') ?>
                  </span>
                </td>
                <td class="py-3.5 px-5 text-right">
                  <?php if ($res->status === 'pending'): ?>
                    <form method="POST" action="my_reservations.php" onsubmit="return confirm('Withdraw hold request for \'<?= addslashes(Helper::e($res->book_title)) ?>\'?');" class="inline">
                      <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                      <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 text-xs font-semibold rounded-lg transition cursor-pointer ml-auto">
                        Withdraw Request
                      </button>
                    </form>
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