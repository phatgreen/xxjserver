<?php
/**
 * test_huyetmach_upgrade.php — nang pham chat Huyết Mach (config + DB local `game`).
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_huyetmach_upgrade.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/huyetmach.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== A: config thuan =====
$all = \player\huyetmach_upgrade_config_all();
tassert('9 buoc', count($all['upgrades']), 9);
$u1 = \player\huyetmach_get_upgrade_info(1);
tassert('info pham 1 to_quality', $u1['to_quality'], 2);
tassert('info pham 1 rate', $u1['success_rate'], 95);
$u9 = \player\huyetmach_get_upgrade_info(9);
tassert('info pham 9 extra id', $u9['extra_material_id'], 108);
tassert('info pham 9 gate', $u9['unlock_realm_min_level'], 280);
tassert('pham 10 toi da', \player\huyetmach_get_upgrade_info(10), false);
tassert('pham 0 khong ton tai', \player\huyetmach_get_upgrade_info(0), false);
// chan pham khong giam dan
$gates = array(); foreach ($all['upgrades'] as $row) { $gates[] = intval($row['unlock_realm_min_level']); }
$sorted = $gates; sort($sorted);
tassert('gate tang don dieu', $gates, $sorted);

// ===== B: DB — duong tuan + tai nguyen =====
$TESTSID = str_repeat('f', 32);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

// row game1 cua TESTSID do Task 3 tao (uid 990112); o day ep ve trang thai biet truoc
$db->prepare("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, uyxb,
        huyetmach_id, huyetmach_quality, huyetmach_awaken_stage)
    VALUES (?, 990112, 'HMTEST', 300, 1000, 10000, NOW(), 0, 1, 1, 0)
    ON DUPLICATE KEY UPDATE uid = 990112, ulv = 300, uyxb = 0,
        huyetmach_id = 1, huyetmach_quality = 1, huyetmach_awaken_stage = 0")
    ->execute(array($TESTSID));
function hm_up_mat($db, $sid, $djid, $sum) {
    $db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid = ?")->execute(array($sid, $djid));
    if ($sum > 0) {
        $db->prepare("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES (?, 990112, ?, ?)")
            ->execute(array($sid, $djid, $sum));
    }
}
function hm_up_reset($db, $sid, $q, $lt, $ulv) {
    $db->prepare("UPDATE game1 SET huyetmach_quality = ?, uyxb = ?, ulv = ? WHERE sid = ?")
        ->execute(array($q, $lt, $ulv, $sid));
    \player\player_cache_clear($sid);
}
$db->prepare("DELETE FROM player_huyetmach_upgrade_log WHERE sid = ?")->execute(array($TESTSID));

// B1: khong co huyet mach
$db->prepare("UPDATE game1 SET huyetmach_id = 0 WHERE sid = ?")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('chua co huyet mach', $r['ok'], false);
tassert('chua co huyet mach msg', strpos($r['msg'], 'chưa có Huyết Mạch') !== false, true);
$db->prepare("UPDATE game1 SET huyetmach_id = 1 WHERE sid = ?")->execute(array($TESTSID));

// B2: chan canh gioi (step 3 can cap 100, dat ulv = 99)
hm_up_reset($db, $TESTSID, 3, 999999999, 99);
hm_up_mat($db, $TESTSID, 112, 50); hm_up_mat($db, $TESTSID, 102, 50);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('thieu canh gioi', $r['ok'], false);
tassert('thieu canh gioi msg', strpos($r['msg'], 'cấp 100') !== false, true);
tassert('thieu canh gioi khong tru LT', intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 999999999);
tassert('thieu canh gioi khong tru 112', intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=112")->fetchColumn()), 50);

// B3: thieu Huyet Thạch — msg phai dung ten co dau (Task 4 Step 4)
hm_up_reset($db, $TESTSID, 1, 999999999, 300);
hm_up_mat($db, $TESTSID, 112, 4); hm_up_mat($db, $TESTSID, 101, 0);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('thieu Huyet Thạch', $r['ok'], false);
tassert('ten vat pham co dau', strpos($r['msg'], 'Huyết Thạch') !== false, true);

// B4: thieu da pham (extra_material_id) — BUOC 2->3 moi co extra (101 x 3), o pham 1 khong co nen phai reset ve 2
hm_up_reset($db, $TESTSID, 2, 999999999, 300);
hm_up_mat($db, $TESTSID, 112, 10); hm_up_mat($db, $TESTSID, 101, 2);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('thieu Huyền Thạch', strpos($r['msg'], 'Huyền Thạch') !== false, true);
tassert('thieu da khong tru 112', intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=112")->fetchColumn()), 10);
tassert('thieu da khong ghi log', intval($db->query("SELECT COUNT(*) FROM player_huyetmach_upgrade_log WHERE sid='$TESTSID'")->fetchColumn()), 0);

// B5: thieu Linh Thạch
hm_up_mat($db, $TESTSID, 101, 3);
hm_up_reset($db, $TESTSID, 1, 999, 300);
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('thieu Linh Thạch', strpos($r['msg'], 'Linh Thạch') !== false, true);

// B6: du du Kien -> duoc tru, duoc ghi log, pham KHONG BAO GIO thap di
hm_up_reset($db, $TESTSID, 1, 999999999, 300);
hm_up_mat($db, $TESTSID, 112, 5);
$t0 = intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn());
$r = \player\huyetmach_upgrade($TESTSID, $db);
tassert('upgrade lan 1 ok', $r['ok'] || strpos($r['msg'], 'thất bại') !== false, true);
tassert('tru dung 1000 LT', $t0 - intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 1000);
tassert('tru het 5 Huyet Thạch', intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=112")->fetchColumn()), 0);
tassert('log 1 dong', intval($db->query("SELECT COUNT(*) FROM player_huyetmach_upgrade_log WHERE sid='$TESTSID'")->fetchColumn()), 1);
$q1 = intval($db->query("SELECT huyetmach_quality FROM game1 WHERE sid='$TESTSID'")->fetchColumn());
tassert('pham chi co the 1 hoac 2', ($q1 === 1 || $q1 === 2), true);

// ===== C: roll THAT — 60 luot pham 1->2 (rate 95) phai nam trong dai 50..60 =====
$ok = 0; $bad = 0;
for ($i = 0; $i < 60; $i++) {
    hm_up_mat($db, $TESTSID, 112, 5);
    $db->prepare("UPDATE game1 SET huyetmach_quality = 1, uyxb = 999999999 WHERE sid = ?")->execute(array($TESTSID));
    \player\player_cache_clear($TESTSID);
    $r = \player\huyetmach_upgrade($TESTSID, $db);
    if (strpos($r['msg'], 'thành công') !== false) { $ok++; } else { $bad++; }
}
tassert('60 luot roll het', $ok + $bad, 60);
tassert('rate 95 trong dai 50..60', ($ok >= 50 && $ok <= 60), true);
tassert('khong bao gio vuot pham 2', intval($db->query("SELECT huyetmach_quality FROM game1 WHERE sid='$TESTSID'")->fetchColumn()) <= 2, true);

// ===== D: dao thu — vet nguyen hien trang, chay 2 lan loi khong duplicated =====
$db->prepare("DELETE FROM player_huyetmach_upgrade_log WHERE sid = ?")->execute(array($TESTSID));
$db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid IN (101,102,108,112)")->execute(array($TESTSID));
$db->prepare("UPDATE game1 SET huyetmach_quality = 1, huyetmach_id = 0, uyxb = 0 WHERE sid = ?")->execute(array($TESTSID));
tassert('sach log', intval($db->query("SELECT COUNT(*) FROM player_huyetmach_upgrade_log WHERE sid='$TESTSID'")->fetchColumn()), 0);

echo "\n" . ($fails === 0 ? "ALL PASS" : $fails . " FAIL") . "\n";
exit($fails === 0 ? 0 : 1);
