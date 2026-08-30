/**
 * Urgency Offer Countdown Timer Component
 */

export function initCountdown(elementId = 'offer-countdown', initialMinutes = 145) {
  const container = document.getElementById(elementId);
  if (!container) return;

  // Store expiration in sessionStorage to persist smoothly across refreshes
  const storageKey = 'cb_offer_end_time';
  let endTime = sessionStorage.getItem(storageKey);

  if (!endTime || isNaN(endTime)) {
    endTime = Date.now() + initialMinutes * 60 * 1000;
    sessionStorage.setItem(storageKey, endTime);
  } else {
    endTime = parseInt(endTime, 10);
    if (endTime < Date.now()) {
      endTime = Date.now() + 45 * 60 * 1000; // Reset with fresh 45m urgency
      sessionStorage.setItem(storageKey, endTime);
    }
  }

  function update() {
    const remaining = Math.max(0, endTime - Date.now());
    const hours = Math.floor(remaining / (1000 * 60 * 60));
    const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((remaining % (1000 * 60)) / 1000);

    const pad = (n) => String(n).padStart(2, '0');

    const hoursEl = container.querySelector('.timer-hours');
    const minsEl = container.querySelector('.timer-mins');
    const secsEl = container.querySelector('.timer-secs');

    if (hoursEl) hoursEl.textContent = pad(hours);
    if (minsEl) minsEl.textContent = pad(minutes);
    if (secsEl) secsEl.textContent = pad(seconds);
  }

  update();
  setInterval(update, 1000);
}
