<?php
/**
 * capture_tip_390.php — Sinh trang iframe 390px mo tooltip that bang route that + JS that,
 * de Chrome headless chang thanh anh chung minh badge/tooltip nhieu he tren dien thoai.
 *
 * Chay:  "...php.exe" tests/capture_tip_390.php
 * Sau do: chrome --headless=new --disable-gpu --hide-scrollbars --window-size=680,1560
 *         --virtual-time-budget=15000 --screenshot=ABS "http://localhost/tests/_tip_many.html"
 *
 * Can tests/e2e_fixture.php chay truoc (nhan vat + 10 mon Phi Phong du 0..5 he).
 * Thanh phan _tip_*.html chi de chang, xoa duoc.
 */
require_once dirname(__DIR__) . '/class/encode.php';
$enc = new \encode\encode();
$sid = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$cases = array(
    // 'pick' => 'max': mon co nhieu he nhat;  'pick' => '3': mon co dung 3 he (chua mac -> co dong so sanh)
    'many'  => array('url' => 'cmd=getbagzb&sid=' . $sid, 'match' => 'Phi Phong', 'pick' => 'max'),
    'three' => array('url' => 'cmd=getbagzb&sid=' . $sid, 'match' => 'Phi Phong', 'pick' => '3'),
);
foreach ($cases as $name => $c) {
    $full = 'http://localhost/game.php?cmd=' . $enc->encode($c['url']);
    $js_match = json_encode($c['match']);
    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>'
        . 'body{margin:0;background:#111;display:flex;justify-content:center;padding:8px}'
        . 'iframe{width:390px;height:1500px;border:0;background:#000}'
        . '</style></head><body><div id="dbg" style="position:fixed;left:4px;top:4px;z-index:9;'
        . 'font:11px/1.4 monospace;color:#0f0;background:#000;padding:2px 6px;max-width:660px"></div>'
        . '<iframe id="f" src="' . $full . '"></iframe><script>'
        . "document.getElementById('f').addEventListener('load', function () {"
        . "  var d = this.contentDocument, w = this.contentWindow;"
        . "  var nodes = d.querySelectorAll('[data-tip]');"
        . "  var want = " . $js_match . ";"
        . "  var pick = " . json_encode($c['pick']) . ";"
        . "  var hit = null, hitN = -1;"
        . "  for (var i = 0; i < nodes.length; i++) {"
        . "    var data = null;"
        // data-tip la JSON da bi json_encode mac dinh escape \uXXXX -> phai parse roi so name
        . "    try { data = JSON.parse(nodes[i].getAttribute('data-tip')); } catch (e) { data = null; }"
        . "    if (!data || !data.name || data.name.indexOf(want) === -1) continue;"
        . "    var n = data.elem ? data.elem.length : 0;"
        . "    if (pick === 'max') { if (n > hitN) { hit = nodes[i]; hitN = n; } continue; }"
        . "    if (n === parseInt(pick, 10) && hit === null) { hit = nodes[i]; hitN = n; }"
        . "  }"
        . "  var dbg = document.getElementById('dbg');"
        . "  if (!hit) { dbg.textContent = 'NOHIT nodes=' + nodes.length; return; }"
        . "  dbg.textContent = 'pick=' + pick + ' elem=' + hitN;"
        . "  hit.scrollIntoView({block:'center'});"
        . "  setTimeout(function () {"
        // itemtip.js: onClick only mo khi KHONG hover duoc, nen may tinh phai dispatch mouseover
        . "    var hover = w.matchMedia ? w.matchMedia('(hover: hover)').matches : true;"
        . "    hit.dispatchEvent(new w.MouseEvent(hover ? 'mouseover' : 'click', {bubbles:true}));"
        . "    setTimeout(function () {"
        . "      var b = d.querySelector('.tcg-itemtip');"
        . "      dbg.textContent += ' | ' + (hover ? 'HOVER' : 'TAP') + ' box=' + (b ? b.style.display : 'none')"
        . "        + ' vw=' + w.innerWidth + ' sw=' + d.documentElement.scrollWidth"
        . "        + ' bw=' + (b ? b.offsetWidth : 0) + ' bh=' + (b ? b.offsetHeight : 0);"
        . "    }, 900);"
        . "  }, 1200);"
        . "}, false);"
        . '</script></body></html>';
    file_put_contents(__DIR__ . '/_tip_' . $name . '.html', $html);
    echo $name . " -> http://localhost/tests/_tip_" . $name . ".html\n";
}
