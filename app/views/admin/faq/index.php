<?php
/**
 * app/views/admin/faq/index.php
 * Owner  : Nhat Linh 
 * Title  : FAQ List (SRTdash Style)
 *
 * Purpose: Data table with client-side search, sort, and pagination using Simple-DataTables
 *
 * Variables available (set by controller via View::render):
 *   $faqs (array) - All FAQ records
 *
 * -----------------------------------------------------------------------
 * Rules:
 *  - Always escape output: <?= htmlspecialchars($var) ?>
 *  - Include CSRF in every form: <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
 *  - Use Simple-DataTables for client-side pagination/search
 */
?>

<?php
$faqs = is_array($faqs ?? null) ? $faqs : [];
?>

<!-- SRTdash Data Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="header-title mb-0">
                            <i class="fa-solid fa-circle-question me-2 text-primary"></i>Quản Lý Hỏi & Đáp
                        </h4>
                        <small class="text-muted d-block mt-1">Quản lý các câu hỏi thường gặp trên trang chủ</small>
                    </div>
                    <a href="<?= ADMIN_URL ?>faq/create" class="btn btn-primary">
                        <i class="fa-solid fa-plus me-2"></i>Thêm Câu Hỏi Mới
                    </a>
                </div>

                <?php if (!empty($faqs)): ?>
                    <!-- Data Table Responsive -->
                    <div class="data-tables datatable-primary">
                        <table id="faqDataTable" class="table table-hover">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th>STT</th>
                                    <th>Câu Hỏi</th>
                                    <th>Chủ Đề</th>
                                    <th>Thứ Tự</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày Tạo</th>
                                    <th style="text-align: center;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($faqs as $index => $faq): 
                                    $isActive = (int)($faq['is_active'] ?? 0) === 1;
                                    $statusClass = $isActive ? 'bg-success' : 'bg-warning';
                                    $statusText = $isActive ? 'Hoạt động' : 'Đã bị ẩn';
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars(substr($faq['question'], 0, 70)) ?></strong>
                                        <?php if (strlen($faq['question']) > 70): ?>
                                            <span title="<?= htmlspecialchars($faq['question']) ?>">...</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= htmlspecialchars($faq['topic_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary text-white fw-bold"><?= (int)$faq['sort_order'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $statusClass ?> text-white fw-bold">
                                            <?php if ($isActive): ?>
                                                <i class="fa-solid fa-check me-1"></i><?= $statusText ?>
                                            <?php else: ?>
                                                <i class="fa-solid fa-ban me-1"></i><?= $statusText ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y', strtotime($faq['created_at'])) ?></small>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="<?= ADMIN_URL ?>faq/edit/<?= $faq['id'] ?>" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="if(confirm('Bạn chắc chắn muốn xóa câu hỏi này?')) deleteFaq(<?= $faq['id'] ?>)"
                                                title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="text-muted">
                            <i class="fa-solid fa-inbox" style="font-size:3em;opacity:0.3"></i>
                            <p class="mt-3">Chưa có câu hỏi nào.</p>
                            <a href="<?= ADMIN_URL ?>faq/create" class="btn btn-primary btn-sm mt-2">
                                <i class="fa-solid fa-plus me-1"></i>Thêm câu hỏi mới
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteFaqForm" method="POST" style="display: none;">
    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
</form>

<!-- Simple-DataTables CSS & JS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/simple-datatables@10/dist/style.min.css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@10/dist/umd/simple-datatables.min.js"></script>

<!-- Initialize Simple-DataTables -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dataTable = document.getElementById('faqDataTable');
    if (dataTable) {
        new simpleDatatables.DataTable(dataTable, {
            perPage: 10,
            perPageSelect: [5, 10, 15, 20],
            searchable: true,
            sortable: true,
            labels: {
                placeholder: "Tìm kiếm...",
                perPage: "câu hỏi mỗi trang",
                noRows: "Không tìm thấy kết quả",
                info: "Hiển thị {start} đến {end} của {rows} câu hỏi"
            }
        });
    }
});

function deleteFaq(id) {
    const form = document.getElementById('deleteFaqForm');
    form.action = '<?= ADMIN_URL ?>faq/delete/' + id;
    form.submit();
}
</script>