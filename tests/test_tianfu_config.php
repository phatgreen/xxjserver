<?php
/**
 * test_tianfu_config.php — cai dat Thien Phu (Phu Cac suu tam): config + ham thuan tuy.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_config.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/tianfu.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== A: dinh luong cau truc =====
$defs = \player\tianfu_defs();
tassert('18 thien phu', count($defs), 18);
tassert('6 nhom', count(\player\tianfu_groups()), 6);
tassert('7 cong', count(\player\tianfu_config_all()['gates']), 7);
tassert('10 pham', count(\player\tianfu_config_all()['qualities']), 10);
tassert('id 1..12 lien tuc', array_keys($defs), array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18));
tassert('def id le', \player\tianfu_def(19), false);
tassert('def id 0', \player\tianfu_def(0), false);

// moi nhom dung 3 cai
$byGroup = array();
foreach ($defs as $d) { $byGroup[$d['group']] = isset($byGroup[$d['group']]) ? $byGroup[$d['group']] + 1 : 1; }
tassert('moi nhom 3 cai', array_values($byGroup), array(3,3,3,3,3,3));

// weight 5/4/3 theo thu tu trong nhom -> tong 72
$sum = 0; $pat = array();
foreach ($defs as $d) { $sum += intval($d['weight']); }
tassert('tong weight = 72', $sum, 72);
foreach (array(1,2,3,4,5,6) as $g) {
    $w = array();
    foreach ($defs as $d) { if (intval($d['group']) === $g) $w[] = intval($d['weight']); }
    $pat[$g] = implode(',', $w);
}
tassert('weight pattern nhom 1', $pat[1], '5,4,3');
tassert('weight pattern nhom 6', $pat[6], '5,4,3');

// moi cong co it nhat 1 chu (ke ca chu phu exp_monster)
$owners = array();
foreach ($defs as $d) {
    foreach (array_keys(\player\tianfu_contributions($d)) as $g) { $owners[$g] = true; }
}
foreach (array_keys(\player\tianfu_config_all()['gates']) as $g) {
    tassert('co chu: ' . $g, isset($owners[$g]), true);
}

// ===== B: pham chat khop nguồn Nhuc Than =====
$tc = json_decode(file_get_contents($ROOT . '/class/config/thechat_config.json'), true);
foreach (\player\tianfu_config_all()['qualities'] as $q => $qd) {
    $k = $qd['key'];
    tassert('weight q' . $q . ' khop thechat', floatval($qd['weight']), floatval($tc['quality_weights'][$k]));
    tassert('name q' . $q . ' khop thechat', $qd['name'], $tc['quality_names'][$k]);
    tassert('color q' . $q . ' khop thechat', $qd['color'], $tc['quality_colors'][$k]);
}
// lv_cap tang dan, mult tang dan
$caps = array(); $mults = array();
foreach (\player\tianfu_config_all()['qualities'] as $qd) { $caps[] = intval($qd['lv_cap']); $mults[] = floatval($qd['mult']); }
tassert('lv_cap 10..60', $caps, array(10,14,18,24,30,36,42,48,54,60));
tassert('mult 1.00..3.50', $mults, array(1.0,1.15,1.3,1.5,1.75,2.0,2.3,2.6,3.0,3.5));
tassert('lv_cap q0', \player\tianfu_lv_cap(0), 0);
tassert('lv_cap q11', \player\tianfu_lv_cap(11), 0);
tassert('lv_cap q10', \player\tianfu_lv_cap(10), 60);

// ===== C: gia =====
tassert('lv cost 0', \player\tianfu_lv_cost(0), array('tf' => 1, 'linh_thach' => 200));
tassert('lv cost 7', \player\tianfu_lv_cost(7)['linh_thach'], 375);
tassert('lv cost 47', \player\tianfu_lv_cost(47)['linh_thach'], 1375);
tassert('break q1', \player\tianfu_break_cost(1), array('tf' => 10, 'linh_thach' => 10000, 'ulv_require' => 0));
tassert('break q9', \player\tianfu_break_cost(9)['linh_thach'], 20000000);
tassert('break q10 het', \player\tianfu_break_cost(10), false);

// ===== D: cong thuc tram — clamp PHAI binding =====
// 24 * 0.25 * 1.50 * 1.12 = 10.08
tassert('calc dia+moc12', \player\tianfu_percent_calc(24, 0.25, 1.50, 12), 10.08);
// 42 * 0.25 * 2.30 = 24.15 (duoi tram, chua clamped)
tassert('calc tien 42', \player\tianfu_percent_calc(42, 0.25, 2.30, 0), 24.15);
// 48 * 0.25 * 2.60 = 31.2 -> clamp 30 (day la khac biet voi stat_cap 15 cua Mach Huyet)
tassert('calc than 48 clamped', \player\tianfu_percent_calc(48, 0.25, 2.60, 0), 30.0);
tassert('calc dao 60 ms30 clamped', \player\tianfu_percent_calc(60, 0.25, 3.50, 30), 30.0);
tassert('calc lv0', \player\tianfu_percent_calc(0, 0.25, 3.50, 30), 0.0);
tassert('calc sub exp_monster', \player\tianfu_percent_calc(48, 0.15, 2.60, 0), 18.72);

// ===== E: contributions / fx =====
tassert('contrib id1', \player\tianfu_contributions($defs[1]), array('exp_cultivate' => 0.25, 'exp_monster' => 0.15));
tassert('contrib id7', \player\tianfu_contributions($defs[7]), array('alchemy' => 0.25));
tassert('fx q1', \player\tianfu_fx_class(1), 0);
tassert('fx q2', \player\tianfu_fx_class(2), 0);
tassert('fx q3', \player\tianfu_fx_class(3), 1);
tassert('fx q5', \player\tianfu_fx_class(5), 2);
tassert('fx q6', \player\tianfu_fx_class(6), 3);
tassert('fx q7', \player\tianfu_fx_class(7), 4);
tassert('fx q10', \player\tianfu_fx_class(10), 5);
tassert('fx q0', \player\tianfu_fx_class(0), 0);
tassert('slot mo cv1', \player\tianfu_slot_open(1), array(1));
tassert('slot mo cv100', \player\tianfu_slot_open(100), array(1,2));
tassert('slot mo cv280', \player\tianfu_slot_open(280), array(1,2,3,4));
tassert('ten gate drop', \player\tianfu_gate_name('drop'), 'Ngưỡng rơi đồ');
tassert('ten gate la', \player\tianfu_gate_name('zzz'), 'zzz');
tassert('open pill', \player\tianfu_open_pill(), array('djid' => 114, 'name' => 'Khai Phú Đan'));

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
