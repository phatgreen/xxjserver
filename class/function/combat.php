<?php
namespace player;

/**
 * combat.php — Thế võ khi chiến đấu (Võ Kỹ / Tuyệt Kỹ / Tuyệt Chiêu) + chiêu phản kích của quái.
 * Số liệu cân bằng: class/config/wugong_skill_config.json, class/config/guaiwu_skill_config.json.
 * Trạng thái trận (lượt, hồi chiêu): bảng player_combat_state (sql/sql_combat_state.sql).
 * Tương thích PHP 5.6.
 */

if (!function_exists(__NAMESPACE__ . '\\combat_cfg')) {
    // Đọc 1 file config JSON trong class/config, cache theo request.
    function combat_cfg($file){
        static $cache = array();
        if (!isset($cache[$file])) {
            $path = __DIR__ . '/../config/' . $file;
            $data = is_file($path) ? json_decode(file_get_contents($path), true) : null;
            $cache[$file] = is_array($data) ? $data : array();
        }
        return $cache[$file];
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_rule')) {
    // Lấy 1 giá trị trong 'rules' của wugong_skill_config.json.
    function combat_rule($key, $tier = null, $default = 0){
        $cfg = combat_cfg('wugong_skill_config.json');
        if (!isset($cfg['rules'][$key])) return $default;
        $v = $cfg['rules'][$key];
        if ($tier === null) return $v;
        return isset($v[(string)$tier]) ? $v[(string)$tier] : $default;
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_move_mult')) {
    // Hệ số sát thương của thế theo cấp võ công: base + per_lv * (cấp - cấp mở), chặn max, cộng thưởng phẩm.
    function combat_move_mult($tier, $wgdj, $rarity){
        $m = combat_rule('mult', $tier, array());
        $base = isset($m['base']) ? floatval($m['base']) : 1.0;
        $per = isset($m['per_lv']) ? floatval($m['per_lv']) : 0;
        $max = isset($m['max']) ? floatval($m['max']) : $base;
        $unlock = intval(combat_rule('unlock_lv', $tier, 0));
        $mult = min($max, $base + $per * max(0, intval($wgdj) - $unlock));
        if ($tier > 1) {
            $rb = combat_rule('rarity_bonus', null, array());
            if (isset($rb[$rarity])) $mult += floatval($rb[$rarity]);
        }
        return round($mult, 2);
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_wugong_moves')) {
    /**
     * 3 thế của võ công đang trang bị. $wg = object từ wgcx() (cần wgid, wgdj, wgname, rarity).
     * Trả array(1 => move, 2 => move, 3 => move); move có: tier, label, name, effect, value, hits,
     * text, mult, unlock_lv, unlocked, cooldown, stamina_mult.
     */
    function combat_wugong_moves($wg){
        $out = array();
        if (!$wg || intval($wg->wgid) <= 0) return $out;
        $cfg = combat_cfg('wugong_skill_config.json');
        $list = isset($cfg['moves'][(string)intval($wg->wgid)]) ? $cfg['moves'][(string)intval($wg->wgid)] : array();
        $wgdj = intval(isset($wg->wgdj) ? $wg->wgdj : 0);
        $rarity = isset($wg->rarity) ? $wg->rarity : '';
        for ($t = 1; $t <= 3; $t++) {
            $mv = isset($list[$t - 1]) ? $list[$t - 1] : array();
            $label = combat_rule('tier_label', $t, 'Thế ' . $t);
            $unlock = intval(combat_rule('unlock_lv', $t, 0));
            $out[$t] = array(
                'tier' => $t,
                'label' => $label,
                // Võ công chưa có cấu hình thế: dùng tên võ công + nhãn bậc.
                'name' => isset($mv['name']) ? $mv['name'] : ($wg->wgname . ' · ' . $label),
                'effect' => isset($mv['effect']) ? $mv['effect'] : '',
                'value' => isset($mv['value']) ? intval($mv['value']) : 0,
                'hits' => isset($mv['hits']) ? max(1, intval($mv['hits'])) : 1,
                'text' => isset($mv['text']) ? $mv['text'] : 'Ngươi thi triển ' . $wg->wgname . ' đánh vào {mob}.',
                'mult' => combat_move_mult($t, $wgdj, $rarity),
                'unlock_lv' => $unlock,
                'unlocked' => $wgdj >= $unlock,
                'cooldown' => intval(combat_rule('cooldown', $t, 0)),
                'stamina_mult' => floatval(combat_rule('stamina_mult', $t, 1)),
            );
        }
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_effect_text')) {
    // Mô tả ngắn hiệu ứng của thế (dùng cho trang Võ Công và nút trong trận).
    function combat_effect_text($effect, $value){
        $value = intval($value);
        switch ($effect) {
            case 'pierce':    return "Xuyên {$value}% phòng ngự";
            case 'crit':      return "{$value}% tỉ lệ chắc chắn bạo kích";
            case 'stun':      return "{$value}% làm địch mất lượt phản kích";
            case 'guard':     return "Giảm {$value}% sát thương nhận ở lượt này";
            case 'heal':      return "Hồi {$value}% khí huyết tối đa";
            case 'lifesteal': return "Hút {$value}% sát thương gây ra";
            case 'burn':      return "Thiêu đốt thêm {$value}% sát thương";
        }
        return '';
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_passive_scale')) {
    // Hiệu ứng bị động mới bật (crit_dmg, hp_regen, escape) mạnh dần theo cấp võ công: min → 100%.
    function combat_passive_scale($wgdj){
        $ps = combat_rule('passive_scale', null, array());
        $min = isset($ps['min']) ? floatval($ps['min']) : 0.3;
        $per = isset($ps['per_lv']) ? floatval($ps['per_lv']) : 0.07;
        return min(1.0, $min + $per * max(0, intval($wgdj)));
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_text')) {
    function combat_text($tpl, $mobName){
        return str_replace('{mob}', $mobName, $tpl);
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_mob_move')) {
    /**
     * Chọn chiêu phản kích của quái theo hệ. Trả array(name, text, mult).
     * name = '' khi là đòn thường.
     */
    function combat_mob_move($element){
        $cfg = combat_cfg('guaiwu_skill_config.json');
        $el = isset($cfg['element'][$element]) ? $cfg['element'][$element]
            : (isset($cfg['element']['none']) ? $cfg['element']['none'] : array());
        $chance = isset($cfg['rules']['special_chance']) ? intval($cfg['rules']['special_chance']) : 0;
        if (!empty($el['special']) && mt_rand(1, 100) <= $chance) {
            return array(
                'name' => $el['special']['name'],
                'text' => $el['special']['text'],
                'mult' => floatval($cfg['rules']['special_mult']),
            );
        }
        $basic = !empty($el['basic']) ? $el['basic'] : array('{mob} phản kích ngươi.');
        return array('name' => '', 'text' => $basic[mt_rand(0, count($basic) - 1)], 'mult' => 1.0);
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_state_ready')) {
    // Bảng player_combat_state có sẵn chưa; chưa có thì thử tạo (1 lần/request).
    function combat_state_ready($dblj){
        static $ready = null;
        if ($ready !== null) return $ready;
        $q = $dblj->query("SHOW TABLES LIKE 'player_combat_state'");
        $ready = ($q && $q->fetchColumn() !== false);
        if (!$ready) {
            $dblj->exec("CREATE TABLE IF NOT EXISTS player_combat_state (
                sid VARCHAR(32) NOT NULL PRIMARY KEY,
                gid INT NOT NULL DEFAULT 0,
                turn INT NOT NULL DEFAULT 0,
                cd2 INT NOT NULL DEFAULT 0,
                cd3 INT NOT NULL DEFAULT 0,
                flag INT NOT NULL DEFAULT 0,
                updated INT NOT NULL DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $q = $dblj->query("SHOW TABLES LIKE 'player_combat_state'");
            $ready = ($q && $q->fetchColumn() !== false);
        }
        // Cột flag (boss tụ lực) thêm sau — bảng tạo từ bản đầu thì bổ sung.
        if ($ready) {
            $q = $dblj->query("SHOW COLUMNS FROM player_combat_state LIKE 'flag'");
            if (!$q || $q->fetchColumn() === false) {
                $dblj->exec("ALTER TABLE player_combat_state ADD COLUMN flag INT NOT NULL DEFAULT 0 AFTER cd3");
                $q = $dblj->query("SHOW COLUMNS FROM player_combat_state LIKE 'flag'");
                $ready = ($q && $q->fetchColumn() !== false);
            }
        }
        return $ready;
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_state_load')) {
    /**
     * Trạng thái trận hiện tại. Đổi sang quái khác (gid khác) => reset.
     * 'ok' = false khi bảng không dùng được => chỉ cho dùng Võ Kỹ.
     */
    function combat_state_load($sid, $gid, $dblj){
        $st = array('ok' => false, 'gid' => intval($gid), 'turn' => 0, 'cd2' => 0, 'cd3' => 0, 'flag' => 0);
        if (!combat_state_ready($dblj)) return $st;
        $st['ok'] = true;
        $stmt = $dblj->prepare("SELECT gid, turn, cd2, cd3, flag FROM player_combat_state WHERE sid = ? LIMIT 1");
        if ($stmt && $stmt->execute(array($sid))) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row && intval($row['gid']) === intval($gid)) {
                $st['turn'] = intval($row['turn']);
                $st['cd2'] = intval($row['cd2']);
                $st['cd3'] = intval($row['cd3']);
                $st['flag'] = intval($row['flag']);
            }
        }
        return $st;
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_state_save')) {
    function combat_state_save($sid, $st, $dblj){
        if (empty($st['ok'])) return false;
        $stmt = $dblj->prepare("INSERT INTO player_combat_state (sid, gid, turn, cd2, cd3, flag, updated)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE gid = VALUES(gid), turn = VALUES(turn), cd2 = VALUES(cd2),
                cd3 = VALUES(cd3), flag = VALUES(flag), updated = VALUES(updated)");
        $flag = isset($st['flag']) ? intval($st['flag']) : 0;
        return $stmt ? $stmt->execute(array($sid, intval($st['gid']), intval($st['turn']),
            intval($st['cd2']), intval($st['cd3']), $flag, time())) : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_move_wait')) {
    // Số lượt còn phải chờ trước khi dùng lại thế $tier ở lượt kế tiếp (0 = dùng được).
    function combat_move_wait($st, $tier){
        if ($tier <= 1) return 0;
        $key = 'cd' . intval($tier);
        $next = intval($st['turn']) + 1;
        return isset($st[$key]) ? max(0, intval($st[$key]) - $next) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_cp_value')) {
    /**
     * Tổng % hiệu ứng công pháp đang trang bị theo loại, đã nhân hệ số + chặn trần
     * (rules.congphap_ratio / rules.congphap_cap). $player->cp_effects do getplayer() nạp.
     */
    function combat_cp_value($player, $type){
        if (empty($player->cp_effects) || !is_array($player->cp_effects)) return 0;
        $ratio = combat_rule('congphap_ratio', $type, 0);
        if ($ratio <= 0) return 0;
        $sum = 0;
        foreach ($player->cp_effects as $e) {
            if (isset($e['type']) && $e['type'] === $type) $sum += floatval($e['value']);
        }
        $cap = floatval(combat_rule('congphap_cap', $type, 0));
        $val = $sum * floatval($ratio);
        return round($cap > 0 ? min($cap, $val) : $val, 2);
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_cp_effective')) {
    // Giá trị % thực áp dụng của 1 công pháp (đã nhân hệ số, chưa xét trần tổng) — dùng để hiển thị.
    function combat_cp_effective($type, $val){
        $ratio = combat_rule('congphap_ratio', $type, null);
        return $ratio === null ? floatval($val) : floatval($val) * floatval($ratio);
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_pick_move')) {
    /**
     * Chọn thế được dùng ở lượt này. $tier = thế người chơi bấm; không đủ điều kiện => về Võ Kỹ.
     * $base = thể lực của 1 đòn thường; $freeTier1 = true khi đòn thường không tốn thể lực (Boss).
     * Trả array('move' => array|null, 'cost' => thể lực cần trừ, 'note' => lý do bị đổi về Võ Kỹ).
     */
    function combat_pick_move($moves, $st, $tier, $stamina, $base, $freeTier1){
        $move = isset($moves[1]) ? $moves[1] : null;
        $note = '';
        $tier = intval($tier);
        if ($tier > 1) {
            if (!isset($moves[$tier])) {
                $note = 'Chưa trang bị võ công.';
            } elseif (!$moves[$tier]['unlocked']) {
                $note = $moves[$tier]['label'] . ' mở khi võ công đạt cấp ' . $moves[$tier]['unlock_lv'] . '.';
            } elseif (empty($st['ok'])) {
                $note = 'Chưa khởi tạo dữ liệu thế võ (bảng player_combat_state).';
            } elseif (combat_move_wait($st, $tier) > 0) {
                $note = '«' . $moves[$tier]['name'] . '» còn hồi ' . combat_move_wait($st, $tier) . ' lượt.';
            } elseif (intval($stamina) < max(1, intval(round($base * $moves[$tier]['stamina_mult'])))) {
                $note = 'Không đủ thể lực để thi triển «' . $moves[$tier]['name'] . '».';
            } else {
                $move = $moves[$tier];
            }
        }
        $cost = 0;
        if ($move && ($move['tier'] > 1 || !$freeTier1)) {
            $cost = max(1, intval(round($base * $move['stamina_mult'])));
        }
        return array('move' => $move, 'cost' => $cost, 'note' => $note);
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_boss_rule')) {
    function combat_boss_rule($key, $default){
        $cfg = combat_cfg('guaiwu_skill_config.json');
        return isset($cfg['boss'][$key]) ? $cfg['boss'][$key] : $default;
    }
}

if (!function_exists(__NAMESPACE__ . '\\combat_boss_skill_name')) {
    // Tên chiêu tụ lực của boss = chiêu đặc trưng theo hệ.
    function combat_boss_skill_name($element){
        $cfg = combat_cfg('guaiwu_skill_config.json');
        if (isset($cfg['element'][$element]['special']['name'])) return $cfg['element'][$element]['special']['name'];
        return isset($cfg['element']['none']['special']['name']) ? $cfg['element']['none']['special']['name'] : 'Tuyệt Kỹ';
    }
}
