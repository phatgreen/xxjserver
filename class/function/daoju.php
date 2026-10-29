<?php
namespace player;

/**
 * Đọc cấu hình đạo cụ từ JSON.
 * Bảng daoju là cấu hình tĩnh nên chuyển sang file JSON.
 * Bảng playerdaoju vẫn giữ trong database vì đây là dữ liệu đạo cụ riêng của từng người chơi.
 */
if (!function_exists(__NAMESPACE__ . '\\daoju_config_path')) {
    function daoju_config_path(){
		//D:\XXJserver\phpstudy_pro\WWW\class\config
        $paths = array(
            __DIR__ . '/../config/daoju_config.json'
        );

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\daoju_config_all')) {
    function daoju_config_all(){
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $file = daoju_config_path();
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

if (!function_exists(__NAMESPACE__ . '\\daoju_config_get')) {
    function daoju_config_get($djid){
        $djid = (string)intval($djid);
        $all = daoju_config_all();

        return (isset($all[$djid]) && is_array($all[$djid])) ? $all[$djid] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\daoju_sell_price')) {
    // Giá bán 1 đạo cụ cho NPC theo class/config/daoju_sell_config.json; 0 = không bán cho NPC được.
    function daoju_sell_price($djid, $djyxb){
        static $pct = null;
        if ($pct === null) {
            $file = __DIR__ . '/../config/daoju_sell_config.json';
            $data = is_file($file) ? json_decode(file_get_contents($file), true) : null;
            $pct = (is_array($data) && isset($data['pct']) && is_array($data['pct'])) ? $data['pct'] : array();
        }
        $key = (string)intval($djid);
        $p = isset($pct[$key]) ? intval($pct[$key]) : 100;
        return intval(floor(intval($djyxb) * max(0, $p) / 100));
    }
}

if (!function_exists(__NAMESPACE__ . '\\hydrate_daoju_config_to_object')) {
    function hydrate_daoju_config_to_object($object, $config){
        if (!$config || !is_array($config)) {
            return $object;
        }

        foreach ($config as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }
}

if (!function_exists(__NAMESPACE__ . '\\daoju_config_row')) {
    function daoju_config_row($djid){
        $config = daoju_config_get($djid);
        return $config ? $config : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\daoju_config_rows_by_ids')) {
    function daoju_config_rows_by_ids($ids){
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (!is_array($ids)) {
            return array();
        }

        $rows = array();
        foreach ($ids as $id) {
            $id = intval(trim($id));
            if ($id <= 0) {
                continue;
            }

            $row = daoju_config_row($id);
            if ($row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}

if (!function_exists(__NAMESPACE__ . '\\item_emoji')) {
    // Biểu tượng vật phẩm theo từ khoá trong tên (đạo cụ / dược phẩm / đan dược chưa có ảnh riêng). Trả array(emoji, màu).
    function item_emoji($name, $default = '📦'){
        static $map = array(
            array('Hồi Hồn', '👻', '#a78bfa'), array('Diên Thọ', '⏳', '#fbbf24'), array('Thọ', '⏳', '#fbbf24'),
            array('Tu Vi Đan', '🔮', '#c084fc'), array('Thể Lực', '⚡', '#facc15'), array('Đột Phá', '💥', '#fb923c'),
            array('Tẩy', '🌀', '#38bdf8'), array('Tố Thể', '🩸', '#f87171'), array('Khai Phú', '🌟', '#fde047'),
            array('Đan', '💊', '#f472b6'), array('Hoàn', '💊', '#f472b6'), array('Tán', '💊', '#f472b6'), array('Thang', '🍵', '#34d399'),
            array('Túi', '💰', '#fbbf24'), array('Thẻ', '🎫', '#60a5fa'), array('Quyển', '📜', '#fcd34d'), array('Bí Tịch', '📘', '#60a5fa'),
            array('Phù', '🧧', '#f87171'), array('Lệnh', '🔖', '#fca5a5'), array('Rương', '🎁', '#fbbf24'), array('Hộp', '🎁', '#fbbf24'),
            array('Thạch', '💎', '#38bdf8'), array('Đá', '💎', '#38bdf8'), array('Tinh', '✨', '#a5f3fc'), array('Ngọc', '🔷', '#22d3ee'),
            array('Thảo', '🌿', '#4ade80'), array('Dược', '🌿', '#4ade80'), array('Hoa', '🌸', '#f9a8d4'), array('Quả', '🍑', '#fb923c'),
            array('Vũ', '🪶', '#e2e8f0'), array('Lông', '🪶', '#e2e8f0'), array('Huyết', '🩸', '#f87171'), array('Cốt', '🦴', '#e5e7eb'),
            array('Sắt', '⛓', '#94a3b8'), array('Kim', '🪙', '#fbbf24'), array('Mộc', '🪵', '#a3e635'), array('Thủy', '💧', '#38bdf8'),
            array('Hỏa', '🔥', '#fb923c'), array('Thổ', '🪨', '#d6a26b')
        );
        $name = (string)$name;
        foreach ($map as $m) {
            if (mb_stripos($name, $m[0], 0, 'UTF-8') !== false) return array($m[1], $m[2]);
        }
        return array($default, '#7dd3fc');
    }
}
