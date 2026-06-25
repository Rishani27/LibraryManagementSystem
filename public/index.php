<?php
// ============================================================
// ULMS — Public Entry Point
// public/index.php
// ============================================================

define('ROOT_URL', '..');
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Session.php';

Session::start();

if (Session::isLoggedIn()) {
    $role = Session::getRole();
    $map  = [
        'admin'     => WEB_BASE . '/app/presentation/admin/dashboard.php',
        'librarian' => WEB_BASE . '/app/presentation/librarian/dashboard.php',
        'student'   => WEB_BASE . '/app/presentation/student/dashboard.php',
    ];
    header('Location: ' . ($map[$role] ?? APP_URL . '/login.php'));
} else {
    header('Location: ' . APP_URL . '/login.php');
}
exit;
