<?php
// Test Boss mach huyet. Chay: php.exe tests/test_huyetmach_boss.php
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

$hasPlayer = intval($db->query("SELECT COUNT(*) FROM game1 WHERE sid = '$TESTSID'")->fetchColumn());
if (!$hasPlayer) {
    $db->prepare("INSERT INTO game1 (sid, uname, ulv, uhp, umaxhp, endtime, huyetmach_id, huyetmach_quality, huyetmach_awaken_stage)
        VALUES ('$TESTSID', 'HMTEST', 300, 1000, 10000, NOW(), 1, 1, 1)")->execute();
    $tmpRow = true;
} else {
    $tmpRow = false;
    $db->prepare("UPDATE game1 SET huyetmach_id = 1, huyetmach_quality = 1, huyetmach_awaken_stage = 1 WHERE sid = ?")->execute(array($TESTSID));
}
$db->prepare("DELETE FROM huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM player_huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid IN (112,113)")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);

// A1: config du 33 boss
$cfg = \player\huyetmach_boss_config_all();
tassert('boss config 33', count($cfg['bosses']), 33);
tassert('boss chan_long_2 dung ten', \player\huyetmach_boss_row('chân_long', 2) ? \player\huyetmach_boss_row('chân_long', 2)['name'] : '', 'Rồng Ngàn Tuổi');
tassert('boss stage 5 khong ton tai', \player\huyetmach_boss_row('chân_long', 5), false);

// A2: spawn theo huyet mach + stage ke tiep
$b = \player\huyetmach_boss_spawn($TESTSID, $db);
tassert('spawn sinh boss', is_array($b), true);
tassert('spawn dung ten', $b['name'], 'Rồng Ngàn Tuổi');
tassert('spawn dung stage', intval($b['stage']), 2);
tassert('spawn co icon tu config', $b['icon'] !== '', true);
tassert('hp > 0', $b['hp'] > 0, true);
$a = \player\huyetmach_boss_active($TESTSID, $db);
tassert('active trung id voi spawn', intval($a['id']), intval($b['id']));
tassert('active duoc nap icon', isset($a['icon']), true);

// A3: throttle max_fights_per_day
$max = intval($cfg['boss_config']['max_fights_per_day']);
$need = $max - \player\huyetmach_boss_fights_today($TESTSID, $db);
for ($i = 0; $i < $need; $i++) {
    $db->prepare("INSERT INTO huyetmach_boss (sid, cfg_key, name, element, level, hp, maxhp, atk, def, crit, spawned_at, status)
        VALUES (?, 'filler', 'Filler', 'none', 1, 1, 1, 1, 1, 0, NOW(), 'killed')")->execute(array($TESTSID));
}
tassert('fights_today dat max', \player\huyetmach_boss_fights_today($TESTSID, $db) >= $max, true);
// Spawn uu tien boss dang chieu: nguoi choi dang danh do phai danh tiep duoc,
// nen chan luot chi test duoc khi KHONG con boss nao song.
$bAgain = \player\huyetmach_boss_spawn($TESTSID, $db);
tassert('spawn tro lai boss dang chieu', intval($bAgain['id']), intval($b['id']));
$db->prepare("UPDATE huyetmach_boss SET status = 'killed', killed_at = NOW() WHERE sid = ? AND status = 'active'")->execute(array($TESTSID));
tassert('spawn bi chan khi het luot', \player\huyetmach_boss_spawn($TESTSID, $db), false);
$st = \player\huyetmach_boss_status($TESTSID, $db);
tassert('status blocked', $st['state'], 'blocked');

// A4: giết boss -> mark + phat that vao playerdaoju
$db->prepare("DELETE FROM huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);
$b2 = \player\huyetmach_boss_spawn($TESTSID, $db);
$before = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 112")->fetchColumn());
$fin = \player\huyetmach_boss_finish($TESTSID, $db, $b2);
// stage 2: 4 (by_stage) + 10 (first_kill) = 14
tassert('finish tra dung so Huyet Thach', $fin['huyet_thach'], 14);
$after = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$TESTSID' AND djid = 112")->fetchColumn());
tassert('Huyet Thach vao toi', $after, $before + 14);
tassert('mark sau khi giot', \player\huyetmach_boss_killed($TESTSID, $b2['name'], $db), true);
$row = $db->query("SELECT status FROM huyetmach_boss WHERE id = " . intval($b2['id']))->fetchColumn();
tassert('boss chuyen status killed', $row, 'killed');
// giot lan 2 (boss gia) khong duoc nhan first_kill nua
$b3 = \player\huyetmach_boss_spawn($TESTSID, $db);
$fin2 = \player\huyetmach_boss_finish($TESTSID, $db, $b3);
tassert('first_kill khong lap lai', $fin2['huyet_thach'], 4);
$st2 = \player\huyetmach_boss_status($TESTSID, $db);
tassert('status farmer con luot', $st2['state'], 'farmer');

// A5: mach am dung dung chi so (khong query DB lan 2)
$vb = array('dodge' => 0, 'reduce' => 0);
tassert('khong co mach thi khong ne', \player\huyetmach_boss_vessel_dodge($vb), false);
tassert('reduce 0 giu nguyen don', \player\huyetmach_boss_vessel_reduce($vb, 100), 100);
$vb2 = array('dodge' => 100, 'reduce' => 100);   // bi chan 15%
tassert('reduce chan 15', \player\huyetmach_boss_vessel_reduce($vb2, 100), 85);

$db->prepare("DELETE FROM huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM player_huyetmach_boss WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid IN (112,113)")->execute(array($TESTSID));
if ($tmpRow) {
    $db->prepare("DELETE FROM game1 WHERE sid = ?")->execute(array($TESTSID));
} else {
    $db->prepare("UPDATE game1 SET huyetmach_id = 0, huyetmach_awaken_stage = 0 WHERE sid = ?")->execute(array($TESTSID));
}
\player\player_cache_clear($TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "$fails FAIL\n";
exit($fails === 0 ? 0 : 1);
