const fs = require('fs');
const path = require('path');
const assert = require('assert');
const vm = require('vm');

console.log('======================================================');
console.log('🧪 RUNNING ADMIN PANEL HASH NAVIGATION & STATE TESTS');
console.log('======================================================\n');

// -----------------------------------------------------------------------------
// Test 17: File Parity Check
// -----------------------------------------------------------------------------
console.log('--- Test 17: File Parity Check ---');
const appJsPath1 = path.join(__dirname, 'admin', 'app.js');
const appJsPath2 = path.join(__dirname, 'E Commerce Baby', 'public', 'admin', 'app.js');
const appJs1 = fs.readFileSync(appJsPath1, 'utf8');
const appJs2 = fs.readFileSync(appJsPath2, 'utf8');
assert.strictEqual(appJs1, appJs2, 'admin/app.js and E Commerce Baby/public/admin/app.js must be identical');
console.log('✅ admin/app.js and E Commerce Baby/public/admin/app.js are 100% identical');

const htmlPath1 = path.join(__dirname, 'admin', 'index.html');
const htmlPath2 = path.join(__dirname, 'E Commerce Baby', 'public', 'admin', 'index.html');
const html1 = fs.readFileSync(htmlPath1, 'utf8');
const html2 = fs.readFileSync(htmlPath2, 'utf8');
assert.strictEqual(html1, html2, 'admin/index.html and E Commerce Baby/public/admin/index.html must be identical');
console.log('✅ admin/index.html and E Commerce Baby/public/admin/index.html are 100% identical');

