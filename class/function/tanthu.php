<?php
namespace player;

/**
 * tanthu.php — Quà Tân Thủ 3 ngày đầu (docs/DESIGN_TANTHU_3NGAY.md, class/config/tanthu_config.json).
 * - Bảng player_tanthu (sid, created_at, lt, x2_until) — KHÔNG thêm cột vào game1 (game1 InnoDB Compact đã sát trần cỡ dòng).
 *   Dòng tạo lúc lập nhân vật (game.php cjplayer); nhân vật cũ không có dòng ⇒ không nhận quà tân thủ.
 * - Ngày tuổi nhân vật theo ngày lịch kể từ player_tanthu.created_at (ngày tạo = ngày 1).
 * - Tân Thủ Tu Vi Đan: lấp đầy thanh tu vi, chỉ trong `days` ngày đầu và dưới trần cấp của ngày.
 * - Túi Linh Thạch Tân Thủ: cộng vào player_tanthu.lt — chỉ dùng trả phí đột phá (trừ trước LT thường).
 * - Thẻ ×2 Hấp Thụ Linh Khí: player_tanthu.x2_until, tu luyện ×2 tới hạn.
 * - Điểm danh + mốc trực tuyến theo ngày tuổi: bảng player_tanthu_claim (INSERT IGNORE chống nhận lặp).
 * Bảng do sql/migrate_tanthu.php tạo; chưa migrate thì mọi hàm trả về "chưa bật", game chạy như cũ.
 */

