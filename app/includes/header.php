<?php
$rootUrl = defined('ROOT_URL') ? ROOT_URL : '../../..';
$webBase = defined('WEB_BASE') ? WEB_BASE : '/library-system';

// Fallbacks for display purposes
$userDisplayName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'System User';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'student';

// Check theme cookie[cite: 10]
$themeClass = 'dark'; 
if (isset($_COOKIE['lms_theme'])) {
    $themeClass = $_COOKIE['lms_theme'] === 'light' ? 'light' : 'dark'; //[cite: 10]
}

// FIX SIDEBAR FLICKER: Check the sidebar cookie before rendering elements
$sidebarStyle = '';
if (isset($_COOKIE['lms_sidebar']) && $_COOKIE['lms_sidebar'] === 'collapsed') {
    // Inject the inline collapsed widths right onto the server-rendered container shell
    $sidebarStyle = 'style="width: 78px !important; min-width: 78px !important; max-width: 78px !important;"';
}
?>
<!DOCTYPE html>
<html lang="en" class="<?= $themeClass ?>"> <!--[cite: 10] -->
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="University Library Management System — <?= htmlspecialchars($pageTitle ?? 'ULMS') ?>">
  <title><?= htmlspecialchars($pageTitle ?? 'ULMS') ?> | University Library</title>
  
  <!-- Tailwind CSS & Lucide Icons -->
  <script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    darkMode: 'class', // This is the fix: tells Tailwind to look for the 'dark' class
  }
</script>
  <script src="https://unpkg.com/lucide@latest"></script>
  
  <style>
    @custom-variant dark (&:where(.dark, .dark *));
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap');
    
    :root {
      --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
      --font-display: "Poppins", sans-serif;
      --font-mono: "JetBrains Mono", ui-monospace, SFMono-Regular, monospace;
      --color-navy: #1E3A8A;
      --color-navy-dark: #111827;
      --color-accent: #3B82F6;
      --color-accent-teal: #14B8A6;
      --color-bg-light: #F9FAFB;
      --color-text-main: #1F2937;
      --color-text-muted: #6B7280;
    }

    body {
      font-family: var(--font-sans);
    }
    .font-display { font-family: var(--font-display); }
    .font-mono { font-family: var(--font-mono); }

    .glass-panel {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(229, 231, 235, 0.6);
    }
    .dark .glass-panel {
      background: rgba(15, 23, 42, 0.8);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(51, 65, 85, 0.5);
    }
    .hover-premium {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-premium:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.5); }

    .hover-premium {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hover-premium:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(8px) scale(0.98);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .animate-fade-in {
      animation: fadeIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
  </style>
</head>
<body class=" bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-white transition-colors duration-200 min-h-screen">
<div class="flex h-screen w-screen overflow-hidden">

  <!-- Include Role-Based Sidebar Navigation dynamically -->
  <?php 
    if ($userRole === 'admin') {
        include __DIR__ . '/nav_admin.php';
    } elseif ($userRole === 'librarian') {
        include __DIR__ . '/nav_librarian.php';
    } else {
        include __DIR__ . '/nav_student.php';
    }
  ?>

  <!-- Main Content Wrapper Area -->
  <div class="flex flex-col flex-1 h-screen overflow-y-auto overflow-x-hidden relative">
    
    <!-- Topbar Component Header - Now Fully Sticky & Perfectly Aligned -->
    <header class="bg-white/80 dark:bg-slate-900/85 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 h-[73px] min-h-[73px] flex items-center justify-between px-4 md:px-8 sticky top-0 z-50 w-full shadow-sm" id="app-topbar">
      <!-- Search -->
      <div class="flex items-center gap-4 flex-1">
        <button onclick="toggleSidebar()" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
  <i data-lucide="menu" class="w-5 h-5"></i>
</button>
        <div class="relative max-w-xs md:max-w-sm w-full hidden sm:block">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
            <i data-lucide="search" class="w-4 h-4"></i>
          </div>
          <input type="text" placeholder="Search operational catalog modules..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-950 text-xs md:text-sm text-slate-900 dark:text-white rounded-xl border border-slate-200/80 dark:border-slate-800/80 focus:border-blue-500 outline-none transition-all placeholder:text-gray-400 shadow-inner">
        </div>
      </div>

      <!-- Right Hand Controls -->
      <div class="flex items-center gap-3 md:gap-5">
        <!-- Live Clock -->
        <div class="hidden lg:flex items-center gap-4 px-3.5 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/80 rounded-xl font-medium text-slate-700 dark:text-slate-300 shadow-sm">
          <div class="flex items-center gap-1.5 text-xs">
            <i data-lucide="calendar" class="w-3.5 h-3.5 text-blue-500"></i>
            <span id="live-date"><?= date('D, M j, Y') ?></span>
          </div>
          <div class="w-px h-3.5 bg-slate-200 dark:bg-slate-800"></div>
          <div class="flex items-center gap-1.5 text-xs font-mono font-semibold tracking-wider">
            <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-500"></i>
            <span id="live-time"><?= date('h:i:s A') ?></span>
          </div>
        </div>

        <!-- Dark Mode Toggle -->
        <button onclick="toggleDarkMode()" id="dark-mode-toggle" class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/40 dark:border-slate-800/50 transition-all cursor-pointer shadow-sm">
          <i data-lucide="moon" id="dark-mode-icon" class="w-4.5 h-4.5"></i>
        </button>

        <!-- Dropdowns Wrapper Profile -->
        <div class="relative">
          <button onclick="toggleProfileMenu()" class="flex items-center gap-3 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all cursor-pointer border border-transparent">
            <!-- Perfect Avatar Circle Element -->
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-display font-bold text-sm flex items-center justify-center shadow-md shrink-0 aspect-square tracking-wider">
              <?= strtoupper(substr($userDisplayName, 0, 1)) ?>
            </div>
            <div class="hidden md:flex flex-col text-left">
              <span class="font-bold text-xs text-slate-800 dark:text-slate-200 flex items-center gap-1 leading-none">
                <?= htmlspecialchars($userDisplayName) ?>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
              </span>
              <span class="text-[10px] text-slate-500 font-semibold mt-1.5 leading-none uppercase tracking-wider"><?= $userRole ?> portal</span>
            </div>
          </button>

          <!-- Hidden Profile Menu Dropdown Container -->
          <div id="profile-dropdown" class="hidden absolute right-0 mt-2.5 w-64 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl shadow-xl z-50 overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/10">
              <span class="font-semibold text-xs text-slate-400 uppercase tracking-wider block mb-1">Signed in as</span>
              <span class="font-display font-bold text-sm text-slate-800 dark:text-white"><?= htmlspecialchars($userDisplayName) ?></span>
            </div>
            <div class="p-2">
              <a href="<?= $webBase ?>/public/logout.php" class="w-full text-left px-3 py-2 text-xs font-semibold text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg flex items-center gap-2 cursor-pointer">
                <i data-lucide="log-out" class="w-4 h-4"></i> Sign Out Portal
              </a>
            </div>
          </div>
        </div>

      </div>
    </header>

    <!-- Main Dashboard Body Yield Wrapper Content with Eye-Catching Card Depth Styling Context -->
    <main class="p-4 md:p-8 flex-1 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-white animate-fade-in [&_.card]:shadow-[0_8px_30px_rgb(0,0,0,0.02)] [&_.card]:dark:shadow-[0_8px_30px_rgb(0,0,0,0.15)] [&_.stat-card]:shadow-[0_8px_30px_rgb(0,0,0,0.02)] [&_.stat-card]:dark:shadow-[0_8px_30px_rgb(0,0,0,0.15)]">