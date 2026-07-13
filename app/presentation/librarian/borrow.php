<?php
// ============================================================
// ULMS — Issue Books (Borrowing) with AJAX Auto-Complete Search
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
require_once ROOT_URL . '/app/business/services/BookService.php';
require_once ROOT_URL . '/app/business/services/UserService.php';

Session::requireLogin('librarian');

// ============================================================
// LIGHTWEIGHT LIVE-SEARCH ENDPOINT SECTION
// ============================================================
if (isset($_GET['ajax_search'])) {
    header('Content-Type: application/json');
    $query = trim($_GET['q'] ?? '');
    
    if (strlen($query) < 1) {
        echo json_encode([]);
        exit;
    }

    if ($_GET['ajax_search'] === 'books') {
        $bookService = new BookService();
        $books = $bookService->searchBooks($query);
        $results = [];
        foreach ($books as $b) {
            if ($b->available_copies > 0) {
                $results[] = [
                    'id' => $b->id,
                    'text' => Helper::e($b->title) . ' (' . Helper::e($b->author) . ') — [' . $b->available_copies . ' available]'
                ];
            }
        }
        echo json_encode($results);
        exit;
    }

    if ($_GET['ajax_search'] === 'students') {
        $userService = new UserService();
        $students = $userService->searchUsers($query, 'student');
        $results = [];
        foreach ($students as $s) {
            $results[] = [
                'id' => $s->id,
                'text' => Helper::e($s->full_name) . ' (@' . Helper::e($s->username) . ')'
            ];
        }
        echo json_encode($results);
        exit;
    }
}

// ============================================================
// STANDARD CORE SYSTEM FORM ACTION SUBMISSION
// ============================================================
$borrowService = new BorrowService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $borrowService->issueBook(
        [
            'book_id' => $_POST['book_id'] ?? 0, 
            'student_id' => $_POST['student_id'] ?? 0,
            'issue_date' => $_POST['issue_date'] ?? date('Y-m-d'),
            'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days'))
        ],
        Session::getUserId()
    );
    Session::flash($result['success'] ? 'success' : 'error', $result['message']);
    header('Location: borrow.php');
    exit;
}

$activeLoans = $borrowService->getActiveLoans();

$pageTitle  = 'Issue Books';
$activePage = 'borrow';
$flash      = Session::getFlash();

$_SESSION['role'] = 'librarian';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <div>
    <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
      Issue Physical Catalog Loans
    </h1>
    <p class="text-xs text-slate-500 font-medium">Link student identities with catalog resources. Due schedules can be manually customized if needed.</p>
  </div>

  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'file-check' : 'alert-circle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-5 rounded-2xl shadow-sm space-y-4">
      <div class="border-b border-slate-50 dark:border-slate-850 pb-2">
        <h2 class="font-display font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
          <i data-lucide="bookmark-plus" class="w-4.5 h-4.5 text-emerald-500"></i> New Catalog Loan
        </h2>
      </div>

      <form method="POST" action="borrow.php" class="space-y-4" autocomplete="off">
        
        <div class="relative">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Search Catalog Book *</label>
          <input type="text" id="book_search" placeholder="Type title, author, or ISBN..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-700 dark:text-slate-300">
          <input type="hidden" name="book_id" id="book_id" required>
          <div id="book_results" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-50 hidden divide-y divide-slate-100 dark:divide-slate-800/50"></div>
        </div>

        <div class="relative">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Search Target Student *</label>
          <input type="text" id="student_search" placeholder="Type name or username..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-700 dark:text-slate-300">
          <input type="hidden" name="student_id" id="student_id" required>
          <div id="student_results" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl z-50 hidden divide-y divide-slate-100 dark:divide-slate-800/50"></div>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
          <div>
            <label for="issue_date" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Issue Date</label>
            <input type="date" id="issue_date" name="issue_date" value="<?= date('Y-m-d') ?>" class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 rounded-lg border border-slate-200 dark:border-slate-800 text-center outline-none focus:border-blue-500" />
          </div>
          <div>
            <label for="due_date" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Due Date</label>
            <input type="date" id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" class="w-full px-2 py-1.5 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 rounded-lg border border-slate-200 dark:border-slate-800 text-center outline-none focus:border-blue-500" />
          </div>
        </div>

        <div class="p-3 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 rounded-xl text-[11px] text-blue-700 dark:text-blue-300 leading-normal">
          Loans allocate stock copies for 14 continuous days. Late turn-ins assess a <strong class="font-mono">Rs. 100.00 per-day penalty fee</strong> automatically.
        </div>

        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-md cursor-pointer transition-all flex items-center justify-center gap-1.5">
          <i data-lucide="check-circle" class="w-4 h-4"></i> Approve & Issue Loan
        </button>
      </form>
    </div>

    <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
      
      <div class="p-4 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-100 dark:border-slate-850/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h2 class="font-display font-bold text-sm text-slate-800 dark:text-white">Active Borrowing Allocations Ledger</h2>
        <div class="relative flex items-center max-w-xs w-full">
          <div class="absolute left-3 text-slate-400 pointer-events-none">
            <i data-lucide="search" class="w-4 h-4"></i>
          </div>
          <input type="text" id="ledger_search" placeholder="Search book or student name..." class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-slate-950 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-700 dark:text-slate-300 focus:border-blue-500">
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse" id="ledger_table">
          <thead>
            <tr class="border-b border-slate-100 dark:border-slate-850 text-slate-400 text-[10px] font-bold uppercase tracking-wider bg-slate-50/20 dark:bg-slate-950/10">
              <th class="py-3 px-4 text-center w-12">No</th>
              <th class="py-3 px-4">Book Asset Title</th>
              <th class="py-3 px-4">Student Name</th>
              <th class="py-3 px-4">Issue Date</th>
              <th class="py-3 px-4">Due Date</th>
              <th class="py-3 px-4 text-right">Circulation Clearance</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
            <?php if (empty($activeLoans)): ?>
              <tr id="no_records_row">
                <td colSpan="6" class="py-12 text-center text-slate-400 font-medium">
                  <i data-lucide="book-open" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                  No active resource loans currently out on external circulation.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($activeLoans as $i => $loan): ?>
                <?php $isOverdue = $loan->isOverdue(); ?>
                <tr class="ledger-row hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                  <td class="py-3 px-4 text-center font-mono text-slate-400 font-semibold row-number"><?= $i + 1 ?></td>
                  <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200 search-book">"<?= Helper::e($loan->book_title) ?>"</td>
                  <td class="py-3 px-4 text-slate-600 dark:text-slate-400 font-medium search-student"><?= Helper::e($loan->student_name) ?></td>
                  <td class="py-3 px-4 font-mono text-slate-400 text-xs"><?= Helper::e(Helper::formatDate($loan->issue_date)) ?></td>
                  <td class="py-3 px-4 font-mono text-slate-400 text-xs"><?= Helper::e(Helper::formatDate($loan->due_date)) ?></td>
                  <td class="py-3 px-4 text-right">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border <?= $isOverdue ? 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-500/10' : 'bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 border-indigo-500/10' ?>">
                      <span class="w-1.5 h-1.5 rounded-full <?= $isOverdue ? 'bg-rose-500 animate-pulse' : 'bg-indigo-500' ?>"></span>
                      <?= $isOverdue ? 'Overdue Allocation' : 'Clear Allocation' ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
              <tr id="ledger_empty_search_row" class="hidden">
                <td colSpan="6" class="py-12 text-center text-slate-400 font-medium">
                  <i data-lucide="search-code" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                  No matching active circulation records found.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
