<?php
namespace player;

/**
 * linhcan_boss.php - Boss Linh Căn
 */

if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_config_path')) {
    function linhcan_boss_config_path(){
        $paths = array(
            __DIR__ . '/../config/linhcan_boss_config.json',
            __DIR__ . '/linhcan_boss_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_config_all')) {
    function linhcan_boss_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $path = linhcan_boss_config_path();
        $json = @file_get_contents($path);
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

/**
 * Boss thế giới chọn hành ngẫu nhiên trong 5 hành config.
 * KHÔNG đọc game1.linhcan_id nữa (cột đã ngừng cập nhật — lỗ hỏng 1.4).
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_world_element')) {
    function linhcan_boss_world_element($dblj){
        $cfg = linhcan_boss_config_all();
        $eb = isset($cfg['boss_config']['element_bosses']) ? $cfg['boss_config']['element_bosses'] : array();
        $keys = array();
        foreach ($eb as $k => $v) {
            if ($k === 'none') continue;
            $keys[] = $k;
        }
        if (!$keys) return 'kim';
        return $keys[array_rand($keys)];
    }
}

/**
 * Kiểm tra boss linh căn đã spawn chưa.
 * Trả về array boss data hoặc false.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_check_spawn')) {
    function linhcan_boss_check_spawn($dblj){
        $config = linhcan_boss_config_all();
        $bossConfig = isset($config['boss_config']) ? $config['boss_config'] : array();
        $spawnInterval = intval(isset($bossConfig['spawn_interval_minutes']) ? $bossConfig['spawn_interval_minutes'] : 120);

        // Kiểm tra boss hiện tại
        $stmt = $dblj->query("SELECT * FROM linhcan_boss WHERE status = 'active' ORDER BY spawned_at DESC LIMIT 1");
        $boss = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($boss) {
            $spawnedAt = strtotime($boss['spawned_at']);
            $elapsed = (time() - $spawnedAt) / 60;

            if ($elapsed < $spawnInterval && $boss['hp'] > 0) {
                return $boss; // Boss still active
            }

            // Boss expired or dead, mark as inactive
            $updateStmt = $dblj->prepare("UPDATE linhcan_boss SET status = 'inactive' WHERE id = ?");
            $updateStmt->execute(array($boss['id']));
        }

        return false;
    }
}

/**
 * Spawn boss linh căn mới.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_spawn')) {
    function linhcan_boss_spawn($dblj){
        $config = linhcan_boss_config_all();
        $bossConfig = isset($config['boss_config']) ? $config['boss_config'] : array();
        $baseStats = isset($bossConfig['base_stats']) ? $bossConfig['base_stats'] : array();
        $levelScaling = isset($bossConfig['level_scaling']) ? $bossConfig['level_scaling'] : array();
        $elementBosses = isset($bossConfig['element_bosses']) ? $bossConfig['element_bosses'] : array();

        // Chọn hành ngẫu nhiên trong 5 hành config (không đọc linhcan_id nữa)
        $element = linhcan_boss_world_element($dblj);

        // Lấy tên boss theo element
        $bossInfo = isset($elementBosses[$element]) ? $elementBosses[$element] : array(
            'name' => 'Linh Thú Lạc',
            'icon' => '👹',
            'color' => '#9C27B0'
        );

        // Tinh level = avg ulv cua MOI nhan vat (cot linhcan_id da nguoi)
        $stmt = $dblj->query("SELECT AVG(ulv) as avg_lv FROM game1");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $avgLevel = $row ? max(1, intval($row['avg_lv'])) : 1;

        // Tính stats
        $hp = intval($baseStats['hp']) + ($avgLevel * intval($levelScaling['hp_per_level']));
        $atk = intval($baseStats['atk']) + ($avgLevel * intval($levelScaling['atk_per_level']));
        $def = intval($baseStats['def']) + ($avgLevel * intval($levelScaling['def_per_level']));
        $crit = intval($baseStats['crit']);
        $lifesteal = intval($baseStats['lifesteal']);

        $bossName = $bossInfo['name'];
        $now = date('Y-m-d H:i:s');

        $stmt = $dblj->prepare("INSERT INTO linhcan_boss (name, element, level, hp, maxhp, atk, def, crit, lifesteal, spawned_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute(array($bossName, $element, $avgLevel, $hp, $hp, $atk, $def, $crit, $lifesteal, $now));

        return array(
            'id' => intval($dblj->lastInsertId()),
            'name' => $bossName,
            'element' => $element,
            'level' => $avgLevel,
            'hp' => $hp,
            'maxhp' => $hp,
            'atk' => $atk,
            'def' => $def,
            'crit' => $crit,
            'lifesteal' => $lifesteal,
            'icon' => isset($bossInfo['icon']) ? $bossInfo['icon'] : '🌀',
            'color' => isset($bossInfo['color']) ? $bossInfo['color'] : '#9C27B0'
        );
    }
}

/**
 * Tính sát thương lên boss linh căn.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_calc_damage')) {
    function linhcan_boss_calc_damage($player, $bossRow, $dblj){
        $baseDmg = max(1, intval($player->ugj) - intval(intval($bossRow['def']) * 0.75));

        // Element multiplier
        $lcElement = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $bossElement = isset($bossRow['element']) ? $bossRow['element'] : '';
        $elemMult = 1.0;
        if (!empty($lcElement) && !empty($bossElement)) {
            $elemMult = linhcan_get_element_multiplier($lcElement, $bossElement);
        }

        $damage = max(1, round($baseDmg * $elemMult));

        // Bạo kích
        if (roll_pct($player->ubj, 100)) {
            $damage = round($damage * 1.72);
            return array('damage' => $damage, 'crit' => true, 'elem_mult' => $elemMult);
        }

        return array('damage' => $damage, 'crit' => false, 'elem_mult' => $elemMult);
    }
}

/**
 * Boss the gioi bi giot: LT + da hanh cua boss + Binh Anh Tinh Thach, cong THAT
 * vao playerdaoju (adddj), khong con "roi ao" nhu ban cu.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_drops')) {
    function linhcan_boss_drops($player, $bossRow, $dblj){
        $config = linhcan_boss_config_all();
        $bossConfig = isset($config['boss_config']) ? $config['boss_config'] : array();
        $drops = isset($bossConfig['drops']) ? $bossConfig['drops'] : array();

        $lt = mt_rand(intval($drops['linh_thach_min']), intval($drops['linh_thach_max']));
        $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")
             ->execute(array($lt, $player->sid));

        $html = "<font color='#FFD700'>💰 {$lt} Linh thạch</font><br/>";

        $bossElement = isset($bossRow['element']) ? $bossRow['element'] : '';
        $stoneId = linhcan_da_id($bossElement);
        $stone = 0;
        if ($stoneId > 0) {
            $stone = mt_rand(intval($drops['tinh_thach_min']), intval($drops['tinh_thach_max']));
            linhcan_give_stone($player->sid, $stoneId, $stone, $dblj);
            $html .= "<font color='#4CAF50'>🪨 {$stone} " . linhcan_element_name($bossElement) . " Tinh Thạch</font><br/>";
        }

        $binhAnh = mt_rand(intval($drops['binh_anh_min']), intval($drops['binh_anh_max']));
        linhcan_give_stone($player->sid, linhcan_binh_anh_id(), $binhAnh, $dblj);
        $html .= "<font color='#E91E63'>✨ {$binhAnh} Bình Anh Tinh Thạch</font><br/>";

        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($player->sid, 'kill_boss', 1, $dblj); // hook Task 6
        }

        return array('linhthach' => $lt, 'stone_id' => $stoneId, 'stone' => $stone,
                     'binh_anh' => $binhAnh, 'html' => $html);
    }
}

/**
 * Đánh dấu boss đã bị tiêu diệt.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_boss_mark_killed')) {
    function linhcan_boss_mark_killed($bossId, $killerSid, $dblj){
        $stmt = $dblj->prepare("UPDATE linhcan_boss SET status = 'killed', killer_sid = ?, killed_at = ? WHERE id = ?");
        $stmt->execute(array($killerSid, date('Y-m-d H:i:s'), $bossId));
    }
}

// ==================== BOSS CA NHAN DOT PHA LINH CAN (khuon huyetmach_boss_*) ====================
// Khac boss the gioi: moi nguoi choi co 1 con theo hanh minh, song vo thoi han den khi bi giot
// (khong expire, khong throttle ngay — tu nhien bi chan bang tran Lv + nguyen lieu dot pha).

if (!function_exists(__NAMESPACE__ . '\\lcboss_config')) {
    function lcboss_config(){
        $cfg = linhcan_boss_config_all();
        $bc = isset($cfg['boss_config']) ? $cfg['boss_config'] : array();
        $pb = isset($bc['personal_boss']) ? $bc['personal_boss'] : array();
        $eb = isset($bc['element_bosses']) ? $bc['element_bosses'] : array();
        return array($pb, $eb);
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_active')) {
    function lcboss_active($sid, $dblj, $element){
        $stmt = $dblj->prepare("SELECT * FROM linhcan_boss_personal
            WHERE sid = ? AND element = ? AND status = 'active'
            ORDER BY spawned_at DESC LIMIT 1");
        $stmt->execute(array($sid, $element));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return false;
        if (intval($row['hp']) <= 0) {
            $dblj->prepare("UPDATE linhcan_boss_personal SET status = 'inactive' WHERE id = ?")
                 ->execute(array($row['id']));
            return false;
        }
        return $row;
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_killed')) {
    function lcboss_killed($sid, $dblj, $element, $toStage){
        $stmt = $dblj->prepare("SELECT COUNT(*) FROM linhcan_boss_personal
            WHERE sid = ? AND element = ? AND to_stage = ? AND status = 'killed'");
        $stmt->execute(array($sid, $element, intval($toStage)));
        return intval($stmt->fetchColumn()) > 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_spawn')) {
    function lcboss_spawn($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return false;
        $active = lcboss_active($sid, $dblj, $element);
        if ($active) return $active;

        $node = linhcan_node_get($sid, $element, $dblj);
        $stage = intval($node['stage']);
        if ($stage >= linhcan_stage_max()) return false;
        if (intval($node['level']) < linhcan_stage_cap($stage)) return false;
        $toStage = $stage + 1;
        if (lcboss_killed($sid, $dblj, $element, $toStage)) return false;

        $player = getplayer($sid, $dblj);
        if (!$player) return false;

        list($pb, $eb) = lcboss_config();
        $lv = max(1, intval($player->ulv));
        $hp = intval($pb['hp_base']) + intval($pb['hp_per_lv']) * $lv;
        $atk = intval($pb['atk_base']) + intval($pb['atk_per_lv']) * $lv;
        $defv = intval($pb['def_base']) + intval($pb['def_per_lv']) * $lv;
        $critv = intval($pb['crit_base']) + intval($pb['crit_per_stage']) * $toStage;
        $name = isset($eb[$element]['name']) ? $eb[$element]['name'] : ('Boss ' . $def['name']);
        $icon = isset($eb[$element]['icon']) ? $eb[$element]['icon'] : '👹';
        $color = isset($eb[$element]['color']) ? $eb[$element]['color'] : $def['color'];

        $ins = $dblj->prepare("INSERT INTO linhcan_boss_personal
            (sid, element, to_stage, name, level, hp, maxhp, atk, def, crit, spawned_at, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'active')");
        $ins->execute(array($sid, $element, $toStage, $name, $lv, $hp, $hp, $atk, $defv, $critv));

        return array(
            'id' => intval($dblj->lastInsertId()), 'sid' => $sid, 'element' => $element,
            'to_stage' => $toStage, 'name' => $name, 'level' => $lv,
            'hp' => $hp, 'maxhp' => $hp, 'atk' => $atk, 'def' => $defv, 'crit' => $critv,
            'icon' => $icon, 'color' => $color, 'spawned_at' => date('Y-m-d H:i:s'), 'status' => 'active'
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_calc_damage')) {
    function lcboss_calc_damage($player, $bossRow){
        $baseDmg = max(1, intval($player->ugj) - intval(intval($bossRow['def']) * 0.75));
        $lcElement = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $bossElement = isset($bossRow['element']) ? $bossRow['element'] : '';
        $elemMult = 1.0;
        if ($lcElement !== '' && $bossElement !== '') {
            $elemMult = linhcan_get_element_multiplier($lcElement, $bossElement);
        }
        $dmg = max(1, round($baseDmg * $elemMult));
        $isCrit = false;
        if (roll_pct($player->ubj, 100)) {
            $dmg = round($dmg * 1.72);
            $isCrit = true;
        }
        return array('damage' => intval($dmg), 'crit' => $isCrit, 'elem_mult' => floatval($elemMult));
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_boss_counter')) {
    // Dam nguoc cua boss len nguoi choi — Task 11 dung khi hien chieu thuong.
    function lcboss_boss_counter($player, $bossRow){
        return max(1, intval($bossRow['atk']) - intval(intval($player->ufy) * 0.6));
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_mark_killed')) {
    function lcboss_mark_killed($sid, $dblj, $row){
        $stmt = $dblj->prepare("UPDATE linhcan_boss_personal
            SET status = 'killed', hp = 0, killed_at = NOW() WHERE id = ? AND sid = ?");
        $stmt->execute(array(intval($row['id']), $sid));
    }
}

if (!function_exists(__NAMESPACE__ . '\\lcboss_drops')) {
    function lcboss_drops($sid, $dblj, $bossRow){
        list($pb, $eb) = lcboss_config();
        $element = isset($bossRow['element']) ? $bossRow['element'] : '';
        $stoneId = linhcan_da_id($element);
        $min = intval($pb['drop_stone_min']); $max = intval($pb['drop_stone_max']);
        $amount = mt_rand($min, max($min, $max));
        $html = '';
        if ($stoneId > 0 && $amount > 0) {
            linhcan_give_stone($sid, $stoneId, $amount, $dblj);
            $html .= "<font color='#4CAF50'>🪨 Thu được {$amount} " . linhcan_material_name($stoneId) . "</font><br/>";
        }
        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($sid, 'kill_boss', 1, $dblj); // hook Task 6
        }
        return array('stone_id' => $stoneId, 'stone' => $amount, 'html' => $html);
    }
}
