<?php
/**
 * gm/lib.php — nền trang GM (gm/gm.php): đăng nhập phiên, CSRF, nhật ký gm_log, danh mục vật phẩm, tiện ích.
 * Dùng chung hàm game (class/player.php) để phát quà y như giftcode / rơi đồ thật. DB lấy từ pdo.php (không ghi cứng mật khẩu).
 * PHP 5.6.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '0');
date_default_timezone_set(date_default_timezone_get());
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db = $dblj;

define('GM_IDLE_SECONDS', 2 * 3600);   // tự đăng xuất sau 2 giờ không thao tác
header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

session_name('XXJGM');
session_start();

function gm_h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function gm_ip() { return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli'; }
function gm_num($v) { return number_format(floatval($v), 0, ',', '.'); }
function gm_req($k, $d = '') { return isset($_REQUEST[$k]) ? (is_array($_REQUEST[$k]) ? $_REQUEST[$k] : trim((string)$_REQUEST[$k])) : $d; }
function gm_url($params) { return 'gm.php?' . http_build_query($params); }

// ── Nhật ký ───────────────────────────────────────────────────
function gm_log_ready($db) {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS gm_log (
            id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at DATETIME NOT NULL,
            ip VARCHAR(64) NOT NULL DEFAULT '',
            action VARCHAR(32) NOT NULL DEFAULT '',
            target VARCHAR(255) NOT NULL DEFAULT '',
            detail TEXT,
            PRIMARY KEY (id),
            KEY action (action)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
        $ok = true;
    } catch (Exception $e) { $ok = false; }
    return $ok;
}
function gm_log($db, $action, $target, $detail) {
    if (!gm_log_ready($db)) return;
    $db->prepare("INSERT INTO gm_log (created_at, ip, action, target, detail) VALUES (?, ?, ?, ?, ?)")
       ->execute(array(date('Y-m-d H:i:s'), gm_ip(), $action, mb_substr((string)$target, 0, 255, 'UTF-8'),
           is_string($detail) ? $detail : json_encode($detail, JSON_UNESCAPED_UNICODE)));
}

// ── Đăng nhập / CSRF ──────────────────────────────────────────
function gm_logged_in() {
    if (empty($_SESSION['gm_ok'])) return false;
    if (time() - intval($_SESSION['gm_last']) > GM_IDLE_SECONDS) { $_SESSION = array(); return false; }
    $_SESSION['gm_last'] = time();
    return true;
}
function gm_login($db, $code) {
    $fails = isset($_SESSION['gm_fail']) ? intval($_SESSION['gm_fail']) : 0;
    if ($fails >= 3) sleep(min(10, $fails));   // chậm dần khi nhập sai nhiều
    if ($code !== '' && password_verify($code, GM_CODE_HASH)) {
        session_regenerate_id(true);
        $_SESSION = array('gm_ok' => 1, 'gm_last' => time(), 'gm_csrf' => bin2hex(gm_random(16)));
        gm_log($db, 'login', '', 'ok');
        return true;
    }
    $_SESSION['gm_fail'] = $fails + 1;
    gm_log($db, 'login_fail', '', 'sai mã GM');
    return false;
}
function gm_random($n) {
    if (function_exists('random_bytes')) return random_bytes($n);
    if (function_exists('openssl_random_pseudo_bytes')) return openssl_random_pseudo_bytes($n);
    $s = ''; for ($i = 0; $i < $n; $i++) $s .= chr(mt_rand(0, 255)); return $s;
}
function gm_csrf_field() { return '<input type="hidden" name="csrf" value="' . gm_h($_SESSION['gm_csrf']) . '">'; }
function gm_csrf_ok() {
    $t = isset($_POST['csrf']) ? (string)$_POST['csrf'] : '';
    return $t !== '' && isset($_SESSION['gm_csrf']) && hash_equals_compat($_SESSION['gm_csrf'], $t);
}
function hash_equals_compat($a, $b) {
    if (function_exists('hash_equals')) return hash_equals($a, $b);
    if (strlen($a) !== strlen($b)) return false;
    $r = 0; for ($i = 0; $i < strlen($a); $i++) $r |= ord($a[$i]) ^ ord($b[$i]); return $r === 0;
}

// ── Danh mục vật phẩm ─────────────────────────────────────────
function gm_daoju_all() { $a = \player\daoju_config_all(); return isset($a['daoju']) ? $a['daoju'] : $a; }
function gm_yaopin_all() { return \player\yaopin_config_all(); }
function gm_zb_all() { $a = \player\zhuangbei_config_all(); return isset($a['zhuangbei']) ? $a['zhuangbei'] : $a; }
function gm_name($kind, $id) {
    $id = (string)intval($id);
    if ($kind === 'dj') { $a = gm_daoju_all(); return isset($a[$id]['djname']) ? $a[$id]['djname'] : ('Đạo cụ #' . $id); }
    if ($kind === 'yp') { $a = gm_yaopin_all(); return isset($a[$id]['ypname']) ? $a[$id]['ypname'] : ('Dược phẩm #' . $id); }
    $a = gm_zb_all(); return isset($a[$id]['zbname']) ? $a[$id]['zbname'] : ('Trang bị #' . $id);
}
function gm_exists($kind, $id) {
    $id = (string)intval($id);
    if ($kind === 'dj') { $a = gm_daoju_all(); return isset($a[$id]); }
    if ($kind === 'yp') { $a = gm_yaopin_all(); return isset($a[$id]); }
    $a = gm_zb_all(); return isset($a[$id]);
}
// <datalist> để chọn vật phẩm bằng tên (gõ tên hoặc id)
function gm_datalists() {
    $out = '<datalist id="dl-dj">';
    foreach (gm_daoju_all() as $id => $d) if (is_array($d)) $out .= '<option value="' . intval($id) . ' · ' . gm_h($d['djname']) . '">';
    $out .= '</datalist><datalist id="dl-yp">';
    foreach (gm_yaopin_all() as $id => $d) if (is_array($d)) $out .= '<option value="' . intval($id) . ' · ' . gm_h($d['ypname']) . '">';
    $out .= '</datalist><datalist id="dl-zb">';
    foreach (gm_zb_all() as $id => $d) if (is_array($d)) $out .= '<option value="' . intval($id) . ' · ' . gm_h($d['zbname']) . ' (phẩm ' . intval(isset($d['rarity']) ? $d['rarity'] : 0) . ', cấp ' . intval(isset($d['realm_require']) ? $d['realm_require'] : 0) . ')">';
    return $out . '</datalist>';
}
// Ô nhập danh sách thưởng: nhiều dòng [vật phẩm][số lượng]; $rows = array(array(id, n), ...)
function gm_reward_rows($name, $kind, $rows, $min = 4, $withCount = true) {
    $html = '<div class="rows" data-name="' . gm_h($name) . '" data-kind="' . $kind . '" data-count="' . ($withCount ? 1 : 0) . '">';
    $n = max($min, count($rows) + 1);
    for ($i = 0; $i < $n; $i++) {
        $id = isset($rows[$i][0]) ? intval($rows[$i][0]) : 0;
        $c = isset($rows[$i][1]) ? intval($rows[$i][1]) : '';
        $val = $id > 0 ? $id . ' · ' . gm_name($kind, $id) : '';
        $html .= '<div class="row"><input class="pick" list="dl-' . $kind . '" name="' . $name . '_id[]" value="' . gm_h($val) . '" placeholder="Gõ tên hoặc id…">'
            . ($withCount ? '<input class="cnt" type="number" min="1" name="' . $name . '_n[]" value="' . gm_h($c) . '" placeholder="SL">' : '') . '</div>';
    }
    return $html . '<button type="button" class="btn ghost addrow">+ dòng</button></div>';
}
// Đọc lại các dòng thưởng từ form ⇒ array(array(id, n)); bỏ dòng trống / id không tồn tại (ghi vào $errors)
function gm_parse_rows($name, $kind, $withCount, &$errors) {
    $ids = isset($_POST[$name . '_id']) && is_array($_POST[$name . '_id']) ? $_POST[$name . '_id'] : array();
    $ns = isset($_POST[$name . '_n']) && is_array($_POST[$name . '_n']) ? $_POST[$name . '_n'] : array();
    $out = array();
    foreach ($ids as $i => $raw) {
        $raw = trim((string)$raw);
        if ($raw === '') continue;
        $id = intval($raw);
        $n = $withCount ? (isset($ns[$i]) ? intval($ns[$i]) : 0) : 1;
        if ($id <= 0 || !gm_exists($kind, $id)) { $errors[] = 'Không có vật phẩm "' . $raw . '"'; continue; }
        if ($n <= 0) { $errors[] = 'Số lượng của "' . gm_name($kind, $id) . '" phải > 0'; continue; }
        $out[] = array($id, $n);
    }
    return $out;
}
// Chuỗi giftcode (dhdj / dhyp = "id|n,id|n", dhzb = "id,id") <-> mảng
function gm_rows_to_str($rows, $withCount = true) {
    $p = array();
    foreach ($rows as $r) $p[] = $withCount ? (intval($r[0]) . '|' . intval($r[1])) : (string)intval($r[0]);
    return implode(',', $p);
}
function gm_str_to_rows($s, $withCount = true) {
    $out = array();
    foreach (explode(',', (string)$s) as $part) {
        $part = trim($part);
        if ($part === '' || $part === '0') continue;
        $x = explode('|', $part);
        $id = intval($x[0]);
        if ($id <= 0) continue;
        $out[] = array($id, $withCount ? (isset($x[1]) ? intval($x[1]) : 1) : 1);
    }
    return $out;
}
function gm_rows_text($rows, $kind, $withCount = true) {
    $p = array();
    foreach ($rows as $r) $p[] = gm_h(gm_name($kind, $r[0])) . ($withCount ? ' ×' . intval($r[1]) : '');
    return implode(', ', $p);
}
// Phát thưởng cho 1 nhân vật bằng hàm game (như giftcode): $rw = array(lt, mt, exp, dj, yp, zb)
function gm_grant($db, $sid, $rw) {
    if ($rw['lt'] > 0) \player\changeyxb(1, $rw['lt'], $sid, $db);
    if ($rw['mt'] > 0) \player\changeczb(1, $rw['mt'], $sid, $db);
    if ($rw['exp'] > 0) $db->prepare("UPDATE game1 SET uexp = uexp + ? WHERE sid = ?")->execute(array($rw['exp'], $sid));
    foreach ($rw['dj'] as $r) \player\adddj($sid, $r[0], $r[1], $db);
    foreach ($rw['yp'] as $r) \player\addyaopin($sid, $r[0], $r[1], $db);
    foreach ($rw['zb'] as $r) for ($k = 0; $k < max(1, $r[1]); $k++) \player\addzb($sid, $r[0], $db);
    \player\player_cache_clear($sid);
}
function gm_reward_text($rw) {
    $p = array();
    if ($rw['lt'] > 0) $p[] = gm_num($rw['lt']) . ' LT';
    if ($rw['mt'] > 0) $p[] = gm_num($rw['mt']) . ' MT';
    if ($rw['exp'] > 0) $p[] = gm_num($rw['exp']) . ' tu vi';
    if ($rw['dj']) $p[] = gm_rows_text($rw['dj'], 'dj');
    if ($rw['yp']) $p[] = gm_rows_text($rw['yp'], 'yp');
    if ($rw['zb']) $p[] = gm_rows_text($rw['zb'], 'zb');
    return $p ? implode(' · ', $p) : '(trống)';
}
// Tìm nhân vật theo tên / uid / sid
function gm_find_player($db, $key) {
    $key = trim((string)$key);
    if ($key === '') return false;
    $st = $db->prepare("SELECT sid, uid, uname, ulv FROM game1 WHERE uname = ? OR sid = ? OR (uid = ? AND ? <> '') LIMIT 1");
    $st->execute(array($key, $key, ctype_digit($key) ? intval($key) : -1, ctype_digit($key) ? '1' : ''));
    return $st->fetch(PDO::FETCH_ASSOC);
}
function gm_has_col($db, $table, $col) {
    $st = $db->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
    $st->execute(array($table, $col));
    return intval($st->fetchColumn()) > 0;
}
