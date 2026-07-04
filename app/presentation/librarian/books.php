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

$_SESSION['role'] = 'librarian';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <!-- Header Title Section -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
        General Academic Book Catalog
      </h1>
      <p class="text-xs text-slate-500">Edit stock counts, categorize classifications, and check resource availability ratings.</p>
    </div>
    <button
      onclick="toggleModal('modal-add', true)"
      class="px-4.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs md:text-sm font-semibold rounded-xl shadow-md transition flex items-center gap-1.5 self-start cursor-pointer"
    >
      <i data-lucide="plus" class="w-4.5 h-4.5"></i> Add Physical Book
    </button>
  </div>

  <!-- Session Validation Alerts Box -->
  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-triangle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <!-- Catalog Metrics Cards Row Grid -->
  <div class="grid grid-cols-2 gap-4 max-w-md">
    <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-blue-500 rounded-xl shadow-sm">
      <span class="text-slate-400 text-[10px] font-semibold uppercase tracking-wider block">Total Books</span>
      <span class="text-lg md:text-xl font-display font-bold text-slate-800 dark:text-white block mt-1"><?= $stats['total'] ?></span>
    </div>
    <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 border-l-4 border-emerald-500 rounded-xl shadow-sm">
      <span class="text-slate-400 text-[10px] font-semibold uppercase tracking-wider block">Available</span>
      <span class="text-lg md:text-xl font-display font-bold text-slate-800 dark:text-white block mt-1"><?= $stats['available'] ?></span>
    </div>
  </div>

  <!-- Search & Filter Controls Panel -->
  <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm animate-fade-in">
    <form method="GET" action="books.php" class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <div class="relative max-w-md w-full">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
          <i data-lucide="search" class="w-4 h-4"></i>
        </div>
        <input
          name="q"
          type="text"
          placeholder="Search catalog by title, author, ISBN..."
          value="<?= Helper::e($searchQuery) ?>"
          class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 text-xs md:text-sm text-slate-900 dark:text-white rounded-xl border border-slate-200/80 dark:border-slate-850/80 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-400"
        />
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-850/80 px-2.5 py-1.5 rounded-xl">
          <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-400"></i>
          <select name="category" class="bg-transparent border-none text-xs font-semibold text-slate-600 dark:text-slate-300 focus:outline-none cursor-pointer">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= Helper::e($cat) ?>" <?= $categoryFilter === $cat ? 'selected' : '' ?>><?= Helper::e($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-sm cursor-pointer">
          Filter
        </button>
        <?php if ($searchQuery || $categoryFilter): ?>
          <a href="books.php" class="px-4 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition text-center no-underline">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Book Inventory DataTable Layout -->
  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
            <th class="py-3 px-5 text-center w-16">No</th>
            <th class="py-3 px-4">Title</th>
            <th class="py-3 px-4">Author</th>
            <th class="py-3 px-4">Standard ISBN</th>
            <th class="py-3 px-4">Category</th>
            <th class="py-3 px-4 text-center">Total Stock</th>
            <th class="py-3 px-4 text-center">Available</th>
            <th class="py-3 px-5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
          <?php if (empty($books)): ?>
            <tr>
              <td colSpan="8" class="py-12 text-center text-slate-400">
                <i data-lucide="book-open" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                No catalog items found matching selection parameters.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($books as $i => $book): ?>
              <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <td class="py-3 px-5 text-center font-mono text-slate-400 font-semibold"><?= $i + 1 ?></td>
                <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-100"><?= Helper::e($book->title) ?></td>
                <td class="py-3 px-4 text-slate-500 dark:text-slate-400"><?= Helper::e($book->author) ?></td>
                <td class="py-3 px-4 font-mono text-xs text-slate-400"><?= Helper::e($book->isbn ?: '—') ?></td>
                <td class="py-3 px-4">
                  <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-150 text-slate-600 dark:bg-slate-800 dark:text-slate-400 uppercase tracking-wide border border-transparent"><?= Helper::e($book->category) ?></span>
                </td>
                <td class="py-3 px-4 text-center font-semibold"><?= $book->total_copies ?></td>
                <td class="py-3 px-4 text-center">
                  <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border <?= $book->available_copies > 0 ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-500/20' ?>">
                    <?= $book->available_copies ?> available
                  </span>
                </td>
                <td class="py-3 px-5 text-right space-x-1.5 whitespace-nowrap">
                  <button
                    onclick="fillEditModal(<?= htmlspecialchars(json_encode([
                      'book_id' => $book->id,
                      'title' => $book->title,
                      'author' => $book->author,
                      'isbn' => $book->isbn,
                      'category' => $book->category,
                      'total_copies' => $book->total_copies
                    ])) ?>)"
                    class="p-1.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-600 dark:bg-amber-950/20 dark:border-amber-900/30 rounded-lg transition-all cursor-pointer"
                    title="Edit Catalog Configuration"
                  >
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </button>
                  <form method="POST" action="books.php" onsubmit="return confirm('Delete \'<?= Helper::e($book->title) ?>\' from database?');" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="book_id" value="<?= $book->id ?>">
                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 rounded-lg transition-all cursor-pointer" title="Delete Asset">
                      <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
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

