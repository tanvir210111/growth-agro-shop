/**
 * scripts/generate_htpasswd.js
 * Generates an Apache/Nginx compatible .htpasswd entry securely using Node.js crypto (SHA-1 / APR1 / Crypt)
 * 
 * Usage:
 *   node scripts/generate_htpasswd.js <username> <password>
 * Example:
 *   node scripts/generate_htpasswd.js growthdemo StrongPass2026!
 */

const crypto = require('crypto');
const fs = require('fs');
const path = require('path');

const username = process.argv[2] || 'growthdemo';
const password = process.argv[3] || 'GrowthAgro2026!';

// SSHA / APR1 / SHA-1 format for Nginx auth_basic: {SHA}Base64(SHA1(password))
const hash = crypto.createHash('sha1').update(password).digest('base64');
const htpasswdLine = `${username}:{SHA}${hash}\n`;

const targetPath = path.join(__dirname, '..', '.htpasswd_example');
fs.writeFileSync(targetPath, htpasswdLine, 'utf8');

console.log('=== HTTP Basic Authentication (.htpasswd) Generated ===');
console.log(`Username: ${username}`);
console.log(`Password: ${password}`);
console.log(`Formatted Entry: ${htpasswdLine.trim()}`);
console.log(`Saved template: ${targetPath}`);
console.log('\nVPS Setup Instructions:');
console.log('1. On your VPS / CloudPanel:');
console.log(`   sudo htpasswd -b -c /home/cloudpanel/htdocs/growthagro.shop/.htpasswd ${username} ${password}`);
console.log('   OR manually write the formatted entry to that file.');
console.log('2. Set secure file permissions:');
console.log('   sudo chown -R cloudpanel:cloudpanel /home/cloudpanel/htdocs/growthagro.shop/.htpasswd');
console.log('   sudo chmod 640 /home/cloudpanel/htdocs/growthagro.shop/.htpasswd');
console.log('3. Reload Nginx:');
console.log('   sudo systemctl reload nginx');
