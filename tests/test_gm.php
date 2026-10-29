<?php
/**
 * test_gm.php — Trang GM mới (gm/gm.php) qua HTTP thật: đăng nhập, CSRF, tab Nhân vật / Phát quà / Giftcode / Vật phẩm / Nhật ký.
 * Cần: server chạy game (mặc định http://127.0.0.1:8091) + mã GM thật qua biến môi trường GM_TEST_CODE.
 *   Windows: set GM_TEST_CODE=<mã GM>   set GM_BASE=http://localhost/   rồi php tests\test_gm.php
 * Tự tạo + tự dọn nhân vật test (GmTestT*) và giftcode test (gmtest-*). Nhật ký GM của lần test giữ lại (đúng chức năng).
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../class/encode.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$db = $dblj;
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$enc = new \encode\encode();
$ROOTURL = rtrim(getenv('GM_BASE') ? getenv('GM_BASE') : 'http://127.0.0.1:8091/', '/') . '/';
$CODE = getenv('GM_TEST_CODE');
if (!$CODE) { echo "SKIP  (chưa đặt GM_TEST_CODE = mã GM)\n"; exit(0); }
$JAR = sys_get_temp_dir() . '/gm_test_cookie_' . getmypid() . '.txt';

$fails = 0;
function ttrue($name, $cond, $info = '') {
    global $fails;
    if ($cond) { echo "PASS  $name" . ($info !== '' ? " ($info)" : '') . "\n"; }
    else { $fails++; echo "FAIL  $name" . ($info !== '' ? " ($info)" : '') . "\n"; }
}
function http($path, $post = null, $jar = true) {
    global $ROOTURL, $JAR;
    usleep(60000);
    $ch = curl_init($ROOTURL . $path);
    $o = array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_FOLLOWLOCATION => true);
    if ($jar) { $o[CURLOPT_COOKIEJAR] = $JAR; $o[CURLOPT_COOKIEFILE] = $JAR; }
    if ($post !== null) { $o[CURLOPT_POST] = true; $o[CURLOPT_POSTFIELDS] = is_string($post) ? $post : http_build_query($post); }
    curl_setopt_array($ch, $o);
    $r = curl_exec($ch); curl_close($ch);
    return (string)$r;
}
function csrf($html) { return preg_match('/name="csrf" value="([0-9a-f]+)"/', $html, $m) ? $m[1] : ''; }
function q1($sql, $p = array()) { global $db; $st = $db->prepare($sql); $st->execute($p); return $st->fetchColumn(); }
function dj($sid, $id) { return intval(q1("SELECT COALESCE(SUM(djsum),0) FROM playerdaoju WHERE sid = ? AND djid = ?", array($sid, $id))); }
function t_clean($db) {
    $sids = $db->query("SELECT sid FROM game1 WHERE uname LIKE 'GmTestT%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($sids as $s) {
        foreach (array('playerdaoju', 'playerzhuangbei', 'playeryaopin', 'duihuan_log') as $t) {
            try { $db->prepare("DELETE FROM $t WHERE sid = ?")->execute(array($s)); } catch (Exception $e) {}
        }
    }
    $db->exec("DELETE FROM game1 WHERE uname LIKE 'GmTestT%'");
    $db->exec("DELETE FROM duihuan WHERE dhm LIKE 'gmtest-%'");
}
function t_char($db, $name, $lv) {
    $sid = substr(md5('gmtest_' . $name), 0, 32);
    $nn = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'game1' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
    $v = array();
    foreach ($nn as $c) $v[$c['COLUMN_NAME']] = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext')) ? '' : 0;
    $gc = \player\getgameconfig($db);
    $v = array_merge($v, array('token' => 'gmtest_' . $name, 'sid' => $sid, 'uname' => $name, 'ulv' => $lv, 'uyxb' => 1000, 'uczb' => 0, 'uexp' => 0,
        'uhp' => 100, 'umaxhp' => 100, 'ustamina' => 100, 'umaxstamina' => 100, 'ugj' => 50, 'ufy' => 50, 'uwx' => 0, 'usex' => 1, 'vip' => 0,
        'nowmid' => intval($gc->firstmid), 'endtime' => date('Y-m-d H:i:s'), 'sfzx' => 1, 'shenfen' => 1));
    $cols = array_keys($v);
    $db->prepare('INSERT INTO game1 (`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')')->execute(array_values($v));
    return $sid;
}

t_clean($db);
$sid = t_char($db, 'GmTestT1', 40);
$uid = intval(q1("SELECT uid FROM game1 WHERE sid = ?", array($sid)));
@unlink($JAR);

// ── Đăng nhập / bảo vệ ────────────────────────────────────────
$h = http('gm/gm.php');
ttrue('G1 chưa đăng nhập: hiện form mã GM', strpos($h, 'name="gmcode"') !== false && strpos($h, 'Nhân vật</a>') === false);
$h = http('gm/gm.php', array('gmcode' => 'sai-ma-' . mt_rand()));
ttrue('G1 mã sai: báo "Mã GM sai", không vào được', strpos($h, 'Mã GM sai') !== false);
$h = http('gm/tab_player.php');
ttrue('G1 mở thẳng gm/tab_player.php: không lộ gì', trim(strip_tags($h)) === '');
ttrue('G1 file cũ đã xoá (gm/gmquery.php, gm/playerquery.php)', strpos(http('gm/gmquery.php'), 'errcode') === false && strpos(http('gm/playerquery.php'), 'errcode') === false);
$h = http('gm/gm.php', array('gmcode' => $CODE));
ttrue('G1 mã đúng: vào trang GM có 5 tab', strpos($h, 'Phát quà') !== false && strpos($h, 'Giftcode') !== false && strpos($h, 'Nhật ký') !== false);
$tok = csrf(http('gm/gm.php?tab=player&sid=' . $sid));
ttrue('G1 có CSRF token', strlen($tok) === 32);
$h = http('gm/gm.php?tab=player&sid=' . $sid, array('act' => 'edit', 'v_ulv' => 99, 'm_ulv' => 'set', 'csrf' => 'sai'));
ttrue('G1 POST thiếu/sai CSRF: bị chặn, không sửa', strpos($h, 'form không hợp lệ') !== false && intval(q1("SELECT ulv FROM game1 WHERE sid = ?", array($sid))) === 40);

// ── Nhân vật ──────────────────────────────────────────────────
$h = http('gm/gm.php?tab=player&q=GmTestT1');
ttrue('G2 tìm theo tên ra nhân vật test', strpos($h, 'GmTestT1') !== false && strpos($h, 'Xem / sửa') !== false);
$h = http('gm/gm.php?tab=player&q=' . $uid);
ttrue('G2 tìm theo uid', strpos($h, 'GmTestT1') !== false);
$h = http('gm/gm.php?tab=player&sid=' . $sid, array('act' => 'edit', 'csrf' => $tok, 'v_ulv' => 77, 'm_ulv' => 'set', 'v_uyxb' => 500, 'm_uyxb' => 'add'));
ttrue('G2 sửa: cấp đặt 77, LT cộng 500', intval(q1("SELECT ulv FROM game1 WHERE sid = ?", array($sid))) === 77 && intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))) === 1500);
ttrue('G2 thông báo trước → sau', strpos($h, 'Cấp: 40 → 77') !== false);
$h = http('gm/gm.php?tab=player&sid=' . $sid, array('act' => 'edit', 'csrf' => $tok, 'v_ulv' => 9999, 'm_ulv' => 'set'));
ttrue('G2 cấp ngoài 1–4300: từ chối', intval(q1("SELECT ulv FROM game1 WHERE sid = ?", array($sid))) === 77 && strpos($h, '1–4300') !== false);
\player\adddj($sid, 1, 20, $db);
http('gm/gm.php?tab=player&sid=' . $sid, array('act' => 'djset', 'csrf' => $tok, 'djid' => 1, 'n' => 7));
ttrue('G2 đặt số lượng Cường Hóa Thạch 20 → 7', dj($sid, 1) === 7);
$h = http('gm/gm.php?tab=player&sid=' . $sid);
ttrue('G2 trang chi tiết hiện túi đồ + trang bị + Tân thủ', strpos($h, 'Cường Hóa Thạch') !== false && strpos($h, 'Trang bị (') !== false && strpos($h, 'Tân thủ') !== false);

// ── Phát quà 2 bước ───────────────────────────────────────────
$zb0 = intval(q1("SELECT COUNT(*) FROM playerzhuangbei WHERE sid = ?", array($sid)));
$gift = array('csrf' => $tok, 'to' => "GmTestT1\nKhongCoAiTen" . mt_rand(), 'lt' => 300, 'mt' => 7, 'exp' => 0,
    'dj_id' => array('1 · Cường Hóa Thạch', ''), 'dj_n' => array(5, ''), 'yp_id' => array(''), 'yp_n' => array(''), 'zb_id' => array('91 · Thánh Phi Phong'), 'zb_n' => array(2));
$h = http('gm/gm.php?tab=gift', array_merge($gift, array('act' => 'preview')));
ttrue('G3 xem trước: 1 nhân vật, báo tên không tìm thấy, chưa phát', strpos($h, 'Xác nhận phát quà') !== false && strpos($h, 'Không tìm thấy') !== false
    && strpos($h, '1 nhân vật:') !== false && intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))) === 1500);
$h = http('gm/gm.php?tab=gift', array_merge($gift, array('act' => 'send')));
ttrue('G3 phát: +300 LT, +7 MT', intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))) === 1800 && intval(q1("SELECT uczb FROM game1 WHERE sid = ?", array($sid))) === 7);
ttrue('G3 phát: +5 Cường Hóa Thạch, +2 Thánh Phi Phong', dj($sid, 1) === 12 && intval(q1("SELECT COUNT(*) FROM playerzhuangbei WHERE sid = ? AND zbid = 91", array($sid))) === 2);
$h = http('gm/gm.php?tab=gift', array('act' => 'preview', 'csrf' => $tok, 'to' => 'GmTestT1', 'dj_id' => array('999999'), 'dj_n' => array(1)));
ttrue('G3 vật phẩm không tồn tại / phần thưởng trống: báo lỗi', strpos($h, 'Không có vật phẩm') !== false && strpos($h, 'Chưa chọn phần thưởng') !== false);

// ── Giftcode: tạo → nhập thật trong game → đếm lượt → sửa → xoá ─
$code = 'gmtest-' . mt_rand(1000, 9999);
$h = http('gm/gm.php?tab=code', array('act' => 'save', 'csrf' => $tok, 'id' => 0, 'dhm' => strtoupper($code), 'dhname' => 'Gói GM test', 'lt' => 250, 'mt' => 0, 'exp' => 0,
    'maxday' => 0, 'maxlv' => 0, 'dj_id' => array('8 · Ngạnh Sí Phong Mật'), 'dj_n' => array(4), 'yp_id' => array(''), 'yp_n' => array(''), 'zb_id' => array('')));
$row = $db->prepare("SELECT * FROM duihuan WHERE dhm = ?"); $row->execute(array($code)); $row = $row->fetch(PDO::FETCH_ASSOC);
ttrue('G4 tạo giftcode (mã chữ hoa tự về chữ thường)', $row && $row['dhdj'] === '8|4' && intval($row['dhyxb']) === 250);
$h = http('gm/gm.php?tab=code', array('act' => 'save', 'csrf' => $tok, 'id' => 0, 'dhm' => $code, 'dhname' => 'Trùng', 'dj_id' => array(''), 'dj_n' => array('')));
ttrue('G4 tạo trùng mã: từ chối', strpos($h, 'đã tồn tại') !== false && intval(q1("SELECT COUNT(*) FROM duihuan WHERE dhm = ?", array($code))) === 1);
$a = intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid)));
http('game.php?cmd=' . $enc->encode("cmd=duihuan&dhm=$code&sid=$sid"), null, false);
ttrue('G4 nhân vật nhập mã trong game: +250 LT, +4 Ngạnh Sí Phong Mật', intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))) === $a + 250 && dj($sid, 8) === 4);
$h = http('gm/gm.php?tab=code&q=' . $code);
ttrue('G4 danh sách: hiện tên vật phẩm + 1 lượt nhập', strpos($h, 'Ngạnh Sí Phong Mật ×4') !== false && strpos($h, '>1</a>') !== false);
$h = http('gm/gm.php?tab=code&act=used&dhm=' . $code);
ttrue('G4 xem ai đã nhập: có GmTestT1', strpos($h, 'GmTestT1') !== false);
http('gm/gm.php?tab=code', array('act' => 'save', 'csrf' => $tok, 'id' => $row['dhid'], 'dhm' => $code, 'dhname' => 'Gói GM test (sửa)', 'lt' => 100,
    'dj_id' => array('1 · Cường Hóa Thạch', '8 · Ngạnh Sí Phong Mật'), 'dj_n' => array(2, 3), 'zb_id' => array('91 · Thánh Phi Phong')));
$r2 = $db->prepare("SELECT * FROM duihuan WHERE dhid = ?"); $r2->execute(array($row['dhid'])); $r2 = $r2->fetch(PDO::FETCH_ASSOC);
ttrue('G4 sửa: tên, LT, 2 đạo cụ, 1 trang bị', $r2['dhname'] === 'Gói GM test (sửa)' && intval($r2['dhyxb']) === 100 && $r2['dhdj'] === '1|2,8|3' && $r2['dhzb'] === '91');
http('gm/gm.php?tab=code', array('act' => 'delete', 'csrf' => $tok, 'id' => $row['dhid']));
ttrue('G4 xoá giftcode', intval(q1("SELECT COUNT(*) FROM duihuan WHERE dhid = ?", array($row['dhid']))) === 0);

// ── Vật phẩm ──────────────────────────────────────────────────
$h = http('gm/gm.php?tab=items&k=dj&q=' . urlencode('Tân Thủ'));
ttrue('G5 tra đạo cụ "Tân Thủ": thấy 118 khóa giao dịch, không bán NPC', strpos($h, 'Tân Thủ Tu Vi Đan') !== false && strpos($h, 'khóa giao dịch') !== false && strpos($h, 'không bán NPC') !== false);
$h = http('gm/gm.php?tab=items&k=dj&q=' . urlencode('Trúc Cơ Đột Phá'));
ttrue('G5 Trúc Cơ Đột Phá Đan: bán NPC 10% + nguồn rơi Boss', strpos($h, 'bán NPC 10%') !== false && strpos($h, 'Boss ') !== false);
$h = http('gm/gm.php?tab=items&k=zb&q=91');
ttrue('G5 tra trang bị: Thánh Phi Phong có phẩm / cấp / chỉ số', strpos($h, 'Thánh Phi Phong') !== false && strpos($h, 'cấp 200') !== false);
$h = http('gm/gm.php?tab=items&k=yp');
ttrue('G5 tab dược phẩm hiện được', strpos($h, 'Dược phẩm:') !== false);
$h = http('gm/gm.php?tab=items&k=dj&f=code');
ttrue('G5 lọc "Có trong giftcode": thấy mã tanthu', strpos($h, '>tanthu<') !== false || intval(q1("SELECT COUNT(*) FROM duihuan WHERE dhm = 'tanthu'")) === 0);

// ── Nhật ký + đăng xuất ───────────────────────────────────────
$h = http('gm/gm.php?tab=log&q=GmTestT1');
ttrue('G6 nhật ký có: sửa chỉ số + sửa túi đồ + phát quà', strpos($h, 'Sửa chỉ số') !== false && strpos($h, 'Sửa túi đồ') !== false && strpos($h, 'Phát quà') !== false);
$h = http('gm/gm.php?tab=log&a=code_new');
ttrue('G6 nhật ký có tạo giftcode test', strpos($h, $code) !== false);
http('gm/gm.php?act=logout');
$h = http('gm/gm.php?tab=player');
ttrue('G6 đăng xuất: quay về form mã GM', strpos($h, 'name="gmcode"') !== false);

t_clean($db);
@unlink($JAR);
echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
