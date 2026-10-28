<?php
/**
 * test_lan2_data.php — Làn 2 (cấp 501–1000): dữ liệu quái (config) + khu / map / boss (DB, sau sql/migrate_lan2.php --apply).
 * Chạy: php tests/test_lan2_data.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}

$data = json_decode(file_get_contents(__DIR__ . '/../sql/lan2_data.json'), true);
$gw = \player\guaiwu_config_all();
$zb = json_decode(file_get_contents(__DIR__ . '/../class/config/zhuangbei_config.json'), true);

// ── Q1. Quái 300–469 ──
$ids = array(); $names = array(); $badLv = array(); $badDrop = array(); $badPill = array();
for ($id = 300; $id <= 469; $id++) {
    if (!isset($gw[(string)$id])) continue;
    $g = $gw[(string)$id];
    $ids[] = $id; $names[] = $g['gname'];
    $lv = intval($g['glv']);
    if ($lv < 501 || $lv > 1000) $badLv[] = $id;
    foreach (explode(',', $g['gzb']) as $z) {
        if (!isset($zb[$z]) || intval($zb[$z]['zblv']) > $lv) $badDrop[] = "$id:$z";
    }
    $p = \player\tupo_pill_for_level($lv);
    $want = $p ? '1,' . $p['djid'] : '1';
    if ($lv % 50 != 0 && $g['gdj'] !== $want) $badPill[] = "$id:{$g['gdj']}";
}
tassert('Q1 đủ 170 quái mã 300–469', count($ids), 170);
tassert('Q1 tên quái không trùng (trong làn và với quái cũ)', count(array_unique(array_map(function($g){ return $g['gname']; }, array_values($gw)))), count($gw));
tassert('Q1 cấp quái trong 501–1000', $badLv, array());
tassert('Q1 rơi trang bị có thật, bậc ≤ cấp quái', $badDrop, array());
tassert('Q1 Đột Phá Đan rơi đúng cảnh giới (dùng được ở cấp quái)', $badPill, array());

// Nhịp: quái Thường cùng kiểu tăng dần theo cấp; nối liền quái cấp 498 cũ (không nhảy > 30%)
$thuong = array();
foreach ($ids as $id) { $g = $gw[(string)$id]; if ($g['dljv'] == 20 && $g['gbj'] == 5 && $g['gxx'] == 5) $thuong[intval($g['glv'])] = intval($g['ghp']); }
ksort($thuong);
$prev = 0; $mono = true; foreach ($thuong as $hp) { if ($hp < $prev) $mono = false; $prev = $hp; }
tassert('Q1 máu quái Thường tăng dần theo cấp', $mono, true);
$first = reset($thuong);
tassert('Q1 quái Thường cấp 502 nối liền quái 488–494 cũ (350k–480k máu)', $first >= 350000 && $first <= 480000, true);

// ── Q2. DB: khu / map / boss ──
$db = $dblj;
$have = $db->query("SELECT COUNT(*) FROM qy WHERE qyid BETWEEN 37 AND 46")->fetchColumn();
if (intval($have) === 0) {
    echo "SKIP  Q2 (chua chay sql/migrate_lan2.php --apply)\n";
} else {
    $maps = array();
    foreach ($db->query("SELECT mid, mname, mup, mdown, mleft, mright, mqy, midboss, ms, mgid FROM mid WHERE mqy BETWEEN 37 AND 46") as $r) $maps[intval($r['mid'])] = $r;
    tassert('Q2 80 map ở khu 37–46', count($maps), 80);
    $per = array(); foreach ($maps as $m) $per[$m['mqy']] = isset($per[$m['mqy']]) ? $per[$m['mqy']] + 1 : 1;
    tassert('Q2 mỗi khu 8 map', array_unique(array_values($per)), array(8));
    // Lối đi 2 chiều + đi từ map 408 tới được cả 80 map
    $opp = array('mup' => 'mdown', 'mdown' => 'mup', 'mleft' => 'mright', 'mright' => 'mleft');
    $all = $maps;
    $r408 = $db->query("SELECT mid, mup, mdown, mleft, mright FROM mid WHERE mid = 408")->fetch(PDO::FETCH_ASSOC);
    $all[408] = $r408;
    $oneWay = array();
    foreach ($maps as $mid => $m) foreach ($opp as $d => $o) {
        $t = intval($m[$d]);
        if ($t && (!isset($all[$t]) || intval($all[$t][$o]) !== $mid)) $oneWay[] = "$mid.$d";
    }
    tassert('Q2 mọi lối đi đều 2 chiều', $oneWay, array());
    $seen = array(408 => 1); $queue = array(408);
    while ($queue) { $c = array_shift($queue); foreach ($opp as $d => $o) { $t = intval($all[$c][$d]); if ($t && isset($maps[$t]) && !isset($seen[$t])) { $seen[$t] = 1; $queue[] = $t; } } }
    tassert('Q2 từ Thiên Ngoại Thiên Đỉnh (408) đi tới cả 80 map', count($seen) - 1, 80);
    $badG = array();
    foreach ($maps as $mid => $m) foreach (explode(',', $m['mgid']) as $p) { $gid = intval($p); if (!isset($gw[(string)$gid])) $badG[] = "$mid:$gid"; }
    tassert('Q2 quái trên map đều có trong config', $badG, array());
    // Boss: 10 boss, mỗi khu 1 ở map cuối (có Tinh Anh), hồi sinh 600 giây, cấp 550…1000
    $bossMaps = array(); $lvs = array();
    foreach ($maps as $mid => $m) if (intval($m['midboss']) > 0) {
        $b = $db->query("SELECT bosslv FROM boss WHERE bossid = " . intval($m['midboss']))->fetch(PDO::FETCH_ASSOC);
        $bossMaps[] = intval($m['ms']) === 600 && $b ? 'ok' : "$mid";
        if ($b) $lvs[] = intval($b['bosslv']);
    }
    sort($lvs);
    tassert('Q2 10 boss, hồi sinh 600 giây', array(count($bossMaps), array_unique($bossMaps)), array(10, array('ok')));
    tassert('Q2 cấp boss 550, 600 … 1000', $lvs, range(550, 1000, 50));
    $qe = $db->query("SELECT COUNT(*) FROM qy q JOIN mid m ON m.mid = q.mid AND m.mqy = q.qyid WHERE q.qyid BETWEEN 37 AND 46")->fetchColumn();
    tassert('Q2 cửa vào khu (qy.mid) là map của chính khu', intval($qe), 10);
    tassert('Q2 có ảnh sơ đồ dt/ditu/37–46.png', count(array_filter(range(37, 46), function($q){ return is_file(__DIR__ . "/../dt/ditu/$q.png"); })), 10);
}

// ── Q4. Nhiệm vụ (L2-C): chương 14–23 trỏ đúng khu / quái / boss / đan; 10 NPC, nhiệm vụ trỏ quái + map làn 2 ──
$mc = \player\nhiemvu_main_config();
$chs = array(); foreach ($mc['chapters'] as $c) if ($c['id'] >= 14) $chs[$c['id']] = $c;
tassert('Q4 chương 14–23', array_keys($chs), range(14, 23));
$bossNames = array(); foreach ($data['bosses'] as $b) $bossNames[] = $b['bossname'];
$bad = array();
foreach ($chs as $cid => $c) {
    $k = $cid - 14;
    if ($c['level_range'] !== array(500 + 50 * $k, 550 + 50 * $k)) $bad[] = "$cid:range";
    foreach ($c['quests'] as $q) {
        if ($q['type'] === 'reach_region' && $q['region'] !== 37 + $k) $bad[] = $q['id'];
        if ($q['type'] === 'kill_monster') foreach ($q['monster_ids'] as $m) if (!isset($gw[(string)$m]) || intval($gw[(string)$m]['glv']) <= 500 + 50 * $k) $bad[] = $q['id'];
        if ($q['type'] === 'kill_boss_name' && !in_array($q['boss_names'][0], $bossNames, true)) $bad[] = $q['id'];
        if ($q['type'] === 'collect_item') {
            // vật phẩm phải rơi từ quái của chính khu
            $drops = false;
            foreach ($ids as $id) { $g = $gw[(string)$id]; if (intval($g['glv']) > 500 + 50 * $k && intval($g['glv']) <= 550 + 50 * $k && in_array((string)$q['item_id'], explode(',', $g['gdj']), true)) $drops = true; }
            if (!$drops) $bad[] = $q['id'];
        }
        if ($q['type'] === 'breakthrough' && $q['target_level'] !== 550 + 50 * $k) $bad[] = $q['id'];
    }
}
tassert('Q4 mọi bước chương 14–23 trỏ đúng khu / quái / boss / vật phẩm rơi trong khu', $bad, array());
$npcs = json_decode(file_get_contents(__DIR__ . '/../class/config/npc_config.json'), true);
$rws = json_decode(file_get_contents(__DIR__ . '/../class/config/renwu_config.json'), true);
$mapIds = array(); foreach ($data['regions'] as $r) foreach ($r['maps'] as $m) $mapIds[] = intval($m['mid']);
$bad = array();
for ($nid = 49; $nid <= 58; $nid++) {
    if (!isset($npcs[(string)$nid])) { $bad[] = "npc$nid"; continue; }
    foreach (explode(',', $npcs[(string)$nid]['taskid']) as $rid) {
        $rw = isset($rws[$rid]) ? $rws[$rid] : null;
        if (!$rw || !isset($gw[$rw['rwyq']]) || intval($gw[$rw['rwyq']]['id']) < 300 || !in_array(intval($rw['rwqy']), $mapIds, true)) $bad[] = "rw$rid";
    }
}
tassert('Q4 10 NPC (49–58), 30 nhiệm vụ trỏ quái làn 2 + map Truyền tống làn 2', $bad, array());
if (intval($have) > 0) {
    $placed = 0;
    foreach (json_decode(file_get_contents(__DIR__ . '/../sql/lan2_c_data.json'), true)['npcs'] as $n) {
        $mn = $db->query("SELECT mnpc FROM mid WHERE mid = " . intval($n['mid']))->fetchColumn();
        if (in_array((string)$n['nid'], explode(',', $mn), true)) $placed++;
    }
    tassert('Q4 DB: 10 NPC đã đặt ở map cửa vào khu (sau migrate_lan2_c.php)', $placed, 10);
}

// ── Q3. EXP quái làn 2 ≈ 400 quái cùng cấp mỗi cấp ──
tassert('Q3 EXP quái cấp 502 = 110 × 502² ÷ 400', \player\guaiwu_exp(502, 107), round(110.0 * 502 * 502 / 400));

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
