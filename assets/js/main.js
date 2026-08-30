/**
 * Main Application Entry Point
 * Orchestrates component initialization, tracking triggers, and CTA interactions.
 */

import { productData } from './data/product-data.js';
import { eventBus } from './core/event-bus.js';
import { appState } from './core/state.js';
import { initCountdown } from './components/countdown.js';
import { initProductGallery } from './components/product-gallery.js';
import { initVariantSelector } from './components/variant-selector.js';
import { initOrderForm } from './components/order-form.js';
import { initFaqAccordion } from './components/faq-accordion.js';

document.addEventListener('DOMContentLoaded', () => {
  // 1. Initialize Core Components
  initCountdown('hero-countdown', 120);
  initCountdown('order-countdown', 120);
  initProductGallery();
  initVariantSelector();
  initOrderForm();
  initFaqAccordion();

  // 2. Track Standard Initial Milestones (PageView & ViewContent)
  eventBus.trackPageView({
    page_type: 'single_product_landing',
    product_name: productData.shortName
  });

  eventBus.trackViewContent(productData, appState.getState().selectedVariant);

  // 3. Smooth CTA Scroll with Action Tracking
  const ctaButtons = document.querySelectorAll('a[href^="#"], button[data-scroll-to]');
  ctaButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      const targetSelector = btn.getAttribute('href') || btn.getAttribute('data-scroll-to');
      if (targetSelector && targetSelector.startsWith('#')) {
        const targetEl = document.querySelector(targetSelector);
        if (targetEl) {
          e.preventDefault();
          const ctaName = btn.getAttribute('data-cta-name') || btn.textContent.trim() || 'Generic CTA';
          
          // Track CTA Click event
          eventBus.trackCtaClick(ctaName, targetSelector);

          // Smooth scroll to target
          targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });

          // If scrolling to order section, trigger initiate checkout hook
          if (targetSelector === '#order-section') {
            appState.triggerCheckoutInitiation();
          }
        }
      }
    });
  });

  // 4. Sticky Mobile Bar Visibility Controller
  const stickyBar = document.getElementById('sticky-mobile-cta');
  const heroSection = document.getElementById('hero-section');
  const orderSection = document.getElementById('order-section');

  if (stickyBar && heroSection) {
    const handleScroll = () => {
      const heroBottom = heroSection.getBoundingClientRect().bottom;
      const orderTop = orderSection ? orderSection.getBoundingClientRect().top : window.innerHeight * 2;

      // Show after scrolling past hero, hide when reaching order section
      if (heroBottom < 0 && orderTop > window.innerHeight - 80) {
        stickyBar.classList.add('visible');
      } else {
        stickyBar.classList.remove('visible');
      }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }
});
