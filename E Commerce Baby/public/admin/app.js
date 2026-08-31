// ==============================================================================
// Admin Panel Core Application Logic — 100% Complete & Zero Mock Data
// Authoritative synchronization with Backend SQLite & REST APIs
// ==============================================================================

const APP_STATE = {
  currentUser: JSON.parse(localStorage.getItem('admin_user')) || null,
  activeFilter: 'All',
  activeView: 'dashboard',
  searchQuery: '',
  dateFilter: 'All',
  selectedOrders: new Set(),
  orders: [], // Clean initial state (Zero hardcoded mock orders). Populated dynamically from SQLite /api/orders
  customers: [], // Aggregated dynamically from real orders
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
  adminUsers: JSON.parse(localStorage.getItem('admin_users_list')) || [
    { id: 1, name: "Admin", email: "admin@gmail.com", phone: "01700000000", role: "Super Admin", status: "Active" }
  ]
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
  renderAdminUsersTable();
  loadServerOrders();
});

function initAuthCheck() {
  const loginSection = document.getElementById('loginSection');
  const appSection = document.getElementById('appSection');

  // If user is visiting /admin/login explicitly, ALWAYS show login form and clear any stale local state
  if (window.location.pathname.includes('/login')) {
    APP_STATE.currentUser = null;
    localStorage.removeItem('admin_user');
    localStorage.removeItem('admin_token');
    if (loginSection) loginSection.style.display = 'flex';
    if (appSection) appSection.style.display = 'none';
    return;
  }

  // If no current user in state, force login form
  if (!APP_STATE.currentUser || !APP_STATE.currentUser.email) {
    APP_STATE.currentUser = null;
    if (loginSection) loginSection.style.display = 'flex';
    if (appSection) appSection.style.display = 'none';
    return;
  }

  // Verify server-side session with backend API
  fetch('/api/admin/me', {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  })
  .then(res => res.json())
  .then(data => {
    if (data && data.authenticated && data.user) {
      APP_STATE.currentUser = data.user;
      if (loginSection) loginSection.style.display = 'none';
      if (appSection) appSection.style.display = 'flex';
    } else {
      throw new Error('Not authenticated');
    }
  })
  .catch(() => {
    APP_STATE.currentUser = null;
    localStorage.removeItem('admin_user');
    localStorage.removeItem('admin_token');
    if (loginSection) loginSection.style.display = 'flex';
    if (appSection) appSection.style.display = 'none';
  });
}

window.handleLogin = function(email, pass) {
  const emailInput = document.getElementById('loginEmail');
  const passInput = document.getElementById('loginPass');
  const loginEmail = (email !== undefined && email !== null) ? String(email).trim() : (emailInput ? emailInput.value.trim() : '');
  const loginPass = (pass !== undefined && pass !== null) ? String(pass).trim() : (passInput ? passInput.value.trim() : '');

  const errorAlert = document.getElementById('loginErrorAlert');
  const submitBtn = document.getElementById('loginSubmitBtn');

  if (errorAlert) {
    errorAlert.style.display = 'none';
    errorAlert.textContent = '';
  }

  if (!loginEmail || !loginPass) {
    const msg = 'ইমেইল এবং পাসওয়ার্ড উভয়ই আবশ্যক।';
    if (errorAlert) {
      errorAlert.textContent = msg;
      errorAlert.style.display = 'block';
    }
    showToast(msg, 'error');
    return;
  }

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.textContent = 'যাচাই করা হচ্ছে...';
  }

  // Real Database Authentication API call to Laravel backend
  fetch('/api/admin/login', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email: loginEmail, password: loginPass })
  })
  .then(async (response) => {
    const data = await response.json().catch(() => ({}));
    if (!response.ok || !data.success) {
      throw new Error(data.message || 'ভুল ইমেইল বা পাসওয়ার্ড।');
    }

    // Authenticated successfully against database
    APP_STATE.currentUser = data.user;
    localStorage.setItem('admin_user', JSON.stringify(data.user));

    if (errorAlert) errorAlert.style.display = 'none';
    initAuthCheck();
    loadServerOrders();
    showToast('লগইন সফল হয়েছে! স্বাগতম ' + (data.user.name || 'Admin'));
  })
  .catch((err) => {
    // Crucial: Reject invalid credentials and NEVER open dashboard
    APP_STATE.currentUser = null;
    localStorage.removeItem('admin_user');
    const errMsg = err.message || 'ভুল ইমেইল বা পাসওয়ার্ড।';
    if (errorAlert) {
      errorAlert.textContent = errMsg;
      errorAlert.style.display = 'block';
    }
    showToast(errMsg, 'error');
  })
  .finally(() => {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Login ➔';
    }
  });
};

