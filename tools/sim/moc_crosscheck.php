<?php
/**
 * Đối chiếu mô hình tools/sim/moc_online_sim.py với server thật (route game.php qua HTTP).
 * Dựng 27 nhân vật test (9 mốc giờ × ngày 1 / tuần 1 / tháng 1) đúng cấp / LT / MT mô phỏng ra,
 * đọc số game hiển thị (tỉ lệ + phí đột phá, thanh tu vi, linh khí/s, trần thể lực) rồi so với công thức mô hình.
 * --bt N : thêm N lần bấm đột phá THẬT ở 3 cấp mẫu (đo tỉ lệ thành công thực tế).
 * Chạy (server thử 127.0.0.1:8091 đang bật): php tools/sim/moc_crosscheck.php --bt 300
 * Dọn: php tools/sim/moc_crosscheck.php --clean
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(dirname(__DIR__));
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
include $ROOT . '/pdo.php';
$db = $dblj;
$enc = new \encode\encode();
$SIMDIR = __DIR__;
$BASE = 'http://127.0.0.1:8091/game.php?cmd=';

function mt_sid($h, $d) { return substr(md5('moctest_' . $h . '_' . $d), 0, 32); }
function mt_clean($db) {
    $db->exec("DELETE FROM game1 WHERE token LIKE 'moctest\\_%'");
}
if (in_array('--clean', $argv)) { mt_clean($db); echo "DA DON nhan vat test.\n"; exit(0); }
$bt = 0;
$i = array_search('--bt', $argv);
if ($i !== false && isset($argv[$i + 1])) $bt = intval($argv[$i + 1]);

function http_get($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30));
    $r = curl_exec($ch); curl_close($ch);
    return (string)$r;
}
function page($cmd) {
    global $enc, $BASE;
    return http_get($BASE . $enc->encode($cmd));
}

// --features: kiểm thật các cơ chế mới đưa vào mô hình (full=True) bằng 1 nhân vật test cấp 150
if (in_array('--features', $argv)) {
    mt_clean($db);
    $sid = mt_sid('feat', 150);
    $nn = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'game1' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
    $v = array();
    foreach ($nn as $c) $v[$c['COLUMN_NAME']] = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext')) ? '' : 0;
    $gc = \player\getgameconfig($db);
    $v = array_merge($v, array('token' => 'moctest_feat', 'sid' => $sid, 'uname' => 'MocTestFeat', 'ulv' => 150, 'uyxb' => 1000000, 'uczb' => 0,
        'uexp' => 0, 'uhp' => 5000, 'umaxhp' => 5000, 'ustamina' => 100, 'umaxstamina' => 100, 'ugj' => 2000, 'ufy' => 1500, 'uwx' => 0, 'usex' => 1,
        'vip' => 0, 'nowmid' => intval($gc->firstmid), 'endtime' => date('Y-m-d H:i:s'), 'sfzx' => 1, 'shenfen' => 1));
    $cols = array_keys($v);
    $db->prepare('INSERT INTO game1 (`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')')->execute(array_values($v));
    $uid = intval($db->query("SELECT uid FROM game1 WHERE sid = " . $db->quote($sid))->fetchColumn());
    $lt = function () use ($db, $sid) { \player\player_cache_clear($sid); return intval($db->query("SELECT uyxb FROM game1 WHERE sid = " . $db->quote($sid))->fetchColumn()); };
    echo "| Cơ chế | Mô hình | Đo thật trên server | Khớp |\n|---|---|---|---|\n";
    // 1) Bán Đột Phá Đan (djid 20/21/22) cho NPC = giá tiệm
    foreach (array(20 => 50000, 21 => 150000, 22 => 200000) as $dj => $price) {
        \player\adddj($sid, $dj, 3, $db);
        $a = $lt(); page("cmd=getbagdj&canshu=maichu1&djid=$dj&yeshu=1&uid=$uid&sid=$sid"); usleep(150000); $b = $lt();
        printf("| Bán 1 Đột Phá Đan djid %d | %d LT | %d LT | %s |\n", $dj, $price, $b - $a, ($b - $a) == $price ? 'OK' : 'LỆCH');
    }
    // 2) Boss Linh Căn thế giới hồi sinh ngay khi chết
    page("cmd=linhcan_boss&sid=$sid");
    $b1 = $db->query("SELECT id, level, maxhp FROM linhcan_boss WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $db->exec("UPDATE linhcan_boss SET hp = 0 WHERE id = " . intval($b1['id']));   // giả lập bị hạ (chỉ để đo)
    usleep(150000); page("cmd=linhcan_boss&sid=$sid");
    $b2 = $db->query("SELECT id, level, maxhp FROM linhcan_boss WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    printf("| Boss Linh Căn: boss mới ngay sau khi bị hạ | có | id %d → %d | %s |\n", $b1['id'], $b2['id'], ($b2 && $b2['id'] != $b1['id']) ? 'OK' : 'LỆCH');
    printf("| Boss Linh Căn: máu = 5000 + 500 × cấp | %d (cấp %d) | %d | %s |\n", 5000 + 500 * $b2['level'], $b2['level'], $b2['maxhp'], (5000 + 500 * $b2['level']) == $b2['maxhp'] ? 'OK' : 'LỆCH');
    // 3) Hầm Mỏ: 5 lượt miễn phí/ngày — đo LT trung bình / lượt + Cường Hóa Thạch qua 100 ngày (đặt lại bộ đếm ngày, chỉ để đo)
    $digs = 0; $gain = 0; $cht0 = 0;
    $chtq = function () use ($db, $sid) { $r = $db->query("SELECT djsum FROM playerdaoju WHERE sid = " . $db->quote($sid) . " AND djid = 1")->fetchColumn(); return intval($r); };
    $cht0 = $chtq();
    for ($i = 0; $i < 200; $i++) {
        $db->exec("UPDATE mine_player SET today_digs = 0 WHERE sid = " . $db->quote($sid));
        $a = $lt(); usleep(150000); page("cmd=hammo&canshu=dig5&sid=$sid"); $b = $lt();
        if ($b > $a) { $digs += 5; $gain += $b - $a; }
    }
    $mz = intval($db->query("SELECT uczb FROM game1 WHERE sid = " . $db->quote($sid))->fetchColumn());
    printf("| Hầm Mỏ: LT / lượt (cấp 150, b = 12) | %.1f | %.1f (%d lượt) | %s |\n", 96 + 0.465 * 12, $digs ? $gain / $digs : 0, $digs, ($digs && abs($gain / $digs - 101.6) < 12) ? 'OK' : 'LỆCH');
    printf("| Hầm Mỏ: Cường Hóa Thạch / lượt | 0.255 | %.3f | %s |\n", $digs ? ($chtq() - $cht0) / $digs : 0, ($digs && abs(($chtq() - $cht0) / $digs - 0.255) < 0.06) ? 'OK' : 'LỆCH');
    // bắt đầu với 0 MT: đào được đủ 500 lượt ⇒ 5 lượt/ngày miễn phí; MT còn lại là MT rơi từ mỏ (0,5% × 1 MT)
    printf("| Hầm Mỏ: 5 lượt/ngày miễn phí (bắt đầu 0 MT) | 1000/1000 lượt | %d/1000 lượt, MT rơi %d (kỳ vọng 5) | %s |\n", $digs, $mz, $digs == 1000 ? 'OK' : 'LỆCH');
    // 4) Vấn Đỉnh Đài: thưởng hạng 1 / ngày
    require_once $ROOT . '/class/function/vandinhdai_reward.php';
    $tb = function_exists("\\player\\vdd_daily_reward_table") ? \player\vdd_daily_reward_table() : \vdd_daily_reward_table();
    $r1 = isset($tb[1]) ? $tb[1] : reset($tb);
    printf("| Vấn Đỉnh Đài: thưởng hạng 1 | 10000 LT + 100 MT | %s | %s |\n", json_encode($r1), (strpos(json_encode($r1), '10000') !== false && strpos(json_encode($r1), '100') !== false) ? 'OK' : 'LỆCH');
    // 5) Huyết Thạch (112) không bán ở tiệm
    $shop = page("cmd=shangdian&kw=" . urlencode('Huyết Thạch') . "&sid=$sid");
    $shop = str_replace('value="Huyết Thạch"', '', $shop);   // bỏ chữ trong ô tìm kiếm, chỉ xét danh sách hàng
    printf("| Tiệm không bán Huyết Thạch (112) | không có | %s | %s |\n", strpos($shop, 'Huyết Thạch') === false ? 'không có' : 'CÓ', strpos($shop, 'Huyết Thạch') === false ? 'OK' : 'LỆCH');
    mt_clean($db);
    exit(0);
}

mt_clean($db);
$sim = json_decode(shell_exec('cd ' . escapeshellarg($SIMDIR) . ' && python3 moc_online_sim.py --json'), true);
$gc = \player\getgameconfig($db);
// Cột NOT NULL không default (giống tests/e2e_fixture.php) + giá trị khởi tạo y hệt cmd=cjplayer (game.php)
$nn = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'game1' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
$rows = array();
foreach ($sim as $h => $byDay) {
    foreach ($byDay as $d => $st) {
        $sid = mt_sid($h, $d);
        $v = array();
        foreach ($nn as $c) $v[$c['COLUMN_NAME']] = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext')) ? '' : 0;
        $v = array_merge($v, array('token' => 'moctest_' . $h . '_' . $d, 'sid' => $sid, 'uname' => 'MocTest' . $h . 'h_d' . $d,
            'ulv' => 1, 'uyxb' => 2000, 'uczb' => 100, 'uexp' => 0, 'uhp' => 35, 'umaxhp' => 35, 'ustamina' => 100, 'umaxstamina' => 100,
            'ugj' => 12, 'ufy' => 5, 'uwx' => 0, 'usex' => 1, 'vip' => 0, 'nowmid' => intval($gc->firstmid),
            'endtime' => date('Y-m-d H:i:s'), 'sfzx' => 1, 'shenfen' => 1));
        $cols = array_keys($v);
        $db->prepare('INSERT INTO game1 (`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')')
           ->execute(array_values($v));
        // Đưa về đúng trạng thái mô phỏng ở mốc này (chỉ để đo): cấp, LT, MT; thanh tu vi đầy để mở nút đột phá
        $L = intval($st['L']);
        $db->prepare('UPDATE game1 SET ulv = ?, uexp = ?, uyxb = ?, uczb = ? WHERE sid = ?')
           ->execute(array($L, ($L + 1) * ($L + 1) * 110, max(0, intval($st['lt'])), max(0, intval($st['mt'])), $sid));
        \player\player_cache_clear($sid);
        $p = \player\getplayer($sid, $db);
        $tp = page("cmd=tupo&sid=$sid");
        $xl = page("cmd=goxiulian&sid=$sid");
        // 1 lượt tu luyện thường thật: lùi mốc bắt đầu 31 phút rồi kết thúc (chỉ để đo) — kỳ vọng min(30 phút × cấp/s, thanh)
        $db->prepare('UPDATE game1 SET uexp = 0, sfxl = 1, xiuliantime = ? WHERE sid = ?')->execute(array(date('Y-m-d H:i:s', time() - 31 * 60), $sid));
        \player\player_cache_clear($sid);
        page("cmd=endxiulian&sid=$sid");
        $gain = intval($db->query("SELECT uexp FROM game1 WHERE sid = " . $db->quote($sid))->fetchColumn());
        preg_match('/break-ring-inner"><b>(\d+)%/', $tp, $mRate);
        preg_match('/Linh thạch<\/span><b>(\d+) \//', $tp, $mCost);
        preg_match('/Tu luyện<\/span><b>([\d.]+)\/s/', $xl, $mXl);
        $rows[] = array('h' => $h, 'd' => $d, 'L' => $L, 'rate' => isset($mRate[1]) ? intval($mRate[1]) : -1,
            'cost' => isset($mCost[1]) ? intval($mCost[1]) : -1, 'maxexp' => intval($p->umaxexp),
            'xl' => isset($mXl[1]) ? floatval($mXl[1]) : -1, 'stcap' => intval($p->umaxstamina)   // getplayer() đã trả trần thực = stamina_cap()
            , 'gain' => $gain, 'sid' => $sid);
    }
}
// Giá trị mô hình (econ_sim.py) cho đúng các cấp đó — trang bị trống, ngày thường
$levels = array_map(function ($r) { return $r['L']; }, $rows);
$exp = json_decode(shell_exec('cd ' . escapeshellarg($SIMDIR) . ' && python3 -c ' . escapeshellarg(
    'import json,sys; from econ_sim import rate,need,tupo_cost; L=json.loads(sys.argv[1]); print(json.dumps([[rate(x,0,0),tupo_cost(x),need(x)] for x in L]))'
) . ' ' . escapeshellarg(json_encode($levels))), true);

echo "| Giờ/ngày | Mốc | Cấp | Tỉ lệ ĐP game / mô hình | Phí ĐP game / mô hình | Thanh tu vi game / mô hình | Linh khí/s game | 1 lượt 30' thật / mô hình | Trần thể lực | Khớp |\n|---|---|---|---|---|---|---|---|---|---|\n";
$bad = 0;
foreach ($rows as $k => $r) {
    list($eRate, $eCost, $eNeed) = $exp[$k];
    $eGain = min(30 * 60 * $r['L'], $eNeed);
    $ok = ($r['gain'] == $eGain && $r['rate'] == $eRate && $r['cost'] == $eCost && $r['maxexp'] == $eNeed && abs($r['xl'] - $r['L']) < 0.01 && $r['stcap'] == 100 + $r['L'] - 1);
    if (!$ok) $bad++;
    printf("| %sh | ngày %s | %d | %d%% / %d%% | %d / %d | %d / %d | %s | %d / %d | %d | %s |\n", $r['h'], $r['d'], $r['L'], $r['rate'], $eRate,
        $r['cost'], $eCost, $r['maxexp'], $eNeed, $r['xl'], $r['gain'], $eGain, $r['stcap'], $ok ? 'OK' : 'LỆCH');
}
echo "\nLệch: $bad / " . count($rows) . "\n";

// Bấm đột phá thật N lần ở 3 cấp mẫu (mỗi lần trả lại đúng cấp + thanh đầy + đủ LT, chỉ để đo xác suất)
if ($bt > 0) {
    echo "\n| Cấp | Tỉ lệ hiển thị | Bấm thật | Thành công | Tỉ lệ đo |\n|---|---|---|---|---|\n";
    foreach (array(array('1', '1'), array('8', '7'), array('24', '30')) as $hd) {
        foreach ($rows as $r) { if ($r['h'] == $hd[0] && $r['d'] == $hd[1]) { $sid = $r['sid']; $L = $r['L']; $shown = $r['rate']; } }
        $ok = 0; $tried = 0;
        for ($n = 0; $n < $bt; $n++) {
            $db->prepare('UPDATE game1 SET ulv = ?, uexp = ?, uyxb = 999999999 WHERE sid = ?')->execute(array($L, ($L + 1) * ($L + 1) * 110, $sid));
            \player\player_cache_clear($sid);
            usleep(120000);   // tránh anti-flood của game.php (lần bấm bị chặn không trừ LT)
            page("cmd=tupo&canshu=tupo&sid=$sid");
            $after = $db->query("SELECT ulv, uyxb FROM game1 WHERE sid = " . $db->quote($sid))->fetch(PDO::FETCH_ASSOC);
            if (intval($after['uyxb']) == 999999999) continue;   // không thực sự bấm
            $tried++;
            if (intval($after['ulv']) == $L + 1) $ok++;
        }
        printf("| %d | %d%% | %d | %d | %.1f%% |\n", $L, $shown, $tried, $ok, $tried ? 100 * $ok / $tried : 0);
    }
}
