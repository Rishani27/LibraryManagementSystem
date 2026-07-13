<?php
// ============================================================
// ULMS — Student Reservations Tracker Panel + Hold Expiry Timers
// presentation/student/my_reservations.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Reservation.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';
require_once ROOT_URL . '/app/business/services/ReservationService.php';

Session::requireLogin('student');

$resSvc    = new ReservationService();
$studentId = Session::getUserId();

// Background check to instantly free overdue inventory items
$resSvc->processExpirations();

// Handle cancellation request submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resId  = (int)($_POST['reservation_id'] ?? 0);
    $result = $resSvc->cancel($resId);
    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: my_reservations.php');
    exit;
}

$reservations = $resSvc->getStudentReservations($studentId);

$pageTitle  = 'My Reservations';
$activePage = 'my_reservations';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
        My Book Holds & Reservations
      </h1>
      <p class="text-xs text-slate-500">Track pending clearances and cancellation pipelines. Approved "Ready" pickups remain saved for 3 calendar days.</p>
    </div>
    <a href="catalog.php" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition shadow-sm flex items-center gap-1.5 self-start no-underline">
      <i data-lucide="plus" class="w-4 h-4"></i> Browse Asset Catalog
    </a>
  </div>

  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80">
      <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white">Active Asset Hold Ledger</h2>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="border-b border-slate-100 dark:border-slate-850 text-slate-400 text-[10px] font-bold uppercase tracking-wider bg-slate-50/20 dark:bg-slate-950/10">
            <th class="py-3 px-5 text-center w-12">No</th>
            <th class="py-3 px-4">Book Title Asset</th>
            <th class="py-3 px-4">Reservation Timestamp</th>
            <th class="py-3 px-4">Pickup Status</th>
            <th class="py-3 px-5 text-right">Operational Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
          <?php if (empty($reservations)): ?>
            <tr>
              <td colSpan="5" class="py-12 text-center text-slate-400 font-medium">
                <i data-lucide="calendar-check" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                You haven't requested or reserved any books yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($reservations as $i => $res): ?>
              <?php 
                $isApprovedHold = ($res->status === 'pending' && !empty($res->fulfilled_at)); 
                $isPurePending  = ($res->status === 'pending' && empty($res->fulfilled_at));
              ?>
              <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <td class="py-3.5 px-5 text-center font-mono text-slate-400 font-semibold"><?= $i + 1 ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-100">"<?= Helper::e($res->book_title) ?>"</td>
                <td class="py-3.5 px-4 font-mono text-slate-450 text-xs"><?= Helper::e(Helper::formatDate($res->reserved_at, DATETIME_FORMAT)) ?></td>
                <td class="py-3.5 px-4">
                  <?php if ($isPurePending): ?>
                    <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border bg-amber-50 dark:bg-amber-950/20 text-amber-600 border-amber-500/20">
                      Pending Approval
                    </span>
                  <?php elseif ($isApprovedHold): ?>
                    <div class="space-y-1 max-w-[160px]">
                      <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-500/20">
                        Approved / Ready to Collect
                      </span>
                      <span class="text-[10px] text-rose-500 font-semibold font-mono flex items-center gap-1">
                        <i data-lucide="hourglass" class="w-3 h-3 animate-pulse"></i>
                        Time Left: <span class="countdown-timer font-bold" data-expiry="<?= date('c', strtotime($res->fulfilled_at)) ?>">Calculating...</span>
                      </span>
                    </div>
                  <?php elseif ($res->status === 'fulfilled'): ?>
                    <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border bg-blue-50 dark:bg-blue-950/20 text-blue-600 border-blue-500/20">
                      Collected / Closed
                    </span>
                  <?php else: ?>
                    <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border bg-slate-50 dark:bg-slate-800 text-slate-500">
                      Expired / Closed
                    </span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                  <?php if ($isPurePending || $isApprovedHold): ?>
                    <form method="POST" action="my_reservations.php" onsubmit="return confirm('Withdraw your reservation request?');">
                      <input type="hidden" name="reservation_id" value="<?= $res->id ?>">
                      <button type="submit" class="px-3 py-1 bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 dark:bg-slate-950 dark:border-rose-900/30 text-xs font-semibold rounded-lg shadow-xs cursor-pointer transition-colors">
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const timers = document.querySelectorAll('.countdown-timer');

    function updateTimers() {
        const now = new Date().getTime();

        timers.forEach(timer => {
            const expiryTime = new Date(timer.getAttribute('data-expiry')).getTime();
            const distance = expiryTime - now;

            if (distance < 0) {
                timer.textContent = "Expired";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let displayStr = "";
            if (days > 0) displayStr += `${days}d `;
            displayStr += `${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s`;

            timer.textContent = displayStr;
        });
    }

    updateTimers();
    setInterval(updateTimers, 1000);
});
</script>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>