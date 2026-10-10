<?php
/**
 * test_nguhanh_random.php — DO roll ngẫu nhiên THẬT của rèn ngũ hành (không force_roll).
 * So ti le thuc te voi cong thuc 90 - 10n + thuong linh can, va kiem soat nguyen lieu.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh_random.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
require_once $ROOT . '/class/function/nap_hanh.php';

$fails = 0; $rolls = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
function tclose($name, $got, $exp, $tol) {
    global $fails;
    $d = abs($got - $exp);
    if ($d <= $tol) { echo "PASS $name (thuc te=$got, ly thuyet=$exp, sai khac " . round($d, 2) . " <= $tol)\n"; }
    else { $fails++; echo "FAIL $name (thuc te=$got, ly thuyet=$exp, sai khac " . round($d, 2) . " > $tol)\n"; }
}

$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$RSID = 'r4nd0mr4nd0mr4nd0mr4nd0mr4nd0m4d';
$RUID = 987651;

function rnd_cleanup($db, $sid) {
    $db->exec("DELETE FROM playerzhuangbei WHERE sid = '$sid'");
    $db->exec("DELETE FROM playerdaoju WHERE sid = '$sid'");
    $db->exec("DELETE FROM player_chetao_log WHERE sid = '$sid'");
    $db->exec("DELETE FROM game1 WHERE sid = '$sid'");
    $db->exec("DELETE FROM player_linhcan_node WHERE sid = '$sid'");
    \player\player_cache_clear($sid);
    \player\zhuangbei_cache_clear();
}
rnd_cleanup($db, $RSID);

// game1. COT linhcan_id/linhcan_quality O DAY DA LA DU LIEU CHET: tu Task 9,
// linhcan_element lay tu bang player_linhcan_node (hanh chu dao), khong tu config 22 can.
// Ty le nap hanh: base 90 - 10n + thuong theo HANH CHU DAO (thuy 0 / hoa 10), chan tran 95.
$colsNN = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'game' AND TABLE_NAME = 'game1'
          AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
          AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array(); $colVals = array();
foreach ($colsNN as $c) {
    $n = $c['COLUMN_NAME'];
    $colNames[] = $n;
    if ($n === 'sid')     { $colVals[] = $db->quote($RSID); continue; }
    if ($n === 'token')   { $colVals[] = $db->quote('rnd'); continue; }
    if ($n === 'endtime') { $colVals[] = $db->quote(date('Y-m-d H:i:s', time() + 86400)); continue; }
    $isStr = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext'));
    $colVals[] = $isStr ? "''" : "0";
}
$gc = \player\getgameconfig($db);
foreach (array('uid' => $RUID, 'uname' => $db->quote('RND NGU HANH'), 'ulv' => 1000,
               'uyxb' => 2000000000, 'nowmid' => intval($gc->firstmid), 'sfzx' => 1,
               'linhcan_id' => 3, 'linhcan_quality' => 5) as $k => $v) {
    if (!in_array($k, $colNames, true)) { $colNames[] = $k; $colVals[] = $v; }
}
$db->exec("INSERT INTO game1 (" . implode(', ', $colNames) . ") VALUES (" . implode(', ', $colVals) . ")");

// 2 mon hang 1 (rarity 1) de A/B khong dung nhau
$cfg = json_decode(file_get_contents($ROOT . '/class/config/zhuangbei_config.json'), true);
$zbid1 = 16; // Pham Phi Sat Phong, rarity 1
$insZb = $db->prepare("INSERT INTO playerzhuangbei (zbid, uid, sid, qianghua, zblv, zbtool,
    zbname, zbinfo, zbgj, zbfy, zbhp, zbbj, zbxx, zbys, taozhuang, element)
    VALUES (?, ?, ?, 0, 0, ?, ?, ?, 0, 0, 0, 0, 0, ?, 1, 'none')");
$zbIds = array();
for ($i = 0; $i < 2; $i++) {
    $it = $cfg[$zbid1];
    $insZb->execute(array($zbid1, $RUID, $RSID, intval($it['zbtool']), $it['zbname'], $it['zbinfo'], $it['zbys']));
    $zbIds[] = intval($db->lastInsertId());
}
$insDj = $db->prepare("INSERT INTO playerdaoju (djid, djsum, sid, uid, djname, djzl, djinfo)
    VALUES (?, 200000, ?, ?, 'Tinh Thach', 1, 'rnd')");
foreach (array(32, 33, 34, 35, 36) as $djid) { $insDj->execute(array($djid, $RSID, $RUID)); }
\player\player_cache_clear($RSID);

// Nguon moi cua linhcan_element = node (hanh chu dao). thuy thuong 0 -> rate 90.
$db->exec("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
    VALUES ('$RSID', 'thuy', 10, 0, NOW()) ON DUPLICATE KEY UPDATE level = 10, stage = 0");
\player\player_cache_clear($RSID);
$pl = \player\getplayer($RSID, $db);
tassert('fixture linhcan_element', $pl->linhcan_element, 'thuy');

/**
 * Chay $n lan that, moi lan reset element ve $pre de rate va chi phi khong doi.
 * Tra ve mang dem thanh/bai + kiem tra so du.
 */
