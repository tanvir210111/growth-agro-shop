/**
 * Package & Variant Selector Component
 */

import { appState } from '../core/state.js';
import { productData } from '../data/product-data.js';

export function initVariantSelector() {
  const container = document.getElementById('variant-options-container');
  if (!container) return;

  function renderVariants(currentState) {
    container.innerHTML = productData.variants.map(variant => {
      const isSelected = variant.id === currentState.selectedVariant.id;
      return `
        <div class="package-card ${isSelected ? 'selected' : ''} ${variant.isPopular ? 'popular' : ''}" data-variant-id="${variant.id}" role="radio" aria-checked="${isSelected}" tabindex="0">
          ${variant.badge ? `<div class="package-badge">${variant.badge}</div>` : ''}
          <div class="package-card-header">
            <div class="package-radio-custom ${isSelected ? 'checked' : ''}"></div>
            <div class="package-title-group">
              <h4 class="package-name">${variant.name}</h4>
              <p class="package-desc">${variant.description}</p>
            </div>
          </div>
          <div class="package-pricing">
            <div class="price-current">${productData.currency}${variant.price}</div>
            <div class="price-original">${productData.currency}${variant.regularPrice}</div>
            <div class="price-savings">সাশ্রয় ${productData.currency}${variant.savings}</div>
          </div>
        </div>
      `;
    }).join('');

    // Attach click events
    container.querySelectorAll('.package-card').forEach(card => {
      const variantId = card.getAttribute('data-variant-id');
      const select = () => {
        appState.setVariant(variantId);
      };
      card.addEventListener('click', select);
      card.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          select();
        }
      });
    });
  }

  // Subscribe to state updates
  appState.subscribe(renderVariants);
}
