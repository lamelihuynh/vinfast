/**
 * public/js/admin/main.js — Admin Panel JS
 * Owner: All members (common)
 *
 * Handles the Srtdash sidebar toggle and any shared admin UI.
 */
document.addEventListener("DOMContentLoaded", () => {

  const toggle = document.getElementById("header-toggle");
  const nav    = document.getElementById("nav-bar");
  const body   = document.getElementById("body-pd");
  const icon   = document.getElementById("header-icon");

  if (toggle) {
    toggle.addEventListener("click", () => {
      nav.classList.toggle("show");
      body.classList.toggle("body-pd");
      icon.classList.toggle("fa-bars");
      icon.classList.toggle("fa-xmark");
    });
  }

  // Confirm dangerous actions
  document.querySelectorAll("[data-confirm]").forEach(el => {
    el.addEventListener("click", e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

});
