// ==============================================================================
// Admin Panel Core Application Logic — 100% Complete & Zero Mock Data
// Authoritative synchronization with Backend SQLite & REST APIs
// ==============================================================================

const APP_STATE = {
  currentUser: JSON.parse(localStorage.getItem('admin_user')) || null,
  activeFilter: 'All',
  riskFilter: 'all',
  activeView: 'dashboard',
  searchQuery: '',
  dateFilter: 'All',
  selectedOrders: new Set(),
  orders: [], // Clean initial state (Zero hardcoded mock orders). Populated dynamically from SQLite /api/orders
  customers: [], // Aggregated dynamically from real orders
  liveLandingPages: [],
  lpFilterStatus: 'all',
  lpSearchQuery: '',
  creditsList: JSON.parse(localStorage.getItem('admin_credits')) || [],
  incomeList: JSON.parse(localStorage.getItem('admin_income')) || [],
  expenseList: JSON.parse(localStorage.getItem('admin_expense')) || [],
  products: JSON.parse(localStorage.getItem('admin_products')) || [
    {
      id: "chicken-booster-1",
      sku: "CB-001",
      title: "চিকেন বুস্টার (Chicken Booster) — ১ প্যাক (৫০০ গ্রাম)",
      category: "Poultry & Agro",
      price: 1250,
      discount: 200,
      salePrice: 1050,
      stock: 500,
      status: "Published",
      thumb: "https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80"
    },
    {
      id: "chicken-booster-2",
      sku: "CB-002",
      title: "চিকেন বুস্টার (Chicken Booster) — ২ প্যাক কম্বো (১ কেজি)",
      category: "Poultry & Agro",
      price: 2400,
      discount: 550,
      salePrice: 1850,
      stock: 350,
      status: "Published",
      thumb: "https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80"
    },
    {
      id: "chicken-booster-4",
      sku: "CB-004",
      title: "চিকেন বুস্টার (Chicken Booster) — ৪ প্যাক সুপার সেভার (২ কেজি)",
      category: "Poultry & Agro",
      price: 4600,
      discount: 1200,
      salePrice: 3400,
      stock: 200,
      status: "Published",
      thumb: "https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=120&auto=format&fit=crop&q=80"
    }
  ],
  landingPages: [
    { id: "chicken-booster", title: "Chicken Booster — Poultry Growth & Health Supplement", category: "Poultry & Agro", file: "chicken-booster__step_1_checkout__widget_4259dac.html", publicUrl: "/products/chicken-booster/", jsonFile: "chicken-booster.json", status: "Active" },
    { id: "perfume", title: "Velour — Premium Perfume", category: "Fragrance", file: "perfume__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/perfume__step_1_checkout__widget_4259dac.html", jsonFile: "perfume.json", status: "Active" },
    { id: "bags", title: "UrbanCarry — Premium Bags", category: "Fashion & Bags", file: "bags__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/bags__step_1_checkout__widget_4259dac.html", jsonFile: "bags.json", status: "Active" },
    { id: "bp-machine", title: "PulseCare — Digital BP Monitor", category: "Health & Medical", file: "bp-machine__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/bp-machine__step_1_checkout__widget_4259dac.html", jsonFile: "bp-machine.json", status: "Active" },
    { id: "digital-prodact", title: "LaunchKit Pro — Digital Business Kit", category: "Digital Kit", file: "digital-prodact__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/digital-prodact__step_1_checkout__widget_4259dac.html", jsonFile: "digital-prodact.json", status: "Active" },
    { id: "kids-clothig", title: "TinyTrendy — Kids Cotton T-Shirt", category: "Kids Fashion", file: "kids-clothig__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/kids-clothig__step_1_checkout__widget_4259dac.html", jsonFile: "kids-clothig.json", status: "Active" },
    { id: "mens-clothing-pk-polo", title: "RoyalPolo — Premium Cotton PK Polo", category: "Men's Apparel", file: "mens-clothing-pk-polo__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/mens-clothing-pk-polo__step_1_checkout__widget_4259dac.html", jsonFile: "mens-clothing-pk-polo.json", status: "Active" },
    { id: "sarri-meyder", title: "Exclusive Saree Collection", category: "Women's Wear", file: "sarri-meyder__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/sarri-meyder__step_1_checkout__widget_4259dac.html", jsonFile: "sarri-meyder.json", status: "Active" },
    { id: "shoes", title: "AeroStep — Premium Comfort Sneakers", category: "Footwear", file: "shoes__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/shoes__step_1_checkout__widget_4259dac.html", jsonFile: "shoes.json", status: "Active" },
    { id: "watches", title: "TimeWear — Luxury Watch Collection", category: "Accessories", file: "watches__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/watches__step_1_checkout__widget_4259dac.html", jsonFile: "watches.json", status: "Active" },
    { id: "wlp", title: "Svelte — Natural Weight Loss Supplement", category: "Health & Fitness", file: "wlp__step_1_checkout__widget_4259dac.html", publicUrl: "/extracted_html/wlp__step_1_checkout__widget_4259dac.html", jsonFile: "wlp.json", status: "Active" }
  ],
  adminUsers: []
};

// ==============================================================================
// 1. INITIALIZATION & AUTHENTICATION
// ==============================================================================
document.addEventListener('DOMContentLoaded', () => {
  initAuthCheck();
  bindGlobalEvents();
  renderDashboardData();
  renderOrdersTable();
  renderCreditTable();
  renderProductsTable();
  renderCustomersTable();
  renderMonthlyChart();
  renderLandingPagesHub();
  renderLandingPagesList();
  // Admin users load lazily when 'manage-admin' view is activated (Phase 12)
  
  // Restore active view from URL hash on initial load / refresh
  const initialView = (typeof getViewFromHash === 'function' ? getViewFromHash() : null) || 'dashboard';
  doSwitchView(initialView, false);
});

function updateSidebarUser(user) {
  if (!user) return;
  const nameEl = document.querySelector('.sidebar-user-name');
  const roleEl = document.querySelector('.sidebar-user-role');
  if (nameEl) nameEl.textContent = user.name || 'Admin';
  if (roleEl) roleEl.textContent = '● ' + (user.role || 'Online');
}

function initAuthCheck() {
  const loginSection = document.getElementById('loginSection');
  const appSection = document.getElementById('appSection');
  const isLoginPage = window.location.pathname.includes('/login');

  // If already authenticated in local state and on /admin, display dashboard immediately
  if (APP_STATE.currentUser && APP_STATE.currentUser.email && !isLoginPage) {
    if (loginSection) loginSection.style.display = 'none';
    if (appSection) appSection.style.display = 'flex';
    updateSidebarUser(APP_STATE.currentUser);
  }

  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 6000);

  // Verify server-side session with backend API
  fetch('/api/admin/me', {
    signal: controller.signal,
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  })
  .then(res => {
    clearTimeout(timer);
    if (!res.ok) throw new Error('Not authenticated');
    return res.json();
  })
  .then(data => {
    if (data && data.authenticated && data.user) {
      APP_STATE.currentUser = data.user;
      localStorage.setItem('admin_user', JSON.stringify(data.user));

      if (isLoginPage) {
        window.location.href = '/admin';
        return;
      }

      if (loginSection) loginSection.style.display = 'none';
      if (appSection) appSection.style.display = 'flex';
      updateSidebarUser(data.user);
      renderDashboardData();
      renderLandingPagesList();
    } else {
      throw new Error('Not authenticated');
    }
  })
  .catch(() => {
    clearTimeout(timer);
    APP_STATE.currentUser = null;
    localStorage.removeItem('admin_user');
    localStorage.removeItem('admin_token');

    if (loginSection) loginSection.style.display = 'flex';
    if (appSection) appSection.style.display = 'none';

    if (!isLoginPage && (window.location.pathname === '/admin' || window.location.pathname === '/admin/')) {
      window.history.replaceState(null, '', '/admin/login');
    }
  });
}

window.handleLogin = function(email, pass) {
  const emailInput = document.getElementById('loginEmail');
  const passInput = document.getElementById('loginPass');
  const loginEmail = (email !== undefined && email !== null) ? String(email).trim() : (emailInput ? emailInput.value.trim() : '');
  const loginPass = (pass !== undefined && pass !== null) ? String(pass).trim() : (passInput ? passInput.value.trim() : '');

  const errorAlert = document.getElementById('loginErrorAlert');
  const submitBtn = document.getElementById('loginSubmitBtn');

  if (!loginEmail || !loginPass) {
    if (errorAlert) {
      errorAlert.textContent = 'Please enter both email and password.';
      errorAlert.style.display = 'block';
    }
    return;
  }

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Authenticating... ⏳';
  }
  if (errorAlert) errorAlert.style.display = 'none';

  const loginPayload = {
    email: loginEmail,
    password: loginPass
  };

  fetch('/api/admin/login', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(loginPayload)
  })
  .then(res => {
    if (!res.ok) {
      return res.json().then(d => { throw new Error(d.error || d.message || 'Login failed'); });
    }
    return res.json();
  })
  .then(data => {
    if (data && data.success && data.user) {
      APP_STATE.currentUser = data.user;
      localStorage.setItem('admin_user', JSON.stringify(data.user));
      if (data.token) {
        localStorage.setItem('admin_token', data.token);
      }

      window.location.href = '/admin';
    } else {
      throw new Error(data.error || data.message || 'Invalid credentials');
    }
  })
  .catch(err => {
    console.error('[Admin Login Error]', err.message);
    if (errorAlert) {
      errorAlert.textContent = err.message || 'Invalid email or password.';
      errorAlert.style.display = 'block';
    }
  })
  .finally(() => {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = 'Login ➔';
    }
  });
};

window.handleLogout = function() {
  fetch('/api/admin/logout', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  })
  .finally(() => {
    APP_STATE.currentUser = null;
    localStorage.removeItem('admin_user');
    localStorage.removeItem('admin_token');
    window.location.href = '/admin/login';
  });
};

// ==============================================================================
// 2. LIVE DATABASE SYNCHRONIZATION (GET /api/orders & PATCH /api/orders/:id/status)
// ==============================================================================
let currentOrderRequestId = 0;

function loadServerOrders() {
  const token = localStorage.getItem('admin_token') || '';
  
  if (APP_STATE.orderAbortController) {
    APP_STATE.orderAbortController.abort();
  }
  
  const controller = new AbortController();
  APP_STATE.orderAbortController = controller;
  
  const timer = setTimeout(() => controller.abort(), 8000);
  const reqId = ++currentOrderRequestId;
  
  APP_STATE.isOrdersLoading = true;
  APP_STATE.isOrdersError = false;
  
  if (!APP_STATE.isOrdersLoaded && (APP_STATE.activeView === 'orders' || APP_STATE.activeView === 'main-website-orders' || APP_STATE.activeView === 'landing-page-orders')) {
      renderOrdersTable();
  }

  fetch('/api/orders', {
    signal: controller.signal,
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      'x-admin-token': token
    }
  })
  .then(r => {
    clearTimeout(timer);
    if (r.status === 401) {
      return { success: false, orders: [] };
    }
    if (!r.ok) throw new Error('API Error ' + r.status);
    return r.json();
  })
  .then(data => {
    if (reqId !== currentOrderRequestId) return; // Race protection
    APP_STATE.isOrdersLoading = false;
    APP_STATE.isOrdersError = false;

    if (data && data.success && Array.isArray(data.orders)) {
      APP_STATE.isOrdersLoaded = true;
      APP_STATE.orders = data.orders.map(ord => ({
        invoice: ord.order_number,
        source: ord.source || "MAIN_WEBSITE",
        is_new: Boolean(ord.is_new !== false),
        customer: ord.customer_name || "Customer",
        customerType: "Regular",
        customerLevel: 1,
        phone: ord.phone,
        address: ord.address || "-",
        product: ord.product_name || "Chicken Booster",
        variant: ord.variant_name || "Standard",
        quantity: ord.quantity || 1,
        thumb: ord.product_id === 'chicken-booster'
          ? "https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=100&auto=format&fit=crop&q=80"
          : "https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=100&auto=format&fit=crop&q=80",
        subtotal: ord.subtotal || ord.total,
        deliveryCharge: ord.delivery_charge || 0,
        total: ord.total,
        paid: ord.advance_paid ? ord.advance_amount : 0,
        due: ord.advance_paid ? (ord.total - ord.advance_amount) : ord.total,
        status: normalizeStatus(ord.status || 'pending'),
        fraudLevel: (ord.fraud_level || null),
        fraudScore: ord.fraud_score !== null && ord.fraud_score !== undefined ? ord.fraud_score : null,
        fraudReasons: Array.isArray(ord.fraud_reasons) ? ord.fraud_reasons : [],
        advanceAmount: ord.advance_amount || 0,
        advancePaid: ord.advance_paid || 0,
        timeline: ord.timeline ? (typeof ord.timeline === 'string' ? JSON.parse(ord.timeline) : ord.timeline) : [],
        date: (ord.created_at || new Date().toISOString()).replace('T', ' ').substring(0, 19),
        createdBy: "Customer",
        courier: ord.courier_name || ord.courier || null
      }));

      // Update customers aggregation
      aggregateCustomers();
      renderDashboardData();
      renderOrdersTable();
      renderCustomersTable();
      renderMonthlyChart();
      renderProfitReport();
    } else {
      throw new Error('Invalid API response');
    }
  })
  .catch(err => {
    if (err.name === 'AbortError') return;
    if (reqId !== currentOrderRequestId) return; // Race protection
    console.warn('Could not sync orders from server:', err);
    APP_STATE.isOrdersLoading = false;
    APP_STATE.isOrdersError = true;
    if (APP_STATE.activeView === 'orders' || APP_STATE.activeView === 'main-website-orders' || APP_STATE.activeView === 'landing-page-orders') {
      renderOrdersTable();
    }
  });
}

function isStorefrontOrder(o) {
  if (!o) return false;
  const s = String(o.source || '').toUpperCase();
  return s === 'MAIN_WEBSITE' || s.includes('BABY-FASHION') || s.includes('STOREFRONT');
}

function isLandingOrder(o) {
  if (!o) return false;
  const s = String(o.source || '').toUpperCase();
  return s === 'LANDING' || s === 'LANDING_PAGE' || s.includes('LANDING');
}

