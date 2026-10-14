<?php
/**
 * test_tianfu_slot.php — mo Phu Vi theo canh gioi, chan 2 o cung cong, hoan vi, thao do.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_slot.php
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
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFTEST', 100, 0);   // cv 100: moi slot 1 va 2

foreach (array(1, 2, 4, 7) as $i => $id) {                 // 4 o 4 nhom khac nhau
    tf_ins_owned($db, $TESTSID, $id, 3 + $i, 10 + $i, 0);
}

// ===== A: o chua so huu =====
$R = \player\tianfu_slot_set($TESTSID, $db, 18, 1);
tassert('chua so huu -> false', $R['ok'], false);

// ===== B: Phu Vi 3 can cv 200 =====
$R = \player\tianfu_slot_set($TESTSID, $db, 1, 3);
tassert('slot 3 chua mo o cv 100', $R['ok'], false);
tassert('msg bao canh gioi', strpos($R['msg'], 'cấp 200') !== false, true);

// ===== C: bat id 1 vao slot 1 -> duoc, mirror theo sau =====
$R = \player\tianfu_slot_set($TESTSID, $db, 1, 1);
tassert('slot 1 ok', $R['ok'], true);
tassert('mirror id = 1', $R['mirror']['tianfu_id'], 1);
tassert('mirror quality', $R['mirror']['tianfu_quality'], 3);
$O = \player\tianfu_owned($TESTSID, $db);
tassert('player_tianfu slot = 1', $O[1]['slot'], 1);
tassert('bonus exp_cultivate > 0', \player\tianfu_bonuses($TESTSID, $db)['exp_cultivate'] > 0, true);

// ===== D: 2 o cung nhom Ngo Tinh -> chan =====
$R = \player\tianfu_slot_set($TESTSID, $db, 2, 2);
tassert('id 2 cung cong voi id 1 -> false', $R['ok'], false);
tassert('msg neu ten cong', strpos($R['msg'], 'Tốc độ tu luyện') !== false, true);
$O = \player\tianfu_owned($TESTSID, $db);
tassert('id 2 van o slot 0', $O[2]['slot'], 0);

// ===== E: khac nhom -> duoc =====
$R = \player\tianfu_slot_set($TESTSID, $db, 4, 2);
tassert('id 4 (khac nhom) vao slot 2 ok', $R['ok'], true);

// ===== F: hoan vi — id 4 dang slot 2, keo no len slot 1 =====
$R = \player\tianfu_slot_set($TESTSID, $db, 4, 1);
tassert('hoan vi ok', $R['ok'], true);
tassert('id 4 -> slot 1', \player\tianfu_owned($TESTSID, $db)[4]['slot'], 1);
tassert('id 1 bi day xuong slot 2', \player\tianfu_owned($TESTSID, $db)[1]['slot'], 2);
tassert('slot 1 = id 4 sau hoan vi', \player\tianfu_activated($TESTSID, $db)[1]['id'], 4);

// ===== G: hoan vi tao xung dot thi PHAI bi chan =====
// Tra ve: slot1 = id 4 (Tài Vận), slot2 = id 1 (Ngộ Tinh). Bat id 2 (Ngộ Tinh) vao slot 3?
// (cv 100 khong mo slot 3) -> nen dung slot 4 cung bi chan; kiem tra bang cache:
$db->prepare("UPDATE game1 SET ulv = 280 WHERE sid = ?")->execute(array($TESTSID));
$R = \player\tianfu_slot_set($TESTSID, $db, 2, 3);
tassert('cv 280 mo slot 3 nhung id 2 xung dot id 1 -> false', $R['ok'], false);
$R = \player\tianfu_slot_set($TESTSID, $db, 7, 3);   // Dan Dao: khac cong
tassert('id 7 vao slot 3 ok', $R['ok'], true);
$R = \player\tianfu_slot_set($TESTSID, $db, 1, 4);   // Ngộ Tinh khong the vao slot 4 vi id 1 dang slot 2? 
tassert('id 1 di chuyen tu slot 2 -> 4, slot 2 trong', $R['ok'], true);
$A = \player\tianfu_activated($TESTSID, $db);
tassert('slot 2 sau khi di chuyen: khong con id 1', isset($A[2]) ? $A[2]['id'] : 0, 0);
tassert('slot 4 = id 1', $A[4]['id'], 1);

// ===== H: thao do bang slot = 0 =====
$R = \player\tianfu_slot_set($TESTSID, $db, 1, 0);
tassert('thao ok', $R['ok'], true);
tassert('id 1 slot 0', \player\tianfu_owned($TESTSID, $db)[1]['slot'], 0);
tassert('khong con bonus cua id 1', \player\tianfu_bonuses($TESTSID, $db)['exp_cultivate'], 0.0);

// ===== I: xoa o dang dung bang id = 0 =====
$R = \player\tianfu_slot_set($TESTSID, $db, 0, 3);
tassert('id 0 -> xoa slot 3 ok', $R['ok'], true);
$A = \player\tianfu_activated($TESTSID, $db);
tassert('slot 3 trong', isset($A[3]), false);

// ===== J: log + mirror cuoi cung =====
$lc = $db->prepare("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = ? AND kind = 'slot'");
$lc->execute(array($TESTSID));
tassert('co log slot', intval($lc->fetchColumn()) >= 5, true);
$M = $db->prepare("SELECT tianfu_id, tianfu_power FROM game1 WHERE sid = ?");
$M->execute(array($TESTSID));
$m = $M->fetch(PDO::FETCH_ASSOC);
tassert('chu phu sau khi thao het = id 4', intval($m['tianfu_id']), 4);
// Seed `foreach (array(1,2,4,7) as $i => $id)`: $i la KHOA mang 0..3
//   id 1 -> q3 lv10 | id 2 -> q4 lv11 | id 4 -> q5 lv12 | id 7 -> q6 lv13
// Den buoc J: slot 1 = id 4, slot 3 da bi xoa o buoc I -> chi con mot o dang bat.
tassert('power = id 4 (slot 3 da xoa)', intval($m['tianfu_power']), 5 * 150 + 12 * 8);

tf_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
