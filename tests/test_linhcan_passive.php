<?php
/**
 * test_linhcan_passive.php — getplayer + zhuangtai doc node Ngu Hanh (Task 9).
 * Mac dinh (N):     "...php.exe" tests/test_linhcan_passive.php
 * Route that (+L):  "...php.exe" tests/test_linhcan_passive.php --live
 * --live can server dev dang chay (mac dinh http://127.0.0.1:8199, override bang LC_BASE).
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
$LIVE = in_array('--live', $argv);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$TESTSID = str_repeat('e', 32);

// Seed NHAN VAT cua rieng test nay (adddj/getplayer can game1.uid)
$db->exec("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, ugj, ufy, ubj, uxx, uyxb, uexp)
    VALUES ('$TESTSID', 990113, 'LCPASSIVE', 30, 5000, 5000, NOW(), 3000, 1000, 20, 0, 100000, 10000)
    ON DUPLICATE KEY UPDATE uid = 990113, ulv = 30, uhp = 5000, umaxhp = 5000, endtime = NOW(),
        ugj = 3000, ufy = 1000, ubj = 20, uxx = 0, uyxb = 100000, uexp = 10000");
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$TESTSID'");
$db->exec("UPDATE game1 SET linhcan_id = 0 WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);

// ===== N: getplayer lay danh hieu + chu dao tu node =====
// N1: chua mo he — element rong, khong cong stat
$p0 = \player\getplayer($TESTSID, $db);
tassert('N1 element rong', $p0->linhcan_element, '');
tassert('N1 ugj khong doi', intval($p0->ugj), 3000);
tassert('N1 total_lv = 0', intval($p0->linhcan_total_lv), 0);
tassert('N1 ten = Phàm Căn (chua luyen)', $p0->linhcan_name, 'Phàm Căn');
tassert('N1 title_id = pham_can', $p0->linhcan_title_id, 'pham_can');
tassert('N1 icon = icon Phàm Căn', $p0->linhcan_icon, '🌱');

// N2: kim 40/1 — atk = round(2*40*1.005*1.0) = 80; tong 40 < 60 → khong milestone
\player\linhcan_node_set($TESTSID, 'kim', 40, 1, $db);
\player\player_cache_clear($TESTSID);
$p1 = \player\getplayer($TESTSID, $db);
tassert('N2 element = chu dao', $p1->linhcan_element, 'kim');
tassert('N2 tier = bac hanh chu dao', intval($p1->linhcan_tier), 1);
tassert('N2 total_lv', intval($p1->linhcan_total_lv), 40);
tassert('N2 ten danh hiem', $p1->linhcan_name, 'Chân Linh Căn · Kim');
tassert('N2 ugj 3000+80', intval($p1->ugj), 3080);
tassert('N2 title_id', $p1->linhcan_title_id, 'chan_linh');
tassert('N2 icon = icon danh hieu (khong phai 🌀 lac loi)', $p1->linhcan_icon, '🗡️');
tassert('N2 color khong rong', $p1->linhcan_color === '', false);

// N3: chuoi khac hanh — key khong dau tinh ra 1.2 (kim khac moc), trung tinh = 1.0
tassert('N3 khac moc', \player\linhcan_get_element_multiplier($p1->linhcan_element, 'moc'), 1.2);
tassert('N3 trung tinh thuy', \player\linhcan_get_element_multiplier($p1->linhcan_element, 'thuy'), 1.0);

// N4: doi chu dao → field theo sau (tho 70/3: tong 110 ≥ 60 → milestone 5%)
\player\linhcan_node_set($TESTSID, 'tho', 70, 3, $db);
\player\player_cache_clear($TESTSID);
$p2 = \player\getplayer($TESTSID, $db);
tassert('N4 chu dao chuyen tho', $p2->linhcan_element, 'tho');
tassert('N4 tier tho', intval($p2->linhcan_tier), 3);
tassert('N4 total 110', intval($p2->linhcan_total_lv), 110);

// N5: node KHONG co nen (moc lv 0) → khong tinh, tong van 110
$db->exec("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
    VALUES ('$TESTSID', 'moc', 0, 0, NOW()) ON DUPLICATE KEY UPDATE level = 0, stage = 0");
\player\player_cache_clear($TESTSID);
$p3 = \player\getplayer($TESTSID, $db);
tassert('N5 node 0 khong doi chu dao', $p3->linhcan_element, 'tho');
tassert('N5 total van 110', intval($p3->linhcan_total_lv), 110);

$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);
$p4 = \player\getplayer($TESTSID, $db);
tassert('N6 sach node → tong 0', intval($p4->linhcan_total_lv), 0);
tassert('N6 sach node → element rong', $p4->linhcan_element, '');
tassert('N6 sach node → ve Phàm Căn', $p4->linhcan_name, 'Phàm Căn');
tassert('N6 sach node → tier 0', intval($p4->linhcan_tier), 0);

if ($LIVE) {
    // ===== L: trang zhuangtai ve bang danh hieu moi =====
    $enc = new \encode\encode();
    $lcBase = getenv('LC_BASE');
    if (!is_string($lcBase) || $lcBase === '') { $lcBase = 'http://127.0.0.1:8199'; }
    $lcGet = function ($plain) use ($enc, $lcBase) {
        return (string) shell_exec('curl -s ' . escapeshellarg($lcBase . '/game.php?cmd=' . $enc->encode($plain)));
    };
    $E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
    $snap = $db->query("SELECT endtime FROM game1 WHERE sid = '$E2ESID'")->fetch(PDO::FETCH_ASSOC);
    if (!is_array($snap)) {
        $fails++;
        echo "FAIL L: fixture e2e khong co trong game1 — chay tests/e2e_fixture.php truoc\n";
    } else {
        $nodeSnap = $db->query("SELECT element, level, stage FROM player_linhcan_node WHERE sid = '$E2ESID'")->fetchAll(PDO::FETCH_ASSOC);
        $db->exec("UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 3000 SECOND) WHERE sid = '$E2ESID'");
        $db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
            VALUES (?, 'kim', 40, 1, NOW()) ON DUPLICATE KEY UPDATE level = 40, stage = 1")->execute(array($E2ESID));
        \player\player_cache_clear($E2ESID);

        $h = $lcGet("cmd=zhuangtai&sid=$E2ESID");
        tassert('L0 HTML du dai', strlen($h) > 500, true);
        tassert('L1 zhuangtai ve danh hieu', strpos($h, 'Chân Linh Căn') !== false, true);
        tassert('L2 zhuangtai ve tong Lv', strpos($h, 'Tổng Lv 40') !== false, true);
        tassert('L3 zhuangtai ve ten hanh', strpos($h, 'Kim') !== false, true);
        tassert('L4 icon danh hieu 🗡️ hien tren trang', strpos($h, '🗡️') !== false, true);

        // L5: sach node → bang Linh Căn an (khongFatal, khong hien "Linh Căn:")
        $db->exec("DELETE FROM player_linhcan_node WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);
        $h0 = $lcGet("cmd=zhuangtai&sid=$E2ESID");
        tassert('L5 khong co bang khi chua mo', strpos($h0, 'Linh Căn:'), false);

        // Restore
        foreach ($nodeSnap as $r) {
            $st = $db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
                VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE level = ?, stage = ?");
            $st->execute(array($E2ESID, $r['element'], intval($r['level']), intval($r['stage']),
                intval($r['level']), intval($r['stage'])));
        }
        $db->exec("UPDATE game1 SET endtime = " . $db->quote($snap['endtime']) . " WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);
    }
}

if ($fails === 0) { echo "\nALL PASS\n"; }
else { echo "\n$fails FAIL\n"; }
exit($fails === 0 ? 0 : 1);
