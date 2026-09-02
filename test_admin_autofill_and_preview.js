const fs = require('fs');
const path = require('path');

const adminHtmlPath = path.join(__dirname, 'E Commerce Baby', 'public', 'admin', 'index.html');
const adminJsPath = path.join(__dirname, 'E Commerce Baby', 'public', 'admin', 'app.js');

let hasErrors = false;

function assert(condition, message) {
    if (!condition) {
        console.error(`❌ FAIL: ${message}`);
        hasErrors = true;
    } else {
        console.log(`✅ PASS: ${message}`);
    }
}

function runTests() {
    console.log("Running Admin SPA Autofill & Live Preview Regression Tests...");

    if (!fs.existsSync(adminHtmlPath) || !fs.existsSync(adminJsPath)) {
        console.error("❌ FAIL: Could not find admin HTML or JS files.");
        process.exit(1);
    }

    const htmlContent = fs.readFileSync(adminHtmlPath, 'utf8');
    const jsContent = fs.readFileSync(adminJsPath, 'utf8');

    // 1. Verify no search/filter input contains admin@gmail.com as a default value
    assert(!htmlContent.includes('value="admin@gmail.com"'), "HTML does not contain 'admin@gmail.com' as a default value for any input.");

    // 2. Verify search/filter inputs have safe autocomplete configuration
    const searchInputs = htmlContent.match(/<input[^>]+type="search"[^>]*>/gi) || [];
    assert(searchInputs.length >= 8, `Found at least 8 search inputs (found ${searchInputs.length})`);
    
    let allHaveAutocompleteOff = true;
    let allHaveNames = true;
    searchInputs.forEach(input => {
        if (!input.includes('autocomplete="off"')) {
            allHaveAutocompleteOff = false;
            console.error(`Input missing autocomplete="off": ${input}`);
        }
        if (!input.match(/name="[^"]+"/)) {
            allHaveNames = false;
            console.error(`Input missing name attribute: ${input}`);
        }
    });

    assert(allHaveAutocompleteOff, "All search inputs have autocomplete=\"off\"");
    assert(allHaveNames, "All search inputs have specific name attributes");

    // 3. Verify JavaScript does not inject admin@gmail.com
    assert(!jsContent.includes('admin@gmail.com'), "app.js does not contain 'admin@gmail.com' injection.");
    
    // 4. Verify JavaScript does not inject currentUser.email into search fields
    assert(!jsContent.match(/\.value\s*=\s*.*currentUser\.email/), "app.js does not inject 'currentUser.email' into inputs.");

    // 5. Verify login email/password fields are NOT broken (should not have autocomplete="off" blindly added to them)
    const loginEmailMatch = htmlContent.match(/<input[^>]+id="loginEmail"[^>]*>/i);
    assert(loginEmailMatch !== null, "Login email input exists");
    if (loginEmailMatch) {
        assert(!loginEmailMatch[0].includes('autocomplete="off"'), "Login email input does NOT have autocomplete=\"off\" (normal browser saving allowed)");
        assert(loginEmailMatch[0].includes('type="text"') || loginEmailMatch[0].includes('type="email"'), "Login email input is properly typed for credentials");
    }

    const loginPassMatch = htmlContent.match(/<input[^>]+id="loginPass"[^>]*>/i);
    assert(loginPassMatch !== null, "Login password input exists");
    if (loginPassMatch) {
        assert(!loginPassMatch[0].includes('autocomplete="off"'), "Login password input does NOT have autocomplete=\"off\"");
    }

    // 6. Verify duplicate/dead Live Preview modal is removed
    assert(!htmlContent.includes('id="lpLivePreviewModal"'), "Dead lpLivePreviewModal stub was completely removed from index.html.");

    // 7. Verify actual working Live Preview modal is still intact and closed by default
    const modalMatch = htmlContent.match(/<div[^>]+id="lpPreviewModal"[^>]*>/);
    assert(modalMatch !== null, "Working lpPreviewModal is still intact in index.html.");
    if (modalMatch) {
        assert(!modalMatch[0].includes('active'), "lpPreviewModal is closed by default (does not have 'active' class)");
    }

    if (hasErrors) {
        console.error("\n❌ Some tests failed.");
        process.exit(1);
    } else {
        console.log("\n✅ All tests passed successfully.");
        process.exit(0);
    }
}

runTests();
