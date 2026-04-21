(function () {
  var root = document.getElementById('vfCheckoutRoot');
  if (!root) return;

  var form = root.querySelector('form');
  if (!form) return;

  var stepInput = form.querySelector('[data-step-input]');
  var panels = root.querySelectorAll('[data-step-panel]');
  var tabs = root.querySelectorAll('[data-step-tab]');
  var nextButtons = form.querySelectorAll('[data-step-next]');
  var prevButtons = form.querySelectorAll('[data-step-prev]');
  var provinceSelect = form.querySelector('[data-province-select]');
  var showroomSelect = form.querySelector('[data-showroom-select]');
  var colorRadios = form.querySelectorAll('[data-color-radio]');
  var mainImage = document.getElementById('vfCheckoutMainImage');
  var summaryColor = form.querySelector('[data-summary-color]');
  var summaryVariant = form.querySelector('[data-summary-variant]');
  var summaryInterior = form.querySelector('[data-summary-interior]');
  var summaryPrice = form.querySelector('[data-summary-price]');
  var selectedExterior = form.querySelector('[data-selected-exterior]');
  var selectedInterior = form.querySelector('[data-selected-interior]');
  var selectedVariantPrice = form.querySelector('[data-selected-variant-price]');
  var variantButtons = form.querySelectorAll('[data-variant-btn]');
  var variantInput = form.querySelector('[data-variant-input]');
  var interiorRadios = form.querySelectorAll('[data-interior-radio]');
  var interiorInput = form.querySelector('[data-interior-input]');
  var switchButtons = form.querySelectorAll('[data-switch-product]');

  var step = Number(root.getAttribute('data-step') || '1');
  if (Number.isNaN(step) || step < 1 || step > 3) {
    step = 1;
  }

  var showroomsByProvince = {};
  try {
    showroomsByProvince = JSON.parse(root.getAttribute('data-showrooms') || '{}') || {};
  } catch (e) {
    showroomsByProvince = {};
  }

  function setStep(nextStep) {
    if (nextStep < 1 || nextStep > 3) return;
    step = nextStep;

    if (stepInput) {
      stepInput.value = String(step);
    }

    panels.forEach(function (panel) {
      var panelStep = Number(panel.getAttribute('data-step-panel') || '1');
      panel.classList.toggle('hidden', panelStep !== step);
    });

    tabs.forEach(function (tab) {
      var tabStep = Number(tab.getAttribute('data-step-tab') || '1');
      var active = tabStep === step;
      tab.classList.toggle('border-vfNavy', active);
      tab.classList.toggle('bg-vfNavy', active);
      tab.classList.toggle('text-white', active);
      tab.classList.toggle('border-slate-200', !active);
      tab.classList.toggle('text-slate-500', !active);
    });
  }

  function validateStep2() {
    var requiredFields = ['full_name', 'phone', 'email', 'province', 'showroom'];

    for (var i = 0; i < requiredFields.length; i += 1) {
      var name = requiredFields[i];
      var input = form.querySelector('[name="' + name + '"]');
      if (!input) continue;

      var value = String(input.value || '').trim();
      if (!value) {
        input.focus();
        return false;
      }
    }

    return true;
  }

  function populateShowrooms(selectedShowroom) {
    if (!provinceSelect || !showroomSelect) return;

    var province = String(provinceSelect.value || '');
    var options = Array.isArray(showroomsByProvince[province]) ? showroomsByProvince[province] : [];

    showroomSelect.innerHTML = '';

    var placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = options.length ? 'Chọn showroom' : 'Chưa có showroom cho tỉnh thành này';
    showroomSelect.appendChild(placeholder);

    options.forEach(function (item) {
      var option = document.createElement('option');
      option.value = item;
      option.textContent = item;
      if (selectedShowroom && selectedShowroom === item) {
        option.selected = true;
      }
      showroomSelect.appendChild(option);
    });
  }

  function syncSelectedColor() {
    var checked = form.querySelector('[data-color-radio]:checked');
    if (!checked) return;

    var colorName = checked.getAttribute('data-color-name') || checked.value || '';
    var colorImage = checked.getAttribute('data-color-image') || '';

    if (summaryColor) {
      summaryColor.textContent = colorName;
    }

    if (selectedExterior) {
      selectedExterior.textContent = colorName;
    }

    if (mainImage && colorImage) {
      mainImage.src = colorImage;
    }
  }

  function formatVnd(value) {
    var num = Number(value || 0);
    if (Number.isNaN(num)) {
      return '0 VNĐ';
    }

    return num.toLocaleString('vi-VN') + ' VNĐ';
  }

  function setActiveVariant(btn) {
    if (!btn) return;

    var variantName = btn.getAttribute('data-variant-name') || '';
    var variantPrice = btn.getAttribute('data-variant-price') || '0';

    variantButtons.forEach(function (item) {
      item.classList.remove('border-vfNavy', 'bg-blue-50');
      item.classList.add('border-slate-200');
    });

    btn.classList.add('border-vfNavy', 'bg-blue-50');
    btn.classList.remove('border-slate-200');

    if (variantInput) {
      variantInput.value = variantName;
    }

    if (summaryVariant) {
      summaryVariant.textContent = variantName || '-';
    }

    var priceText = formatVnd(variantPrice);
    if (selectedVariantPrice) {
      selectedVariantPrice.textContent = priceText;
    }
    if (summaryPrice) {
      summaryPrice.textContent = priceText;
    }
  }

  function syncSelectedInterior() {
    var checked = form.querySelector('[data-interior-radio]:checked');
    if (!checked) return;

    var interiorCode = checked.value || '';
    var interiorName = checked.getAttribute('data-interior-name') || interiorCode || '-';

    if (interiorInput) {
      interiorInput.value = interiorCode;
    }

    if (selectedInterior) {
      selectedInterior.textContent = interiorName;
    }

    if (summaryInterior) {
      summaryInterior.textContent = interiorName;
    }
  }

  nextButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = Number(btn.getAttribute('data-step-next') || '1');

      if (step === 2 && target === 3 && !validateStep2()) {
        return;
      }

      syncSelectedColor();
      setStep(target);
    });
  });

  prevButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = Number(btn.getAttribute('data-step-prev') || '1');
      setStep(target);
    });
  });

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var target = Number(tab.getAttribute('data-step-tab') || '1');
      if (target === 3 && !validateStep2()) {
        return;
      }
      setStep(target);
    });
  });

  if (provinceSelect) {
    provinceSelect.addEventListener('change', function () {
      populateShowrooms('');
    });
  }

  colorRadios.forEach(function (radio) {
    radio.addEventListener('change', syncSelectedColor);
  });

  variantButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      setActiveVariant(btn);
    });
  });

  interiorRadios.forEach(function (radio) {
    radio.addEventListener('change', syncSelectedInterior);
  });

  switchButtons.forEach(function (btn) {
    btn.addEventListener('click', function (event) {
      if (btn.hasAttribute('data-switch-product-quick')) {
        return;
      }

      var accepted = window.confirm('Đổi sang dòng xe này và bắt đầu lại từ bước chọn cấu hình?');
      if (!accepted) {
        event.preventDefault();
      }
    });
  });

  var selectedShowroom = showroomSelect ? String(showroomSelect.getAttribute('data-selected') || '') : '';
  populateShowrooms(selectedShowroom);

  var defaultVariantBtn = form.querySelector('[data-variant-btn].border-vfNavy') || variantButtons[0];
  if (defaultVariantBtn) {
    setActiveVariant(defaultVariantBtn);
  }

  syncSelectedColor();
  syncSelectedInterior();
  setStep(step);
})();
