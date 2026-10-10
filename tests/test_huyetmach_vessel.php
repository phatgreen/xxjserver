<?php
/**
 * test_huyetmach_vessel.php — config + ham doc thuan tuy cua Mang Mach Huyet.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_huyetmach_vessel.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/huyetmach.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== A: config + ham thuan tuy =====
$defs = \player\huyetmach_vessel_defs();
tassert('8 vessels', count($defs), 8);
tassert('4 duong 4 am', count(array_filter($defs, function ($d) { return $d['group'] === 'duong'; })), 4);
tassert('vessel 1 atk', \player\huyetmach_vessel_def(1)['stat'], 'atk');
tassert('vessel bad id', \player\huyetmach_vessel_def(99), false);

tassert('cap stage 0', \player\huyetmach_vessel_cap(0), 8);
tassert('cap stage 4', \player\huyetmach_vessel_cap(4), 48);
tassert('cap stage 9', \player\huyetmach_vessel_cap(9), 0);
tassert('stage max', \player\huyetmach_vessel_stage_max(), 4);
tassert('tier 2 name', \player\huyetmach_vessel_tier(2)['name'], 'Thức Tỉnh');
tassert('tier 5 missing', \player\huyetmach_vessel_tier(5), false);

// refine: 1 + floor(lv/8) Huyet Thach, 200 + lv*25 Linh Thach
tassert('cost lv0', \player\huyetmach_vessel_refine_cost(0),
    array('material_id' => 112, 'material_amount' => 1, 'linh_thach_cost' => 200));
tassert('cost lv7', \player\huyetmach_vessel_refine_cost(7)['material_amount'], 1);
tassert('cost lv8', \player\huyetmach_vessel_refine_cost(8)['material_amount'], 2);
tassert('cost lv47 lt', \player\huyetmach_vessel_refine_cost(47)['linh_thach_cost'], 1375);

// milestone
$m0 = \player\huyetmach_vessel_milestone(0);
tassert('ms0 none', $m0['name'], '');
tassert('ms0 pct', $m0['bonus_percent'], 0.0);
tassert('ms0 next', $m0['next_milestone_lv'], 32);
$m1 = \player\huyetmach_vessel_milestone(32);
tassert('ms32 name', $m1['name'], 'Khai Mạch');
tassert('ms32 pct', $m1['bonus_percent'], 5.0);
tassert('ms32 next', $m1['next_milestone_lv'], 80);
$mMax = \player\huyetmach_vessel_milestone(384);
tassert('ms max done', $mMax['next_milestone_lv'], 0);
tassert('ms max pct', $mMax['bonus_percent'], 30.0);  // = gia tri moc 4 trong config (Task 12 so no dong 13)

tassert('label atk', \player\huyetmach_vessel_stat_label('atk'), 'Công Kích');
tassert('label junk', \player\huyetmach_vessel_stat_label('zzz'), 'zzz');

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
