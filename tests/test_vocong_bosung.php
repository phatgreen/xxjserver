<?php
/**
 * test_vocong_bosung.php — 24 võ công lấp khoảng trống (CG 48/60/69/78), giá "Mua ngay" bí tịch = giá Thiên Cơ Các,
 * công pháp CG >= 30 đổi Tàn Quyển. KHÔNG cần DB.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_vocong_bosung.php
 */
error_reporting(E_ALL & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}

$sk = \player\combat_cfg('wugong_skill_config.json');
$bad = array(); $realms = array();
for ($id = 111; $id <= 134; $id++) {
    $c = \player\wugong_config_get($id);
    if (!$c) { $bad[] = "$id: khong co"; continue; }
    $realms[intval($c['realm_require'])] = isset($realms[intval($c['realm_require'])]) ? $realms[intval($c['realm_require'])] + 1 : 1;
    if (!isset($sk['moves'][(string)$id]) || count($sk['moves'][(string)$id]) !== 3) $bad[] = "$id: the vo";
    if (\player\tiendao_need($id) <= 0) $bad[] = "$id: khong doi Tan Quyen";
    if (intval($c['price_linhthong']) + intval($c['price_masthach']) !== 0) $bad[] = "$id: van ban tien";
}
ksort($realms);
tassert('B1 24 bo moi hop le', $bad, array());
tassert('B1 moi moc CG 48/60/69/78 co 6 bo', $realms, array(48 => 6, 60 => 6, 69 => 6, 78 => 6));
// Chỉ số nằm giữa 2 mốc kề cùng hệ (vd Kiếm CG 48 giữa CG 42 và CG 55)
$a = \player\wugong_config_get(81); $m = \player\wugong_config_get(111); $b = \player\wugong_config_get(87);
tassert('B2 cong Kiem CG 48 nam giua CG 42 va 55', $a['add_gj'] < $m['add_gj'] && $m['add_gj'] < $b['add_gj'], true);
tassert('B2 so quyen: CG 48 = 15 Hon Nguyen, CG 78 = 45 Dai Dao',
    array(\player\tiendao_djid_for(111), \player\tiendao_need(111), \player\tiendao_djid_for(134), \player\tiendao_need(134)),
    array(116, 15, 117, 45));

// Giá Mua ngay bí tịch = giá shop
tassert('P1 bo ban Linh thach (wgid 2) = 500 LT', \player\wugong_book_price(2), array('lt', 500, 0));
tassert('P1 bo ban Ma thach (wgid 8) = 300 MT', \player\wugong_book_price(8), array('mt', 300, 0));
tassert('P1 bo doi Tan Quyen (wgid 64) = 10 Tien Dao Tan Quyen', \player\wugong_book_price(64), array('tq', 10, 115));
tassert('P2 bo shop khong ban (wgid 69, CG 30) = gia thap nhat cung moc', \player\wugong_book_price(69), array('tq', 10, 115));
tassert('P2 khong co vo cong (wgid 0) = khong mua', \player\wugong_book_price(0), array('', 0, 0));

// Công pháp
tassert('C1 cong phap CG 28 van ban tien', \player\tiendao_cp_cost(22), array(0, 0));
tassert('C1 cong phap CG 30 doi 10 Tien Dao Tan Quyen', \player\tiendao_cp_cost(23), array(115, 10));

echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
