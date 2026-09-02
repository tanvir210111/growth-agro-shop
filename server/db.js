/**
 * Database Module (SQLite via built-in node:sqlite DatabaseSync)
 * 
 * Provides:
 * - WAL mode & Foreign key enforcement
 * - Auto-migration / table creation
 * - Customer identity resolution
 * - Atomic transactional order creation (orders + order_items)
 * - Server-side idempotency / duplicate submission protection
 * - Order retrieval and status management
 */

const { DatabaseSync } = require('node:sqlite');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const dataDir = path.join(__dirname, '..', 'data');
if (!fs.existsSync(dataDir)) {
  fs.mkdirSync(dataDir, { recursive: true });
}

const dbPath = path.join(dataDir, 'database.sqlite');
const db = new DatabaseSync(dbPath);

// Enable WAL mode for high concurrency and Foreign Keys
db.exec('PRAGMA journal_mode = WAL;');
db.exec('PRAGMA foreign_keys = ON;');

// Initialize Tables
db.exec(`
  CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT UNIQUE NOT NULL,
    address TEXT,
    customer_level INTEGER DEFAULT 1,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_number TEXT UNIQUE NOT NULL,
    customer_id INTEGER NOT NULL REFERENCES customers(id),
    customer_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    address TEXT NOT NULL,
    delivery_zone TEXT NOT NULL,
    delivery_charge INTEGER NOT NULL,
    subtotal INTEGER NOT NULL,
    total INTEGER NOT NULL,
    currency TEXT NOT NULL DEFAULT 'BDT',
    status TEXT NOT NULL DEFAULT 'pending',
    payment_method TEXT NOT NULL DEFAULT 'Cash on Delivery',
    source TEXT NOT NULL DEFAULT 'Landing Page',
    landing_page TEXT NOT NULL DEFAULT '/products/chicken-booster/',
    idempotency_key TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id TEXT NOT NULL,
    product_name TEXT NOT NULL,
    variant_id TEXT NOT NULL,
    variant_name TEXT NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price INTEGER NOT NULL,
    line_total INTEGER NOT NULL
  );

  CREATE INDEX IF NOT EXISTS idx_orders_phone ON orders(phone);
  CREATE INDEX IF NOT EXISTS idx_orders_order_number ON orders(order_number);
  CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders(created_at);
  CREATE INDEX IF NOT EXISTS idx_orders_idempotency ON orders(idempotency_key);
`);

console.log(`[Database] SQLite connected & initialized at: ${dbPath}`);

// Safe Schema Migrations for Growth Agro Unified Order System
const existingColumns = new Set(
  db.prepare("PRAGMA table_info(orders);").all().map(c => c.name)
);

const requiredColumns = [
  { name: 'fraud_level', def: "TEXT DEFAULT 'new_customer'" },
  { name: 'fraud_score', def: "INTEGER DEFAULT 0" },
  { name: 'advance_amount', def: "INTEGER DEFAULT 0" },
  { name: 'advance_paid', def: "INTEGER DEFAULT 0" },
  { name: 'courier_name', def: "TEXT DEFAULT NULL" },
  { name: 'courier_tracking_id', def: "TEXT DEFAULT NULL" },
  { name: 'courier_status', def: "TEXT DEFAULT NULL" },
  { name: 'timeline', def: "TEXT DEFAULT '[]'" }
];

for (const col of requiredColumns) {
  if (!existingColumns.has(col.name)) {
    try {
      db.exec(`ALTER TABLE orders ADD COLUMN ${col.name} ${col.def};`);
    } catch (e) {
      console.warn(`[Migration] Column ${col.name} note:`, e.message);
    }
  }
}

const ALLOWED_ORDER_STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];


/**
 * Generate a collision-safe unique order number
 * Format: CB-YYYYMMDD-XXXX
 */
function generateOrderNumber() {
  const now = new Date();
  const dateStr = now.toISOString().slice(0, 10).replace(/-/g, '');
  
  for (let i = 0; i < 10; i++) {
    const randomSuffix = crypto.randomBytes(3).toString('hex').toUpperCase();
    const candidate = `CB-${dateStr}-${randomSuffix}`;
    
    const existing = db.prepare('SELECT id FROM orders WHERE order_number = ?').get(candidate);
    if (!existing) {
      return candidate;
    }
  }
  
  // Fallback if loop exhausted
  return `CB-${dateStr}-${Date.now().toString(36).toUpperCase()}`;
}

