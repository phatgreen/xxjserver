<?php
/**
 * test_linhcan_combat.php — 5 skill ngu hanh THUC CHIEN (Task 8).
 * Mac dinh (A-D):   "...php.exe" tests/test_linhcan_combat.php
 * Route that (+E):  "...php.exe" tests/test_linhcan_combat.php --live
 * --live can server dev dang chay (mac dinh http://127.0.0.1:8199, override bang LC_BASE).
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
require_once $ROOT . '/class/function/linhcan_skill.php';

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

$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$TESTSID = str_repeat('f', 32);

// ===== A: combat_mods dung tung skill =====
$modsHoa = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('hoa_diem_chuong'));
tclose('A1 hoa atk_mult 1.5', $modsHoa['atk_mult'], 1.5, 0.0001);
$modsThuy = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('thuy_ho_thuat'));
tclose('A2 thuy heal_pct 24 (8x3)', $modsThuy['heal_pct'], 24.0, 0.0001);
$modsMoc = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('moc_linh_thuat'));
tassert('A3 moc lifesteal +25', $modsMoc['lifesteal_add'], 25);
$modsKim = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('kim_cuong_chuong'));
tassert('A4 kim crit +40', $modsKim['crit_add'], 40);
$modsTho = \player\linhcan_skill_combat_mods(\player\linhcan_skill_get('tho_ho_thuat'));
tassert('A5 tho def_pct 30', $modsTho['def_pct'], 30);
tassert('A6 khong con hu_khong', \player\linhcan_skill_get('hu_khong_chuong'), false);
$mEmpty = \player\linhcan_skill_combat_mods(false);
tassert('A7 skill false = 8 key mac dinh', count($mEmpty), 8);
tclose('A8 mac dinh atk_mult 1.0', $mEmpty['atk_mult'], 1.0, 0.0001);
tassert('A9 ten debuff', \player\linhcan_skill_combat_mods(array('type' => 'debuff', 'ignore_def_percent' => 50, 'name' => 'X'))['ignore_def_pct'], 50.0);

// ===== B: can_use theo bac hanh + khi huyet =====
$pSim = new stdClass(); $pSim->umaxhp = 1000; $pSim->uhp = 100; $pSim->ulv = 1;
$sk = \player\linhcan_skill_get('kim_cuong_chuong');
tassert('B1 stage 1 fail', \player\linhcan_skill_can_use($pSim, $sk, 1)['ok'], false);
tassert('B2 stage 2 uhp==cost van fail', \player\linhcan_skill_can_use($pSim, $sk, 2)['ok'], false);
$pSim->uhp = 101;
tassert('B3 uhp 101 du', \player\linhcan_skill_can_use($pSim, $sk, 2)['ok'], true);
tassert('B3b mp_cost 100', \player\linhcan_skill_can_use($pSim, $sk, 2)['mp_cost'], 100);
tassert('B4 skill false fail', \player\linhcan_skill_can_use($pSim, false, 5)['ok'], false);
$pSim->uhp = 1000;
tassert('B5 stage 5 (Thanh Can) ok', \player\linhcan_skill_can_use($pSim, $sk, 5)['ok'], true);
tassert('B6 msg dich', \player\linhcan_skill_can_use($pSim, $sk, 0)['msg'], 'Hành chủ đạo chưa đạt Địa Căn (bậc 2).');

// ===== C: for_stage — hanh chu dao phai >= Dia Can =====
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '" . $TESTSID . "'");
tassert('C1 chua mo linh can', \player\linhcan_skill_for_stage($TESTSID, $db), false);
\player\linhcan_node_set($TESTSID, 'kim', 35, 1, $db);
tassert('C2 chu dao kim stage1 (chua Dia Can)', \player\linhcan_skill_for_stage($TESTSID, $db), false);
\player\linhcan_node_set($TESTSID, 'kim', 35, 2, $db);
$sk2 = \player\linhcan_skill_for_stage($TESTSID, $db);
tassert('C3 stage2 mo kim_cuong', is_array($sk2) ? $sk2['id'] : false, 'kim_cuong_chuong');
\player\linhcan_node_set($TESTSID, 'moc', 60, 2, $db);
$sk3 = \player\linhcan_skill_for_stage($TESTSID, $db);
tassert('C4 doi chu dao -> skill moc', is_array($sk3) ? $sk3['id'] : false, 'moc_linh_thuat');
\player\linhcan_node_set($TESTSID, 'moc', 1, 0, $db);
\player\linhcan_node_set($TESTSID, 'kim', 35, 2, $db);
tassert('C5 chu dao quay ve kim', \player\linhcan_skill_for_stage($TESTSID, $db)['id'], 'kim_cuong_chuong');

// ===== D: cooldown van dung bang player_linhcan_skill =====
$db->exec("DELETE FROM player_linhcan_skill WHERE sid = '" . $TESTSID . "'");
tassert('D1 chua dung -> dc true', \player\linhcan_skill_check_cooldown($TESTSID, 'kim_cuong_chuong', $db), true);
\player\linhcan_skill_record_use($TESTSID, 'kim_cuong_chuong', $db);
tassert('D2 vua dung -> dc false', \player\linhcan_skill_check_cooldown($TESTSID, 'kim_cuong_chuong', $db), false);
tassert('D3 cooldown_text khong rong', \player\linhcan_skill_cooldown_text($TESTSID, 'kim_cuong_chuong', $db) !== '', true);
$db->exec("UPDATE player_linhcan_skill SET last_used = DATE_SUB(NOW(), INTERVAL 10 MINUTE) WHERE sid = '" . $TESTSID . "'");
tassert('D4 qua 5 phut -> dc true', \player\linhcan_skill_check_cooldown($TESTSID, 'kim_cuong_chuong', $db), true);

$db->exec("DELETE FROM player_linhcan_node WHERE sid = '" . $TESTSID . "'");
$db->exec("DELETE FROM player_linhcan_skill WHERE sid = '" . $TESTSID . "'");

// ===== E: ROUTE THAT qua HTTP (chi chay khi co --live) =====
if ($LIVE) {
    $enc = new \encode\encode();
    $lcBase = getenv('LC_BASE');
    if (!is_string($lcBase) || $lcBase === '') { $lcBase = 'http://127.0.0.1:8199'; }
    $lcGet = function ($plain) use ($enc, $lcBase) {
        return (string) shell_exec('curl -s ' . escapeshellarg($lcBase . '/game.php?cmd=' . $enc->encode($plain)));
    };
    $E2ESID = 'e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2e2';
    $pl = \player\getplayer($E2ESID, $db);
    if (!is_object($pl)) {
        $fails++;
        echo "FAIL E: fixture e2e khong ton tai — chay tests/e2e_fixture.php truoc\n";
    } else {
        $snap = $db->query("SELECT ulv,ugj,ufy,ubj,uxx,uhp,umaxhp,ustamina FROM game1 WHERE sid = '$E2ESID'")->fetch(PDO::FETCH_ASSOC);

        // E.1 seed: ep endtime, node kim 35/bac 2 (chu dao → Kim Cương Chưởng crit +40)
        $db->exec("UPDATE game1 SET endtime = DATE_ADD(NOW(), INTERVAL 3000 SECOND) WHERE sid = '$E2ESID'");
        $db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
            VALUES (?, 'kim', 35, 2, NOW()) ON DUPLICATE KEY UPDATE level = 35, stage = 2")->execute(array($E2ESID));
        $db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
        // Top-up = dung gia tri umaxhp SAU buff cua getplayer (khong phai cot tho 35)
        $lcTopUp = function () use ($db, $E2ESID) {
            $p = \player\getplayer($E2ESID, $db);
            $db->exec("UPDATE game1 SET uhp = " . intval($p->umaxhp) . ", ustamina = umaxstamina WHERE sid = '$E2ESID'");
            \player\player_cache_clear($E2ESID);
        };
        $lcTopUp();
        $skE = \player\linhcan_skill_for_stage($E2ESID, $db);
        tassert('E1 chu dao kim mo kim_cuong', is_array($skE) ? $skE['id'] : false, 'kim_cuong_chuong');

        $mid = intval($pl->nowmid);
        $lcGj = intval(round(floatval($pl->ufy) * 0.75)) + 300;
        $db->exec("INSERT INTO midguaiwu (gname, ghp, ggj, gfy, glv, mid, gyid, gexp, sid, gmaxhp, gbj, gxx, ms)
            VALUES ('Tam Unh Linh Can', 2000000000, $lcGj, 200, 35, $mid, 54, 1, '', '2000000000', 0, 0, 0)");
        $gidL = intval($db->lastInsertId());
        tassert('E1 seed quai lien', $gidL > 0, true);

        // E.2 don PVE co skill: thi trien → cooldown → tu choi
        $h1 = $lcGet("cmd=pvegj&gid=$gidL&nowmid=$mid&sid=$E2ESID&canshu=uselcskill");
        tassert('E2.0 HTML du dai (server song)', strlen($h1) > 300, true);
        tassert('E2.1 co chu Thi triển', strpos($h1, 'Thi triển') !== false, true);
        tassert('E2.2 ten skill hien', strpos($h1, 'Kim Cương Chưởng') !== false, true);
        $rowC = intval($db->query("SELECT COUNT(*) FROM player_linhcan_skill WHERE sid = '$E2ESID' AND skill_id = 'kim_cuong_chuong'")->fetchColumn());
        tassert('E2.3 ghi cooldown row', $rowC, 1);
        $hpNow = intval($db->query("SELECT uhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
        $plB = \player\getplayer($E2ESID, $db);
        $hpMaxBuffed = intval($plB->umaxhp); // cot umaxhp tho = 35, gia tri sau buff = 34079
        tassert('E2.4 HP bi truu sau khi thi trien', $hpNow < $hpMaxBuffed, true);
        $costExp = max(1, intval($hpMaxBuffed * 10 / 100));
        tassert('E2.4b so khi huyet truu trong loi = 10% max sau buff', strpos($h1, '(−' . $costExp . ' khí huyết)') !== false, true);
        $lcTopUp();
        $h2 = $lcGet("cmd=pvegj&gid=$gidL&nowmid=$mid&sid=$E2ESID&canshu=uselcskill");
        tassert('E2.5 don 2 bi chan cooldown', strpos($h2, 'hồi chiêu') !== false, true);

        // E.3 mod THAT duong dan: thuy (heal 8%x3=24%) — so hien ra phai khop cong thuc
        $plC = \player\getplayer($E2ESID, $db);
        $maxExp = intval($plC->umaxhp);
        $db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
            VALUES (?, 'thuy', 40, 2, NOW()) ON DUPLICATE KEY UPDATE level = 40, stage = 2")->execute(array($E2ESID));
        $db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
        $lcTopUp();
        $h3pre = $lcGet("cmd=pvegj&gid=$gidL&nowmid=$mid&sid=$E2ESID"); // don moc: khong co skill
        $db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
        $lcTopUp();
        $h3 = $lcGet("cmd=pvegj&gid=$gidL&nowmid=$mid&sid=$E2ESID&canshu=uselcskill");
        $healExp = max(1, round($maxExp * 24 / 100));
        tassert('E3.1 chu dao thuy -> ten skill khac', strpos($h3, 'Thủy Hộ Thuật') !== false, true);
        tassert('E3.2 so hoi = 24% max sau buff (' . $healExp . ')', strpos($h3, 'hồi ' . $healExp . ' khí huyết') !== false, true);
        // Don moc CHUA bam skill: duoc phep co NUT ghi ten skill, nhung KHONG duoc co dong thong bao thi trien
        tassert('E3.3 don moc khong co dong "Thuy Ho Thuat: hoi"', strpos($h3pre, 'Thủy Hộ Thuật: hồi') === false, true);
        tassert('E3.3b don moc khong bi tru khi huyet thi trien', strpos($h3pre, 'khí huyết)!') === false, true);
        $hpAfter = intval($db->query("SELECT uhp FROM game1 WHERE sid = '$E2ESID'")->fetchColumn());
        tassert('E3.4 HP cuoi tran bi chan theo max sau buff', $hpAfter <= $maxExp, true);

        // E.4 stat mod crit (kim +40) — bang chung thong ke: P(bao kich) 123/200 -> 163/200
        $db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
            VALUES (?, 'kim', 45, 2, NOW()) ON DUPLICATE KEY UPDATE level = 45, stage = 2")->execute(array($E2ESID));
        $db->exec("UPDATE player_linhcan_node SET level = 1, stage = 0 WHERE sid = '$E2ESID' AND element = 'thuy'");
        \player\player_cache_clear($E2ESID);
        $NC = 150; $cPlain = 0; $cSkill = 0;
        for ($i = 0; $i < $NC; $i++) {
            $lcTopUp();
            $hx = $lcGet("cmd=pvegj&gid=$gidL&nowmid=$mid&sid=$E2ESID");
            if (strpos($hx, 'Bạo kích') !== false) $cPlain++;
        }
        for ($i = 0; $i < $NC; $i++) {
            $db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
            $lcTopUp();
            $hx = $lcGet("cmd=pvegj&gid=$gidL&nowmid=$mid&sid=$E2ESID&canshu=uselcskill");
            if (strpos($hx, 'Bạo kích') !== false) $cSkill++;
            if (strpos($hx, 'Thi triển') === false) { $fails++; echo "FAIL E4 don $i khong thi trien duoc\n"; break; }
        }
        $rPlain = round($cPlain / $NC * 100, 1);
        $rSkill = round($cSkill / $NC * 100, 1);
        echo "E4 crit: khong skill=$cPlain/$NC ($rPlain%), co skill=$cSkill/$NC ($rSkill%), ly thuyet 61.5 -> 81.5\n";
        tassert('E4.1 khong skill gan ly thuyet 61.5% (+/-10)', abs($rPlain - 61.5) <= 10, true);
        tassert('E4.2 co skill gan ly thuyet 81.5% (+/-10)', abs($rSkill - 81.5) <= 10, true);
        tassert('E4.3 crit tang it nhat 10 diem phan tram', $rSkill - $rPlain >= 10, true);

        // E.5 boss pvbgj cung cat nut (assert danh so E5.x)
        $db->exec("DELETE FROM boss WHERE bossid = 9001");
        $db->exec("INSERT INTO boss (bossid, bossname, bossinfo, bosslv, bosshp, bossmaxhp, bossgj, bossfy, bossbj, bossxx, bosszb, dljv, bossdj, djjv, bs, bosstime, sid, bossyp, ypjv, bossexp)
            VALUES (9001, 'Boss Thu Skill (tam thoi)', 'tests/test_linhcan_combat.php --live', 35, 2000000000, 2000000000, $lcGj, 200, 0, 0, '', 0, '', 0, 0, NOW(), '', '', 0, '0')");
        $db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
        $lcTopUp();
        $h3 = $lcGet("cmd=pvbgj&bossid=9001&nowmid=$mid&sid=$E2ESID&canshu=uselcskill");
        tassert('E5.0 boss HTML du dai', strlen($h3) > 300, true);
        tassert('E5.1 boss cung thi trien', strpos($h3, 'Thi triển') !== false, true);
        $bhp = intval($db->query("SELECT bosshp FROM boss WHERE bossid = 9001")->fetchColumn());
        // boss.php:363-368 co 20% "Ne tranh" ($gphurt = 0) → 1 don la may run, quay 5 don (P=0.2^5 ≈ 0.03%)
        for ($i = 0; $i < 5 && $bhp >= 2000000000; $i++) {
            $lcTopUp();
            $lcGet("cmd=pvbgj&bossid=9001&nowmid=$mid&sid=$E2ESID");
            $bhp = intval($db->query("SELECT bosshp FROM boss WHERE bossid = 9001")->fetchColumn());
        }
        tassert('E5.2 don that giam mau boss (sau toi da 5 don)', $bhp < 2000000000, true);

        // E.5 don det
        $db->exec("DELETE FROM midguaiwu WHERE id = $gidL");
        $db->exec("DELETE FROM boss WHERE bossid = 9001");
        $db->exec("DELETE FROM player_linhcan_skill WHERE sid = '$E2ESID'");
        $db->exec("DELETE FROM player_linhcan_node WHERE sid = '$E2ESID'");
        $db->exec("UPDATE game1 SET ulv = " . intval($snap['ulv']) . ", ugj = " . intval($snap['ugj'])
            . ", ufy = " . intval($snap['ufy']) . ", ubj = " . intval($snap['ubj'])
            . ", uxx = " . intval($snap['uxx']) . ", uhp = " . intval($snap['uhp'])
            . ", umaxhp = " . intval($snap['umaxhp']) . ", ustamina = " . intval($snap['ustamina'])
            . " WHERE sid = '$E2ESID'");
        \player\player_cache_clear($E2ESID);
        tassert('E6.1 cleanup quai lien', intval($db->query("SELECT COUNT(*) FROM midguaiwu WHERE id = $gidL")->fetchColumn()), 0);
        tassert('E6.2 cleanup node + skill', intval($db->query("SELECT COUNT(*) FROM player_linhcan_node WHERE sid = '$E2ESID'")->fetchColumn())
            + intval($db->query("SELECT COUNT(*) FROM player_linhcan_skill WHERE sid = '$E2ESID'")->fetchColumn()), 0);
    }
} else {
    echo "SKIP E (them --live de chay route that; can php -S 127.0.0.1:8199)\n";
}

echo "\n" . ($fails === 0 ? "ALL PASS\n" : "SO FAIL = " . $fails . "\n");
exit($fails === 0 ? 0 : 1);