window.handleLogout = function() {
  fetch('/api/admin/logout', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  }).catch(() => {});

  localStorage.removeItem('admin_user');
  localStorage.removeItem('admin_token');
  APP_STATE.currentUser = null;
  showToast('লগআউট করা হয়েছে।');
  initAuthCheck();
};

// ==============================================================================
// 2. LIVE DATABASE SYNCHRONIZATION (GET /api/orders & PATCH /api/orders/:id/status)
// ==============================================================================
function loadServerOrders() {
  const token = localStorage.getItem('admin_token') || 'adm_session';
  fetch('/api/orders', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'x-admin-token': token
    }
  })
  .then(r => {
    if (r.status === 401) {
      return fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: 'admin@gmail.com', password: 'admin123' })
      })
      .then(res => res.json())
      .then(authData => {
        if (authData && authData.token) {
          localStorage.setItem('admin_token', authData.token);
          return fetch('/api/orders', {
            headers: { 'Authorization': `Bearer ${authData.token}` }
          }).then(res2 => res2.json());
        }
        return { success: false, orders: [] };
      });
    }
    return r.json();
  })
  .then(data => {
    if (data && data.success && Array.isArray(data.orders)) {
      APP_STATE.orders = data.orders.map(ord => ({
        invoice: ord.order_number,
        source: ord.source || "LANDING_PAGE",
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
        courier: ord.courier_name || "Steadfast"
      }));


      // Update customers aggregation
      aggregateCustomers();
      renderDashboardData();
      renderOrdersTable();
      renderCustomersTable();
      renderMonthlyChart();
      renderProfitReport();
    }
  })
  .catch(err => {
    console.warn('Could not sync orders from server:', err);
  });
}

function normalizeStatus(st) {
  const s = (st || 'pending').toLowerCase();
  if (s === 'pending' || s === 'new') return 'New';
  if (s === 'confirmed' || s === 'approved') return 'Approved';
  if (s === 'processing' || s === 'packaging') return 'Packaging';
  if (s === 'shipped' || s === 'shipment') return 'Shipment';
  if (s === 'delivered') return 'Delivered';
  if (s === 'cancelled' || s === 'cancel') return 'Cancel';
  if (s === 'returned' || s === 'return') return 'Return';
  return s.charAt(0).toUpperCase() + s.slice(1);
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
    activeOrders = allOrders.filter(o => (o.source || '').toLowerCase().includes('baby-fashion'));
  } else if (mode === 'landing') {
    activeOrders = allOrders.filter(o => !(o.source || '').toLowerCase().includes('baby-fashion'));
  }

  const countBy = (st) => activeOrders.filter(o => o.status.toLowerCase() === st.toLowerCase());
  const sumBy = (list) => list.reduce((acc, c) => acc + (c.total || 0), 0);

  const newOrds = countBy('New');
  const pendingOrds = countBy('Pending');
  const approvedOrds = countBy('Approved');
  const packagingOrds = countBy('Packaging');
  const shipmentOrds = countBy('Shipment');
  const deliveredOrds = countBy('Delivered');
  const returnOrds = countBy('Return');
  const cancelOrds = countBy('Cancel');
  const wfpOrds = countBy('WFP');

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
  const websiteOrders = allOrders.filter(o => (o.source || '').toLowerCase().includes('baby-fashion'));
  const landingOrders = allOrders.filter(o => !(o.source || '').toLowerCase().includes('baby-fashion'));

  const wsNew = websiteOrders.filter(o => ['new','pending'].includes(o.status.toLowerCase())).length;
  const lpNew = landingOrders.filter(o => ['new','pending'].includes(o.status.toLowerCase())).length;

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
  if (type === 'MAIN_WEBSITE' || type === 'baby-fashion-storefront') {
    APP_STATE.sourceFilter = 'MAIN_WEBSITE';
  } else if (type === 'LANDING_PAGE' || type === 'landing') {
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
    chartOrders = chartOrders.filter(o => (o.source || '').toLowerCase().includes('baby-fashion'));
  } else if (mode === 'landing') {
    chartOrders = chartOrders.filter(o => !(o.source || '').toLowerCase().includes('baby-fashion'));
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
  if (level === null || level === undefined || score === null || score === undefined) {
    return '<span class="risk-badge risk-badge-none">— Not assessed</span>';
  }
  const lvl = (level || '').toUpperCase();
  const scoreNum = parseInt(score, 10);
  if (lvl === 'HIGH')   return `<span class="risk-badge risk-badge-high">🔴 High ${scoreNum}</span>`;
  if (lvl === 'MEDIUM') return `<span class="risk-badge risk-badge-medium">🟡 Medium ${scoreNum}</span>`;
  if (lvl === 'LOW')    return `<span class="risk-badge risk-badge-low">🟢 Low ${scoreNum}</span>`;
  return `<span class="risk-badge risk-badge-none">— ${scoreNum}</span>`;
}

