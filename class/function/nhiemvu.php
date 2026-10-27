<?php
namespace player;

// ─── Config loaders ────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_daily_config')) {
    function nhiemvu_daily_config(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(__DIR__ . '/../config/daily_quests.json');
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_weekly_config')) {
    function nhiemvu_weekly_config(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(__DIR__ . '/../config/weekly_quests.json');
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_monthly_config')) {
    function nhiemvu_monthly_config(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(__DIR__ . '/../config/monthly_quests.json');
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_achieve_config')) {
    function nhiemvu_achieve_config(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(__DIR__ . '/../config/achievements.json');
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_hidden_config')) {
    function nhiemvu_hidden_config(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(__DIR__ . '/../config/hidden_quests.json');
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_main_config')) {
    function nhiemvu_main_config(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(__DIR__ . '/../config/nhiemvu_main_config.json');
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

// ─── Helpers ───────────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_current_week_num')) {
    function nhiemvu_current_week_num(){
        return intval(date('o') . str_pad(date('W'), 2, '0', STR_PAD_LEFT));
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_current_month')) {
    function nhiemvu_current_month(){
        return date('Y-m');
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_type_match')) {
    function nhiemvu_type_match($questType, $type){
        if ($questType === $type) return true;
        $alias = array(
            'upgrade' => array('upgrade', 'upgrade_total'),
            'alchemy' => array('alchemy', 'alchemy_count'),
            'craft'   => array('craft', 'craft_count', 'craft_rarity'),
            'boss'    => array('boss', 'kill_boss'),
            'collect' => array('collect', 'collect_item', 'collect_multiple'),
        );
        if (!isset($alias[$type])) return false;
        return in_array($questType, $alias[$type]);
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_slot_id')) {
    // zhuangbei_config.zbtool ids; game/trangbi only names these slots
    function nhiemvu_slot_id($slot){
        if ($slot === 'any' || $slot === '' || $slot === null) return 0;
        if (is_numeric($slot)) return intval($slot);
        $map = array('weapon' => 1, 'hat' => 2, 'armor' => 3, 'shoes' => 4,
                     'gloves' => 5, 'necklace' => 7, 'ring' => 8);
        $k = strtolower($slot);
        return isset($map[$k]) ? $map[$k] : -1;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_rarity_min')) {
    // zhuangbei_config.rarity is 1..10 (Pham..Dao); color words map onto that ladder
    function nhiemvu_rarity_min($rarity){
        if (is_numeric($rarity)) return intval($rarity);
        $map = array('white' => 1, 'green' => 2, 'blue' => 3, 'purple' => 4,
                     'gold' => 5, 'yellow' => 5, 'orange' => 6, 'red' => 7);
        $k = strtolower(trim($rarity));
        return isset($map[$k]) ? $map[$k] : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_grant_reward')) {
    function nhiemvu_grant_reward($sid, $exp, $lt, $mt, $dblj){
        if ($exp > 0) changeexp($sid, $dblj, $exp);
        if ($lt > 0)  changeyxb(1, $lt, $sid, $dblj);
        if ($mt > 0)  changeczb(1, $mt, $sid, $dblj);
    }
}

// ─── Daily Quests ──────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_daily_get')) {
    function nhiemvu_daily_get($sid, $dblj){
        $today = date('Y-m-d');
        $config = nhiemvu_daily_config();
        $pool = isset($config['daily_quest_pool']) ? $config['daily_quest_pool'] : array();
        $count = min(intval(isset($config['daily_count']) ? $config['daily_count'] : 5), count($pool));

        $stmt = $dblj->prepare("SELECT * FROM player_nhiemvu_daily WHERE sid = ? AND reset_date = ?");
        $stmt->execute(array($sid, $today));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($rows)) {
            // Reset — assign random quests
            $keys = array_rand($pool, $count);
            if (!is_array($keys)) $keys = array($keys);
            $assigned = array();
            $insertStmt = $dblj->prepare("INSERT INTO player_nhiemvu_daily (sid, quest_id, reset_date, progress, completed) VALUES (?, ?, ?, 0, 0)");
            foreach ($keys as $k) {
                $q = $pool[$k];
                $insertStmt->execute(array($sid, $q['id'], $today));
                $q['progress'] = 0;
                $q['completed'] = 0;
                $q['claimed'] = 0;
                $assigned[] = $q;
            }
            return $assigned;
        }

        $result = array();
        foreach ($rows as $r) {
            $q = null;
            foreach ($pool as $p) {
                if ($p['id'] === $r['quest_id']) { $q = $p; break; }
            }
            if (!$q) continue;
            $q['progress']  = intval($r['progress']);
            $q['completed'] = intval($r['completed']);
            $q['claimed']   = isset($r['claimed']) ? intval($r['claimed']) : 0;
            $result[] = $q;
        }
        return $result;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_daily_update')) {
    function nhiemvu_daily_update($sid, $type, $amount, $dblj){
        $config = nhiemvu_daily_config();
        $pool = isset($config['daily_quest_pool']) ? $config['daily_quest_pool'] : array();
        $today = date('Y-m-d');
        $updated = false;

        foreach ($pool as $q) {
            if (!nhiemvu_type_match($q['type'], $type)) continue;
            $stmt = $dblj->prepare("SELECT progress, completed FROM player_nhiemvu_daily WHERE sid = ? AND quest_id = ? AND reset_date = ? LIMIT 1");
            $stmt->execute(array($sid, $q['id'], $today));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) continue;
            if (intval($row['completed'])) continue;

            $progress = intval($row['progress']) + intval($amount);
            $target = intval($q['target']);
            $completed = $progress >= $target ? 1 : 0;
            $stmt2 = $dblj->prepare("UPDATE player_nhiemvu_daily SET progress = ?, completed = ? WHERE sid = ? AND quest_id = ? AND reset_date = ?");
            $stmt2->execute(array($progress, $completed, $sid, $q['id'], $today));
            $updated = true;
        }
        return $updated;
    }
}

// ─── Weekly Quests ─────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_weekly_get')) {
    function nhiemvu_weekly_get($sid, $dblj){
        $week = nhiemvu_current_week_num();
        $config = nhiemvu_weekly_config();
        $quests = isset($config['weekly_quests']) ? $config['weekly_quests'] : array();
        $result = array();

        // Ensure all quests exist for this week
        $insertStmt = $dblj->prepare("INSERT IGNORE INTO player_nhiemvu_weekly (sid, quest_id, week_number, progress, completed) VALUES (?, ?, ?, 0, 0)");
        foreach ($quests as $q) {
            $insertStmt->execute(array($sid, $q['id'], $week));
        }

        $stmt = $dblj->prepare("SELECT * FROM player_nhiemvu_weekly WHERE sid = ? AND week_number = ?");
        $stmt->execute(array($sid, $week));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $rowMap = array();
        foreach ($rows as $r) $rowMap[$r['quest_id']] = $r;

        foreach ($quests as $q) {
            $r = isset($rowMap[$q['id']]) ? $rowMap[$q['id']] : null;
            if (!$r) continue;
            $q['progress']  = intval($r['progress']);
            $q['completed'] = intval($r['completed']);
            $q['claimed']   = isset($r['claimed']) ? intval($r['claimed']) : 0;
            $result[] = $q;
        }
        return $result;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_weekly_update')) {
    function nhiemvu_weekly_update($sid, $type, $amount, $dblj){
        $week = nhiemvu_current_week_num();
        $config = nhiemvu_weekly_config();
        $quests = isset($config['weekly_quests']) ? $config['weekly_quests'] : array();
        $updated = false;

        foreach ($quests as $q) {
            if (!nhiemvu_type_match($q['type'], $type)) continue;
            $stmt = $dblj->prepare("SELECT progress, completed FROM player_nhiemvu_weekly WHERE sid = ? AND quest_id = ? AND week_number = ? LIMIT 1");
            $stmt->execute(array($sid, $q['id'], $week));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || intval($row['completed'])) continue;

            $progress = intval($row['progress']) + intval($amount);
            $target = intval($q['target']);
            $completed = $progress >= $target ? 1 : 0;
            $stmt2 = $dblj->prepare("UPDATE player_nhiemvu_weekly SET progress = ?, completed = ? WHERE sid = ? AND quest_id = ? AND week_number = ?");
            $stmt2->execute(array($progress, $completed, $sid, $q['id'], $week));
            $updated = true;
        }
        return $updated;
    }
}

// ─── Monthly Quests ────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_monthly_get')) {
    function nhiemvu_monthly_get($sid, $dblj){
        $month = nhiemvu_current_month();
        $config = nhiemvu_monthly_config();
        $quests = isset($config['monthly_quests']) ? $config['monthly_quests'] : array();
        $result = array();

        $insertStmt = $dblj->prepare("INSERT IGNORE INTO player_nhiemvu_monthly (sid, quest_id, month, progress, completed) VALUES (?, ?, ?, 0, 0)");
        foreach ($quests as $q) {
            $insertStmt->execute(array($sid, $q['id'], $month));
        }

        $stmt = $dblj->prepare("SELECT * FROM player_nhiemvu_monthly WHERE sid = ? AND month = ?");
        $stmt->execute(array($sid, $month));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $rowMap = array();
        foreach ($rows as $r) $rowMap[$r['quest_id']] = $r;

        foreach ($quests as $q) {
            $r = isset($rowMap[$q['id']]) ? $rowMap[$q['id']] : null;
            if (!$r) continue;
            $q['progress']  = intval($r['progress']);
            $q['completed'] = intval($r['completed']);
            $q['claimed']   = isset($r['claimed']) ? intval($r['claimed']) : 0;
            $result[] = $q;
        }
        return $result;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_monthly_update')) {
    function nhiemvu_monthly_update($sid, $type, $amount, $dblj){
        $month = nhiemvu_current_month();
        $config = nhiemvu_monthly_config();
        $quests = isset($config['monthly_quests']) ? $config['monthly_quests'] : array();
        $updated = false;

        foreach ($quests as $q) {
            if (!nhiemvu_type_match($q['type'], $type)) continue;
            $stmt = $dblj->prepare("SELECT progress, completed FROM player_nhiemvu_monthly WHERE sid = ? AND quest_id = ? AND month = ? LIMIT 1");
            $stmt->execute(array($sid, $q['id'], $month));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || intval($row['completed'])) continue;

            $progress = intval($row['progress']) + intval($amount);
            $target = intval($q['target']);
            $completed = $progress >= $target ? 1 : 0;
            $stmt2 = $dblj->prepare("UPDATE player_nhiemvu_monthly SET progress = ?, completed = ? WHERE sid = ? AND quest_id = ? AND month = ?");
            $stmt2->execute(array($progress, $completed, $sid, $q['id'], $month));
            $updated = true;
        }
        return $updated;
    }
}

// ─── Achievements ──────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_achievement_get_all')) {
    function nhiemvu_achievement_get_all($sid, $dblj){
        $config = nhiemvu_achieve_config();
        $cats = isset($config['achievements']) ? $config['achievements'] : array();
        $result = array();

        $insertStmt = $dblj->prepare("INSERT IGNORE INTO player_nhiemvu_achievements (sid, achievement_id, progress, claimed) VALUES (?, ?, 0, 0)");
        foreach ($cats as $ck => $cat) {
            $catResult = array('cat' => $cat, 'tiers' => array());
            foreach ($cat['tiers'] as $tier) {
                $insertStmt->execute(array($sid, $tier['id']));
                $stmt = $dblj->prepare("SELECT progress, claimed FROM player_nhiemvu_achievements WHERE sid = ? AND achievement_id = ? LIMIT 1");
                $stmt->execute(array($sid, $tier['id']));
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $tier['progress'] = $row ? intval($row['progress']) : 0;
                $tier['claimed'] = $row ? intval($row['claimed']) : 0;
                $catResult['tiers'][] = $tier;
            }
            $result[] = $catResult;
        }
        return $result;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_achievement_update')) {
    function nhiemvu_achievement_update($sid, $category, $amount, $dblj){
        $config = nhiemvu_achieve_config();
        $cats = isset($config['achievements']) ? $config['achievements'] : array();
        if (!isset($cats[$category])) return false;

        foreach ($cats[$category]['tiers'] as $tier) {
            $stmt = $dblj->prepare("SELECT progress, claimed FROM player_nhiemvu_achievements WHERE sid = ? AND achievement_id = ? LIMIT 1");
            $stmt->execute(array($sid, $tier['id']));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || intval($row['claimed'])) continue;

            $progress = intval($row['progress']) + intval($amount);
            if ($progress > intval($tier['target'])) $progress = intval($tier['target']);
            $stmt2 = $dblj->prepare("UPDATE player_nhiemvu_achievements SET progress = ? WHERE sid = ? AND achievement_id = ?");
            $stmt2->execute(array($progress, $sid, $tier['id']));
        }
        return true;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_achievement_claim')) {
    function nhiemvu_achievement_claim($sid, $achievementId, $dblj){
        $config = nhiemvu_achieve_config();
        $cats = isset($config['achievements']) ? $config['achievements'] : array();
        $tier = null;
        foreach ($cats as $cat) {
            foreach ($cat['tiers'] as $t) {
                if ($t['id'] === $achievementId) { $tier = $t; break 2; }
            }
        }
        if (!$tier) return array('ok' => false, 'msg' => 'Không tìm thấy thành tựu.');

        $stmt = $dblj->prepare("SELECT progress, claimed FROM player_nhiemvu_achievements WHERE sid = ? AND achievement_id = ? LIMIT 1");
        $stmt->execute(array($sid, $achievementId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return array('ok' => false, 'msg' => 'Không tìm thấy dữ liệu.');
        if (intval($row['claimed'])) return array('ok' => false, 'msg' => 'Đã nhận thưởng rồi.');
        if (intval($row['progress']) < intval($tier['target'])) return array('ok' => false, 'msg' => 'Chưa đạt mục tiêu.');

        $exp = intval($tier['exp']);
        $lt = intval($tier['lt']);
        $mt = intval($tier['mt']);

        nhiemvu_grant_reward($sid, $exp, $lt, $mt, $dblj);

        $stmt = $dblj->prepare("UPDATE player_nhiemvu_achievements SET claimed = 1 WHERE sid = ? AND achievement_id = ?");
        $stmt->execute(array($sid, $achievementId));

        return array('ok' => true, 'msg' => "Nhận thưởng thành tựu \"{$tier['name']}\" thành công! +{$exp} EXP, +{$lt} LT, +{$mt} MT");
    }
}

// ─── Hidden Quests ─────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_hidden_get_all')) {
    function nhiemvu_hidden_get_all($sid, $dblj){
        $config = nhiemvu_hidden_config();
        $quests = isset($config['hidden_quests']) ? $config['hidden_quests'] : array();
        $result = array();

        $insertStmt = $dblj->prepare("INSERT IGNORE INTO player_nhiemvu_hidden (sid, quest_id, discovered, completed) VALUES (?, ?, 0, 0)");
        foreach ($quests as $q) {
            $insertStmt->execute(array($sid, $q['id']));
            $stmt = $dblj->prepare("SELECT discovered, completed, progress FROM player_nhiemvu_hidden WHERE sid = ? AND quest_id = ? LIMIT 1");
            $stmt->execute(array($sid, $q['id']));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $q['discovered'] = $row ? intval($row['discovered']) : 0;
            $q['completed'] = $row ? intval($row['completed']) : 0;
            $q['progress'] = $row ? intval($row['progress']) : 0;
            $result[] = $q;
        }
        return $result;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_hidden_discover')) {
    function nhiemvu_hidden_discover($sid, $trigger, $dblj){
        $config = nhiemvu_hidden_config();
        $quests = isset($config['hidden_quests']) ? $config['hidden_quests'] : array();
        foreach ($quests as $q) {
            if ($q['trigger'] !== $trigger) continue;
            $stmt = $dblj->prepare("SELECT discovered, completed FROM player_nhiemvu_hidden WHERE sid = ? AND quest_id = ? LIMIT 1");
            $stmt->execute(array($sid, $q['id']));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || intval($row['discovered'])) continue;

            $stmt = $dblj->prepare("UPDATE player_nhiemvu_hidden SET discovered = 1 WHERE sid = ? AND quest_id = ?");
            $stmt->execute(array($sid, $q['id']));
            return array('ok' => true, 'msg' => "Phát hiện nhiệm vụ ẩn: {$q['name']}!", 'quest' => $q);
        }
        return array('ok' => false, 'msg' => '');
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_hidden_complete')) {
    function nhiemvu_hidden_complete($sid, $questId, $dblj){
        $config = nhiemvu_hidden_config();
        $quests = isset($config['hidden_quests']) ? $config['hidden_quests'] : array();
        $q = null;
        foreach ($quests as $hq) {
            if ($hq['id'] === $questId) { $q = $hq; break; }
        }
        if (!$q) return array('ok' => false, 'msg' => 'Không tìm thấy nhiệm vụ ẩn.');

        $stmt = $dblj->prepare("SELECT completed FROM player_nhiemvu_hidden WHERE sid = ? AND quest_id = ? LIMIT 1");
        $stmt->execute(array($sid, $questId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || intval($row['completed'])) return array('ok' => false, 'msg' => 'Đã hoàn thành rồi.');

        $exp = intval($q['exp']);
        $lt = intval($q['lt']);
        $mt = intval($q['mt']);

        nhiemvu_grant_reward($sid, $exp, $lt, $mt, $dblj);

        $stmt = $dblj->prepare("UPDATE player_nhiemvu_hidden SET completed = 1 WHERE sid = ? AND quest_id = ?");
        $stmt->execute(array($sid, $questId));

        return array('ok' => true, 'msg' => "Hoàn thành nhiệm vụ ẩn \"{$q['name']}\"! +{$exp} EXP, +{$lt} LT, +{$mt} MT");
    }
}

// ─── Hidden quest triggers ─────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_hidden_event')) {
    // Goi tu nhiemvu_update_all. Moi trigger thuoc 1 trong 3 dang:
    //   instant : dieu kien dung ngay -> phat hien + hoan thanh + thuong
    //   streak  : cong tien do, reset khi su kien that bai (context.reset)
    //   window  : cong tien do trong so gio tinh tu timer (kill_speed)
    function nhiemvu_hidden_event($sid, $trigger, $dblj, $context = array()){
        $config = nhiemvu_hidden_config();
        $quests = isset($config['hidden_quests']) ? $config['hidden_quests'] : array();

        foreach ($quests as $q) {
            if ($q['trigger'] !== $trigger) continue;

            $target = max(1, intval($q['target']));
            $dblj->prepare("INSERT IGNORE INTO player_nhiemvu_hidden (sid, quest_id, discovered, completed, progress) VALUES (?, ?, 0, 0, 0)")
                 ->execute(array($sid, $q['id']));
            $stmt = $dblj->prepare("SELECT completed, progress, timer FROM player_nhiemvu_hidden WHERE sid = ? AND quest_id = ? LIMIT 1");
            $stmt->execute(array($sid, $q['id']));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row || intval($row['completed'])) return false;

            $progress = intval($row['progress']);
            $instant = false;
            $timerVal = $row['timer'];

            if ($trigger === 'alchemy_batch') {
                $instant = isset($context['amount']) && intval($context['amount']) >= $target;
            } elseif ($trigger === 'boss_low_hp') {
                $hpPct = isset($context['player_hp_percent']) ? floatval($context['player_hp_percent']) : -1;
                $instant = ($hpPct > 0 && $hpPct < $target);
            } elseif ($trigger === 'collect_all_elements') {
                $stmt2 = $dblj->prepare("SELECT COUNT(*) FROM playerdaoju WHERE sid = ? AND djid IN (32,33,34,35,36) AND djsum > 0");
                $stmt2->execute(array($sid));
                $instant = intval($stmt2->fetchColumn()) >= $target;
            } elseif ($trigger === 'upgrade_success_15') {
                $need = isset($q['need_qianghua']) ? intval($q['need_qianghua']) : 15;
                $instant = isset($context['qianghua']) && intval($context['qianghua']) >= $need;
            } elseif ($trigger === 'alchemy_low_rate') {
                $maxRate = isset($q['rate_max']) ? floatval($q['rate_max']) : 30;
                $rate = isset($context['success_rate']) ? floatval($context['success_rate']) : -1;
                $instant = ($rate > 0 && $rate <= $maxRate);
            } elseif ($trigger === 'craft_streak' || $trigger === 'pvp_win_streak') {
                $progress = !empty($context['reset']) ? 0 : $progress + 1;
                $instant = $progress >= $target;
            } elseif ($trigger === 'kill_speed') {
                $window = isset($q['window_minutes']) ? max(1, intval($q['window_minutes'])) : 10;
                $now = time();
                $start = $row['timer'] ? strtotime($row['timer']) : 0;
                if (!$start || ($now - $start) > $window * 60) {
                    $progress = 1;
                    $timerVal = date('Y-m-d H:i:s', $now);
                } else {
                    $progress++;
                }
                $instant = $progress >= $target;
            }

            $dblj->prepare("UPDATE player_nhiemvu_hidden SET progress = ?, timer = ? WHERE sid = ? AND quest_id = ?")
                 ->execute(array($progress, $timerVal, $sid, $q['id']));

            if ($instant) {
                nhiemvu_hidden_discover($sid, $trigger, $dblj);
                nhiemvu_hidden_complete($sid, $q['id'], $dblj);
            } elseif ($progress > 0) {
                nhiemvu_hidden_discover($sid, $trigger, $dblj);
            }
            return true;
        }
        return false;
    }
}

// ─── Main Quests ───────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_main_get_all')) {
    function nhiemvu_main_get_all($sid, $dblj){
        $config = nhiemvu_main_config();
        $chapters = isset($config['chapters']) ? $config['chapters'] : array();
        $result = array();

        $insertStmt = $dblj->prepare("INSERT IGNORE INTO player_nhiemvu_main (sid, quest_id, status) VALUES (?, ?, 0)");
        foreach ($chapters as $ch) {
            foreach ($ch['quests'] as $q) {
                $insertStmt->execute(array($sid, $q['id']));
            }
        }

        $stmt = $dblj->prepare("SELECT quest_id, status, progress FROM player_nhiemvu_main WHERE sid = ?");
        $stmt->execute(array($sid));
        $statusMap = array();
        $progressMap = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $statusMap[$row['quest_id']] = intval($row['status']);
            $progressMap[$row['quest_id']] = intval($row['progress']);
        }

        foreach ($chapters as $ch) {
            $chData = $ch;
            $chData['quests'] = array();
            foreach ($ch['quests'] as $q) {
                $q['status'] = isset($statusMap[$q['id']]) ? $statusMap[$q['id']] : 0;
                $q['progress'] = isset($progressMap[$q['id']]) ? $progressMap[$q['id']] : 0;
                $q['target'] = nhiemvu_main_target($q);
                $chData['quests'][] = $q;
            }
            $result[] = $chData;
        }
        return $result;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_main_target')) {
    function nhiemvu_main_target($quest){
        if (isset($quest['target_count'])) return max(1, intval($quest['target_count']));
        if (isset($quest['item_count']))  return max(1, intval($quest['item_count']));
        if (isset($quest['count']))       return max(1, intval($quest['count']));
        if (isset($quest['target_plus'])) return max(1, intval($quest['target_plus']));
        if (isset($quest['items'])) {
            $n = 0;
            foreach ($quest['items'] as $it) $n += max(1, intval($it['count']));
            return max(1, $n);
        }
        return 1; // one-shot quests (dialogue, breakthrough)
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_main_goal_text')) {
    function nhiemvu_main_goal_text($quest){
        $q = $quest;
        $slotNames = array(1 => 'Vũ khí', 2 => 'Mũ', 3 => 'Giáp', 4 => 'Giày',
                           5 => 'Găng', 7 => 'Dây chuyền', 8 => 'Nhẫn');
        switch ($q['type']) {
            case 'dialogue':
                return 'Mở bảng Nhiệm Vụ để nghe chỉ dẫn';
            case 'kill':
                $n   = isset($q['target_count']) ? intval($q['target_count']) : 1;
                $min = isset($q['target_monster_min']) ? intval($q['target_monster_min']) : 0;
                $max = isset($q['target_monster_max']) ? intval($q['target_monster_max']) : 999;
                return "Diệt $n quái cấp $min - $max";
            case 'kill_boss':
                return 'Hạ ' . (isset($q['target_count']) ? intval($q['target_count']) : 1) . ' BOSS';
            case 'collect_item':
                $id = intval($q['item_id']);
                $cfg = daoju_config_get($id);
                $name = isset($cfg['djname']) ? $cfg['djname'] : "vật phẩm $id";
                return 'Thu ' . intval($q['item_count']) . ' ' . $name;
            case 'collect_multiple':
                $parts = array();
                foreach ($q['items'] as $it) {
                    $id = intval($it['id']);
                    $cfg = daoju_config_get($id);
                    $parts[] = intval($it['count']) . ' ' . (isset($cfg['djname']) ? $cfg['djname'] : "vật phẩm $id");
                }
                return 'Thu ' . implode(' + ', $parts);
            case 'alchemy_count':
                return 'Luyện đan ' . intval($q['count']) . ' lần';
            case 'craft_count':
                return 'Chế tạo ' . intval($q['count']) . ' sản phẩm';
            case 'craft_rarity':
                $need = nhiemvu_rarity_min($q['rarity']);
                return 'Chế tạo trang bị phẩm chất ' . zhuangbei_rarity_name($need) . ' trở lên';
            case 'upgrade_slot':
                $slotId = nhiemvu_slot_id(isset($q['slot']) ? $q['slot'] : 'any');
                $slotName = $slotId <= 0 ? 'bất kỳ trang bị nào' : (isset($slotNames[$slotId]) ? $slotNames[$slotId] : "lỗ $slotId");
                return 'Cường hóa ' . $slotName . ' tới +' . intval($q['target_plus']);
            case 'breakthrough':
                return 'Đột phá đạt cấp ' . intval($q['target_level']);
        }
        return '';
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_main_update')) {
    function nhiemvu_main_update($sid, $type, $dblj, $context = array(), $amount = 1){
        $config = nhiemvu_main_config();
        $chapters = isset($config['chapters']) ? $config['chapters'] : array();
        if (empty($chapters)) return array('ok' => false, 'msg' => '');

        $stmt = $dblj->prepare("SELECT quest_id, status, progress FROM player_nhiemvu_main WHERE sid = ?");
        $stmt->execute(array($sid));
        $statusMap = array();
        $progressMap = array();
        $exists = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $statusMap[$row['quest_id']] = intval($row['status']);
            $progressMap[$row['quest_id']] = intval($row['progress']);
            $exists[$row['quest_id']] = true;
        }

        foreach ($chapters as $ch) {
            foreach ($ch['quests'] as $q) {
                $qid = $q['id'];
                $curStatus = isset($statusMap[$qid]) ? $statusMap[$qid] : 0;
                if ($curStatus === 2) continue; // already finished
                if (!nhiemvu_main_match_type($q, $type, $context)) continue;
                // Only the current quest of the chain may advance
                if ($curStatus === 0 && !nhiemvu_main_check_prereq($sid, $ch, $q, $statusMap, $dblj)) continue;

                $target = nhiemvu_main_target($q);
                $have = isset($progressMap[$qid]) ? $progressMap[$qid] : 0;

                if ($type === 'breakthrough') {
                    $lv = isset($context['level']) ? intval($context['level']) : 0;
                    if (!isset($q['target_level']) || $lv < intval($q['target_level'])) continue;
                    $have = $target;
                } elseif ($type === 'upgrade_slot') {
                    $plus = isset($context['qianghua']) ? intval($context['qianghua']) : 0;
                    if ($plus < $target) continue;
                    $have = $target;
                } else {
                    $have += max(1, intval($amount));
                    if ($have > $target) $have = $target;
                }

                if ($have >= $target) {
                    $up = $dblj->prepare("INSERT INTO player_nhiemvu_main (sid, quest_id, status, progress) VALUES (?, ?, 2, ?)
                                          ON DUPLICATE KEY UPDATE status = 2, progress = ?");
                    $up->execute(array($sid, $qid, $target, $target));
                    $statusMap[$qid] = 2;

                    $exp = intval(isset($q['exp']) ? $q['exp'] : 0);
                    $lt  = intval(isset($q['lt']) ? $q['lt'] : 0);
                    $mt  = intval(isset($q['mt']) ? $q['mt'] : 0);
                    nhiemvu_grant_reward($sid, $exp, $lt, $mt, $dblj);

                    $parts = array();
                    if ($exp > 0) $parts[] = "+$exp EXP";
                    if ($lt > 0)  $parts[] = "+$lt LT";
                    if ($mt > 0)  $parts[] = "+$mt MT";
                    $rewardTxt = $parts ? ' — ' . implode(', ', $parts) : '';

                    return array('ok' => true, 'quest' => $q, 'msg' => "Hoàn thành nhiệm vụ chính tuyến: {$q['name']}{$rewardTxt}");
                }

                $up = $dblj->prepare("INSERT INTO player_nhiemvu_main (sid, quest_id, status, progress) VALUES (?, ?, 1, ?)
                                      ON DUPLICATE KEY UPDATE status = 1, progress = ?");
                $up->execute(array($sid, $qid, $have, $have));
                return array('ok' => false, 'msg' => '', 'quest' => $q, 'progress' => $have, 'target' => $target);
            }
        }
        return array('ok' => false, 'msg' => '');
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_main_check_prereq')) {
    function nhiemvu_main_check_prereq($sid, $chapter, $quest, &$statusMap, $dblj){
        // First quest in chapter: check all quests in previous chapter are done
        if ($quest === $chapter['quests'][0]) {
            // Find previous chapter
            $config = nhiemvu_main_config();
            $chapters = isset($config['chapters']) ? $config['chapters'] : array();
            $prevIdx = -1;
            foreach ($chapters as $idx => $ch) {
                if ($ch['id'] === $chapter['id']) { $prevIdx = $idx - 1; break; }
            }
            if ($prevIdx >= 0 && isset($chapters[$prevIdx])) {
                foreach ($chapters[$prevIdx]['quests'] as $pq) {
                    $s = isset($statusMap[$pq['id']]) ? $statusMap[$pq['id']] : 0;
                    if ($s !== 2) return false;
                }
            }
        } else {
            // Depends on previous quest in same chapter
            $prevQid = '';
            foreach ($chapter['quests'] as $i => $cq) {
                if ($cq['id'] === $quest['id']) {
                    if ($i > 0) {
                        $prevQid = $chapter['quests'][$i - 1]['id'];
                    }
                    break;
                }
            }
            if ($prevQid) {
                $s = isset($statusMap[$prevQid]) ? $statusMap[$prevQid] : 0;
                if ($s !== 2) return false;
            }
        }
        return true;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_main_match_type')) {
    function nhiemvu_main_match_type($quest, $type, $context){
        if ($quest['type'] !== $type) return false;

        switch ($type) {
            case 'kill':
                $min = isset($quest['target_monster_min']) ? intval($quest['target_monster_min']) : 0;
                $max = isset($quest['target_monster_max']) ? intval($quest['target_monster_max']) : 999999;
                // Config ranges are monster LEVELS (chapter 1 asks for lv1-5, chapter 3 for lv25-40)
                $lv = isset($context['monster_level']) ? intval($context['monster_level'])
                     : (isset($context['monster_id']) ? intval($context['monster_id']) : 0);
                if ($lv >= $min && $lv <= $max) return true;
                return false;
            case 'collect_item':
                $itemId = isset($quest['item_id']) ? intval($quest['item_id']) : 0;
                $gotId = isset($context['item_id']) ? intval($context['item_id']) : 0;
                if ($itemId === $gotId) return true;
                return false;
            case 'collect_multiple':
                if (!isset($quest['items']) || !isset($context['item_id'])) return false;
                foreach ($quest['items'] as $it) {
                    if (intval($it['id']) === intval($context['item_id'])) return true;
                }
                return false;
            case 'breakthrough':
                $targetLevel = isset($quest['target_level']) ? intval($quest['target_level']) : 0;
                $currentLevel = isset($context['level']) ? intval($context['level']) : 0;
                if ($currentLevel >= $targetLevel) return true;
                return false;
            case 'craft_rarity':
                $need = nhiemvu_rarity_min($quest['rarity']);
                $got = isset($context['rarity']) ? nhiemvu_rarity_min($context['rarity']) : 0;
                return $need > 0 && $got >= $need;
            case 'upgrade_slot':
                $want = nhiemvu_slot_id(isset($quest['slot']) ? $quest['slot'] : 'any');
                if ($want <= 0) return true; // 'any' or unnamed slot
                $got = isset($context['slot']) ? nhiemvu_slot_id($context['slot']) : -1;
                return $got === $want;
            case 'alchemy_count':
            case 'craft_count':
            case 'kill_boss':
                return true; // Count-based
            case 'dialogue':
                return true;
            default:
                return true;
        }
    }
}

// ─── Universal update wrapper ──────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_complete_all_sync')) {
    // 'complete_all' quests track how many sibling quests of the same period are done.
    function nhiemvu_complete_all_sync($sid, $scope, $dblj){
        if ($scope === 'weekly') {
            $config = nhiemvu_weekly_config(); $poolKey = 'weekly_quests';
            $table = 'player_nhiemvu_weekly'; $periodCol = 'week_number';
            $periodVal = nhiemvu_current_week_num();
        } elseif ($scope === 'monthly') {
            $config = nhiemvu_monthly_config(); $poolKey = 'monthly_quests';
            $table = 'player_nhiemvu_monthly'; $periodCol = 'month';
            $periodVal = nhiemvu_current_month();
        } else {
            return false;
        }

        $quests = isset($config[$poolKey]) ? $config[$poolKey] : array();
        $targets = array();
        $siblings = array();
        foreach ($quests as $q) {
            if ($q['type'] === 'complete_all') $targets[] = $q;
            else $siblings[] = $q['id'];
        }
        if (empty($targets) || empty($siblings)) return false;

        $in = implode(',', array_fill(0, count($siblings), '?'));
        $stmt = $dblj->prepare("SELECT quest_id, completed FROM $table WHERE sid = ? AND $periodCol = ? AND quest_id IN ($in)");
        $args = array($sid, $periodVal);
        foreach ($siblings as $id) $args[] = $id;
        $stmt->execute($args);

        $done = 0;
        while ($r = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (intval($r['completed'])) $done++;
        }

        foreach ($targets as $q) {
            $target = max(1, intval($q['target']));
            $progress = min($done, $target);
            $completed = $done >= $target ? 1 : 0;
            $up = $dblj->prepare("UPDATE $table SET progress = ?, completed = GREATEST(completed, ?)
                                  WHERE sid = ? AND quest_id = ? AND $periodCol = ? AND claimed = 0");
            $up->execute(array($progress, $completed, $sid, $q['id'], $periodVal));
        }
        return true;
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_update_all')) {
    function nhiemvu_update_all($sid, $type, $amount, $dblj, $context = array()){
        $weeklyTouched   = nhiemvu_weekly_update($sid, $type, $amount, $dblj);
        nhiemvu_daily_update($sid, $type, $amount, $dblj);
        $monthlyTouched  = nhiemvu_monthly_update($sid, $type, $amount, $dblj);
        if ($weeklyTouched)  nhiemvu_complete_all_sync($sid, 'weekly', $dblj);
        if ($monthlyTouched) nhiemvu_complete_all_sync($sid, 'monthly', $dblj);

        // Map achievement categories
        $achieveMap = array(
            'kill'   => 'monster_slayer',
            'alchemy'=> 'alchemy_master',
            'craft'  => 'blacksmith',
            'collect'=> 'collector',
            'boss'   => 'boss_hunter',
            'pvp'    => 'pvp_warrior',
            'cultivate'=> 'cultivation_expert',
        );
        if (isset($achieveMap[$type])) {
            nhiemvu_achievement_update($sid, $achieveMap[$type], $amount, $dblj);
        }

        // Hidden quests: moi su kien co the bat duoc 1 or 2 trigger an.
        $hiddenMap = array(
            'alchemy'    => array('alchemy_batch', 'alchemy_low_rate'),
            'craft'      => array('craft_streak'),
            'craft_fail' => array('craft_streak'),
            'boss'       => array('boss_low_hp'),
            'collect'    => array('collect_all_elements'),
            'upgrade'    => array('upgrade_success_15'),
            'kill'       => array('kill_speed'),
            'pvp'        => array('pvp_win_streak'),
            'pvp_lose'   => array('pvp_win_streak'),
        );
        if (isset($hiddenMap[$type])) {
            $hctx = $context;
            $hctx['amount'] = $amount;
            if ($type === 'craft_fail' || $type === 'pvp_lose') $hctx['reset'] = true;
            foreach ($hiddenMap[$type] as $trig) {
                nhiemvu_hidden_event($sid, $trig, $dblj, $hctx);
            }
        }

        // Main-line quests name their types differently from daily/weekly ones.
        $mainAlias = array(
            'alchemy' => array('alchemy_count'),
            'craft'    => array('craft_count', 'craft_rarity'),
            'boss'     => array('kill_boss'),
            'upgrade'  => array('upgrade_slot'),
            'collect'  => array('collect_item', 'collect_multiple'),
        );
        $mainTypes = isset($mainAlias[$type]) ? $mainAlias[$type] : array($type);
        foreach ($mainTypes as $mt) {
            $result = nhiemvu_main_update($sid, $mt, $dblj, $context, $amount);
            if ($result['ok']) return $result;
        }
        return array('ok' => false, 'msg' => '');
    }
}

// ─── Claim helpers ─────────────────────────────────────────────

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_claim_daily_bonus')) {
    function nhiemvu_claim_daily_bonus($sid, $dblj){
        $daily = nhiemvu_daily_get($sid, $dblj);
        $config = nhiemvu_daily_config();
        $allDone = true;
        foreach ($daily as $q) {
            if (!$q['completed']) { $allDone = false; break; }
        }
        if (!$allDone) return array('ok' => false, 'msg' => 'Chưa hoàn thành tất cả nhiệm vụ ngày.');

        $today = date('Y-m-d');
        $stmt = $dblj->prepare("SELECT 1 FROM player_nhiemvu_daily WHERE sid = ? AND reset_date = ? AND quest_id = 'daily_bonus'");
        $stmt->execute(array($sid, $today));
        if ($stmt->fetch()) return array('ok' => false, 'msg' => 'Đã nhận thưởng ngày rồi.');

        $bonus = isset($config['daily_bonus']) ? $config['daily_bonus'] : array('exp' => 1000, 'lt' => 500, 'mt' => 5);
        $exp = intval($bonus['exp']);
        $lt = intval($bonus['lt']);
        $mt = intval($bonus['mt']);

        nhiemvu_grant_reward($sid, $exp, $lt, $mt, $dblj);

        $stmt = $dblj->prepare("INSERT INTO player_nhiemvu_daily (sid, quest_id, reset_date, progress, completed) VALUES (?, 'daily_bonus', ?, 1, 1)");
        $stmt->execute(array($sid, $today));

        return array('ok' => true, 'msg' => "Nhận thưởng ngày thành công! +{$exp} EXP, +{$lt} LT, +{$mt} MT");
    }
}

if (!function_exists(__NAMESPACE__ . '\\nhiemvu_claim_quest')) {
    function nhiemvu_claim_quest($sid, $table, $questId, $dblj){
        $configMap = array(
            'player_nhiemvu_daily'   => __NAMESPACE__ . '\\nhiemvu_daily_config',
            'player_nhiemvu_weekly'  => __NAMESPACE__ . '\\nhiemvu_weekly_config',
            'player_nhiemvu_monthly' => __NAMESPACE__ . '\\nhiemvu_monthly_config',
        );

        if (!isset($configMap[$table])) return array('ok' => false, 'msg' => 'Sai loại nhiệm vụ.');

        switch ($table) {
            case 'player_nhiemvu_daily':
                $identifier = 'reset_date';
                $idVal = date('Y-m-d');
                break;
            case 'player_nhiemvu_weekly':
                $identifier = 'week_number';
                $idVal = nhiemvu_current_week_num();
                break;
            case 'player_nhiemvu_monthly':
                $identifier = 'month';
                $idVal = nhiemvu_current_month();
                break;
            default:
                return array('ok' => false, 'msg' => 'Sai loại.');
        }

        $stmt = $dblj->prepare("SELECT progress, completed, claimed FROM $table WHERE sid = ? AND quest_id = ? AND $identifier = ? LIMIT 1");
        $stmt->execute(array($sid, $questId, $idVal));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return array('ok' => false, 'msg' => 'Không tìm thấy nhiệm vụ.');
        if (intval($row['claimed'])) return array('ok' => false, 'msg' => 'Đã nhận thưởng rồi.');

        // Find quest config
        $configFn = $configMap[$table];
        $config = $configFn();
        $key = ($table === 'player_nhiemvu_daily') ? 'daily_quest_pool' : (($table === 'player_nhiemvu_weekly') ? 'weekly_quests' : 'monthly_quests');
        $pool = isset($config[$key]) ? $config[$key] : array();
        $qData = null;
        foreach ($pool as $q) {
            if ($q['id'] === $questId) { $qData = $q; break; }
        }
        if (!$qData) return array('ok' => false, 'msg' => 'Không tìm thấy cấu hình nhiệm vụ.');

        if (!intval($row['completed']) && intval($row['progress']) < intval($qData['target'])) return array('ok' => false, 'msg' => 'Chưa hoàn thành nhiệm vụ.');

        $exp = intval(isset($qData['exp']) ? $qData['exp'] : 0);
        $lt = intval(isset($qData['lt']) ? $qData['lt'] : 0);
        $mt = intval(isset($qData['mt']) ? $qData['mt'] : 0);

        nhiemvu_grant_reward($sid, $exp, $lt, $mt, $dblj);

        $stmt = $dblj->prepare("UPDATE $table SET completed = 1, claimed = 1 WHERE sid = ? AND quest_id = ? AND $identifier = ?");
        $stmt->execute(array($sid, $questId, $idVal));

        return array('ok' => true, 'msg' => "Nhận thưởng \"{$qData['name']}\"! +{$exp} EXP, +{$lt} LT, +{$mt} MT");
    }
}
