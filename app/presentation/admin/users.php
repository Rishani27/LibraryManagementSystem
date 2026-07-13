<?php
// ============================================================
// ULMS — Admin User Management + Clearance & Edit Engine
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

// ── Handle Action Workflow Processing Actions ─────────────────
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

    } elseif ($action === 'approve_student') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $result = $userService->approveStudentRegistration($userId);
        Session::flash($result['success'] ? 'success' : 'error', $result['message']);

    } elseif ($action === 'update') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $result = $userService->modifyUserDetails($userId, [
            'full_name' => $_POST['full_name'] ?? '',
            'email'     => $_POST['email'] ?? '',
            'is_active' => (int)($_POST['is_active'] ?? 1)
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

// ── Data Query Routing Segments ─────────────────────────────
$searchQuery  = trim($_GET['q'] ?? '');
$roleFilter   = $_GET['role'] ?? '';
$viewApproval = ($_GET['tab'] ?? '') === 'pending';

$allUsersList = $searchQuery ? $userService->searchUsers($searchQuery, $roleFilter ?: null) : $userService->getAllUsers($roleFilter ?: null);

$users = [];
$pendingApprovals = [];

foreach ($allUsersList as $u) {
    if (!$u->is_active && $u->role === 'student' && strpos($u->email, '##FAC:') !== false) {
        $pendingApprovals[] = $u;
    } else {
        if (!$viewApproval) {
            $users[] = $u;
        }
    }
}

if ($viewApproval) {
    $users = $pendingApprovals;
}

$stats      = $userService->getUserStats();
$pageTitle  = 'User Management';
$activePage = 'users';
$flash      = Session::getFlash();

include ROOT_URL . '/app/includes/header.php';
?>

<div class="space-y-6">
  
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl md:text-2xl font-display font-bold text-slate-900 dark:text-white">
        Academic Registrar Registry
      </h1>
      <p class="text-xs text-slate-500">Manage centralized library clearance, edit roles, and approve student registration matrices.</p>
    </div>
    <button onclick="toggleModal('modal-create', true)" class="px-4.5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs md:text-sm font-semibold rounded-xl shadow-md transition flex items-center gap-1.5 self-start cursor-pointer">
      <i data-lucide="plus" class="w-4.5 h-4.5"></i> Add New User Registry
    </button>
  </div>

  <?php if ($flash['message']): ?>
    <div class="p-4 rounded-2xl border text-xs font-medium flex items-center gap-2.5 <?= $flash['type'] === 'success' ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/30 text-rose-700 dark:text-rose-400' ?>">
      <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-triangle' ?>" class="w-5 h-5 shrink-0"></i>
      <span><?= Helper::e($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <div class="p-1 bg-slate-100 dark:bg-slate-950 border border-slate-200/30 dark:border-slate-850/40 rounded-xl flex max-w-sm shadow-xs font-semibold text-xs">
    <a href="users.php" class="flex-1 text-center py-2 px-3 rounded-lg transition-all no-underline <?= !$viewApproval ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-800' ?>">Active Members</a>
    <a href="?tab=pending" class="flex-1 text-center py-2 px-3 rounded-lg transition-all no-underline relative <?= $viewApproval ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-500 hover:text-slate-800' ?>">
      Pending Approvals Queue
      <?php if (count($pendingApprovals) > 0): ?>
        <span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
      <?php endif; ?>
    </a>
  </div>

  <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-200/50 dark:border-slate-850/50 text-slate-400 text-xs font-bold uppercase tracking-wider">
            <th class="py-3 px-5 text-center w-12">No</th>
            <th class="py-3 px-4">Full Name</th>
            <th class="py-3 px-4">University Index / Username</th>
            <th class="py-3 px-4">Email Address</th>
            <th class="py-3 px-4">Allocation Track</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-5 text-right">Operational Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-xs md:text-sm">
          <?php if (empty($users)): ?>
            <tr>
              <td colSpan="7" class="py-12 text-center text-slate-400">
                <i data-lucide="shield-alert" class="w-12 h-12 text-slate-300 mx-auto mb-2 block"></i>
                No accounts tracked inside this segment.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $i => $user): ?>
              <?php 
                $displayEmail = $user->email;
                $trackLabel = $user->role;
                if (strpos($user->email, '##FAC:') !== false) {
                    $ex = explode('##FAC:', $user->email);
                    $displayEmail  = $ex[0];
                    $meta = explode('##CRS:', $ex[1]);
                    $trackLabel = $meta[0] . ' (' . $meta[1] . ')';
                }
              ?>
              <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-850/10 transition-colors">
                <td class="py-3 px-5 text-center font-mono text-slate-400 font-semibold"><?= $i + 1 ?></td>
                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100"><?= Helper::e($user->full_name) ?></td>
                <td class="py-3 px-4 font-mono text-xs text-slate-600 dark:text-slate-300">@<?= Helper::e($user->username) ?></td>
                <td class="py-3 px-4 text-slate-500 dark:text-slate-400"><?= Helper::e($displayEmail) ?></td>
                <td class="py-3 px-4">
                  <span class="inline-block text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase border bg-indigo-50 dark:bg-indigo-950/20 text-indigo-600 border-indigo-500/20">
                    <?= Helper::e($trackLabel) ?>
                  </span>
                </td>
                <td class="py-3 px-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border <?= $user->is_active ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 border-emerald-500/10' : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 border-rose-500/10' ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' ?>"></span>
                    <?= $user->is_active ? 'Active' : 'Pending Action' ?>
                  </span>
                </td>
                <td class="py-3 px-5 text-right whitespace-nowrap">
                  <div class="inline-flex items-center gap-1.5">
                    <?php if ($viewApproval): ?>
                      <form method="POST" action="users.php">
                        <input type="hidden" name="action" value="approve_student">
                        <input type="hidden" name="user_id" value="<?= $user->id ?>">
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm cursor-pointer flex items-center gap-1">
                          <i data-lucide="check-square" class="w-3.5 h-3.5"></i> Approve Student Index
                        </button>
                      </form>
                    <?php else: ?>
                      <button onclick="openEditModal(<?= $user->id ?>, '<?= addslashes(Helper::e($user->full_name)) ?>', '<?= addslashes(Helper::e($displayEmail)) ?>', <?= $user->is_active ?>)" class="p-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-600 dark:text-slate-300 rounded-lg transition border border-transparent cursor-pointer" title="Edit User">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                      </button>
                      
                      <?php if ($user->id !== Session::getUserId() && $user->role !== 'admin'): ?>
                        <form method="POST" action="users.php" onsubmit="return confirm('Remove account permanently?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="user_id" value="<?= $user->id ?>">
                          <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200/50 text-rose-600 dark:bg-rose-950/20 dark:border-rose-900/30 rounded-lg cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                          </button>
                        </form>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<div id="modal-create" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-display font-bold text-base text-slate-900 dark:text-white">Add New Academic User</h3>
      <button onclick="toggleModal('modal-create', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
    </div>
    <form method="POST" action="users.php" class="space-y-4">
      <input type="hidden" name="action" value="create">
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Full Name</label>
        <input name="full_name" type="text" required placeholder="John Smith" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-900 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Username / Identifier Code</label>
        <input name="username" type="text" required placeholder="e.g. CT/2021/001 or librarian_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-900 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Email</label>
        <input name="email" type="email" required placeholder="john@university.edu" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-900 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Password</label>
        <input name="password" type="password" required placeholder="Min 6 characters" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-900 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Access Role Profile</label>
        <select name="role" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 outline-none font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
          <option value="">Select role…</option>
          <option value="librarian">Librarian</option>
          <option value="student">Student</option>
        </select>
      </div>
      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
        <button type="button" onclick="toggleModal('modal-create', false)" class="px-4 py-2 text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200">Cancel</button>
        <button type="submit" class="px-4.5 py-2 bg-blue-600 text-white text-xs font-semibold rounded-xl shadow-md">Create User</button>
      </div>
    </form>
  </div>
</div>

<div id="modal-edit" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-display font-bold text-base text-slate-900 dark:text-white flex items-center gap-1.5">
         <i data-lucide="user-cog" class="w-5 h-5 text-indigo-500"></i> Edit Registry Profile Account
      </h3>
      <button onclick="toggleModal('modal-edit', false)" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
    </div>
    <form method="POST" action="users.php" class="space-y-4">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="user_id" id="edit_user_id">
      
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Full Name</label>
        <input name="full_name" id="edit_full_name" type="text" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-900 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Email Address</label>
        <input name="email" id="edit_email" type="email" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 outline-none text-slate-900 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Clearance Access State</label>
        <select name="is_active" id="edit_is_active" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-sm rounded-xl border border-slate-200 dark:border-slate-800 outline-none font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
          <option value="1">Active / Allowed Entry</option>
          <option value="0">Inactive / Suspended</option>
        </select>
      </div>
      <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
        <button type="button" onclick="toggleModal('modal-edit', false)" class="px-4 py-2 text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-200">Cancel</button>
        <button type="submit" class="px-4.5 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-xl shadow-md">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  function toggleModal(modalId, show) {
      const modal = document.getElementById(modalId);
      if(show) modal.classList.remove('hidden');
      else modal.classList.add('hidden');
  }

  // Inject target database values into edit form slots synchronously
  function openEditModal(id, fullName, email, isActive) {
      document.getElementById('edit_user_id').value = id;
      document.getElementById('edit_full_name').value = fullName;
      document.getElementById('edit_email').value = email;
      document.getElementById('edit_is_active').value = isActive;
      toggleModal('modal-edit', true);
  }

  document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
</script>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>