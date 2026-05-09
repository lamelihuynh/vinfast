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
  var summaryDeposit = form.querySelector('[data-summary-deposit]');
  var summaryColorSurcharge = form.querySelector('[data-summary-color-surcharge]');
  var selectedExterior = form.querySelector('[data-selected-exterior]');
  var selectedInterior = form.querySelector('[data-selected-interior]');
  var selectedVariantPrice = form.querySelector('[data-selected-variant-price]');
  var priceBreakdownToggle = form.querySelector('[data-price-breakdown-toggle]');
  var priceBreakdownPanel = form.querySelector('[data-price-breakdown-panel]');
  var priceBreakdownIcon = form.querySelector('[data-price-breakdown-icon]');
  var colorChoices = form.querySelectorAll('[data-color-choice]');
  var variantButtons = form.querySelectorAll('[data-variant-btn]');
  var variantInput = form.querySelector('[data-variant-input]');
  var interiorRadios = form.querySelectorAll('[data-interior-radio]');
  var interiorInput = form.querySelector('[data-interior-input]');
  var switchButtons = form.querySelectorAll('[data-switch-product]');
  var baseDeposit = Number(root.getAttribute('data-deposit-amount') || '15000000');
  if (Number.isNaN(baseDeposit) || baseDeposit < 0) {
    baseDeposit = 15000000;
  }
  var depositNonRefundable = String(root.getAttribute('data-deposit-non-refundable') || '0') === '1';
  var selectedColorSurcharge = Number(root.getAttribute('data-color-surcharge') || '0');
  if (Number.isNaN(selectedColorSurcharge) || selectedColorSurcharge < 0) {
    selectedColorSurcharge = 0;
  }
  var currentVariantPrice = Number(selectedVariantPrice ? String(selectedVariantPrice.textContent || '').replace(/[^0-9]/g, '') : 0);
  if (Number.isNaN(currentVariantPrice) || currentVariantPrice < 0) {
    currentVariantPrice = 0;
  }

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

  function saveStep2DataToSession(callback) {
    var ownerTypeRadio = form.querySelector('[name="owner_type"]:checked');
    var payMethodRadio = form.querySelector('[name="pay_method"]:checked');
    var csrfToken = form.querySelector('[name="_csrf"]') ? form.querySelector('[name="_csrf"]').value : '';
    
    var step2Data = {
      _csrf: csrfToken,
      owner_type: ownerTypeRadio ? ownerTypeRadio.value : 'ca-nhan',
      full_name: form.querySelector('[name="full_name"]') ? form.querySelector('[name="full_name"]').value : '',
      phone: form.querySelector('[name="phone"]') ? form.querySelector('[name="phone"]').value : '',
      email: form.querySelector('[name="email"]') ? form.querySelector('[name="email"]').value : '',
      cccd: form.querySelector('[name="cccd"]') ? form.querySelector('[name="cccd"]').value : '',
      province: form.querySelector('[name="province"]') ? form.querySelector('[name="province"]').value : '',
      showroom: form.querySelector('[name="showroom"]') ? form.querySelector('[name="showroom"]').value : '',
      salesperson: form.querySelector('[name="salesperson"]') ? form.querySelector('[name="salesperson"]').value : '',
      voucher: form.querySelector('[name="voucher"]') ? form.querySelector('[name="voucher"]').value : '',
      variant_name: form.querySelector('[name="variant_name"]') ? form.querySelector('[name="variant_name"]').value : '',
      interior_code: form.querySelector('[name="interior_code"]') ? form.querySelector('[name="interior_code"]').value : '',
      pay_method: payMethodRadio ? payMethodRadio.value : 'card-intl',
      agree_terms: form.querySelector('[name="agree_terms"]:checked') ? '1' : '',
    };

    console.log('Saving step2 data:', step2Data);

    var request = new XMLHttpRequest();
    request.open('POST', window.location.pathname + '?save_checkout_step2=1', true);
    request.setRequestHeader('Content-Type', 'application/json');
    request.setRequestHeader('X-CSRF-Token', csrfToken);
    request.onload = function() {
      console.log('Step2 data saved. Response:', request.responseText);
      // Update step 3 display with latest form data
      updateStep3Display(step2Data);
      // Call callback after data is saved
      if (typeof callback === 'function') {
        callback();
      }
    };
    request.onerror = function() {
      console.log('Error saving step2 data');
      // Still proceed even if AJAX fails
      if (typeof callback === 'function') {
        callback();
      }
    };
    request.send(JSON.stringify(step2Data));
  }

  function updateStep3Display(data) {
    var summaryName = form.querySelector('[data-summary-name]');
    var summaryPhone = form.querySelector('[data-summary-phone]');
    var summaryEmail = form.querySelector('[data-summary-email]');
    var summaryCccd = form.querySelector('[data-summary-cccd]');
    var summaryProvince = form.querySelector('[data-summary-province]');
    var summaryShowroom = form.querySelector('[data-summary-showroom]');

    if (summaryName) summaryName.textContent = data.full_name || '';
    if (summaryPhone) summaryPhone.textContent = data.phone || '';
    if (summaryEmail) summaryEmail.textContent = data.email || '';
    if (summaryCccd) summaryCccd.textContent = data.cccd || '';
    if (summaryProvince) summaryProvince.textContent = data.province || '';
    if (summaryShowroom) summaryShowroom.textContent = data.showroom || '';
  }

  function clearStep2Errors() {
    var errorElements = form.querySelectorAll('[data-error]');
    errorElements.forEach(function (elem) {
      elem.classList.add('hidden');
      elem.textContent = '';
    });
  }

  function showFieldError(fieldName, message) {
    var errorElement = form.querySelector('[data-error="' + fieldName + '"]');
    if (!errorElement) return;
    errorElement.textContent = message;
    errorElement.classList.remove('hidden');
  }

  function validateStep2() {
    clearStep2Errors();
    
    var hasError = false;
    var requiredFields = ['full_name', 'phone', 'email', 'cccd', 'province', 'showroom'];
    var requiredMessages = {
      full_name: 'Vui lòng điền họ và tên',
      phone: 'Vui lòng điền số điện thoại',
      email: 'Vui lòng điền email',
      cccd: 'Vui lòng điền số CCCD',
      province: 'Vui lòng chọn tỉnh thành',
      showroom: 'Vui lòng chọn showroom',
    };

    for (var i = 0; i < requiredFields.length; i += 1) {
      var name = requiredFields[i];
      var input = form.querySelector('[name="' + name + '"]');
      if (!input) continue;

      var value = String(input.value || '').trim();
      if (!value) {
        showFieldError(name, requiredMessages[name] || 'Vui lòng điền trường này');
        hasError = true;
        continue;
      }

      // Validate phone: exactly 10 digits
      if (name === 'phone') {
        if (!/^[0-9]{10}$/.test(value)) {
          showFieldError(name, 'Số điện thoại phải là 10 chữ số');
          hasError = true;
        }
      }

      // Validate email format
      if (name === 'email') {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
          showFieldError(name, 'Email không hợp lệ');
          hasError = true;
        }
      }
    }

    // Validate CCCD if filled: exactly 12 digits
    var cccdInput = form.querySelector('[name="cccd"]');
    if (cccdInput) {
      var cccdValue = String(cccdInput.value || '').trim();
      if (cccdValue && !/^[0-9]{12}$/.test(cccdValue)) {
        showFieldError('cccd', 'Số CCCD phải là 12 chữ số');
        hasError = true;
      }
    }

    if (hasError) {
      var firstError = form.querySelector('[data-error]:not(.hidden)');
      if (firstError) {
        firstError.closest('div').scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }

    return !hasError;
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

    colorChoices.forEach(function (choice) {
      var isActive = !!choice.querySelector('[data-color-radio]:checked');
      choice.classList.toggle('is-active', isActive);
      choice.classList.toggle('border-vfNavy', isActive);
      choice.classList.toggle('shadow-[0_0_0_2px_rgba(20,100,244,0.22)]', isActive);
      choice.classList.toggle('border-slate-200', !isActive);
    });

    var colorName = checked.getAttribute('data-color-name') || checked.value || '';
    var colorImage = checked.getAttribute('data-color-image') || '';
    selectedColorSurcharge = Number(checked.getAttribute('data-color-surcharge') || 0);
    if (Number.isNaN(selectedColorSurcharge) || selectedColorSurcharge < 0) {
      selectedColorSurcharge = 0;
    }

    if (summaryColor) {
      summaryColor.textContent = colorName;
    }

    if (summaryColorSurcharge) {
      summaryColorSurcharge.textContent = formatVnd(selectedColorSurcharge);
    }

    var surchargeWraps = form.querySelectorAll('[data-summary-color-surcharge-wrap], [data-selected-color-surcharge-wrap]');
    surchargeWraps.forEach(function (wrap) {
      if (!wrap) return;
      wrap.classList.toggle('hidden', selectedColorSurcharge <= 0);
    });

    if (selectedExterior) {
      selectedExterior.textContent = colorName;
    }

    if (mainImage && colorImage) {
      mainImage.src = colorImage;
    }

    updateDepositSummary();
  }

  function formatVnd(value) {
    var num = Number(value || 0);
    if (Number.isNaN(num)) {
      return '0 VNĐ';
    }

    return num.toLocaleString('vi-VN') + ' VNĐ';
  }

  function updatePriceBreakdown() {
    var basePrice = currentVariantPrice;
    if (Number.isNaN(basePrice) || basePrice < 0) {
      basePrice = 0;
    }
    // Total price with color surcharge (Giá xe kèm pin = Tổng dự kiến)
    var totalWithSurcharge = basePrice + selectedColorSurcharge;

    var priceTotalEstimates = form.querySelectorAll('[data-price-total-estimate]');
    priceTotalEstimates.forEach(function (el) {
      el.textContent = formatVnd(totalWithSurcharge);
    });
  }

  function updateDepositSummary() {
    var totalDeposit = baseDeposit;
    if (summaryDeposit) {
      summaryDeposit.textContent = formatVnd(totalDeposit);
    }

    var depositHint = form.querySelector('[data-deposit-hint]');
    if (depositHint) {
      depositHint.textContent = depositNonRefundable ? 'Tiền đặt cọc có thể không được hoàn lại.' : '';
    }

    var surchargeWraps = form.querySelectorAll('[data-summary-color-surcharge-wrap], [data-selected-color-surcharge-wrap]');
    surchargeWraps.forEach(function (wrap) {
      if (!wrap) return;
      wrap.classList.toggle('hidden', selectedColorSurcharge <= 0);
    });

    updatePriceBreakdown();
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

    currentVariantPrice = Number(variantPrice || 0);
    if (Number.isNaN(currentVariantPrice) || currentVariantPrice < 0) {
      currentVariantPrice = 0;
    }

    var priceText = formatVnd(currentVariantPrice);
    if (selectedVariantPrice) {
      selectedVariantPrice.textContent = priceText;
    }
    if (summaryPrice) {
      summaryPrice.textContent = priceText;
    }

    updatePriceBreakdown();
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
      
      // Save step 2 data to session before moving to step 3
      if (step === 2 && target === 3) {
        saveStep2DataToSession(function() {
          setStep(target);
        });
      } else {
        setStep(target);
      }
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
      
      // Save step 2 data before moving to step 3 from tabs
      if (step === 2 && target === 3) {
        saveStep2DataToSession(function() {
          setStep(target);
        });
      } else {
        setStep(target);
      }
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

  if (priceBreakdownToggle && priceBreakdownPanel) {
    priceBreakdownToggle.addEventListener('click', function () {
      var expanded = !priceBreakdownPanel.classList.contains('hidden');
      priceBreakdownPanel.classList.toggle('hidden', expanded);
      priceBreakdownToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      if (priceBreakdownIcon) {
        priceBreakdownIcon.classList.toggle('rotate-180', !expanded);
      }
    });
  }

  var selectedShowroom = showroomSelect ? String(showroomSelect.getAttribute('data-selected') || '') : '';
  populateShowrooms(selectedShowroom);

  var defaultVariantBtn = form.querySelector('[data-variant-btn].border-vfNavy') || variantButtons[0];
  if (defaultVariantBtn) {
    setActiveVariant(defaultVariantBtn);
  } else {
    var activeSwitchBtn = form.querySelector('[data-switch-product].border-vfNavy') || switchButtons[0];
    if (activeSwitchBtn) {
      var priceSpan = activeSwitchBtn.querySelector('.text-vfNavy');
      if (priceSpan) {
        currentVariantPrice = Number((priceSpan.textContent || '').replace(/[^0-9]/g, ''));
        if (Number.isNaN(currentVariantPrice) || currentVariantPrice < 0) {
          currentVariantPrice = 0;
        }
        if (selectedVariantPrice) {
          selectedVariantPrice.textContent = formatVnd(currentVariantPrice);
        }
      }
    }
  }

  syncSelectedColor();
  syncSelectedInterior();
  updatePriceBreakdown();
  updateDepositSummary();
  setStep(step);
})();
