<?php
/**
 * test_e2e_full.php — Cho 1 nhân vật test MỚI đi qua toàn bộ tính năng bằng route thật game.php (HTTP)
 * và ghi nhận lỗi. Mỗi bước kiểm: HTTP 200, không có "Fatal error / Warning / Notice / Uncaught"
 * trong HTML, log PHP của bản chạy không có Fatal / Uncaught / lỗi SQL (SQLSTATE), và trạng thái DB đổi đúng.
 *
 * Cần server thử: php -S 127.0.0.1:8091 -t /tmp/xxjrun  (bash tools/cloud/sync_run.sh trước đó).
 *   E2E_BASE (mặc định http://127.0.0.1:8091), E2E_LOG (mặc định /tmp/xxjrun/php_error.log).
 *
 * Chạy:  php tests/test_e2e_full.php            chạy toàn bộ, dọn nhân vật test khi xong
 *        php tests/test_e2e_full.php --keep     giữ nhân vật test để soi
 *        php tests/test_e2e_full.php --clean    chỉ dọn nhân vật test
 *        php tests/test_e2e_full.php --relax    tự bật server phụ 127.0.0.1:8092 với sql_mode KHÔNG strict
 *                                               (auto_prepend tests/e2e_relax_prepend.php) — giả lập MySQL
 *                                               production không bật STRICT_TRANS_TABLES, để đi tiếp các
 *                                               luồng bị chặn bởi lỗi "INSERT thiếu cột NOT NULL".
 * Không đụng nhân vật thật (uid 547) / nhân vật test khác (990125, 987650, 543). Boss thế giới bị đánh
 * được chụp lại trước và khôi phục sau. PHP 5.6 tương thích.
 */
$RELAX = in_array('--relax', $argv);
$relaxProc = null;
if ($RELAX) {
    // Server phụ: cùng mã /tmp/xxjrun, kết nối PDO persistent bị đặt sql_mode không strict ngay khi mở.
    putenv('E2E_BASE=http://127.0.0.1:8092');
    $pre = __DIR__ . '/e2e_relax_prepend.php';
    $cmdSrv = 'exec php -d auto_prepend_file=' . escapeshellarg($pre) . ' -S 127.0.0.1:8092 -t /tmp/xxjrun';
    $relaxProc = proc_open($cmdSrv, array(0 => array('file', '/dev/null', 'r'), 1 => array('file', '/dev/null', 'w'), 2 => array('file', '/dev/null', 'w')), $pipesSrv);
    usleep(800000);
}
require_once __DIR__ . '/e2e_full_lib.php';
$db = $dblj;
if ($RELAX) { $db->exec("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'"); }

$UNAME  = 'E2EFull';
$TOKEN  = 'e2efull' . md5('main-token-2026-10-29');
$SID    = e2e_sid_for($UNAME, $TOKEN);
$UNAME2 = 'E2EFull2';
$TOKEN2 = 'e2efull' . md5('opp-token-2026-10-29');
$SID2   = e2e_sid_for($UNAME2, $TOKEN2);

if (in_array('--clean', $argv)) {
    $n = e2e_cleanup($db, $SID) + e2e_cleanup($db, $SID2);
    $db->prepare("DELETE FROM userinfo WHERE token IN (?, ?)")->execute(array($TOKEN, $TOKEN2)); // tài khoản test
    $db->prepare("DELETE FROM arena_log WHERE attacker_sid IN (?, ?) OR defender_sid IN (?, ?)")->execute(array($SID, $SID2, $SID, $SID2));
    echo "Đã dọn $n dòng.\n";
    exit(0);
}

// ============================ KHUNG TEST ============================
$RESULTS = array();      // [mục, tính năng, PASS/FAIL, chi tiết]
$WARNINGS = array();     // cảnh báo PHP trong log (không tính FAIL), gom theo nội dung
$CUR = '';               // tính năng đang test
$fails = 0;

function feat($name) { global $CUR; $CUR = $name; echo "\n## $name\n"; }

function chk($name, $ok, $detail) {
    global $RESULTS, $CUR, $fails;
    if (!$ok) $fails++;
    $RESULTS[] = array($name, $CUR, $ok ? 'PASS' : 'FAIL', $detail);
    echo ($ok ? 'PASS  ' : 'FAIL  ') . $name . ($detail !== '' ? '  | ' . $detail : '') . "\n";
    return $ok;
}

/** Làm mới phiên (game1.endtime) để không bị "Offline thời gian quá dài". */
function fresh($sid) {
    global $db;
    $db->prepare("UPDATE game1 SET endtime = NOW(), sfzx = 1 WHERE sid = ?")->execute(array($sid));
    \player\player_cache_clear($sid);
}

/**
 * Gọi route + kiểm tra chung. Trả body (chuỗi). $mustHave = chuỗi phải có trong HTML (hoặc '').
 */
function go($name, $plain, $post, $mustHave) {
    global $WARNINGS, $SID, $SID2;
    fresh(strpos($plain, $SID2) !== false ? $SID2 : $SID);
    list($code, $body, $log) = e2e_http($plain, $post);
    $probs = array();
    $isRedirect = in_array($code, array(301, 302, 303), true) && $GLOBALS['E2E_LAST_REDIRECT'] !== '';
    if ($code !== 200 && !$isRedirect) $probs[] = "HTTP $code";
    if (!$isRedirect && strlen($body) < 200) $probs[] = 'body rỗng/ngắn (' . strlen($body) . 'B)';
    if (preg_match('/(Fatal error|Uncaught|Parse error|<b>Warning<\/b>|<b>Notice<\/b>|Warning: |Notice: )/', $body, $m)) $probs[] = 'HTML có "' . $m[1] . '"';
    if (strpos($body, 'Offline thời gian quá dài') !== false) $probs[] = 'bị đá về đăng nhập';
    if (strpos($body, 'fk-lock-title') !== false) $probs[] = 'trang 🔒 tính năng chưa mở';
    if ($mustHave !== '' && strpos($body, $mustHave) === false) $probs[] = 'thiếu nội dung "' . $mustHave . '"';
    foreach ($log as $ln) {
        $msg = preg_replace('/^\[[^\]]+\]\s*/', '', $ln);
        if (preg_match('/(Fatal|Uncaught|SQLSTATE)/', $msg)) {
            $probs[] = 'log: ' . substr(str_replace('/tmp/xxjrun/', '', $msg), 0, 220);
        } elseif (strpos($msg, 'PHP Warning') !== false || strpos($msg, 'PHP Notice') !== false) {
            $k = substr(str_replace('/tmp/xxjrun/', '', $msg), 0, 240);
            if (!isset($WARNINGS[$k])) $WARNINGS[$k] = array();
            $WARNINGS[$k][$name] = 1;
        }
    }
    chk($name, empty($probs), implode('; ', array_unique($probs)));
    return $body;
}

function row($sid = null) { global $db, $SID; return e2e_row($db, $sid === null ? $SID : $sid); }
function q1($sql, $args) { global $db; $st = $db->prepare($sql); $st->execute($args); return $st->fetchColumn(); }
function dj($djid, $sid = null) { global $db, $SID; return e2e_dj($db, $sid === null ? $SID : $sid, $djid); }
function setdj($djid, $n, $sid = null, $uid = null) { global $db, $SID, $UID; e2e_set_dj($db, $sid === null ? $SID : $sid, $uid === null ? $UID : $uid, $djid, $n); }
function setcol($col, $val, $sid = null) { global $db, $SID; $db->prepare("UPDATE game1 SET `$col` = ? WHERE sid = ?")->execute(array($val, $sid === null ? $SID : $sid)); \player\player_cache_clear(); }
// Chỉ số getplayer() đọc ở tiến trình PHP mới (tránh cache tĩnh trong tiến trình test)
function pl($sid = null) { global $SID; return e2e_player_fresh($sid === null ? $SID : $sid); }

/** Chạy 1 khối test; ngoại lệ PHP trong chính test => FAIL (không dừng cả bộ). */
function block($name, $fn) {
    feat($name);
    try { call_user_func($fn); }
    catch (Exception $e) { chk("$name: ngoại lệ trong test", false, $e->getMessage()); }
    catch (Throwable $e) { chk("$name: lỗi PHP trong test", false, $e->getMessage() . ' @' . $e->getLine()); } // PHP 7+ (PHP 5.6 bỏ qua)
}

// ============================ CHUẨN BỊ ============================
$up = intval(@file_get_contents(getenv('E2E_BASE') ? getenv('E2E_BASE') : 'http://127.0.0.1:8091') !== false);
echo "Server: " . $E2E_BASE . ($RELAX ? ' (relax sql_mode)' : ' (sql_mode của DB: ' . $db->query('SELECT @@GLOBAL.sql_mode')->fetchColumn() . ')') . "\n";
e2e_cleanup($db, $SID);
e2e_cleanup($db, $SID2);
// Tạo nhân vật cần token thuộc tài khoản đã đăng ký (userinfo) — tạo tài khoản test trước
$db->prepare("DELETE FROM userinfo WHERE token IN (?, ?)")->execute(array($TOKEN, $TOKEN2));
$db->prepare("INSERT INTO userinfo (username, userpass, token) VALUES (?, ?, ?)")->execute(array('e2efull_acc', 'x', $TOKEN));
$db->prepare("DELETE FROM arena_log WHERE attacker_sid IN (?, ?) OR defender_sid IN (?, ?)")->execute(array($SID, $SID2, $SID, $SID2));
$UID = 0; $UID2 = 0;

