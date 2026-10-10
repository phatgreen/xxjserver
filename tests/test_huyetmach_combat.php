<?php
/**
 * test_huyetmach_combat.php — 4 chi so mach am trong cham dot (Task 11).
 * Mac dinh (A-D):   "...php.exe" tests/test_huyetmach_combat.php
 * Route that (+E):  "...php.exe" tests/test_huyetmach_combat.php --live
 * Do nguong pen (F): "...php.exe" tests/test_huyetmach_combat.php --measure
 * --live can server dev dang chay (mac dinh http://127.0.0.1:8123, override bang HM_BASE) va fixture e2e da co trong DB.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
require_once $ROOT . '/class/function/huyetmach.php';
require_once $ROOT . '/class/function/huyetmach_boss.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
function tclose($name, $got, $exp, $tol) {
    global $fails;
    $d = abs($got - $exp);
    if ($d <= $tol) { echo "PASS $name (thuc te=$got, ly thuyet=$exp, lech " . round($d, 3) . " <= $tol)\n"; }
    else { $fails++; echo "FAIL $name (thuc te=$got, ly thuyet=$exp, lech " . round($d, 3) . " > $tol)\n"; }
}
$LIVE = in_array('--live', $argv);
$MEASURE = in_array('--measure', $argv);
$ZERO = array('dodge' => 0.0, 'pen' => 0.0, 'reduce' => 0.0, 'luck' => 0.0);

// ===== A: 5 helper thuan tuy, khong DB =====
tassert('vessel rong = 4 key 0.0', \player\huyetmach_combat_stats(array()), $ZERO);
tassert('vessel null an toan', \player\huyetmach_combat_stats(null), $ZERO);
$cs = \player\huyetmach_combat_stats(array('dodge' => 12, 'pen' => 99, 'reduce' => -5, 'luck' => 7.5));
tassert('dodge giu 12', $cs['dodge'], 12.0);
tassert('pen chan 15', $cs['pen'], 15.0);
tassert('reduce am ve 0', $cs['reduce'], 0.0);
tassert('luck giu 7.5', $cs['luck'], 7.5);

tassert('pierce 0 khong xuyen', \player\huyetmach_combat_pierce_def(0, 1000), 1000);
tassert('pierce 15 tren 1000', \player\huyetmach_combat_pierce_def(15, 1000), 850);
tassert('pierce 15 tren 470 (def boss lv30)', \player\huyetmach_combat_pierce_def(15, 470), 400);
tassert('pierce 150 van chan 15', \player\huyetmach_combat_pierce_def(150, 1000), 850);
tassert('pierce def 0', \player\huyetmach_combat_pierce_def(15, 0), 0);

tassert('reduce 0 giu don', \player\huyetmach_combat_phys_reduce(0, 100), 100);
tassert('reduce 15 tren 100', \player\huyetmach_combat_phys_reduce(15, 100), 85);
tassert('reduce don 0 van 0 (khong ep max(1))', \player\huyetmach_combat_phys_reduce(15, 0), 0);
tassert('reduce 150 chan 15 tren 1000', \player\huyetmach_combat_phys_reduce(150, 1000), 850);

tassert('luck 0 khong doi nguong', \player\huyetmach_combat_luck_gate(0, 21), 21);
tassert('luck 15 tren 21 (median dljv)', \player\huyetmach_combat_luck_gate(15, 21), 24);
tassert('luck 15 tren 6 (median djjv)', \player\huyetmach_combat_luck_gate(15, 6), 7);
tassert('luck nguong 0 van 0', \player\huyetmach_combat_luck_gate(15, 0), 0);

// ===== B: wrapper Task 8 giu han vi cu =====
$vb = array('dodge' => 0, 'reduce' => 0);
tassert('wrapper: khong mach thi khong ne', \player\huyetmach_boss_vessel_dodge($vb), false);
tassert('wrapper: reduce 0 giu nguyen don', \player\huyetmach_boss_vessel_reduce($vb, 100), 100);
$vb2 = array('dodge' => 100, 'reduce' => 100);
tassert('wrapper: reduce chan 15', \player\huyetmach_boss_vessel_reduce($vb2, 100), 85);

// ===== C: phan bo roll THAT (khong force-roll, theo tests/test_nguhanh_random.php) =====
$N = 30000; $dodges = 0;
for ($i = 0; $i < $N; $i++) { if (\player\huyetmach_combat_roll_dodge(12)) $dodges++; }
tclose('dodge 12% khop ly thuyet (N=30000)', round($dodges / $N * 100, 2), 12.0, 0.8);
$zeroHit = 0;
for ($i = 0; $i < 3000; $i++) { if (\player\huyetmach_combat_roll_dodge(0)) $zeroHit++; }
tassert('pct 0 khong bao gio ne', $zeroHit, 0);
$capHit = 0; $NC = 10000;
for ($i = 0; $i < $NC; $i++) { if (\player\huyetmach_combat_roll_dodge(99)) $capHit++; }
tclose('pct 99 bi chan xuong 15% (N=10000)', round($capHit / $NC * 100, 2), 15.0, 1.5);

// ===== D: noi vao nhan vat that (fixture e2e) =====
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
$pl = \player\getplayer($E2ESID, $db);
if (!is_object($pl)) {
    $fails++;
    echo "FAIL fixture e2e khong ton tai — chay tests/e2e_fixture.php truoc\n";
} else {
    tassert('getplayer gan huyetmach_vessel', isset($pl->huyetmach_vessel) && is_array($pl->huyetmach_vessel), true);
    $need = array('atk', 'hp', 'crit', 'lifesteal', 'dodge', 'pen', 'reduce', 'luck');
    tassert('vessel du 8 chi so', count(array_intersect($need, array_keys($pl->huyetmach_vessel))), 8);
    $opp = \player\getplayer1($pl->uid, $db);
    tassert('doi thu PvP co vessel (getplayer1 -> getplayer)', is_object($opp) && isset($opp->huyetmach_vessel), true);
}

// ===== E: ROUTE THAT qua HTTP (chi chay khi co --live) =====
if ($LIVE) {
    $enc = new \encode\encode();
    // Server dev local = 127.0.0.1:8123 (php -S). Qua Apache port 80 thi dat HM_BASE=http://localhost
    $hmBase = getenv('HM_BASE');
    if (!is_string($hmBase) || $hmBase === '') { $hmBase = 'http://127.0.0.1:8123'; }
    $hmGet = function ($plain) use ($enc, $hmBase) {
        return (string) shell_exec('curl -s ' . escapeshellarg($hmBase . '/game.php?cmd=' . $enc->encode($plain)));
    };

    // E.1 — snapshot + seed 8 mach lv48, stage 4 (tran 48) cho fixture
    $snap = $db->query("SELECT ulv,ugj,ufy,ubj,uxx,uhp,umaxhp,ustamina,huyetmach_id,huyetmach_quality,huyetmach_awaken_stage FROM game1 WHERE sid = '$E2ESID'")->fetch(PDO::FETCH_ASSOC);
    // e2e_fixture.php KHONG seed mach huyet -> luop 8 dong vessel de restore o E.5
    $vesSnap = $db->query("SELECT vessel_id, level FROM player_huyetmach_vessel WHERE sid = '$E2ESID'")->fetchAll(PDO::FETCH_ASSOC);
    if (!is_array($snap)) {
        $fails++;
        echo "FAIL E: fixture e2e khong co trong game1 — chay tests/e2e_fixture.php truoc\n";
    } else {
        for ($v = 1; $v <= 8; $v++) {
            $db->prepare("INSERT INTO player_huyetmach_vessel (sid, vessel_id, level, updated_at)
                VALUES (?, ?, 48, NOW()) ON DUPLICATE KEY UPDATE level = 48")->execute(array($E2ESID, $v));
        }
        $db->exec("UPDATE game1 SET huyetmach_awaken_stage = 4 WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);

        $vb = \player\huyetmach_vessel_bonuses($E2ESID, $db);
        tassert('E seed: tong cap 8x48', $vb['total_lv'], 384);
        tassert('E seed: stage 4 -> tran 48', $vb['cap'], 48);
        tassert('E seed: lv48 dodge = 12', $vb['dodge'], 12.0);
        tassert('E seed: lv48 pen = 12', $vb['pen'], 12.0);
        tassert('E seed: lv48 reduce = 12', $vb['reduce'], 12.0);
        tassert('E seed: lv48 luck = 12', $vb['luck'], 12.0);
        $pl2 = \player\getplayer($E2ESID, $db);
        tassert('E noi qua getplayer: vessel = 12', $pl2->huyetmach_vessel['pen'], 12.0);

        // E.2 — quai lien + boss tam thoi. ggj du lon de $phurt > 0 ma KHONG phai nho san 15%:
        // $phurt = ggj - ufy*0.75, lay ufy DA TRU khi cong trang bi cua chinh trang do.
        $mid = intval($pl2->nowmid);
        $hmGj = intval(round(floatval($pl2->ufy) * 0.75)) + 300;
        // LUON INSERT dong rieng roi XOA o E.5 — KHONG tai su dung dong co cua `mid`
        // (midguaiwu la bang cua chung: doi phuong khac dang dan di qua).
        // HP seed = 2 ty (duoi INT_MAX). Do thuc te: 7848 sat thuong/don PVE, 3747/don BOSS nen 60+20 don khong diet noi.
        $db->exec("INSERT INTO midguaiwu (gname, ghp, ggj, gfy, glv, mid, gyid, gexp, sid, gmaxhp, gbj, gxx, ms)
            VALUES ('Tam Unh Mach Huyet', 2000000000, $hmGj, 200, 48, $mid, 54, 1, '', '2000000000', 0, 0, 0)");
        $gid = intval($db->lastInsertId());
        tassert('E seed: tao quai lien thanh cong', $gid > 0, true);
        $db->exec("DELETE FROM boss WHERE bossid = 9001");
        $db->exec("INSERT INTO boss (bossid, bossname, bossinfo, bosslv, bosshp, bossmaxhp, bossgj, bossfy,
            bossbj, bossxx, bosszb, dljv, bossdj, djjv, bs, bosstime, sid, bossyp, ypjv, bossexp)
            VALUES (9001, 'Boss Thử Mạch (tạm thời)', 'Dong tam thoi cho tests/test_huyetmach_combat.php --live',
            30, 2000000000, 2000000000, $hmGj, 200, 0, 0, '', 0, '', 0, 0, NOW(), '', '', 0, '0')");

    // E.3 — quay 60 don PVE + 20 don BOSS, dem 3 chuoi thong bao mach am
    $hmMsgPen    = 'Âm Duy Mạch (Xuyên Giảm)';
    $hmMsgReduce = 'Dương Duy Mạch (Giảm Vật Lý)';
    $hmMsgDodge  = 'Âm Kiều Mạch (Né Tránh)';
    $nPve = 60; $nBoss = 20;
    $cPen = 0; $cRed = 0; $cDodge = 0; $cShort = 0;
    $bPen = 0; $bRed = 0; $bDodge = 0; $bShort = 0;
    for ($i = 0; $i < $nPve; $i++) {
        $db->exec("UPDATE game1 SET uhp = umaxhp, ustamina = umaxstamina WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);
        $h = $hmGet("cmd=pvegj&gid=$gid&nowmid=$mid&sid=$E2ESID");
        if (strlen($h) < 300) { $cShort++; continue; }
        if (strpos($h, $hmMsgPen) !== false) $cPen++;
        if (strpos($h, $hmMsgReduce) !== false) $cRed++;
        if (strpos($h, $hmMsgDodge) !== false) $cDodge++;
    }
    for ($i = 0; $i < $nBoss; $i++) {
        $db->exec("UPDATE game1 SET uhp = umaxhp WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);
        $h = $hmGet("cmd=pvbgj&bossid=9001&nowmid=$mid&sid=$E2ESID");
        if (strlen($h) < 300) { $bShort++; continue; }
        if (strpos($h, $hmMsgPen) !== false) $bPen++;
        if (strpos($h, $hmMsgReduce) !== false) $bRed++;
        if (strpos($h, $hmMsgDodge) !== false) $bDodge++;
    }

    // E.4 — bien that song sau khi cham dot (kiem $hurt + clamp + $cwxl di dung thu tu)
    $hpNow = intval($db->query("SELECT uhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
    $ghpNow = intval($db->query("SELECT ghp FROM midguaiwu WHERE id = $gid")->fetchColumn());
    $bhpNow = intval($db->query("SELECT bosshp FROM boss WHERE bossid = 9001")->fetchColumn());
    tassert('E pve: HTML du dai 60/60 don', $cShort, 0);
    tassert('E pve: quai van con song (ghp 2 ty khong bi diet)', $ghpNow > 1990000000, true);
    tassert('E pve: don that co giam mau quai (route khong chay vong)', $ghpNow < 2000000000, true);
    tassert('E boss: boss van con song', $bhpNow > 1995000000, true);
    tassert('E boss: don that co giam mau boss (route khong chay vong)', $bhpNow < 2000000000, true);
    tassert('E pve: pen hien 100% so don', $cPen, $nPve);
    tassert('E pve: reduce hien 100% so don', $cRed, $nPve);
    tassert('E boss: pen hien 100% so don', $bPen, $nBoss);
    tassert('E boss: reduce hien 100% so don', $bRed, $nBoss);
    tassert('E pve: dodge xuat hien it nhat 1 lan trong 60 don (p=0.4% triet de', $cDodge >= 1, true);
    tassert('E pve: dodge duoi 35% (ly thuyet 12%)', $cDodge < intval($nPve * 0.35), true);
    tassert('E boss: dodge duoi 40% (ly thuyet 12%, N=20)', $bDodge < intval($nBoss * 0.40), true);
    echo "E thong ke: pve dodge=$cDodge/$nPve (" . round($cDodge / $nPve * 100, 1) . "%), "
        . "boss dodge=$bDodge/$nBoss (" . round($bDodge / $nBoss * 100, 1) . "%), uhp sau 80 don = $hpNow\n";

    // E.5 — don det. Neu khoi E fatal giua chung, chay tay 5 cau nay truoc khi nghi test do code.
    $db->exec("DELETE FROM midguaiwu WHERE id = $gid");
    tassert('E cleanup: quai lien da sach', intval($db->query("SELECT COUNT(*) FROM midguaiwu WHERE id = $gid")->fetchColumn()), 0);
    $db->exec("DELETE FROM boss WHERE bossid = 9001");
    // Restore dung trang thai ban dau (khong de lai fixture bong mach = 0)
    $db->exec("DELETE FROM player_huyetmach_vessel WHERE sid = '$E2ESID'");
    $rv = $db->prepare("INSERT INTO player_huyetmach_vessel (sid, vessel_id, level, updated_at) VALUES (?, ?, ?, NOW())");
    foreach ($vesSnap as $vs) { $rv->execute(array($E2ESID, intval($vs['vessel_id']), intval($vs['level']))); }
    tassert('E cleanup: vessel fixture du 8 dong nhu ban dau', intval($db->query("SELECT COUNT(*) FROM player_huyetmach_vessel WHERE sid = '$E2ESID'")->fetchColumn()), count($vesSnap));
    $db->exec("UPDATE game1 SET huyetmach_id = " . intval($snap['huyetmach_id'])
        . ", huyetmach_quality = " . intval($snap['huyetmach_quality'])
        . ", huyetmach_awaken_stage = " . intval($snap['huyetmach_awaken_stage'])
        . ", ulv = " . intval($snap['ulv']) . ", ugj = " . intval($snap['ugj']) . ", ufy = " . intval($snap['ufy'])
        . ", ubj = " . intval($snap['ubj']) . ", uxx = " . intval($snap['uxx'])
        . ", uhp = " . intval($snap['uhp']) . ", umaxhp = " . intval($snap['umaxhp'])
        . ", ustamina = " . intval($snap['ustamina']) . " WHERE sid = '$E2ESID'");
    \player\player_cache_clear($E2ESID);
    }
} else {
    echo "SKIP E (them --live de chay route that qua Apache)\n";
}

// ===== F: do anh huong trung binh cua pen (chi chay khi co --measure) =====
if ($MEASURE) {
    // Cong thuc sao y: pve.php:237 / boss.php:235 (san 15% ugj), pvp.php:104 (san 5% ugj).
    // Thu cong KHONG tinh $cwgj (thu sung), vi no la hang so cong vao ca 2 ve nen khong doi ty le.
    if (!function_exists('hm_measure_gain')) {
        function hm_measure_gain($atk, $def, $floorRatio, $pen) {
            $base = max(round($atk - $def * 0.75), round($atk * $floorRatio));
            $pdef = \player\huyetmach_combat_pierce_def($pen, $def);
            $now  = max(round($atk - $pdef * 0.75), round($atk * $floorRatio));
            if ($base <= 0) return array('gain' => 0.0, 'floored' => ($now <= $base));
            return array('gain' => ($now - $base) / $base * 100.0, 'floored' => ($now <= $base));
        }
    }
    $PEN = 12.0; // mach lv48, stage 4 (0.25 x 48) — khong dung 15% de do theo tran thoi

    // 15 mau phan vi theo `ugj` cua nhan vat that trong game1 (median va p75 cho ket qua giong het)
    $rows = $db->query("SELECT ugj, ufy FROM game1 WHERE ugj > 0 AND ufy >= 0 ORDER BY ugj DESC")->fetchAll(PDO::FETCH_ASSOC);
    $pick = array();
    foreach (array(0, 0.07, 0.14, 0.21, 0.29, 0.36, 0.43, 0.5, 0.57, 0.64, 0.71, 0.79, 0.86, 0.93, 1.0) as $fr) {
        $idx = intval(round($fr * (count($rows) - 1)));
        if (isset($rows[$idx])) $pick[] = $rows[$idx];
    }
    $mons   = $db->query("SELECT DISTINCT gfy FROM midguaiwu WHERE gfy > 0 ORDER BY gfy")->fetchAll(PDO::FETCH_COLUMN);
    $bosses = $db->query("SELECT DISTINCT bossfy FROM boss WHERE bossfy > 0 ORDER BY bossfy")->fetchAll(PDO::FETCH_COLUMN);

    $sumPve = 0.0; $nPairPve = 0; $floorPve = 0;
    foreach ($pick as $p) {
        foreach ($mons as $gfy) {
            $r = hm_measure_gain(floatval($p['ugj']), floatval($gfy), 0.15, $PEN);
            $sumPve += $r['gain']; $nPairPve++;
            if ($r['floored']) $floorPve++;
        }
    }
    $sumBoss = 0.0; $nPairBoss = 0; $floorBoss = 0;
    foreach ($pick as $p) {
        foreach ($bosses as $fy) {
            $r = hm_measure_gain(floatval($p['ugj']), floatval($fy), 0.15, $PEN);
            $sumBoss += $r['gain']; $nPairBoss++;
            if ($r['floored']) $floorBoss++;
        }
    }
    $sumPvp = 0.0; $nPairPvp = 0; $floorPvp = 0;
    foreach ($pick as $a) {
        foreach ($pick as $d) {
            $r = hm_measure_gain(floatval($a['ugj']), floatval($d['ufy']), 0.05, $PEN);
            $sumPvp += $r['gain']; $nPairPvp++;
            if ($r['floored']) $floorPvp++;
        }
    }
    $meanPve = $nPairPve ? $sumPve / $nPairPve : 0.0;
    $meanBoss = $nPairBoss ? $sumBoss / $nPairBoss : 0.0;
    $meanPvp = $nPairPvp ? $sumPvp / $nPairPvp : 0.0;

    echo "F mau: " . count($pick) . " nhan vat x " . count($mons) . " quai, "
        . count($pick) . " x " . count($bosses) . " boss, " . count($pick) . "^2 cap PvP (pen=$PEN%) \n";
    echo "F PVE  mean=" . round($meanPve, 2) . "%  san-chan=" . $floorPve . "/" . $nPairPve . "\n";
    echo "F BOSS mean=" . round($meanBoss, 2) . "%  san-chan=" . $floorBoss . "/" . $nPairBoss . "\n";
    echo "F PVP  mean=" . round($meanPvp, 2) . "%  san-chan=" . $floorPvp . "/" . $nPairPvp . "\n";
    echo "PEN_PVE_MEAN=" . round($meanPve, 2) . " GUARD="
        . (($meanPve <= 8.0 && $meanBoss <= 8.0) ? 'OK' : 'VUOT') . "\n";

    tassert('F: mean pen PVE van <= 8% (quyet dinh ap dung)', $meanPve <= 8.0, true);
    tassert('F: mean pen BOSS van <= 8% (quyet dinh ap dung)', $meanBoss <= 8.0, true);
    if ($meanPvp > 8.0) {
        echo "F ghi chu: PVP mean " . round($meanPvp, 2) . "% > 8% => co Y KHONG ap pen o game/pvp.php (Step 10).\n";
    } else {
        echo "F CAN NHAC: PVP mean " . round($meanPvp, 2) . "% nay da duoi 8% => phai DOI CHU CHOT lai viec bo pen o PvP.\n";
    }
} else {
    echo "SKIP F (them --measure de do lai nguong pen)\n";
}
echo "\n" . ($fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n");
exit($fails === 0 ? 0 : 1);
