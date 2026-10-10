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
            'can_transform' => false,
            'vessel' => array()
        );

        $config = huyetmach_info($sid, $dblj);
        if (!$config) {
            return array_merge($empty, array('vessel' => huyetmach_vessel_bonuses($sid, $dblj)));
        }

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
            'can_transform' => $canTransform,
            'vessel' => huyetmach_vessel_bonuses($sid, $dblj)
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
        if (!$player || intval($player->huyetmach_id) <= 0) {
            return array('ok' => false, 'msg' => 'Bạn chưa có Huyết Mạch.', 'new_stage' => 0);
        }
        $currentStage = intval($player->huyetmach_awaken_stage);
        $gate = huyetmach_vessel_break_rows($sid, $dblj);
        if (!$gate['next']) {
            return array('ok' => false, 'msg' => 'Đã thức tỉnh tối đa.', 'new_stage' => $currentStage);
        }
        if (!$gate['ready']) {
            foreach ($gate['rows'] as $row) {
                if (!$row['ok']) return array('ok' => false, 'msg' => 'Chưa đủ điều kiện: ' . $row['text'] . '.', 'new_stage' => $currentStage);
            }
        }
        $next = $gate['next'];
        $toStage = intval($next['stage']);
        $mid = intval($next['material_id']); $mamt = intval($next['material_amount']);
        $lt  = intval($next['linh_thach_cost']);

        $dblj->beginTransaction();
        try {
            if ($mid > 0 && $mamt > 0) {
                $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
                $stmt->execute(array($mamt, $sid, $mid, $mamt));
                if ($stmt->rowCount() !== 1) throw new Exception('thieu vat lieu');
            }
            if ($lt > 0) {
                $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
                $stmt->execute(array($lt, $sid, $lt));
                if ($stmt->rowCount() !== 1) throw new Exception('thieu linh thach');
            }
            $stmt = $dblj->prepare("UPDATE game1 SET huyetmach_awaken_stage = ? WHERE sid = ?");
            $stmt->execute(array($toStage, $sid));
            $dblj->prepare("INSERT INTO player_huyetmach_awaken_log (sid, from_stage, to_stage, boss_killed, success, created_at)
                VALUES (?, ?, ?, ?, 1, NOW())")->execute(array($sid, $currentStage, $toStage,
                $gate['boss_name'] !== '' ? $gate['boss_name'] : null));
            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            return array('ok' => false, 'msg' => 'Thức tỉnh thất bại, thử lại sau.', 'new_stage' => $currentStage);
        }

        player_cache_clear($sid);
        $toName = $next['name'];
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
            106 => 'Thần Thạch', 107 => 'Đế Lệnh', 108 => 'Đạo Nguyên',
            112 => 'Huyết Thạch', 113 => 'Huyết Mạch Đan'
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
        // HP, khong phai uxx (Hut Máu) — bug cu tru nham cot
        if (intval($player->uhp) < $hpCost) {
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
        // HP, khong phai uxx (Hut Máu) — bug cu tru nham cot
        $newHP = max(1, intval($player->uhp) - $hpCost);

        $stmtHP = $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?");
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

/** ===== MANG MACH HUYET: checklist thuc tinh ===== */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_next_stage')) {
    function huyetmach_vessel_next_stage($stage){
        $next = huyetmach_vessel_tier(intval($stage) + 1);
        return $next ? $next : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_boss_name')) {
    /** Ten boss can win de len `toStage`, theo huyet mach dang mang. */
    function huyetmach_vessel_boss_name($bloodlineId, $toStage){
        $key = huyetmach_get_key_by_id($bloodlineId);
        if (!$key) return '';
        $all = huyetmach_awakening_config_all();
        $list = isset($all['boss_list']) ? $all['boss_list'] : array();
        $sk = 'stage_' . intval($toStage);
        if (isset($list[$key][$sk]['name'])) return $list[$key][$sk]['name'];
        return '';
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_break_rows')) {
    function huyetmach_vessel_break_rows($sid, $dblj){
        $rows = array(); $ready = true; $bossName = '';
        $player = getplayer($sid, $dblj);
        if (!$player) return array('rows' => $rows, 'ready' => false, 'next' => false, 'boss_name' => '');

        $stage = intval($player->huyetmach_awaken_stage);
        $next = huyetmach_vessel_next_stage($stage);
        if (!$next) return array('rows' => $rows, 'ready' => false, 'next' => false, 'boss_name' => '');

        $b = huyetmach_vessel_bonuses($sid, $dblj);
        $need = intval($next['total_lv_require']);
        $ok = $b['total_lv'] >= $need; $ready = $ready && $ok;
        $rows[] = array('ok' => $ok, 'text' => 'Tổng cấp Bát Mạch: ' . $b['total_lv'] . ' / ' . $need);

        $needLv = intval($next['realm_min_level']);
        $ok = intval($player->ulv) >= $needLv; $ready = $ready && $ok;
        $rows[] = array('ok' => $ok, 'text' => 'Cảnh giới: cấp ' . intval($player->ulv) . ' / ' . $needLv);

        $mid = intval($next['material_id']); $mamt = intval($next['material_amount']);
        if ($mid > 0 && $mamt > 0) {
            $have = huyetmach_vessel_daoju_have($sid, $dblj, $mid);
            $ok = $have >= $mamt; $ready = $ready && $ok;
            $rows[] = array('ok' => $ok, 'text' => huyetmach_get_material_name($mid) . ': ' . $have . ' / ' . $mamt);
        }

        $lt = intval($next['linh_thach_cost']);
        if ($lt > 0) {
            $ok = intval($player->uyxb) >= $lt; $ready = $ready && $ok;
            $rows[] = array('ok' => $ok, 'text' => 'Linh Thạch: ' . number_format(intval($player->uyxb)) . ' / ' . number_format($lt));
        }

        if (intval($next['boss_require']) === 1) {
            $bossName = huyetmach_vessel_boss_name(intval($player->huyetmach_id), intval($next['stage']));
            if ($bossName !== '') {
                // boss_id trong player_huyetmach_boss luu THE TEN boss — Task 8 phai mark bang dung ten nay
                $ok = huyetmach_boss_killed($sid, $bossName, $dblj); $ready = $ready && $ok;
                $rows[] = array('ok' => $ok, 'text' => 'Boss mạch huyết: ' . $bossName . ($ok ? ' (đã giết)' : ' (chưa giết)'));
            } else {
                // chuyen huyet mach chua co boss tuong ung -> khong chan, ghi de debug
                $rows[] = array('ok' => true, 'text' => 'Boss mạch huyết: chưa có cấu hình cho huyết mạch này');
            }
        }

        return array('rows' => $rows, 'ready' => $ready, 'next' => $next, 'boss_name' => $bossName);
    }
}

/** ===== MANG MACH HUYET: Luyen Mach ===== */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_daoju_have')) {
    function huyetmach_vessel_daoju_have($sid, $dblj, $djid){
        $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
        $stmt->execute(array($sid, intval($djid)));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? intval($row['djsum']) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_refine')) {
    /** Luyen 1 mach +1 cap. Thanh cong 100%. Ton Huyet Thach + Linh Thach. */
    function huyetmach_vessel_refine($sid, $dblj, $vesselId){
        $def = huyetmach_vessel_def($vesselId);
        if (!$def) return array('ok' => false, 'msg' => 'Mach huyet khong ton tai.', 'level' => 0, 'cost' => array());

        $stage = 0;
        $stmt = $dblj->prepare("SELECT huyetmach_awaken_stage, uyxb FROM game1 WHERE sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return array('ok' => false, 'msg' => 'Khong tim thay nhan vat.', 'level' => 0, 'cost' => array());
        $stage = intval($row['huyetmach_awaken_stage']);
        $linhThach = intval($row['uyxb']);

        $levels = huyetmach_vessel_levels($sid, $dblj);
        $vid = intval($def['id']);
        $lv = intval($levels[$vid]);
        $cap = huyetmach_vessel_cap($stage);
        if ($lv >= $cap) {
            return array('ok' => false, 'msg' => $def['name'] . ' đã đạt trần ' . $cap . ' cấp — phải thức tỉnh Huyết Mạch để luyện tiếp.',
                'level' => $lv, 'cost' => array());
        }

        $cost = huyetmach_vessel_refine_cost($lv);
        $have = huyetmach_vessel_daoju_have($sid, $dblj, $cost['material_id']);
        if ($have < $cost['material_amount']) {
            return array('ok' => false,
                'msg' => huyetmach_get_material_name($cost['material_id']) . ' không đủ: cần '
                    . $cost['material_amount'] . ', có ' . $have . '.',
                'level' => $lv, 'cost' => $cost);
        }
        if ($linhThach < $cost['linh_thach_cost']) {
            return array('ok' => false,
                'msg' => 'Linh Thạch không đủ: cần ' . number_format($cost['linh_thach_cost'])
                    . ', có ' . number_format($linhThach) . '.',
                'level' => $lv, 'cost' => $cost);
        }

        $dblj->beginTransaction();
        try {
            $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $stmt->execute(array($cost['material_amount'], $sid, $cost['material_id'], $cost['material_amount']));
            if ($stmt->rowCount() !== 1) throw new Exception('mat Huyet Thach');

            $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ?")
                ->execute(array($cost['linh_thach_cost'], $sid));

            $dblj->prepare("INSERT INTO player_huyetmach_vessel (sid, vessel_id, level, updated_at)
                VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE level = ?, updated_at = NOW()")
                ->execute(array($sid, $vid, $lv + 1, $lv + 1));

            $dblj->commit();
        } catch (Exception $e) {
            $dblj->rollBack();
            return array('ok' => false, 'msg' => 'Luyen mach that bai, thu lai sau.', 'level' => $lv, 'cost' => $cost);
        }

        player_cache_clear($sid);
        return array('ok' => true,
            'msg' => 'Luyện ' . $def['name'] . ' thành công — cấp ' . ($lv + 1) . '.',
            'level' => $lv + 1, 'cost' => $cost);
    }
}

/** ===== MANG MACH HUYET: cap mach + tong hop chi so ===== */
if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_levels')) {
    function huyetmach_vessel_levels($sid, $dblj){
        $levels = array();
        foreach (huyetmach_vessel_defs() as $id => $def) $levels[intval($id)] = 0;
        try {
            $stmt = $dblj->prepare("SELECT vessel_id, level FROM player_huyetmach_vessel WHERE sid = ?");
            $stmt->execute(array($sid));
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $vid = intval($row['vessel_id']);
                if (isset($levels[$vid])) $levels[$vid] = max(0, intval($row['level']));
            }
        } catch (Exception $e) { /* bang chua ton tai (production): xem nhu chua luyen */ }
        return $levels;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_total')) {
    function huyetmach_vessel_total($levels){
        $total = 0;
        foreach ($levels as $lv) $total += intval($lv);
        return $total;
    }
}

if (!function_exists(__NAMESPACE__ . '\\huyetmach_vessel_bonuses')) {
    function huyetmach_vessel_bonuses($sid, $dblj){
        $defs  = huyetmach_vessel_defs();
        $all   = huyetmach_vessel_config_all();
        $caps  = isset($all['stat_cap']) ? $all['stat_cap'] : array();
        $stage = 0;
        $stmt = $dblj->prepare("SELECT huyetmach_awaken_stage FROM game1 WHERE sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) $stage = intval($row['huyetmach_awaken_stage']);
        $cap = huyetmach_vessel_cap($stage);

        $levels = huyetmach_vessel_levels($sid, $dblj);
        $total  = huyetmach_vessel_total($levels);
        $ms     = huyetmach_vessel_milestone($total);
        $mult   = 1 + floatval($ms['bonus_percent']) / 100;

        $out = array('atk' => 0, 'hp' => 0, 'crit' => 0, 'lifesteal' => 0,
                     'dodge' => 0, 'pen' => 0, 'reduce' => 0, 'luck' => 0);
        foreach ($defs as $id => $def) {
            $lv = intval($levels[intval($id)]);
            $stat = $def['stat'];
            $val = floatval($def['per_lv']) * $lv;
            if (isset($caps[$stat])) {
                // chi so am: o day chi chan tuyet doi stat_cap; tran cap theo stage do Task 4 chan luc ghi
                $out[$stat] = min(floatval($caps[$stat]), round($out[$stat] + $val, 2));
            } else {
                $out[$stat] = $out[$stat] + round($val * $mult);
            }
        }
        // atk/hp la diem THO cong vao cot INT (ugj/umaxhp) -> chan ve int SAU khi cong don;
        // crit/lifesteal + 4 chi so am van la float (per_lv 0.2/0.4/0.25).
        $out['atk'] = intval($out['atk']);
        $out['hp'] = intval($out['hp']);
        return array_merge($out, array(
            'levels' => $levels, 'total_lv' => $total, 'cap' => $cap, 'stage' => $stage,
            'milestone_name' => $ms['name'], 'milestone_percent' => floatval($ms['bonus_percent']),
            'next_milestone_name' => $ms['next_milestone_name'],
            'next_milestone_percent' => floatval($ms['next_milestone_percent']),
            'next_milestone_lv' => intval($ms['next_milestone_lv']),
        ));
    }
}
