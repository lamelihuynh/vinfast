<?php
/**
 * app/views/admin/news/index.php
 * Owner  : Nhat Tan (Member 4)
 * Title  : News List
 *
 * Purpose: Search bar. Paginated table: thumbnail, title, author, date, edit/delete actions.
 *
 * Variables available (set by controller via View::render):
 *   $articles (array), $q (string), $pg (Pagination)
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
$totalNews     = News::count();
$publishedNews = News::count('', '', 'Hiển thị');
$hiddenNews    = News::count('', '', 'Ẩn');

$current_page = $pg->current ?? 1;
$total_items  = $pg->total ?? $totalNews;
$start_item   = ($current_page - 1) * 10 + 1;
$end_item     = min($start_item + 9, $total_items);

$sort = $_GET['sort'] ?? 'time';
?>

<div class="row">
    <div class="col-lg-12">
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card text-white bg-primary h-100 shadow-sm" style="border: none;">
                    <div class="card-body p-4">
                        <div class="text-white-50 text-uppercase font-weight-bold mb-2" style="font-size: 13px;">Tổng bài viết</div>
                        <h2 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem;"><?= number_format($totalNews) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="card text-white h-100 shadow-sm" style="background-color: #10b981 !important; border: none;">
                    <div class="card-body p-4">
                        <div class="text-white-50 text-uppercase font-weight-bold mb-2" style="font-size: 13px;">Đã đăng</div>
                        <h2 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem;"><?= number_format($publishedNews) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white h-100 shadow-sm" style="background-color: #64748b !important; border: none;">
                    <div class="card-body p-4">
                        <div class="text-white-50 text-uppercase font-weight-bold mb-2" style="font-size: 13px;">Đã ẩn</div>
                        <h2 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem;"><?= number_format($hiddenNews) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm" style="border-radius: 8px; border: none;">
            <div class="card-body">
                <form action="<?= ADMIN_URL ?>news" method="GET" class="w-100 m-0">
                    <div class="row align-items-center" style="row-gap: 15px;">
                        
                        <div class="col-12 col-md-5 d-flex align-items-center" style="gap: 10px;">
                            <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Tìm tiêu đề..." class="form-control">
                            <button type="submit" class="btn btn-primary text-nowrap">Tìm kiếm</button>
                            <?php if (!empty($q)): ?>
                                <a href="<?= ADMIN_URL ?>news" class="text-danger text-nowrap">Xóa lọc</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-12 col-md-7 d-flex flex-column flex-sm-row justify-content-md-end align-items-stretch align-items-sm-center" style="gap: 10px;">
                            <select name="sort" class="form-control" onchange="this.form.submit()" style="max-width: 100%; width: auto; cursor: pointer; border: 1px solid #ced4da; background-color: #f8f9fa;">
                                <option value="time" <?= $sort === 'time' ? 'selected' : '' ?>>Sắp xếp: Thời gian đăng</option>
                                <option value="id_asc" <?= $sort === 'id_asc' ? 'selected' : '' ?>>Sắp xếp: ID Tăng dần</option>
                            </select>
                            <a href="<?= ADMIN_URL ?>news/create" class="btn btn-success text-nowrap text-center">+ Thêm bài viết</a>
                        </div>
                        
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm" style="border-radius: 8px; border: none;">
            <div class="card-body">
                <h4 class="header-title">Danh sách bài viết</h4>
                <div class="single-table">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="text-uppercase bg-light">
                                <tr>
                                    <th scope="col" style="width: 5%;">ID</th>
                                    <th scope="col" class="text-left" style="min-width: 250px;">Bài viết</th>
                                    <th scope="col" style="min-width: 120px;">Danh mục</th>
                                    <th scope="col" style="min-width: 100px;">Lượt xem</th>
                                    <th scope="col" style="min-width: 120px;">Trạng thái</th>
                                    <th scope="col" style="min-width: 110px;">Ngày tạo</th>
                                    <th scope="col" style="min-width: 120px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($articles)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Không tìm thấy bài viết nào phù hợp.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($articles as $art): ?>
                                        <tr>
                                            <th scope="row" class="align-middle"><?= $art['id'] ?></th>
                                            
                                            <td class="text-left align-middle" style="max-width: 250px;">
                                                <div class="d-flex align-items-center">
                                                    
                                                    <?php 
                                                        $thumbUrl = '';
                                                        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                                                        
                                                        foreach ($extensions as $ext) {
                                                            if (file_exists(ROOT . "/public/images/news/{$art['id']}/thumbnail.{$ext}")) {
                                                                $thumbUrl = BASE_URL . "public/images/news/{$art['id']}/thumbnail.{$ext}";
                                                                break;
                                                            }
                                                        }
                                                        
                                                        if ($thumbUrl === '') {
                                                            foreach ($extensions as $ext) {
                                                                if (file_exists(ROOT . "/public/images/news/{$art['id']}/1.{$ext}")) {
                                                                    $thumbUrl = BASE_URL . "public/images/news/{$art['id']}/1.{$ext}";
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        
                                                        
                                                        if ($thumbUrl === '') {
                                                            $dbImage = !empty($art['img_link']) ? str_replace('\\', '/', $art['img_link']) : '';
                                                            $cleanPath = preg_replace('#^/?vinfast/#', '', ltrim($dbImage, '/'));
                                                            $thumbUrl = !empty($cleanPath) ? BASE_URL . $cleanPath : 'https://via.placeholder.com/150x100?text=No+Image';
                                                        }
                                                    ?>
                                                    <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="Thumbnail" style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px; flex-shrink: 0;" class="mr-3 border border-gray-200 shadow-sm">
                                                    
                                                    <div style="min-width: 0; flex: 1;">
                                                        <div class="font-weight-bold text-dark text-truncate" title="<?= htmlspecialchars($art['title']) ?>">
                                                            <?= htmlspecialchars($art['title']) ?>
                                                        </div>
                                                        <div class="text-muted small text-truncate">
                                                            <?= htmlspecialchars($art['slug']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="align-middle">
                                                <span style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; display: inline-block;">
                                                    <?= htmlspecialchars($art['catalog'] ?? 'Chưa phân loại') ?>
                                                </span>
                                            </td>

                                            <td class="align-middle"><?= number_format((int)($art['views'] ?? 0)) ?></td>

                                            <td class="align-middle">
                                                <?php if (($art['news_state'] ?? '') === 'Hiển thị'): ?>
                                                    <span style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; display: inline-block;">
                                                        Hiển thị
                                                    </span>
                                                <?php else: ?>
                                                    <span style="background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; display: inline-block;">
                                                        Ẩn
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="align-middle"><?= date('d/m/Y', strtotime($art['created_at'])) ?></td>

                                            <td class="align-middle">
                                                <ul class="d-flex justify-content-center list-unstyled mb-0 gap-2">
                                                    <li class="mr-3">
                                                        <a href="<?= ADMIN_URL ?>news/show/<?= $art['id'] ?>" class="text-secondary" title="Xem chi tiết"><i class="ti-eye"></i></a>
                                                    </li>
                                                    <li class="mr-3">
                                                        <a href="<?= ADMIN_URL ?>news/edit/<?= $art['id'] ?>" class="text-warning" title="Chỉnh sửa"><i class="ti-pencil"></i></a>
                                                    </li>
                                                    <li>
                                                        <form action="<?= ADMIN_URL ?>news/delete/<?= $art['id'] ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
                                                            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                                                            <button type="submit" class="text-danger border-0 bg-transparent p-0" title="Xóa bài viết" style="cursor: pointer;"><i class="ti-trash"></i></button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($total_items > 0): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="small text-muted">
                        Hiển thị <strong><?= $start_item ?>-<?= $end_item ?></strong> / <strong><?= $total_items ?></strong> kết quả
                    </div>
                    <?php if (isset($pg) && $pg->pages > 1): ?>
                        <ul class="pagination mb-0">
                            <?php if ($pg->current > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $pg->current - 1 ?>&q=<?= urlencode($q) ?>&sort=<?= urlencode($sort) ?>">Trước</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pg->pages; $i++): ?>
                                <li class="page-item <?= $i === $pg->current ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($q) ?>&sort=<?= urlencode($sort) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pg->current < $pg->pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= $pg->current + 1 ?>&q=<?= urlencode($q) ?>&sort=<?= urlencode($sort) ?>">Sau</a></li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>