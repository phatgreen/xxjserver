<?php
namespace player;

/**
 * feature_unlock.php — mở tính năng theo cấp (docs/MO_KHOA_TINH_NANG.md).
 * Cấu hình: class/config/feature_unlock.json. Tính năng không khai báo = mở từ cấp 1.
 */

if (!function_exists(__NAMESPACE__ . '\\feature_unlock_config')) {
    function feature_unlock_config(){
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $file = __DIR__ . '/../config/feature_unlock.json';
        $data = is_file($file) ? json_decode(file_get_contents($file), true) : null;
        $cache = (is_array($data) && isset($data['features']) && is_array($data['features'])) ? $data['features'] : array();
        foreach ($cache as $key => $f) {
            $cache[$key]['key'] = $key;
            $cache[$key]['level'] = isset($f['level']) ? intval($f['level']) : 1;
        }
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\feature_get')) {
    function feature_get($key){
        $all = feature_unlock_config();
        return isset($all[$key]) ? $all[$key] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\feature_is_open_lv')) {
    // Tính năng không khai báo trong config luôn mở.
    function feature_is_open_lv($lv, $key){
        $f = feature_get($key);
        return !$f || intval($lv) >= $f['level'];
    }
}

if (!function_exists(__NAMESPACE__ . '\\feature_is_open')) {
    function feature_is_open($player, $key){
        return feature_is_open_lv(isset($player->ulv) ? $player->ulv : 0, $key);
    }
}

if (!function_exists(__NAMESPACE__ . '\\feature_key_for_cmd')) {
    function feature_key_for_cmd($cmd){
        static $map = null;
        if ($map === null) {
            $map = array();
            foreach (feature_unlock_config() as $key => $f) {
                if (!isset($f['cmds']) || !is_array($f['cmds'])) continue;
                foreach ($f['cmds'] as $c) {
                    $map[$c] = $key;
                }
            }
        }
        return isset($map[$cmd]) ? $map[$cmd] : '';
    }
}

if (!function_exists(__NAMESPACE__ . '\\feature_lock_for_cmd')) {
    // Trả về cấu hình tính năng nếu lệnh $cmd thuộc tính năng CHƯA mở, ngược lại false.
    function feature_lock_for_cmd($player, $cmd){
        $key = feature_key_for_cmd($cmd);
        if ($key === '' || feature_is_open($player, $key)) {
            return false;
        }
        return feature_get($key);
    }
}

if (!function_exists(__NAMESPACE__ . '\\feature_quest_type_open')) {
    // Loại nhiệm vụ ngày/tuần/tháng có làm được ở cấp $lv không (tính năng cần dùng đã mở chưa).
    function feature_quest_type_open($lv, $questType){
        foreach (feature_unlock_config() as $key => $f) {
            if (isset($f['quest_types']) && in_array($questType, $f['quest_types'], true) && intval($lv) < $f['level']) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists(__NAMESPACE__ . '\\feature_next_locked')) {
    // $n tính năng sắp mở gần nhất (để hiện 🔒 "Mở ở cấp X").
    function feature_next_locked($lv, $n = 2){
        $list = array();
        foreach (feature_unlock_config() as $f) {
            if (intval($lv) < $f['level']) $list[] = $f;
        }
        usort($list, function($a, $b){ return $a['level'] - $b['level']; });
        return array_slice($list, 0, $n);
    }
}
