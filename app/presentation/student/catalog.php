<?php
// ============================================================
// ULMS — Student Book Catalog + Reservation  (FR-08)
// presentation/student/catalog.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Book.php';
require_once ROOT_URL . '/app/data/repositories/BookRepository.php';
require_once ROOT_URL . '/app/data/repositories/ReservationRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/ReservationService.php';

Session::requireLogin('student');

$resSvc    = new ReservationService();
$studentId = Session::getUserId();

// Handle reservation POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = (int)($_POST['book_id'] ?? 0);
    $result = $resSvc->reserve($bookId, $studentId);
    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: catalog.php');
    exit;
}

$bookRepo    = new BookRepository();
$searchQuery = trim($_GET['q'] ?? '');
$category    = trim($_GET['category'] ?? '');

$books = $searchQuery
    ? $bookRepo->search($searchQuery, $category ?: null)
    : ($category ? $bookRepo->filterByCategory($category) : $bookRepo->findAll(200));

$categories = $bookRepo->getAllCategories();

$pageTitle  = 'Browse Catalog';
$activePage = 'catalog';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_student.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">🔍 Browse Book Catalog</span>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Search -->
    <div class="card mb-3">
      <form method="GET" class="search-bar">
        <div class="search-input-wrap" style="flex:1">
          <span class="search-icon">🔍</span>
          <input name="q" type="text" class="form-control search-input"
                 placeholder="Search by title, author, ISBN…"
                 value="<?= Helper::e($searchQuery) ?>">
        </div>
        <select name="category" class="form-control" style="width:180px">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= Helper::e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= Helper::e($cat) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($searchQuery || $category): ?>
        <a href="catalog.php" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Books table -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Available Books (<?= count($books) ?>)</h2>
        <span class="text-muted" style="font-size:0.8rem">
          🟢 = Available &nbsp;&nbsp; 🔴 = Unavailable (you can reserve)
        </span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Title</th><th>Author</th><th>Category</th><th>Copies Available</th><th>Action</th></tr>
          </thead>
          <tbody>
          <?php if (empty($books)): ?>
            <tr><td colspan="6" class="text-center text-muted" style="padding:32px">No books found.</td></tr>
          <?php else: ?>
          <?php foreach ($books as $i => $book): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><strong><?= Helper::e($book->title) ?></strong>
                <?php if ($book->isbn): ?>
                <br><span class="text-muted" style="font-size:0.75rem">ISBN: <?= Helper::e($book->isbn) ?></span>
                <?php endif; ?>
              </td>
              <td><?= Helper::e($book->author) ?></td>
              <td><span class="badge badge-default"><?= Helper::e($book->category) ?></span></td>
              <td>
                <span class="badge <?= $book->available_copies > 0 ? 'badge-active' : 'badge-overdue' ?>">
                  <?= $book->available_copies ?> / <?= $book->total_copies ?>
                </span>
              </td>
              <td>
                <?php if (!$book->isAvailable()): ?>
                <form method="POST">
                  <input type="hidden" name="book_id" value="<?= $book->id ?>">
                  <button type="submit" class="btn btn-warning btn-sm">🔖 Reserve</button>
                </form>
                <?php else: ?>
                <span class="text-muted" style="font-size:0.8rem">Visit library to borrow</span>
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