// -----------------------------------------------------------------------------
// DOM Simulation Environment Helper
// -----------------------------------------------------------------------------
function createDomEnvironment(initialHash = '', htmlContent = html1) {
  const elementsById = new Map();
  const allElements = [];

  class ClassList {
    constructor() {
      this.classes = new Set();
    }
    add(...cls) { cls.forEach(c => this.classes.add(c)); }
    remove(...cls) { cls.forEach(c => this.classes.delete(c)); }
    contains(c) { return this.classes.has(c); }
    toggle(c) {
      if (this.classes.has(c)) { this.classes.delete(c); return false; }
      else { this.classes.add(c); return true; }
    }
    toString() { return Array.from(this.classes).join(' '); }
  }

  class MockElement {
    constructor(tag, id, classNames, attributes = {}) {
      this.tagName = tag.toUpperCase();
      this.id = id || '';
      this.classList = new ClassList();
      if (classNames) {
        classNames.split(/\s+/).filter(Boolean).forEach(c => this.classList.add(c));
      }
      this.style = {};
      this.attributes = { ...attributes };
      this.children = [];
      this.parentElement = null;
      this.listeners = {};
      this.innerHTML = '';
      this.textContent = '';
      this.value = '';
    }

    getContext(type) {
      return {
        clearRect: () => {},
        beginPath: () => {},
        arc: () => {},
        fill: () => {},
        stroke: () => {},
        moveTo: () => {},
        lineTo: () => {},
        closePath: () => {},
        fillRect: () => {},
        strokeRect: () => {},
        fillText: () => {},
        measureText: () => ({ width: 50 }),
        save: () => {},
        restore: () => {},
        translate: () => {},
        rotate: () => {},
        scale: () => {},
        createLinearGradient: () => ({ addColorStop: () => {} }),
        setLineDash: () => {}
      };
    }

    getBoundingClientRect() {
      return { width: 500, height: 300, top: 0, left: 0, right: 500, bottom: 300 };
    }

    appendChild(child) {
      this.children.push(child);
      child.parentElement = this;
      return child;
    }

    removeChild(child) {
      const idx = this.children.indexOf(child);
      if (idx !== -1) this.children.splice(idx, 1);
      return child;
    }

    getAttribute(name) { return this.attributes[name] || null; }
    setAttribute(name, val) { this.attributes[name] = val; }

    addEventListener(event, fn) {
      if (!this.listeners[event]) this.listeners[event] = [];
      this.listeners[event].push(fn);
    }

    dispatchEvent(event) {
      const eventObj = typeof event === 'string' ? { type: event } : event;
      if (!eventObj.target) eventObj.target = this;
      const list = this.listeners[eventObj.type] || [];
      list.forEach(fn => fn(eventObj));

      // Bubble up to parent unless stopPropagation
      if (!eventObj._stopped && this.parentElement) {
        this.parentElement.dispatchEvent(eventObj);
      }
    }

    click() {
      const eventObj = {
        type: 'click',
        target: this,
        _stopped: false,
        stopPropagation() { this._stopped = true; },
        preventDefault() {}
      };
      this.dispatchEvent(eventObj);
    }

    closest(selector) {
      let cur = this;
      while (cur) {
        if (selector.startsWith('.') && cur.classList.contains(selector.slice(1))) {
          return cur;
        }
        if (selector.startsWith('#') && cur.id === selector.slice(1)) {
          return cur;
        }
        cur = cur.parentElement;
      }
      return null;
    }

    querySelectorAll(selector) {
      const results = [];
      function traverse(el) {
        if (selector.startsWith('.')) {
          const cls = selector.slice(1);
          if (el.classList.contains(cls)) results.push(el);
        }
        el.children.forEach(traverse);
      }
      this.children.forEach(traverse);
      return results;
    }
  }

  // Parse HTML tags with ID, class, onclick
  const tagRegex = /<([a-z0-9]+)([^>]*?)>/gi;
  let match;
  while ((match = tagRegex.exec(htmlContent)) !== null) {
    const tag = match[1];
    const attrsStr = match[2];
    if (tag.toLowerCase() === 'script' || tag.toLowerCase() === 'style' || tag.toLowerCase() === 'link' || tag.toLowerCase() === 'meta') {
      continue;
    }

    const idMatch = attrsStr.match(/\bid=["']([^"']+)["']/i);
    const classMatch = attrsStr.match(/\bclass=["']([^"']+)["']/i);
    const onclickMatch = attrsStr.match(/\bonclick=["']([^"']+)["']/i);
    const styleMatch = attrsStr.match(/\bstyle=["']([^"']+)["']/i);

    const id = idMatch ? idMatch[1] : '';
    const classes = classMatch ? classMatch[1] : '';
    const attrs = {};
    if (onclickMatch) attrs.onclick = onclickMatch[1];
    if (id) attrs.id = id;

    const el = new MockElement(tag, id, classes, attrs);
    if (styleMatch) {
      const styleParts = styleMatch[1].split(';');
      styleParts.forEach(p => {
        const [k, v] = p.split(':');
        if (k && v) el.style[k.trim()] = v.trim();
      });
    }

    if (id) elementsById.set(id, el);
    allElements.push(el);
  }

  // Set up hierarchy for sidebar navigation groups
  const groupIds = [
    'nav-group-orders', 'nav-group-accounts', 'nav-group-products', 'nav-group-website',
    'nav-group-settings', 'nav-group-admin', 'nav-group-customer', 'nav-group-profit', 'nav-group-landingpages'
  ];

  groupIds.forEach(gId => {
    const groupEl = elementsById.get(gId);
    if (!groupEl) return;
    const groupStart = htmlContent.indexOf(`id="${gId}"`);
    if (groupStart === -1) return;
    const groupEnd = htmlContent.indexOf('</li>', groupStart);
    const groupSubHtml = htmlContent.slice(groupStart, groupEnd === -1 ? groupStart + 1000 : groupEnd + 500);

    const subIdMatches = groupSubHtml.match(/id="(subnav-[^"]+)"/g) || [];
    subIdMatches.forEach(m => {
      const sId = m.replace('id="', '').replace('"', '');
      const subEl = elementsById.get(sId);
      if (subEl) {
        subEl.parentElement = groupEl;
        groupEl.children.push(subEl);
      }
    });
  });

  const eventListeners = {
    DOMContentLoaded: [],
    hashchange: [],
    popstate: []
  };

  const location = {
    pathname: '/admin/',
    hash: initialHash,
    href: 'http://localhost:8000/admin/' + initialHash,
    origin: 'http://localhost:8000'
  };

  const history = {
    pushState(state, title, url) {
      if (url.startsWith('#')) {
        location.hash = url;
      } else {
        location.href = url;
        const hashIdx = url.indexOf('#');
        location.hash = hashIdx !== -1 ? url.slice(hashIdx) : '';
      }
    },
    replaceState(state, title, url) {
      if (url.startsWith('#')) {
        location.hash = url;
      } else {
        location.href = url;
        const hashIdx = url.indexOf('#');
        location.hash = hashIdx !== -1 ? url.slice(hashIdx) : '';
      }
    }
  };

  const mockDocument = {
    getElementById(id) {
      return elementsById.get(id) || null;
    },
    querySelectorAll(selector) {
      const parts = selector.trim().split(/\s+/);
      const last = parts[parts.length - 1];
      if (last.startsWith('.')) {
        const cls = last.slice(1);
        return allElements.filter(el => el.classList.contains(cls));
      }
      if (last.startsWith('#')) {
        const id = last.slice(1);
        return allElements.filter(el => el.id === id);
      }
      return [];
    },
    querySelector(selector) {
      if (selector.startsWith('.')) {
        const cls = selector.slice(1);
        const matchBracket = cls.match(/^([a-zA-Z0-9_-]+)\[([^=~|^$*]+)([*^$]?=)["']?([^"']+)["']?\]$/);
        if (matchBracket) {
          const baseClass = matchBracket[1];
          const attr = matchBracket[2];
          const op = matchBracket[3];
          const val = matchBracket[4];
          return allElements.find(el => {
            if (!el.classList.contains(baseClass)) return false;
            const attrVal = el.getAttribute(attr);
            if (!attrVal) return false;
            if (op === '*=') return attrVal.includes(val);
            if (op === '=') return attrVal === val;
            return false;
          }) || null;
        }
        return allElements.find(el => el.classList.contains(cls)) || null;
      }
      return null;
    },
    createElement(tag) {
      return new MockElement(tag);
    },
    addEventListener(event, fn) {
      if (eventListeners[event]) eventListeners[event].push(fn);
    }
  };

  const mockWindow = {
    innerWidth: 1200,
    location,
    history,
    document: mockDocument,
    localStorage: {
      _data: {
        admin_user: JSON.stringify({ id: 1, name: 'Admin', email: 'admin@gmail.com', role: 'Super Admin' }),
        admin_token: 'fake-token-123'
      },
      getItem(k) { return this._data[k] || null; },
      setItem(k, v) { this._data[k] = String(v); },
      removeItem(k) { delete this._data[k]; }
    },
    fetch: async () => ({ ok: true, json: async () => ({ success: true }) }),
    setTimeout: (fn) => setTimeout(fn, 0),
    clearTimeout: () => {},
    addEventListener(event, fn) {
      if (eventListeners[event]) eventListeners[event].push(fn);
    },
    dispatchEvent(event) {
      const list = eventListeners[event.type || event] || [];
      list.forEach(fn => fn(event));
    }
  };

  const sandbox = {
    ...mockWindow,
    document: mockDocument,
    location,
    history,
    localStorage: mockWindow.localStorage,
    fetch: mockWindow.fetch,
    setTimeout: mockWindow.setTimeout,
    clearTimeout: mockWindow.clearTimeout,
    AbortController: global.AbortController || class AbortController { constructor() { this.signal = {}; } abort() {} },
    Set: global.Set,
    Map: global.Map,
    Array: global.Array,
    Object: global.Object,
    String: global.String,
    Number: global.Number,
    Boolean: global.Boolean,
    Date: global.Date,
    Math: global.Math,
    JSON: global.JSON,
    parseInt: global.parseInt,
    parseFloat: global.parseFloat,
    encodeURIComponent: global.encodeURIComponent,
    decodeURIComponent: global.decodeURIComponent,
    console: { log: () => {}, warn: () => {}, error: () => {} },
    navigator: { clipboard: { writeText: async () => {} } }
  };
  sandbox.window = sandbox;
  sandbox.global = sandbox;
  sandbox.globalThis = sandbox;

  vm.createContext(sandbox);
  vm.runInContext(appJs1, sandbox);

  return {
    window: mockWindow,
    document: mockDocument,
    location,
    history,
    eventListeners,
    elementsById,
    sandbox,
    getAppState() {
      return vm.runInContext('APP_STATE', sandbox);
    },
    triggerDOMContentLoaded() {
      eventListeners['DOMContentLoaded'].forEach(fn => fn());
    },
    triggerHashChange(newHash) {
      location.hash = newHash;
      eventListeners['hashchange'].forEach(fn => fn({ type: 'hashchange' }));
    },
    triggerPopState() {
      eventListeners['popstate'].forEach(fn => fn({ type: 'popstate' }));
    }
  };
}

