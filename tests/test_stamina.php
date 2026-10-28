<?php
/**
 * test_stamina.php — Thể lực: tối đa 100 + 1 mỗi cấp, hồi 1 điểm / phút, hồi đúng tới trần mới.
 * Phần getplayer CẦN DB (nhân vật e2e, tự trả lại sau khi chạy).
 * Chạy: php tests/test_stamina.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$db = $dblj;
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}

// ── S1. Công thức ─────────────────────────────────────────────
tassert('S1 cap 1 = 100', \player\stamina_cap(100, 1), 100);
tassert('S1 cap 500 = 599', \player\stamina_cap(100, 500), 599);
tassert('S1 cap 4300 = 4399', \player\stamina_cap(100, 4300), 4399);
tassert('S1 hoi 1 diem / 60 giay', \player\stamina_interval_sec(), 60);
$t0 = strtotime('2026-10-28 10:00:00');
$r = \player\settle_stamina(100, \player\stamina_cap(100, 500), date('Y-m-d H:i:s', $t0), $t0 + 600, \player\stamina_interval_sec());
tassert('S1 10 phut hoi 10 diem (100 -> 110, tran 599)', $r['value'], 110);
$r = \player\settle_stamina(590, 599, date('Y-m-d H:i:s', $t0), $t0 + 3600, 60);
tassert('S1 khong vuot tran 599', $r['value'], 599);

// ── S2. getplayer trả trần theo cấp (DB) ──────────────────────
$sid = str_repeat('e2', 16);
$p0 = \player\getplayer($sid, $db);
if (!$p0 || !$p0->sid) {
    echo "SKIP  S2 (khong co nhan vat e2e — chay php tests/e2e_fixture.php)\n";
} else {
    $orig = $db->query("SELECT ulv, umaxstamina FROM game1 WHERE sid = '$sid'")->fetch(PDO::FETCH_ASSOC);
    $db->prepare("UPDATE game1 SET ulv = 250, umaxstamina = 100 WHERE sid = ?")->execute(array($sid));
    \player\player_cache_clear($sid);
    tassert('S2 cap 250: the luc toi da 349', intval(\player\getplayer($sid, $db)->umaxstamina), 349);
    tassert('S2 cot DB giu goc 100', intval($db->query("SELECT umaxstamina FROM game1 WHERE sid = '$sid'")->fetchColumn()), 100);
    $db->prepare("UPDATE game1 SET ulv = ?, umaxstamina = ? WHERE sid = ?")->execute(array($orig['ulv'], $orig['umaxstamina'], $sid));
    \player\player_cache_clear($sid);
}

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
