<?php
// Test boss ca nhan dot pha linh can (Task 4). Chay: php.exe tests/test_linhcan_boss.php
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

$db->exec("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, ugj, ufy, ubj, uyxb, uexp)
    VALUES ('$TESTSID', 990112, 'LCBTEST', 30, 5000, 5000, NOW(), 3000, 1000, 20, 100000, 10000)
    ON DUPLICATE KEY UPDATE uid = 990112, ulv = 30, uhp = 5000, umaxhp = 5000, endtime = NOW(),
        ugj = 3000, ufy = 1000, ubj = 20, uyxb = 100000, uexp = 10000");
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM player_linhcan_break_log WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (34, 35, 37)");
\player\linhcan_node_set($TESTSID, 'kim', 30, 1, $db);
\player\player_cache_clear($TESTSID);

// D1: hanh chua cham tran bac → khong duoc sinh boss
tassert('D1 hoa lv0 khong sinh', \player\lcboss_spawn($TESTSID, $db, 'hoa'), false);

// D2: sinh boss kim (lv 30 = cap stage1 → to_stage 2)
$b = \player\lcboss_spawn($TESTSID, $db, 'kim');
tassert('D2 sinh boss', is_array($b), true);
tassert('D2 to_stage 2', intval($b['to_stage']), 2);
tassert('D2 element', $b['element'], 'kim');
tassert('D2 hp=8000+220*ulv', intval($b['hp']), 8000 + 220 * 30);
tassert('D2 atk=260+7*ulv', intval($b['atk']), 260 + 7 * 30);
tassert('D2 def=100+10*ulv', intval($b['def']), 100 + 10 * 30);
tassert('D2 crit=10+2*to_stage', intval($b['crit']), 10 + 2 * 2);
tassert('D2 ten tu config element_bosses', $b['name'], 'Kim Linh Tộc Trưởng');

// D3-4: active va spawn lap lai tra ve DUNG con dang chieu (khong spam 2 con)
$a = \player\lcboss_active($TESTSID, $db, 'kim');
tassert('D3 active cung id', intval($a['id']), intval($b['id']));
$b2 = \player\lcboss_spawn($TESTSID, $db, 'kim');
tassert('D4 spawn lai cung id', intval($b2['id']), intval($b['id']));

// D5: sat thuong co khoa che
$player = \player\getplayer($TESTSID, $db);
$hit = \player\lcboss_calc_damage($player, $a);
tassert('D5 du 3 key', isset($hit['damage']) && isset($hit['crit']) && isset($hit['elem_mult']), true);
tassert('D5 dmg >= 1', $hit['damage'] >= 1, true);

// D6: requirements khi CHUA giot boss → dong boss Thieu
$req = \player\linhcan_break_requirements($TESTSID, $db, 'kim');
tassert('D6 ok false', $req['ok'], false);
tassert('D6 to_stage 2', intval($req['to_stage']), 2);
$bossRowReq = array();
foreach ($req['rows'] as $r) { if ($r['key'] === 'boss') { $bossRowReq = $r; } }
tassert('D6 boss cur/need', $bossRowReq['cur'] . '/' . $bossRowReq['need'], '0/1');

// D7: giot boss (moc = ghi thang status, khuong mai pve) + du binh anh → Du het
$db->exec("UPDATE linhcan_boss_personal SET status = 'killed', hp = 0, killed_at = NOW() WHERE id = " . intval($b['id']));
tassert('D7.1 lcboss_killed true', \player\lcboss_killed($TESTSID, $db, 'kim', 2), true);
$reqA = \player\linhcan_break_requirements($TESTSID, $db, 'kim');
$baReq = array(); foreach ($reqA['rows'] as $r) { if ($r['key'] === 'binh_anh') { $baReq = $r; } }
tassert('D7.2 thieu binh anh van ok false', $reqA['ok'], false);
tassert('D7.2 binh anh need 2', intval($baReq['need']), 2);
$db->exec("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES ('$TESTSID', 990112, 37, 5)");
\player\player_cache_clear($TESTSID);
$req = \player\linhcan_break_requirements($TESTSID, $db, 'kim');
tassert('D7.3 du dieu kien → ok true', $req['ok'], true);

// D8: break that — 100% thanh cong, stage 1→2, tru dung nguyen lieu
$r = \player\linhcan_break($TESTSID, $db, 'kim');
tassert('D8 break ok', $r['ok'], true);
tassert('D8 stage moi', intval($r['stage']), 2);
$node = \player\linhcan_node_get($TESTSID, 'kim', $db);
tassert('D8 node stage 2', intval($node['stage']), 2);
tassert('D8 node van giu lv 30', intval($node['level']), 30);
$ba = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37")->fetchColumn());
tassert('D8 tru 2 binh anh (5-2)', $ba, 3);
$g1 = $db->query("SELECT uyxb, uexp FROM game1 WHERE sid = '$TESTSID'")->fetch(\PDO::FETCH_ASSOC);
tassert('D8 tru LT 20000', intval($g1['uyxb']), 80000);
tassert('D8 tru uexp 5000', intval($g1['uexp']), 5000);
$blog = intval($db->query("SELECT COUNT(*) FROM player_linhcan_break_log WHERE sid = '$TESTSID'")->fetchColumn());
tassert('D8 break_log >= 1 dong', $blog >= 1, true);

// D9: that buoc truu giua chung → hoan lai, KHONG mat tien oan (bai hoc linhcan_upgrade cu)
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37");
$r9 = \player\linhcan_break($TESTSID, $db, 'kim');
tassert('D9 thieu da → fail', $r9['ok'], false);
$g1b = $db->query("SELECT uyxb, uexp FROM game1 WHERE sid = '$TESTSID'")->fetch(\PDO::FETCH_ASSOC);
tassert('D9 LT khong dong', intval($g1b['uyxb']), 80000);
tassert('D9 uexp khong dong', intval($g1b['uexp']), 5000);
$node9 = \player\linhcan_node_get($TESTSID, 'kim', $db);
tassert('D9 stage giu nguyen 2', intval($node9['stage']), 2);

// D10: sau break, can lv 50 (cap stage 2) moi sinh boss tiep → chan ky
tassert('D10 lv 30 < cap 50 → khong sinh', \player\lcboss_spawn($TESTSID, $db, 'kim'), false);

// ===== E: boss the gioi (Task 5) =====
$seen = array();
for ($i = 0; $i < 200; $i++) {
    $e = \player\linhcan_boss_world_element($db);
    $seen[$e] = true;
}
tassert('E1 world element chi trong 5 hanh', count($seen), 5);
tassert('E1 khong con none', isset($seen['none']), false);

$cfgW = \player\linhcan_boss_config_all();
tassert('E2 config khong con boss none', isset($cfgW['boss_config']['element_bosses']['none']), false);

// E3: spawn the gioi dung AVG(ulv) TOAN BO game1 (khong loc linhcan_id nua)
$db->exec("DELETE FROM linhcan_boss WHERE status = 'active'"); // local harness — bao cao chu neu co boss dang chieu
$w = \player\linhcan_boss_spawn($db);
$avgAll = max(1, intval($db->query("SELECT AVG(ulv) FROM game1")->fetchColumn()));
tassert('E3 level = avg ulv moi nguoi', intval($w['level']), $avgAll);
tassert('E3 hp cong thuc cu giu nguyen', intval($w['hp']), 5000 + 500 * $avgAll);

// E4: drop THAT vao playerdaoju
$playerW = \player\getplayer($TESTSID, $db);
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (31, 32, 33, 34, 35, 36, 37)");
$db->exec("UPDATE game1 SET uyxb = 0 WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);
$d = \player\linhcan_boss_drops($playerW, array('element' => 'hoa'), $db);
$lt = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$TESTSID'")->fetchColumn());
tassert('E4 LT roi 100..500', ($lt >= 100 && $lt <= 500), true);
$daHoa = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 32")->fetchColumn());
tassert('E4 da hoa roi 2..4', ($daHoa >= 2 && $daHoa <= 4), true);
$daHanhKhac = intval($db->query("SELECT COUNT(*) FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (33,34,35,36)")->fetchColumn());
tassert('E4 chi roi da dung hanh boss', $daHanhKhac, 0);
tassert('E4 binh anh roi 1..2', ($d['binh_anh'] >= 1 && $d['binh_anh'] <= 2), true);
$daBA = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 37")->fetchColumn());
tassert('E4 binh anh co trong inventory', ($daBA === intval($d['binh_anh'])), true);
tassert('E4 html co chu', strpos($d['html'], 'Linh') !== false, true);

$db->exec("DELETE FROM linhcan_boss WHERE id = " . intval($w['id'])); // don boss gia vua spawn
// Cleanup
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM player_linhcan_break_log WHERE sid = '$TESTSID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID' AND djid IN (32, 34, 35, 37)");
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$TESTSID'");
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
