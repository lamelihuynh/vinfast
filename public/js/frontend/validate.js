/**
 * public/js/frontend/validate.js — Client-side Form Validation
 * Owner: All members (common)
 *
 * Uses Bootstrap 5 validation classes (was-validated / invalid-feedback).
 * Server-side validation in Validator.php is the authoritative check;
 * this file only provides faster feedback to the user.
 *
 * Add class="needs-validation" and novalidate to any form to enable.
 */
document.addEventListener("DOMContentLoaded", () => {

  // Bootstrap constraint validation trigger
  document.querySelectorAll("form.needs-validation").forEach(form => {
    form.addEventListener("submit", e => {
      if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
      form.classList.add("was-validated");
    });
  });

  // Password confirmation
  const pw = document.getElementById("password");
  const cp = document.getElementById("confirm_password");
  if (pw && cp) cp.addEventListener("input", () =>
    cp.setCustomValidity(cp.value !== pw.value ? "Passwords do not match." : ""));

});
