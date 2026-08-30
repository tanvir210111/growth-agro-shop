const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'extracted_html');
const manifest = JSON.parse(fs.readFileSync(path.join(dir, 'manifest.json'), 'utf8'));

console.log('=== COMPREHENSIVE LANDING PAGE AUDIT ===\n');

manifest.forEach((item, idx) => {
  const htmlPath = path.join(dir, item.htmlFile);
  const html = fs.readFileSync(htmlPath, 'utf8');

  const titleMatch = html.match(/<title>(.*?)<\/title>/is);
  const h1Match = html.match(/<h1[^>]*>(.*?)<\/h1>/is);
  const brandMatch = html.match(/class=["']brand[^"']*["'][^>]*>(.*?)<\/div>/is) || html.match(/class=["']brand-mark[^"']*["'][^>]*>(.*?)<\/span>/is);
  const hasStyleTag = /<style[\s>]/i.test(html);
  const externalCss = (html.match(/<link[^>]+rel=["']stylesheet["'][^>]+>/gi) || []).filter(l => !l.includes('fonts.googleapis.com'));
  const hasFAB = html.includes('fab-stack') || html.includes('fab-wa');
  const hasStickyMobile = html.includes('sticky-cta');
  const hasStickyDesktop = html.includes('desktop-sticky-bar');
  const hasFAQ = html.includes('faq-item') || html.includes('faqList');
  const hasReviews = html.includes('test-card') || html.includes('review') || html.includes('stars');
  const hasScripts = /<script[\s>]/i.test(html);

  console.log(`[${idx + 1}] File: ${item.jsonFile}`);
  console.log(`    Step: "${item.stepTitle}" (${item.stepType})`);
  console.log(`    Title: ${titleMatch ? titleMatch[1].trim() : 'N/A'}`);
  console.log(`    H1: ${h1Match ? h1Match[1].replace(/<[^>]+>/g, '').replace(/\s+/g, ' ').trim() : 'N/A'}`);
  console.log(`    HTML Size: ${html.length} chars`);
  console.log(`    CSS Type: ${hasStyleTag ? 'Embedded <style>' : 'NO <style> TAG!'} ${externalCss.length ? '| External links: ' + externalCss.join(', ') : ''}`);
  console.log(`    Features: FAB=${hasFAB}, MobileSticky=${hasStickyMobile}, DesktopSticky=${hasStickyDesktop}, FAQ=${hasFAQ}, Reviews=${hasReviews}, JS=${hasScripts}`);
  console.log('--------------------------------------------------');
});
