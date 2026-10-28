<?php
/**
 * test_dot_a_data.php — Kiểm tra dữ liệu đợt A (võ công 40-69, công pháp 29-56). KHÔNG cần DB.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_dot_a_data.php
 */
error_reporting(E_ALL & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}

$wg = \player\wugong_config_all();
$sk = \player\combat_cfg('wugong_skill_config.json');
$cp = \player\congphap_config_all();

// ===== Võ công =====
$ids = array(); $bad = array(); $budget = array();
$okWgEff = array('pierce_def', 'crit_dmg', 'damage_reduce', 'escape', 'hp_regen', 'exp_bonus', 'drop_bonus',
    'combo_hit', 'steal_reward', 'counter_lifesteal', 'control_reduce_damage', 'poison_immune', '');
$rarOrder = array('nhap_mon' => 0, 'ha_thua' => 1, 'trung_cap' => 2, 'thuong_thua' => 3, 'than_cong' => 4, 'tien_dao' => 5, 'hon_nguyen' => 6, 'dai_dao' => 7);
foreach ($wg as $key => $w) {
    $id = intval($w['wgid']);
    if ($id <= 0) continue;
    $ids[] = $id;
    if (intval($w['id']) !== $id) $bad[] = "$id: id != wgid";
    if (!isset($rarOrder[$w['rarity']])) $bad[] = "$id: rarity la {$w['rarity']}";
    if (!in_array($w['effect_type'], $okWgEff, true)) $bad[] = "$id: effect {$w['effect_type']}";
    if (count(explode('|', $w['wginfo'])) !== 5) $bad[] = "$id: wginfo khong du 5 phan";
    if (!\player\wugong_config_get($id) || \player\wugong_config_get($id)['wgname'] !== $w['wgname']) $bad[] = "$id: wugong_config_get sai";
    $b = intval($w['add_gj']) + intval($w['add_fy']) + intval($w['add_hp']) / 10 + intval($w['add_bj']) + intval($w['add_xx']);
    $budget[$w['rarity']][] = $b;
    $mv = isset($sk['moves'][(string)$id]) ? $sk['moves'][(string)$id] : array();
    if (count($mv) !== 3) $bad[] = "$id: khong du 3 the";
    foreach ($mv as $m) if (strpos($m['text'], '{mob}') === false) $bad[] = "$id: van the thieu {mob}";
}
// Đếm trong phạm vi đợt A (wgid < 70); 5 bộ CG 25 của làn 3 (70–74, tools/gen/lan3_a_build.py) vẫn qua vòng kiểm tra trên
tassert('W1 60 vo cong (29 cu + 30 moi + "khong co")', count(array_filter($wg, function($w){ return intval($w['wgid']) < 70; })), 60);
tassert('W1 lan 3: 5 bo Tuyet The CG 25 (wgid 70–74, 1 bo / he)', count(array_filter($wg, function($w){ return intval($w['wgid']) >= 70 && intval($w['wgid']) <= 74 && $w['rarity'] === 'than_cong' && intval($w['realm_require']) === 25; })), 5);
tassert('W1 lan 4: 6 bo Tien Dao CG 36 (wgid 75–80, 1 bo / he + Ngu Hanh)', count(array_filter($wg, function($w){ return intval($w['wgid']) >= 75 && intval($w['wgid']) <= 80 && $w['rarity'] === 'tien_dao' && intval($w['realm_require']) === 36; })), 6);
tassert('W1 lan 5: 6 bo Hon Nguyen CG 42 (wgid 81–86, 1 bo / he + Ngu Hanh)', count(array_filter($wg, function($w){ return intval($w['wgid']) >= 81 && intval($w['wgid']) <= 86 && $w['rarity'] === 'hon_nguyen' && intval($w['realm_require']) === 42; })), 6);
tassert('W1 lan 6: 6 bo Hon Nguyen CG 55 (wgid 87–92, 1 bo / he + Ngu Hanh)', count(array_filter($wg, function($w){ return intval($w['wgid']) >= 87 && intval($w['wgid']) <= 92 && $w['rarity'] === 'hon_nguyen' && intval($w['realm_require']) === 55; })), 6);
tassert('W1 lan 7: 6 bo Hon Nguyen CG 65 (wgid 93–98, 1 bo / he + Ngu Hanh)', count(array_filter($wg, function($w){ return intval($w['wgid']) >= 93 && intval($w['wgid']) <= 98 && $w['rarity'] === 'hon_nguyen' && intval($w['realm_require']) === 65; })), 6);
tassert('W1 lan 8: 6 bo Dai Dao CG 72 (wgid 99–104, phẩm mới)', count(array_filter($wg, function($w){ return intval($w['wgid']) >= 99 && intval($w['wgid']) <= 104 && $w['rarity'] === 'dai_dao' && intval($w['realm_require']) === 72; })), 6);
tassert('W1 lan 9: 6 bo Dai Dao CG 83 (wgid 105–110)', count(array_filter($wg, function($w){ return intval($w['wgid']) >= 105 && intval($w['wgid']) <= 110 && $w['rarity'] === 'dai_dao' && intval($w['realm_require']) === 83; })), 6);
tassert('W1 wgid khong trung', count($ids), count(array_unique($ids)));
tassert('W1 moi bo hop le (id, phẩm, hiệu ứng, mô tả, 3 thế)', $bad, array());
// Ngân sách trung bình tăng dần theo phẩm
$avg = array();
foreach ($rarOrder as $r => $i) $avg[$i] = isset($budget[$r]) ? array_sum($budget[$r]) / count($budget[$r]) : 0;
ksort($avg);
$inc = true; for ($i = 1; $i < count($avg); $i++) if ($avg[$i] <= $avg[$i - 1]) $inc = false;
tassert('W2 chi so trung binh tang dan theo 8 pham', $inc, true);
// Tiên Đạo không bán
$tdSell = 0; foreach ($wg as $w) if (in_array($w['rarity'], array('tien_dao', 'hon_nguyen', 'dai_dao'), true) && (intval($w['price_linhthong']) > 0 || intval($w['price_masthach']) > 0)) $tdSell++;
tassert('W3 Tien Dao / Hon Nguyen / Dai Dao khong ban o Thien Co Cac', $tdSell, 0);

