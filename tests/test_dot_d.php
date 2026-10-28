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
tassert('D1 ti le 400/450/500 = 3/4/5%', $cfg['drop_pct'], array('400' => 3, '450' => 4, '500' => 5));

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
