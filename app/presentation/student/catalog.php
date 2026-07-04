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

$_SESSION['role'] = 'student';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <div>
    <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
      Browse University Catalog
    </h1>
    <p class="text-xs text-slate-500 font-medium">Browse, search, and reserve physical learning resources. Reserved items can be collected from the head librarian desk.</p>
  </div>

  <!-- Flash Updates System Feedbacks Alert Banner -->
  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <!-- Search & Category Filters Wrapper Bar -->
  <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm">
    <form method="GET" action="catalog.php" class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <div class="relative max-w-md w-full">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
          <i data-lucide="search" class="w-4 h-4"></i>
        </div>
        <input
          name="q"
          type="text"
          placeholder="Search catalog by title, author, or ISBN..."
          value="<?= Helper::e($searchQuery) ?>"
          class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 text-xs md:text-sm text-slate-900 dark:text-white rounded-xl border border-slate-200/80 dark:border-slate-850/80 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-400"
        />
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-850/80 px-2.5 py-1.5 rounded-xl">
          <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-400"></i>
          <select name="category" class="bg-transparent border-none text-xs font-semibold text-slate-600 dark:text-slate-300 focus:outline-none cursor-pointer">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= Helper::e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= Helper::e($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition shadow-sm cursor-pointer">
          Search
        </button>
        <?php if ($searchQuery || $category): ?>
          <a href="catalog.php" class="px-4 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition text-center no-underline">Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Book Assets Display Grid Box -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($books)): ?>
      <div class="col-span-full bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl py-16 text-center text-slate-400">
        <i data-lucide="alert-circle" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
        No catalog matches found inside database records.
      </div>
    <?php else: ?>
      <?php foreach ($books as $i => $book): ?>
        <?php
          // Assign visual cover color strings dynamically based on hash code distributions
          $colors = ['from-blue-600 to-indigo-800', 'from-cyan-600 to-teal-800', 'from-slate-800 to-neutral-950', 'from-emerald-600 to-teal-900', 'from-violet-600 to-purple-900'];
          $coverColor = $colors[abs(crc32($book->category ?? 'default')) % count($colors)];
          $isAvail = $book->available_copies > 0;
        ?>
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm flex gap-4 hover:shadow-md transition-shadow">
          
          <!-- Visual Vector Card Cover Component representation -->
          <div class="rounded-lg bg-gradient-to-br <?= $coverColor ?> text-white shadow-md relative overflow-hidden flex flex-col justify-between p-3 shrink-0 w-28 h-40 font-sans">
            <div class="absolute top-0 left-1 w-px h-full bg-white/20"></div>
            <div class="absolute top-0 left-2.5 w-1.5 h-full bg-black/10"></div>
            <div class="z-10 text-left"><i data-lucide="bookmark" class="text-amber-400/80 fill-amber-400/25 w-5 h-5"></i></div>
            <div class="z-10 text-left">
              <span class="font-display font-extrabold block leading-tight text-[11px] tracking-tight line-clamp-3 text-ellipsis overflow-hidden"><?= Helper::e($book->title) ?></span>
              <span class="text-slate-200/80 text-[9px] mt-1 block truncate">By <?= Helper::e($book->author) ?></span>
            </div>
          </div>

          <!-- Description and Interaction details block -->
          <div class="flex-1 flex flex-col justify-between min-w-0">
            <div>
              <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider block"><?= Helper::e($book->category) ?></span>
              <h3 class="font-display font-bold text-sm text-slate-900 dark:text-white mt-1 leading-tight block truncate" title="<?= Helper::e($book->title) ?>">
                <?= Helper::e($book->title) ?>
              </h3>
              <span class="text-slate-500 text-xs mt-0.5 block truncate">By <?= Helper::e($book->author) ?></span>
              <?php if ($book->isbn): ?>
                <span class="text-[10px] font-mono text-slate-400 block mt-1.5">ISBN: <?= Helper::e($book->isbn) ?></span>
              <?php endif; ?>
            </div>

            <div class="pt-2.5 border-t border-slate-50 dark:border-slate-850">
              <div class="flex justify-between items-center text-xs">
                <span class="text-slate-400">Inventory Allocation:</span>
                <span class="font-semibold text-slate-700 dark:text-slate-300"><?= $book->available_copies ?> / <?= $book->total_copies ?></span>
              </div>
              
              <div class="mt-3 flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border <?= $isAvail ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 border-emerald-500/20' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 border-rose-500/20' ?>">
                  <span class="w-1 h-1 rounded-full <?= $isAvail ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                  <?= $isAvail ? 'In Stock' : 'Out of Stock' ?>
                </span>
                
                <?php if (!$book->isAvailable()): ?>
                  <form method="POST" action="catalog.php" onsubmit="return confirm('Place hold reservation request for \'<?= addslashes(Helper::e($book->title)) ?>\'?');">
                    <input type="hidden" name="book_id" value="<?= $book->id ?>">
                    <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition shadow-xs cursor-pointer">
                      Reserve Asset
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-slate-400 text-[10px] font-medium pr-1">Visit Desk to Borrow</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>