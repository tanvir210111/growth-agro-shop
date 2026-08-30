/**
 * Vendor-Neutral Event Dispatcher & Data Layer Foundation (Phase 3)
 * 
 * Implements a clean, standardized Data Layer event architecture:
 * - Safely initializes and preserves window.dataLayer
 * - Standardizes on snake_case event names: page_view, view_content, cta_click, select_item, initiate_checkout, purchase
 * - Generates collision-resistant event IDs
 * - Zero PII in dataLayer payloads
 * - Excludes all third-party tracking scripts (strictly Phase 4/5/7/8)
 */

// Initialize window.dataLayer safely without overwriting existing items
if (typeof window !== 'undefined') {
  window.dataLayer = window.dataLayer || [];
}

/**
 * Generate collision-safe event ID
 */
function generateEventId() {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID();
  }
  const timestamp = Date.now().toString(36);
  const rand = Math.random().toString(36).substring(2, 10);
  return `evt_${timestamp}_${rand}`;
}

class EventDispatcher {
  constructor() {
    this.listeners = new Map();
    this.isDebug = false;
    this.completedPurchases = new Set();
    this.hasInitiatedCheckout = false;
    this.hasViewedContent = false;
  }

  /**
   * Toggle development debug logging
   * @param {boolean} enabled 
   */
  enableDebug(enabled = true) {
    this.isDebug = Boolean(enabled);
  }

  /**
   * Subscribe to specific app events
   * @param {string} eventName 
   * @param {Function} callback 
   */
  on(eventName, callback) {
    if (!this.listeners.has(eventName)) {
      this.listeners.set(eventName, []);
    }
    this.listeners.get(eventName).push(callback);
    return () => this.off(eventName, callback);
  }

  /**
   * Unsubscribe from events
   * @param {string} eventName 
   * @param {Function} callback 
   */
  off(eventName, callback) {
    if (!this.listeners.has(eventName)) return;
    const callbacks = this.listeners.get(eventName).filter(cb => cb !== callback);
    this.listeners.set(eventName, callbacks);
  }

  /**
   * Core normalized event emitter
   * Pushes to window.dataLayer and notifies registered internal observers
   * @param {string} eventName 
   * @param {Object} payload 
   */
  emit(eventName, payload = {}) {
    const eventId = payload.event_id || generateEventId();
    const eventTime = new Date().toISOString();

    const pageInfo = typeof window !== 'undefined' ? {
      url: window.location.href,
      path: window.location.pathname,
      title: document.title
    } : {};

    const eventObject = {
      event: eventName,
      event_id: eventId,
      event_time: eventTime,
      page: pageInfo,
      ...payload
    };

    // Ensure event_id is preserved
    eventObject.event_id = eventId;

    // Push clean object into window.dataLayer
    if (typeof window !== 'undefined' && Array.isArray(window.dataLayer)) {
      window.dataLayer.push(eventObject);
    }

    // Notify registered listeners
    if (this.listeners.has(eventName)) {
      this.listeners.get(eventName).forEach(callback => {
        try {
          callback(eventObject);
        } catch (err) {
          console.warn(`[EventBus] Error in listener for ${eventName}:`, err);
        }
      });
    }

    // Debug logging without PII
    if (this.isDebug) {
      console.log(`%c[DataLayer Event: ${eventName}]`, 'color: #10b981; font-weight: bold;', eventObject);
    }

    return eventObject;
  }

  // ==========================================
  // Standardized Conversion Milestones
  // ==========================================

  /**
   * 1. page_view - Triggered once on initial page load
   */
  trackPageView(customPageData = {}) {
    return this.emit("page_view", customPageData);
  }

  /**
   * 2. view_content - Triggered once when product is viewed
   */
  trackViewContent(product, selectedVariant) {
    if (this.hasViewedContent) return null;
    this.hasViewedContent = true;

    const variant = selectedVariant || {};
    const price = variant.price || (product.pricing ? product.pricing.offerPrice : 990);

    return this.emit("view_content", {
      ecommerce: {
        currency: product.currencyCode || "BDT",
        value: price,
        items: [
          {
            item_id: product.id || "chicken-booster",
            item_name: product.shortName || product.name || "Chicken Booster",
            item_variant: variant.id || "variant-2",
            variant_name: variant.name || "Default",
            price: price,
            quantity: 1
          }
        ]
      }
    });
  }

  /**
   * 3. cta_click - Triggered on major order CTA clicks
   */
  trackCtaClick(ctaName, targetSection = "#order-section") {
    return this.emit("cta_click", {
      cta_name: ctaName || "Order Button",
      target_section: targetSection
    });
  }

  /**
   * 4. select_item - Triggered when user selects a variant package
   */
  trackSelectItem(product, variant) {
    return this.emit("select_item", {
      ecommerce: {
        currency: product.currencyCode || "BDT",
        value: variant.price,
        items: [
          {
            item_id: product.id || "chicken-booster",
            item_name: product.shortName || product.name || "Chicken Booster",
            item_variant: variant.id,
            variant_name: variant.name,
            price: variant.price,
            quantity: 1
          }
        ]
      }
    });
  }

  /**
   * 5. initiate_checkout - Triggered on first checkout flow interaction (fired once per session)
   */
  trackInitiateCheckout(product, selectedVariant, totalValue) {
    if (this.hasInitiatedCheckout) return null;
    this.hasInitiatedCheckout = true;

    const variant = selectedVariant || {};
    const price = totalValue || variant.price || 990;

    return this.emit("initiate_checkout", {
      ecommerce: {
        currency: product.currencyCode || "BDT",
        value: price,
        items: [
          {
            item_id: product.id || "chicken-booster",
            item_name: product.shortName || product.name || "Chicken Booster",
            item_variant: variant.id || "variant-2",
            variant_name: variant.name || "Default",
            price: variant.price || price,
            quantity: 1
          }
        ]
      }
    });
  }

  /**
   * 6. purchase - Triggered strictly ONLY after successful server order confirmation
   * Contains authoritative server-confirmed order values. Zero customer PII in dataLayer.
   */
  trackPurchase(orderResult) {
    if (!orderResult || !orderResult.order_number) {
      console.warn('[EventBus] trackPurchase called without valid order_number. Aborted.');
      return null;
    }

    const orderNumber = orderResult.order_number;

    // Prevent duplicate purchase event firing for same order ID
    if (this.completedPurchases.has(orderNumber)) {
      return null;
    }
    this.completedPurchases.add(orderNumber);

    const eventId = `evt_pur_${orderNumber.replace(/[^A-Za-z0-9]/g, '_')}`;

    return this.emit("purchase", {
      event_id: eventId,
      transaction_id: orderNumber,
      order_number: orderNumber,
      ecommerce: {
        transaction_id: orderNumber,
        value: orderResult.total,
        currency: orderResult.currency || "BDT",
        shipping: orderResult.delivery_charge || 0,
        items: [
          {
            item_id: orderResult.product_id || "chicken-booster",
            item_name: orderResult.product || "Chicken Booster",
            item_variant: orderResult.variant_id || "variant-2",
            variant_name: orderResult.variant || "",
            price: orderResult.subtotal || orderResult.total,
            quantity: orderResult.quantity || 1
          }
        ]
      }
    });
  }
}

export const eventBus = new EventDispatcher();
if (typeof window !== 'undefined') {
  window.trackingEvents = eventBus;
}
