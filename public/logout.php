<?php
// ============================================================
// ULMS — Logout Handler
// public/logout.php
// ============================================================

define('ROOT_URL', '..');
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Helper.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/data/repositories/LogRepository.php';
require_once __DIR__ . '/../app/business/services/LogService.php';
require_once __DIR__ . '/../app/business/services/AuthService.php';

Session::start();

if (Session::isLoggedIn()) {
    $auth = new AuthService();
    $auth->logout();
}

Helper::redirect(APP_URL . '/login.php');
