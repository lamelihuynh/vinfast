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

  // Star picker & "Xem thêm đánh giá" (dùng chung cho cả trang chi tiết xe và phụ kiện, T1-16)
  document.querySelectorAll('.vf-review-form').forEach(function (form) {
    var ratingInput = form.querySelector('.js-review-rating-input');
    var ratingLabel = form.querySelector('.js-review-rating-label');
    var starBtns = form.querySelectorAll('.js-review-star');
    if (!ratingInput || !starBtns.length) return;

    starBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var val = Math.max(1, Math.min(5, parseInt(btn.getAttribute('data-star') || '5', 10) || 5));
        ratingInput.value = String(val);
        if (ratingLabel) ratingLabel.textContent = val + '/5 sao';
        starBtns.forEach(function (b) {
          var s = parseInt(b.getAttribute('data-star') || '0', 10) || 0;
          var active = s <= val;
          b.classList.toggle('text-amber-400', active);
          b.classList.toggle('text-slate-300', !active);
          var icon = b.querySelector('i');
          if (icon) {
            icon.classList.toggle('fa-solid', active);
            icon.classList.toggle('fa-regular', !active);
          }
        });
      });
    });
  });

  var loadMoreReviewsBtn = document.getElementById('vfLoadMoreReviewsBtn');
  var reviewListEl = document.getElementById('vfReviewList');
  if (loadMoreReviewsBtn && reviewListEl) {
    loadMoreReviewsBtn.addEventListener('click', function () {
      if (loadMoreReviewsBtn.disabled) return;
      var baseUrl = loadMoreReviewsBtn.getAttribute('data-url') || '';
      if (!baseUrl) return;

      var nextPage = parseInt(loadMoreReviewsBtn.getAttribute('data-next-page') || '2', 10) || 2;
      var total = parseInt(loadMoreReviewsBtn.getAttribute('data-total') || '0', 10) || 0;
      var params = new URLSearchParams();
      params.set('page', String(nextPage));

      // Gửi id bài cuối đang hiển thị làm keyset cursor; server tự đọc lại mốc created_at chính xác từ PostgreSQL
      var items = reviewListEl.querySelectorAll('.vf-review-item');
      var lastItem = items.length ? items[items.length - 1] : null;
      var lastId = lastItem ? (lastItem.getAttribute('data-review-id') || '') : '';
      if (/^[1-9][0-9]*$/.test(lastId)) {
        params.set('before_id', lastId);
      }

      loadMoreReviewsBtn.disabled = true;
      fetch(baseUrl + '?' + params.toString(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Accept': 'text/html' }
      })
        .then(function (res) {
          if (!res.ok) throw new Error('HTTP ' + res.status);
          return res.text();
        })
        .then(function (html) {
          var trimmed = (html || '').trim();
          if (!trimmed) {
            loadMoreReviewsBtn.remove();
            return;
          }
          var temp = document.createElement('div');
          temp.innerHTML = trimmed;
          var newArticles = temp.querySelectorAll('.vf-review-item');
          if (!newArticles.length) {
            loadMoreReviewsBtn.remove();
            return;
          }
          newArticles.forEach(function (el) {
            reviewListEl.appendChild(el);
          });

          var shownNow = reviewListEl.querySelectorAll('.vf-review-item').length;
          loadMoreReviewsBtn.setAttribute('data-shown', String(shownNow));
          loadMoreReviewsBtn.setAttribute('data-next-page', String(nextPage + 1));

          var remaining = Math.max(0, total - shownNow);
          if (remaining <= 0 || newArticles.length < 5) {
            loadMoreReviewsBtn.remove();
          } else {
            var labelSpan = loadMoreReviewsBtn.querySelector('span');
            if (labelSpan) {
              labelSpan.textContent = 'Xem thêm đánh giá (còn ' + String(remaining).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ')';
            }
            loadMoreReviewsBtn.disabled = false;
          }
        })
        .catch(function () {
          loadMoreReviewsBtn.disabled = false;
        });
    });
  }

  // Product detail interactions
  var root = document.getElementById('vfProductDetail');
  if (!root) return;

  var mainImage = document.getElementById('vfPdMainImage');
  var thumbsWrap = document.getElementById('vfPdThumbs');
  var tabButtons = root.querySelectorAll('.vf-pd-tab-btn');
  var panes = root.querySelectorAll('.vf-pd-pane');
  var variantButtons = root.querySelectorAll('.vf-pd-variant-btn');
  var colorGroups = root.querySelectorAll('.vf-pd-color-group');
  var priceValueEl = document.getElementById('vfPdPriceValue');
  var mobilePriceValueEl = document.getElementById('vfPdMobilePriceValue');
  var selectedVariantLabelEl = document.getElementById('vfPdSelectedVariantLabel');
  var selectedColorLabelEl = document.getElementById('vfPdSelectedColorLabel');
  var variantInput = document.getElementById('vfPdVariantInput');
  var colorInput = document.getElementById('vfPdColorInput');
  var colorQueryInput = document.getElementById('vfPdColorQueryInput');
  var depositForm = document.getElementById('vfPdDepositForm');

  var variantsData = [];
  var variantsDataEl = document.getElementById('vfPdVariantsData');
  if (variantsDataEl && variantsDataEl.textContent) {
    try {
      variantsData = JSON.parse(variantsDataEl.textContent) || [];
    } catch (e) {
      variantsData = [];
    }
  }

  function formatVnd(amount) {
    var num = Math.max(0, Math.round(Number(amount) || 0));
    return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function findVariantData(variantId) {
    for (var i = 0; i < variantsData.length; i++) {
      if (String(variantsData[i].variantId || '') === String(variantId || '')) {
        return variantsData[i];
      }
    }
    return variantsData[0] || null;
  }

  var currentVariantId = variantInput ? variantInput.value : (variantsData[0] ? String(variantsData[0].variantId) : '');

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

  function activeColorGroup() {
    for (var i = 0; i < colorGroups.length; i++) {
      if (colorGroups[i].getAttribute('data-variant-colors') === currentVariantId) {
        return colorGroups[i];
      }
    }
    return colorGroups[0] || null;
  }

  function syncPriceAndLinks(activeColorBtn) {
    var vData = findVariantData(currentVariantId);
    var basePrice = vData ? (Number(vData.price) || 0) : 0;
    if (!vData && variantButtons.length > 0) {
      variantButtons.forEach(function (vb) {
        if (vb.getAttribute('data-variant-id') === currentVariantId) {
          basePrice = Number(vb.getAttribute('data-price')) || 0;
        }
      });
    }

    var colorCode = activeColorBtn ? (activeColorBtn.getAttribute('data-color-code') || '') : '';
    var colorName = activeColorBtn ? (activeColorBtn.getAttribute('data-color-name') || '') : '';
    var surcharge = activeColorBtn ? Math.max(0, parseInt(activeColorBtn.getAttribute('data-color-surcharge') || '0', 10) || 0) : 0;

    if (vData || activeColorBtn) {
      var formattedTotal = formatVnd(basePrice + surcharge);
      if (priceValueEl) priceValueEl.textContent = formattedTotal;
      if (mobilePriceValueEl) mobilePriceValueEl.textContent = formattedTotal;
    }

    if (selectedVariantLabelEl && vData) {
      selectedVariantLabelEl.textContent = vData.name || '';
    }

    if (selectedColorLabelEl) {
      if (colorName) {
        selectedColorLabelEl.textContent = colorName + (surcharge > 0 ? (' (+' + formatVnd(surcharge) + ' đ)') : '');
      } else {
        var grp = activeColorGroup();
        var hasBtns = grp && grp.querySelectorAll('.vf-pd-color-btn').length > 0;
        selectedColorLabelEl.textContent = hasBtns ? 'Chọn một màu để xem ảnh tương ứng' : 'Chưa cấu hình màu cho phiên bản này';
      }
    }

    if (variantInput) variantInput.value = currentVariantId;
    if (colorInput) colorInput.value = colorCode;
    if (colorQueryInput) colorQueryInput.value = colorCode;

    // Đồng bộ link đặt cọc ở tab mô tả và thanh mobile CTA
    var isAuth = depositForm ? depositForm.getAttribute('data-is-auth') === '1' : true;
    var loginUrl = depositForm ? (depositForm.getAttribute('data-login-url') || '/auth/login') : '/auth/login';
    var checkoutPath = depositForm ? (depositForm.getAttribute('data-checkout-path') || '') : '';

    root.querySelectorAll('.vf-pd-checkout-link').forEach(function (link) {
      var base = link.getAttribute('data-checkout-base') || '';
      if (!base) return;
      var params = new URLSearchParams();
      if (currentVariantId) params.set('variant_id', currentVariantId);
      if (colorCode) {
        params.set('color_code', colorCode);
        params.set('color', colorCode);
      }
      var qs = params.toString();
      if (isAuth || !checkoutPath) {
        link.setAttribute('href', base + (qs ? ('?' + qs) : ''));
      } else {
        var target = checkoutPath + (qs ? ('?' + qs) : '');
        link.setAttribute('href', loginUrl + '?redirect=' + encodeURIComponent(target));
      }
    });
  }

  function setActiveColor(btn, scrollOnMobile) {
    var group = (btn && btn.closest('.vf-pd-color-group')) || activeColorGroup();
    if (group) {
      group.querySelectorAll('.vf-pd-color-btn').forEach(function (item) {
        item.classList.remove('is-active');
      });
    }

    if (btn) {
      btn.classList.add('is-active');
    }

    syncPriceAndLinks(btn || null);

    if (!btn) return;
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

    if (scrollOnMobile && window.innerWidth < 1024 && mainImage) {
      var rect = mainImage.getBoundingClientRect();
      if (rect.bottom < 72 || rect.top > window.innerHeight - 120) {
        mainImage.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  }

  function selectVariant(variantId, updateHistory) {
    var vData = findVariantData(variantId);
    if (!vData) return;

    var prevColorCode = colorInput ? colorInput.value : '';
    currentVariantId = String(vData.variantId);

    variantButtons.forEach(function (btn) {
      var on = btn.getAttribute('data-variant-id') === currentVariantId;
      btn.classList.toggle('is-active', on);
      btn.classList.toggle('border-vfNavy', on);
      btn.classList.toggle('bg-blue-50/70', on);
      btn.classList.toggle('text-vfNavy', on);
      btn.classList.toggle('border-slate-200', !on);
      btn.classList.toggle('bg-white', !on);
      btn.classList.toggle('text-slate-700', !on);
      btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    });

    colorGroups.forEach(function (grp) {
      grp.classList.toggle('hidden', grp.getAttribute('data-variant-colors') !== currentVariantId);
    });

    var group = activeColorGroup();
    var nextColorBtn = null;
    if (group) {
      if (prevColorCode) {
        group.querySelectorAll('.vf-pd-color-btn').forEach(function (b) {
          if ((b.getAttribute('data-color-code') || '').toUpperCase() === prevColorCode.toUpperCase()) {
            nextColorBtn = b;
          }
        });
      }
      if (!nextColorBtn) {
        nextColorBtn = group.querySelector('.vf-pd-color-btn.is-active') || group.querySelector('.vf-pd-color-btn');
      }
    }

    setActiveColor(nextColorBtn, false);

    if (updateHistory && window.history && typeof window.history.replaceState === 'function') {
      try {
        var nextUrl = new URL(window.location.href);
        nextUrl.searchParams.set('variant', currentVariantId);
        window.history.replaceState(null, '', nextUrl.pathname + nextUrl.search + nextUrl.hash);
      } catch (e) {
        // ignore URL errors on legacy browsers
      }
    }
  }

  tabButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var key = btn.getAttribute('data-tab') || 'mota';
      setTab(key);
    });
  });

  variantButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var vid = btn.getAttribute('data-variant-id') || '';
      if (vid) selectVariant(vid, true);
    });
  });

  root.querySelectorAll('.vf-pd-color-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      setActiveColor(btn, true);
    });
  });

  if (depositForm) {
    depositForm.addEventListener('submit', function (e) {
      if (depositForm.getAttribute('data-is-auth') === '1') {
        return;
      }
      e.preventDefault();
      var loginUrl = depositForm.getAttribute('data-login-url') || '/auth/login';
      var checkoutPath = depositForm.getAttribute('data-checkout-path') || '/order/checkout';
      var params = new URLSearchParams();
      if (variantInput && variantInput.value) params.set('variant_id', variantInput.value);
      if (colorInput && colorInput.value) {
        params.set('color_code', colorInput.value);
        params.set('color', colorInput.value);
      }
      var qs = params.toString();
      window.location.href = loginUrl + '?redirect=' + encodeURIComponent(checkoutPath + (qs ? ('?' + qs) : ''));
    });
  }

  var initialGroup = activeColorGroup();
  var initialColorBtn = initialGroup
    ? (initialGroup.querySelector('.vf-pd-color-btn.is-active') || initialGroup.querySelector('.vf-pd-color-btn'))
    : null;
  setActiveColor(initialColorBtn, false);

  if (window.location.hash === '#danhgia') {
    setTab('danhgia');
  }
})();
