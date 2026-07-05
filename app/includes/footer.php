<?php $webBase = defined('WEB_BASE') ? WEB_BASE : '/library-system'; ?>
    </main>
  </div>
</div>

<script src="<?= $webBase ?>/public/assets/js/script.js"></script>
<script>
  // Safe icon creation
  try {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  } catch (e) {
    console.log("Lucide icons load waiting...");
  }

  // Master layout theme enforcement engine (Bulletproof)
  function applyThemeStyles(theme) {
    const html = document.documentElement;
    const icon = document.getElementById('dark-mode-icon');
    
    if (theme === 'light') {
      html.classList.remove('dark');
      if (icon) {
        icon.setAttribute('data-lucide', 'moon');
        icon.style.color = '#334155';
      }
    } else {
      html.classList.add('dark');
      if (icon) {
        icon.setAttribute('data-lucide', 'sun');
        icon.style.color = '#fbbf24';
      }
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }

  // Interactive Swapping Trigger
  function toggleDarkMode() {
    const activeTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
    document.cookie = "lms_theme=" + activeTheme + "; path=/; max-age=" + (365*24*60*60);
    applyThemeStyles(activeTheme);
  }

  // Pure JavaScript Sidebar Controller Engine
  function applySidebarState(state) {
    try {
      const sidebarEl = document.querySelector('aside') || document.querySelector('.sidebar');
      if (!sidebarEl) return;

      // Force programmatic transitions
      sidebarEl.style.setProperty('transition', 'all 0.25s cubic-bezier(0.4, 0, 0.2, 1)', 'important');

      if (state === 'collapsed') {
        sidebarEl.style.setProperty('width', '78px', 'important');
        sidebarEl.style.setProperty('min-width', '78px', 'important');
        sidebarEl.style.setProperty('max-width', '78px', 'important');
        
        sidebarEl.querySelectorAll('.sidebar-text-target, .sidebar-brand h2, .sidebar-brand span, .nav-section-title, .sidebar-footer, .sidebar-user div:last-child, span').forEach(el => {
          el.style.setProperty('display', 'none', 'important');
        });
      } else {
        sidebarEl.style.setProperty('width', '260px', 'important');
        sidebarEl.style.setProperty('min-width', '260px', 'important');
        sidebarEl.style.setProperty('max-width', '260px', 'important');
        
        sidebarEl.querySelectorAll('.sidebar-text-target, .sidebar-brand h2, .sidebar-brand span, .nav-section-title, .sidebar-footer, .sidebar-user div:last-child, span').forEach(el => {
          el.style.display = '';
        });
      }
    } catch (e) {
      console.error("Sidebar formatting error: ", e);
    }
  }

  function toggleSidebar() {
    const sidebarEl = document.querySelector('aside') || document.querySelector('.sidebar');
    if (!sidebarEl) return;
    
    // Check state based on current inline width settings
    const isCollapsed = sidebarEl.style.width === '78px';
    const nextState = isCollapsed ? 'expanded' : 'collapsed';
    
    // Store cookie parameter path state
    document.cookie = "lms_sidebar=" + nextState + "; path=/; max-age=" + (365*24*60*60);
    applySidebarState(nextState);
  }

  // Run on startup loop to pull Saved Session cookie parameters
  window.addEventListener('DOMContentLoaded', () => {
    // Process unstructured textual nodes inside navbar targets automatically
    try {
      document.querySelectorAll('.nav-item, .sidebar-brand, .sidebar-user, .sidebar-footer').forEach(el => {
        el.childNodes.forEach(node => {
          if (node.nodeType === Node.TEXT_NODE && node.nodeValue.trim() !== '') {
            const span = document.createElement('span');
            span.className = 'sidebar-text-target';
            span.textContent = node.nodeValue;
            el.replaceChild(span, node);
          }
        });
      });
    } catch (err) { console.log("Text nodes formatting skipped"); }

    // Read stored profile adjustments configurations
    try {
      const cookies = document.cookie.split('; ');
      
      // Theme parser
      const themeCookie = cookies.find(row => row.startsWith('lms_theme='));
      const savedTheme = themeCookie ? themeCookie.split('=')[1] : 'dark';
      applyThemeStyles(savedTheme);
      
      // Sidebar persistent state tracker parser
      const sidebarCookie = cookies.find(row => row.startsWith('lms_sidebar='));
      const savedSidebar = sidebarCookie ? sidebarCookie.split('=')[1] : 'expanded';
      applySidebarState(savedSidebar);
    } catch (e) {
      applyThemeStyles('dark');
    }
  });

  // User Profile dropdown menu visibility toggle
  function toggleProfileMenu() {
    try {
      const menu = document.getElementById('profile-dropdown');
      if (menu) {
        menu.classList.toggle('hidden');
      }
    } catch (e) {}
  }

  // Live Digital Clock counter loop updater
  setInterval(() => {
    try {
      const timeSpan = document.getElementById('live-time');
      if (timeSpan) {
        const now = new Date();
        timeSpan.innerText = now.toLocaleTimeString('en-US', {
          hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
      }
    } catch (e) {}
  }, 1000);
</script>
</body>
</html>