<?php
/**
 * test_lan2_data.php — Làn 2 (cấp 501–1000): dữ liệu quái (config) + khu / map / boss (DB, sau sql/migrate_lan2.php --apply).
 * Làn sau dùng lại: tests/test_lan3_data.php đặt $LANE rồi require file này.
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

// Tham số làn (mặc định làn 2)
if (!isset($LANE)) $LANE = array('n' => 2, 'gid0' => 300, 'lv0' => 500, 'qy0' => 37, 'entry' => 408, 'ch0' => 14, 'npc0' => 49);
// k = số khu của làn (mặc định 10; làn 9 có 6 khu): 17 quái / 8 map / 1 boss / 1 chương / 1 NPC mỗi khu
$K = isset($LANE['k']) ? $LANE['k'] : 10; $NQ = 17 * $K; $NM = 8 * $K;
$N = $LANE['n']; $G0 = $LANE['gid0']; $G1 = $G0 + $NQ - 1; $LV0 = $LANE['lv0']; $LV1 = $LV0 + 50 * $K;
$QY0 = $LANE['qy0']; $QY1 = $QY0 + $K - 1; $ENTRY = $LANE['entry']; $CH0 = $LANE['ch0']; $NPC0 = $LANE['npc0'];
$data = json_decode(file_get_contents(__DIR__ . "/../sql/lan{$N}_data.json"), true);
$gw = \player\guaiwu_config_all();
$zb = json_decode(file_get_contents(__DIR__ . '/../class/config/zhuangbei_config.json'), true);

// ── Q1. Quái làn (170 mã liên tiếp) ──
$ids = array(); $names = array(); $badLv = array(); $badDrop = array(); $badPill = array();
for ($id = $G0; $id <= $G1; $id++) {
    if (!isset($gw[(string)$id])) continue;
    $g = $gw[(string)$id];
    $ids[] = $id; $names[] = $g['gname'];
    $lv = intval($g['glv']);
    if ($lv <= $LV0 || $lv > $LV1) $badLv[] = $id;
    foreach (explode(',', $g['gzb']) as $z) {
        if (!isset($zb[$z]) || intval($zb[$z]['zblv']) > $lv) $badDrop[] = "$id:$z";
    }
    $p = \player\tupo_pill_for_level($lv);
    $want = $p ? '1,' . $p['djid'] : '1';
    if ($lv % 50 != 0 && $g['gdj'] !== $want) $badPill[] = "$id:{$g['gdj']}";
}
tassert("Q1 đủ $NQ quái mã {$G0}–$G1", count($ids), $NQ);
tassert('Q1 tên quái không trùng (trong làn và với quái cũ)', count(array_unique(array_map(function($g){ return $g['gname']; }, array_values($gw)))), count($gw));
tassert("Q1 cấp quái trong " . ($LV0 + 1) . "–$LV1", $badLv, array());
tassert('Q1 rơi trang bị có thật, bậc ≤ cấp quái', $badDrop, array());
tassert('Q1 Đột Phá Đan rơi đúng cảnh giới (dùng được ở cấp quái)', $badPill, array());

// Nhịp: quái Thường tăng dần theo cấp; nối liền quái Thường cuối làn trước (đổi bậc đồ ⇒ cho phép tăng tới 50%)
$thuong = array();
foreach ($ids as $id) { $g = $gw[(string)$id]; if ($g['dljv'] == 20 && $g['gbj'] == 5 && $g['gxx'] == 5) $thuong[intval($g['glv'])] = intval($g['ghp']); }
ksort($thuong);
$prev = 0; $mono = true; foreach ($thuong as $hp) { if ($hp < $prev) $mono = false; $prev = $hp; }
tassert('Q1 máu quái Thường tăng dần theo cấp', $mono, true);
$first = reset($thuong);
$prevLv = 0; $prevHp = 0;
foreach ($gw as $k => $g) if (intval($k) < $G0 && $g['dljv'] == 20 && $g['gbj'] == 5 && $g['gxx'] == 5 && intval($g['glv']) <= $LV0 && intval($g['glv']) > $prevLv) { $prevLv = intval($g['glv']); $prevHp = intval($g['ghp']); }
tassert("Q1 quái Thường đầu làn nối liền quái Thường cấp $prevLv làn trước (×0,95–×1,5)", $first >= $prevHp * 0.95 && $first <= $prevHp * 1.5, true);

// ── Q2. DB: khu / map / boss ──
$db = $dblj;
$have = $db->query("SELECT COUNT(*) FROM qy WHERE qyid BETWEEN $QY0 AND $QY1")->fetchColumn();
if (intval($have) === 0) {
    echo "SKIP  Q2 (chua chay sql/migrate_lan$N.php --apply)\n";
} else {
    $maps = array();
    foreach ($db->query("SELECT mid, mname, mup, mdown, mleft, mright, mqy, midboss, ms, mgid FROM mid WHERE mqy BETWEEN $QY0 AND $QY1") as $r) $maps[intval($r['mid'])] = $r;
    tassert("Q2 $NM map ở khu {$QY0}–$QY1", count($maps), $NM);
    $per = array(); foreach ($maps as $m) $per[$m['mqy']] = isset($per[$m['mqy']]) ? $per[$m['mqy']] + 1 : 1;
    tassert('Q2 mỗi khu 8 map', array_unique(array_values($per)), array(8));
    // Lối đi 2 chiều + đi từ map cửa vào của làn tới được cả 80 map
    $opp = array('mup' => 'mdown', 'mdown' => 'mup', 'mleft' => 'mright', 'mright' => 'mleft');
    $all = $maps;
    $all[$ENTRY] = $db->query("SELECT mid, mup, mdown, mleft, mright FROM mid WHERE mid = $ENTRY")->fetch(PDO::FETCH_ASSOC);
    $oneWay = array();
    foreach ($maps as $mid => $m) foreach ($opp as $d => $o) {
        $t = intval($m[$d]);
        // map đích ngoài làn (vd cửa sang làn sau) -> đọc từ DB để kiểm tra chiều về
        if ($t && !isset($all[$t])) { $row = $db->query("SELECT mid, mup, mdown, mleft, mright FROM mid WHERE mid = $t")->fetch(PDO::FETCH_ASSOC); if ($row) $all[$t] = $row; }
        if ($t && (!isset($all[$t]) || intval($all[$t][$o]) !== $mid)) $oneWay[] = "$mid.$d";
    }
    tassert('Q2 mọi lối đi đều 2 chiều', $oneWay, array());
    $seen = array($ENTRY => 1); $queue = array($ENTRY);
    while ($queue) { $c = array_shift($queue); foreach ($opp as $d => $o) { $t = intval($all[$c][$d]); if ($t && isset($maps[$t]) && !isset($seen[$t])) { $seen[$t] = 1; $queue[] = $t; } } }
    tassert("Q2 từ map cửa vào $ENTRY đi tới cả $NM map", count($seen) - 1, $NM);
    $badG = array();
    foreach ($maps as $mid => $m) foreach (explode(',', $m['mgid']) as $p) { $gid = intval($p); if (!isset($gw[(string)$gid])) $badG[] = "$mid:$gid"; }
    tassert('Q2 quái trên map đều có trong config', $badG, array());
    // Boss: 10 boss, mỗi khu 1 ở map cuối (có Tinh Anh), hồi sinh 600 giây, cấp LV0+50 … LV1
    $bossMaps = array(); $lvs = array();
    foreach ($maps as $mid => $m) if (intval($m['midboss']) > 0) {
        $b = $db->query("SELECT bosslv FROM boss WHERE bossid = " . intval($m['midboss']))->fetch(PDO::FETCH_ASSOC);
        $bossMaps[] = intval($m['ms']) === 600 && $b ? 'ok' : "$mid";
        if ($b) $lvs[] = intval($b['bosslv']);
    }
    sort($lvs);
    tassert("Q2 $K boss, hồi sinh 600 giây", array(count($bossMaps), array_unique($bossMaps)), array($K, array('ok')));
    tassert("Q2 cấp boss " . ($LV0 + 50) . " … $LV1", $lvs, range($LV0 + 50, $LV1, 50));
    $qe = $db->query("SELECT COUNT(*) FROM qy q JOIN mid m ON m.mid = q.mid AND m.mqy = q.qyid WHERE q.qyid BETWEEN $QY0 AND $QY1")->fetchColumn();
    tassert('Q2 cửa vào khu (qy.mid) là map của chính khu', intval($qe), $K);
    tassert("Q2 có ảnh sơ đồ dt/ditu/{$QY0}–$QY1.png", count(array_filter(range($QY0, $QY1), function($q){ return is_file(__DIR__ . "/../dt/ditu/$q.png"); })), $K);
}

// ── Q4. Nhiệm vụ: 10 chương của làn trỏ đúng khu / quái / boss / vật phẩm; 10 NPC, nhiệm vụ trỏ quái + map của làn ──
$mc = \player\nhiemvu_main_config();
$chs = array(); foreach ($mc['chapters'] as $c) if ($c['id'] >= $CH0 && $c['id'] <= $CH0 + $K - 1) $chs[$c['id']] = $c;
tassert("Q4 chương {$CH0}–" . ($CH0 + $K - 1), array_keys($chs), range($CH0, $CH0 + $K - 1));
$bossNames = array(); foreach ($data['bosses'] as $b) $bossNames[] = $b['bossname'];
$bad = array();
foreach ($chs as $cid => $c) {
    $k = $cid - $CH0;
    if ($c['level_range'] !== array($LV0 + 50 * $k, $LV0 + 50 + 50 * $k)) $bad[] = "$cid:range";
    foreach ($c['quests'] as $q) {
        if ($q['type'] === 'reach_region' && $q['region'] !== $QY0 + $k) $bad[] = $q['id'];
        if ($q['type'] === 'kill_monster') foreach ($q['monster_ids'] as $m) if (!isset($gw[(string)$m]) || intval($gw[(string)$m]['glv']) <= $LV0 + 50 * $k) $bad[] = $q['id'];
        if ($q['type'] === 'kill_boss_name' && !in_array($q['boss_names'][0], $bossNames, true)) $bad[] = $q['id'];
        if ($q['type'] === 'collect_item') {
            // vật phẩm phải rơi từ quái của chính khu
            $drops = false;
            foreach ($ids as $id) { $g = $gw[(string)$id]; if (intval($g['glv']) > $LV0 + 50 * $k && intval($g['glv']) <= $LV0 + 50 + 50 * $k && in_array((string)$q['item_id'], explode(',', $g['gdj']), true)) $drops = true; }
            if (!$drops) $bad[] = $q['id'];
        }
        if ($q['type'] === 'breakthrough' && $q['target_level'] !== $LV0 + 50 + 50 * $k) $bad[] = $q['id'];
    }
}
tassert('Q4 mọi bước chương của làn trỏ đúng khu / quái / boss / vật phẩm rơi trong khu', $bad, array());
$npcs = json_decode(file_get_contents(__DIR__ . '/../class/config/npc_config.json'), true);
$rws = json_decode(file_get_contents(__DIR__ . '/../class/config/renwu_config.json'), true);
$mapIds = array(); foreach ($data['regions'] as $r) foreach ($r['maps'] as $m) $mapIds[] = intval($m['mid']);
$bad = array();
for ($nid = $NPC0; $nid <= $NPC0 + $K - 1; $nid++) {
    if (!isset($npcs[(string)$nid])) { $bad[] = "npc$nid"; continue; }
    foreach (explode(',', $npcs[(string)$nid]['taskid']) as $rid) {
        $rw = isset($rws[$rid]) ? $rws[$rid] : null;
        if (!$rw || !isset($gw[$rw['rwyq']]) || intval($gw[$rw['rwyq']]['id']) < $G0 || !in_array(intval($rw['rwqy']), $mapIds, true)) $bad[] = "rw$rid";
    }
}
tassert("Q4 $K NPC ({$NPC0}–" . ($NPC0 + $K - 1) . "), " . (3 * $K) . " nhiệm vụ trỏ quái + map Truyền tống của làn", $bad, array());
if (intval($have) > 0) {
    $placed = 0;
    foreach (json_decode(file_get_contents(__DIR__ . "/../sql/lan{$N}_c_data.json"), true)['npcs'] as $n) {
        $mn = $db->query("SELECT mnpc FROM mid WHERE mid = " . intval($n['mid']))->fetchColumn();
        if (in_array((string)$n['nid'], explode(',', $mn), true)) $placed++;
    }
    tassert("Q4 DB: $K NPC đã đặt ở map cửa vào khu (sau migrate_lan{$N}_c.php)", $placed, $K);
}

// ── Q3. EXP quái ≈ 400 quái cùng cấp mỗi cấp ──
$L2 = $LV0 + 2;
tassert("Q3 EXP quái cấp $L2 = 110 × {$L2}² ÷ 400", \player\guaiwu_exp($L2, 107), round(110.0 * $L2 * $L2 / 400));

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