if (!function_exists(__NAMESPACE__ . '\\tanthu_config')) {
    function tanthu_config(){
        static $cache = null;
        if ($cache === null) {
            $file = __DIR__ . '/../config/tanthu_config.json';
            $data = is_file($file) ? json_decode(file_get_contents($file), true) : null;
            $cache = is_array($data) ? $data : array();
        }
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_ready')) {
    // Đã chạy migrate_tanthu.php chưa (bảng player_tanthu + player_tanthu_claim).
    function tanthu_ready($dblj){
        static $ok = null;
        if ($ok !== null) return $ok;
        $ok = false;
        try {
            $st = @$dblj->query("SHOW TABLES LIKE 'player_tanthu%'");
            $tbl = $st ? $st->fetchAll(\PDO::FETCH_COLUMN) : array();
            $ok = in_array('player_tanthu', $tbl, true) && in_array('player_tanthu_claim', $tbl, true);
        } catch (\Exception $e) {
            $ok = false;
        }
        return $ok;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_row')) {
    function tanthu_row($sid, $dblj){
        if (!tanthu_ready($dblj)) return false;
        $st = $dblj->prepare("SELECT g.ulv, t.created_at, t.lt AS tanthu_lt, t.x2_until AS tanthu_x2_until
            FROM game1 g LEFT JOIN player_tanthu t ON t.sid = g.sid WHERE g.sid = ? LIMIT 1");
        $st->execute(array($sid));
        return $st->fetch(\PDO::FETCH_ASSOC);
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_day')) {
    // Ngày tuổi (1, 2, 3, …) theo ngày lịch; 0 = không có ngày tạo (nhân vật cũ / chưa migrate).
    function tanthu_day($sid, $dblj, $row = null){
        $r = $row ? $row : tanthu_row($sid, $dblj);
        if (!$r || empty($r['created_at'])) return 0;
        $c = strtotime(substr($r['created_at'], 0, 10));
        if (!$c) return 0;
        return intval(floor((strtotime(date('Y-m-d')) - $c) / 86400)) + 1;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_active_day')) {
    // Ngày tuổi nếu còn trong thời gian tân thủ, ngược lại 0.
    function tanthu_active_day($sid, $dblj, $row = null){
        $cfg = tanthu_config();
        $d = tanthu_day($sid, $dblj, $row);
        return ($d >= 1 && $d <= intval(isset($cfg['days']) ? $cfg['days'] : 3)) ? $d : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_cap')) {
    function tanthu_cap($day){
        $cfg = tanthu_config();
        return isset($cfg['cap'][(string)intval($day)]) ? intval($cfg['cap'][(string)intval($day)]) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_item')) {
    function tanthu_item($key){
        $cfg = tanthu_config();
        return isset($cfg['items'][$key]) ? intval($cfg['items'][$key]) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_pill_check')) {
    // Có dùng được Tân Thủ Tu Vi Đan lúc này không: array(ok, msg).
    function tanthu_pill_check($sid, $dblj){
        $r = tanthu_row($sid, $dblj);
        if (!$r) return array(false, 'Tính năng tân thủ chưa bật.');
        $d = tanthu_active_day($sid, $dblj, $r);
        if ($d <= 0) return array(false, 'Tân Thủ Tu Vi Đan chỉ dùng được trong 3 ngày đầu kể từ khi tạo nhân vật.');
        $cap = tanthu_cap($d);
        if (intval($r['ulv']) >= $cap) return array(false, 'Hôm nay (ngày ' . $d . ') Tân Thủ Tu Vi Đan chỉ dùng dưới cấp ' . $cap . '.');
        $p = getplayer($sid, $dblj);
        if (intval($p->uexp) >= intval($p->umaxexp)) return array(false, 'Thanh tu vi đã đầy, hãy Đột Phá trước.');
        return array(true, '');
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_use_pill')) {
    // Dùng 1 Tân Thủ Tu Vi Đan: lấp đầy thanh tu vi (uexp = umaxexp). array(ok, msg).
    function tanthu_use_pill($sid, $dblj){
        list($ok, $msg) = tanthu_pill_check($sid, $dblj);
        if (!$ok) return array(false, $msg);
        $pid = tanthu_item('pill');
        if (!deledjsum($pid, 1, $sid, $dblj)) return array(false, 'Không còn Tân Thủ Tu Vi Đan.');
        $p = getplayer($sid, $dblj);
        $dblj->prepare("UPDATE game1 SET uexp = GREATEST(uexp, ?) WHERE sid = ?")->execute(array(intval($p->umaxexp), $sid));
        player_cache_clear($sid);
        return array(true, 'Dùng Tân Thủ Tu Vi Đan: tu vi đã đầy (' . number_format(intval($p->umaxexp)) . ').');
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_use_item')) {
    // Nhánh use_effect tanthu_* của game/use_dj.php. $all = dùng hết (chỉ cho Túi Linh Thạch).
    function tanthu_use_item($sid, $djid, $type, $all, $dblj){
        if (!tanthu_ready($dblj)) return array(false, 'Tính năng tân thủ chưa bật.');
        $cfg = tanthu_config();
        if ($type === 'tanthu_pill') return tanthu_use_pill($sid, $dblj);
        if ($type === 'tanthu_bag') {
            $have = getplayerdaoju($sid, intval($djid), $dblj);
            $n = $all ? ($have ? intval($have->djsum) : 0) : 1;
            if ($n < 1 || !deledjsum(intval($djid), $n, $sid, $dblj)) return array(false, 'Không còn Túi Linh Thạch Tân Thủ.');
            $add = $n * intval(isset($cfg['bag_lt']) ? $cfg['bag_lt'] : 500000);
            $dblj->prepare("INSERT INTO player_tanthu (sid, created_at, lt) VALUES (?, '2000-01-01 00:00:00', ?) ON DUPLICATE KEY UPDATE lt = lt + VALUES(lt)")
                 ->execute(array($sid, $add));
            player_cache_clear($sid);
            return array(true, 'Mở ' . $n . ' Túi Linh Thạch Tân Thủ: Túi Tân Thủ +' . number_format($add) . ' Linh Thạch (chỉ dùng trả phí Đột Phá).');
        }
        if ($type === 'tanthu_x2') {
            if (!deledjsum(intval($djid), 1, $sid, $dblj)) return array(false, 'Không còn thẻ.');
            $h = intval(isset($cfg['x2_hours']) ? $cfg['x2_hours'] : 72);
            $r = tanthu_row($sid, $dblj);
            $from = max(time(), ($r && !empty($r['tanthu_x2_until'])) ? intval(strtotime($r['tanthu_x2_until'])) : 0);
            $until = date('Y-m-d H:i:s', $from + $h * 3600);
            $dblj->prepare("INSERT INTO player_tanthu (sid, created_at, x2_until) VALUES (?, '2000-01-01 00:00:00', ?) ON DUPLICATE KEY UPDATE x2_until = VALUES(x2_until)")
                 ->execute(array($sid, $until));
            player_cache_clear($sid);
            return array(true, 'Kích hoạt Thẻ ×2 Hấp Thụ Linh Khí tới ' . $until . '.');
        }
        return array(false, 'Vật phẩm này không thể sử dụng.');
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_x2_mult')) {
    // Hệ số tu luyện của Thẻ ×2 (2 khi còn hạn, ngược lại 1).
    function tanthu_x2_mult($sid, $dblj){
        $r = tanthu_row($sid, $dblj);
        if (!$r || empty($r['tanthu_x2_until'])) return 1;
        return strtotime($r['tanthu_x2_until']) > time() ? 2 : 1;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_pool')) {
    function tanthu_pool($sid, $dblj){
        $r = tanthu_row($sid, $dblj);
        return $r ? max(0, intval($r['tanthu_lt'])) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_pay_tupo')) {
    // Trả phí đột phá: Túi Tân Thủ trước, thiếu thì Linh Thạch thường — một câu UPDATE có điều kiện (không trừ nửa vời).
    // Trả về true/false như changeyxb(2, ...). Chưa migrate ⇒ đúng changeyxb cũ.
    function tanthu_pay_tupo($sid, $cost, $dblj){
        $cost = intval($cost);
        if (!tanthu_ready($dblj)) return changeyxb(2, $cost, $sid, $dblj);
        if ($cost <= 0) return true;
        $pool = tanthu_pool($sid, $dblj);
        $a = min($pool, $cost); $b = $cost - $a;
        if ($a <= 0) return changeyxb(2, $b, $sid, $dblj);
        // 2 bảng InnoDB trong 1 câu UPDATE ⇒ trừ cả hai hoặc không trừ gì
        $st = $dblj->prepare("UPDATE game1 g JOIN player_tanthu t ON t.sid = g.sid SET t.lt = t.lt - ?, g.uyxb = g.uyxb - ?
            WHERE g.sid = ? AND t.lt >= ? AND g.uyxb >= ?");
        $st->execute(array($a, $b, $sid, $a, $b));
        player_cache_clear($sid);
        return $st->rowCount() > 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_status')) {
    // Trạng thái quà ngày tuổi hiện tại cho tab Trực Tuyến: false nếu không còn tân thủ.
    function tanthu_status($sid, $lv, $dblj){
        $r = tanthu_row($sid, $dblj);
        $d = tanthu_active_day($sid, $dblj, $r);
        if ($d <= 0) return false;
        $cfg = tanthu_config();
        $st = $dblj->prepare("SELECT kind FROM player_tanthu_claim WHERE sid = ? AND day = ?");
        $st->execute(array($sid, $d));
        $got = array_flip($st->fetchAll(\PDO::FETCH_COLUMN));
        $on = online_reward_status($sid, $lv, $dblj);
        $min = intval($on['minutes']);
        $daily = isset($cfg['daily'][(string)$d]) ? $cfg['daily'][(string)$d] : array('pill' => 0, 'bag' => 0);
        $list = array();
        $ms = isset($cfg['online'][(string)$d]) ? $cfg['online'][(string)$d] : array();
        foreach ($ms as $i => $m) {
            $list[] = array('idx' => $i, 'minutes' => intval($m[0]), 'pill' => intval($m[1]), 'bag' => intval($m[2]),
                'reached' => $min >= intval($m[0]), 'claimed' => isset($got['on' . $i]));
        }
        return array('day' => $d, 'days' => intval($cfg['days']), 'cap' => tanthu_cap($d), 'minutes' => $min,
            'daily' => array('pill' => intval($daily['pill']), 'bag' => intval($daily['bag']), 'claimed' => isset($got['daily'])),
            'online' => $list, 'pool' => $r ? max(0, intval($r['tanthu_lt'])) : 0,
            'x2_until' => ($r && !empty($r['tanthu_x2_until']) && strtotime($r['tanthu_x2_until']) > time()) ? $r['tanthu_x2_until'] : '');
    }
}

if (!function_exists(__NAMESPACE__ . '\\tanthu_claim')) {
    // Nhận quà điểm danh ($kind = 'daily') hoặc mốc trực tuyến ($kind = 'on<idx>') của ngày tuổi hiện tại.
    function tanthu_claim($sid, $kind, $lv, $dblj){
        $s = tanthu_status($sid, $lv, $dblj);
        if (!$s) return array('ok' => false, 'msg' => 'Đã hết thời gian quà tân thủ.');
        $pill = 0; $bag = 0; $name = '';
        if ($kind === 'daily') {
            if ($s['daily']['claimed']) return array('ok' => false, 'msg' => 'Hôm nay đã điểm danh.');
            $pill = $s['daily']['pill']; $bag = $s['daily']['bag']; $name = 'Điểm danh tân thủ ngày ' . $s['day'];
        } elseif (preg_match('/^on(\d+)$/', $kind, $mm) && isset($s['online'][intval($mm[1])])) {
            $m = $s['online'][intval($mm[1])];
            if ($m['claimed']) return array('ok' => false, 'msg' => 'Đã nhận mốc này rồi.');
            if (!$m['reached']) return array('ok' => false, 'msg' => 'Chưa đủ ' . $m['minutes'] . ' phút trực tuyến hôm nay.');
            $pill = $m['pill']; $bag = $m['bag']; $name = 'Trực tuyến tân thủ ' . ($m['minutes'] / 60) . ' giờ';
        } else {
            return array('ok' => false, 'msg' => 'Mốc không tồn tại.');
        }
        $lock = $dblj->prepare("INSERT IGNORE INTO player_tanthu_claim (sid, day, kind, created_at) VALUES (?, ?, ?, ?)");
        $lock->execute(array($sid, $s['day'], $kind, date('Y-m-d H:i:s')));
        if ($lock->rowCount() < 1) return array('ok' => false, 'msg' => 'Đã nhận rồi.');
        $parts = array();
        if ($pill > 0) { adddj($sid, tanthu_item('pill'), $pill, $dblj); $parts[] = $pill . ' Tân Thủ Tu Vi Đan'; }
        if ($bag > 0) { adddj($sid, tanthu_item('bag'), $bag, $dblj); $parts[] = $bag . ' Túi Linh Thạch Tân Thủ'; }
        return array('ok' => true, 'msg' => $name . ': ' . implode(', ', $parts) . '.');
    }
}
