<?php
/**
 * test_tianfu_lvup.php — Nang Cap (100%) va Dot Pham (100%) + hang dieu kien UI.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_lvup.php
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

$db = tf_test_db();
$TESTSID = str_repeat('f', 32);
$TESTUID = 990112;
tf_cleanup($db, $TESTSID);
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFTEST', 1000, 500);   // cv1000, 500 diem tf

// ===== A: o chua so huu -> tu choi =====
$R = \player\tianfu_lvup($TESTSID, $db, 5);
tassert('chua so huu -> ok false', $R['ok'], false);
tassert('diem tf khong bi an', \player\tianfu_player_wallet($TESTSID, $db)['tf'], 500);

tf_ins_owned($db, $TESTSID, 1, 1, 0, 1);          // Phàm Phẩm, lv 0, slot 1
$O = \player\tianfu_owned($TESTSID, $db);
tassert('slot 1 = id 1 (chu phu)', $O[1]['slot'], 1);

// ===== B: lvup 1 -> tieu 1 tf + 200 LT, lv = 1 =====
$db->prepare("UPDATE game1 SET uyxb = 100000 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_lvup($TESTSID, $db, 1);
tassert('lvup ok', $R['ok'], true);
tassert('lv = 1', $R['lv'], 1);
tassert('cost dung cong thuc', $R['cost'], array('tf' => 1, 'linh_thach' => 200));
$W = \player\tianfu_player_wallet($TESTSID, $db);
tassert('tf 500 -> 499', $W['tf'], 499);
tassert('LT 100000 -> 99800', $W['uyxb'], 99800);

// ===== C: tran Lv cua pham chat 1 = 10 -> khong lvup duoc nua =====
for ($i = 1; $i < 10; $i++) { \player\tianfu_lvup($TESTSID, $db, 1); }
$R = \player\tianfu_lvup($TESTSID, $db, 1);
tassert('lv 10 la tran q1', $R['ok'], false);
tassert('msg bao tran', strpos($R['msg'], 'cấp tối đa') !== false, true);
$O = \player\tianfu_owned($TESTSID, $db);
tassert('lv dung bang 10', $O[1]['lv'], 10);

// ===== D: break khi chua dat tran -> bi chan =====
tf_ins_owned($db, $TESTSID, 4, 1, 5, 0);          // id 4, lv 5 < 10
$R = \player\tianfu_break($TESTSID, $db, 4);
tassert('break khi lv < tran -> false', $R['ok'], false);

// ===== E: break 1..3 khong doi hoi canh gioi (ulv 1000 du) =====
$R = \player\tianfu_break($TESTSID, $db, 1);
tassert('break q1 -> q2 ok', $R['ok'], true);
tassert('quality = 2', $R['quality'], 2);
tassert('cost break q1', $R['cost'], array('tf' => 10, 'linh_thach' => 10000, 'ulv_require' => 0));

// ===== F: break q4 can ulv 100 — nhan vat cv 50 phai bi chan =====
tf_ins_owned($db, $TESTSID, 5, 4, 24, 0);         // dia pham, lv = tran 24
$db->prepare("UPDATE game1 SET ulv = 50, uyxb = 900000, tf = 300 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_break($TESTSID, $db, 5);
tassert('cv 50 khong du Dot Pham len thien pham', $R['ok'], false);
tassert('msg canh gioi', strpos($R['msg'], 'cấp 100') !== false, true);
$db->prepare("UPDATE game1 SET ulv = 100 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_break($TESTSID, $db, 5);
tassert('cv 100 du', $R['ok'], true);
tassert('q4 -> q5', $R['quality'], 5);

// ===== G: break q9 -> q10 can cv 500; q10 het =====
tf_ins_owned($db, $TESTSID, 6, 9, 54, 0);
$R = \player\tianfu_break($TESTSID, $db, 6);
tassert('q9 can cv 500 -> false o cv 100', $R['ok'], false);
$db->prepare("UPDATE game1 SET ulv = 500, uyxb = 30000000, tf = 500 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_break($TESTSID, $db, 6);
tassert('q9 -> q10 ok o cv 500', $R['ok'], true);
$R = \player\tianfu_break($TESTSID, $db, 6);
tassert('q10 het pham de dot', $R['ok'], false);
// q10 co tran Lv = 60, id 6 dang lv 54 -> van con 6 cap de luyen
$R = \player\tianfu_lvup($TESTSID, $db, 6);
tassert('q10 lv 54 van luyen duoc', $R['ok'], true);
tassert('lv len 55', $R['lv'], 55);

// ===== H: thieu tien / thieu diem -> false va KHONG an tai san =====
tf_ins_owned($db, $TESTSID, 9, 1, 0, 0);
$db->prepare("UPDATE game1 SET tf = 0, uyxb = 50 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_lvup($TESTSID, $db, 9);
tassert('thieu diem tf -> false', $R['ok'], false);
$W = \player\tianfu_player_wallet($TESTSID, $db);
tassert('LT khong bi tr khi that bai', $W['uyxb'], 50);
tassert('lv khong tang khi that bai', \player\tianfu_owned($TESTSID, $db)[9]['lv'], 0);

// ===== I: rows cho UI =====
$db->prepare("UPDATE game1 SET tf = 500, uyxb = 5000000 WHERE sid = ?")->execute(array($TESTSID));
$LV = \player\tianfu_lvup_rows($TESTSID, $db, 1);
tassert('lvup rows = 3', count($LV), 3);
tassert('rows la mang {text,ok}', isset($LV[0]['text']) && isset($LV[0]['ok']), true);
$BR = \player\tianfu_break_rows($TESTSID, $db, 1);   // id 1: q2, lv 10 < tran 14
tassert('break rows = 4', count($BR), 4);
tassert('dong "cap tran" chua dat -> ok false', $BR[0]['ok'], false);
tassert('dong "diem tf" du -> ok true', $BR[1]['ok'], true);
$BRQ = \player\tianfu_break_rows($TESTSID, $db, 6);   // id 6 dang q10
tassert('q10 rows = 1 dong', count($BRQ), 1);
tassert('q10 rows ok false', $BRQ[0]['ok'], false);

// ===== J: log + mirror =====
$lc = $db->prepare("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = ? AND kind IN ('lvup','break')");
$lc->execute(array($TESTSID));
tassert('co log lvup/break', intval($lc->fetchColumn()) > 10, true);
$M = $db->prepare("SELECT tianfu_lv, tianfu_power FROM game1 WHERE sid = ?");
$M->execute(array($TESTSID));
$m = $M->fetch(PDO::FETCH_ASSOC);
tassert('mirror lv = lv chu phu', intval($m['tianfu_lv']), 10);
tassert('mirror power khong am', intval($m['tianfu_power']) > 0, true);

tf_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