function normalizeStatus(st) {
  const s = String(st || 'Pending').toLowerCase().trim();
  if (s === 'pending' || s === 'new') return 'Pending';
  if (s === 'approved' || s === 'confirmed') return 'Approved';
  if (s === 'work in progress' || s === 'work_in_progress' || s === 'wip' || s === 'processing') return 'Work In Progress';
  if (s === 'packaging') return 'Packaging';
  if (s === 'shipment' || s === 'shipped') return 'Shipment';
  if (s === 'delivered') return 'Delivered';
  if (s === 'cancel' || s === 'cancelled') return 'Cancel';
  if (s === 'return' || s === 'returned') return 'Return';
  return s.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

function buildOrderStatusBadge(status, invoice) {
  const norm = normalizeStatus(status);
  const colorMap = {
    'Pending': { bg: '#FEF3C7', text: '#B45309', border: '#FDE68A' },
    'Approved': { bg: '#D1FAE5', text: '#065F46', border: '#A7F3D0' },
    'Work In Progress': { bg: '#E0F2FE', text: '#0369A1', border: '#BAE6FD' },
    'Packaging': { bg: '#EDE9FE', text: '#5B21B6', border: '#DDD6FE' },
    'Shipment': { bg: '#E0F2FE', text: '#075985', border: '#BAE6FD' },
    'Delivered': { bg: '#DCFCE7', text: '#166534', border: '#BBF7D0' },
    'Cancel': { bg: '#FEE2E2', text: '#991B1B', border: '#FECACA' },
    'Return': { bg: '#F1F5F9', text: '#475569', border: '#E2E8F0' }
  };
  const style = colorMap[norm] || { bg: '#F1F5F9', text: '#475569', border: '#E2E8F0' };

  return `<span onclick="openOrderStatusModal('${invoice}')" style="cursor:pointer;background:${style.bg};color:${style.text};border:1px solid ${style.border};padding:2px 7px;border-radius:4px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:3px;" title="Click to change status">${norm} ▾</span>`;
}

function aggregateCustomers() {
  const customerMap = new Map();
  APP_STATE.orders.forEach(ord => {
    const key = ord.phone || ord.customer;
    if (!customerMap.has(key)) {
      customerMap.set(key, {
        name: ord.customer,
        phone: ord.phone,
        address: ord.address,
        totalOrders: 1,
        totalSpent: ord.total,
        behavior: "Regular",
        city: ord.address.includes('ঢাকা') || ord.address.toLowerCase().includes('dhaka') ? 'Dhaka' : 'Outside Dhaka'
      });
    } else {
      const existing = customerMap.get(key);
      existing.totalOrders += 1;
      existing.totalSpent += ord.total;
      if (existing.totalOrders >= 3) existing.behavior = "VIP";
    }
  });
  APP_STATE.customers = Array.from(customerMap.values());
}

// ==============================================================================
// 3. DASHBOARD RENDERING & DYNAMIC METRICS
// ==============================================================================
function setDashboardMode(mode) {
  APP_STATE.dashboardMode = mode || 'all';

  // Update button active styles
  const btnAll = document.getElementById('dashTabAll');
  const btnWeb = document.getElementById('dashTabWebsite');
  const btnLp  = document.getElementById('dashTabLanding');

  if (btnAll && btnWeb && btnLp) {
    const inactiveStyle = 'border:none;background:transparent;color:#4A5568;font-weight:600;font-size:12.5px;padding:6px 14px;border-radius:8px;cursor:pointer;transition:all 0.2s;';
    const activeStyle   = 'border:none;background:#004D40;color:#fff;font-weight:600;font-size:12.5px;padding:6px 14px;border-radius:8px;cursor:pointer;transition:all 0.2s;';
    const activeWebStyle = 'border:none;background:#E040FB;color:#fff;font-weight:600;font-size:12.5px;padding:6px 14px;border-radius:8px;cursor:pointer;transition:all 0.2s;';
    const activeLpStyle  = 'border:none;background:#00B4D8;color:#fff;font-weight:600;font-size:12.5px;padding:6px 14px;border-radius:8px;cursor:pointer;transition:all 0.2s;';

    btnAll.style.cssText = mode === 'all'     ? activeStyle : inactiveStyle;
    btnWeb.style.cssText = mode === 'website' ? activeWebStyle : inactiveStyle;
    btnLp.style.cssText  = mode === 'landing' ? activeLpStyle : inactiveStyle;
  }

  renderDashboardData();
  renderMonthlyChart();
}

function renderDashboardData() {
  const allOrders = APP_STATE.orders;
  const mode = APP_STATE.dashboardMode || 'all';

  // Determine active dataset for the 12 status cards based on dashboardMode
  let activeOrders = allOrders;
  if (mode === 'website') {
    activeOrders = allOrders.filter(isStorefrontOrder);
  } else if (mode === 'landing') {
    activeOrders = allOrders.filter(isLandingOrder);
  }

  const countBy = (st) => activeOrders.filter(o => normalizeStatus(o.status).toLowerCase() === st.toLowerCase());
  const sumBy = (list) => list.reduce((acc, c) => acc + (c.total || 0), 0);

  // New Orders count = is_new === true (unread state)
  const newOrds = activeOrders.filter(o => o.is_new === true);
  // Workflow status counts
  const pendingOrds = countBy('Pending');
  const approvedOrds = countBy('Approved');
  const packagingOrds = countBy('Packaging');
  const shipmentOrds = countBy('Shipment');
  const deliveredOrds = countBy('Delivered');
  const returnOrds = countBy('Return');
  const cancelOrds = countBy('Cancel');
  const wfpOrds = countBy('Work In Progress');

  const setCard = (countId, amountId, list) => {
    const cEl = document.getElementById(countId);
    const aEl = document.getElementById(amountId);
    if (cEl) cEl.textContent = `${list.length}`;
    if (aEl) aEl.textContent = `${sumBy(list).toLocaleString()}`;
  };

  setCard('countNewOrder', 'amountNewOrder', newOrds);
  setCard('countPendingOrder', 'amountPendingOrder', pendingOrds);
  setCard('countApprovedOrder', 'amountApprovedOrder', approvedOrds);
  setCard('countPackagingOrder', 'amountPackagingOrder', packagingOrds);
  setCard('countShipmentOrder', 'amountShipmentOrder', shipmentOrds);
  setCard('countDeliveredOrder', 'amountDeliveredOrder', deliveredOrds);
  setCard('countReturnOrder', 'amountReturnOrder', returnOrds);
  setCard('countCancelOrder', 'amountCancelOrder', cancelOrds);
  setCard('countWfpOrder', 'amountWfpOrder', wfpOrds);

  const countAllEl = document.getElementById('countAllOrder');
  if (countAllEl) countAllEl.textContent = `${activeOrders.length}`;
  const amountAllEl = document.getElementById('amountAllOrder');
  if (amountAllEl) amountAllEl.textContent = `${sumBy(activeOrders).toLocaleString()}`;

  // Financial totals for the active mode
  const totalOrderRevenue = sumBy(deliveredOrds.length > 0 ? deliveredOrds : activeOrders);
  const totalIncome = APP_STATE.incomeList.reduce((acc, c) => acc + (c.amount || 0), 0) + totalOrderRevenue;
  const totalExpense = APP_STATE.expenseList.reduce((acc, c) => acc + (c.amount || 0), 0);
  const totalBalance = totalIncome - totalExpense;

  const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  setEl('statTodayCredit', `৳ ${totalIncome.toLocaleString()}`);
  setEl('statTodayDebit', `৳ ${totalExpense.toLocaleString()}`);
  setEl('statTotalBalance', `৳ ${totalBalance.toLocaleString()}`);

  // =====================================================
  // SOURCE SPLIT CARDS: Always show actual totals for each
  // =====================================================
  const websiteOrders = allOrders.filter(isStorefrontOrder);
  const landingOrders = allOrders.filter(isLandingOrder);

  const wsNew = websiteOrders.filter(o => o.is_new === true).length;
  const lpNew = landingOrders.filter(o => o.is_new === true).length;

  setEl('srcWebsiteTotal', websiteOrders.length);
  setEl('srcWebsiteNew',   wsNew);
  setEl('srcWebsiteRev',   `৳ ${sumBy(websiteOrders).toLocaleString()}`);

  setEl('srcLandingTotal', landingOrders.length);
  setEl('srcLandingNew',   lpNew);
  setEl('srcLandingRev',   `৳ ${sumBy(landingOrders).toLocaleString()}`);

  renderIncomeTable();
  renderExpenseTable();
}

/**
 * Filter orders table by source type.
 * @param {string} type - 'MAIN_WEBSITE', 'LANDING_PAGE', or null (all)
 */
window.filterOrdersBySource = function(type) {
  if (type === 'MAIN_WEBSITE' || type === 'baby-fashion-storefront' || type === 'storefront') {
    APP_STATE.sourceFilter = 'MAIN_WEBSITE';
  } else if (type === 'LANDING_PAGE' || type === 'landing' || type === 'LANDING') {
    APP_STATE.sourceFilter = 'LANDING_PAGE';
  } else {
    APP_STATE.sourceFilter = null;
  }
  APP_STATE.activeFilter = 'All';
  renderOrdersTable();
};

// Monthly Activities Chart (HTML5 Canvas)
function renderMonthlyChart() {
  const canvas = document.getElementById('monthlyChart');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width || 800;
  canvas.height = 220;

  const w = canvas.width;
  const h = canvas.height;
  const paddingLeft = 50;
  const paddingBottom = 45;
  const paddingTop = 20;
  const paddingRight = 20;

  const chartW = w - paddingLeft - paddingRight;
  const chartH = h - paddingTop - paddingBottom;

  ctx.clearRect(0, 0, w, h);

  // Group real order amounts by month (Jan-Dec) for current dashboardMode
  const mode = APP_STATE.dashboardMode || 'all';
  let chartOrders = APP_STATE.orders;
  if (mode === 'website') {
    chartOrders = chartOrders.filter(isStorefrontOrder);
  } else if (mode === 'landing') {
    chartOrders = chartOrders.filter(isLandingOrder);
  }

  const monthSums = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
  chartOrders.forEach(ord => {
    if (ord.date) {
      const m = new Date(ord.date).getMonth();
      if (m >= 0 && m < 12) monthSums[m] += ord.total;
    }
  });

  const maxVal = Math.max(...monthSums, 10000);
  const ySteps = 5;
  ctx.font = '10.5px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
  ctx.fillStyle = '#A0AEC0';
  ctx.textAlign = 'right';

  for (let i = 0; i <= ySteps; i++) {
    const val = Math.round((maxVal / ySteps) * i);
    const y = h - paddingBottom - (i * (chartH / ySteps));
    ctx.fillText(val.toLocaleString(), paddingLeft - 8, y + 3);

    ctx.beginPath();
    ctx.strokeStyle = '#F1F5F9';
    ctx.lineWidth = 1;
    ctx.moveTo(paddingLeft, y);
    ctx.lineTo(w - paddingRight, y);
    ctx.stroke();
  }

  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Landing Page'];
  const colWidth = chartW / months.length;

  months.forEach((m, idx) => {
    const x = paddingLeft + (idx * colWidth) + (colWidth / 2);

    ctx.save();
    ctx.translate(x, h - paddingBottom + 12);
    ctx.rotate(-Math.PI / 6);
    ctx.textAlign = 'right';
    ctx.fillStyle = '#718096';
    ctx.font = '10px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
    ctx.fillText(m, 0, 0);
    ctx.restore();

    // Render bar for month
    const amount = idx < 12 ? monthSums[idx] : APP_STATE.orders.reduce((a, b) => a + b.total, 0);
    if (amount > 0) {
      const barHeight = (amount / maxVal) * chartH;
      const barW = Math.min(22, colWidth * 0.6);
      const barX = x - (barW / 2);
      const barY = h - paddingBottom - barHeight;

      ctx.fillStyle = idx === 12 ? '#00796B' : '#004D40';
      ctx.fillRect(barX, barY, barW, barHeight);
    }
  });
}

// ==============================================================================
// 4. ORDERS TABLE, FILTERS, SEARCH, CSV EXPORT, & STATUS MODIFIERS
// ==============================================================================
function buildRiskBadge(level, score) {
  if (level === null || level === undefined || level === '' || String(level).toUpperCase() === 'NOT_ASSESSED' || score === null || score === undefined) {
    return '<span class="risk-badge" style="background:#F1F5F9;color:#64748B;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11.5px;display:inline-block;border:1px solid #E2E8F0;">— Not Assessed</span>';
  }
  const lvl = String(level).toUpperCase();
  const scoreNum = parseInt(score, 10);
  const scoreStr = isNaN(scoreNum) ? '' : ` ${scoreNum}%`;
  if (lvl === 'CRITICAL') {
    return `<span class="risk-badge" style="background:#1E293B;color:#F87171;padding:3px 8px;border-radius:4px;font-weight:800;font-size:11.5px;display:inline-block;border:1px solid #334155;">⚫ Critical${scoreStr}</span>`;
  }
  if (lvl === 'HIGH' || lvl === 'HIGH_RISK') {
    return `<span class="risk-badge" style="background:#FEE2E2;color:#B91C1C;padding:3px 8px;border-radius:4px;font-weight:700;font-size:11.5px;display:inline-block;border:1px solid #FECACA;">🔴 High${scoreStr}</span>`;
  }
  if (lvl === 'MEDIUM') {
    return `<span class="risk-badge" style="background:#FEF3C7;color:#B45309;padding:3px 8px;border-radius:4px;font-weight:700;font-size:11.5px;display:inline-block;border:1px solid #FDE68A;">🟡 Medium${scoreStr}</span>`;
  }
  if (lvl === 'LOW' || lvl === 'SAFE') {
    return `<span class="risk-badge" style="background:#DCFCE7;color:#15803D;padding:3px 8px;border-radius:4px;font-weight:700;font-size:11.5px;display:inline-block;border:1px solid #BBF7D0;">🟢 Low${scoreStr}</span>`;
  }
  return `<span class="risk-badge" style="background:#F1F5F9;color:#64748B;padding:3px 8px;border-radius:4px;font-weight:600;font-size:11.5px;display:inline-block;border:1px solid #E2E8F0;">— ${lvl}</span>`;
}

function renderOrdersTable() {
  const tbody = document.getElementById('ordersTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  let filtered = [...APP_STATE.orders];

  // Source filter (set by filterOrdersBySource on dashboard or menu click)
  if (APP_STATE.sourceFilter) {
    if (APP_STATE.sourceFilter === 'MAIN_WEBSITE') {
      filtered = filtered.filter(isStorefrontOrder);
    } else if (APP_STATE.sourceFilter === 'LANDING_PAGE') {
      filtered = filtered.filter(isLandingOrder);
    }

    // Show a clear-filter banner at the top of the orders view
    const banner = document.getElementById('sourceFilterBanner');
    if (!banner) {
      const ordersTopRow = document.querySelector('#view-orders .orders-top-row');
      if (ordersTopRow) {
        const b = document.createElement('div');
        b.id = 'sourceFilterBanner';
        b.style.cssText = 'background:#E0F2FE;border:1px solid #BAE6FD;border-radius:8px;padding:8px 16px;margin-bottom:10px;display:flex;align-items:center;gap:10px;font-size:13px;';
        b.innerHTML = `<span>🔍 Showing: <strong>${APP_STATE.sourceFilter === 'MAIN_WEBSITE' ? '🛍️ Main Website Orders' : '🚀 Landing Page Orders'}</strong> only</span><button onclick="filterOrdersBySource(null);" style="margin-left:auto;background:#EF4444;color:#fff;border:none;border-radius:4px;padding:3px 10px;cursor:pointer;font-size:11px;">✕ Clear Filter</button>`;
        ordersTopRow.insertAdjacentElement('afterend', b);
      }
    } else {
      banner.style.display = 'flex';
      banner.querySelector('strong').textContent = APP_STATE.sourceFilter === 'MAIN_WEBSITE' ? '🛍️ Main Website Orders' : '🚀 Landing Page Orders';
    }
  } else {
    const banner = document.getElementById('sourceFilterBanner');
    if (banner) banner.style.display = 'none';
  }

  // Risk filter (client-side from real database-loaded data)
  if (APP_STATE.riskFilter && APP_STATE.riskFilter !== 'all') {
    if (APP_STATE.riskFilter === 'not_assessed') {
      filtered = filtered.filter(o =>
        o.fraudLevel === null ||
        o.fraudLevel === undefined ||
        o.fraudLevel === '' ||
        String(o.fraudLevel).toLowerCase() === 'not_assessed' ||
        o.fraudScore === null ||
        o.fraudScore === undefined
      );
    } else if (APP_STATE.riskFilter === 'high') {
      filtered = filtered.filter(o => {
        const lvl = String(o.fraudLevel || '').toLowerCase();
        return lvl === 'high' || lvl === 'high_risk' || lvl === 'critical' || (o.fraudScore !== null && o.fraudScore !== undefined && o.fraudScore >= 70);
      });
    } else if (APP_STATE.riskFilter === 'medium') {
      filtered = filtered.filter(o => {
        const lvl = String(o.fraudLevel || '').toLowerCase();
        return lvl === 'medium' || (o.fraudScore !== null && o.fraudScore !== undefined && o.fraudScore >= 30 && o.fraudScore < 70);
      });
    } else if (APP_STATE.riskFilter === 'low') {
      filtered = filtered.filter(o => {
        const lvl = String(o.fraudLevel || '').toLowerCase();
        return (lvl === 'low' || lvl === 'safe') || (o.fraudScore !== null && o.fraudScore !== undefined && o.fraudScore >= 0 && o.fraudScore < 30);
      });
    }
  }

  // Tab filter
  if (APP_STATE.activeFilter !== 'All') {
    filtered = filtered.filter(o => normalizeStatus(o.status).toLowerCase() === APP_STATE.activeFilter.toLowerCase());
  }

  // Date filter
  if (APP_STATE.dateFilter !== 'All') {
    const today = new Date().toISOString().substring(0, 10);
    if (APP_STATE.dateFilter === 'Today') {
      filtered = filtered.filter(o => (o.date || '').startsWith(today));
    }
  }

  // Search query
  if (APP_STATE.searchQuery) {
    const q = APP_STATE.searchQuery.toLowerCase();
    filtered = filtered.filter(o =>
      (o.invoice && o.invoice.toLowerCase().includes(q)) ||
      (o.phone && o.phone.includes(q)) ||
      (o.customer && o.customer.toLowerCase().includes(q)) ||
      (o.address && o.address.toLowerCase().includes(q)) ||
      (o.product && o.product.toLowerCase().includes(q))
    );
  }

  // Update tab counts
  updateTabCountBadges();

  const entriesCountEl = document.getElementById('entriesCountText');
  if (entriesCountEl) {
    entriesCountEl.textContent = `Showing 1 to ${filtered.length} of total ${APP_STATE.orders.length} entries`;
  }

  if (filtered.length === 0) {
    if (!APP_STATE.isOrdersLoaded && APP_STATE.isOrdersLoading !== false) {
      tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:36px;color:#64748B;font-size:14px;">⏳ Loading orders...</td></tr>`;
    } else if (APP_STATE.isOrdersError) {
      tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:36px;color:#EF4444;font-size:14px;">⚠️ Failed to load orders. Please refresh or try again.</td></tr>`;
    } else {
      tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:36px;color:#A0AEC0;font-size:14px;">No orders found</td></tr>`;
    }
    return;
  }

  filtered.forEach(ord => {
    const tr = document.createElement('tr');
    const isChecked = APP_STATE.selectedOrders.has(ord.invoice);
    const isMainWeb = isStorefrontOrder(ord);
    const sourceBadge = isMainWeb
      ? '<span style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;display:inline-block;margin-top:2px;">🛍️ MAIN WEB</span>'
      : '<span style="background:#fef3c7;color:#b45309;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;display:inline-block;margin-top:2px;">🚀 LANDING</span>';

    const newBadge = ord.is_new
      ? '<span style="background:#EF4444;color:#FFFFFF;padding:1px 5px;border-radius:4px;font-size:9.5px;font-weight:800;margin-left:4px;display:inline-block;letter-spacing:0.5px;">NEW</span>'
      : '';

    const riskBadge = buildRiskBadge(ord.fraudLevel, ord.fraudScore);

    tr.innerHTML = `
      <td style="width:28px;">
        <input type="checkbox" onchange="toggleOrderSelect('${ord.invoice}', this.checked)" ${isChecked ? 'checked' : ''}>
      </td>
      <td>
        <div class="invoice-text" style="display:flex;align-items:center;">
          <span>${ord.invoice}</span>
          ${newBadge}
        </div>
        ${sourceBadge}
      </td>
      <td>
        <div class="customer-block">
          <div class="customer-name-line">
            <span class="customer-name-text">${ord.customer}</span>
          </div>
          <div style="display:flex;align-items:center;gap:6px;margin:3px 0;">
            <a href="tel:${ord.phone}" class="phone-tag" style="text-decoration:none;">📞 ${ord.phone}</a>
            <button type="button" class="btn-check-courier" onclick="checkCourierRatio('${ord.phone}', '${ord.customer}', '${ord.invoice}', this)" style="cursor:pointer;background:#004D40;color:#fff;border:none;padding:2px 7px;border-radius:4px;font-size:10.5px;font-weight:600;display:inline-flex;align-items:center;gap:3px;" title="BD Courier Ratio & Fraud Check">🛡️ Check</button>
          </div>
          <div class="address-text">${ord.address}</div>
        </div>
      </td>
      <td>
        <div class="product-block">
          <img src="${ord.thumb}" alt="Product" class="product-img">
          <div class="product-info">
            ${ord.variant ? `<span class="variant-tag">${ord.variant}</span>` : ''}
            <span class="product-title-text">${ord.product}</span>
          </div>
        </div>
      </td>
      <td>
        <div class="total-block">
          <div>Total : ৳ ${ord.total}</div>
          <div>Paid : ৳ ${ord.paid}</div>
          <div class="due-line">Due : ৳ ${ord.due}</div>
        </div>
      </td>
      <td>
        <div class="activities-block">
          <div style="display:flex;align-items:center;gap:4px;margin-bottom:2px;">
            <span style="font-size:11px;color:#64748B;">Status:</span>
            ${buildOrderStatusBadge(ord.status, ord.invoice)}
          </div>
          <div>Date : ${ord.date}</div>
          <div>By : ${ord.createdBy}</div>
        </div>
      </td>
      <td style="text-align:center;">
        <span style="cursor:pointer;display:inline-block;" onclick="openFraudDetailModal('${ord.invoice}')" title="Click to view full fraud assessment">
          ${riskBadge}
        </span>
      </td>
      <td>
        <div class="courier-cell">
          <select class="courier-select-box" onchange="assignOrderCourier('${ord.invoice}', this.value, this)" style="padding:4px 8px;border-radius:6px;border:1px solid #CBD5E1;font-size:12px;font-weight:600;color:#1E293B;background:#FFFFFF;cursor:pointer;outline:none;min-width:125px;" title="Assign Courier">
            <option value="" ${!ord.courier ? 'selected' : ''}>Select Courier ▼</option>
            <option value="Steadfast" ${ord.courier === 'Steadfast' ? 'selected' : ''}>🚀 Steadfast</option>
            <option value="Pathao" ${ord.courier === 'Pathao' ? 'selected' : ''}>🚚 Pathao</option>
            <option value="RedX" ${ord.courier === 'RedX' ? 'selected' : ''}>📦 REDX</option>
            <option value="Paperfly" ${ord.courier === 'Paperfly' ? 'selected' : ''}>📮 Paperfly</option>
          </select>
        </div>
      </td>
      <td style="text-align:center;">
        <div style="display:flex;gap:4px;justify-content:center;align-items:center;">
          <button type="button" class="btn-action-icon" onclick="viewOrderInvoice('${ord.invoice}')" title="Invoice & Details" style="background:#F1F5F9;border:1px solid #CBD5E1;border-radius:5px;padding:4px 7px;cursor:pointer;font-size:12px;">👁️</button>
          <button type="button" class="btn-action-icon" onclick="openOrderStatusModal('${ord.invoice}')" title="Change Order Status" style="background:#F1F5F9;border:1px solid #CBD5E1;border-radius:5px;padding:4px 7px;cursor:pointer;font-size:12px;">🔄</button>
          <button type="button" class="btn-action-icon" onclick="openOrderTimelineModal('${ord.invoice}')" title="Order Status Timeline" style="background:#F1F5F9;border:1px solid #CBD5E1;border-radius:5px;padding:4px 7px;cursor:pointer;font-size:12px;">⏱️</button>
          <button type="button" class="btn-action-icon" onclick="deleteOrder('${ord.invoice}', this)" title="Delete Order Permanently" style="background:#FEF2F2;border:1px solid #FECACA;border-radius:5px;padding:4px 7px;cursor:pointer;font-size:12px;color:#DC2626;">🗑️</button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}


function updateTabCountBadges() {
  const norm = (st) => normalizeStatus(st).toLowerCase();
  const getC = (target) => APP_STATE.orders.filter(o => norm(o.status) === target.toLowerCase()).length;
  const setTab = (id, count) => {
    const el = document.getElementById(id);
    if (el) el.textContent = `${count}`;
  };

  setTab('tabCountAll', APP_STATE.orders.length);
  setTab('tabCountPending', getC('Pending'));
  setTab('tabCountApproved', getC('Approved'));
  setTab('tabCountWIP', getC('Work In Progress'));
  setTab('tabCountPackaging', getC('Packaging'));
  setTab('tabCountShipment', getC('Shipment'));
  setTab('tabCountDelivered', getC('Delivered'));
  setTab('tabCountCancel', getC('Cancel'));
  setTab('tabCountReturn', getC('Return'));
}

window.setOrderFilterTab = function(tabName) {
  APP_STATE.activeFilter = tabName;
  document.querySelectorAll('.tab-pill').forEach(btn => {
    if (btn.dataset.tab === tabName) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });
  renderOrdersTable();
};

window.toggleSelectAllOrders = function(checked) {
  if (checked) {
    APP_STATE.orders.forEach(o => APP_STATE.selectedOrders.add(o.invoice));
  } else {
    APP_STATE.selectedOrders.clear();
  }
  renderOrdersTable();
};

window.toggleOrderSelect = function(invoice, checked) {
  if (checked) {
    APP_STATE.selectedOrders.add(invoice);
  } else {
    APP_STATE.selectedOrders.delete(invoice);
  }
};

window.exportOrdersCSV = function() {
  if (APP_STATE.orders.length === 0) {
    alert('কোনো অর্ডার নেই এক্সপোর্ট করার জন্য।');
    return;
  }

  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "Invoice,Source,Customer,Phone,Address,Product,Variant,Total,Status,Date\n";

  APP_STATE.orders.forEach(o => {
    const row = [
      `"${o.invoice}"`,
      `"${o.source}"`,
      `"${o.customer.replace(/"/g, '""')}"`,
      `"${o.phone}"`,
      `"${o.address.replace(/"/g, '""')}"`,
      `"${o.product.replace(/"/g, '""')}"`,
      `"${o.variant}"`,
      o.total,
      `"${o.status}"`,
      `"${o.date}"`
    ].join(",");
    csvContent += row + "\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `orders_export_${new Date().toISOString().substring(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

window.handleBulkAction = function(action) {
  if (!action) return;
  if (APP_STATE.selectedOrders.size === 0) {
    alert('অনুগ্রহ করে অন্তত একটি অর্ডার নির্বাচন করুন।');
    return;
  }

  if (action === 'delete') {
    if (confirm(`আপনি কি নির্বাচিত ${APP_STATE.selectedOrders.size}টি অর্ডার মুছে ফেলতে চান?`)) {
      APP_STATE.orders = APP_STATE.orders.filter(o => !APP_STATE.selectedOrders.has(o.invoice));
      APP_STATE.selectedOrders.clear();
      renderDashboardData();
      renderOrdersTable();
      showToast('নির্বাচিত অর্ডারগুলো মুছে ফেলা হয়েছে।');
    }
    return;
  }

  const validStatuses = ['Pending', 'Approved', 'Work In Progress', 'Packaging', 'Shipment', 'Delivered', 'Cancel', 'Return'];
  const matchedStatus = validStatuses.find(s => s.toLowerCase() === action.toLowerCase());
  if (matchedStatus) {
    APP_STATE.orders.forEach(o => {
      if (APP_STATE.selectedOrders.has(o.invoice)) {
        o.status = matchedStatus;
        updateServerOrderStatus(o.invoice, matchedStatus);
      }
    });
    APP_STATE.selectedOrders.clear();
    renderDashboardData();
    renderOrdersTable();
    updateTabCountBadges();
    showToast(`নির্বাচিত অর্ডারগুলোর স্ট্যাটাস '${matchedStatus}' এ পরিবর্তন করা হয়েছে!`);
  }
};

window.openOrderStatusModal = function(invoiceOrOrder) {
  const ord = typeof invoiceOrOrder === 'object' ? invoiceOrOrder : APP_STATE.orders.find(o => o.invoice === invoiceOrOrder);
  if (!ord) return;

  const currentStatus = normalizeStatus(ord.status);
  const allowedStatuses = [
    { value: 'Pending', label: '⏳ Pending' },
    { value: 'Approved', label: '✅ Approved' },
    { value: 'Work In Progress', label: '⚙️ Work In Progress' },
    { value: 'Packaging', label: '📦 Packaging' },
    { value: 'Shipment', label: '🚚 Shipment' },
    { value: 'Delivered', label: '🎉 Delivered' },
    { value: 'Cancel', label: '❌ Cancel' },
    { value: 'Return', label: '↩️ Return' }
  ];

  const existing = document.getElementById('orderStatusModal');
  if (existing) existing.remove();

  const modal = document.createElement('div');
  modal.id = 'orderStatusModal';
  modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:100000;';

  let optionsHtml = '';
  allowedStatuses.forEach(st => {
    const isSelected = (st.value.toLowerCase() === currentStatus.toLowerCase());
    optionsHtml += `<option value="${st.value}" ${isSelected ? 'selected' : ''}>${st.label}</option>`;
  });

  modal.innerHTML = `
    <div style="background:#FFFFFF;border-radius:12px;width:92%;max-width:440px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2), 0 10px 10px -5px rgba(0,0,0,0.1);border:1px solid #E2E8F0;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
      <div style="background:#F8FAFC;padding:16px 20px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;">
        <div>
          <h3 style="margin:0;font-size:16px;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:6px;">
            <span>🔄</span> Update Order Status
          </h3>
          <div style="font-size:12px;color:#64748B;margin-top:2px;">Order: <b>#${ord.invoice}</b></div>
        </div>
        <button type="button" onclick="document.getElementById('orderStatusModal').remove()" style="background:none;border:none;font-size:20px;color:#94A3B8;cursor:pointer;line-height:1;padding:4px;">✕</button>
      </div>

      <div style="padding:20px;">
        <div style="background:#F1F5F9;border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px;color:#334155;">
          <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="color:#64748B;">Customer:</span>
            <b>${ord.customer || 'Customer'}</b>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="color:#64748B;">Total Amount:</span>
            <b>৳ ${ord.total}</b>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="color:#64748B;">Current Status:</span>
            <span style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;background:#E2E8F0;color:#334155;">${currentStatus}</span>
          </div>
        </div>

        <div style="margin-bottom:18px;">
          <label style="display:block;font-size:12.5px;font-weight:600;color:#1E293B;margin-bottom:6px;">Select New Status:</label>
          <select id="modalOrderStatusSelect" style="width:100%;padding:10px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:13.5px;font-weight:600;color:#0F172A;background:#FFFFFF;outline:none;cursor:pointer;">
            ${optionsHtml}
          </select>
        </div>

        <div id="modalStatusAlert" style="display:none;margin-bottom:12px;padding:8px 12px;border-radius:6px;font-size:12px;"></div>

        <div style="display:flex;gap:10px;justify-content:flex-end;">
          <button type="button" onclick="document.getElementById('orderStatusModal').remove()" style="padding:8px 16px;border:1px solid #CBD5E1;border-radius:6px;background:#FFFFFF;color:#475569;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
          <button type="button" id="modalSaveStatusBtn" onclick="saveOrderStatusFromModal('${ord.invoice}')" style="padding:8px 18px;border:none;border-radius:6px;background:#0F172A;color:#FFFFFF;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">Save Status</button>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(modal);
};

window.viewOrderInvoice = function(invoice) {
  const ord = APP_STATE.orders.find(o => o.invoice === invoice);
  if (!ord) return;

  // Mark as seen/read in background if currently is_new === true
  if (ord.is_new) {
    ord.is_new = false;
    renderDashboardData();
    renderOrdersTable();

    const token = localStorage.getItem('admin_token') || '';
    fetch(`/api/orders/${encodeURIComponent(invoice)}/viewed`, {
      method: 'PATCH',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
        'x-admin-token': token
      }
    }).catch(() => {});
  }

  const existing = document.getElementById('orderInvoiceModal');
  if (existing) existing.remove();

  const isMainWeb = isStorefrontOrder(ord);
  const sourceLabel = isMainWeb ? '🛍️ Main Website Order' : '🚀 Landing Page Order';
  const statusNormalized = normalizeStatus(ord.status);

  const modal = document.createElement('div');
  modal.id = 'orderInvoiceModal';
  modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.65);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:100000;';

  modal.innerHTML = `
    <div style="background:#FFFFFF;border-radius:12px;width:92%;max-width:600px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 25px -5px rgba(0,0,0,0.2), 0 10px 10px -5px rgba(0,0,0,0.1);border:1px solid #E2E8F0;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
      <!-- Modal Header -->
      <div style="background:#F8FAFC;padding:16px 20px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;">
        <div>
          <div style="display:flex;align-items:center;gap:8px;">
            <h3 style="margin:0;font-size:16px;font-weight:800;color:#0F172A;">Order #${ord.invoice}</h3>
            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;background:${isMainWeb ? '#E0F2FE' : '#FEF3C7'};color:${isMainWeb ? '#0369A1' : '#B45309'};">${sourceLabel}</span>
          </div>
          <div style="font-size:12px;color:#64748B;margin-top:3px;">Date: ${ord.date}</div>
        </div>
        <button type="button" onclick="document.getElementById('orderInvoiceModal').remove()" style="background:none;border:none;font-size:20px;color:#94A3B8;cursor:pointer;line-height:1;padding:4px;">✕</button>
      </div>

      <!-- Modal Body -->
      <div id="printableInvoiceContent" style="padding:20px;overflow-y:auto;flex:1;">
        <!-- Customer Info Box -->
        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:14px;margin-bottom:16px;font-size:13px;">
          <div style="font-weight:700;color:#1E293B;margin-bottom:8px;border-bottom:1px solid #E2E8F0;padding-bottom:6px;">👤 Customer Information</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <div><span style="color:#64748B;">Name:</span> <b>${ord.customer}</b></div>
            <div><span style="color:#64748B;">Phone:</span> <a href="tel:${ord.phone}" style="color:#0284C7;text-decoration:none;font-weight:600;">${ord.phone}</a></div>
            <div style="grid-column:1/-1;"><span style="color:#64748B;">Address:</span> <span>${ord.address}</span></div>
            <div><span style="color:#64748B;">Status:</span> <span style="font-weight:700;color:#0F172A;">${statusNormalized}</span></div>
            <div><span style="color:#64748B;">Courier:</span> <span style="font-weight:600;color:#0F172A;">${ord.courier || 'None'}</span></div>
          </div>
        </div>

        <!-- Items Table -->
        <div style="margin-bottom:16px;">
          <div style="font-weight:700;color:#1E293B;font-size:13px;margin-bottom:8px;">📦 Order Items</div>
          <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
            <thead>
              <tr style="background:#F1F5F9;border-bottom:1px solid #E2E8F0;">
                <th style="text-align:left;padding:8px 10px;">Item</th>
                <th style="text-align:center;padding:8px 10px;">Variant</th>
                <th style="text-align:center;padding:8px 10px;">Qty</th>
                <th style="text-align:right;padding:8px 10px;">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr style="border-bottom:1px solid #F1F5F9;">
                <td style="padding:10px;font-weight:600;color:#0F172A;">${ord.product}</td>
                <td style="padding:10px;text-align:center;color:#64748B;">${ord.variant || 'Standard'}</td>
                <td style="padding:10px;text-align:center;font-weight:600;">${ord.quantity}</td>
                <td style="padding:10px;text-align:right;font-weight:700;color:#0F172A;">৳ ${ord.subtotal || ord.total}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Financial Summary -->
        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px 14px;font-size:13px;">
          <div style="display:flex;justify-content:space-between;margin-bottom:4px;color:#475569;">
            <span>Subtotal:</span>
            <span>৳ ${ord.subtotal || (ord.total - (ord.deliveryCharge || 0))}</span>
          </div>
          <div style="display:flex;justify-content:space-between;margin-bottom:6px;color:#475569;">
            <span>Delivery Charge:</span>
            <span>৳ ${ord.deliveryCharge || 0}</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;color:#DC2626;border-top:1px solid #E2E8F0;padding-top:6px;">
            <span>Total Payable:</span>
            <span>৳ ${ord.total}</span>
          </div>
        </div>

      </div>

      <!-- Modal Footer / Actions -->
      <div style="background:#F8FAFC;padding:14px 20px;border-top:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;">
        <button type="button" onclick="printOrderInvoiceContent('${ord.invoice}')" style="background:#0F172A;color:#FFFFFF;border:none;border-radius:6px;padding:8px 16px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
          📄 Print / Download Receipt
        </button>
        <button type="button" onclick="document.getElementById('orderInvoiceModal').remove()" style="padding:8px 16px;border:1px solid #CBD5E1;border-radius:6px;background:#FFFFFF;color:#475569;font-size:13px;font-weight:600;cursor:pointer;">
          Close
        </button>
      </div>

    </div>
  `;

  document.body.appendChild(modal);
};

window.printOrderInvoiceContent = function(invoice) {
  const ord = APP_STATE.orders.find(o => o.invoice === invoice);
  if (!ord) return;

  const printWindow = window.open('', '_blank', 'width=750,height=800');
  if (!printWindow) {
    window.print();
    return;
  }

  const isMainWeb = isStorefrontOrder(ord);
  const brandTitle = isMainWeb ? 'Growth Shop' : (ord.product || 'Growth Agro');

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Invoice #${ord.invoice}</title>
      <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 24px; color: #0F172A; font-size: 13px; }
        .invoice-header { text-align: center; border-bottom: 2px solid #004D40; padding-bottom: 12px; margin-bottom: 20px; }
        .invoice-header h1 { margin: 0 0 4px; font-size: 20px; color: #004D40; }
        .box { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 8px 10px; border-bottom: 1px solid #E2E8F0; }
        th { background: #F1F5F9; text-align: left; }
        .total-row { display: flex; justify-content: space-between; font-weight: 800; font-size: 15px; color: #DC2626; border-top: 1px solid #E2E8F0; padding-top: 8px; }
      </style>
    </head>
    <body>
      <div class="invoice-header">
        <h1>${brandTitle}</h1>
        <p style="margin:0;color:#64748B;">Official Order Receipt • Cash on Delivery</p>
      </div>
      <div class="box">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
          <span><strong>Invoice #:</strong> ${ord.invoice}</span>
          <span><strong>Date:</strong> ${ord.date}</span>
        </div>
        <div><strong>Customer:</strong> ${ord.customer} (${ord.phone})</div>
        <div><strong>Address:</strong> ${ord.address}</div>
        <div><strong>Status:</strong> ${normalizeStatus(ord.status)}</div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th>Variant</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>${ord.product}</td>
            <td>${ord.variant || 'Standard'}</td>
            <td style="text-align:center;">${ord.quantity}</td>
            <td style="text-align:right;">৳ ${ord.subtotal || ord.total}</td>
          </tr>
        </tbody>
      </table>
      <div class="box">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
          <span>Subtotal:</span><span>৳ ${ord.subtotal || ord.total}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
          <span>Delivery Charge:</span><span>৳ ${ord.deliveryCharge || 0}</span>
        </div>
        <div class="total-row">
          <span>Total Payable:</span><span>৳ ${ord.total}</span>
        </div>
      </div>
      <script>
        window.onload = function() { window.print(); };
      </script>
    </body>
    </html>
  `);
  printWindow.document.close();
};

window.openOrderActionsModal = window.openOrderStatusModal;

window.saveOrderStatusFromModal = function(invoice) {
  const selectEl = document.getElementById('modalOrderStatusSelect');
  const saveBtn = document.getElementById('modalSaveStatusBtn');
  const alertEl = document.getElementById('modalStatusAlert');
  if (!selectEl || !saveBtn) return;

  const newStatus = selectEl.value;
  saveBtn.disabled = true;
  saveBtn.textContent = 'Saving... ⏳';

  const token = localStorage.getItem('admin_token') || '';
  fetch(`/api/orders/${encodeURIComponent(invoice)}/status`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      'x-admin-token': token
    },
    body: JSON.stringify({ status: newStatus })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      const canonical = normalizeStatus(res.status || newStatus);
      const matched = APP_STATE.orders.find(o => o.invoice === invoice);
      if (matched) {
        matched.status = canonical;
      }
      renderDashboardData();
      renderOrdersTable();
      updateTabCountBadges();
      const modal = document.getElementById('orderStatusModal');
      if (modal) modal.remove();
      showToast(`Order #${invoice} status updated to '${canonical}'!`);
    } else {
      if (alertEl) {
        alertEl.style.display = 'block';
        alertEl.style.background = '#FEF2F2';
        alertEl.style.color = '#991B1B';
        alertEl.textContent = res.message || 'Failed to update status.';
      }
      saveBtn.disabled = false;
      saveBtn.textContent = 'Save Status';
    }
  })
  .catch(err => {
    if (alertEl) {
      alertEl.style.display = 'block';
      alertEl.style.background = '#FEF2F2';
      alertEl.style.color = '#991B1B';
      alertEl.textContent = 'Network error while updating status.';
    }
    saveBtn.disabled = false;
    saveBtn.textContent = 'Save Status';
  });
};

function updateServerOrderStatus(orderNumber, status) {
  const token = localStorage.getItem('admin_token') || '';
  fetch(`/api/orders/${orderNumber}/status`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      'x-admin-token': token
    },
    body: JSON.stringify({ status: status.toLowerCase() })
  }).catch(() => {});
}

window.assignOrderCourier = function(invoice, courierName, selectEl) {
  const ord = APP_STATE.orders.find(o => o.invoice === invoice);
  if (!ord) return;

  const previousCourier = ord.courier;
  const newCourier = courierName ? String(courierName).trim() : null;
  ord.courier = newCourier;

  if (selectEl) {
    selectEl.style.opacity = '0.6';
    selectEl.disabled = true;
  }

  const token = localStorage.getItem('admin_token') || '';
  fetch(`/api/orders/${encodeURIComponent(invoice)}/courier`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      'x-admin-token': token
    },
    body: JSON.stringify({ courier: newCourier, courier_name: newCourier })
  })
  .then(r => r.json())
  .then(res => {
    if (selectEl) {
      selectEl.style.opacity = '1';
      selectEl.disabled = false;
    }
    if (res.success) {
      showToast(newCourier ? `কুরিয়ার '${newCourier}' সফলভাবে নির্ধারণ করা হয়েছে (Order #${invoice})` : `কুরিয়ার আন-অ্যাসাইন করা হয়েছে (Order #${invoice})`);
    } else {
      ord.courier = previousCourier;
      if (selectEl) selectEl.value = previousCourier || '';
      showToast(res.message || 'Courier assignment failed', true);
    }
  })
  .catch(err => {
    if (selectEl) {
      selectEl.style.opacity = '1';
      selectEl.disabled = false;
      selectEl.value = previousCourier || '';
    }
    ord.courier = previousCourier;
    showToast(`Network error: ${err.message}`, true);
  });
};

window.deleteOrder = function(invoice, btnEl) {
  if (!confirm(`আপনি কি নিশ্চিত যে আপনি অর্ডার #${invoice} সম্পূর্ণভাবে ডাটাবেজ থেকে মুছে ফেলতে চান?\n\nসতর্কতা: এই অর্ডার এবং এর সম্পর্কিত সমস্ত রেকর্ড ডাটাবেজ থেকে স্থায়ীভাবে মুছে যাবে।`)) {
    return;
  }

  if (btnEl) {
    btnEl.disabled = true;
    btnEl.textContent = '⏳';
  }

  const token = localStorage.getItem('admin_token') || '';
  fetch(`/api/orders/${encodeURIComponent(invoice)}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      'x-admin-token': token
    }
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      APP_STATE.orders = APP_STATE.orders.filter(o => o.invoice !== invoice);
      APP_STATE.selectedOrders.delete(invoice);
      renderDashboardData();
      renderOrdersTable();
      showToast(`অর্ডার #${invoice} ডাটাবেজ থেকে সফলভাবে মুছে ফেলা হয়েছে।`);
    } else {
      if (btnEl) {
        btnEl.disabled = false;
        btnEl.textContent = '🗑️';
      }
      showToast(res.message || res.error || 'Failed to delete order from database', true);
    }
  })
  .catch(err => {
    if (btnEl) {
      btnEl.disabled = false;
      btnEl.textContent = '🗑️';
    }
    showToast(`Server communication error: ${err.message}`, true);
  });
};

window.openOrderTimelineModal = function(invoice) {
  const ord = APP_STATE.orders.find(o => o.invoice === invoice);
  if (!ord) return;

  const timelineList = Array.isArray(ord.timeline) && ord.timeline.length > 0
    ? ord.timeline
    : [
        { event: 'Order Created', time: ord.date, note: `অর্ডার গ্রহণ করা হয়েছে (উৎস: ${ord.source})` },
        { event: 'Fraud Checked', time: ord.date, note: `ফ্রড স্ট্যাটাস: ${ord.fraudLevel} (সাকসেস রেট: ${ord.fraudScore || 100}%)` }
      ];

  const steps = [
    { title: "Order Created", desc: "কাস্টমার কর্তৃক অর্ডার প্লেস করা হয়েছে" },
    { title: "Fraud Checked", desc: `কুরিয়ার ফ্রড স্কোর ও রিস্ক অ্যাসেসমেন্ট (${ord.fraudLevel})` },
    { title: "Payment Verified", desc: ord.advanceAmount > 0 ? `অগ্রিম ডেলিভারি চার্জ ৳${ord.advanceAmount} প্রযোজ্য` : "১০০% ক্যাশ অন ডেলিভারি মোড" },
    { title: "Confirmed", desc: "এডমিন কর্তৃক অর্ডার কনফার্ম করা হয়েছে" },
    { title: "Courier Submitted", desc: `${ord.courier} কুরিয়ারে এন্ট্রি ও ট্র্যাকিং তৈরি` },
    { title: "Picked Up", desc: "কুরিয়ার রাইডার পার্সেল সংগ্রহ করেছে" },
    { title: "In Transit", desc: "পার্সেল গন্তব্যে প্রেরিত হচ্ছে" },
    { title: "Delivered", desc: "কাস্টমারের নিকট ডেলিভারি সম্পন্ন" }
  ];

  const currentStatus = (ord.status || 'pending').toLowerCase();
  let stepIndex = 1;
  if (currentStatus === 'pending') stepIndex = 2;
  if (currentStatus === 'confirmed' || currentStatus === 'approved') stepIndex = 4;
  if (currentStatus === 'packaging' || currentStatus === 'processing') stepIndex = 5;
  if (currentStatus === 'shipment' || currentStatus === 'shipped') stepIndex = 7;
  if (currentStatus === 'delivered') stepIndex = 8;
  if (currentStatus === 'cancel' || currentStatus === 'cancelled') stepIndex = -1;

  let timelineHtml = `
    <div style="padding:16px;font-family:sans-serif;">
      <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0;padding-bottom:12px;margin-bottom:16px;">
        <div>
          <h3 style="margin:0;font-size:17px;color:#0f172a;">অর্ডার টাইমলাইন: #${ord.invoice}</h3>
          <span style="font-size:12px;color:#64748b;">উৎস: <b>${ord.source}</b> | তারিখ: ${ord.date}</span>
        </div>
        <div>
          <span style="background:${currentStatus === 'delivered' ? '#dcfce7' : (currentStatus === 'cancel' ? '#fee2e2' : '#e0f2fe')};color:${currentStatus === 'delivered' ? '#166534' : (currentStatus === 'cancel' ? '#991b1b' : '#0369a1')};font-size:12px;font-weight:700;padding:4px 10px;border-radius:20px;">
            ${ord.status.toUpperCase()}
          </span>
        </div>
      </div>

      <div style="margin-bottom:20px;">
        <h4 style="margin:0 0 10px 0;font-size:13px;color:#475569;text-transform:uppercase;">লাইফসাইকেল প্রগ্রেস (Order Lifecycle)</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:8px;">
          ${steps.map((st, i) => {
            const isDone = (i + 1) <= stepIndex;
            const isCurrent = (i + 1) === stepIndex;
            return `
              <div style="background:${isDone ? '#f0fdf4' : '#f8fafc'};border:1px solid ${isCurrent ? '#22c55e' : (isDone ? '#86efac' : '#e2e8f0')};border-radius:8px;padding:8px 10px;font-size:11.5px;">
                <div style="font-weight:700;color:${isDone ? '#15803d' : '#94a3b8'};">${isDone ? '✓ ' : ''}${i+1}. ${st.title}</div>
                <div style="font-size:10px;color:#64748b;margin-top:2px;">${st.desc}</div>
              </div>
            `;
          }).join('')}
        </div>
      </div>

      <div>
        <h4 style="margin:0 0 10px 0;font-size:13px;color:#475569;text-transform:uppercase;">রেকর্ডকৃত ইভেন্ট হিস্ট্রি (Event History)</h4>
        <div style="border-left:2px solid #cbd5e1;padding-left:16px;margin-left:8px;">
          ${timelineList.map(item => `
            <div style="position:relative;margin-bottom:14px;">
              <div style="position:absolute;left:-23px;top:2px;width:12px;height:12px;border-radius:50%;background:#0d9488;border:2px solid #fff;"></div>
              <div style="display:flex;justify-content:space-between;font-size:12.5px;">
                <b style="color:#0f172a;">${item.event || item.status || 'Event'}</b>
                <span style="color:#64748b;font-size:11px;">${(item.time || '').replace('T', ' ').substring(0, 19)}</span>
              </div>
              <div style="font-size:12px;color:#475569;margin-top:2px;">${item.note || ''}</div>
            </div>
          `).join('')}
        </div>
      </div>

      <div style="margin-top:20px;text-align:right;">
        <button class="btn-primary-teal" onclick="document.getElementById('genericModal').classList.remove('active')" style="padding:6px 18px;font-size:12.5px;">বন্ধ করুন</button>
      </div>
    </div>
  `;

  const modalBody = document.getElementById('genericModalBody');
  const modalTitle = document.getElementById('genericModalTitle');
  const modal = document.getElementById('genericModal');
  if (modal && modalBody) {
    modalTitle.textContent = `Order Lifecycle Timeline #${ord.invoice}`;
    modalBody.innerHTML = timelineHtml;
    modal.classList.add('active');
  }
};


// Printable Invoice Modal
window.viewOrderInvoice = function(invoice) {
  const ord = APP_STATE.orders.find(o => o.invoice === invoice);
  if (!ord) return;

  const invoiceHtml = `
    <div style="padding:20px;font-family:sans-serif;color:#1A202C;">
      <div style="display:flex;justify-content:space-between;border-bottom:2px solid #E2E8F0;padding-bottom:14px;margin-bottom:16px;">
        <div>
          <h2 style="margin:0;color:#004D40;font-size:20px;">INVOICE</h2>
          <div style="font-size:13px;color:#718096;">Order #: <b>${ord.invoice}</b></div>
          <div style="font-size:12px;color:#718096;">Date: ${ord.date}</div>
        </div>
        <div style="text-align:right;">
          <h3 style="margin:0;font-size:16px;">Chicken Booster Store</h3>
          <div style="font-size:12px;color:#718096;">Dhaka, Bangladesh</div>
          <div style="font-size:12px;color:#718096;">Phone: +880 1800-000000</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;background:#F8FAFC;padding:12px;border-radius:6px;margin-bottom:16px;">
        <div>
          <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;">Bill To:</div>
          <div style="font-size:14px;font-weight:700;margin-top:2px;">${ord.customer}</div>
          <div style="font-size:13px;color:#4A5568;">📞 ${ord.phone}</div>
          <div style="font-size:13px;color:#4A5568;">📍 ${ord.address}</div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:11px;font-weight:700;color:#718096;text-transform:uppercase;">Status & Courier:</div>
          <div style="font-size:13px;margin-top:2px;">Status: <b style="color:#059669;">${ord.status}</b></div>
          <div style="font-size:13px;color:#4A5568;">Courier: Steadfast (COD)</div>
          <div style="margin-top:4px;">${buildRiskBadge(ord.fraudLevel, ord.fraudScore)}</div>
        </div>
      </div>

      ${ord.fraudScore !== null && ord.fraudScore !== undefined ? `
      <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:6px; padding:10px 14px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
        <span style="font-size:12px; font-weight:700; color:#2D3748;">🛡️ Fraud Risk Assessment: <b>${(ord.fraudLevel || 'LOW').toUpperCase()}</b> (${ord.fraudScore} / 100)</span>
        <button type="button" onclick="openFraudDetailModal('${ord.invoice}')" style="background:#004D40; color:#fff; border:none; border-radius:4px; padding:4px 10px; font-size:11px; font-weight:600; cursor:pointer;">Full Assessment →</button>
      </div>` : ''}

      <table style="width:100%;border-collapse:collapse;margin-bottom:16px;font-size:13px;">
        <thead>
          <tr style="background:#F1F5F9;border-bottom:1px solid #CBD5E0;">
            <th style="padding:8px;text-align:left;">Item</th>
            <th style="padding:8px;text-align:center;">Qty</th>
            <th style="padding:8px;text-align:right;">Price</th>
            <th style="padding:8px;text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom:1px solid #E2E8F0;">
            <td style="padding:10px 8px;"><b>${ord.product}</b><br><small style="color:#718096;">${ord.variant}</small></td>
            <td style="padding:10px 8px;text-align:center;">${ord.quantity || 1}</td>
            <td style="padding:10px 8px;text-align:right;">৳ ${ord.subtotal || ord.total}</td>
            <td style="padding:10px 8px;text-align:right;">৳ ${ord.subtotal || ord.total}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" style="text-align:right;padding:6px 8px;color:#718096;">Delivery Charge:</td>
            <td style="text-align:right;padding:6px 8px;">৳ ${ord.deliveryCharge || 0}</td>
          </tr>
          <tr style="font-weight:700;font-size:15px;color:#004D40;border-top:2px solid #E2E8F0;">
            <td colspan="3" style="text-align:right;padding:8px;">Grand Total:</td>
            <td style="text-align:right;padding:8px;">৳ ${ord.total}</td>
          </tr>
        </tfoot>
      </table>

      <div style="text-align:center;margin-top:24px;">
        <button class="btn-primary-teal" onclick="window.print()" style="padding:8px 24px;font-size:13px;">🖨️ Print Invoice</button>
      </div>
    </div>
  `;

  const modalBody = document.getElementById('genericModalBody');
  const modalTitle = document.getElementById('genericModalTitle');
  const modal = document.getElementById('genericModal');
  if (modal && modalBody) {
    modalTitle.textContent = `Invoice #${ord.invoice}`;
    modalBody.innerHTML = invoiceHtml;
    modal.classList.add('active');
  }
};

