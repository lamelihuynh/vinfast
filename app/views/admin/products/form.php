<?php

/**
 * app/views/admin/products/form.php
 * Owner  : Hai Nam 
 * Title  : Product Form (Vietnamese - VinFast Vehicle Management)
 *
 * Purpose: Product creation/editing with Vietnamese UI and VinFast-specific vehicle specs
 * Fields: Name, Category (Dòng Xe), Slug, Price, Description, Technical Specs (Range, Power, Acceleration, Max Speed, Battery), Images, Status
 * 
 * Variables available (set by controller via View::render):
 *   $product (array|null), $cats (array)
 */
?>
<?php
if (!isset($product) || !is_array($product)) {
    $product = null;
}
if (!isset($old) || !is_array($old)) {
    $old = [];
}
if (!isset($cats) || !is_array($cats)) {
    $cats = [];
}

$isEdit = isset($product['id']);
$formId = (int)($old['id'] ?? ($product['id'] ?? 0));

$value = static function (string $key, $default = '') use ($old, $product) {
    if (array_key_exists($key, $old)) {
        return $old[$key];
    }
    if (is_array($product) && array_key_exists($key, $product)) {
        return $product[$key];
    }
    return $default;
};

$specs = is_array($product) && is_array($product['specs'] ?? null) ? $product['specs'] : [];
$specOld = [
    'range' => $value('range', (string)($specs['range'] ?? '')),
    'power' => $value('power', (string)($specs['power'] ?? '')),
    'acceleration' => $value('acceleration', (string)($specs['acceleration'] ?? '')),
    'max_speed' => $value('max_speed', (string)($specs['max_speed'] ?? '')),
    'battery' => $value('battery', (string)($specs['battery'] ?? '')),
];

$exteriorColors = [];
$normalizeColorImagePath = static function (string $path): string {
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    $path = str_replace('\\', '/', $path);
    if (preg_match('~(?:^|[A-Za-z]:)?(?:.*/)?public/images/(.+)$~i', $path, $match)) {
        return ltrim((string)$match[1], '/');
    }

    return ltrim($path, '/');
};

if (isset($old['exterior_colors_raw'])) {
    $exteriorColorsText = (string)$old['exterior_colors_raw'];
} else {
    $exteriorColorsRows = is_array($specs['exterior_colors'] ?? null) ? $specs['exterior_colors'] : [];
    foreach ($exteriorColorsRows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $code = strtoupper(trim((string)($row['code'] ?? '')));
        $name = trim((string)($row['name'] ?? ''));
        $image = $normalizeColorImagePath((string)($row['image'] ?? ''));
        $hex = trim((string)($row['hex'] ?? ''));

        if ($code === '' || $name === '') {
            continue;
        }

        $line = $code . '|' . $name;
        if ($image !== '') {
            $line .= '|' . $image;
        }
        if ($hex !== '') {
            $line .= '|' . strtoupper($hex);
        }

        $exteriorColors[] = $line;
    }

    $exteriorColorsText = implode("\n", $exteriorColors);
}

$existingImages = [];
if (isset($old['existing_images']) && is_array($old['existing_images'])) {
    $existingImages = $old['existing_images'];
} elseif (is_array($product) && is_array($product['images'] ?? null)) {
    $existingImages = $product['images'];
}
?>

