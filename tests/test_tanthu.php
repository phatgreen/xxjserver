<?php
/**
 * test_tanthu.php — Quà Tân Thủ 3 ngày (docs/DESIGN_TANTHU_3NGAY.md), đi qua route game.php thật:
 *   tạo nhân vật (cmd=cjplayer) → giftcode TANTHU / NHOMTELE (điều kiện ngày tuổi, cấp) → mở túi / thẻ ×2 (use_dj)
 *   → điểm danh + mốc trực tuyến (nhiemvu claim_tanthu) → Tân Thủ Tu Vi Đan (trần cấp theo ngày, hết hạn ngày 4)
 *   → trả phí đột phá từ Túi Tân Thủ → Đột Phá Liên Tục (theo chuỗi tự tải trang) → khóa bán / giao dịch.
 * CẦN DB đã chạy sql/migrate_tanthu.php --apply + server thử 127.0.0.1:8091 (bash tools/cloud/start_env.sh).
 * Tự tạo + tự dọn nhân vật test (tên TanThuT*). Chạy: php tests/test_tanthu.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../class/encode.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$db = $dblj;
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$enc = new \encode\encode();
$BASE = getenv('TT_BASE') ? getenv('TT_BASE') : 'http://127.0.0.1:8091/game.php?cmd=';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}
function ttrue($name, $cond, $info = '') {
    global $fails;
    if ($cond) { echo "PASS  $name" . ($info !== '' ? " ($info)" : '') . "\n"; }
    else { $fails++; echo "FAIL  $name" . ($info !== '' ? " ($info)" : '') . "\n"; }
}
function page($cmd) {
    global $enc, $BASE;
    usleep(120000);   // tránh anti-flood của game.php
    $ch = curl_init($BASE . $enc->encode($cmd));
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30));
    $r = curl_exec($ch); curl_close($ch);
    return (string)$r;
}
function raw($url) {   // URL đã mã hóa sẵn (lấy từ trang)
    global $BASE;
    usleep(120000);
    $ch = curl_init(preg_replace('/\?cmd=.*$/', '', $BASE) . $url);
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30));
    $r = curl_exec($ch); curl_close($ch);
    return (string)$r;
}
function q1($sql, $p = array()) { global $db; $st = $db->prepare($sql); $st->execute($p); return $st->fetchColumn(); }
function dj($sid, $id) { return intval(q1("SELECT COALESCE(SUM(djsum),0) FROM playerdaoju WHERE sid = ? AND djid = ?", array($sid, $id))); }
function setp($sid, $sets) {
    global $db;
    $cols = array(); $vals = array();
    foreach ($sets as $k => $v) { $cols[] = "`$k` = ?"; $vals[] = $v; }
    $vals[] = $sid;
    $db->prepare("UPDATE game1 SET " . implode(', ', $cols) . " WHERE sid = ?")->execute($vals);
    \player\player_cache_clear($sid);
}
function t_clean($db) {
    $sids = $db->query("SELECT sid FROM game1 WHERE uname LIKE 'TanThuT%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($sids as $s) {
        foreach (array('playerdaoju', 'player_tanthu', 'player_tanthu_claim', 'duihuan_log', 'player_online_reward', 'player_nhiemvu_main') as $t) {
            try { $db->prepare("DELETE FROM $t WHERE sid = ?")->execute(array($s)); } catch (Exception $e) {}
        }
    }
    $db->exec("DELETE FROM game1 WHERE uname LIKE 'TanThuT%'");
    $db->exec("DELETE FROM userinfo WHERE username LIKE 'tanthut%'");
}
// Tạo nhân vật qua route thật cmd=cjplayer (token phải có trong userinfo)
function t_create($db, $name) {
    $token = md5('tanthu-token-' . $name . microtime());
    $db->prepare("INSERT INTO userinfo (username, userpass, token, ip) VALUES (?, '', ?, '')")->execute(array(strtolower($name), $token));
    page("cmd=cjplayer&token=$token&username=$name&sex=1&shenfen=1");   // form tạo nhân vật luôn gửi shenfen (môn phái)
    return md5($name . $token . '229');
}

if (!\player\tanthu_ready($db)) { echo "SKIP  (chưa chạy php sql/migrate_tanthu.php --apply)\n"; exit(0); }
if (page('cmd=gomid') === '') { echo "SKIP  (server thử 127.0.0.1:8091 chưa bật — bash tools/cloud/start_env.sh)\n"; exit(0); }
t_clean($db);
$PILL = \player\tanthu_item('pill'); $BAG = \player\tanthu_item('bag'); $X2 = \player\tanthu_item('x2');

// ── T1. Tạo nhân vật: có ngày tạo, ngày tuổi 1 ─────────────────
$sid = t_create($db, 'TanThuT1');
tassert('T1 tạo nhân vật qua cmd=cjplayer', intval(q1("SELECT COUNT(*) FROM game1 WHERE sid = ?", array($sid))), 1);
ttrue('T1 player_tanthu.created_at = hôm nay', substr((string)q1("SELECT created_at FROM player_tanthu WHERE sid = ?", array($sid)), 0, 10) === date('Y-m-d'));
tassert('T1 ngày tuổi = 1', \player\tanthu_day($sid, $db), 1);
$old = $db->query("SELECT g.sid FROM game1 g LEFT JOIN player_tanthu t ON t.sid = g.sid WHERE t.sid IS NULL AND g.uname NOT LIKE 'TanThuT%' LIMIT 1")->fetchColumn();
if ($old) tassert('T1 nhân vật cũ (không có dòng player_tanthu): ngày tuổi 0', \player\tanthu_day($old, $db), 0);

// ── T2. Giftcode ───────────────────────────────────────────────
$h = page("cmd=duihuan&dhm=tanthu&sid=$sid");
tassert('T2 TANTHU: +50 Tân Thủ Tu Vi Đan', dj($sid, $PILL), 50);
tassert('T2 TANTHU: +3 Túi Linh Thạch', dj($sid, $BAG), 3);
$h = page("cmd=duihuan&dhm=tanthu&sid=$sid");
ttrue('T2 TANTHU nhập lại: báo đã dùng, không cộng', dj($sid, $PILL) === 50 && strpos($h, 'đã được sử dụng') !== false);
page("cmd=duihuan&dhm=nhomtele&sid=$sid");
tassert('T2 NHOMTELE: +1 Thẻ ×2', dj($sid, $X2), 1);
tassert('T2 NHOMTELE: tổng 80 Tu Vi Đan', dj($sid, $PILL), 80);
tassert('T2 NHOMTELE: tổng 5 Túi', dj($sid, $BAG), 5);
$s2 = t_create($db, 'TanThuT2');
$db->prepare("UPDATE player_tanthu SET created_at = ? WHERE sid = ?")->execute(array(date('Y-m-d H:i:s', strtotime('-3 days')), $s2));
$h = page("cmd=duihuan&dhm=tanthu&sid=$s2");
ttrue('T2 nhân vật ngày 4: giftcode tân thủ bị từ chối', dj($s2, $PILL) === 0 && strpos($h, '3 ngày đầu') !== false);
$db->prepare("UPDATE player_tanthu SET created_at = NOW() WHERE sid = ?")->execute(array($s2));
setp($s2, array('ulv' => 301));
$h = page("cmd=duihuan&dhm=tanthu&sid=$s2");
ttrue('T2 cấp 301: giftcode tân thủ bị từ chối', dj($s2, $PILL) === 0 && strpos($h, 'cấp 300 trở xuống') !== false);

// ── T3. Khóa: không bán NPC, không treo Phường Thị ─────────────
$uid = intval(q1("SELECT uid FROM game1 WHERE sid = ?", array($sid)));
$a = intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid)));
page("cmd=getbagdj&canshu=maichu1&djid=$PILL&yeshu=1&uid=$uid&sid=$sid");
ttrue('T3 bán NPC Tu Vi Đan: không nhận LT, không mất đan', dj($sid, $PILL) === 80 && intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))) === $a);
$h = page("cmd=djinfo&canshu=chushou&djid=$BAG&djcount=1&pay=100&sid=$sid");
ttrue('T3 treo Phường Thị Túi LT: bị chặn', dj($sid, $BAG) === 5 && strpos($h, 'bị khóa') !== false
    && intval(q1("SELECT COUNT(*) FROM fangshi_dj WHERE uid = ? AND djid = ?", array($uid, $BAG))) === 0);

// ── T4. Mở túi + thẻ ×2 ────────────────────────────────────────
page("cmd=use_dj&djid=$BAG&tatca=1&sid=$sid");
tassert('T4 "Mở hết" 5 túi: Túi Tân Thủ = 2.500.000', intval(q1("SELECT lt FROM player_tanthu WHERE sid = ?", array($sid))), 2500000);
tassert('T4 hết túi trong túi đồ', dj($sid, $BAG), 0);
tassert('T4 LT thường không đổi', intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))), $a);
page("cmd=use_dj&djid=$X2&sid=$sid");
$until = strtotime((string)q1("SELECT x2_until FROM player_tanthu WHERE sid = ?", array($sid)));
ttrue('T4 Thẻ ×2: hạn ~72 giờ', abs($until - (time() + 72 * 3600)) < 120, date('Y-m-d H:i', $until));
// 1 lượt tu luyện thường 30' thật ở cấp 50: không thẻ = 90.000, có thẻ = 180.000 (thanh 286.110)
setp($sid, array('ulv' => 50, 'uexp' => 0, 'sfxl' => 1, 'xiuliantime' => date('Y-m-d H:i:s', time() - 31 * 60)));
page("cmd=endxiulian&sid=$sid");
tassert('T4 tu luyện 30\' cấp 50 có Thẻ ×2 = 180.000 tu vi', intval(q1("SELECT uexp FROM game1 WHERE sid = ?", array($sid))), 180000);
setp($s2, array('ulv' => 50, 'uexp' => 0, 'sfxl' => 1, 'xiuliantime' => date('Y-m-d H:i:s', time() - 31 * 60)));
page("cmd=endxiulian&sid=$s2");
tassert('T4 đối chứng không thẻ = 90.000 tu vi', intval(q1("SELECT uexp FROM game1 WHERE sid = ?", array($s2))), 90000);

// ── T5. Điểm danh + mốc trực tuyến ─────────────────────────────
$p0 = dj($sid, $PILL); $b0 = dj($sid, $BAG);
page("cmd=nhiemvu&action=claim_tanthu&tkind=daily&tab=online&sid=$sid");
ttrue('T5 điểm danh ngày 1: +30 đan, +1 túi', dj($sid, $PILL) - $p0 === 30 && dj($sid, $BAG) - $b0 === 1);
page("cmd=nhiemvu&action=claim_tanthu&tkind=daily&tab=online&sid=$sid");
tassert('T5 điểm danh lần 2: không cộng', dj($sid, $PILL) - $p0, 30);
$h = page("cmd=nhiemvu&action=claim_tanthu&tkind=on0&tab=online&sid=$sid");
ttrue('T5 mốc 1 giờ khi chưa đủ giờ: từ chối', dj($sid, $PILL) - $p0 === 30 && strpos($h, 'Chưa đủ') !== false);
$db->prepare("REPLACE INTO player_online_reward (sid, day, seconds, last_ts, claimed) VALUES (?, ?, 3700, ?, '')")->execute(array($sid, date('Y-m-d'), time()));
page("cmd=nhiemvu&action=claim_tanthu&tkind=on0&tab=online&sid=$sid");
ttrue('T5 mốc 1 giờ (đủ 61 phút): +6 đan, +1 túi', dj($sid, $PILL) - $p0 === 36 && dj($sid, $BAG) - $b0 === 2);
$h = page("cmd=nhiemvu&tab=online&sid=$sid");
ttrue('T5 tab Trực Tuyến có thẻ "Quà Tân Thủ — Ngày 1/3"', strpos($h, 'Quà Tân Thủ — Ngày 1/3') !== false);

// ── T6. Tân Thủ Tu Vi Đan: lấp thanh, trần cấp ngày, hết hạn ngày 4 ─
setp($sid, array('ulv' => 10, 'uexp' => 0));
$p0 = dj($sid, $PILL);
page("cmd=tupo&canshu=tanthu_dan&sid=$sid");
tassert('T6 dùng 1 đan ở cấp 10: tu vi = 13.310 (đầy thanh)', intval(q1("SELECT uexp FROM game1 WHERE sid = ?", array($sid))), 13310);
tassert('T6 trừ 1 đan', $p0 - dj($sid, $PILL), 1);
page("cmd=tupo&canshu=tanthu_dan&sid=$sid");
tassert('T6 thanh đã đầy: không trừ đan', $p0 - dj($sid, $PILL), 1);
setp($sid, array('ulv' => 150, 'uexp' => 0));
$h = page("cmd=tupo&canshu=tanthu_dan&sid=$sid");
ttrue('T6 ngày 1 cấp 150 (trần 150): từ chối', $p0 - dj($sid, $PILL) === 1 && strpos($h, 'chỉ dùng dưới cấp 150') !== false);
$db->prepare("UPDATE player_tanthu SET created_at = ? WHERE sid = ?")->execute(array(date('Y-m-d H:i:s', strtotime('-1 day')), $sid));
page("cmd=tupo&canshu=tanthu_dan&sid=$sid");
ttrue('T6 ngày 2 cấp 150 (trần 250): dùng được', $p0 - dj($sid, $PILL) === 2);
$db->prepare("UPDATE player_tanthu SET created_at = ? WHERE sid = ?")->execute(array(date('Y-m-d H:i:s', strtotime('-3 days')), $sid));
setp($sid, array('ulv' => 20, 'uexp' => 0));
$h = page("cmd=tupo&canshu=tanthu_dan&sid=$sid");
ttrue('T6 ngày 4: đan hết tác dụng', $p0 - dj($sid, $PILL) === 2 && strpos($h, '3 ngày đầu') !== false);
$db->prepare("UPDATE player_tanthu SET created_at = NOW() WHERE sid = ?")->execute(array($sid));

// ── T7. Phí đột phá: Túi Tân Thủ trước, rồi LT thường; tỉ lệ không đổi ─
setp($sid, array('ulv' => 20, 'uexp' => 21 * 21 * 110, 'uyxb' => 0));
$pool0 = intval(q1("SELECT lt FROM player_tanthu WHERE sid = ?", array($sid)));
$h = page("cmd=tupo&sid=$sid");
preg_match('/break-ring-inner"><b>(\d+)%/', $h, $m);
$exp = 75 - intval(round(20 * 0.6)) - 3 + intval(\player\tupo_event_bonus()[0]);
ttrue('T7 tỉ lệ hiển thị giữ đúng luật (không cộng tân thủ)', isset($m[1]) && intval($m[1]) === intval(75 - 20 * 0.6 - 3 + \player\tupo_event_bonus()[0]), isset($m[1]) ? $m[1] . '%' : 'không đọc được');
ttrue('T7 trang Đột Phá hiện số dư Túi Tân Thủ', strpos($h, 'Túi Tân Thủ') !== false && strpos($h, number_format($pool0)) !== false);
page("cmd=tupo&canshu=tupo&sid=$sid");
tassert('T7 bấm đột phá cấp 20 (phí 420), LT thường = 0: túi -420', $pool0 - intval(q1("SELECT lt FROM player_tanthu WHERE sid = ?", array($sid))), 420);
tassert('T7 LT thường không bị trừ', intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))), 0);
$db->prepare("UPDATE player_tanthu SET lt = 100 WHERE sid = ?")->execute(array($sid));
$L = intval(q1("SELECT ulv FROM game1 WHERE sid = ?", array($sid)));
$cost = \player\istupo($sid, $db) == 1 ? $L * $L * 3 : $L * ($L + 1);
setp($sid, array('uexp' => ($L + 1) * ($L + 1) * 110, 'uyxb' => 100000));
page("cmd=tupo&canshu=tupo&sid=$sid");
ttrue('T7 túi 100 không đủ: trừ 100 túi + phần thiếu từ LT thường', intval(q1("SELECT lt FROM player_tanthu WHERE sid = ?", array($sid))) === 0
    && 100000 - intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))) === $cost - 100, "phí $cost");
setp($sid, array('uexp' => 999999999, 'uyxb' => 0));
$L0 = intval(q1("SELECT ulv FROM game1 WHERE sid = ?", array($sid)));
page("cmd=tupo&canshu=tupo&sid=$sid");
tassert('T7 túi 0 + LT 0: không đột phá được', intval(q1("SELECT ulv FROM game1 WHERE sid = ?", array($sid))), $L0);

// ── T8. Đột Phá Liên Tục: theo chuỗi tự tải trang tới khi dừng ──
$db->prepare("UPDATE player_tanthu SET lt = 5000000 WHERE sid = ?")->execute(array($sid));
$db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid = ?")->execute(array($sid, $PILL));
\player\adddj($sid, $PILL, 15, $db);
setp($sid, array('ulv' => 20, 'uexp' => 0, 'uyxb' => 0));
$h = page("cmd=tupo&sid=$sid");
ttrue('T8 có nút "Đột phá liên tục" (thanh chưa đầy nhưng còn đan)', strpos($h, 'Đột phá liên tục') !== false);
preg_match('/href="\?cmd=([^"]+)">Đột phá liên tục/', $h, $m);
$url = isset($m[1]) ? '?cmd=' . $m[1] : '';
$steps = 0; $last = '';
while ($url !== '' && $steps < 400) {
    $last = raw($url); $steps++;
    $url = preg_match('/location\.href="(\?cmd=[^"]+)"/', $last, $mm) ? $mm[1] : '';
}
$L1 = intval(q1("SELECT ulv FROM game1 WHERE sid = ?", array($sid)));
ttrue('T8 chạy liên tục: dùng hết 15 đan, lên ~15 cấp', dj($sid, $PILL) === 0 && $L1 >= 33 && $L1 <= 35, "cấp 20 → $L1 sau $steps lần bấm");
ttrue('T8 dừng với lý do "thanh tu vi chưa đầy"', strpos($last, 'Dừng đột phá liên tục') !== false && strpos($last, 'thanh tu vi chưa đầy') !== false);
ttrue('T8 toàn bộ phí lấy từ Túi Tân Thủ, LT thường vẫn 0', intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))) === 0
    && intval(q1("SELECT lt FROM player_tanthu WHERE sid = ?", array($sid))) < 5000000);
// Trần cấp ngày 1 = 150: đứng ở 149 với đan → lên 150 rồi dừng (đan không dùng được ở cấp 150)
\player\adddj($sid, $PILL, 10, $db);
setp($sid, array('ulv' => 149, 'uexp' => 150 * 150 * 110));
$db->prepare("UPDATE player_tanthu SET lt = 900000000 WHERE sid = ?")->execute(array($sid));
$url = '?cmd=' . $enc->encode("cmd=tupo&canshu=tupo&tpdan=0&lientuc=1&sid=$sid"); $steps = 0;
while ($url !== '' && $steps < 400) {
    $last = raw($url); $steps++;
    $url = preg_match('/location\.href="(\?cmd=[^"]+)"/', $last, $mm) ? $mm[1] : '';
}
$L2 = intval(q1("SELECT ulv FROM game1 WHERE sid = ?", array($sid)));
// bại mất tu vi dưới trần thì lấp lại bằng đan (hợp lệ) ⇒ chỉ đòi: không vượt 150, còn đan, dừng vì trần (hoặc hết 200 lần)
ttrue('T8 trần ngày 1: dừng ở cấp <= 150, còn đan chưa dùng', $L2 <= 150 && dj($sid, $PILL) >= 1 && (strpos($last, 'trần cấp Tân Thủ') !== false || strpos($last, 'đã bấm') !== false),
    "cấp $L2, còn " . dj($sid, $PILL) . " đan, $steps lần bấm");

t_clean($db);
echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
