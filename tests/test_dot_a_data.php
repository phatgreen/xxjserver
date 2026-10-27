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
$rarOrder = array('nhap_mon' => 0, 'ha_thua' => 1, 'trung_cap' => 2, 'thuong_thua' => 3, 'than_cong' => 4, 'tien_dao' => 5);
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
tassert('W1 60 vo cong (29 cu + 30 moi + "khong co")', count($wg), 60);
tassert('W1 wgid khong trung', count($ids), count(array_unique($ids)));
tassert('W1 moi bo hop le (id, phẩm, hiệu ứng, mô tả, 3 thế)', $bad, array());
// Ngân sách trung bình tăng dần theo phẩm
$avg = array();
foreach ($rarOrder as $r => $i) $avg[$i] = isset($budget[$r]) ? array_sum($budget[$r]) / count($budget[$r]) : 0;
ksort($avg);
$inc = true; for ($i = 1; $i < count($avg); $i++) if ($avg[$i] <= $avg[$i - 1]) $inc = false;
tassert('W2 chi so trung binh tang dan theo 6 pham', $inc, true);
// Tiên Đạo không bán
$tdSell = 0; foreach ($wg as $w) if ($w['rarity'] === 'tien_dao' && (intval($w['price_linhthong']) > 0 || intval($w['price_masthach']) > 0)) $tdSell++;
tassert('W3 Tien Dao khong ban o Thien Co Cac', $tdSell, 0);

// ===== Công pháp =====
$cbad = array(); $cids = array();
$okCpEff = array('absorb_speed', 'exp_bonus', 'hp_regen', 'drop_rate', 'dmg_reduce', 'heal_bonus', 'def_bonus',
    'mining_bonus', 'atk_bonus', 'crit_dmg', 'stone_bonus');
foreach ($cp as $key => $c) {
    $id = intval($c['cpid']); $cids[] = $id;
    if ((string)$id !== (string)$key) $cbad[] = "$id: key != cpid";
    if (!in_array($c['effect_type'], $okCpEff, true)) $cbad[] = "$id: effect {$c['effect_type']}";
    if (intval($c['tier']) < 1 || intval($c['tier']) > 7 || intval($c['subtier']) < 1 || intval($c['subtier']) > 4) $cbad[] = "$id: giai/pham";
    if (floatval($c['effect_value_max']) <= floatval($c['effect_value'])) $cbad[] = "$id: max <= base";
    if (count(explode('|', $c['cpinfo'])) !== 3) $cbad[] = "$id: cpinfo";
    if (intval($c['price_linhthong']) <= 0 && intval($c['price_masthach']) <= 0) $cbad[] = "$id: khong co gia";
}
tassert('C1 56 cong phap', count($cp), 56);
tassert('C1 cpid khong trung', count($cids), count(array_unique($cids)));
tassert('C1 moi bo hop le', $cbad, array());
// Mỗi giai có 8 bộ
$perGiai = array(); foreach ($cp as $c) { $g = intval($c['tier']); $perGiai[$g] = isset($perGiai[$g]) ? $perGiai[$g] + 1 : 1; }
ksort($perGiai);
tassert('C2 moi giai 8 bo', array_values($perGiai), array(8, 8, 8, 8, 8, 8, 8));

echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