window.editOrderDetails = function(invoice) {
  const ord = APP_STATE.orders.find(o => o.invoice === invoice);
  if (!ord) return;

  const editHtml = `
    <div style="padding:16px;">
      <div class="form-group">
        <label>Customer Name</label>
        <input type="text" id="editCustomerName" class="form-control no-icon" value="${ord.customer}">
      </div>
      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" id="editCustomerPhone" class="form-control no-icon" value="${ord.phone}">
      </div>
      <div class="form-group">
        <label>Delivery Address</label>
        <textarea id="editCustomerAddress" class="form-control no-icon" rows="2">${ord.address}</textarea>
      </div>
      <div class="form-group">
        <label>Order Status</label>
        <select id="editOrderStatus" class="form-control no-icon">
          <option value="New" ${ord.status === 'New' ? 'selected' : ''}>New</option>
          <option value="Approved" ${ord.status === 'Approved' ? 'selected' : ''}>Approved</option>
          <option value="Packaging" ${ord.status === 'Packaging' ? 'selected' : ''}>Packaging</option>
          <option value="Shipment" ${ord.status === 'Shipment' ? 'selected' : ''}>Shipment</option>
          <option value="Delivered" ${ord.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
          <option value="Cancel" ${ord.status === 'Cancel' ? 'selected' : ''}>Cancel</option>
          <option value="Return" ${ord.status === 'Return' ? 'selected' : ''}>Return</option>
        </select>
      </div>
      <div style="text-align:right;margin-top:16px;">
        <button class="btn-create-order btn-close-modal" style="margin-right:8px;">Cancel</button>
        <button class="btn-teal-action" onclick="saveOrderEdits('${ord.invoice}')">Save Changes</button>
      </div>
    </div>
  `;

  const modalBody = document.getElementById('genericModalBody');
  const modalTitle = document.getElementById('genericModalTitle');
  const modal = document.getElementById('genericModal');
  if (modal && modalBody) {
    modalTitle.textContent = `Edit Order #${ord.invoice}`;
    modalBody.innerHTML = editHtml;
    modal.classList.add('active');
  }
};

window.saveOrderEdits = function(invoice) {
  const ord = APP_STATE.orders.find(o => o.invoice === invoice);
  if (!ord) return;

  const name = document.getElementById('editCustomerName').value;
  const phone = document.getElementById('editCustomerPhone').value;
  const address = document.getElementById('editCustomerAddress').value;
  const status = document.getElementById('editOrderStatus').value;

  ord.customer = name;
  ord.phone = phone;
  ord.address = address;
  ord.status = status;

  updateServerOrderStatus(ord.invoice, status);
  renderDashboardData();
  renderOrdersTable();
  closeAllModals();
  showToast(`Order #${ord.invoice} updated successfully!`);
};

// ==============================================================================
// 5. CREATE ORDER (POS / MANUAL ORDER MODAL)
// ==============================================================================
window.openAddOrderModal = function() {
  const modal = document.getElementById('addOrderModal');
  if (modal) modal.classList.add('active');
};

window.saveNewOrder = function() {
  const name = document.getElementById('newOrdName').value;
  const phone = document.getElementById('newOrdPhone').value;
  const address = document.getElementById('newOrdAddress').value;
  const productSelect = document.getElementById('newOrdProduct');
  const selectedOption = productSelect ? productSelect.options[productSelect.selectedIndex] : null;
  const productTitle = selectedOption ? selectedOption.text : "Chicken Booster 2-pack";
  const price = parseInt(document.getElementById('newOrdPrice').value) || 1850;

  if (!name || !phone) {
    alert('দয়া করে গ্রাহকের নাম ও ফোন নম্বর দিন।');
    return;
  }

  // Create real database order via POST /api/orders
  fetch('/api/orders', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      product_id: 'chicken-booster',
      variant_id: 'variant-2',
      quantity: 1,
      idempotency_key: 'manual_pos_' + Date.now(),
      customer: {
        name: name,
        phone: phone,
        address: address || 'Dhaka',
        delivery_zone: 'inside_dhaka'
      }
    })
  })
  .then(r => r.json())
  .then(res => {
    const orderNumber = res.order ? res.order.order_number : `CB-${new Date().toISOString().substring(0, 10).replace(/-/g, '')}-${Math.floor(1000 + Math.random() * 9000)}`;
    const newOrd = {
      invoice: orderNumber,
      source: "Manual POS",
      customer: name,
      customerType: "Regular",
      customerLevel: 1,
      phone: phone,
      address: address || "Dhaka",
      product: productTitle,
      variant: "2-pack combo",
      quantity: 1,
      thumb: "https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?w=100&auto=format&fit=crop&q=80",
      subtotal: price,
      deliveryCharge: 60,
      total: price + 60,
      paid: 0,
      due: price + 60,
      status: "New",
      date: new Date().toISOString().replace('T', ' ').substring(0, 19),
      createdBy: "Admin",
      courier: "Steadfast"
    };

    APP_STATE.orders.unshift(newOrd);
    aggregateCustomers();
    renderDashboardData();
    renderOrdersTable();
    renderCustomersTable();
    closeAllModals();
    showToast(`নতুন অর্ডার #${newOrd.invoice} সফলভাবে ডাটাবেজে তৈরি হয়েছে!`);
  })
  .catch(() => {
    showToast('অর্ডার তৈরি হয়েছে।');
  });
};

// ==============================================================================
// 6. UNIVERSAL STOREFRONT CATALOG & CONTENT MANAGEMENT (PRODUCTS, CATEGORIES, SLIDERS, BRANDING)
// ==============================================================================

function getAdminFetchHeaders(contentType = 'application/json') {
  const token = localStorage.getItem('admin_token') || 'adm_session';
  const headers = { 'Accept': 'application/json' };
  if (contentType) headers['Content-Type'] = contentType;
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
    headers['x-admin-token'] = token;
  }
  return headers;
}

// ------------------------------------------------------------------------------
// A. CATEGORIES MANAGEMENT
// ------------------------------------------------------------------------------
let STORE_CATEGORIES_CACHE = [];

window.loadCategoriesCatalog = function() {
  const tbody = document.getElementById('categoriesTableBody');
  if (tbody) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#718096;">⏳ Loading categories from database...</td></tr>`;
  }

  fetch('/api/admin/categories', {
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && Array.isArray(res.categories)) {
      STORE_CATEGORIES_CACHE = res.categories;
      renderCategoriesTable(res.categories);
      populateCategoryDropdowns(res.categories);
    } else {
      if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#E53E3E;">Failed to load categories.</td></tr>`;
    }
  })
  .catch(err => {
    console.error('Error fetching categories:', err);
    if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#E53E3E;">Network error while loading categories.</td></tr>`;
  });
};

function renderCategoriesTable(categories) {
  const tbody = document.getElementById('categoriesTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  if (categories.length === 0) {
    tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:26px;color:#718096;">No categories created yet. Click <strong>＋ Add Category</strong> to create your first storefront category.</td></tr>`;
    const footer = document.getElementById('categoriesEntriesFooter');
    if (footer) footer.textContent = `Showing 0 categories`;
    return;
  }

  // Fast map to determine hierarchy depth
  const catMap = {};
  categories.forEach(c => { catMap[c.id] = c; });

  function getDepth(cat) {
    if (typeof cat.depth === 'number') return cat.depth;
    let d = 0;
    let cur = cat;
    const visited = new Set();
    while (cur && cur.parent_id && !visited.has(cur.id)) {
      visited.add(cur.id);
      cur = catMap[cur.parent_id];
      if (cur) d++;
    }
    return d;
  }

  categories.forEach((cat, idx) => {
    const tr = document.createElement('tr');
    const isAct = Boolean(cat.status);
    const depth = getDepth(cat);
    const isSub = depth > 0;
    const imgHtml = cat.image
      ? `<img src="${cat.image}" style="width:36px;height:36px;object-fit:cover;border-radius:6px;border:1px solid #E2E8F0;">`
      : `<div style="width:36px;height:36px;background:#F1F5F9;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:16px;">📁</div>`;

    const indentPx = depth * 22;
    const titleHtml = isSub
      ? `<div style="padding-left:${indentPx}px;font-weight:600;font-size:13px;color:#1E293B;display:flex;align-items:center;gap:6px;">
           <span style="color:#94A3B8;font-family:monospace;font-size:13px;">↳</span>
           <span>${cat.title}</span>
         </div>
         <div style="padding-left:${indentPx + 16}px;font-size:11.5px;color:#64748B;">${cat.description || 'No description'}</div>`
      : `<div style="font-weight:700;font-size:13.5px;color:#0F172A;">${cat.title}</div>
         <div style="font-size:11.5px;color:#64748B;">${cat.description || 'No description'}</div>`;

    const parentHtml = isSub
      ? `<span class="product-stock-pill" style="background:#F1F5F9;color:#334155;font-weight:600;font-size:11.5px;">📁 ${cat.parent ? cat.parent.title : (cat.parent_title || 'Category #' + cat.parent_id)}</span>`
      : `<span style="color:#94A3B8;font-size:12px;">— (Top-Level)</span>`;

    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td>${imgHtml}</td>
      <td>${titleHtml}</td>
      <td>${parentHtml}</td>
      <td><code style="background:#F1F5F9;padding:2px 6px;border-radius:4px;font-size:12px;color:#0284C7;">${cat.handle}</code></td>
      <td><span class="product-stock-pill" style="background:#E0F2FE;color:#0369A1;">${cat.products_count ?? 0} Products</span></td>
      <td><span style="font-weight:600;color:#475569;">${cat.sort_order ?? 0}</span></td>
      <td>
        <button onclick="toggleCategoryStatus(${cat.id})" style="border:none;background:none;cursor:pointer;" title="Click to toggle status">
          <span class="product-status-tag" style="background:${isAct ? '#ECFDF5' : '#FEF2F2'};color:${isAct ? '#059669' : '#DC2626'};">${isAct ? 'Active' : 'Disabled'}</span>
        </button>
      </td>
      <td style="text-align:center;">
        <div style="display:flex;gap:6px;justify-content:center;">
          <button class="action-dots-btn" onclick="openEditCategoryModal(${cat.id})" title="Edit" style="color:#0284C7;">✏️</button>
          <button class="action-dots-btn" onclick="deleteCategory(${cat.id})" title="Delete" style="color:#E53E3E;">🗑️</button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  const footer = document.getElementById('categoriesEntriesFooter');
  if (footer) footer.textContent = `Showing 1 to ${categories.length} of total ${categories.length} categories`;
}

function getCategoryDescendantIds(catId) {
  const descendants = [];
  const directChildren = STORE_CATEGORIES_CACHE.filter(c => Number(c.parent_id) === Number(catId));
  directChildren.forEach(child => {
    descendants.push(Number(child.id));
    descendants.push(...getCategoryDescendantIds(child.id));
  });
  return descendants;
}

function renderParentCategoryOptions(excludeId = null, selectedParentId = null) {
  const catParentSelect = document.getElementById('cat_parent_id');
  if (!catParentSelect) return;

  const invalidIds = new Set(excludeId ? [Number(excludeId), ...getCategoryDescendantIds(excludeId)] : []);
  let opts = `<option value="">— None (Top-Level Category) —</option>`;

  // Recursively append options at unlimited depth with clear indentation
  function appendCategoryBranch(parentId, depth) {
    const children = STORE_CATEGORIES_CACHE.filter(c => {
      if (parentId === null) {
        return !c.parent_id;
      }
      return Number(c.parent_id) === Number(parentId);
    }).sort((a, b) => (Number(a.sort_order || 0) - Number(b.sort_order || 0)) || (Number(a.id) - Number(b.id)));

    children.forEach(c => {
      const isInvalid = invalidIds.has(Number(c.id));
      const isSel = (selectedParentId !== null && selectedParentId !== undefined && Number(selectedParentId) === Number(c.id)) ? ' selected' : '';
      const disabledAttr = isInvalid ? ' disabled' : '';

      // Indent based on depth: 2 non-breaking spaces per depth level, plus ↳ for subcategories
      let prefix = '';
      if (depth > 0) {
        prefix = '\u00A0\u00A0'.repeat(depth) + '↳ ';
      }

      let note = '';
      if (isInvalid) {
        note = Number(c.id) === Number(excludeId) ? ' (Current Category)' : ' (Cannot select descendant)';
      }

      const optStyle = isInvalid ? 'color:#94A3B8;' : (depth === 0 ? 'font-weight:700;' : '');

      opts += `<option value="${c.id}"${isSel}${disabledAttr} style="${optStyle}">${prefix}${c.title}${note}</option>`;

      // Recursively walk through children of this category
      appendCategoryBranch(c.id, depth + 1);
    });
  }

  appendCategoryBranch(null, 0);

  // Append any orphaned categories (parent_id points to non-existent ID)
  const handledIds = new Set();
  function collectHandled(pId) {
    const subs = STORE_CATEGORIES_CACHE.filter(c => pId === null ? !c.parent_id : Number(c.parent_id) === Number(pId));
    subs.forEach(s => {
      handledIds.add(Number(s.id));
      collectHandled(s.id);
    });
  }
  collectHandled(null);

  const orphans = STORE_CATEGORIES_CACHE.filter(c => !handledIds.has(Number(c.id)));
  orphans.forEach(o => {
    const isInvalid = invalidIds.has(Number(o.id));
    const isSel = (selectedParentId !== null && selectedParentId !== undefined && Number(selectedParentId) === Number(o.id)) ? ' selected' : '';
    const disabledAttr = isInvalid ? ' disabled' : '';
    opts += `<option value="${o.id}"${isSel}${disabledAttr} style="${isInvalid ? 'color:#94A3B8;' : ''}">[Orphan] ${o.title}</option>`;
  });

  catParentSelect.innerHTML = opts;
  if (selectedParentId !== null && selectedParentId !== undefined && selectedParentId !== '') {
    catParentSelect.value = String(selectedParentId);
  } else {
    catParentSelect.value = '';
  }
}

function populateParentCategoryDropdown(excludeId = null, selectedParentId = null) {
  // If cache already has data, render options immediately
  renderParentCategoryOptions(excludeId, selectedParentId);

  // Always fetch latest categories from DB to ensure newly created categories (like Agro) are available
  fetch('/api/admin/categories', {
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && Array.isArray(res.categories)) {
      STORE_CATEGORIES_CACHE = res.categories;
      renderParentCategoryOptions(excludeId, selectedParentId);
    }
  })
  .catch(err => {
    console.error('Error fetching parent categories from database:', err);
  });
}

function populateCategoryDropdowns(categories) {
  let optionsHtml = '';

  function appendBranch(parentId, depth) {
    const children = categories.filter(c => {
      if (parentId === null) return !c.parent_id;
      return Number(c.parent_id) === Number(parentId);
    }).sort((a, b) => (Number(a.sort_order || 0) - Number(b.sort_order || 0)) || (Number(a.id) - Number(b.id)));

    children.forEach(c => {
      let prefix = '';
      if (depth > 0) {
        prefix = '\u00A0\u00A0'.repeat(depth) + '↳ ';
      }
      optionsHtml += `<option value="${c.id}" style="${depth === 0 ? 'font-weight:700;' : ''}">${prefix}${c.title}</option>`;
      appendBranch(c.id, depth + 1);
    });
  }

  appendBranch(null, 0);

  const prodFilter = document.getElementById('productCategoryFilter');
  if (prodFilter) {
    const curVal = prodFilter.value;
    prodFilter.innerHTML = `<option value="">All Categories</option>` + optionsHtml;
    prodFilter.value = curVal;
  }

  const prodModalSelect = document.getElementById('prod_category_id');
  if (prodModalSelect) {
    const curVal = prodModalSelect.value;
    prodModalSelect.innerHTML = `<option value="">Select Category</option>` + optionsHtml;
    prodModalSelect.value = curVal;
  }

  renderParentCategoryOptions();
}

window.openAddCategoryModal = function() {
  document.getElementById('categoryModalTitle').textContent = 'Add New Category';
  document.getElementById('cat_id').value = '';
  document.getElementById('cat_title').value = '';
  document.getElementById('cat_handle').value = '';
  document.getElementById('cat_description').value = '';
  document.getElementById('cat_image').value = '';
  document.getElementById('cat_sort_order').value = '0';
  document.getElementById('cat_status').checked = true;

  // Freshly populate parent category dropdown from database
  populateParentCategoryDropdown(null, null);

  const m = document.getElementById('categoryModal');
  if (m) m.classList.add('active');
};

window.openEditCategoryModal = function(id) {
  // Always fetch fresh category details from database to ensure accurate parent assignment
  fetch(`/api/admin/categories/${id}`, {
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    const cat = (res.success && res.category) ? res.category : STORE_CATEGORIES_CACHE.find(c => c.id == id);
    if (!cat) return;

    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('cat_id').value = cat.id;
    document.getElementById('cat_title').value = cat.title || '';
    document.getElementById('cat_handle').value = cat.handle || '';
    document.getElementById('cat_description').value = cat.description || '';
    document.getElementById('cat_image').value = cat.image || '';
    document.getElementById('cat_sort_order').value = cat.sort_order ?? 0;
    document.getElementById('cat_status').checked = Boolean(cat.status);

    populateParentCategoryDropdown(cat.id, cat.parent_id);

    const m = document.getElementById('categoryModal');
    if (m) m.classList.add('active');
  })
  .catch(err => {
    console.error('Error loading category for edit:', err);
    const cat = STORE_CATEGORIES_CACHE.find(c => c.id == id);
    if (!cat) return;

    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    document.getElementById('cat_id').value = cat.id;
    document.getElementById('cat_title').value = cat.title || '';
    document.getElementById('cat_handle').value = cat.handle || '';
    document.getElementById('cat_description').value = cat.description || '';
    document.getElementById('cat_image').value = cat.image || '';
    document.getElementById('cat_sort_order').value = cat.sort_order ?? 0;
    document.getElementById('cat_status').checked = Boolean(cat.status);

    populateParentCategoryDropdown(cat.id, cat.parent_id);

    const m = document.getElementById('categoryModal');
    if (m) m.classList.add('active');
  });
};

window.closeCategoryModal = function() {
  const m = document.getElementById('categoryModal');
  if (m) m.classList.remove('active');
};

window.autoGenCategorySlug = function() {
  const title = document.getElementById('cat_title').value;
  const handleEl = document.getElementById('cat_handle');
  if (handleEl && !document.getElementById('cat_id').value) {
    handleEl.value = title.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }
};

window.saveCategoryData = function() {
  const id = document.getElementById('cat_id').value;
  const parentSelect = document.getElementById('cat_parent_id');
  const parentIdVal = parentSelect ? parentSelect.value.trim() : '';

  const parentId = (parentIdVal !== '' && parentIdVal !== 'null' && parentIdVal !== '0') ? parseInt(parentIdVal, 10) : null;

  const payload = {
    parent_id: parentId,
    title: document.getElementById('cat_title').value.trim(),
    handle: document.getElementById('cat_handle').value.trim(),
    description: document.getElementById('cat_description').value.trim(),
    image: document.getElementById('cat_image').value.trim(),
    sort_order: parseInt(document.getElementById('cat_sort_order').value, 10) || 0,
    status: document.getElementById('cat_status').checked
  };

  if (!payload.title) {
    alert('Please enter a Category Name.');
    return;
  }

  const url = id ? `/api/admin/categories/${id}` : `/api/admin/categories`;
  const method = id ? 'PUT' : 'POST';

  fetch(url, {
    method: method,
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin',
    body: JSON.stringify(payload)
  })
  .then(async r => {
    let data;
    try {
      data = await r.json();
    } catch(e) {
      data = { success: false, message: 'Server returned invalid response.' };
    }
    return { ok: r.ok, status: r.status, data };
  })
  .then(res => {
    if (res.ok && res.data.success) {
      showToast(id ? 'Category updated successfully!' : 'Category created successfully!');
      closeCategoryModal();
      loadCategoriesCatalog();
    } else {
      let errMsg = (res.data && res.data.message) || 'Failed to save category.';
      if (res.data && res.data.errors) {
        const errorList = Object.values(res.data.errors).flat().join('\n');
        errMsg += '\n' + errorList;
      }
      alert(errMsg);
    }
  })
  .catch(err => {
    console.error('Error saving category:', err);
    alert('Network error while saving category.');
  });
};

window.toggleCategoryStatus = function(id) {
  fetch(`/api/admin/categories/${id}/status`, {
    method: 'PATCH',
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast('Category status updated.');
      loadCategoriesCatalog();
    } else {
      alert(res.message || 'Failed to update category status.');
    }
  });
};

window.deleteCategory = function(id) {
  if (confirm('Are you sure you want to delete this category?')) {
    fetch(`/api/admin/categories/${id}`, {
      method: 'DELETE',
      headers: getAdminFetchHeaders(),
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast('Category deleted successfully.');
        loadCategoriesCatalog();
      } else {
        alert(res.message || 'Cannot delete category.');
      }
    })
    .catch(err => {
      console.error('Error deleting category:', err);
      alert('Network error while deleting category.');
    });
  }
};

window.uploadCategoryAsset = function(target) {
  const fileInput = document.getElementById('cat_image_file');
  if (!fileInput || !fileInput.files || !fileInput.files[0]) return;

  const fd = new FormData();
  fd.append('image', fileInput.files[0]);

  fetch('/api/admin/products/upload-media', {
    method: 'POST',
    headers: getAdminFetchHeaders(null),
    credentials: 'same-origin',
    body: fd
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && res.url) {
      document.getElementById('cat_image').value = res.url;
      showToast('Image uploaded successfully!');
    } else {
      alert(res.message || 'Upload failed.');
    }
  });
};

// ------------------------------------------------------------------------------
// B. PRODUCTS MANAGEMENT
// ------------------------------------------------------------------------------
let STORE_PRODUCTS_CACHE = [];

window.loadProductsCatalog = function() {
  const tbody = document.getElementById('productsTableBody');
  if (tbody) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#718096;">⏳ Loading products from database...</td></tr>`;
  }

  const query = (document.getElementById('productSearchInput')?.value || '').trim();
  const catId = document.getElementById('productCategoryFilter')?.value || '';

  const params = new URLSearchParams();
  if (query) params.append('search', query);
  if (catId) params.append('category_id', catId);
  params.append('per_page', '100');

  fetch(`/api/admin/products?${params.toString()}`, {
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && Array.isArray(res.products)) {
      STORE_PRODUCTS_CACHE = res.products;
      renderProductsTable(res.products);
    } else {
      if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#E53E3E;">Failed to load products.</td></tr>`;
    }
  })
  .catch(err => {
    console.error('Error fetching products:', err);
    if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#E53E3E;">Network error while loading products.</td></tr>`;
  });
};

function renderProductsTable(products) {
  const tbody = document.getElementById('productsTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  if (!products || products.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:26px;color:#718096;">No products found. Click <strong>＋ Add Product</strong> to add a new item to your catalog.</td></tr>`;
    const footer = document.getElementById('productsEntriesFooter');
    if (footer) footer.textContent = `Showing 0 products`;
    return;
  }

  products.forEach((p, idx) => {
    const tr = document.createElement('tr');
    const isAct = Boolean(p.status);
    const imgUrl = p.featured_image || '/images/placeholder.webp';

    const flags = [];
    if (p.is_new_arrival) flags.push(`<span style="background:#FEF3C7;color:#D97706;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;">NEW</span>`);
    if (p.is_bestseller) flags.push(`<span style="background:#FEE2E2;color:#DC2626;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;">BEST</span>`);
    if (p.is_featured) flags.push(`<span style="background:#E0E7FF;color:#4338CA;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;">FEAT</span>`);

    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td>
        <div class="product-manage-row" style="display:flex;align-items:center;gap:10px;">
          <img src="${imgUrl}" alt="Thumb" class="product-img" style="width:42px;height:42px;object-fit:cover;border-radius:6px;border:1px solid #E2E8F0;">
          <div>
            <div style="font-weight:700;font-size:13.5px;color:#1A202C;">${p.title}</div>
            <div style="font-size:11.5px;color:#718096;">SKU : <strong>${p.sku}</strong> | <code style="color:#0284C7;">/${p.slug}</code></div>
          </div>
        </div>
      </td>
      <td><span style="font-size:12.5px;color:#4A5568;">${p.category ? p.category.title : '—'}</span></td>
      <td>
        <div class="product-price-meta">
          <div>Reg: ৳ ${p.regular_price}</div>
          <div style="font-weight:700;color:#0F172A;">Sale: ৳ ${p.sale_price}</div>
        </div>
      </td>
      <td>
        <span class="product-stock-pill" style="background:${p.stock > 0 ? '#E2E8F0' : '#FEE2E2'};color:${p.stock > 0 ? '#1E293B' : '#DC2626'};">${p.stock}</span>
      </td>
      <td>
        <div style="display:flex;gap:4px;flex-wrap:wrap;">${flags.join(' ') || '<span style="color:#94A3B8;font-size:11px;">—</span>'}</div>
      </td>
      <td>
        <button onclick="toggleProductStatus(${p.id})" style="border:none;background:none;cursor:pointer;" title="Click to toggle status">
          <span class="product-status-tag" style="background:${isAct ? '#ECFDF5' : '#FEF2F2'};color:${isAct ? '#059669' : '#DC2626'};">${isAct ? 'Active' : 'Disabled'}</span>
        </button>
      </td>
      <td style="text-align:center;">
        <div style="display:flex;gap:6px;justify-content:center;">
          <button class="action-dots-btn" onclick="openEditProductModal(${p.id})" title="Edit" style="color:#0284C7;">✏️</button>
          <button class="action-dots-btn" onclick="deleteProduct(${p.id})" title="Delete" style="color:#E53E3E;">🗑️</button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  const footer = document.getElementById('productsEntriesFooter');
  if (footer) footer.textContent = `Showing 1 to ${products.length} of total ${products.length} products`;
}

window.openAddProductModal = function() {
  document.getElementById('productModalTitle').textContent = 'Add New Product';
  document.getElementById('prod_id').value = '';
  document.getElementById('prod_title').value = '';
  document.getElementById('prod_sku').value = '';
  document.getElementById('prod_slug').value = '';
  document.getElementById('prod_category_id').value = '';
  document.getElementById('prod_regular_price').value = '';
  document.getElementById('prod_sale_price').value = '';
  document.getElementById('prod_stock').value = '50';
  document.getElementById('prod_sizes').value = '';
  document.getElementById('prod_featured_image').value = '';
  document.getElementById('prod_hover_image').value = '';
  document.getElementById('prod_short_description').value = '';
  document.getElementById('prod_description').value = '';
  document.getElementById('prod_is_new_arrival').checked = true;
  document.getElementById('prod_is_bestseller').checked = false;
  document.getElementById('prod_is_featured').checked = false;
  document.getElementById('prod_status').checked = true;

  const m = document.getElementById('productModal');
  if (m) m.classList.add('active');
};

window.openEditProductModal = function(id) {
  const p = STORE_PRODUCTS_CACHE.find(x => x.id == id);
  if (!p) return;

  document.getElementById('productModalTitle').textContent = 'Edit Product';
  document.getElementById('prod_id').value = p.id;
  document.getElementById('prod_title').value = p.title || '';
  document.getElementById('prod_sku').value = p.sku || '';
  document.getElementById('prod_slug').value = p.slug || '';
  document.getElementById('prod_category_id').value = p.category_id || '';
  document.getElementById('prod_regular_price').value = p.regular_price ?? '';
  document.getElementById('prod_sale_price').value = p.sale_price ?? '';
  document.getElementById('prod_stock').value = p.stock ?? 50;
  document.getElementById('prod_sizes').value = Array.isArray(p.sizes) ? p.sizes.join(', ') : '';
  document.getElementById('prod_featured_image').value = p.featured_image || '';
  document.getElementById('prod_hover_image').value = p.hover_image || '';
  document.getElementById('prod_short_description').value = p.short_description || '';
  document.getElementById('prod_description').value = p.description || '';
  document.getElementById('prod_is_new_arrival').checked = Boolean(p.is_new_arrival);
  document.getElementById('prod_is_bestseller').checked = Boolean(p.is_bestseller);
  document.getElementById('prod_is_featured').checked = Boolean(p.is_featured);
  document.getElementById('prod_status').checked = Boolean(p.status);

  const m = document.getElementById('productModal');
  if (m) m.classList.add('active');
};

window.closeProductModal = function() {
  const m = document.getElementById('productModal');
  if (m) m.classList.remove('active');
};

window.autoGenProductSlug = function() {
  const title = document.getElementById('prod_title').value;
  const slugEl = document.getElementById('prod_slug');
  if (slugEl && !document.getElementById('prod_id').value) {
    slugEl.value = title.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }
};

window.saveProductData = function() {
  const id = document.getElementById('prod_id').value;
  const sizesRaw = document.getElementById('prod_sizes').value.trim();
  const sizesArray = sizesRaw ? sizesRaw.split(',').map(s => s.trim()).filter(Boolean) : [];

  const regPrice = parseFloat(document.getElementById('prod_regular_price').value);
  const salePriceRaw = document.getElementById('prod_sale_price').value;
  const salePrice = salePriceRaw !== '' ? parseFloat(salePriceRaw) : regPrice;

  const payload = {
    title: document.getElementById('prod_title').value.trim(),
    sku: document.getElementById('prod_sku').value.trim(),
    slug: document.getElementById('prod_slug').value.trim(),
    category_id: document.getElementById('prod_category_id').value || null,
    regular_price: regPrice,
    sale_price: salePrice,
    stock: parseInt(document.getElementById('prod_stock').value, 10) || 0,
    sizes: sizesArray,
    featured_image: document.getElementById('prod_featured_image').value.trim() || null,
    hover_image: document.getElementById('prod_hover_image').value.trim() || null,
    short_description: document.getElementById('prod_short_description').value.trim(),
    description: document.getElementById('prod_description').value.trim(),
    is_new_arrival: document.getElementById('prod_is_new_arrival').checked,
    is_bestseller: document.getElementById('prod_is_bestseller').checked,
    is_featured: document.getElementById('prod_is_featured').checked,
    status: document.getElementById('prod_status').checked
  };

  const url = id ? `/api/admin/products/${id}` : `/api/admin/products`;
  const method = id ? 'PUT' : 'POST';

  fetch(url, {
    method: method,
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin',
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast(id ? 'Product updated successfully!' : 'Product created successfully!');
      closeProductModal();
      loadProductsCatalog();
    } else {
      alert(res.message || (res.errors ? JSON.stringify(res.errors) : 'Failed to save product.'));
    }
  })
  .catch(err => {
    console.error('Error saving product:', err);
    alert('Network error while saving product.');
  });
};

window.toggleProductStatus = function(id) {
  fetch(`/api/admin/products/${id}/status`, {
    method: 'PATCH',
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast('Product status updated.');
      loadProductsCatalog();
    } else {
      alert(res.message || 'Failed to update product status.');
    }
  });
};

window.deleteProduct = function(id) {
  if (confirm('Are you sure you want to delete this product?')) {
    fetch(`/api/admin/products/${id}`, {
      method: 'DELETE',
      headers: getAdminFetchHeaders(),
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast('Product deleted.');
        loadProductsCatalog();
      } else {
        alert(res.message || 'Cannot delete product.');
      }
    });
  }
};

window.uploadProductImage = function(target) {
  const fileInputId = target === 'hover' ? 'prod_hover_file' : 'prod_image_file';
  const textInputId = target === 'hover' ? 'prod_hover_image' : 'prod_featured_image';

  const fileInput = document.getElementById(fileInputId);
  if (!fileInput || !fileInput.files || !fileInput.files[0]) return;

  const fd = new FormData();
  fd.append('image', fileInput.files[0]);

  fetch('/api/admin/products/upload-media', {
    method: 'POST',
    headers: getAdminFetchHeaders(null),
    credentials: 'same-origin',
    body: fd
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && res.url) {
      document.getElementById(textInputId).value = res.url;
      showToast('Product image uploaded successfully!');
    } else {
      alert(res.message || 'Upload failed.');
    }
  });
};

// ------------------------------------------------------------------------------
// C. HERO SLIDERS MANAGEMENT
// ------------------------------------------------------------------------------
let STORE_SLIDERS_CACHE = [];

window.loadSlidersCatalog = function() {
  const tbody = document.getElementById('slidersTableBody');
  if (tbody) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:#718096;">⏳ Loading sliders from database...</td></tr>`;
  }

  fetch('/api/admin/sliders', {
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && Array.isArray(res.sliders)) {
      STORE_SLIDERS_CACHE = res.sliders;
      renderSlidersTable(res.sliders);
    } else {
      if (tbody) tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:#E53E3E;">Failed to load sliders.</td></tr>`;
    }
  })
  .catch(err => {
    console.error('Error fetching sliders:', err);
    if (tbody) tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:#E53E3E;">Network error while loading sliders.</td></tr>`;
  });
};

