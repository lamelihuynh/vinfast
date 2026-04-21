document.addEventListener('DOMContentLoaded', function () {
  var modalEl = document.getElementById('productModal');
  if (!modalEl || typeof bootstrap === 'undefined') {
    return;
  }

  var dataEl = document.getElementById('productModalData');
  var products = [];
  if (dataEl && dataEl.textContent) {
    try {
      products = JSON.parse(dataEl.textContent);
    } catch (e) {
      products = [];
    }
  }

  var baseUrl = (window.VF_PRODUCT_MODAL_BASE_URL || '').toString();
  var modalApi = bootstrap.Modal.getOrCreateInstance(modalEl);

  var titleEl = document.getElementById('productModalTitle');
  var submitBtn = document.getElementById('productModalSubmitBtn');
  var inputId = document.getElementById('modal_id');
  var inputName = document.getElementById('modal_name');
  var inputCategory = document.getElementById('modal_category_id');
  var inputSlug = document.getElementById('modal_slug');
  var inputPrice = document.getElementById('modal_price');
  var inputDescription = document.getElementById('modal_description');
  var inputRange = document.getElementById('modal_range');
  var inputPower = document.getElementById('modal_power');
  var inputAcceleration = document.getElementById('modal_acceleration');
  var inputMaxSpeed = document.getElementById('modal_max_speed');
  var inputBattery = document.getElementById('modal_battery');
  var inputExteriorColorsRaw = document.getElementById('modal_exterior_colors_raw');
  var inputIsActive = document.getElementById('modal_is_active');
  var inputImages = document.getElementById('modal_images');
  var inputMainImage = document.getElementById('modal_main_image');
  var inputMainNewIndex = document.getElementById('modal_main_new_index');
  var existingSection = document.getElementById('existingImagesSection');
  var existingContainer = document.getElementById('existingImagesContainer');
  var newSection = document.getElementById('newImagesSection');
  var newContainer = document.getElementById('newImagesContainer');
  var openCreateBtn = document.getElementById('openCreateProductBtn');
  var newImageObjectUrls = [];

  var slugTouched = false;
  var currentEditingFamily = '';

  function colorRowsToTextarea(rows) {
    if (!Array.isArray(rows)) return '';

    return rows
      .map(function (row) {
        if (!row || typeof row !== 'object') return '';
        var code = String(row.code || '').trim().toUpperCase();
        var name = String(row.name || '').trim();
        var image = String(row.image || '').trim();
        var hex = String(row.hex || '').trim();
        if (!code || !name) return '';

        var parts = [code, name];
        if (image) parts.push(image);
        if (hex) parts.push(hex);
        return parts.join('|');
      })
      .filter(function (line) {
        return line !== '';
      })
      .join('\n');
  }

  function slugify(text) {
    return (text || '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function extractFamily(text) {
    var value = String(text || '').toLowerCase().trim();
    if (!value) return '';

    var match = value.match(/(?:^|[-_])vf(?:-?mpv)?-?([3-9])(?:[-_]|$)/i);
    if (match) return 'vf' + match[1];

    var normalized = value.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    if (!normalized) return '';

    if (normalized.indexOf('vinfast-') === 0) {
      normalized = normalized.slice(8);
    }

    normalized = normalized.replace(/^-+|-+$/g, '');
    if (!normalized) return '';

    var family = normalized.split('-')[0] || '';
    return /^[a-z0-9]+$/.test(family) ? family : '';
  }

  function getFamilyFromProduct(product) {
    if (!product || typeof product !== 'object') return '';
    return String(product.family || extractFamily(product.slug || product.name || '') || '').toLowerCase();
  }

  function clearExistingImages() {
    existingContainer.innerHTML = '';
    existingSection.classList.add('d-none');
  }

  function clearNewImagesPreview() {
    revokeNewImageObjectUrls();
    newContainer.innerHTML = '';
    newSection.classList.add('d-none');
  }

  function revokeNewImageObjectUrls() {
    newImageObjectUrls.forEach(function (url) {
      try {
        URL.revokeObjectURL(url);
      } catch (e) {
      }
    });
    newImageObjectUrls = [];
  }

  function escapeHtml(text) {
    return String(text || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function normalizeRel(path) {
    return String(path || '').replace(/^\/+/, '');
  }

  function setMainExisting(rel) {
    inputMainImage.value = normalizeRel(rel);
    inputMainNewIndex.value = '';
    refreshMainIndicators();
  }

  function setMainNew(index) {
    inputMainNewIndex.value = String(index);
    inputMainImage.value = '';
    refreshMainIndicators();
  }

  function refreshMainIndicators() {
    var currentMainRel = normalizeRel(inputMainImage.value);
    var currentMainNewIndex = inputMainNewIndex.value;

    existingContainer.querySelectorAll('.image-item').forEach(function (item) {
      var rel = normalizeRel(item.getAttribute('data-rel') || '');
      var isMain = rel !== '' && rel === currentMainRel;
      item.classList.toggle('border-primary', isMain);

      var badge = item.querySelector('.badge-main-existing');
      if (badge) {
        badge.classList.toggle('d-none', !isMain);
      }

      var btn = item.querySelector('.btn-set-main-existing');
      if (btn) {
        btn.textContent = isMain ? 'Ảnh chính' : 'Đặt ảnh chính';
        btn.classList.toggle('btn-primary', isMain);
        btn.classList.toggle('btn-outline-primary', !isMain);
      }
    });

    newContainer.querySelectorAll('.new-image-item').forEach(function (item) {
      var idx = item.getAttribute('data-new-index') || '';
      var isMain = idx !== '' && idx === currentMainNewIndex;
      item.classList.toggle('border-primary', isMain);

      var badge = item.querySelector('.badge-main-new');
      if (badge) {
        badge.classList.toggle('d-none', !isMain);
      }

      var btn = item.querySelector('.btn-set-main-new');
      if (btn) {
        btn.textContent = isMain ? 'Ảnh chính' : 'Đặt ảnh chính';
        btn.classList.toggle('btn-primary', isMain);
        btn.classList.toggle('btn-outline-primary', !isMain);
      }
    });
  }

  function addExistingImageItem(relativePath) {
    var col = document.createElement('div');
    col.className = 'col-6 col-md-3 image-item border rounded';
    var rel = String(relativePath || '').replace(/^\/+/, '');
    var url = baseUrl + 'public/images/' + rel;
    var escapedRel = rel.replace(/"/g, '&quot;');
    col.setAttribute('data-rel', rel);

    col.innerHTML =
      '<div class="border rounded p-2 h-100">' +
      '<div class="d-flex justify-content-between align-items-center mb-2">' +
      '<span class="badge bg-primary badge-main-existing d-none">Chính</span>' +
      '<button type="button" class="btn btn-sm btn-outline-primary btn-set-main-existing">Đặt ảnh chính</button>' +
      '</div>' +
      '<img src="' + url + '" alt="Product image" class="w-100" style="height:100px;object-fit:cover;border-radius:6px;">' +
      '<input type="hidden" name="existing_images[]" value="' + escapedRel + '">' +
      '<button type="button" class="btn btn-sm btn-outline-danger w-100 mt-2 btn-remove-existing">Xóa ảnh</button>' +
      '</div>';
    existingContainer.appendChild(col);
  }

  function renderNewImagePreview(fileList) {
    clearNewImagesPreview();
    if (!fileList || !fileList.length) {
      return;
    }

    newSection.classList.remove('d-none');

    fileList.forEach(function (file, index) {
      var col = document.createElement('div');
      col.className = 'col-6 col-md-3 new-image-item border rounded p-2';
      col.setAttribute('data-new-index', String(index));
      var safeName = escapeHtml(file.name);

      col.innerHTML =
        '<div class="d-flex justify-content-between align-items-center mb-2">' +
        '<span class="badge bg-primary badge-main-new d-none">Chính</span>' +
        '<button type="button" class="btn btn-sm btn-outline-primary btn-set-main-new">Đặt ảnh chính</button>' +
        '</div>' +
        '<img alt="New image" class="w-100" style="height:100px;object-fit:cover;border-radius:6px;">' +
        '<div class="small text-muted mt-2 text-truncate" title="' + safeName + '">' + safeName + '</div>';

      var img = col.querySelector('img');
      var objectUrl = URL.createObjectURL(file);
      img.src = objectUrl;
      newImageObjectUrls.push(objectUrl);

      newContainer.appendChild(col);
    });

    bindNewMainButtons();
  }

  function bindRemoveExisting() {
    existingContainer.querySelectorAll('.btn-remove-existing').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = btn.closest('.image-item');
        var removedRel = item ? normalizeRel(item.getAttribute('data-rel') || '') : '';
        if (item) item.remove();

        if (removedRel !== '' && normalizeRel(inputMainImage.value) === removedRel) {
          inputMainImage.value = '';
          var firstRemaining = existingContainer.querySelector('.image-item');
          if (firstRemaining) {
            inputMainImage.value = normalizeRel(firstRemaining.getAttribute('data-rel') || '');
          }
        }

        if (existingContainer.children.length === 0) {
          existingSection.classList.add('d-none');
        }

        refreshMainIndicators();
      });
    });

    existingContainer.querySelectorAll('.btn-set-main-existing').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = btn.closest('.image-item');
        if (!item) return;
        var rel = normalizeRel(item.getAttribute('data-rel') || '');
        if (rel === '') return;
        setMainExisting(rel);
      });
    });
  }

  function bindNewMainButtons() {
    newContainer.querySelectorAll('.btn-set-main-new').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = btn.closest('.new-image-item');
        if (!item) return;
        var idx = Number(item.getAttribute('data-new-index') || -1);
        if (idx < 0) return;
        setMainNew(idx);
      });
    });
  }

  function resetFormForCreate() {
    titleEl.textContent = 'Thêm sản phẩm mới';
    submitBtn.textContent = 'Tạo sản phẩm';
    inputId.value = '0';
    inputName.value = '';
    inputCategory.value = '';
    inputSlug.value = '';
    inputPrice.value = '';
    inputDescription.value = '';
    inputRange.value = '';
    inputPower.value = '';
    inputAcceleration.value = '';
    inputMaxSpeed.value = '';
    inputBattery.value = '';
    if (inputExteriorColorsRaw) inputExteriorColorsRaw.value = '';
    inputIsActive.checked = true;
    inputImages.value = '';
    inputMainImage.value = '';
    inputMainNewIndex.value = '';
    slugTouched = false;
    currentEditingFamily = '';
    clearExistingImages();
    clearNewImagesPreview();
  }

  function fillFormForEdit(product) {
    titleEl.textContent = 'Chỉnh sửa sản phẩm';
    submitBtn.textContent = 'Lưu thay đổi';
    inputId.value = String(product.id || 0);
    inputName.value = product.name || '';
    inputCategory.value = String(product.category_id || '');
    inputSlug.value = product.slug || '';
    inputPrice.value = product.price || '';
    inputDescription.value = product.description || '';
    inputRange.value = product.range || '';
    inputPower.value = product.power || '';
    inputAcceleration.value = product.acceleration || '';
    inputMaxSpeed.value = product.max_speed || '';
    inputBattery.value = product.battery || '';
    if (inputExteriorColorsRaw) {
      inputExteriorColorsRaw.value = colorRowsToTextarea(product.exterior_colors || []);
    }
    inputIsActive.checked = Number(product.is_active || 0) === 1;
    inputImages.value = '';
    inputMainImage.value = '';
    inputMainNewIndex.value = '';
    slugTouched = true;
    currentEditingFamily = getFamilyFromProduct(product);

    clearExistingImages();
    if (Array.isArray(product.images) && product.images.length > 0) {
      existingSection.classList.remove('d-none');
      product.images.forEach(addExistingImageItem);
      inputMainImage.value = normalizeRel(product.images[0] || '');
      bindRemoveExisting();
    }

    clearNewImagesPreview();
    refreshMainIndicators();
  }

  if (openCreateBtn) {
    openCreateBtn.addEventListener('click', function () {
      resetFormForCreate();
      modalApi.show();
    });
  }

  if (inputSlug) {
    inputSlug.addEventListener('input', function () {
      slugTouched = inputSlug.value.trim() !== '';
    });
  }

  if (inputName) {
    inputName.addEventListener('input', function () {
      if (!slugTouched) {
        inputSlug.value = slugify(inputName.value);
      }
    });
  }

  if (inputImages) {
    inputImages.addEventListener('change', function () {
      var files = Array.prototype.slice.call(inputImages.files || []);
      renderNewImagePreview(files);

      if (files.length === 0) {
        inputMainNewIndex.value = '';
      } else if (inputMainNewIndex.value === '' && inputMainImage.value === '') {
        inputMainNewIndex.value = '0';
      }

      refreshMainIndicators();
    });
  }

  document.querySelectorAll('.btn-edit-product').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var id = Number(btn.getAttribute('data-id') || 0);
      var product = products.find(function (item) {
        return Number(item.id) === id;
      });
      if (!product) return;
      fillFormForEdit(product);
      modalApi.show();
    });
  });
});
