<?php
/**
 * test_nguhanh_combat.php — Kiem chon he khac che + dong chu khac che trong tran that.
 * Chay: "...php.exe" tests/test_nguhanh_combat.php   (can tests/e2e_fixture.php chay truoc)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
require_once $ROOT . '/class/function/nap_hanh.php';
require_once $ROOT . '/class/function/zhuangbei.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}

// ===== A: zhuangbei_pick_element thuan tuy =====
$full = array('kim', 'moc', 'thuy', 'hoa', 'tho');
$khac = array('kim' => 'moc', 'moc' => 'tho', 'tho' => 'thuy', 'thuy' => 'hoa', 'hoa' => 'kim');
$reverse = array();
foreach ($khac as $a => $b) { $reverse[$b] = $a; } // $reverse[$monster] = he khac no
foreach ($full as $monster) {
    tassert("pick full vs $monster", \player\zhuangbei_pick_element($full, $monster), $reverse[$monster]);
}
tassert('pick set thieu hanh khac', \player\zhuangbei_pick_element(array('kim', 'hoa'), 'thuy'), '');
tassert('pick set trong', \player\zhuangbei_pick_element(array(), 'thuy'), '');
tassert('pick quai khong hanh', \player\zhuangbei_pick_element($full, 'none'), '');
tassert('pick quai null', \player\zhuangbei_pick_element($full, null), '');
// uu tien theo thu tu kim,moc,thuy,hoa,tho: ca 2 hanh cung khac duoc -> lay hanh dau tien
// (thuc te chi 1 hanh khac duoc 1 quai, nen kiem hanh bi khac cung dung):
tassert('mult khac che +20%', \player\zhuangbei_get_element_multiplier('thuy', 'hoa'), 1.2);
tassert('mult bi khac -15%', \player\zhuangbei_get_element_multiplier('hoa', 'thuy'), 0.85);
tassert('mult trung hanh', \player\zhuangbei_get_element_multiplier('hoa', 'hoa'), 1.0);
tassert('mult khong lien quan', \player\zhuangbei_get_element_multiplier('kim', 'thuy'), 1.0);

// ===== B: nhan vat that (e2e_fixture) — zb_element_set + tran pve =====
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$pl = \player\getplayer($ESID, $db);
if (!is_object($pl) || intval($pl->uid) === 0) {
    echo "FAIL fixture e2e khong ton tai — chay tests/e2e_fixture.php truoc\n";
    exit(1);
}
$set = isset($pl->zb_element_set) ? $pl->zb_element_set : array();
sort($set);
tassert('e2e zb_element_set tu 16 mon dang mac', $set, array('hoa', 'kim', 'moc', 'tho', 'thuy'));
foreach ($reverse as $monster => $want) {
    tassert("e2e pick vs $monster", \player\zhuangbei_pick_element($pl->zb_element_set, $monster), $want);
}

// ===== C: tran that qua HTTP cmd=pvegj =====
// Dy 54 = Thuy U Linh (hanh thuy) -> trang bi 5 he phai chon 'tho' (tho khac thuy).
$mid = intval($pl->nowmid);
$gid = intval($db->query("SELECT id FROM midguaiwu WHERE mid = $mid AND gyid = 54 LIMIT 1")->fetchColumn());
if ($gid === 0) {
    $db->exec("INSERT INTO midguaiwu (gname, ghp, ggj, gfy, glv, mid, gyid, gexp, sid, gmaxhp, gbj, gxx, ms)
        VALUES ('Thuy U Linh', 999999, 1, 1, 48, $mid, 54, 1, '', '999999', 0, 0, 0)");
    $gid = intval($db->lastInsertId());
    $created = true;
} else {
    $db->exec("UPDATE midguaiwu SET ghp = 999999, gmaxhp = '999999', sid = '' WHERE id = $gid");
    $created = false;
}
$db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$ESID'");
\player\player_cache_clear($ESID);

$enc = new \encode\encode();
$url = 'http://localhost/game.php?cmd=' . $enc->encode("cmd=pvegj&gid=$gid&nowmid=$mid&sid=$ESID");
$html = shell_exec('curl -s ' . escapeshellarg($url));
$out = io_write($html, $gid, $mid);
function io_write($html, $gid, $mid) {
    $f = fopen(__DIR__ . '/_combat_detail.txt', 'w');
    fwrite($f, "gid=$gid mid=$mid len=" . strlen($html) . "\n");
    if (preg_match_all('/(kh[^\s<]*\s*(?:kh[a-zà-ỹ]*\s*)?[^<]*)/u', (string)$html, $m)) {
        foreach ($m[1] as $line) { fwrite($f, "KHAC: " . trim(strip_tags($line)) . "\n"); }
    }
    fwrite($f, "HAS_TRANGBI_KHAC=" . (strpos((string)$html, 'Trang bị khắc') !== false ? 'yes' : 'no') . "\n");
    fwrite($f, "HAS_LINHCAN_KHAC=" . (strpos((string)$html, 'Linh căn khắc') !== false ? 'yes' : 'no') . "\n");
    fwrite($f, "HAS_BI_KHAC=" . (strpos((string)$html, 'bị') !== false && strpos((string)$html, 'khắc!') !== false ? 'yes' : 'no') . "\n");
    fclose($f);
    return $html;
}
tassert('HTTP 200 (have html)', strlen($html) > 500, true);
tassert('pve that: hien "Trang bi khac Thuy"', strpos($html, 'Trang bị khắc') !== false, true);
tassert('pve that: khong dung label Linh can', strpos($html, 'Linh căn khắc'), false);
tassert('pve that: thong bao +20%', strpos($html, '20%') !== false, true);
if ($created) { $db->exec("DELETE FROM midguaiwu WHERE id = $gid"); }

// ===== D: BOSS da — bang `boss` khong cot `element` nen khac che chi co hieu luc voi QUAI =====
$boss1 = \player\getboss(1, $db);
tassert('LOI-DA-LIEN quan: boss 1 khong co hanh (bang boss thieu cot element)',
    isset($boss1->element) ? $boss1->element : null, '');
$hasElemCol = $db->query("SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='game' AND TABLE_NAME='boss' AND COLUMN_NAME='element'")->fetchColumn();
tassert('bang boss that su khong co cot element', intval($hasElemCol), 0);
echo ">> Ghi chu: doan khac che trong game/boss.php (dong 241-274) dung duoc la khi"
   . " bang `boss` co cot element + duoc dien gia tri. Can chu du an quyet dinh.\n";

echo "\n" . ($fails === 0 ? "ALL PASS\n" : "$fails FAIL\n");
echo "Chi tiet tran danh: tests/_combat_detail.txt\n";
exit($fails === 0 ? 0 : 1);
