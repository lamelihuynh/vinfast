/**
 * public/js/frontend/main.js — Shared Frontend JS
 * Owner: All members (common)
 *
 * - Highlights active nav link based on current URL
 * - Scroll-to-top button
 * - Lazy-loads images with data-src attribute
 */
document.addEventListener("DOMContentLoaded", () => {

  // Active nav link
  document.querySelectorAll(".nav-link").forEach(a => {
    if (a.href === location.href) a.classList.add("active");
  });

  // Scroll-to-top button
  const btn = document.createElement("button");
  btn.id = "scrollTopBtn"; btn.textContent = "↑";
  document.body.appendChild(btn);
  addEventListener("scroll", () => btn.style.display = scrollY > 300 ? "block" : "none");
  btn.onclick = () => scrollTo({ top:0, behavior:"smooth" });

  // Lazy-load images
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver(es => es.forEach(e => {
      if (e.isIntersecting) { e.target.src = e.target.dataset.src; io.unobserve(e.target); }
    }));
    document.querySelectorAll("img[data-src]").forEach(img => io.observe(img));
  }

  // Shared header interactions
  const header = document.getElementById("vfHeader");
  if (header) {
    const onScroll = () => {
      if (window.scrollY > 60) header.classList.add("vf-header--compact");
      else header.classList.remove("vf-header--compact");
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  const userTrigger = document.getElementById("vfUserTrigger");
  const userDropdown = document.getElementById("vfUserDropdown");
  if (userTrigger && userDropdown) {
    userTrigger.addEventListener("click", (e) => {
      e.preventDefault();
      const willOpen = userDropdown.classList.contains("hidden");
      userDropdown.classList.toggle("hidden", !willOpen);
      userDropdown.classList.toggle("is-open", willOpen);
      const expanded = willOpen ? "true" : "false";
      userTrigger.setAttribute("aria-expanded", expanded);
    });

    document.addEventListener("click", (e) => {
      const target = e.target;
      if (!(target instanceof Node)) return;
      if (!userDropdown.contains(target) && !userTrigger.contains(target)) {
        userDropdown.classList.add("hidden");
        userDropdown.classList.remove("is-open");
        userTrigger.setAttribute("aria-expanded", "false");
      }
    });
  }

  const mobileOpen = document.getElementById("vfMobileToggle");
  const mobileClose = document.getElementById("vfMobileClose");
  const mobilePanel = document.getElementById("vfMobilePanel");
  const mobileOverlay = document.getElementById("vfMobileOverlay");

  const openMobilePanel = () => {
    if (!mobilePanel || !mobileOverlay) return;
    mobilePanel.classList.remove("translate-x-full");
    mobilePanel.classList.add("translate-x-0");
    mobileOverlay.classList.remove("opacity-0", "pointer-events-none");
    mobileOverlay.classList.add("opacity-100");
  };

  const closeMobilePanel = () => {
    if (!mobilePanel || !mobileOverlay) return;
    mobilePanel.classList.add("translate-x-full");
    mobilePanel.classList.remove("translate-x-0");
    mobileOverlay.classList.add("opacity-0", "pointer-events-none");
    mobileOverlay.classList.remove("opacity-100");
  };

  if (mobileOpen) mobileOpen.addEventListener("click", openMobilePanel);
  if (mobileClose) mobileClose.addEventListener("click", closeMobilePanel);
  if (mobileOverlay) mobileOverlay.addEventListener("click", closeMobilePanel);

  const langButtons = [
    document.getElementById("vfLangToggle"),
    document.getElementById("vfLangToggleMain"),
  ].filter(Boolean);
  let lang = "VI";

  const setLang = (next) => {
    lang = next;
    const topLabel = document.getElementById("vfLangLabel");
    if (topLabel) topLabel.textContent = lang;
    const mainLabel = document.querySelector("#vfLangToggleMain .vf-lang-toggle__label");
    if (mainLabel) mainLabel.textContent = lang;
  };

  langButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      setLang(lang === "VI" ? "EN" : "VI");
    });
  });

});
