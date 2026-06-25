<?php
// ============================================================
// ULMS — Admin Navigation Sidebar
// includes/nav_admin.php
// ============================================================

require_once __DIR__ . '/../core/Session.php';
Session::requireLogin('admin');

$webBase    = defined('WEB_BASE') ? WEB_BASE : '/library-system';
$rootUrl    = defined('ROOT_URL') ? ROOT_URL : '../../..';
$activePage = $activePage ?? '';
$fullName   = Session::getFullName();
$initial    = strtoupper(substr($fullName, 0, 1));

$navItems = [
  'dashboard' => ['icon' => '🏠', 'label' => 'Dashboard',       'href' => $webBase . '/app/presentation/admin/dashboard.php'],
  'users'     => ['icon' => '👥', 'label' => 'User Management',  'href' => $webBase . '/app/presentation/admin/users.php'],
  'logs'      => ['icon' => '📋', 'label' => 'Activity Logs',    'href' => $webBase . '/app/presentation/admin/logs.php'],
];
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo">📚</div>
    <h2>ULMS</h2>
    <span>University Library</span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-title">Main Menu</div>
    <?php foreach ($navItems as $key => $item): ?>
    <a href="<?= $item['href'] ?>" class="nav-item <?= $activePage === $key ? 'active' : '' ?>">
      <span class="nav-icon"><?= $item['icon'] ?></span>
      <?= htmlspecialchars($item['label']) ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= $initial ?></div>
      <div>
        <div style="font-size:0.82rem;font-weight:600"><?= htmlspecialchars($fullName) ?></div>
        <div style="font-size:0.72rem;color:#ef4444">Administrator</div>
      </div>
    </div>
    <a href="<?= $webBase ?>/public/logout.php" class="btn btn-ghost btn-sm w-100">🚪 Logout</a>
  </div>
</aside>
