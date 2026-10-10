<?php
namespace player;

/**
 * huyetmach.php - Hệ thống Huyết Mạch
 *
 * Huyết Mạch ảnh hưởng:
 * - ATK, HP, Crit, Lifesteal (stat bonus)
 * - Element damage bonus
 * - Passive (element dmg, crit, exp, drop)
 * - Biến thân (transformation)
 * - Thần Thông (mở khóa theo phẩm + thức tỉnh)
 */

if (!function_exists(__NAMESPACE__ . '\\huyetmach_config_path')) {
    function huyetmach_config_path(){
        $paths = array(
            __DIR__ . '/../config/huyetmach_config.json',
            __DIR__ . '/huyetmach_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_config_all')) {
    function huyetmach_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = huyetmach_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        $cache = is_array($data) ? $data : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_config_get')) {
    function huyetmach_config_get($id){
        $id = (string)intval($id);
        $all = huyetmach_config_all();
        return (isset($all[$id]) && is_array($all[$id])) ? $all[$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_random')) {
    function huyetmach_random(){
        $all = huyetmach_config_all();
        if (empty($all)) return false;

        $totalWeight = 0;
        foreach ($all as $hm) {
            if (!isset($hm['weight'])) continue;
            $totalWeight += floatval($hm['weight']);
        }

        $roll = mt_rand(0, intval($totalWeight * 100)) / 100;
        $cumulative = 0;

        foreach ($all as $hm) {
            if (!isset($hm['weight'])) continue;
            $cumulative += floatval($hm['weight']);
            if ($roll <= $cumulative) return $hm;
        }

        return end($all);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_roll')) {
    function huyetmach_roll(){
        $type = huyetmach_random();
        if (!$type) return false;

        $qualityChances = array(
            1 => 40.0,
            2 => 25.0,
            3 => 15.0,
            4 => 10.0,
            5 => 6.0,
            6 => 2.5,
            7 => 1.0,
            8 => 0.4,
            9 => 0.09,
            10 => 0.01
        );

        $roll = mt_rand(0, 10000) / 100;
        $cumulative = 0;
        $quality = 1;
        foreach ($qualityChances as $q => $chance) {
            $cumulative += $chance;
            if ($roll <= $cumulative) {
                $quality = $q;
                break;
            }
        }

        return array('type' => $type, 'quality' => $quality);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_roll_min')) {
    function huyetmach_roll_min($minQuality = 1, $maxQuality = 3){
        $type = huyetmach_random();
        if (!$type) return false;

        $minQuality = max(1, intval($minQuality));
        $maxQuality = max($minQuality, intval($maxQuality));

        $qualityChances = array(
            1 => 40.0,
            2 => 25.0,
            3 => 15.0,
            4 => 10.0,
            5 => 6.0,
            6 => 2.5,
            7 => 1.0,
            8 => 0.4,
            9 => 0.09,
            10 => 0.01
        );

        $filtered = array();
        $totalChance = 0;
        foreach ($qualityChances as $q => $chance) {
            if ($q >= $minQuality && $q <= $maxQuality) {
                $filtered[$q] = $chance;
                $totalChance += $chance;
            }
        }

        if (empty($filtered)) {
            return array('type' => $type, 'quality' => $minQuality);
        }

        $roll = mt_rand(0, intval($totalChance * 100)) / 100;
        $cumulative = 0;
        $quality = $minQuality;
        foreach ($filtered as $q => $chance) {
            $cumulative += $chance;
            if ($roll <= $cumulative) {
                $quality = $q;
                break;
            }
        }

        return array('type' => $type, 'quality' => $quality);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_assign')) {
    function huyetmach_assign($sid, $dblj, $huyetmach_id, $quality = 1){
        $huyetmach_id = intval($huyetmach_id);
        $quality = intval($quality);
        if ($huyetmach_id <= 0) return false;
        if ($quality < 1) $quality = 1;
        if ($quality > 10) $quality = 10;

        $config = huyetmach_config_get($huyetmach_id);
        if (!$config) return false;

        $stmt = $dblj->prepare("UPDATE game1 SET huyetmach_id = ?, huyetmach_quality = ? WHERE sid = ?");
        $ok = $stmt->execute(array($huyetmach_id, $quality, $sid));
        player_cache_clear($sid);
        return $ok;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_info')) {
    function huyetmach_info($sid, $dblj){
        $stmt = $dblj->prepare("SELECT huyetmach_id, huyetmach_quality, huyetmach_awaken_stage, huyetmach_transform_cd FROM game1 WHERE sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || empty($row['huyetmach_id'])) return false;

        $config = huyetmach_config_get(intval($row['huyetmach_id']));
        if (!$config) return false;

        $config['_quality'] = intval($row['huyetmach_quality']);
        $config['_awaken_stage'] = intval($row['huyetmach_awaken_stage']);
        $config['_transform_cd'] = $row['huyetmach_transform_cd'];
        return $config;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_realm_scale')) {
    function huyetmach_realm_scale($jingjieValue){
        return 1 + intval($jingjieValue) * 0.18;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_awaken_mult')) {
    function huyetmach_awaken_mult($stage){
        return 1 + intval($stage) * 0.25;
    }
}

/**
 * Tính tổng bonus từ huyết mạch.
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_calc_bonuses')) {
    function huyetmach_calc_bonuses($sid, $dblj){
        $empty = array(
            'stats' => array('atk' => 0, 'hp' => 0, 'crit' => 0, 'lifesteal' => 0),
            'element_dmg' => 0,
            'passive_element' => 0,
            'passive_crit' => 0,
            'passive_exp' => 0,
            'passive_drop' => 0,
            'quality' => 1,
            'awaken_stage' => 0,
            'element' => '',
            'name' => '',
            'color' => '',
            'icon' => '',
            'transform_name' => '',
            'transform_icon' => '',
            'can_transform' => false
        );

        $config = huyetmach_info($sid, $dblj);
        if (!$config) return $empty;

        $quality = isset($config['_quality']) ? intval($config['_quality']) : 1;
        $awakenStage = isset($config['_awaken_stage']) ? intval($config['_awaken_stage']) : 0;
        $baseStats = isset($config['base_stats']) ? $config['base_stats'] : array();

        $realmScale = huyetmach_realm_scale(isset($GLOBALS['player']) ? $GLOBALS['player']->jingjieValue : 0);
        $awakenMult = huyetmach_awaken_mult($awakenStage);

        $finalATK = round(floatval(isset($baseStats['atk']) ? $baseStats['atk'] : 0) * $quality * $realmScale * $awakenMult);
        $finalHP = round(floatval(isset($baseStats['hp']) ? $baseStats['hp'] : 0) * $quality * $realmScale * $awakenMult);
        $finalCrit = round(floatval(isset($baseStats['crit']) ? $baseStats['crit'] : 0) * $quality * $realmScale * $awakenMult);
        $finalLifesteal = round(floatval(isset($baseStats['lifesteal']) ? $baseStats['lifesteal'] : 0) * $quality * $realmScale * $awakenMult);

        $elementDmgBase = floatval(isset($config['element_dmg_base']) ? $config['element_dmg_base'] : 0);
        $elementDmg = round($elementDmgBase * $quality * $awakenMult);

        $transformUnlockStage = isset($config['unlock_stage']) ? intval($config['unlock_stage']) : 3;
        $canTransform = ($awakenStage >= $transformUnlockStage) && ($awakenStage >= 3);

        return array(
            'stats' => array('atk' => $finalATK, 'hp' => $finalHP, 'crit' => $finalCrit, 'lifesteal' => $finalLifesteal),
            'element_dmg' => $elementDmg,
            'passive_element' => floatval(isset($config['passive_element']) ? $config['passive_element'] : 0),
            'passive_crit' => floatval(isset($config['passive_crit']) ? $config['passive_crit'] : 0),
            'passive_exp' => floatval(isset($config['passive_exp']) ? $config['passive_exp'] : 0),
            'passive_drop' => floatval(isset($config['passive_drop']) ? $config['passive_drop'] : 0),
            'quality' => $quality,
            'awaken_stage' => $awakenStage,
            'element' => isset($config['element']) ? $config['element'] : '',
            'name' => isset($config['name']) ? $config['name'] : '',
            'color' => isset($config['color']) ? $config['color'] : '#9e9e9e',
            'icon' => isset($config['icon']) ? $config['icon'] : '🩸',
            'transform_name' => isset($config['transform_name']) ? $config['transform_name'] : '',
            'transform_icon' => isset($config['transform_icon']) ? $config['transform_icon'] : '',
            'can_transform' => $canTransform
        );
    }
}

/**
 * Awakening config
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_awakening_config_path')) {
    function huyetmach_awakening_config_path(){
        $paths = array(
            __DIR__ . '/../config/huyetmach_awakening_config.json',
            __DIR__ . '/huyetmach_awakening_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_awakening_config_all')) {
    function huyetmach_awakening_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = huyetmach_awakening_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        $cache = is_array($data) ? $data : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_get_awakening_info')) {
    function huyetmach_get_awakening_info($currentStage){
        $all = huyetmach_awakening_config_all();
        $stages = isset($all['stages']) ? $all['stages'] : array();
        foreach ($stages as $s) {
            if (intval($s['from_stage']) === intval($currentStage)) return $s;
        }
        return false;
    }
}

/**
 * Kiểm tra boss đã đánh bại chưa.
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_killed')) {
    function huyetmach_boss_killed($sid, $bossId, $dblj){
        $stmt = $dblj->prepare("SELECT killed_at FROM player_huyetmach_boss WHERE sid = ? AND boss_id = ? LIMIT 1");
        $stmt->execute(array($sid, $bossId));
        return ($stmt->fetch(\PDO::FETCH_ASSOC)) ? true : false;
    }
}

/**
 * Ghi nhận boss đã đánh bại.
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_boss_mark')) {
    function huyetmach_boss_mark($sid, $bossId, $dblj){
        $stmt = $dblj->prepare("INSERT INTO player_huyetmach_boss (sid, boss_id, killed_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE killed_at = NOW()");
        $stmt->execute(array($sid, $bossId));
    }
}

/**
 * Lấy progress quest thức tỉnh.
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_quest_progress')) {
    function huyetmach_quest_progress($sid, $questId, $dblj){
        $stmt = $dblj->prepare("SELECT progress, completed, claimed FROM player_huyetmach_quest WHERE sid = ? AND quest_id = ? LIMIT 1");
        $stmt->execute(array($sid, $questId));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return array('progress' => array(), 'completed' => 0, 'claimed' => 0);
        return array(
            'progress' => $row['progress'] ? json_decode($row['progress'], true) : array(),
            'completed' => intval($row['completed']),
            'claimed' => intval($row['claimed'])
        );
    }
}

/**
 * Cập nhật progress quest.
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_quest_update')) {
    function huyetmach_quest_update($sid, $questId, $objective, $amount, $dblj){
        $current = huyetmach_quest_progress($sid, $questId, $dblj);
        $prog = $current['progress'];
        if (!isset($prog[$objective])) $prog[$objective] = 0;
        $prog[$objective] += intval($amount);

        $progJson = json_encode($prog);
        $stmt = $dblj->prepare("INSERT INTO player_huyetmach_quest (sid, quest_id, progress, completed, claimed, created_at) VALUES (?, ?, ?, 0, 0, NOW()) ON DUPLICATE KEY UPDATE progress = ?");
        $stmt->execute(array($sid, $questId, $progJson, $progJson));
    }
}

/**
 * Thức tỉnh huyết mạch.
 * Stage 2+: cần boss_drop + quest.
 * Stage 3+: cần material thêm.
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_awaken')) {
    function huyetmach_awaken($sid, $dblj){
        $player = getplayer($sid, $dblj);
        $currentStage = intval($player->huyetmach_awaken_stage);
        $currentId = intval($player->huyetmach_id);

        if ($currentId <= 0) {
            return array('ok' => false, 'msg' => 'Bạn chưa có Huyết Mạch.');
        }

        $awakenInfo = huyetmach_get_awakening_info($currentStage);
        if (!$awakenInfo) {
            return array('ok' => false, 'msg' => 'Đã thức tỉnh tối đa.');
        }

        $realmMinLevel = intval($awakenInfo['realm_min_level']);
        if ($player->ulv < $realmMinLevel) {
            return array('ok' => false, 'msg' => 'Cần đạt cảnh giới ' . $awakenInfo['realm_require'] . ' (cấp ' . $realmMinLevel . '+).');
        }

        if (isset($awakenInfo['boss_drop_require']) && $awakenInfo['boss_drop_require']) {
            $config = huyetmach_info($sid, $dblj);
            $hmKey = huyetmach_get_key_by_id($currentId);
            $stageKey = 'stage_' . intval($awakenInfo['to_stage']);
            $awakenAll = huyetmach_awakening_config_all();
            $bossList = isset($awakenAll['boss_list']) ? $awakenAll['boss_list'] : array();

            if ($hmKey && isset($bossList[$hmKey][$stageKey])) {
                $bossId = $bossList[$hmKey][$stageKey]['name'];
                if (!huyetmach_boss_killed($sid, $bossId, $dblj)) {
                    return array('ok' => false, 'msg' => 'Cần đánh bại boss "' . $bossId . '" trước.');
                }
            }
        }

        if (isset($awakenInfo['material_id']) && intval($awakenInfo['material_id']) > 0) {
            $materialId = intval($awakenInfo['material_id']);
            $materialAmount = intval($awakenInfo['material_amount']);
            $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
            $stmt->execute(array($sid, $materialId));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $have = $row ? intval($row['djsum']) : 0;
            if ($have < $materialAmount) {
                $matName = huyetmach_get_material_name($materialId);
                return array('ok' => false, 'msg' => $matName . ' không đủ. Cần ' . $materialAmount . ', có ' . $have . '.');
            }
            $stmtConsume = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $stmtConsume->execute(array($materialAmount, $sid, $materialId, $materialAmount));
        }

        $toStage = intval($awakenInfo['to_stage']);
        $stmtUp = $dblj->prepare("UPDATE game1 SET huyetmach_awaken_stage = ? WHERE sid = ?");
        $stmtUp->execute(array($toStage, $sid));

        $stmtLog = $dblj->prepare("INSERT INTO player_huyetmach_awaken_log (sid, from_stage, to_stage, success, created_at) VALUES (?, ?, ?, 1, NOW())");
        $stmtLog->execute(array($sid, $currentStage, $toStage));

        player_cache_clear($sid);

        $toName = isset($awakenInfo['to_name']) ? $awakenInfo['to_name'] : 'Stage ' . $toStage;
        return array('ok' => true, 'msg' => 'Thức tỉnh thành công! Huyết Mạch đã lên ' . $toName . '.', 'new_stage' => $toStage);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_get_key_by_id')) {
    function huyetmach_get_key_by_id($id){
        $id = intval($id);
        $map = array(
            1 => 'chân_long', 2 => 'chân_phượng', 3 => 'kỳ_lân',
            4 => 'kim_ô', 5 => 'côn_bằng', 6 => 'huyền_vũ',
            7 => 'bạch_hổ', 8 => 'chu_tước', 9 => 'bàn_cổ',
            10 => 'hỗn_độ', 11 => 'hồng_mông'
        );
        return isset($map[$id]) ? $map[$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_get_material_name')) {
    function huyetmach_get_material_name($djid){
        $names = array(
            100 => 'Tinh Thể Đá', 101 => 'Huyền Thạch', 102 => 'Địa Linh Ngọc',
            103 => 'Thiên Cơ Đá', 104 => 'Thánh Thạch', 105 => 'Tiên Lộ',
            106 => 'Thần Thạch', 107 => 'Đế Lệnh', 108 => 'Đạo Nguyên'
        );
        return isset($names[$djid]) ? $names[$djid] : 'Vật phẩm';
    }
}

/**
 * Transform config
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_transform_config_path')) {
    function huyetmach_transform_config_path(){
        $paths = array(
            __DIR__ . '/../config/huyetmach_transform_config.json',
            __DIR__ . '/huyetmach_transform_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_transform_config_all')) {
    function huyetmach_transform_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = huyetmach_transform_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        $cache = is_array($data) ? $data : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_get_transform')) {
    function huyetmach_get_transform($bloodlineId){
        $all = huyetmach_transform_config_all();
        $transforms = isset($all['transforms']) ? $all['transforms'] : array();
        foreach ($transforms as $t) {
            if (intval($t['bloodline_id']) === intval($bloodlineId)) return $t;
        }
        return false;
    }
}

/**
 * Kiểm tra có thể biến thân không.
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_can_transform')) {
    function huyetmach_can_transform($sid, $dblj){
        $config = huyetmach_info($sid, $dblj);
        if (!$config) return array('ok' => false, 'msg' => 'Bạn chưa có Huyết Mạch.');

        $awakenStage = isset($config['_awaken_stage']) ? intval($config['_awaken_stage']) : 0;
        if ($awakenStage < 3) {
            return array('ok' => false, 'msg' => 'Cần thức tỉnh Chân Thân (Stage 3) để biến thân.');
        }

        $transformCd = isset($config['_transform_cd']) ? $config['_transform_cd'] : null;
        if ($transformCd) {
            $cdTime = strtotime($transformCd);
            if ($cdTime > time()) {
                $remaining = $cdTime - time();
                $mins = intval($remaining / 60);
                $secs = $remaining % 60;
                return array('ok' => false, 'msg' => 'Biến thân đang hồi. Còn ' . $mins . ' phút ' . $secs . ' giây.');
            }
        }

        $player = getplayer($sid, $dblj);
        $hpCostPercent = 20;
        $hpCost = intval($player->umaxhp * $hpCostPercent / 100);
        if ($player->uxx < $hpCost) {
            return array('ok' => false, 'msg' => 'HP không đủ để biến thân (cần ' . number_format($hpCost) . ' HP).');
        }

        return array('ok' => true, 'msg' => 'Có thể biến thân.');
    }
}

/**
 * Kích hoạt biến thân.
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_activate_transform')) {
    function huyetmach_activate_transform($sid, $dblj){
        $canTransform = huyetmach_can_transform($sid, $dblj);
        if (!$canTransform['ok']) return $canTransform;

        $config = huyetmach_info($sid, $dblj);
        $hmId = intval($config['id']);
        $transform = huyetmach_get_transform($hmId);
        if (!$transform) return array('ok' => false, 'msg' => 'Không tìm thấy thông tin biến thân.');

        $player = getplayer($sid, $dblj);
        $hpCostPercent = intval($transform['hp_cost_percent']);
        $hpCost = intval($player->umaxhp * $hpCostPercent / 100);
        $newHP = max(1, $player->uxx - $hpCost);

        $stmtHP = $dblj->prepare("UPDATE game1 SET uxx = ? WHERE sid = ?");
        $stmtHP->execute(array($newHP, $sid));

        $cdMinutes = intval($transform['cooldown_minutes']);
        $cdTime = date('Y-m-d H:i:s', time() + $cdMinutes * 60);
        $stmtCD = $dblj->prepare("UPDATE game1 SET huyetmach_transform_cd = ? WHERE sid = ?");
        $stmtCD->execute(array($cdTime, $sid));

        player_cache_clear($sid);

        return array(
            'ok' => true,
            'msg' => 'Biến thân thành công! Đã biến thành ' . $transform['name'] . '.',
            'transform' => $transform,
            'hp_cost' => $hpCost
        );
    }
}

/**
 * Display names
 */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_quality_name')) {
    function huyetmach_quality_name($quality){
        $map = array(
            1 => 'Phàm Phẩm', 2 => 'Linh Phẩm', 3 => 'Huyền Phẩm',
            4 => 'Địa Phẩm', 5 => 'Thiên Phẩm', 6 => 'Thánh Phẩm',
            7 => 'Tiên Phẩm', 8 => 'Thần Phẩm', 9 => 'Đế Phẩm', 10 => 'Đạo Phẩm'
        );
        return isset($map[$quality]) ? $map[$quality] : 'Phàm Phẩm';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_quality_color')) {
    function huyetmach_quality_color($quality){
        $map = array(
            1 => '#9e9e9e', 2 => '#4caf50', 3 => '#2196f3',
            4 => '#9c27b0', 5 => '#ff9800', 6 => '#ff5722',
            7 => '#f44336', 8 => '#ffd700', 9 => '#7c4dff', 10 => '#e0e0e0'
        );
        return isset($map[$quality]) ? $map[$quality] : '#9e9e9e';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_quality_icon')) {
    function huyetmach_quality_icon($quality){
        $map = array(
            1 => '⚪', 2 => '🟢', 3 => '🔵',
            4 => '🟣', 5 => '🟡', 6 => '🟠',
            7 => '🔴', 8 => '✨', 9 => '🌌', 10 => '☯'
        );
        return isset($map[$quality]) ? $map[$quality] : '⚪';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_awaken_name')) {
    function huyetmach_awaken_name($stage){
        $map = array(
            0 => 'Vĩnh Viễn Trầm Mệnh',
            1 => 'Sơ Giác',
            2 => 'Thức Tỉnh',
            3 => 'Chân Thân',
            4 => 'Huyết Thống Viên Mãn'
        );
        return isset($map[$stage]) ? $map[$stage] : 'Vĩnh Viễn Trầm Mệnh';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_awaken_color')) {
    function huyetmach_awaken_color($stage){
        $map = array(
            0 => '#666666', 1 => '#4caf50', 2 => '#ff9800',
            3 => '#f44336', 4 => '#ffd700'
        );
        return isset($map[$stage]) ? $map[$stage] : '#666666';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_awaken_icon')) {
    function huyetmach_awaken_icon($stage){
        $map = array(
            0 => '🔒', 1 => '🌟', 2 => '⭐', 3 => '💫', 4 => '👑'
        );
        return isset($map[$stage]) ? $map[$stage] : '🔒';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_element_name')) {
    function huyetmach_element_name($element){
        $map = array(
            'kim' => 'Kim', 'moc' => 'Mộc', 'thuy' => 'Thủy',
            'hoa' => 'Hỏa', 'tho' => 'Thổ', 'all' => 'Ngũ Hành', 'none' => 'Vô Hành'
        );
        return isset($map[$element]) ? $map[$element] : 'Không rõ';
    }
}

/** ===== MANG MACH HUYET: config + ham thuan tuy ===== */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_config_path')) {
    function huyetmach_vessel_config_path(){
        $paths = array(
            __DIR__ . '/../config/huyetmach_vessel_config.json',
            __DIR__ . '/huyetmach_vessel_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_config_all')) {
    function huyetmach_vessel_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = huyetmach_vessel_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $data = json_decode(file_get_contents($file), true);
        $cache = is_array($data) ? $data : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_defs')) {
    function huyetmach_vessel_defs(){
        $all = huyetmach_vessel_config_all();
        return isset($all['vessels']) ? $all['vessels'] : array();
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_tiers')) {
    function huyetmach_vessel_tiers(){
        $all = huyetmach_vessel_config_all();
        return isset($all['tiers']) ? $all['tiers'] : array();
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_def')) {
    function huyetmach_vessel_def($id){
        $defs = huyetmach_vessel_defs();
        $id = intval($id);
        return isset($defs[$id]) ? $defs[$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_tier')) {
    function huyetmach_vessel_tier($stage){
        $tiers = huyetmach_vessel_tiers();
        $stage = intval($stage);
        return isset($tiers[$stage]) ? $tiers[$stage] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_cap')) {
    function huyetmach_vessel_cap($stage){
        $tier = huyetmach_vessel_tier($stage);
        return $tier ? intval($tier['lv_cap']) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_stage_max')) {
    function huyetmach_vessel_stage_max(){
        $max = 0;
        foreach (huyetmach_vessel_tiers() as $tier) {
            if (intval($tier['stage']) > $max) $max = intval($tier['stage']);
        }
        return $max;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_refine_cost')) {
    function huyetmach_vessel_refine_cost($lv){
        $all = huyetmach_vessel_config_all();
        $r = isset($all['refine']) ? $all['refine'] : array();
        $lv = max(0, intval($lv));
        $per = max(1, intval(isset($r['material_per_lv']) ? $r['material_per_lv'] : 8));
        return array(
            'material_id'     => intval(isset($r['material_id']) ? $r['material_id'] : 112),
            'material_amount' => intval(isset($r['material_base']) ? $r['material_base'] : 1) + intval($lv / $per),
            'linh_thach_cost' => intval(isset($r['linh_thach_base']) ? $r['linh_thach_base'] : 200)
                + $lv * intval(isset($r['linh_thach_per_lv']) ? $r['linh_thach_per_lv'] : 25),
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_milestone')) {
    function huyetmach_vessel_milestone($total){
        $all = huyetmach_vessel_config_all();
        $ms = isset($all['milestones']) ? $all['milestones'] : array();
        $total = intval($total);
        $name = ''; $pct = 0.0; $next = array('name' => '', 'percent' => 0, 'total_lv' => 0);
        foreach ($ms as $m) {
            $need = intval($m['total_lv']);
            if ($total >= $need) {
                $name = $m['name']; $pct = floatval($m['bonus_percent']);
            } elseif ($next['total_lv'] === 0 || $need < $next['total_lv']) {
                $next = array('name' => $m['name'], 'percent' => floatval($m['bonus_percent']), 'total_lv' => $need);
            }
        }
        return array('name' => $name, 'bonus_percent' => $pct,
            'next_milestone_name' => $next['name'], 'next_milestone_percent' => $next['percent'],
            'next_milestone_lv' => $next['total_lv']);
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_stat_label')) {
    function huyetmach_vessel_stat_label($stat){
        $all = huyetmach_vessel_config_all();
        $labels = isset($all['stat_labels']) ? $all['stat_labels'] : array();
        return isset($labels[$stat]) ? $labels[$stat] : $stat;
    }
}
