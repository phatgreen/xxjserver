<?php
namespace player;

/**
 * thechat.php - Hệ thống Thể Chất
 *
 * Thể Chất ảnh hưởng:
 * - HP, DEF, Crit (stat bonus)
 * - Hồi phục, Miễn nhiễm, Giảm dmg (passive)
 * - Element bonus (tương tác ngũ hành)
 * - Thần Thông (mở khóa theo phẩm + thức tỉnh)
 */

if (!function_exists(__NAMESPACE__ . '\\thechat_config_path')) {
    function thechat_config_path(){
        $paths = array(
            __DIR__ . '/../config/thechat_config.json',
            __DIR__ . '/thechat_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_config_all')) {
    function thechat_config_all(){
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $file = thechat_config_path();
        if (!is_file($file)) {
            $cache = array();
            return $cache;
        }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $cache = array();
            return $cache;
        }

        // Loại bỏ meta keys (category_names, quality_*, ...) trước khi trả về
        foreach ($data as $k => $v) {
            if (!is_array($v) || !isset($v['id'])) {
                unset($data[$k]);
            }
        }

        $cache = $data;
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_config_meta')) {
    function thechat_config_meta(){
        $all = thechat_config_all();
        $file = thechat_config_path();
        if (!is_file($file)) return array();
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        $meta = array();
        foreach ($data as $k => $v) {
            if (!is_array($v) || !isset($v['id'])) {
                $meta[$k] = $v;
            }
        }
        return $meta;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_category_name')) {
    function thechat_category_name($categoryId){
        $meta = thechat_config_meta();
        $cats = isset($meta['category_names']) ? $meta['category_names'] : array();
        return isset($cats[(string)$categoryId]) ? $cats[(string)$categoryId] : 'Không phân loại';
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_config_get')) {
    function thechat_config_get($id){
        $id = (string)intval($id);
        $all = thechat_config_all();
        return (isset($all[$id]) && is_array($all[$id])) ? $all[$id] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_random')) {
    function thechat_random(){
        $all = thechat_config_all();
        if (empty($all)) {
            return false;
        }

        $totalWeight = 0;
        foreach ($all as $tc) {
            if (!isset($tc['weight'])) continue;
            $totalWeight += floatval($tc['weight']);
        }

        $roll = mt_rand(0, intval($totalWeight * 100)) / 100;
        $cumulative = 0;

        foreach ($all as $tc) {
            if (!isset($tc['weight'])) continue;
            $cumulative += floatval($tc['weight']);
            if ($roll <= $cumulative) {
                return $tc;
            }
        }

        $last = end($all);
        return $last;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_roll')) {
    function thechat_roll(){
        $type = thechat_random();
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

if (!function_exists(__NAMESPACE__ . '\\thechat_roll_min')) {
    function thechat_roll_min($minQuality = 1, $maxQuality = 3){
        $type = thechat_random();
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

if (!function_exists(__NAMESPACE__ . '\\thechat_assign')) {
    function thechat_assign($sid, $dblj, $thechat_id, $quality = 1){
        $thechat_id = intval($thechat_id);
        $quality = intval($quality);
        if ($thechat_id <= 0) return false;
        if ($quality < 1) $quality = 1;
        if ($quality > 10) $quality = 10;

        $config = thechat_config_get($thechat_id);
        if (!$config) return false;

        $stmt = $dblj->prepare("UPDATE game1 SET thechat_id = ?, thechat_quality = ? WHERE sid = ?");
        $ok = $stmt->execute(array($thechat_id, $quality, $sid));
        player_cache_clear($sid);
        return $ok;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_info')) {
    function thechat_info($sid, $dblj){
        $stmt = $dblj->prepare("SELECT thechat_id, thechat_quality, thechat_awaken_stage FROM game1 WHERE sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || empty($row['thechat_id'])) {
            return false;
        }
        $config = thechat_config_get(intval($row['thechat_id']));
        if (!$config) return false;
        $config['_quality'] = intval($row['thechat_quality']);
        $config['_awaken_stage'] = intval($row['thechat_awaken_stage']);
        return $config;
    }
}

/**
 * Tính realm scale multiplier.
 * jingjieValue = realm step (0-based index in rangesjj).
 */
if (!function_exists(__NAMESPACE__ . '\\thechat_realm_scale')) {
    function thechat_realm_scale($jingjieValue){
        return 1 + intval($jingjieValue) * 0.15;
    }
}

/**
 * Tính awaken stage multiplier.
 */
if (!function_exists(__NAMESPACE__ . '\\thechat_awaken_mult')) {
    function thechat_awaken_mult($stage){
        return 1 + intval($stage) * 0.2;
    }
}

/**
 * Tính tổng bonus từ thể chất.
 * Trả về array:
 * - stats: array(hp, atk, def, crit)
 * - passive_exp, passive_regen, passive_resist, passive_reduce
 * - element_bonus_percent
 * - quality (1-10)
 * - awaken_stage (0-3)
 * - element, name, color, icon
 */
if (!function_exists(__NAMESPACE__ . '\\thechat_calc_bonuses')) {
    function thechat_calc_bonuses($sid, $dblj){
        // Nhục Thân (6 bộ phận) luôn tính, kể cả khi chưa có Thể Chất.
        $body = thechat_body_bonuses($sid, $dblj);

        $empty = array(
            'stats' => array(
                'hp' => $body['hp'], 'atk' => $body['atk'], 'def' => $body['def'],
                'crit' => $body['crit'], 'lifesteal' => $body['lifesteal']
            ),
            'passive_exp' => $body['exp'],
            'passive_regen' => 0,
            'passive_resist' => 0,
            'passive_reduce' => 0,
            'passive_hp_regen_flat' => 0,
            'element_bonus_percent' => 0,
            'quality' => 1,
            'awaken_stage' => $body['stage'],
            'category_id' => 0,
            'category_name' => '',
            'element' => '',
            'name' => '',
            'color' => '',
            'icon' => '',
            'body' => $body
        );

        $config = thechat_info($sid, $dblj);
        if (!$config) return $empty;

        $quality = isset($config['_quality']) ? intval($config['_quality']) : 1;
        $awakenStage = isset($config['_awaken_stage']) ? intval($config['_awaken_stage']) : 0;
        $baseStats = isset($config['base_stats']) ? $config['base_stats'] : array();
        $categoryId = isset($config['category_id']) ? intval($config['category_id']) : 0;
        $categoryName = isset($config['category_name']) ? $config['category_name'] : '';

        $realmScale = thechat_realm_scale(isset($GLOBALS['player']) ? $GLOBALS['player']->jingjieValue : 0);
        $awakenMult = thechat_awaken_mult($awakenStage);

        $finalHP = round(floatval(isset($baseStats['hp']) ? $baseStats['hp'] : 0) * $quality * $realmScale * $awakenMult) + $body['hp'];
        $finalATK = round(floatval(isset($baseStats['atk']) ? $baseStats['atk'] : 0) * $quality * $realmScale * $awakenMult) + $body['atk'];
        $finalDEF = round(floatval(isset($baseStats['def']) ? $baseStats['def'] : 0) * $quality * $realmScale * $awakenMult) + $body['def'];
        $finalCrit = round(floatval(isset($baseStats['crit']) ? $baseStats['crit'] : 0) * $quality * $realmScale * $awakenMult) + $body['crit'];

        $passiveExp = floatval(isset($config['passive_exp']) ? $config['passive_exp'] : 0) + $body['exp'];
        $passiveRegen = floatval(isset($config['passive_regen']) ? $config['passive_regen'] : 0);
        $passiveResist = floatval(isset($config['passive_resist']) ? $config['passive_resist'] : 0);
        $passiveReduce = floatval(isset($config['passive_reduce']) ? $config['passive_reduce'] : 0);
        $passiveHpRegenFlat = floatval(isset($config['passive_hp_regen_flat']) ? $config['passive_hp_regen_flat'] : 0);
        $elementBonus = floatval(isset($config['element_bonus_percent']) ? $config['element_bonus_percent'] : 0);

        return array(
            'stats' => array(
                'hp' => $finalHP, 'atk' => $finalATK, 'def' => $finalDEF,
                'crit' => $finalCrit, 'lifesteal' => $body['lifesteal']
            ),
            'passive_exp' => $passiveExp,
            'passive_regen' => $passiveRegen,
            'passive_resist' => $passiveResist,
            'passive_reduce' => $passiveReduce,
            'passive_hp_regen_flat' => $passiveHpRegenFlat,
            'element_bonus_percent' => $elementBonus,
            'quality' => $quality,
            'awaken_stage' => $awakenStage,
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'element' => isset($config['element']) ? $config['element'] : '',
            'name' => isset($config['name']) ? $config['name'] : '',
            'color' => isset($config['color']) ? $config['color'] : '#9e9e9e',
            'icon' => isset($config['icon']) ? $config['icon'] : '🧑',
            'body' => $body
        );
    }
}

/**
 * Upgrade config
 */
if (!function_exists(__NAMESPACE__ . '\\thechat_upgrade_config_path')) {
    function thechat_upgrade_config_path(){
        $paths = array(
            __DIR__ . '/../config/thechat_upgrade_config.json',
            __DIR__ . '/thechat_upgrade_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_upgrade_config_all')) {
    function thechat_upgrade_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = thechat_upgrade_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        $cache = is_array($data) ? $data : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_get_upgrade_info')) {
    function thechat_get_upgrade_info($currentQuality){
        $all = thechat_upgrade_config_all();
        $upgrades = isset($all['upgrades']) ? $all['upgrades'] : array();
        foreach ($upgrades as $u) {
            if (intval($u['from_quality']) === intval($currentQuality)) {
                return $u;
            }
        }
        return false;
    }
}

/**
 * Nâng cấp phẩm chất thể chất.
 * Trả về array('ok' => bool, 'msg' => string).
 */
if (!function_exists(__NAMESPACE__ . '\\thechat_upgrade')) {
    function thechat_upgrade($sid, $dblj){
        $player = getplayer($sid, $dblj);
        $currentQuality = intval($player->thechat_quality);
        $currentId = intval($player->thechat_id);

        if ($currentId <= 0) {
            return array('ok' => false, 'msg' => 'Bạn chưa có Thể Chất.');
        }

        $upgradeInfo = thechat_get_upgrade_info($currentQuality);
        if (!$upgradeInfo) {
            return array('ok' => false, 'msg' => 'Đã đạt phẩm chất tối đa.');
        }

        $linhThachCost = intval($upgradeInfo['linh_thach_cost']);
        $materialId = intval($upgradeInfo['material_id']);
        $materialAmount = intval($upgradeInfo['material_amount']);
        $extraMaterialId = isset($upgradeInfo['extra_material_id']) ? intval($upgradeInfo['extra_material_id']) : 0;
        $extraMaterialAmount = isset($upgradeInfo['extra_material_amount']) ? intval($upgradeInfo['extra_material_amount']) : 0;
        $successRate = floatval($upgradeInfo['success_rate']);

        if ($player->uyxb < $linhThachCost) {
            return array('ok' => false, 'msg' => 'Linh Thạch không đủ. Cần ' . number_format($linhThachCost) . '.');
        }

        $stmt = $dblj->prepare("SELECT djid, djsum FROM playerdaoju WHERE sid = ? AND djid = ?");
        $stmt->execute(array($sid, $materialId));
        $matRow = $stmt->fetch(\PDO::FETCH_ASSOC);
        $matHave = $matRow ? intval($matRow['djsum']) : 0;
        if ($matHave < $materialAmount) {
            $matName = thechat_get_material_name($materialId);
            return array('ok' => false, 'msg' => $matName . ' không đủ. Cần ' . $materialAmount . ', có ' . $matHave . '.');
        }

        if ($extraMaterialId > 0) {
            $stmt2 = $dblj->prepare("SELECT djid, djsum FROM playerdaoju WHERE sid = ? AND djid = ?");
            $stmt2->execute(array($sid, $extraMaterialId));
            $extraRow = $stmt2->fetch(\PDO::FETCH_ASSOC);
            $extraHave = $extraRow ? intval($extraRow['djsum']) : 0;
            if ($extraHave < $extraMaterialAmount) {
                $extraName = thechat_get_material_name($extraMaterialId);
                return array('ok' => false, 'msg' => $extraName . ' không đủ. Cần ' . $extraMaterialAmount . ', có ' . $extraHave . '.');
            }
        }

        $toQuality = intval($upgradeInfo['to_quality']);

        $stmtLy = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
        $stmtLy->execute(array($linhThachCost, $sid, $linhThachCost));

        $stmtM1 = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
        $stmtM1->execute(array($materialAmount, $sid, $materialId, $materialAmount));

        if ($extraMaterialId > 0) {
            $stmtM2 = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $stmtM2->execute(array($extraMaterialAmount, $sid, $extraMaterialId, $extraMaterialAmount));
        }

        $roll = mt_rand(1, 100);
        $success = ($roll <= $successRate) ? 1 : 0;

        if ($success) {
            $stmtUp = $dblj->prepare("UPDATE game1 SET thechat_quality = ? WHERE sid = ?");
            $stmtUp->execute(array($toQuality, $sid));
        }

        $stmtLog = $dblj->prepare("INSERT INTO player_thechat_log (sid, from_quality, to_quality, success, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmtLog->execute(array($sid, $currentQuality, $toQuality, $success));

        player_cache_clear($sid);

        if ($success) {
            $toName = isset($upgradeInfo['to_name']) ? $upgradeInfo['to_name'] : 'Phẩm ' . $toQuality;
            return array('ok' => true, 'msg' => 'Nâng cấp thành công! Thể Chất đã lên ' . $toName . '.', 'new_quality' => $toQuality);
        } else {
            return array('ok' => false, 'msg' => 'Nâng cấp thất bại! Vật phẩm đã mất.', 'new_quality' => $currentQuality);
        }
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_get_material_name')) {
    function thechat_get_material_name($djid){
        $names = array(
            100 => 'Tinh Thể Đá',
            101 => 'Huyền Thạch',
            102 => 'Địa Linh Ngọc',
            103 => 'Thiên Cơ Đá',
            104 => 'Thánh Thạch',
            105 => 'Tiên Lộ',
            106 => 'Thần Thạch',
            107 => 'Đế Lệnh',
            108 => 'Đạo Nguyên'
        );
        return isset($names[$djid]) ? $names[$djid] : 'Vật phẩm';
    }
}

/**
 * Awakening config
 */
if (!function_exists(__NAMESPACE__ . '\\thechat_awakening_config_path')) {
    function thechat_awakening_config_path(){
        $paths = array(
            __DIR__ . '/../config/thechat_awakening_config.json',
            __DIR__ . '/thechat_awakening_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_awakening_config_all')) {
    function thechat_awakening_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = thechat_awakening_config_path();
        if (!is_file($file)) { $cache = array(); return $cache; }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        $cache = is_array($data) ? $data : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_get_awakening_info')) {
    function thechat_get_awakening_info($currentStage){
        $all = thechat_awakening_config_all();
        $stages = isset($all['stages']) ? $all['stages'] : array();
        foreach ($stages as $s) {
            if (intval($s['from_stage']) === intval($currentStage)) {
                return $s;
            }
        }
        return false;
    }
}

/**
 * Thức tỉnh thể chất.
 * Trả về array('ok' => bool, 'msg' => string).
 */
if (!function_exists(__NAMESPACE__ . '\\thechat_awaken')) {
    function thechat_awaken($sid, $dblj){
        $player = getplayer($sid, $dblj);
        $currentStage = intval($player->thechat_awaken_stage);
        $currentId = intval($player->thechat_id);

        if ($currentId <= 0) {
            return array('ok' => false, 'msg' => 'Bạn chưa có Thể Chất.');
        }

        $awakenInfo = thechat_get_awakening_info($currentStage);
        if (!$awakenInfo) {
            return array('ok' => false, 'msg' => 'Đã thức tỉnh tối đa.');
        }

        $realmMinLevel = intval($awakenInfo['realm_min_level']);
        if ($player->ulv < $realmMinLevel) {
            return array('ok' => false, 'msg' => 'Cần đạt cảnh giới ' . $awakenInfo['realm_require'] . ' (cấp ' . $realmMinLevel . '+).');
        }

        $materialId = isset($awakenInfo['material_id']) ? intval($awakenInfo['material_id']) : 0;
        $materialAmount = isset($awakenInfo['material_amount']) ? intval($awakenInfo['material_amount']) : 0;

        if ($materialId > 0 && $materialAmount > 0) {
            $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
            $stmt->execute(array($sid, $materialId));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $have = $row ? intval($row['djsum']) : 0;
            if ($have < $materialAmount) {
                $matName = thechat_get_material_name($materialId);
                return array('ok' => false, 'msg' => $matName . ' không đủ. Cần ' . $materialAmount . ', có ' . $have . '.');
            }

            $stmtConsume = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $stmtConsume->execute(array($materialAmount, $sid, $materialId, $materialAmount));
        }

        $toStage = intval($awakenInfo['to_stage']);
        $stmtUp = $dblj->prepare("UPDATE game1 SET thechat_awaken_stage = ? WHERE sid = ?");
        $stmtUp->execute(array($toStage, $sid));

        $stmtLog = $dblj->prepare("INSERT INTO player_thechat_awaken_log (sid, from_stage, to_stage, success, created_at) VALUES (?, ?, ?, 1, NOW())");
        $stmtLog->execute(array($sid, $currentStage, $toStage));

        player_cache_clear($sid);

        $toName = isset($awakenInfo['to_name']) ? $awakenInfo['to_name'] : 'Stage ' . $toStage;
        return array('ok' => true, 'msg' => 'Thức tỉnh thành công! Thể Chất đã lên ' . $toName . '.', 'new_stage' => $toStage);
    }
}

/**
 * Lấy quality name từ quality number.
 */
if (!function_exists(__NAMESPACE__ . '\\thechat_quality_name')) {
    function thechat_quality_name($quality){
        $map = array(
            1 => 'Phàm Phẩm', 2 => 'Linh Phẩm', 3 => 'Huyền Phẩm',
            4 => 'Địa Phẩm', 5 => 'Thiên Phẩm', 6 => 'Thánh Phẩm',
            7 => 'Tiên Phẩm', 8 => 'Thần Phẩm', 9 => 'Đế Phẩm', 10 => 'Đạo Phẩm'
        );
        return isset($map[$quality]) ? $map[$quality] : 'Phàm Phẩm';
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_quality_color')) {
    function thechat_quality_color($quality){
        $map = array(
            1 => '#9e9e9e', 2 => '#4caf50', 3 => '#2196f3',
            4 => '#9c27b0', 5 => '#ff9800', 6 => '#ff5722',
            7 => '#f44336', 8 => '#ffd700', 9 => '#7c4dff', 10 => '#e0e0e0'
        );
        return isset($map[$quality]) ? $map[$quality] : '#9e9e9e';
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_quality_icon')) {
    function thechat_quality_icon($quality){
        $map = array(
            1 => '⚪', 2 => '🟢', 3 => '🔵',
            4 => '🟣', 5 => '🟡', 6 => '🟠',
            7 => '🔴', 8 => '✨', 9 => '🌌', 10 => '☯'
        );
        return isset($map[$quality]) ? $map[$quality] : '⚪';
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_awaken_name')) {
    function thechat_awaken_name($stage){
        $map = array(
            0 => 'Phàm Cốt',
            1 => 'Kim Cốt',
            2 => 'Ngọc Cốt',
            3 => 'Linh Cốt',
            4 => 'Kiếp Cốt',
            5 => 'Tiên Cốt'
        );
        return isset($map[$stage]) ? $map[$stage] : 'Phàm Cốt';
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_awaken_color')) {
    function thechat_awaken_color($stage){
        $map = array(
            0 => '#8d99ae',
            1 => '#ffd166',
            2 => '#4dd0e1',
            3 => '#b388ff',
            4 => '#ff7043',
            5 => '#fff3b0'
        );
        return isset($map[$stage]) ? $map[$stage] : '#8d99ae';
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_awaken_icon')) {
    function thechat_awaken_icon($stage){
        $map = array(
            0 => '🌑',
            1 => '🌕',
            2 => '💎',
            3 => '🔮',
            4 => '🌀',
            5 => '🐉'
        );
        return isset($map[$stage]) ? $map[$stage] : '🌑';
    }
}

/* =====================================================
 * NHỤC THÂN — 6 bộ phận tôi luyện + 6 bậc Cốt Cách
 * Config: class/config/thechat_body_config.json
 * DB: player_thechat_node (sid, node_id, node_lv)
 * ===================================================== */

if (!function_exists(__NAMESPACE__ . '\\thechat_body_config_path')) {
    function thechat_body_config_path(){
        $paths = array(
            __DIR__ . '/../config/thechat_body_config.json',
            __DIR__ . '/thechat_body_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) return $path;
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_config_all')) {
    function thechat_body_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $file = thechat_body_config_path();
        if (!is_file($file)) { $cache = array('nodes' => array(), 'tiers' => array(), 'refine' => array(), 'milestones' => array()); return $cache; }
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data)) $data = array();
        $cache = array(
            'nodes' => isset($data['nodes']) ? $data['nodes'] : array(),
            'tiers' => isset($data['tiers']) ? $data['tiers'] : array(),
            'refine' => isset($data['refine']) ? $data['refine'] : array(),
            'milestones' => isset($data['milestones']) ? $data['milestones'] : array(),
            'stat_labels' => isset($data['stat_labels']) ? $data['stat_labels'] : array()
        );
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_node')) {
    function thechat_body_node($nodeId){
        $nodes = thechat_body_config_all();
        $nodes = isset($nodes['nodes']) ? $nodes['nodes'] : array();
        $nodeId = (string)intval($nodeId);
        return (isset($nodes[$nodeId]) && is_array($nodes[$nodeId])) ? $nodes[$nodeId] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_nodes')) {
    function thechat_body_nodes(){
        $cfg = thechat_body_config_all();
        return $cfg['nodes'];
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_stat_label')) {
    function thechat_body_stat_label($stat){
        $cfg = thechat_body_config_all();
        return isset($cfg['stat_labels'][$stat]) ? $cfg['stat_labels'][$stat] : $stat;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_tier')) {
    function thechat_body_tier($stage){
        $cfg = thechat_body_config_all();
        $stage = (string)intval($stage);
        return (isset($cfg['tiers'][$stage]) && is_array($cfg['tiers'][$stage])) ? $cfg['tiers'][$stage] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_tier_cap')) {
    function thechat_body_tier_cap($stage){
        $tier = thechat_body_tier($stage);
        return $tier ? intval($tier['lv_cap']) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_tier_max_stage')) {
    function thechat_body_tier_max_stage(){
        $cfg = thechat_body_config_all();
        $max = 0;
        foreach ($cfg['tiers'] as $t) {
            if (intval($t['stage']) > $max) $max = intval($t['stage']);
        }
        return $max;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_refine_cost')) {
    /** Chi phí nâng 1 bộ phận từ cấp $lv lên $lv+1. */
    function thechat_body_refine_cost($lv){
        $cfg = thechat_body_config_all();
        $r = $cfg['refine'];
        $lv = max(0, intval($lv));
        $step = isset($r['material_per_lv']) ? max(1, intval($r['material_per_lv'])) : 5;
        return array(
            'material_id' => intval($r['material_id']),
            'material_amount' => max(1, intval($r['material_base']) + intval(floor($lv / $step))),
            'linh_thach_cost' => max(1, intval($r['linh_thach_base']) + $lv * intval($r['linh_thach_per_lv']))
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_levels')) {
    /** Cấp 6 bộ phận của người chơi: array(1..6 => lv). */
    function thechat_body_levels($sid, $dblj){
        if (!isset($GLOBALS['__thechat_body_levels_cache'])) $GLOBALS['__thechat_body_levels_cache'] = array();
        $sid = (string)$sid;
        if (isset($GLOBALS['__thechat_body_levels_cache'][$sid])) return $GLOBALS['__thechat_body_levels_cache'][$sid];

        $levels = array();
        foreach (thechat_body_nodes() as $id => $node) {
            $levels[intval($id)] = 0;
        }
        $stmt = $dblj->prepare("SELECT node_id, node_lv FROM player_thechat_node WHERE sid = ?");
        $stmt->execute(array($sid));
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $id = intval($row['node_id']);
            if (isset($levels[$id])) $levels[$id] = max(0, intval($row['node_lv']));
        }
        $GLOBALS['__thechat_body_levels_cache'][$sid] = $levels;
        return $levels;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_total_lv')) {
    function thechat_body_total_lv($levels){
        $total = 0;
        foreach ($levels as $lv) $total += intval($lv);
        return $total;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_milestone')) {
    /** Mốc Kích Hoạt đang ăn được, kèm mốc kế tiếp để hiển thị. */
    function thechat_body_milestone($totalLv){
        $cfg = thechat_body_config_all();
        $totalLv = intval($totalLv);
        $current = false;
        $next = false;
        foreach ($cfg['milestones'] as $m) {
            if ($totalLv >= intval($m['total_lv'])) {
                $current = $m;
            } elseif ($next === false) {
                $next = $m;
            }
        }
        return array('current' => $current, 'next' => $next);
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_bonuses')) {
    /**
     * Tổng chỉ số Nhục Thân. Cấp càng cao chỉ số càng lớn, chỉ số nhân theo mốc Kích Hoạt.
     * KHONG nhan canh gioi: truyen thong nhan scale cua thechat_realm_scale() dang doc
     * gia tri $GLOBALS['player'] chua duoc gan o lan goi dau.
     */
    function thechat_body_bonuses($sid, $dblj){
        if (isset($GLOBALS['__thechat_body_bonuses_cache'][$sid])) return $GLOBALS['__thechat_body_bonuses_cache'][$sid];
        $cfg = thechat_body_config_all();
        $out = array(
            'hp' => 0, 'atk' => 0, 'def' => 0, 'crit' => 0, 'lifesteal' => 0, 'exp' => 0,
            'levels' => array(), 'total_lv' => 0, 'cap' => 0, 'stage' => 0,
            'milestone_name' => '', 'milestone_percent' => 0,
            'next_milestone_lv' => 0, 'next_milestone_name' => '', 'next_milestone_percent' => 0
        );
        if (empty($cfg['nodes'])) return $out;

        $levels = thechat_body_levels($sid, $dblj);
        $playerStage = 0;
        $stmt = $dblj->prepare("SELECT thechat_awaken_stage FROM game1 WHERE sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) $playerStage = intval($row['thechat_awaken_stage']);

        $out['stage'] = $playerStage;
        $total = thechat_body_total_lv($levels);
        $ms = thechat_body_milestone($total);
        $mult = 1 + ($ms['current'] ? floatval($ms['current']['bonus_percent']) / 100 : 0);

        foreach ($cfg['nodes'] as $node) {
            $lv = isset($levels[intval($node['id'])]) ? intval($levels[intval($node['id'])]) : 0;
            if ($lv <= 0) continue;
            $stat = $node['stat'];
            if (!isset($out[$stat])) continue;
            $gain = floatval($node['per_lv']) * $lv * $mult;
            $out[$stat] = (in_array($stat, array('crit', 'lifesteal', 'exp')))
                ? round($out[$stat] + $gain, 2)
                : $out[$stat] + round($gain);
        }

        $out['levels'] = $levels;
        $out['total_lv'] = $total;
        $out['cap'] = thechat_body_tier_cap($playerStage);
        if ($ms['current']) {
            $out['milestone_name'] = $ms['current']['name'];
            $out['milestone_percent'] = floatval($ms['current']['bonus_percent']);
        }
        if ($ms['next']) {
            $out['next_milestone_lv'] = intval($ms['next']['total_lv']);
            $out['next_milestone_name'] = $ms['next']['name'];
            $out['next_milestone_percent'] = floatval($ms['next']['bonus_percent']);
        }
        $GLOBALS['__thechat_body_bonuses_cache'][$sid] = $out;
        return $out;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_daoju_have')) {
    function thechat_body_daoju_have($sid, $dblj, $djid){
        $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
        $stmt->execute(array($sid, intval($djid)));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? intval($row['djsum']) : 0;
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_refine')) {
    /** Luyện hóa: +1 cấp cho một bộ phận. Thành công 100%, tốn Tinh Thể Đá + Linh Thạch. */
    function thechat_body_refine($sid, $dblj, $nodeId){
        $node = thechat_body_node($nodeId);
        if (!$node) return array('ok' => false, 'msg' => 'Không có bộ phận này.');

        $player = getplayer($sid, $dblj);
        $stage = intval($player->thechat_awaken_stage);
        $cap = thechat_body_tier_cap($stage);
        $levels = thechat_body_levels($sid, $dblj);
        $lv = isset($levels[intval($node['id'])]) ? intval($levels[intval($node['id'])]) : 0;

        if ($lv >= $cap) {
            $tier = thechat_body_tier($stage);
            $tierName = $tier ? $tier['name'] : 'Cốt Cách';
            return array('ok' => false, 'msg' => $node['name'] . ' đã đạt trần ' . $cap . ' cấp của ' . $tierName . '. Phải đột phá Cốt Cách mới luyện tiếp được.');
        }

        $cost = thechat_body_refine_cost($lv);
        $have = thechat_body_daoju_have($sid, $dblj, $cost['material_id']);
        if ($have < $cost['material_amount']) {
            $matName = thechat_get_material_name($cost['material_id']);
            return array('ok' => false, 'msg' => $matName . ' không đủ. Cần ' . $cost['material_amount'] . ', có ' . $have . '.');
        }
        if (intval($player->uyxb) < $cost['linh_thach_cost']) {
            return array('ok' => false, 'msg' => 'Linh Thạch không đủ. Cần ' . number_format($cost['linh_thach_cost']) . '.');
        }

        $dblj->beginTransaction();
        try {
            $stmtLy = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
            $stmtLy->execute(array($cost['linh_thach_cost'], $sid, $cost['linh_thach_cost']));
            if ($stmtLy->rowCount() === 0) throw new Exception('linh_thach');

            $stmtDj = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $stmtDj->execute(array($cost['material_amount'], $sid, $cost['material_id'], $cost['material_amount']));
            if ($stmtDj->rowCount() === 0) throw new Exception('daoju');

            $stmtIns = $dblj->prepare(
                "INSERT INTO player_thechat_node (sid, node_id, node_lv, updated_at) VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE node_lv = node_lv + 1, updated_at = NOW()"
            );
            $stmtIns->execute(array($sid, intval($node['id']), 1));
            $dblj->commit();
        } catch (Exception $e) {
            if ($dblj->inTransaction()) $dblj->rollBack();
            return array('ok' => false, 'msg' => 'Luyện hóa thất bại, tài nguyên chưa bị trừ. Thử lại.');
        }

        player_cache_clear($sid);
        thechat_body_levels_flush();

        return array(
            'ok' => true,
            'msg' => $node['name'] . ' lên cấp ' . ($lv + 1) . '.',
            'node_id' => intval($node['id']),
            'new_lv' => $lv + 1
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_levels_flush')) {
    /** Xóa cache cấp bộ phận trong request (gọi sau khi luyện hóa / đột phá). */
    function thechat_body_levels_flush(){
        $GLOBALS['__thechat_body_levels_cache'] = array();
        $GLOBALS['__thechat_body_bonuses_cache'] = array();
    }
}

if (!function_exists(__NAMESPACE__ . '\\thechat_body_break')) {
    /** Đột phá Cốt Cách: mở bậc kế, nâng trần cấp của 6 bộ phận. */
    function thechat_body_break($sid, $dblj){
        $player = getplayer($sid, $dblj);
        $stage = intval($player->thechat_awaken_stage);
        $next = thechat_body_tier($stage + 1);
        if (!$next) return array('ok' => false, 'msg' => 'Cốt Cách đã đạt cực hạn, không còn bậc kế tiếp.');

        $levels = thechat_body_levels($sid, $dblj);
        $total = thechat_body_total_lv($levels);
        if ($total < intval($next['total_lv_require'])) {
            return array('ok' => false, 'msg' => 'Tổng cấp Lục Bộ Phận chưa đủ. Cần ' . intval($next['total_lv_require']) . ', đang có ' . $total . '.');
        }
        if (intval($player->ulv) < intval($next['realm_min_level'])) {
            return array('ok' => false, 'msg' => 'Cảnh giới chưa tới. Cần cấp ' . intval($next['realm_min_level']) . ', đang ở cấp ' . intval($player->ulv) . '.');
        }

        $matId = intval($next['material_id']);
        $matAmt = intval($next['material_amount']);
        $ltCost = intval($next['linh_thach_cost']);
        if ($matId > 0 && $matAmt > 0) {
            $have = thechat_body_daoju_have($sid, $dblj, $matId);
            if ($have < $matAmt) {
                $matName = thechat_get_material_name($matId);
                return array('ok' => false, 'msg' => $matName . ' không đủ. Cần ' . $matAmt . ', có ' . $have . '.');
            }
        }
        if (intval($player->uyxb) < $ltCost) {
            return array('ok' => false, 'msg' => 'Linh Thạch không đủ. Cần ' . number_format($ltCost) . '.');
        }

        $toStage = intval($next['stage']);
        $dblj->beginTransaction();
        try {
            if ($ltCost > 0) {
                $stmtLy = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
                $stmtLy->execute(array($ltCost, $sid, $ltCost));
                if ($stmtLy->rowCount() === 0) throw new Exception('linh_thach');
            }
            if ($matId > 0 && $matAmt > 0) {
                $stmtDj = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
                $stmtDj->execute(array($matAmt, $sid, $matId, $matAmt));
                if ($stmtDj->rowCount() === 0) throw new Exception('daoju');
            }
            $stmtUp = $dblj->prepare("UPDATE game1 SET thechat_awaken_stage = ? WHERE sid = ? AND thechat_awaken_stage = ?");
            $stmtUp->execute(array($toStage, $sid, $stage));
            if ($stmtUp->rowCount() === 0) throw new Exception('stage');

            $stmtLog = $dblj->prepare("INSERT INTO player_thechat_awaken_log (sid, from_stage, to_stage, success, created_at) VALUES (?, ?, ?, 1, NOW())");
            $stmtLog->execute(array($sid, $stage, $toStage));
            $dblj->commit();
        } catch (Exception $e) {
            if ($dblj->inTransaction()) $dblj->rollBack();
            return array('ok' => false, 'msg' => 'Đột phá thất bại, tài nguyên chưa bị trừ. Thử lại.');
        }

        player_cache_clear($sid);
        thechat_body_levels_flush();

        return array('ok' => true, 'msg' => 'Đột phá thành công! Cốt Cách đạt ' . $next['name'] . ', trần mỗi bộ phận là ' . intval($next['lv_cap']) . ' cấp.', 'new_stage' => $toStage);
    }
}
