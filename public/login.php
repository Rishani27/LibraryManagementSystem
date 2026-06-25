<?php
// ============================================================
// ULMS — Login Page (Presentation + Auth Handler)
// public/login.php
// ============================================================

define('ROOT_URL', '..');
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Helper.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/data/repositories/UserRepository.php';
require_once __DIR__ . '/../app/data/repositories/LogRepository.php';
require_once __DIR__ . '/../app/business/services/LogService.php';
require_once __DIR__ . '/../app/business/services/AuthService.php';

Session::start();

// Already logged in → redirect
if (Session::isLoggedIn()) {
    $roleMap = [
        'admin'     => WEB_BASE . '/app/presentation/admin/dashboard.php',
        'librarian' => WEB_BASE . '/app/presentation/librarian/dashboard.php',
        'student'   => WEB_BASE . '/app/presentation/student/dashboard.php',
    ];
    Helper::redirect($roleMap[Session::getRole()] ?? WEB_BASE . '/public/login.php');
}

$error   = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $authService = new AuthService();
    $result      = $authService->login($username, $password);

    if ($result['success']) {
        $role = $result['role'];
        $map  = [
            'admin'     => WEB_BASE . '/app/presentation/admin/dashboard.php',
            'librarian' => WEB_BASE . '/app/presentation/librarian/dashboard.php',
            'student'   => WEB_BASE . '/app/presentation/student/dashboard.php',
        ];
        Helper::redirect($map[$role] ?? WEB_BASE . '/public/login.php');
    } else {
        $error = $result['message'];
    }
}

// Flash from redirect
$msg = $_GET['msg'] ?? '';
if ($msg === 'login_required') $error = 'Please log in to continue.';
if ($msg === 'unauthorized')   $error = 'You do not have permission to view that page.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Login to the University Library Management System">
  <title>Login | University Library Management System</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-page">
  <div class="login-card fade-in">

    <div class="login-logo">
      <div class="login-logo-icon">📚</div>
      <h1><?= APP_NAME ?></h1>
      <p>Sign in to your account</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= Helper::e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input
          id="username" name="username" type="text"
          class="form-control"
          placeholder="Enter your username"
          value="<?= Helper::e($_POST['username'] ?? '') ?>"
          required autocomplete="username"
        >
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input
          id="password" name="password" type="password"
          class="form-control"
          placeholder="Enter your password"
          required autocomplete="current-password"
        >
      </div>

      <button type="submit" class="btn btn-primary w-100 mt-2" style="justify-content:center;padding:12px">
        🔐 Sign In
      </button>
    </form>

    <p class="text-center text-muted mt-3" style="font-size:0.78rem">
      University Library Management System v<?= APP_VERSION ?>
    </p>

  </div>
</div>
<script src="assets/js/script.js"></script>
</body>
</html>