// ============================ 1. TẠO NHÂN VẬT (route thật) ============================
block('Tạo nhân vật', function () {
    global $db, $SID, $UNAME, $TOKEN, $UID;
    // Token tự đặt (không có trong userinfo) phải bị từ chối
    $fake = 'e2efake' . md5('khong-co-tai-khoan');
    list($fc, $fbody) = e2e_http_raw('cmd=cjplayer&token=' . $fake . '&username=e2efake01&sex=1&shenfen=1');
    chk('cjplayer từ chối token không thuộc tài khoản nào', q1("SELECT COUNT(*) FROM game1 WHERE token = ?", array($fake)) == 0
        && strpos($fbody, 'Hoan Nghênh') === false, '');
    list($code, $body, $log) = e2e_http_raw('cmd=cjplayer&token=' . $TOKEN . '&username=' . $UNAME . '&sex=1&shenfen=1');
    $r = row();
    $sqlErr = '';
    foreach ($log as $ln) { if (strpos($ln, 'SQLSTATE') !== false) { $sqlErr = substr(str_replace('/tmp/xxjrun/', '', $ln), 26, 200); break; } }
    chk('cjplayer tạo được dòng game1', $r !== false, $r ? '' : 'không có dòng game1; ' . $sqlErr);
    chk('cjplayer không báo "Hoan Nghênh" khi INSERT thất bại', $r !== false || strpos($body, 'Hoan Nghênh') === false,
        $r ? '' : 'trang vẫn in lời chào dù không tạo được nhân vật');
    if (!$r) {
        // Dựng thay bằng INSERT đủ cột (cùng giá trị khởi tạo cjplayer) để đi tiếp
        e2e_insert_game1($db, $SID, $TOKEN, $UNAME, array());
        $r = row();
    }
    $UID = intval($r['uid']);
    chk('chỉ số khởi tạo: cấp 1, 35 HP, 12 công, 5 thủ, 2000 LT, 100 MT',
        intval($r['ulv']) === 1 && intval($r['umaxhp']) === 35 && intval($r['ugj']) === 12 && intval($r['ufy']) === 5
        && intval($r['uyxb']) === 2000 && intval($r['uczb']) === 100, "uid=$UID");
    // login: chỉ đặt endtime + header refresh sang Đại sảnh (không có thân trang)
    $db->prepare("UPDATE game1 SET endtime = DATE_SUB(NOW(), INTERVAL 30 MINUTE) WHERE sid = ?")->execute(array($SID));
    list($c, $b, $l) = e2e_http("cmd=login&sid=$SID", null);
    $bad = '';
    foreach ($l as $ln) { if (preg_match('/(Fatal|Uncaught|SQLSTATE)/', $ln)) $bad = $ln; }
    chk('Đăng nhập (cmd=login): 200, làm mới endtime, không lỗi', $c === 200 && $bad === ''
        && strtotime(row()['endtime']) >= time() - 5, $bad);
});

// ============================ 2. KHÓA TÍNH NĂNG THEO CẤP (cấp 1) ============================
block('Mở khóa tính năng theo cấp', function () {
    global $SID;
    fresh($SID);
    list($c, $b, $l) = e2e_http("cmd=shangdian&sid=$SID", null);
    chk('cấp 1: Cửa Hàng (cấp 3) hiện trang 🔒', strpos($b, 'fk-lock-title') !== false, '');
    list($c, $b, $l) = e2e_http("cmd=thechat&sid=$SID", null);
    chk('cấp 1: Thể Chất (cấp 120) hiện trang 🔒', strpos($b, 'fk-lock-title') !== false, '');
    go('cấp 1: Đại sảnh vẫn vào được', "cmd=gomid&newmid=225&sid=$SID", null, 'Thôn Quảng Tràng');
});

// ============================ 3. TU LUYỆN + ĐỘT PHÁ (cấp thấp) ============================
block('Tu luyện', function () {
    global $db, $SID;
    go('trang Tu Luyện', "cmd=goxiulian&sid=$SID", null, '');
    $r0 = row();
    go('bắt đầu tu luyện thường', "cmd=startxiulian&canshu=1&sid=$SID", null, '');
    chk('sfxl = 1 sau khi bắt đầu', intval(row()['sfxl']) === 1, 'sfxl=' . row()['sfxl']);
    // lùi mốc 10 phút rồi kết thúc
    $db->prepare("UPDATE game1 SET xiuliantime = DATE_SUB(NOW(), INTERVAL 600 SECOND) WHERE sid = ?")->execute(array($SID));
    go('kết thúc tu luyện', "cmd=endxiulian&sid=$SID", null, '');
    $r1 = row();
    chk('nhận tu vi > 0 và sfxl về 0', intval($r1['uexp']) > intval($r0['uexp']) && intval($r1['sfxl']) === 0,
        'uexp ' . $r0['uexp'] . ' -> ' . $r1['uexp']);
    // Tu luyện Linh Thạch x2: trừ round(phút × giá/phút); cấp 1 giá = 1 LT/phút
    $db->prepare("UPDATE game1 SET uexp = 0 WHERE sid = ?")->execute(array($SID));
    go('bắt đầu tu luyện Linh Thạch x2', "cmd=startxiulian&canshu=2&sid=$SID", null, '');
    $db->prepare("UPDATE game1 SET xiuliantime = DATE_SUB(NOW(), INTERVAL 600 SECOND) WHERE sid = ?")->execute(array($SID));
    $lt0 = intval(row()['uyxb']);
    $b = go('kết thúc tu luyện x2', "cmd=endxiulian&sid=$SID", null, '');
    $lt1 = intval(row()['uyxb']);
    chk('trừ Linh Thạch = round(phút × 1)', $lt0 - $lt1 === 10, "trừ " . ($lt0 - $lt1));
    if (preg_match('/Tiêu hao: (\d+) Linh thạch/u', $b, $m)) {
        chk('số Linh Thạch hiển thị "Tiêu hao" = số thật bị trừ', intval($m[1]) === $lt0 - $lt1, 'hiển thị ' . $m[1] . ', trừ ' . ($lt0 - $lt1));
    }
    // Phút lẻ: tiền trừ = round(phút) nhưng chữ "Tiêu hao" = ceil(phút) — 70 giây => trừ 1, hiển thị 2
    $db->prepare("UPDATE game1 SET uexp = 0 WHERE sid = ?")->execute(array($SID)); // tu vi đầy thì game.php đổi start -> end
    go('bắt đầu tu luyện x2 (70 giây)', "cmd=startxiulian&canshu=2&sid=$SID", null, '');
    $db->prepare("UPDATE game1 SET xiuliantime = DATE_SUB(NOW(), INTERVAL 70 SECOND) WHERE sid = ?")->execute(array($SID));
    $lt0 = intval(row()['uyxb']);
    $b = go('kết thúc tu luyện x2 sau 70 giây', "cmd=endxiulian&sid=$SID", null, '');
    $lt1 = intval(row()['uyxb']);
    $shown = preg_match('/Tiêu hao: (\d+) Linh thạch/u', $b, $m) ? intval($m[1]) : -1;
    chk('70 giây: "Tiêu hao" hiển thị = số Linh Thạch thật bị trừ', $shown === $lt0 - $lt1, 'hiển thị ' . $shown . ', trừ ' . ($lt0 - $lt1));
});

block('Đột phá', function () {
    global $db, $SID;
    $okOnce = false; $tries = 0; $detail = '';
    while (!$okOnce && $tries < 8) {
        $tries++;
        $p = pl();
        $db->prepare("UPDATE game1 SET uexp = ?, uyxb = 100000 WHERE sid = ?")->execute(array(intval($p->umaxexp), $SID));
        $r0 = row();
        $b = go("đột phá lần $tries", "cmd=tupo&canshu=tupo&sid=$SID", null, '');
        $r1 = row();
        if (intval($r1['ulv']) === intval($r0['ulv']) + 1) {
            $okOnce = true;
            $lv = intval($r0['ulv']);
            $dHp = intval($r1['umaxhp']) - intval($r0['umaxhp']);
            $dGj = intval($r1['ugj']) - intval($r0['ugj']);
            $dFy = intval($r1['ufy']) - intval($r0['ufy']);
            // upplayerlv (bảng cấp 1-49: HP 30, Công/Thủ 2.5) + cộng cố định 4+lv/1.2, 2+lv/2.5, 3+lv/2
            $eHp = 30 + 4 + round($lv / 1.2);
            chk('đột phá thành công: HP +' . $eHp . ', Công +' . (2.5 + 2 + round($lv / 2.5)) . '±0.5, Thủ +' . (2.5 + 3 + round($lv / 2)) . '±0.5',
                $dHp === intval($eHp) && abs($dGj - (2.5 + 2 + round($lv / 2.5))) <= 0.5 && abs($dFy - (2.5 + 3 + round($lv / 2))) <= 0.5,
                "HP +$dHp Công +$dGj Thủ +$dFy, tf +" . (intval($r1['tf']) - intval($r0['tf'])));
        }
    }
    chk("đột phá thành công trong $tries lần (tỉ lệ cấp thấp ~70%)", $okOnce, '');
});

// ============================ 4. LÊN CẤP 130 + CẤP TÀI NGUYÊN ============================
block('Chuẩn bị nhân vật cấp 130', function () {
    global $db, $SID, $UID;
    e2e_level_to($db, $SID, 130);
    $db->prepare("UPDATE game1 SET uyxb = 50000000, uczb = 5000000, uexp = 0, uhp = 999999, ustamina = 300, vip = 0 WHERE sid = ?")->execute(array($SID));
    $p = pl();
    chk('nhân vật cấp 130', intval($p->ulv) === 130, 'cảnh giới ' . $p->jingjieValue . ', HP max ' . $p->umaxhp);
    // Cơ Duyên (nhiệm vụ hướng dẫn) — kiểm 1 nhiệm vụ "ghé trang", rồi đánh dấu xong hết để thưởng
    // của chúng không lẫn vào phép đo tiền ở các bước sau.
    $lt0 = intval(row()['uyxb']);
    go('Cơ Duyên: lần đầu mở Dịch Chuyển (allmap)', "cmd=allmap&sid=$SID", null, '');
    chk('Cơ Duyên cd_allmap hoàn thành + thưởng Linh thạch', intval(q1("SELECT status FROM player_nhiemvu_main WHERE sid = ? AND quest_id = 'cd_allmap'", array($SID))) === 2
        && intval(row()['uyxb']) > $lt0, 'LT +' . (intval(row()['uyxb']) - $lt0));
    $cd = json_decode(file_get_contents(dirname(__DIR__) . '/class/config/nhiemvu_coduyen_config.json'), true);
    foreach ($cd['quests'] as $q) {
        $db->prepare("INSERT INTO player_nhiemvu_main (sid, quest_id, status, progress) VALUES (?, ?, 2, 1) ON DUPLICATE KEY UPDATE status = 2")->execute(array($SID, $q['id']));
    }
    $db->prepare("UPDATE game1 SET uyxb = 50000000 WHERE sid = ?")->execute(array($SID));
    go('Trạng thái (zhuangtai)', "cmd=zhuangtai&sid=$SID", null, '');
    go('Đại sảnh cấp 130', "cmd=gomid&newmid=225&sid=$SID", null, '');
});

