<?php
namespace player;

/**
 * Đợt C — Thưởng trực tuyến (mốc phút hoạt động trong ngày) và Bế Quan (thưởng ngoại tuyến).
 * Cấu hình: class/config/nhiemvu_reward_rules.json (khối "online", "offline").
 * Bảng tự tạo khi chạy lần đầu (giống player_combat_state).
 */

if (!function_exists(__NAMESPACE__ . '\\online_reward_ready')) {
    function online_reward_ready($dblj){
        static $ok = null;
        if ($ok !== null) return $ok;
        try {
            $dblj->exec("CREATE TABLE IF NOT EXISTS player_online_reward (
                sid VARCHAR(64) NOT NULL,
                day DATE NOT NULL,
                seconds INT NOT NULL DEFAULT 0,
                last_ts INT NOT NULL DEFAULT 0,
                claimed VARCHAR(64) NOT NULL DEFAULT '',
                PRIMARY KEY (sid, day)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
            $dblj->exec("CREATE TABLE IF NOT EXISTS player_offline_reward (
                sid VARCHAR(64) NOT NULL,
                pending_exp BIGINT NOT NULL DEFAULT 0,
                pending_hours INT NOT NULL DEFAULT 0,
                day DATE NOT NULL,
                hours_today INT NOT NULL DEFAULT 0,
                PRIMARY KEY (sid)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
            $ok = true;
        } catch (\Exception $e) {
            $ok = false;
        }
        return $ok;
    }
}

if (!function_exists(__NAMESPACE__ . '\\online_reward_tick')) {
    // Gọi mỗi request hợp lệ: cộng khoảng cách tới request trước nếu ≤ max_gap_seconds (treo máy không tính).
    function online_reward_tick($sid, $dblj){
        if (!online_reward_ready($dblj)) return;
        $rules = nhiemvu_rules();
        $gap = isset($rules['online']['max_gap_seconds']) ? intval($rules['online']['max_gap_seconds']) : 300;
        $now = time();
        $stmt = $dblj->prepare("INSERT INTO player_online_reward (sid, day, seconds, last_ts) VALUES (?, ?, 0, ?)
            ON DUPLICATE KEY UPDATE seconds = seconds + IF(? - last_ts BETWEEN 1 AND ?, ? - last_ts, 0), last_ts = ?");
        $stmt->execute(array($sid, date('Y-m-d', $now), $now, $now, $gap, $now, $now));
    }
}

if (!function_exists(__NAMESPACE__ . '\\online_reward_status')) {
    // Danh sách mốc hôm nay: minutes, reached, claimed + thưởng thực nhận ở cấp $lv.
    function online_reward_status($sid, $lv, $dblj){
        $rules = nhiemvu_rules();
        $ms = isset($rules['online']['milestones']) ? $rules['online']['milestones'] : array();
        $row = array('seconds' => 0, 'claimed' => '');
        if (online_reward_ready($dblj)) {
            $st = $dblj->prepare("SELECT seconds, claimed FROM player_online_reward WHERE sid = ? AND day = ?");
            $st->execute(array($sid, date('Y-m-d')));
            $r = $st->fetch(\PDO::FETCH_ASSOC);
            if ($r) $row = $r;
        }
        $min = intval(floor(intval($row['seconds']) / 60));
        $out = array();
        foreach ($ms as $i => $m) {
            $out[] = array(
                'idx' => $i, 'minutes' => intval($m['minutes']),
                'reached' => $min >= intval($m['minutes']),
                'claimed' => strpos(',' . $row['claimed'], ',' . $i . ',') !== false,
                'lt' => intval(isset($m['lt_mult']) ? $m['lt_mult'] : 0) * max(1, intval($lv)),
                'mt' => intval(isset($m['mt']) ? $m['mt'] : 0),
                'stamina' => intval(isset($m['stamina']) ? $m['stamina'] : 0),
                'items' => isset($m['items']) ? $m['items'] : array(),
            );
        }
        return array('minutes' => $min, 'list' => $out);
    }
}

if (!function_exists(__NAMESPACE__ . '\\online_reward_item_name')) {
    function online_reward_item_name($it){
        if ($it['type'] === 'yaopin') {
            $c = yaopin_config_get(intval($it['id']));
            return isset($c['ypname']) ? $c['ypname'] : 'Dược phẩm';
        }
        $c = daoju_config_get(intval($it['id']));
        return isset($c['djname']) ? $c['djname'] : 'Đạo cụ';
    }
}

if (!function_exists(__NAMESPACE__ . '\\online_reward_claim')) {
    function online_reward_claim($sid, $idx, $lv, $dblj){
        $idx = intval($idx);
        $stt = online_reward_status($sid, $lv, $dblj);
        if (!isset($stt['list'][$idx])) return array('ok' => false, 'msg' => 'Mốc không tồn tại.');
        $m = $stt['list'][$idx];
        if (!$m['reached']) return array('ok' => false, 'msg' => 'Chưa đủ ' . $m['minutes'] . ' phút trực tuyến hôm nay.');
        if ($m['claimed']) return array('ok' => false, 'msg' => 'Đã nhận mốc này rồi.');
        // Khóa nhận trước khi phát (chống bấm đúp)
        $lock = $dblj->prepare("UPDATE player_online_reward SET claimed = CONCAT(claimed, ?) WHERE sid = ? AND day = ?
            AND CONCAT(',', claimed) NOT LIKE ?");
        $lock->execute(array($idx . ',', $sid, date('Y-m-d'), '%,' . $idx . ',%'));
        if ($lock->rowCount() < 1) return array('ok' => false, 'msg' => 'Đã nhận mốc này rồi.');

        $parts = array();
        if ($m['lt'] > 0) { changeyxb(1, $m['lt'], $sid, $dblj); $parts[] = '+' . number_format($m['lt']) . ' linh thạch'; }
        if ($m['mt'] > 0) { changeczb(1, $m['mt'], $sid, $dblj); $parts[] = '+' . $m['mt'] . ' ma thạch'; }
        if ($m['stamina'] > 0) {
            $st = $dblj->prepare("UPDATE game1 SET ustamina = LEAST(umaxstamina + ulv - 1, ustamina + ?) WHERE sid = ?");
            $st->execute(array($m['stamina'], $sid));
            player_cache_clear($sid);
            $parts[] = '+' . $m['stamina'] . ' thể lực';
        }
        foreach ($m['items'] as $it) {
            if ($it['type'] === 'yaopin') addyaopin($sid, intval($it['id']), intval($it['count']), $dblj);
            else adddj($sid, intval($it['id']), intval($it['count']), $dblj);
            $parts[] = online_reward_item_name($it) . ' x' . intval($it['count']);
        }
        return array('ok' => true, 'msg' => 'Nhận thưởng trực tuyến ' . $m['minutes'] . ' phút: ' . implode(', ', $parts));
    }
}

// ─── Bế Quan (ngoại tuyến) ──────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\offline_reward_on_login')) {
    // Gọi lúc đăng nhập, TRƯỚC khi làm mới endtime. Tích tu vi bế quan theo số giờ offline,
    // tối đa max_hours giờ mỗi ngày; đang Tu Luyện thì không tính (tránh nhận đôi).
    function offline_reward_on_login($player, $dblj){
        if (!online_reward_ready($dblj)) return 0;
        $rules = nhiemvu_rules();
        $cfg = isset($rules['offline']) ? $rules['offline'] : array();
        $minLv = isset($cfg['min_level']) ? intval($cfg['min_level']) : 10;
        $minH  = isset($cfg['min_hours']) ? intval($cfg['min_hours']) : 1;
        $maxH  = isset($cfg['max_hours']) ? intval($cfg['max_hours']) : 8;
        $pct   = isset($cfg['exp_pct_per_hour']) ? floatval($cfg['exp_pct_per_hour']) : 2;
        if (intval($player->ulv) < $minLv || intval($player->sfxl) > 0) return 0;
        $endTs = strtotime($player->endtime);
        if (!$endTs) return 0;
        $hours = intval(floor((time() - $endTs) / 3600));
        if ($hours < $minH) return 0;

        $today = date('Y-m-d');
        $st = $dblj->prepare("SELECT day, hours_today FROM player_offline_reward WHERE sid = ?");
        $st->execute(array($player->sid));
        $r = $st->fetch(\PDO::FETCH_ASSOC);
        $used = ($r && $r['day'] === $today) ? intval($r['hours_today']) : 0;
        $grant = min($hours, $maxH - $used);
        if ($grant <= 0) return 0;
        $exp = intval(floor(nhiemvu_level_need(intval($player->ulv)) * $pct * $grant / 100));
        $up = $dblj->prepare("INSERT INTO player_offline_reward (sid, pending_exp, pending_hours, day, hours_today) VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE pending_exp = pending_exp + VALUES(pending_exp), pending_hours = pending_hours + VALUES(pending_hours),
            hours_today = IF(day = VALUES(day), hours_today + VALUES(hours_today), VALUES(hours_today)), day = VALUES(day)");
        $up->execute(array($player->sid, $exp, $grant, $today, $grant));
        return $exp;
    }
}

if (!function_exists(__NAMESPACE__ . '\\offline_reward_pending')) {
    function offline_reward_pending($sid, $dblj){
        if (!online_reward_ready($dblj)) return array('exp' => 0, 'hours' => 0);
        $st = $dblj->prepare("SELECT pending_exp, pending_hours FROM player_offline_reward WHERE sid = ?");
        $st->execute(array($sid));
        $r = $st->fetch(\PDO::FETCH_ASSOC);
        return $r ? array('exp' => intval($r['pending_exp']), 'hours' => intval($r['pending_hours'])) : array('exp' => 0, 'hours' => 0);
    }
}

if (!function_exists(__NAMESPACE__ . '\\offline_reward_claim')) {
    function offline_reward_claim($sid, $dblj){
        $p = offline_reward_pending($sid, $dblj);
        if ($p['exp'] <= 0) return array('ok' => false, 'msg' => 'Không có tu vi bế quan để nhận.');
        $player = getplayer($sid, $dblj);
        if (intval($player->uexp) >= intval($player->umaxexp)) {
            return array('ok' => false, 'msg' => 'Tu vi đã đầy, hãy Đột Phá trước rồi nhận tu vi bế quan.');
        }
        $lock = $dblj->prepare("UPDATE player_offline_reward SET pending_exp = 0, pending_hours = 0 WHERE sid = ? AND pending_exp = ?");
        $lock->execute(array($sid, $p['exp']));
        if ($lock->rowCount() < 1) return array('ok' => false, 'msg' => 'Đã nhận rồi.');
        changeexp($sid, $dblj, $p['exp']);
        return array('ok' => true, 'msg' => 'Bế quan ' . $p['hours'] . ' giờ: nhận ' . number_format($p['exp']) . ' tu vi.');
    }
}
