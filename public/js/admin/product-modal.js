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
  var inputDepositNonRefundable = document.getElementById('modal_deposit_non_refundable');
  var inputExteriorColorsRaw = document.getElementById('modal_exterior_colors_raw');
  var inputIsActive = document.getElementById('modal_is_active');
  var inputImages = document.getElementById('modal_images');
  var inputMainImage = document.getElementById('modal_main_image');
  var inputMainNewIndex = document.getElementById('modal_main_new_index');
  var newSection = document.getElementById('newImagesSection');
  var newContainer = document.getElementById('newImagesContainer');
  var openCreateBtn = document.getElementById('openCreateProductBtn');
  var btnOpenCreateCategory = document.getElementById('btnOpenCreateCategory');
  var createCategoryModalEl = document.getElementById('createCategoryModal');
  var createCategoryForm = document.getElementById('createCategoryForm');
  var createCategoryName = document.getElementById('createCategoryName');
  var createCategoryFeedback = document.getElementById('createCategoryFeedback');
  var createCategorySubmitBtn = document.getElementById('createCategorySubmitBtn');
  var createCategoryModalApi = createCategoryModalEl ? bootstrap.Modal.getOrCreateInstance(createCategoryModalEl) : null;
  var btnOpenDeleteCategory = document.getElementById('btnOpenDeleteCategory');
  var deleteCategoryModalEl = document.getElementById('deleteCategoryModal');
  var deleteCategoryForm = document.getElementById('deleteCategoryForm');
  var deleteCategoryNameEl = document.getElementById('deleteCategoryName');
  var deleteCategoryIdInput = document.getElementById('deleteCategoryId');
  var deleteCategoryFeedback = document.getElementById('deleteCategoryFeedback');
  var deleteCategorySubmitBtn = document.getElementById('deleteCategorySubmitBtn');
  var deleteCategoryModalApi = deleteCategoryModalEl ? bootstrap.Modal.getOrCreateInstance(deleteCategoryModalEl) : null;
  var returnToProductAfterCreate = false;
  var returnToProductAfterDelete = false;
  var newImageObjectUrls = [];

  var slugTouched = false;

  function colorRowsToTextarea(rows) {
    if (!Array.isArray(rows)) return '';

    function normalizePath(path) {
      var value = String(path || '').trim().replace(/\\/g, '/');
      var match = value.match(/(?:^|[A-Za-z]:)?(?:.*\/)?public\/images\/(.+)$/i);
      if (match) {
        value = match[1] || '';
      }
      return value.replace(/^\/+/, '');
    }

    return rows
      .map(function (row) {
        if (!row || typeof row !== 'object') return '';
        var code = String(row.code || '').trim().toUpperCase();
        var name = String(row.name || '').trim();
        var image = normalizePath(row.image || '');
        var hex = String(row.hex || '').trim();
        var surcharge = Number(row.surcharge || 0);
        if (!code || !name) return '';

        var parts = [code, name];
        if (image) parts.push(image);
        if (hex) parts.push(hex);
        if (surcharge > 0) parts.push(String(Math.max(0, Math.floor(surcharge))));
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

  function showCategoryFeedback(message, isError) {
    if (!createCategoryFeedback) return;
    createCategoryFeedback.classList.remove('d-none', 'text-danger', 'text-success');
    createCategoryFeedback.classList.add(isError ? 'text-danger' : 'text-success');
    createCategoryFeedback.textContent = message || '';
  }

  function upsertCategoryOption(category) {
    if (!inputCategory || !category || !category.id) return;

    var id = String(category.id);
    var name = String(category.name || '').trim();
    if (!name) return;

    var existing = inputCategory.querySelector('option[value="' + id + '"]');
    if (!existing) {
      existing = document.createElement('option');
      existing.value = id;
      inputCategory.appendChild(existing);
    }
    existing.textContent = name;
    inputCategory.value = id;
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
    if (inputDepositAmount) inputDepositAmount.value = '15000000';
    if (inputDepositNonRefundable) inputDepositNonRefundable.value = '1';
    if (inputExteriorColorsRaw) inputExteriorColorsRaw.value = '';
    inputIsActive.checked = true;
    inputImages.value = '';
    inputMainImage.value = '';
    inputMainNewIndex.value = '';
    slugTouched = false;
    clearNewImagesPreview();
    renderColorImageTable({ images: [], exterior_colors: [] });
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
    if (inputDepositNonRefundable) inputDepositNonRefundable.value = '1';
    if (inputExteriorColorsRaw) {
      inputExteriorColorsRaw.value = colorRowsToTextarea(product.exterior_colors || []);
    }
    // render table editor for existing images
    renderColorImageTable(product);
    inputIsActive.checked = Number(product.is_active || 0) === 1;
    inputImages.value = '';
    inputMainImage.value = '';
    inputMainNewIndex.value = '';
    slugTouched = true;

    // existing images are managed via the color-image table now
    // ensure textarea and main image are synced from the table
    serializeTableToTextarea();

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

    images.forEach(function (img, idx) {
      var rel = String(img || '').replace(/^\/+/, '');
      var filename = rel.split('/').pop() || rel;
      var existing = colorMap[rel] || {};
      var surchargeValue = Number(existing.surcharge || 0);
      var codeValue = String(existing.code || filename.split('.')[0] || '').trim().toUpperCase();

      var tr = document.createElement('tr');

      tr.innerHTML =
        '<td><img src="' + (window.VF_PRODUCT_MODAL_BASE_URL || '') + 'public/images/' + rel + '" alt="" class="img-fluid" style="height:40px;object-fit:cover;border-radius:4px"></td>' +
        '<td class="align-middle"><div class="text-truncate" style="max-width:180px">' + escapeHtml(filename) + '</div><input type="hidden" class="color-table-code" value="' + escapeHtml(codeValue) + '"></td>' +
        '<td class="align-middle"><input type="text" class="form-control form-control-sm color-table-name" placeholder="VD: Xanh dương" value="' + escapeHtml(existing.name || '') + '"></td>' +
        '<td class="align-middle"><input type="text" class="form-control form-control-sm color-table-hex" placeholder="VD: #0000FF" value="' + escapeHtml(existing.hex || '') + '"></td>' +
        '<td class="align-middle"><input type="number" min="0" step="1000" class="form-control form-control-sm color-table-surcharge" placeholder="VD: 5000000" value="' + (surchargeValue > 0 ? String(Math.max(0, Math.floor(surchargeValue))) : '') + '"></td>' +
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

  function autoMatchTableCodes() {
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
  }

  function serializeTableToTextarea() {
    if (!inputExteriorColorsRaw) return;
    var table = document.getElementById('colorImageTable');
    if (!table) return;
    var lines = [];
    table.querySelectorAll('tbody tr').forEach(function (tr) {
      var rel = tr.getAttribute('data-rel') || '';
      var code = (tr.querySelector('.color-table-code') || {}).value || '';
      var name = (tr.querySelector('.color-table-name') || {}).value || '';
      var hex = (tr.querySelector('.color-table-hex') || {}).value || '';
      var surcharge = (tr.querySelector('.color-table-surcharge') || {}).value || '';
      code = String(code || '').trim().toUpperCase();
      name = String(name || '').trim();
      hex = String(hex || '').trim();
      surcharge = String(surcharge || '').trim();
      if (!code || !name) {
        return;
      }
      var parts = [code, name];
      if (rel) parts.push(rel);
      if (hex) parts.push(hex);
      if (surcharge !== '') parts.push(surcharge.replace(/\D+/g, ''));
      lines.push(parts.join('|'));
    });
    
    
    inputExteriorColorsRaw.value = lines.join('\n');
    // set main image from checked radio in the table (if any)
    var checked = document.querySelector('input[name="modal_default_image"]:checked');
    if (checked && checked.value) {
      inputMainImage.value = String(checked.value || '').replace(/^\/+/, '');
      inputMainNewIndex.value = '';
    } else if (lines.length === 0) {
      inputMainImage.value = '';
    } else {
      // fallback: first existing_images[] value present in table
      var firstRel = (table.querySelector('input[name="existing_images[]"]') || {}).value || '';
      if (firstRel) inputMainImage.value = String(firstRel).replace(/^\/+/, '');
    }
    return true;
  }

  // wire auto-match and sync buttons
  var btnAuto = document.getElementById('btnAutoMatchColors');
  if (btnAuto) btnAuto.addEventListener('click', function () { 
    autoMatchTableCodes(); 
  });

  // ensure table serialized before submit
  var modalForm = document.getElementById('productModalForm');
  if (modalForm) {
    modalForm.addEventListener('submit', function () {
      serializeTableToTextarea();
    });
  }

  if (openCreateBtn) {
    openCreateBtn.addEventListener('click', function () {
      resetFormForCreate();
      modalApi.show();
    });
  }

  if (btnOpenCreateCategory && createCategoryModalApi && createCategoryForm) {
    btnOpenCreateCategory.addEventListener('click', function () {
      returnToProductAfterCreate = true;
      createCategoryForm.reset();
      if (createCategoryFeedback) {
        createCategoryFeedback.classList.add('d-none');
        createCategoryFeedback.textContent = '';
      }

      // Prefer deterministic open to avoid relying only on hidden event timing.
      createCategoryModalApi.show();
      modalApi.hide();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
      if (returnToProductAfterCreate && createCategoryModalApi && createCategoryModalEl && !createCategoryModalEl.classList.contains('show')) {
        createCategoryModalApi.show();
      }
      if (returnToProductAfterDelete && deleteCategoryModalApi && deleteCategoryModalEl && !deleteCategoryModalEl.classList.contains('show')) {
        deleteCategoryModalApi.show();
      }
    });

    createCategoryModalEl.addEventListener('shown.bs.modal', function () {
      if (createCategoryName) {
        createCategoryName.focus();
      }
    });

    createCategoryModalEl.addEventListener('hidden.bs.modal', function () {
      if (returnToProductAfterCreate) {
        returnToProductAfterCreate = false;
        modalApi.show();
      }
    });

    createCategoryForm.addEventListener('submit', function (e) {
      e.preventDefault();

      var name = String((createCategoryName && createCategoryName.value) || '').trim();
      if (!name) {
        showCategoryFeedback('Vui lòng nhập tên danh mục.', true);
        return;
      }

      var csrfInput = modalEl.querySelector('input[name="_csrf"]');
      var csrf = csrfInput ? csrfInput.value : '';
      if (!csrf) {
        showCategoryFeedback('Thiếu CSRF token.', true);
        return;
      }

      if (createCategorySubmitBtn) {
        createCategorySubmitBtn.disabled = true;
      }
      showCategoryFeedback('Đang lưu danh mục...', false);

      var payload = new URLSearchParams();
      payload.set('name', name);
      payload.set('_csrf', csrf);

      fetch(window.VF_ADMIN_URL + 'products/createcategory', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: payload.toString()
      })
        .then(function (res) {
          return res.json().catch(function () {
            return {
              ok: false,
              message: 'Phản hồi không hợp lệ từ máy chủ.'
            };
          });
        })
        .then(function (data) {
          if (!data || !data.ok || !data.category) {
            showCategoryFeedback((data && data.message) || 'Không thể thêm danh mục.', true);
            return;
          }

          upsertCategoryOption(data.category);
          showCategoryFeedback((data && data.message) || 'Đã thêm danh mục.', false);
          createCategoryModalApi.hide();
        })
        .catch(function () {
          showCategoryFeedback('Lỗi kết nối khi thêm danh mục.', true);
        })
        .finally(function () {
          if (createCategorySubmitBtn) {
            createCategorySubmitBtn.disabled = false;
          }
        });
    });

    if (btnOpenDeleteCategory && deleteCategoryModalApi && deleteCategoryForm) {
      btnOpenDeleteCategory.addEventListener('click', function () {
        var selected = (inputCategory && inputCategory.options[inputCategory.selectedIndex]) || null;
        var id = selected ? selected.value : '';
        var name = selected ? selected.textContent : '';
        if (!id) {
          if (createCategoryFeedback) {
            createCategoryFeedback.classList.remove('d-none');
            createCategoryFeedback.classList.add('text-danger');
            createCategoryFeedback.textContent = 'Vui lòng chọn danh mục trước khi xóa.';
          }
          return;
        }

        if (deleteCategoryNameEl) deleteCategoryNameEl.textContent = name || '';
        if (deleteCategoryIdInput) deleteCategoryIdInput.value = String(id || '0');

        // open delete modal deterministically
        deleteCategoryModalApi.show();
        modalApi.hide();
        returnToProductAfterDelete = true;
      });

      deleteCategoryModalEl.addEventListener('hidden.bs.modal', function () {
        if (returnToProductAfterDelete) {
          returnToProductAfterDelete = false;
          modalApi.show();
        }
      });

      deleteCategoryForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var id = String((deleteCategoryIdInput && deleteCategoryIdInput.value) || '').trim();
        if (!id || Number(id) <= 0) {
          if (deleteCategoryFeedback) {
            deleteCategoryFeedback.classList.remove('d-none');
            deleteCategoryFeedback.classList.add('text-danger');
            deleteCategoryFeedback.textContent = 'ID danh mục không hợp lệ.';
          }
          return;
        }

        var csrfInput = modalEl.querySelector('input[name="_csrf"]');
        var csrf = csrfInput ? csrfInput.value : '';
        if (!csrf) {
          if (deleteCategoryFeedback) {
            deleteCategoryFeedback.classList.remove('d-none');
            deleteCategoryFeedback.classList.add('text-danger');
            deleteCategoryFeedback.textContent = 'Thiếu CSRF token.';
          }
          return;
        }

        if (deleteCategorySubmitBtn) deleteCategorySubmitBtn.disabled = true;
        if (deleteCategoryFeedback) {
          deleteCategoryFeedback.classList.remove('d-none');
          deleteCategoryFeedback.classList.remove('text-danger');
          deleteCategoryFeedback.classList.add('text-muted');
          deleteCategoryFeedback.textContent = 'Đang xóa...';
        }

        var payload = new URLSearchParams();
        payload.set('id', id);
        payload.set('_csrf', csrf);

        fetch(window.VF_ADMIN_URL + 'products/deletecategory', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: payload.toString()
        })
          .then(function (res) {
            return res.json().catch(function () { return { ok: false, message: 'Phản hồi không hợp lệ.' }; });
          })
          .then(function (data) {
            if (data && data.ok) {
              // remove option
              var opt = inputCategory.querySelector('option[value="' + id + '"]');
              if (opt) opt.remove();
              inputCategory.value = '';
              if (deleteCategoryFeedback) {
                deleteCategoryFeedback.classList.remove('text-danger');
                deleteCategoryFeedback.classList.add('text-success');
                deleteCategoryFeedback.textContent = (data.message || 'Đã xóa danh mục.');
              }
              deleteCategoryModalApi.hide();
              return;
            }

            if (deleteCategoryFeedback) {
              deleteCategoryFeedback.classList.remove('text-muted');
              deleteCategoryFeedback.classList.add('text-danger');
              deleteCategoryFeedback.textContent = (data && data.message) || 'Không thể xóa danh mục.';
            }
          })
          .catch(function () {
            if (deleteCategoryFeedback) {
              deleteCategoryFeedback.classList.remove('text-muted');
              deleteCategoryFeedback.classList.add('text-danger');
              deleteCategoryFeedback.textContent = 'Lỗi kết nối khi xóa danh mục.';
            }
          })
          .finally(function () {
            if (deleteCategorySubmitBtn) deleteCategorySubmitBtn.disabled = false;
          });
      });
    }
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
