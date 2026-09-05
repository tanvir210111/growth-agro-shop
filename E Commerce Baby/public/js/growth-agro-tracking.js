/**
 * Growth Agro Unified Tracking Client (GrowthAgroTracking)
 * Provides asynchronous, non-blocking behavioral analytics dispatch via sendBeacon & fetch.
 */
(function(window) {
  'use strict';

  var ENDPOINT = '/api/tracking/event';
  var recentEvents = new Set();

  function generateDedupeKey(eventName, data) {
    var id = data.entity_id || data.cta_identifier || '';
    return eventName + ':' + (data.entity_type || '') + ':' + id;
  }

  var GrowthAgroTracking = {
    /**
     * Dispatch an analytics event asynchronously.
     * @param {string} eventName - page_view, product_view, category_view, search, cta_click, add_to_cart, checkout_started, purchase
     * @param {Object} [data] - Event payload
     */
    track: function(eventName, data) {
      if (!eventName || typeof eventName !== 'string') return;
      data = data || {};

      // Light client-side deduplication for rapid repeated calls (within 1 second)
      var dedupeKey = generateDedupeKey(eventName, data);
      if (recentEvents.has(dedupeKey)) {
        return;
      }
      recentEvents.add(dedupeKey);
      setTimeout(function() {
        recentEvents.delete(dedupeKey);
      }, 1000);

      var payload = {
        event_name: eventName,
        event_id: data.event_id ? String(data.event_id) : null,
        entity_type: data.entity_type || null,
        entity_id: data.entity_id ? String(data.entity_id) : null,
        cta_identifier: data.cta_identifier || null,
        page_path: data.page_path || window.location.pathname,
        url: window.location.href,
        referrer: document.referrer || null,
        event_value: typeof data.event_value === 'number' ? data.event_value : null,
        properties: data.properties && typeof data.properties === 'object' ? data.properties : {}
      };

      var jsonString = JSON.stringify(payload);

      // Strategy 1: navigator.sendBeacon (Optimal for page unloads, CTA clicks, non-blocking background queue)
      if (navigator.sendBeacon) {
        try {
          var blob = new Blob([jsonString], { type: 'application/json' });
          var sent = navigator.sendBeacon(ENDPOINT, blob);
          if (sent) return;
        } catch (e) {
          // Fall back to fetch
        }
      }

      // Strategy 2: fetch with keepalive & credentials
      if (window.fetch) {
        try {
          window.fetch(ENDPOINT, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: jsonString,
            keepalive: true,
            credentials: 'same-origin'
          }).catch(function() {
            // Silently absorb failures so user experience is never affected
          });
        } catch (e) {}
      }
    },

    /**
     * Helper to track CTA clicks
     */
    trackCta: function(ctaIdentifier, entityId, properties) {
      this.track('cta_click', {
        entity_type: 'cta',
        entity_id: entityId || 'landing_page',
        cta_identifier: ctaIdentifier,
        properties: properties || {}
      });
    }
  };

  window.GrowthAgroTracking = GrowthAgroTracking;

  // Auto-initialize standard DOM listeners when document is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAutoListeners);
  } else {
    initAutoListeners();
  }

  function initAutoListeners() {
    // Delegated click listener for any element with data-track-cta
    document.addEventListener('click', function(e) {
      var target = e.target.closest('[data-track-cta]');
      if (target) {
        var ctaId = target.getAttribute('data-track-cta') || target.id || target.className;
        var entity = target.getAttribute('data-track-entity') || 'cta';
        GrowthAgroTracking.trackCta(ctaId, entity);
      }
    }, { passive: true });
  }

})(typeof window !== 'undefined' ? window : this);
