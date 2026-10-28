<?php
/**
 * e2e_full_lib.php — Thư viện dùng chung cho tests/test_e2e_full.php và tests/test_player_stats.php.
 * Tương thích PHP 5.6 (không dùng ??, không type hint, không closure ngắn).
 *
 * - Kết nối DB bằng pdo.php của repo (không in mật khẩu).
 * - Tạo / dọn nhân vật test RIÊNG (sid/uid mới, không đụng uid 547 / 990125 / 987650 / 543).
 * - Gọi route thật game.php qua HTTP (curl) và ghi nhận lỗi PHP trong php_error.log của bản chạy.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$E2E_ROOT = dirname(__DIR__);
require_once $E2E_ROOT . '/class/player.php';
require_once $E2E_ROOT . '/class/encode.php';
// pdo.php khai báo $dblj (persistent) — dùng chung, bật exception cho test.
require_once $E2E_ROOT . '/pdo.php';
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Uid KHÔNG được đụng tới (nhân vật thật + nhân vật test của người khác).
$E2E_PROTECTED_UIDS = array(543, 547, 987650, 990125);

/** Sid chuẩn game: md5(username . token . '229') — giống game.php case 'cjplayer'. */
function e2e_sid_for($uname, $token) {
    return md5($uname . $token . '229');
}

/** Bảo vệ: chỉ cho phép thao tác trên sid do test tạo (tiền tố tên E2E). */
function e2e_assert_own($db, $sid) {
    global $E2E_PROTECTED_UIDS;
    $st = $db->prepare("SELECT uid, uname, token FROM game1 WHERE sid = ?");
    $st->execute(array($sid));
    $r = $st->fetch(PDO::FETCH_ASSOC);
    if (!$r) return true;
    if (in_array(intval($r['uid']), $E2E_PROTECTED_UIDS, true) || strpos($r['token'], 'e2efull') !== 0) {
        fwrite(STDERR, "TU CHOI: sid $sid khong phai nhan vat test (uid {$r['uid']}).\n");
        exit(2);
    }
    return true;
}

/**
 * Chèn game1 cho nhân vật test giống hệt giá trị khởi tạo của cjplayer (cấp 1, 35 HP, 12 công, 5 thủ)
 * + điền đủ cột NOT NULL không có default (DB cloud bật STRICT_TRANS_TABLES).
 */
function e2e_insert_game1($db, $sid, $token, $uname, $extra) {
    $colsNN = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'game1'
          AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
          AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
    $vals = array();
    foreach ($colsNN as $c) {
        $isStr = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext', 'longtext'));
        $vals[$c['COLUMN_NAME']] = $isStr ? '' : 0;
    }
    $base = array(
        'sid' => $sid, 'token' => $token, 'uname' => $uname, 'ulv' => 1, 'uyxb' => 2000, 'uczb' => 100,
        'uexp' => 0, 'uhp' => 35, 'umaxhp' => 35, 'ustamina' => 100, 'umaxstamina' => 100,
        'ugj' => 12, 'ufy' => 5, 'uwx' => 0, 'usex' => 1, 'vip' => 0, 'nowmid' => 225,
        'endtime' => date('Y-m-d H:i:s'), 'sfzx' => 1, 'shenfen' => 1,
        'linhcan_id' => 0, 'linhcan_quality' => 0, 'thechat_id' => 0, 'thechat_quality' => 0,
        'huyetmach_id' => 0, 'huyetmach_quality' => 0,
    );
    foreach ($base as $k => $v) $vals[$k] = $v;
    foreach ($extra as $k => $v) $vals[$k] = $v;
    $cols = array_keys($vals);
    $ph = implode(',', array_fill(0, count($cols), '?'));
    $st = $db->prepare("INSERT INTO game1 (`" . implode('`,`', $cols) . "`) VALUES ($ph)");
    $st->execute(array_values($vals));
    \player\player_cache_clear($sid);
    return intval($db->lastInsertId());
}

/** Đọc 1 dòng game1 (mảng) — bỏ cache. */
function e2e_row($db, $sid) {
    $st = $db->prepare("SELECT * FROM game1 WHERE sid = ?");
    $st->execute(array($sid));
    return $st->fetch(PDO::FETCH_ASSOC);
}

/** Số lượng đạo cụ đang có. */
function e2e_dj($db, $sid, $djid) {
    $st = $db->prepare("SELECT COALESCE(SUM(djsum),0) FROM playerdaoju WHERE sid = ? AND djid = ?");
    $st->execute(array($sid, intval($djid)));
    return intval($st->fetchColumn());
}