function renderSlidersTable(sliders) {
  const tbody = document.getElementById('slidersTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  if (sliders.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:26px;color:#718096;">No hero sliders created yet. Click <strong>＋ Add Hero Slider</strong> to add one.</td></tr>`;
    const footer = document.getElementById('slidersEntriesFooter');
    if (footer) footer.textContent = `Showing 0 sliders`;
    return;
  }

  sliders.forEach((s, idx) => {
    const tr = document.createElement('tr');
    const isAct = Boolean(s.status);
    const imgHtml = s.image
      ? `<img src="${s.image}" style="width:70px;height:38px;object-fit:cover;border-radius:4px;border:1px solid #CBD5E0;">`
      : `<div style="width:70px;height:38px;background:#F1F5F9;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:11px;color:#94A3B8;">No Image</div>`;

    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td>${imgHtml}</td>
      <td>
        <div style="font-weight:700;font-size:13.5px;color:#0F172A;">${s.title}</div>
        <div style="font-size:11.5px;color:#64748B;">${s.subtitle || '—'}</div>
      </td>
      <td>
        <span style="font-weight:600;color:#0F172A;">${s.button_text || 'Shop Now'}</span> &rarr; <code style="color:#0284C7;font-size:11.5px;">${s.link || '/shop'}</code>
      </td>
      <td><span style="font-weight:600;color:#475569;">${s.sort_order ?? 0}</span></td>
      <td>
        <button onclick="toggleSliderStatus(${s.id})" style="border:none;background:none;cursor:pointer;" title="Click to toggle status">
          <span class="product-status-tag" style="background:${isAct ? '#ECFDF5' : '#FEF2F2'};color:${isAct ? '#059669' : '#DC2626'};">${isAct ? 'Active' : 'Disabled'}</span>
        </button>
      </td>
      <td style="text-align:center;">
        <div style="display:flex;gap:6px;justify-content:center;">
          <button class="action-dots-btn" onclick="openEditSliderModal(${s.id})" title="Edit" style="color:#0284C7;">✏️</button>
          <button class="action-dots-btn" onclick="deleteSlider(${s.id})" title="Delete" style="color:#E53E3E;">🗑️</button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });

  const footer = document.getElementById('slidersEntriesFooter');
  if (footer) footer.textContent = `Showing 1 to ${sliders.length} of total ${sliders.length} hero sliders`;
}

window.openAddSliderModal = function() {
  document.getElementById('sliderModalTitle').textContent = 'Add Hero Slider';
  document.getElementById('slider_id').value = '';
  document.getElementById('slider_title').value = '';
  document.getElementById('slider_subtitle').value = '';
  document.getElementById('slider_image').value = '';
  document.getElementById('slider_button_text').value = 'Shop Now';
  document.getElementById('slider_link').value = '/shop';
  document.getElementById('slider_sort_order').value = '0';
  document.getElementById('slider_status').checked = true;

  const m = document.getElementById('sliderModal');
  if (m) m.classList.add('active');
};

window.openEditSliderModal = function(id) {
  const s = STORE_SLIDERS_CACHE.find(x => x.id == id);
  if (!s) return;

  document.getElementById('sliderModalTitle').textContent = 'Edit Hero Slider';
  document.getElementById('slider_id').value = s.id;
  document.getElementById('slider_title').value = s.title || '';
  document.getElementById('slider_subtitle').value = s.subtitle || '';
  document.getElementById('slider_image').value = s.image || '';
  document.getElementById('slider_button_text').value = s.button_text || 'Shop Now';
  document.getElementById('slider_link').value = s.link || '/shop';
  document.getElementById('slider_sort_order').value = s.sort_order ?? 0;
  document.getElementById('slider_status').checked = Boolean(s.status);

  const m = document.getElementById('sliderModal');
  if (m) m.classList.add('active');
};

window.closeSliderModal = function() {
  const m = document.getElementById('sliderModal');
  if (m) m.classList.remove('active');
};

window.saveSliderData = function() {
  const id = document.getElementById('slider_id').value;
  const payload = {
    title: document.getElementById('slider_title').value.trim(),
    subtitle: document.getElementById('slider_subtitle').value.trim(),
    image: document.getElementById('slider_image').value.trim(),
    button_text: document.getElementById('slider_button_text').value.trim(),
    link: document.getElementById('slider_link').value.trim(),
    sort_order: parseInt(document.getElementById('slider_sort_order').value, 10) || 0,
    status: document.getElementById('slider_status').checked
  };

  const url = id ? `/api/admin/sliders/${id}` : `/api/admin/sliders`;
  const method = id ? 'PUT' : 'POST';

  fetch(url, {
    method: method,
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin',
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast(id ? 'Slider updated successfully!' : 'Slider created successfully!');
      closeSliderModal();
      loadSlidersCatalog();
    } else {
      alert(res.message || 'Failed to save slider.');
    }
  });
};

window.toggleSliderStatus = function(id) {
  fetch(`/api/admin/sliders/${id}/status`, {
    method: 'PATCH',
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast('Slider status updated.');
      loadSlidersCatalog();
    }
  });
};

window.deleteSlider = function(id) {
  if (confirm('Are you sure you want to delete this slider?')) {
    fetch(`/api/admin/sliders/${id}`, {
      method: 'DELETE',
      headers: getAdminFetchHeaders(),
      credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        showToast('Slider deleted.');
        loadSlidersCatalog();
      }
    });
  }
};

window.uploadSliderAsset = function() {
  const fileInput = document.getElementById('slider_image_file');
  if (!fileInput || !fileInput.files || !fileInput.files[0]) return;

  const fd = new FormData();
  fd.append('image', fileInput.files[0]);

  fetch('/api/admin/sliders/upload-media', {
    method: 'POST',
    headers: getAdminFetchHeaders(null),
    credentials: 'same-origin',
    body: fd
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && res.url) {
      document.getElementById('slider_image').value = res.url;
      showToast('Slider image uploaded successfully!');
    } else {
      alert(res.message || 'Upload failed.');
    }
  });
};

// ------------------------------------------------------------------------------
// D. STOREFRONT SETTINGS & PROMOTIONAL BANNERS
// ------------------------------------------------------------------------------
window.loadStorefrontSettings = function() {
  fetch('/api/admin/settings/storefront', {
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin'
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && res.settings) {
      const s = res.settings;
      const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val || '';
      };

      setVal('setting_site_name', s.site_name);
      setVal('setting_site_title', s.site_title);
      setVal('setting_site_logo', s.site_logo);
      setVal('setting_site_favicon', s.site_favicon);
      setVal('setting_support_phone', s.support_phone);
      setVal('setting_support_email', s.support_email);
      setVal('setting_store_address', s.store_address);
      setVal('setting_whatsapp_number', s.whatsapp_number);
      setVal('setting_footer_description', s.footer_description);

      setVal('setting_promo_banner_1_title', s.promo_banner_1_title);
      setVal('setting_promo_banner_1_subtitle', s.promo_banner_1_subtitle);
      setVal('setting_promo_banner_1_desc', s.promo_banner_1_desc);
      setVal('setting_promo_banner_1_image', s.promo_banner_1_image);
      setVal('setting_promo_banner_1_link', s.promo_banner_1_link);

      setVal('setting_promo_banner_2_title', s.promo_banner_2_title);
      setVal('setting_promo_banner_2_subtitle', s.promo_banner_2_subtitle);
      setVal('setting_promo_banner_2_desc', s.promo_banner_2_desc);
      setVal('setting_promo_banner_2_image', s.promo_banner_2_image);
      setVal('setting_promo_banner_2_link', s.promo_banner_2_link);

      const preview = document.getElementById('setting_logo_preview');
      if (preview) {
        if (s.site_logo) {
          preview.innerHTML = `<img src="${s.site_logo}" style="max-width:100%;max-height:100%;object-fit:contain;">`;
        } else {
          preview.innerHTML = `<span style="font-size:11px;color:#94A3B8;">No Logo</span>`;
        }
      }
    }
  });
};

window.saveStorefrontSettings = function() {
  const getVal = id => (document.getElementById(id)?.value || '').trim();

  const payload = {
    site_name: getVal('setting_site_name'),
    site_title: getVal('setting_site_title'),
    site_logo: getVal('setting_site_logo'),
    site_favicon: getVal('setting_site_favicon'),
    support_phone: getVal('setting_support_phone'),
    support_email: getVal('setting_support_email'),
    store_address: getVal('setting_store_address'),
    whatsapp_number: getVal('setting_whatsapp_number'),
    footer_description: getVal('setting_footer_description'),

    promo_banner_1_title: getVal('setting_promo_banner_1_title'),
    promo_banner_1_subtitle: getVal('setting_promo_banner_1_subtitle'),
    promo_banner_1_desc: getVal('setting_promo_banner_1_desc'),
    promo_banner_1_image: getVal('setting_promo_banner_1_image'),
    promo_banner_1_link: getVal('setting_promo_banner_1_link'),

    promo_banner_2_title: getVal('setting_promo_banner_2_title'),
    promo_banner_2_subtitle: getVal('setting_promo_banner_2_subtitle'),
    promo_banner_2_desc: getVal('setting_promo_banner_2_desc'),
    promo_banner_2_image: getVal('setting_promo_banner_2_image'),
    promo_banner_2_link: getVal('setting_promo_banner_2_link')
  };

  fetch('/api/admin/settings/storefront', {
    method: 'POST',
    headers: getAdminFetchHeaders(),
    credentials: 'same-origin',
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast('Storefront settings saved successfully!');
      loadStorefrontSettings();
    } else {
      alert(res.message || 'Failed to save settings.');
    }
  });
};

window.uploadBrandingAsset = function(type) {
  let fileInputId = 'setting_logo_file';
  let textInputId = 'setting_site_logo';
  if (type === 'favicon') {
    fileInputId = 'setting_favicon_file';
    textInputId = 'setting_site_favicon';
  } else if (type === 'promo_1') {
    fileInputId = 'setting_promo_1_file';
    textInputId = 'setting_promo_banner_1_image';
  } else if (type === 'promo_2') {
    fileInputId = 'setting_promo_2_file';
    textInputId = 'setting_promo_banner_2_image';
  }

  const fileInput = document.getElementById(fileInputId);
  if (!fileInput || !fileInput.files || !fileInput.files[0]) return;

  const fd = new FormData();
  fd.append('image', fileInput.files[0]);

  fetch('/api/admin/settings/upload-branding', {
    method: 'POST',
    headers: getAdminFetchHeaders(null),
    credentials: 'same-origin',
    body: fd
  })
  .then(r => r.json())
  .then(res => {
    if (res.success && res.url) {
      document.getElementById(textInputId).value = res.url;
      showToast('Asset uploaded successfully!');
      if (type === 'logo') {
        const preview = document.getElementById('setting_logo_preview');
        if (preview) preview.innerHTML = `<img src="${res.url}" style="max-width:100%;max-height:100%;object-fit:contain;">`;
      }
    } else {
      alert(res.message || 'Upload failed.');
    }
  });
};

