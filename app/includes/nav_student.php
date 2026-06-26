<?php
// ============================================================
// ULMS — Student Navigation Sidebar
// includes/nav_student.php
// ============================================================

require_once __DIR__ . '/../core/Session.php';
Session::requireLogin('student');

$webBase    = defined('WEB_BASE') ? WEB_BASE : '/library-system';
$activePage = $activePage ?? '';
$fullName   = Session::getFullName();
$initial    = strtoupper(substr($fullName, 0, 1));

$navItems = [
  'dashboard'       => ['icon' => '🏠', 'label' => 'Dashboard',        'href' => $webBase . '/app/presentation/student/dashboard.php'],
  'catalog'         => ['icon' => '🔍', 'label' => 'Browse Catalog',   'href' => $webBase . '/app/presentation/student/catalog.php'],
  'my_reservations' => ['icon' => '🔖', 'label' => 'My Reservations',  'href' => $webBase . '/app/presentation/student/my_reservations.php'],
];
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo">📚</div>
    <h2>ULMS</h2>
    <span>University Library</span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-title">Student Portal</div>
    <?php foreach ($navItems as $key => $item): ?>
    <a href="<?= $item['href'] ?>" class="nav-item <?= $activePage === $key ? 'active' : '' ?>">
      <span class="nav-icon"><?= $item['icon'] ?></span>
      <?= htmlspecialchars($item['label']) ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar" style="background:#059669"><?= $initial ?></div>
      <div>
        <div style="font-size:0.82rem;font-weight:600"><?= htmlspecialchars($fullName) ?></div>
        <div style="font-size:0.72rem;color:#34d399">Student</div>
      </div>
    </div>
    <a href="<?= $webBase ?>/public/logout.php" class="btn btn-ghost btn-sm w-100">🚪 Logout</a>
  </div>
</aside>
