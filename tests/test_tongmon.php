<?php
/**
 * test_tongmon.php — Tông Môn (docs/DESIGN_TONGMON_CANBANG.md): bảng lợi ích, % áp vào getplayer(), cột migrate.
 * Phần T2 CẦN DB (nhân vật e2e; tạo tông môn tạm rồi xóa sau khi chạy). Chạy sau sql/migrate_tongmon.php --apply.
 * Chạy: php tests/test_tongmon.php
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

// ── T1. Bảng lợi ích ──────────────────────────────────────────
$tb = \player\club_benefits_table();
tassert('T1 du 10 cap', count($tb), 10);
tassert('T1 cap 1: 8 nguoi, +1% HP, +0.5% cong/thu, +1% EXP', $tb[1], array(8, 1, 0.5, 0.5, 1));
tassert('T1 cap 10: 50 nguoi, +10% HP, +5% cong/thu, +10% EXP', $tb[10], array(50, 10, 5, 5, 10));

// ── T2. getplayer cộng % theo cấp tông môn (DB) ───────────────
$sid = str_repeat('e2', 16);
$p0 = \player\getplayer($sid, $db);
$inClub = $p0 && $p0->sid ? intval($db->query("SELECT COUNT(*) FROM clubplayer WHERE sid = '$sid'")->fetchColumn()) : 0;
if (!$p0 || !$p0->sid) {
    echo "SKIP  T2 (khong co nhan vat e2e — chay php tests/e2e_fixture.php)\n";
} elseif ($inClub) {
    echo "SKIP  T2 (nhan vat e2e dang o tong mon that — khong dung vao)\n";
} else {
    tassert('T2 chua vao tong mon: bonus 0', $p0->club_bonus, array('lv' => 0, 'hp' => 0, 'atk' => 0, 'def' => 0, 'exp' => 0));
    $base = array(intval($p0->umaxhp), intval($p0->ugj), intval($p0->ufy));

    $db->prepare("INSERT INTO club(clubname, clubinfo, clublv, clubexp, clubno1, clubyxb, clubczb, clubdiamond) VALUES ('TEST_TM', '', 5, 0, 0, 0, 0, 0)")->execute();
    $cid = intval($db->lastInsertId());
    $db->prepare("INSERT INTO clubplayer(clubid, uid, sid, uclv) VALUES (?, 0, ?, 0)")->execute(array($cid, $sid));
    \player\player_cache_clear($sid);
    tassert('T2 dang cho duyet (uclv 0): bonus 0', \player\getplayer($sid, $db)->club_bonus['lv'], 0);

    $db->prepare("UPDATE clubplayer SET uclv = 1 WHERE sid = ?")->execute(array($sid));
    \player\player_cache_clear($sid);
    $p5 = \player\getplayer($sid, $db);
    tassert('T2 cap 5: bonus', $p5->club_bonus, array('lv' => 5, 'hp' => 5, 'atk' => 2.5, 'def' => 2.5, 'exp' => 5));
    // % tông môn nhân cùng chỗ Cộng Hưởng, TRƯỚC chỉ số cộng thẳng của Linh Căn => lệch nhỏ so với gốc × %; dung sai 0,2%
    $near = function ($got, $exp) { return abs($got - $exp) <= max(2, $exp * 0.002); };
    tassert('T2 cap 5: khi huyet ~+5%', $near(intval($p5->umaxhp), $base[0] * 1.05), true);
    tassert('T2 cap 5: cong ~+2.5%', $near(intval($p5->ugj), $base[1] * 1.025), true);
    tassert('T2 cap 5: thu ~+2.5%', $near(intval($p5->ufy), $base[2] * 1.025), true);

    $db->prepare("DELETE FROM clubplayer WHERE sid = ? AND clubid = ?")->execute(array($sid, $cid));
    $db->prepare("DELETE FROM club WHERE clubid = ?")->execute(array($cid));
    \player\player_cache_clear($sid);
    tassert('T2 roi tong mon: khi huyet ve goc', intval(\player\getplayer($sid, $db)->umaxhp), $base[0]);
}

// ── T3. Cột chống nhận lặp đã có (migrate_tongmon.php) ────────
$dbName = $db->query("SELECT DATABASE()")->fetchColumn();
foreach (array(array('clubplayer', 'online_claim_at'), array('clubplayer', 'quest_bonus_date'), array('club', 'boss_date')) as $c) {
    $st = $db->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
    $st->execute(array($dbName, $c[0], $c[1]));
    tassert("T3 co cot {$c[0]}.{$c[1]}", intval($st->fetchColumn()), 1);
}

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
