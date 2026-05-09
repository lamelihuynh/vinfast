<div class="card-body border-top pt-3 mt-2">
    <?php if (isset($pg) && $pg->total > 0): ?>
        <div class="d-flex align-items-center justify-content-between">
            <small class="text-muted">
                Hiển thị <strong><?= (($pg->current - 1) * $pg->perPage) + 1 ?></strong> đến
                <strong><?= min($pg->current * $pg->perPage, $pg->total) ?></strong>
                trên tổng <strong><?= $pg->total ?></strong> <?= htmlspecialchars($itemName ?? 'bản ghi') ?>
            </small>
            
            <?php if ($pg->pages > 1): ?>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $pg->hasPrev() ? '' : 'disabled' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($pageUrl . ($pg->current - 1)) ?>" <?= $pg->hasPrev() ? '' : 'disabled' ?>>
                            <i class="fa-solid fa-chevron-left"></i> Trước
                        </a>
                    </li>

                    <?php
                    $startPage = max(1, $pg->current - 2);
                    $endPage = min($pg->pages, $pg->current + 2);

                    if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl . 1) ?>">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i === $pg->current ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl . $i) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    if ($endPage < $pg->pages):
                        if ($endPage < $pg->pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl . $pg->pages) ?>"><?= $pg->pages ?></a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?= $pg->hasNext() ? '' : 'disabled' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($pageUrl . ($pg->current + 1)) ?>" <?= $pg->hasNext() ? '' : 'disabled' ?>>
                            Sau <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
