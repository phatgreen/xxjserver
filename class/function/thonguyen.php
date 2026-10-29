<?php
namespace player;

/**
 * thonguyen.php — Tuổi & Thọ Nguyên (docs/DESIGN_THONGUYEN.md, class/config/thonguyen_config.json).
 * - Tuổi tính theo GIỜ THẬT (cả lúc offline): hours_per_year giờ = 1 tuổi. Lưu mốc "ngày sinh" (unix time) ở bảng
 *   player_thonguyen (sid, moc) — KHÔNG thêm cột vào game1 (game1 đã sát trần cỡ dòng). Bảng tự tạo lần đầu dùng.
 *   Nhân vật chưa có dòng ⇒ tạo dòng moc = bây giờ (tuổi 0) ở lần vào game đầu tiên.
 * - Thọ nguyên = (gốc cảnh giới + phần tăng theo cấp) × (1 + % Huyết Mạch / Thể Chất / Thiên Phú / Linh Căn, trần max_total).
 * - Thọ nguyên còn ≤ 20/10/5% ⇒ hấp thụ linh khí (tu luyện + bế quan) −20/30/40%. Tuổi ≥ thọ nguyên ⇒ Tọa Hóa.
 * - Diên Thọ Đan (lùi tuổi), Đại Tỉnh Hồi Hồn Đan / Cửu Chuyển Hoàn Hồn Đan (sống lại): đặt lại moc.
 */

