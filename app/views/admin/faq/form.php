<?php
/**
 * app/views/admin/faq/form.php
 * Owner  : Nhat Linh 
 * Title  : FAQ Form
 *
 * Purpose: Question input (max 500), answer textarea (max 2000), sort order number, active checkbox. Hidden id field for edit. CSRF token. POSTs to /admin/faq/save.
 *
 * Variables available (set by controller via View::render):
 *   $faq (array|null — null for new), $action (string: 'create' or 'edit')
 *
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Include pagination partial where needed: include ROOT."/app/views/frontend/partials/pagination.php"
 */
?>

<?php
$faq = $faq ?? null;
$action = $action ?? 'create';
$isEdit = ($action === 'edit' && $faq !== null);
$title = $isEdit ? 'Chỉnh Sửa Câu Hỏi' : 'Tạo Câu Hỏi Mới';
$pageTitle = $isEdit ? 'Chỉnh sửa' : 'Tạo mới';

// Get error messages from session
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>

<!-- Page Header -->
<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h4 class="mb-1"><?= htmlspecialchars($title) ?></h4>
        <small class="text-muted">
            <i class="fa-solid fa-layer-group me-1"></i>
            Dashboard / Hỏi & Đáp / <?= htmlspecialchars($pageTitle) ?>
        </small>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="<?= ADMIN_URL ?>faq" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay Lại
        </a>
    </div>
</div>

<!-- Form Card -->
<div class="card">
    <div class="card-body">
                <!-- Display validation errors if any -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fa-solid fa-circle-exclamation me-2"></i>Lỗi:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $field => $message): ?>
                                <li><?= htmlspecialchars($message) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" action="<?= ADMIN_URL ?>faq/save">
                    <!-- CSRF Token -->
                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                    
                    <!-- FAQ ID (for edit) -->
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= (int)$faq['id'] ?>">
                    <?php endif; ?>
                    <!-- Topic -->
                    <div class="mb-3">
                        <label for="topic_id" class="form-label">
                            <strong>Chủ Đề <span class="text-danger">*</span></strong>
                        </label>

                        <select
                            class="form-select <?= isset($errors['topic_id']) ? 'is-invalid' : '' ?>"
                            id="topic_id"
                            name="topic_id"
                            required
                        >
                            <option value="">-- Chọn chủ đề --</option>

                            <?php foreach ($topics as $topic): ?>
                                <option
                                    value="<?= (int)$topic['id'] ?>"
                                    <?= ((int)($faq['topic_id'] ?? 0) === (int)$topic['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($topic['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if (isset($errors['topic_id'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['topic_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Question Field -->
                    <div class="mb-3">
                        <label for="question" class="form-label">
                            <strong>Câu Hỏi <span class="text-danger">*</span></strong>
                        </label>
                        <input 
                            type="text" 
                            class="form-control <?= isset($errors['question']) ? 'is-invalid' : '' ?>"
                            id="question" 
                            name="question" 
                            maxlength="500"
                            placeholder="Nhập câu hỏi (tối đa 500 ký tự)"
                            value="<?= htmlspecialchars($faq['question'] ?? '') ?>"
                            required
                        >
                        <?php if (isset($errors['question'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['question']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Answer Field -->
                    <div class="mb-3">
                        <label for="answer" class="form-label">
                            <strong>Câu Trả Lời <span class="text-danger">*</span></strong>
                        </label>
                        <textarea 
                            class="form-control <?= isset($errors['answer']) ? 'is-invalid' : '' ?>"
                            id="answer" 
                            name="answer" 
                            rows="8"
                            placeholder="Nhập câu trả lời chi tiết"
                            required
                        ><?= htmlspecialchars($faq['answer'] ?? '') ?></textarea>
                        <?php if (isset($errors['answer'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['answer']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sort Order -->
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">
                            <strong>Thứ Tự Hiển Thị</strong>
                        </label>
                        <input 
                            type="number" 
                            class="form-control"
                            id="sort_order" 
                            name="sort_order" 
                            value="<?= (int)($faq['sort_order'] ?? 0) ?>"
                            min="0"
                        >
                        <small class="form-text text-muted d-block mt-1">Số nhỏ hiển thị trước, số lớn hiển thị sau</small>
                    </div>

                    <!-- Active Status -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input 
                                type="checkbox" 
                                class="form-check-input" 
                                id="is_active" 
                                name="is_active" 
                                value="1"
                                <?= ($faq['is_active'] ?? 1) ? 'checked' : '' ?>
                            >
                            <label class="form-check-label" for="is_active">
                                <strong>Hoạt động</strong> (Hiển thị trên trang frontend)
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-check me-2"></i>
                            <?= $isEdit ? 'Cập Nhật' : 'Tạo Mới' ?>
                        </button>
                        <a href="<?= ADMIN_URL ?>faq" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-xmark me-2"></i>Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-light border-0">
                <h5 class="card-title mb-0">
                    <i class="fa-solid fa-circle-info me-2 text-info"></i>Hướng Dẫn
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="text-muted small mb-1"><strong>Câu Hỏi:</strong></p>
                    <p class="text-muted small">Phần tiêu đề câu hỏi của người dùng. Tối đa 500 ký tự.</p>
                </div>
                <div class="mb-3">
                    <p class="text-muted small mb-1"><strong>Câu Trả Lời:</strong></p>
                    <p class="text-muted small">Phần nội dung trả lời chi tiết. Có thể chứa nhiều dòng.</p>
                </div>
                <div class="mb-3">
                    <p class="text-muted small mb-1"><strong>Thứ Tự:</strong></p>
                    <p class="text-muted small">Số lớn được hiển thị sau. Để 0 để xuất hiện trước.</p>
                </div>
                <div>
                    <p class="text-muted small mb-1"><strong>Hoạt động:</strong></p>
                    <p class="text-muted small">Nếu không chọn, câu hỏi sẽ không hiển thị trên trang frontend.</p>
                </div>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="card mt-3">
            <div class="card-header bg-light border-0">
                <h5 class="card-title mb-0">
                    <i class="fa-solid fa-eye me-2 text-info"></i>Xem Trước
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Câu hỏi này sẽ xuất hiện trên trang <a href="<?= BASE_URL ?>faq" target="_blank" class="text-decoration-none">Hỏi & Đáp <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a> khi hoạt động.
                </p>
            </div>
        </div>
    </div>
</div>