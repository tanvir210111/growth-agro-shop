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
      this._innerHTML = '';
      this.textContent = '';
      this.value = '';
    }

    get innerHTML() { return this._innerHTML; }
    set innerHTML(val) {
      this._innerHTML = val;
      if (!val) {
        this.children = [];
      }
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
    URLSearchParams: global.URLSearchParams,
    URL: global.URL,
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

// -----------------------------------------------------------------------------
// Test 18: "🌐 View Live Store" button verification
// -----------------------------------------------------------------------------
console.log('\n--- Test 18: View Live Store button verification ---');
{
  [
    { name: 'admin/index.html', content: html1 },
    { name: 'E Commerce Baby/public/admin/index.html', content: html2 }
  ].forEach(({ name, content }) => {
    const liveStoreMatch = content.match(/<a\b[^>]*>[\s\S]*?🌐\s*View Live Store[\s\S]*?<\/a>/i);
    assert.ok(liveStoreMatch, `${name} must contain the "View Live Store" link`);
    const tag = liveStoreMatch[0];
    assert.ok(tag.includes('href="https://growthagro.shop/"'), `${name} View Live Store must have href="https://growthagro.shop/"`);
    assert.ok(tag.includes('target="_blank"'), `${name} View Live Store must have target="_blank"`);
    assert.ok(!tag.includes('127.0.0.1:8000'), `${name} View Live Store must not point to 127.0.0.1:8000`);
    assert.ok(!tag.includes('localhost'), `${name} View Live Store must not point to localhost`);
    assert.ok(!tag.includes('href="/"'), `${name} View Live Store must not have href="/"`);
  });
  console.log('✅ "🌐 View Live Store" points to https://growthagro.shop/ with target="_blank" in both HTML files');
}

// -----------------------------------------------------------------------------
// Test 19: "🛍️ View Website Orders →" button verification
// -----------------------------------------------------------------------------
console.log('\n--- Test 19: View Website Orders button verification ---');
{
  [
    { name: 'admin/index.html', content: html1 },
    { name: 'E Commerce Baby/public/admin/index.html', content: html2 }
  ].forEach(({ name, content }) => {
    const btnMatch = content.match(/<button\b[^>]*>[\s\S]*?View Website Orders[\s\S]*?<\/button>/i);
    assert.ok(btnMatch, `${name} must contain the "View Website Orders" button`);
    const btnTag = btnMatch[0];
    assert.ok(btnTag.includes("filterOrdersBySource('MAIN_WEBSITE')"), `${name} button must call filterOrdersBySource('MAIN_WEBSITE')`);
    assert.ok(btnTag.includes("switchView('main-website-orders')"), `${name} button must call switchView('main-website-orders')`);
  });

  const env = createDomEnvironment();
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'dashboard');

  // Simulate clicking the dashboard button
  env.sandbox.window.filterOrdersBySource('MAIN_WEBSITE');
  env.sandbox.window.switchView('main-website-orders');

  assert.strictEqual(env.getAppState().activeView, 'main-website-orders');
  assert.strictEqual(env.getAppState().sourceFilter, 'MAIN_WEBSITE');
  assert.strictEqual(env.document.getElementById('view-orders').style.display, 'block');
  assert.strictEqual(env.document.getElementById('subnav-website-orders').classList.contains('active'), true);
  assert.strictEqual(env.document.getElementById('nav-group-orders').classList.contains('open'), true);
  assert.strictEqual(env.location.hash, '#main-website-orders');
  console.log('✅ "🛍️ View Website Orders →" opens Orders view with activeView=main-website-orders and sourceFilter=MAIN_WEBSITE');
}

// -----------------------------------------------------------------------------
// Test 20: "🚀 View Landing Page Orders →" button verification
// -----------------------------------------------------------------------------
console.log('\n--- Test 20: View Landing Page Orders button verification ---');
{
  [
    { name: 'admin/index.html', content: html1 },
    { name: 'E Commerce Baby/public/admin/index.html', content: html2 }
  ].forEach(({ name, content }) => {
    const btnMatch = content.match(/<button\b[^>]*>[\s\S]*?View Landing Page Orders[\s\S]*?<\/button>/i);
    assert.ok(btnMatch, `${name} must contain the "View Landing Page Orders" button`);
    const btnTag = btnMatch[0];
    assert.ok(btnTag.includes("filterOrdersBySource('LANDING')"), `${name} button must call filterOrdersBySource('LANDING')`);
    assert.ok(btnTag.includes("switchView('landing-page-orders')"), `${name} button must call switchView('landing-page-orders')`);
  });

  const env = createDomEnvironment();
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'dashboard');

  // Simulate clicking the dashboard button
  env.sandbox.window.filterOrdersBySource('LANDING');
  env.sandbox.window.switchView('landing-page-orders');

  assert.strictEqual(env.getAppState().activeView, 'landing-page-orders');
  assert.strictEqual(env.getAppState().sourceFilter, 'LANDING');
  assert.strictEqual(env.document.getElementById('view-orders').style.display, 'block');
  assert.strictEqual(env.document.getElementById('subnav-landing-orders').classList.contains('active'), true);
  assert.strictEqual(env.document.getElementById('nav-group-orders').classList.contains('open'), true);
  assert.strictEqual(env.location.hash, '#landing-page-orders');
  console.log('✅ "🚀 View Landing Page Orders →" opens Orders view with activeView=landing-page-orders and sourceFilter=LANDING');
}

