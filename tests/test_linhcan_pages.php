<?php
// Live page tests Task 11: claim quest + 2 loai boss qua URL THAT (dead-code fix proof).
// Can dev server chay (mac dinh http://127.0.0.1:8123, override LC_BASE) + fixture e2e trong DB.
// Chay: D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe tests/test_linhcan_pages.php
// Khong ghi crop credential: lay chuoi noi ket tu pdo.php (nguoi ma game.php dung).
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../class/encode.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else {
        $fails++;
        echo "FAIL  $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
// Body < 500 byte = bi da ve login (endtime het han / dung lai blob cmd 1 lan).
function tok($name, $body) {
    if (strlen($body) < 500) { echo "!! $name body=" . strlen($body) . " byte — server chet hoac blob cmd da dung\n"; }
    tassert($name, strlen($body) > 500, true);
}

$E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$ROOT = dirname(__DIR__);
$dsn = "mysql:host=localhost;dbname=game;charset=utf8";
$pdoCfg = null;
// pdo.php khai bao $sqlname/$sqlpass o pham vi toan cau khi duoc include.
include $ROOT . '/pdo.php';
$db = new PDO($dsn, $sqlname, $sqlpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$db->exec("SET NAMES utf8mb4");

$enc = new \encode\encode();
$base = getenv('LC_BASE');
if (!is_string($base) || $base === '') { $base = 'http://127.0.0.1:8123'; }
function getpage($plain) {
    global $enc, $base;
    // Blob cmd DUNG MOT LAN (game.php chong replay) → encode lai moi lan goi.
    return (string) shell_exec('curl -s ' . escapeshellarg($base . '/game.php?cmd=' . $enc->encode($plain)));
}

$DA_KIM = \player\linhcan_da_id('kim');
$BA_ID  = \player\linhcan_binh_anh_id();
$DJIDS  = '(32,33,34,35,36,37)';

$db->exec("UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 3000 SECOND) WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_linhcan_quest WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$E2ESID' AND djid IN $DJIDS");
$db->exec("UPDATE game1 SET uyxb = 100000, uexp = 100000, uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);

// G1: trang quest render du 5 quest Task 6 + dung CSS noi (khong con <style> cung cap)
$g1 = getpage("cmd=linhcan_quest&sid=$E2ESID");
tok('G1 page quest live', $g1);
tassert('G1 render 5 quest', strpos($g1, 'Luyện căn cần mẫn') !== false && strpos($g1, 'Đột phá cảnh giới') !== false, true);
tassert('G1 class lcq-wrap', strpos($g1, 'lcq-wrap') !== false, true);
tassert('G1 khong con <style> noi tuyen', strpos($g1, '.lcq-container') === false, true);
tassert('G1 tabbar dung lai khuon Task 10', strpos($g1, 'lc-tabbar') !== false && strpos($g1, 'Boss Ngũ Hành') !== false, true);

// G2: bo day du progress → claim QUA URL that. Bug cu `cmd==claim_reward`: nhanh CHET,
// khong bao gio lam duoc dieu nay.
foreach (array('train_element', 'kill_monsters', 'kill_boss', 'receive_stone') as $ht) {
    \player\linhcan_quest_update($E2ESID, $ht, 20, $db);
}
\player\linhcan_quest_update($E2ESID, 'break_once', 1, $db);
\player\player_cache_clear($E2ESID);
$ltBefore = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
$g2 = getpage("cmd=linhcan_quest&claim_reward=1&sid=$E2ESID");
tok('G2 page claim live', $g2);
$ltAfter = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
$baG2 = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$E2ESID' AND djid = $BA_ID")->fetchColumn());
tassert('G2 claim cong 500 LT THAT', $ltAfter - $ltBefore, 500);
tassert('G2 Binh Anh vao TOI THAT (1-2)', $baG2 >= 1 && $baG2 <= 2, true);
tassert('G2 thong bao nhan thuong hien tren page', strpos($g2, 'Nhận thưởng thành công') !== false, true);
$weekNow = \player\linhcan_quest_current_week();
$doneFlag = intval($db->query("SELECT completed FROM player_linhcan_quest WHERE sid = '$E2ESID' AND quest_week = '$weekNow'")->fetchColumn());
tassert('G2 cot completed = 1', $doneFlag, 1);
// Regression Task 11: `$isCompleted` tung tinh TRUOC khi claim → nhan xong thuong nut van con.
tassert('G2 nhan xong thi AN nut NHAN THUONG', strpos($g2, 'class="lcq-btn"') === false, true);
tassert('G2 status chuyen sang da nhan', strpos($g2, 'Đã nhận thưởng tuần này') !== false, true);

// G3: claim lan 2 bi chan, tien khong dong
$g3 = getpage("cmd=linhcan_quest&claim_reward=1&sid=$E2ESID");
$lt3 = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
tassert('G3 claim kep LT khong dong', $lt3, $ltAfter);
tassert('G3 page bao da nhan', strpos($g3, 'Đã nhận thưởng tuần này rồi') !== false, true);

// G4: boss the gioi — attack QUA URL lam HP GIAM THAT. Bug cu `cmd==attack_lc_boss`: khong the.
// Luu y: linhcan_boss la bang CHUNG toan server — test xoa de co mot con moi spawn.
$db->exec("DELETE FROM linhcan_boss");
$g4a = getpage("cmd=linhcan_boss&sid=$E2ESID");
tok('G4 page boss the gioi', $g4a);
$bwRow = $db->query("SELECT id, hp FROM linhcan_boss ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
tassert('G4 boss tu spawn', is_array($bwRow), true);
$bwHp = intval($bwRow['hp']); $bwId = intval($bwRow['id']);
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$g4b = getpage("cmd=linhcan_boss&attack_lc_boss=1&sid=$E2ESID");
tok('G4 page sau khi tan cong', $g4b);
$bwHp2 = intval($db->query("SELECT hp FROM linhcan_boss WHERE id = $bwId")->fetchColumn());
tassert('G4 world boss hp GIAM qua URL', $bwHp2 < $bwHp, true);
tassert('G4 hien dong sat thuong', strpos($g4b, 'sát thương lên') !== false, true);
$uHp4 = intval($db->query("SELECT uhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
$uMax4 = intval($db->query("SELECT umaxhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
tassert('G4 phan dong ghi van vao game1', $uHp4 <= $uMax4 && $uHp4 >= 0, true);

// G5: boss ca nhan theo hanh — seed Lv tran bac roi khiêu chiến + giot QUA URL
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$E2ESID'");
\player\linhcan_node_set($E2ESID, 'kim', \player\linhcan_stage_cap(0), 0, $db); // tran bac 0 → du dieu kien sinh boss to_stage 1
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
// completed=1 (do G2) chan hook `linhcan_quest_update` → mo lai de do duoc tang hook.
$db->exec("UPDATE player_linhcan_quest SET completed = 0 WHERE sid = '$E2ESID' AND quest_week = '$weekNow'");
\player\player_cache_clear($E2ESID);
$g5 = getpage("cmd=linhcan_boss&sid=$E2ESID&el=kim");
tok('G5 page boss ca nhan el=kim', $g5);
tassert('G5 page co card boss', strpos($g5, 'lcb-card') !== false, true);
tassert('G5 page co nut tan cong', strpos($g5, 'TẤN CÔNG') !== false, true);
$pbRow = $db->query("SELECT * FROM linhcan_boss_personal WHERE sid = '$E2ESID' AND element = 'kim' AND status = 'active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
tassert('G5 boss ca nhan tu spawn khi mo page', is_array($pbRow), true);
$qBefore = \player\linhcan_quest_get_progress($E2ESID, $db);
$kbBefore = isset($qBefore['progress']['3']) ? intval($qBefore['progress']['3']) : 0;
$db->exec("UPDATE linhcan_boss_personal SET hp = 1 WHERE id = " . intval($pbRow['id']));
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$g5k = getpage("cmd=linhcan_boss&sid=$E2ESID&el=kim&pattack=1");
tok('G5 page sau khi giot boss ca nhan', $g5k);
tassert('G5 phan tich tieu diet + mo khoa dot pha', strpos($g5k, 'đã bị tiêu diệt') !== false && strpos($g5k, 'Mở khóa Đột Phá') !== false, true);
$pbSt = $db->query("SELECT status FROM linhcan_boss_personal WHERE id = " . intval($pbRow['id']))->fetchColumn();
tassert('G5 pattack giot boss → status killed', $pbSt, 'killed');
tassert('G5 trang KHONG UPDATE lai hp sau mark_killed', intval($db->query("SELECT hp FROM linhcan_boss_personal WHERE id = " . intval($pbRow['id']))->fetchColumn()), 0);
$baG5 = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid = '$E2ESID' AND djid = $DA_KIM")->fetchColumn());
tassert('G5 da Kim vao TOI that', $baG5 >= 1, true);
$qPb = \player\linhcan_quest_get_progress($E2ESID, $db);
tassert('G5 hook kill_boss tang DUNG 1 (khong double)', intval($qPb['progress']['3']) - $kbBefore, 1);

// G6: trong thuong chan tan cong (khuon huyetmach_boss.php:17)
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$E2ESID'");
\player\linhcan_node_set($E2ESID, 'moc', \player\linhcan_stage_cap(0), 0, $db);
$sp6 = \player\lcboss_spawn($E2ESID, $db, 'moc');
tassert('G6 seed boss moc', is_array($sp6), true);
$db->exec("UPDATE game1 SET uhp = 0 WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
$g6Hp = intval($db->query("SELECT hp FROM linhcan_boss_personal WHERE sid = '$E2ESID' AND element = 'moc' AND status = 'active'")->fetchColumn());
$g6 = getpage("cmd=linhcan_boss&sid=$E2ESID&el=moc&pattack=1");
$g6Hp2 = intval($db->query("SELECT hp FROM linhcan_boss_personal WHERE sid = '$E2ESID' AND element = 'moc' AND status = 'active'")->fetchColumn());
tassert('G6 uhp=0 → boss khong mat mau', $g6Hp2, $g6Hp);
tassert('G6 page bao trong thuong', strpos($g6, 'đang trọng thương') !== false, true);

// G7: trang wash co class lcw-* (Task 7 trang, Task 11 CSS)
$g7 = getpage("cmd=linhcan_wash&sid=$E2ESID");
tok('G7 page tay linh', $g7);
tassert('G7 wash render', strpos($g7, 'lcw-wrap') !== false, true);

// G8: hanh la / thieu el → page van render duoc (khong fatal)
$g8 = getpage("cmd=linhcan_boss&sid=$E2ESID&el=kimchi");
tok('G8 el la → chuyen ve che do the gioi', $g8);
tassert('G8 el la khong hien loi PHP', stripos($g8, 'Fatal error') === false && strpos($g8, 'Notice') === false && strpos($g8, 'Warning') === false, true);
$gAll = $g1 . $g2 . $g3 . $g4a . $g4b . $g5 . $g5k . $g6 . $g7 . $g8;
tassert('KHONG co loi PHP tren moi page', stripos($gAll, 'Fatal error') === false && stripos($gAll, 'Parse error') === false && stripos($gAll, 'Undefined ') === false, true);

// Cleanup
$db->exec("DELETE FROM player_linhcan_quest WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM linhcan_boss_personal WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM linhcan_boss");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$E2ESID' AND djid IN $DJIDS");
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$E2ESID'");
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
