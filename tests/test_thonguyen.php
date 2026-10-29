<?php
/**
 * test_thonguyen.php — Tuổi & Thọ Nguyên (docs/DESIGN_THONGUYEN.md), đi qua route game.php thật:
 *   tạo nhân vật → tuổi 0, thọ nguyên 60 → tuổi theo giờ thật → % thưởng hệ thống → vùng −20/30/40% (tu luyện)
 *   → Tọa Hóa: khoá tu luyện / quái / đột phá → mua + dùng Đại Tỉnh / Cửu Chuyển → Diên Thọ Đan.
 * CẦN server thử 127.0.0.1:8091 (bash tools/cloud/start_env.sh). Tự tạo + tự dọn nhân vật test (ThoNguyenT*).
 * Chạy: php tests/test_thonguyen.php
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
// Đặt tuổi (năm) trực tiếp trong DB
function set_age($sid, $years) {
    global $db;
    $c = \player\thonguyen_config();
    $db->prepare("UPDATE player_thonguyen SET moc = ? WHERE sid = ?")->execute(array(time() - intval($years * $c['hours_per_year'] * 3600), $sid));
}
function st($sid) {
    global $db;
    \player\thonguyen_moc($sid, $db, true);
    \player\player_cache_clear($sid);
    return \player\thonguyen_status(\player\getplayer($sid, $db), $db);
}
function t_clean($db) {
    $sids = $db->query("SELECT sid FROM game1 WHERE uname LIKE 'ThoNguyenT%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($sids as $s) {
        foreach (array('playerdaoju', 'player_tanthu', 'player_tanthu_claim', 'player_thonguyen', 'player_online_reward', 'player_nhiemvu_main') as $t) {
            try { $db->prepare("DELETE FROM $t WHERE sid = ?")->execute(array($s)); } catch (Exception $e) {}
        }
    }
    $db->exec("DELETE FROM game1 WHERE uname LIKE 'ThoNguyenT%'");
    $db->exec("DELETE FROM userinfo WHERE username LIKE 'thonguyent%'");
}
function t_create($db, $name) {
    $token = md5('thonguyen-token-' . $name . microtime());
    $db->prepare("INSERT INTO userinfo (username, userpass, token, ip) VALUES (?, '', ?, '')")->execute(array(strtolower($name), $token));
    page("cmd=cjplayer&token=$token&username=$name&sex=1&shenfen=1");
    return md5($name . $token . '229');
}

if (page('cmd=gomid') === '') { echo "SKIP  (server thử 127.0.0.1:8091 chưa bật — bash tools/cloud/start_env.sh)\n"; exit(0); }
ttrue('T0 bảng player_thonguyen tự tạo', \player\thonguyen_ready($db));
t_clean($db);
$cfg = \player\thonguyen_config();

// ── T1. Nhân vật mới: tuổi 0, thọ nguyên 60 ─────────────────
$sid = t_create($db, 'ThoNguyenT1');
ttrue('T1 tạo nhân vật', intval(q1("SELECT COUNT(*) FROM game1 WHERE sid = ?", array($sid))) === 1);
$h = page("cmd=zhuangtai&sid=$sid");
ttrue('T1 trang cá nhân có thanh Tuổi / Thọ nguyên', strpos($h, 'Tuổi / Thọ nguyên') !== false);
ttrue('T1 vào game lần đầu đã có mốc tuổi', intval(q1("SELECT moc FROM player_thonguyen WHERE sid = ?", array($sid))) > 0);
$s = st($sid);
ttrue('T1 tuổi ~0', $s['age'] < 0.1, round($s['age'], 3));
$p = \player\getplayer($sid, $db);
list($bonus) = \player\thonguyen_bonus_pct($p);
tassert('T1 thọ nguyên cấp ' . intval($p->ulv) . ' = 60 × (1 + thưởng)', round($s['life'], 2), round(\player\thonguyen_lifespan($p), 2));
tassert('T1 cấp 0 gốc 60', \player\thonguyen_realm_base(0), 60.0);

// ── T2. Tuổi theo giờ thật: 10 giờ = 5 tuổi ───────────────────
$db->prepare("UPDATE player_thonguyen SET moc = moc - 36000 WHERE sid = ?")->execute(array($sid));
$s = st($sid);
ttrue('T2 lùi mốc 10 giờ ⇒ 5 tuổi', abs($s['age'] - 5) < 0.05, round($s['age'], 3));

// ── T3. Thọ nguyên theo cấp / cảnh giới / hệ thống ──────────
setp($sid, array('ulv' => 50));
$p = \player\getplayer($sid, $db);
list($b0) = \player\thonguyen_bonus_pct($p);
ttrue('T3 cấp 50 (vào Luyện Thể) gốc 100', abs(\player\thonguyen_lifespan($p) / (1 + $b0 / 100) - 100) < 0.01, round(\player\thonguyen_lifespan($p), 1));
setp($sid, array('ulv' => 75));
$p = \player\getplayer($sid, $db);
ttrue('T3 cấp 75 = 100 + (120−100)×0,6×25/50 = 106', abs(\player\thonguyen_lifespan($p) / (1 + $b0 / 100) - 106) < 0.01);
// phẩm chất thật do getplayer nạp từ bảng riêng từng hệ ⇒ thử trên bản sao object
$p = clone \player\getplayer($sid, $db);
$p->huyetmach_quality = 6; $p->thechat_quality = 1; $p->tianfu_quality = 1; $p->linhcan_total_lv = 0;
list($b1, $parts) = \player\thonguyen_bonus_pct($p);
ttrue('T3 Huyết Mạch phẩm 6 ⇒ +10%', abs($parts['Huyết Mạch'] - 10) < 0.001, json_encode($parts, JSON_UNESCAPED_UNICODE));
$p->huyetmach_quality = 10; $p->thechat_quality = 10; $p->tianfu_quality = 10; $p->linhcan_total_lv = 200;
list($b2) = \player\thonguyen_bonus_pct($p);
ttrue('T3 tổng thưởng có trần 50%', $b2 <= 50.0 && $b2 >= 49.999, $b2);
ttrue('T3 Phi Thăng thọ nguyên tăng vọt', \player\thonguyen_realm_base(25) / \player\thonguyen_realm_base(24) >= 2.79);
setp($sid, array('ulv' => 20));

// ── T4. Vùng giảm hấp thụ linh khí ───────────────────────────
$p = \player\getplayer($sid, $db);
$life = \player\thonguyen_lifespan($p);
foreach (array(array(0.85, 0.8, 20), array(0.92, 0.7, 30), array(0.97, 0.6, 40), array(0.5, 1.0, 0)) as $c) {
    set_age($sid, $life * $c[0]);
    $s = st($sid);
    tassert('T4 tuổi ' . ($c[0] * 100) . '% thọ nguyên ⇒ hấp thụ ×' . $c[1], array(round($s['mult'], 2), $s['cut'], $s['dead']), array($c[1], $c[2], false));
}
set_age($sid, $life * 0.97);
$h = page("cmd=goxiulian&sid=$sid");
ttrue('T4 trang tu luyện vẫn vào được khi thọ nguyên cạn', strpos($h, 'Tọa Hóa') === false && $h !== '');
$h = page("cmd=gomid&newmid=" . intval(\player\getplayer($sid, $db)->nowmid) . "&sid=$sid");
ttrue('T4 đại sảnh nhắc thọ nguyên sắp cạn', strpos($h, 'hấp thụ linh khí −40%') !== false);
// tu luyện thường 120 giây: tu vi nhận = 60% bình thường
setp($sid, array('uexp' => 0, 'sfxl' => 0));
setp($sid, array('sfxl' => 1, 'xiuliantime' => date('Y-m-d H:i:s', time() - 120)));
page("cmd=endxiulian&sid=$sid");
$g40 = intval(q1("SELECT uexp FROM game1 WHERE sid = ?", array($sid)));
set_age($sid, $life * 0.5);
setp($sid, array('uexp' => 0, 'sfxl' => 1, 'xiuliantime' => date('Y-m-d H:i:s', time() - 120)));
page("cmd=endxiulian&sid=$sid");
$g0 = intval(q1("SELECT uexp FROM game1 WHERE sid = ?", array($sid)));
ttrue('T4 tu luyện 2 phút khi còn ≤ 5% ≈ 60% bình thường', $g0 > 0 && abs($g40 / $g0 - 0.6) < 0.03, "$g40 / $g0");

// ── T5. Tọa Hóa: bị khoá ────────────────────────────────────
set_age($sid, $life + 1);
$s = st($sid);
ttrue('T5 tuổi > thọ nguyên ⇒ Tọa Hóa', $s['dead'] && $s['mult'] == 0);
setp($sid, array('sfxl' => 0));
foreach (array('startxiulian&canshu=1' => 'tu luyện', 'tupo' => 'đột phá', 'pve' => 'đánh quái') as $c => $n) {
    $h = page("cmd=$c&sid=$sid");
    ttrue("T5 $n bị chặn → trang Tọa Hóa", strpos($h, 'Đã Tọa Hóa') !== false);
}
tassert('T5 không bắt đầu tu luyện được', intval(q1("SELECT sfxl FROM game1 WHERE sid = ?", array($sid))), 0);
$h = page("cmd=getbagdj&sid=$sid");
ttrue('T5 túi đồ vẫn mở được', strpos($h, 'Đã Tọa Hóa') === false && $h !== '');
$h = page("cmd=thonguyen&sid=$sid");
ttrue('T5 trang Thọ Nguyên chỉ bán Hồi Hồn Đan', strpos($h, 'Đại Tỉnh Hồi Hồn Đan') !== false && strpos($h, 'Cửu Chuyển Hoàn Hồn Đan') !== false && strpos($h, '<b>Diên Thọ Đan') === false);

// ── T6. Mua + dùng Đại Tỉnh Hồi Hồn Đan ─────────────────────
$p = \player\getplayer($sid, $db);
list($price) = \player\thonguyen_price('daitinh', $p);
tassert('T6 giá Đại Tỉnh cảnh giới 0 = 2.000 LT', $price, 2000);
setp($sid, array('uyxb' => 1500));
page("cmd=thonguyen&tnact=buy&tnk=daitinh&sid=$sid");
tassert('T6 thiếu LT không mua được', dj($sid, $cfg['items']['daitinh']), 0);
setp($sid, array('uyxb' => 5000));
page("cmd=thonguyen&tnact=buy&tnk=daitinh&sid=$sid");
tassert('T6 mua 1 viên: −2.000 LT, +1 đan', array(intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))), dj($sid, $cfg['items']['daitinh'])), array(3000, 1));
$h = page("cmd=thonguyen&tnact=use&tnk=daitinh&sid=$sid");
$s = st($sid);
ttrue('T6 dùng Đại Tỉnh ⇒ sống lại, còn 15%', !$s['dead'] && abs($s['left_pct'] - 15) < 0.5, round($s['left_pct'], 2));
tassert('T6 đan đã trừ', dj($sid, $cfg['items']['daitinh']), 0);
setp($sid, array('uexp' => 0));
$h = page("cmd=startxiulian&canshu=1&sid=$sid");
ttrue('T6 sống lại ⇒ tu luyện lại được', intval(q1("SELECT sfxl FROM game1 WHERE sid = ?", array($sid))) > 0);
setp($sid, array('sfxl' => 0));

// ── T7. Cửu Chuyển (MT) qua túi đồ use_dj ───────────────────
set_age($sid, $life * 2);
setp($sid, array('uczb' => 100));
page("cmd=thonguyen&tnact=buy&tnk=cuuchuyen&sid=$sid");
tassert('T7 mua Cửu Chuyển −30 MT', array(intval(q1("SELECT uczb FROM game1 WHERE sid = ?", array($sid))), dj($sid, $cfg['items']['cuuchuyen'])), array(70, 1));
page("cmd=use_dj&djid=" . $cfg['items']['cuuchuyen'] . "&sid=$sid");
$s = st($sid);
ttrue('T7 dùng Cửu Chuyển từ túi ⇒ còn 50%', !$s['dead'] && abs($s['left_pct'] - 50) < 0.5, round($s['left_pct'], 2));
page("cmd=use_dj&djid=" . $cfg['items']['daitinh'] . "&sid=$sid");
\player\adddj($sid, $cfg['items']['daitinh'], 1, $db);
page("cmd=use_dj&djid=" . $cfg['items']['daitinh'] . "&sid=$sid");
tassert('T7 còn sống: không dùng Hồi Hồn Đan (không mất đan)', dj($sid, $cfg['items']['daitinh']), 1);

// ── T8. Diên Thọ Đan ────────────────────────────────────────
$before = st($sid);
setp($sid, array('uyxb' => 10000));
page("cmd=thonguyen&tnact=buy&tnk=dientho&sid=$sid");
page("cmd=thonguyen&tnact=use&tnk=dientho&sid=$sid");
$s = st($sid);
ttrue('T8 Diên Thọ Đan: tuổi −10% thọ nguyên', abs(($before['age'] - $s['age']) - $s['life'] * 0.1) < 0.2, round($before['age'], 1) . ' → ' . round($s['age'], 1));
tassert('T8 giá Diên Thọ 1.000 LT', intval(q1("SELECT uyxb FROM game1 WHERE sid = ?", array($sid))), 9000);

// ── T9. Nguồn Diên Thọ Đan: mốc online 240', top Vấn Đỉnh Đài, tầng 50 Tỏa Yêu Tháp ──
$rules = \player\nhiemvu_rules();
$m240 = null; foreach ($rules['online']['milestones'] as $m) if (intval($m['minutes']) === 240) $m240 = $m;
ttrue('T9 mốc online 240 phút có 1 Diên Thọ Đan', $m240 && in_array(array('type' => 'daoju', 'id' => 121, 'count' => 1), $m240['items'], true));
require_once __DIR__ . '/../class/function/vandinhdai_reward.php';
$vt = vdd_daily_reward_table();
tassert('T9 bảng VĐĐ: top 1/2/3/4/10/11/20/21 = 3/2/2/1/1/1/1/0 Diên Thọ Đan', array($vt[1]['dientho'], $vt[2]['dientho'], $vt[3]['dientho'], $vt[4]['dientho'], $vt[10]['dientho'], $vt[11]['dientho'], $vt[20]['dientho'], isset($vt[21]['dientho']) ? $vt[21]['dientho'] : 0), array(3, 2, 2, 1, 1, 1, 1, 0));
// Chốt thưởng thật: nhân vật test top 1 (chỉ trên DB thử — xoá thưởng hôm nay rồi phát lại bằng --force)
$db->exec("DELETE FROM arena_daily_reward WHERE reward_date = CURDATE()");
$db->prepare("REPLACE INTO arena_player (sid, score, wins) VALUES (?, 99999999, 1)")->execute(array($sid));
$dt0 = dj($sid, 121);
vdd_issue_daily_rewards($db, true);
tassert('T9 chốt thưởng VĐĐ top 1: +3 Diên Thọ Đan', dj($sid, 121) - $dt0, 3);
$db->prepare("DELETE FROM arena_player WHERE sid = ?")->execute(array($sid));
$db->exec("DELETE FROM arena_daily_reward WHERE reward_date = CURDATE()");
$tyt = file_get_contents(__DIR__ . '/../game/toayeuthap.php');
ttrue('T9 Tỏa Yêu Tháp tầng 50/100/150 thưởng Diên Thọ Đan', strpos($tyt, "array('id' => 121, 'count' => 1)") !== false);

t_clean($db);
echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
