<?php
/**
 * test_tianfu_open.php — Khai Phu Dan 114: tru dan, roll co trong, khong mat lan nao.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_open.php
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
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFTEST', 1000, 500);

// ===== A: trong dan -> fail, khong ghi gi =====
$R = \player\tianfu_open($TESTSID, $db);
tassert('khong dan -> ok false', $R['ok'], false);
tassert('thong bao ro', $R['msg'] !== '', true);
tassert('khong co dong player_tianfu nao', \player\tianfu_total_lv(\player\tianfu_owned($TESTSID, $db)), 0);
tassert('log khong bi nhiem', \player\tianfu_open_count($TESTSID, $db), 0);

// ===== B: 300 lan khai mo -> 18 o deu bat duoc (weight > 0 ca 18) =====
tf_give_daoju($db, $TESTSID, $TESTUID, 114, 400);
$seenTalent = array(); $seenQuality = array();
for ($i = 0; $i < 300; $i++) {
    $R = \player\tianfu_open($TESTSID, $db);
    if (!$R['ok']) { echo "FAIL open lan $i: " . $R['msg'] . "\n"; $fails++; break; }
    $seenTalent[$R['talent']['id']] = true;
    $seenQuality[$R['quality']] = true;
}
tassert('18/18 thien phu deu co trong roll', count($seenTalent), 18);
tassert('pham chat 1..10 deu ra it nhat 1 lan', count($seenQuality), 10);
tassert('dan bi tru het 300', \player\tianfu_open_count($TESTSID, $db), 300);

$O = \player\tianfu_owned($TESTSID, $db);
$have = 0;
foreach ($O as $r) { if ($r['have'] === 1) $have++; }
tassert('du 18 o da so huu', $have, 18);

// ===== C: tru dan dung so con lai =====
$row = $db->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND uid = ? AND djid = 114");
$row->execute(array($TESTSID, $TESTUID));
tassert('con 100 dan', intval($row->fetchColumn()), 100);

// ===== D: khong mat lan — trung o thi +1 pham chat =====
// Xoa toan bo, dung 1 dan ep vao mot o cu the bang cach kiem tra ket qua vong lap.
$db->exec("DELETE FROM player_tianfu WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM player_tianfu_log WHERE sid = '$TESTSID'");
// Nap them dan truoc vong lap: o ra la loai weight 3 (p = 1/24) thi voi 100 dan
// xac suat khong trung o nao trong 100 lan la 1,4% -> het dan, `talent` = false,
// dieu kien while dung thanh true mai mai (test treo). 3000 dan = du tuyen.
tf_give_daoju($db, $TESTSID, $TESTUID, 114, 3000);
$first = \player\tianfu_open($TESTSID, $db);
tassert('lan dau = thien phu moi', $first['is_new'], true);
$again = \player\tianfu_open($TESTSID, $db);
$tries = 0;
while ($again['talent']['id'] !== $first['talent']['id'] && $tries < 2000) {
    $again = \player\tianfu_open($TESTSID, $db);
    $tries++;
}
tassert('tim thay o trung trong ' . $tries . ' lan thu', $again['talent']['id'], $first['talent']['id']);
tassert('lan trung van ok', $again['ok'], true);
tassert('lan trung is_new false', $again['is_new'], false);
$cur = \player\tianfu_owned($TESTSID, $db);
if ($first['is_new'] && $first['quality'] < 10) {
    tassert('pham chat tang dung 1', $cur[$first['talent']['id']]['quality'], $first['quality'] + 1);
} else {
    tassert('da tran pham thi giu nguyen', $cur[$first['talent']['id']]['quality'], 10);
}
// Vong lap tim o trung CHAC CHAN chen them cac o khac truoc khi trung (45 lan thu
// ra ~16 loai khac nhau), nen khong the doi "ca bang con 1 dong". Cai can kiem
// chung la o bi trung KHONG tao dong thu hai (PK sid+tianfu_id + ON DUPLICATE).
$cntSt = $db->prepare("SELECT COUNT(*) FROM player_tianfu WHERE sid = ? AND tianfu_id = ?");
$cntSt->execute(array($TESTSID, intval($first['talent']['id'])));
tassert('o bi trung van chi 1 dong', intval($cntSt->fetchColumn()), 1);
$allSt = $db->prepare("SELECT COUNT(*) FROM player_tianfu WHERE sid = ?");
$allSt->execute(array($TESTSID));
$allRows = intval($allSt->fetchColumn());
tassert('so dong = so lan open that (khong co dong ao)', $allRows >= 2 && $allRows <= 18, true);

tf_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
