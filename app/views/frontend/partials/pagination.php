<?php
/**
 * app/views/frontend/partials/pagination.php — Pagination Links
 * Owner: All members (common)
 *
 * Required variables in the view scope:
 *   $pg      — Pagination instance
 *   $pageUrl — base URL string ending with "page=" e.g. BASE_URL."products?q=x&page="
 */
if (isset($pg) && $pg->pages > 1): ?>
<nav aria-label="Page navigation" class="my-4">
  <ul class="pagination justify-content-center">
    <li class="page-item <?= $pg->hasPrev() ? '' : 'disabled' ?>">
      <a class="page-link" href="<?= $pageUrl . ($pg->current - 1) ?>">&#8592; Prev</a>
    </li>
    <?php for ($i = 1; $i <= $pg->pages; $i++): ?>
      <li class="page-item <?= $i === $pg->current ? 'active' : '' ?>">
        <a class="page-link" href="<?= $pageUrl . $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= $pg->hasNext() ? '' : 'disabled' ?>">
      <a class="page-link" href="<?= $pageUrl . ($pg->current + 1) ?>">Next &#8594;</a>
    </li>
  </ul>
</nav>
<?php endif; ?>
