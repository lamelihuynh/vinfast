(function () {
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
    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('translate-x-0');
    overlay.classList.remove('hidden');
  }

  function closeFilter() {
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
      // ignore storage failures in private mode
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

})();
