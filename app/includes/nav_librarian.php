<?php
// ============================================================
// ULMS — Librarian Navigation Sidebar
// includes/nav_librarian.php
// ============================================================

require_once __DIR__ . '/../core/Session.php';
Session::requireLogin('librarian');

$webBase    = defined('WEB_BASE') ? WEB_BASE : '/library-system';
$activePage = $activePage ?? '';
$fullName   = Session::getFullName();
$initial    = strtoupper(substr($fullName, 0, 1));

$navItems = [
  'dashboard'    => ['icon' => '🏠', 'label' => 'Dashboard',      'href' => $webBase . '/app/presentation/librarian/dashboard.php'],
  'books'        => ['icon' => '📖', 'label' => 'Book Catalog',    'href' => $webBase . '/app/presentation/librarian/books.php'],
  'borrow'       => ['icon' => '📤', 'label' => 'Issue Books',     'href' => $webBase . '/app/presentation/librarian/borrow.php'],
  'return'       => ['icon' => '📥', 'label' => 'Return Books',    'href' => $webBase . '/app/presentation/librarian/return.php'],
  'reservations' => ['icon' => '🔖', 'label' => 'Reservations',   'href' => $webBase . '/app/presentation/librarian/reservations.php'],
  'fines'        => ['icon' => '💰', 'label' => 'Fines',          'href' => $webBase . '/app/presentation/librarian/fines.php'],
  'reports'      => ['icon' => '📊', 'label' => 'Reports',        'href' => $webBase . '/app/presentation/librarian/reports.php'],
];
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo">📚</div>
    <h2>ULMS</h2>
    <span>University Library</span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-title">Library Operations</div>
    <?php foreach ($navItems as $key => $item): ?>
    <a href="<?= $item['href'] ?>" class="nav-item <?= $activePage === $key ? 'active' : '' ?>">
      <span class="nav-icon"><?= $item['icon'] ?></span>
      <?= htmlspecialchars($item['label']) ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar" style="background:#0891b2"><?= $initial ?></div>
      <div>
        <div style="font-size:0.82rem;font-weight:600"><?= htmlspecialchars($fullName) ?></div>
        <div style="font-size:0.72rem;color:#22d3ee">Librarian</div>
      </div>
    </div>
    <a href="<?= $webBase ?>/public/logout.php" class="btn btn-ghost btn-sm w-100">🚪 Logout</a>
  </div>
</aside>
