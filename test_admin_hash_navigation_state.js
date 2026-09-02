const fs = require('fs');
const path = require('path');
const assert = require('assert');
const vm = require('vm');

console.log('======================================================');
console.log('🧪 RUNNING ADMIN PANEL HASH NAVIGATION & STATE TESTS');
console.log('======================================================\n');

// -----------------------------------------------------------------------------
// Test 9: File Parity Check
// -----------------------------------------------------------------------------
console.log('--- Test 9: File Parity Check ---');
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
      const list = this.listeners[event.type || event] || [];
      list.forEach(fn => fn(event));
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
      if (selector.startsWith('.')) {
        const cls = selector.slice(1);
        return allElements.filter(el => el.classList.contains(cls));
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
    }
  };
}

// -----------------------------------------------------------------------------
// Test 1: No hash -> Dashboard
// -----------------------------------------------------------------------------
console.log('\n--- Test 1: Initial load without hash (Default Dashboard) ---');
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
// Test 2: #marketing -> Marketing visible + Setting & Configuration expanded
// -----------------------------------------------------------------------------
console.log('\n--- Test 2: Initial load with #marketing ---');
{
  const env = createDomEnvironment('#marketing');
  env.triggerDOMContentLoaded();

  const viewMarketing = env.document.getElementById('view-marketing');
  const viewDash = env.document.getElementById('view-dashboard');
  const subnavMarketing = env.document.getElementById('subnav-marketing');
  const groupSettings = env.document.getElementById('nav-group-settings');

  assert.strictEqual(viewMarketing.style.display, 'block', 'view-marketing must be visible');
  assert.strictEqual(viewDash.style.display, 'none', 'view-dashboard must be hidden');
  assert.strictEqual(subnavMarketing.classList.contains('active'), true, 'subnav-marketing must have active class');
  assert.strictEqual(groupSettings.classList.contains('open'), true, 'nav-group-settings must have open class');
  assert.strictEqual(env.getAppState().activeView, 'marketing', 'APP_STATE.activeView must be marketing');
  console.log('✅ #marketing successfully restores Marketing view & expands Setting & Configuration');
}

// -----------------------------------------------------------------------------
// Test 3: #courier-api -> Courier API visible + Setting & Configuration expanded
// -----------------------------------------------------------------------------
console.log('\n--- Test 3: Initial load with #courier-api ---');
{
  const env = createDomEnvironment('#courier-api');
  env.triggerDOMContentLoaded();

  const viewCourier = env.document.getElementById('view-courier-api');
  const viewDash = env.document.getElementById('view-dashboard');
  const subnavCourier = env.document.getElementById('subnav-courier-api');
  const groupSettings = env.document.getElementById('nav-group-settings');

  assert.strictEqual(viewCourier.style.display, 'block', 'view-courier-api must be visible');
  assert.strictEqual(viewDash.style.display, 'none', 'view-dashboard must be hidden');
  assert.strictEqual(subnavCourier.classList.contains('active'), true, 'subnav-courier-api must have active class');
  assert.strictEqual(groupSettings.classList.contains('open'), true, 'nav-group-settings must have open class');
  assert.strictEqual(env.getAppState().activeView, 'courier-api', 'APP_STATE.activeView must be courier-api');
  console.log('✅ #courier-api successfully restores Courier API view & expands Setting & Configuration');
}

// -----------------------------------------------------------------------------
// Test 4: #manage-admin -> Manage Admin visible + Admin group expanded
// -----------------------------------------------------------------------------
console.log('\n--- Test 4: Initial load with #manage-admin ---');
{
  const env = createDomEnvironment('#manage-admin');
  env.triggerDOMContentLoaded();

  const viewManageAdmin = env.document.getElementById('view-manage-admin');
  const viewDash = env.document.getElementById('view-dashboard');
  const subnavManageAdmin = env.document.getElementById('subnav-manage-admin');
  const groupAdmin = env.document.getElementById('nav-group-admin');

  assert.strictEqual(viewManageAdmin.style.display, 'block', 'view-manage-admin must be visible');
  assert.strictEqual(viewDash.style.display, 'none', 'view-dashboard must be hidden');
  assert.strictEqual(subnavManageAdmin.classList.contains('active'), true, 'subnav-manage-admin must have active class');
  assert.strictEqual(groupAdmin.classList.contains('open'), true, 'nav-group-admin must have open class');
  assert.strictEqual(env.getAppState().activeView, 'manage-admin', 'APP_STATE.activeView must be manage-admin');
  console.log('✅ #manage-admin successfully restores Manage Admin view & expands Admin group');
}

