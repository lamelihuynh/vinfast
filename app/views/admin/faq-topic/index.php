<?php
$topics = is_array($topics ?? null) ? $topics : [];
?>

<div class="row">
    <div class="col-12">

        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="header-title mb-0">
                            <i class="fa-solid fa-layer-group me-2 text-primary"></i>
                            Quản Lý Chủ Đề FAQ
                        </h4>

                        <small class="text-muted">
                            Quản lý nhóm câu hỏi FAQ
                        </small>
                    </div>

                    <a href="<?= ADMIN_URL ?>faq-topic/create"
                       class="btn btn-primary">

                        <i class="fa-solid fa-plus me-2"></i>
                        Thêm Chủ Đề
                    </a>
                </div>

                <?php if (!empty($topics)): ?>

                    <div class="table-responsive">
                        
                        <!-- CHỈ THÊM ID: id="topicDataTable" VÀO ĐÂY -->
                        <table id="topicDataTable" class="table table-hover align-middle">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên Chủ Đề</th>
                                    <th>Slug</th>
                                    <th>Thứ Tự</th>
                                    <th>Trạng Thái</th>
                                    <th class="text-center">Hành Động</th>
                                </tr>
                            </thead>

                            <tbody>

                            <?php foreach ($topics as $topic): ?>

                                <tr>

                                    <td>
                                        <?= (int)$topic['id'] ?>
                                    </td>


                                    <td>
                                        <div class="fw-bold">
                                            <?= htmlspecialchars($topic['name']) ?>
                                        </div>

                                        <?php if (!empty($topic['icon_svg'])): ?>
                                            <div style="width:28px;height:28px;">
                                                <?= $topic['icon_svg'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($topic['slug']) ?>
                                    </td>

                                    <td>
                                        <?= (int)$topic['sort_order'] ?>
                                    </td>

                                    <td>

                                        <?php if ((int)$topic['is_active'] === 1): ?>

                                            <span class="badge bg-success">
                                                Hoạt động
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                Đã ẩn
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="text-center">

                                        <a href="<?= ADMIN_URL ?>faq-topic/edit/<?= $topic['id'] ?>"
                                           class="btn btn-sm btn-outline-primary">

                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <form
                                            method="POST"
                                            action="<?= ADMIN_URL ?>faq-topic/delete/<?= $topic['id'] ?>"
                                            class="d-inline"
                                        >
                                            <input
                                                type="hidden"
                                                name="_csrf"
                                                value="<?= Auth::csrfToken() ?>"
                                            >

                                            <button
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Xóa chủ đề này?')"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5">

                        <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>

                        <p class="text-muted">
                            Chưa có chủ đề FAQ nào.
                        </p>

                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<!-- THÊM ĐOẠN SCRIPT NÀY ĐỂ KÍCH HOẠT PHÂN TRANG -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@10/dist/style.min.css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@10/dist/umd/simple-datatables.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableEl = document.getElementById('topicDataTable');
    if (tableEl) {
        new simpleDatatables.DataTable(tableEl, {
            perPage: 10, // Số dòng trên mỗi trang
            labels: {
                placeholder: "Tìm kiếm...",
                perPage: "mục mỗi trang",
                noRows: "Không tìm thấy dữ liệu",
                info: "Hiển thị {start} đến {end} của {rows} mục"
            }
        });
    }
});
</script>