// ============================ 5. CỬA HÀNG ============================
block('Cửa hàng', function () {
    global $db, $SID, $UID;
    go('trang Cửa Hàng', "cmd=shangdian&sid=$SID", null, '');
    $yd = \player\getyaodanonce(1, $db);
    $lt0 = intval(row()['uyxb']);
    $n0 = intval(q1("SELECT COALESCE(SUM(ydsum),0) FROM playeryaodan WHERE sid = ? AND ydid = 1", array($SID)));
    go('mua 1 Đan dược (Linh thạch)', "cmd=shangdian&canshu=gogoumai&ydcount=1&ydid=1&p=1&sid=$SID", null, '');
    $n1 = intval(q1("SELECT COALESCE(SUM(ydsum),0) FROM playeryaodan WHERE sid = ? AND ydid = 1", array($SID)));
    chk('đan dược: trừ đúng giá ' . $yd->ydjg . ' LT, +1 viên', $lt0 - intval(row()['uyxb']) === intval($yd->ydjg) && $n1 === $n0 + 1,
        'trừ ' . ($lt0 - intval(row()['uyxb'])) . ', số viên ' . $n0 . '->' . $n1);
    // Đạo cụ (Linh thạch) — đạo cụ 1 (Cường hóa thạch): chưa có dòng => INSERT mới
    $d = \player\getdaoju(1, $db);
    $price = max(50, intval($d->djyxb));
    $lt0 = intval(row()['uyxb']);
    go('mua 1 Đạo cụ mới (Cường hóa thạch)', "cmd=shangdian&canshu2=gogoumai2&buy=1&djid=1&p=1&sid=$SID", null, '');
    chk('đạo cụ mới: trừ ' . $price . ' LT và +1 vào túi', $lt0 - intval(row()['uyxb']) === $price && dj(1) === 1,
        'trừ ' . ($lt0 - intval(row()['uyxb'])) . ', túi có ' . dj(1));
    // Trang bị (Linh thạch): zbid 1 (rarity 1 => 100 LT)
    $lt0 = intval(row()['uyxb']);
    $z0 = intval(q1("SELECT COUNT(*) FROM playerzhuangbei WHERE sid = ?", array($SID)));
    go('mua 1 Trang bị (zbid 1)', "cmd=shangdian&canshu3=gogoumai3&buy=1&zbid=1&p=1&sid=$SID", null, '');
    $z1 = intval(q1("SELECT COUNT(*) FROM playerzhuangbei WHERE sid = ?", array($SID)));
    chk('trang bị: trừ 100 LT và +1 món vào túi', $lt0 - intval(row()['uyxb']) === 100 && $z1 === $z0 + 1,
        'trừ ' . ($lt0 - intval(row()['uyxb'])) . ', số món ' . $z0 . '->' . $z1);
    // Không được mua trang bị cấp > 30 qua shop (chặn cả gọi thẳng URL)
    $lt0 = intval(row()['uyxb']);
    go('mua trang bị cấp cao bị chặn', "cmd=shangdian&canshu3=gogoumai3&buy=1&zbid=95&p=1&sid=$SID", null, '');
    chk('trang bị cấp > 30 không bán, không trừ tiền', $lt0 === intval(row()['uyxb']), '');
    // Tiên Đạo Tàn Quyển (no_shop) không mua được
    go('mua Tàn Quyển (no_shop) bị chặn', "cmd=shangdian&canshu2=gogoumai2&buy=1&djid=115&p=1&sid=$SID", null, '');
    chk('Tàn Quyển không mua được ở shop', dj(115) === 0, '');
    // ydid không tồn tại
    go('mua đan dược ydid không tồn tại', "cmd=shangdian&canshu=gogoumai&ydcount=1&ydid=99999&p=1&sid=$SID", null, '');
});

// ============================ 6. TÚI ĐỒ ============================
block('Túi đồ', function () {
    global $SID;
    go('Túi đồ - Trang bị', "cmd=getbagzb&sid=$SID", null, '');
    go('Túi đồ - Đạo cụ', "cmd=getbagdj&sid=$SID", null, '');
    go('Túi đồ - Dược phẩm', "cmd=getbagyp&sid=$SID", null, '');
    go('Túi đồ - Đan dược', "cmd=getbagyd&sid=$SID", null, '');
    go('Túi đồ - Kỹ năng', "cmd=getbagjn&sid=$SID", null, '');
    go('xem đạo cụ (djinfo)', "cmd=djinfo&djid=1&sid=$SID", null, '');
    go('xem đan dược (ydinfo)', "cmd=ydinfo&ydid=1&sid=$SID", null, '');
    go('xem trang bị hệ thống (zbinfo_sys)', "cmd=zbinfo_sys&zbid=1&sid=$SID", null, '');
});

// ============================ 7. TRANG BỊ ============================
$ZB = array();
block('Trang bị (mặc / tháo / cường hóa / ngũ hành / phân giải / bán)', function () {
    global $db, $SID, $UID, $ZB;
    $cfg = \player\zhuangbei_config_all();
    $best = array();
    foreach ($cfg as $zbid => $c) {
        $t = intval($c['zbtool']);
        if (intval($c['zblv']) > 130) continue;
        if (!isset($best[$t]) || intval($c['rarity']) > intval($cfg[$best[$t]]['rarity'])) $best[$t] = $zbid;
    }
    $ZB['weapon'] = e2e_add_zb($db, $SID, $UID, $best[1], 0, 'none');
    $ZB['armor']  = e2e_add_zb($db, $SID, $UID, $best[4], 0, 'none');
    $ZB['spare']  = e2e_add_zb($db, $SID, $UID, $best[2], 0, 'none');
    $ZB['sell']   = e2e_add_zb($db, $SID, $UID, 1, 2, 'none');
    $p0 = pl();
    go('mặc vũ khí (setzbwz ô 1)', "cmd=setzbwz&zbwz=1&zbnowid={$ZB['weapon']}&sid=$SID", null, '');
    $p1 = pl();
    chk('tool1 = vũ khí, Công tăng đúng zbgj', intval(row()['tool1']) === $ZB['weapon']
        && intval($p1->ugj) - intval($p0->ugj) >= intval($cfg[$best[1]]['zbgj']), 'Công ' . $p0->ugj . ' -> ' . $p1->ugj);
    go('mặc sai ô (vũ khí vào ô 4) bị từ chối', "cmd=setzbwz&zbwz=4&zbnowid={$ZB['spare']}&sid=$SID", null, '');
    chk('ô 4 không bị gán món sai loại', intval(row()['tool4']) !== $ZB['spare'], '');
    go('mặc giáp (ô 4)', "cmd=setzbwz&zbwz=4&zbnowid={$ZB['armor']}&sid=$SID", null, '');
    go('trang Trang bị', "cmd=trangbi&sid=$SID", null, '');
    go('tháo giáp (xxzb ô 4)', "cmd=xxzb&zbwz=4&sid=$SID", null, '');
    chk('tool4 = 0 sau khi tháo', intval(row()['tool4']) === 0, '');
    go('mặc nhanh (quickequip)', "cmd=quickequip&sid=$SID", null, '');
    chk('quickequip mặc lại giáp vào ô 4', intval(row()['tool4']) === $ZB['armor'], 'tool4=' . row()['tool4']);
    go('xem trang bị (chakanzb)', "cmd=chakanzb&zbnowid={$ZB['weapon']}&sid=$SID", null, '');

    // Cường hóa: đá = qh×3+1, LT = công thức tianfu_forge_cost; thành công +round(gốc×8%)
    setdj(1, 500);
    $base = intval($cfg[$best[1]]['zbgj']);
    $okUp = false;
    for ($i = 0; $i < 6 && !$okUp; $i++) {
        $z0 = $db->query("SELECT zbgj, qianghua FROM playerzhuangbei WHERE zbnowid = {$ZB['weapon']}")->fetch(PDO::FETCH_ASSOC);
        $qh = intval($z0['qianghua']);
        $needStone = $qh * 3 + 1;
        $needLt = \player\tianfu_forge_cost(0, intval(round($qh / 2) * round($qh / 3) * 2 * round($qh / 4) + 1));
        $s0 = dj(1); $lt0 = intval(row()['uyxb']);
        go("cường hóa Công lần " . ($i + 1), "cmd=upzb&upsx=zbgj&zbnowid={$ZB['weapon']}&sid=$SID", null, '');
        $z1 = $db->query("SELECT zbgj, qianghua FROM playerzhuangbei WHERE zbnowid = {$ZB['weapon']}")->fetch(PDO::FETCH_ASSOC);
        $s1 = dj(1); $lt1 = intval(row()['uyxb']);
        $costOk = ($s0 - $s1 === $needStone) && ($lt0 - $lt1 === intval($needLt));
        if (intval($z1['qianghua']) === $qh + 1) {
            $okUp = true;
            chk('cường hóa thành công: +' . max(1, round($base * 0.08)) . ' Công (8% gốc), tốn ' . $needStone . ' đá + ' . $needLt . ' LT',
                intval($z1['zbgj']) - intval($z0['zbgj']) === intval(max(1, round($base * 0.08))) && $costOk,
                'Công ' . $z0['zbgj'] . '->' . $z1['zbgj'] . ', đá -' . ($s0 - $s1) . ', LT -' . ($lt0 - $lt1));
        } else {
            chk('cường hóa thất bại vẫn trừ đúng phí', $costOk && intval($z1['zbgj']) === intval($z0['zbgj']), 'đá -' . ($s0 - $s1) . ', LT -' . ($lt0 - $lt1));
        }
    }
    chk('cường hóa thành công ít nhất 1 lần / 6', $okUp, '');
    chk('getplayer() cộng phần cường hóa', intval(pl()->ugj) > intval($p1->ugj) || !$okUp, 'Công ' . $p1->ugj . ' -> ' . pl()->ugj);

    // Ngũ hành: nạp hệ Kim vào vũ khí (đá Kim 35, LT = 500 × rarity × 1)
    setdj(35, 10);
    $rar = intval($cfg[$best[1]]['rarity']);
    $s0 = dj(35); $lt0 = intval(row()['uyxb']);
    $b = go('nạp hệ Kim (nap_hanh)', "cmd=nap_hanh&he=kim&zbnowid={$ZB['weapon']}&sid=$SID", null, '');
    $el = q1("SELECT element FROM playerzhuangbei WHERE zbnowid = ?", array($ZB['weapon']));
    $paid = ($s0 - dj(35) === 1) && ($lt0 - intval(row()['uyxb']) === 500 * $rar);
    chk('nạp hành: trừ 1 đá Kim + ' . (500 * $rar) . ' LT; thành công thì element chứa kim', $paid,
        'element=' . $el . ', đá -' . ($s0 - dj(35)) . ', LT -' . ($lt0 - intval(row()['uyxb'])));
    go('nạp trùng hệ Kim bị chặn (nếu đã có)', "cmd=nap_hanh&he=kim&zbnowid={$ZB['weapon']}&sid=$SID", null, '');
    go('nạp hệ không hợp lệ', "cmd=nap_hanh&he=xyz&zbnowid={$ZB['weapon']}&sid=$SID", null, '');

    // Phân giải: tốn qh×20+20 LT, nhận Cường hóa thạch, xóa món
    $lt0 = intval(row()['uyxb']); $s0 = dj(1);
    go('phân giải trang bị (delezb)', "cmd=delezb&zbnowid={$ZB['spare']}&sid=$SID", null, '');
    chk('phân giải: xóa món, trừ 20 LT, +đá cường hóa', intval(q1("SELECT COUNT(*) FROM playerzhuangbei WHERE zbnowid = ?", array($ZB['spare']))) === 0
        && $lt0 - intval(row()['uyxb']) === 20 && dj(1) > $s0, 'LT -' . ($lt0 - intval(row()['uyxb'])) . ', đá +' . (dj(1) - $s0));
    // Bán: nhận rarity×25 + qh×5 (E2 2026-10-29, cũ ×50)
    $lt0 = intval(row()['uyxb']);
    go('bán trang bị (maichu)', "cmd=getbagzb&canshu=maichu&yeshu=0&zbnowid={$ZB['sell']}&sid=$SID", null, '');
    chk('bán zbid 1 (+2): nhận 1×25 + 2×5 = 35 LT', intval(row()['uyxb']) - $lt0 === 35, 'nhận ' . (intval(row()['uyxb']) - $lt0));
    go('Thần Trang (taozhuang)', "cmd=taozhuang&sid=$SID", null, '');
});

