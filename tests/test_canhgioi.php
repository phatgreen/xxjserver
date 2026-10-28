<?php
/**
 * test_canhgioi.php — Lộ Trình Cảnh Giới (class/config/canhgioi_reward.json): danh sách mốc, mức thưởng, nhận thưởng 1 lần.
 * Phần nhận thưởng CẦN DB (dùng nhân vật e2e, tự trả lại cấp / tiền / tu vi sau khi chạy).
 * Chạy: php tests/test_canhgioi.php
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

// ── R1. Danh sách mốc ─────────────────────────────────────────
$ms = \player\nhiemvu_realm_milestones();
$byId = array();
foreach ($ms as $m) $byId[$m['id']] = $m;
tassert('R1 86 canh gioi x 5 moc - 1 (canh gioi dau khong co dot pha vao) = 429', count($ms), 429);
tassert('R1 ma moc khong trung', count($byId), 429);
tassert('R1 moc dau = CG10 (Nhap Mon So ky)', $ms[0]['id'] . '|' . $ms[0]['name'], 'CG10|Nhập Môn Sơ kỳ');
tassert('R1 moc cuoi = CG4290 (Vo Nga Vien Man)', $ms[428]['id'] . '|' . $ms[428]['name'], 'CG4290|Vô Ngã Viên Mãn');
tassert('R1 CG51 = dot pha Luyen The', isset($byId['CG51']) ? $byId['CG51']['name'] . '|' . (int)$byId['CG51']['major'] : '', 'Đột Phá Luyện Thể|1');
tassert('R1 CG4251 = dot pha Vo Nga', isset($byId['CG4251']) ? $byId['CG4251']['name'] : '', 'Đột Phá Vô Ngã');
tassert('R1 khong co CG1 / CG4300', isset($byId['CG1']) || isset($byId['CG4300']), false);
$sorted = true;
for ($i = 1; $i < count($ms); $i++) if ($ms[$i]['lv'] <= $ms[$i - 1]['lv']) $sorted = false;
tassert('R1 xep tang dan theo cap', $sorted, true);

// ── R2. Thưởng ────────────────────────────────────────────────
$r = \player\nhiemvu_realm_reward($byId['CG951']);
tassert('R2 CG951 dot pha: exp 15% thanh cap', $r['exp'], intval(floor(952 * 952 * 110 * 15 / 100)));
tassert('R2 CG951: LT = 10 x 951 x (1 + 9.51)', $r['lt'], intval(round(10 * 951 * (1 + 951 / 100))));
tassert('R2 CG951: MT = 10 x (95.1 + 3)', $r['mt'], 981);
$r = \player\nhiemvu_realm_reward($byId['CG960']);
tassert('R2 CG960 So ky: exp 5%, khong MT', array($r['exp'], $r['mt']), array(intval(floor(961 * 961 * 110 * 5 / 100)), 0));
tassert('R2 CG960: LT = 2 x 960 x 10.6', $r['lt'], 20352);

// ── R3. Nhận thưởng (DB) ──────────────────────────────────────
$sid = str_repeat('e2', 16);
$p0 = \player\getplayer($sid, $db);
if (!$p0) {
    echo "SKIP  R3 (khong co nhan vat e2e — chay php tests/e2e_fixture.php)\n";
} else {
    $orig = $db->query("SELECT ulv, uexp, uyxb, uczb FROM game1 WHERE sid = '$sid'")->fetch(PDO::FETCH_ASSOC);
    $db->prepare("DELETE FROM player_nhiemvu_achievements WHERE sid = ? AND achievement_id LIKE 'CG%'")->execute(array($sid));
    $db->prepare("UPDATE game1 SET ulv = 960, uexp = 0 WHERE sid = ?")->execute(array($sid));
    \player\player_cache_clear($sid);

    $g = \player\nhiemvu_realm_get($sid, 960, $db);
    tassert('R3 cap 960: so moc nhan duoc = 4 x 19 + 19 dot pha + So ky 960 = 96', count($g['claimable']), 96);
    tassert('R3 moc sap toi dau tien = CG970', $g['upcoming'][0]['id'], 'CG970');
    tassert('R3 cham do nhiem vu', \player\nhiemvu_realm_has_claimable($sid, 960, $db), true);

    $c = \player\nhiemvu_realm_claim($sid, 'CG970', $db);
    tassert('R3 chua dat CG970: tu choi', $c['ok'], false);
    $c = \player\nhiemvu_realm_claim($sid, 'CGxx', $db);
    tassert('R3 ma sai: tu choi', $c['ok'], false);

    $before = $db->query("SELECT uyxb, uczb FROM game1 WHERE sid = '$sid'")->fetch(PDO::FETCH_ASSOC);
    $c = \player\nhiemvu_realm_claim($sid, 'CG951', $db);
    tassert('R3 nhan CG951 thanh cong', $c['ok'], true);
    $after = $db->query("SELECT uyxb, uczb FROM game1 WHERE sid = '$sid'")->fetch(PDO::FETCH_ASSOC);
    $rw = \player\nhiemvu_realm_reward($byId['CG951']);
    tassert('R3 cong dung LT', $after['uyxb'] - $before['uyxb'], $rw['lt']);
    tassert('R3 cong dung MT', $after['uczb'] - $before['uczb'], $rw['mt']);
    $c = \player\nhiemvu_realm_claim($sid, 'CG951', $db);
    tassert('R3 nhan lan 2: tu choi', $c['ok'], false);
    $after2 = $db->query("SELECT uyxb FROM game1 WHERE sid = '$sid'")->fetch(PDO::FETCH_ASSOC);
    tassert('R3 lan 2 khong cong them LT', $after2['uyxb'], $after['uyxb']);

    // Nhận hết các mốc còn lại -> hết chấm đỏ
    foreach (\player\nhiemvu_realm_get($sid, 960, $db)['claimable'] as $m) \player\nhiemvu_realm_claim($sid, $m['id'], $db);
    tassert('R3 nhan het: khong con cham do', \player\nhiemvu_realm_has_claimable($sid, 960, $db), false);
    tassert('R3 da nhan 96 moc', \player\nhiemvu_realm_get($sid, 960, $db)['claimed'], 96);

    // Dọn: trả lại trạng thái nhân vật e2e
    $db->prepare("DELETE FROM player_nhiemvu_achievements WHERE sid = ? AND achievement_id LIKE 'CG%'")->execute(array($sid));
    $db->prepare("UPDATE game1 SET ulv = ?, uexp = ?, uyxb = ?, uczb = ? WHERE sid = ?")
       ->execute(array($orig['ulv'], $orig['uexp'], $orig['uyxb'], $orig['uczb'], $sid));
    \player\player_cache_clear($sid);
}

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
