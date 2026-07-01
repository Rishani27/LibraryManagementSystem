<?php
// ============================================================
// ULMS — Book Catalog Management  (FR-05 — Member 2)
// presentation/librarian/books.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/Book.php';
require_once ROOT_URL . '/app/data/repositories/BookRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/validators/BookValidator.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/services/BookService.php';

Session::requireLogin('librarian');

$bookService = new BookService();

// ── POST actions ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $result = $bookService->addBook([
            'title'        => trim($_POST['title'] ?? ''),
            'author'       => trim($_POST['author'] ?? ''),
            'isbn'         => trim($_POST['isbn'] ?? ''),
            'category'     => trim($_POST['category'] ?? ''),
            'total_copies' => (int)($_POST['total_copies'] ?? 1),
        ]);
        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

    } elseif ($action === 'edit') {
        $id     = (int)($_POST['book_id'] ?? 0);
        $result = $bookService->updateBook($id, [
            'title'        => trim($_POST['title'] ?? ''),
            'author'       => trim($_POST['author'] ?? ''),
            'isbn'         => trim($_POST['isbn'] ?? ''),
            'category'     => trim($_POST['category'] ?? ''),
            'total_copies' => (int)($_POST['total_copies'] ?? 1),
        ]);
        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

    } elseif ($action === 'delete') {
        $id     = (int)($_POST['book_id'] ?? 0);
        $result = $bookService->deleteBook($id);
        Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    }

    header('Location: books.php');
    exit;
}

// ── Data ─────────────────────────────────────────────────
$searchQuery    = trim($_GET['q'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');

if ($searchQuery) {
    $books = $bookService->searchBooks($searchQuery, $categoryFilter ?: null);
} elseif ($categoryFilter) {
    $books = $bookService->filterByCategory($categoryFilter);
} else {
    $books = $bookService->getAllBooks(200);
}

$categories = $bookService->getAllCategories();
$stats       = $bookService->getBookStats();

$pageTitle  = 'Book Catalog';
$activePage = 'books';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_librarian.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">📖 Book Catalog</span>
    <button class="btn btn-primary btn-sm" onclick="openModal('modal-add')">+ Add Book</button>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:20px;max-width:400px">
      <div class="stat-card">
        <div class="stat-icon blue">📚</div>
        <div><div class="stat-value"><?= $stats['total'] ?></div><div class="stat-label">Total Books</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div><div class="stat-value"><?= $stats['available'] ?></div><div class="stat-label">Available</div></div>
      </div>
    </div>

    <!-- Search + filter -->
    <div class="card mb-3">
      <form method="GET" class="search-bar">
        <div class="search-input-wrap">
          <span class="search-icon">🔍</span>
          <input name="q" type="text" class="form-control search-input"
                 placeholder="Search by title, author, ISBN, or category…"
                 value="<?= Helper::e($searchQuery) ?>">
        </div>
        <select name="category" class="form-control" style="width:180px">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= Helper::e($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>>
            <?= Helper::e($cat) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($searchQuery || $categoryFilter): ?>
        <a href="books.php" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Books table -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Books (<?= count($books) ?>)</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Title</th><th>Author</th><th>ISBN</th><th>Category</th>
                <th>Total</th><th>Available</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php if (empty($books)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:32px">No books found.</td></tr>
          <?php else: ?>
          <?php foreach ($books as $i => $book): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><strong><?= Helper::e($book->title) ?></strong></td>
              <td><?= Helper::e($book->author) ?></td>
              <td class="text-muted"><?= Helper::e($book->isbn ?: '—') ?></td>
              <td><span class="badge badge-default"><?= Helper::e($book->category) ?></span></td>
              <td><?= $book->total_copies ?></td>
              <td>
                <span class="badge <?= $book->available_copies > 0 ? 'badge-active' : 'badge-overdue' ?>">
                  <?= $book->available_copies ?>
                </span>
              </td>
              <td style="display:flex;gap:6px">
                <button class="btn btn-warning btn-sm"
                  onclick="fillModal('modal-edit',{
                    book_id:'<?= $book->id ?>',title:'<?= addslashes($book->title) ?>',
                    author:'<?= addslashes($book->author) ?>',isbn:'<?= addslashes($book->isbn) ?>',
                    category:'<?= addslashes($book->category) ?>',total_copies:'<?= $book->total_copies ?>'
                  });openModal('modal-edit')">✏️ Edit</button>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="action"  value="delete">
                  <input type="hidden" name="book_id" value="<?= $book->id ?>">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="Delete '<?= Helper::e($book->title) ?>'?">🗑</button>
                </form>
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

<!-- Add Book Modal -->
<div class="modal-overlay" id="modal-add">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">➕ Add New Book</span>
      <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-group"><label class="form-label">Title *</label>
        <input name="title" type="text" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Author *</label>
        <input name="author" type="text" class="form-control" required></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">ISBN</label>
          <input name="isbn" type="text" class="form-control" placeholder="978-…"></div>
        <div class="form-group"><label class="form-label">Category *</label>
          <input name="category" type="text" class="form-control" list="category-list" required>
          <datalist id="category-list">
            <?php foreach ($categories as $cat): ?><option value="<?= Helper::e($cat) ?>"><?php endforeach; ?>
          </datalist>
        </div>
      </div>
      <div class="form-group"><label class="form-label">Total Copies *</label>
        <input name="total_copies" type="number" class="form-control" value="1" min="1" required></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-add')">Cancel</button>
        <button type="submit" class="btn btn-primary">✅ Add Book</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Book Modal -->
<div class="modal-overlay" id="modal-edit">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">✏️ Edit Book</span>
      <button class="modal-close" onclick="closeModal('modal-edit')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action"  value="edit">
      <input type="hidden" name="book_id" value="">
      <div class="form-group"><label class="form-label">Title *</label>
        <input name="title" type="text" class="form-control" required></div>
      <div class="form-group"><label class="form-label">Author *</label>
        <input name="author" type="text" class="form-control" required></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">ISBN</label>
          <input name="isbn" type="text" class="form-control"></div>
        <div class="form-group"><label class="form-label">Category *</label>
          <input name="category" type="text" class="form-control" list="category-list" required></div>
      </div>
      <div class="form-group"><label class="form-label">Total Copies *</label>
        <input name="total_copies" type="number" class="form-control" min="1" required></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit')">Cancel</button>
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>