<!-- Add Book Modal Framework Window -->
<div id="modal-add" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full shadow-2xl p-6 md:p-8">
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-2">
        <i data-lucide="book-open" class="w-5 h-5 text-emerald-500"></i>
        <h3 class="font-display font-bold text-base md:text-lg text-slate-900 dark:text-white">Add Book Asset</h3>
      </div>
      <button onclick="toggleModal('modal-add', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
    </div>

    <form method="POST" action="books.php" class="space-y-4">
      <input type="hidden" name="action" value="add">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Book Title *</label>
        <input name="title" type="text" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none text-slate-900 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Author Name *</label>
        <input name="author" type="text" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none text-slate-900 dark:text-white" />
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">ISBN</label>
          <input name="isbn" type="text" placeholder="978-..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none font-mono text-slate-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Category Classification *</label>
          <input name="category" type="text" list="category-list" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none font-semibold text-slate-700 dark:text-slate-300" />
          <datalist id="category-list">
            <?php foreach ($categories as $cat): ?><option value="<?= Helper::e($cat) ?>"><?php endforeach; ?>
          </datalist>
        </div>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Total Allocation Copies *</label>
        <input name="total_copies" type="number" value="1" min="1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none font-mono text-slate-900 dark:text-white" />
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 mt-5">
        <button type="button" onclick="toggleModal('modal-add', false)" class="px-4 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200 cursor-pointer">Cancel</button>
        <button type="submit" class="px-4.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md cursor-pointer">Add Book</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Book Modal Framework Window -->
<div id="modal-edit" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full shadow-2xl p-6 md:p-8">
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-2">
        <i data-lucide="edit-3" class="w-5 h-5 text-amber-500"></i>
        <h3 class="font-display font-bold text-base md:text-lg text-slate-900 dark:text-white">Modify Catalog Asset</h3>
      </div>
      <button onclick="toggleModal('modal-edit', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
    </div>

    <form method="POST" action="books.php" class="space-y-4">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="book_id" id="edit-book-id">
      
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Book Title *</label>
        <input name="title" id="edit-title" type="text" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none text-slate-900 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Author Name *</label>
        <input name="author" id="edit-author" type="text" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none text-slate-900 dark:text-white" />
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">ISBN</label>
          <input name="isbn" id="edit-isbn" type="text" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none font-mono text-slate-900 dark:text-white" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Category *</label>
          <input name="category" id="edit-category" type="text" list="category-list" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none font-semibold text-slate-700 dark:text-slate-300" />
        </div>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Total Copies *</label>
        <input name="total_copies" id="edit-total-copies" type="number" min="1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-emerald-500 outline-none font-mono text-slate-900 dark:text-white" />
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 mt-5">
        <button type="button" onclick="toggleModal('modal-edit', false)" class="px-4 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200 cursor-pointer">Cancel</button>
        <button type="submit" class="px-4.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-md cursor-pointer">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  function toggleModal(id, show) {
    document.getElementById(id).classList.toggle('hidden', !show);
  }
  function fillEditModal(data) {
    document.getElementById('edit-book-id').value = data.book_id;
    document.getElementById('edit-title').value = data.title;
    document.getElementById('edit-author').value = data.author;
    document.getElementById('edit-isbn').value = data.isbn;
    document.getElementById('edit-category').value = data.category;
    document.getElementById('edit-total-copies').value = data.total_copies;
    toggleModal('modal-edit', true);
  }
</script>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>