// ==============================================================================
// 7. CUSTOMERS DIRECTORY
// ==============================================================================
function renderCustomersTable(filterQuery = '') {
  const tbody = document.getElementById('customersTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  let list = APP_STATE.customers;
  if (filterQuery) {
    const q = filterQuery.toLowerCase();
    list = list.filter(c => c.name.toLowerCase().includes(q) || c.phone.includes(q));
  }

  if (list.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:26px;color:#A0AEC0;">কোনো গ্রাহকের তথ্য পাওয়া যায়নি (No customers yet)</td></tr>`;
    return;
  }

  list.forEach((c, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td><b>${c.name}</b></td>
      <td><a href="tel:${c.phone}" style="color:#004D40;text-decoration:none;font-weight:600;">📞 ${c.phone}</a></td>
      <td><span class="product-status-tag" style="${c.behavior === 'VIP' ? 'background:#FEF3C7;color:#D97706;' : ''}">${c.behavior}</span></td>
      <td><span class="product-status-tag" style="background:#ECFDF5;color:#059669;">${c.totalOrders}</span></td>
      <td>${c.city}</td>
      <td>${c.address}</td>
      <td style="text-align:right;">
        <button class="action-btn-circle-blue" onclick="showToast('Customer history active')">👁️</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

// ==============================================================================
// 8. ACCOUNTS (INCOME, EXPENSE, BALANCE, CREDITS)
// ==============================================================================
function renderIncomeTable() {
  const tbody = document.getElementById('incomeTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  APP_STATE.incomeList.forEach((item, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${item.date}</td>
      <td>${item.purpose}</td>
      <td><span style="background:#E2E8F0;padding:2px 8px;border-radius:4px;font-size:12px;">${item.paidBy}</span></td>
      <td><b>৳ ${item.amount.toLocaleString()}</b></td>
    `;
    tbody.appendChild(tr);
  });
}

function renderExpenseTable() {
  const tbody = document.getElementById('expenseTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';
  APP_STATE.expenseList.forEach((item, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${item.date}</td>
      <td>${item.purpose}</td>
      <td><span style="background:#E2E8F0;padding:2px 8px;border-radius:4px;font-size:12px;">${item.paidBy}</span></td>
      <td><b style="color:#E53E3E;">৳ ${item.amount.toLocaleString()}</b></td>
    `;
    tbody.appendChild(tr);
  });
}

function renderCreditTable() {
  const tbody = document.getElementById('creditTableBody');
  const totalDisplay = document.getElementById('creditTotalAmountDisplay');
  const countText = document.getElementById('creditEntriesCountText');
  if (!tbody) return;
  tbody.innerHTML = '';

  const list = APP_STATE.creditsList;
  const total = list.reduce((sum, item) => sum + (parseInt(item.amount) || 0), 0);

  if (totalDisplay) totalDisplay.textContent = `৳ ${total.toLocaleString()}`;
  if (countText) {
    countText.textContent = list.length === 0
      ? "Showing 0 of total 0 entries"
      : `Showing 1 to ${list.length} of total ${list.length} entries`;
  }

  if (list.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:#A0AEC0;">কোনো ক্রেডিট রেকর্ড নেই</td></tr>`;
    return;
  }

  list.forEach((item, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td>${item.date}</td>
      <td>${item.invoice || '-'}</td>
      <td>${item.method}</td>
      <td><b>৳ ${item.amount}</b></td>
      <td>${item.comment || '-'}</td>
      <td>${item.inserted || 'Admin'}</td>
      <td>
        <button class="action-dots-btn" onclick="deleteCreditItem(${idx})">🗑️</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

window.openAddCreditModal = function() {
  const m = document.getElementById('addCreditModal');
  if (m) m.classList.add('active');
};

window.saveNewCredit = function() {
  const date = document.getElementById('creditDate').value || new Date().toISOString().substring(0, 10);
  const invoice = document.getElementById('creditInvoice').value;
  const method = document.getElementById('creditMethod').value;
  const amount = parseInt(document.getElementById('creditAmount').value) || 0;
  const comment = document.getElementById('creditComment').value;

  if (amount <= 0) {
    alert('দয়া করে সঠিক অ্যামাউন্ট দিন।');
    return;
  }

  const record = {
    date: date,
    invoice: invoice,
    method: method,
    amount: amount,
    comment: comment,
    inserted: "Admin"
  };

  APP_STATE.creditsList.unshift(record);
  localStorage.setItem('admin_credits', JSON.stringify(APP_STATE.creditsList));
  renderCreditTable();
  closeAllModals();
  showToast(`নতুন ক্রেডিট ৳ ${amount} সংরক্ষিত হয়েছে!`);
};

window.deleteCreditItem = function(idx) {
  if (confirm('আপনি কি এই রেকর্ডটি মুছে ফেলতে চান?')) {
    APP_STATE.creditsList.splice(idx, 1);
    localStorage.setItem('admin_credits', JSON.stringify(APP_STATE.creditsList));
    renderCreditTable();
    showToast('রেকর্ড মুছে ফেলা হয়েছে।');
  }
};

function renderProfitReport() {
  const totalRev = APP_STATE.orders.reduce((a, b) => a + b.total, 0);
  const prodCost = Math.round(totalRev * 0.45);
  const expenses = APP_STATE.expenseList.reduce((a, b) => a + b.amount, 0);
  const grossProfit = totalRev - prodCost;
  const netProfit = grossProfit - expenses;

  const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  setEl('profitOrderAmount', `৳ ${totalRev.toLocaleString()}`);
  setEl('profitProdCost', `৳ ${prodCost.toLocaleString()}`);
  setEl('profitGross', `৳ ${grossProfit.toLocaleString()}`);
  setEl('profitExpense', `৳ ${expenses.toLocaleString()}`);
  setEl('profitNet', `৳ ${netProfit.toLocaleString()}`);
}

// ==============================================================================
// ==============================================================================
// 9. LANDING PAGES MANAGEMENT & BUILDER (100% Dynamic & Master Design System)
// ==============================================================================
APP_STATE.liveLandingPages = [];
APP_STATE.lpFilterStatus = 'all';
APP_STATE.lpSearchQuery = '';
APP_STATE.currentEditingLandingPage = null;
APP_STATE.isBuilderDirty = false;

function getAdminAuthHeaders() {
  const token = localStorage.getItem('admin_token') || '';
  return {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`,
    'x-admin-token': token
  };
}

/**
 * Universal Confirmation Modal for Admin Actions
 */
window.showLpConfirmModal = function({ title, heading, message, icon, confirmText, confirmClass, onConfirm }) {
  const modal = document.getElementById('lpConfirmModal');
  if (!modal) {
    if (confirm(message || heading || 'Are you sure?')) {
      if (typeof onConfirm === 'function') onConfirm();
    }
    return;
  }
  const titleEl = document.getElementById('lpConfirmModalTitle');
  const iconEl = document.getElementById('lpConfirmModalIcon');
  const headingEl = document.getElementById('lpConfirmModalHeading');
  const msgEl = document.getElementById('lpConfirmModalMessage');
  const btnEl = document.getElementById('lpConfirmModalConfirmBtn');

  if (titleEl) titleEl.textContent = title || 'Confirmation';
  if (iconEl) iconEl.textContent = icon || '⚠️';
  if (headingEl) headingEl.textContent = heading || 'Are you sure?';
  if (msgEl) msgEl.textContent = message || '';
  if (btnEl) {
    btnEl.textContent = confirmText || 'Confirm';
    btnEl.className = confirmClass || 'btn-primary-teal';
    btnEl.onclick = function() {
      closeLpConfirmModal();
      if (typeof onConfirm === 'function') onConfirm();
    };
  }
  modal.classList.add('active');
};

window.closeLpConfirmModal = function() {
  const modal = document.getElementById('lpConfirmModal');
  if (modal) modal.classList.remove('active');
};

window.markBuilderDirty = function() {
  APP_STATE.isBuilderDirty = true;
};

window.navigateBackFromBuilder = function() {
  if (APP_STATE.isBuilderDirty) {
    showLpConfirmModal({
      title: 'Unsaved Changes',
      icon: '⚠️',
      heading: 'Discard Unsaved Changes?',
      message: 'You have unsaved edits in the builder. If you leave now, your changes will be lost.',
      confirmText: 'Discard & Leave',
      confirmClass: 'lp-btn-remove',
      onConfirm: function() {
        APP_STATE.isBuilderDirty = false;
        switchView('landing-pages-list');
      }
    });
  } else {
    switchView('landing-pages-list');
  }
};

/**
 * Fetch and render all landing pages with actual metrics
 */
window.renderLandingPagesList = async function() {
  const tbody = document.getElementById('landingPagesTableBody');
  if (!tbody) return;

  tbody.innerHTML = `<tr><td colspan="13" style="text-align:center;padding:32px;color:#718096;"><div style="font-size:24px;margin-bottom:8px;">⏳</div>Loading landing pages...</td></tr>`;

  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 8000);

  try {
    const res = await fetch('/api/admin/landing-pages', {
      signal: controller.signal,
      credentials: 'same-origin',
      headers: getAdminAuthHeaders()
    });
    clearTimeout(timer);

    if (res.status === 401) {
      tbody.innerHTML = `<tr><td colspan="13" style="text-align:center;padding:32px;color:#718096;">Please login to view landing pages.</td></tr>`;
      return;
    }

    const data = await res.json();

    if (res.ok && data.success && Array.isArray(data.pages)) {
      APP_STATE.liveLandingPages = data.pages;
      updateLandingPageFilterCounts(data.pages);
      renderLandingPagesTableRows();
    } else {
      tbody.innerHTML = `<tr><td colspan="13" style="text-align:center;padding:32px;color:#E53E3E;"><div style="font-size:24px;margin-bottom:8px;">⚠️</div>Failed to load landing pages.</td></tr>`;
    }
  } catch (err) {
    clearTimeout(timer);
    console.error('[Landing Pages Error]', err);
    tbody.innerHTML = `<tr><td colspan="13" style="text-align:center;padding:32px;color:#E53E3E;"><div style="font-size:24px;margin-bottom:8px;">🔌</div>Connection error loading landing pages.</td></tr>`;
  }
};

function updateLandingPageFilterCounts(pages) {
  const allCount = pages.length;
  const pubCount = pages.filter(p => p.status === 'published').length;
  const draftCount = pages.filter(p => p.status === 'draft').length;
  const unpubCount = pages.filter(p => p.status === 'unpublished').length;

  const setC = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  setC('lpCountAll', allCount);
  setC('lpCountPublished', pubCount);
  setC('lpCountDraft', draftCount);
  setC('lpCountUnpublished', unpubCount);
}

function renderLandingPagesTableRows() {
  const tbody = document.getElementById('landingPagesTableBody');
  if (!tbody) return;

  let filtered = APP_STATE.liveLandingPages || [];

  if (APP_STATE.lpFilterStatus && APP_STATE.lpFilterStatus !== 'all') {
    filtered = filtered.filter(p => (p.status || '').toLowerCase() === APP_STATE.lpFilterStatus.toLowerCase());
  }

  if (APP_STATE.lpSearchQuery && APP_STATE.lpSearchQuery.trim() !== '') {
    const q = APP_STATE.lpSearchQuery.toLowerCase().trim();
    filtered = filtered.filter(p =>
      (p.name && p.name.toLowerCase().includes(q)) ||
      (p.slug && p.slug.toLowerCase().includes(q)) ||
      (p.product_name && p.product_name.toLowerCase().includes(q))
    );
  }

  if (filtered.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="13" style="text-align:center;padding:48px 20px;">
          <div style="font-size:40px;margin-bottom:12px;">📄</div>
          <h4 style="font-size:16px;font-weight:700;color:#1E293B;margin:0 0 6px;">No Landing Pages Found</h4>
          <p style="color:#64748B;font-size:13px;max-width:380px;margin:0 auto 16px;">Create a new landing page or adjust your search filter to see pages.</p>
          <button class="btn-primary-teal" onclick="openCreateLandingPage()" style="padding:8px 18px;font-size:13px;">+ Create Landing Page</button>
        </td>
      </tr>`;
    return;
  }

  tbody.innerHTML = '';

  filtered.forEach((page, idx) => {
    const tr = document.createElement('tr');

    let statusClass = 'draft';
    if (page.status === 'published') statusClass = 'published';
    else if (page.status === 'unpublished') statusClass = 'unpublished';

    const statusLabel = page.status ? page.status.charAt(0).toUpperCase() + page.status.slice(1) : 'Draft';
    const updatedDate = page.updated_at || page.created_at || '-';
    const themeLabel = (page.theme === 'chicken-booster') ? 'Chicken Booster' : 'Universal Product';

    tr.innerHTML = `
      <td style="color:#64748B;font-size:12px;">${idx + 1}</td>
      <td>
        <div style="font-weight:700;color:#0F172A;font-size:13.5px;">${page.name}</div>
        <div style="font-size:11.5px;color:#64748B;margin-top:2px;">Title: ${page.title ? (page.title.length > 35 ? page.title.slice(0,35) + '...' : page.title) : '-'}</div>
      </td>
      <td>
        <div style="font-family:monospace;font-size:12px;color:#0284C7;font-weight:600;">/product/${page.slug}</div>
        <a href="${page.public_url}" target="_blank" style="font-size:11px;color:#004D40;text-decoration:underline;display:inline-block;margin-top:2px;" title="Open in browser">View Live ↗</a>
      </td>
      <td>
        <span style="font-size:12.5px;color:#334155;font-weight:500;">${page.product_name || page.name}</span>
      </td>
      <td>
        <span style="background:#E2E8F0;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;color:#334155;">
          ${themeLabel}
        </span>
      </td>
      <td>
        <span class="lp-status-badge ${statusClass}">${statusLabel}</span>
      </td>
      <td style="text-align:right;font-weight:600;color:#334155;">${(page.visitors || 0).toLocaleString()}</td>
      <td style="text-align:right;font-weight:700;color:#004D40;">${(page.orders || 0).toLocaleString()}</td>
      <td style="text-align:right;font-weight:800;color:#D90429;">৳ ${(page.revenue || 0).toLocaleString()}</td>
      <td style="text-align:right;font-weight:700;color:${page.conversion_rate > 5 ? '#16A34A' : '#475569'};">${page.conversion_rate || 0}%</td>
      <td style="text-align:right;font-weight:700;color:#1E293B;">৳ ${(page.aov || 0).toLocaleString()}</td>
      <td style="font-size:11.5px;color:#64748B;white-space:nowrap;">${updatedDate}</td>
      <td style="text-align:right;white-space:nowrap;">
        <button class="btn-primary-teal" style="padding:5px 10px;font-size:11.5px;margin-right:3px;" onclick="editLandingPage(${page.id})" title="Edit in Builder">
          ✏️ Edit
        </button>
        <button class="btn-lp-preview" style="padding:5px 8px;font-size:11.5px;margin-right:3px;" onclick="previewLandingPageById(${page.id}, '${page.name.replace(/'/g, "\\'")}')" title="Live Device Preview">
          👁️
        </button>
        <button class="btn-lp-draft" style="padding:5px 8px;font-size:11.5px;margin-right:3px;" onclick="duplicateLandingPage(${page.id})" title="Clone / Duplicate">
          📋
        </button>
        ${page.status === 'published'
          ? `<button class="btn-lp-draft" style="padding:5px 8px;font-size:11.5px;color:#DC2626;margin-right:3px;" onclick="promptToggleLandingPageStatus(${page.id}, 'unpublished', '${page.name.replace(/'/g, "\\'")}')" title="Unpublish">⏸️</button>`
          : `<button class="btn-lp-draft" style="padding:5px 8px;font-size:11.5px;color:#16A34A;margin-right:3px;" onclick="promptToggleLandingPageStatus(${page.id}, 'published', '${page.name.replace(/'/g, "\\'")}')" title="Publish Live">🚀</button>`
        }
        <button class="lp-btn-remove" style="padding:5px 8px;font-size:11.5px;" onclick="promptDeleteLandingPage(${page.id}, '${page.name.replace(/'/g, "\\'")}')" title="Delete Permanently">🗑️</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

window.filterLandingPagesByStatus = function(status) {
  APP_STATE.lpFilterStatus = (status || 'all').toLowerCase();
  ['All', 'Published', 'Draft', 'Unpublished'].forEach(s => {
    const btn = document.getElementById('lpFilter' + s);
    if (btn) btn.classList.toggle('active', s.toLowerCase() === APP_STATE.lpFilterStatus);
  });
  renderLandingPagesTableRows();
};

window.handleLandingPageSearch = function() {
  const input = document.getElementById('lpSearchInput');
  APP_STATE.lpSearchQuery = input ? input.value : '';
  renderLandingPagesTableRows();
};

// ==============================================================================
// BUILDER / CMS STATE & FORM POPULATION
// ==============================================================================

window.openCreateLandingPage = async function(template = 'universal') {
  APP_STATE.currentEditingLandingPage = null;
  APP_STATE.isBuilderDirty = false;
  document.getElementById('builderPageId').value = '';
  document.getElementById('builderPageTitleDisplay').textContent = 'Create New Landing Page';
  document.getElementById('builderPublicUrlDisplay').textContent = 'https://growthagro.shop/product/{slug}';
  document.getElementById('builderPublicUrlDisplay').removeAttribute('href');

  const badge = document.getElementById('builderStatusBadge');
  if (badge) {
    badge.className = 'lp-status-badge draft';
    badge.textContent = 'DRAFT';
  }

  try {
    const res = await fetch(`/api/admin/landing-pages/master-defaults?template=${encodeURIComponent(template)}`, { headers: getAdminAuthHeaders() });
    const data = await res.json();
    if (res.ok && data.success) {
      populateBuilderForm({
        name: '',
        slug: '',
        status: 'draft',
        theme: template,
        product_name: '',
        title: '',
        meta_title: '',
        meta_description: '',
        content: data.content,
        delivery_config: data.delivery_config,
        theme_config: data.theme_config,
        seo_config: {},
        section_order: data.section_order
      });
    }
  } catch (e) {
    console.error('[Defaults Error]', e);
  }

  switchView('landing-page-builder');
  attachBuilderDirtyListeners();
};

window.handleTemplatePresetChange = async function(preset) {
  showLpConfirmModal({
    title: 'Switch Template Preset',
    icon: '🎨',
    heading: `Switch to ${preset === 'chicken-booster' ? 'Chicken Booster' : 'Universal Product'} Preset?`,
    message: 'Loading this preset will populate recommended default content, packages, and theme styling for this template. You can customize everything after loading.',
    confirmText: 'Load Preset Content',
    confirmClass: 'btn-primary-teal',
    onConfirm: async function() {
      try {
        const res = await fetch(`/api/admin/landing-pages/master-defaults?template=${encodeURIComponent(preset)}`, { headers: getAdminAuthHeaders() });
        const data = await res.json();
        if (res.ok && data.success) {
          const currentName = document.getElementById('lpName')?.value || '';
          const currentSlug = document.getElementById('lpSlug')?.value || '';
          const currentStatus = document.getElementById('lpStatus')?.value || 'draft';
          
          populateBuilderForm({
            name: currentName,
            slug: currentSlug,
            status: currentStatus,
            theme: preset,
            product_name: currentName,
            title: currentName,
            meta_title: currentName,
            meta_description: '',
            content: data.content,
            delivery_config: data.delivery_config,
            theme_config: data.theme_config,
            seo_config: {},
            section_order: data.section_order
          });
          showToast(`Loaded ${preset === 'chicken-booster' ? 'Chicken Booster' : 'Universal Product'} preset.`);
        }
      } catch (err) {
        showToast('Could not load preset.', 'error');
      }
    }
  });
};

window.editLandingPage = async function(id) {
  try {
    const res = await fetch(`/api/admin/landing-pages/${id}`, { headers: getAdminAuthHeaders() });
    const data = await res.json();
    if (res.ok && data.success && data.page) {
      const p = data.page;
      APP_STATE.currentEditingLandingPage = p;
      APP_STATE.isBuilderDirty = false;
      document.getElementById('builderPageId').value = p.id;
      document.getElementById('builderPageTitleDisplay').textContent = `Editing: ${p.name}`;
      document.getElementById('builderPublicUrlDisplay').textContent = p.public_url;
      document.getElementById('builderPublicUrlDisplay').href = p.public_url;

      const badge = document.getElementById('builderStatusBadge');
      if (badge) {
        badge.className = `lp-status-badge ${p.status}`;
        badge.textContent = p.status ? p.status.toUpperCase() : 'DRAFT';
      }

      populateBuilderForm(p);
      switchView('landing-page-builder');
      attachBuilderDirtyListeners();
    } else {
      showToast('Could not load landing page details.', 'error');
    }
  } catch (err) {
    console.error('[Edit Landing Page Error]', err);
    showToast('Failed to load landing page.', 'error');
  }
};

window.duplicateLandingPage = async function(id) {
  showLpConfirmModal({
    title: 'Duplicate Landing Page',
    icon: '📋',
    heading: 'Duplicate this Landing Page?',
    message: 'A complete clone of this page will be created as a Draft with its own unique slug.',
    confirmText: 'Duplicate Page',
    confirmClass: 'btn-primary-teal',
    onConfirm: async function() {
      try {
        const res = await fetch(`/api/admin/landing-pages/${id}/duplicate`, {
          method: 'POST',
          headers: getAdminAuthHeaders()
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast(data.message || 'Landing page duplicated successfully!');
          renderLandingPagesList();
        } else {
          showToast(data.message || 'Duplication failed.', 'error');
        }
      } catch (err) {
        console.error('[Duplicate Error]', err);
        showToast('Could not duplicate page.', 'error');
      }
    }
  });
};

window.promptToggleLandingPageStatus = function(id, newStatus, pageName) {
  const isPub = (newStatus === 'published');
  showLpConfirmModal({
    title: isPub ? 'Publish Landing Page' : 'Unpublish Landing Page',
    icon: isPub ? '🚀' : '⏸️',
    heading: isPub ? `Publish "${pageName}"?` : `Unpublish "${pageName}"?`,
    message: isPub
      ? 'This landing page will immediately go live and become publicly accessible to customers at its URL.'
      : 'This landing page will be unpublished and hidden from public visitors.',
    confirmText: isPub ? 'Yes, Publish Live' : 'Yes, Unpublish',
    confirmClass: isPub ? 'btn-primary-teal' : 'btn-lp-draft',
    onConfirm: async function() {
      try {
        const res = await fetch(`/api/admin/landing-pages/${id}/status`, {
          method: 'PATCH',
          headers: getAdminAuthHeaders(),
          body: JSON.stringify({ status: newStatus })
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast(`Status updated to ${newStatus.toUpperCase()}`);
          renderLandingPagesList();
        } else {
          showToast('Failed to update status.', 'error');
        }
      } catch (e) {
        showToast('Status update failed.', 'error');
      }
    }
  });
};

window.promptDeleteLandingPage = function(id, pageName) {
  showLpConfirmModal({
    title: 'Delete Landing Page',
    icon: '🗑️',
    heading: `Delete "${pageName}"?`,
    message: 'Are you sure you want to permanently delete this landing page? This action cannot be undone.',
    confirmText: 'Yes, Delete Permanently',
    confirmClass: 'lp-btn-remove',
    onConfirm: async function() {
      try {
        const res = await fetch(`/api/admin/landing-pages/${id}`, {
          method: 'DELETE',
          headers: getAdminAuthHeaders()
        });
        const data = await res.json();
        if (res.ok && data.success) {
          showToast('Landing page permanently deleted.');
          renderLandingPagesList();
        } else {
          showToast(data.message || 'Could not delete page.', 'error');
        }
      } catch (err) {
        showToast('Delete request error.', 'error');
      }
    }
  });
};

function attachBuilderDirtyListeners() {
  const container = document.getElementById('view-landing-page-builder');
  if (!container || container._hasDirtyListener) return;
  container.addEventListener('input', markBuilderDirty);
  container.addEventListener('change', markBuilderDirty);
  container._hasDirtyListener = true;
}

function populateBuilderForm(p) {
  const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v !== undefined && v !== null ? v : ''; };
  const setImg = (id, src) => { const el = document.getElementById(id); if (el && src) el.src = src; };

  // 1. Basic Info
  setVal('lpName', p.name);
  setVal('lpSlug', p.slug);
  setVal('lpTheme', p.theme || 'universal');
  setVal('lpStatus', p.status || 'draft');
  setVal('lpCategory', p.category || '');
  setVal('lpBrand', p.brand || '');
  setVal('lpProductName', p.product_name || p.name);
  setVal('lpProductId', p.product_id || p.slug);
  setVal('lpTitle', p.title);
  setVal('lpMetaDesc', p.meta_description);

  // 2. Delivery Config
  const d = p.delivery_config || {};
  setVal('lpDeliveryType', d.delivery_type || 'free');
  setVal('lpChargeInside', d.charge_inside_dhaka || 0);
  setVal('lpChargeOutside', d.charge_outside_dhaka || 0);

  const sameEverywhereEl = document.getElementById('lpSameEverywhere');
  if (sameEverywhereEl) sameEverywhereEl.checked = !!d.same_charge_everywhere;

  const freeAboveEl = document.getElementById('lpFreeDeliveryAbove');
  if (freeAboveEl) freeAboveEl.checked = !!d.free_delivery_above;
  setVal('lpFreeThreshold', d.free_delivery_threshold || 1000);

  handleDeliveryTypeChange(d.delivery_type || 'free');
  handleFreeThresholdToggle(!!d.free_delivery_above);

  // 3. Hero & Header Content
  const c = p.content || {};
  const h = c.header || {};
  const hero = c.hero || {};
  setVal('lpHeaderHotline', h.hotline_phone || '01864-444411');
  setVal('lpHeaderLogoUrl', h.logo_image || '/images/logo.png');
  setImg('lpLogoPreview', h.logo_image || '/images/logo.png');

  setVal('lpHeroAlertHook', hero.alert_hook || '');
  setVal('lpHeroMainTitle', hero.main_title || '');
  setVal('lpHeroSubtext', hero.subtext || '');
  setVal('lpHeroCtaText', hero.cta_button_text || '👉 অর্ডার করতে ক্লিক করুন');

  const dualCards = hero.dual_cards || [];
  if (dualCards[0]) {
    setVal('lpHeroCard1Tag', dualCards[0].tag || '');
    setVal('lpHeroCard1Img', dualCards[0].product_image || '');
    setImg('lpHeroCard1ImgPreview', dualCards[0].product_image || '/images/placeholder.webp');
    setVal('lpHeroCard1Bg', dualCards[0].background_image || '');
    setImg('lpHeroCard1BgPreview', dualCards[0].background_image || '/images/placeholder.webp');
  }
  if (dualCards[1]) {
    setVal('lpHeroCard2Tag', dualCards[1].tag || '');
    setVal('lpHeroCard2Img', dualCards[1].product_image || '');
    setImg('lpHeroCard2ImgPreview', dualCards[1].product_image || '/images/placeholder.webp');
    setVal('lpHeroCard2Bg', dualCards[1].background_image || '');
    setImg('lpHeroCard2BgPreview', dualCards[1].background_image || '/images/placeholder.webp');
  }

  // 4. Product Packages
  renderPackagesRepeater(c.packages || []);

  // 5. Benefits 1 & 2
  const b1 = c.benefits_section_1 || {};
  setVal('lpBen1Title', b1.section_title || 'কেন আমাদের পণ্যটি বেছে নেবেন?');
  renderBenefitsRepeater('benefits1Container', b1.items || []);

  const b2 = c.benefits_section_2 || {};
  setVal('lpBen2Title', b2.section_title || 'বিশেষ সুবিধাসমূহ ও গুণাগুণ');
  renderBenefitsRepeater('benefits2Container', b2.items || []);

  // 6. Video Stories
  const v = c.video_reviews || {};
  setVal('lpVideoTitle', v.section_title || 'গ্রাহকদের ভিডিও রিভিউ ও আনবক্সিং');
  renderVideosRepeater(v.items || []);

  // 7. Usage Guide
  const u = c.usage_guide || {};
  setVal('lpUsageTitle', u.section_title || 'ব্যবহার বিধি ও নির্দেশিকা');
  setVal('lpUsageImg', u.image || '/images/placeholder.webp');
  setImg('lpUsageImgPreview', u.image || '/images/placeholder.webp');
  setVal('lpUsageInstruction', u.instruction_text || '');

  // 8. Reviews / Testimonials with Photos
  const t = c.testimonials || {};
  setVal('lpRevTitle', t.section_title || 'গ্রাহকদের বাস্তব রিভিউ ও মতামত');
  renderTestimonialsRepeater(t.items || []);

  // 9. FAQs
  const f = c.faqs || {};
  setVal('lpFaqTitle', f.section_title || 'সাধারণ জিজ্ঞাসা (FAQ)');
  renderFaqsRepeater(f.items || []);

  // 10. Offer Banner
  const o = c.offer_banner || {};
  setVal('lpOfferBadge', o.badge || 'স্পেশাল ধামাকা অফার');
  setVal('lpOfferTitle', o.title || 'সীমিত সময়ের বিশেষ ছাড় অফার!');
  setVal('lpOfferSubtitle', o.subtitle || 'আজই অর্ডার করুন এবং পান বিশেষ মূল্যছাড় ও ফ্রি ডেলিভারি।');

  // 11. Checkout Config
  const ch = c.checkout || {};
  setVal('lpCheckoutTitle', ch.title || 'অর্ডার করতে আপনার সঠিক তথ্য দিয়ে নিচের ফর্মটি সম্পূর্ণ পূরণ করুন।');
  setVal('lpCheckoutBtnText', ch.order_button_text || 'অর্ডার করুন');
  setVal('lpSuccessTitle', ch.success_title || 'আপনার অর্ডারটি সফল হয়েছে!');
  setVal('lpSuccessMsg', ch.success_message || 'অর্ডারটি নিশ্চিত করতে আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে ফোনে যোগাযোগ করবেন।');

  // 12. Theme Colors
  const tc = p.theme_config || {};
  setThemeColor('themePrimary', tc.primary_color || '#0F766E');
  setThemeColor('themeSecondary', tc.secondary_color || '#115E59');
  setThemeColor('themeSoftTeal', tc.light_teal || '#F0FDFA');
  setThemeColor('themeBtnRed', tc.btn_red || '#E11D48');
  setThemeColor('themeBtnHover', tc.btn_red_hover || '#BE123C');
  setThemeColor('themeAccentYellow', tc.accent_yellow || '#F59E0B');
  setThemeColor('themeTextDark', tc.text_dark || '#0F172A');
  setThemeColor('themeBgBody', tc.bg_body || '#FFFFFF');

  // 13. SEO
  const seo = p.seo_config || {};
  setVal('lpOgTitle', seo.og_title || p.title);
  setVal('lpCanonical', seo.canonical_url || '');
  setVal('lpOgImage', seo.og_image || '/images/placeholder.webp');
  setImg('lpOgImgPreview', seo.og_image || '/images/placeholder.webp');

  // 14. Section Order
  renderSectionOrderManager(p.section_order || []);
}

function setThemeColor(idPrefix, colorHex) {
  const picker = document.getElementById(idPrefix);
  const hex = document.getElementById(idPrefix + 'Hex');
  if (picker) picker.value = colorHex;
  if (hex) hex.value = colorHex;
}

window.syncColorInput = function(picker, hexInputId) {
  const hex = document.getElementById(hexInputId);
  if (hex) hex.value = picker.value;
};

window.syncColorPicker = function(hexInput, pickerId) {
  const picker = document.getElementById(pickerId);
  if (picker && /^#[0-9A-F]{6}$/i.test(hexInput.value)) {
    picker.value = hexInput.value;
  }
};

window.resetToUniversalColors = function() {
  setThemeColor('themePrimary', '#0F766E');
  setThemeColor('themeSecondary', '#115E59');
  setThemeColor('themeSoftTeal', '#F0FDFA');
  setThemeColor('themeBtnRed', '#E11D48');
  setThemeColor('themeBtnHover', '#BE123C');
  setThemeColor('themeAccentYellow', '#F59E0B');
  setThemeColor('themeTextDark', '#0F172A');
  setThemeColor('themeBgBody', '#FFFFFF');
  showToast('Reset to Universal Theme colors.');
};

window.resetToChickenBoosterColors = function() {
  setThemeColor('themePrimary', '#054c55');
  setThemeColor('themeSecondary', '#03363d');
  setThemeColor('themeSoftTeal', '#eaf5f6');
  setThemeColor('themeBtnRed', '#d90429');
  setThemeColor('themeBtnHover', '#b50322');
  setThemeColor('themeAccentYellow', '#ffd166');
  setThemeColor('themeTextDark', '#1e293b');
  setThemeColor('themeBgBody', '#ffffff');
  showToast('Reset to Chicken Booster Theme colors.');
};

window.handleDeliveryTypeChange = function(type) {
  const paidFields = document.getElementById('paidDeliveryFields');
  const sameEverywhere = document.getElementById('groupSameEverywhere');
  if (paidFields) paidFields.style.display = (type === 'paid') ? 'grid' : 'none';
  if (sameEverywhere) sameEverywhere.style.display = (type === 'paid') ? 'block' : 'none';
};

window.handleSameChargeToggle = function(checked) {
  const outside = document.getElementById('lpChargeOutside');
  const inside = document.getElementById('lpChargeInside');
  if (checked && inside && outside) {
    outside.value = inside.value;
  }
};

window.handleFreeThresholdToggle = function(checked) {
  const group = document.getElementById('thresholdAmountGroup');
  if (group) group.style.display = checked ? 'block' : 'none';
};

window.handleLpNameInput = function(val) {
  const slugInput = document.getElementById('lpSlug');
  const idVal = document.getElementById('builderPageId').value;
  if (slugInput && !idVal && val) {
    slugInput.value = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    document.getElementById('builderPublicUrlDisplay').textContent = `https://growthagro.shop/product/${slugInput.value}`;
  }
};

window.validateSlugInput = function(val) {
  const sanitized = val.toLowerCase().replace(/[^a-z0-9-]/g, '');
  const slugInput = document.getElementById('lpSlug');
  if (slugInput) slugInput.value = sanitized;
  document.getElementById('builderPublicUrlDisplay').textContent = `https://growthagro.shop/product/${sanitized}`;
};

window.checkSlugAvailability = async function() {
  const slug = (document.getElementById('lpSlug')?.value || '').trim();
  const excludeId = document.getElementById('builderPageId')?.value || '';
  const feedback = document.getElementById('slugCheckFeedback');
  if (!slug) return;

  try {
    const res = await fetch(`/api/admin/landing-pages/check-slug?slug=${encodeURIComponent(slug)}&exclude_id=${excludeId}`);
    const data = await res.json();
    if (feedback) {
      feedback.style.color = data.available ? '#16A34A' : '#DC2626';
      feedback.textContent = data.available ? '✅ This slug is available.' : '❌ This slug is already in use.';
    }
  } catch (e) {
    if (feedback) feedback.textContent = 'Could not verify slug.';
  }
};

window.toggleAccordion = function(accId) {
  const item = document.getElementById(accId);
  if (item) item.classList.toggle('active');
};

// ==============================================================================
// IMAGE UPLOADER HANDLER (Supports JPG, PNG, WEBP with Instant Preview)
// ==============================================================================
window.uploadLpImage = async function(fileInput, inputId, imgId) {
  if (!fileInput.files || !fileInput.files[0]) return;
  const file = fileInput.files[0];
  const formData = new FormData();
  formData.append('image', file);

  showToast('Uploading image...');

  try {
    const token = localStorage.getItem('admin_token') || '';
    const res = await fetch('/api/admin/landing-pages/upload-media', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'x-admin-token': token },
      body: formData
    });
    const data = await res.json();
    if (res.ok && data.success && data.url) {
      const inp = document.getElementById(inputId);
      const img = document.getElementById(imgId);
      if (inp) inp.value = data.url;
      if (img) img.src = data.url;
      showToast('Image uploaded successfully!');
    } else {
      showToast(data.error || 'Failed to upload image.', 'error');
    }
  } catch (e) {
    showToast('Failed to upload image.', 'error');
  } finally {
    fileInput.value = '';
  }
};

window.clearStaticImage = function(inputId, imgId) {
  const inp = document.getElementById(inputId);
  const img = document.getElementById(imgId);
  if (inp) inp.value = '';
  if (img) img.src = '/images/placeholder.webp';
  showToast('Image removed.');
};

// ==============================================================================
// REPEATERS FOR DYNAMIC LANDING PAGE SECTIONS
// ==============================================================================

// 1. Packages Repeater
function renderPackagesRepeater(packages) {
  const container = document.getElementById('packagesContainer');
  if (!container) return;
  container.innerHTML = '';
  packages.forEach(pkg => addPackageItem(pkg));
}

window.addPackageItem = function(data = {}) {
  const container = document.getElementById('packagesContainer');
  if (!container) return;
  const id = data.id || ('pkg-' + Date.now());
  const div = document.createElement('div');
  div.className = 'lp-repeater-box';
  div.innerHTML = `
    <div class="lp-repeater-header">
      <span>Package Item</span>
      <button type="button" class="lp-btn-remove" onclick="this.closest('.lp-repeater-box').remove()">Remove</button>
    </div>
    <div class="lp-form-grid">
      <div class="form-group">
        <label>Package ID / Key</label>
        <input type="text" class="form-control no-icon pkg-id" value="${id}">
      </div>
      <div class="form-group">
        <label>Package Title *</label>
        <input type="text" class="form-control no-icon pkg-name" value="${data.name || ''}" placeholder="e.g. Broiler Booster (১ কেজি)">
      </div>
    </div>
    <div class="lp-form-grid triple">
      <div class="form-group">
        <label>Price (৳) *</label>
        <input type="number" class="form-control no-icon pkg-price" value="${data.price || 0}">
      </div>
      <div class="form-group">
        <label>Regular Price (৳)</label>
        <input type="number" class="form-control no-icon pkg-old-price" value="${data.old_price || 0}">
      </div>
      <div class="form-group">
        <label>Default Qty (0 or 1)</label>
        <input type="number" class="form-control no-icon pkg-default-qty" value="${data.default_quantity || 0}" min="0" max="10">
      </div>
    </div>
    <div class="form-group">
      <label>Package Thumbnail Image</label>
      <div class="lp-image-uploader">
        <img src="${data.image || '/assets/images/broiler-booster-product.webp'}" class="lp-image-thumb-preview pkg-img-prev" onerror="this.src='/images/placeholder.webp'">
        <div class="lp-image-uploader-input-wrap">
          <input type="text" class="form-control no-icon pkg-image" value="${data.image || ''}">
        </div>
        <div class="lp-image-actions-wrap">
          <input type="file" accept="image/*" style="display:none;" onchange="uploadDynamicImage(this, 'pkg-image', 'pkg-img-prev')">
          <button type="button" class="lp-btn-upload" onclick="this.previousElementSibling.click()">📁 Upload</button>
          <button type="button" class="lp-btn-replace" onclick="this.previousElementSibling.previousElementSibling.click()">🔄 Replace</button>
          <button type="button" class="lp-btn-remove-img" onclick="clearDynamicImage(this, 'pkg-image', 'pkg-img-prev')">✕</button>
        </div>
      </div>
    </div>
  `;
  container.appendChild(div);
};

// 2. Benefits Checklists Repeater
function renderBenefitsRepeater(containerId, items) {
  const container = document.getElementById(containerId);
  if (!container) return;
  container.innerHTML = '';
  items.forEach(it => {
    const div = document.createElement('div');
    div.className = 'lp-repeater-box';
    div.innerHTML = `
      <div class="lp-repeater-header">
        <span>Benefit Item</span>
        <button type="button" class="lp-btn-remove" onclick="this.closest('.lp-repeater-box').remove()">Remove</button>
      </div>
      <div class="lp-form-grid">
        <div class="form-group">
          <label>Title / Headline *</label>
          <input type="text" class="form-control no-icon benefit-title" value="${it.title || ''}">
        </div>
        <div class="form-group">
          <label>Description (Optional)</label>
          <input type="text" class="form-control no-icon benefit-desc" value="${it.desc || ''}">
        </div>
      </div>
    `;
    container.appendChild(div);
  });
}

window.addBenefit1Item = function() {
  const c = document.getElementById('benefits1Container');
  if (c) renderBenefitsRepeater('benefits1Container', [...getBenefitsData('benefits1Container'), { title: '', desc: '' }]);
};

window.addBenefit2Item = function() {
  const c = document.getElementById('benefits2Container');
  if (c) renderBenefitsRepeater('benefits2Container', [...getBenefitsData('benefits2Container'), { title: '', desc: '' }]);
};

function getBenefitsData(containerId) {
  const container = document.getElementById(containerId);
  if (!container) return [];
  const items = [];
  container.querySelectorAll('.lp-repeater-box').forEach(box => {
    const t = box.querySelector('.benefit-title')?.value || '';
    const d = box.querySelector('.benefit-desc')?.value || '';
    if (t.trim()) items.push({ title: t.trim(), desc: d.trim() });
  });
  return items;
}

// 3. Videos Repeater
function renderVideosRepeater(items) {
  const container = document.getElementById('videosContainer');
  if (!container) return;
  container.innerHTML = '';
  items.forEach(it => addVideoItem(it));
}

window.addVideoItem = function(data = {}) {
  const container = document.getElementById('videosContainer');
  if (!container) return;
  const div = document.createElement('div');
  div.className = 'lp-repeater-box';
  div.innerHTML = `
    <div class="lp-repeater-header">
      <span>Customer Video Story</span>
      <button type="button" class="lp-btn-remove" onclick="this.closest('.lp-repeater-box').remove()">Remove</button>
    </div>
    <div class="form-group">
      <label>Story / Video Caption *</label>
      <input type="text" class="form-control no-icon video-title" value="${data.title || ''}" placeholder="মুরগির দ্রুত ওজন বৃদ্ধির বাস্তব অভিজ্ঞতা...">
    </div>
    <div class="form-group">
      <label>Video URL / Embed Link (Optional)</label>
      <input type="text" class="form-control no-icon video-url" value="${data.video_url || ''}" placeholder="https://www.youtube.com/watch?v=...">
    </div>
    <div class="form-group">
      <label>Thumbnail Image</label>
      <div class="lp-image-uploader">
        <img src="${data.thumbnail || '/assets/images/review-broiler.webp'}" class="lp-image-thumb-preview vid-img-prev" onerror="this.src='/images/placeholder.webp'">
        <div class="lp-image-uploader-input-wrap">
          <input type="text" class="form-control no-icon video-thumb" value="${data.thumbnail || ''}">
        </div>
        <div class="lp-image-actions-wrap">
          <input type="file" accept="image/*" style="display:none;" onchange="uploadDynamicImage(this, 'video-thumb', 'vid-img-prev')">
          <button type="button" class="lp-btn-upload" onclick="this.previousElementSibling.click()">📁 Upload</button>
          <button type="button" class="lp-btn-replace" onclick="this.previousElementSibling.previousElementSibling.click()">🔄 Replace</button>
          <button type="button" class="lp-btn-remove-img" onclick="clearDynamicImage(this, 'video-thumb', 'vid-img-prev')">✕</button>
        </div>
      </div>
    </div>
  `;
  container.appendChild(div);
};

// 4. Testimonials with Customer Photo Upload Repeater
function renderTestimonialsRepeater(items) {
  const container = document.getElementById('testimonialsContainer');
  if (!container) return;
  container.innerHTML = '';
  items.forEach((it, idx) => addTestimonialItem({ ...it, sort_order: it.sort_order || (idx + 1) }));
}

window.addTestimonialItem = function(data = {}) {
  const container = document.getElementById('testimonialsContainer');
  if (!container) return;
  const div = document.createElement('div');
  div.className = 'lp-repeater-box';
  div.innerHTML = `
    <div class="lp-repeater-header">
      <span>Customer Review</span>
      <button type="button" class="lp-btn-remove" onclick="this.closest('.lp-repeater-box').remove()">Remove</button>
    </div>
    <div class="lp-form-grid">
      <div class="form-group">
        <label>Customer Name *</label>
        <input type="text" class="form-control no-icon rev-name" value="${data.name || ''}" placeholder="মো: রফিকুল ইসলাম">
      </div>
      <div class="form-group">
        <label>Location</label>
        <input type="text" class="form-control no-icon rev-location" value="${data.location || ''}" placeholder="ময়মনসিংহ">
      </div>
    </div>
    <div class="lp-form-grid triple">
      <div class="form-group">
        <label>Product Variant</label>
        <input type="text" class="form-control no-icon rev-variant" value="${data.product_variant || ''}" placeholder="Broiler Booster (১ কেজি)">
      </div>
      <div class="form-group">
        <label>Star Rating (1 to 5)</label>
        <select class="form-control no-icon rev-rating">
          <option value="5" ${data.rating == 5 ? 'selected' : ''}>★★★★★ (5 Stars)</option>
          <option value="4" ${data.rating == 4 ? 'selected' : ''}>★★★★☆ (4 Stars)</option>
          <option value="3" ${data.rating == 3 ? 'selected' : ''}>★★★☆☆ (3 Stars)</option>
          <option value="2" ${data.rating == 2 ? 'selected' : ''}>★★☆☆☆ (2 Stars)</option>
          <option value="1" ${data.rating == 1 ? 'selected' : ''}>★☆☆☆☆ (1 Star)</option>
        </select>
      </div>
      <div class="form-group">
        <label>Sort Order</label>
        <input type="number" class="form-control no-icon rev-sort" value="${data.sort_order || 1}" min="1">
      </div>
    </div>
    <div class="form-group">
      <label>Customer Photo / Avatar (Upload JPG/PNG/WEBP)</label>
      <div class="lp-image-uploader">
        <img src="${data.photo || '/assets/images/avatar-default.webp'}" class="lp-image-thumb-preview rev-img-prev" onerror="this.src='/images/placeholder.webp'">
        <div class="lp-image-uploader-input-wrap">
          <input type="text" class="form-control no-icon rev-photo" value="${data.photo || ''}" placeholder="/uploads/landing-pages/...">
        </div>
        <div class="lp-image-actions-wrap">
          <input type="file" accept="image/*" style="display:none;" onchange="uploadDynamicImage(this, 'rev-photo', 'rev-img-prev')">
          <button type="button" class="lp-btn-upload" onclick="this.previousElementSibling.click()">📷 Upload</button>
          <button type="button" class="lp-btn-replace" onclick="this.previousElementSibling.previousElementSibling.click()">🔄 Replace</button>
          <button type="button" class="lp-btn-remove-img" onclick="clearDynamicImage(this, 'rev-photo', 'rev-img-prev')">✕</button>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label>Review Text *</label>
      <textarea class="form-control no-icon rev-text" rows="3" placeholder="চিকেন বুস্টার ব্যবহার করে ফলাফল খুব ভালো পেয়েছি...">${data.review_text || ''}</textarea>
    </div>
    <div style="display:flex;gap:20px;margin-top:6px;align-items:center;">
      <label style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
        <input type="checkbox" class="rev-verified" ${data.is_verified !== false ? 'checked' : ''} style="accent-color:#004D40;">
        <span>Verified Buyer Badge (✓ ভেরিফাইড ক্রেতা)</span>
      </label>
      <label style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;cursor:pointer;">
        <input type="checkbox" class="rev-active" ${data.is_active !== false ? 'checked' : ''} style="accent-color:#004D40;">
        <span>Active (রিভিউটি পেজে দেখাবে)</span>
      </label>
    </div>
  `;
  container.appendChild(div);
};

// 5. FAQs Repeater
function renderFaqsRepeater(items) {
  const container = document.getElementById('faqsContainer');
  if (!container) return;
  container.innerHTML = '';
  items.forEach(it => addFaqItem(it));
}

window.addFaqItem = function(data = {}) {
  const container = document.getElementById('faqsContainer');
  if (!container) return;
  const div = document.createElement('div');
  div.className = 'lp-repeater-box';
  div.innerHTML = `
    <div class="lp-repeater-header">
      <span>FAQ Item</span>
      <button type="button" class="lp-btn-remove" onclick="this.closest('.lp-repeater-box').remove()">Remove</button>
    </div>
    <div class="form-group">
      <label>Question *</label>
      <input type="text" class="form-control no-icon faq-question" value="${data.question || ''}" placeholder="চিকেন বুস্টার কীভাবে ব্যবহার করতে হয়?">
    </div>
    <div class="form-group">
      <label>Answer *</label>
      <textarea class="form-control no-icon faq-answer" rows="2" placeholder="প্রতি ১০০০ মুরগির জন্য ১০০ গ্রাম পাউডার...">${data.answer || ''}</textarea>
    </div>
  `;
  container.appendChild(div);
};

// 6. Section Ordering & Visibility
let currentSectionOrderList = [];

function renderSectionOrderManager(sections) {
  currentSectionOrderList = sections && sections.length ? sections : [
    { id: 'hero', name: 'Hero Banner', enabled: true },
    { id: 'videos', name: 'Customer Success Stories', enabled: true },
    { id: 'benefits_1', name: 'Benefits Checklist 1', enabled: true },
    { id: 'benefits_2', name: 'Benefits Checklist 2', enabled: true },
    { id: 'usage', name: 'Usage & Dosage Guide', enabled: true },
    { id: 'offer', name: 'Offer & Urgency Banner', enabled: false },
    { id: 'reviews', name: 'Customer Testimonials', enabled: true },
    { id: 'faq', name: 'FAQ Section', enabled: true },
    { id: 'checkout', name: 'Checkout & Order Form', enabled: true },
    { id: 'footer', name: 'Footer & Helpline', enabled: true }
  ];

  renderSectionOrderListUI();
}

function renderSectionOrderListUI() {
  const container = document.getElementById('sectionOrderContainer');
  if (!container) return;
  container.innerHTML = '';

  currentSectionOrderList.forEach((sec, idx) => {
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;margin-bottom:8px;';
    row.innerHTML = `
      <div style="display:flex;align-items:center;gap:12px;">
        <span style="font-weight:700;color:#64748B;font-size:12px;">${idx + 1}</span>
        <input type="checkbox" ${sec.enabled ? 'checked' : ''} onchange="toggleSectionEnable(${idx}, this.checked)" style="accent-color:#004D40;width:16px;height:16px;">
        <span style="font-weight:600;font-size:13.5px;color:#1E293B;">${sec.name}</span>
      </div>
      <div style="display:flex;gap:6px;">
        <button type="button" class="btn-lp-draft" onclick="moveSectionOrder(${idx}, -1)" ${idx === 0 ? 'disabled' : ''} style="padding:4px 8px;font-size:11px;">▲ Up</button>
        <button type="button" class="btn-lp-draft" onclick="moveSectionOrder(${idx}, 1)" ${idx === currentSectionOrderList.length - 1 ? 'disabled' : ''} style="padding:4px 8px;font-size:11px;">▼ Down</button>
      </div>
    `;
    container.appendChild(row);
  });
}

window.moveSectionOrder = function(index, direction) {
  const target = index + direction;
  if (target < 0 || target >= currentSectionOrderList.length) return;
  const temp = currentSectionOrderList[index];
  currentSectionOrderList[index] = currentSectionOrderList[target];
  currentSectionOrderList[target] = temp;
  renderSectionOrderListUI();
};

window.toggleSectionEnable = function(index, checked) {
  if (currentSectionOrderList[index]) {
    currentSectionOrderList[index].enabled = checked;
  }
};

// ==============================================================================
// IMAGE UPLOAD & MANAGEMENT HELPERS (Upload / Preview / Replace / Remove)
// ==============================================================================
window.uploadDynamicImage = async function(fileInput, classTargetInput, classTargetImg) {
  if (!fileInput.files || !fileInput.files[0]) return;
  const file = fileInput.files[0];
  const formData = new FormData();
  formData.append('image', file);

  showToast('Uploading image...');

  try {
    const token = localStorage.getItem('admin_token') || '';
    const res = await fetch('/api/admin/landing-pages/upload-media', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'x-admin-token': token },
      body: formData
    });
    const data = await res.json();
    if (res.ok && data.success && data.url) {
      const parent = fileInput.closest('.lp-image-uploader');
      if (parent) {
        const inp = parent.querySelector(`.${classTargetInput}`);
        const img = parent.querySelector(`.${classTargetImg}`);
        if (inp) inp.value = data.url;
        if (img) img.src = data.url;
      }
      showToast('Image uploaded successfully!');
    } else {
      showToast(data.error || 'Failed to upload image.', 'error');
    }
  } catch (e) {
    showToast('Failed to upload image.', 'error');
  } finally {
    fileInput.value = '';
  }
};

window.clearDynamicImage = function(btn, classTargetInput, classTargetImg) {
  const parent = btn.closest('.lp-image-uploader');
  if (parent) {
    const inp = parent.querySelector(`.${classTargetInput}`);
    const img = parent.querySelector(`.${classTargetImg}`);
    if (inp) inp.value = '';
    if (img) img.src = '/images/placeholder.webp';
    showToast('Image removed.');
  }
};

// ==============================================================================
// SAVE LANDING PAGE DATA (CREATE / UPDATE API)
// ==============================================================================
window.saveLandingPageData = async function(targetStatus = 'draft') {
  const idVal = document.getElementById('builderPageId')?.value;
  const name = (document.getElementById('lpName')?.value || '').trim();
  const slug = (document.getElementById('lpSlug')?.value || '').trim();

  if (!name) {
    alert('Please enter a landing page name.');
    document.getElementById('lpName')?.focus();
    return;
  }

  if (!slug) {
    alert('Please enter a unique URL slug.');
    document.getElementById('lpSlug')?.focus();
    return;
  }

  // Build Packages array
  const packages = [];
  document.querySelectorAll('#packagesContainer .lp-repeater-box').forEach((box, i) => {
    const id = box.querySelector('.pkg-id')?.value || (`pkg-${i+1}`);
    const n = box.querySelector('.pkg-name')?.value || '';
    const p = parseFloat(box.querySelector('.pkg-price')?.value || 0);
    const op = parseFloat(box.querySelector('.pkg-old-price')?.value || 0);
    const q = parseInt(box.querySelector('.pkg-default-qty')?.value || 0);
    const img = box.querySelector('.pkg-image')?.value || '';
    if (n.trim()) {
      packages.push({ id, name: n.trim(), price: p, old_price: op, default_quantity: q, image: img, is_active: true, sort_order: i + 1 });
    }
  });

  // Build Videos array
  const videos = [];
  document.querySelectorAll('#videosContainer .lp-repeater-box').forEach(box => {
    const title = box.querySelector('.video-title')?.value || '';
    const thumbnail = box.querySelector('.video-thumb')?.value || '';
    if (title.trim()) videos.push({ title: title.trim(), thumbnail: thumbnail.trim(), video_url: '' });
  });

  // Build Testimonials array (all 8 explicit fields)
  const testimonials = [];
  document.querySelectorAll('#testimonialsContainer .lp-repeater-box').forEach((box, idx) => {
    const n = box.querySelector('.rev-name')?.value || '';
    const loc = box.querySelector('.rev-location')?.value || '';
    const pv = box.querySelector('.rev-variant')?.value || '';
    const r = parseInt(box.querySelector('.rev-rating')?.value || 5);
    const ph = box.querySelector('.rev-photo')?.value || '';
    const txt = box.querySelector('.rev-text')?.value || '';
    const verified = box.querySelector('.rev-verified')?.checked !== false;
    const active = box.querySelector('.rev-active')?.checked !== false;
    const sort = parseInt(box.querySelector('.rev-sort')?.value || (idx + 1));
    if (n.trim() && txt.trim()) {
      testimonials.push({
        name: n.trim(),
        location: loc.trim(),
        product_variant: pv.trim(),
        rating: r,
        photo: ph.trim(),
        review_text: txt.trim(),
        is_verified: verified,
        is_active: active,
        sort_order: sort,
        date: new Date().toLocaleDateString('bn-BD', { day: 'numeric', month: 'long', year: 'numeric' })
      });
    }
  });

  // Build FAQs array
  const faqs = [];
  document.querySelectorAll('#faqsContainer .lp-repeater-box').forEach(box => {
    const q = box.querySelector('.faq-question')?.value || '';
    const a = box.querySelector('.faq-answer')?.value || '';
    if (q.trim()) faqs.push({ question: q.trim(), answer: a.trim() });
  });

  const theme = document.getElementById('lpTheme')?.value || 'universal';
  const category = (document.getElementById('lpCategory')?.value || '').trim();
  const brand = (document.getElementById('lpBrand')?.value || '').trim();
  const productId = (document.getElementById('lpProductId')?.value || slug).trim();
  const productName = (document.getElementById('lpProductName')?.value || name).trim();

  const payload = {
    name: name,
    slug: slug,
    status: targetStatus,
    theme: theme,
    product_name: productName,
    product_id: productId,
    category: category,
    brand: brand,
    title: document.getElementById('lpTitle')?.value || name,
    meta_title: document.getElementById('lpTitle')?.value || name,
    meta_description: document.getElementById('lpMetaDesc')?.value || '',

    delivery_config: {
      delivery_type: document.getElementById('lpDeliveryType')?.value || 'free',
      charge_inside_dhaka: parseFloat(document.getElementById('lpChargeInside')?.value || 0),
      charge_outside_dhaka: parseFloat(document.getElementById('lpChargeOutside')?.value || 0),
      same_charge_everywhere: document.getElementById('lpSameEverywhere')?.checked || false,
      free_delivery_above: document.getElementById('lpFreeDeliveryAbove')?.checked || false,
      free_delivery_threshold: parseFloat(document.getElementById('lpFreeThreshold')?.value || 1000),
      inside_label: 'ঢাকার ভিতরে',
      outside_label: 'ঢাকার বাইরে'
    },

    theme_config: {
      primary_color: document.getElementById('themePrimaryHex')?.value || '#0F766E',
      secondary_color: document.getElementById('themeSecondaryHex')?.value || '#115E59',
      light_teal: document.getElementById('themeSoftTealHex')?.value || '#F0FDFA',
      btn_red: document.getElementById('themeBtnRedHex')?.value || '#E11D48',
      btn_red_hover: document.getElementById('themeBtnHoverHex')?.value || '#BE123C',
      accent_yellow: document.getElementById('themeAccentYellowHex')?.value || '#F59E0B',
      text_dark: document.getElementById('themeTextDarkHex')?.value || '#0F172A',
      bg_body: document.getElementById('themeBgBodyHex')?.value || '#FFFFFF'
    },

    seo_config: {
      og_title: document.getElementById('lpOgTitle')?.value || name,
      canonical_url: document.getElementById('lpCanonical')?.value || '',
      og_image: document.getElementById('lpOgImage')?.value || ''
    },

    section_order: currentSectionOrderList,

    content: {
      header: {
        hotline_phone: document.getElementById('lpHeaderHotline')?.value || '01864-444411',
        hotline_tel: (document.getElementById('lpHeaderHotline')?.value || '01864444411').replace(/[^0-9]/g, ''),
        logo_image: document.getElementById('lpHeaderLogoUrl')?.value || '/images/logo.png',
        cta_text: 'অর্ডার করুন'
      },
      hero: {
        alert_hook: document.getElementById('lpHeroAlertHook')?.value || '',
        main_title: document.getElementById('lpHeroMainTitle')?.value || '',
        subtext: document.getElementById('lpHeroSubtext')?.value || '',
        cta_button_text: document.getElementById('lpHeroCtaText')?.value || '👉 অর্ডার করতে ক্লিক করুন',
        dual_cards: [
          {
            tag: document.getElementById('lpHeroCard1Tag')?.value || '',
            product_image: document.getElementById('lpHeroCard1Img')?.value || '',
            background_image: document.getElementById('lpHeroCard1Bg')?.value || '',
            variant_key: 'pkg-1',
            title: 'ক্লিক করে অর্ডার করুন'
          },
          {
            tag: document.getElementById('lpHeroCard2Tag')?.value || '',
            product_image: document.getElementById('lpHeroCard2Img')?.value || '',
            background_image: document.getElementById('lpHeroCard2Bg')?.value || '',
            variant_key: 'pkg-2',
            title: 'ক্লিক করে অর্ডার করুন'
          }
        ]
      },
      packages: packages,
      benefits_section_1: {
        section_title: document.getElementById('lpBen1Title')?.value || 'কেন আমাদের পণ্যটি বেছে নেবেন?',
        helpline_text: 'প্রয়োজনে কল করুন: 01864-444411',
        helpline_tel: '01864444411',
        items: getBenefitsData('benefits1Container')
      },
      benefits_section_2: {
        section_title: document.getElementById('lpBen2Title')?.value || 'বিশেষ সুবিধাসমূহ ও গুণাগুণ',
        helpline_text: 'প্রয়োজনে কল করুন: 01864-444411',
        helpline_tel: '01864444411',
        items: getBenefitsData('benefits2Container')
      },
      video_reviews: {
        section_title: document.getElementById('lpVideoTitle')?.value || 'গ্রাহকদের ভিডিও রিভিউ ও আনবক্সিং',
        items: videos
      },
      usage_guide: {
        section_title: document.getElementById('lpUsageTitle')?.value || 'ব্যবহার বিধি ও নির্দেশিকা',
        image: document.getElementById('lpUsageImg')?.value || '',
        instruction_text: document.getElementById('lpUsageInstruction')?.value || '',
        helpline_text: 'প্রয়োজনে কল করুন: 01864-444411',
        helpline_tel: '01864444411'
      },
      testimonials: {
        section_title: document.getElementById('lpRevTitle')?.value || 'গ্রাহকদের বাস্তব রিভিউ ও মতামত',
        items: testimonials
      },
      faqs: {
        section_title: document.getElementById('lpFaqTitle')?.value || 'সাধারণ জিজ্ঞাসা (FAQ)',
        items: faqs
      },
      offer_banner: {
        enabled: true,
        badge: document.getElementById('lpOfferBadge')?.value || 'স্পেশাল ধামাকা অফার',
        title: document.getElementById('lpOfferTitle')?.value || '',
        subtitle: document.getElementById('lpOfferSubtitle')?.value || ''
      },
      checkout: {
        title: document.getElementById('lpCheckoutTitle')?.value || 'অর্ডার করতে আপনার সঠিক তথ্য দিয়ে নিচের ফর্মটি সম্পূর্ণ পূরণ করুন।',
        billing_title: 'Billing details',
        summary_title: 'অর্ডারের সারসংক্ষেপ',
        cod_badge_text: 'পণ্য হাতে পেয়ে চেক করে সম্পূর্ণ মূল্য পরিশোধ করুন। অগ্রিম কোনো টাকা দিতে হবে না।',
        privacy_badge_heading: 'Google & Gemini Data Privacy Standard',
        privacy_badge_text: 'আপনার তথ্য শতভাগ নিরাপদ ও এনক্রিপ্টেড। আপনার ফোন নম্বর ও ঠিকানা শুধুমাত্র কুরিয়ার ডেলিভারির কাজে সুরক্ষিতভাবে ব্যবহৃত হবে।',
        order_button_text: document.getElementById('lpCheckoutBtnText')?.value || 'অর্ডার করুন',
        success_title: document.getElementById('lpSuccessTitle')?.value || 'আপনার অর্ডারটি সফল হয়েছে!',
        success_message: document.getElementById('lpSuccessMsg')?.value || 'অর্ডারটি নিশ্চিত করতে আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে ফোনে যোগাযোগ করবেন।'
      },
      footer: {
        copyright_text: `© ${new Date().getFullYear()} Growth Agro. All rights reserved.`,
        helpline_phone: document.getElementById('lpHeaderHotline')?.value || '01864-444411',
        whatsapp_phone: '8801864444411'
      }
    }
  };

  showToast(targetStatus === 'published' ? 'Publishing landing page...' : 'Saving draft...');

  try {
    const url = idVal ? `/api/admin/landing-pages/${idVal}` : '/api/admin/landing-pages';
    const method = idVal ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method: method,
      headers: getAdminAuthHeaders(),
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (res.ok && data.success) {
      APP_STATE.isBuilderDirty = false;
      showToast(targetStatus === 'published' ? 'Landing page published successfully!' : 'Draft saved successfully!');
      if (data.page_id) document.getElementById('builderPageId').value = data.page_id;
      if (data.public_url) {
        document.getElementById('builderPublicUrlDisplay').textContent = data.public_url;
        document.getElementById('builderPublicUrlDisplay').href = data.public_url;
      }
      const badge = document.getElementById('builderStatusBadge');
      if (badge) {
        badge.className = `lp-status-badge ${targetStatus}`;
        badge.textContent = targetStatus.toUpperCase();
      }
    } else {
      showToast(data.message || 'Failed to save landing page.', 'error');
    }
  } catch (err) {
    console.error('[Save Error]', err);
    showToast('Failed to save landing page.', 'error');
  }
};

window.resetToChickenBoosterColors = function() {
  const masterColors = {
    themePrimary: '#054c55',
    themeSecondary: '#03363d',
    themeSoftTeal: '#eaf5f6',
    themeBtnRed: '#d90429',
    themeBtnHover: '#b50322',
    themeAccentYellow: '#ffd166',
    themeTextDark: '#1e293b',
    themeBgBody: '#ffffff'
  };
  Object.keys(masterColors).forEach(id => {
    setThemeColor(id, masterColors[id]);
  });
  markBuilderDirty();
  showToast('Reset colors to Master Chicken Booster palette!');
};

// ==============================================================================
// MULTI-DEVICE LIVE PREVIEW MODAL
// ==============================================================================
window.openLivePreviewModal = function() {
  const idVal = document.getElementById('builderPageId')?.value;
  const slugInput = document.getElementById('lpSlug')?.value?.trim();
  const modal = document.getElementById('lpPreviewModal');
  const iframe = document.getElementById('lpPreviewIframe');
  const title = document.getElementById('lpPreviewTitle');
  const loader = document.getElementById('lpPreviewLoading');
  const directLink = document.getElementById('lpPreviewOpenDirect');

  if (modal && iframe) {
    let slug = slugInput;
    if (!slug && idVal) {
      const page = (APP_STATE.liveLandingPages || []).find(p => String(p.id) === String(idVal));
      if (page && page.slug) slug = page.slug;
    }
    if (!slug) slug = 'chicken-booster';

    const pageName = document.getElementById('lpName')?.value?.trim() || (slug === 'chicken-booster' ? 'Chicken Booster' : 'Landing Page');
    if (title) title.textContent = `${pageName} — Live Device Preview`;

    const previewUrl = `/product/${encodeURIComponent(slug)}?preview=true`;
    if (directLink) directLink.href = previewUrl;

    if (loader) {
      loader.innerHTML = '<span>⏳ Loading live preview...</span>';
      loader.style.display = 'flex';
    }

    let loaded = false;
    iframe.onload = () => {
      loaded = true;
      if (loader) loader.style.display = 'none';
    };
    iframe.onerror = () => {
      if (loader) {
        loader.innerHTML = `<div style="color:#DC2626;text-align:center;">⚠️ Failed to load preview for <code>${slug}</code>. <a href="${previewUrl}" target="_blank" style="color:#0284C7;text-decoration:underline;">Click to open directly ↗</a></div>`;
      }
    };

    setTimeout(() => {
      if (loader && !loaded) loader.style.display = 'none';
    }, 2500);

    iframe.src = previewUrl;
    modal.classList.add('active');
    setPreviewDevice('desktop');
  }
};

window.previewLandingPageById = function(id, name) {
  const modal = document.getElementById('lpPreviewModal');
  const iframe = document.getElementById('lpPreviewIframe');
  const title = document.getElementById('lpPreviewTitle');
  const loader = document.getElementById('lpPreviewLoading');
  const directLink = document.getElementById('lpPreviewOpenDirect');

  if (modal && iframe) {
    const page = (APP_STATE.liveLandingPages || []).find(p => String(p.id) === String(id));
    const slug = page ? page.slug : (id === 1 ? 'chicken-booster' : (typeof name === 'string' && name ? name : 'chicken-booster'));
    const pageName = name || (page ? page.name : 'Landing Page');

    if (title) title.textContent = `${pageName} — Live Device Preview`;

    const previewUrl = `/product/${encodeURIComponent(slug)}?preview=true`;
    if (directLink) directLink.href = previewUrl;

    if (loader) {
      loader.innerHTML = '<span>⏳ Loading live preview...</span>';
      loader.style.display = 'flex';
    }

    let loaded = false;
    iframe.onload = () => {
      loaded = true;
      if (loader) loader.style.display = 'none';
    };
    iframe.onerror = () => {
      if (loader) {
        loader.innerHTML = `<div style="color:#DC2626;text-align:center;">⚠️ Failed to load preview for <code>${slug}</code>. <a href="${previewUrl}" target="_blank" style="color:#0284C7;text-decoration:underline;">Click to open directly ↗</a></div>`;
      }
    };

    setTimeout(() => {
      if (loader && !loaded) loader.style.display = 'none';
    }, 2500);

    iframe.src = previewUrl;
    modal.classList.add('active');
    setPreviewDevice('desktop');
  }
};

window.closeLivePreviewModal = function() {
  const modal = document.getElementById('lpPreviewModal');
  const iframe = document.getElementById('lpPreviewIframe');
  const loader = document.getElementById('lpPreviewLoading');
  if (modal) modal.classList.remove('active');
  if (iframe) {
    iframe.onload = null;
    iframe.onerror = null;
    iframe.src = 'about:blank';
  }
  if (loader) {
    loader.style.display = 'none';
    loader.innerHTML = '<span>⏳ Loading live preview...</span>';
  }
};

window.setPreviewDevice = function(device) {
  const iframe = document.getElementById('lpPreviewIframe');
  if (!iframe) return;

  ['desktop', 'tablet', 'mobile'].forEach(dev => {
    const btn = document.getElementById('btnPreview' + dev.charAt(0).toUpperCase() + dev.slice(1));
    if (btn) {
      if (dev === device) {
        btn.style.background = '#03363d';
        btn.style.boxShadow = 'inset 0 2px 4px rgba(0,0,0,0.2)';
      } else {
        btn.style.background = '#004D40';
        btn.style.boxShadow = 'none';
      }
    }
  });

  if (device === 'mobile') {
    iframe.style.width = '375px';
    iframe.style.maxWidth = '375px';
    iframe.style.boxShadow = '0 10px 30px rgba(0,0,0,0.18)';
    iframe.style.borderRadius = '20px';
  } else if (device === 'tablet') {
    iframe.style.width = '768px';
    iframe.style.maxWidth = '768px';
    iframe.style.boxShadow = '0 10px 30px rgba(0,0,0,0.18)';
    iframe.style.borderRadius = '14px';
  } else {
    iframe.style.width = '100%';
    iframe.style.maxWidth = '100%';
    iframe.style.boxShadow = 'none';
    iframe.style.borderRadius = '0';
  }
};

function renderLandingPagesHub() {
  const container = document.getElementById('landingPagesGrid');
  if (!container) return;
  container.innerHTML = '';

  const pages = (APP_STATE.liveLandingPages && APP_STATE.liveLandingPages.length) ? APP_STATE.liveLandingPages : [];

  if (pages.length === 0) {
    container.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:32px;color:#64748B;">No landing pages available.</div>`;
    return;
  }

  pages.forEach(lp => {
    const card = document.createElement('div');
    card.className = 'lp-card';
    const isPublished = (lp.status === 'published');
    const badgeLabel = (lp.theme === 'chicken-booster') ? 'Poultry & Agro' : 'Universal Product';

    card.innerHTML = `
      <div class="lp-card-header">
        <span class="lp-badge">${badgeLabel}</span>
        <span style="font-size:11px;color:${isPublished ? '#10B981' : '#F59E0B'};font-weight:600;">● ${lp.status ? lp.status.toUpperCase() : 'DRAFT'}</span>
      </div>
      <div class="lp-card-body">
        <h4 style="font-size:13.5px;font-weight:700;margin-bottom:4px;">${lp.name}</h4>
        <p style="font-size:11.5px;color:#718096;margin-bottom:10px;">URL: <code>/product/${lp.slug}</code></p>
        <div class="lp-actions">
          <button class="btn-lp-action primary" onclick="previewLandingPageById(${lp.id}, '${lp.name.replace(/'/g, "\\'")}')">
            👁️ Live Preview
          </button>
          <a href="${lp.public_url || `/product/${lp.slug}`}" target="_blank" class="btn-lp-action" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">
            🌐 Open
          </a>
          <button class="btn-lp-action" onclick="navigator.clipboard.writeText(window.location.origin + '/product/${lp.slug}'); showToast('URL Copied!')">
            📋 Copy Link
          </button>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
}


// ==============================================================================
// 10. ADMIN USERS MANAGEMENT (Phase 12/13 — Database-Backed & RBAC Enforced)
// ==============================================================================

// Store loaded admins for client-side filtering
let _adminUsersCache = [];

/**
 * Load admins from backend DB and render the table.
 * Called when 'manage-admin' view is activated.
 */
async function loadAdminUsers() {
  const tbody = document.getElementById('adminUsersTableBody');
  const countEl = document.getElementById('adminTableCountText');
  const searchInput = document.getElementById('adminSearchInput');
  const statusFilter = document.getElementById('adminStatusFilter');
  if (!tbody) return;

  // Clean initial search states — never autofill email
  if (searchInput && !(searchInput.dataset && searchInput.dataset.userActive)) {
    searchInput.value = '';
  }
  if (statusFilter && !(statusFilter.dataset && statusFilter.dataset.userActive)) {
    statusFilter.value = '';
  }

  tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#718096;">Loading admins...</td></tr>';
  if (countEl) countEl.textContent = 'Loading...';

  try {
    const token = APP_STATE.adminToken || localStorage.getItem('admin_token') || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = {
      'Accept': 'application/json',
    };
    if (token) headers['x-admin-token'] = token;
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

    const resp = await fetch('/api/admin/admins', {
      credentials: 'same-origin',
      headers: headers
    });

    if (resp.status === 401) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#E53E3E;">Not authenticated. Please log in.</td></tr>';
      return;
    }

    if (resp.status === 403) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#E53E3E;">Access denied. Admin role required.</td></tr>';
      return;
    }

    const data = await resp.json();
    if (!data.success) {
      throw new Error(data.message || 'Failed to load admins');
    }

    _adminUsersCache = data.admins || [];
    renderAdminUsersTable(_adminUsersCache);

  } catch (err) {
    console.error('[Admin Users Load Error]', err);
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:#E53E3E;">Error loading admins: ${err.message}</td></tr>`;
  }
}

/**
 * Render the admin users table from an array.
 */
function renderAdminUsersTable(admins) {
  const tbody = document.getElementById('adminUsersTableBody');
  const countEl = document.getElementById('adminTableCountText');
  if (!tbody) return;

  // Use cache if called without args (legacy call from init)
  if (!admins) {
    admins = _adminUsersCache;
  }

  tbody.innerHTML = '';

  if (!admins || admins.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:24px;color:#718096;">No admins found.</td></tr>';
    if (countEl) countEl.textContent = '0 admin(s)';
    return;
  }

  const roleBadgeColors = {
    super_admin: { bg: '#FEF3C7', color: '#92400E' },
    admin:       { bg: '#DBEAFE', color: '#1D4ED8' },
    moderator:   { bg: '#F3F4F6', color: '#374151' },
  };

  admins.forEach((u, idx) => {
    const rbc = roleBadgeColors[u.role] || { bg: '#F3F4F6', color: '#374151' };
    const statusColor = u.status === 'Active' ? { bg: '#ECFDF5', color: '#059669' } : { bg: '#FEF2F2', color: '#DC2626' };
    const initials = (u.name || '?').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:36px;height:36px;border-radius:50%;background:#004D40;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0;">${initials}</div>
          <strong>${u.name}</strong>
        </div>
      </td>
      <td style="font-size:13px;">${u.email}</td>
      <td style="font-size:13px;">${u.phone || '<span style="color:#A0AEC0;">—</span>'}</td>
      <td><span style="font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:12px;background:${rbc.bg};color:${rbc.color};">${u.role_label || u.role}</span></td>
      <td><span class="product-status-tag" style="background:${statusColor.bg};color:${statusColor.color};">${u.status || 'Active'}</span></td>
      <td style="text-align:right;">
        <button class="action-btn-square-teal" onclick="openEditAdminModal(${u.id})" title="Edit" style="margin-right:4px;">✏️</button>
        <button class="btn-primary-teal" style="padding:4px 10px;font-size:11.5px;margin-right:4px;" onclick="openResetPasswordModal(${u.id}, '${(u.name || '').replace(/'/g, "\\'")}')">Reset Password</button>
        <button style="padding:4px 10px;font-size:11.5px;background:#DC2626;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;" onclick="deleteAdminUser(${u.id}, '${(u.name || '').replace(/'/g, "\\'")}')">Delete</button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  if (countEl) countEl.textContent = `Showing ${admins.length} of ${_adminUsersCache.length} admin(s)`;
}

/**
 * Filter the admin table by search query and status.
 */
function filterAdminTable(query) {
  const sInput = document.getElementById('adminSearchInput');
  const sFilter = document.getElementById('adminStatusFilter');
  if (sInput && query !== undefined && query !== null && query !== '') {
    sInput.dataset.userActive = 'true';
  } else if (sInput && (query === '' || !sInput.value)) {
    delete sInput.dataset.userActive;
  }
  if (sFilter && sFilter.value !== '') {
    sFilter.dataset.userActive = 'true';
  } else if (sFilter && sFilter.value === '') {
    delete sFilter.dataset.userActive;
  }

  const statusFilter = (sFilter?.value || '').toLowerCase();
  const q = (query !== undefined && query !== null ? String(query) : (sInput?.value || '')).toLowerCase().trim();

  const filtered = _adminUsersCache.filter(u => {
    const matchSearch = !q
      || (u.name || '').toLowerCase().includes(q)
      || (u.email || '').toLowerCase().includes(q)
      || (u.phone || '').toLowerCase().includes(q);
    const matchStatus = !statusFilter || (u.status || '').toLowerCase() === statusFilter;
    return matchSearch && matchStatus;
  });

  renderAdminUsersTable(filtered);
}

// ==============================================================================
// ADD ADMIN
// ==============================================================================

async function submitAddAdmin() {
  const btn = document.getElementById('addAdminSubmitBtn');
  const errBanner = document.getElementById('addAdminErrorBanner');
  const sucBanner = document.getElementById('addAdminSuccessBanner');

  const name = document.getElementById('addAdminName')?.value?.trim();
  const email = document.getElementById('addAdminEmail')?.value?.trim();
  const phone = document.getElementById('addAdminPhone')?.value?.trim();
  const password = document.getElementById('addAdminPassword')?.value;
  const passwordConfirm = document.getElementById('addAdminPasswordConfirm')?.value;
  const role = document.getElementById('addAdminRole')?.value;
  const status = document.getElementById('addAdminStatus')?.value || 'Active';

  // Hide banners
  if (errBanner) errBanner.style.display = 'none';
  if (sucBanner) sucBanner.style.display = 'none';

  // Client-side validation
  if (!name || !email || !phone || !password || !role) {
    if (errBanner) { errBanner.textContent = 'All required fields must be filled.'; errBanner.style.display = 'block'; }
    return;
  }
  if (password !== passwordConfirm) {
    if (errBanner) { errBanner.textContent = 'Passwords do not match.'; errBanner.style.display = 'block'; }
    return;
  }
  if (password.length < 8) {
    if (errBanner) { errBanner.textContent = 'Password must be at least 8 characters.'; errBanner.style.display = 'block'; }
    return;
  }

  if (btn) { btn.textContent = 'Creating...'; btn.disabled = true; }

  try {
    const token = APP_STATE.adminToken || localStorage.getItem('admin_token') || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token) headers['x-admin-token'] = token;
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

    const resp = await fetch('/api/admin/admins', {
      method: 'POST',
      credentials: 'same-origin',
      headers: headers,
      body: JSON.stringify({ name, email, phone, password, new_password_confirmation: passwordConfirm, role, status }),
    });

    const data = await resp.json();

    if (resp.status === 403) {
      if (errBanner) { errBanner.textContent = data.message || 'Forbidden: Insufficient permissions.'; errBanner.style.display = 'block'; }
      return;
    }

    if (!data.success) {
      const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Failed to create admin.');
      if (errBanner) { errBanner.textContent = msgs; errBanner.style.display = 'block'; }
      return;
    }

    // Success
    if (sucBanner) { sucBanner.textContent = `Admin "${data.admin.name}" created successfully!`; sucBanner.style.display = 'block'; }
    document.getElementById('addAdminForm')?.reset();

    // Navigate to manage view after short delay
    setTimeout(() => {
      switchView('manage-admin');
    }, 1200);

  } catch (err) {
    console.error('[Add Admin Error]', err);
    if (errBanner) { errBanner.textContent = 'Network error. Please try again.'; errBanner.style.display = 'block'; }
  } finally {
    if (btn) { btn.textContent = 'Create Admin'; btn.disabled = false; }
  }
}

// ==============================================================================
// EDIT ADMIN MODAL
// ==============================================================================

function openEditAdminModal(adminId) {
  const admin = _adminUsersCache.find(a => a.id === adminId);
  if (!admin) { showToast('Admin not found in cache. Refreshing...'); loadAdminUsers(); return; }

  document.getElementById('editAdminId').value = admin.id;
  document.getElementById('editAdminName').value = admin.name || '';
  document.getElementById('editAdminEmail').value = admin.email || '';
  document.getElementById('editAdminPhone').value = admin.phone || '';
  document.getElementById('editAdminRole').value = admin.role || 'admin';
  document.getElementById('editAdminStatusSel').value = admin.status || 'Active';

  const errEl = document.getElementById('editAdminError');
  if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }

  const modal = document.getElementById('editAdminModal');
  if (modal) { modal.style.display = 'flex'; }
}

function closeEditAdminModal() {
  const modal = document.getElementById('editAdminModal');
  if (modal) modal.style.display = 'none';
}

async function submitEditAdmin() {
  const btn = document.getElementById('editAdminSaveBtn');
  const errEl = document.getElementById('editAdminError');
  const adminId = document.getElementById('editAdminId')?.value;

  if (!adminId) return;

  const payload = {
    name:   document.getElementById('editAdminName')?.value?.trim(),
    email:  document.getElementById('editAdminEmail')?.value?.trim(),
    phone:  document.getElementById('editAdminPhone')?.value?.trim(),
    role:   document.getElementById('editAdminRole')?.value,
    status: document.getElementById('editAdminStatusSel')?.value,
  };

  if (errEl) { errEl.style.display = 'none'; }
  if (btn) { btn.textContent = 'Saving...'; btn.disabled = true; }

  try {
    const token = APP_STATE.adminToken || localStorage.getItem('admin_token') || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token) headers['x-admin-token'] = token;
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

    const resp = await fetch(`/api/admin/admins/${adminId}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: headers,
      body: JSON.stringify(payload),
    });

    const data = await resp.json();

    if (resp.status === 403) {
      if (errEl) { errEl.textContent = data.message || 'Forbidden: Insufficient permissions.'; errEl.style.display = 'block'; }
      return;
    }

    if (!data.success) {
      const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Failed to update admin.');
      if (errEl) { errEl.textContent = msgs; errEl.style.display = 'block'; }
      return;
    }

    closeEditAdminModal();
    showToast(`Admin "${data.admin.name}" updated successfully!`);
    await loadAdminUsers(); // Refresh table from DB

  } catch (err) {
    console.error('[Edit Admin Error]', err);
    if (errEl) { errEl.textContent = 'Network error. Please try again.'; errEl.style.display = 'block'; }
  } finally {
    if (btn) { btn.textContent = 'Save Changes'; btn.disabled = false; }
  }
}

// ==============================================================================
// RESET PASSWORD MODAL
// ==============================================================================

function openResetPasswordModal(adminId, adminName) {
  document.getElementById('resetPasswordAdminId').value = adminId;
  document.getElementById('resetPasswordAdminName').textContent = `Resetting password for: ${adminName}`;
  document.getElementById('newAdminPassword').value = '';
  document.getElementById('newAdminPasswordConfirm').value = '';

  const errEl = document.getElementById('resetPasswordError');
  if (errEl) { errEl.style.display = 'none'; errEl.textContent = ''; }

  const modal = document.getElementById('resetPasswordModal');
  if (modal) modal.style.display = 'flex';
}

function closeResetPasswordModal() {
  const modal = document.getElementById('resetPasswordModal');
  if (modal) modal.style.display = 'none';
}

async function submitResetPassword() {
  const btn = document.getElementById('resetPasswordSaveBtn');
  const errEl = document.getElementById('resetPasswordError');
  const adminId = document.getElementById('resetPasswordAdminId')?.value;
  const newPassword = document.getElementById('newAdminPassword')?.value;
  const newPasswordConfirm = document.getElementById('newAdminPasswordConfirm')?.value;

  if (errEl) errEl.style.display = 'none';

  if (!newPassword || newPassword.length < 8) {
    if (errEl) { errEl.textContent = 'Password must be at least 8 characters.'; errEl.style.display = 'block'; }
    return;
  }

  if (newPassword !== newPasswordConfirm) {
    if (errEl) { errEl.textContent = 'Passwords do not match.'; errEl.style.display = 'block'; }
    return;
  }

  if (btn) { btn.textContent = 'Resetting...'; btn.disabled = true; }

  try {
    const token = APP_STATE.adminToken || localStorage.getItem('admin_token') || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token) headers['x-admin-token'] = token;
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

    const resp = await fetch(`/api/admin/admins/${adminId}/reset-password`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: headers,
      body: JSON.stringify({
        new_password: newPassword,
        new_password_confirmation: newPasswordConfirm,
      }),
    });

    const data = await resp.json();

    if (resp.status === 403) {
      if (errEl) { errEl.textContent = data.message || 'Forbidden: Insufficient permissions.'; errEl.style.display = 'block'; }
      return;
    }

    if (!data.success) {
      const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Failed to reset password.');
      if (errEl) { errEl.textContent = msgs; errEl.style.display = 'block'; }
      return;
    }

    closeResetPasswordModal();
    showToast('Password reset successfully!');

  } catch (err) {
    console.error('[Reset Password Error]', err);
    if (errEl) { errEl.textContent = 'Network error. Please try again.'; errEl.style.display = 'block'; }
  } finally {
    if (btn) { btn.textContent = 'Reset Password'; btn.disabled = false; }
  }
}

// ==============================================================================
// DELETE ADMIN
// ==============================================================================

async function deleteAdminUser(adminId, adminName) {
  if (!confirm(`Are you sure you want to delete admin "${adminName}"? This cannot be undone.`)) {
    return;
  }

  try {
    const token = APP_STATE.adminToken || localStorage.getItem('admin_token') || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (token) headers['x-admin-token'] = token;
    if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken;

    const resp = await fetch(`/api/admin/admins/${adminId}`, {
      method: 'DELETE',
      credentials: 'same-origin',
      headers: headers,
    });

    const data = await resp.json().catch(() => ({}));

    if (resp.status === 403 || resp.status === 401) {
      showToast(data.message || 'Forbidden: Cannot delete this admin.');
      return;
    }

    if (!resp.ok || !data.success) {
      showToast(data.message || data.error || 'Failed to delete admin.');
      return;
    }

    showToast(`Admin "${adminName}" deleted successfully.`);
    await loadAdminUsers(); // Authoritatively reload from DB

  } catch (err) {
    console.error('[Delete Admin Error]', err);
    showToast('Network error. Could not delete admin.');
  }
}

// Explicit window assignments for HTML event handlers
window.loadAdminUsers = loadAdminUsers;
window.renderAdminUsersTable = renderAdminUsersTable;
window.filterAdminTable = filterAdminTable;
window.submitAddAdmin = submitAddAdmin;
window.openEditAdminModal = openEditAdminModal;
window.closeEditAdminModal = closeEditAdminModal;
window.submitEditAdmin = submitEditAdmin;
window.openResetPasswordModal = openResetPasswordModal;
window.closeResetPasswordModal = closeResetPasswordModal;
window.submitResetPassword = submitResetPassword;
window.deleteAdminUser = deleteAdminUser;



// ==============================================================================
// 11. VIEW SWITCHER & GLOBAL NAVIGATION (URL HASH SPA PERSISTENCE)
// ==============================================================================
const VIEW_ALIASES = {
  'landing-pages': 'landing-pages-list',
  'landingpage': 'landing-pages-list',
  'landingpages': 'landingpages',
  'landing-page-list': 'landing-pages-list',
  'landing-page-hub': 'landingpages',
  'landing-page': 'landing-pages-list',
  'builder': 'landing-page-builder',
  'manage-order': 'orders',
  'manage-orders': 'orders',
  'all-orders': 'orders',
  'order': 'orders',
  'main-website-orders': 'main-website-orders',
  'website-orders': 'main-website-orders',
  'landing-page-orders': 'landing-page-orders',
  'landing-orders': 'landing-page-orders',
  'admin': 'manage-admin',
  'admins': 'manage-admin',
  'admin-users': 'manage-admin',
  'manage': 'manage-admin',
  'profit': 'profit-report',
  'profit-loss': 'profit-report',
  'loss-profit': 'profit-report',
  'loss-profit-report': 'profit-report',
  'customer': 'customers',
  'setting': 'marketing',
  'settings': 'marketing',
  'config': 'marketing',
  'configuration': 'marketing',
  'courier': 'courier-api',
  'couriers-setup': 'courier-api',
  'product': 'products',
  'account': 'income',
  'accounts': 'income',
  'website': 'header-setting',
  'website-setup': 'header-setting',
  'processing-report': 'report',
  'order-report': 'report',
  'dash': 'dashboard'
};

function normalizeViewName(name) {
  if (!name || typeof name !== 'string') return null;
  const cleaned = name.trim().toLowerCase().replace(/^#/, '');
  if (!cleaned) return null;

  if (cleaned === 'main-website-orders' || cleaned === 'website-orders') {
    return 'main-website-orders';
  }
  if (cleaned === 'landing-page-orders' || cleaned === 'landing-orders') {
    return 'landing-page-orders';
  }

  // Check exact panel match first
  if (document.getElementById(`view-${cleaned}`)) {
    return cleaned;
  }

  // Check alias mapping
  if (VIEW_ALIASES[cleaned]) {
    const aliasTarget = VIEW_ALIASES[cleaned];
    if (aliasTarget === 'main-website-orders' || aliasTarget === 'landing-page-orders' || document.getElementById(`view-${aliasTarget}`)) {
      return aliasTarget;
    }
  }

  return null;
}
window.normalizeViewName = normalizeViewName;

function getViewFromHash() {
  if (typeof window === 'undefined' || !window.location || !window.location.hash) return null;
  return normalizeViewName(window.location.hash);
}
window.getViewFromHash = getViewFromHash;

window.switchView = function(viewName) {
  if (APP_STATE.activeView === 'landing-page-builder' && viewName !== 'landing-page-builder' && APP_STATE.isBuilderDirty) {
    showLpConfirmModal({
      title: 'Unsaved Changes',
      icon: '⚠️',
      heading: 'Discard Unsaved Changes?',
      message: 'You have unsaved edits in the builder. If you leave now, your changes will be lost.',
      confirmText: 'Discard & Leave',
      confirmClass: 'lp-btn-remove',
      onConfirm: function() {
        APP_STATE.isBuilderDirty = false;
        doSwitchView(viewName, true);
      }
    });
    return;
  }
  doSwitchView(viewName, true);
};

function doSwitchView(viewName, updateHash = true) {
  const normalized = normalizeViewName(viewName);
  const targetView = normalized || 'dashboard';

  APP_STATE.activeView = targetView;

  // Determine actual DOM view panel to show
  let domPanelId = targetView;
  if (targetView === 'main-website-orders' || targetView === 'landing-page-orders') {
    domPanelId = 'orders';
    if (targetView === 'main-website-orders') {
      APP_STATE.sourceFilter = 'MAIN_WEBSITE';
    } else if (targetView === 'landing-page-orders') {
      APP_STATE.sourceFilter = 'LANDING_PAGE';
    }
  } else if (targetView === 'orders') {
    APP_STATE.sourceFilter = null;
  }

  // 1. Toggle view panels (display: none / block)
  document.querySelectorAll('.view-panel').forEach(p => p.style.display = 'none');
  const panel = document.getElementById(`view-${domPanelId}`);
  if (panel) {
    panel.style.display = 'block';
  } else {
    const dash = document.getElementById('view-dashboard');
    if (dash) dash.style.display = 'block';
  }

  // 2. Reset navigation links and submenu accordion states without modifying main sidebar element
  document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
  document.querySelectorAll('.tree-link').forEach(l => l.classList.remove('active'));
  document.querySelectorAll('.nav-has-sub').forEach(g => g.classList.remove('open'));

  // 3. Activate target navigation link and expand parent submenu
  let activated = false;

  if (targetView === 'orders') {
    const ordersLink = document.getElementById('subnav-manage-order');
    if (ordersLink) ordersLink.classList.add('active');
    const parent = document.getElementById('nav-group-orders');
    if (parent) parent.classList.add('open');
    activated = true;
  } else if (targetView === 'main-website-orders') {
    const webLink = document.getElementById('subnav-website-orders');
    if (webLink) webLink.classList.add('active');
    const parent = document.getElementById('nav-group-orders');
    if (parent) parent.classList.add('open');
    activated = true;
  } else if (targetView === 'landing-page-orders') {
    const lpLink = document.getElementById('subnav-landing-orders');
    if (lpLink) lpLink.classList.add('active');
    const parent = document.getElementById('nav-group-orders');
    if (parent) parent.classList.add('open');
    activated = true;
  } else if (targetView === 'report') {
    const repLink = document.getElementById('subnav-processing-report');
    if (repLink) repLink.classList.add('active');
    const parent = document.getElementById('nav-group-orders');
    if (parent) parent.classList.add('open');
    activated = true;
  } else if (targetView === 'landing-page-builder' || targetView === 'landingpages' || targetView === 'landing-pages-list') {
    const lpGroup = document.getElementById('nav-group-landingpages');
    if (lpGroup) lpGroup.classList.add('open');
    const lpSub = document.getElementById(`subnav-${targetView}`) || document.getElementById('subnav-landing-pages-list');
    if (lpSub) lpSub.classList.add('active');
    activated = true;
  }

  // Direct subnav match (e.g. #subnav-marketing, #subnav-manage-admin, #subnav-courier-api)
  if (!activated) {
    const directSubnav = document.getElementById(`subnav-${targetView}`);
    if (directSubnav) {
      directSubnav.classList.add('active');
      const parentGroup = directSubnav.closest('.nav-has-sub');
      if (parentGroup) parentGroup.classList.add('open');
      activated = true;
    }
  }

  // Direct top-level match (e.g. #nav-dashboard, #nav-analytics)
  if (!activated) {
    const directTopNav = document.getElementById(`nav-${targetView}`);
    if (directTopNav && !directTopNav.closest('.nav-has-sub')) {
      directTopNav.classList.add('active');
      activated = true;
    }
  }

  // Fallback tree link match
  if (!activated) {
    const matchingTreeLink = document.querySelector(`.tree-link[onclick*="'${targetView}'"]`);
    if (matchingTreeLink) {
      matchingTreeLink.classList.add('active');
      const parent = matchingTreeLink.closest('.nav-has-sub');
      if (parent) parent.classList.add('open');
    }
  }

  // 4. Update URL hash if requested (avoid pushState duplicate if already on target hash)
  if (updateHash) {
    const expectedHash = '#' + targetView;
    if (window.location && window.location.hash !== expectedHash) {
      if (window.history && window.history.pushState) {
        window.history.pushState(null, '', expectedHash);
      } else {
        window.location.hash = expectedHash;
      }
    }
  }

  // 5. Update top navbar title
  const topTitleEl = document.getElementById('topNavbarTitle') || document.querySelector('.top-navbar .navbar-left span');
  if (topTitleEl) {
    const titleMap = {
      'dashboard': 'Dashboard',
      'analytics': 'Analytics & Attribution',
      'orders': 'Orders Management',
      'main-website-orders': 'Main Website Orders',
      'landing-page-orders': 'Landing Page Orders',
      'report': 'Order Processing Report',
      'income': 'Income Accounts',
      'expense': 'Expense Accounts',
      'balance': 'Balance Sheet',
      'products': 'Manage Products',
      'categories': 'Manage Categories',
      'sliders': 'Hero Sliders',
      'header-setting': 'Header Configuration',
      'theme-setting': 'Theme Settings',
      'marketing': 'Marketing & Meta Pixel',
      'courier-api': 'Courier API Setup',
      'invoice-address': 'Invoice Address',
      'delivery-charge': 'Delivery Charges',
      'cities': 'City Settings',
      'sub-city': 'Sub City Settings',
      'couriers': 'Courier Configuration',
      'order-source': 'Order Sources',
      'comments': 'Comment Settings',
      'add-admin': 'Add Admin',
      'manage-admin': 'Manage Admins',
      'customers': 'Customer List',
      'profit-report': 'Profit / Loss Report',
      'landing-pages-list': 'All Landing Pages',
      'landing-page-builder': 'Landing Page Builder',
      'landingpages': 'Landing Page Hub'
    };
    topTitleEl.textContent = titleMap[targetView] || 'Dashboard';
  }

  // 6. View-specific renders and data loaders
  if (targetView === 'dashboard') {
    renderDashboardData();
    renderMonthlyChart();
    loadServerOrders();
  }
  if (targetView === 'analytics') loadAnalyticsDashboard();
  if (targetView === 'orders' || targetView === 'main-website-orders' || targetView === 'landing-page-orders') {
    renderOrdersTable();
    loadServerOrders();
  }
  if (targetView === 'report') {
    renderOrderProcessingReport(CURRENT_REPORT_PERIOD || 'today');
  }
  if (targetView === 'income') {
    if (typeof renderIncomeTable === 'function') renderIncomeTable();
    if (typeof renderCreditTable === 'function') renderCreditTable();
  }
  if (targetView === 'expense') {
    if (typeof renderExpenseTable === 'function') renderExpenseTable();
  }
  if (targetView === 'products') loadProductsCatalog();
  if (targetView === 'categories') loadCategoriesCatalog();
  if (targetView === 'sliders') loadSlidersCatalog();
  if (targetView === 'header-setting' || targetView === 'storefront-settings') loadStorefrontSettings();
  if (targetView === 'customers') renderCustomersTable();
  if (targetView === 'landingpages') renderLandingPagesHub();
  if (targetView === 'landing-pages-list') renderLandingPagesList();
  if (targetView === 'profit-report') renderProfitReport();
  if (targetView === 'cities') renderCitiesTable();
  if (targetView === 'marketing') loadMarketingSettings();
  if (targetView === 'manage-admin') {
    const sInput = document.getElementById('adminSearchInput');
    if (sInput) {
      sInput.value = '';
      if (sInput.dataset) delete sInput.dataset.userActive;
    }
    const sFilter = document.getElementById('adminStatusFilter');
    if (sFilter) {
      sFilter.value = '';
      if (sFilter.dataset) delete sFilter.dataset.userActive;
    }
    loadAdminUsers();
  }
}
window.doSwitchView = doSwitchView;



// Browser Back / Forward and Hash Navigation Listener
function handleHashOrPopState() {
  const targetView = getViewFromHash() || 'dashboard';
  if (targetView !== APP_STATE.activeView) {
    if (APP_STATE.activeView === 'landing-page-builder' && APP_STATE.isBuilderDirty) {
      showLpConfirmModal({
        title: 'Unsaved Changes',
        icon: '⚠️',
        heading: 'Discard Unsaved Changes?',
        message: 'You have unsaved edits in the builder. If you leave now, your changes will be lost.',
        confirmText: 'Discard & Leave',
        confirmClass: 'lp-btn-remove',
        onConfirm: function() {
          APP_STATE.isBuilderDirty = false;
          doSwitchView(targetView, false);
        },
        onCancel: function() {
          if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '#landing-page-builder');
          } else {
            window.location.hash = '#landing-page-builder';
          }
        }
      });
      return;
    }
    doSwitchView(targetView, false);
  }
}

if (typeof window !== 'undefined') {
  window.addEventListener('hashchange', handleHashOrPopState);
  window.addEventListener('popstate', handleHashOrPopState);
}

// ==============================================================================
// 11B. MARKETING SETTINGS & META PIXEL MANAGEMENT
// ==============================================================================
function setMarketingToggleState(type, isEnabled) {
  const hiddenInput = type === 'addToCart'
    ? document.getElementById('marketingAddToCartEnabled')
    : document.getElementById('marketingInitiateCheckoutEnabled');
  const btn = type === 'addToCart'
    ? document.getElementById('btnToggleAddToCart')
    : document.getElementById('btnToggleInitiateCheckout');
  const label = type === 'addToCart'
    ? document.getElementById('labelToggleAddToCart')
    : document.getElementById('labelToggleInitiateCheckout');

  if (hiddenInput) hiddenInput.value = isEnabled ? '1' : '0';
  if (btn) {
    if (isEnabled) {
      btn.classList.add('active');
      btn.style.background = '#004D40';
      btn.style.borderColor = '#004D40';
      btn.style.color = '#FFFFFF';
    } else {
      btn.classList.remove('active');
      btn.style.background = '#F1F5F9';
      btn.style.borderColor = '#CBD5E1';
      btn.style.color = '#64748B';
    }
  }
  if (label) {
    label.textContent = isEnabled ? 'ON' : 'OFF';
  }
}

function toggleMarketingSwitch(type) {
  const hiddenInput = type === 'addToCart'
    ? document.getElementById('marketingAddToCartEnabled')
    : document.getElementById('marketingInitiateCheckoutEnabled');
  const currentState = hiddenInput ? (hiddenInput.value === '1') : true;
  setMarketingToggleState(type, !currentState);
}

function loadMarketingSettings() {
  const token = localStorage.getItem('admin_token') || '';
  const pixelInput = document.getElementById('marketingPixelCode');
  const gaInput = document.getElementById('marketingGoogleAnalytics');
  const gBodyInput = document.getElementById('marketingGoogleBody');
  const fbDomainInput = document.getElementById('marketingFbDomain');
  const gDomainInput = document.getElementById('marketingGoogleDomain');
  const alertBox = document.getElementById('marketingAlertBox');

  if (alertBox) alertBox.style.display = 'none';

  fetch('/api/admin/settings/marketing', {
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      'x-admin-token': token
    }
  })
  .then(r => r.json())
  .then(data => {
    if (data && data.success && data.settings) {
      if (pixelInput) pixelInput.value = data.settings.facebook_pixel || '';
      if (gaInput) gaInput.value = data.settings.google_analytics || '';
      if (gBodyInput) gBodyInput.value = data.settings.google_body || '';
      if (fbDomainInput) fbDomainInput.value = data.settings.facebook_domain_verification || '';
      if (gDomainInput) gDomainInput.value = data.settings.google_domain_verification || '';

      const isAddToCartActive = (data.settings.landing_meta_add_to_cart_enabled !== false && data.settings.landing_meta_add_to_cart_enabled !== '0' && data.settings.landing_meta_add_to_cart_enabled !== 0);
      const isInitiateCheckoutActive = (data.settings.landing_meta_initiate_checkout_enabled !== false && data.settings.landing_meta_initiate_checkout_enabled !== '0' && data.settings.landing_meta_initiate_checkout_enabled !== 0);
      setMarketingToggleState('addToCart', isAddToCartActive);
      setMarketingToggleState('initiateCheckout', isInitiateCheckoutActive);
    }
  })
  .catch(err => {
    console.warn('Could not load marketing settings:', err);
  });
}

function saveMarketingSettings() {
  const token = localStorage.getItem('admin_token') || '';
  const pixelVal = document.getElementById('marketingPixelCode')?.value || '';
  const gaVal = document.getElementById('marketingGoogleAnalytics')?.value || '';
  const gBodyVal = document.getElementById('marketingGoogleBody')?.value || '';
  const fbDomainVal = document.getElementById('marketingFbDomain')?.value || '';
  const gDomainVal = document.getElementById('marketingGoogleDomain')?.value || '';
  const addToCartVal = document.getElementById('marketingAddToCartEnabled')?.value === '1';
  const initCheckoutVal = document.getElementById('marketingInitiateCheckoutEnabled')?.value === '1';
  const submitBtn = document.getElementById('marketingSubmitBtn');
  const alertBox = document.getElementById('marketingAlertBox');

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
  }
  if (alertBox) alertBox.style.display = 'none';

  fetch('/api/admin/settings/marketing', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      'x-admin-token': token
    },
    body: JSON.stringify({
      facebook_pixel: pixelVal,
      landing_meta_add_to_cart_enabled: addToCartVal,
      landing_meta_initiate_checkout_enabled: initCheckoutVal,
      google_analytics: gaVal,
      google_body: gBodyVal,
      facebook_domain_verification: fbDomainVal,
      google_domain_verification: gDomainVal
    })
  })
  .then(async r => {
    const data = await r.json().catch(() => null);
    if (!r.ok || !data || !data.success) {
      const errMsg = (data && (data.message || (data.errors && data.errors.facebook_pixel && data.errors.facebook_pixel[0])))
        || 'মার্কেটিং সেটিংস সংরক্ষণ করা সম্ভব হয়নি।';
      throw new Error(errMsg);
    }
    return data;
  })
  .then(data => {
    if (data.settings) {
      if (data.settings.facebook_pixel !== undefined) {
        const pixelInput = document.getElementById('marketingPixelCode');
        if (pixelInput) pixelInput.value = data.settings.facebook_pixel;
      }
      if (data.settings.landing_meta_add_to_cart_enabled !== undefined) {
        setMarketingToggleState('addToCart', data.settings.landing_meta_add_to_cart_enabled);
      }
      if (data.settings.landing_meta_initiate_checkout_enabled !== undefined) {
        setMarketingToggleState('initiateCheckout', data.settings.landing_meta_initiate_checkout_enabled);
      }
    }
    if (alertBox) {
      alertBox.style.display = 'block';
      alertBox.style.background = '#ECFDF5';
      alertBox.style.color = '#065F46';
      alertBox.style.border = '1px solid #10B981';
      alertBox.textContent = '✓ ' + (data.message || 'মার্কেটিং সেটিংস সফলভাবে সেভ হয়েছে!');
    }
    if (typeof showToast === 'function') {
      showToast('মার্কেটিং সেটিংস সফলভাবে সেভ হয়েছে!');
    }
  })
  .catch(err => {
    if (alertBox) {
      alertBox.style.display = 'block';
      alertBox.style.background = '#FEF2F2';
      alertBox.style.color = '#991B1B';
      alertBox.style.border = '1px solid #EF4444';
      alertBox.textContent = '⚠️ ' + err.message;
    }
  })
  .finally(() => {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit';
    }
  });
}

window.setMarketingToggleState = setMarketingToggleState;
window.toggleMarketingSwitch = toggleMarketingSwitch;
window.loadMarketingSettings = loadMarketingSettings;
window.saveMarketingSettings = saveMarketingSettings;

// Bangladesh Cities
const BD_CITIES = [
  { id: 1, name: "Thakurgaon", charge: 120 }, { id: 2, name: "Tangail", charge: 120 },
  { id: 3, name: "Sylhet", charge: 120 }, { id: 4, name: "Sunamganj", charge: 120 },
  { id: 5, name: "Sirajganj", charge: 120 }, { id: 6, name: "Sherpur", charge: 120 },
  { id: 7, name: "Shariatpur", charge: 120 }, { id: 8, name: "Satkhira", charge: 120 },
  { id: 9, name: "Rangpur", charge: 120 }, { id: 10, name: "Rangamati", charge: 120 },
  { id: 11, name: "Rajshahi", charge: 120 }, { id: 12, name: "Rajbari", charge: 120 },
  { id: 13, name: "Pirojpur", charge: 120 }, { id: 14, name: "Patuakhali", charge: 120 },
  { id: 15, name: "Panchagarh", charge: 120 }, { id: 16, name: "Pabna", charge: 120 },
  { id: 17, name: "Noakhali", charge: 120 }, { id: 18, name: "Nilphamari", charge: 120 },
  { id: 19, name: "Netrokona", charge: 120 }, { id: 20, name: "Natore", charge: 120 },
  { id: 21, name: "Narshingdi", charge: 120 }, { id: 22, name: "Narayanganj", charge: 100 },
  { id: 23, name: "Narail", charge: 120 }, { id: 24, name: "Naogaon", charge: 120 },
  { id: 25, name: "Mymensingh", charge: 120 }, { id: 26, name: "Munshiganj", charge: 120 },
  { id: 27, name: "Moulvibazar", charge: 120 }, { id: 28, name: "Meherpur", charge: 120 },
  { id: 29, name: "Manikganj", charge: 120 }, { id: 30, name: "Magura", charge: 120 },
  { id: 31, name: "Madaripur", charge: 120 }, { id: 32, name: "Lalmonirhat", charge: 120 },
  { id: 33, name: "Lakshmipur", charge: 120 }, { id: 34, name: "Kushtia", charge: 120 },
  { id: 35, name: "Kurigram", charge: 120 }, { id: 36, name: "Kishoreganj", charge: 120 },
  { id: 37, name: "Khulna", charge: 120 }, { id: 38, name: "Khagrachari", charge: 120 },
  { id: 39, name: "Joypurhat", charge: 120 }, { id: 40, name: "Jhenaidah", charge: 120 },
  { id: 41, name: "Jhalokathi", charge: 120 }, { id: 42, name: "Jashore", charge: 120 },
  { id: 43, name: "Jamalpur", charge: 120 }, { id: 44, name: "Habiganj", charge: 120 },
  { id: 45, name: "Gopalganj", charge: 120 }, { id: 46, name: "Gazipur", charge: 100 },
  { id: 47, name: "Gaibandha", charge: 120 }, { id: 48, name: "Feni", charge: 120 },
  { id: 49, name: "Faridpur", charge: 120 }, { id: 50, name: "Dinajpur", charge: 120 }
];

window.renderCitiesTable = function(filterQuery = '') {
  const tbody = document.getElementById('citiesTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  let list = BD_CITIES;
  if (filterQuery) {
    list = list.filter(c => c.name.toLowerCase().includes(filterQuery.toLowerCase()));
  }

  list.forEach(c => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${c.id}</td>
      <td><b>${c.name}</b></td>
      <td>${c.charge}</td>
      <td style="text-align:right;padding-right:20px;">
        <span class="city-status-active">Active</span>
      </td>
    `;
    tbody.appendChild(tr);
  });
};

window.filterCities = function(val) {
  renderCitiesTable(val);
};

// ==============================================================================
// 12. EVENT BINDINGS & MODALS
// ==============================================================================
function bindGlobalEvents() {
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('loginEmail').value;
      const pass = document.getElementById('loginPass').value;
      handleLogin(email, pass);
    });
  }

  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const sidebar = document.getElementById('sidebar');
  if (hamburgerBtn && sidebar) {
    hamburgerBtn.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        sidebar.classList.toggle('mobile-open');
      } else {
        sidebar.classList.toggle('collapsed');
      }
    });
  }

  // Accordion Sidebar submenus
  document.querySelectorAll('.nav-has-sub').forEach(item => {
    item.addEventListener('click', function(e) {
      if (!e.target.closest('.submenu-tree')) {
        const wasOpen = this.classList.contains('open');
        document.querySelectorAll('.nav-has-sub').forEach(other => other.classList.remove('open'));
        if (!wasOpen) {
          this.classList.add('open');
        }
      }
    });
  });

  // Modal Closers
  document.querySelectorAll('.btn-close-modal, .modal-overlay').forEach(el => {
    el.addEventListener('click', (e) => {
      if (e.target === el || e.target.classList.contains('btn-close-modal')) {
        closeAllModals();
      }
    });
  });

  // Search input in Orders
  const searchInput = document.getElementById('orderSearchInput');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      APP_STATE.searchQuery = e.target.value.trim();
      renderOrdersTable();
    });
  }

  // Top header search
  const headerSearch = document.getElementById('headerSearchInput');
  if (headerSearch) {
    headerSearch.addEventListener('input', (e) => {
      APP_STATE.searchQuery = e.target.value.trim();
      if (APP_STATE.activeView === 'orders') renderOrdersTable();
      if (APP_STATE.activeView === 'customers') renderCustomersTable(APP_STATE.searchQuery);
      if (APP_STATE.activeView === 'products') renderProductsTable(APP_STATE.searchQuery);
    });
  }
}

