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
                            <input type="text" class="form-control" name="name" id="modal_name" required>
                        </div>
                        <div class="col-md-5">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label mb-0">Danh mục <span class="text-danger">*</span></label>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-2" id="btnOpenCreateCategory">
                                        <i class="fa-solid fa-plus me-1"></i>Thêm danh mục
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnOpenDeleteCategory" title="Xóa danh mục đã chọn">
                                        <i class="fa-solid fa-trash me-1"></i>Xóa
                                    </button>
                                </div>
                            </div>
                            <select class="form-select" name="category_id" id="modal_category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars((string)$c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Giá bán (VND) <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="1000" class="form-control" name="price" id="modal_price" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea class="form-control" rows="3" name="description" id="modal_description"></textarea>
                        </div>

                        <div class="col-12">
                            <h6 class="mb-2">Thông số kỹ thuật</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quãng đường</label>
                            <input type="text" class="form-control" name="range" id="modal_range">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Công suất</label>
                            <input type="text" class="form-control" name="power" id="modal_power">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tăng tốc</label>
                            <input type="text" class="form-control" name="acceleration" id="modal_acceleration">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vận tốc tối đa</label>
                            <input type="text" class="form-control" name="max_speed" id="modal_max_speed">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pin / Dung lượng</label>
                            <input type="text" class="form-control" name="battery" id="modal_battery">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Số tiền đặt cọc (VND)</label>
                            <input type="number" min="0" step="1000" class="form-control" name="deposit_amount" id="modal_deposit_amount" value="15000000">
                        </div>


                        <!-- Hidden field for exterior colors (auto-filled from table by JS) -->
                        <input type="hidden" name="exterior_colors_raw" id="modal_exterior_colors_raw" value="">

                        <div class="col-12">
                            <label class="form-label">Tải ảnh mới</label>
                            <input type="file" class="form-control" name="images[]" id="modal_images" accept="image/jpeg,image/png,image/webp" multiple>
                            <small class="text-muted">JPG/PNG/WebP, tối đa 5MB mỗi ảnh. Hãy chọn 1 ảnh chính để hiển thị ưu tiên.</small>
                        </div>

                        <!-- Table editor for existing images -> color mapping (srtdash-style) -->
                        <div class="col-12 mt-3">
                            <h6 class="mb-2">Bảng ảnh & gán màu</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover" id="colorImageTable">
                                    <thead>
                                        <tr>
                                            <th class="w-12">Ảnh</th>
                                            <th>Filename</th>
                                            <th>Tên màu <span class="text-danger">*</span></th>
                                            <th>HEX</th>
                                            <th>Phụ thu</th>
                                            <th class="w-12">Mặc định</th>
                                            <th class="w-12">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAutoMatchColors">Auto-match từ filename</button>
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

<!-- Create Category Modal (for Product Modal flow) -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="createCategoryForm" class="d-flex flex-column">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-folder-plus me-2"></i>Thêm danh mục mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="createCategoryName" name="name" placeholder="Ví dụ: Sedan điện" required>
                    </div>
                    <div id="createCategoryFeedback" class="small d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="createCategorySubmitBtn">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Lưu danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteCategoryForm" class="d-flex flex-column">
                <div class="modal-header border-danger">
                    <h5 class="modal-title text-danger">
                        <i class="fa-solid fa-trash me-2"></i>Xóa danh mục
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc muốn xóa danh mục <strong id="deleteCategoryName"></strong>?</p>
                    <p class="small text-muted">Nếu danh mục đang có sản phẩm, thao tác sẽ bị từ chối.</p>
                    <div id="deleteCategoryFeedback" class="small d-none mt-2"></div>
                    <input type="hidden" id="deleteCategoryId" name="id" value="0">
                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger" id="deleteCategorySubmitBtn">Xóa danh mục</button>
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