/**
 * Resolve or create customer by phone number
 * Customer identity is resolved, but historical order customer data is retained separately on the order.
 */
function resolveCustomer(name, phone, address) {
  const now = new Date().toISOString();
  
  const existing = db.prepare('SELECT * FROM customers WHERE phone = ?').get(phone);
  if (existing) {
    // Update customer's latest name and address if changed
    db.prepare('UPDATE customers SET name = ?, address = ?, updated_at = ? WHERE id = ?').run(
      name || existing.name,
      address || existing.address,
      now,
      existing.id
    );
    return existing.id;
  }

  const insert = db.prepare(`
    INSERT INTO customers (name, phone, address, customer_level, created_at, updated_at)
    VALUES (?, ?, ?, 1, ?, ?)
  `);
  const result = insert.run(name, phone, address || '', now, now);
  return Number(result.lastInsertRowid);
}

/**
 * Check for duplicate submission within debounce window (60s) or matching idempotency key
 */
function findDuplicateOrder(phone, productId, variantId, idempotencyKey = null) {
  if (idempotencyKey) {
    const byKey = db.prepare(`
      SELECT o.*, oi.product_id, oi.variant_id, oi.quantity, oi.variant_name
      FROM orders o
      JOIN order_items oi ON o.id = oi.order_id
      WHERE o.idempotency_key = ?
    `).get(idempotencyKey);
    return byKey || null;
  }

  // Check recent submission in the last 60 seconds when no idempotency key is passed
  const sixtySecondsAgo = new Date(Date.now() - 60000).toISOString();
  const recentOrder = db.prepare(`
    SELECT o.*, oi.product_id, oi.variant_id, oi.quantity, oi.variant_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.phone = ? AND oi.product_id = ? AND oi.variant_id = ? AND o.created_at >= ?
    ORDER BY o.id DESC LIMIT 1
  `).get(phone, productId, variantId, sixtySecondsAgo);

  return recentOrder || null;
}

/**
 * Create a new order with atomic transaction
 */
