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

// ===== B: cap mach + tong hop chi so (DB local `game`) =====
$TESTSID = str_repeat('f', 32);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

function hm_reset($db, $sid) {
    $db->prepare("DELETE FROM player_huyetmach_vessel WHERE sid = ?")->execute(array($sid));
    $db->prepare("UPDATE game1 SET huyetmach_awaken_stage = 0 WHERE sid = ?")->execute(array($sid));
}
function hm_set_level($db, $sid, $vid, $lv) {
    $db->prepare("INSERT INTO player_huyetmach_vessel (sid, vessel_id, level, updated_at)
        VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE level = VALUES(level)")->execute(array($sid, $vid, $lv));
}

if (!$db->query("SELECT COUNT(*) FROM game1 WHERE sid = '$TESTSID'")->fetchColumn()) {
    // test can mot dong game1 de doc stage; dong nay bi xoa vo dieu kien o cuoi test
    $db->prepare("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, huyetmach_id, huyetmach_quality, huyetmach_awaken_stage)
        VALUES ('$TESTSID', 990112, 'HMTEST', 300, 1000, 10000, NOW(), 0, 1, 0)")->execute();
}
$db->prepare("UPDATE game1 SET uid = 990112 WHERE sid = ?")->execute(array($TESTSID));

hm_reset($db, $TESTSID);

$lv0 = \player\huyetmach_vessel_levels($TESTSID, $db);
tassert('levels du 8 phan tu', count($lv0), 8);
tassert('levels mac dinh 0', intval($lv0[1]), 0);
tassert('total 0', \player\huyetmach_vessel_total($lv0), 0);

$b0 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('bonus stage 0', $b0['stage'], 0);
tassert('bonus cap 8', $b0['cap'], 8);
tassert('bonus atk 0', $b0['atk'], 0);
tassert('bonus ms name', $b0['milestone_name'], '');

hm_set_level($db, $TESTSID, 1, 5);
hm_set_level($db, $TESTSID, 2, 5);
$b1 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('total 10', $b1['total_lv'], 10);
tassert('atk 5x3', $b1['atk'], 15);
tassert('hp 5x24', $b1['hp'], 120);

// cap 15 van dung vi tran 8 bi stage 0 chan o 8 -> dat tran thi milestone + cap van theo stage
hm_set_level($db, $TESTSID, 3, 8);
hm_set_level($db, $TESTSID, 4, 8);
hm_set_level($db, $TESTSID, 5, 8);
hm_set_level($db, $TESTSID, 6, 8);
hm_set_level($db, $TESTSID, 7, 8);
hm_set_level($db, $TESTSID, 8, 8);
$b2 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('total 58', $b2['total_lv'], 58);
tassert('ms dat Khai Mach', $b2['milestone_name'], 'Khai Mạch');
tassert('ms pct 5', $b2['milestone_percent'], 5.0);
tassert('atk nhan milestone', $b2['atk'], 16);          // round(3*5*1.05) = 16, KHONG phai 22 (round tung mach)
tassert('am stat reduce', $b2['reduce'], 2.0);           // 0.25 x min(8, tran 8) -> FLOAT vi ham dung round(..,2)
tassert('am stat luck', $b2['luck'], 2.0);

// stage 2: tran 24, milestone van theo tong cap
$db->prepare("UPDATE game1 SET huyetmach_awaken_stage = 2 WHERE sid = ?")->execute(array($TESTSID));
$b3 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('stage 2 cap 24', $b3['cap'], 24);

// chan 15% cho chi so am: 8 mach am x per_lv 0.25 x 80 cap -> vuot 15
for ($i = 5; $i <= 8; $i++) hm_set_level($db, $TESTSID, $i, 80);
$b4 = \player\huyetmach_vessel_bonuses($TESTSID, $db);
tassert('dodge cap 15', $b4['dodge'], 15.0);
tassert('pen cap 15', $b4['pen'], 15.0);

// dong game1 tam giu lai cho phan C; don duoc o cuoi file
hm_reset($db, $TESTSID);

// ===== C: Luyen Mach =====
if (!$db->query("SELECT COUNT(*) FROM game1 WHERE sid = '$TESTSID'")->fetchColumn()) {
    $db->prepare("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, huyetmach_id, huyetmach_quality, huyetmach_awaken_stage)
        VALUES ('$TESTSID', 990112, 'HMTEST', 300, 1000, 10000, NOW(), 0, 1, 0)")->execute();
}
$db->prepare("UPDATE game1 SET uid = 990112 WHERE sid = ?")->execute(array($TESTSID));

$ok = \player\huyetmach_vessel_refine($TESTSID, $db, 99);
tassert('refine vessel loi', $ok['ok'], false);

hm_reset($db, $TESTSID);
$db->prepare("UPDATE game1 SET uyxb = 0 WHERE sid = ?")->execute(array($TESTSID));
$ok = \player\huyetmach_vessel_refine($TESTSID, $db, 1);
tassert('refine thieu Huyet Thach', strpos($ok['msg'], 'Huyết Thạch') !== false, true);

// cap du 112 x 5 va Linh Thach 100000
$ins = $db->prepare("INSERT INTO playerdaoju (sid, uid, djid, djsum, djname, djzl, djinfo)
    VALUES (?, 990112, 112, 5, 'Huyết Thạch', '112', 'test') ON DUPLICATE KEY UPDATE djsum = 5");
$ins->execute(array($TESTSID));
$db->prepare("UPDATE game1 SET uyxb = 100000 WHERE sid = ?")->execute(array($TESTSID));
$ok = \player\huyetmach_vessel_refine($TESTSID, $db, 1);
tassert('refine ok', $ok['ok'], true);
tassert('refine lv 1', $ok['level'], 1);
tassert('tru 1 Huyet Thach', intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=112")->fetchColumn()), 4);
tassert('tru 200 Linh Thach', intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 99800);

// nap them de kiem tra chan tran chu khong phai thieu vat lieu
$db->prepare("UPDATE playerdaoju SET djsum = 30 WHERE sid = ? AND djid = 112")->execute(array($TESTSID));

// chan tran stage 0 = 8
for ($i = 1; $i <= 8; $i++) \player\huyetmach_vessel_refine($TESTSID, $db, 2);
$ok = \player\huyetmach_vessel_refine($TESTSID, $db, 2);
tassert('tran stage 0', strpos($ok['msg'], 'trần') !== false || strpos($ok['msg'], 'Trần') !== false, true);
tassert('lv dung tran', \player\huyetmach_vessel_levels($TESTSID, $db)[2], 8);

// ===== don ve sinh DB =====
hm_reset($db, $TESTSID);
$db->prepare("DELETE FROM playerdaoju WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM game1 WHERE sid = ?")->execute(array($TESTSID));

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
