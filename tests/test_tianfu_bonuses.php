<?php
/**
 * test_tianfu_bonuses.php — kiem tra cong thuc 15 key cua \player\tianfu_bonuses()
 * va ban sao game1.tianfu_* do tianfu_recalc_mirror() viet ra.
 * Chay SAU Task 2 (schema) + Task 3 (ham). CLI:
 *   "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_bonuses.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/tests/tianfu_test_lib.php';

function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

$db   = tf_test_db();
$TESTSID = str_repeat('f', 32);   // sid rieng cua he Thien Phu
$TESTUID = 990112;

tf_cleanup($db, $TESTSID);
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFTEST', 1000, 500);

// ===== 1) Chua so huu gi: bonuses phai ra toan 0, khong fatal =====
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('bonus keys = 15', count($B), 15);
tassert('empty exp_cultivate', $B['exp_cultivate'], 0.0);
tassert('empty total_lv', $B['total_lv'], 0);
tassert('empty milestone_name', $B['milestone_name'], '');
tassert('empty milestone_percent', $B['milestone_percent'], 0.0);   // float (lech plan: `0` int khong qua ===)
tassert('empty next milestone lv', $B['next_milestone_lv'], 60);
tassert('empty activated_count', $B['activated_count'], 0);
tassert('empty power', $B['power'], 0);

// ===== 2) So huu + hai thien phu cung mot cong: CONG raw roi clamp =====
// id 1: lv 20, q 4 (mult 1.50) -> 20*0.25*1.50 = 7.5
// id 2: lv 20, q 3 (mult 1.30) -> 20*0.25*1.30 = 6.5  => 14.0
// tong lv so huu = 40 < 60 nen milestone = 0
$O = \player\tianfu_owned($TESTSID, $db);
tassert('owned du 18 o', count($O), 18);
tassert('o 4 chua so huu -> have 0', $O[4]['have'], 0);
tf_ins_owned($db, $TESTSID, 1, 4, 20, 1);
tf_ins_owned($db, $TESTSID, 2, 3, 20, 2);
$O = \player\tianfu_owned($TESTSID, $db);
tassert('o 1 da so huu', $O[1]['have'], 1);
tassert('o 1 lay ten tu config', $O[1]['name'], 'Ngộ Tính Nghịch Thiên');
$A = \player\tianfu_activated($TESTSID, $db);
tassert('activated = 2 dong', count($A), 2);
tassert('activated key theo so thu tu slot', (isset($A[1]) ? $A[1]['id'] : -1), 1);
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('two talents sum 14.0 (khong clamp tung cai)', $B['exp_cultivate'], 14.0);
tassert('exp_monster sub 0.15 x2', $B['exp_monster'], 8.4);
tassert('total_lv = 40', $B['total_lv'], 40);
tassert('activated_count = 2', $B['activated_count'], 2);
tassert('power = slot1+slot2', $B['power'], (4 * 150 + 20 * 8) + (3 * 150 + 20 * 8));

// ===== 3) Clamp 30 SAU khi nhan milestone, do tren 1 thien phu =====
// Ngat id 2 khoi Phu Vi de do dung mot minh id 1.
$db->prepare("UPDATE player_tianfu SET slot = 0 WHERE sid = ? AND tianfu_id = 2")->execute(array($TESTSID));
tf_ins_owned($db, $TESTSID, 1, 5, 60, 1);   // q5 mult 1.75 -> raw 60*0.25*1.75 = 26.25
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('tong lv so huu van tinh id 2 (slot 0)', $B['total_lv'], 80);
tassert('milestone dat moc 60', $B['milestone_name'], 'Khai Phú');
tassert('milestone percent', $B['milestone_percent'], 5.0);
tassert('next milestone 150', $B['next_milestone_lv'], 150);
tassert('26.25 x 1.05 = 27.56 (chua clamp)', $B['exp_cultivate'], 27.56);
tassert('exp_monster 15.75 x 1.05 = 16.54', $B['exp_monster'], 16.54);
// Bop id 3 (q10 mult 3.50, lv 60 -> raw 52.5) vao slot 2: 26.25 + 52.5 = 78.75 -> clamp 30
tf_ins_owned($db, $TESTSID, 3, 10, 60, 2);
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('clamp 30 sau khi cong hai cai', $B['exp_cultivate'], 30.0);
tassert('exp_monster cung clamp (47.25 x 1.05)', $B['exp_monster'], 30.0);
tassert('tong lv 140 -> moc van 5%', $B['milestone_percent'], 5.0);
tassert('power 2 slot bat', $B['power'], (5 * 150 + 60 * 8) + (10 * 150 + 60 * 8));

// ===== 4) Thien phu chua bat (slot 0) van tinh total_lv, KHONG tinh cong =====
tf_ins_owned($db, $TESTSID, 7, 8, 40, 0);   // Dan Dao, chua bat vao Phu Vi
$B = \player\tianfu_bonuses($TESTSID, $db);
tassert('total_lv tinh ca chua bat', $B['total_lv'], 180);
tassert('milestone nhao len 150', $B['milestone_name'], 'Thông Linh');
tassert('alchemy = 0 vi chua bat vao Phu Vi', $B['alchemy'], 0.0);
tassert('moc ke la 300', $B['next_milestone_lv'], 300);
tassert('power khong doi (id 7 slot 0)', $B['power'], (5 * 150 + 60 * 8) + (10 * 150 + 60 * 8));

// ===== 5) recalc_mirror: game1.tianfu_* = ban sao slot 1 + power aggregate =====
$M = \player\tianfu_recalc_mirror($TESTSID, $db);
tassert('mirror id = slot 1', $M['tianfu_id'], 1);
tassert('mirror quality', $M['tianfu_quality'], 5);
tassert('mirror lv', $M['tianfu_lv'], 60);
tassert('mirror power = bonuses power', $M['tianfu_power'], $B['power']);
$row = $db->prepare("SELECT tianfu_id, tianfu_quality, tianfu_lv, tianfu_power FROM game1 WHERE sid = ?");
$row->execute(array($TESTSID));
$g = $row->fetch(PDO::FETCH_ASSOC);
tassert('game1 id', intval($g['tianfu_id']), 1);
tassert('game1 quality', intval($g['tianfu_quality']), 5);
tassert('game1 lv', intval($g['tianfu_lv']), 60);
tassert('game1 power', intval($g['tianfu_power']), $B['power']);

// ===== 6) Khong co chu phu (slot 1 trong) -> 3 cot dau ve 0, power van tinh =====
$db->prepare("UPDATE player_tianfu SET slot = 0 WHERE sid = ? AND tianfu_id = 1")->execute(array($TESTSID));
$M = \player\tianfu_recalc_mirror($TESTSID, $db);
tassert('no master -> id 0', $M['tianfu_id'], 0);
tassert('no master -> quality 0', $M['tianfu_quality'], 0);
tassert('no master -> lv 0', $M['tianfu_lv'], 0);
tassert('power chi con slot 2 (id 3)', $M['tianfu_power'], (10 * 150 + 60 * 8));

// ===== 7) tianfu_log khong dc, co dong migrate =====
\player\tianfu_log($TESTSID, 'migrate', 1, 0, 5, 'test', $db);
$lc = $db->prepare("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = ? AND kind = 'migrate'");
$lc->execute(array($TESTSID));
tassert('log migrate', intval($lc->fetchColumn()), 1);

tf_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
