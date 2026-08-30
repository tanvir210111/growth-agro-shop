/**
 * Application State Management
 * Maintains reactive single source of truth for cart selection,
 * delivery calculation, and pricing updates.
 */

import { productData } from '../data/product-data.js';
import { eventBus } from './event-bus.js';

class AppState {
  constructor() {
    this.product = productData;
    // Default to the popular combo or first variant
    this.selectedVariant = productData.variants.find(v => v.isPopular) || productData.variants[0];
    this.selectedDelivery = productData.deliveryOptions[0]; // inside dhaka default
    this.orderCount = 1;
    this.hasInitiatedCheckout = false;
    this.subscribers = [];
  }

  subscribe(callback) {
    this.subscribers.push(callback);
    callback(this.getState());
    return () => {
      this.subscribers = this.subscribers.filter(cb => cb !== callback);
    };
  }

  notify() {
    const currentState = this.getState();
    this.subscribers.forEach(cb => cb(currentState));
  }

  getState() {
    const subtotal = this.selectedVariant.price;
    // Check if free delivery applies
    const deliveryCharge = this.selectedVariant.freeDelivery ? 0 : this.selectedDelivery.charge;
    const totalAmount = subtotal + deliveryCharge;
    const totalSavings = this.selectedVariant.savings + (this.selectedVariant.freeDelivery ? this.selectedDelivery.charge : 0);

    return {
      product: this.product,
      selectedVariant: this.selectedVariant,
      selectedDelivery: this.selectedDelivery,
      subtotal,
      deliveryCharge,
      totalAmount,
      totalSavings,
      isFreeDelivery: this.selectedVariant.freeDelivery,
      quantity: this.selectedVariant.quantity
    };
  }

  setVariant(variantId) {
    const variant = this.product.variants.find(v => v.id === variantId);
    if (variant && variant.id !== this.selectedVariant.id) {
      this.selectedVariant = variant;
      eventBus.trackSelectItem(this.product, variant);
      this.notify();
    }
  }

  setDeliveryZone(zoneId) {
    const option = this.product.deliveryOptions.find(d => d.id === zoneId);
    if (option && option.id !== this.selectedDelivery.id) {
      this.selectedDelivery = option;
      this.notify();
    }
  }

  triggerCheckoutInitiation() {
    if (!this.hasInitiatedCheckout) {
      this.hasInitiatedCheckout = true;
      const state = this.getState();
      eventBus.trackInitiateCheckout(this.product, this.selectedVariant, state.totalAmount);
    }
  }
}

export const appState = new AppState();