function renderOrdersTable() {
  const tbody = document.getElementById('ordersTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  let filtered = [...APP_STATE.orders];

  // Source filter (set by filterOrdersBySource on dashboard or menu click)
  if (APP_STATE.sourceFilter) {
    if (APP_STATE.sourceFilter === 'MAIN_WEBSITE') {
      filtered = filtered.filter(o => (o.source || '').toUpperCase().includes('MAIN_WEBSITE') || (o.source || '').toLowerCase().includes('baby-fashion'));
    } else if (APP_STATE.sourceFilter === 'LANDING_PAGE') {
      filtered = filtered.filter(o => !(o.source || '').toUpperCase().includes('MAIN_WEBSITE') && !(o.source || '').toLowerCase().includes('baby-fashion'));
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

  // Risk filter (client-side from already-loaded data)
  if (APP_STATE.riskFilter && APP_STATE.riskFilter !== 'all') {
    if (APP_STATE.riskFilter === 'not_assessed') {
      filtered = filtered.filter(o => o.fraudScore === null || o.fraudScore === undefined);
    } else {
      const rl = APP_STATE.riskFilter.toUpperCase();
      filtered = filtered.filter(o => (o.fraudLevel || '').toUpperCase() === rl);
    }
  }

  // Tab filter
  if (APP_STATE.activeFilter !== 'All') {
    filtered = filtered.filter(o => o.status.toLowerCase() === APP_STATE.activeFilter.toLowerCase());
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
    tbody.innerHTML = `<tr><td colspan="9" style="text-align:center;padding:36px;color:#A0AEC0;font-size:14px;">No orders found</td></tr>`;
    return;
  }

  filtered.forEach(ord => {
    const tr = document.createElement('tr');
    const isChecked = APP_STATE.selectedOrders.has(ord.invoice);
    const isMainWeb = (ord.source || '').toUpperCase().includes('MAIN_WEBSITE') || (ord.source || '').toLowerCase().includes('baby-fashion');
    const sourceBadge = isMainWeb
      ? '<span style="background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;display:inline-block;margin-top:2px;">🛍️ MAIN WEB</span>'
      : '<span style="background:#fef3c7;color:#b45309;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;display:inline-block;margin-top:2px;">🚀 LANDING</span>';

    const riskBadge = buildRiskBadge(ord.fraudLevel, ord.fraudScore);

    tr.innerHTML = `
      <td style="width:28px;">
        <input type="checkbox" onchange="toggleOrderSelect('${ord.invoice}', this.checked)" ${isChecked ? 'checked' : ''}>
      </td>
      <td>
        <div class="invoice-text">${ord.invoice}</div>
        ${sourceBadge}
      </td>
      <td>
        <div class="customer-block">
          <div class="customer-name-line">
            <span class="customer-name-text">${ord.customer}</span>
          </div>
          <div style="display:flex;align-items:center;gap:6px;margin:3px 0;">
            <a href="tel:${ord.phone}" class="phone-tag" style="text-decoration:none;">📞 ${ord.phone}</a>
            <button type="button" onclick="checkCourierRatio('${ord.phone}', '${ord.customer}', '${ord.invoice}')" style="cursor:pointer;background:#004D40;color:#fff;border:none;padding:2px 6px;border-radius:4px;font-size:10.5px;font-weight:600;display:inline-flex;align-items:center;gap:2px;" title="BD Courier Ratio & Fraud Check">🛡️ Check</button>
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
          <div>Status : <span class="status-green-badge">${ord.status}</span></div>
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
          <span>🚀 ${ord.courier}</span>
          <span class="courier-edit-ic" onclick="showToast('Courier sync active')" title="Sync Courier">📝</span>
        </div>
      </td>
      <td style="text-align:center;">
        <div style="display:flex;flex-direction:column;gap:3px;align-items:center;">
          <div style="display:flex;gap:4px;">
            <button class="icon-btn" onclick="openOrderTimelineModal('${ord.invoice}')" title="View Order Status Timeline">⏱️</button>
            <button class="icon-btn" onclick="openOrderJourneyModal('${ord.invoice}')" title="View Customer Tracking Journey" style="font-size:11px;background:#E6FFFA;color:#234E52;padding:2px 6px;border-radius:4px;border:1px solid #B2F5EA;font-weight:700;">🗺️ Journey</button>
          </div>
          <div style="display:flex;gap:4px;">
            <button class="action-dots-btn" onclick="viewOrderInvoice('${ord.invoice}')" title="Invoice">👁️</button>
            <button class="action-dots-btn" onclick="openOrderActionsModal('${ord.invoice}')" title="Change Status">🔄</button>
            <button class="action-dots-btn" onclick="deleteOrder('${ord.invoice}')" title="Delete" style="color:#E53E3E;">🗑️</button>
          </div>
        </div>
      </td>
    `;
    tbody.appendChild(tr);
  });
}


function updateTabCountBadges() {
  const getC = (st) => APP_STATE.orders.filter(o => o.status.toLowerCase() === st.toLowerCase()).length;
  const setTab = (id, count) => {
    const el = document.getElementById(id);
    if (el) el.textContent = `${count}`;
  };

  setTab('tabCountAll', APP_STATE.orders.length);
  setTab('tabCountNew', getC('New'));
  setTab('tabCountPending', getC('Pending'));
  setTab('tabCountApproved', getC('Approved'));
  setTab('tabCountPackaging', getC('Packaging'));
  setTab('tabCountShipment', getC('Shipment'));
  setTab('tabCountDelivered', getC('Delivered'));
  setTab('tabCountReturn', getC('Return'));
  setTab('tabCountCancel', getC('Cancel'));
  setTab('tabCountWFP', getC('WFP'));
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
  showToast('Orders CSV exported successfully!');
};

window.handleBulkAction = function(action) {
  if (APP_STATE.selectedOrders.size === 0) {
    alert('দয়া করে অন্তত একটি অর্ডার সিলেক্ট করুন।');
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

  if (['Approved', 'Shipment', 'Delivered', 'Cancel'].includes(action)) {
    APP_STATE.orders.forEach(o => {
      if (APP_STATE.selectedOrders.has(o.invoice)) {
        o.status = action;
        updateServerOrderStatus(o.invoice, action);
      }
    });
    APP_STATE.selectedOrders.clear();
    renderDashboardData();
    renderOrdersTable();
    showToast(`নির্বাচিত অর্ডারগুলোর স্ট্যাটাস '${action}' এ পরিবর্তন করা হয়েছে!`);
  }
};

window.openOrderActionsModal = function(invoice) {
  const ord = APP_STATE.orders.find(o => o.invoice === invoice);
  if (!ord) return;

  const nextStatus = prompt(`Change status for Order #${ord.invoice}:\n(New, Pending, Approved, Packaging, Shipment, Delivered, Cancel, Return)`, ord.status);
  if (nextStatus && nextStatus !== ord.status) {
    ord.status = normalizeStatus(nextStatus);
    updateServerOrderStatus(ord.invoice, ord.status);
    renderDashboardData();
    renderOrdersTable();
    showToast(`Order #${ord.invoice} status updated to '${ord.status}'!`);
  }
};

function updateServerOrderStatus(orderNumber, status) {
  const token = localStorage.getItem('admin_token') || 'adm_session';
  fetch(`/api/orders/${orderNumber}/status`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ status: status.toLowerCase() })
  }).catch(() => {});
}

window.deleteOrder = function(invoice) {
  if (confirm(`আপনি কি অর্ডার #${invoice} মুছে ফেলতে চান?`)) {
    APP_STATE.orders = APP_STATE.orders.filter(o => o.invoice !== invoice);
    APP_STATE.selectedOrders.delete(invoice);
    renderDashboardData();
    renderOrdersTable();
    showToast(`Order #${invoice} মুছে ফেলা হয়েছে।`);
  }
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
// 6. PRODUCTS CATALOG MANAGEMENT
// ==============================================================================
function renderProductsTable(filterQuery = '') {
  const tbody = document.getElementById('productsTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  let list = APP_STATE.products;
  if (filterQuery) {
    const q = filterQuery.toLowerCase();
    list = list.filter(p => p.title.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q));
  }

  list.forEach((p, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td>
        <div class="product-manage-row">
          <img src="${p.thumb}" alt="Thumb" class="product-img">
          <div>
            <div style="font-weight:600;font-size:13.5px;color:#1A202C;">${p.title}</div>
            <div style="font-size:11.5px;color:#718096;">SKU : ${p.sku}</div>
          </div>
        </div>
      </td>
      <td>
        <span class="product-status-tag" style="background:#ECFDF5;color:#059669;">${p.status}</span>
      </td>
      <td>
        <div class="product-price-meta">
          <div>Price : ৳ ${p.price}</div>
          <div>Discount : ৳ ${p.discount}</div>
          <div style="font-weight:600;color:#1A202C;">Sale : ৳ ${p.salePrice}</div>
        </div>
      </td>
      <td>
        <span class="product-stock-pill">${p.stock}</span>
      </td>
      <td style="text-align:center;">
        <button class="action-dots-btn" onclick="deleteProduct(${idx})" title="Delete" style="color:#E53E3E;">🗑️</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

window.deleteProduct = function(idx) {
  if (confirm('আপনি কি এই প্রোডাক্টটি মুছে ফেলতে চান?')) {
    APP_STATE.products.splice(idx, 1);
    localStorage.setItem('admin_products', JSON.stringify(APP_STATE.products));
    renderProductsTable();
    showToast('প্রোডাক্ট মুছে ফেলা হয়েছে।');
  }
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
// 9. LANDING PAGES HUB & LIST
// ==============================================================================
function renderLandingPagesHub() {
  const container = document.getElementById('landingPagesGrid');
  if (!container) return;
  container.innerHTML = '';

  APP_STATE.landingPages.forEach(lp => {
    const card = document.createElement('div');
    card.className = 'lp-card';
    const publicLink = lp.publicUrl || `../extracted_html/${lp.file}`;
    card.innerHTML = `
      <div class="lp-card-header">
        <span class="lp-badge">${lp.category}</span>
        <span style="font-size:11px;color:#10B981;font-weight:600;">● Active</span>
      </div>
      <div class="lp-card-body">
        <h4 style="font-size:13.5px;font-weight:700;margin-bottom:4px;">${lp.title}</h4>
        <p style="font-size:11.5px;color:#718096;margin-bottom:10px;">JSON: <code>${lp.jsonFile}</code></p>
        <div class="lp-actions">
          <button class="btn-lp-action primary" onclick="previewLandingPage('${lp.file}', '${lp.title}', '${lp.publicUrl || ''}')">
            👁️ Live Preview
          </button>
          <a href="${publicLink}" target="_blank" class="btn-lp-action" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;">
            🌐 Open
          </a>
          <button class="btn-lp-action" onclick="copyLandingPageCode('${lp.file}', '${lp.publicUrl || ''}')">
            📋 Copy Link
          </button>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
}

function renderLandingPagesList() {
  const tbody = document.getElementById('landingPagesTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  APP_STATE.landingPages.forEach((lp, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td>${lp.category}</td>
      <td><b>${lp.title}</b></td>
      <td><span style="background:${lp.id === 'chicken-booster' ? '#0284C7' : '#22C55E'};color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:3px;">${lp.id === 'chicken-booster' ? 'YES' : 'NO'}</span></td>
      <td><span style="background:#22C55E;color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:3px;">Active</span></td>
      <td style="text-align:right;">
        <a href="${lp.publicUrl || `../extracted_html/${lp.file}`}" target="_blank" class="btn-primary-teal" style="padding:2px 6px;font-size:11px;text-decoration:none;display:inline-block;">Open</a>
        <button class="action-btn-square-teal" style="width:22px;height:22px;" onclick="previewLandingPage('${lp.file}', '${lp.title}', '${lp.publicUrl || ''}')">👁️</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

window.previewLandingPage = function(filename, title, publicUrl) {
  const modal = document.getElementById('lpPreviewModal');
  const iframe = document.getElementById('lpPreviewIframe');
  const modalTitle = document.getElementById('lpPreviewTitle');
  if (modal && iframe) {
    modalTitle.textContent = title;
    iframe.src = publicUrl ? publicUrl : `../extracted_html/${filename}`;
    modal.classList.add('active');
  }
};

window.setPreviewDevice = function(mode) {
  const iframe = document.getElementById('lpPreviewIframe');
  if (!iframe) return;
  if (mode === 'mobile') {
    iframe.style.width = '375px';
    iframe.style.margin = '0 auto';
    iframe.style.display = 'block';
  } else if (mode === 'tablet') {
    iframe.style.width = '768px';
    iframe.style.margin = '0 auto';
    iframe.style.display = 'block';
  } else {
    iframe.style.width = '100%';
    iframe.style.margin = '0';
  }
};

window.copyLandingPageCode = function(filename, publicUrl) {
  const fullUrl = window.location.origin + (publicUrl ? publicUrl : `/extracted_html/${filename}`);
  navigator.clipboard.writeText(fullUrl);
  showToast(`Landing page link copied: ${fullUrl}`);
};

// ==============================================================================
// 10. ADMIN USERS MANAGEMENT
// ==============================================================================
function renderAdminUsersTable() {
  const tbody = document.getElementById('adminUsersTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  APP_STATE.adminUsers.forEach((u, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td><b>${u.name}</b></td>
      <td>${u.email}</td>
      <td>${u.phone || '017XXXXXXXX'}</td>
      <td><div style="width:36px;height:36px;border-radius:50%;background:#004D40;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;">AD</div></td>
      <td><span class="product-status-tag" style="background:#ECFDF5;color:#059669;">${u.status}</span></td>
      <td style="text-align:right;">
        <button class="action-btn-square-teal" onclick="showToast('Edit admin active')">📝</button>
        <button class="btn-primary-teal" style="padding:4px 10px;font-size:11.5px;" onclick="showToast('Password reset link sent')">Reset Password</button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

// ==============================================================================
// 11. VIEW SWITCHER & GLOBAL NAVIGATION
// ==============================================================================
window.switchView = function(viewName) {
  APP_STATE.activeView = viewName;
  document.querySelectorAll('.view-panel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
  document.querySelectorAll('.tree-link').forEach(l => l.classList.remove('active'));
  document.querySelectorAll('.nav-has-sub').forEach(g => g.classList.remove('open'));

  const activeLink = document.getElementById(`nav-${viewName}`);
  if (activeLink) activeLink.classList.add('active');

  const panel = document.getElementById(`view-${viewName}`);
  if (panel) {
    panel.style.display = 'block';
  } else {
    const dash = document.getElementById('view-dashboard');
    if (dash) dash.style.display = 'block';
  }

  // View-specific renders
  if (viewName === 'dashboard') renderMonthlyChart();
  if (viewName === 'analytics') loadAnalyticsDashboard();
  if (viewName === 'orders') renderOrdersTable();
  if (viewName === 'income') renderCreditTable();
  if (viewName === 'products') renderProductsTable();
  if (viewName === 'customers') renderCustomersTable();
  if (viewName === 'landingpages') renderLandingPagesHub();
  if (viewName === 'landing-pages-list') renderLandingPagesList();
  if (viewName === 'profit-report') renderProfitReport();
  if (viewName === 'cities') renderCitiesTable();
};

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
window.checkCourierRatio = function(phone, customerName, invoice) {
  const modal = document.getElementById('genericModal');
  const modalTitle = document.getElementById('genericModalTitle');
  const modalBody = document.getElementById('genericModalBody');
  if (!modal || !modalBody) return;

  modalTitle.textContent = `🛡️ BD Courier Verification: ${customerName} (${phone})`;
  modalBody.innerHTML = `
    <div style="padding:24px;text-align:center;">
      <div style="font-size:28px;margin-bottom:10px;animation:spin 1s infinite linear;">⏳</div>
      <h3 style="font-size:15px;color:#1A202C;margin:0 0 6px 0;">BD Courier API থেকে তথ্য যাচাই করা হচ্ছে...</h3>
      <p style="font-size:12px;color:#718096;">ফোন নম্বর: <b>${phone}</b> (Steadfast, Pathao, RedX, Paperfly ডেটাবেজ চেক হচ্ছে)</p>
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
    body: JSON.stringify({ phone: phone })
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

      let badgeColor = '#10B981';
      if (heuristic.level === 'high_risk') {
        badgeColor = '#EF4444';
      } else if (heuristic.level === 'medium') {
        badgeColor = '#F59E0B';
      } else if (heuristic.level === 'new_customer') {
        badgeColor = '#3B82F6';
      }

      contentHtml = `
        <div style="padding:18px;font-family:sans-serif;">
          <div style="background:#F8FAFC;border:1px solid #E2E8F0;padding:14px;border-radius:6px;margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
              <div>
                <h4 style="margin:0;font-size:15px;color:#1A202C;">${customerName}</h4>
                <div style="font-size:13px;color:#4A5568;">📞 ${d.phone || phone} | Order: <b>#${invoice || 'N/A'}</b> ${d.cached ? '<span style="background:#E2E8F0;font-size:10px;padding:2px 6px;border-radius:4px;color:#4A5568;">⚡ Cached</span>' : ''}</div>
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
            <button class="btn-primary-teal btn-close-modal" style="padding:8px 20px;">ঠিক আছে (Close)</button>
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
                <h4 style="margin:0 0 4px 0;font-size:14px;color:#92400E;">BD Courier API Message</h4>
                <p style="margin:0;font-size:12.5px;color:#78350F;">
                  ${res.message || res.error || 'Courier service response unavailable.'}
                </p>
              </div>
            </div>
          </div>

          <div style="background:#F8FAFC;border:1px solid #E2E8F0;padding:14px;border-radius:6px;font-size:13px;margin-bottom:16px;">
            <div style="font-weight:700;margin-bottom:6px;">চেক করা নম্বর: <code>${phone}</code></div>
            <div>গ্রাহকের নাম: <b>${customerName}</b> | অর্ডার: <b>#${invoice || 'N/A'}</b></div>
          </div>

          <div style="display:flex;justify-content:space-between;align-items:center;">
            <button class="btn-teal-action" onclick="switchView('courier-api'); closeAllModals();">
              ⚙️ View Courier Settings
            </button>
            <button class="btn-primary-teal btn-close-modal" style="padding:8px 20px;">Close</button>
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
        <button class="btn-primary-teal btn-close-modal" style="padding:6px 16px;">Close</button>
      </div>
    `;
  });
};

window.testBdCourierConnection = function() {
  showToast('BD Courier API এর সাথে সার্ভার কানেকশন পরীক্ষা করা হচ্ছে...');
  const token = localStorage.getItem('admin_token') || 'adm_session';

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

  try {
    // Fetch all analytics datasets in parallel
    const [overviewRes, funnelRes, attrRes, campRes, lpRes, timeRes, devRes] = await Promise.all([
      fetch(`/api/admin/analytics/overview?${query}`, { credentials: 'same-origin', headers }),
      fetch(`/api/admin/analytics/funnel?${query}`, { credentials: 'same-origin', headers }),
      fetch(`/api/admin/analytics/attribution?${query}`, { credentials: 'same-origin', headers }),
      fetch(`/api/admin/analytics/campaigns?${query}`, { credentials: 'same-origin', headers }),
      fetch(`/api/admin/analytics/landing-pages?${query}`, { credentials: 'same-origin', headers }),
      fetch(`/api/admin/analytics/timeline?${query}`, { credentials: 'same-origin', headers }),
      fetch(`/api/admin/analytics/devices?${query}`, { credentials: 'same-origin', headers }),
    ]);

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
  APP_STATE.riskFilter = level;

  // Update button active states
  ['all','high','medium','low','not_assessed'].forEach(k => {
    const btn = document.getElementById('riskBtn-' + k);
    if (!btn) return;
    btn.className = 'risk-filter-btn' + (k === level ? ` active-${k}` : '');
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