// -----------------------------------------------------------------------------
// Test 1: /admin/ -> Dashboard
// -----------------------------------------------------------------------------
console.log('\n--- Test 1: Initial load without hash (/admin/ -> Dashboard) ---');
{
  const env = createDomEnvironment('');
  env.triggerDOMContentLoaded();

  const viewDash = env.document.getElementById('view-dashboard');
  const navDash = env.document.getElementById('nav-dashboard');
  const viewMarketing = env.document.getElementById('view-marketing');

  assert.strictEqual(viewDash.style.display, 'block', 'view-dashboard must be visible (display: block)');
  assert.strictEqual(navDash.classList.contains('active'), true, 'nav-dashboard must have active class');
  assert.strictEqual(viewMarketing.style.display, 'none', 'view-marketing must be hidden (display: none)');
  assert.strictEqual(env.getAppState().activeView, 'dashboard', 'APP_STATE.activeView must be dashboard');
  console.log('✅ Dashboard loads and renders as default when no hash is present');
}

// -----------------------------------------------------------------------------
// Test 2: /admin/#marketing -> Marketing
// -----------------------------------------------------------------------------
console.log('\n--- Test 2: /admin/#marketing -> Marketing ---');
{
  const env = createDomEnvironment('#marketing');
  env.triggerDOMContentLoaded();

  const viewMarketing = env.document.getElementById('view-marketing');
  const subnavMarketing = env.document.getElementById('subnav-marketing');
  const groupSettings = env.document.getElementById('nav-group-settings');

  assert.strictEqual(viewMarketing.style.display, 'block', 'view-marketing must be visible');
  assert.strictEqual(subnavMarketing.classList.contains('active'), true, 'subnav-marketing must be active');
  assert.strictEqual(groupSettings.classList.contains('open'), true, 'Setting & Configuration group must be open');
  assert.strictEqual(env.getAppState().activeView, 'marketing', 'APP_STATE.activeView must be marketing');
  console.log('✅ /admin/#marketing restores Marketing view with expanded parent');
}

