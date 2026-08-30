const fs = require('fs');
const path = require('path');

const srcDir = path.join(__dirname, 'Advance Landing Page');
const htmlDir = path.join(__dirname, 'extracted_html');
const manifest = JSON.parse(fs.readFileSync(path.join(htmlDir, 'manifest.json'), 'utf8'));

console.log('Starting Repackaging & Synchronization of all Landing Pages...');

// Step 1: Handle bags.json duplication fix
// First, read bags (1).json to get the authentic UrbanCarry bag flow
const bags1Path = path.join(srcDir, 'bags (1).json');
const bags1Raw = fs.readFileSync(bags1Path, 'utf8');

// Overwrite bags.json with the bags (1).json content as base
fs.writeFileSync(path.join(srcDir, 'bags.json'), bags1Raw, 'utf8');
console.log('Fixed: bags.json now uses the authentic Bags template instead of shoes duplicate.');

// Step 2: Loop through all JSON files and update their embedded HTML widgets from extracted_html/
const files = fs.readdirSync(srcDir).filter(f => f.endsWith('.json'));

files.forEach(file => {
  const filePath = path.join(srcDir, file);
  try {
    let raw = fs.readFileSync(filePath, 'utf8');
    let wasDoubleEncoded = false;
    let data = JSON.parse(raw);
    if (typeof data === 'string') {
      wasDoubleEncoded = true;
      data = JSON.parse(data);
    }

    let modified = false;

    if (Array.isArray(data)) {
      data.forEach((flow, fi) => {
        if (flow.steps) {
          flow.steps.forEach((step, si) => {
            if (step.meta && step.meta._elementor_data) {
              let elData = step.meta._elementor_data[0];
              let isElString = false;
              if (typeof elData === 'string') {
                isElString = true;
                elData = JSON.parse(elData);
              }

              function updateHtmlWidget(node) {
                if (node.elType === 'widget' && node.settings && node.settings.html !== undefined) {
                  const baseName = file.replace('.json', '');
                  // Check matching html file
                  let htmlFileName = `${baseName}__step_${si}_${step.type}__widget_${node.id}.html`;
                  let htmlPath = path.join(htmlDir, htmlFileName);

                  // If file is bags.json, it might match bags (1)
                  if (!fs.existsSync(htmlPath) && file === 'bags.json') {
                    htmlFileName = `bags (1)__step_${si}_${step.type}__widget_${node.id}.html`;
                    htmlPath = path.join(htmlDir, htmlFileName);
                  }

                  if (fs.existsSync(htmlPath)) {
                    const newHtml = fs.readFileSync(htmlPath, 'utf8');
                    node.settings.html = newHtml;
                    modified = true;
                    console.log(`Updated HTML widget in ${file} [step ${si}: ${step.title}, widget: ${node.id}]`);
                  }
                }
                if (node.elements) node.elements.forEach(updateHtmlWidget);
              }

              if (Array.isArray(elData)) {
                elData.forEach(updateHtmlWidget);
                if (isElString) {
                  step.meta._elementor_data[0] = JSON.stringify(elData);
                } else {
                  step.meta._elementor_data[0] = elData;
                }
              }
            }
          });
        }
      });
    }

    if (modified || file === 'bags.json') {
      let finalJsonStr = JSON.stringify(data);
      if (wasDoubleEncoded) {
        finalJsonStr = JSON.stringify(finalJsonStr);
      }
      fs.writeFileSync(filePath, finalJsonStr, 'utf8');
      console.log(`Successfully saved updated JSON: ${file}`);
    }
  } catch (err) {
    console.error(`Error processing ${file}:`, err.message);
  }
});

console.log('\nAll JSON files repackaged successfully!');
