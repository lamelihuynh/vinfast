/**
 * public/js/admin/main.js — Admin Panel JS
 * Owner: All members (common)
 *
 * Holds app-specific UI behavior layered on top of Srtdash.
 */
document.addEventListener("DOMContentLoaded", () => {
  // Confirm dangerous actions
  document.querySelectorAll("[data-confirm]").forEach(el => {
    el.addEventListener("click", e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  const pageContainer = document.querySelector('.page-container');
  const navBtn = document.querySelector('.nav-btn');
  const sidebar = document.querySelector('.sidebar-menu');

  if (!pageContainer || !navBtn || !sidebar) {
    return;
  }

  const backdrop = document.createElement('button');
  backdrop.type = 'button';
  backdrop.className = 'admin-sidebar-backdrop';
  backdrop.setAttribute('aria-label', 'Đóng menu');
  document.body.appendChild(backdrop);

  const isMobileViewport = () => window.innerWidth <= 1364;

  const syncBackdrop = () => {
    const isOpenOnMobile = isMobileViewport() && !pageContainer.classList.contains('sbar_collapsed');
    backdrop.classList.toggle('is-active', isOpenOnMobile);
    sidebar.setAttribute('aria-hidden', String(!isOpenOnMobile && isMobileViewport()));
  };

  const closeSidebar = () => {
    if (!isMobileViewport()) {
      return;
    }

    if (!pageContainer.classList.contains('sbar_collapsed')) {
      pageContainer.classList.add('sbar_collapsed');
      syncBackdrop();
    }
  };

  backdrop.addEventListener('click', closeSidebar);

  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeSidebar();
    }
  });

  window.addEventListener('resize', syncBackdrop);

  const observer = new MutationObserver(syncBackdrop);
  observer.observe(pageContainer, { attributes: true, attributeFilter: ['class'] });

  syncBackdrop();

});
