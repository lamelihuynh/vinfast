<?php
$statusLabel = static function (string $status): string {
    $map = [
        'unread' => 'Chưa đọc',
        'read' => 'Đã đọc',
        'replied' => 'Đã phản hồi',
    ];
    return $map[$status] ?? ucfirst($status);
};
?>
<div>
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0">Liên hệ gần đây</h5>
                <?php if (!empty($mockFlags['contacts'])): ?>
                    <span class="badge badge-warning">mock data</span>
                <?php endif; ?>
            </div>

            <?php if (empty($recentContacts)): ?>
                <p class="text-muted mb-0">Chưa có liên hệ.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($recentContacts as $contact): ?>
                        <?php $status = (string)($contact['status'] ?? 'unread'); ?>
                        <li class="list-group-item px-0 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="font-weight-bold"><?= htmlspecialchars((string)($contact['name'] ?? '')) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars((string)($contact['email'] ?? '')) ?></div>
                            </div>
                            <span class="badge badge-pill badge-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($statusLabel($status)) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Truy cập nhanh</h5>
            <div class="row">
                <?php foreach ($quickActions as $action): ?>
                    <div class="col-12 col-md-6 mb-2">
                        <a class="btn btn-outline-primary btn-sm btn-block text-left" href="<?= htmlspecialchars((string)($action['url'] ?? '#')) ?>">
                            <i class="<?= htmlspecialchars((string)($action['icon'] ?? 'ti-angle-right')) ?> mr-1"></i>
                            <?= htmlspecialchars((string)($action['label'] ?? 'Action')) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
