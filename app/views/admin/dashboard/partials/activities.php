<?php
$activityMeta = [
    'order' => ['icon' => 'ti-receipt', 'badge' => 'badge-primary'],
    'contact' => ['icon' => 'ti-email', 'badge' => 'badge-danger'],
    'comment' => ['icon' => 'ti-comment', 'badge' => 'badge-warning'],
];
?>
<div class="card h-100">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Hoạt động gần đây</h5>
            <?php if (!empty($mockFlags['activities'])): ?>
                <span class="badge badge-warning">mock data</span>
            <?php endif; ?>
        </div>

        <?php if (empty($activities)): ?>
            <p class="text-muted mb-0">Không có hoạt động mới.</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($activities as $activity): ?>
                    <?php
                    $type = (string)($activity['type'] ?? 'order');
                    $meta = $activityMeta[$type] ?? $activityMeta['order'];
                    ?>
                    <li class="list-group-item px-0">
                        <div class="d-flex align-items-start">
                            <span class="badge <?= htmlspecialchars($meta['badge']) ?> mr-2 mt-1"><i class="<?= htmlspecialchars($meta['icon']) ?>"></i></span>
                            <div>
                                <div class="small text-dark"><?= htmlspecialchars((string)($activity['text'] ?? '')) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars((string)date('d/m/Y H:i', strtotime((string)($activity['time'] ?? 'now')))) ?></div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>