if (!function_exists(__NAMESPACE__ . '\\thonguyen_config')) {
    function thonguyen_config(){
        static $cache = null;
        if ($cache === null) {
            $file = __DIR__ . '/../config/thonguyen_config.json';
            $data = is_file($file) ? json_decode(file_get_contents($file), true) : null;
            $cache = is_array($data) ? $data : array();
        }
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_ready')) {
    // Bảng player_thonguyen (tự tạo). Lỗi DB ⇒ false: game chạy như cũ, không ai bị Tọa Hóa.
    function thonguyen_ready($dblj){
        static $ok = null;
        if ($ok !== null) return $ok;
        try {
            $dblj->exec("CREATE TABLE IF NOT EXISTS player_thonguyen (
                sid VARCHAR(64) NOT NULL, moc INT UNSIGNED NOT NULL, PRIMARY KEY (sid)) ENGINE=InnoDB DEFAULT CHARSET=utf8");
            $ok = true;
        } catch (\Exception $e) {
            $ok = false;
        }
        return $ok;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_moc')) {
    // Mốc "ngày sinh" (unix time); chưa có thì tạo = bây giờ. 0 nếu bảng không dùng được.
    function thonguyen_moc($sid, $dblj, $reset = false){
        static $cache = array();   // 1 SELECT / request
        if ($reset) { unset($cache[$sid]); return 0; }
        if (isset($cache[$sid])) return $cache[$sid];
        if (!thonguyen_ready($dblj)) return 0;
        $st = $dblj->prepare("SELECT moc FROM player_thonguyen WHERE sid = ?");
        $st->execute(array($sid));
        $moc = $st->fetchColumn();
        if ($moc === false) {
            $moc = time();
            $dblj->prepare("INSERT IGNORE INTO player_thonguyen (sid, moc) VALUES (?, ?)")->execute(array($sid, $moc));
        }
        return $cache[$sid] = intval($moc);
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_realm_base')) {
    // Thọ nguyên gốc lúc vào cảnh giới $i (cấp 50 × $i).
    function thonguyen_realm_base($i){
        $c = thonguyen_config();
        $b = isset($c['realm_base']) ? $c['realm_base'] : array(60);
        $i = max(0, intval($i));
        if ($i < count($b)) return floatval($b[$i]);
        $v = floatval($b[count($b) - 1]);
        $pi = intval($c['phithang_index']);
        for ($k = count($b); $k <= $i; $k++) {
            if ($k < $pi) $v *= floatval($c['growth']);
            elseif ($k == $pi) $v *= floatval($c['growth']) * floatval($c['phithang_jump']);
            else $v *= floatval($c['tien_growth']);
        }
        return $v;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_bonus_pct')) {
    // % thọ nguyên cộng thêm từ Huyết Mạch / Thể Chất / Thiên Phú / Linh Căn — array(tổng, chi tiết).
    function thonguyen_bonus_pct($player){
        $c = thonguyen_config();
        $b = $c['bonus'];
        $hm = max(0, intval(isset($player->huyetmach_quality) ? $player->huyetmach_quality : 0) - 1) * floatval($b['huyetmach_per_quality']);
        $tc = max(0, intval(isset($player->thechat_quality) ? $player->thechat_quality : 0) - 1) * floatval($b['thechat_per_quality']);
        $tf = max(0, intval(isset($player->tianfu_quality) ? $player->tianfu_quality : 0) - 1) * floatval($b['tianfu_per_quality']);
        $lc = min(floatval($b['linhcan_max']), intval(isset($player->linhcan_total_lv) ? $player->linhcan_total_lv : 0) * floatval($b['linhcan_per_level']));
        $all = min(floatval($b['max_total']), $hm + $tc + $tf + $lc);
        return array($all, array('Huyết Mạch' => $hm, 'Thể Chất' => $tc, 'Thiên Phú' => $tf, 'Linh Căn' => $lc));
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_lifespan')) {
    // Thọ nguyên (năm) theo cấp + % thưởng hệ thống.
    function thonguyen_lifespan($player){
        $c = thonguyen_config();
        $lv = max(0, intval($player->ulv));
        $i = intval(floor($lv / 50));
        $t0 = thonguyen_realm_base($i);
        $t1 = thonguyen_realm_base($i + 1);
        $base = $t0 + ($t1 - $t0) * floatval($c['in_realm_share']) * ($lv % 50) / 50;
        list($pct) = thonguyen_bonus_pct($player);
        return $base * (1 + $pct / 100);
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_status')) {
    // Trạng thái: tuổi (năm, số thực), thọ nguyên, % còn lại, hệ số hấp thụ, đã Tọa Hóa chưa. false nếu tính năng không chạy.
    function thonguyen_status($player, $dblj){
        $moc = thonguyen_moc($player->sid, $dblj);
        if ($moc <= 0) return false;
        $c = thonguyen_config();
        $ysec = floatval($c['hours_per_year']) * 3600;
        $age = max(0, time() - $moc) / $ysec;
        $life = thonguyen_lifespan($player);
        $left = $life > 0 ? max(0, ($life - $age) / $life * 100) : 0;
        $mult = 1; $cut = 0;
        foreach ($c['penalty'] as $p) {   // [ngưỡng % còn lại, % giảm] — ngưỡng nhỏ trước
            if ($left <= floatval($p[0])) { $cut = intval($p[1]); $mult = 1 - $cut / 100; break; }
        }
        $dead = $age >= $life;
        return array('age' => $age, 'life' => $life, 'left_pct' => $left, 'mult' => $dead ? 0 : $mult, 'cut' => $cut,
            'dead' => $dead, 'ysec' => $ysec, 'moc' => $moc);
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_absorb_mult')) {
    // Hệ số hấp thụ linh khí cho tu luyện / bế quan (1 = bình thường).
    function thonguyen_absorb_mult($player, $dblj){
        $s = thonguyen_status($player, $dblj);
        return $s ? $s['mult'] : 1;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_is_dead')) {
    function thonguyen_is_dead($player, $dblj){
        $s = thonguyen_status($player, $dblj);
        return $s ? $s['dead'] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_years_text')) {
    // 1234 → "1.234", 125000 → "12,5 vạn", 3e8 → "3 vạn vạn"… cho số năm lớn.
    function thonguyen_years_text($y){
        $y = floatval($y);
        if ($y < 100000) return number_format(floor($y), 0, ',', '.');
        $units = array(array(1e16, 'triệu ức'), array(1e12, 'vạn ức'), array(1e8, 'ức'), array(1e4, 'vạn'));
        foreach ($units as $u) {
            if ($y >= $u[0]) {
                $v = $y / $u[0];
                return ($v >= 100 ? number_format(floor($v), 0, ',', '.') : rtrim(rtrim(number_format($v, 1, ',', '.'), '0'), ',')) . ' ' . $u[1];
            }
        }
        return number_format(floor($y), 0, ',', '.');
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_realm_index')) {
    function thonguyen_realm_index($player){
        return intval(floor(max(0, intval($player->ulv)) / 50));
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_price')) {
    // Giá mua tại trang Thọ Nguyên theo cảnh giới: array(số tiền, 'lt'|'mt').
    function thonguyen_price($key, $player){
        $c = thonguyen_config();
        $n = thonguyen_realm_index($player) + 1;
        if ($key === 'cuuchuyen') return array(intval($c['price']['cuuchuyen_mt']) * $n, 'mt');
        $base = intval($c['price'][$key . '_lt']);
        return array(intval(round($base * pow($n, 1.5) / 100) * 100), 'lt');
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_set_age')) {
    // Đặt tuổi = $years năm (đổi moc). Chỉ ghi khi moc chưa bị đổi giữa chừng (chống bấm đúp).
    function thonguyen_set_age($sid, $years, $s, $dblj){
        $newMoc = time() - intval(floor(max(0, $years) * $s['ysec']));
        $st = $dblj->prepare("UPDATE player_thonguyen SET moc = ? WHERE sid = ? AND moc = ?");
        $st->execute(array($newMoc, $sid, $s['moc']));
        thonguyen_moc($sid, $dblj, true);
        return $st->rowCount() > 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_apply')) {
    // Tác dụng 1 viên (đã trừ vật phẩm hay chưa do người gọi lo): array(ok, msg).
    function thonguyen_apply($sid, $key, $dblj, $check_only = false){
        $player = getplayer($sid, $dblj);
        $s = thonguyen_status($player, $dblj);
        if (!$s) return array(false, 'Tính năng Thọ Nguyên chưa bật.');
        $c = thonguyen_config();
        if ($key === 'dientho') {
            if ($s['dead']) return array(false, 'Đã Tọa Hóa — Diên Thọ Đan vô dụng, cần Hồi Hồn Đan.');
            if ($s['age'] < 1) return array(false, 'Tuổi còn quá nhỏ, chưa cần Diên Thọ Đan.');
            $newAge = max(0, $s['age'] - $s['life'] * floatval($c['dientho_pct']) / 100);
            $msg = 'Diên Thọ Đan: tuổi ' . thonguyen_years_text($s['age']) . ' → ' . thonguyen_years_text($newAge) . '.';
        } elseif ($key === 'daitinh' || $key === 'cuuchuyen') {
            if (!$s['dead']) return array(false, 'Chưa Tọa Hóa, không cần Hồi Hồn Đan.');
            $leftPct = floatval($c[$key . '_left_pct']);
            $newAge = $s['life'] * (1 - $leftPct / 100);
            $msg = ($key === 'daitinh' ? 'Đại Tỉnh Hồi Hồn Đan' : 'Cửu Chuyển Hoàn Hồn Đan') . ': hồn phách quy vị, sống lại với ' . $leftPct . '% thọ nguyên.';
        } else {
            return array(false, 'Vật phẩm này không thể sử dụng.');
        }
        if ($check_only) return array(true, '');
        if (!thonguyen_set_age($sid, $newAge, $s, $dblj)) return array(false, 'Thao tác quá nhanh, thử lại.');
        player_cache_clear($sid);
        return array(true, $msg);
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_use_item')) {
    // Nhánh use_effect thonguyen_* của game/use_dj.php — dùng 1 viên trong túi.
    function thonguyen_use_item($sid, $djid, $type, $dblj){
        $key = substr($type, strlen('thonguyen_'));
        list($ok, $msg) = thonguyen_apply($sid, $key, $dblj, true);
        if (!$ok) return array(false, $msg);
        if (!deledjsum(intval($djid), 1, $sid, $dblj)) return array(false, 'Không còn vật phẩm này.');
        return thonguyen_apply($sid, $key, $dblj);
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_buy')) {
    // Mua 1 viên tại trang Thọ Nguyên (vào túi): array(ok, msg).
    function thonguyen_buy($sid, $key, $dblj){
        $c = thonguyen_config();
        if (!isset($c['items'][$key])) return array(false, 'Vật phẩm không hợp lệ.');
        $player = getplayer($sid, $dblj);
        list($price, $cur) = thonguyen_price($key, $player);
        $ok = $cur === 'mt' ? changeczb(2, $price, $sid, $dblj) : changeyxb(2, $price, $sid, $dblj);
        if (!$ok) return array(false, 'Không đủ ' . ($cur === 'mt' ? 'Ma Thạch' : 'Linh Thạch') . ' (cần ' . number_format($price) . ').');
        adddj($sid, intval($c['items'][$key]), 1, $dblj);
        player_cache_clear($sid);
        return array(true, 'Đã mua 1 viên (−' . number_format($price) . ' ' . ($cur === 'mt' ? 'Ma Thạch' : 'Linh Thạch') . ').');
    }
}

if (!function_exists(__NAMESPACE__ . '\\thonguyen_block_cmd')) {
    // Lệnh bị khoá khi Tọa Hóa: tu luyện, đánh quái, boss, PK, đột phá, phó bản.
    function thonguyen_block_cmd($cmd){
        static $list = array('pve', 'pvegj', 'pvp', 'pvb', 'pvbgj', 'startxiulian', 'tupo', 'clubboss_fight', 'linhcan_boss',
            'huyetmach_boss', 'toayeuthap', 'vandinhdai', 'hammo', 'sauthu');
        return in_array($cmd, $list, true);
    }
}