// ============================ 8. THIÊN CƠ CÁC ============================
block('Thiên Cơ Các', function () {
    global $db, $SID, $UID;
    go('Thiên Cơ Các - tab Võ công', "cmd=thiencoccac&tab=wg&sid=$SID", null, '');
    go('Thiên Cơ Các - tab Công pháp', "cmd=thiencoccac&tab=cp&sid=$SID", null, '');
    $lt0 = intval(row()['uyxb']);
    go('mua võ công 2 (500 LT)', "cmd=thiencoccac&canshu=buy_wg&buy_type=wg&buy_id=2&tab=wg&sid=$SID", null, '');
    chk('mua võ công: trừ 500 LT, có dòng playerwugong', $lt0 - intval(row()['uyxb']) === 500
        && intval(q1("SELECT COUNT(*) FROM playerwugong WHERE sid = ? AND wgid = 2", array($SID))) === 1, 'trừ ' . ($lt0 - intval(row()['uyxb'])));
    $lt0 = intval(row()['uyxb']);
    go('mua lại võ công 2 (+1 bí tịch)', "cmd=thiencoccac&canshu=buy_wg&buy_type=wg&buy_id=2&tab=wg&sid=$SID", null, '');
    chk('mua lại: không thêm dòng trùng, +1 bí tịch', intval(q1("SELECT COUNT(*) FROM playerwugong WHERE sid = ? AND wgid = 2", array($SID))) === 1
        && intval(q1("SELECT wgsum FROM playerwugong WHERE sid = ? AND wgid = 2", array($SID))) === 2, '');
    $cfgCp = \player\congphap_config_all();
    $mt0 = intval(row()['uczb']); $lt0 = intval(row()['uyxb']);
    go('mua công pháp 73 trả Linh thạch', "cmd=thiencoccac&canshu=buy_cp&buy_type=cp&buy_id=73&tien=lt&tab=cp&sid=$SID", null, '');
    chk('công pháp 73: trừ ' . $cfgCp['73']['price_linhthong'] . ' LT (không trừ MT), có dòng playercongphap',
        $lt0 - intval(row()['uyxb']) === intval($cfgCp['73']['price_linhthong']) && $mt0 === intval(row()['uczb'])
        && intval(q1("SELECT COUNT(*) FROM playercongphap WHERE sid = ? AND cpid = 73", array($SID))) === 1, '');
    $mt0 = intval(row()['uczb']);
    go('mua công pháp 29 trả Ma thạch (mặc định)', "cmd=thiencoccac&canshu=buy_cp&buy_type=cp&buy_id=29&tab=cp&sid=$SID", null, '');
    chk('công pháp 29: trừ ' . $cfgCp['29']['price_masthach'] . ' MT', $mt0 - intval(row()['uczb']) === intval($cfgCp['29']['price_masthach']), '');
    // Cảnh giới chưa đủ
    $lt0 = intval(row()['uyxb']); $mt0 = intval(row()['uczb']);
    go('mua võ công yêu cầu cảnh giới cao bị chặn', "cmd=thiencoccac&canshu=buy_wg&buy_type=wg&buy_id=36&tab=wg&sid=$SID", null, '');
    chk('cảnh giới chưa đủ: không trừ tiền, không thêm võ công', $lt0 === intval(row()['uyxb']) && $mt0 === intval(row()['uczb'])
        && intval(q1("SELECT COUNT(*) FROM playerwugong WHERE sid = ? AND wgid = 36", array($SID))) === 0, '');
    // Đổi Tàn Quyển: võ công Tiên Đạo + công pháp cảnh giới >= 30 — tạm nâng cấp nhân vật để đủ cảnh giới
    $td = json_decode(file_get_contents(dirname(__DIR__) . '/class/config/tiendao_config.json'), true);
    $wgTd = intval($td['wgid'][0]);
    $wgCfg = \player\wugong_config_get($wgTd);
    $needRealm = intval($wgCfg['realm_require']);
    $lvForRealm = max(130, ($needRealm - 1) * 50);
    $oldLv = intval(row()['ulv']);
    setcol('ulv', $lvForRealm);
    $tdDj = \player\tiendao_djid_for($wgTd); $tdNeed = \player\tiendao_need($wgTd);
    setdj($tdDj, $tdNeed + 3);
    $lt0 = intval(row()['uyxb']); $mt0 = intval(row()['uczb']);
    go("đổi võ công Tiên Đạo $wgTd bằng Tàn Quyển", "cmd=thiencoccac&canshu=buy_wg&buy_type=wg&buy_id=$wgTd&tab=wg&sid=$SID", null, '');
    chk("đổi Tàn Quyển: trừ $tdNeed quyển (djid $tdDj), không trừ tiền, có võ công", dj($tdDj) === 3 && $lt0 === intval(row()['uyxb'])
        && $mt0 === intval(row()['uczb']) && intval(q1("SELECT COUNT(*) FROM playerwugong WHERE sid = ? AND wgid = ?", array($SID, $wgTd))) === 1,
        'còn ' . dj($tdDj) . ' quyển');
    $cpTq = 0;
    foreach ($td['cp_need_by_cpid'] as $k => $v) { $cpTq = intval($k); break; }
    if ($cpTq > 0) {
        list($cdj, $cneed) = \player\tiendao_cp_cost($cpTq);
        $cpR = intval($cfgCp[(string)$cpTq]['realm_require']);
        setcol('ulv', max($lvForRealm, ($cpR - 1) * 50));
        setdj($cdj, $cneed);
        $lt0 = intval(row()['uyxb']); $mt0 = intval(row()['uczb']);
        go("đổi công pháp $cpTq bằng Tàn Quyển", "cmd=thiencoccac&canshu=buy_cp&buy_type=cp&buy_id=$cpTq&tab=cp&sid=$SID", null, '');
        chk("đổi công pháp bằng Tàn Quyển: trừ $cneed quyển, không trừ tiền", dj($cdj) === 0 && $lt0 === intval(row()['uyxb'])
            && $mt0 === intval(row()['uczb']) && intval(q1("SELECT COUNT(*) FROM playercongphap WHERE sid = ? AND cpid = ?", array($SID, $cpTq))) === 1, '');
    }
    setcol('ulv', $oldLv);
    $db->prepare("DELETE FROM playerwugong WHERE sid = ? AND wgid = ?")->execute(array($SID, $wgTd));
    if ($cpTq > 0) $db->prepare("DELETE FROM playercongphap WHERE sid = ? AND cpid = ?")->execute(array($SID, $cpTq));
});

