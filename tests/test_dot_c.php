<?php
/**
 * test_dot_c.php — Đợt C: nhiệm vụ theo cấp, chính tuyến 4–13, NPC khu mới, thưởng trực tuyến / bế quan.
 * CẦN DB (đã chạy sql/migrate_dot_b.php + migrate_dot_c.php). Tự tạo nhân vật test riêng rồi dọn.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_dot_c.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$db = $dblj;
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}
$SID = str_repeat('c', 32);
function seed($lv, $extra = '') {
    global $db, $SID;
    $db->exec("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, ugj, ufy, ubj, uyxb, uexp)
        VALUES ('$SID', 990133, 'DOTCTEST', $lv, 5000, 5000, NOW(), 3000, 1000, 20, 0, 0)
        ON DUPLICATE KEY UPDATE ulv = $lv, uexp = 0, uyxb = 0, endtime = NOW(), sfxl = 0 $extra");
    \player\player_cache_clear($SID);
}
function cleanup() {
    global $db, $SID;
    \player\online_reward_ready($db);
    foreach (array('player_nhiemvu_main', 'player_nhiemvu_daily', 'player_online_reward', 'player_offline_reward', 'playerdaoju', 'playeryaopin') as $t) {
        $db->exec("DELETE FROM $t WHERE sid = '$SID'");
    }
}
cleanup();

// ── D1. Dữ liệu ──────────────────────────────────────────────
$main = \player\nhiemvu_main_config();
// Đếm trong phạm vi đợt C (chương 1–13); chương 14+ của làn 2 có test riêng tests/test_lan2_data.php (vòng kiểm tra dưới vẫn chạy cho mọi chương)
tassert('D1 13 chuong', count(array_filter($main['chapters'], function($c){ return $c['id'] <= 13; })), 13);
$newQ = 0; $bad = array();
$qyIds = array(); foreach ($db->query("SELECT qyid FROM qy") as $r) $qyIds[intval($r['qyid'])] = 1;
$bossNames = array(); foreach ($db->query("SELECT bossname FROM boss") as $r) $bossNames[trim(strip_tags($r['bossname']))] = 1;
foreach ($main['chapters'] as $ch) {
    if ($ch['id'] < 4) continue;
    foreach ($ch['quests'] as $q) {
        if ($ch['id'] <= 13) $newQ++;
        if ($q['type'] === 'reach_region' && !isset($qyIds[$q['region']])) $bad[] = $q['id'] . ' khu';
        if ($q['type'] === 'kill_boss_name') foreach ($q['boss_names'] as $b) if (!isset($bossNames[$b])) $bad[] = $q['id'] . ' boss';
        if ($q['type'] === 'kill_monster') foreach ($q['monster_ids'] as $g) if (!\player\guaiwu_config_get($g)) $bad[] = $q['id'] . ' quai';
        if (\player\nhiemvu_main_goal_text($q) === '') $bad[] = $q['id'] . ' mo ta';
    }
}
tassert('D1 67 NV chinh tuyen moi', $newQ, 67);
tassert('D1 tham chieu khu/boss/quai/mo ta deu dung', $bad, array());
$npcs = json_decode(file_get_contents(__DIR__ . '/../class/config/npc_config.json'), true);
$bad = array();
for ($nid = 40; $nid <= 48; $nid++) {
    if (!isset($npcs[$nid])) { $bad[] = "npc $nid"; continue; }
    foreach (explode(',', $npcs[$nid]['taskid']) as $rw) {
        $t = \player\renwu_config_get(intval($rw));
        if (!$t || !\player\guaiwu_config_get(intval($t['rwyq']))) $bad[] = "nv $rw";
    }
    $st = $db->prepare("SELECT COUNT(*) FROM mid WHERE CONCAT(',', mnpc, ',') LIKE ?");
    $st->execute(array('%,' . $nid . ',%'));
    if (intval($st->fetchColumn()) < 1) $bad[] = "npc $nid chua dat vao map";
}
tassert('D1 9 NPC x 3 NV hop le + da dat vao map', $bad, array());

// ── D2. Thưởng theo cấp ───────────────────────────────────────
$need100 = 101 * 101 * 110;
$r = \player\nhiemvu_reward(array('exp' => 400, 'lt' => 150, 'mt' => 0), 'daily', 100);
tassert('D2 ngay cap 100 = 3% thanh cap', $r['exp'], intval(floor($need100 * 3 / 100)));
tassert('D2 linh thach x(1+100/100)', $r['lt'], 300);
$r = \player\nhiemvu_reward(array('exp' => 400, 'lt' => 150, 'mt' => 0), 'daily', 1);
tassert('D2 cap 1 giu muc san cu', $r['exp'], 400);
$r = \player\nhiemvu_reward(array('type' => 'complete_all', 'exp' => 0, 'lt' => 0, 'mt' => 20), 'weekly', 100);
tassert('D2 tong hop tuan = 50%', $r['exp'], intval(floor($need100 * 50 / 100)));
tassert('D3 chuong 1 o cap 300 tinh tran cap 10', \player\nhiemvu_main_ref_level($main['chapters'][0], 300), 10);

// ── D4. Chính tuyến chương 4 ──────────────────────────────────
seed(55);
\player\nhiemvu_main_get_all($SID, $db);
foreach ($main['chapters'] as $ch) if ($ch['id'] <= 3) foreach ($ch['quests'] as $q) {
    $db->prepare("UPDATE player_nhiemvu_main SET status = 2 WHERE sid = ? AND quest_id = ?")->execute(array($SID, $q['id']));
}
$rv = \player\nhiemvu_main_update($SID, 'reach_region', $db, array('region' => 22));
tassert('D4 sai khu (chua toi luot 4.5) khong tinh', !empty($rv['ok']), false);
$rv = \player\nhiemvu_main_update($SID, 'reach_region', $db, array('region' => 21));
tassert('D4 den Viem Duong -> xong 4.1', !empty($rv['ok']) && $rv['quest']['id'] === '4.1', true);
\player\player_cache_clear($SID);
$p = \player\getplayer($SID, $db);
tassert('D4 nhan 5% thanh cap 55', intval($p->uexp), intval(floor(56 * 56 * 110 * 5 / 100)));
\player\nhiemvu_update_all($SID, 'kill', 1, $db, array('monster_level' => 50, 'monster_id' => 1));
\player\nhiemvu_update_all($SID, 'kill', 1, $db, array('monster_level' => 30, 'monster_id' => 1));
$st = $db->prepare("SELECT progress FROM player_nhiemvu_main WHERE sid = ? AND quest_id = '4.2'");
$st->execute(array($SID));
tassert('D4 4.2 chi tinh quai cap 45-58', intval($st->fetchColumn()), 1);

// ── D5. Lọc cấp quái cho NV ngày ──────────────────────────────
seed(100);
$db->prepare("INSERT INTO player_nhiemvu_daily (sid, quest_id, reset_date, progress, completed) VALUES (?, 'D2', ?, 0, 0)")
   ->execute(array($SID, date('Y-m-d')));
\player\nhiemvu_update_all($SID, 'kill', 1, $db, array('monster_level' => 10));
\player\nhiemvu_update_all($SID, 'kill', 1, $db, array('monster_level' => 90));
$st = $db->prepare("SELECT progress FROM player_nhiemvu_daily WHERE sid = ? AND quest_id = 'D2' AND reset_date = ?");
$st->execute(array($SID, date('Y-m-d')));
tassert('D5 quai thap hon 15 cap khong tinh NV ngay', intval($st->fetchColumn()), 1);

// ── D6. Trực tuyến ────────────────────────────────────────────
\player\online_reward_tick($SID, $db);
$db->exec("UPDATE player_online_reward SET last_ts = last_ts - 120 WHERE sid = '$SID'");
\player\online_reward_tick($SID, $db);
$db->exec("UPDATE player_online_reward SET last_ts = last_ts - 1000 WHERE sid = '$SID'");
\player\online_reward_tick($SID, $db);
$st = $db->prepare("SELECT seconds FROM player_online_reward WHERE sid = ? AND day = ?");
$st->execute(array($SID, date('Y-m-d')));
tassert('D6 cong 120s, bo khoang treo 1000s', intval($st->fetchColumn()), 120);
$r = \player\online_reward_claim($SID, 0, 100, $db);
tassert('D6 chua du 10 phut', $r['ok'], false);
$db->exec("UPDATE player_online_reward SET seconds = 600 WHERE sid = '$SID'");
$r = \player\online_reward_claim($SID, 0, 100, $db);
tassert('D6 nhan moc 10 phut', $r['ok'], true);
$r = \player\online_reward_claim($SID, 0, 100, $db);
tassert('D6 khong nhan 2 lan', $r['ok'], false);
\player\player_cache_clear($SID);
tassert('D6 linh thach = 2 x cap', intval(\player\getplayer($SID, $db)->uyxb), 200);

// ── D7. Bế Quan ───────────────────────────────────────────────
$off5h = date('Y-m-d H:i:s', time() - 5 * 3600); // giờ PHP như game.php (MySQL có thể lệch múi giờ)
seed(55, ", endtime = '$off5h'");
$p = \player\getplayer($SID, $db);
$e1 = \player\offline_reward_on_login($p, $db);
tassert('D7 offline 5 gio = 10% thanh cap', $e1, intval(floor(56 * 56 * 110 * 2 * 5 / 100)));
$e2 = \player\offline_reward_on_login($p, $db);
tassert('D7 lan 2 trong ngay chi con 3 gio (tran 8 gio/ngay)', $e2, intval(floor(56 * 56 * 110 * 2 * 3 / 100)));
tassert('D7 lan 3 het tran', \player\offline_reward_on_login($p, $db), 0);
$r = \player\offline_reward_claim($SID, $db);
\player\player_cache_clear($SID);
tassert('D7 nhan tu vi be quan', $r['ok'] && intval(\player\getplayer($SID, $db)->uexp) === $e1 + $e2, true);
tassert('D7 khong nhan 2 lan', \player\offline_reward_claim($SID, $db)['ok'], false);
seed(55, ", endtime = '$off5h', sfxl = 1");
tassert('D7 dang Tu Luyen khong tinh', \player\offline_reward_on_login(\player\getplayer($SID, $db), $db), 0);

cleanup();
$db->exec("DELETE FROM game1 WHERE sid = '$SID'");
echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
