// Chụp màn hình: OUT=<thư mục> node tools/cloud/shot_url.js 'ten|URL' ... (viewport 390x844, full page)
const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 390, height: 844 } });
  const urls = process.argv.slice(2);
  for (let i = 0; i < urls.length; i++) {
    const [name, u] = urls[i].split('|');
    await p.goto(u, { waitUntil: 'domcontentloaded' }); await p.waitForTimeout(700);
    await p.screenshot({ path: process.env.OUT + '/' + name + '.png', fullPage: true });
  }
  await b.close();
})();
