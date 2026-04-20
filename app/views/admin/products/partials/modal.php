<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="<?= ADMIN_URL ?>products/save" enctype="multipart/form-data" id="productModalForm" class="d-flex flex-column" style="max-height: calc(100vh - 3.5rem);">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">Chỉnh sửa sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body overflow-auto" style="max-height: calc(100vh - 12rem);">
                    <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
                    <input type="hidden" name="id" id="modal_id" value="0">
                    <input type="hidden" name="main_image" id="modal_main_image" value="">
                    <input type="hidden" name="main_new_index" id="modal_main_new_index" value="">

                    <div class="row g-3 align-items-start">
                        <div class="col-md-7">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="modal_name" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="modal_category_id" required>
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars((string)$c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">URL Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="slug" id="modal_slug" required>
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

                        <div class="col-12">
                            <label class="form-label">Màu ngoại thất (mỗi dòng 1 màu)</label>
                            <textarea class="form-control" rows="5" name="exterior_colors_raw" id="modal_exterior_colors_raw" placeholder="CE18|Infinity Blanc|#E5E5E5&#10;CE1V|Zenith Grey|#8B9284&#10;CE2Q|Crimson Red|uploads/products/CE2Q.webp|#8A1C2B"></textarea>
                            <small class="text-muted">Nhập nhanh: MA_MAU|TEN_MAU|#HEX. Nếu có ảnh riêng theo màu: MA_MAU|TEN_MAU|uploads/products/CE18.webp|#HEX</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Tải ảnh mới</label>
                            <input type="file" class="form-control" name="images[]" id="modal_images" accept="image/jpeg,image/png,image/webp" multiple>
                            <small class="text-muted">JPG/PNG/WebP, tối đa 2MB mỗi ảnh. Hãy chọn 1 ảnh chính để hiển thị ưu tiên.</small>
                        </div>

                        <div class="col-12 d-none" id="newImagesSection">
                            <label class="form-label">Xem trước ảnh mới</label>
                            <div class="row g-2" id="newImagesContainer"></div>
                        </div>

                        <div class="col-12 d-none" id="existingImagesSection">
                            <label class="form-label">Ảnh hiện tại</label>
                            <div class="row g-2" id="existingImagesContainer"></div>
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