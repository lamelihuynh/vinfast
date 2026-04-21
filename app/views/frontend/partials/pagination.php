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
    <div class="flex items-center justify-center gap-2">
      <a
        class="h-9 w-9 rounded-md border border-slate-200 flex items-center justify-center text-slate-500 transition <?= $pg->hasPrev() ? 'hover:bg-slate-100' : 'pointer-events-none opacity-40' ?>"
        href="<?= $pageUrl . ($pg->current - 1) ?>"
        aria-label="Previous page">
        <i class="fa-solid fa-chevron-left text-xs"></i>
      </a>

      <?php for ($i = 1; $i <= $pg->pages; $i++): ?>
        <a
          class="h-9 w-9 rounded-md flex items-center justify-center text-sm transition <?= $i === $pg->current ? 'bg-vfNavy text-white' : 'border border-slate-200 text-slate-600 hover:bg-slate-100' ?>"
          href="<?= $pageUrl . $i ?>"
          aria-label="Page <?= $i ?>"
          aria-current="<?= $i === $pg->current ? 'page' : 'false' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <a
        class="h-9 w-9 rounded-md border border-slate-200 flex items-center justify-center text-slate-500 transition <?= $pg->hasNext() ? 'hover:bg-slate-100' : 'pointer-events-none opacity-40' ?>"
        href="<?= $pageUrl . ($pg->current + 1) ?>"
        aria-label="Next page">
        <i class="fa-solid fa-chevron-right text-xs"></i>
      </a>
    </div>
  </nav>
<?php endif; ?>