/** Đặt số lượng đạo cụ (tạo dòng nếu chưa có — điền đủ cột NOT NULL). */
function e2e_set_dj($db, $sid, $uid, $djid, $sum) {
    $st = $db->prepare("SELECT COUNT(*) FROM playerdaoju WHERE sid = ? AND djid = ?");
    $st->execute(array($sid, intval($djid)));
    if (intval($st->fetchColumn()) > 0) {
        $db->prepare("UPDATE playerdaoju SET djsum = ? WHERE sid = ? AND djid = ?")->execute(array(intval($sum), $sid, intval($djid)));
    } else {
        $cfg = \player\daoju_config_get(intval($djid));
        $name = $cfg && isset($cfg['djname']) ? $cfg['djname'] : ('DJ ' . $djid);
        $db->prepare("INSERT INTO playerdaoju (djid, djsum, sid, uid, djname, djzl, djinfo) VALUES (?, ?, ?, ?, ?, 1, 'e2e')")
           ->execute(array(intval($djid), intval($sum), $sid, intval($uid), $name));
    }
}

/** Thêm trang bị theo zbid config vào túi (đủ cột NOT NULL). Trả zbnowid. */
function e2e_add_zb($db, $sid, $uid, $zbid, $qianghua, $element) {
    $cfg = \player\zhuangbei_config_get(intval($zbid));
    if (!$cfg) return 0;
    $st = $db->prepare("INSERT INTO playerzhuangbei (zbid, uid, sid, qianghua, zblv, zbtool, zbname, zbinfo,
        zbgj, zbfy, zbhp, zbbj, zbxx, zbys, taozhuang, element) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $st->execute(array(intval($zbid), intval($uid), $sid, intval($qianghua), intval($cfg['zblv']), intval($cfg['zbtool']),
        $cfg['zbname'], isset($cfg['zbinfo']) ? $cfg['zbinfo'] : '', intval($cfg['zbgj']), intval($cfg['zbfy']),
        intval($cfg['zbhp']), intval($cfg['zbbj']), intval($cfg['zbxx']), isset($cfg['zbys']) ? $cfg['zbys'] : '#fff',
        isset($cfg['taozhuang']) ? intval($cfg['taozhuang']) : 0, $element));
    \player\zhuangbei_cache_clear();
    return intval($db->lastInsertId());
}

/**
 * Lên cấp bằng đúng đường thành công của game/tupo.php (upplayerlv + cộng cố định theo cấp),
 * gọi hàm trực tiếp để có chỉ số gốc giống người chơi thật đã đột phá tới cấp $toLv.
 */
function e2e_level_to($db, $sid, $toLv) {
    for ($guard = 0; $guard < 5000; $guard++) {
        \player\player_cache_clear($sid);
        $p = \player\getplayer($sid, $db);
        if (intval($p->ulv) >= $toLv) break;
        $db->prepare("UPDATE game1 SET uexp = ? WHERE sid = ?")->execute(array(intval($p->umaxexp), $sid));
        \player\player_cache_clear($sid);
        $lv = intval($p->ulv);
        $uphp = 4 + round($lv / 1.2);
        $upgj = 2 + round($lv / 2.5);
        $upfy = 3 + round($lv / 2);
        \player\upplayerlv($sid, $db);
        \player\addplayersx('umaxhp', $uphp, $sid, $db);
        \player\addplayersx('ugj', $upgj, $sid, $db);
        \player\addplayersx('ufy', $upfy, $sid, $db);
        \player\addplayersx('tf', 5, $sid, $db);
    }
    \player\player_cache_clear($sid);
}

/**
 * Dọn sạch nhân vật test: mọi bảng có cột sid kiểu chuỗi (so sánh đúng kiểu),
 * bảng chỉ có uid (hoặc sid kiểu số) thì xóa theo uid của nhân vật test.
 */
function e2e_cleanup($db, $sid) {
    e2e_assert_own($db, $sid);
    $st = $db->prepare("SELECT uid FROM game1 WHERE sid = ?");
    $st->execute(array($sid));
    $uid = intval($st->fetchColumn());
    $cols = $db->query("SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME IN ('sid','uid')")->fetchAll(PDO::FETCH_ASSOC);
    $byTable = array();
    foreach ($cols as $c) { $byTable[$c['TABLE_NAME']][$c['COLUMN_NAME']] = $c['DATA_TYPE']; }
    $n = 0;
    foreach ($byTable as $t => $cs) {
        if ($t === 'game1') continue;
        $sidIsStr = isset($cs['sid']) && in_array($cs['sid'], array('varchar', 'text', 'char', 'mediumtext', 'longtext'));
        if ($sidIsStr) {
            $d = $db->prepare("DELETE FROM `$t` WHERE sid = ?");
            $d->execute(array($sid));
            $n += $d->rowCount();
        } elseif (isset($cs['uid']) && $uid > 0 && !in_array($uid, $GLOBALS['E2E_PROTECTED_UIDS'], true)) {
            $d = $db->prepare("DELETE FROM `$t` WHERE uid = ?");
            $d->execute(array($uid));
            $n += $d->rowCount();
        }
    }
    // Tông môn do nhân vật test lập (tên có tiền tố E2E)
    $db->exec("DELETE FROM club WHERE clubname LIKE 'E2EF%'");
    // Tin hệ thống chào mừng khi tạo nhân vật qua route thật
    $db->prepare("DELETE FROM ggliaotian WHERE uid = 0 AND msg LIKE ?")->execute(array('%E2EFull%'));
    $d = $db->prepare("DELETE FROM game1 WHERE sid = ?");
    $d->execute(array($sid));
    $n += $d->rowCount();
    \player\player_cache_clear();
    \player\zhuangbei_cache_clear();
    return $n;
}