// -----------------------------------------------------------------------------
// Test 3: Refresh /admin/#marketing -> Marketing still active
// -----------------------------------------------------------------------------
console.log('\n--- Test 3: Refresh /admin/#marketing -> Marketing still active ---');
{
  const env = createDomEnvironment('#marketing');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'marketing');
  assert.strictEqual(env.document.getElementById('view-marketing').style.display, 'block');
  console.log('✅ Refreshing /admin/#marketing preserves Marketing view');
}

// -----------------------------------------------------------------------------
// Test 4: /admin/#courier-api -> Courier API
// -----------------------------------------------------------------------------
console.log('\n--- Test 4: /admin/#courier-api -> Courier API ---');
{
  const env = createDomEnvironment('#courier-api');
  env.triggerDOMContentLoaded();

  const viewCourier = env.document.getElementById('view-courier-api');
  const subnavCourier = env.document.getElementById('subnav-courier-api');
  const groupSettings = env.document.getElementById('nav-group-settings');

  assert.strictEqual(viewCourier.style.display, 'block', 'view-courier-api must be visible');
  assert.strictEqual(subnavCourier.classList.contains('active'), true, 'subnav-courier-api must be active');
  assert.strictEqual(groupSettings.classList.contains('open'), true, 'nav-group-settings must be open');
  assert.strictEqual(env.getAppState().activeView, 'courier-api', 'APP_STATE.activeView must be courier-api');
  console.log('✅ /admin/#courier-api restores Courier API view with expanded parent');
}

// -----------------------------------------------------------------------------
// Test 5: Refresh /admin/#courier-api -> Courier API still active
// -----------------------------------------------------------------------------
console.log('\n--- Test 5: Refresh /admin/#courier-api -> Courier API still active ---');
{
  const env = createDomEnvironment('#courier-api');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'courier-api');
  assert.strictEqual(env.document.getElementById('view-courier-api').style.display, 'block');
  console.log('✅ Refreshing /admin/#courier-api preserves Courier API view');
}

