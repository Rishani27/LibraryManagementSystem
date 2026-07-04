<?php
// ============================================================
// ULMS — Admin User Management  (FR-03 — Member 1)
// presentation/admin/users.php
// ============================================================

define('ROOT_URL', '../../..');
require_once ROOT_URL . '/app/config/config.php';
require_once ROOT_URL . '/app/core/Session.php';
require_once ROOT_URL . '/app/core/Helper.php';
require_once ROOT_URL . '/app/core/Database.php';
require_once ROOT_URL . '/app/data/models/User.php';
require_once ROOT_URL . '/app/data/repositories/UserRepository.php';
require_once ROOT_URL . '/app/data/repositories/LogRepository.php';
require_once ROOT_URL . '/app/business/services/LogService.php';
require_once ROOT_URL . '/app/business/validators/UserValidator.php';
require_once ROOT_URL . '/app/business/services/UserService.php';

Session::requireLogin('admin');

$userService = new UserService();

// ── Handle POST actions ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $result = $userService->createUser([
            'username'  => trim($_POST['username'] ?? ''),
            'password'  => $_POST['password'] ?? '',
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'role'      => $_POST['role'] ?? '',
        ]);
        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

    } elseif ($action === 'delete') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId === Session::getUserId()) {
            Session::flash('error', 'You cannot delete your own account.');
        } else {
            $result = $userService->deleteUser($userId);
            Session::flash($result['success'] ? 'success' : 'error', $result['message']);
        }
    }

    header('Location: users.php');
    exit;
}

// ── Data ──────────────────────────────────────────────────
$searchQuery = trim($_GET['q'] ?? '');
$roleFilter  = $_GET['role'] ?? '';

$users = $searchQuery
    ? $userService->searchUsers($searchQuery, $roleFilter ?: null)
    : $userService->getAllUsers($roleFilter ?: null);

$stats      = $userService->getUserStats();
$pageTitle  = 'User Management';
$activePage = 'users';
$flash      = Session::getFlash();

$_SESSION['role'] = 'admin';
$_SESSION['user_name'] = Session::getFullName();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <!-- Header Title & Action Controls -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
        Academic Registrar Registry
      </h1>
      <p class="text-xs text-slate-500">Manage centralized library clearance, edit roles, and monitor status configurations.</p>
    </div>
    <button
      onclick="toggleModal('modal-create', true)"
      class="px-4.5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs md:text-sm font-semibold rounded-xl shadow-md transition flex items-center gap-1.5 self-start cursor-pointer"
    >
      <i data-lucide="plus" class="w-4.5 h-4.5"></i> Add New User Registry
    </button>
  </div>

  <!-- Session Validation Alerts Box -->
  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-triangle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <!-- Registry Metrics Cards Row Grid -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php 
    $statMap = [
      ['Total Enrolled Users', $stats['total'], 'border-blue-500/20'],
      ['Administrators', $stats['admins'], 'border-indigo-500/20'],
      ['University Librarians', $stats['librarians'], 'border-emerald-500/20'],
      ['Enrolled Students', $stats['students'], 'border-purple-500/20']
    ];
    foreach ($statMap as [$label, $val, $borderBorder]): 
    ?>
      <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 border-l-4 rounded-xl shadow-sm <?= $borderBorder ?>">
        <span class="text-slate-400 text-[10px] font-semibold uppercase tracking-wider block"><?= $label ?></span>
        <span class="text-lg md:text-xl font-display font-bold text-slate-800 dark:text-white block mt-1"><?= $val ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Unified Search and Filters Bar Section -->
  <div class="bg-white dark:bg-slate-900 p-4 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm">
    <form method="GET" action="users.php" class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
      <div class="relative max-w-md w-full">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
          <i data-lucide="search" class="w-4 h-4"></i>
        </div>
        <input
          name="q"
          type="text"
          placeholder="Search registry by name, email, or username..."
          value="<?= Helper::e($searchQuery) ?>"
          class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 text-xs md:text-sm text-slate-900 dark:text-white rounded-xl border border-slate-200/80 dark:border-slate-850/80 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400"
        />
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-850/80 px-2.5 py-1.5 rounded-xl">
          <i data-lucide="filter" class="w-3.5 h-3.5 text-slate-400"></i>
          <select name="role" class="bg-transparent border-none text-xs font-semibold text-slate-600 dark:text-slate-300 focus:outline-none cursor-pointer">
            <option value="">All Roles</option>
            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="librarian" <?= $roleFilter === 'librarian' ? 'selected' : '' ?>>Librarian</option>
            <option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>Student</option>
          </select>
        </div>

        <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition shadow-sm cursor-pointer">
          Filter
        </button>
        <?php if ($searchQuery || $roleFilter): ?>
          <a href="users.php" class="px-4 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg transition text-center no-underline">
            Clear
          </a>
          <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Main User Accounts Database Registry Layout Table -->
  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden animate-fade-in">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
            <th class="py-3 px-5 text-center w-16">No</th>
            <th class="py-3 px-4">Full Name</th>
            <th class="py-3 px-4">Username</th>
            <th class="py-3 px-4">Email Address</th>
            <th class="py-3 px-4">Role Badge</th>
            <th class="py-3 px-4">Clearance</th>
            <th class="py-3 px-4">Created Date</th>
            <th class="py-3 px-5 text-right">Moderator Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
          <?php if (empty($users)): ?>
            <tr>
              <td colSpan="8" class="py-12 text-center text-slate-400">
                <i data-lucide="shield-alert" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                No operational users found inside registry matching selection parameters.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $i => $user): ?>
              <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <td class="py-3 px-5 text-center font-mono text-slate-400 font-semibold"><?= $i + 1 ?></td>
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8.5 h-8.5 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-display font-bold text-slate-700 dark:text-slate-300 uppercase">
                      <?= strtoupper(substr($user->full_name, 0, 2)) ?>
                    </div>
                    <span class="font-semibold text-slate-800 dark:text-slate-100 block"><?= Helper::e($user->full_name) ?></span>
                  </div>
                </td>
                <td class="py-3 px-4 font-mono text-xs text-slate-600 dark:text-slate-300">@<?= Helper::e($user->username) ?></td>
                <td class="py-3 px-4 text-slate-500 dark:text-slate-400"><?= Helper::e($user->email) ?></td>
                <td class="py-3 px-4">
                  <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border <?= $user->role === 'admin' ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 border-blue-500/20' : ($user->role === 'librarian' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' : 'bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 dark:text-indigo-400 border-indigo-500/20') ?>">
                    <?= Helper::e($user->role) ?>
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border <?= $user->is_active ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-500/10' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-500/10' ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                    <?= $user->is_active ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td class="py-3 px-4 font-mono text-slate-400 text-xs"><?= Helper::e(Helper::formatDate($user->created_at)) ?></td>
                <td class="py-3 px-5 text-right whitespace-nowrap">
                  <?php if ($user->id !== Session::getUserId() && $user->role !== 'admin'): ?>
                    <form method="POST" action="users.php" onsubmit="return confirm('Remove user account \'<?= Helper::e($user->username) ?>\' permanently?');" class="inline">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="user_id" value="<?= $user->id ?>">
                      <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200/50 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 rounded-lg transition-all cursor-pointer" title="Delete Account Matrix">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-slate-400 text-xs block pr-3 font-medium">—</span>
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