function createOrder(orderInput = {}) {
  const now = new Date().toISOString();
  const cName = orderInput.customerName || orderInput.customer_name || 'Customer';
  const cPhone = orderInput.phone || orderInput.customer_phone || '';
  const cAddress = orderInput.address || orderInput.customer_address || orderInput.shipping_address || '-';
  const pId = orderInput.productId || orderInput.product_id || 'chicken-booster';
  const pName = orderInput.productName || orderInput.product_name || 'Product';
  const vId = orderInput.variantId || orderInput.variant_id || 'default';
  const vName = orderInput.variantName || orderInput.variant_name || 'Standard';
  const qty = Number(orderInput.quantity || 1);
  const uPrice = Number(orderInput.unitPrice || orderInput.unit_price || (orderInput.subtotal ? orderInput.subtotal / qty : orderInput.total || 0));
  const sTotal = Number(orderInput.subtotal ?? (uPrice * qty));
  const dZone = orderInput.deliveryZone || orderInput.delivery_zone || orderInput.delivery_area || 'inside';
  const dCharge = Number(orderInput.deliveryCharge ?? orderInput.delivery_charge ?? 0);
  const gTotal = Number(orderInput.total ?? orderInput.total_amount ?? (sTotal + dCharge));
  const curr = orderInput.currency || 'BDT';
  const idempKey = orderInput.idempotencyKey || orderInput.idempotency_key || null;
  const lp = orderInput.landingPage || orderInput.landing_page || '/products/chicken-booster/';
  const src = orderInput.source || 'LANDING_PAGE';
  const payMethod = orderInput.paymentMethod || orderInput.payment_method || 'Cash on Delivery';
  const fLevel = orderInput.fraudLevel || orderInput.fraud_level || null;
  const fScore = (orderInput.fraudScore !== undefined && orderInput.fraudScore !== null) ? Number(orderInput.fraudScore) : null;
  const advAmount = Number(orderInput.advanceAmount ?? orderInput.advance_amount ?? 0);
  const advPaid = Number(orderInput.advancePaid ?? orderInput.advance_paid ?? 0);
  const cNameCourier = orderInput.courierName || orderInput.courier_name || null;
  const timeline = orderInput.timeline || null;

  const orderNumber = orderInput.orderNumber || orderInput.order_number || generateOrderNumber();

  const initialTimeline = Array.isArray(timeline) && timeline.length > 0 ? timeline : [
    { event: 'Order Created', status: 'pending', time: now, note: `অর্ডার গ্রহণ করা হয়েছে (উৎস: ${src})` }
  ];

  db.exec('BEGIN TRANSACTION;');
  try {
    const customerId = resolveCustomer(cName, cPhone, cAddress);

    const insertOrder = db.prepare(`
      INSERT INTO orders (
        order_number, customer_id, customer_name, phone, address,
        delivery_zone, delivery_charge, subtotal, total, currency,
        status, payment_method, source, landing_page, idempotency_key,
        fraud_level, fraud_score, advance_amount, advance_paid,
        courier_name, timeline, created_at, updated_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const orderResult = insertOrder.run(
      orderNumber,
      customerId ?? null,
      cName,
      cPhone,
      cAddress,
      dZone,
      dCharge,
      sTotal,
      gTotal,
      curr,
      payMethod,
      src,
      lp,
      idempKey,
      fLevel,
      fScore,
      advAmount,
      advPaid,
      cNameCourier,
      JSON.stringify(initialTimeline),
      now,
      now
    );
    const orderId = Number(orderResult.lastInsertRowid);

    const insertItem = db.prepare(`
      INSERT INTO order_items (
        order_id, product_id, product_name, variant_id, variant_name,
        quantity, unit_price, line_total
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `);

    if (Array.isArray(orderInput.items) && orderInput.items.length > 0) {
      for (const it of orderInput.items) {
        insertItem.run(
          orderId,
          pId,
          it.name || it.variant_name || pName,
          it.id || it.variant_id || vId,
          it.name || it.variant_name || vName,
          Number(it.quantity || 1),
          Number(it.price || it.unit_price || 0),
          Number(it.total || ((it.price || 0) * (it.quantity || 1)))
        );
      }
    } else {
      insertItem.run(
        orderId,
        pId,
        pName,
        vId,
        vName,
        qty,
        uPrice,
        sTotal
      );
    }

    db.exec('COMMIT;');

    return {
      order_number: orderNumber,
      status: 'pending',
      customer_name: cName,
      phone: cPhone,
      address: cAddress,
      delivery_zone: dZone,
      product: pName,
      product_name: pName,
      variant: vName,
      variant_name: vName,
      quantity: qty,
      unit_price: uPrice,
      subtotal: sTotal,
      delivery_charge: dCharge,
      total: gTotal,
      currency: curr,
      payment_method: payMethod,
      source: src,
      landing_page: lp,
      idempotency_key: idempKey,
      fraud_level: fLevel,
      fraud_score: fScore,
      advance_amount: advAmount,
      advance_paid: advPaid,
      created_at: now
    };
  } catch (err) {
    db.exec('ROLLBACK;');
    throw err;
  }
}


/**
 * Append an event to an order's timeline
 */
function addOrderTimelineEvent(orderNumber, eventName, note = '', status = null) {
  const now = new Date().toISOString();
  const order = db.prepare('SELECT timeline, status FROM orders WHERE order_number = ?').get(orderNumber);
  if (!order) return false;

  let timelineArray = [];
  try {
    timelineArray = order.timeline ? JSON.parse(order.timeline) : [];
  } catch (e) {
    timelineArray = [];
  }

  timelineArray.push({
    event: eventName,
    status: status || order.status,
    time: now,
    note: note
  });

  const update = db.prepare('UPDATE orders SET timeline = ?, updated_at = ? WHERE order_number = ?');
  return update.run(JSON.stringify(timelineArray), now, orderNumber).changes > 0;
}

/**
 * List orders with optional source filter (ALL, MAIN_WEBSITE, LANDING_PAGE)
 */
function listOrders(limit = 100, offset = 0, sourceFilter = null) {
  let query = `
    SELECT 
      o.id, o.order_number, o.customer_id, o.customer_name, o.phone, o.address,
      o.delivery_zone, o.delivery_charge, o.subtotal, o.total, o.currency,
      o.status, o.payment_method, o.source, o.landing_page, 
      o.fraud_level, o.fraud_score, o.advance_amount, o.advance_paid,
      o.courier_name, o.courier_tracking_id, o.courier_status, o.timeline,
      o.created_at, o.updated_at,
      oi.product_id, oi.product_name, oi.variant_id, oi.variant_name, oi.quantity, oi.unit_price
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
  `;

  const params = [];
  if (sourceFilter && sourceFilter !== 'ALL') {
    query += ` WHERE o.source = ? `;
    params.push(sourceFilter);
  }

  query += ` ORDER BY o.id DESC LIMIT ? OFFSET ? `;
  params.push(limit, offset);

  return db.prepare(query).all(...params);
}

/**
 * Get single order by order number (Admin only)
 */
function getOrderByNumber(orderNumber) {
  const row = db.prepare(`
    SELECT 
      o.id, o.order_number, o.customer_id, o.customer_name, o.phone, o.address,
      o.delivery_zone, o.delivery_charge, o.subtotal, o.total, o.currency,
      o.status, o.payment_method, o.source, o.landing_page,
      o.fraud_level, o.fraud_score, o.advance_amount, o.advance_paid,
      o.courier_name, o.courier_tracking_id, o.courier_status, o.timeline,
      o.created_at, o.updated_at,
      oi.product_id, oi.product_name, oi.variant_id, oi.variant_name, oi.quantity, oi.unit_price
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.order_number = ?
  `).get(orderNumber);

  return row || null;
}

/**
 * Update order status and automatically append to timeline
 */
function updateOrderStatus(orderNumber, newStatus, note = '') {
  const normalizedStatus = (newStatus || '').toLowerCase().trim();
  if (!ALLOWED_ORDER_STATUSES.includes(normalizedStatus)) {
    throw new Error(`Invalid status: ${newStatus}. Allowed: ${ALLOWED_ORDER_STATUSES.join(', ')}`);
  }

  const now = new Date().toISOString();
  const order = db.prepare('SELECT timeline FROM orders WHERE order_number = ?').get(orderNumber);
  if (!order) return false;

  let timelineArray = [];
  try {
    timelineArray = order.timeline ? JSON.parse(order.timeline) : [];
  } catch (e) {
    timelineArray = [];
  }

  const statusLabels = {
    pending: 'অর্ডার পেন্ডিং',
    confirmed: 'অর্ডার কনফার্ম করা হয়েছে',
    processing: 'প্রসেসিং শুরু হয়েছে',
    shipped: 'কুরিয়ারে হস্তান্তর করা হয়েছে',
    delivered: 'সফলভাবে ডেলিভারি সম্পন্ন',
    cancelled: 'অর্ডার বাতিল করা হয়েছে'
  };

  timelineArray.push({
    event: `Status: ${normalizedStatus.toUpperCase()}`,
    status: normalizedStatus,
    time: now,
    note: note || (statusLabels[normalizedStatus] || `স্ট্যাটাস পরিবর্তন: ${normalizedStatus}`)
  });

  const update = db.prepare('UPDATE orders SET status = ?, timeline = ?, updated_at = ? WHERE order_number = ?');
  const result = update.run(normalizedStatus, JSON.stringify(timelineArray), now, orderNumber);
  
  return result.changes > 0;
}

/**
  * Update order courier name
  */
function updateOrderCourier(orderNumber, courierName) {
  const now = new Date().toISOString();
  const update = db.prepare('UPDATE orders SET courier_name = ?, updated_at = ? WHERE order_number = ?');
  const result = update.run(courierName || null, now, orderNumber);
  return result.changes > 0;
}

/**
  * Permanently delete an order and its order_items
  */
function deleteOrder(orderNumber) {
  const order = db.prepare('SELECT id FROM orders WHERE order_number = ?').get(orderNumber);
  if (!order) return false;

  db.exec('BEGIN TRANSACTION;');
  try {
    db.prepare('DELETE FROM order_items WHERE order_id = ?').run(order.id);
    const delOrder = db.prepare('DELETE FROM orders WHERE id = ?').run(order.id);
    db.exec('COMMIT;');
    return delOrder.changes > 0;
  } catch (err) {
    db.exec('ROLLBACK;');
    throw err;
  }
}

module.exports = {
  db,
  generateOrderNumber,
  resolveCustomer,
  findDuplicateOrder,
  createOrder,
  listOrders,
  getOrderByNumber,
  updateOrderStatus,
  updateOrderCourier,
  deleteOrder,
  addOrderTimelineEvent,
  ALLOWED_ORDER_STATUSES
};