// -----------------------------------------------------------------------------
// Test 6: /admin/#orders -> Orders
// -----------------------------------------------------------------------------
console.log('\n--- Test 6: /admin/#orders -> Orders ---');
{
  const env = createDomEnvironment('#orders');
  env.triggerDOMContentLoaded();

  const viewOrders = env.document.getElementById('view-orders');
  const subnavOrders = env.document.getElementById('subnav-manage-order');
  const groupOrders = env.document.getElementById('nav-group-orders');

  assert.strictEqual(viewOrders.style.display, 'block', 'view-orders must be visible');
  assert.strictEqual(subnavOrders.classList.contains('active'), true, 'subnav-manage-order must be active');
  assert.strictEqual(groupOrders.classList.contains('open'), true, 'nav-group-orders must be open');
  assert.strictEqual(env.getAppState().activeView, 'orders', 'APP_STATE.activeView must be orders');
  console.log('✅ /admin/#orders restores Orders view with expanded parent');
}

// -----------------------------------------------------------------------------
// Test 7: Refresh /admin/#orders -> Orders still active
// -----------------------------------------------------------------------------
console.log('\n--- Test 7: Refresh /admin/#orders -> Orders still active ---');
{
  const env = createDomEnvironment('#orders');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'orders');
  assert.strictEqual(env.document.getElementById('view-orders').style.display, 'block');
  console.log('✅ Refreshing /admin/#orders preserves Orders view');
}

// -----------------------------------------------------------------------------
// Test 8: /admin/#manage-admin -> Manage Admin
// -----------------------------------------------------------------------------
console.log('\n--- Test 8: /admin/#manage-admin -> Manage Admin ---');
{
  const env = createDomEnvironment('#manage-admin');
  env.triggerDOMContentLoaded();

  const viewManageAdmin = env.document.getElementById('view-manage-admin');
  const subnavManageAdmin = env.document.getElementById('subnav-manage-admin');
  const groupAdmin = env.document.getElementById('nav-group-admin');

  assert.strictEqual(viewManageAdmin.style.display, 'block', 'view-manage-admin must be visible');
  assert.strictEqual(subnavManageAdmin.classList.contains('active'), true, 'subnav-manage-admin must be active');
  assert.strictEqual(groupAdmin.classList.contains('open'), true, 'nav-group-admin must be open');
  assert.strictEqual(env.getAppState().activeView, 'manage-admin', 'APP_STATE.activeView must be manage-admin');
  console.log('✅ /admin/#manage-admin restores Manage Admin view with expanded parent');
}

// -----------------------------------------------------------------------------
// Test 9: Refresh /admin/#manage-admin -> Manage Admin still active
// -----------------------------------------------------------------------------
console.log('\n--- Test 9: Refresh /admin/#manage-admin -> Manage Admin still active ---');
{
  const env = createDomEnvironment('#manage-admin');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'manage-admin');
  assert.strictEqual(env.document.getElementById('view-manage-admin').style.display, 'block');
  console.log('✅ Refreshing /admin/#manage-admin preserves Manage Admin view');
}

// -----------------------------------------------------------------------------
// Test 10: Clicking sidebar Marketing does not hide/collapse sidebar
// -----------------------------------------------------------------------------
console.log('\n--- Test 10: Clicking sidebar Marketing does not hide/collapse sidebar ---');
{
  const env = createDomEnvironment('');
  env.triggerDOMContentLoaded();

  const sidebar = env.document.getElementById('sidebar');
  assert.strictEqual(sidebar.classList.contains('collapsed'), false, 'Sidebar starts non-collapsed');

  // Trigger switchView to marketing
  env.sandbox.window.switchView('marketing');

  assert.strictEqual(sidebar.classList.contains('collapsed'), false, 'Sidebar must remain non-collapsed');
  assert.strictEqual(env.getAppState().activeView, 'marketing');
  console.log('✅ Switching to Marketing leaves sidebar visible and non-collapsed');
}

