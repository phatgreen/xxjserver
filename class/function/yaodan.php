<?php
namespace player;

/**
 * Đọc cấu hình đan dược (yaodan) từ JSON.
 * Bảng yaodan là cấu hình tĩnh nên không SELECT từ DB nữa.
 * Bảng playeryaodan vẫn giữ trong DB vì đây là dữ liệu riêng của từng người chơi.
 */
function yaodan_config_path(){
    $paths = array(
		//D:\XXJserver\phpstudy_pro\WWW\class\config
        __DIR__ . '/../config/yaodan_config.json'
    );

    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return $paths[0];
}

function yaodan_config_all(){
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $file = yaodan_config_path();
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

function yaodan_config_get($ydid){
    $ydid = (string)intval($ydid);
    $all = yaodan_config_all();

    return (isset($all[$ydid]) && is_array($all[$ydid])) ? $all[$ydid] : false;
}

function hydrate_yaodan_config_to_object($object, $config){
    if (!$config || !is_array($config)) {
        return $object;
    }

    foreach ($config as $key => $value) {
        $object->$key = $value;
    }

    return $object;
}
