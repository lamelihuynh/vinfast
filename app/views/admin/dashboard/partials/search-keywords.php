<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Từ khóa tìm nhiều</h5>
            <span class="text-muted small">30 ngày</span>
        </div>

        <?php if (empty($searchKeywords)): ?>
            <p class="text-muted mb-0">Chưa có lượt tìm kiếm.</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($searchKeywords as $kw): ?>
                    <li class="list-group-item px-0 d-flex align-items-center justify-content-between">
                        <span><?= htmlspecialchars((string)($kw['keyword'] ?? '')) ?></span>
                        <span class="small text-muted">
                            <strong class="text-dark"><?= (int)($kw['count'] ?? 0) ?></strong> lượt · <?= (int)($kw['uniqueUsers'] ?? 0) ?> người
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
