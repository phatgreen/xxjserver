<?php
namespace player;

/* =========================
   CẤU HÌNH QUÁI VẬT BẰNG JSON
   - Bảng guaiwu là dữ liệu cấu hình tĩnh, chuyển sang file JSON.
   - Bảng midguaiwu vẫn giữ trong DB vì đây là quái đang tồn tại trên bản đồ/trận đấu.
========================= */

if (!function_exists(__NAMESPACE__ . '\\guaiwu_config_path')) {
    function guaiwu_config_path(){
        $paths = array(
            __DIR__ . '/../config/guaiwu_config.json',
            __DIR__ . '/../guaiwu_config.json',
            dirname(__DIR__) . '/config/guaiwu_config.json',
            dirname(__DIR__) . '/guaiwu_config.json'
        );

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\guaiwu_config_all')) {
    function guaiwu_config_all(){
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $file = guaiwu_config_path();
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

if (!function_exists(__NAMESPACE__ . '\\guaiwu_config_get')) {
    function guaiwu_config_get($gyid){
        $gyid = (string)intval($gyid);
        $all = guaiwu_config_all();

        return (isset($all[$gyid]) && is_array($all[$gyid])) ? $all[$gyid] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\hydrate_guaiwu_config_to_object')) {
    function hydrate_guaiwu_config_to_object($guaiwu, $config){
        if (!$config || !is_array($config)) {
            return $guaiwu;
        }

        foreach ($config as $key => $value) {
            $guaiwu->$key = $value;
        }

        $id = isset($config['id']) ? intval($config['id']) : 0;
        $guaiwu->gid = $id;
        $guaiwu->gyid = $id;

        if (!isset($guaiwu->gexp) || $guaiwu->gexp === null) {
            $guaiwu->gexp = 0;
        }

        return $guaiwu;
    }
}

if (!function_exists(__NAMESPACE__ . '\\get_guaiwu_config_list')) {
    function get_guaiwu_config_list(){
        return array_values(guaiwu_config_all());
    }
}

if (!function_exists(__NAMESPACE__ . '\\guaiwu_exp')) {
    /**
     * EXP một quái khi sinh ra map (docs/KHUNG_TONG_500_4300.md mục 3, KT-0).
     * Cấp ≤ 500: cấp × (106–108) như cũ. Cấp > 500: 110 × cấp² ÷ KILLS (EXP lên cấp chia số quái mỗi cấp),
     * giữ dao động (106–108)/107 ⇒ khoảng 400 quái cùng cấp/cấp thay vì tăng tuyến tính tới ~4.400.
     * Tính bằng float rồi round(): đúng cả trên PHP 32-bit (giá trị lớn nhất ~5 triệu).
     */
    function guaiwu_exp($glv, $sjexp = null){
        $kills = 400;
        $glv = intval($glv);
        if ($sjexp === null) {
            $sjexp = mt_rand(6, 8) + 100;
        }
        if ($glv <= 500) {
            return round($glv * $sjexp, 0);
        }
        return round(110.0 * $glv * $glv / $kills * $sjexp / 107, 0);
    }
}
