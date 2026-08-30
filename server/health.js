/**
 * Application Health & System Status Module
 * 
 * Provides runtime health diagnostics for CloudPanel, uptime monitors, and VPS status checks.
 */

const { db } = require('./db');

function getHealthStatus() {
  const uptimeSeconds = process.uptime();
  const memUsage = process.memoryUsage();

  // Test SQLite Database Read Connection
  let dbStatus = 'healthy';
  let totalOrders = 0;
  let totalCustomers = 0;

  try {
    const orderCountRow = db.prepare('SELECT COUNT(*) as count FROM orders').get();
    const customerCountRow = db.prepare('SELECT COUNT(*) as count FROM customers').get();
    totalOrders = orderCountRow ? orderCountRow.count : 0;
    totalCustomers = customerCountRow ? customerCountRow.count : 0;
  } catch (err) {
    dbStatus = 'degraded: ' + err.message;
  }

  return {
    status: dbStatus === 'healthy' ? 'ok' : 'degraded',
    environment: process.env.NODE_ENV || 'development',
    timestamp: new Date().toISOString(),
    uptime: {
      seconds: Math.floor(uptimeSeconds),
      formatted: formatUptime(uptimeSeconds)
    },
    system: {
      nodeVersion: process.version,
      platform: process.platform,
      arch: process.arch,
      pid: process.pid
    },
    memory: {
      rssMb: (memUsage.rss / 1024 / 1024).toFixed(2),
      heapUsedMb: (memUsage.heapUsed / 1024 / 1024).toFixed(2),
      heapTotalMb: (memUsage.heapTotal / 1024 / 1024).toFixed(2)
    },
    database: {
      engine: 'SQLite (node:sqlite)',
      status: dbStatus,
      totalOrders,
      totalCustomers
    }
  };
}

function formatUptime(seconds) {
  const d = Math.floor(seconds / (3600 * 24));
  const h = Math.floor((seconds % (3600 * 24)) / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  const s = Math.floor(seconds % 60);
  return `${d}d ${h}h ${m}m ${s}s`;
}

module.exports = {
  getHealthStatus
};