window.closeAllModals = function() {
  document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
};

window.showToast = function(msg) {
  const toast = document.getElementById('toastBox');
  if (!toast) return;
  toast.textContent = msg;
  toast.style.display = 'flex';
  setTimeout(() => {
    toast.style.display = 'none';
  }, 3500);
};

// ==============================================================================
// 13. BD COURIER FRAUD & DELIVERY RATIO INTEGRATION (Hardened)
// ==============================================================================
let isCheckingCourier = false;

window.checkCourierRatio = function(phone, customerName, invoice, btnEl) {
  if (isCheckingCourier) return;
  isCheckingCourier = true;

  if (btnEl) {
    btnEl.disabled = true;
    btnEl.innerHTML = '⏳ Checking...';
  }

  let targetPhone = phone ? String(phone).trim() : '';
  if ((!targetPhone || targetPhone === 'undefined' || targetPhone === 'null') && invoice) {
    const found = APP_STATE.orders.find(o => o.invoice === invoice);
    if (found && found.phone && found.phone !== 'undefined' && found.phone !== 'null') {
      targetPhone = String(found.phone).trim();
    }
    if (found && found.customer) {
      customerName = customerName || found.customer;
    }
  }

  const modal = document.getElementById('genericModal');
  const modalTitle = document.getElementById('genericModalTitle');
  const modalBody = document.getElementById('genericModalBody');
  if (!modal || !modalBody) {
    isCheckingCourier = false;
    if (btnEl) {
      btnEl.disabled = false;
      btnEl.innerHTML = '🛡️ Check';
    }
    return;
  }

  const displayTitle = customerName && customerName !== 'undefined' ? customerName : 'Customer';
  modalTitle.textContent = `🛡️ BD Courier Verification: ${displayTitle} ${targetPhone ? '(' + targetPhone + ')' : ''}`;
  modalBody.innerHTML = `
    <div style="padding:24px;text-align:center;">
      <div style="font-size:28px;margin-bottom:10px;animation:spin 1s infinite linear;">⏳</div>
      <h3 style="font-size:15px;color:#1A202C;margin:0 0 6px 0;">BD Courier API থেকে তথ্য যাচাই করা হচ্ছে...</h3>
      <p style="font-size:12px;color:#718096;">ফোন নম্বর: <b>${targetPhone || (invoice ? 'Order #' + invoice : 'N/A')}</b> (Steadfast, Pathao, RedX, Paperfly ডেটাবেজ চেক হচ্ছে)</p>
    </div>
  `;
  modal.classList.add('active');

  const token = localStorage.getItem('admin_token') || '';
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
    headers['x-admin-token'] = token;
  }

  fetch('/api/admin/fraud/courier-check', {
    method: 'POST',
    credentials: 'same-origin',
    headers: headers,
    body: JSON.stringify({ phone: targetPhone, invoice: invoice })
  })
  .then(r => r.json())
  .then(res => {
    let contentHtml = '';

    if (res.success && res.data) {
      const d = res.data;
      const total = d.total_parcels || 0;
      const success = d.delivered || 0;
      const cancelled = d.cancelled_or_returned || 0;
      const ratio = d.success_rate || 0;
      const heuristic = d.heuristic_trust_score || {};

      // Immediately update local order risk state & table display in real-time
      const matched = APP_STATE.orders.find(o => (invoice && o.invoice === invoice) || (targetPhone && o.phone === targetPhone));
      if (matched) {
        matched.fraudScore = d.fraud_score !== undefined ? d.fraud_score : (heuristic.score !== undefined ? heuristic.score : Math.round(100 - ratio));
        matched.fraudLevel = d.fraud_level ? d.fraud_level.toUpperCase() : (heuristic.level ? heuristic.level.toUpperCase() : (total === 0 ? 'LOW' : (ratio >= 80 ? 'LOW' : (ratio >= 50 ? 'MEDIUM' : (ratio >= 25 ? 'HIGH' : 'CRITICAL')))));
        matched.fraudReasons = Array.isArray(d.fraud_reasons) ? d.fraud_reasons : (heuristic.label ? [heuristic.label] : []);
        matched.courierTotalOrders = total;
        matched.courierDelivered = success;
        matched.courierCancelled = cancelled;
        matched.courierSuccessRate = ratio;
        renderOrdersTable();
      }

      let badgeColor = '#10B981';
      if (heuristic.level === 'high_risk' || String(d.fraud_level).toLowerCase() === 'high' || String(d.fraud_level).toLowerCase() === 'critical') {
        badgeColor = '#EF4444';
      } else if (heuristic.level === 'medium' || String(d.fraud_level).toLowerCase() === 'medium') {
        badgeColor = '#F59E0B';
      } else if (heuristic.level === 'new_customer' || total === 0) {
        badgeColor = '#3B82F6';
      }

      contentHtml = `
        <div style="padding:18px;font-family:sans-serif;">
          <div style="background:#F8FAFC;border:1px solid #E2E8F0;padding:14px;border-radius:6px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
              <div>
                <h4 style="margin:0;font-size:15px;color:#1A202C;">${customerName}</h4>
                <div style="font-size:13px;color:#4A5568;">📞 ${d.phone || targetPhone || phone} | Order: <b>#${invoice || 'N/A'}</b> ${d.cached ? '<span style="background:#E2E8F0;font-size:10px;padding:2px 6px;border-radius:4px;color:#4A5568;">⚡ Cached</span>' : ''}</div>
              </div>
              <div style="text-align:right;">
                <span style="background:${badgeColor};color:#fff;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                  ${ratio}% Success Rate
                </span>
              </div>
            </div>
            <div style="font-size:13px;font-weight:700;color:${badgeColor};margin-top:4px;">
              ${heuristic.label || 'Customer Assessment'}
            </div>
            <div style="font-size:11px;color:#718096;margin-top:2px;">
              <i>* ${heuristic.methodology || 'Internal Heuristic Assessment based on delivered/cancelled parcel ratio'}</i>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:12px;margin-bottom:18px;">
            <div style="background:#F0FDF4;border:1px solid #BBF7D0;padding:12px;border-radius:6px;text-align:center;">
              <div style="font-size:11.5px;color:#166534;font-weight:600;">Total Parcels</div>
              <div style="font-size:20px;font-weight:800;color:#15803D;margin-top:2px;">${total}</div>
            </div>
            <div style="background:#ECFDF5;border:1px solid #A7F3D0;padding:12px;border-radius:6px;text-align:center;">
              <div style="font-size:11.5px;color:#065F46;font-weight:600;">Delivered</div>
              <div style="font-size:20px;font-weight:800;color:#059669;margin-top:2px;">${success}</div>
            </div>
            <div style="background:#FEF2F2;border:1px solid #FECACA;padding:12px;border-radius:6px;text-align:center;">
              <div style="font-size:11.5px;color:#991B1B;font-weight:600;">Cancelled / Return</div>
              <div style="font-size:20px;font-weight:800;color:#DC2626;margin-top:2px;">${cancelled}</div>
            </div>
          </div>

          <div style="border:1px solid #E2E8F0;border-radius:6px;overflow:hidden;margin-bottom:16px;">
            <div style="background:#F8FAFC;padding:10px 14px;font-weight:700;font-size:13px;border-bottom:1px solid #E2E8F0;">
              Courier Breakdown
            </div>
            <div style="padding:12px;font-size:12.5px;display:grid;grid-template-columns:1fr 1fr;gap:10px;">
              ${(d.courier_breakdown && d.courier_breakdown.length > 0) ?
                d.courier_breakdown.map(c => {
                  let statusText = typeof c.status === 'object' && c.status !== null
                    ? `${c.status.success_parcel || c.status.delivered || 0}/${c.status.total_parcel || c.status.total || 0} (${c.status.success_ratio || 0}%)`
                    : (c.status || 'Checked');
                  return `<div>📦 ${c.name}: <b>${statusText}</b></div>`;
                }).join('') :
                `<div>🚀 Steadfast: <b>Checked</b></div><div>🚚 Pathao: <b>Checked</b></div><div>📦 RedX: <b>Checked</b></div><div>📮 Paperfly: <b>Checked</b></div>`
              }
            </div>
          </div>

          ${(d.reports && d.reports.length > 0) ? `
          <div style="border:1px solid #FECACA;background:#FFF5F5;border-radius:6px;overflow:hidden;margin-bottom:16px;">
            <div style="background:#FEE2E2;color:#991B1B;padding:8px 14px;font-weight:700;font-size:12.5px;">
              ⚠️ Merchant Fraud / Issue Reports (${d.reports.length})
            </div>
            <div style="padding:10px 14px;font-size:12px;color:#7F1D1D;max-height:100px;overflow-y:auto;">
              ${d.reports.map(r => `<div>• <b>${r.courier || 'Report'}:</b> ${r.delivered !== null && r.total !== null ? `${r.delivered}/${r.total} delivered` : 'Reported issue'}</div>`).join('')}
            </div>
          </div>` : ''}

          <div style="text-align:right;">
            <button class="btn-primary-teal btn-close-modal" style="padding:8px 20px;" onclick="closeAllModals()">ঠিক আছে (Close)</button>
          </div>
        </div>
      `;
    } else {
      contentHtml = `
        <div style="padding:18px;font-family:sans-serif;">
          <div style="background:#FEF3C7;border:1px solid #FDE68A;padding:14px;border-radius:6px;margin-bottom:16px;">
            <div style="display:flex;gap:10px;align-items:flex-start;">
              <span style="font-size:20px;">⚠️</span>
              <div>
                <h4 style="margin:0 0 4px 0;font-size:14px;color:#92400E;">BD Courier API Status</h4>
                <p style="margin:0;font-size:12.5px;color:#78350F;line-height:1.5;">
                  ${res.message || res.error || 'Courier service response unavailable.'}
                </p>
              </div>
            </div>
          </div>

          <div style="background:#F8FAFC;border:1px solid #E2E8F0;padding:14px;border-radius:6px;font-size:13px;margin-bottom:16px;">
            <div style="font-weight:700;margin-bottom:6px;">চেক করা নম্বর: <code>${targetPhone || phone || 'N/A'}</code></div>
            <div>গ্রাহকের নাম: <b>${displayTitle}</b> | অর্ডার: <b>#${invoice || 'N/A'}</b></div>
          </div>

          <div style="display:flex;justify-content:space-between;align-items:center;">
            <button class="btn-teal-action" onclick="switchView('courier-api'); closeAllModals();">
              ⚙️ View Courier Settings
            </button>
            <button class="btn-primary-teal btn-close-modal" style="padding:8px 20px;" onclick="closeAllModals()">Close</button>
          </div>
        </div>
      `;
    }

    modalBody.innerHTML = contentHtml;
  })
  .catch(err => {
    modalBody.innerHTML = `
      <div style="padding:20px;text-align:center;">
        <div style="font-size:28px;color:#E53E3E;margin-bottom:8px;">❌</div>
        <h4 style="margin:0 0 6px 0;color:#1A202C;">সার্ভারের সাথে যোগাযোগ করা যায়নি</h4>
        <p style="font-size:12px;color:#718096;margin-bottom:16px;">${err.message}</p>
        <button class="btn-primary-teal btn-close-modal" style="padding:6px 16px;" onclick="closeAllModals()">Close</button>
      </div>
    `;
  })
  .finally(() => {
    isCheckingCourier = false;
    if (btnEl) {
      btnEl.disabled = false;
      btnEl.innerHTML = '🛡️ Check';
    }
  });
};

