<?php
/**
 * app/views/frontend/partials/footer.php — Customer Footer
 * Owner: Tang Vu (Member 1) — initial build, others can extend
 *
 * Contact info is loaded from SiteSetting when available.
 */
?>
<footer class="bg-dark text-light pt-5 pb-3 mt-5">
  <div class="container">
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <img src="<?= BASE_URL ?>public/images/logo/vinfast-logo.png" alt="VinFast" height="40" class="mb-3">
        <p class="text-muted small">Vietnam's leading electric vehicle manufacturer.</p>
      </div>
      <div class="col-md-2">
        <h6 class="text-white mb-3">Quick Links</h6>
        <ul class="list-unstyled small">
          <li><a href="<?= BASE_URL ?>products" class="text-muted text-decoration-none">Vehicles</a></li>
          <li><a href="<?= BASE_URL ?>news"     class="text-muted text-decoration-none">News</a></li>
          <li><a href="<?= BASE_URL ?>about"    class="text-muted text-decoration-none">About</a></li>
          <li><a href="<?= BASE_URL ?>faq"      class="text-muted text-decoration-none">FAQ</a></li>
          <li><a href="<?= BASE_URL ?>contact"  class="text-muted text-decoration-none">Contact</a></li>
        </ul>
      </div>
      <div class="col-md-3">
        <h6 class="text-white mb-3">Contact</h6>
        <p class="text-muted small mb-1"><i class="fa fa-location-dot me-2"></i>TODO: load from SiteSetting</p>
        <p class="text-muted small mb-1"><i class="fa fa-phone me-2"></i>TODO</p>
        <p class="text-muted small"><i class="fa fa-envelope me-2"></i>TODO</p>
      </div>
    </div>
    <hr class="border-secondary">
    <p class="text-center text-muted small mb-0">&copy; <?= date('Y') ?> VinFast. All rights reserved.</p>
  </div>
</footer>