// ============================ 9. VÕ CÔNG ============================
block('Võ công', function () {
    global $db, $SID;
    go('Bí tịch (xxwg)', "cmd=xxwg&sid=$SID", null, '');
    $p0 = pl();
    go('học / luyện võ công 2 (xuexi)', "cmd=xxwg&canshu=xuexi&wgid=2&sid=$SID", null, '');
    chk('game1.wugong = 2', intval(row()['wugong']) === 2, '');
    go('xem chi tiết võ công (cwinfo)', "cmd=xxwg&canshu=cwinfo&wgid=2&sid=$SID", null, '');
    go('Tu hành võ công (wgxl)', "cmd=wgxl&sid=$SID", null, '');
    // Đầy EXP, thiếu bí tịch => "Mua ngay"
    $db->prepare("UPDATE playerwugong SET wgxl = 100, wgxlmax = 100, wgsum = 0, wgdj = 1 WHERE sid = ? AND wgid = 2")->execute(array($SID));
    list($cur, $price, $pdj) = \player\wugong_book_price(2);
    $b = go('trang Tu hành có nút "Mua ngay"', "cmd=wgxl&sid=$SID", null, 'Mua ngay');
    go('mở hộp Mua bí tịch', "cmd=wgxl&canshu=buybook&wgid=2&sid=$SID", null, 'Mua bí tịch');
    $lt0 = intval(row()['uyxb']); $mt0 = intval(row()['uczb']);
    go('xác nhận mua 2 bí tịch', "cmd=wgxl&canshu=buybook_confirm&wgid=2&buycount=2&sid=$SID", null, '');
    $paid = $cur === 'lt' ? $lt0 - intval(row()['uyxb']) : $mt0 - intval(row()['uczb']);
    chk("Mua ngay: +2 bí tịch, trừ 2 × $price " . $cur, intval(q1("SELECT wgsum FROM playerwugong WHERE sid = ? AND wgid = 2", array($SID))) === 2
        && $paid === 2 * $price, 'trừ ' . $paid);
    go('mua vượt số còn thiếu bị chặn về số thiếu', "cmd=wgxl&canshu=buybook_confirm&wgid=2&buycount=50&sid=$SID", null, '');
    chk('không mua thêm khi đã đủ bí tịch', intval(q1("SELECT wgsum FROM playerwugong WHERE sid = ? AND wgid = 2", array($SID))) === 2, '');
    $p1 = pl();
    go('nâng cấp võ công (wgxiulian)', "cmd=wgxiulian&canshu=1&wgid=2&sid=$SID", null, '');
    $w = $db->query("SELECT wgdj, wgsum, wgxl, wgxlmax FROM playerwugong WHERE sid = " . $db->quote($SID) . " AND wgid = 2")->fetch(PDO::FETCH_ASSOC);
    chk('võ công lên cấp 2, tiêu 2 bí tịch, EXP về 0, wgxlmax 100 + max(10, 20%) = 120', intval($w['wgdj']) === 2 && intval($w['wgsum']) === 0
        && intval($w['wgxl']) === 0 && intval($w['wgxlmax']) === 120, json_encode($w));
    $wc = \player\wugong_config_get(2);
    $p2 = pl();
    chk('getplayer() cộng add_gj × cấp võ công (' . $wc['add_gj'] . ' × 2)', intval($p2->ugj) - intval($p1->ugj) === intval($wc['add_gj']), 'Công ' . $p1->ugj . ' -> ' . $p2->ugj);
    // Bảo mật: luyện võ công không sở hữu
    go('xuexi võ công KHÔNG sở hữu (wgid 23)', "cmd=xxwg&canshu=xuexi&wgid=23&sid=$SID", null, '');
    chk('không được đặt võ công chưa sở hữu làm võ công đang luyện', intval(row()['wugong']) !== 23, 'game1.wugong = ' . row()['wugong']);
    setcol('wugong', 2);
    go('bế quan (biguan)', "cmd=xxwg&canshu=biguan&sid=$SID", null, '');
    chk('biguan: wugong = 0', intval(row()['wugong']) === 0, '');
    setcol('wugong', 2);
});

// ============================ 10. CÔNG PHÁP + CỘNG HƯỞNG ============================
block('Công pháp', function () {
    global $db, $SID;
    go('trang Công pháp', "cmd=gocp&sid=$SID", null, '');
    $p0 = pl();
    go('vận công pháp 29 (atk_bonus)', "cmd=gocp&canshu=equip&cpid=29&sid=$SID", null, '');
    chk('cpequipped = 1', intval(q1("SELECT cpequipped FROM playercongphap WHERE sid = ? AND cpid = 29", array($SID))) === 1, '');
    $p1 = pl();
    chk('vận atk_bonus làm Công tăng', intval($p1->ugj) > intval($p0->ugj), 'Công ' . $p0->ugj . ' -> ' . $p1->ugj);
    go('vận thêm công pháp 73 khi đã đầy slot (cảnh giới 3 = 1 slot)', "cmd=gocp&canshu=equip&cpid=73&sid=$SID", null, '');
    chk('không vượt số slot vận công', intval(q1("SELECT COUNT(*) FROM playercongphap WHERE sid = ? AND cpequipped = 1", array($SID))) === 1, '');
    go('xem chi tiết công pháp (cpinfo)', "cmd=gocp&canshu=cpinfo&cpid=29&sid=$SID", null, '');
    go('thu công pháp 29 (unequip)', "cmd=gocp&canshu=unequip&cpid=29&sid=$SID", null, '');
    chk('cpequipped = 0', intval(q1("SELECT cpequipped FROM playercongphap WHERE sid = ? AND cpid = 29", array($SID))) === 0, '');
    chk('thu công: Công về như cũ', intval(pl()->ugj) === intval($p0->ugj), '');
    go('vận công pháp chưa sở hữu (cpid 1)', "cmd=gocp&canshu=equip&cpid=1&sid=$SID", null, '');
    chk('không thể vận công pháp chưa sở hữu', intval(q1("SELECT COUNT(*) FROM playercongphap WHERE sid = ? AND cpid = 1", array($SID))) === 0, '');
    go('Cộng Hưởng (Tàng Kinh Các)', "cmd=conghuong&sid=$SID", null, '');
});

// ============================ 11. CHẾ TẠO + LUYỆN ĐAN ============================
block('Chế tạo', function () {
    global $db, $SID;
    go('trang Chế tạo', "cmd=chetao&sid=$SID", null, '');
    go('xem công thức 8', "cmd=chetao&recipe_id=8&sid=$SID", null, '');
    foreach (array(32, 33, 34, 35, 36) as $d) setdj($d, 3);
    setdj(37, 0);
    $l0 = intval(q1("SELECT COUNT(*) FROM player_chetao_log WHERE sid = ?", array($SID)));
    go('chế tạo công thức 8 (Bình Anh Tinh Thạch)', "cmd=do_craft_chetao&recipe_id=8&sid=$SID", null, '');
    $l1 = intval(q1("SELECT COUNT(*) FROM player_chetao_log WHERE sid = ?", array($SID)));
    $succ = intval(q1("SELECT success FROM player_chetao_log WHERE sid = ? ORDER BY 1 DESC LIMIT 1", array($SID)));
    $last = $db->query("SELECT success FROM player_chetao_log WHERE sid = " . $db->quote($SID) . " ORDER BY created_at DESC LIMIT 1")->fetchColumn();
    chk('ghi log chế tạo', $l1 === $l0 + 1, '');
    if (intval($last) === 1) {
        chk('thành công: trừ 3 mỗi loại đá, +1 Bình Anh', dj(32) === 0 && dj(36) === 0 && dj(37) === 1, 'Bình Anh ' . dj(37));
    } else {
        chk('thất bại: không mất nguyên liệu (thiết kế "trừ khi thành công")', dj(32) === 3 && dj(37) === 0, '');
    }
    go('chế tạo khi thiếu cấp (công thức 1 yêu cầu cấp 400)', "cmd=do_craft_chetao&recipe_id=1&sid=$SID", null, '');
});

block('Luyện đan', function () {
    global $db, $SID;
    go('trang Luyện đan', "cmd=luonden&sid=$SID", null, '');
    go('xem công thức 1', "cmd=luonden&recipe_id=1&sid=$SID", null, '');
    setdj(30, 10);
    $yp0 = intval(q1("SELECT COALESCE(SUM(ypsum),0) FROM playeryaopin WHERE sid = ? AND ypid = 6", array($SID)));
    $c0 = intval(q1("SELECT COALESCE(MAX(total_craft),0) FROM player_alchemist WHERE sid = ?", array($SID)));
    $tries = 0; $okOnce = false;
    while ($tries < 3 && !$okOnce) {
        $tries++;
        setdj(30, 10);
        $b = go("luyện Hồi Khí Đan (công thức 1) lần $tries", "cmd=do_craft_luonden&recipe_id=1&sid=$SID", null, '');
        $yp1 = intval(q1("SELECT COALESCE(SUM(ypsum),0) FROM playeryaopin WHERE sid = ? AND ypid = 6", array($SID)));
        if (dj(30) === 7) {
            $okOnce = true;
            chk('luyện thành công: trừ đủ 3 nguyên liệu, +2 Hồi Khí Đan vào túi', $yp1 === $yp0 + 2, 'đan ' . $yp0 . '->' . $yp1);
        } else {
            chk('luyện thất bại: chỉ mất một nửa nguyên liệu (3/2 -> 1)', dj(30) === 9 && $yp1 === $yp0, 'nguyên liệu còn ' . dj(30));
        }
    }
    chk('player_alchemist.total_craft tăng', intval(q1("SELECT COALESCE(MAX(total_craft),0) FROM player_alchemist WHERE sid = ?", array($SID))) > $c0, '');
    go('dùng Giải Độc Đan khi không có (use_detox_pill)', "cmd=use_detox_pill&sid=$SID", null, '');
});

// ============================ 12. LINH CĂN / THỂ CHẤT / HUYẾT MẠCH / THIÊN PHÚ ============================
block('Linh căn', function () {
    global $db, $SID;
    go('trang Linh căn', "cmd=linhcan&sid=$SID", null, '');
    setdj(35, 20);
    $cost = \player\linhcan_train_cost(0);
    $s0 = dj(35); $lt0 = intval(row()['uyxb']); $p0 = pl();
    go('luyện hành Kim', "cmd=linhcan&el=kim&canshu=train&sid=$SID", null, '');
    $lv = intval(q1("SELECT level FROM player_linhcan_node WHERE sid = ? AND element = 'kim'", array($SID)));
    chk('Kim Lv 1, trừ ' . $cost['stone'] . ' đá Kim + ' . $cost['linh_thach'] . ' LT', $lv === 1 && $s0 - dj(35) === $cost['stone']
        && $lt0 - intval(row()['uyxb']) === $cost['linh_thach'], 'Lv ' . $lv . ', đá -' . ($s0 - dj(35)) . ', LT -' . ($lt0 - intval(row()['uyxb'])));
    // Kim Lv1 => linh căn chủ đạo = Kim => vũ khí đã nạp Kim kích hoạt Nội tại Nạp Hành nấc 1 (+3% Công của món hợp hệ)
    $wz = intval(q1("SELECT zbgj FROM playerzhuangbei WHERE zbnowid = ? AND element LIKE '%kim%'", array(intval(row()['tool1']))));
    $expAdd = 2 + intval($wz * 3 / 100);
    chk('getplayer(): Công +2 (Kim 2/Lv) + Nạp Hành 3% × Công vũ khí hệ Kim (' . $wz . ') = +' . $expAdd, intval(pl()->ugj) - intval($p0->ugj) === $expAdd, 'Công ' . $p0->ugj . ' -> ' . pl()->ugj);
    go('đột phá linh căn khi chưa đủ Lv', "cmd=linhcan&el=kim&canshu=break&sid=$SID", null, '');
    go('Nhiệm vụ linh căn', "cmd=linhcan_quest&sid=$SID", null, '');
    go('Boss linh căn', "cmd=linhcan_boss&sid=$SID", null, '');
    go('Tẩy luyện linh căn (linhcan_wash)', "cmd=linhcan_wash&el=kim&sid=$SID", null, '');
});

