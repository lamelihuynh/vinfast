/**
 * public/js/frontend/cart.js — AJAX Cart
 * Owner: Hai Nam (Member 3)
 *
 * Adds items to the cart via fetch() without a full page reload.
 * Reads CSRF token from <meta name="csrf-token">.
 * Updates cart badge count in the navbar after each successful add.
 *
 * Usage: add class="btn-add-cart" and data-product-id="ID" to any button.
 */
document.addEventListener("DOMContentLoaded", () => {

  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? "";

  document.querySelectorAll(".btn-add-cart").forEach(btn => {
    btn.addEventListener("click", async () => {
      const pid = btn.dataset.productId;
      const qty = btn.dataset.qty ?? 1;
      try {
        const res  = await fetch("cart/add", {
          method:"POST",
          headers:{"Content-Type":"application/x-www-form-urlencoded"},
          body:`product_id=${pid}&qty=${qty}&_csrf=${csrf()}`,
        });
        const data = await res.json();
        if (data.ok) {
          const badge = document.querySelector("#cart-badge");
          if (badge) badge.textContent = (parseInt(badge.textContent||"0") + 1).toString();
          const orig = btn.textContent;
          btn.textContent = "✓ Added";
          btn.disabled = true;
          setTimeout(() => { btn.textContent = orig; btn.disabled = false; }, 1800);
        } else {
          alert(data.msg ?? "Could not add to cart.");
        }
      } catch(e) { console.error("Cart error:", e); }
    });
  });

});
