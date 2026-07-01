<?php
// ============================================================
// ULMS — Issue Books (Borrowing)  (FR-06 — Member 3)
// presentation/librarian/borrow.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Loan.php';
require_once ROOT_URL . '/app/data/models/Book.php';
require_once ROOT_URL . '/app/data/models/User.php';
require_once ROOT_URL . '/app/data/repositories/LoanRepository.php';
require_once ROOT_URL . '/app/data/repositories/BookRepository.php';
require_once ROOT_URL . '/app/data/repositories/UserRepository.php';
require_once ROOT_URL . '/app/data/repositories/FineRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/validators/BorrowValidator.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/BorrowService.php';

Session::requireLogin('librarian');

$borrowService = new BorrowService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $borrowService->issueBook(
        ['book_id' => $_POST['book_id'] ?? 0, 'student_id' => $_POST['student_id'] ?? 0],
        Session::getUserId()
    );
    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: borrow.php');
    exit;
}

$activeLoans = $borrowService->getActiveLoans();
$students    = $borrowService->getAllStudents();
$bookRepo    = new BookRepository();
$availBooks  = array_filter($bookRepo->findAll(500), fn($b) => $b->available_copies > 0);

$pageTitle  = 'Issue Books';
$activePage = 'borrow';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_librarian.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">📤 Issue Books</span>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:340px 1fr;gap:20px">

      <!-- Issue form -->
      <div class="card" style="align-self:start">
        <div class="card-header"><h2 class="card-title">📤 Issue a Book</h2></div>
        <form method="POST">
          <div class="form-group">
            <label class="form-label">Select Book *</label>
            <select name="book_id" class="form-control" required>
              <option value="">-- Select available book --</option>
              <?php foreach ($availBooks as $book): ?>
              <option value="<?= $book->id ?>">
                <?= Helper::e($book->title) ?> (<?= Helper::e($book->author) ?>) — <?= $book->available_copies ?> left
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Select Student *</label>
            <select name="student_id" class="form-control" required>
              <option value="">-- Select student --</option>
              <?php foreach ($students as $s): ?>
              <option value="<?= $s->id ?>"><?= Helper::e($s->full_name) ?> (<?= Helper::e($s->username) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Issue Date</label>
            <input type="text" class="form-control" value="<?= date('d M Y') ?>" disabled>
          </div>
          <div class="form-group">
            <label class="form-label">Due Date (14 days)</label>
            <input type="text" class="form-control"
                   value="<?= date('d M Y', strtotime('+14 days')) ?>" disabled>
          </div>
          <button type="submit" class="btn btn-primary w-100" style="justify-content:center">
            ✅ Issue Book
          </button>
        </form>
      </div>

      <!-- Active loans list -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Active Loans (<?= count($activeLoans) ?>)</h2>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Book</th><th>Student</th><th>Issue Date</th><th>Due Date</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php if (empty($activeLoans)): ?>
              <tr><td colspan="6" class="text-center text-muted" style="padding:24px">No active loans.</td></tr>
            <?php else: ?>
            <?php foreach ($activeLoans as $i => $loan): ?>
              <?php $isOverdue = $loan->isOverdue(); ?>
              <tr <?= $isOverdue ? 'data-overdue="1"' : '' ?>>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td><?= Helper::e($loan->book_title) ?></td>
                <td><?= Helper::e($loan->student_name) ?></td>
                <td class="text-muted"><?= Helper::e(Helper::formatDate($loan->issue_date)) ?></td>
                <td class="text-muted"><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
                <td>
                  <?php if ($isOverdue): ?>
                    <span class="badge badge-overdue">Overdue</span>
                  <?php else: ?>
                    <span class="badge badge-active">Active</span>
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
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>
