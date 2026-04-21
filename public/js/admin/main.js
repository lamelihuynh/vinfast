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

});
