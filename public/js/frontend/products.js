(function () {
  // Products listing interactions
  var STORAGE_KEY = 'vf-products-filter-sections-v1';
  var sidebar = document.getElementById('productsSidebar');
  var openBtn = document.getElementById('openProductsFilter');
  var closeBtn = document.getElementById('closeProductsFilter');
  var overlay = document.getElementById('productsOverlay');
  var filterForm = document.getElementById('productsFilterForm');
  var catInput = document.getElementById('filterCatInput');
  var sortInput = document.getElementById('filterSortInput');
  var perPageInput = document.getElementById('filterPerPageInput');
  var toolbarSortSelect = document.getElementById('productsSortSelect');
  var toolbarPerPageSelect = document.getElementById('productsPerPageSelect');
  var catButtons = document.querySelectorAll('.js-cat-btn');

  function openFilter() {
    if (!sidebar || !overlay) return;
    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('translate-x-0');
    overlay.classList.remove('hidden');
  }

  function closeFilter() {
    if (!sidebar || !overlay) return;
    sidebar.classList.add('-translate-x-full');
    sidebar.classList.remove('translate-x-0');
    overlay.classList.add('hidden');
  }

  if (sidebar && openBtn && overlay) {
    openBtn.addEventListener('click', openFilter);
    overlay.addEventListener('click', closeFilter);
    if (closeBtn) closeBtn.addEventListener('click', closeFilter);
  }

  function readSectionState() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : {};
    } catch (e) {
      return {};
    }
  }

  function saveSectionState(nextState) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(nextState));
    } catch (e) {
      // ignore storage failures
    }
  }

  function setSectionOpen(target, icon, isOpen) {
    if (!target) return;
    target.classList.toggle('hidden', !isOpen);
    if (icon) icon.classList.toggle('rotate-180', isOpen);
  }

  var sectionState = readSectionState();

  document.querySelectorAll('[data-filter-toggle]').forEach(function (toggleBtn) {
    var targetId = toggleBtn.getAttribute('data-target');
    var target = targetId ? document.getElementById(targetId) : null;
    var icon = toggleBtn.querySelector('[data-filter-icon]');
    if (!target) return;

    if (typeof sectionState[targetId] === 'boolean') {
      setSectionOpen(target, icon, sectionState[targetId]);
    } else {
      sectionState[targetId] = !target.classList.contains('hidden');
    }

    toggleBtn.addEventListener('click', function () {
      var isOpen = target.classList.contains('hidden');
      setSectionOpen(target, icon, isOpen);
      sectionState[targetId] = isOpen;
      saveSectionState(sectionState);
    });
  });
  saveSectionState(sectionState);

  function clearDynamicAttrInputs() {
    if (!filterForm) return;
    filterForm.querySelectorAll('[name^="attr["]').forEach(function (el) {
      el.disabled = true;
    });
  }

  // Đổi loại sản phẩm (xe / phụ kiện): reset danh mục, mức giá, phạm vi và thuộc tính động
  document.querySelectorAll('.js-type-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var typeInput = document.getElementById('filterTypeInput');
      if (!filterForm || !typeInput) return;
      typeInput.value = btn.getAttribute('data-type-value') || 'all';
      if (catInput) catInput.value = '0';
      filterForm.querySelectorAll('input[name="price"], input[name="range"]').forEach(function (i) { i.checked = i.value === 'all'; });
      clearDynamicAttrInputs();
      filterForm.submit();
    });
  });

  if (catButtons.length > 0 && catInput) {
    catButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var value = btn.getAttribute('data-cat-value') || '0';
        if (catInput.value !== value) {
          clearDynamicAttrInputs();
        }
        catInput.value = value;
        if (filterForm) filterForm.submit();
      });
    });
  }

  if (filterForm) {
    filterForm.querySelectorAll('input[name="price"], input[name="range"], .js-attr-filter').forEach(function (input) {
      input.addEventListener('change', function () {
        filterForm.submit();
      });
    });

    if (toolbarSortSelect) {
      toolbarSortSelect.addEventListener('change', function () {
        if (sortInput) sortInput.value = toolbarSortSelect.value || 'default';
        filterForm.submit();
      });
    }

    if (toolbarPerPageSelect) {
      toolbarPerPageSelect.addEventListener('change', function () {
        if (perPageInput) perPageInput.value = toolbarPerPageSelect.value || '12';
        filterForm.submit();
      });
    }
  }

  // Wishlist toggle (dùng chung cho card /products, /wishlist và trang chi tiết)
  function setWishlistBtnState(btn, active) {
    btn.classList.toggle('is-active', active);
    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    btn.setAttribute('aria-label', active ? 'Bỏ khỏi yêu thích' : 'Thêm vào yêu thích');
    var icon = btn.querySelector('[data-heart-icon]');
    if (icon) {
      icon.classList.toggle('fa-solid', active);
      icon.classList.toggle('fa-regular', !active);
    }
  }

  document.addEventListener('click', function (e) {
    var btn = e.target && e.target.closest ? e.target.closest('[data-wishlist-product]') : null;
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();

    var productId = btn.getAttribute('data-wishlist-product') || '';
    if (!productId || btn.disabled) return;

    var variantId = btn.getAttribute('data-wishlist-variant') || '';
    var url = btn.getAttribute('data-wishlist-url') || '/wishlist/toggle';
    var wasActive = btn.classList.contains('is-active') || btn.getAttribute('aria-pressed') === 'true';
    var nextActive = !wasActive;
    var nextState = nextActive ? 'on' : 'off';

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf = csrfMeta ? (csrfMeta.getAttribute('content') || '') : '';

    var escapedPid = productId.replace(/"/g, '\\"');
    var relatedBtns = Array.prototype.slice.call(
      document.querySelectorAll('[data-wishlist-product="' + escapedPid + '"]')
    );
    relatedBtns.forEach(function (b) {
      b.disabled = true;
      setWishlistBtnState(b, nextActive);
    });

    var body = new URLSearchParams();
    body.set('_csrf', csrf);
    body.set('product_id', productId);
    if (variantId) body.set('variant_id', variantId);
    body.set('state', nextState);

    fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-CSRF-Token': csrf,
        'Accept': 'application/json'
      },
      body: body.toString()
    })
      .then(function (res) {
        return res.json().catch(function () { return null; }).then(function (data) {
          return { status: res.status, ok: res.ok, data: data };
        });
      })
      .then(function (result) {
        if (result.status === 401) {
          relatedBtns.forEach(function (b) { setWishlistBtnState(b, wasActive); });
          window.location.href = (result.data && result.data.login_url) ? result.data.login_url : '/auth/login';
          return;
        }
        if (!result.ok || !result.data || result.data.status !== 'ok') {
          relatedBtns.forEach(function (b) { setWishlistBtnState(b, wasActive); });
          return;
        }
        var confirmed = result.data.state === 'on';
        relatedBtns.forEach(function (b) { setWishlistBtnState(b, confirmed); });
      })
      .catch(function () {
        relatedBtns.forEach(function (b) { setWishlistBtnState(b, wasActive); });
      })
      .finally(function () {
        relatedBtns.forEach(function (b) { b.disabled = false; });
      });
  });

  // Product detail interactions
  var root = document.getElementById('vfProductDetail');
  if (!root) return;

  var mainImage = document.getElementById('vfPdMainImage');
  var thumbsWrap = document.getElementById('vfPdThumbs');
  var tabButtons = root.querySelectorAll('.vf-pd-tab-btn');
  var panes = root.querySelectorAll('.vf-pd-pane');
  var colorButtons = root.querySelectorAll('.vf-pd-color-btn');

  function setActiveThumb(targetBtn) {
    if (!thumbsWrap || !mainImage || !targetBtn) return;

    thumbsWrap.querySelectorAll('.vf-pd-thumb').forEach(function (btn) {
      btn.classList.remove('is-active');
    });

    targetBtn.classList.add('is-active');
    var nextImg = targetBtn.getAttribute('data-img') || '';
    if (nextImg) mainImage.src = nextImg;
  }

  if (thumbsWrap) {
    thumbsWrap.querySelectorAll('.vf-pd-thumb').forEach(function (btn) {
      btn.addEventListener('click', function () {
        setActiveThumb(btn);
      });
    });
  }

  function setTab(tabKey) {
    tabButtons.forEach(function (btn) {
      btn.classList.toggle('is-active', btn.getAttribute('data-tab') === tabKey);
    });

    panes.forEach(function (pane) {
      pane.classList.toggle('is-active', pane.getAttribute('data-pane') === tabKey);
    });
  }

  function setActiveColor(btn) {
    if (!btn) return;

    colorButtons.forEach(function (item) {
      item.classList.remove('is-active');
    });

    btn.classList.add('is-active');

    var colorCode = btn.getAttribute('data-color-code') || '';
    var colorName = btn.getAttribute('data-color-name') || '';
    var colorImage = btn.getAttribute('data-color-image') || '';

    if (!colorImage || !mainImage) return;

    var matchedThumb = null;
    if (thumbsWrap) {
      thumbsWrap.querySelectorAll('.vf-pd-thumb').forEach(function (thumb) {
        if (thumb.getAttribute('data-img') === colorImage) {
          matchedThumb = thumb;
        }
      });
    }

    if (matchedThumb) {
      setActiveThumb(matchedThumb);
    } else {
      mainImage.src = colorImage;
    }

    if (window.innerWidth < 1024 && mainImage) {
      var rect = mainImage.getBoundingClientRect();
      if (rect.bottom < 72 || rect.top > window.innerHeight - 120) {
        mainImage.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  }

  tabButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var key = btn.getAttribute('data-tab') || 'mota';
      setTab(key);
    });
  });

  if (colorButtons.length > 0) {
    colorButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        setActiveColor(btn);
      });
    });

    var defaultColorBtn = root.querySelector('.vf-pd-color-btn.is-active');
    if (defaultColorBtn) {
      setActiveColor(defaultColorBtn);
    }
  }
})();
