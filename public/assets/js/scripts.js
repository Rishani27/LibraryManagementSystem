// ============================================================
// ULMS — Global JavaScript
// assets/js/script.js
// ============================================================

'use strict';

// ── Modal helpers ─────────────────────────────────────────
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('active');
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('active');
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
  }
});

// ── Flash message auto-dismiss ────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 4000);
  });

  // ── Sidebar mobile toggle ─────────────────────────────
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar   = document.querySelector('.sidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
  }

  // ── Delete confirmation ───────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const msg = btn.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // ── Search live filter (client-side fallback) ─────────
  const searchInput = document.getElementById('live-search');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const q = this.value.toLowerCase();
      document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  // ── Print button ──────────────────────────────────────
  document.querySelectorAll('.btn-print').forEach(btn => {
    btn.addEventListener('click', () => window.print());
  });

  // ── Highlight overdue rows ────────────────────────────
  document.querySelectorAll('tr[data-overdue="1"]').forEach(row => {
    row.classList.add('overdue-row');
  });
});

// ── Populate a modal form field ───────────────────────────
function fillModal(modalId, data) {
  Object.entries(data).forEach(([key, value]) => {
    const el = document.querySelector(`#${modalId} [name="${key}"]`);
    if (el) el.value = value;
  });
}
