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

  if (catButtons.length > 0 && catInput) {
    catButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var value = btn.getAttribute('data-cat-value') || '0';
        catInput.value = value;
        if (filterForm) filterForm.submit();
      });
    });
  }

  if (filterForm) {
    filterForm.querySelectorAll('input[name="price"], input[name="range"]').forEach(function (input) {
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

  // Product detail interactions
  var root = document.getElementById('vfProductDetail');
  if (!root) return;

  var mainImage = document.getElementById('vfPdMainImage');
  var thumbsWrap = document.getElementById('vfPdThumbs');
  var wishlistBtn = document.getElementById('vfPdWishlistBtn');
  var tabButtons = root.querySelectorAll('.vf-pd-tab-btn');
  var panes = root.querySelectorAll('.vf-pd-pane');
  var colorButtons = root.querySelectorAll('.vf-pd-color-btn');
  var selectedColorLabels = root.querySelectorAll('[data-selected-color-label]');
  var checkoutLinks = root.querySelectorAll('.vf-pd-checkout-link');

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

  if (wishlistBtn) {
    wishlistBtn.addEventListener('click', function () {
      wishlistBtn.classList.toggle('is-active');
      var active = wishlistBtn.classList.contains('is-active');
      wishlistBtn.setAttribute('aria-pressed', active ? 'true' : 'false');

      var icon = wishlistBtn.querySelector('[data-heart-icon]');
      if (icon) {
        icon.classList.toggle('fa-solid', active);
        icon.classList.toggle('fa-regular', !active);
      }
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

  function setCheckoutColor(code) {
    if (!checkoutLinks.length) return;

    checkoutLinks.forEach(function (checkoutLink) {
      var base = checkoutLink.getAttribute('data-checkout-base') || checkoutLink.getAttribute('href') || '';
      if (!base) return;

      if (!code) {
        checkoutLink.href = base;
        return;
      }

      checkoutLink.href = base + '?color=' + encodeURIComponent(code);
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

    if (colorName && selectedColorLabels.length) {
      selectedColorLabels.forEach(function (label) {
        label.textContent = colorName;
      });
    }

    setCheckoutColor(colorCode);

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

    var defaultColorBtn = root.querySelector('.vf-pd-color-btn.is-active') || colorButtons[0];
    if (defaultColorBtn) {
      setActiveColor(defaultColorBtn);
    }
  }
})();