function run_rolls($db, $RSID, $zbnowid, $he, $pre, $n, $label) {
    $db->exec("UPDATE playerzhuangbei SET element = " . $db->quote($pre) . " WHERE zbnowid = " . intval($zbnowid));
    \player\zhuangbei_cache_clear($zbnowid);
    $stoneDjid = \player\nap_hanh_cost($pre, 1, $he);
    $stDjid = $stoneDjid['stone_djid'];
    $sumStone = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$RSID' AND djid=$stDjid")->fetchColumn());
    $sumLt = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$RSID'")->fetchColumn());
    $logBefore = intval($db->query("SELECT COUNT(*) FROM player_chetao_log WHERE sid='$RSID' AND log_type='nap_hanh'")->fetchColumn());
    $ok = 0; $bad = 0; $rateSeen = array(); $matErr = 0; $elemErr = 0;
    for ($i = 0; $i < $n; $i++) {
        $r = \player\nap_hanh_do($RSID, $zbnowid, $he, $db);
        if (!isset($r['rate']) || $r['rate'] <= 0) { $matErr++; continue; }
        $rateSeen[$r['rate']] = true;
        $after = $db->query("SELECT element FROM playerzhuangbei WHERE zbnowid = " . intval($zbnowid))->fetchColumn();
        if ($r['ok']) {
            $ok++;
            $exp = \player\zhuangbei_elements_join(array_merge(\player\zhuangbei_elements_parse($pre), array($he)));
            if ($after !== $exp) { $elemErr++; }
        } else {
            $bad++;
            if ($after !== $pre) { $elemErr++; } // that bai khong duoc doi element
        }
        $db->exec("UPDATE playerzhuangbei SET element = " . $db->quote($pre) . " WHERE zbnowid = " . intval($zbnowid));
        \player\zhuangbei_cache_clear($zbnowid);
    }
    $sumStoneAfter = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$RSID' AND djid=$stDjid")->fetchColumn());
    $sumLtAfter = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$RSID'")->fetchColumn());
    $logAfter = intval($db->query("SELECT COUNT(*) FROM player_chetao_log WHERE sid='$RSID' AND log_type='nap_hanh'")->fetchColumn());
    echo "== $label: ok=$ok bad=$bad n=$n\n";
    return array('ok' => $ok, 'bad' => $bad, 'n' => $n, 'matErr' => $matErr, 'elemErr' => $elemErr,
        'stoneUsed' => $sumStone - $sumStoneAfter, 'ltUsed' => $sumLt - $sumLtAfter,
        'logs' => $logAfter - $logBefore, 'rates' => array_keys($rateSeen),
        'unitStone' => $stoneDjid['stone_qty'], 'unitLt' => $stoneDjid['linhthach']);
}

