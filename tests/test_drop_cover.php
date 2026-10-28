<?php
/**
 * test_drop_cover.php — Nguồn trang bị bậc >= 60: đủ 15 ô (quái 9 ô thường, boss 6 ô quý); Thương Điếm chỉ bán bậc <= 30;
 * ô thường cộng đột phá / tu luyện số nguyên, nhỏ hơn ô quý. Phần boss CẦN DB (đọc bảng boss).
 * Chạy: php tests/test_drop_cover.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$ROOT = dirname(__DIR__);

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}

$zb = json_decode(file_get_contents($ROOT . '/class/config/zhuangbei_config.json'), true);
$gw = json_decode(file_get_contents($ROOT . '/class/config/guaiwu_config.json'), true);
$Z = array();
foreach ($zb as $v) $Z[intval($v['zbid'])] = $v;
$SPECIAL = array(8, 9, 10, 11, 13, 14);

// ── D1. Nguồn rơi ─────────────────────────────────────────────
$src = array();   // [bậc][ô] => 'Q' / 'B'
$monBad = 0;
foreach ($gw as $g) {
    if (intval($g['glv']) < 60 || $g['gzb'] === '') continue;
    foreach (explode(',', $g['gzb']) as $id) {
        $v = $Z[intval($id)];
        if (in_array(intval($v['zbtool']), $SPECIAL, true)) $monBad++;
        $src[intval($v['zblv'])][intval($v['zbtool'])]['Q'] = 1;
    }
}
tassert('D1 quai cap >= 60 khong roi o quy', $monBad, 0);
$bossBad = 0;
foreach ($dblj->query("SELECT bosslv, bosszb FROM boss WHERE bosslv >= 60") as $b) {
    foreach (explode(',', $b['bosszb']) as $id) {
        if ($id === '') continue;
        $v = $Z[intval($id)];
        if (!in_array(intval($v['zbtool']), $SPECIAL, true)) $bossBad++;
        $src[intval($v['zblv'])][intval($v['zbtool'])]['B'] = 1;
    }
}
tassert('D1 boss cap >= 60 chi roi o quy (chay sql/migrate_drop_cover.php --apply neu FAIL)', $bossBad, 0);
$miss = array();
$tiers = array();
foreach ($Z as $v) if (intval($v['zblv']) >= 60) $tiers[intval($v['zblv'])][intval($v['zbtool'])] = 1;
foreach ($tiers as $T => $slots) {
    foreach ($slots as $s => $one) if (empty($src[$T][$s])) $miss[] = "$T/$s";
}
tassert('D1 moi bac >= 60 du 15 o co nguon (thieu: ' . implode(' ', array_slice($miss, 0, 8)) . ')', count($miss), 0);

// ── D2. Chỉ số huyền ô thường ─────────────────────────────────
$bad = 0;
foreach ($Z as $v) {
    $T = intval($v['zblv']); $s = intval($v['zbtool']);
    if ($T < 60 || in_array($s, $SPECIAL, true)) continue;
    $sp = null;
    foreach ($Z as $w) if (intval($w['zblv']) == $T && intval($w['zbtool']) == 9) { $sp = $w; break; }
    $cu = null;
    foreach ($Z as $w) if (intval($w['zblv']) == $T && intval($w['zbtool']) == 8) { $cu = $w; break; }
    if (!(intval($v['tuposjs']) >= 1 && intval($v['tuposjs']) < intval($sp['tuposjs']) && intval($v['tupoexp']) >= 1 && intval($v['tupoexp']) < intval($cu['tupoexp']))) $bad++;
}
tassert('D2 o thuong bac >= 60: dot pha / tu luyen >= 1 va nho hon o quy', $bad, 0);

// ── D3. Thương Điếm chỉ bán bậc <= 30 ─────────────────────────
$src3 = file_get_contents($ROOT . '/game/shangdian.php');
tassert('D3 tiem chan trang bi bac > 30', strpos($src3, "define('TCG_SHOP_ZB_MAX_LV', 30)") !== false && substr_count($src3, 'TCG_SHOP_ZB_MAX_LV') >= 4, true);

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
