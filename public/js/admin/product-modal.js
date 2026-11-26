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

  var modalApi = bootstrap.Modal.getOrCreateInstance(modalEl);
  var oldState = window.VF_PRODUCT_MODAL_OLD && typeof window.VF_PRODUCT_MODAL_OLD === 'object' ? window.VF_PRODUCT_MODAL_OLD : null;

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
  var inputDepositAmount = document.getElementById('modal_deposit_amount');
  var inputIsActive = document.getElementById('modal_is_active');
  var inputImages = document.getElementById('modal_images');
  var inputMainImage = document.getElementById('modal_main_image');
  var inputMainNewIndex = document.getElementById('modal_main_new_index');
  var newSection = document.getElementById('newImagesSection');
  var newContainer = document.getElementById('newImagesContainer');
  var openCreateBtn = document.getElementById('openCreateProductBtn');
  var newImageObjectUrls = [];

  var slugTouched = false;

  // ---- Phụ kiện: định nghĩa category (id => {kind, dynamic_attributes, variant_attributes}) ----
  var categoryDefs = {};
  var catDefsEl = document.getElementById('productCategoryDefs');
  if (catDefsEl && catDefsEl.textContent) {
    try {
      categoryDefs = JSON.parse(catDefsEl.textContent) || {};
    } catch (e) {
      categoryDefs = {};
    }
  }
  var attrsContainer = document.getElementById('accessoryAttrsContainer');
  var variantsTable = document.getElementById('accessoryVariantsTable');
  var btnAddVariant = document.getElementById('btnAddVariant');
  var currentKind = 'vehicle';
  var currentProduct = null; // sản phẩm/old state đang mở (để lấy lại specs/variants/màu khi đổi category)
  var variantSeq = 0;

  function slugify(text) {
    return (text || '')
      .normalize('NFD')
      .replace(/[̀-ͯ]/g, '')
      .replace(/[đĐ]/g, 'd')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function categoryDef(id) {
    return categoryDefs[String(id || '')] || null;
  }

  function kindOfCategory(id) {
    var def = categoryDef(id);
    return def && def.kind === 'accessory' ? 'accessory' : 'vehicle';
  }

  function asObject(value) {
    return value && typeof value === 'object' && !Array.isArray(value) ? value : {};
  }

  function setSectionEnabled(el, enabled) {
    el.classList.toggle('d-none', !enabled);
    el.querySelectorAll('input, select, textarea, button').forEach(function (input) {
      input.disabled = !enabled;
    });
  }

  function applyKindVisibility(kind) {
    document.querySelectorAll('#productModalForm .js-vehicle-only').forEach(function (el) {
      setSectionEnabled(el, kind !== 'accessory');
    });
    document.querySelectorAll('#productModalForm .js-accessory-only').forEach(function (el) {
      setSectionEnabled(el, kind === 'accessory');
    });
    document.querySelectorAll('#colorImageTable .js-vehicle-col').forEach(function (el) {
      el.classList.toggle('d-none', kind === 'accessory');
    });
    var title = document.getElementById('imageTableTitle');
    if (title) title.textContent = kind === 'accessory' ? 'Ảnh sản phẩm' : 'Bảng ảnh & gán màu';
    var defaultTh = document.getElementById('imageTableDefaultTh');
    if (defaultTh) defaultTh.textContent = kind === 'accessory' ? 'Ảnh chính' : 'Mặc định';
  }

  function attrLabelHtml(def) {
    return escapeHtml(def.label || def.key) + (def.required ? ' <span class="text-danger">*</span>' : '');
  }

  function optionsWith(def, extra) {
    var options = Array.isArray(def.options) ? def.options.map(String) : [];
    (Array.isArray(extra) ? extra : [extra]).forEach(function (v) {
      if (v !== undefined && v !== null && v !== '' && options.indexOf(String(v)) === -1) {
        options.push(String(v));
      }
    });
    return options;
  }

  /** Ô nhập cho 1 thuộc tính sản phẩm (name="attrs[key]"). */
  function renderAttrField(def, value) {
    var key = def.key;
    var name = 'attrs[' + key + ']';
    var id = 'attr_' + key;
    var col = document.createElement('div');
    col.className = (def.type === 'text' || def.type === 'list') ? 'col-12' : 'col-md-6';
    col.setAttribute('data-attr-key', key);
    col.setAttribute('data-attr-type', def.type);
    var req = def.required ? ' required' : '';
    var unit = def.unit ? '<span class="input-group-text">' + escapeHtml(def.unit) + '</span>' : '';
    var html = '';

    if (def.type === 'boolean') {
      var on = value === true || value === 1 || value === '1' || value === 'true';
      html = '<div class="form-check form-switch mt-md-4">' +
        '<input class="form-check-input" type="checkbox" role="switch" name="' + name + '" id="' + id + '" value="1"' + (on ? ' checked' : '') + '>' +
        '<label class="form-check-label" for="' + id + '">' + attrLabelHtml(def) + '</label></div>';
    } else if (def.type === 'enum') {
      var current = value === undefined || value === null ? '' : String(value);
      html = '<label class="form-label" for="' + id + '">' + attrLabelHtml(def) + '</label>' +
        '<select class="form-select" name="' + name + '" id="' + id + '"' + req + '><option value="">-- Chọn --</option>' +
        optionsWith(def, current).map(function (opt) {
          return '<option value="' + escapeHtml(opt) + '"' + (opt === current ? ' selected' : '') + '>' + escapeHtml(opt) + '</option>';
        }).join('') + '</select>';
    } else if (def.type === 'text') {
      html = '<label class="form-label" for="' + id + '">' + attrLabelHtml(def) + '</label>' +
        '<textarea class="form-control" rows="2" maxlength="2000" name="' + name + '" id="' + id + '"' + req + '>' + escapeHtml(value == null ? '' : value) + '</textarea>';
    } else if (def.type === 'list') {
      var selected = (Array.isArray(value) ? value : (value ? [value] : [])).map(String);
      html = '<label class="form-label d-block">' + attrLabelHtml(def) + '</label><div class="d-flex flex-wrap gap-3">' +
        optionsWith(def, selected).map(function (opt, i) {
          var optId = id + '_' + i;
          return '<div class="form-check mb-0"><input class="form-check-input" type="checkbox" name="' + name + '[]" id="' + optId + '" value="' + escapeHtml(opt) + '"' +
            (selected.indexOf(opt) !== -1 ? ' checked' : '') + '><label class="form-check-label" for="' + optId + '">' + escapeHtml(opt) + '</label></div>';
        }).join('') + '</div>';
    } else {
      var type = def.type === 'number' ? 'number' : 'text';
      var extra = def.type === 'number' ? ' step="any"' : ' maxlength="255"';
      html = '<label class="form-label" for="' + id + '">' + attrLabelHtml(def) + '</label>' +
        '<div class="input-group"><input type="' + type + '" class="form-control" name="' + name + '" id="' + id + '"' + extra + req +
        ' value="' + escapeHtml(value == null ? '' : value) + '">' + unit + '</div>';
    }

    col.innerHTML = html;
    return col;
  }

  function renderAttrs(def, specs) {
    attrsContainer.innerHTML = '';
    var attrs = def && Array.isArray(def.dynamic_attributes) ? def.dynamic_attributes : [];
    if (!attrs.length) {
      attrsContainer.innerHTML = '<div class="col-12 text-muted small"><em>Danh mục này chưa khai báo thuộc tính.</em></div>';
      return;
    }
    attrs.forEach(function (a) {
      attrsContainer.appendChild(renderAttrField(a, specs[a.key]));
    });
  }

  function readAttrsState() {
    var out = {};
    attrsContainer.querySelectorAll('[data-attr-key]').forEach(function (col) {
      var key = col.getAttribute('data-attr-key');
      var type = col.getAttribute('data-attr-type');
      if (type === 'boolean') {
        var cb = col.querySelector('input[type=checkbox]');
        out[key] = !!(cb && cb.checked);
      } else if (type === 'list') {
        out[key] = Array.prototype.slice.call(col.querySelectorAll('input[type=checkbox]:checked')).map(function (el) { return el.value; });
      } else {
        var input = col.querySelector('input, select, textarea');
        if (input && input.value !== '') out[key] = input.value;
      }
    });
    return out;
  }

  function variantAttrDefs() {
    var def = categoryDef(inputCategory.value);
    return def && Array.isArray(def.variant_attributes) ? def.variant_attributes : [];
  }

  function renderVariantHead(vdefs) {
    variantsTable.querySelector('thead').innerHTML = '<tr>' +
      '<th style="min-width:160px">Tên phiên bản <span class="text-danger">*</span></th>' +
      vdefs.map(function (d) {
        return '<th style="min-width:120px">' + attrLabelHtml(d) + '</th>';
      }).join('') +
      '<th style="min-width:130px">Giá (VND) <span class="text-danger">*</span></th>' +
      '<th style="min-width:110px">Tồn kho</th>' +
      '<th class="text-center">Hiển thị</th>' +
      '<th></th></tr>';
  }

  function suggestVariantName(tr) {
    if (tr.getAttribute('data-name-touched') === '1') return;
    var parts = [];
    tr.querySelectorAll('.variant-attr').forEach(function (el) {
      if (el.value.trim() !== '') parts.push(el.value.trim());
    });
    tr.querySelector('.variant-name').value = parts.join(' / ');
  }

  function addVariantRow(v) {
    v = v || {};
    var vdefs = variantAttrDefs();
    var idx = variantSeq++;
    var prefix = 'variants[' + idx + ']';
    var attrs = asObject(v.attributes);
    var hasStock = v.stock !== undefined && v.stock !== null && v.stock !== '';
    var reserved = Number(v.reserved || 0);
    var isActive = v.is_active === undefined || v.is_active === null || Number(v.is_active) === 1 || v.is_active === true;

    var tr = document.createElement('tr');
    tr.className = 'variant-row';
    tr.setAttribute('data-name-touched', v.name ? '1' : '0');

    var html = '<td>' +
      '<input type="hidden" name="' + prefix + '[variant_id]" class="variant-id" value="' + escapeHtml(v.variant_id || '') + '">' +
      '<input type="hidden" name="' + prefix + '[stock_orig]" class="variant-stock-orig" value="' + escapeHtml(v.stock_orig == null ? '' : v.stock_orig) + '">' +
      '<input type="hidden" name="' + prefix + '[version_orig]" class="variant-version-orig" value="' + escapeHtml(v.version_orig == null ? '' : v.version_orig) + '">' +
      '<input type="hidden" name="' + prefix + '[reserved]" class="variant-reserved" value="' + reserved + '">' +
      '<input type="text" class="form-control form-control-sm variant-name" name="' + prefix + '[name]" maxlength="120" required value="' + escapeHtml(v.name || '') + '">' +
      (v.variant_id ? '<small class="text-muted">' + escapeHtml(v.variant_id) + '</small>' : '<small class="text-muted">Mới</small>') +
      '</td>';

    vdefs.forEach(function (d) {
      var val = attrs[d.key] === undefined || attrs[d.key] === null ? '' : String(attrs[d.key]);
      var nm = prefix + '[attrs][' + d.key + ']';
      var req = d.required ? ' required' : '';
      if (d.type === 'enum') {
        html += '<td><select class="form-select form-select-sm variant-attr" data-key="' + escapeHtml(d.key) + '" name="' + nm + '"' + req + '><option value="">--</option>' +
          optionsWith(d, val).map(function (opt) {
            return '<option value="' + escapeHtml(opt) + '"' + (opt === val ? ' selected' : '') + '>' + escapeHtml(opt) + '</option>';
          }).join('') + '</select></td>';
      } else {
        var t = d.type === 'number' ? 'number" step="any' : 'text" maxlength="255';
        html += '<td><input type="' + t + '" class="form-control form-control-sm variant-attr" data-key="' + escapeHtml(d.key) + '" name="' + nm + '"' + req + ' value="' + escapeHtml(val) + '"></td>';
      }
    });

    html += '<td><input type="number" min="0" step="1" class="form-control form-control-sm variant-price" name="' + prefix + '[price]" required value="' + escapeHtml(v.price === undefined || v.price === null ? '' : v.price) + '"></td>' +
      '<td><input type="number" min="0" step="1" class="form-control form-control-sm variant-stock" name="' + prefix + '[stock]" placeholder="' + (v.variant_id ? 'Giữ nguyên' : '0') + '" value="' + escapeHtml(hasStock ? v.stock : '') + '">' +
      (v.variant_id ? '<small class="text-muted d-block">Tồn: ' + escapeHtml(v.stock_orig === '' || v.stock_orig == null ? '—' : v.stock_orig) + ' · Đang giữ: ' + reserved + '</small>' : '') +
      '</td>' +
      '<td class="text-center"><input type="checkbox" class="form-check-input variant-active" name="' + prefix + '[is_active]" value="1"' + (isActive ? ' checked' : '') + '></td>' +
      '<td><button type="button" class="btn btn-sm btn-outline-danger btn-remove-variant" title="Xóa phiên bản"><i class="fa-solid fa-xmark"></i></button></td>';

    tr.innerHTML = html;
    variantsTable.querySelector('tbody').appendChild(tr);

    tr.querySelector('.variant-name').addEventListener('input', function (e) {
      tr.setAttribute('data-name-touched', e.target.value.trim() !== '' ? '1' : '0');
    });
    tr.querySelectorAll('.variant-attr').forEach(function (el) {
      el.addEventListener(el.tagName === 'SELECT' ? 'change' : 'input', function () {
        suggestVariantName(tr);
      });
    });
    tr.querySelector('.btn-remove-variant').addEventListener('click', function () {
      var rows = variantsTable.querySelectorAll('tbody tr.variant-row');
      if (rows.length <= 1) {
        window.alert('Sản phẩm phụ kiện cần ít nhất 1 phiên bản.');
        return;
      }
      if (reserved > 0 && !window.confirm('Phiên bản này đang có ' + reserved + ' sản phẩm được giữ cho đơn hàng. Vẫn xóa?')) {
        return;
      }
      tr.remove();
    });
    return tr;
  }

  function readVariantsState() {
    return Array.prototype.slice.call(variantsTable.querySelectorAll('tbody tr.variant-row')).map(function (tr) {
      var attrs = {};
      tr.querySelectorAll('.variant-attr').forEach(function (el) {
        if (el.value !== '') attrs[el.getAttribute('data-key')] = el.value;
      });
      return {
        variant_id: tr.querySelector('.variant-id').value,
        version_orig: tr.querySelector('.variant-version-orig').value,
        stock_orig: tr.querySelector('.variant-stock-orig').value,
        reserved: tr.querySelector('.variant-reserved').value,
        name: tr.getAttribute('data-name-touched') === '1' ? tr.querySelector('.variant-name').value : '',
        price: tr.querySelector('.variant-price').value,
        stock: tr.querySelector('.variant-stock').value,
        is_active: tr.querySelector('.variant-active').checked ? 1 : 0,
        attributes: attrs
      };
    });
  }

  function renderVariants(variants) {
    variantsTable.querySelector('tbody').innerHTML = '';
    variantSeq = 0;
    renderVariantHead(variantAttrDefs());
    var list = Array.isArray(variants) && variants.length ? variants : [{}];
    list.forEach(function (v) {
      var tr = addVariantRow(v);
      if (!v || !v.name) suggestVariantName(tr);
    });
  }

  /** Render khối phụ kiện/xe theo category đang chọn. state: {specs, variants} hoặc null (lấy từ DOM hiện tại). */
  function applyCategory(state) {
    var prevKind = currentKind;
    var kind = kindOfCategory(inputCategory.value);
    currentKind = kind;

    if (kind === 'accessory') {
      if (!state) {
        state = prevKind === 'accessory'
          ? { specs: readAttrsState(), variants: readVariantsState() }
          : { specs: asObject(currentProduct && currentProduct.specs), variants: (currentProduct && currentProduct.variants) || [] };
      }
      renderAttrs(categoryDef(inputCategory.value), asObject(state.specs));
      renderVariants(state.variants);
    }

    applyKindVisibility(kind);

    if (prevKind !== kind) {
      // Đổi loại: vẽ lại bảng ảnh (bỏ/hiện cột màu), giữ danh sách ảnh hiện có
      var rels = Array.prototype.slice.call(document.querySelectorAll('#colorImageTable tbody tr[data-rel]')).map(function (tr) {
        return tr.getAttribute('data-rel');
      });
      renderColorImageTable({ images: rels, exterior_colors: (currentProduct && currentProduct.exterior_colors) || [] });
    }
  }

  if (btnAddVariant) {
    btnAddVariant.addEventListener('click', function () {
      var tr = addVariantRow({});
      suggestVariantName(tr);
      tr.querySelector('.variant-name').focus();
    });
  }

  if (inputCategory) {
    inputCategory.addEventListener('change', function () {
      applyCategory(null);
    });
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
    return String(text === undefined || text === null ? '' : text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function setMainImage(rel, newIndex) {
    if (newIndex !== undefined && newIndex !== null) {
      inputMainNewIndex.value = String(newIndex);
      inputMainImage.value = '';
    } else {
      inputMainImage.value = String(rel || '').replace(/^\/+/, '');
      inputMainNewIndex.value = '';
    }
    refreshMainIndicators();
  }

  function refreshMainIndicators() {
    var currentMainNewIndex = inputMainNewIndex.value;

    // Only handle new image preview section (existing images are in the table)
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

  function bindNewMainButtons() {
    newContainer.querySelectorAll('.btn-set-main-new').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = btn.closest('.new-image-item');
        if (!item) return;
        var idx = Number(item.getAttribute('data-new-index') || -1);
        if (idx >= 0) setMainImage(null, idx);
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
    if (inputDepositAmount) inputDepositAmount.value = '';
    inputIsActive.checked = true;
    inputImages.value = '';
    inputMainImage.value = '';
    inputMainNewIndex.value = '';
    slugTouched = false;
    clearNewImagesPreview();
    currentProduct = null;
    renderColorImageTable({ images: [], exterior_colors: [] });
    applyCategory({ specs: {}, variants: [] });
  }

  function fillFormFromState(state) {
    // Determine if this is edit or create mode based on state.id
    var isEditMode = state && Number(state.id || 0) > 0;

    if (isEditMode) {
      titleEl.textContent = 'Chỉnh sửa sản phẩm';
      submitBtn.textContent = 'Lưu thay đổi';
      slugTouched = true;  // Lock slug to prevent auto-regeneration
    } else {
      titleEl.textContent = 'Thêm sản phẩm mới';
      submitBtn.textContent = 'Tạo sản phẩm';
      slugTouched = false;
    }

    // Fill form with state data
    inputId.value = String(Number(state.id || 0));
    inputName.value = state.name || '';
    inputCategory.value = String(state.category_id || '');
    inputSlug.value = state.slug || '';
    inputPrice.value = state.price || '';
    inputDescription.value = state.description || '';
    inputRange.value = state.range || '';
    inputPower.value = state.power || '';
    inputAcceleration.value = state.acceleration || '';
    inputMaxSpeed.value = state.max_speed || '';
    inputBattery.value = state.battery || '';
    if (inputDepositAmount) {
      if (isEditMode) {
        inputDepositAmount.value = String(state.deposit_amount || '');
      } else {
        inputDepositAmount.value = '';
      }
    }
    inputIsActive.checked = Number(state.is_active || 0) === 1 || state.is_active === true || state.is_active === '1';

    // Use state images/colors if available, otherwise empty
    var images = Array.isArray(state.existing_images) ? state.existing_images : [];
    var colors = Array.isArray(state.exterior_colors) ? state.exterior_colors : [];
    currentProduct = state;
    renderColorImageTable({ images: images, exterior_colors: colors });
    applyCategory({ specs: asObject(state.specs), variants: Array.isArray(state.variants) ? state.variants : [] });
    clearNewImagesPreview();
    refreshMainIndicators();
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
    if (inputDepositAmount) inputDepositAmount.value = String(product.deposit_amount || 15000000);
    // render table editor for existing images
    currentProduct = product;
    renderColorImageTable(product);
    applyCategory({ specs: asObject(product.specs), variants: Array.isArray(product.variants) ? product.variants : [] });
    inputIsActive.checked = Number(product.is_active || 0) === 1;
    inputImages.value = '';
    inputMainImage.value = '';
    inputMainNewIndex.value = '';
    slugTouched = true;

    clearNewImagesPreview();
    refreshMainIndicators();
  }

  function renderColorImageTable(product) {
    var table = document.getElementById('colorImageTable');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    tbody.innerHTML = '';

    var images = Array.isArray(product.images) ? product.images : [];
    var colors = Array.isArray(product.exterior_colors) ? product.exterior_colors : [];

    // Show placeholder if no images
    if (images.length === 0) {
      var emptyTr = document.createElement('tr');
      emptyTr.innerHTML = '<td colspan="7" class="text-center text-muted py-3"><em>Sản phẩm này chưa có ảnh. Hãy tải ảnh mới ở trên.</em></td>';
      tbody.appendChild(emptyTr);
      return;
    }

    // map by basename to prefill
    var colorMap = {};
    colors.forEach(function (r) {
      if (r && typeof r === 'object' && r.code) {
        colorMap[(r.image || '').replace(/^\/+/, '')] = r;
      }
    });

    if (kindOfCategory(inputCategory.value) === 'accessory') {
      // Phụ kiện: không gán màu theo ảnh, chỉ chọn ảnh chính (main_image) và xóa ảnh
      images.forEach(function (img, idx) {
        var rel = String(img || '').replace(/^\/+/, '');
        var filename = rel.split('/').pop() || rel;
        var tr = document.createElement('tr');
        tr.innerHTML =
          '<td><img src="' + (window.VF_PRODUCT_MODAL_BASE_URL || '') + 'public/images/' + escapeHtml(rel) + '" alt="" class="img-fluid" style="height:40px;object-fit:cover;border-radius:4px"></td>' +
          '<td class="align-middle"><div class="text-truncate" style="max-width:260px">' + escapeHtml(filename) + '</div></td>' +
          '<td class="align-middle text-center"><input type="radio" name="accessory_main_pick" class="form-check-input" value="' + escapeHtml(rel) + '"' + (idx === 0 ? ' checked' : '') + '></td>' +
          '<td class="align-middle"><input type="hidden" name="existing_images[]" value="' + escapeHtml(rel) + '"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">Xóa</button></td>';
        tr.setAttribute('data-rel', rel);
        tr.querySelector('input[type=radio]').addEventListener('change', function () {
          setMainImage(rel);
        });
        tbody.appendChild(tr);
      });
      tbody.querySelectorAll('.btn-remove-row').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var tr = btn.closest('tr');
          if (tr) tr.remove();
        });
      });
      return;
    }

    images.forEach(function (img, idx) {
      var rel = String(img || '').replace(/^\/+/, '');
      var filename = rel.split('/').pop() || rel;
      var existing = colorMap[rel] || {};
      var surchargeValue = Number(existing.surcharge || 0);
      var codeValue = String(existing.code || filename.split('.')[0] || '').trim().toUpperCase();

      var tr = document.createElement('tr');

      tr.innerHTML =
        '<td><img src="' + (window.VF_PRODUCT_MODAL_BASE_URL || '') + 'public/images/' + rel + '" alt="" class="img-fluid" style="height:40px;object-fit:cover;border-radius:4px"></td>' +
        '<td class="align-middle"><div class="text-truncate" style="max-width:180px">' + escapeHtml(filename) + '</div><input type="hidden" name="color_code[]" class="color-table-code" value="' + escapeHtml(codeValue) + '"><input type="hidden" name="color_image[]" value="' + escapeHtml(rel) + '"></td>' +
        '<td class="align-middle"><input type="text" name="color_name[]" class="form-control form-control-sm color-table-name" placeholder="VD: Xanh dương" value="' + escapeHtml(existing.name || '') + '"></td>' +
        '<td class="align-middle"><input type="text" name="color_hex[]" class="form-control form-control-sm color-table-hex" placeholder="VD: #0000FF" value="' + escapeHtml(existing.hex || '') + '"></td>' +
        '<td class="align-middle"><input type="number" name="color_surcharge[]" min="0" step="1000" class="form-control form-control-sm color-table-surcharge" placeholder="VD: 5000000" value="' + (surchargeValue > 0 ? String(Math.max(0, Math.floor(surchargeValue))) : '') + '"></td>' +
        '<td class="align-middle text-center">' +
          '<input type="radio" name="modal_default_image" class="form-check-input" value="' + escapeHtml(rel) + '" ' + (existing.isDefault || idx === 0 ? 'checked' : '') + '>' +
        '</td>' +
        '<td class="align-middle"><input type="hidden" name="existing_images[]" value="' + escapeHtml(rel) + '"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">Xóa</button></td>';

      // store rel on row
      tr.setAttribute('data-rel', rel);

      tbody.appendChild(tr);
    });

    // bind remove
    tbody.querySelectorAll('.btn-remove-row').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var tr = btn.closest('tr');
        if (tr) tr.remove();
      });
    });
  }

  var btnAuto = document.getElementById('btnAutoMatchColors');
  if (btnAuto) btnAuto.addEventListener('click', function () { 
    var table = document.getElementById('colorImageTable');
    if (!table) return;
    table.querySelectorAll('tbody tr').forEach(function (tr) {
      var rel = tr.getAttribute('data-rel') || '';
      var filename = rel.split('/').pop() || rel;
      var basename = filename.split('.')[0] || '';
      var codeInput = tr.querySelector('.color-table-code');
      if (codeInput && (!codeInput.value || codeInput.value.trim() === '')) {
        codeInput.value = basename.toUpperCase();
      }
    });
  });

  if (openCreateBtn) {
    openCreateBtn.addEventListener('click', function () {
      resetFormForCreate();
      modalApi.show();
    });
  }

  if (oldState && Object.keys(oldState).length > 0) {
    fillFormFromState(oldState);
    modalApi.show();
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
