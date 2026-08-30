const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'Advance Landing Page');
const files = fs.readdirSync(dir).filter(f => f.endsWith('.json'));

const results = [];

files.forEach(file => {
  const filePath = path.join(dir, file);
  try {
    let raw = fs.readFileSync(filePath, 'utf8');
    let data = JSON.parse(raw);
    if (typeof data === 'string') {
      data = JSON.parse(data);
    }
    
    const item = {
      filename: file,
      sizeBytes: fs.statSync(filePath).size,
      dataType: typeof data,
      isArray: Array.isArray(data),
      contentOverview: {}
    };

    if (Array.isArray(data)) {
      item.flows = data.map((flow, fi) => {
        const flowInfo = {
          title: flow.title,
          flow_type: flow.flow_type,
          stepsCount: flow.steps ? flow.steps.length : 0,
          steps: []
        };
        if (flow.steps) {
          flowInfo.steps = flow.steps.map(step => {
            const sInfo = {
              title: step.title,
              type: step.type,
              widgets: []
            };
            if (step.meta && step.meta._elementor_data) {
              try {
                let elData = step.meta._elementor_data[0];
                if (typeof elData === 'string') elData = JSON.parse(elData);
                sInfo.sectionsCount = Array.isArray(elData) ? elData.length : 0;
                
                function extractInfo(node) {
                  if (node.elType === 'widget') {
                    const w = {
                      widgetType: node.widgetType,
                      id: node.id
                    };
                    if (node.settings && node.settings.html) {
                      w.isHtmlWidget = true;
                      w.htmlLength = node.settings.html.length;
                      // extract some title or tags
                      const titleMatch = node.settings.html.match(/<title>([^<]+)<\/title>/i);
                      const h1Match = node.settings.html.match(/<h1[^>]*>([\s\S]*?)<\/h1>/i);
                      if (titleMatch) w.pageTitle = titleMatch[1];
                      if (h1Match) w.pageH1 = h1Match[1].replace(/<[^>]+>/g, '').trim();
                    }
                    sInfo.widgets.push(w);
                  }
                  if (node.elements) node.elements.forEach(extractInfo);
                }
                if (Array.isArray(elData)) elData.forEach(extractInfo);
              } catch (e) {
                sInfo.parseError = e.message;
              }
            }
            return sInfo;
          });
        }
        return flowInfo;
      });
    } else if (typeof data === 'object' && data !== null) {
      // Elementor template or other export format
      item.objectKeys = Object.keys(data);
      item.title = data.title;
      item.type = data.type;
      if (data.content) {
        item.contentLength = data.content.length;
      }
    }

    results.push(item);
  } catch (e) {
    results.push({ filename: file, error: e.message });
  }
});

fs.writeFileSync(path.join(__dirname, 'analysis_summary.json'), JSON.stringify(results, null, 2));
console.log('Analysis summary written successfully.');
