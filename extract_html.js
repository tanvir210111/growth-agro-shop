const fs = require('fs');
const path = require('path');

const srcDir = path.join(__dirname, 'Advance Landing Page');
const outDir = path.join(__dirname, 'extracted_html');

if (!fs.existsSync(outDir)) {
  fs.mkdirSync(outDir, { recursive: true });
}

const files = fs.readdirSync(srcDir).filter(f => f.endsWith('.json'));

console.log(`Extracting HTML from ${files.length} JSON files...`);

const manifest = [];

files.forEach(file => {
  const filePath = path.join(srcDir, file);
  try {
    let raw = fs.readFileSync(filePath, 'utf8');
    let data = JSON.parse(raw);
    if (typeof data === 'string') {
      data = JSON.parse(data);
    }

    if (Array.isArray(data)) {
      data.forEach((flow, fi) => {
        if (flow.steps) {
          flow.steps.forEach((step, si) => {
            if (step.meta && step.meta._elementor_data) {
              let elData = step.meta._elementor_data[0];
              if (typeof elData === 'string') elData = JSON.parse(elData);

              function findHtmlWidgets(node) {
                if (node.elType === 'widget' && node.settings && node.settings.html) {
                  const baseName = file.replace('.json', '');
                  const htmlFileName = `${baseName}__step_${si}_${step.type}__widget_${node.id}.html`;
                  const outPath = path.join(outDir, htmlFileName);
                  fs.writeFileSync(outPath, node.settings.html, 'utf8');
                  console.log(`Saved: ${htmlFileName} (${node.settings.html.length} chars)`);

                  manifest.push({
                    jsonFile: file,
                    flowIndex: fi,
                    flowTitle: flow.title,
                    stepIndex: si,
                    stepTitle: step.title,
                    stepType: step.type,
                    widgetId: node.id,
                    htmlFile: htmlFileName,
                    length: node.settings.html.length
                  });
                }
                if (node.elements) node.elements.forEach(findHtmlWidgets);
              }

              if (Array.isArray(elData)) elData.forEach(findHtmlWidgets);
            }
          });
        }
      });
    }
  } catch (err) {
    console.error(`Failed processing ${file}:`, err);
  }
});

fs.writeFileSync(path.join(outDir, 'manifest.json'), JSON.stringify(manifest, null, 2));
console.log('Manifest written to extracted_html/manifest.json');