// -----------------------------------------------------------------------------
// Test 21: Source normalization & filterOrdersBySource decoupling
// -----------------------------------------------------------------------------
console.log('\n--- Test 21: Source normalization & decoupling ---');
{
  const env = createDomEnvironment();
  env.triggerDOMContentLoaded();

  // Ensure filterOrdersBySource does NOT perform navigation itself
  assert.strictEqual(env.getAppState().activeView, 'dashboard');
  env.sandbox.window.filterOrdersBySource('MAIN_WEBSITE');
  assert.strictEqual(env.getAppState().sourceFilter, 'MAIN_WEBSITE');
  assert.strictEqual(env.getAppState().activeView, 'dashboard', 'filterOrdersBySource must not change activeView directly');

  // Aliases for LANDING
  ['LANDING', 'LANDING_PAGE', 'landing', 'landing-orders', 'landing-page'].forEach(alias => {
    env.sandbox.window.filterOrdersBySource(alias);
    assert.strictEqual(env.getAppState().sourceFilter, 'LANDING', `Alias "${alias}" must normalize to LANDING`);
  });

  // Aliases for MAIN_WEBSITE
  ['MAIN_WEBSITE', 'main-website', 'storefront', 'baby-fashion-storefront', 'website'].forEach(alias => {
    env.sandbox.window.filterOrdersBySource(alias);
    assert.strictEqual(env.getAppState().sourceFilter, 'MAIN_WEBSITE', `Alias "${alias}" must normalize to MAIN_WEBSITE`);
  });

  // Clearing / Null / All
  [null, undefined, '', 'all', 'ALL', 'unknown-val'].forEach(val => {
    env.sandbox.window.filterOrdersBySource(val);
    assert.strictEqual(env.getAppState().sourceFilter, null, `Value "${val}" must clear sourceFilter to null`);
  });

  console.log('✅ Source normalization correctly handles all canonical values and aliases without auto-navigating');
}

// -----------------------------------------------------------------------------
// Test 22: Direct URL / Hash navigation support
// -----------------------------------------------------------------------------
console.log('\n--- Test 22: Direct URL / Hash navigation support ---');
{
  // #main-website-orders
  {
    const env = createDomEnvironment('#main-website-orders');
    env.triggerDOMContentLoaded();
    assert.strictEqual(env.getAppState().activeView, 'main-website-orders');
    assert.strictEqual(env.getAppState().sourceFilter, 'MAIN_WEBSITE');
    assert.strictEqual(env.document.getElementById('view-orders').style.display, 'block');
  }

  // #landing-page-orders
  {
    const env = createDomEnvironment('#landing-page-orders');
    env.triggerDOMContentLoaded();
    assert.strictEqual(env.getAppState().activeView, 'landing-page-orders');
    assert.strictEqual(env.getAppState().sourceFilter, 'LANDING');
    assert.strictEqual(env.document.getElementById('view-orders').style.display, 'block');
  }

  // #orders?source=MAIN_WEBSITE
  {
    const env = createDomEnvironment('#orders?source=MAIN_WEBSITE');
    env.triggerDOMContentLoaded();
    assert.strictEqual(env.getAppState().activeView, 'main-website-orders');
    assert.strictEqual(env.getAppState().sourceFilter, 'MAIN_WEBSITE');
  }

  // #orders?source=LANDING
  {
    const env = createDomEnvironment('#orders?source=LANDING');
    env.triggerDOMContentLoaded();
    assert.strictEqual(env.getAppState().activeView, 'landing-page-orders');
    assert.strictEqual(env.getAppState().sourceFilter, 'LANDING');
  }

  // ?source=MAIN_WEBSITE query
  {
    const env = createDomEnvironment('');
    env.location.search = '?source=MAIN_WEBSITE';
    env.triggerDOMContentLoaded();
    assert.strictEqual(env.getAppState().activeView, 'main-website-orders');
    assert.strictEqual(env.getAppState().sourceFilter, 'MAIN_WEBSITE');
  }

  // ?source=LANDING query
  {
    const env = createDomEnvironment('');
    env.location.search = '?source=LANDING';
    env.triggerDOMContentLoaded();
    assert.strictEqual(env.getAppState().activeView, 'landing-page-orders');
    assert.strictEqual(env.getAppState().sourceFilter, 'LANDING');
  }

  console.log('✅ Direct URL hash and search query navigation correctly apply view and sourceFilter');
}

