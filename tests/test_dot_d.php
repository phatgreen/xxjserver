<?php
/**
 * test_dot_d.php — Đợt D: Tiên Đạo Tàn Quyển (cấu hình, đạo cụ không bán ở tiệm, trừ đúng số quyển khi đổi).
 * Phần trừ quyển CẦN DB (dùng nhân vật e2e, tự dọn sau khi chạy).
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_dot_d.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$db = $dblj;
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}

// ── D1. Cấu hình ──────────────────────────────────────────────
$cfg = \player\tiendao_config();
tassert('D1 djid = 115', intval($cfg['djid']), 115);
tassert('D1 can 10 quyen', intval($cfg['need']), 10);
tassert('D1 ti le 400/450/500 = 3/4/5%', array_slice($cfg['drop_pct'], 0, 3, true), array('400' => 3, '450' => 4, '500' => 5));
// Làn 2: 10 boss 550–1000 (docs/LAN2_CAP_500_1000.md L6)
tassert('D1 lan 2: 550–700 = 5%, 750–900 = 6%, 950–1000 = 7%', array_slice($cfg['drop_pct'], 3, 10, true),
    array('550' => 5, '600' => 5, '650' => 5, '700' => 5, '750' => 6, '800' => 6, '850' => 6, '900' => 6, '950' => 7, '1000' => 7));
// Làn 3: 10 boss 1050–1500 rơi 5% (docs/LAN3_CAP_1000_1500.md mục 6)
tassert('D1 lan 3: 1050–1500 = 5%', array_slice($cfg['drop_pct'], 13, 10, true), array_fill_keys(range(1050, 1500, 50), 5));
// Làn 4: boss 1550–2000 5%; 6 bộ Tiên Đạo CG 36 (wgid 75–80) đổi 15 quyển, bộ CG 30 giữ 10 (docs/LAN4_CAP_1500_2000.md)
tassert('D1 lan 4: 1550–2000 = 5%', array_slice($cfg['drop_pct'], 23, 10, true), array_fill_keys(range(1550, 2000, 50), 5));
// Làn 5: boss 2050–2500 5%, rơi Hỗn Nguyên Tàn Quyển (116); 6 bộ Hỗn Nguyên (wgid 81–86) đổi 10 quyển 116 (docs/LAN5_CAP_2000_2500.md)
tassert('D1 lan 5: 2050–2500 = 5%', array_slice($cfg['drop_pct'], 33, 10, true), array_fill_keys(range(2050, 2500, 50), 5));
// Làn 6: boss 2550–3000 rơi 116; 6 bộ Hỗn Nguyên CG 55 (wgid 87–92) đổi quyển 116, bộ CG 42 giữ 10 (docs/LAN6_CAP_2500_3000.md)
// Chủ dự án 2026-10-28: hiếm dần theo làn — làn 6 rơi 4% / cần 20, làn 7 rơi 3% / cần 30, làn 8 rơi 2% / cần 40
tassert('D1 lan 6: 2550–3000 = 4%', array_slice($cfg['drop_pct'], 43, 10, true), array_fill_keys(range(2550, 3000, 50), 4));
tassert('D1 lan 6: boss 2550 / 3000 roi 116', array(\player\tiendao_drop_djid(2550), \player\tiendao_drop_djid(3000)), array(116, 116));
tassert('D1 lan 6: Hon Nguyen CG 55 doi 20 quyen 116, CG 42 van 10', array(\player\tiendao_need(87), \player\tiendao_need(92), \player\tiendao_djid_for(87), \player\tiendao_need(81)), array(20, 20, 116, 10));
// Làn 7: boss 3050–3500 rơi 116; 6 bộ Hỗn Nguyên CG 65 (wgid 93–98) (docs/LAN7_CAP_3000_3500.md)
tassert('D1 lan 7: 3050–3500 = 3%', array_slice($cfg['drop_pct'], 53, 10, true), array_fill_keys(range(3050, 3500, 50), 3));
tassert('D1 lan 7: boss 3050 / 3500 roi 116', array(\player\tiendao_drop_djid(3050), \player\tiendao_drop_djid(3500)), array(116, 116));
tassert('D1 lan 7: Hon Nguyen CG 65 doi 30 quyen 116, CG 55 can 20', array(\player\tiendao_need(93), \player\tiendao_need(98), \player\tiendao_djid_for(98), \player\tiendao_need(87)), array(30, 30, 116, 20));
// Làn 8: boss 3550–4000 rơi Đại Đạo Tàn Quyển (117) 2%; 6 bộ Đại Đạo CG 72 (wgid 99–104) đổi 40 quyển 117 (docs/LAN8_CAP_3500_4000.md)
tassert('D1 lan 8: 3550–4000 = 2%', array_slice($cfg['drop_pct'], 63, 10, true), array_fill_keys(range(3550, 4000, 50), 2));
tassert('D1 lan 8: boss 3500 roi 116, 3550 / 4000 roi 117', array(\player\tiendao_drop_djid(3500), \player\tiendao_drop_djid(3550), \player\tiendao_drop_djid(4000)), array(116, 117, 117));
// Làn 9: boss 4050–4300 rơi 117 1%; 6 bộ Đại Đạo CG 83 (wgid 105–110) đổi 50 quyển 117 (docs/LAN9_CAP_4000_4300.md)
tassert('D1 lan 9: 4050–4300 = 1%', array_slice($cfg['drop_pct'], 73, null, true), array_fill_keys(range(4050, 4300, 50), 1));
tassert('D1 lan 9: Dai Dao CG 83 doi 50 quyen 117, boss 4300 roi 117', array(\player\tiendao_need(105), \player\tiendao_need(110), \player\tiendao_djid_for(110), \player\tiendao_drop_djid(4300)), array(50, 50, 117, 117));
tassert('D1 lan 8: Dai Dao CG 72 doi 40 quyen 117', array(\player\tiendao_need(99), \player\tiendao_need(104), \player\tiendao_djid_for(99), \player\tiendao_djid_for(104)), array(40, 40, 117, 117));
$dd = \player\daoju_config_get(117);
tassert('D1 lan 8: dao cu 117 Dai Dao Tan Quyen khong ban, ban lai 0', array($dd['djname'], intval($dd['no_shop']), intval($dd['djyxb'])), array('Đại Đạo Tàn Quyển', 1, 0));
tassert('D1 lan 5: boss 2000 roi 115, 2050 / 2500 roi 116', array(\player\tiendao_drop_djid(2000), \player\tiendao_drop_djid(2050), \player\tiendao_drop_djid(2500)), array(115, 116, 116));
tassert('D1 lan 5: Hon Nguyen doi 10 quyen 116, Tien Dao van dung 115', array(\player\tiendao_need(81), \player\tiendao_djid_for(81), \player\tiendao_djid_for(86), \player\tiendao_djid_for(75), \player\tiendao_djid_for(64)), array(10, 116, 116, 115, 115));
$dj116 = \player\daoju_config_get(116);
tassert('D1 lan 5: dao cu 116 khong ban, ban lai 0', array($dj116 ? $dj116['djname'] : false, intval($dj116['no_shop']), intval($dj116['djyxb'])), array('Hỗn Nguyên Tàn Quyển', 1, 0));
tassert('D1 lan 4: Tien Dao CG 36 can 15 quyen, CG 30 van 10', array(\player\tiendao_need(75), \player\tiendao_need(80), \player\tiendao_need(64)), array(15, 15, 10));

// ── D2. Đạo cụ: có trong config, không bán ở Thương Điếm, bán lại 0 tiền ──
$dj = \player\daoju_config_get(115);
tassert('D2 co dao cu 115', $dj ? $dj['djname'] : false, 'Tiên Đạo Tàn Quyển');
tassert('D2 no_shop', isset($dj['no_shop']) ? intval($dj['no_shop']) : 0, 1);
tassert('D2 ban lai 0 linh thach', intval($dj['djyxb']), 0);

// ── D3. Chỉ 5 bộ Tiên Đạo đổi bằng Tàn Quyển, vẫn cần cảnh giới 30 ──
$bad = array();
foreach (array(64, 65, 66, 67, 68) as $wgid) {
    $wg = \player\wugong_config_get($wgid);
    if (\player\tiendao_need($wgid) !== 10 || intval($wg['realm_require']) !== 30 || $wg['rarity'] !== 'tien_dao') $bad[] = $wgid;
}
tassert('D3 wgid 64-68 doi 10 quyen, can CG 30', $bad, array());
tassert('D3 bo Tuyet The (58) khong doi bang quyen', \player\tiendao_need(58), 0);

// ── D4. Trừ quyển: thiếu thì không trừ, đủ thì trừ đúng 10 ──
$sid = str_repeat('e2', 16);
if (!\player\getplayer($sid, $db)) {
    echo "SKIP  D4 (khong co nhan vat e2e — chay php tests/e2e_fixture.php)\n";
} else {
    $db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid = 115")->execute(array($sid));
    \player\adddj($sid, 115, 9, $db);
    tassert('D4 9 quyen: khong du', \player\deledjsum(115, 10, $sid, $db), false);
    tassert('D4 9 quyen: khong bi tru', intval(\player\getplayerdaoju($sid, 115, $db)->djsum), 9);
    \player\adddj($sid, 115, 1, $db);
    tassert('D4 10 quyen: tru duoc', \player\deledjsum(115, 10, $sid, $db), true);
    tassert('D4 con lai 0', intval(\player\getplayerdaoju($sid, 115, $db)->djsum), 0);
    $db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid = 115")->execute(array($sid));
}

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
