const fs = require('fs');
const path = require('path');

const rootDir = __dirname;
const canonicalHtmlPath = path.join(rootDir, 'products', 'chicken-booster', 'index.html');
const extractedHtmlDir = path.join(rootDir, 'extracted_html');
const extractedHtmlPath = path.join(extractedHtmlDir, 'chicken-booster__step_1_checkout__widget_4259dac.html');
const advanceJsonDir = path.join(rootDir, 'Advance Landing Page');
const chickenBoosterJsonPath = path.join(advanceJsonDir, 'chicken-booster.json');
const manifestPath = path.join(extractedHtmlDir, 'manifest.json');

console.log('--- Synchronizing Chicken Booster from Canonical Source ---');

if (!fs.existsSync(canonicalHtmlPath)) {
  console.error(`Canonical HTML file not found at: ${canonicalHtmlPath}`);
  process.exit(1);
}

const canonicalHtml = fs.readFileSync(canonicalHtmlPath, 'utf8');

// 1. Copy canonical HTML into extracted_html/
if (!fs.existsSync(extractedHtmlDir)) {
  fs.mkdirSync(extractedHtmlDir, { recursive: true });
}
fs.writeFileSync(extractedHtmlPath, canonicalHtml, 'utf8');
console.log(`[1/3] Synchronized: ${extractedHtmlPath} (${canonicalHtml.length} bytes)`);

// 2. Create Advance Landing Page/chicken-booster.json based on Elementor / CartFlows JSON schema
const baseTemplate = [
  {
    title: "Chicken Booster",
    flow_type: "flows",
    flow_meta: {
      "wcf-flow-indexing": "",
      "wcf-testing": "no",
      "wcf-enable-analytics": "yes",
      "wcf-flow-custom-script": "",
      "wcf-flow-custom-js": "",
      "wcf-flow-custom-css": "",
      "wcf-enable-gcp-styling": "no",
      "wcf-gcp-primary-color": "#15803d",
      "wcf-gcp-secondary-color": "#14532d",
      "wcf-gcp-text-color": "#0f172a",
      "wcf-gcp-accent-color": "#d97706"
    },
    steps: [
      {
        title: "Sales Landing",
        type: "landing",
        meta: {
          "wcf-flow-id": ["3328"],
          "wcf-step-type": ["landing"],
          "_wp_page_template": ["cartflows-default"]
        },
        post_content: '""""""'
      },
      {
        title: "chakout",
        type: "checkout",
        meta: {
          "wcf-flow-id": ["3328"],
          "wcf-step-type": ["checkout"],
          "_wp_page_template": ["cartflows-canvas"],
          "_elementor_edit_mode": ["builder"],
          "_elementor_template_type": ["wp-post"],
          "_elementor_version": ["4.1.5"],
          "_elementor_pro_version": ["3.27.5"],
          "_elementor_data": [
            JSON.stringify([
              {
                id: "cb_sec_1",
                elType: "section",
                settings: [],
                elements: [
                  {
                    id: "cb_col_1",
                    elType: "column",
                    settings: { _column_size: 100, _inline_size: 100, space_between_widgets: 0 },
                    elements: [
                      {
                        id: "4259dac",
                        elType: "widget",
                        settings: { html: canonicalHtml },
                        elements: [],
                        widgetType: "html"
                      }
                    ],
                    isInner: false
                  }
                ],
                isInner: false
              }
            ])
          ]
        }
      }
    ]
  }
];

if (!fs.existsSync(advanceJsonDir)) {
  fs.mkdirSync(advanceJsonDir, { recursive: true });
}

// Encode JSON matching CartFlows double-string representation
const finalJsonString = JSON.stringify(JSON.stringify(baseTemplate));
fs.writeFileSync(chickenBoosterJsonPath, finalJsonString, 'utf8');
console.log(`[2/3] Generated JSON flow template: ${chickenBoosterJsonPath}`);

// 3. Update extracted_html/manifest.json
let manifest = [];
if (fs.existsSync(manifestPath)) {
  try {
    manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
  } catch (e) {
    manifest = [];
  }
}

// Remove any existing entry for chicken-booster
manifest = manifest.filter(m => m.jsonFile !== 'chicken-booster.json');

// Add Chicken Booster entry
manifest.push({
  jsonFile: "chicken-booster.json",
  flowIndex: 0,
  flowTitle: "Chicken Booster",
  stepIndex: 1,
  stepTitle: "chakout",
  stepType: "checkout",
  widgetId: "4259dac",
  htmlFile: "chicken-booster__step_1_checkout__widget_4259dac.html",
  length: canonicalHtml.length
});

fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2), 'utf8');
console.log(`[3/3] Updated manifest: ${manifestPath} (Total entries: ${manifest.length})`);
console.log('\n✓ Chicken Booster synchronization completed successfully!');
