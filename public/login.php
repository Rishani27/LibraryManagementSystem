<?php
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

$msg = $_GET['msg'] ?? '';
if ($msg === 'login_required') $error = 'Please log in to continue.';
if ($msg === 'unauthorized')   $error = 'You do not have permission to view that page.';

$themeClass = 'dark';
if (isset($_COOKIE['lms_theme'])) {
    $themeClass = $_COOKIE['lms_theme'] === 'light' ? 'light' : 'dark';
}
?>
<!DOCTYPE html>
<html lang="en" class="<?= $themeClass ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Login to the University Library Management System">
  <title>Login | University Library Management System</title>
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap');
    
    :root {
      --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
      --font-display: "Poppins", sans-serif;
      --font-mono: "JetBrains Mono", ui-monospace, SFMono-Regular, monospace;
    }

    body {
      font-family: var(--font-sans);
    }
    .font-display { font-family: var(--font-display); }
    .font-mono { font-family: var(--font-mono); }
  </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-white transition-colors duration-200 min-h-screen">

  <div class="min-h-screen flex font-sans" id="login-container">
    
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-950 text-white p-12 flex-col justify-between border-r border-slate-200/10 dark:border-slate-800/40">
      
      <div class="absolute inset-0 z-0 opacity-15 mix-blend-overlay pointer-events-none bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=1200');"></div>

      <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl -translate-x-12 -translate-y-12 z-0"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl translate-x-12 translate-y-12 z-0"></div>
      <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:24px_24px] z-0"></div>

      <div class="relative z-10 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
          <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
        </div>
        <div>
          <span class="font-display font-bold text-xl tracking-tight bg-gradient-to-r from-white via-slate-200 to-gray-400 bg-clip-text text-transparent uppercase">
            <?= defined('APP_NAME') ? APP_NAME : 'METROPOLIS' ?>
          </span>
          <span class="text-xs block text-blue-400 font-medium tracking-widest uppercase">UNIVERSITY LIBRARY</span>
        </div>
      </div>

      <div class="relative z-10 my-auto flex flex-col items-center text-center px-6">
        <div class="w-48 h-48 rounded-3xl bg-slate-900/40 border border-white/10 flex items-center justify-center relative mb-8 backdrop-blur-xl">
          <svg class="w-32 h-32 text-blue-400/80" viewBox="0 0 120 120" fill="none">
            <rect x="25" y="25" width="70" height="70" rx="12" stroke="currentColor" stroke-width="2" stroke-dasharray="3 3" />
            <path d="M40 50 H80 M40 65 H80 M40 80 H60" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            <circle cx="85" cy="80" r="10" stroke="#10B981" stroke-width="2" fill="#10B981" fill-opacity="0.1" />
            <path d="M85 75 V85 M80 80 H90" stroke="#10B981" stroke-width="2" stroke-linecap="round" />
          </svg>
          <i data-lucide="sparkles" class="w-6 h-6 text-amber-400 absolute top-4 right-4 animate-pulse"></i>
        </div>

        <h1 class="text-4xl font-display font-bold tracking-tight text-white mb-4">Every book,
every borrower,
beautifully managed.</h1>
        <p class="text-slate-300 text-sm max-w-md leading-relaxed">
          Access millions of resources, manage catalog loans, request acquisitions, and verify reservations through our unified university portal.
        </p>
      </div>

      <div class="relative z-10 flex items-center justify-between text-xs text-slate-500">
        <span>&copy; <?= date('Y') ?> ULMS</span>
        <span class="flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          
        </span>
      </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 md:p-12 bg-slate-50 dark:bg-slate-950">
      <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-8 md:p-10 shadow-[0_20px_50px_rgba(15,23,42,0.08)] dark:shadow-[0_25px_60px_rgba(0,0,0,0.45)]">
        
        <div class="flex lg:hidden items-center gap-2.5 mb-8 justify-center">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center shadow-lg">
            <i data-lucide="book-open" class="w-4.5 h-4.5 text-white"></i>
          </div>
          <span class="font-display font-bold text-lg text-slate-800 dark:text-white tracking-tight">University LMS</span>
        </div>

        <div class="mb-6 text-center lg:text-left">
          <h2 class="text-2xl font-display font-bold text-slate-900 dark:text-white tracking-tight">Sign In to LMS Portal</h2>
          <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Please enter your authorized network system credentials</p>
        </div>

        <?php if ($error): ?>
          <div class="mb-4 p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/30 rounded-xl text-xs text-red-600 dark:text-red-400 font-medium flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 shrink-0"></i>
            <span><?= Helper::e($error) ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4" novalidate id="login-form-element">
          <div>
            <label for="username" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
              Username / Email Address
            </label>
            <div class="relative flex items-center">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                <i data-lucide="mail" class="w-4.5 h-4.5"></i>
              </div>
              <input
                id="username"
                name="username"
                type="text"
                value="<?= Helper::e($_POST['username'] ?? '') ?>"
                placeholder="name@univ.edu or username"
                required
                autocomplete="username"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 text-sm text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800/80 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-950/20 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600 shadow-inner"
              />
            </div>
          </div>

          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                Password
              </label>
              <a href="#forgot" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Forgot?</a>
            </div>
            <div class="relative flex items-center">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10">
                <i data-lucide="lock" class="w-4.5 h-4.5"></i>
              </div>
              <input
                id="password"
                name="password"
                type="password"
                placeholder="••••••••••••"
                required
                autocomplete="current-password"
                class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 text-sm text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800/80 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:focus:ring-blue-950/20 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-600 shadow-inner"
              />
            </div>
          </div>

          <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input type="checkbox" name="remember" checked class="w-4.5 h-4.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
              <span class="text-xs text-slate-500 dark:text-slate-400">Keep me logged in</span>
            </label>
          </div>

          <button
            type="submit"
            id="login-submit-btn"
            class="w-full py-3 px-4 rounded-xl font-semibold text-sm text-white shadow-lg bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl hover:shadow-indigo-500/10 cursor-pointer active:scale-[0.98] flex items-center justify-center gap-2 transition-all"
          >
            <i data-lucide="key" class="w-4 h-4"></i>
            <span>Sign In to System</span>
          </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/80 text-center text-xs text-slate-400 dark:text-slate-500">
          &copy; <?= date('Y') ?> University Library System v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?>. All rights reserved.
        </div>

      </div>
    </div>
  </div>

  <script src="assets/js/script.js"></script>
  <script>
    lucide.createIcons();

    document.getElementById('login-form-element').addEventListener('submit', function() {
        const btn = document.getElementById('login-submit-btn');
        btn.disabled = true;
        btn.className = "w-full py-3 px-4 rounded-xl font-semibold text-sm text-white shadow-lg bg-indigo-600/80 dark:bg-indigo-500/80 cursor-wait flex items-center justify-center gap-2 transition-all";
        btn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg> Authorizing Access...`;
    });
  </script>
</body>
</html>