// ===== A: n=0, linh can thuy (+0) -> rate 90 =====
$A = run_rolls($db, $RSID, $zbIds[0], 'thuy', 'none', 1000, 'A rate 90');
tassert('A rate duy nhat = 90', $A['rates'], array(90));
tassert('A khong co loi nguyen lieu', $A['matErr'], 0);
tassert('A element khong bao gio sai', $A['elemErr'], 0);
tassert('A tong roll', $A['ok'] + $A['bad'], 1000);
tclose('A ti le that ~ 90%', round($A['ok'] / 10, 1), 90, 4);
tassert('A da tieu dung = so lan x don vi da', $A['stoneUsed'], $A['n'] * $A['unitStone']);
tassert('A linh thach tieu dung = so lan x don vi', $A['ltUsed'], $A['n'] * $A['unitLt']);
tassert('A log = so lan', $A['logs'], $A['n']);

// ===== B: n=4 -> rate 50, phai co CA thanh va bai (chung to roll ngau nhien that) =====
$B = run_rolls($db, $RSID, $zbIds[0], 'tho', 'kim,moc,thuy,hoa', 1000, 'B rate 50');
tassert('B rate duy nhat = 50', $B['rates'], array(50));
tassert('B co ca thanh lan bai (random that)', ($B['ok'] > 0 && $B['bad'] > 0), true);
tassert('B element khong bao gio sai', $B['elemErr'], 0);
tclose('B ti le that ~ 50%', round($B['ok'] / 10, 1), 50, 6);
tassert('B chi phi tang theo n=4 (1+n = 5 vien da)', $B['unitStone'], 5);
tassert('B linh thach = 500*1*(1+4)', $B['unitLt'], 2500);

// ===== C: chu dao 'hoa' (+10, bang dung moi 'all' cu) -> tran 95 =====
// 'all' tung den tu căn id 21 (config ngau nhien) — he moi khong con duong nguoc ve 'all'.
// Van giu y nghia test chan tran: 90 + 10 = 100 -> min(cap 95).
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '$RSID'");
$db->exec("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
    VALUES ('$RSID', 'hoa', 20, 0, NOW()) ON DUPLICATE KEY UPDATE level = 20, stage = 0");
\player\player_cache_clear($RSID);
$plC = \player\getplayer($RSID, $db);
tassert('C linhcan_element = hoa', $plC->linhcan_element, 'hoa');
$C = run_rolls($db, $RSID, $zbIds[1], 'kim', 'none', 600, 'C rate 95 (tran)');
tassert('C rate = 95 (da chan tran)', $C['rates'], array(95));
tclose('C ti le that ~ 95%', round($C['ok'] / 6, 1), 95, 5);

// ===== D: 5 he -> chan block, khong mat nguyen lieu =====
$db->exec("UPDATE playerzhuangbei SET element = 'kim,moc,thuy,hoa,tho' WHERE zbnowid = " . intval($zbIds[1]));
\player\zhuangbei_cache_clear($zbIds[1]);
$lt0 = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$RSID'")->fetchColumn());
$st0 = intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$RSID' AND djid=35")->fetchColumn());
$rD = \player\nap_hanh_do($RSID, $zbIds[1], 'kim', $db);
tassert('D block 5 he', $rD['ok'], false);
// Du 5 he = da co mat 5 hanh hop le, nen kiem tra "trung he" luon bat truoc.
// Nhanh 'Trang bi da du 5 he ngu hanh.' trong nap_hanh_do la nhanh khong the toi.
tassert('D msg chan khi du 5 he', $rD['msg'], 'Món đã mang hệ Kim, không thể kích hoạt trùng.');
$rD2 = \player\nap_hanh_do($RSID, $zbIds[1], 'tho', $db);
tassert('D chan voi moi he khac', $rD2['msg'], 'Món đã mang hệ Thổ, không thể kích hoạt trùng.');
tassert('D khong mat linh thach', intval($db->query("SELECT uyxb FROM game1 WHERE sid='$RSID'")->fetchColumn()), $lt0);
tassert('D khong mat da', intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$RSID' AND djid=35")->fetchColumn()), $st0);

rnd_cleanup($db, $RSID);
echo "\n" . ($fails === 0 ? "ALL PASS" : "$fails FAIL") . "  (rolls that: " . (1000 + 1000 + 600) . ")\n";
exit($fails === 0 ? 0 : 1);
