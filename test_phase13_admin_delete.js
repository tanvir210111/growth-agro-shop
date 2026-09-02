const http = require('http');

function makeRequest(options, postData = null) {
  return new Promise((resolve, reject) => {
    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          const parsed = JSON.parse(body);
          resolve({ status: res.statusCode, body: parsed, raw: body });
        } catch (e) {
          resolve({ status: res.statusCode, body: null, raw: body });
        }
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
  console.log('--- PHASE 13 ADMIN DELETE & DB INTEGRATION TESTS ---');
  let passed = 0;
  let failed = 0;

  function assert(condition, name) {
    if (condition) {
      console.log(`✅ PASS: ${name}`);
      passed++;
    } else {
      console.error(`❌ FAIL: ${name}`);
      failed++;
    }
  }

  const token = 'adm_' + 'a'.repeat(32);

  try {
    // 1. Unauthenticated GET /api/admin/admins -> 401
    const unauthGet = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/admins',
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });
    assert(unauthGet.status === 401, 'Unauthenticated list request returns 401');

    // 2. Authenticated GET /api/admin/admins -> 200, count is 1 (only super admin)
    const authGet = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/admins',
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'x-admin-token': token
      }
    });
    assert(authGet.status === 200, 'Authenticated list returns 200');
    assert(authGet.body && authGet.body.success === true, 'Response indicates success: true');
    assert(authGet.body.count === 1, `Total admin count in DB is exactly 1 (got: ${authGet.body.count})`);

    const primaryAdmin = authGet.body.admins[0];
    assert(primaryAdmin.email === 'admin@gmail.com', `Remaining admin is admin@gmail.com (got: ${primaryAdmin.email})`);
    assert(primaryAdmin.role === 'super_admin', `Remaining admin role is super_admin (got: ${primaryAdmin.role})`);
    assert(!primaryAdmin.hasOwnProperty('password'), 'Password field is NEVER returned');

    // 3. Create a temporary moderator to test deletion
    const createMod = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/admins',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'x-admin-token': token
      }
    }, {
      name: 'Temp Moderator',
      email: 'temp_mod_' + Date.now() + '@example.com',
      phone: '01711112222',
      password: 'password123',
      role: 'moderator',
      status: 'Active'
    });
    assert(createMod.status === 201, 'Super Admin can create a Moderator');
    const modId = createMod.body.admin.id;

    // 4. Verify count is now 2
    const listAfterCreate = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/admins',
      method: 'GET',
      headers: { 'Accept': 'application/json', 'x-admin-token': token }
    });
    assert(listAfterCreate.body.count === 2, 'Admin list now has 2 accounts');

    // 5. Delete the temporary moderator -> 200
    const deleteMod = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/admins/${modId}`,
      method: 'DELETE',
      headers: { 'Accept': 'application/json', 'x-admin-token': token }
    });
    assert(deleteMod.status === 200, 'Super Admin can delete moderator');
    assert(deleteMod.body.success === true, 'Delete response success: true');

    // 6. Verify count is back to 1
    const listAfterDelete = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/admins',
      method: 'GET',
      headers: { 'Accept': 'application/json', 'x-admin-token': token }
    });
    assert(listAfterDelete.body.count === 1, 'Admin count after deletion is back to 1');
    assert(listAfterDelete.body.admins[0].email === 'admin@gmail.com', 'Only admin@gmail.com remains');

    // 7. Attempt to delete non-existent ID -> 404
    const delete404 = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: '/api/admin/admins/999999',
      method: 'DELETE',
      headers: { 'Accept': 'application/json', 'x-admin-token': token }
    });
    assert(delete404.status === 404, 'Deleting non-existent admin returns 404');

    // 8. Attempt to delete final remaining super admin -> 403
    const deleteFinalSuper = await makeRequest({
      hostname: '127.0.0.1',
      port: 8000,
      path: `/api/admin/admins/${primaryAdmin.id}`,
      method: 'DELETE',
      headers: { 'Accept': 'application/json', 'x-admin-token': token }
    });
    assert(deleteFinalSuper.status === 403, 'Deleting the final remaining Super Admin is forbidden (403)');

    // 9. Confirm Growth Agro Admin was deleted and does NOT exist
    const growthAgroCheck = listAfterDelete.body.admins.find(a => a.email === 'admin@growthagro.shop');
    assert(!growthAgroCheck, 'Growth Agro Admin (admin@growthagro.shop) does NOT exist in DB');

  } catch (e) {
    console.error('Test execution error:', e);
    failed++;
  }

  console.log(`\n========================================`);
  console.log(`TOTAL: Passed: ${passed}, Failed: ${failed}`);
  console.log(`========================================`);
  process.exit(failed > 0 ? 1 : 0);
}

runTests();