// ===================== HTTP =====================
$E2E_BASE = getenv('E2E_BASE') ? rtrim(getenv('E2E_BASE'), '/') : 'http://127.0.0.1:8091';
$E2E_LOG  = getenv('E2E_LOG') ? getenv('E2E_LOG') : '/tmp/xxjrun/php_error.log';
$E2E_ENC  = new \encode\encode();

/** Kích thước hiện tại của log lỗi PHP bản chạy. */
function e2e_log_size() {
    global $E2E_LOG;
    clearstatcache();
    return is_file($E2E_LOG) ? filesize($E2E_LOG) : 0;
}

/** Dòng log mới kể từ offset (bỏ Deprecated — PHP 8 báo dynamic property, không có ở PHP 5.6). */
function e2e_log_since($off) {
    global $E2E_LOG;
    clearstatcache();
    if (!is_file($E2E_LOG)) return array();
    $sz = filesize($E2E_LOG);
    if ($sz <= $off) return array();
    $fh = fopen($E2E_LOG, 'r');
    fseek($fh, $off);
    $txt = fread($fh, $sz - $off);
    fclose($fh);
    $out = array();
    foreach (explode("\n", $txt) as $ln) {
        if (trim($ln) === '' || strpos($ln, 'PHP Deprecated') !== false) continue;
        if (strpos($ln, '[GA]') !== false) continue; // log debug AJAX, không phải lỗi
        $out[] = $ln;
    }
    return $out;
}

/**
 * Gọi route thật. $plain = "cmd=...&...&sid=..." (chưa mã hoá). $post = mảng POST hoặc null.
 * Trả array(code, body, logLines).
 */
function e2e_http($plain, $post) {
    global $E2E_BASE, $E2E_ENC;
    $url = $E2E_BASE . '/game.php?cmd=' . $E2E_ENC->encode($plain);
    $off = e2e_log_size();
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if (is_array($post)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = curl_exec($ch);
    $code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    $GLOBALS['E2E_LAST_REDIRECT'] = (string)curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    usleep(30000); // chờ server ghi log xong
    return array($code, (string)$body, e2e_log_since($off));
}

/** Gọi URL thô (không mã hoá) — dùng cho cjplayer / kiểm tra bảo mật. */
function e2e_http_raw($query) {
    global $E2E_BASE;
    $off = e2e_log_size();
    $ch = curl_init($E2E_BASE . '/game.php?' . $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    $body = curl_exec($ch);
    $code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    curl_close($ch);
    usleep(30000);
    return array($code, (string)$body, e2e_log_since($off));
}

/**
 * Đọc getplayer() trong 1 tiến trình PHP MỚI (không dính cache tĩnh của tiến trình test:
 * __zhuangbei_runtime_cache, static cache của wgcx(), cache Nhục Thân...). Trả object.
 */
function e2e_player_fresh($sid) {
    $out = shell_exec('php ' . escapeshellarg(__FILE__) . ' --dump ' . escapeshellarg($sid) . ' 2>/dev/null');
    $d = json_decode((string)$out, true);
    return is_array($d) ? (object)$d : new stdClass();
}

// Chế độ con: php tests/e2e_full_lib.php --dump <sid>  => in JSON chỉ số getplayer() (ngữ cảnh request mới)
if (PHP_SAPI === 'cli' && isset($argv[1]) && $argv[1] === '--dump' && realpath($argv[0]) === realpath(__FILE__)) {
    $GLOBALS['player'] = new \player\player();
    $p = \player\getplayer($argv[2], $dblj);
    $o = array();
    foreach (array('uid', 'ulv', 'ugj', 'ufy', 'umaxhp', 'ubj', 'uxx', 'uhp', 'umaxexp', 'jingjieValue', 'tianfu_power',
                   'club_bonus', 'wugong', 'cw', 'linhcan_element', 'thechat_stats', 'huyetmach_vessel') as $k) {
        $o[$k] = isset($p->$k) ? $p->$k : null;
    }
    $o['power'] = \player\combat_power($p);
    echo json_encode($o);
    exit(0);
}
