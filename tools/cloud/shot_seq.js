// Chụp theo thời gian (kiểm hiệu ứng mô phỏng trận): OUT=<thư mục> node tools/cloud/shot_seq.js URL
const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch();
  const p = await b.newPage({ viewport: { width: 1280, height: 860 } });
  p.on('pageerror', e => console.log('JSERR', e.message));
  await p.goto(process.argv[2], { waitUntil: 'domcontentloaded' });
  let last = 0;
  for (const t of [300, 1300, 3600]) { await p.waitForTimeout(t - last); last = t;
    await p.screenshot({ path: process.env.OUT + '/seq' + t + '.png' }); }
  await b.close();
})();