block('Thể chất', function () {
    global $db, $SID;
    go('trang Thể chất', "cmd=thechat&sid=$SID", null, '');
    $cfg = json_decode(file_get_contents(dirname(__DIR__) . '/class/config/thechat_body_config.json'), true);
    setdj(100, 50);
    $s0 = dj(100); $lt0 = intval(row()['uyxb']); $p0 = pl();
    go('luyện Nhục Thân bộ phận 1 (POST refine)', "cmd=thechat&node=1&sid=$SID", array('nb_action' => 'refine', 'nb_node' => 1), '');
    $lv = intval(q1("SELECT node_lv FROM player_thechat_node WHERE sid = ? AND node_id = 1", array($SID)));
    $needMat = intval($cfg['refine']['material_base']); $needLt = intval($cfg['refine']['linh_thach_base']);
    chk("Nhục Thân bộ phận 1 Lv 1, trừ $needMat Tinh Thể Đá + $needLt LT", $lv === 1 && $s0 - dj(100) === $needMat && $lt0 - intval(row()['uyxb']) === $needLt,
        'Lv ' . $lv . ', đá -' . ($s0 - dj(100)) . ', LT -' . ($lt0 - intval(row()['uyxb'])));
    chk('getplayer(): Thủ +' . $cfg['nodes']['1']['per_lv'] . ' (Bì Phủ)', intval(pl()->ufy) - intval($p0->ufy) === intval($cfg['nodes']['1']['per_lv']), 'Thủ ' . $p0->ufy . ' -> ' . pl()->ufy);
    go('trang nâng phẩm Thể chất', "cmd=thechat_upgrade&sid=$SID", null, '');
    go('nâng phẩm khi chưa có Thể chất (POST)', "cmd=thechat_upgrade&sid=$SID", array('action' => 'upgrade'), '');
});

block('Huyết mạch', function () {
    global $db, $SID;
    go('trang Huyết mạch', "cmd=huyetmach&sid=$SID", null, '');
    $cfg = json_decode(file_get_contents(dirname(__DIR__) . '/class/config/huyetmach_vessel_config.json'), true);
    setdj(112, 50);
    $s0 = dj(112); $lt0 = intval(row()['uyxb']); $p0 = pl();
    go('luyện Mạch 1 (POST refine)', "cmd=huyetmach&node=1&sid=$SID", array('hm_action' => 'refine', 'hm_node' => 1), '');
    $lv = intval(q1("SELECT level FROM player_huyetmach_vessel WHERE sid = ? AND vessel_id = 1", array($SID)));
    $needMat = intval($cfg['refine']['material_base']); $needLt = intval($cfg['refine']['linh_thach_base']);
    chk("Mạch 1 Lv 1, trừ $needMat Huyết Thạch + $needLt LT", $lv === 1 && $s0 - dj(112) === $needMat && $lt0 - intval(row()['uyxb']) === $needLt,
        'Lv ' . $lv . ', đá -' . ($s0 - dj(112)) . ', LT -' . ($lt0 - intval(row()['uyxb'])));
    chk('getplayer(): Công +3 (Đốc Mạch)', intval(pl()->ugj) - intval($p0->ugj) === 3, 'Công ' . $p0->ugj . ' -> ' . pl()->ugj);
    go('Huyết mạch - nâng phẩm', "cmd=huyetmach_upgrade&sid=$SID", null, '');
    go('Huyết mạch - biến thân', "cmd=huyetmach_transform&sid=$SID", null, '');
    go('Huyết mạch - boss', "cmd=huyetmach_boss&sid=$SID", null, '');
});

block('Thiên phú', function () {
    global $db, $SID;
    go('trang Thiên phú', "cmd=tianfu&sid=$SID", null, '');
    go('Khai Phú (GET không roll)', "cmd=tianfu_open&sid=$SID", null, '');
    setdj(114, 2);
    $n0 = intval(q1("SELECT COUNT(*) FROM player_tianfu WHERE sid = ?", array($SID)));
    go('Khai Phú (POST open)', "cmd=tianfu_open&sid=$SID", array('tp_action' => 'open'), '');
    $n1 = intval(q1("SELECT COUNT(*) FROM player_tianfu WHERE sid = ?", array($SID)));
    chk('Khai Phú: trừ 1 Khai Phú Đan, có thiên phú', dj(114) === 1 && $n1 >= 1, 'đan còn ' . dj(114) . ', thiên phú ' . $n0 . '->' . $n1);
    chk('game1.tianfu_power đồng bộ player_tianfu (gương)', intval(pl()->tianfu_power) === intval(\player\tianfu_bonuses($SID, $db)['power']), '');
    go('Bộ sưu tập thiên phú', "cmd=tianfu_collection&sid=$SID", null, '');
});