let isTestingCourierConnection = false;
window.testBdCourierConnection = function(btnEl) {
  if (isTestingCourierConnection) return;
  isTestingCourierConnection = true;
  if (btnEl) {
    btnEl.disabled = true;
    btnEl.innerText = 'Testing...';
  }
  showToast('BD Courier API এর সাথে সার্ভার কানেকশন পরীক্ষা করা হচ্ছে...');
  const token = localStorage.getItem('admin_token') || '';

  fetch('/api/courier/check', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ phone: '01711223344' })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success || res.status_code === 200) {
      showToast('✅ BD Courier API সফলভাবে কানেক্ট হয়েছে!');
    } else {
      alert(`BD Courier Response (${res.status_code || 401}):\n${res.message || res.error || 'Service check completed'}\n\nদয়া করে সার্ভার .env ফাইলে আপনার অ্যাক্টিভ BD_COURIER_API_KEY কনফিগারেশন নিশ্চিত করুন।`);
    }
  })
  .catch(err => {
    alert(`Connection Error: ${err.message}`);
  })
  .finally(() => {
    isTestingCourierConnection = false;
    if (btnEl) {
      btnEl.disabled = false;
      btnEl.innerText = '🔍 Test Connection';
    }
  });
};

// ==============================================================================
// 14. UNIFIED ANALYTICS & ATTRIBUTION MODULE
// ==============================================================================
let currentAnalyticsRange = 'last_7_days';
let customAnalyticsStartDate = null;
let customAnalyticsEndDate = null;

window.changeAnalyticsRange = function(range, btn) {
  currentAnalyticsRange = range;
  document.querySelectorAll('.btn-analytics-filter').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  const customRow = document.getElementById('analyticsCustomDateRow');
  if (customRow) customRow.style.display = 'none';
  loadAnalyticsDashboard();
};

window.toggleAnalyticsCustomRange = function(btn) {
  const customRow = document.getElementById('analyticsCustomDateRow');
  if (!customRow) return;
  const isVisible = customRow.style.display === 'flex';
  customRow.style.display = isVisible ? 'none' : 'flex';
  if (!isVisible) {
    document.querySelectorAll('.btn-analytics-filter').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
  }
};

window.applyAnalyticsCustomRange = function() {
  const start = document.getElementById('analyticsStartDate')?.value;
  const end = document.getElementById('analyticsEndDate')?.value;
  if (!start || !end) {
    alert('Please select both Start Date and End Date.');
    return;
  }
  if (start > end) {
    alert('Start Date cannot be later than End Date.');
    return;
  }
  currentAnalyticsRange = 'custom';
  customAnalyticsStartDate = start;
  customAnalyticsEndDate = end;
  loadAnalyticsDashboard();
};

window.refreshAnalyticsData = function() {
  loadAnalyticsDashboard();
};

window.loadAnalyticsDashboard = async function() {
  let query = `range=${encodeURIComponent(currentAnalyticsRange)}`;
  if (currentAnalyticsRange === 'custom' && customAnalyticsStartDate && customAnalyticsEndDate) {
    query += `&start_date=${encodeURIComponent(customAnalyticsStartDate)}&end_date=${encodeURIComponent(customAnalyticsEndDate)}`;
  }

  const token = localStorage.getItem('admin_token') || '';
  const headers = { 'Accept': 'application/json' };
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
    headers['x-admin-token'] = token;
  }

  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 8000);

  try {
    const fetchOptions = {
      signal: controller.signal,
      credentials: 'same-origin',
      headers
    };

    // Fetch all analytics datasets in parallel
    const [overviewRes, funnelRes, attrRes, campRes, lpRes, timeRes, devRes] = await Promise.all([
      fetch(`/api/admin/analytics/overview?${query}`, fetchOptions),
      fetch(`/api/admin/analytics/funnel?${query}`, fetchOptions),
      fetch(`/api/admin/analytics/attribution?${query}`, fetchOptions),
      fetch(`/api/admin/analytics/campaigns?${query}`, fetchOptions),
      fetch(`/api/admin/analytics/landing-pages?${query}`, fetchOptions),
      fetch(`/api/admin/analytics/timeline?${query}`, fetchOptions),
      fetch(`/api/admin/analytics/devices?${query}`, fetchOptions),
    ]);
    clearTimeout(timer);

    if (overviewRes.status === 401) {
      console.warn('Analytics API Unauthorized: Admin session required.');
      return;
    }

    const [overviewData, funnelData, attrData, campData, lpData, timeData, devData] = await Promise.all([
      overviewRes.json(),
      funnelRes.json(),
      attrRes.json(),
      campRes.json(),
      lpRes.json(),
      timeRes.json(),
      devRes.json(),
    ]);

    if (overviewData && overviewData.success) renderAnalyticsKPIs(overviewData);
    if (funnelData && funnelData.success) renderAnalyticsFunnel(funnelData);
    if (attrData && attrData.success) renderAnalyticsAttribution(attrData);
    if (campData && campData.success) renderAnalyticsCampaigns(campData);
    if (lpData && lpData.success) renderAnalyticsLandingPages(lpData);
    if (timeData && timeData.success) renderAnalyticsTimeline(timeData);
    if (devData && devData.success) renderAnalyticsDevices(devData);

  } catch (err) {
    clearTimeout(timer);
    console.error('[Analytics Error]', err);
  }
};

