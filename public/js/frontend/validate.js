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
  const character = document.getElementById("character");
  const uppercase = document.getElementById("uppercase");
  const special = document.getElementById("special");

  const setRuleState = (el, isValid) => {
    if (!el) return;
    el.classList.toggle("is-valid", isValid);
  };

  const validatePasswordRules = () => {
    if (!pw) return;

    const val = pw.value;
    const isLengthValid = val.length >= 8;
    const hasUpperAndLower = /[A-Z]/.test(val) && /[a-z]/.test(val);
    const hasNumber = /\d/.test(val);

    setRuleState(character, isLengthValid);
    setRuleState(uppercase, hasUpperAndLower);
    setRuleState(special, hasNumber);

    // Keep browser validation consistent with visual checklist.
    pw.setCustomValidity(isLengthValid && hasUpperAndLower && hasNumber ? "" : "Mật khẩu chưa đủ điều kiện.");

    if (cp) {
      cp.setCustomValidity(cp.value !== pw.value ? "Passwords do not match." : "");
    }
  };

  if (pw) {
    pw.addEventListener("input", validatePasswordRules);
    validatePasswordRules();
  }

  if (pw && cp) {
    cp.addEventListener("input", () => {
      cp.setCustomValidity(cp.value !== pw.value ? "Passwords do not match." : "");
    });
  }

  // Show/hide password controls
  document.querySelectorAll(".toggle-password").forEach(btn => {
    btn.addEventListener("click", () => {
      const targetId = btn.getAttribute("data-target");
      if (!targetId) return;

      const input = document.getElementById(targetId);
      if (!input) return;

      const show = input.type === "password";
      input.type = show ? "text" : "password";

      const openIcon = btn.querySelector(".eye-open");
      const closedIcon = btn.querySelector(".eye-closed");
      if (openIcon && closedIcon) {
        openIcon.classList.toggle("hidden", show);
        closedIcon.classList.toggle("hidden", !show);
      }

      btn.setAttribute("aria-pressed", show ? "true" : "false");
      btn.setAttribute("aria-label", show ? "Ẩn mật khẩu" : "Hiện mật khẩu");
    });
  });

});
