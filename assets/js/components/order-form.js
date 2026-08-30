/**
 * Order Form & Checkout Component
 * Handles live validation, delivery fee calculation, dynamic summary,
 * and standard order dispatch boundaries.
 */

import { appState } from '../core/state.js';
import { eventBus } from '../core/event-bus.js';
import { productData } from '../data/product-data.js';

export function initOrderForm() {
  const form = document.getElementById('checkout-form');
  const nameInput = document.getElementById('customer-name');
  const phoneInput = document.getElementById('customer-phone');
  const addressInput = document.getElementById('customer-address');
  const notesInput = document.getElementById('customer-notes');
  const deliveryRadios = document.querySelectorAll('input[name="delivery-zone"]');

  // Summary elements
  const summaryPackageName = document.getElementById('summary-package-name');
  const summarySubtotal = document.getElementById('summary-subtotal');
  const summaryDelivery = document.getElementById('summary-delivery');
  const summaryTotal = document.getElementById('summary-total');
  const summarySavings = document.getElementById('summary-savings');
  const submitBtn = document.getElementById('submit-order-btn');
  const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
  const btnSpinner = submitBtn ? submitBtn.querySelector('.btn-spinner') : null;

  // Modal elements
  const successModal = document.getElementById('order-success-modal');
  const modalOrderId = document.getElementById('modal-order-id');
  const modalPackage = document.getElementById('modal-package');
  const modalTotal = document.getElementById('modal-total');
  const modalAddress = document.getElementById('modal-address');
  const modalPhone = document.getElementById('modal-phone');
  const modalCloseBtn = document.getElementById('modal-close-btn');
  const modalWhatsappBtn = document.getElementById('modal-whatsapp-btn');

  if (!form) return;

  // 1. Subscribe to App State changes to update order summary live
  appState.subscribe((state) => {
    if (summaryPackageName) summaryPackageName.textContent = state.selectedVariant.name;
    if (summarySubtotal) summarySubtotal.textContent = `${productData.currency}${state.subtotal}`;
    
    if (summaryDelivery) {
      if (state.isFreeDelivery) {
        summaryDelivery.innerHTML = `<span class="badge-free">ফ্রি ডেলিভারি (৳০)</span>`;
      } else {
        summaryDelivery.textContent = `${productData.currency}${state.deliveryCharge}`;
      }
    }

    if (summaryTotal) summaryTotal.textContent = `${productData.currency}${state.totalAmount}`;
    if (summarySavings && state.totalSavings > 0) {
      summarySavings.textContent = `মোট সাশ্রয়: ${productData.currency}${state.totalSavings}`;
      summarySavings.style.display = 'inline-block';
    } else if (summarySavings) {
      summarySavings.style.display = 'none';
    }

    // Sync delivery radio state
    deliveryRadios.forEach(radio => {
      if (radio.value === state.selectedDelivery.id) {
        radio.checked = true;
      }
    });
  });

  // 2. Track form interaction on first focus
  const formInputs = [nameInput, phoneInput, addressInput, notesInput].filter(Boolean);
  formInputs.forEach(input => {
    input.addEventListener('focus', () => {
      appState.triggerCheckoutInitiation();
      eventBus.trackFormInteraction(input.name || input.id);
    }, { once: true });
  });

  // 3. Delivery zone radio change listener
  deliveryRadios.forEach(radio => {
    radio.addEventListener('change', (e) => {
      appState.setDeliveryZone(e.target.value);
    });
  });

  // Helper validation functions
  function validatePhone(phone) {
    const clean = phone.replace(/[\s\-]/g, '');
    const bdPhoneRegex = /^(?:\+88|88)?(01[3-9]\d{8})$/;
    return bdPhoneRegex.test(clean);
  }

  function showError(inputEl, message) {
    const parent = inputEl.closest('.form-group') || inputEl.parentElement;
    parent.classList.add('has-error');
    let errorEl = parent.querySelector('.field-error-msg');
    if (!errorEl) {
      errorEl = document.createElement('span');
      errorEl.className = 'field-error-msg';
      parent.appendChild(errorEl);
    }
    errorEl.textContent = message;
  }

  function clearError(inputEl) {
    const parent = inputEl.closest('.form-group') || inputEl.parentElement;
    parent.classList.remove('has-error');
    const errorEl = parent.querySelector('.field-error-msg');
    if (errorEl) errorEl.textContent = '';
  }

  // Real-time input cleanup
  [nameInput, phoneInput, addressInput].forEach(input => {
    if (!input) return;
    input.addEventListener('input', () => clearError(input));
  });

  // 4. Form Submit Handler
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    let isValid = true;
    const name = nameInput ? nameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';
    const address = addressInput ? addressInput.value.trim() : '';
    const notes = notesInput ? notesInput.value.trim() : '';

    if (!name || name.length < 2) {
      showError(nameInput, 'অনুগ্রহ করে আপনার সম্পূর্ণ নাম লিখুন');
      isValid = false;
    } else {
      clearError(nameInput);
    }

    if (!phone || !validatePhone(phone)) {
      showError(phoneInput, 'সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 01864444411)');
      isValid = false;
    } else {
      clearError(phoneInput);
    }

    if (!address || address.length < 8) {
      showError(addressInput, 'অনুগ্রহ করে আপনার বিস্তারিত ঠিকানা (জেলা, থানা, এলাকা) দিন');
      isValid = false;
    } else {
      clearError(addressInput);
    }

    if (!isValid) {
      const firstError = form.querySelector('.has-error input, .has-error textarea');
      if (firstError) firstError.focus();
      return;
    }

    const state = appState.getState();
    const orderId = `CB-${Date.now().toString().slice(-6)}`;

    // Dispatch Lead tracking event
    eventBus.trackLead({
      name,
      phone,
      address,
      notes,
      deliveryZone: state.selectedDelivery.label
    }, {
      orderId,
      variantName: state.selectedVariant.name,
      totalAmount: state.totalAmount
    });

    // Loading State
    if (submitBtn) submitBtn.disabled = true;
    if (btnText) btnText.style.display = 'none';
    if (btnSpinner) btnSpinner.style.display = 'inline-block';

    // Simulate reliable async mock order creation (800ms)
    setTimeout(() => {
      // Reset button
      if (submitBtn) submitBtn.disabled = false;
      if (btnText) btnText.style.display = 'inline-block';
      if (btnSpinner) btnSpinner.style.display = 'none';

      const purchaseResult = {
        orderId,
        product: state.product,
        variant: state.selectedVariant,
        delivery: state.selectedDelivery,
        totalAmount: state.totalAmount,
        customer: {
          name,
          phone,
          address,
          notes
        }
      };

      // Dispatch tracking Purchase milestone
      eventBus.trackPurchase(purchaseResult);

      // Display Success Modal
      if (modalOrderId) modalOrderId.textContent = orderId;
      if (modalPackage) modalPackage.textContent = state.selectedVariant.name;
      if (modalTotal) modalTotal.textContent = `${productData.currency}${state.totalAmount}`;
      if (modalPhone) modalPhone.textContent = phone;
      if (modalAddress) modalAddress.textContent = address;

      if (modalWhatsappBtn) {
        const waText = encodeURIComponent(`অর্ডার আইডি: ${orderId}\nপণ্য: ${state.selectedVariant.name}\nমোট মূল্য: ${productData.currency}${state.totalAmount}\nনাম: ${name}\nফোন: ${phone}\nঠিকানা: ${address}`);
        modalWhatsappBtn.href = `https://wa.me/${productData.whatsappNumber}?text=${waText}`;
      }

      if (successModal) {
        successModal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }

      // Reset form
      form.reset();
    }, 800);
  });

  // Modal close handlers
  if (modalCloseBtn && successModal) {
    modalCloseBtn.addEventListener('click', () => {
      successModal.classList.remove('active');
      document.body.style.overflow = '';
    });
  }

  if (successModal) {
    successModal.addEventListener('click', (e) => {
      if (e.target === successModal) {
        successModal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  }
}
