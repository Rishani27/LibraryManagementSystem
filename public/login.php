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
require_once __DIR__ . '/../app/business/services/UserService.php';

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
$initialMode = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? 'login';
    
    if ($formType === 'login') {
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
            $initialMode = 'login';
        }
    } elseif ($formType === 'register') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $index_no  = trim($_POST['index_no'] ?? '');
        $faculty   = trim($_POST['faculty'] ?? '');
        $course    = trim($_POST['course'] ?? '');

        $userService = new UserService();
        $result = $userService->registerPendingStudent([
            'username'  => $index_no,
            'password'  => $password,
            'full_name' => $full_name,
            'email'     => $email,
            'faculty'   => $faculty,
            'course'    => $course
        ]);

        if ($result['success']) {
            $success = 'Registration request submitted! Please wait for administrative approval.';
            $initialMode = 'login'; 
            // Flush post details on actual success
            $_POST = [];
        } else {
            $error = $result['message'];
            $initialMode = 'register';
        }
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
  <title>Welcome to ULMS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' }</script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap');
    body { font-family: "Inter", sans-serif; }
    .font-display { font-family: "Plus Jakarta Sans", sans-serif; }
  </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-white transition-colors duration-200 min-h-screen overflow-hidden flex items-center justify-center">

  <div class="relative w-full h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-950">
    
    <div id="wallpaper-panel" class="hidden lg:flex absolute top-0 bottom-0 w-1/2 z-30 transition-all duration-1000 ease-in-out border-r border-slate-200/10 p-12 flex-col justify-between <?= $initialMode === 'login' ? 'left-0 bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-950' : 'left-1/2 bg-gradient-to-br from-purple-900 via-violet-950 to-slate-950' ?>">
      
      <div class="absolute inset-0 z-0 opacity-15 mix-blend-overlay pointer-events-none bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=1200');"></div>
      <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl -translate-x-12 -translate-y-12 transition-all duration-1000" id="glow-top"></div>
      <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl translate-x-12 translate-y-12 transition-all duration-1000" id="glow-bottom"></div>

      <div class="relative z-10 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center shadow-lg">
          <i data-lucide="book-open" class="w-5 h-5 text-white"></i>
        </div>
        <div>
          <span class="font-display font-bold text-xl tracking-tight bg-gradient-to-r from-white to-gray-400 bg-clip-text text-transparent uppercase">ULMS</span>
          <span class="text-xs block text-blue-400 font-medium tracking-widest uppercase">University Library</span>
        </div>
      </div>

      <div class="relative z-10 my-auto flex flex-col items-center text-center px-6">
        <div class="w-40 h-40 rounded-3xl bg-slate-900/40 border border-white/10 flex items-center justify-center relative mb-8 backdrop-blur-xl">
          <svg class="w-24 h-24 text-blue-400/80 transition-transform duration-1000 ease-in-out" id="wallpaper-svg" viewBox="0 0 120 120" fill="none">
            <rect x="25" y="25" width="70" height="70" rx="12" stroke="currentColor" stroke-width="2" stroke-dasharray="3 3" />
            <path d="M40 50 H80 M40 65 H80 M40 80 H60" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            <circle cx="85" cy="80" r="10" stroke="#10B981" stroke-width="2" fill="#10B981" fill-opacity="0.1" />
          </svg>
          <i data-lucide="sparkles" class="w-5 h-5 text-amber-400 absolute top-4 right-4 animate-pulse"></i>
        </div>
        
        <div class="transition-all duration-500 ease-in-out transform" id="text-fade-container">
          <h1 id="wallpaper-heading" class="text-3xl font-display font-bold tracking-tight text-white mb-4 transition-opacity duration-300">
            <?= $initialMode === 'login' ? 'Every book, every borrower, beautifully managed.' : 'Your gateway to a universe of academic knowledge.' ?>
          </h1>
          <p id="wallpaper-paragraph" class="text-slate-300 text-xs max-w-sm leading-relaxed transition-opacity duration-300">
            <?= $initialMode === 'login' ? 'Access millions of resources, manage catalog loans, request acquisitions, and verify reservations through our unified portal.' : 'Join your university library network today. Create your pending student profile request matrix and wait for registrar approval clearance.' ?>
          </p>
        </div>
      </div>

      <div class="relative z-10 flex items-center justify-between text-xs text-slate-500">
        <span>&copy; <?= date('Y') ?> ULMS</span>
        <span class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Systems Operational</span>
      </div>
    </div>

    <div id="left-form-container" class="w-full lg:w-1/2 h-full flex items-center justify-center p-6 md:p-8 absolute top-0 bottom-0 left-0 transition-all duration-1000 ease-in-out transform <?= $initialMode === 'login' ? 'pointer-events-none opacity-0 scale-95 z-10' : 'z-20 pointer-events-auto opacity-100 scale-100' ?>">
      <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-2xl p-6 md:p-8 shadow-xl">
        <div class="mb-5 text-center lg:text-left">
          <h2 class="text-xl font-display font-bold text-slate-900 dark:text-white tracking-tight">Create Student Account</h2>
          <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Submit verification details to the registry queue</p>
        </div>

        <form method="POST" action="login.php" class="space-y-3.5" autocomplete="off">
          <input type="hidden" name="form_type" value="register">
          
          <div>
            <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Full Name *</label>
            <input name="full_name" type="text" value="<?= Helper::e($_POST['full_name'] ?? '') ?>" required placeholder="John Doe" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-indigo-500" />
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">University Index Number *</label>
            <input name="index_no" type="text" value="<?= Helper::e($_POST['index_no'] ?? '') ?>" required placeholder="e.g. CT/2021/001" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-indigo-500" />
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Email Address *</label>
            <input name="email" type="email" value="<?= Helper::e($_POST['email'] ?? '') ?>" required placeholder="student@university.edu" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-indigo-500" />
          </div>

          <div class="grid grid-cols-2 gap-2.5">
            <div>
              <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Faculty *</label>
              <select name="faculty" id="faculty_select" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-700 dark:text-slate-300 rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-indigo-500 cursor-pointer">
                <option value="">Select...</option>
                <option value="Technology" <?= (($_POST['faculty'] ?? '') === 'Technology') ? 'selected' : '' ?>>Technology</option>
                <option value="Management" <?= (($_POST['faculty'] ?? '') === 'Management') ? 'selected' : '' ?>>Management</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Course *</label>
              <select name="course" id="course_select" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-700 dark:text-slate-300 rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-indigo-500 cursor-pointer">
                <option value="">Choose Faculty</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Account Password *</label>
            <input name="password" type="password" minlength="6" required placeholder="Min 6 characters" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 text-xs text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-indigo-500" />
          </div>

          <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold text-xs rounded-xl shadow-md cursor-pointer flex items-center justify-center gap-1.5 mt-2">
            <i data-lucide="arrow-right-circle" class="w-4 h-4"></i> Submit Matrix Request
          </button>
        </form>

        <div class="mt-4 text-center text-xs text-slate-500">
          Already have an account? <button type="button" onclick="toggleMode('login')" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline bg-transparent border-none cursor-pointer">Sign In here</button>
        </div>
      </div>
    </div>

    <div id="right-form-container" class="w-full lg:w-1/2 h-full flex items-center justify-center p-6 md:p-12 absolute top-0 bottom-0 right-0 transition-all duration-1000 ease-in-out transform <?= $initialMode === 'login' ? 'z-20 pointer-events-auto opacity-100 scale-100' : 'pointer-events-none opacity-0 scale-95 z-10' ?>">
      <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-8 md:p-10 shadow-xl">
        
        <div class="mb-6 text-center lg:text-left">
          <h2 class="text-2xl font-display font-bold text-slate-900 dark:text-white tracking-tight">Sign In to LMS Portal</h2>
          <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Please enter your authorized network system credentials</p>
        </div>

        <div id="alert-messages-wrapper">
          <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/30 rounded-xl text-xs text-red-600 dark:text-red-400 font-medium flex items-center gap-2">
              <i data-lucide="alert-triangle" class="w-4 h-4 text-red-500 shrink-0"></i>
              <span><?= Helper::e($error) ?></span>
            </div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 rounded-xl text-xs text-emerald-700 dark:text-emerald-400 font-medium flex items-center gap-2">
              <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0"></i>
              <span><?= Helper::e($success) ?></span>
            </div>
          <?php endif; ?>
        </div>

        <form method="POST" action="login.php" class="space-y-4" id="login-form-element">
          <input type="hidden" name="form_type" value="login">
          <div>
            <label for="username" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Username / Email Address</label>
            <div class="relative flex items-center">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10"><i data-lucide="mail" class="w-4.5 h-4.5"></i></div>
              <input id="username" name="username" type="text" value="<?= Helper::e($_POST['username'] ?? '') ?>" placeholder="name@univ.edu or username" required autocomplete="username" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 text-sm text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-blue-500 shadow-inner" />
            </div>
          </div>

          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Password</label>
              <a href="#forgot" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Forgot?</a>
            </div>
            <div class="relative flex items-center">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 z-10"><i data-lucide="lock" class="w-4.5 h-4.5"></i></div>
              <input id="password" name="password" type="password" placeholder="••••••••••••" required autocomplete="current-password" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 text-sm text-slate-900 dark:text-white rounded-xl border border-slate-200 dark:border-slate-800 outline-none focus:border-blue-500 shadow-inner" />
            </div>
          </div>

          <button type="submit" id="login-submit-btn" class="w-full py-3 px-4 rounded-xl font-semibold text-sm text-white shadow-lg bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md cursor-pointer flex items-center justify-center gap-2 transition-all">
            <i data-lucide="key" class="w-4 h-4"></i><span>Sign In to System</span>
          </button>
        </form>

        <div class="mt-4 text-center text-xs text-slate-500 dark:text-slate-400">
            New student arrival? <button type="button" onclick="toggleMode('register')" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline bg-transparent border-none cursor-pointer">Request Account Registration</button>
        </div>
      </div>
    </div>

  </div>

  <script>
    lucide.createIcons();

    const coursesByFaculty = {
        'Technology': ['CS', 'CT', 'ET'],
        'Management': ['FRCS', 'MGMT']
    };

    function populateCourses(facultyValue, selectedCourse = '') {
        const courseSelect = document.getElementById('course_select');
        courseSelect.innerHTML = '';
        if (!facultyValue) {
            courseSelect.innerHTML = '<option value="">Choose Faculty</option>';
            return;
        }
        coursesByFaculty[facultyValue].forEach(c => {
            const opt = document.createElement('option');
            opt.value = c; 
            opt.textContent = c;
            if (c === selectedCourse) opt.selected = true;
            courseSelect.appendChild(opt);
        });
    }

    document.getElementById('faculty_select').addEventListener('change', function() {
        populateCourses(this.value);
    });

    // Handle retention loops on server-side reloads flawlessly
    <?php if (!empty($_POST['faculty'])): ?>
      populateCourses('<?= addslashes($_POST['faculty']) ?>', '<?= addslashes($_POST['course'] ?? '') ?>');
    <?php endif; ?>

    // ============================================================
    // FLUID 1000MS ANIMATION & SMOOTH TEXT TRANSITION CROSS-FADE
    // ============================================================
    function toggleMode(mode) {
        const wallpaper = document.getElementById('wallpaper-panel');
        const leftForm = document.getElementById('left-form-container');
        const rightForm = document.getElementById('right-form-container');
        
        const textFade = document.getElementById('text-fade-container');
        const heading = document.getElementById('wallpaper-heading');
        const paragraph = document.getElementById('wallpaper-paragraph');
        const svg = document.getElementById('wallpaper-svg');
        
        const glowTop = document.getElementById('glow-top');
        const glowBottom = document.getElementById('glow-bottom');
        
        // FIXED: Instantly wipes alert feedback container markup to ensure errors are context-isolated
        const alertsBox = document.getElementById('alert-messages-wrapper');
        if(alertsBox) alertsBox.innerHTML = '';

        textFade.classList.add('opacity-0', 'scale-95');

        if (mode === 'register') {
            wallpaper.classList.remove('left-0', 'from-blue-900', 'via-indigo-950', 'to-slate-950');
            wallpaper.classList.add('left-1/2', 'from-purple-900', 'via-violet-950', 'to-slate-950');
            
            glowTop.classList.remove('bg-blue-500/10');
            glowTop.classList.add('bg-fuchsia-500/10');
            glowBottom.classList.remove('bg-emerald-500/10');
            glowBottom.classList.add('bg-purple-500/10');

            svg.style.transform = "rotate(180deg) scale(0.9)";

            setTimeout(() => {
                heading.textContent = "Your gateway to a universe of academic knowledge.";
                paragraph.textContent = "Join your university library network today. Create your pending student profile request matrix and wait for registrar approval clearance.";
                textFade.classList.remove('opacity-0', 'scale-95');
            }, 350);

            rightForm.classList.add('pointer-events-none', 'opacity-0', 'scale-95');
            rightForm.classList.remove('z-20', 'opacity-100', 'scale-100');
            
            leftForm.classList.add('z-20', 'opacity-100', 'pointer-events-auto', 'scale-100');
            leftForm.classList.remove('pointer-events-none', 'opacity-0', 'scale-95');
        } else {
            wallpaper.classList.remove('left-1/2', 'from-purple-900', 'via-violet-950', 'to-slate-950');
            wallpaper.classList.add('left-0', 'from-blue-900', 'via-indigo-950', 'to-slate-950');
            
            glowTop.classList.add('bg-blue-500/10');
            glowTop.classList.remove('bg-fuchsia-500/10');
            glowBottom.classList.add('bg-emerald-500/10');
            glowBottom.classList.remove('bg-purple-500/10');

            svg.style.transform = "rotate(0deg) scale(1)";

            setTimeout(() => {
                heading.textContent = "Every book, every borrower, beautifully managed.";
                paragraph.textContent = "Access millions of resources, manage catalog loans, request acquisitions, and verify reservations through our unified portal.";
                textFade.classList.remove('opacity-0', 'scale-95');
            }, 350);

            leftForm.classList.add('pointer-events-none', 'opacity-0', 'scale-95');
            leftForm.classList.remove('z-20', 'opacity-100', 'pointer-events-auto', 'scale-100');
            
            rightForm.classList.add('z-20', 'opacity-100', 'pointer-events-auto', 'scale-100');
            rightForm.classList.remove('pointer-events-none', 'opacity-0', 'scale-95');
        }
    }
  </script>
</body>
</html>