// Rà mobile: OUT=<thư mục> node tools/cloud/audit_mobile.js "ten|URL" ... (390x844) — in bề rộng, phần tử tràn mép, nút <32px, chữ <11px + chụp màn hình
const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 1, isMobile: true, hasTouch: true });
  for (const a of process.argv.slice(2)) { const [n,u]=a.split('|');
    try { await p.goto(u,{waitUntil:'domcontentloaded',timeout:20000}); await p.waitForTimeout(500);
      const r = await p.evaluate(()=>{const W=innerWidth;const sw=document.documentElement.scrollWidth;
        const inScroll=e=>{let a=e.parentElement;while(a){const o=getComputedStyle(a).overflowX;if(o==='auto'||o==='scroll')return true;a=a.parentElement}return false};
        const over=[...document.querySelectorAll('body *')].filter(e=>{const x=e.getBoundingClientRect();return x.width>0&&x.right>W+2&&!inScroll(e)}).slice(0,3).map(e=>(e.className||e.tagName).toString().slice(0,40));
        const small=[...document.querySelectorAll('a,button')].filter(e=>{const x=e.getBoundingClientRect();return x.width>0&&x.height>0&&(x.height<32)}).length;
        const tiny=[...document.querySelectorAll('body *')].filter(e=>e.childNodes.length&&[...e.childNodes].some(c=>c.nodeType==3&&c.textContent.trim())&&parseFloat(getComputedStyle(e).fontSize)<11).length;
        return {sw,H:document.documentElement.scrollHeight,over,small,tiny,title:document.title}});
      await p.screenshot({path:process.env.OUT+'/'+n+'.png'});
      console.log(n.padEnd(12), 'rong', r.sw, 'cao', r.H, '| nut <32px:', r.small, '| chu <11px:', r.tiny, r.over.length? '| TRAN: '+r.over.join(', '):'');
    } catch(e){ console.log(n,'LOI',e.message.slice(0,80)); } }
  await b.close(); })();
