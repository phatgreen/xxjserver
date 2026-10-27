<?php
/**
 * test_nguhanh_market.php — Treo ban / mua lai / thu hoi trang bi co he ngu hanh o Phuong Thi.
 * Chay: "...php.exe" tests/test_nguhanh_market.php   (can tests/e2e_fixture.php chay truoc)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
require_once $ROOT . '/class/function/nap_hanh.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$enc = new \encode\encode();
$SS = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';   // nguoi ban (e2e_fixture)
$BS = 'b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0';   // nguoi mua
$BUID = 987652;

function get_http($url) { return (string)shell_exec('curl -s ' . escapeshellarg($url)); }
function url_of($enc, $inner) { return 'http://localhost/game.php?cmd=' . $enc->encode($inner); }

// ===== 0: nhan vat mua + kiem tra fixture nguoi ban =====
$db->exec("DELETE FROM game1 WHERE sid = '$BS'");
$db->exec("DELETE FROM playerzhuangbei WHERE sid = '$BS'");
$colsNN = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'game' AND TABLE_NAME = 'game1'
          AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
          AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array(); $colVals = array();
foreach ($colsNN as $c) {
    $n = $c['COLUMN_NAME']; $colNames[] = $n;
    if ($n === 'sid')     { $colVals[] = $db->quote($BS); continue; }
    if ($n === 'token')   { $colVals[] = $db->quote('buyer'); continue; }
    if ($n === 'endtime') { $colVals[] = $db->quote(date('Y-m-d H:i:s', time() + 86400)); continue; }
    $isStr = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext'));
    $colVals[] = $isStr ? "''" : "0";
}
$gc = \player\getgameconfig($db);
foreach (array('uid' => $BUID, 'uname' => $db->quote('E2E Buyer'), 'ulv' => 1000,
               'uyxb' => 100000, 'nowmid' => intval($gc->firstmid), 'sfzx' => 1) as $k => $v) {
    if (!in_array($k, $colNames, true)) { $colNames[] = $k; $colVals[] = $v; }
}
$db->exec("INSERT INTO game1 (" . implode(', ', $colNames) . ") VALUES (" . implode(', ', $colVals) . ")");

$item = $db->query("SELECT zbnowid, element, zbid FROM playerzhuangbei
        WHERE sid = '$SS' AND element = 'kim,thuy' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$item) { echo "FAIL khong tim thay mon 'kim,thuy' cua nguoi ban — chay e2e_fixture.php\n"; exit(1); }
$ZB = intval($item['zbnowid']);
$ltSeller0 = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$SS'")->fetchColumn());
$ltBuyer0  = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$BS'")->fetchColumn());

// ===== 1: treo ban =====
$html = get_http(url_of($enc, "cmd=chakanzb&canshu=chushou&pay=1000&zbnowid=$ZB&sid=$SS"));
tassert(' treo ban: khong co loi PDO', (bool)preg_match('/PDOException|Fatal error/i', $html), false);
$row = $db->query("SELECT uid, sid, element FROM playerzhuangbei WHERE zbnowid = $ZB")->fetch(PDO::FETCH_ASSOC);
tassert('mon bi detach (uid=0)', intval($row['uid']), 0);
tassert('mon bi detach (sid="")', $row['sid'], '');
tassert('element van nam tren mon sau khi detach', $row['element'], 'kim,thuy');
$fs = $db->query("SELECT payid, element, zbid, uid FROM fangshi_zb WHERE zbnowid = $ZB")->fetch(PDO::FETCH_ASSOC);
tassert('fangshi_zb.element = kim,thuy', $fs['element'], 'kim,thuy');
$PAYID = intval($fs['payid']);

// ===== 2: trang Phuong Thi (nguoi mua xem) =====
$mk = get_http(url_of($enc, "cmd=fangshi&fangshi=zhuangbei&sid=$BS"));
tassert('phuong thi: hien ten mon Dao/Tien Phi Phong', (bool)preg_match('/Phi Phong/u', $mk), true);
tassert('phuong thi: co nut Mua', (bool)preg_match('/Mua/u', $mk), true);
$hasTipElem = (strpos($mk, 'Kim') !== false && strpos($mk, 'Th') !== false);
tassert('phuong thi: tooltip/ten hanh co Kim + Thuy', $hasTipElem, true);

// ===== 3: nguoi mua mua =====
$buy = get_http(url_of($enc, "cmd=fangshi&fangshi=zhuangbei&canshu=buy&payid=$PAYID&sid=$BS"));
tassert('mua: khong co loi PDO', (bool)preg_match('/PDOException|Fatal error/i', $buy), false);
tassert('mua: giao dich thanh cong', (bool)preg_match('/Giao dịch thành công/u', $buy), true);
$after = $db->query("SELECT uid, sid, element FROM playerzhuangbei WHERE zbnowid = $ZB")->fetch(PDO::FETCH_ASSOC);
tassert('mua: chu moi = buyer uid', intval($after['uid']), $BUID);
tassert('mua: chu moi = buyer sid', $after['sid'], $BS);
tassert('mua: ELEMENT KHONG MAT (kim,thuy)', $after['element'], 'kim,thuy');
tassert('mua: tin treo bi xoa', intval($db->query("SELECT COUNT(*) FROM fangshi_zb WHERE payid = $PAYID")->fetchColumn()), 0);
$ltSeller1 = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$SS'")->fetchColumn());
$ltBuyer1  = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$BS'")->fetchColumn());
tassert('mua: nguoi mua mat 1000', $ltBuyer0 - $ltBuyer1, 1000);
tassert('mua: nguoi ban nhan 900 (thu 10%)', $ltSeller1 - $ltSeller0, 900);

// ===== 4: thu hoi (retract) — dung mon 'kim,moc,thuy' cua NGUOI BAN =====
$item2 = $db->query("SELECT zbnowid FROM playerzhuangbei WHERE sid = '$SS' AND element = 'kim,moc,thuy' LIMIT 1")
            ->fetch(PDO::FETCH_ASSOC);
if ($item2) {
    $ZB2 = intval($item2['zbnowid']);
    get_http(url_of($enc, "cmd=chakanzb&canshu=chushou&pay=2000&zbnowid=$ZB2&sid=$SS"));
    $fs2 = $db->query("SELECT payid, element FROM fangshi_zb WHERE zbnowid = $ZB2")->fetch(PDO::FETCH_ASSOC);
    tassert('thu hoi: treo duoc, element = kim,moc,thuy', $fs2['element'], 'kim,moc,thuy');
    get_http(url_of($enc, "cmd=fangshi&fangshi=zhuangbei&canshu=retract&payid=" . intval($fs2['payid']) . "&sid=$SS"));
    $back = $db->query("SELECT uid, sid, element FROM playerzhuangbei WHERE zbnowid = $ZB2")->fetch(PDO::FETCH_ASSOC);
    tassert('thu hoi: ve lai tay chu', intval($back['uid']), 987650);
    tassert('thu hoi: element khong doi', $back['element'], 'kim,moc,thuy');
    tassert('thu hoi: tin treo bi xoa', intval($db->query("SELECT COUNT(*) FROM fangshi_zb WHERE payid = " . intval($fs2['payid']))->fetchColumn()), 0);
} else {
    echo "FAIL thu hoi: khong tim thay mon 'kim,moc,thuy' cua nguoi ban\n"; $fails++;
}

// ===== 5: mua xong van ren tiep duoc (element theo mon, khong phai theo chu) =====
$zdB = \player\getzb($ZB, $db);
tassert('buyer doc duoc mon (object)', is_object($zdB), true);
tassert('buyer thay element = kim,thuy', $zdB->element, 'kim,thuy');
$cost = \player\nap_hanh_cost($zdB->element, intval($zdB->rarity), 'hoa');
tassert('chi phi lan 5 tinh theo 2 hanh (n=2)', $cost['n'], 2);
tassert('chi phi da = 1+n', $cost['stone_qty'], 3);

// don dep: tra mon ve cho nguoi ban cu de e2e_fixture lan sau chay duoc nhu cũ
$db->exec("UPDATE playerzhuangbei SET sid = '$SS', uid = 987650 WHERE zbnowid = $ZB");
$db->exec("DELETE FROM game1 WHERE sid = '$BS'");
$db->exec("DELETE FROM playerzhuangbei WHERE sid = '$BS'");
$db->exec("DELETE FROM fangshi_zb WHERE zbnowid IN ($ZB, " . (isset($ZB2) ? $ZB2 : 0) . ")");
\player\player_cache_clear($SS);

echo "\n" . ($fails === 0 ? "ALL PASS\n" : "$fails FAIL\n");
exit($fails === 0 ? 0 : 1);
