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

  const setRuleState = (el, isValid) => {
    if (!el) return;
    el.classList.toggle("is-valid", isValid);
  };

  document.querySelectorAll("form.needs-validation").forEach(form => {
    const passwordInput = form.querySelector("#password, #new_password");
    const confirmInput = form.querySelector("#confirm_password");
    const currentInput = form.querySelector("#current");
    const character = form.querySelector("#character");
    const uppercase = form.querySelector("#uppercase");
    const special = form.querySelector("#special");
    const notSame = form.querySelector("#notSame");

    const shouldValidateStrength = Boolean(confirmInput) || (passwordInput && passwordInput.id === "new_password");
    if (!passwordInput || !shouldValidateStrength) {
      return;
    }

    const validatePasswordRules = () => {
      const val = passwordInput.value;
      if (val === "") {
        passwordInput.setCustomValidity("");
        if (confirmInput) {
          confirmInput.setCustomValidity("");
        }
        setRuleState(character, false);
        setRuleState(uppercase, false);
        setRuleState(special, false);
        return;
      }

      const isLengthValid = val.length >= 8;
      const hasUpperAndLower = /[A-Z]/.test(val) && /[a-z]/.test(val);
      const hasNumber = /\d/.test(val);
      const sameAsCurrent = Boolean(currentInput && currentInput.value && currentInput.value === val);

      setRuleState(character, isLengthValid);
      setRuleState(uppercase, hasUpperAndLower);
      setRuleState(special, hasNumber);
      setRuleState(notSame, !sameAsCurrent || !val);

      // Keep browser validation consistent with the visible checklist.
      passwordInput.setCustomValidity(
        isLengthValid && hasUpperAndLower && hasNumber && !sameAsCurrent
          ? ""
          : (sameAsCurrent ? "Mật khẩu mới không được giống mật khẩu hiện tại." : "Mật khẩu chưa đủ điều kiện.")
      );

      if (confirmInput) {
        confirmInput.setCustomValidity(confirmInput.value !== passwordInput.value ? "Passwords do not match." : "");
      }
    };

    passwordInput.addEventListener("input", validatePasswordRules);
    validatePasswordRules();

    if (confirmInput) {
      confirmInput.addEventListener("input", () => {
        confirmInput.setCustomValidity(confirmInput.value !== passwordInput.value ? "Passwords do not match." : "");
      });
    }
  });

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