// -----------------------------------------------------------------------------
// Test 5: #orders -> Orders visible + Orders group expanded
// -----------------------------------------------------------------------------
console.log('\n--- Test 5: Initial load with #orders ---');
{
  const env = createDomEnvironment('#orders');
  env.triggerDOMContentLoaded();

  const viewOrders = env.document.getElementById('view-orders');
  const viewDash = env.document.getElementById('view-dashboard');
  const subnavOrders = env.document.getElementById('subnav-manage-order');
  const groupOrders = env.document.getElementById('nav-group-orders');

  assert.strictEqual(viewOrders.style.display, 'block', 'view-orders must be visible');
  assert.strictEqual(viewDash.style.display, 'none', 'view-dashboard must be hidden');
  assert.strictEqual(subnavOrders.classList.contains('active'), true, 'subnav-manage-order must have active class');
  assert.strictEqual(groupOrders.classList.contains('open'), true, 'nav-group-orders must have open class');
  assert.strictEqual(env.getAppState().activeView, 'orders', 'APP_STATE.activeView must be orders');
  console.log('✅ #orders successfully restores Orders view & expands Orders group');
}

// -----------------------------------------------------------------------------
// Test 6: switchView('marketing') updates URL hash to #marketing
// -----------------------------------------------------------------------------
console.log('\n--- Test 6: switchView(\'marketing\') updates URL hash ---');
{
  const env = createDomEnvironment('');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'dashboard');

  // Trigger switchView
  env.sandbox.window.switchView('marketing');

  const viewMarketing = env.document.getElementById('view-marketing');
  const groupSettings = env.document.getElementById('nav-group-settings');
  const subnavMarketing = env.document.getElementById('subnav-marketing');

  assert.strictEqual(env.location.hash, '#marketing', 'URL hash must be updated to #marketing');
  assert.strictEqual(viewMarketing.style.display, 'block', 'Marketing panel must be displayed');
  assert.strictEqual(groupSettings.classList.contains('open'), true, 'Setting & Configuration group must be open');
  assert.strictEqual(subnavMarketing.classList.contains('active'), true, 'subnav-marketing must be active');
  assert.strictEqual(env.getAppState().activeView, 'marketing', 'APP_STATE.activeView must be marketing');
  console.log('✅ switchView(\'marketing\') successfully updates URL hash and switches view');
}

// -----------------------------------------------------------------------------
// Test 7: hashchange to #courier-api -> Courier API becomes active
// -----------------------------------------------------------------------------
console.log('\n--- Test 7: hashchange to #courier-api (Browser Back / Forward simulation) ---');
{
  const env = createDomEnvironment('#marketing');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'marketing');

  // Simulate hashchange event (e.g. forward button clicked to #courier-api)
  env.triggerHashChange('#courier-api');

  const viewCourier = env.document.getElementById('view-courier-api');
  const viewMarketing = env.document.getElementById('view-marketing');
  const subnavCourier = env.document.getElementById('subnav-courier-api');

  assert.strictEqual(viewCourier.style.display, 'block', 'Courier API panel must be displayed');
  assert.strictEqual(viewMarketing.style.display, 'none', 'Marketing panel must be hidden');
  assert.strictEqual(subnavCourier.classList.contains('active'), true, 'subnav-courier-api must be active');
  assert.strictEqual(env.getAppState().activeView, 'courier-api', 'APP_STATE.activeView must be courier-api');
  console.log('✅ hashchange event cleanly updates active view without full page reload');
}

// -----------------------------------------------------------------------------
// Test 8: Invalid hash -> Fallback to Dashboard without crashing
// -----------------------------------------------------------------------------
console.log('\n--- Test 8: Invalid hash fallback ---');
{
  const env = createDomEnvironment('#invalid-view-xyz');
  env.triggerDOMContentLoaded();

  const viewDash = env.document.getElementById('view-dashboard');
  const navDash = env.document.getElementById('nav-dashboard');

  assert.strictEqual(viewDash.style.display, 'block', 'Dashboard panel must be visible on invalid hash');
  assert.strictEqual(navDash.classList.contains('active'), true, 'Dashboard nav link must be active');
  assert.strictEqual(env.getAppState().activeView, 'dashboard', 'APP_STATE.activeView must fall back to dashboard');
  console.log('✅ Invalid hash safely falls back to Dashboard without errors');
}

console.log('\n======================================================');
console.log('🎉 ALL 9 AUTOMATED TESTS PASSED SUCCESSFULLY!');
console.log('======================================================\n');
