<?php
namespace player;

/**
 * congphap.php - Hệ thống Công Pháp (Passive Buff)
 * Công pháp là các phương pháp tu luyện bị động, buff trạng thái cho nhân vật.
 * Khác với võ công (chiến đấu), công pháp luôn active khi trang bị.
 */

if (!function_exists(__NAMESPACE__ . '\\congphap_config_path')) {
    function congphap_config_path(){
        $paths = array(
            __DIR__ . '/../config/congphap_config.json',
            __DIR__ . '/congphap_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\congphap_config_all')) {
    function congphap_config_all(){
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $file = congphap_config_path();
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
        $cache = $data;
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\congphap_config_get')) {
    function congphap_config_get($cpid){
        $cpid = (string)intval($cpid);
        $all = congphap_config_all();
        return (isset($all[$cpid]) && is_array($all[$cpid])) ? $all[$cpid] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\hydrate_congphap_config_to_object')) {
    function hydrate_congphap_config_to_object($object, $config){
        if (!$config || !is_array($config)) {
            return $object;
        }
        foreach ($config as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }
}

/**
 * Lấy thông tin công pháp của player từ DB + merge config JSON.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_cx')) {
    function congphap_cx($cpid, $sid, $dblj, $forceRefresh = false){
        static $cache = array();

        $cpid = intval($cpid);
        if ($cpid <= 0 || $sid === '') {
            return false;
        }

        $cacheKey = $sid . ':' . $cpid;
        if (!$forceRefresh && isset($cache[$cacheKey])) {
            return $cache[$cacheKey] ? clone $cache[$cacheKey] : false;
        }

        $stmt = $dblj->prepare('SELECT * FROM playercongphap WHERE cpid = ? AND sid = ? LIMIT 1');
        $stmt->execute(array($cpid, $sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $cache[$cacheKey] = false;
            return false;
        }

        $cp = new \stdClass();
        hydrate_object_from_row($cp, $row);

        $config = congphap_config_get($cpid);
        hydrate_congphap_config_to_object($cp, $config);

        $cache[$cacheKey] = clone $cp;
        return $cache[$cacheKey];
    }
}

/**
 * Lấy tất cả công pháp của player.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_sl')) {
    function congphap_sl($sid, $dblj){
        $stmt = $dblj->prepare('SELECT * FROM playercongphap WHERE sid = ?');
        $stmt->execute(array($sid));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = array();
        foreach ($rows as $row) {
            $cp = new \stdClass();
            hydrate_object_from_row($cp, $row);
            $config = congphap_config_get($cp->cpid);
            hydrate_congphap_config_to_object($cp, $config);
            $result[] = $cp;
        }
        return $result;
    }
}

/**
 * Lấy danh sách công pháp đang equip (cpequipped = 1).
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_equipped')) {
    function congphap_equipped($sid, $dblj){
        $all = congphap_sl($sid, $dblj);
        $equipped = array();
        foreach ($all as $cp) {
            if (isset($cp->cpequipped) && intval($cp->cpequipped) == 1) {
                $equipped[] = $cp;
            }
        }
        return $equipped;
    }
}

/**
 * Nhặt/học công pháp mới.
 * Nếu đã có: tăng cpexp.
 * Nếu chưa có: thêm vào playercongphap.
 */
if (!function_exists(__NAMESPACE__ . '\\cqcp')) {
    function cqcp($cpid, $sid, $dblj){
        $cpid = intval($cpid);
        if ($cpid <= 0) {
            return false;
        }

        $config = congphap_config_get($cpid);
        if (!$config) {
            return false;
        }

        $existing = congphap_cx($cpid, $sid, $dblj);
        if ($existing) {
            // Đã có → thêm exp công pháp
            $addExp = max(10, intval($config['max_level'] * 5));
            return congphap_add_exp($cpid, $sid, $dblj, $addExp);
        }

        $player = getplayer($sid, $dblj);

        $stmt = $dblj->prepare('
            INSERT INTO playercongphap (cpid, sid, cpdj, cpexp, cpexpmax, cpequipped)
            VALUES (?, ?, 1, 0, 100, 0)
        ');
        return $stmt->execute(array($cpid, $sid));
    }
}

/**
 * Thêm exp cho công pháp.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_add_exp')) {
    function congphap_add_exp($cpid, $sid, $dblj, $amount){
        $cpid = intval($cpid);
        $amount = max(0, intval($amount));
        if ($cpid <= 0 || $amount <= 0) {
            return array('add' => 0, 'now' => 0, 'max' => 0, 'full' => false, 'levelup' => false);
        }

        $cp = congphap_cx($cpid, $sid, $dblj);
        if (!$cp) {
            return array('add' => 0, 'now' => 0, 'max' => 0, 'full' => false, 'levelup' => false);
        }

        $now = intval($cp->cpexp);
        $max = max(1, intval($cp->cpexpmax));
        $dj = max(1, intval($cp->cpdj));
        $maxLevel = max(1, intval($cp->max_level));

        // Đã max level
        if ($dj >= $maxLevel && $now >= $max) {
            return array('add' => 0, 'now' => $now, 'max' => $max, 'full' => true, 'levelup' => false);
        }

        $new = $now + $amount;
        $levelup = false;

        // Kiểm tra level up
        if ($new >= $max && $dj < $maxLevel) {
            $dj++;
            $new = $new - $max;
            // Tăng max exp cho level mới: base * (1 + level * 0.3)
            $max = max(100, intval($max * 1.3));
            $levelup = true;

            $stmt = $dblj->prepare('UPDATE playercongphap SET cpdj = ?, cpexp = ?, cpexpmax = ? WHERE cpid = ? AND sid = ?');
            $stmt->execute(array($dj, $new, $max, $cpid, $sid));
        } else {
            $new = min($max, $new);
            $stmt = $dblj->prepare('UPDATE playercongphap SET cpexp = ? WHERE cpid = ? AND sid = ?');
            $stmt->execute(array($new, $cpid, $sid));
        }

        player_cache_clear($sid);

        return array(
            'add' => $amount,
            'now' => $new,
            'max' => $max,
            'full' => ($new >= $max),
            'levelup' => $levelup,
            'new_level' => $dj
        );
    }
}

if (!function_exists(__NAMESPACE__ . '\\congphap_add_exp_to_equipped')) {
    function congphap_add_exp_to_equipped($sid, $dblj, $amount) {
        $amount = max(0, intval($amount));
        if ($amount <= 0) {
            return 0;
        }

        $equipped = congphap_equipped($sid, $dblj);
        if (empty($equipped)) {
            return 0;
        }

        $totalAdded = 0;
        foreach ($equipped as $cp) {
            if (!isset($cp->cpid) || intval($cp->cpid) <= 0) {
                continue;
            }
            $result = congphap_add_exp(intval($cp->cpid), $sid, $dblj, $amount);
            $totalAdded += isset($result['add']) ? intval($result['add']) : 0;
        }

        return $totalAdded;
    }
}

/**
 * Trang bị / bỏ trang bị công pháp.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_equip')) {
    function congphap_equip($cpid, $sid, $dblj, $equip = true){
        $cpid = intval($cpid);
        if ($cpid <= 0) return false;

        $val = $equip ? 1 : 0;
        $stmt = $dblj->prepare('UPDATE playercongphap SET cpequipped = ? WHERE cpid = ? AND sid = ?');
        $result = $stmt->execute(array($val, $cpid, $sid));
        player_cache_clear($sid);
        return $result;
    }
}

/**
 * Bỏ trang bị tất cả công pháp.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_unequip_all')) {
    function congphap_unequip_all($sid, $dblj){
        $stmt = $dblj->prepare('UPDATE playercongphap SET cpequipped = 0 WHERE sid = ?');
        $result = $stmt->execute(array($sid));
        player_cache_clear($sid);
        return $result;
    }
}

/**
 * Xóa công pháp.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_delete')) {
    function congphap_delete($cpid, $sid, $dblj){
        $cpid = intval($cpid);
        if ($cpid <= 0) return false;

        $stmt = $dblj->prepare('DELETE FROM playercongphap WHERE cpid = ? AND sid = ?');
        $result = $stmt->execute(array($cpid, $sid));
        player_cache_clear($sid);
        return $result;
    }
}

/**
 * Đếm số slot equip hiện tại.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_equip_count')) {
    function congphap_equip_count($sid, $dblj){
        $stmt = $dblj->prepare('SELECT COUNT(*) as cnt FROM playercongphap WHERE sid = ? AND cpequipped = 1');
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? intval($row['cnt']) : 0;
    }
}

/**
 * Tính số slot equip tối đa theo cảnh giới.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_max_equip')) {
    function congphap_max_equip($player){
        $jj = isset($player->jingjieValue) ? intval($player->jingjieValue) : 1;
        if ($jj <= 6)  return 1;  // Nhập Môn → Ngự Khí
        if ($jj <= 11) return 2;  // Luyện Khí → Nguyên Anh
        return 3;                 // Tụ Thần → Đại Thừa
    }
}

/**
 * Tính real value của effect theo level.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_real_value')) {
    function congphap_real_value($config, $level){
        if (is_object($config)) {
            $base = floatval($config->effect_value);
            $max = floatval($config->effect_value_max);
            $lvl = max(1, intval($level));
            $mLvl = max(1, intval($config->max_level));
        } else {
            $base = floatval($config['effect_value']);
            $max = floatval($config['effect_value_max']);
            $lvl = max(1, intval($level));
            $mLvl = max(1, intval($config['max_level']));
        }
        if ($mLvl <= 1) return $max;
        return $base + ($lvl - 1) * ($max - $base) / ($mLvl - 1);
    }
}

/**
 * Tính toán tất cả buff từ công pháp đang equipped.
 * Trả về array các effect và penalty.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_calc_buffs')) {
    function congphap_calc_buffs($sid, $dblj){
        $equipped = congphap_equipped($sid, $dblj);
        $result = array(
            'effects' => array(),
            'absorb_speed' => 0,
            'penalty_atk' => 0,
            'penalty_def' => 0,
        );

        foreach ($equipped as $cp) {
            if (!isset($cp->effect_type) || $cp->effect_type === '') continue;

            $val = congphap_real_value($cp, $cp->cpdj);
            $type = $cp->effect_type;

            $result['effects'][] = array(
                'type' => $type,
                'value' => $val,
                'name' => isset($cp->cpname) ? $cp->cpname : '',
            );

            if ($type === 'absorb_speed') {
                $result['absorb_speed'] += $val;
                $result['penalty_atk'] += $val * floatval($cp->penalty_atk_percent);
                $result['penalty_def'] += $val * floatval($cp->penalty_def_percent);
            }
            if ($type === 'exp_bonus') {
                $result['penalty_atk'] += $val * floatval(isset($cp->penalty_atk_percent) ? $cp->penalty_atk_percent : 0);
                $result['penalty_def'] += $val * floatval(isset($cp->penalty_def_percent) ? $cp->penalty_def_percent : 0);
            }
        }

        return $result;
    }
}

/**
 * Lấy tên hiển thị của effect type.
 */
if (!function_exists(__NAMESPACE__ . '\\congphap_effect_name')) {
    function congphap_effect_name($type){
        $map = array(
            'absorb_speed' => 'Hấp thụ linh khí',
            'exp_bonus'    => 'Tăng tu vi',
            'drop_rate'    => 'Tỉ lệ rơi vật phẩm',
            'stone_bonus'  => 'Linh thạch',
            'mining_bonus' => 'Đào mỏ',
            'heal_bonus'   => 'Hồi phục HP',
            'crit_dmg'     => 'Sát thương bạo kích',
            'def_bonus'    => 'Phòng ngự',
            'hp_regen'     => 'Hồi HP mỗi round',
            'dmg_reduce'   => 'Giảm sát thương',
        );
        return isset($map[$type]) ? $map[$type] : $type;
    }
}
?>