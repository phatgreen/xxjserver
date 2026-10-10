<?php
namespace player;

/**
 * linhcan_skill.php - Kỹ năng Linh Căn
 */

if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_config_path')) {
    function linhcan_skill_config_path(){
        $paths = array(
            __DIR__ . '/../config/linhcan_skill_config.json',
            __DIR__ . '/linhcan_skill_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_config_all')) {
    function linhcan_skill_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $path = linhcan_skill_config_path();
        $json = @file_get_contents($path);
        $cache = $json ? json_decode($json, true) : array('skills'=>array());
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_get')) {
    function linhcan_skill_get($skillId){
        $config = linhcan_skill_config_all();
        $skills = isset($config['skills']) ? $config['skills'] : array();
        return isset($skills[$skillId]) ? $skills[$skillId] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_for_element')) {
    function linhcan_skill_for_element($element){
        $config = linhcan_skill_config_all();
        $map = isset($config['element_to_skill']) ? $config['element_to_skill'] : array();
        $skillId = isset($map[$element]) ? $map[$element] : false;
        return $skillId ? linhcan_skill_get($skillId) : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_can_use')) {
    /** $lcStage = bac cua HANH CHU DAO (khong phai ulv — he moi mo khoa theo Địa Căn). */
    function linhcan_skill_can_use($player, $skill, $lcStage){
        if (!$skill) return array('ok' => false, 'msg' => 'Không có kỹ năng.');
        $unlockStage = intval(isset($skill['unlock_stage']) ? $skill['unlock_stage'] : 2);
        if (intval($lcStage) < $unlockStage) {
            return array('ok' => false, 'msg' => 'Hành chủ đạo chưa đạt Địa Căn (bậc ' . $unlockStage . ').');
        }
        $mpCostPercent = floatval(isset($skill['mp_cost_percent']) ? $skill['mp_cost_percent'] : 10);
        $mpCost = max(1, intval(intval($player->umaxhp) * $mpCostPercent / 100));
        if (intval($player->uhp) <= $mpCost) {
            return array('ok' => false, 'msg' => 'Khí huyết không đủ để thi triển (còn trên ' . $mpCost . ' HP).');
        }
        return array('ok' => true, 'mp_cost' => $mpCost);
    }
}

/** Skill hien dung duoc = skill cua hanh chu dao, khi hanh do >= Dia Can. */
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_for_stage')) {
    function linhcan_skill_for_stage($sid, $dblj){
        $nodes = linhcan_node_rows($sid, $dblj);
        $dom = linhcan_dominant($nodes);
        if ($dom === '') return false;
        if (intval($nodes[$dom]['stage']) < 2) return false;
        return linhcan_skill_for_element($dom);
    }
}

/**
 * Dong goc cac mod 1 luot chi (request-scoped). `duration_turns` chi con y nghia
 * doivoi heal (dot don lap + heal_percent); buff/debuff = dung luot bam nay.
 * `aoe` bo qua vi PVE/boss hien chi 1 muc tieu.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_combat_mods')) {
    function linhcan_skill_combat_mods($skill){
        $m = array('atk_mult' => 1.0, 'crit_add' => 0, 'lifesteal_add' => 0,
                   'def_pct' => 0, 'heal_pct' => 0, 'ignore_def_pct' => 0,
                   'name' => '', 'icon' => '');
        if (!$skill) return $m;
        $m['name'] = isset($skill['name']) ? $skill['name'] : '';
        $m['icon'] = isset($skill['icon']) ? $skill['icon'] : '✨';
        $type = isset($skill['type']) ? $skill['type'] : '';
        if ($type === 'damage' && isset($skill['multiplier'])) {
            $m['atk_mult'] = floatval($skill['multiplier']);
        }
        if ($type === 'heal' && isset($skill['heal_percent'])) {
            $d = isset($skill['duration_turns']) ? max(1, intval($skill['duration_turns'])) : 1;
            $m['heal_pct'] = floatval($skill['heal_percent']) * $d;
        }
        if ($type === 'buff') {
            if (isset($skill['lifesteal_bonus'])) $m['lifesteal_add'] = intval($skill['lifesteal_bonus']);
            if (isset($skill['crit_bonus'])) $m['crit_add'] = intval($skill['crit_bonus']);
            if (isset($skill['def_bonus'])) $m['def_pct'] = intval($skill['def_bonus']);
        }
        if ($type === 'debuff' && isset($skill['ignore_def_percent'])) {
            $m['ignore_def_pct'] = floatval($skill['ignore_def_percent']);
        }
        return $m;
    }
}

/**
 * Áp dụng hiệu ứng skill lên player khi dùng trong combat.
 * Trả về array các buff/debuff đã áp dụng.
 * KHONG con dung trong combat (Task 8 dung linhcan_skill_combat_mods — mod 1 luot,
 * khong ghi de chi so lang ghien). Giu lai de lich su / test cu.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_apply')) {
    function linhcan_skill_apply($player, $skill){
        if (!$skill) return array();

        $applied = array();
        $type = isset($skill['type']) ? $skill['type'] : '';
        $duration = isset($skill['duration_turns']) ? intval($skill['duration_turns']) : 1;

        switch ($type) {
            case 'damage':
                // Skill gây damage - xử lý ở caller
                $applied[] = array(
                    'type' => 'damage',
                    'multiplier' => floatval(isset($skill['multiplier']) ? $skill['multiplier'] : 1.5),
                    'aoe' => isset($skill['aoe']) ? $skill['aoe'] : false
                );
                break;

            case 'heal':
                $healPercent = floatval(isset($skill['heal_percent']) ? $skill['heal_percent'] : 8);
                $healAmount = max(1, intval(intval($player->umaxhp) * $healPercent / 100));
                $player->uhp = min(intval($player->umaxhp), intval($player->uhp) + $healAmount);
                $applied[] = array(
                    'type' => 'heal',
                    'amount' => $healAmount,
                    'duration' => $duration
                );
                break;

            case 'buff':
                if (isset($skill['lifesteal_bonus'])) {
                    $player->uxx += intval($skill['lifesteal_bonus']);
                    $applied[] = array('type' => 'lifesteal_buff', 'value' => intval($skill['lifesteal_bonus']), 'duration' => $duration);
                }
                if (isset($skill['crit_bonus'])) {
                    $player->ubj += intval($skill['crit_bonus']);
                    $applied[] = array('type' => 'crit_buff', 'value' => intval($skill['crit_bonus']), 'duration' => $duration);
                }
                if (isset($skill['def_bonus'])) {
                    $player->ufy += intval(intval($player->ufy) * intval($skill['def_bonus']) / 100);
                    $applied[] = array('type' => 'def_buff', 'value' => intval($skill['def_bonus']), 'duration' => $duration);
                }
                break;

            case 'debuff':
                // Debuff áp dụng lên mục tiêu, xử lý ở caller
                if (isset($skill['ignore_def_percent'])) {
                    $applied[] = array(
                        'type' => 'ignore_def',
                        'percent' => floatval($skill['ignore_def_percent']),
                        'duration' => $duration
                    );
                }
                break;
        }

        return $applied;
    }
}

/**
 * Kiểm tra cooldown skill.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_check_cooldown')) {
    function linhcan_skill_check_cooldown($sid, $skillId, $dblj){
        $stmt = $dblj->prepare("SELECT last_used FROM player_linhcan_skill WHERE sid = ? AND skill_id = ? LIMIT 1");
        $stmt->execute(array($sid, $skillId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return true; // Chưa dùng lần nào

        $lastUsed = strtotime($row['last_used']);
        $config = linhcan_skill_get($skillId);
        $cooldownMinutes = isset($config['cooldown_minutes']) ? intval($config['cooldown_minutes']) : 5;
        $cooldownSeconds = $cooldownMinutes * 60;

        return (time() - $lastUsed) >= $cooldownSeconds;
    }
}

/**
 * Ghi nhận thời gian dùng skill.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_record_use')) {
    function linhcan_skill_record_use($sid, $skillId, $dblj){
        $now = date('Y-m-d H:i:s');
        $stmt = $dblj->prepare("INSERT INTO player_linhcan_skill (sid, skill_id, last_used) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE last_used = ?");
        $stmt->execute(array($sid, $skillId, $now, $now));
    }
}

/**
 * Lấy thông tin cooldown còn lại (text).
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_skill_cooldown_text')) {
    function linhcan_skill_cooldown_text($sid, $skillId, $dblj){
        $stmt = $dblj->prepare("SELECT last_used FROM player_linhcan_skill WHERE sid = ? AND skill_id = ? LIMIT 1");
        $stmt->execute(array($sid, $skillId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return '';

        $lastUsed = strtotime($row['last_used']);
        $config = linhcan_skill_get($skillId);
        $cooldownMinutes = isset($config['cooldown_minutes']) ? intval($config['cooldown_minutes']) : 5;
        $cooldownSeconds = $cooldownMinutes * 60;
        $remaining = $cooldownSeconds - (time() - $lastUsed);

        if ($remaining <= 0) return '';

        $mins = intval($remaining / 60);
        $secs = $remaining % 60;
        return $mins > 0 ? "{$mins}m{$secs}s" : "{$secs}s";
    }
}
?>
