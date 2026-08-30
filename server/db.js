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
function createOrder({
  customerName,
  phone,
  address,
  productId,
  productName,
  variantId,
  variantName,
  quantity,
  unitPrice,
  subtotal,
  deliveryZone,
  deliveryCharge,
  total,
  currency = 'BDT',
  idempotencyKey = null,
  landingPage = '/products/chicken-booster/'
}) {
  const now = new Date().toISOString();
  const orderNumber = generateOrderNumber();

  db.exec('BEGIN TRANSACTION;');
  try {
    const customerId = resolveCustomer(customerName, phone, address);

    const insertOrder = db.prepare(`
      INSERT INTO orders (
        order_number, customer_id, customer_name, phone, address,
        delivery_zone, delivery_charge, subtotal, total, currency,
        status, payment_method, source, landing_page, idempotency_key,
        created_at, updated_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'Cash on Delivery', 'Landing Page', ?, ?, ?, ?)
    `);

    const orderResult = insertOrder.run(
      orderNumber, customerId, customerName, phone, address,
      deliveryZone, deliveryCharge, subtotal, total, currency,
      landingPage, idempotencyKey, now, now
    );
    const orderId = Number(orderResult.lastInsertRowid);

    const insertItem = db.prepare(`
      INSERT INTO order_items (
        order_id, product_id, product_name, variant_id, variant_name,
        quantity, unit_price, line_total
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `);

    insertItem.run(
      orderId, productId, productName, variantId, variantName,
      quantity, unitPrice, subtotal
    );

    db.exec('COMMIT;');

    return {
      id: orderId,
      order_number: orderNumber,
      customer_id: customerId,
      customer_name: customerName,
      phone,
      address,
      delivery_zone: deliveryZone,
      delivery_charge: deliveryCharge,
      subtotal,
      total,
      currency,
      status: 'pending',
      payment_method: 'Cash on Delivery',
      product_id: productId,
      product_name: productName,
      variant_id: variantId,
      variant_name: variantName,
      quantity,
      created_at: now
    };
  } catch (err) {
    db.exec('ROLLBACK;');
    throw err;
  }
}

/**
 * List all orders with items (Admin only)
 */
function listOrders(limit = 100, offset = 0) {
  const rows = db.prepare(`
    SELECT 
      o.id, o.order_number, o.customer_id, o.customer_name, o.phone, o.address,
      o.delivery_zone, o.delivery_charge, o.subtotal, o.total, o.currency,
      o.status, o.payment_method, o.source, o.landing_page, o.created_at, o.updated_at,
      oi.product_id, oi.product_name, oi.variant_id, oi.variant_name, oi.quantity, oi.unit_price
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    ORDER BY o.id DESC
    LIMIT ? OFFSET ?
  `).all(limit, offset);

  return rows;
}

/**
 * Get single order by order number (Admin only)
 */
function getOrderByNumber(orderNumber) {
  const row = db.prepare(`
    SELECT 
      o.id, o.order_number, o.customer_id, o.customer_name, o.phone, o.address,
      o.delivery_zone, o.delivery_charge, o.subtotal, o.total, o.currency,
      o.status, o.payment_method, o.source, o.landing_page, o.created_at, o.updated_at,
      oi.product_id, oi.product_name, oi.variant_id, oi.variant_name, oi.quantity, oi.unit_price
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.order_number = ?
  `).get(orderNumber);

  return row || null;
}

/**
 * Update order status (Admin only)
 */
function updateOrderStatus(orderNumber, newStatus) {
  const normalizedStatus = (newStatus || '').toLowerCase().trim();
  if (!ALLOWED_ORDER_STATUSES.includes(normalizedStatus)) {
    throw new Error(`Invalid status: ${newStatus}. Allowed: ${ALLOWED_ORDER_STATUSES.join(', ')}`);
  }

  const now = new Date().toISOString();
  const update = db.prepare('UPDATE orders SET status = ?, updated_at = ? WHERE order_number = ?');
  const result = update.run(normalizedStatus, now, orderNumber);
  
  return result.changes > 0;
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
  ALLOWED_ORDER_STATUSES
};
