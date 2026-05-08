<?php
$topic = $topic ?? null;

$isEdit = !empty($topic);
?>

<div class="row">
    <div class="col-lg-8">

        <div class="card">
            <div class="card-body">

                <h4 class="mb-4">
                    <?= $isEdit ? 'Chỉnh sửa chủ đề' : 'Tạo chủ đề mới' ?>
                </h4>

                <form
                    method="POST"
                    action="<?= ADMIN_URL ?>faq-topic/save"
                >

                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= Auth::csrfToken() ?>"
                    >

                    <?php if ($isEdit): ?>

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$topic['id'] ?>"
                        >

                    <?php endif; ?>

                    <!-- NAME -->
                    <div class="mb-3">

                        <label class="form-label">
                            Tên chủ đề
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($topic['name'] ?? '') ?>"
                        >
                    </div>

                    <!-- SLUG -->
                    <div class="mb-3">

                        <label class="form-label">
                            Slug
                        </label>

                        <input
                            type="text"
                            name="slug"
                            class="form-control"
                            required
                            value="<?= htmlspecialchars($topic['slug'] ?? '') ?>"
                        >
                    </div>

                    

                    <!-- SVG -->
                    <div class="mb-3">

                        <label class="form-label">
                            SVG Icon
                        </label>

                        <textarea
                            name="icon_svg"
                            rows="8"
                            class="form-control"
                        ><?= htmlspecialchars($topic['icon_svg'] ?? '') ?></textarea>

                    </div>

                    <!-- SORT -->
                    <div class="mb-3">

                        <label class="form-label">
                            Thứ tự
                        </label>

                        <input
                            type="number"
                            name="sort_order"
                            class="form-control"
                            value="<?= (int)($topic['sort_order'] ?? 0) ?>"
                        >

                    </div>

                    <!-- ACTIVE -->
                    <div class="form-check mb-4">

                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="is_active"
                            id="is_active"
                            <?= ($topic['is_active'] ?? 1) ? 'checked' : '' ?>
                        >

                        <label class="form-check-label" for="is_active">
                            Hoạt động
                        </label>

                    </div>

                    <button class="btn btn-primary">

                        <i class="fa-solid fa-check me-2"></i>

                        <?= $isEdit ? 'Cập nhật' : 'Tạo chủ đề' ?>

                    </button>

                </form>

            </div>
        </div>

    </div>
</div>