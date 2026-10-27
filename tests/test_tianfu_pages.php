<?php
/**
 * test_tianfu_pages.php — 3 route THAT cua he Thien Phu: tianfu / tianfu_collection / tianfu_open.
 * Can dev server dang chay (mac dinh http://127.0.0.1:8091, override bang bien moi truong TP_BASE)
 * va nhan vat e2e trong DB (chay tests/e2e_fixture.php truoc).
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_pages.php
 * Khong ghi credential: lay chuoi noi ket tu pdo.php (dung nguoi ma game.php dung).
 */
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
// Body < 500 byte = bi da ve login (endtime het han) hoac blob cmd mot lan da bi dung.
function tok($name, $body) {
    if (strlen($body) < 500) { echo "!! $name body=" . strlen($body) . " byte — server chet hoac blob cmd da dung\n"; }
    tassert($name, strlen($body) > 500, true);
}

$E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$ROOT = dirname(__DIR__);
$dsn = "mysql:host=localhost;dbname=game;charset=utf8";
$sqlname = null; $sqlpass = null;
include $ROOT . '/pdo.php';   // khai bao $sqlname/$sqlpass o pham vi toan cau
$db = new PDO($dsn, $sqlname, $sqlpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$db->exec("SET NAMES utf8mb4");

$enc = new \encode\encode();
$base = getenv('TP_BASE');
if (!is_string($base) || $base === '') { $base = 'http://127.0.0.1:8091'; }

function getpage($plain) {
    global $enc, $base;
    // Blob cmd DUNG MOT LAN (game.php chong replay) -> encode lai moi lan goi.
    return (string) shell_exec('curl -s ' . escapeshellarg($base . '/game.php?cmd=' . $enc->encode($plain)));
}
function postpage($plain, $fields) {
    global $enc, $base;
    $args = '';
    foreach ($fields as $k => $v) { $args .= ' -d ' . escapeshellarg($k . '=' . $v); }
    return (string) shell_exec('curl -s -X POST' . $args . ' '
        . escapeshellarg($base . '/game.php?cmd=' . $enc->encode($plain)));
}
function pillCount($db, $sid) {
    $st = $db->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = 114");
    $st->execute(array($sid));
    return intval($st->fetchColumn());
}
function ownedCount($db, $sid) {
    $st = $db->prepare("SELECT COUNT(*) FROM player_tianfu WHERE sid = ?");
    $st->execute(array($sid));
    return intval($st->fetchColumn());
}

// ===== SETUP: nhan vat e2e phai con han, Thien Phu trang, khong co dan 114 =====
$db->exec("UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 3000 SECOND) WHERE sid = '$E2ESID'");
$db->exec("UPDATE game1 SET tf = 500, uyxb = 5000000, tianfu_id = 0, tianfu_quality = 0,
           tianfu_lv = 0, tianfu_power = 0 WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_tianfu WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_tianfu_log WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$E2ESID' AND djid = 114");
\player\player_cache_clear($E2ESID);
$E2EUID = intval($db->query("SELECT uid FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
tassert('fixture e2e ton tai (uid doc duoc)', $E2EUID > 0, true);

// ===== P1: trang chu Thien Phu (Task 8) =====
$g1 = getpage("cmd=tianfu&sid=$E2ESID");
tok('P1 route tianfu live', $g1);
tassert('P1 class tp-wrap + fx', strpos($g1, 'class="tp-wrap tp-fx') !== false, true);
tassert('P1 title THIEN PHU', strpos($g1, '<span class="tp-title">THIÊN PHÚ</span>') !== false, true);
tassert('P1 chua co chu phu (dang trang)', strpos($g1, 'Chưa có Chủ Phú') !== false, true);
tassert('P1 tabbar 4 nut', substr_count($g1, 'tp-tabbar') === 1 && substr_count($g1, '<nav class="tp-tabbar">') === 1, true);

// ===== P2: Phu Cac (Task 9) =====
$g2 = getpage("cmd=tianfu_collection&sid=$E2ESID");
tok('P2 route tianfu_collection live', $g2);
tassert('P2 luoi dung 18 o', substr_count($g2, 'class="tpc-cell') , 18);
tassert('P2 panel chi tiet co mat (khong can hover)', strpos($g2, 'tpc-detail') !== false, true);
tassert('P2 o id1 dang is-lock', strpos($g2, 'tpc-cell is-lock is-sel') !== false, true);
tassert('P2 GET thuan khong co nut ghi nao', strpos($g2, 'name="tp_action"') === false, true);
tassert('P2 co loi dan ve Mo Thien Phu', strpos($g2, 'Mở Thiên Phú') !== false, true);

// ===== P3: Khai Phu (Task 10) — GET thuan, chua co dan =====
$g3 = getpage("cmd=tianfu_open&sid=$E2ESID");
tok('P3 route tianfu_open live', $g3);
tassert('P3 class tpo-wrap', strpos($g3, 'class="tpo-wrap"') !== false, true);
tassert('P3 dong chu thang nguyen van', strpos($g3, 'Trúng sẽ không mất — nó tăng 1 phẩm chất cho chính thiên phú ấy.') !== false, true);
tassert('P3 bang ty le = 10 pham + 1 dong tieu de', substr_count($g3, '</tr>'), 11);
tassert('P3 nut Khai Mo bi khoa khi het dan', strpos($g3, 'class="tpo-act is-off"') !== false && strpos($g3, 'disabled') !== false, true);
tassert('P3 GET khong tra ket qua roll', strpos($g3, 'tpo-result') === false, true);
tassert('P3 hien gia that cua dan (50.000)', strpos($g3, '50.000') !== false, true);
tassert('P3 tabbar nut 3 dang on', strpos($g3, '<a class="on" href="?cmd=') !== false, true);

// ===== P4: GET 2 lan lien tiep KHONG tieu dan (chung minh [CHOT] "chi POST moi roll") =====
$db->exec("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES ('$E2ESID', $E2EUID, 114, 3)
           ON DUPLICATE KEY UPDATE djsum = 3");
\player\player_cache_clear($E2ESID);
$g4a = getpage("cmd=tianfu_open&sid=$E2ESID");
$g4b = getpage("cmd=tianfu_open&sid=$E2ESID");
tok('P4 GET lan 1', $g4a); tok('P4 GET lan 2', $g4b);
tassert('P4 2 lan GET khong lam mat dan', pillCount($db, $E2ESID), 3);
tassert('P4 GET khong sinh ket qua', strpos($g4a, 'tpo-result') === false && strpos($g4b, 'tpo-result') === false, true);
tassert('P4 nut mo khoa khi co dan', strpos($g4b, 'class="tpo-act is-off"') === false, true);
tassert('P4 so lan khai mo van 0', strpos($g4b, 'đã mở <b>0</b>') !== false, true);

// ===== P5: POST tp_action=open — roll that, tru dung 1 dan =====
$p5 = postpage("cmd=tianfu_open&sid=$E2ESID", array('tp_action' => 'open'));
tok('P5 POST open', $p5);
tassert('P5 co panel ket qua', strpos($p5, 'tpo-result-name') !== false, true);
tassert('P5 ghi nhan thien phu moi', strpos($p5, 'Thiên phú MỚI') !== false, true);
tassert('P5 tru dung 1 dan (3 -> 2)', pillCount($db, $E2ESID), 2);
tassert('P5 tao dung 1 dong player_tianfu', ownedCount($db, $E2ESID), 1);
$slotSt = $db->prepare("SELECT slot, quality FROM player_tianfu WHERE sid = ?"); $slotSt->execute(array($E2ESID));
$slotRow = $slotSt->fetch(PDO::FETCH_ASSOC);
tassert('P5 khong tu dat lam chu phu (slot = 0)', intval($slotRow['slot']), 0);
tassert('P5 pham trong khoang 1..10', intval($slotRow['quality']) >= 1 && intval($slotRow['quality']) <= 10, true);
tassert('P5 log kind=open duoc ghi', intval($db->query("SELECT COUNT(*) FROM player_tianfu_log WHERE sid = " . $db->quote($E2ESID) . " AND kind = 'open'")->fetchColumn()), 1);
tassert('P5 dem so lan khai mo tang len 1', strpos($p5, 'đã mở <b>1</b>') !== false, true);

// ===== P6: POST khi het dan — msg bad, khong ghi gi =====
$db->exec("UPDATE playerdaoju SET djsum = 0 WHERE sid = '$E2ESID' AND djid = 114");
\player\player_cache_clear($E2ESID);
$p6 = postpage("cmd=tianfu_open&sid=$E2ESID", array('tp_action' => 'open'));
tok('P6 POST khi het dan', $p6);
tassert('P6 msg bad', strpos($p6, 'class="tp-msg bad"') !== false, true);
tassert('P6 noi dung msg het dan', strpos($p6, 'Không còn Khai Phú Đan trong túi.') !== false, true);
tassert('P6 khong co panel ket qua', strpos($p6, 'tpo-result-name') === false, true);
tassert('P6 khong them dong nao', ownedCount($db, $E2ESID), 1);

// ===== P7: khong co loi PHP lo ra ngoai tren bat ky page nao =====
$gAll = $g1 . $g2 . $g3 . $g4a . $g4b . $p5 . $p6;
tassert('KHONG co loi PHP tren moi page', stripos($gAll, 'Fatal error') === false
    && stripos($gAll, 'Parse error') === false && stripos($gAll, 'Undefined ') === false
    && stripos($gAll, 'Warning</b>:') === false, true);

// ===== CLEANUP — chi xoa phan Thien Phu =====
// KHONG goi tf_cleanup($db, $E2ESID): ham do xoa HET playerdaoju cua sid, tuc la cuon luon
// dan Linh Can 32-37 ma tests/test_linhcan_pages.php dang dung (hai test chung 1 nhan vat e2e).
$db->exec("DELETE FROM player_tianfu WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM player_tianfu_log WHERE sid = '$E2ESID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$E2ESID' AND djid = 114");
$db->exec("UPDATE game1 SET tianfu_id = 0, tianfu_quality = 0, tianfu_lv = 0, tianfu_power = 0
           WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