// ===== Công pháp =====
$cbad = array(); $cids = array();
$okCpEff = array('absorb_speed', 'exp_bonus', 'hp_regen', 'drop_rate', 'dmg_reduce', 'heal_bonus', 'def_bonus',
    'mining_bonus', 'atk_bonus', 'crit_dmg', 'stone_bonus');
foreach ($cp as $key => $c) {
    $id = intval($c['cpid']); $cids[] = $id;
    if ((string)$id !== (string)$key) $cbad[] = "$id: key != cpid";
    if (!in_array($c['effect_type'], $okCpEff, true)) $cbad[] = "$id: effect {$c['effect_type']}";
    if (intval($c['tier']) < 0 || intval($c['tier']) > 10 || intval($c['subtier']) < 1 || intval($c['subtier']) > 4) $cbad[] = "$id: giai/pham";
    if (floatval($c['effect_value_max']) <= floatval($c['effect_value'])) $cbad[] = "$id: max <= base";
    if (count(explode('|', $c['cpinfo'])) !== 3) $cbad[] = "$id: cpinfo";
    if (intval($c['price_linhthong']) <= 0 && intval($c['price_masthach']) <= 0) $cbad[] = "$id: khong co gia";
}
// Đợt A: cpid 1–56; làn 5 + làn 6 thêm 8 bộ Đế Giai cpid 57–64; làn 7–8 thêm 8 bộ Đạo Giai cpid 65–72; Phàm Giai cpid 73–76 (cp_pham_build.py); làn 9 thêm 4 bộ Chí Tôn Giai cpid 77–80
tassert('C1 80 cong phap', count($cp), 80);
tassert('C1 cpid khong trung', count($cids), count(array_unique($cids)));
tassert('C1 moi bo hop le', $cbad, array());
// Mỗi giai có 8 bộ
$perGiai = array(); foreach ($cp as $c) { $g = intval($c['tier']); $perGiai[$g] = isset($perGiai[$g]) ? $perGiai[$g] + 1 : 1; }
ksort($perGiai);
tassert('C2 giai 0 (Pham Giai) 4 bo, giai 1–9 moi giai 8 bo, giai 10 (Chi Ton Giai) 4 bo', $perGiai, array(0 => 4, 1 => 8, 2 => 8, 3 => 8, 4 => 8, 5 => 8, 6 => 8, 7 => 8, 8 => 8, 9 => 8, 10 => 4));
$de = array(); foreach ($cp as $c) if (intval($c['tier']) === 8) $de[] = intval($c['realm_require']);
sort($de);
tassert('C2 De Giai yeu cau CG 45, 45, 48, 48, 52, 52, 57, 57', $de, array(45, 45, 48, 48, 52, 52, 57, 57));
$dao = array(); foreach ($cp as $c) if (intval($c['tier']) === 9) $dao[] = intval($c['realm_require']);
sort($dao);
tassert('C2 Dao Giai yeu cau CG 62, 62, 67, 67, 74, 74, 78, 78', $dao, array(62, 62, 67, 67, 74, 74, 78, 78));
// Giá (chủ dự án 2026-10-28, tools/gen/cp_price_build.py): giai 0–3 bán cả 2 loại tiền, Linh thạch = 3 × Ma thạch; giai 4+ chỉ Ma thạch
$pbad = array();
foreach ($cp as $c) {
    $t = intval($c['tier']); $lt = intval($c['price_linhthong']); $mt = intval($c['price_masthach']);
    if ($mt <= 0) $pbad[] = $c['cpid'] . ': khong co gia MT';
    if ($t <= 3 && $lt !== 3 * $mt) $pbad[] = $c['cpid'] . ': LT != 3 x MT';
    if ($t >= 4 && $lt !== 0) $pbad[] = $c['cpid'] . ': giai 4+ van ban LT';
}
tassert('C3 gia: giai 0–3 LT = 3 x MT, giai 4+ chi MT', $pbad, array());
$pr = function($id) use ($cp) { return intval($cp[(string)$id]['price_masthach']); };
tassert('C3 gia moc: Pham 1.000 · Hoang 14.000 · De Giai So 1.000.000 · Dao Giai Thuong 10.480.000 · Chi Ton Giai 14.690.000 / 19.040.000', array($pr(73), $pr(1), $pr(57), $pr(72), $pr(77), $pr(80)), array(1000, 14000, 1000000, 10480000, 14690000, 19040000));

echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
