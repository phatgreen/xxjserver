<?php
// Test quest tuan Linh Can 5/5 hook (Task 6). Chay: php.exe tests/test_linhcan_quest.php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}
$TESTSID = str_repeat('f', 32);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
// Seed NHAN VAT cua rieng test nay (khong duoc phu thuoc vao test khac — adddj can uid)
$db->exec("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, ugj, ufy, ubj, uyxb, uexp)
    VALUES ('$TESTSID', 990112, 'LCBTEST', 30, 5000, 5000, NOW(), 3000, 1000, 20, 0, 10000)
    ON DUPLICATE KEY UPDATE uid = 990112, ulv = 30, uhp = 5000, umaxhp = 5000, endtime = NOW(),
        ugj = 3000, ufy = 1000, ubj = 20, uyxb = 0, uexp = 10000");
$week = \player\linhcan_quest_current_week();
$db->exec("DELETE FROM player_linhcan_quest WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37");
$db->exec("UPDATE game1 SET uyxb = 0 WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);

// F1: config 5 quest, type duy nhat khop ham moi
$cfg = \player\linhcan_quest_config_all();
$types = array();
foreach ($cfg['weekly_quests'] as $q) { $types[] = $q['type']; }
sort($types);
tassert('F1 5 type dang hook', implode(',', $types), 'break_once,kill_boss,kill_monsters,receive_stone,train_element');

// F2: tung hook tang dung quest
$p = \player\linhcan_quest_get_progress($TESTSID, $db);
tassert('F2 bat dau 5 key = 0', count($p['progress']), 5);
\player\linhcan_quest_update($TESTSID, 'train_element', 1, $db);
\player\linhcan_quest_update($TESTSID, 'kill_monsters', 3, $db);
\player\linhcan_quest_update($TESTSID, 'loai_khong_ton_tai', 5, $db);
$p = \player\linhcan_quest_get_progress($TESTSID, $db);
tassert('F2 train_element=1', intval($p['progress']['1']), 1);
tassert('F2 kill_monsters=3', intval($p['progress']['2']), 3);
tassert('F2 type la khong tang gi', array_sum($p['progress']), 4);

// F3: give_stone hook tren = receive_stone dung
\player\linhcan_give_stone($TESTSID, 32, 2, $db);
$p = \player\linhcan_quest_get_progress($TESTSID, $db);
tassert('F3 receive_stone tang 2', intval($p['progress']['4']), 2);

// F4: chua du → claim fail
$r = \player\linhcan_quest_claim($TESTSID, $db);
tassert('F4 chua du fail', $r['ok'], false);

// F5: du 5/5 → claim cap LT + BINH ANH THAT vao inventory
\player\linhcan_quest_update($TESTSID, 'train_element', 9, $db);
\player\linhcan_quest_update($TESTSID, 'kill_monsters', 7, $db);
\player\linhcan_quest_update($TESTSID, 'kill_boss', 1, $db);
\player\linhcan_quest_update($TESTSID, 'receive_stone', 3, $db);
\player\linhcan_quest_update($TESTSID, 'break_once', 1, $db);
$r = \player\linhcan_quest_claim($TESTSID, $db);
tassert('F5 claim ok', $r['ok'], true);
$uyxb = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$TESTSID'")->fetchColumn());
tassert('F5 LT = 500 (+2 cua give_stone F3 khong lien quan)', $uyxb, 500);
$ba = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37")->fetchColumn());
tassert('F5 binh anh roi that 1..2', ($ba >= 1 && $ba <= 2), true);
tassert('F5 msg co binh anh', strpos($r['msg'], 'Bình Anh') !== false, true);

// F6: claim lan 2 bi chan; claim sau khi completed khong cho them
$r2 = \player\linhcan_quest_claim($TESTSID, $db);
tassert('F6 claim lap lai fail', $r2['ok'], false);
$ba2 = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37")->fetchColumn());
tassert('F6 so da khong doi', $ba2, $ba);

// F7: completed quyen sach — update sau khi claim khong tang them
$before = \player\linhcan_quest_get_progress($TESTSID, $db);
$sumBefore = array_sum($before['progress']);
\player\linhcan_quest_update($TESTSID, 'kill_monsters', 1, $db);
$after = \player\linhcan_quest_get_progress($TESTSID, $db);
tassert('F7 completed khoa progress', array_sum($after['progress']), $sumBefore);

$db->exec("DELETE FROM player_linhcan_quest WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (32, 37)");
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
