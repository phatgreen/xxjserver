<?php
namespace player;

/**
 * Đọc cấu hình nhiệm vụ từ JSON.
 * Bảng renwu là cấu hình tĩnh, chuyển sang file JSON.
 * Bảng playerrenwu vẫn giữ trong database vì đây là dữ liệu nhiệm vụ riêng của từng người chơi.
 */
if (!function_exists(__NAMESPACE__ . '\\renwu_config_path')) {
    function renwu_config_path(){
		//D:\XXJserver\phpstudy_pro\WWW\class\config
        $paths = array(
            __DIR__ . '../../config/renwu_config.json'
        );

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\renwu_config_all')) {
    function renwu_config_all(){
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $file = renwu_config_path();
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

if (!function_exists(__NAMESPACE__ . '\\renwu_config_get')) {
    function renwu_config_get($rwid){
        $rwid = (string)intval($rwid);
        $all = renwu_config_all();

        return (isset($all[$rwid]) && is_array($all[$rwid])) ? $all[$rwid] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\hydrate_renwu_config_to_object')) {
    function hydrate_renwu_config_to_object($object, $config){
        if (!$config || !is_array($config)) {
            return $object;
        }

        foreach ($config as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }
}
