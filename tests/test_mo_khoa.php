<?php
/**
 * test_mo_khoa.php — Mở tính năng theo cấp (docs/MO_KHOA_TINH_NANG.md): cấu hình, chặn lệnh, lọc nhiệm vụ ngày/tuần/tháng,
 * thứ tự chính tuyến chương 1–3. Phần nhiệm vụ CẦN DB (dùng nhân vật e2e, trả lại cấp cũ sau khi chạy).
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_mo_khoa.php
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
function fake_player($lv) { $p = new \player\player(); $p->ulv = $lv; return $p; }

// ── K1. Cấu hình: mọi lệnh khai báo phải có trong router game.php, không lệnh nào thuộc 2 tính năng ──
$router = file_get_contents(__DIR__ . '/../game.php');
$seen = array(); $missing = array(); $dup = array();
foreach (\player\feature_unlock_config() as $key => $f) {
    foreach ($f['cmds'] as $c) {
        if (!preg_match("/'" . preg_quote($c, '/') . "'\s*=>|case '" . preg_quote($c, '/') . "'/", $router)) $missing[] = $c;
        if (isset($seen[$c])) $dup[] = $c;
        $seen[$c] = $key;
    }
}
tassert('K1 lệnh khai báo đều có trong router', $missing, array());
tassert('K1 không lệnh nào thuộc 2 tính năng', $dup, array());
$core = array('goxiulian', 'startxiulian', 'endxiulian', 'tupo', 'gomid', 'pve', 'pvegj', 'trangbi', 'zhuangtai',
              'getbagdj', 'getbagyp', 'usedaoju', 'nhiemvu', 'mytask', 'qydt', 'npc', 'boss', 'pvbgj', 'upzb');
$coreLocked = array();
foreach ($core as $c) if (\player\feature_key_for_cmd($c) !== '') $coreLocked[] = $c;
tassert('K1 lệnh lõi (tu luyện, đột phá, đánh quái, trang bị, nhiệm vụ) không bị khóa', $coreLocked, array());

// ── K2. Chặn / mở theo cấp ──
tassert('K2 cấp 1: Luyện Đan khóa', (bool)\player\feature_lock_for_cmd(fake_player(1), 'luonden'), true);
tassert('K2 cấp 14: Luyện Đan khóa', (bool)\player\feature_lock_for_cmd(fake_player(14), 'do_craft_luonden'), true);
tassert('K2 cấp 15: Luyện Đan mở', \player\feature_lock_for_cmd(fake_player(15), 'luonden'), false);
tassert('K2 cấp 24: Chế Tạo khóa', (bool)\player\feature_lock_for_cmd(fake_player(24), 'chetao'), true);
tassert('K2 cấp 25: Chế Tạo mở', \player\feature_lock_for_cmd(fake_player(25), 'chetao'), false);
tassert('K2 cấp 1: tu luyện luôn mở', \player\feature_lock_for_cmd(fake_player(1), 'goxiulian'), false);
tassert('K2 lệnh lạ không khóa', \player\feature_lock_for_cmd(fake_player(1), 'khong_ton_tai'), false);
$lk = \player\feature_lock_for_cmd(fake_player(1), 'chongwu');
tassert('K2 trả về tên + cấp mở', array($lk['name'], $lk['level']), array('Linh Sủng', 8));
$next = \player\feature_next_locked(9, 2);
tassert('K2 2 tính năng sắp mở ở cấp 9', array($next[0]['level'], $next[1]['level']), array(10, 10));

// ── K3. Loại nhiệm vụ ──
tassert('K3 cấp 12: nhiệm vụ Luyện Đan chưa làm được', \player\feature_quest_type_open(12, 'alchemy'), false);
tassert('K3 cấp 24: nhiệm vụ Chế Tạo chưa làm được', \player\feature_quest_type_open(24, 'craft'), false);
tassert('K3 cấp 79: nhiệm vụ PvP chưa làm được', \player\feature_quest_type_open(79, 'pvp'), false);
tassert('K3 cấp 1: diệt quái luôn làm được', \player\feature_quest_type_open(1, 'kill'), true);

// ── K4. Chính tuyến chương 1–3: không bước nào cần tính năng mở muộn hơn chương ──
$need = array('alchemy_count' => 'luonden', 'craft_count' => 'chetao', 'craft_rarity' => 'chetao', 'learn_book' => 'thiencoccac', 'conghuong' => 'conghuong');
$bad = array();
$mc = \player\nhiemvu_main_config();
foreach ($mc['chapters'] as $ch) {
    $hi = isset($ch['level_range'][1]) ? intval($ch['level_range'][1]) : 9999;
    foreach ($ch['quests'] as $q) {
        if (isset($need[$q['type']])) {
            $f = \player\feature_get($need[$q['type']]);
            if ($f['level'] > $hi) $bad[] = $q['id'] . ' (' . $q['name'] . ' cần cấp ' . $f['level'] . ', chương tới ' . $hi . ')';
        }
    }
}
tassert('K4 không bước chính tuyến nào cần tính năng mở sau chương', $bad, array());

// ── K5. Nhiệm vụ ngày / tuần / tháng theo cấp (DB) ──
$sid = str_repeat('e2', 16);
$row = $db->query("SELECT ulv FROM game1 WHERE sid = '$sid'")->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "SKIP  K5 (không có nhân vật e2e — chạy php tests/e2e_fixture.php)\n";
} else {
    $oldLv = intval($row['ulv']);
    $types = function($list) { $t = array(); foreach ($list as $q) $t[] = $q['type']; return $t; };
    $setLv = function($lv) use ($db, $sid) {
        $db->prepare("UPDATE game1 SET ulv = ? WHERE sid = ?")->execute(array($lv, $sid));
        \player\player_cache_clear($sid);
    };

    $setLv(12);
    $db->prepare("DELETE FROM player_nhiemvu_daily WHERE sid = ?")->execute(array($sid));
    $d = $types(\player\nhiemvu_daily_get($sid, $db));
    tassert('K5 cấp 12: nhiệm vụ ngày không có Luyện Đan / Chế Tạo / PvP',
        array_values(array_intersect($d, array('alchemy', 'craft', 'pvp'))), array());
    tassert('K5 cấp 12: vẫn có nhiệm vụ ngày', count($d) > 0, true);
    $w = \player\nhiemvu_weekly_get($sid, $db);
    tassert('K5 cấp 12: nhiệm vụ tuần ẩn Luyện Đan / Chế Tạo',
        array_values(array_intersect($types($w), array('alchemy', 'craft'))), array());
    $tg = 0; foreach ($w as $q) if ($q['type'] === 'complete_all') $tg = $q['target'];
    tassert('K5 cấp 12: Tổng Hợp tuần = số nhiệm vụ đang làm được (6)', $tg, 6);

    $setLv(30);
    $db->prepare("DELETE FROM player_nhiemvu_daily WHERE sid = ?")->execute(array($sid));
    $w = \player\nhiemvu_weekly_get($sid, $db);
    $tg = 0; foreach ($w as $q) if ($q['type'] === 'complete_all') $tg = $q['target'];
    tassert('K5 cấp 30: Tổng Hợp tuần = 9 như cấu hình', $tg, 9);
    $m = \player\nhiemvu_monthly_get($sid, $db);
    tassert('K5 cấp 30: đủ 5 nhiệm vụ tháng', count($m), 5);

    $db->prepare("DELETE FROM player_nhiemvu_daily WHERE sid = ?")->execute(array($sid));
    $setLv($oldLv);
}

// ── K6. Cơ Duyên ──
$cfg = \player\nhiemvu_coduyen_config();
$bad = array(); $ids = array();
foreach ($cfg as $q) {
    if (strlen($q['id']) > 20 || isset($ids[$q['id']]) || !\player\feature_get($q['feature'])) $bad[] = $q['id'];
    $ids[$q['id']] = 1;
}
tassert('K6 cấu hình: mã ≤ 20 ký tự (cột quest_id), không trùng, tính năng tồn tại', $bad, array());
tassert('K6 mỗi tính năng mở theo cấp có 1 nhiệm vụ Cơ Duyên', count($cfg), count(\player\feature_unlock_config()));
$row = $db->query("SELECT ulv, uexp, uyxb FROM game1 WHERE sid = '$sid'")->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $_SESSION = array();
    $reset = function() use ($db, $sid) { $db->prepare("DELETE FROM player_nhiemvu_main WHERE sid = ? AND quest_id LIKE 'cd\\_%'")->execute(array($sid)); $_SESSION = array(); };
    $st = function($id) use ($db, $sid) { $q = $db->prepare("SELECT status FROM player_nhiemvu_main WHERE sid = ? AND quest_id = ?"); $q->execute(array($sid, $id)); return intval($q->fetchColumn()); };
    $exp = function() use ($db, $sid) { return intval($db->query("SELECT uexp FROM game1 WHERE sid = '$sid'")->fetchColumn()); };
    $setLv = function($lv) use ($db, $sid) { $db->prepare("UPDATE game1 SET ulv = ?, uexp = 0 WHERE sid = ?")->execute(array($lv, $sid)); \player\player_cache_clear($sid); };
    $reset();
    $setLv(2);
    \player\nhiemvu_coduyen_event($sid, 'shop_buy', $db);
    tassert('K6 cấp 2 (Cửa Hàng chưa mở): mua không tính', $st('cd_shangdian'), 0);
    $setLv(3);
    $m = \player\nhiemvu_coduyen_event($sid, 'shop_buy', $db);
    tassert('K6 cấp 3: mua lần đầu -> hoàn thành', $st('cd_shangdian'), 2);
    tassert('K6 có thông báo hoàn thành', count($m) === 1 && strpos($m[0], 'Lần Đầu Vào Tiệm') !== false, true);
    $e1 = $exp();
    tassert('K6 nhận tu vi thưởng', $e1 > 0, true);
    $_SESSION = array(); // phiên mới: vẫn không nhận lần 2 (đọc trạng thái từ DB)
    \player\nhiemvu_coduyen_event($sid, 'shop_buy', $db);
    tassert('K6 mua lần 2 không thưởng lại', $exp(), $e1);
    $setLv(5);
    \player\nhiemvu_coduyen_event($sid, 'visit:xephang', $db);
    tassert('K6 ghé tính năng chưa mở (Xếp Hạng cấp 20) không tính', $st('cd_xephang'), 0);
    \player\nhiemvu_coduyen_event($sid, 'visit:allmap', $db);
    tassert('K6 ghé Dịch Chuyển ở cấp 5 -> hoàn thành', $st('cd_allmap'), 2);
    tassert('K6 ghé tính năng không làm xong nhiệm vụ tính năng khác', $st('cd_chongwu'), 0);
    $list = \player\nhiemvu_coduyen_get($sid, 5, $db);
    $keys = array(); foreach ($list as $q) $keys[] = $q['id'];
    tassert('K6 danh sách cấp 5 = 2 tính năng đã mở', $keys, array('cd_shangdian', 'cd_allmap'));
    $reset();
    $db->prepare("UPDATE game1 SET ulv = ?, uexp = ?, uyxb = ? WHERE sid = ?")->execute(array($row['ulv'], $row['uexp'], $row['uyxb'], $sid));
    \player\player_cache_clear($sid);
}

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