// -----------------------------------------------------------------------------
// Test 23: Generic "All Orders" view clears source filter
// -----------------------------------------------------------------------------
console.log('\n--- Test 23: Generic All Orders resets sourceFilter ---');
{
  const env = createDomEnvironment('#main-website-orders');
  env.triggerDOMContentLoaded();
  assert.strictEqual(env.getAppState().activeView, 'main-website-orders');
  assert.strictEqual(env.getAppState().sourceFilter, 'MAIN_WEBSITE');

  // Switch to normal generic orders
  env.sandbox.window.switchView('orders');
  assert.strictEqual(env.getAppState().activeView, 'orders');
  assert.strictEqual(env.getAppState().sourceFilter, null);
  assert.strictEqual(env.location.hash, '#orders');
  assert.strictEqual(env.document.getElementById('subnav-manage-order').classList.contains('active'), true);

  // From landing-page-orders to orders
  env.sandbox.window.switchView('landing-page-orders');
  assert.strictEqual(env.getAppState().activeView, 'landing-page-orders');
  assert.strictEqual(env.getAppState().sourceFilter, 'LANDING');

  env.sandbox.window.switchView('orders');
  assert.strictEqual(env.getAppState().activeView, 'orders');
  assert.strictEqual(env.getAppState().sourceFilter, null);
  console.log('✅ Switching to generic All Orders view reliably clears sourceFilter to null');
}

// -----------------------------------------------------------------------------
// Test 24: Orders table source filtering logic
// -----------------------------------------------------------------------------
console.log('\n--- Test 24: Orders table source filtering logic ---');
{
  const env = createDomEnvironment('#dashboard');
  env.triggerDOMContentLoaded();

  const state = env.getAppState();
  state.orders = [
    { invoice: 'ORD-001', source: 'MAIN_WEBSITE' },
    { invoice: 'ORD-002', source: 'LANDING' },
    { invoice: 'ORD-003', source: 'LANDING_PAGE' },
    { invoice: 'ORD-004', source: 'landing-orders' },
    { invoice: 'ORD-005', source: 'main-website' }
  ];

  // When sourceFilter is MAIN_WEBSITE
  state.sourceFilter = 'MAIN_WEBSITE';
  state.activeView = 'main-website-orders';
  const tableBody = env.document.getElementById('ordersTableBody');
  const getRenderedHtml = () => tableBody.children.map(tr => tr.innerHTML).join('');

  // When sourceFilter is MAIN_WEBSITE
  state.sourceFilter = 'MAIN_WEBSITE';
  state.activeView = 'main-website-orders';
  env.sandbox.window.renderOrdersTable();
  let rendered = getRenderedHtml();
  assert.ok(rendered.includes('ORD-001'), 'ORD-001 must be shown for MAIN_WEBSITE');
  assert.ok(rendered.includes('ORD-005'), 'ORD-005 must be shown for MAIN_WEBSITE');
  assert.ok(!rendered.includes('ORD-002'), 'ORD-002 must be excluded for MAIN_WEBSITE');
  assert.ok(!rendered.includes('ORD-003'), 'ORD-003 must be excluded for MAIN_WEBSITE');
  assert.ok(!rendered.includes('ORD-004'), 'ORD-004 must be excluded for MAIN_WEBSITE');

  // When sourceFilter is LANDING
  state.sourceFilter = 'LANDING';
  state.activeView = 'landing-page-orders';
  env.sandbox.window.renderOrdersTable();
  rendered = getRenderedHtml();
  assert.ok(rendered.includes('ORD-002'), 'ORD-002 must be shown for LANDING');
  assert.ok(rendered.includes('ORD-003'), 'ORD-003 must be shown for LANDING');
  assert.ok(rendered.includes('ORD-004'), 'ORD-004 must be shown for LANDING');
  assert.ok(!rendered.includes('ORD-001'), 'ORD-001 must be excluded for LANDING');
  assert.ok(!rendered.includes('ORD-005'), 'ORD-005 must be excluded for LANDING');

  // When sourceFilter is null (All Orders)
  state.sourceFilter = null;
  state.activeView = 'orders';
  env.sandbox.window.renderOrdersTable();
  rendered = getRenderedHtml();
  assert.ok(rendered.includes('ORD-001'), 'ORD-001 must be shown for All Orders');
  assert.ok(rendered.includes('ORD-002'), 'ORD-002 must be shown for All Orders');
  assert.ok(rendered.includes('ORD-003'), 'ORD-003 must be shown for All Orders');
  assert.ok(rendered.includes('ORD-004'), 'ORD-004 must be shown for All Orders');
  assert.ok(rendered.includes('ORD-005'), 'ORD-005 must be shown for All Orders');

  console.log('✅ Table rendering displays exactly the orders corresponding to active sourceFilter');
}

console.log('\n======================================================');
console.log('🎉 ALL 24 AUTOMATED TESTS PASSED SUCCESSFULLY!');
console.log('======================================================\n');
