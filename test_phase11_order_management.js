/**
 * Phase 11 Comprehensive Verification Test Suite
 * Order Management, is_new vs status, Source Separation, Reports & Receipt
 */
const http = require('http');

function makeRequest(options, postData = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let data = '';
      res.on('data', chunk => { data += chunk; });
      res.on('end', () => {
        let json = null;
        try { json = JSON.parse(data); } catch (e) {}
        resolve({ statusCode: res.statusCode, headers: res.headers, body: data, json });
      });
    });
    req.on('error', reject);
    if (postData) {
      req.write(typeof postData === 'string' ? postData : JSON.stringify(postData));
    }
    req.end();
  });
}

async function runTests() {
  console.log('======================================================');
  console.log('🧪 RUNNING PHASE 11 COMPREHENSIVE ORDER MANAGEMENT TESTS');
  console.log('======================================================\n');

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`  ✅ PASS: ${message}`);
      passed++;
    } else {
      console.error(`  ❌ FAIL: ${message}`);
      failed++;
    }
  }

  try {
    // 1. Check Storefront Order Creation & Unread is_new = true
    const testPhone = '01711' + Math.floor(100000 + Math.random() * 900000);
    const checkoutRes = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/checkout',
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'text/html,application/xhtml+xml',
      }
    }, `customer_name=Test+Buyer&customer_phone=${testPhone}&customer_address=Dhaka+Test&delivery_area=inside_dhaka&payment_method=cod&direct_product_id=1&direct_quantity=1`);

    assert([302, 200, 201].includes(checkoutRes.statusCode), 'Storefront checkout succeeds and redirects to order success');

    // 2. Fetch admin orders list and verify canonical source & is_new
    const adminOrdersRes = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/orders',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': 'adm_session_1234567890abcdef'
      }
    });

    assert(adminOrdersRes.statusCode === 200 && adminOrdersRes.json && adminOrdersRes.json.success, 'Admin orders endpoint returns 200 OK');
    
    const orders = adminOrdersRes.json.orders || [];
    const latestOrder = orders.find(o => o.phone === testPhone);

    assert(latestOrder !== undefined, `Found newly created storefront order in admin list`);
    if (latestOrder) {
      assert(latestOrder.is_new === true, 'New order has is_new = true');
      assert(latestOrder.status.toLowerCase() === 'pending', 'New order has status = Pending');
      assert(latestOrder.source === 'MAIN_WEBSITE', 'Storefront order has canonical source = MAIN_WEBSITE');

      // 3. Mark order as viewed via PATCH /api/orders/:id/viewed
      const viewRes = await makeRequest({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/api/orders/${encodeURIComponent(latestOrder.order_number)}/viewed`,
        method: 'PATCH',
        headers: {
          'Accept': 'application/json',
          'x-admin-token': 'adm_session_1234567890abcdef'
        }
      });

      assert(viewRes.statusCode === 200 && viewRes.json && viewRes.json.success, 'Mark order viewed endpoint returns 200 OK');
      assert(viewRes.json.is_new === false, 'Mark order viewed response indicates is_new = false');
      assert(viewRes.json.status.toLowerCase() === 'pending', 'Order status remains Pending (not changed by viewing)');

      // 4. Verify in DB orders list that is_new is now false and status remains pending
      const verifyOrdersRes = await makeRequest({
        hostname: '127.0.0.1',
        port: 8000,
        path: '/api/admin/orders',
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'x-admin-token': 'adm_session_1234567890abcdef'
        }
      });

      const updatedOrder = (verifyOrdersRes.json.orders || []).find(o => o.order_number === latestOrder.order_number);
      assert(updatedOrder && updatedOrder.is_new === false, 'Persistent DB state for viewed order has is_new = false');
      assert(updatedOrder && updatedOrder.status.toLowerCase() === 'pending', 'Persistent DB status remains unchanged (Pending)');
    }

    // 5. Test Order Processing Report API
    const reportRes = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/reports/order-processing?period=today',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': 'adm_session_1234567890abcdef'
      }
    });

    assert(reportRes.statusCode === 200 && reportRes.json && reportRes.json.success, 'Order Processing Report endpoint returns 200 OK');
    const metrics = reportRes.json.report || reportRes.json.metrics;
    assert(metrics && typeof metrics.created === 'number' && typeof metrics.pending === 'number', 'Report contains valid numerical breakdown');

    // 6. Test Storefront Order Success Receipt Print HTML
    if (latestOrder) {
      const successPageRes = await makeRequest({
        hostname: '127.0.0.1',
        port: 8000,
        path: `/order/success/${encodeURIComponent(latestOrder.order_number)}`,
        method: 'GET'
      });

      assert(successPageRes.statusCode === 200, 'Order success page returns 200 OK');
      assert(successPageRes.body.includes('রসিদ ডাউনলোড / প্রিন্ট করুন'), 'Order success page contains Receipt Download/Print button');
      assert(successPageRes.body.includes('window.print()'), 'Receipt button triggers window.print()');
      assert(successPageRes.body.includes('@media print'), 'Print styling rules included on order success page');
    }

  } catch (err) {
    console.error('Test execution error:', err);
    failed++;
  }

  console.log('\n======================================================');
  console.log(`📊 SUMMARY: ${passed} passed, ${failed} failed`);
  console.log('======================================================');

  if (failed > 0) {
    process.exit(1);
  } else {
    process.exit(0);
  }
}

runTests();