// ============================ 13. LINH SỦNG ============================
block('Linh sủng', function () {
    global $db, $SID;
    go('trang Linh sủng', "cmd=chongwu&sid=$SID", null, '');
    $c0 = intval(q1("SELECT COUNT(*) FROM playerchongwu WHERE sid = ?", array($SID)));
    try { \player\addchongwu_from_pet($SID, 1, 2, $db); } catch (Exception $e) { }
    $c1 = intval(q1("SELECT COUNT(*) FROM playerchongwu WHERE sid = ?", array($SID)));
    chk('addchongwu_from_pet (thu phục ở Sào Thú) thêm được linh sủng', $c1 === $c0 + 1, $c1 === $c0 + 1 ? '' : 'INSERT playerchongwu lỗi (thiếu cột NOT NULL tool1..6 khi strict)');
    if ($c1 === $c0) {
        $db->prepare("INSERT INTO playerchongwu (cwname, cwhp, cwmaxhp, cwgj, cwfy, cwbj, cwxx, cwlv, cwexp, tool1, tool2, tool3, tool4, tool5, tool6, sid, uphp, upgj, upfy, cwpz, cwmaxexp)
            VALUES ('E2E Pet', 100, 100, 50, 20, 0, 0, 1, 0, 0,0,0,0,0,0, ?, 10, 2, 2, 1, 100)")->execute(array($SID));
    }
    $cwid = intval(q1("SELECT MAX(cwid) FROM playerchongwu WHERE sid = ?", array($SID)));
    go('xem linh sủng (cwinfo)', "cmd=chongwu&cwid=$cwid&canshu=cwinfo&sid=$SID", null, '');
    go('xuất chiến (chuzhan)', "cmd=chongwu&canshu=chuzhan&cwid=$cwid&sid=$SID", null, '');
    chk('game1.cw = linh sủng', intval(row()['cw']) === $cwid, 'cw=' . row()['cw']);
    go('thu hồi (shouhui)', "cmd=chongwu&canshu=shouhui&cwid=$cwid&sid=$SID", null, '');
    chk('game1.cw = 0', intval(row()['cw']) === 0, '');
    $other = intval($db->query("SELECT cwid FROM playerchongwu WHERE sid <> " . $db->quote($SID) . " LIMIT 1")->fetchColumn());
    if ($other > 0) {
        go('xuất chiến linh sủng của người khác bị chặn', "cmd=chongwu&canshu=chuzhan&cwid=$other&sid=$SID", null, '');
        chk('không xuất chiến được linh sủng người khác', intval(row()['cw']) !== $other, '');
        setcol('cw', 0);
    }
    go('xuất chiến lại', "cmd=chongwu&canshu=chuzhan&cwid=$cwid&sid=$SID", null, '');
});

// ============================ 14. NHIỆM VỤ ============================
block('Nhiệm vụ', function () {
    global $SID;
    foreach (array('daily', 'weekly', 'monthly', 'main', 'achievements', 'online', 'canhgioi', 'hidden') as $tab) {
        go("Nhiệm vụ tab $tab", "cmd=nhiemvu&tab=$tab&sid=$SID", null, '');
    }
    go('nhận thưởng cảnh giới (claim_realm_all)', "cmd=nhiemvu&action=claim_realm_all&tab=canhgioi&sid=$SID", null, '');
    go('Tin tức nhiệm vụ (taskguide)', "cmd=taskguide&sid=$SID", null, '');
    go('Nhiệm vụ của ta (mytask)', "cmd=mytask&sid=$SID", null, '');
    go('NPC 17', "cmd=npc&nid=17&sid=$SID", null, '');
});

// ============================ 15. PVE + BOSS ============================
block('Đánh quái PVE', function () {
    global $db, $SID;
    setcol('nowmid', 228);
    go('vào bản đồ chiến đấu 228', "cmd=gomid&newmid=228&sid=$SID", null, '');
    $gid = intval(q1("SELECT id FROM midguaiwu WHERE mid = 228 AND sid = '' LIMIT 1", array()));
    chk('bản đồ 228 sinh quái', $gid > 0, '');
    if ($gid <= 0) return;
    go('xem quái (getginfo)', "cmd=getginfo&gid=$gid&sid=$SID", null, '');
    $e0 = intval(row()['uexp']);
    go('vào trận (pve)', "cmd=pve&gid=$gid&nowmid=228&sid=$SID", null, '');
    chk('quái được gán cho nhân vật', q1("SELECT sid FROM midguaiwu WHERE id = ?", array($gid)) === $SID, '');
    $win = false;
    for ($i = 1; $i <= 10 && !$win; $i++) {
        go("tấn công lượt $i (pvegj)", "cmd=pvegj&gid=$gid&nowmid=228&sid=$SID", null, '');
        $win = q1("SELECT COUNT(*) FROM midguaiwu WHERE id = ?", array($gid)) == 0 || intval(q1("SELECT ghp FROM midguaiwu WHERE id = ?", array($gid))) <= 0;
    }
    chk('hạ quái trong 10 lượt và nhận tu vi', $win && intval(row()['uexp']) > $e0, 'uexp ' . $e0 . ' -> ' . row()['uexp']);
});

$BOSS_SNAP = null;
block('Boss thế giới', function () {
    global $db, $SID, $BOSS_SNAP;
    $bid = intval($db->query("SELECT bossid FROM boss WHERE bosshp > 0 AND bosslv <= 130 ORDER BY bosslv DESC LIMIT 1")->fetchColumn());
    chk('có boss còn sống cấp ≤ 130', $bid > 0, '');
    if ($bid <= 0) return;
    $BOSS_SNAP = $db->query("SELECT * FROM boss WHERE bossid = $bid")->fetch(PDO::FETCH_ASSOC);
    // Khôi phục boss kể cả khi test dừng giữa chừng (lỗi PHP / Ctrl+C không bắt được nhưng Fatal thì có)
    register_shutdown_function('e2e_restore_boss');
    go("xem boss $bid (bossinfo)", "cmd=boss&bossid=$bid&sid=$SID", null, '');
    $h0 = intval($BOSS_SNAP['bosshp']);
    go('vào trận boss (pvb)', "cmd=pvb&bossid=$bid&sid=$SID", null, '');
    for ($i = 1; $i <= 3; $i++) {
        go("đánh boss lượt $i (pvbgj)", "cmd=pvbgj&bossid=$bid&nowmid=" . row()['nowmid'] . "&sid=$SID", null, '');
    }
    $h1 = intval(q1("SELECT bosshp FROM boss WHERE bossid = ?", array($bid)));
    chk('boss mất máu sau 3 lượt', $h1 < $h0, "HP $h0 -> $h1");
    go('bỏ chạy (taopao)', "cmd=taopao&bossid=$bid&sid=$SID", null, '');
    chk('dòng boss thế giới vẫn còn sau khi rời trận', intval(q1("SELECT COUNT(*) FROM boss WHERE bossid = ?", array($bid))) === 1, '');
});

// ============================ 16. PHÓ BẢN ============================
block('Phó bản', function () {
    global $db, $SID, $SID2, $UNAME2, $TOKEN2, $UID2;
    go('trang Phó bản', "cmd=phoban&sid=$SID", null, '');
    // Tỏa Yêu Tháp
    $db->prepare("UPDATE game1 SET uhp = 999999 WHERE sid = ?")->execute(array($SID));
    go('Tỏa Yêu Tháp', "cmd=toayeuthap&sid=$SID", null, '');
    $f0 = intval(q1("SELECT floor FROM player_toayeuthap WHERE sid = ?", array($SID)));
    $e0 = intval(row()['uexp']);
    go('Tỏa Yêu Tháp: khiêu chiến tầng 1', "cmd=toayeuthap&canshu=fight&sid=$SID", null, '');
    $f1 = intval(q1("SELECT floor FROM player_toayeuthap WHERE sid = ?", array($SID)));
    $expR = 140 + 42 + intval(2.2);
    chk('thắng tầng 1: lên tầng 2, +' . $expR . ' tu vi', $f1 === $f0 + 1 && intval(row()['uexp']) - $e0 === $expR, "tầng $f0 -> $f1, tu vi +" . (intval(row()['uexp']) - $e0));
    // Vấn Đỉnh Đài — đối thủ là nhân vật test thứ 2
    e2e_insert_game1($db, $SID2, $TOKEN2, $UNAME2, array('ulv' => 120, 'ugj' => 500, 'ufy' => 300, 'umaxhp' => 5000, 'uhp' => 5000));
    $UID2 = intval(row($SID2)['uid']);
    go('Vấn Đỉnh Đài', "cmd=vandinhdai&sid=$SID", null, '');
    go('Vấn Đỉnh Đài (đối thủ test vào bảng)', "cmd=vandinhdai&sid=$SID2", null, '');
    $e0 = intval(row()['uexp']);
    go('Vấn Đỉnh Đài: tỷ võ với nhân vật test 2', "cmd=vandinhdai&canshu=fight&target=$SID2&sid=$SID", null, '');
    $ap = $db->query("SELECT today_fights, wins, losses, score FROM arena_player WHERE sid = " . $db->quote($SID))->fetch(PDO::FETCH_ASSOC);
    chk('đã đánh 1 trận, nhận tu vi, có log', intval($ap['today_fights']) === 1 && intval(row()['uexp']) > $e0
        && intval(q1("SELECT COUNT(*) FROM arena_log WHERE attacker_sid = ?", array($SID))) === 1, json_encode($ap));
    go('Vấn Đỉnh Đài: tự đánh chính mình bị chặn', "cmd=vandinhdai&canshu=fight&target=$SID&sid=$SID", null, '');
    // Hầm Mỏ
    go('Hầm Mỏ', "cmd=hammo&sid=$SID", null, '');
    $lt0 = intval(row()['uyxb']); $mt0 = intval(row()['uczb']);
    $l0 = intval(q1("SELECT COUNT(*) FROM mine_log WHERE sid = ?", array($SID)));
    go('Hầm Mỏ: đào 1 lần (miễn phí)', "cmd=hammo&canshu=dig&sid=$SID", null, '');
    $logs = $db->query("SELECT yxb_reward, czb_reward FROM mine_log WHERE sid = " . $db->quote($SID) . " ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $l1 = intval(q1("SELECT COUNT(*) FROM mine_log WHERE sid = ?", array($SID)));
    chk('đào: +1 log, Linh thạch cộng đúng phần thưởng, lượt miễn phí không trừ MT', $l1 === $l0 + 1
        && intval(row()['uyxb']) - $lt0 === intval($logs['yxb_reward']) && intval(row()['uczb']) - $mt0 === intval($logs['czb_reward']),
        'LT +' . (intval(row()['uyxb']) - $lt0) . ' / log ' . $logs['yxb_reward'] . ', MT ' . (intval(row()['uczb']) - $mt0));
    $db->prepare("UPDATE mine_player SET today_digs = 5 WHERE sid = ?")->execute(array($SID));
    $mt0 = intval(row()['uczb']);
    go('Hầm Mỏ: đào 5 lần (trả phí 5 MT/lần)', "cmd=hammo&canshu=dig5&sid=$SID", null, '');
    $czbGot = intval(q1("SELECT COALESCE(SUM(czb_reward),0) FROM (SELECT czb_reward FROM mine_log WHERE sid = ? ORDER BY id DESC LIMIT 5) t", array($SID)));
    chk('đào 5 lần trả phí: trừ 25 MT (trừ phần thưởng MT)', $mt0 - intval(row()['uczb']) + $czbGot === 25, 'MT -' . ($mt0 - intval(row()['uczb'])) . ', thưởng MT ' . $czbGot);
    // Sào Thú
    go('Sào Thú', "cmd=sauthu&sid=$SID", null, '');
    go('Sào Thú: vào bản đồ 1', "cmd=sauthu&canshu=enter&map=1&sid=$SID", null, '');
});

// ============================ 17. TÔNG MÔN ============================
block('Tông môn', function () {
    global $db, $SID;
    go('Danh sách tông môn', "cmd=clublist&sid=$SID", null, '');
    go('Tông môn (chưa vào)', "cmd=club&sid=$SID", null, '');
    go('Form lập tông môn', "cmd=club&canshu=createclub&sid=$SID", null, 'Sáng Lập Tông Môn');
    $mt0 = intval(row()['uczb']);
    list($c, $b, $l) = e2e_http("cmd=club&canshu=docreateclub&sid=$SID", array('clubname' => 'E2EF Mon', 'clubinfo' => 'test', 'paytype' => 'mt'));
    $cid = intval(q1("SELECT clubid FROM club WHERE clubname = 'E2EF Mon'", array()));
    chk('lập tông môn: có club + clubplayer (uclv 1), trừ 20000 MT', $cid > 0 && $mt0 - intval(row()['uczb']) === 20000
        && intval(q1("SELECT uclv FROM clubplayer WHERE sid = ?", array($SID))) === 1, "clubid=$cid, MT -" . ($mt0 - intval(row()['uczb'])));
    if ($cid <= 0) return;
    $p0 = pl();
    chk('vào tông môn cấp 1: getplayer() +1% HP, +0.5% Công/Thủ', $p0->club_bonus['lv'] === 1 && floatval($p0->club_bonus['hp']) == 1, json_encode($p0->club_bonus));
    go('trang tông môn của ta', "cmd=club&clubid=$cid&sid=$SID", null, '');
    $lt0 = intval(row()['uyxb']); $cl0 = intval(q1("SELECT clubyxb FROM club WHERE clubid = ?", array($cid)));
    go('Cống hiến (form)', "cmd=club&canshu=donate&sid=$SID", null, '');
    e2e_http("cmd=club&canshu=donatedo&sid=$SID", array('donate_type' => 'lt', 'donate_amount' => 1000));
    chk('cống hiến 1000 LT: trừ người, cộng quỹ', $lt0 - intval(row()['uyxb']) === 1000 && intval(q1("SELECT clubyxb FROM club WHERE clubid = ?", array($cid))) - $cl0 === 1000,
        'LT -' . ($lt0 - intval(row()['uyxb'])) . ', quỹ +' . (intval(q1("SELECT clubyxb FROM club WHERE clubid = ?", array($cid))) - $cl0));
    go('Nhiệm vụ tông môn', "cmd=club&canshu=questview&sid=$SID", null, '');
    go('Boss tông môn', "cmd=club&canshu=clubboss&sid=$SID", null, '');
    go('Nhận thưởng online tông môn', "cmd=club&canshu=claimonline&sid=$SID", null, '');
    go('Giải tán tông môn', "cmd=club&canshu=deleteclub&sid=$SID", null, '');
});

// ============================ 18. GIAO DỊCH / QUY ĐỔI / ĐỔI QUÀ ============================
block('Giao dịch (Phường thị)', function () {
    global $db, $SID, $SID2, $UID, $UID2;
    go('Phường thị - đạo cụ', "cmd=fangshi&fangshi=daoju&sid=$SID", null, '');
    go('Phường thị - trang bị', "cmd=fangshi&fangshi=zhuangbei&sid=$SID", null, '');
    go('Phường thị - cửa hàng', "cmd=fangshi&fangshi=shangdian&sid=$SID", null, '');
    if ($UID2 <= 0) return;
    // Người bán = nhân vật test 2: treo 1 trang bị giá 1000 LT
    $zb = e2e_add_zb($db, $SID2, $UID2, 1, 0, 'none');
    go('người bán treo trang bị (zbinfo chushou)', "cmd=zbinfo&canshu=chushou&pay=1000&zbnowid=$zb&sid=$SID2", null, '');
    $payid = intval(q1("SELECT payid FROM fangshi_zb WHERE zbnowid = ?", array($zb)));
    chk('treo bán: có fangshi_zb, món tách khỏi túi (uid 0)', $payid > 0 && intval(q1("SELECT uid FROM playerzhuangbei WHERE zbnowid = ?", array($zb))) === 0, "payid=$payid");
    if ($payid <= 0) return;
    $db->prepare("UPDATE game1 SET uyxb = 100000 WHERE sid = ?")->execute(array($SID2));
    $lt0 = intval(row()['uyxb']); $lt20 = intval(row($SID2)['uyxb']);
    go('người mua mua trang bị', "cmd=fangshi&fangshi=zhuangbei&canshu=buy&payid=$payid&sid=$SID", null, '');
    chk('mua: người mua -1000 LT, người bán +950 (thuế 5%: phẩm 1, giá < 10k — trade_tax_config.json), món về người mua', $lt0 - intval(row()['uyxb']) === 1000
        && intval(row($SID2)['uyxb']) - $lt20 === 950 && q1("SELECT sid FROM playerzhuangbei WHERE zbnowid = ?", array($zb)) === $SID,
        'mua -' . ($lt0 - intval(row()['uyxb'])) . ', bán +' . (intval(row($SID2)['uyxb']) - $lt20));
    // Treo rồi thu hồi
    go('người mua treo lại món vừa mua', "cmd=zbinfo&canshu=chushou&pay=500&zbnowid=$zb&sid=$SID", null, '');
    $payid2 = intval(q1("SELECT payid FROM fangshi_zb WHERE zbnowid = ?", array($zb)));
    go('thu hồi tin treo bán', "cmd=fangshi&fangshi=zhuangbei&canshu=retract&payid=$payid2&sid=$SID", null, '');
    chk('thu hồi: món về túi, xóa tin', q1("SELECT sid FROM playerzhuangbei WHERE zbnowid = ?", array($zb)) === $SID
        && intval(q1("SELECT COUNT(*) FROM fangshi_zb WHERE zbnowid = ?", array($zb))) === 0, '');
    $db->prepare("DELETE FROM fangshi_zb WHERE zbnowid = ?")->execute(array($zb));
    $db->prepare("DELETE FROM playerzhuangbei WHERE zbnowid = ? AND uid = 0")->execute(array($zb));
});

block('Quy đổi / Đổi quà', function () {
    global $db, $SID;
    go('trang Quy đổi', "cmd=quydoi&sid=$SID", null, '');
    $lt0 = intval(row()['uyxb']); $mt0 = intval(row()['uczb']);
    go('quy đổi 10 MT (POST)', "cmd=quydoi&sid=$SID", array('mt_amount' => 10), '');
    chk('quy đổi: -10 MT, +10000 LT (1:1000), có log', $mt0 - intval(row()['uczb']) === 10 && intval(row()['uyxb']) - $lt0 === 10000
        && intval(q1("SELECT COUNT(*) FROM exchange_log WHERE sid = ?", array($SID))) === 1, '');
    $mt0 = intval(row()['uczb']);
    go('quy đổi số âm bị chặn', "cmd=quydoi&sid=$SID", array('mt_amount' => -5), '');
    chk('số âm: không đổi', $mt0 === intval(row()['uczb']), '');
    go('trang Đổi quà', "cmd=duihuan&sid=$SID", null, '');
    $mt0 = intval(row()['uczb']);
    go('nhập giftcode vip666sss', "cmd=duihuan&dhm=vip666sss&sid=$SID", null, '');
    chk('giftcode: +8888 MT', intval(row()['uczb']) - $mt0 === 8888, '+' . (intval(row()['uczb']) - $mt0));
    $mt0 = intval(row()['uczb']);
    go('nhập lại giftcode (đã dùng)', "cmd=duihuan&dhm=vip666sss&sid=$SID", null, '');
    chk('giftcode chỉ nhận 1 lần', intval(row()['uczb']) === $mt0, '');
});

// ============================ 19. XẾP HẠNG / CHAT / BẢN ĐỒ / HẢO HỮU ============================
block('Xếp hạng', function () {
    global $SID;
    foreach (array('power', 'level', 'gong', 'fang', 'exp') as $k) go("Xếp hạng $k", "cmd=paihang&canshu=$k&sid=$SID", null, '');
});

block('Chat', function () {
    global $db, $SID, $UID;
    go('Tán gẫu - thế giới', "cmd=liaotian&ltlx=all&sid=$SID", null, '');
    $msg = 'E2EFull xin chao ' . date('His');
    go('gửi tin thế giới (sendliaotian)', "cmd=sendliaotian&ltlx=all&ltmsg=" . rawurlencode($msg) . "&sid=$SID", null, '');
    chk('tin nhắn vào ggliaotian với uid nhân vật', intval(q1("SELECT COUNT(*) FROM ggliaotian WHERE uid = ? AND msg = ?", array($UID, $msg))) === 1, '');
    go('gửi tin có HTML bị escape', "cmd=sendliaotian&ltlx=all&ltmsg=" . rawurlencode('<b>x</b>') . "&sid=$SID", null, '');
    chk('chống XSS: lưu dạng &lt;b&gt;', intval(q1("SELECT COUNT(*) FROM ggliaotian WHERE uid = ? AND msg = '&lt;b&gt;x&lt;/b&gt;'", array($UID))) === 1, '');
    go('Tán gẫu - riêng', "cmd=liaotian&ltlx=im&sid=$SID", null, '');
});

block('Bản đồ / Hảo hữu / Xem người chơi', function () {
    global $SID, $UID2;
    go('Bản đồ thế giới (allmap)', "cmd=allmap&sid=$SID", null, '');
    go('đi sang phải: 225 -> 226', "cmd=gomid&newmid=226&sid=$SID", null, '');
    go('Hảo hữu (im)', "cmd=im&sid=$SID", null, '');
    if ($UID2 > 0) go('xem thông tin người chơi khác', "cmd=getplayerinfo&uid=$UID2&sid=$SID", null, '');
    go('Thưởng online / hoạt động (gamnol)', "cmd=gamnol&sid=$SID", null, '');
    go('Kỳ ngộ (qydt)', "cmd=qydt&sid=$SID", null, '');
});

// ============================ 20. BẢO MẬT ============================
block('Bảo mật router', function () {
    global $SID;
    // extract() biến từ URL ghi đè $ym khi cmd không có trong $cmdMap => include file tùy ý
    fresh($SID);
    list($c, $b, $l) = e2e_http("cmd=khongco&ym=class/config/feature_unlock.json&sid=$SID", null);
    chk('cmd lạ + ym=<file> KHÔNG include file tùy ý (LFI)', strpos($b, '"features"') === false,
        strpos($b, '"features"') !== false ? 'trang in nội dung class/config/feature_unlock.json' : '');
    fresh($SID);
    list($c, $b, $l) = e2e_http("cmd=gomid&newmid=225&pvpts=" . rawurlencode('<script>e2exss()</script>') . "&sid=$SID", null);
    chk('biến $pvpts/$tpts không bị ghi đè từ URL (XSS phản xạ)', strpos($b, '<script>e2exss()</script>') === false, '');
    list($c, $b, $l) = e2e_http_raw("cmd=gomid&sid=../../etc");
    chk('sid không hợp lệ bị chặn', strpos($b, 'Thôn') === false, '');
});

// ============================ KHÔI PHỤC + DỌN ============================
/** Đưa boss thế giới đã đánh về đúng trạng thái chụp trước test (chạy 1 lần). */
function e2e_restore_boss() {
    global $db, $BOSS_SNAP;
    if (!$BOSS_SNAP) return;
    $sets = array(); $vals = array();
    foreach ($BOSS_SNAP as $k => $v) { if ($k === 'bossid') continue; $sets[] = "`$k` = ?"; $vals[] = $v; }
    $vals[] = $BOSS_SNAP['bossid'];
    $db->prepare("UPDATE boss SET " . implode(', ', $sets) . " WHERE bossid = ?")->execute($vals);
    echo "\n(Đã khôi phục boss {$BOSS_SNAP['bossid']} về trạng thái trước test)\n";
    $BOSS_SNAP = null;
}
e2e_restore_boss();
if (!in_array('--keep', $argv)) {
    $db->prepare("DELETE FROM arena_log WHERE attacker_sid IN (?, ?) OR defender_sid IN (?, ?)")->execute(array($SID, $SID2, $SID, $SID2));
    $db->prepare("DELETE FROM midguaiwu WHERE sid = ?")->execute(array($SID));
    $n = e2e_cleanup($db, $SID) + e2e_cleanup($db, $SID2);
    $db->prepare("DELETE FROM userinfo WHERE token IN (?, ?)")->execute(array($TOKEN, $TOKEN2)); // tài khoản test
    echo "Đã dọn $n dòng của 2 nhân vật test.\n";
}
if ($relaxProc) { proc_terminate($relaxProc); }

// ============================ TỔNG KẾT ============================
echo "\n================ TỔNG KẾT THEO TÍNH NĂNG ================\n";
$byF = array();
foreach ($RESULTS as $r) {
    if (!isset($byF[$r[1]])) $byF[$r[1]] = array(0, 0);
    $byF[$r[1]][$r[2] === 'PASS' ? 0 : 1]++;
}
foreach ($byF as $f => $c) echo '  ' . $f . str_repeat(' ', max(1, 60 - mb_strlen($f, 'UTF-8'))) . sprintf("PASS %3d  FAIL %3d\n", $c[0], $c[1]);
if (!empty($WARNINGS)) {
    echo "\n---- Cảnh báo PHP trong log (không tính FAIL; PHP 8 Warning ~ PHP 5.6 Notice) ----\n";
    foreach ($WARNINGS as $w => $steps) echo "  * $w\n      (bước: " . implode(', ', array_slice(array_keys($steps), 0, 3)) . ")\n";
}
$total = count($RESULTS);
echo "\nTổng: $total mục, PASS " . ($total - $fails) . ", FAIL $fails\n";
echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
