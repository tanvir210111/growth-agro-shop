const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'extracted_html');
const manifest = JSON.parse(fs.readFileSync(path.join(dir, 'manifest.json'), 'utf8'));

const detailedAnalysis = manifest.map(item => {
  const htmlPath = path.join(dir, item.htmlFile);
  const html = fs.readFileSync(htmlPath, 'utf8');

  // Extract key information
  const title = (html.match(/<title>([^<]+)<\/title>/i) || [])[1] || 'No Title';
  const metaDesc = (html.match(/<meta[^>]*name=["']description["'][^>]*content=["']([^"']+)["']/i) || [])[1] || 'No meta description';
  const h1 = ((html.match(/<h1[^>]*>([\s\S]*?)<\/h1>/i) || [])[1] || 'No H1').replace(/<[^>]+>/g, '').trim();
  
  // Phone / WhatsApp links
  const waLinks = (html.match(/https:\/\/wa\.me\/[^\s"'>]+/g) || []);
  const telLinks = (html.match(/tel:[^\s"'>]+/g) || []);
  
  // Pricing
  const priceMatches = html.match(/৳[\d,]+/g) || [];
  
  // Images vs Emojis
  const imgTags = (html.match(/<img[^>]+>/g) || []);
  const emojiCheck = (html.match(/[\u{1F300}-\u{1F6FF}\u{1F900}-\u{1F9FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/gu) || []);

  // CTA Links
  const links = (html.match(/href=["']([^"']+)["']/g) || []).map(l => l.replace(/href=["']|["']/g, ''));

  // Sections found
  const sections = [];
  if (/class="[^"]*hero/i.test(html) || /<header/i.test(html)) sections.push('Hero');
  if (/proof|review|rating|testi/i.test(html)) sections.push('Social Proof / Reviews');
  if (/feature|benefit|spec|sec/i.test(html)) sections.push('Features / Benefits');
  if (/faq/i.test(html)) sections.push('FAQ Accordion');
  if (/sticky-cta|desktop-sticky-bar/i.test(html)) sections.push('Sticky CTA Bars');
  if (/fab-stack|fab-wa/i.test(html)) sections.push('Floating Action Buttons (FAB)');
  if (/form|checkout/i.test(html)) sections.push('Checkout Integration');

  // Issues detected
  const issues = [];
  if (waLinks.some(l => l.includes('XXXXXXXXX') || l.includes('8801XXXXXXXXX') || l.includes('01700000000'))) {
    issues.push('Placeholder WhatsApp number (8801XXXXXXXXX / dummy)');
  }
  if (html.includes('8801XXXXXXXXX')) {
    issues.push('Contains placeholder 8801XXXXXXXXX');
  }
  if (imgTags.length === 0) {
    issues.push('No <img> tags used (relying purely on emojis/CSS placeholders)');
  }
  if (html.length < 10000) {
    issues.push('Very short/basic landing page compared to advanced ones');
  }
  if (item.jsonFile === 'bags.json' && title.includes('স্নিকার্স')) {
    issues.push('Mismatch: bags.json contains Shoes/Sneakers content instead of Bags');
  }

  return {
    jsonFile: item.jsonFile,
    htmlFile: item.htmlFile,
    length: html.length,
    title,
    h1,
    metaDesc,
    sections,
    waLinks: [...new Set(waLinks)],
    telLinks: [...new Set(telLinks)],
    samplePrices: [...new Set(priceMatches)].slice(0, 5),
    imgCount: imgTags.length,
    emojiCount: emojiCheck.length,
    issues
  };
});

fs.writeFileSync(path.join(__dirname, 'detailed_analysis.json'), JSON.stringify(detailedAnalysis, null, 2));
console.log('Detailed analysis saved to detailed_analysis.json');
