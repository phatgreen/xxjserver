<?php
/**
 * e2e_fixture.php — Dựng nhân vật test cho route game.php thật (CLI, PHP 5.6).
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/e2e_fixture.php
 * In ra danh sách URL đã mã hoá để headless Chrome mở bằng route thật.
 * Không đụng nhân vật thật: sid giả $E2ESID, uid giả $E2EUID.
 * Dọn sạch nhân vật test sau khi xong:  tests/e2e_fixture.php --clean
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
require_once $ROOT . '/class/function/nap_hanh.php';

$E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$E2EUID = 987650;

$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$enc = new \encode\encode();

function e2e_cleanup($db, $sid) {
    $db->exec("DELETE FROM playerzhuangbei WHERE sid = '$sid'");
    $db->exec("DELETE FROM playerdaoju WHERE sid = '$sid'");
    $db->exec("DELETE FROM player_chetao_log WHERE sid = '$sid'");
    // KHONG xoa fangshi_zb: cot sid o day van la INT (see memory sid-schema-debt),
    // so sanh 'hex' voi int se so voi 0 va won luong treo ban that.
    $db->exec("DELETE FROM game1 WHERE sid = '$sid'");
    \player\player_cache_clear($sid);
    \player\zhuangbei_cache_clear();
}
// --clean: do nhan vat test (ban + nguoi mua cua test_nguhanh_market) roi thoat, khong dung moi.
if (in_array('--clean', $argv)) {
    e2e_cleanup($db, $E2ESID);
    e2e_cleanup($db, 'b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0');
    echo "DA DON sach nhan vat test.\n";
    exit(0);
}
e2e_cleanup($db, $E2ESID);

// ===== game1: tu sinh INSERT du cot NOT NULL khong default =====
$colsNN = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'game' AND TABLE_NAME = 'game1'
          AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
          AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array(); $colVals = array();
foreach ($colsNN as $c) {
    $n = $c['COLUMN_NAME'];
    $colNames[] = $n;
    if ($n === 'sid')     { $colVals[] = $db->quote($E2ESID); continue; }
    if ($n === 'token')   { $colVals[] = $db->quote('e2etest'); continue; }
    if ($n === 'endtime') { $colVals[] = $db->quote(date('Y-m-d H:i:s', time() + 86400)); continue; }
    $isStr = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext'));
    $colVals[] = $isStr ? "''" : "0";
}
$gc = \player\getgameconfig($db);
$firstmid = intval($gc->firstmid);
$extras = array(
    'uid' => $E2EUID, 'uname' => $db->quote('E2E Test'), 'ulv' => 1000,
    'uyxb' => 5000000, 'uczb' => 500000, 'usex' => 1, 'vip' => 10,
    'nowmid' => $firstmid, 'sfzx' => 1, 'shenfen' => 1,
    'linhcan_id' => 1, 'linhcan_quality' => 5,
);
foreach ($extras as $k => $v) {
    if (!in_array($k, $colNames, true)) { $colNames[] = $k; $colVals[] = $v; }
}
$db->exec("INSERT INTO game1 (" . implode(', ', $colNames) . ") VALUES (" . implode(', ', $colVals) . ")");

// ===== Trang bi: moi o 1 vien + 10 mon Phi Phong trong tui =====
$cfg = json_decode(file_get_contents($ROOT . '/class/config/zhuangbei_config.json'), true);
$byTool = array();
foreach ($cfg as $zbid => $item) {
    $t = intval($item['zbtool']);
    if (!isset($byTool[$t])) $byTool[$t] = array();
    $byTool[$t][] = $zbid;
}
// Mon Phi Phong (zbid 16-20, 91-95) = ca 10 bac rarity, de thay anh ao choàng
$phiPhong = array(16, 17, 18, 19, 20, 91, 92, 93, 94, 95);
$elements = array('none', 'none', 'kim', 'thuy', 'kim,thuy', 'hoa', 'moc',
                  'kim,moc,thuy', 'kim,moc,thuy,hoa', 'kim,moc,thuy,hoa,tho');

$insZb = $db->prepare("INSERT INTO playerzhuangbei (zbid, uid, sid, qianghua, zblv, zbtool,
    zbname, zbinfo, zbgj, zbfy, zbhp, zbbj, zbxx, zbys, taozhuang, element)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$ids = array();       // zbid => zbnowid
$equipped = array();  // tool => zbnowid
function ins_one($zbid, $item, $db, $insZb, $E2EUID, $E2ESID, $element, $qh) {
    $insZb->execute(array($zbid, $E2EUID, $E2ESID, $qh, intval($item['zblv']), intval($item['zbtool']),
        $item['zbname'], $item['zbinfo'], intval($item['zbgj']), intval($item['zbfy']),
        intval($item['zbhp']), intval($item['zbbj']), intval($item['zbxx']),
        $item['zbys'], intval($item['taozhuang']), $element));
    return intval($db->lastInsertId());
}
foreach ($phiPhong as $i => $zbid) {
    $ids[$zbid] = ins_one($zbid, $cfg[$zbid], $db, $insZb, $E2EUID, $E2ESID, $elements[$i], 0);
}
$equipped[8] = $ids[95]; // Dao Phi Phong (rarity 10, 5 he) dang mac
foreach ($byTool as $tool => $zbids) {
    if ($tool === 8) continue;
    // lay mon bac cao nhat cua o (rarity lon nhat)
    $best = $zbids[0]; $bestR = -1;
    foreach ($zbids as $z) { if (intval($cfg[$z]['rarity']) > $bestR) { $bestR = intval($cfg[$z]['rarity']); $best = $z; } }
    $ids[$best] = ins_one($best, $cfg[$best], $db, $insZb, $E2EUID, $E2ESID, 'none', 0);
    $equipped[$tool] = $ids[$best];
}
// O 7 (Yeu Dai) va 12: them mon hop hanh de test khac che
$sets = array(1 => 'kim', 4 => 'tho', 5 => 'moc');

foreach ($sets as $tool => $el) {
    if (isset($equipped[$tool])) {
        $db->exec("UPDATE playerzhuangbei SET element = " . $db->quote($el) . " WHERE zbnowid = " . intval($equipped[$tool]));
    }
}
// Vat lieu: 5 loai Tinh Thach + Linh Thach du dung
$insDj = $db->prepare("INSERT INTO playerdaoju (djid, djsum, sid, uid, djname, djzl, djinfo)
    VALUES (?, ?, ?, ?, ?, 1, 'e2e')");
$stoneNames = array('hoa' => 32, 'thuy' => 33, 'moc' => 34, 'kim' => 35, 'tho' => 36);
foreach ($stoneNames as $ten => $djid) { $insDj->execute(array($djid, 60, $E2ESID, $E2EUID, ucfirst($ten) . ' Tinh Thach')); }

// Cap nhat o dang mac
$upd = array();
for ($t = 1; $t <= 16; $t++) {
    $v = isset($equipped[$t]) ? intval($equipped[$t]) : 0;
    $upd[] = "tool$t = $v";
}
$db->exec("UPDATE game1 SET " . implode(', ', $upd) . " WHERE sid = '$E2ESID'");
\player\player_cache_clear($E2ESID);
\player\zhuangbei_cache_clear();

// ===== In URL =====
function u($enc, $inner) { return 'http://localhost/game.php?cmd=' . $enc->encode($inner); }
echo "SID=$E2ESID UID=$E2EUID firstmid=$firstmid\n";
echo "EQUIPPED: zb95=" . $ids[95] . " zb16=" . $ids[16] . "\n";
$map = array();
foreach ($phiPhong as $zbid) { $map[] = "$zbid:" . $ids[$zbid]; }
echo "PHIPHONG_IDS " . implode(' ', $map) . "\n\n";
$urls = array(
    'dai_sanh'   => "cmd=gomid&newmid=$firstmid&sid=$E2ESID",
    'trangbi'    => "cmd=trangbi&sid=$E2ESID",
    'zhuangtai'  => "cmd=zhuangtai&sid=$E2ESID",
    'bagzb'      => "cmd=getbagzb&sid=$E2ESID",
    'chakanzb_95' => "cmd=chakanzb&zbnowid=" . $ids[95] . "&sid=$E2ESID",
    'chakanzb_16' => "cmd=chakanzb&zbnowid=" . $ids[16] . "&sid=$E2ESID",
    'chakanzb_93' => "cmd=chakanzb&zbnowid=" . $ids[93] . "&sid=$E2ESID",
    'fangshi'    => "cmd=fangshi&sid=$E2ESID",
    'shangdian'  => "cmd=shangdian&sid=$E2ESID",
);
foreach ($urls as $name => $inner) {
    echo str_pad($name, 14) . ' ' . u($enc, $inner) . "\n";
}
