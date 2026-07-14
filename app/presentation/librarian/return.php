<?php
// ============================================================
// ULMS — Return Books  (FR-07 — Member 3)
// presentation/librarian/return.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Loan.php';
require_once ROOT_URL . '/app/data/models/Book.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/BookRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/ReturnService.php';
require_once ROOT_URL . '/app/business/services/BorrowService.php';

Session::requireLogin('librarian');

$returnService = new ReturnService();
$borrowService = new BorrowService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = (int)($_POST['loan_id'] ?? 0);
    $result = $returnService->returnBook($loanId);
    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: return.php');
    exit;
}



$activeLoans = $returnService->getActiveLoans();

$pageTitle  = 'Return Books';
$activePage = 'return';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_librarian.php';






    
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">📥 Return Books</span>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Active Loans — Select to Return (<?= count($activeLoans) ?>)</h2>
      </div>

      <?php if (empty($activeLoans)): ?>
        <div class="text-center text-muted" style="padding:40px">✅ No active loans to return.</div>
      <?php else: ?>

      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Book</th><th>Student</th><th>Issue Date</th><th>Due Date</th>
                <th>Days Overdue</th><th>Est. Fine</th><th>Action</th></tr>
          </thead>
          <tbody>
          <?php foreach ($activeLoans as $i => $loan): ?>
            <?php
              $overdue = Helper::calculateOverdueDays($loan->due_date);
              $fine    = Helper::calculateFine($overdue);
            ?>
            <tr <?= $overdue > 0 ? 'data-overdue="1"' : '' ?>>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><strong><?= Helper::e($loan->book_title) ?></strong><br>
                  <span class="text-muted" style="font-size:0.78rem"><?= Helper::e($loan->book_author) ?></span>
              </td>
              <td><?= Helper::e($loan->student_name) ?>
                  <br><span class="text-muted" style="font-size:0.78rem"><?= Helper::e($loan->student_username) ?></span>
              </td>
              <td class="text-muted"><?= Helper::e(Helper::formatDate($loan->issue_date)) ?></td>
              <td class="text-muted"><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
              <td>
                <?php if ($overdue > 0): ?>
                  <span class="badge badge-overdue"><?= $overdue ?> day<?= $overdue > 1 ? 's' : '' ?></span>
                <?php else: ?>
                  <span class="badge badge-active">On time</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($fine > 0): ?>
                  <span style="color:#f87171;font-weight:600"><?= Helper::formatCurrency($fine) ?></span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="loan_id" value="<?= $loan->id ?>">
                  <button type="submit" class="btn btn-success btn-sm"
                    data-confirm="Return '<?= Helper::e($loan->book_title) ?>' for <?= Helper::e($loan->student_name) ?>?<?= $fine > 0 ? ' A fine of ' . Helper::formatCurrency($fine) . ' will be applied.' : '' ?>">
                    📥 Return
                  </button>
                </form>
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
