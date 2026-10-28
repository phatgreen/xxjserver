<?php
/**
 * test_dieu_huong.php — khung điều hướng UI-2/UI-3 (class/function/dieu_huong.php): mọi lệnh có trong router,
 * không lệnh thuộc 2 tab, không mất lối vào nào của Đại Sảnh / Tu Luyện cũ, 🔒 theo cấp. Không cần DB.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_dieu_huong.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../class/encode.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}
function fake_player($lv) { $p = new \player\player(); $p->ulv = $lv; $p->nowmid = 225; return $p; }

$router = file_get_contents(__DIR__ . '/../game.php');
$inRouter = function($c) use ($router) {
    return (bool)preg_match("/'" . preg_quote($c, '/') . "'\s*=>|case '" . preg_quote($c, '/') . "'/", $router);
};

// ── D1. Lệnh của tab / tab con / Tiên Đồ đều có trong router; không lệnh nào thuộc 2 tab ──
$all = array(); $seen = array(); $dup = array();
foreach (\player\nav_tabs_config() as $key => $t) {
    $all[] = $t['item'][0];
    foreach ($t['cmds'] as $c) { if (isset($seen[$c])) $dup[] = $c; $seen[$c] = $key; $all[] = $c; }
    if (!empty($t['sub'])) foreach ($t['sub'] as $s) { $all[] = $s[0][0]; foreach ($s[1] as $c) $all[] = $c; }
}
$links = array();
foreach (\player\nav_tiendo_groups() as $g) foreach ($g[1] as $it) if ($it[0] !== 'index.php') { $c = strtok($it[0], '&'); $all[] = $c; $links[] = $c; }
$missing = array();
foreach (array_unique($all) as $c) if (!$inRouter($c)) $missing[] = $c;
tassert('D1 mọi lệnh điều hướng có trong router', $missing, array());
tassert('D1 không lệnh nào thuộc 2 tab', $dup, array());

// ── D2. Không mất lối vào: mọi nút Đại Sảnh cũ (21) + Tu Luyện cũ (10 nút tròn + thanh dưới 6) còn ở khung mới ──
foreach (\player\nav_tabs_config() as $t) {
    $links[] = $t['item'][0];
    if (!empty($t['sub'])) foreach ($t['sub'] as $s) $links[] = $s[0][0];
}
$old = array('goxiulian', 'wgxl', 'phoban', 'mytask', 'qydt', 'thiencoccac', 'zhuangtai', 'trangbi', 'getbagzb', 'luonden',
    'chetao', 'liaotian', 'chongwu', 'paihang', 'fangshi', 'club', 'im', 'duihuan', 'quydoi', 'shangdian',
    'gocp', 'linhcan', 'thechat', 'huyetmach', 'wgxiulian', 'xxwg', 'tianfu', 'nhiemvu');
$lost = array();
foreach ($old as $c) {
    // wgxl và wgxiulian cùng trang game/wugong.php
    if (!in_array($c, $links, true) && !($c === 'wgxl' && in_array('wgxiulian', $links, true))) $lost[] = $c;
}
tassert('D2 không mất lối vào cũ nào', $lost, array());
$cnt = array_count_values($links);
$twice = array(); foreach ($cnt as $c => $n) if ($n > 1 && !in_array($c, array('zhuangtai', 'chongwu', 'nhiemvu'), true)) $twice[] = $c;
tassert('D2 mỗi lối vào chỉ một chỗ (trừ tab = tab con đầu)', $twice, array());

// ── D3. Tab đang đứng + 🔒 ──
tassert('D3 trangbi thuộc tab Nhân Vật', \player\nav_tab_for_cmd('trangbi'), 'nhanvat');
tassert('D3 pve thuộc tab Hành Tẩu', \player\nav_tab_for_cmd('pve'), 'hanhtau');
tassert('D3 tupo thuộc tab Tu Luyện', \player\nav_tab_for_cmd('tupo'), 'tuluyen');
$enc = new \encode\encode();
$sid = str_repeat('ab', 16);
$h1 = \player\nav_bottom_html(fake_player(1), $sid, $enc, 'trangbi', array('nhiemvu' => true));
tassert('D3 thanh dưới đủ 5 tab', substr_count($h1, 'class="bn-tab'), 5);
tassert('D3 đúng 1 tab sáng', substr_count($h1, ' on'), 1);
tassert('D3 cấp 1: Linh Sủng 🔒 8', strpos($h1, '🔒 8') !== false, true);
tassert('D3 chấm đỏ Nhiệm Vụ', substr_count($h1, 'nav-dot'), 1);
$h2 = \player\nav_bottom_html(fake_player(1000), $sid, $enc, 'gomid');
tassert('D3 cấp 1000: không tab nào khóa', strpos($h2, 'is-locked'), false);
tassert('D3 tab con chỉ có ở Nhân Vật / Linh Sủng / Nhiệm Vụ', \player\nav_sub_html(fake_player(1), $sid, $enc, 'goxiulian'), '');
$s = \player\nav_sub_html(fake_player(1), $sid, $enc, 'getbagyp');
tassert('D3 tab con Nhân Vật: 6 mục, Túi Đồ sáng', array(substr_count($s, 'class="sn-tab'), strpos($s, 'sn-tab on" href') !== false && strpos($s, '<b>Túi Đồ</b>') > strpos($s, ' on"')), array(6, true));
$td = \player\nav_tiendo_html(fake_player(1), $sid, $enc, array('tuthan'));
tassert('D3 Tiên Đồ Tu Thân cấp 1: 6 nút đều 🔒', array(substr_count($td, 'class="td-item'), substr_count($td, 'is-locked')), array(6, 6));

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