<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h4 class="mb-1"><?= $isEdit ? 'Chỉnh Sửa Sản Phẩm' : 'Tạo Sản Phẩm Mới' ?></h4>
        <small class="text-muted">
            <i class="fa-solid fa-layer-group me-1"></i>
            Dashboard / Sản Phẩm / <?= $isEdit ? 'Chỉnh Sửa' : 'Tạo Mới' ?>
        </small>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="<?= ADMIN_URL ?>products" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Quay Lại
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="POST" action="<?= ADMIN_URL ?>products/save" enctype="multipart/form-data" class="row g-3" id="productForm">
            <input type="hidden" name="_csrf" value="<?= Auth::csrfToken() ?>">
            <input type="hidden" name="id" value="<?= $formId ?>">

            <!-- Basic Info Section -->
            <div class="col-12">
                <h6 class="header-title mb-3"><i class="fa-solid fa-circle-info me-2"></i>Thông Tin Cơ Bản</h6>
            </div>

            <div class="col-md-8">
                <div class="form-group">
                    <label class="form-label" for="nameInput">Tên Sản Phẩm <span class="badge bg-danger ms-1">Bắt buộc</span></label>
                    <input type="text" class="form-control" name="name" id="nameInput"
                        value="<?= htmlspecialchars((string)$value('name')) ?>"
                        placeholder="VinFast VF9 Plus, VinFast VF8..." required>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-lightbulb me-1"></i>Ví dụ: VinFast VF9 Plus, VinFast VF8 Eco Plus</small>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label" for="categorySelect">Dòng Xe <span class="badge bg-danger ms-1">Bắt buộc</span></label>
                    <select class="form-select" name="category_id" id="categorySelect" required>
                        <option value="">-- Chọn dòng xe --</option>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= (int)$value('category_id') === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)$c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label" for="slugInput">URL Slug <span class="badge bg-danger ms-1">Bắt buộc</span></label>
                    <div class="input-group">
                        <span class="input-group-text">/products/</span>
                        <input type="text" class="form-control" name="slug" id="slugInput"
                            value="<?= htmlspecialchars((string)$value('slug')) ?>"
                            placeholder="vinfast-vf9-plus" required>
                    </div>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-link me-1"></i>Tự động sinh từ tên sản phẩm</small>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label" for="priceInput">Giá Bán <span class="badge bg-danger ms-1">Bắt buộc</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₫</span>
                        <input type="number" min="0" step="1000" class="form-control text-end" name="price" id="priceInput"
                            value="<?= htmlspecialchars((string)$value('price', '0')) ?>"
                            placeholder="0" required>
                        <span class="input-group-text">VND</span>
                    </div>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-tag me-1"></i>Giá hiển thị trên website khách hàng</small>
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <label class="form-label" for="descriptionInput">Mô Tả <small class="text-muted">(Tùy chọn)</small></label>
                    <textarea class="form-control" rows="4" name="description" id="descriptionInput"
                        placeholder="Mô tả chi tiết về sản phẩm, đặc điểm nổi bật..."><?= htmlspecialchars((string)$value('description')) ?></textarea>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-circle-info me-1"></i>Thông tin chi tiết sẽ giúp khách hàng hiểu rõ hơn về xe</small>
                </div>
            </div>

            <!-- Technical Specs Section -->
            <div class="col-12">
                <hr class="my-3">
                <h6 class="header-title mb-3"><i class="fa-solid fa-microchip me-2"></i>Thông Số Kỹ Thuật</h6>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label" for="rangeInput">Quãng Đường (km) <small class="text-muted">Ví dụ: 326</small></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="range" id="rangeInput"
                            placeholder="326"
                            value="<?= htmlspecialchars((string)$specOld['range']) ?>">
                        <span class="input-group-text">km</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label" for="powerInput">Công Suất <small class="text-muted">Ví dụ: 102 hp</small></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="power" id="powerInput"
                            placeholder="102"
                            value="<?= htmlspecialchars((string)$specOld['power']) ?>">
                        <span class="input-group-text">hp</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label" for="accelerationInput">Tăng Tốc (0-100) <small class="text-muted">Ví dụ: 7.8s</small></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="acceleration" id="accelerationInput"
                            placeholder="7.8"
                            value="<?= htmlspecialchars((string)$specOld['acceleration']) ?>">
                        <span class="input-group-text">s</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label" for="maxSpeedInput">Vận Tốc Tối Đa <small class="text-muted">Ví dụ: 130</small></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="max_speed" id="maxSpeedInput"
                            placeholder="130"
                            value="<?= htmlspecialchars((string)$specOld['max_speed']) ?>">
                        <span class="input-group-text">km/h</span>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label" for="batteryInput">Pin / Dung Lượng <small class="text-muted">Ví dụ: 37.23</small></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="battery" id="batteryInput"
                            placeholder="37.23"
                            value="<?= htmlspecialchars((string)$specOld['battery']) ?>">
                        <span class="input-group-text">kWh</span>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <label class="form-label" for="exteriorColorsInput">Màu ngoại thất <small class="text-muted">(Mỗi dòng 1 màu)</small></label>
                    <textarea class="form-control" rows="5" name="exterior_colors_raw" id="exteriorColorsInput" placeholder="CE18|Infinity Blanc|#E5E5E5&#10;CE1V|Zenith Grey|#8B9284&#10;CE2Q|Crimson Red|uploads/products/CE2Q.webp|#8A1C2B"><?= htmlspecialchars((string)$exteriorColorsText) ?></textarea>
                    <small class="text-muted d-block mt-1"><i class="fa-solid fa-palette me-1"></i>Gộp luôn trong 1 dòng: MA_MAU|TEN_MAU|#HEX. Nếu có ảnh riêng theo màu thì chèn thêm cột ảnh trước HEX.</small>
                </div>
            </div>

            <!-- Specs Preview Section -->
            <div class="col-12">
                <div class="alert alert-info border-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-info" style="font-size:1.2em"></i>
                    <div>
                        <strong>Mẹo:</strong> Hãy điền đầy đủ các thông số kỹ thuật để sản phẩm hiển thị hoàn chỉnh trên trang web.
                    </div>
                </div>
            </div>

            <!-- Images Section -->
            <div class="col-12">
                <hr class="my-3">
                <h6 class="header-title mb-3"><i class="fa-solid fa-image me-2"></i>Hình Ảnh Sản Phẩm</h6>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <label class="form-label" for="imagesInput">Tải Ảnh Lên</label>
                    <div class="alert alert-light border-2 border-dashed p-5 text-center" role="button" style="cursor:pointer">
                        <div class="mb-3">
                            <i class="fa-solid fa-cloud-arrow-up" style="font-size:3rem;color:#3b82f6"></i>
                        </div>
                        <input type="file" class="form-control d-none" name="images[]" id="imagesInput"
                            accept="image/jpeg,image/png,image/webp" multiple>
                        <h6 class="mb-2"><strong>Nhấp để tải lên hoặc kéo thả ảnh</strong></h6>
                        <p class="text-muted mb-0">
                            <small>
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Hỗ trợ: JPG, PNG, WebP • Tối đa 2MB/ảnh
                            </small>
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!empty($existingImages)): ?>
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-check-circle me-1"></i>Ảnh Hiện Tại</label>
                        <div class="row g-3" id="existingImagesGrid">
                            <?php foreach ($existingImages as $img): ?>
                                <?php if (!is_string($img) || trim($img) === '') continue; ?>
                                <?php $imgRel = ltrim($img, '/'); ?>
                                <div class="col-6 col-md-4 col-lg-3 image-item">
                                    <div class="card card-body border-0 shadow-sm p-0 overflow-hidden h-100">
                                        <div class="position-relative" style="background:#f3f4f6">
                                            <img src="<?= htmlspecialchars(BASE_URL . 'public/images/' . $imgRel) ?>" alt="Product image"
                                                class="w-100" style="height:140px;object-fit:cover;">
                                        </div>
                                        <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($imgRel) ?>">
                                        <button type="button" class="btn btn-danger btn-sm w-100 mt-2 btn-remove-image">
                                            <i class="fa-solid fa-trash me-1"></i>Xóa
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status Section -->
            <div class="col-12">
                <hr class="my-3">
                <h6 class="header-title mb-3"><i class="fa-solid fa-toggle-on me-2"></i>Trạng Thái Hiển Thị</h6>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <div class="form-check form-switch form-check-lg">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="activeSwitch"
                            <?= (int)$value('is_active', 1) === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="activeSwitch">
                            <strong>Hiển Thị Trên Website</strong>
                            <br>
                            <small class="text-muted">Khi bật, sản phẩm sẽ xuất hiện trên website dành cho khách hàng</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-12">
                <div class="d-flex gap-2 justify-content-end pt-3">
                    <a href="<?= ADMIN_URL ?>products" class="btn btn-light btn-lg">
                        <i class="fa-solid fa-times me-1"></i>Hủy
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-<?= $isEdit ? 'save' : 'plus' ?> me-1"></i>
                        <?= $isEdit ? 'Lưu Thay Đổi' : 'Tạo Sản Phẩm' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var nameInput = document.getElementById('nameInput');
        var slugInput = document.getElementById('slugInput');
        var manualSlug = slugInput && slugInput.value.trim() !== '';

        function slugify(text) {
            return (text || '')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        if (slugInput) {
            slugInput.addEventListener('input', function() {
                manualSlug = slugInput.value.trim() !== '';
            });
        }

        if (nameInput && slugInput) {
            nameInput.addEventListener('input', function() {
                if (!manualSlug) {
                    slugInput.value = slugify(nameInput.value);
                }
            });
        }

        // Image upload handler
        document.getElementById('imagesInput').addEventListener('change', function(e) {
            console.log('Files selected:', e.target.files.length);
        });

        // Image removal handler
        document.querySelectorAll('.btn-remove-image').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var item = btn.closest('.image-item');
                if (item) item.remove();
            });
        });

        // Drag and drop for file upload
        var uploadArea = document.querySelector('[role="button"]');
        if (uploadArea && document.getElementById('imagesInput')) {
            uploadArea.addEventListener('click', function() {
                document.getElementById('imagesInput').click();
            });

            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('bg-light');
            });

            uploadArea.addEventListener('dragleave', function() {
                uploadArea.classList.remove('bg-light');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('bg-light');
                var files = e.dataTransfer.files;
                if (document.getElementById('imagesInput')) {
                    document.getElementById('imagesInput').files = files;
                    var event = new Event('change', {
                        bubbles: true
                    });
                    document.getElementById('imagesInput').dispatchEvent(event);
                }
            });
        }
    });
</script>