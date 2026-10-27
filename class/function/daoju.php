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
            __DIR__ . '../../config/daoju_config.json'
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
