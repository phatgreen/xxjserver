<?php
/**
 * test_e1_e5_thue.php — kiểm thật 5 bản sửa kinh tế (docs/BAO_CAO_MOC_THOI_GIAN_2026-10-29.md mục 6) + thuế Phường Thị 5–15%.
 *   E1 Đột Phá Đan: bán NPC 10% + không rơi khi chênh cấp >= 35 (boss) — đánh boss thật qua route pvbgj
 *   E2 Cường Hóa Thạch không bán NPC; đồ bán NPC 25/phẩm ⇒ chế tạo + bán / phân giải không còn lãi
 *   E3 Boss Linh Căn hồi sinh sau spawn_interval_minutes tính từ lúc bị hạ
 *   E4 Cơ Duyên Huyết Mạch tặng 32 Huyết Thạch ⇒ luyện mạch tới thức tỉnh Sơ Giác
 *   E5 Độc tính luyện đan giảm 3 điểm/giờ
 *   Thuế Phường Thị theo giá trị + phẩm cấp, trừ vào tiền người bán
 * CẦN DB + server thử 127.0.0.1:8091 (bash tools/cloud/start_env.sh). Tự tạo + tự dọn nhân vật test (token t15test_*).
 * Chạy: php tests/test_e1_e5_thue.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../class/encode.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$db = $dblj;
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$enc = new \encode\encode();
$BASE = getenv('T15_BASE') ? getenv('T15_BASE') : 'http://127.0.0.1:8091/game.php?cmd=';

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
function page($cmd, $post = null) {
    global $enc, $BASE;
    usleep(120000);   // tránh anti-flood của game.php
    $ch = curl_init($BASE . $enc->encode($cmd));
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30));
    if ($post !== null) { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); }
    $r = curl_exec($ch); curl_close($ch);
    return (string)$r;
}
function t_clean($db) {
    $sids = $db->query("SELECT sid FROM game1 WHERE token LIKE 't15test\\_%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($sids as $s) {
        foreach (array('playerdaoju', 'playerzhuangbei', 'player_alchemist', 'player_nhiemvu_main', 'player_huyetmach_vessel') as $t) {
            try { $db->prepare("DELETE FROM $t WHERE sid = ?")->execute(array($s)); } catch (Exception $e) {}
        }
    }
    $db->exec("DELETE FROM game1 WHERE token LIKE 't15test\\_%'");
}
function t_char($db, $name, $lv, $lt) {
    $sid = substr(md5('t15test_' . $name), 0, 32);
    $nn = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'game1' AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
    $v = array();
    foreach ($nn as $c) $v[$c['COLUMN_NAME']] = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext')) ? '' : 0;
    $gc = \player\getgameconfig($db);
    $v = array_merge($v, array('token' => 't15test_' . $name, 'sid' => $sid, 'uname' => 'T15' . $name, 'ulv' => $lv, 'uyxb' => $lt, 'uczb' => 0,
        'uexp' => 0, 'uhp' => 999999, 'umaxhp' => 999999, 'ustamina' => 9999, 'umaxstamina' => 100, 'ugj' => 9000000, 'ufy' => 900000, 'uwx' => 0,
        'usex' => 1, 'vip' => 0, 'nowmid' => intval($gc->firstmid), 'endtime' => date('Y-m-d H:i:s'), 'sfzx' => 1, 'shenfen' => 1));
    $cols = array_keys($v);
    $db->prepare('INSERT INTO game1 (`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')')->execute(array_values($v));
    return $sid;
}
function lt_of($db, $sid) { \player\player_cache_clear($sid); return intval($db->query("SELECT uyxb FROM game1 WHERE sid = " . $db->quote($sid))->fetchColumn()); }
function dj_of($db, $sid, $djid) { return intval($db->query("SELECT COALESCE(SUM(djsum),0) FROM playerdaoju WHERE sid = " . $db->quote($sid) . " AND djid = " . intval($djid))->fetchColumn()); }

t_clean($db);
$probe = page('cmd=gomid');
if ($probe === '') { echo "SKIP  (server thử 127.0.0.1:8091 chưa bật — bash tools/cloud/start_env.sh)\n"; exit(0); }

// ── E1. Đột Phá Đan ────────────────────────────────────────────
tassert('E1 giá bán NPC Trúc Cơ Đột Phá Đan (50.000) = 10%', \player\daoju_sell_price(20, 50000), 5000);
tassert('E1 giá bán NPC Đại Thừa Đột Phá Đan (1.000.000) = 10%', \player\daoju_sell_price(26, 1000000), 100000);
tassert('E1 đạo cụ khác giữ 100% (Ngạnh Sí Phong Mật 10)', \player\daoju_sell_price(8, 10), 10);
ttrue('E1 tupo_is_pill 20..26 / không phải 1', \player\tupo_is_pill(20) && \player\tupo_is_pill(26) && !\player\tupo_is_pill(1));
$s = t_char($db, 'sell', 50, 0);
$uid = intval($db->query("SELECT uid FROM game1 WHERE sid = " . $db->quote($s))->fetchColumn());
\player\adddj($s, 20, 3, $db);
$a = lt_of($db, $s); page("cmd=getbagdj&canshu=maichu1&djid=20&yeshu=1&uid=$uid&sid=$s");
tassert('E1 route bán 1 viên djid 20 nhận 5.000 LT', lt_of($db, $s) - $a, 5000);
tassert('E1 còn 2 viên', dj_of($db, $s, 20), 2);
$dj = page("cmd=djinfo&djid=20&uid=$uid&sid=$s");
ttrue('E1 trang đạo cụ hiện "Bán cho NPC 5.000"', strpos($dj, 'Bán cho NPC') !== false && strpos($dj, '5.000 Linh thạch') !== false);

// Boss 30 (bossid 1) rơi "1,20": đánh thật N lần, mỗi lần đặt máu boss = 1 và lùi mốc hồi sinh (chỉ để đo)
function boss_trial($db, $sid, $lv, $n) {
    $db->prepare("UPDATE game1 SET ulv = ?, uhp = 999999 WHERE sid = ?")->execute(array($lv, $sid));
    $p0 = dj_of($db, $sid, 20); $c0 = dj_of($db, $sid, 1); $kills = 0;
    $bossMid = intval($db->query("SELECT mid FROM mid WHERE midboss = 1 LIMIT 1")->fetchColumn());
    $orig = $db->query("SELECT bosshp, bossmaxhp, bossgj FROM boss WHERE bossid = 1")->fetch(PDO::FETCH_ASSOC);
    for ($i = 0; $i < $n; $i++) {
        $db->exec("UPDATE boss SET bosshp = 1 WHERE bossid = 1");
        $db->exec("UPDATE mid SET mgtime = '2020-01-01 00:00:00' WHERE midboss = 1");
        $db->prepare("UPDATE game1 SET nowmid = ?, uhp = 999999, ustamina = 9999 WHERE sid = ?")->execute(array($bossMid, $sid));
        \player\player_cache_clear($sid);
        page("cmd=pvbgj&bossid=1&sid=$sid&nowmid=$bossMid");
        if (intval($db->query("SELECT bosshp FROM boss WHERE bossid = 1")->fetchColumn()) <= 0) $kills++;
    }
    $db->prepare("UPDATE boss SET bosshp = ?, bossmaxhp = ?, bossgj = ? WHERE bossid = 1")->execute(array($orig['bosshp'], $orig['bossmaxhp'], $orig['bossgj']));
    return array($kills, dj_of($db, $sid, 20) - $p0, dj_of($db, $sid, 1) - $c0);
}
$bs = t_char($db, 'boss', 40, 0);
list($k, $pill, $cht) = boss_trial($db, $bs, 40, 250);
ttrue('E1 đối chứng cấp 40 (chênh 10) hạ boss 30 vẫn rơi Đột Phá Đan', $k >= 150 && $pill > 0, "hạ $k, đan +$pill, đá +$cht");
list($k, $pill, $cht) = boss_trial($db, $bs, 100, 250);
ttrue('E1 cấp 100 (chênh 70) hạ boss 30: KHÔNG rơi Đột Phá Đan', $k >= 150 && $pill === 0, "hạ $k, đan +$pill");
ttrue('E1 cấp 100: Cường Hóa Thạch vẫn rơi (chỉ chặn đan)', $cht > 0, "đá +$cht");

// ── E2. Cường Hóa Thạch / trang bị ─────────────────────────────
tassert('E2 Cường Hóa Thạch giá bán NPC = 0', \player\daoju_sell_price(1, 10), 0);
\player\adddj($s, 1, 50, $db);
$a = lt_of($db, $s); $out = page("cmd=getbagdj&canshu=maichu1&djid=1&yeshu=1&uid=$uid&sid=$s");
tassert('E2 route bán Cường Hóa Thạch: không nhận LT', lt_of($db, $s) - $a, 0);
tassert('E2 route bán Cường Hóa Thạch: không mất đá', dj_of($db, $s, 1), 50);
ttrue('E2 báo "không bán cho NPC được"', strpos($out, 'không bán cho NPC') !== false);
$db->prepare("UPDATE game1 SET ulv = 250, uyxb = 100000 WHERE sid = ?")->execute(array($s));
$zn = \player\addzb($s, 91, $db);   // Thánh Phi Phong (chế tạo #4, phẩm 6)
$a = lt_of($db, $s); page("cmd=getbagzb&canshu=maichu&yeshu=0&zbnowid=$zn&sid=$s");
tassert('E2 bán NPC Thánh Phi Phong phẩm 6 = 150 LT (25/phẩm)', lt_of($db, $s) - $a, 150);
$zn = \player\addzb($s, 91, $db);
$a = lt_of($db, $s); $c0 = dj_of($db, $s, 1); page("cmd=delezb&zbnowid=$zn&sid=$s");
tassert('E2 phân giải trừ phí 20 LT', $a - lt_of($db, $s), 20);
ttrue('E2 phân giải ra ~295 Cường Hóa Thạch (chỉ dùng cường hóa, không bán được)', dj_of($db, $s, 1) - $c0 >= 295, '+' . (dj_of($db, $s, 1) - $c0));
// Mọi công thức chế tạo: nguyên liệu mua tiệm > giá bán NPC món làm ra
$cfg = json_decode(file_get_contents(__DIR__ . '/../class/config/chetao_config.json'), true);
$rec = isset($cfg['recipes']) ? $cfg['recipes'] : $cfg; $bad = array(); $nrec = 0;
foreach ($rec as $r) {
    if (!is_array($r) || !isset($r['type']) || $r['type'] !== 'equipment') continue;
    $nrec++; $cost = 0;
    foreach ($r['materials'] as $m) { $d = \player\daoju_config_get($m['id']); $cost += max(50, intval($d['djyxb'])) * intval($m['amount']); }
    $zb = \player\zhuangbei_config_get(intval($r['result_zbid']));
    $sell = intval($zb['rarity']) * 25;
    if ($sell >= $cost) $bad[] = $r['id'] . ':' . $sell . '>=' . $cost;
}
ttrue("E2 $nrec công thức: bán NPC món chế tạo < nguyên liệu", $nrec > 20 && empty($bad), implode(' ', $bad));

// ── E3. Boss Linh Căn hồi sinh ─────────────────────────────────
$lc = t_char($db, 'lcb', 150, 0);
// Boss thế giới dùng chung server: lần chạy trước có thể vừa hạ 1 con (còn trong 120') ⇒ lùi mốc đó để bắt đầu sạch (chỉ để đo)
$db->prepare("UPDATE linhcan_boss SET killed_at = ? WHERE status = 'killed' AND killed_at > ?")->execute(array(date('Y-m-d H:i:s', time() - 3 * 3600), date('Y-m-d H:i:s', time() - 3 * 3600)));
page("cmd=linhcan_boss&sid=$lc");
$b1 = $db->query("SELECT id FROM linhcan_boss WHERE status = 'active' ORDER BY id DESC LIMIT 1")->fetchColumn();
ttrue('E3 có boss đang hoạt động', $b1 !== false);
$db->exec("UPDATE linhcan_boss SET hp = 0 WHERE id = " . intval($b1));
\player\linhcan_boss_mark_killed(intval($b1), $lc, $db);
$html = page("cmd=linhcan_boss&sid=$lc");
$b2 = $db->query("SELECT COUNT(*) FROM linhcan_boss WHERE id > " . intval($b1))->fetchColumn();
tassert('E3 vừa hạ xong: KHÔNG sinh boss mới', intval($b2), 0);
ttrue('E3 trang báo "xuất hiện lại sau 120 phút"', strpos($html, 'xuất hiện lại sau 120 phút') !== false);
$db->prepare("UPDATE linhcan_boss SET killed_at = ? WHERE id = ?")->execute(array(date('Y-m-d H:i:s', time() - 119 * 60), intval($b1)));
page("cmd=linhcan_boss&sid=$lc");
tassert('E3 sau 119 phút: vẫn chưa có boss mới', intval($db->query("SELECT COUNT(*) FROM linhcan_boss WHERE id > " . intval($b1))->fetchColumn()), 0);
$db->prepare("UPDATE linhcan_boss SET killed_at = ? WHERE id = ?")->execute(array(date('Y-m-d H:i:s', time() - 121 * 60), intval($b1)));
page("cmd=linhcan_boss&sid=$lc");
tassert('E3 sau 121 phút: có đúng 1 boss mới', intval($db->query("SELECT COUNT(*) FROM linhcan_boss WHERE id > " . intval($b1) . " AND status = 'active'")->fetchColumn()), 1);

// ── E4. Huyết Thạch mở đầu ─────────────────────────────────────
$hm = t_char($db, 'hm', 101, 1000000);
page("cmd=huyetmach&sid=$hm");
tassert('E4 ghé Huyết Mạch lần đầu (cấp 101): +32 Huyết Thạch', dj_of($db, $hm, 112), 32);
page("cmd=huyetmach&sid=$hm");
tassert('E4 ghé lần 2: không cộng thêm', dj_of($db, $hm, 112), 32);
$ok = 0;
for ($node = 1; $node <= 8; $node++) {
    for ($k = 0; $k < 4; $k++) { $r = \player\huyetmach_vessel_refine($hm, $db, $node); if (!empty($r['ok'])) $ok++; }
}
tassert('E4 32 Huyết Thạch luyện được 32 cấp mạch', $ok, 32);
tassert('E4 dùng hết đúng 32 Huyết Thạch', dj_of($db, $hm, 112), 0);
// Huyết mạch: Tố Thể Đan (110) mua ở tiệm bằng LT, dùng qua route use_dj
// tab Đạo cụ của Thương Điếm = canshu2=gogoumai2; bỏ chữ trong ô tìm kiếm, chỉ xét danh sách hàng
$shop = str_replace('value="Tố Thể Đan"', '', page("cmd=shangdian&canshu2=gogoumai2&kw=" . urlencode('Tố Thể Đan') . "&sid=$hm"));
ttrue('E4 tiệm (tab Đạo cụ) có bán Tố Thể Đan (nhận huyết mạch)', strpos($shop, 'Tố Thể Đan') !== false && strpos($shop, 'Không tìm thấy vật phẩm nào') === false);
$shop = str_replace('value="Huyết Thạch"', '', page("cmd=shangdian&canshu2=gogoumai2&kw=" . urlencode('Huyết Thạch') . "&sid=$hm"));
ttrue('E4 tiệm (tab Đạo cụ) KHÔNG bán Huyết Thạch — nguồn đầu tiên là Cơ Duyên', strpos($shop, 'Không tìm thấy vật phẩm nào') !== false);
\player\adddj($hm, 110, 1, $db);
page("cmd=use_dj&djid=110&sid=$hm");
\player\player_cache_clear($hm);
ttrue('E4 dùng Tố Thể Đan: có huyết mạch', intval($db->query("SELECT huyetmach_id FROM game1 WHERE sid = " . $db->quote($hm))->fetchColumn()) > 0);
$aw = \player\huyetmach_awaken($hm, $db);
\player\player_cache_clear($hm);
ttrue('E4 thức tỉnh Sơ Giác (bậc 1) thành công', !empty($aw['ok']), isset($aw['msg']) ? strip_tags($aw['msg']) : '');

// ── E5. Độc tính luyện đan giảm theo giờ ───────────────────────
$al = t_char($db, 'alch', 50, 0);
\player\luonden_get_alchemist($al, $db);   // tạo dòng
$db->prepare("UPDATE player_alchemist SET total_toxin = 1000, pill_toxin = 500, updated_at = NOW() - INTERVAL 630 MINUTE WHERE sid = ?")->execute(array($al));
$r = \player\luonden_get_alchemist($al, $db);
tassert('E5 10,5 giờ: phản phệ 1000 → 970', intval($r['total_toxin']), 970);
tassert('E5 10,5 giờ: đan độc 500 → 470', intval($r['pill_toxin']), 470);
$left = intval($db->query("SELECT TIMESTAMPDIFF(MINUTE, updated_at, NOW()) FROM player_alchemist WHERE sid = " . $db->quote($al))->fetchColumn());
ttrue('E5 giữ phần lẻ 30 phút cho lần sau', $left >= 29 && $left <= 31, "$left phút");
$r = \player\luonden_get_alchemist($al, $db);
tassert('E5 đọc lại ngay: không giảm thêm', intval($r['total_toxin']), 970);
$db->prepare("UPDATE player_alchemist SET total_toxin = 2, pill_toxin = 0, updated_at = NOW() - INTERVAL 5 HOUR WHERE sid = ?")->execute(array($al));
$r = \player\luonden_get_alchemist($al, $db);
tassert('E5 không xuống dưới 0', intval($r['total_toxin']), 0);
$db->prepare("UPDATE player_alchemist SET total_toxin = 1006, updated_at = NOW() - INTERVAL 3 HOUR WHERE sid = ?")->execute(array($al));
$r = \player\luonden_get_alchemist($al, $db);
ttrue('E5 qua 3 giờ: 1006 → 997, mở khóa luyện đan (<= 1000)', intval($r['total_toxin']) === 997);

// ── Thuế Phường Thị 5–15% ──────────────────────────────────────
tassert('THUE đạo cụ rẻ (giá gốc 10, bán 100) = 5%', \player\trade_tax_pct('daoju', 10, 100), 5);
tassert('THUE Đột Phá Đan (giá gốc 50k) bán 60k = 5+1+5 = 11%', \player\trade_tax_pct('daoju', 50000, 60000), 11);
tassert('THUE Đột Phá Đan bán 2M = trần 15%', \player\trade_tax_pct('daoju', 50000, 2000000), 15);
tassert('THUE trang bị phẩm 3 bán 500 = 5%', \player\trade_tax_pct('zb', 3, 500), 5);
tassert('THUE trang bị phẩm 7 bán 200k = 5+3+2 = 10%', \player\trade_tax_pct('zb', 7, 200000), 10);
tassert('THUE trang bị phẩm 14 bán 5M = 15%', \player\trade_tax_pct('zb', 14, 5000000), 15);
tassert('THUE sau thuế 60.000 × 89% = 53.400', \player\trade_tax_after(60000, 11), 53400);
// Mua thật qua route: người bán treo 1 Đột Phá Đan 60.000 (đặt thẳng vào fangshi_dj — chỉ để đo)
$seller = t_char($db, 'seller', 50, 0); $buyer = t_char($db, 'buyer', 50, 100000);
$sUid = intval($db->query("SELECT uid FROM game1 WHERE sid = " . $db->quote($seller))->fetchColumn());
$db->prepare("INSERT INTO fangshi_dj (djid, djcount, uid, pay, djname, djinfo, djzl) VALUES (20, 1, ?, 60000, 'Trúc Cơ Đột Phá Đan', '', 20)")->execute(array($sUid));
$payid = intval($db->lastInsertId());
page("cmd=fangshi&fangshi=daoju&sid=$buyer"); page("cmd=fangshi&fangshi=daoju&sid=$seller");   // nhận Cơ Duyên lần đầu ghé (+LT) trước khi đo
$b0 = lt_of($db, $buyer); $s0 = lt_of($db, $seller);
$bh = page("cmd=fangshi&fangshi=daoju&canshu=buy&payid=$payid&buycount=1&sid=$buyer");
tassert('THUE người mua trả 60.000', $b0 - lt_of($db, $buyer), 60000);
tassert('THUE người bán nhận 53.400 (thuế 11%)', lt_of($db, $seller) - $s0, 53400);
tassert('THUE người mua nhận 1 đan', dj_of($db, $buyer, 20), 1);
ttrue('THUE thông báo ghi thuế 11%', strpos($bh, 'thuế 11%') !== false);
// Trang bị: Thánh Phi Phong phẩm 6, giá 150.000 ⇒ 5 + 3 + 1 = 9%
$zn = \player\addzb($seller, 91, $db);
$z = \player\getzb($zn, $db);
$db->prepare("INSERT INTO fangshi_zb (zbnowid, zbname, qianghua, pay, zbinfo, zbgj, zbfy, zbbj, zbxx, tuposjs, tupoexp, zbid, uid, sid, zbhp, zblv, uname, zbys, element)
    VALUES (?, ?, 0, 150000, '', ?, ?, 0, 0, 0, 0, 91, ?, ?, ?, 0, 'T15seller', '', '')")
   ->execute(array($zn, $z->zbname, intval($z->zbgj), intval($z->zbfy), $sUid, $seller, intval($z->zbhp)));
$payid = intval($db->lastInsertId());
$db->prepare("UPDATE game1 SET uyxb = 200000 WHERE sid = ?")->execute(array($buyer));
$s0 = lt_of($db, $seller);
$bh = page("cmd=fangshi&fangshi=zhuangbei&canshu=buy&payid=$payid&sid=$buyer");
tassert('THUE trang bị phẩm 6 giá 150.000: người bán nhận 136.500 (thuế 9%)', lt_of($db, $seller) - $s0, 136500);
tassert('THUE trang bị về túi người mua', intval($db->query("SELECT COUNT(*) FROM playerzhuangbei WHERE zbnowid = $zn AND sid = " . $db->quote($buyer))->fetchColumn()), 1);

t_clean($db);
echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
