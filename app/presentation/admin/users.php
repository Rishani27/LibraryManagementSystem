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

include ROOT_URL . '/app/includes/header.php';
include ROOT_URL . '/app/includes/nav_admin.php';
?>
<div class="main-content">
  <div class="topbar">
    <span class="topbar-title">👥 User Management</span>
    <button class="btn btn-primary btn-sm" onclick="openModal('modal-create')">+ Create User</button>
  </div>

  <div class="page-content fade-in">

    <?php if ($flash['message']): ?>
    <div class="alert alert-<?= Helper::e($flash['type']) ?>"><?= Helper::e($flash['message']) ?></div>
    <?php endif; ?>

    <!-- Stats row -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
      <?php $statMap = [['Total','👥',$stats['total'],'blue'],['Admins','🛡️',$stats['admins'],'red'],['Librarians','📚',$stats['librarians'],'cyan'],['Students','🎓',$stats['students'],'green']]; ?>
      <?php foreach ($statMap as [$label,$icon,$val,$color]): ?>
      <div class="stat-card">
        <div class="stat-icon <?= $color ?>"><?= $icon ?></div>
        <div><div class="stat-value"><?= $val ?></div><div class="stat-label"><?= $label ?></div></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Search + filter -->
    <div class="card" style="margin-bottom:20px">
      <form method="GET" class="search-bar">
        <div class="search-input-wrap">
          <span class="search-icon">🔍</span>
          <input id="q" name="q" type="text" class="form-control search-input"
                 placeholder="Search by name, username, or email…"
                 value="<?= Helper::e($searchQuery) ?>">
        </div>
        <select name="role" class="form-control" style="width:150px">
          <option value="">All Roles</option>
          <option value="admin"     <?= $roleFilter==='admin'     ?'selected':'' ?>>Admin</option>
          <option value="librarian" <?= $roleFilter==='librarian' ?'selected':'' ?>>Librarian</option>
          <option value="student"   <?= $roleFilter==='student'   ?'selected':'' ?>>Student</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($searchQuery || $roleFilter): ?>
        <a href="users.php" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Users table -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">User Registry (<?= count($users) ?>)</h2>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Full Name</th><th>Username</th><th>Email</th>
              <th>Role</th><th>Status</th><th>Created</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($users)): ?>
            <tr><td colspan="8" class="text-center text-muted" style="padding:32px">No users found.</td></tr>
          <?php else: ?>
          <?php foreach ($users as $i => $user): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><?= Helper::e($user->full_name) ?></td>
              <td><code style="font-size:0.8rem"><?= Helper::e($user->username) ?></code></td>
              <td class="text-muted"><?= Helper::e($user->email) ?></td>
              <td>
                <span class="badge role-<?= Helper::e($user->role) ?>">
                  <?= ucfirst(Helper::e($user->role)) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $user->is_active ? 'badge-active' : 'badge-overdue' ?>">
                  <?= $user->is_active ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td class="text-muted"><?= Helper::e(Helper::formatDate($user->created_at)) ?></td>
              <td>
                <?php if ($user->id !== Session::getUserId() && $user->role !== 'admin'): ?>
                <form method="POST" style="display:inline">
                  <input type="hidden" name="action"  value="delete">
                  <input type="hidden" name="user_id" value="<?= $user->id ?>">
                  <button type="submit" class="btn btn-danger btn-sm"
                          data-confirm="Delete user '<?= Helper::e($user->username) ?>'? This cannot be undone.">
                    🗑 Delete
                  </button>
                </form>
                <?php else: ?>
                <span class="text-muted">—</span>
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

<!-- Create User Modal -->
<div class="modal-overlay" id="modal-create">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">➕ Create New User</span>
      <button class="modal-close" onclick="closeModal('modal-create')">✕</button>
    </div>
    <form method="POST" action="users.php">
      <input type="hidden" name="action" value="create">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input name="full_name" type="text" class="form-control" placeholder="John Smith" required>
        </div>
        <div class="form-group">
          <label class="form-label">Username *</label>
          <input name="username" type="text" class="form-control" placeholder="john_smith" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input name="email" type="email" class="form-control" placeholder="john@university.edu" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input name="password" type="password" class="form-control" placeholder="Min 6 characters" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Role *</label>
        <select name="role" class="form-control" required>
          <option value="">Select role…</option>
          <option value="librarian">Librarian</option>
          <option value="student">Student</option>
        </select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-create')">Cancel</button>
        <button type="submit" class="btn btn-primary">✅ Create User</button>
      </div>
    </form>
  </div>
</div>

<?php include ROOT_URL . '/app/includes/footer.php'; ?>