<!-- Drop-in Premium Visual Modal Creation Framework Wrapper -->
<div id="modal-create" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full shadow-2xl p-6 md:p-8">
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-2">
        <i data-lucide="users" class="w-5 h-5 text-blue-500"></i>
        <h3 class="font-display font-bold text-base md:text-lg text-slate-900 dark:text-white">Add New Academic User</h3>
      </div>
      <button onclick="toggleModal('modal-create', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
    </div>

    <form method="POST" action="users.php" class="space-y-4">
      <input type="hidden" name="action" value="create">
      
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Full Name *</label>
        <input name="full_name" type="text" required placeholder="John Smith" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-blue-500 outline-none text-slate-900 dark:text-white" />
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Username *</label>
        <input name="username" type="text" required placeholder="john_smith" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-blue-500 outline-none text-slate-900 dark:text-white" />
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Email *</label>
        <input name="email" type="email" required placeholder="john@university.edu" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-blue-500 outline-none text-slate-900 dark:text-white" />
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Password *</label>
        <input name="password" type="password" required placeholder="Min 6 characters" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-blue-500 outline-none text-slate-900 dark:text-white" />
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Access Role Profile *</label>
        <select name="role" required class="w-full px-3 py-2.5 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 focus:border-blue-500 outline-none font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
          <option value="">Select role…</option>
          <option value="librarian">Librarian</option>
          <option value="student">Student</option>
        </select>
      </div>

      <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 mt-5">
        <button type="button" onclick="toggleModal('modal-create', false)" class="px-4 py-2 text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200 cursor-pointer">Cancel</button>
        <button type="submit" class="px-4.5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-md cursor-pointer">Create User</button>
      </div>
    </form>
  </div>
</div>

<script>
  function toggleModal(modalId, show) {
      const modal = document.getElementById(modalId);
      if(show) {
          modal.classList.remove('hidden');
      } else {
          modal.classList.add('hidden');
      }
  }
</script>

<?php 
include ROOT_URL . '/app/includes/footer.php'; 
?>