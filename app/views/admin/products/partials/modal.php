<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <?php if (!isset($cats) || !is_array($cats)) {
                $cats = [];
            } ?>
            <form method="POST" action="<?= ADMIN_URL ?>products/save" enctype="multipart/form-data" id="productModalForm" class="d-flex flex-column" style="max-height: calc(100vh - 3.5rem);">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">Chỉnh sửa sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body overflow-auto" style="max-height: calc(100vh - 12rem);">
                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                    <input type="hidden" name="id" id="modal_id" value="0">
                    <input type="hidden" name="slug" id="modal_slug" value="">
                    <input type="hidden" name="main_image" id="modal_main_image" value="">
                    <input type="hidden" name="main_new_index" id="modal_main_new_index" value="">

                    <div class="row g-3 align-items-start">
                        <div class="col-md-7">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="modal_name" maxlength="120" required>
                            <small class="text-muted">Tối đa 120 ký tự.</small>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label mb-1">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="modal_category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars((string)$c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 js-vehicle-only">
                            <label class="form-label">Giá bán (VND) <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="1000" class="form-control" name="price" id="modal_price" required>
                            <!-- Xe từ 2 phiên bản: ô giá bị khóa, nội dung do product-modal.js điền -->
                            <div class="form-text text-warning d-none" id="modalPriceLockNote"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" rows="3" name="description" id="modal_description" maxlength="2000"></textarea>
                            <small class="text-muted">Tối đa 2000 ký tự.</small>
                        </div>

                        <!-- Thuộc tính động theo category (render bởi product-modal.js cho cả xe và phụ kiện, T1-14) -->
                        <div class="col-12 d-none" id="dynamicAttrsSection">
                            <h6 class="mb-2" id="dynamicAttrsTitle">Thông số &amp; Thuộc tính</h6>
                            <div class="row g-3" id="accessoryAttrsContainer"></div>
                        </div>

                        <!-- Phụ kiện: phiên bản (mỗi phiên bản có giá + tồn kho riêng) -->
                        <div class="col-12 js-accessory-only d-none" id="accessoryVariantsSection">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0">Phiên bản <span class="text-danger">*</span></h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddVariant">
                                    <i class="fa-solid fa-plus me-1"></i>Thêm phiên bản
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-1" id="accessoryVariantsTable">
                                    <thead class="table-light"></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <small class="text-muted">Tên phiên bản tự gợi ý từ thuộc tính (có thể sửa). Để trống tồn kho để giữ nguyên số hiện tại; phiên bản mới mặc định tồn kho 0.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tải ảnh mới</label>
                            <input type="file" class="form-control" name="images[]" id="modal_images" accept="image/jpeg,image/png,image/webp" multiple>
                            <small class="text-muted">JPG/PNG/WebP, tối đa 5MB mỗi ảnh. Hãy chọn 1 ảnh chính để hiển thị ưu tiên.</small>
                        </div>

                        <!-- Table editor for existing images -> color mapping (srtdash-style) -->
                        <div class="col-12 mt-3">
                            <h6 class="mb-2" id="imageTableTitle">Bảng ảnh & gán màu</h6>
                            <div class="form-text text-warning mb-2 d-none" id="modalColorLockNote">Màu ở bảng này áp dụng cho phiên bản đầu tiên; màu của các phiên bản khác giữ nguyên.</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="colorImageTable">
                                    <thead>
                                        <tr>
                                            <th class="w-12">Ảnh</th>
                                            <th>Filename</th>
                                            <th class="js-vehicle-col">Tên màu <span class="text-danger">*</span></th>
                                            <th class="js-vehicle-col">HEX</th>
                                            <th class="js-vehicle-col">Phụ thu</th>
                                            <th class="w-12" id="imageTableDefaultTh">Mặc định</th>
                                            <th class="w-12">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="newImagesSection">
                            <label class="form-label">Xem trước ảnh mới</label>
                            <div class="row g-2" id="newImagesContainer"></div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="modal_is_active" checked>
                                <label class="form-check-label" for="modal_is_active">Hiển thị trên website</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="productModalSubmitBtn">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="deleteProductForm" class="d-contents">
                <div class="modal-header border-danger">
                    <h5 class="modal-title text-danger">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Xóa sản phẩm
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Bạn có chắc chắn muốn xóa sản phẩm <strong id="deleteProductName"></strong>?</p>
                    <p class="text-muted small mt-2 mb-0">Thao tác này không thể hoàn tác.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-2"></i>Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>