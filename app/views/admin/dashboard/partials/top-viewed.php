<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Sản phẩm xem nhiều</h5>
            <span class="text-muted small">30 ngày</span>
        </div>

        <?php if (empty($topViewed)): ?>
            <p class="text-muted mb-0">Chưa có lượt xem.</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($topViewed as $item): ?>
                    <?php $name = (string)($item['name'] ?? ''); ?>
                    <li class="list-group-item px-0 d-flex align-items-center justify-content-between">
                        <span>
                            <?= htmlspecialchars($name !== '' ? $name : '#' . (string)($item['productID'] ?? '')) ?>
                            <?php if ($name === ''): ?>
                                <span class="small text-muted">(đã xóa)</span>
                            <?php elseif (empty($item['isActive'])): ?>
                                <span class="small text-muted">(đã ẩn)</span>
                            <?php endif; ?>
                        </span>
                        <strong><?= (int)($item['views'] ?? 0) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