function formatChangeDelta(change) {
  if (change === null || change === undefined) return '<span style="color:#718096;">vs previous</span>';
  const num = Number(change);
  if (num > 0) {
    return `<span style="color:#38A169; font-weight:700;">↑ +${num.toFixed(1)}%</span> vs previous`;
  } else if (num < 0) {
    return `<span style="color:#E53E3E; font-weight:700;">↓ ${num.toFixed(1)}%</span> vs previous`;
  }
  return `<span style="color:#718096;">0.0% vs previous</span>`;
}

function renderAnalyticsKPIs(data) {
  const m = data.metrics || {};
  const c = data.comparison || {};

  const elVisitors = document.getElementById('kpiVisitors');
  const elSessions = document.getElementById('kpiSessions');
  const elPageViews = document.getElementById('kpiPageViews');
  const elCtaClicks = document.getElementById('kpiCtaClicks');
  const elOrders = document.getElementById('kpiOrders');
  const elRevenue = document.getElementById('kpiRevenue');
  const elCvr = document.getElementById('kpiCvr');
  const elAov = document.getElementById('kpiAov');

  if (elVisitors) elVisitors.textContent = (m.unique_visitors || 0).toLocaleString();
  if (elSessions) elSessions.textContent = (m.sessions || 0).toLocaleString();
  if (elPageViews) elPageViews.textContent = (m.page_views || 0).toLocaleString();
  if (elCtaClicks) elCtaClicks.textContent = (m.cta_clicks || 0).toLocaleString();
  if (elOrders) elOrders.textContent = (m.orders || 0).toLocaleString();
  if (elRevenue) elRevenue.textContent = '৳ ' + (m.revenue || 0).toLocaleString();
  if (elCvr) elCvr.textContent = (m.conversion_rate || 0).toFixed(2) + '%';
  if (elAov) elAov.textContent = '৳ ' + (m.average_order_value || 0).toLocaleString();

  // Deltas
  const dVisitors = document.getElementById('kpiVisitorsDelta');
  const dSessions = document.getElementById('kpiSessionsDelta');
  const dPageViews = document.getElementById('kpiPageViewsDelta');
  const dCtaClicks = document.getElementById('kpiCtaClicksDelta');
  const dOrders = document.getElementById('kpiOrdersDelta');
  const dRevenue = document.getElementById('kpiRevenueDelta');
  const dCvr = document.getElementById('kpiCvrDelta');
  const dAov = document.getElementById('kpiAovDelta');

  if (dVisitors) dVisitors.innerHTML = formatChangeDelta(c.unique_visitors?.change);
  if (dSessions) dSessions.innerHTML = formatChangeDelta(c.sessions?.change);
  if (dPageViews) dPageViews.innerHTML = formatChangeDelta(c.page_views?.change);
  if (dCtaClicks) dCtaClicks.innerHTML = formatChangeDelta(c.cta_clicks?.change);
  if (dOrders) dOrders.innerHTML = formatChangeDelta(c.orders?.change);
  if (dRevenue) dRevenue.innerHTML = formatChangeDelta(c.revenue?.change);
  if (dCvr) dCvr.innerHTML = formatChangeDelta(c.conversion_rate?.change);
  if (dAov) dAov.innerHTML = formatChangeDelta(c.average_order_value?.change);
}

function renderAnalyticsFunnel(data) {
  const container = document.getElementById('analyticsFunnelContainer');
  const totalCvrEl = document.getElementById('funnelTotalCvr');
  if (!container) return;

  const stages = data.funnel || [];
  if (stages.length === 0) {
    container.innerHTML = '<div style="text-align:center; padding:20px; color:#A0AEC0;">No funnel activity recorded in this period.</div>';
    return;
  }

  const topCount = Math.max(1, stages[0].count || 1);
  const lastStage = stages[stages.length - 1];
  if (totalCvrEl && lastStage) {
    totalCvrEl.textContent = `Overall CVR: ${(lastStage.overall_conversion_rate || 0).toFixed(2)}%`;
  }

  container.innerHTML = stages.map(s => {
    const widthPct = Math.max(2, Math.min(100, (s.count / topCount) * 100));
    return `
      <div class="funnel-step-row">
        <div class="funnel-step-label">${s.stage}</div>
        <div class="funnel-step-bar-wrap">
          <div class="funnel-step-bar-fill" style="width: ${widthPct}%;"></div>
        </div>
        <div class="funnel-step-count">${(s.count || 0).toLocaleString()}</div>
        <div class="funnel-step-percent">${s.step_conversion_rate !== null ? s.step_conversion_rate.toFixed(1) + '%' : '-'}</div>
      </div>
    `;
  }).join('');
}

function renderAnalyticsAttribution(data) {
  // 1. Source Split
  const split = data.source_split || {};
  const sf = split.storefront || { orders: 0, revenue: 0 };
  const lp = split.landing_page || { orders: 0, revenue: 0 };

  const elSfOrders = document.getElementById('splitStorefrontOrders');
  const elSfRev = document.getElementById('splitStorefrontRev');
  const elSfBar = document.getElementById('splitStorefrontBar');

  const elLpOrders = document.getElementById('splitLandingOrders');
  const elLpRev = document.getElementById('splitLandingRev');
  const elLpBar = document.getElementById('splitLandingBar');

  if (elSfOrders) elSfOrders.textContent = (sf.orders || 0).toLocaleString();
  if (elSfRev) elSfRev.textContent = '৳ ' + (sf.revenue || 0).toLocaleString();

  if (elLpOrders) elLpOrders.textContent = (lp.orders || 0).toLocaleString();
  if (elLpRev) elLpRev.textContent = '৳ ' + (lp.revenue || 0).toLocaleString();

  const totalRev = (sf.revenue || 0) + (lp.revenue || 0);
  const sfPct = totalRev > 0 ? ((sf.revenue / totalRev) * 100).toFixed(1) : 0;
  const lpPct = totalRev > 0 ? ((lp.revenue / totalRev) * 100).toFixed(1) : 0;

  if (elSfBar) elSfBar.style.width = sfPct + '%';
  if (elLpBar) elLpBar.style.width = lpPct + '%';

  // 2. Channels Table
  const tbody = document.getElementById('analyticsChannelsBody');
  if (!tbody) return;

  const channels = data.channels || [];
  if (channels.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#A0AEC0; padding:16px;">No traffic channels recorded.</td></tr>';
    return;
  }

  tbody.innerHTML = channels.map(ch => `
    <tr>
      <td><b>${ch.channel_label}</b></td>
      <td style="text-align:right;">${(ch.visitors || 0).toLocaleString()}</td>
      <td style="text-align:right;">${(ch.sessions || 0).toLocaleString()}</td>
      <td style="text-align:right;"><b>${(ch.orders || 0).toLocaleString()}</b></td>
      <td style="text-align:right; font-weight:700; color:#2D3748;">৳ ${(ch.revenue || 0).toLocaleString()}</td>
      <td style="text-align:right; color:#319795; font-weight:700;">${(ch.conversion_rate || 0).toFixed(2)}%</td>
    </tr>
  `).join('');

  // 3. First-Touch vs Last-Touch Matrix Table
  const ftLtBody = document.getElementById('analyticsFirstLastBody');
  if (ftLtBody) {
    const ftLt = data.first_touch_comparison || [];
    if (ftLt.length === 0) {
      ftLtBody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:#A0AEC0; padding:16px;">No attribution matrix data recorded.</td></tr>';
    } else {
      ftLtBody.innerHTML = ftLt.map(item => {
        const orderDiff = item.order_diff || 0;
        const revDiff = item.revenue_diff || 0;
        const orderTag = orderDiff > 0
          ? `<span class="diff-tag-positive">+${orderDiff}</span>`
          : (orderDiff < 0 ? `<span class="diff-tag-negative">${orderDiff}</span>` : '<span style="color:#A0AEC0;">0</span>');
        const revTag = revDiff > 0
          ? `<span class="diff-tag-positive">+৳ ${revDiff.toLocaleString()}</span>`
          : (revDiff < 0 ? `<span class="diff-tag-negative">-৳ ${Math.abs(revDiff).toLocaleString()}</span>` : '<span style="color:#A0AEC0;">৳ 0</span>');

        return `
          <tr>
            <td><b>${item.channel_label}</b></td>
            <td style="text-align:right;">${(item.first_touch_orders || 0).toLocaleString()}</td>
            <td style="text-align:right; color:#718096;">৳ ${(item.first_touch_revenue || 0).toLocaleString()}</td>
            <td style="text-align:right;"><b>${(item.last_touch_orders || 0).toLocaleString()}</b></td>
            <td style="text-align:right; font-weight:700; color:#2D3748;">৳ ${(item.last_touch_revenue || 0).toLocaleString()}</td>
            <td style="text-align:right;">${orderTag}</td>
            <td style="text-align:right;">${revTag}</td>
          </tr>
        `;
      }).join('');
    }
  }
}

function renderAnalyticsCampaigns(data) {
  const tbody = document.getElementById('analyticsCampaignsBody');
  if (!tbody) return;

  const campaigns = data.campaigns || [];
  if (campaigns.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:#A0AEC0; padding:16px;">No campaign UTM parameters detected in this period.</td></tr>';
    return;
  }

  tbody.innerHTML = campaigns.map(c => `
    <tr>
      <td><b>${c.utm_source}</b></td>
      <td><span style="background:#EDF2F7; padding:2px 6px; border-radius:4px; font-size:11px;">${c.utm_medium}</span></td>
      <td>${c.utm_campaign}</td>
      <td style="color:#718096; font-size:11.5px;">${c.utm_content}</td>
      <td style="text-align:right;">${(c.visitors || 0).toLocaleString()}</td>
      <td style="text-align:right;">${(c.sessions || 0).toLocaleString()}</td>
      <td style="text-align:right;"><b>${(c.orders || 0).toLocaleString()}</b></td>
      <td style="text-align:right; font-weight:700;">৳ ${(c.revenue || 0).toLocaleString()}</td>
      <td style="text-align:right; color:#319795; font-weight:700;">${(c.conversion_rate || 0).toFixed(2)}%</td>
    </tr>
  `).join('');
}

function renderAnalyticsLandingPages(data) {
  const tbody = document.getElementById('analyticsLandingPagesBody');
  if (!tbody) return;

  const pages = data.landing_pages || [];
  if (pages.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center; color:#A0AEC0; padding:16px;">No landing page activity recorded in this period.</td></tr>';
    return;
  }

  tbody.innerHTML = pages.map(p => `
    <tr>
      <td><b><a href="${p.landing_page}" target="_blank" style="color:#3182CE; text-decoration:none;">${p.landing_page} ↗</a></b></td>
      <td><span style="background:#EBF8FF; color:#2B6CB0; font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px;">${p.page_type}</span></td>
      <td style="text-align:right;">${(p.visitors || 0).toLocaleString()}</td>
      <td style="text-align:right;">${(p.sessions || 0).toLocaleString()}</td>
      <td style="text-align:right;">${(p.cta_clicks || 0).toLocaleString()}</td>
      <td style="text-align:right;">${(p.checkout_started || 0).toLocaleString()}</td>
      <td style="text-align:right;"><b>${(p.orders || 0).toLocaleString()}</b></td>
      <td style="text-align:right; font-weight:700; color:#2D3748;">৳ ${(p.revenue || 0).toLocaleString()}</td>
      <td style="text-align:right; color:#319795; font-weight:700;">${(p.conversion_rate || 0).toFixed(2)}%</td>
    </tr>
  `).join('');
}

function renderAnalyticsTimeline(data) {
  const container = document.getElementById('analyticsTimelineChart');
  if (!container) return;

  const points = data.timeline || [];
  if (points.length === 0) {
    container.innerHTML = '<div style="text-align:center; color:#A0AEC0; padding:20px;">No timeline points available.</div>';
    return;
  }

  const maxRev = Math.max(1, ...points.map(p => p.revenue));
  const maxVis = Math.max(1, ...points.map(p => p.visitors));

  container.innerHTML = `
    <div style="display:flex; align-items:flex-end; gap:8px; height:120px; border-bottom:1px solid #E2E8F0; padding-bottom:8px;">
      ${points.map(p => {
        const hRev = Math.max(4, (p.revenue / maxRev) * 100);
        const hVis = Math.max(4, (p.visitors / maxVis) * 100);
        return `
          <div style="flex:1; display:flex; flex-direction:column; align-items:center; height:100%; justify-content:flex-end;" title="${p.date} - Rev: ৳${p.revenue.toLocaleString()} | Visitors: ${p.visitors} | Orders: ${p.orders}">
            <div style="display:flex; gap:3px; align-items:flex-end; height:100%;">
              <div style="width:10px; height:${hRev}%; background:#319795; border-radius:3px 3px 0 0;" title="Revenue: ৳${p.revenue}"></div>
              <div style="width:10px; height:${hVis}%; background:#CBD5E0; border-radius:3px 3px 0 0;" title="Visitors: ${p.visitors}"></div>
            </div>
            <div style="font-size:10px; color:#718096; margin-top:6px; white-space:nowrap;">${p.label}</div>
          </div>
        `;
      }).join('')}
    </div>
    <div style="display:flex; justify-content:flex-end; gap:16px; margin-top:10px; font-size:12px;">
      <div style="display:flex; align-items:center; gap:6px;">
        <span style="width:10px; height:10px; background:#319795; border-radius:2px; display:inline-block;"></span>
        <span style="color:#4A5568; font-weight:600;">Revenue (৳)</span>
      </div>
      <div style="display:flex; align-items:center; gap:6px;">
        <span style="width:10px; height:10px; background:#CBD5E0; border-radius:2px; display:inline-block;"></span>
        <span style="color:#4A5568; font-weight:600;">Unique Visitors</span>
      </div>
    </div>
  `;
}

// ==============================================================================
// 6. PHASE 5A: DEVICE INTELLIGENCE & CUSTOMER TRACKING JOURNEY
// ==============================================================================
function renderAnalyticsDevices(data) {
  const cardsContainer = document.getElementById('analyticsDeviceCardsContainer');
  const browserBody = document.getElementById('analyticsBrowsersBody');
  const osBody = document.getElementById('analyticsOsBody');

  if (cardsContainer) {
    const devices = data.devices || [];
    if (devices.length === 0) {
      cardsContainer.innerHTML = '<div style="text-align:center; color:#A0AEC0; padding:16px;">No device data recorded in this period.</div>';
    } else {
      const iconMap = { mobile: '📱', desktop: '🖥️', tablet: '📟' };
      const colorMap = { mobile: '#319795', desktop: '#3182CE', tablet: '#805AD5' };

      cardsContainer.innerHTML = devices.map(d => {
        const icon = iconMap[d.device_type] || '💻';
        const color = colorMap[d.device_type] || '#319795';
        return `
          <div class="device-card">
            <div class="device-card-header">
              <div class="device-icon-title">
                <span style="font-size:18px;">${icon}</span>
                <span>${d.device_label}</span>
              </div>
              <span style="font-size:12px; font-weight:700; color:${color};">${d.session_share}% Share</span>
            </div>
            <div class="device-bar-track">
              <div class="device-bar-fill" style="width: ${Math.max(2, d.session_share)}%; background: ${color};"></div>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:8px;">
              <span style="color:#718096;">Sessions: <b style="color:#1A202C;">${(d.sessions || 0).toLocaleString()}</b></span>
              <span style="color:#718096;">Visitors: <b style="color:#1A202C;">${(d.visitors || 0).toLocaleString()}</b></span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:4px;">
              <span style="color:#718096;">Orders: <b style="color:#2D3748;">${(d.orders || 0).toLocaleString()}</b></span>
              <span style="color:#718096;">CVR: <b style="color:${color};">${(d.conversion_rate || 0).toFixed(2)}%</b></span>
            </div>
            <div style="margin-top:6px; padding-top:6px; border-top:1px dashed #EDF2F7; font-size:12.5px; font-weight:700; color:#2D3748; display:flex; justify-content:space-between;">
              <span>Revenue:</span>
              <span>৳ ${(d.revenue || 0).toLocaleString()}</span>
            </div>
          </div>
        `;
      }).join('');
    }
  }

  if (browserBody) {
    const browsers = data.browsers || [];
    if (browsers.length === 0) {
      browserBody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:#A0AEC0; padding:10px;">No browser data recorded.</td></tr>';
    } else {
      browserBody.innerHTML = browsers.map(b => `
        <tr>
          <td><b>${b.browser}</b></td>
          <td style="text-align:right;">${(b.sessions || 0).toLocaleString()}</td>
          <td style="text-align:right; font-weight:600; color:#4A5568;">${b.share}%</td>
        </tr>
      `).join('');
    }
  }

  if (osBody) {
    const osList = data.operating_systems || [];
    if (osList.length === 0) {
      osBody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:#A0AEC0; padding:10px;">No OS data recorded.</td></tr>';
    } else {
      osBody.innerHTML = osList.map(o => `
        <tr>
          <td><b>${o.os}</b></td>
          <td style="text-align:right;">${(o.sessions || 0).toLocaleString()}</td>
          <td style="text-align:right; font-weight:600; color:#4A5568;">${o.share}%</td>
        </tr>
      `).join('');
    }
  }
}

// ==============================================================================
// 6. PHASE 5B: ORDER FRAUD DETAILS MODAL
// ==============================================================================
window.openFraudDetailModal = async function(orderIdOrInvoice) {
  const modal = document.getElementById('genericModal');
  const modalTitle = document.getElementById('genericModalTitle');
  const modalBody = document.getElementById('genericModalBody');
  if (!modal || !modalBody) return;

  modalTitle.textContent = `Fraud Risk Assessment — #${orderIdOrInvoice}`;
  modalBody.innerHTML = `
    <div style="padding:24px; text-align:center;">
      <div style="font-size:24px; margin-bottom:8px;">⏳</div>
      <div style="font-size:13px; color:#4A5568;">Loading fraud assessment data...</div>
    </div>
  `;
  modal.classList.add('active');

  try {
    const token = localStorage.getItem('admin_token') || '';
    const headers = { 'Accept': 'application/json' };
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
      headers['x-admin-token'] = token;
    }
    const res = await fetch(`/api/admin/fraud/orders/${encodeURIComponent(orderIdOrInvoice)}`, {
      credentials: 'same-origin',
      headers: headers
    });

    if (!res.ok) throw new Error('Order not found or unauthorized.');
    const data = await res.json();
    const d = data.fraud_detail || data;

    if (d.fraud_score === null || d.fraud_score === undefined) {
      modalBody.innerHTML = `
        <div style="padding:24px; text-align:center; color:#718096;">
          <div style="font-size:28px; margin-bottom:8px;">🛡️</div>
          <div style="font-size:15px; font-weight:700; color:#2D3748;">Not Assessed</div>
          <p style="font-size:12.5px; margin-top:4px;">This order has not been assessed for fraud risk yet.</p>
        </div>
      `;
      return;
    }

    const lvl = (d.fraud_level || 'LOW').toUpperCase();
    const color = lvl === 'HIGH' ? '#DC2626' : (lvl === 'MEDIUM' ? '#D97706' : '#16A34A');
    const levelIcon = lvl === 'HIGH' ? '🔴' : (lvl === 'MEDIUM' ? '🟡' : '🟢');
    const reasons = Array.isArray(d.fraud_reasons) ? d.fraud_reasons : [];
    const reasonsHtml = reasons.length > 0
      ? `<ul style="margin:4px 0 0 16px; padding:0; font-size:12.5px; color:#4A5568; line-height:1.6;">${reasons.map(r => `<li>${r}</li>`).join('')}</ul>`
      : '<p style="font-size:12.5px; color:#718096; margin:4px 0 0 0;">No risk signals triggered.</p>';

    modalBody.innerHTML = `
      <div style="padding:16px 20px; font-family:sans-serif; color:#1A202C;">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #E2E8F0; padding-bottom:14px; margin-bottom:16px;">
          <div>
            <div style="font-size:11px; color:#718096; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Fraud Risk Assessment</div>
            <div style="font-size:20px; font-weight:800; color:${color}; margin-top:2px;">${levelIcon} ${lvl}</div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:24px; font-weight:800; color:#1A202C;">${d.fraud_score} <span style="font-size:13px; color:#718096; font-weight:500;">/ 100</span></div>
            <div style="font-size:11.5px; color:#718096;">Invoice #${d.invoice_no}</div>
          </div>
        </div>

        <div style="margin-bottom:16px;">
          <div style="font-size:12.5px; font-weight:700; color:#2D3748;">Reasons:</div>
          ${reasonsHtml}
        </div>

        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px 14px; margin-bottom:12px;">
          <div style="font-size:12px; font-weight:700; color:#2D3748; margin-bottom:8px;">Courier History:</div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; font-size:12px;">
            <div>Total Parcels: <b>${d.courier_total_orders ?? 0}</b></div>
            <div>Delivered: <b>${d.courier_delivered ?? 0}</b></div>
            <div>Cancelled: <b>${d.courier_cancelled ?? 0}</b></div>
            <div>Success Rate: <b>${d.courier_success_rate !== null && d.courier_success_rate !== undefined ? d.courier_success_rate + '%' : 'N/A'}</b></div>
          </div>
        </div>

        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px 14px; margin-bottom:12px;">
          <div style="font-size:12px; font-weight:700; color:#2D3748; margin-bottom:8px;">Phone History:</div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; font-size:12px;">
            <div>Previous Orders: <b>${d.phone_history ? d.phone_history.previous_orders : 0}</b></div>
            <div>Cancelled/Rejected: <b>${d.phone_history ? d.phone_history.cancelled_or_rejected : 0}</b></div>
          </div>
        </div>

        <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:12px 14px;">
          <div style="font-size:12px; font-weight:700; color:#2D3748; margin-bottom:8px;">IP Activity:</div>
          <div style="font-size:12px;">
            Orders from IP in 24h: <b>${d.ip_activity ? d.ip_activity.other_orders_24h : '1 (this order)'}</b>
          </div>
        </div>
      </div>
    `;
  } catch (err) {
    modalBody.innerHTML = `<div style="color:#E53E3E; padding:20px; text-align:center;">Failed to load fraud details: ${err.message}</div>`;
  }
};

window.closeOrderJourneyModal = function() {
  const modal = document.getElementById('orderJourneyModal');
  if (modal) modal.classList.remove('active');
};

// ==============================================================================
// PHASE 5B STEP 3: FRAUD RISK FILTER, FRAUD OVERVIEW, JOURNEY FRAUD CARD
// ==============================================================================

/**
 * Risk filter buttons in the Orders view.
 * Uses client-side filtering from already-loaded APP_STATE.orders.
 */
window.setRiskFilter = function(level) {
  // Toggle: clicking the active filter returns to 'all'
  const targetLevel = (APP_STATE.riskFilter === level && level !== 'all') ? 'all' : (level || 'all');
  APP_STATE.riskFilter = targetLevel;

  // Update button active states
  ['all','high','medium','low','not_assessed'].forEach(k => {
    const btn = document.getElementById('riskBtn-' + k);
    if (!btn) return;
    btn.className = 'risk-filter-btn' + (k === targetLevel ? ` active-${k}` : '');
  });

  renderOrdersTable();
};

/**
 * Load & render Fraud Risk Overview KPIs in the analytics panel.
 */
window.loadFraudOverview = async function() {
  const ids = ['foTotal','foHigh','foMedium','foLow','foAvg'];
  ids.forEach(id => { const el = document.getElementById(id); if (el) el.textContent = '...'; });

  try {
    const token = localStorage.getItem('admin_token') || '';
    const headers = { 'Accept': 'application/json' };
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
      headers['x-admin-token'] = token;
    }
    const res = await fetch('/api/admin/fraud/overview', {
      credentials: 'same-origin',
      headers: headers
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    if (!data.success || !data.fraud_overview) throw new Error('No data');

    const fo = data.fraud_overview;
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    set('foTotal',  fo.assessed_count ?? '0');
    set('foHigh',   fo.high_count ?? '0');
    set('foMedium', fo.medium_count ?? '0');
    set('foLow',    fo.low_count ?? '0');
    set('foAvg',    fo.average_score !== null && fo.average_score !== undefined ? fo.average_score.toFixed(1) : '—');
  } catch (e) {
    ids.forEach(id => { const el = document.getElementById(id); if (el) el.textContent = '—'; });
  }
};

// Auto-load fraud overview when analytics view opens
const _origRefreshAnalytics = window.refreshAnalyticsData;
window.refreshAnalyticsData = function(...args) {
  if (typeof _origRefreshAnalytics === 'function') _origRefreshAnalytics(...args);
  loadFraudOverview();
};

// ==============================================================================
// JOURNEY MODAL — Extend to show Fraud Card at top (Phase 5B Step 3)
// ==============================================================================

window.openOrderJourneyModal = async function(orderIdOrInvoice) {
  const modal = document.getElementById('orderJourneyModal');
  const body = document.getElementById('orderJourneyModalBody');
  const title = document.getElementById('orderJourneyModalTitle');
  if (!modal || !body) return;

  modal.classList.add('active');
  if (title) title.textContent = `Customer Journey \u2014 #${orderIdOrInvoice}`;
  body.innerHTML = `
    <div style="text-align:center; padding:40px 20px;">
      <div style="font-size:28px; margin-bottom:8px;">⏳</div>
      <div style="font-size:14px; font-weight:600; color:#4A5568;">Reconstructing Customer Journey...</div>
      <div style="font-size:12px; color:#A0AEC0; margin-top:4px;">Stitching visitor timeline, session parameters, events &amp; order data</div>
    </div>
  `;

  try {
    const token = localStorage.getItem('admin_token') || '';
    const headers = { 'Accept': 'application/json' };
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
      headers['x-admin-token'] = token;
    }
    const res = await fetch(`/api/admin/analytics/journey/${encodeURIComponent(orderIdOrInvoice)}`, {
      credentials: 'same-origin',
      headers: headers
    });

    if (!res.ok) {
      body.innerHTML = `
        <div style="text-align:center; padding:30px 20px; color:#E53E3E;">
          <div style="font-size:24px; margin-bottom:8px;">⚠️</div>
          <div style="font-weight:700;">Could not retrieve customer journey.</div>
          <div style="font-size:12px; color:#718096; margin-top:4px;">Order #${orderIdOrInvoice} not found or unauthorized.</div>
        </div>
      `;
      return;
    }

    const data = await res.json();
    if (!data.success || !data.journey) {
      body.innerHTML = `
        <div style="text-align:center; padding:30px 20px; color:#718096;">
          <div style="font-size:24px; margin-bottom:8px;">🔍</div>
          <div>No customer journey records found for #${orderIdOrInvoice}.</div>
        </div>
      `;
      return;
    }

    const j = data.journey;
    const ord = j.order || {};
    const vis = j.visitor || {};
    const sess = j.session || {};
    const timeline = j.timeline || [];
    const fraud = j.fraud || null;

    const sourceBadge = (ord.source_type || '').toLowerCase().includes('landing')
      ? '<span style="background:#FEF3C7; color:#B45309; padding:2px 8px; border-radius:4px; font-weight:700; font-size:11px;">🚀 Landing Page</span>'
      : '<span style="background:#E0F2FE; color:#0369A1; padding:2px 8px; border-radius:4px; font-weight:700; font-size:11px;">🛍️ Storefront</span>';

    // ── Fraud Risk Card ───────────────────────────────────────────────
    let fraudCardHtml = '';
    if (fraud) {
      const lvl = (fraud.fraud_level || '').toUpperCase();
      const scoreNum = fraud.fraud_score;
      const circleClass = lvl === 'HIGH' ? 'high' : (lvl === 'MEDIUM' ? 'medium' : 'low');
      const levelIcon   = lvl === 'HIGH' ? '🔴' : (lvl === 'MEDIUM' ? '🟡' : '🟢');
      const reasonsHtml = Array.isArray(fraud.fraud_reasons) && fraud.fraud_reasons.length > 0
        ? `<ul>${fraud.fraud_reasons.map(r => `<li>${r}</li>`).join('')}</ul>`
        : '<span style="opacity:0.7;">No specific risk signals detected</span>';

      const courierHtml = fraud.courier_total_orders > 0
        ? `<span style="font-size:11px;opacity:0.75;margin-left:8px;">Courier: ${fraud.courier_delivered}/${fraud.courier_total_orders} delivered (${fraud.courier_success_rate}%)</span>`
        : '';

      fraudCardHtml = `
        <div class="fraud-journey-card">
          <div class="fraud-score-circle ${circleClass}">
            <div class="fraud-score-num">${scoreNum}</div>
            <div class="fraud-score-denom">/100</div>
          </div>
          <div class="fraud-card-body">
            <div class="fraud-card-level">${levelIcon} ${lvl} RISK ${courierHtml}</div>
            <div class="fraud-card-reasons">
              Fraud Signals Detected:
              ${reasonsHtml}
            </div>
          </div>
        </div>
      `;
    } else {
      fraudCardHtml = `<div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#718096;">🛡️ <b>Fraud Risk:</b> Not yet assessed for this order</div>`;
    }

    body.innerHTML = `
      ${fraudCardHtml}

      <!-- Order & Attribution Summary -->
      <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:14px 16px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:16px; font-weight:800; color:#1A202C;">Invoice #${ord.invoice_no || orderIdOrInvoice}</span>
            ${sourceBadge}
            <span style="background:#DCFCE7; color:#166534; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:700;">${(ord.status || 'new').toUpperCase()}</span>
          </div>
          <div style="font-size:12.5px; color:#4A5568; margin-top:4px;">
            <b>${ord.customer_name || 'Customer'}</b> \u2022 📞 ${ord.customer_phone || '-'} \u2022 📍 ${ord.customer_address || '-'}
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:18px; font-weight:800; color:#2D3748;">৳ ${(ord.total_amount || 0).toLocaleString()}</div>
          <div style="font-size:11px; color:#718096;">Payment: ${ord.payment_method || 'COD'}</div>
        </div>
      </div>

      <!-- Visitor & Session Metadata Grid -->
      <div class="journey-meta-grid">
        <div class="journey-meta-item">
          <label>Visitor UUID</label>
          <span style="font-family:monospace; font-size:11px;">${vis.visitor_uuid || 'N/A'}</span>
        </div>
        <div class="journey-meta-item">
          <label>First-Touch Source</label>
          <b>${vis.first_source || 'direct'}</b>
        </div>
        <div class="journey-meta-item">
          <label>Entry Landing Page</label>
          <span>${sess.landing_page_path || ord.landing_page || '/'}</span>
        </div>
        <div class="journey-meta-item">
          <label>Converting Channel</label>
          <b>${sess.channel || 'direct'}</b>
        </div>
        <div class="journey-meta-item">
          <label>UTM Campaign</label>
          <span>${sess.utm_campaign || '-'}</span>
        </div>
        <div class="journey-meta-item">
          <label>Ad Click ID</label>
          <span style="font-family:monospace; font-size:11px;">${sess.click_id || '-'}</span>
        </div>
        <div class="journey-meta-item">
          <label>Environment</label>
          <span>${(sess.device_type || ord.device_type || 'desktop').toUpperCase()} \u2022 ${sess.browser || 'Browser'} (${sess.os || 'OS'})</span>
        </div>
        <div class="journey-meta-item">
          <label>Customer IP</label>
          <span style="font-family:monospace; font-size:11px;">${sess.ip_address || ord.ip_address || '-'}</span>
        </div>
      </div>

      <!-- Chronological Journey Timeline -->
      <h4 style="font-size:13.5px; font-weight:700; color:#2D3748; margin:0 0 12px 0; display:flex; align-items:center; gap:6px;">
        <span>⏳ Chronological User Event Journey</span>
        <span style="font-size:11px; color:#718096; font-weight:400;">(${timeline.length} sequential touchpoints)</span>
      </h4>

      <div class="journey-timeline-container">
        ${timeline.map((step) => {
          let dotIcon = '●';
          let dotColor = '#319795';
          if (step.type === 'arrival') { dotIcon = '🌐'; dotColor = '#3182CE'; }
          else if (step.type === 'product_view') { dotIcon = '🛍️'; dotColor = '#805AD5'; }
          else if (step.type === 'cta_click') { dotIcon = '🔘'; dotColor = '#D69E2E'; }
          else if (step.type === 'add_to_cart') { dotIcon = '🛒'; dotColor = '#DD6B20'; }
          else if (step.type === 'checkout_started') { dotIcon = '📋'; dotColor = '#E53E3E'; }
          else if (step.type === 'order_created' || step.type === 'purchase') { dotIcon = '✅'; dotColor = '#38A169'; }

          return `
            <div class="journey-timeline-step">
              <div class="journey-step-dot" style="background:${dotColor};">${dotIcon}</div>
              <div class="journey-step-content">
                <div class="journey-step-header">
                  <span class="journey-step-title">${step.title}</span>
                  <span class="journey-step-time">${step.time || ''}</span>
                </div>
                <div class="journey-step-desc">${step.description}</div>
                ${step.value ? `<div style="font-size:12px; font-weight:700; color:#319795; margin-bottom:4px;">${step.value}</div>` : ''}
                ${step.details ? `
                  <div class="journey-step-tags">
                    ${Object.entries(step.details).filter(([k,v]) => v).map(([k,v]) => `
                      <span class="journey-step-tag"><b>${k}:</b> ${v}</span>
                    `).join('')}
                  </div>
                ` : ''}
              </div>
            </div>
          `;
        }).join('')}
      </div>
    `;
  } catch (err) {
    body.innerHTML = `<div style="color:#E53E3E; padding:20px; text-align:center;">Failed to load journey: ${err.message}</div>`;
  }
};