// -----------------------------------------------------------------------------
// Test 11: Clicking sidebar Orders does not hide/collapse sidebar
// -----------------------------------------------------------------------------
console.log('\n--- Test 11: Clicking sidebar Orders does not hide/collapse sidebar ---');
{
  const env = createDomEnvironment('');
  env.triggerDOMContentLoaded();

  const sidebar = env.document.getElementById('sidebar');
  env.sandbox.window.switchView('orders');

  assert.strictEqual(sidebar.classList.contains('collapsed'), false, 'Sidebar must remain non-collapsed');
  assert.strictEqual(env.getAppState().activeView, 'orders');
  console.log('✅ Switching to Orders leaves sidebar visible and non-collapsed');
}

// -----------------------------------------------------------------------------
// Test 12: Clicking Landing Page does not hide/collapse sidebar
// -----------------------------------------------------------------------------
console.log('\n--- Test 12: Clicking Landing Page does not hide/collapse sidebar ---');
{
  const env = createDomEnvironment('');
  env.triggerDOMContentLoaded();

  const sidebar = env.document.getElementById('sidebar');
  env.sandbox.window.switchView('landing-pages-list');

  assert.strictEqual(sidebar.classList.contains('collapsed'), false, 'Sidebar must remain non-collapsed');
  assert.strictEqual(env.getAppState().activeView, 'landing-pages-list');
  console.log('✅ Switching to Landing Pages leaves sidebar visible and non-collapsed');
}

// -----------------------------------------------------------------------------
// Test 13: Parent submenu remains expanded for the selected child
// -----------------------------------------------------------------------------
console.log('\n--- Test 13: Parent submenu remains expanded for selected child ---');
{
  const env = createDomEnvironment('');
  env.triggerDOMContentLoaded();

  // Test marketing in settings
  env.sandbox.window.switchView('marketing');
  assert.strictEqual(env.document.getElementById('nav-group-settings').classList.contains('open'), true);

  // Test add-admin in admin
  env.sandbox.window.switchView('add-admin');
  assert.strictEqual(env.document.getElementById('nav-group-admin').classList.contains('open'), true);
  assert.strictEqual(env.document.getElementById('nav-group-settings').classList.contains('open'), false);

  // Test income in accounts
  env.sandbox.window.switchView('income');
  assert.strictEqual(env.document.getElementById('nav-group-accounts').classList.contains('open'), true);
  console.log('✅ Parent submenu accordions expand correctly and exclusively for active views');
}

// -----------------------------------------------------------------------------
// Test 14: Invalid hash safely falls back to Dashboard
// -----------------------------------------------------------------------------
console.log('\n--- Test 14: Invalid hash fallback ---');
{
  const env = createDomEnvironment('#invalid-random-xyz');
  env.triggerDOMContentLoaded();

  const viewDash = env.document.getElementById('view-dashboard');
  assert.strictEqual(viewDash.style.display, 'block');
  assert.strictEqual(env.getAppState().activeView, 'dashboard');
  console.log('✅ Invalid hash safely falls back to Dashboard');
}

// -----------------------------------------------------------------------------
// Test 15: Browser hashchange switches views correctly
// -----------------------------------------------------------------------------
console.log('\n--- Test 15: Browser hashchange event ---');
{
  const env = createDomEnvironment('#marketing');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'marketing');

  env.triggerHashChange('#courier-api');
  assert.strictEqual(env.getAppState().activeView, 'courier-api');
  assert.strictEqual(env.document.getElementById('view-courier-api').style.display, 'block');
  console.log('✅ hashchange event seamlessly switches view');
}

// -----------------------------------------------------------------------------
// Test 16: Browser Back/Forward (popstate) works
// -----------------------------------------------------------------------------
console.log('\n--- Test 16: Browser Back/Forward (popstate) ---');
{
  const env = createDomEnvironment('#orders');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'orders');

  env.location.hash = '#marketing';
  env.triggerPopState();
  assert.strictEqual(env.getAppState().activeView, 'marketing');
  assert.strictEqual(env.document.getElementById('view-marketing').style.display, 'block');
  console.log('✅ popstate restores active view correctly');
}

console.log('\n======================================================');
console.log('🎉 ALL 17 AUTOMATED TESTS PASSED SUCCESSFULLY!');
console.log('======================================================\n');
