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

});
