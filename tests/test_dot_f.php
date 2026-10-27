<?php
/**
 * test_dot_f.php — Đợt F: dữ liệu trang bị (đủ ô, phẩm chất theo bậc, bậc 500), Thần Trang 15 món,
 * cường hóa +8% chỉ số gốc, chế tạo đúng cấp. Phần cường hóa CẦN DB (tự tạo nhân vật test rồi dọn).
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_dot_f.php
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

// ── F1. Dữ liệu trang bị ──────────────────────────────────────
$all = \player\zhuangbei_config_all();
$tiers = array(1 => 1, 10 => 2, 30 => 3, 60 => 4, 120 => 5, 200 => 6, 300 => 7, 400 => 8, 500 => 9, 600 => 9, 800 => 10);
$slots = array(); $badRarity = array(); $sum = array();
foreach ($all as $v) {
    $t = intval($v['zblv']);
    $slots[$t][intval($v['zbtool'])] = 1;
    if (!isset($tiers[$t]) || intval($v['rarity']) !== $tiers[$t]) $badRarity[] = $v['zbid'];
    if (intval($v['realm_require']) !== $t) $badRarity[] = 'realm' . $v['zbid'];
    if (!isset($sum[$t][intval($v['zbtool'])])) $sum[$t][intval($v['zbtool'])] = intval($v['zbgj']) + intval($v['zbfy']) + intval($v['zbhp']);
}
$miss = array();
foreach ($tiers as $t => $r) if (!isset($slots[$t]) || count($slots[$t]) !== 15) $miss[] = $t;
tassert('F1 11 bac, moi bac du 15 o', $miss, array());
tassert('F1 pham chat + yeu cau cap theo bac', $badRarity, array());
$prev = -1; $mono = true;
foreach ($tiers as $t => $r) { $s = array_sum($sum[$t]); if ($s <= $prev) $mono = false; $prev = $s; }
tassert('F1 tong chi so tang dan theo bac', $mono, true);

// ── F2. Thần Trang: đủ 15 món = Hoàn chỉnh ───────────────────
tassert('F2 15 mon = moc Hoan chinh', \player\taozhuang_stage_by_count(15), 16);
tassert('F2 12 mon = Dai thanh', \player\taozhuang_stage_by_count(12), 12);

// ── F3. Chế tạo: cấp yêu cầu = bậc đồ ────────────────────────
$ct = json_decode(file_get_contents(__DIR__ . '/../class/config/chetao_config.json'), true);
$bad = array();
foreach ($ct['recipes'] as $r) {
    if ($r['type'] !== 'equipment') continue;
    $zb = \player\zhuangbei_config_get($r['result_zbid']);
    if (!$zb || intval($r['level_required']) !== intval($zb['zblv'])) $bad[] = $r['id'];
}
tassert('F3 che tao dung cap', $bad, array());

// ── F4. Cường hóa +8% chỉ số gốc ─────────────────────────────
$SID = str_repeat('f', 31) . '9';
$db->exec("INSERT INTO game1 (sid, uid, uname, ulv, uhp, umaxhp, endtime, ugj, ufy, ubj, uyxb, uexp)
    VALUES ('$SID', 990177, 'DOTFTEST', 500, 5000, 5000, NOW(), 100, 100, 0, 0, 0)
    ON DUPLICATE KEY UPDATE ulv = 500, endtime = NOW()");
$db->exec("DELETE FROM playerzhuangbei WHERE sid = '$SID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$SID'");
\player\player_cache_clear($SID);
$base = \player\zhuangbei_config_get(23);   // Thần Lam Tinh Kiếm (bậc 400)
$bgj = intval($base['zbgj']);
// DB còn giữ chỉ số theo gốc cũ (thấp hơn gốc mới): lần cường hóa đầu đưa về gốc rồi cộng 8%
$db->exec("INSERT INTO playerzhuangbei (zbid, uid, sid, qianghua, zbgj, zbfy, zbhp, zbbj, zbxx) VALUES (23, 990177, '$SID', 0, 783, 0, 0, 48, 28)");
$zn = intval($db->lastInsertId());
\player\adddj($SID, 1, 50, $db);
$r = \player\upzbsx($zn, 'zbgj', $SID, $db);
$st = $db->prepare("SELECT zbgj, qianghua FROM playerzhuangbei WHERE zbnowid = ?");
$st->execute(array($zn)); $row = $st->fetch(PDO::FETCH_ASSOC);
tassert('F4 +1 tu goc cu = goc moi + 8% goc moi', array($r, intval($row['zbgj']), intval($row['qianghua'])), array(1, $bgj + intval(round($bgj * 0.08)), 1));
// Lần sau cộng đúng 8% gốc (không lũy tiến) — thử tới khi thành công (tỉ lệ ~97% ở +1)
for ($i = 0; $i < 20; $i++) { if (\player\upzbsx($zn, 'zbgj', $SID, $db) === 1) break; }
$st->execute(array($zn)); $row = $st->fetch(PDO::FETCH_ASSOC);
tassert('F4 +2 = goc + 2 x 8% goc', intval($row['zbgj']), $bgj + 2 * intval(round($bgj * 0.08)));

$db->exec("DELETE FROM playerzhuangbei WHERE sid = '$SID'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '$SID'");
$db->exec("DELETE FROM game1 WHERE sid = '$SID'");
echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