function initAutocomplete(inputId, resultsId, hiddenId, type) {
    const input = document.getElementById(inputId);
    const resultsContainer = document.getElementById(resultsId);
    const hiddenInput = document.getElementById(hiddenId);
    let debounceTimer;

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 1) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('hidden');
            hiddenInput.value = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`borrow.php?ajax_search=${type}&q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length === 0) {
                        const noResult = document.createElement('div');
                        noResult.className = 'p-3 text-xs text-slate-400 font-medium italic';
                        noResult.textContent = 'No records found...';
                        resultsContainer.appendChild(noResult);
                    } else {
                        data.forEach(item => {
                            const row = document.createElement('div');
                            row.className = 'p-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer transition-colors';
                            row.textContent = item.text;
                            row.addEventListener('click', () => {
                                input.value = item.text;
                                hiddenInput.value = item.id;
                                resultsContainer.innerHTML = '';
                                resultsContainer.classList.add('hidden');
                            });
                            resultsContainer.appendChild(row);
                        });
                    }
                    resultsContainer.classList.remove('hidden');
                });
        }, 200);
    });

    document.addEventListener('click', function(e) {
        if (e.target !== input && e.target !== resultsContainer) {
            resultsContainer.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initAutocomplete('book_search', 'book_results', 'book_id', 'books');
    initAutocomplete('student_search', 'student_results', 'student_id', 'students');
    
    document.getElementById('issue_date').addEventListener('change', function() {
        if(this.value) {
            const issueDate = new Date(this.value);
            issueDate.setDate(issueDate.getDate() + 14);
            
            const yyyy = issueDate.getFullYear();
            const mm = String(issueDate.getMonth() + 1).padStart(2, '0');
            const dd = String(issueDate.getDate()).padStart(2, '0');
            
            document.getElementById('due_date').value = `${yyyy}-${mm}-${dd}`;
        }
    });

    // ============================================================
    // LEDGER LIVE INTERACTIVE CLIENT FILTER ENGINE
    // ============================================================
    const ledgerSearch = document.getElementById('ledger_search');
    const rows = document.querySelectorAll('.ledger-row');
    const emptySearchRow = document.getElementById('ledger_empty_search_row');

    if (ledgerSearch) {
        ledgerSearch.addEventListener('input', filterLedgerRows);
        
        // Let hitting enter key also process or clean up values seamlessly
        ledgerSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterLedgerRows();
            }
        });
    }

    function filterLedgerRows() {
        const keyword = ledgerSearch.value.trim().toLowerCase();
        let dynamicVisibleIndex = 1;
        let matchesCount = 0;

        rows.forEach(row => {
            const bookText = row.querySelector('.search-book').textContent.toLowerCase();
            const studentText = row.querySelector('.search-student').textContent.toLowerCase();
            
            if (keyword === '' || bookText.includes(keyword) || studentText.includes(keyword)) {
                row.classList.remove('hidden');
                // Re-index row numbers dynamically to keep sequence perfect
                const numCell = row.querySelector('.row-number');
                if (numCell) numCell.textContent = dynamicVisibleIndex++;
                matchesCount++;
            } else {
                row.classList.add('hidden');
            }
        });

        // Toggle fallback "No records found" row if everything is filtered out
        if (emptySearchRow) {
            if (matchesCount === 0 && keyword !== '') {
                emptySearchRow.classList.remove('hidden');
            } else {
                emptySearchRow.classList.add('hidden');
            }
        }
    }
});
</script>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>