<?php
namespace player;

/**
 * Đọc cấu hình dược phẩm (yaopin) từ JSON.
 * Bảng yaopin là cấu hình tĩnh nên không SELECT từ DB nữa.
 * Bảng playeryaopin vẫn giữ trong DB vì đây là dữ liệu riêng của từng người chơi.
 */
function yaopin_config_path(){
    $paths = array(
		//D:\XXJserver\phpstudy_pro\WWW\class\config
        __DIR__ . '/../config/yaopin_config.json'
    );

    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return $paths[0];
}

function yaopin_config_all(){
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $file = yaopin_config_path();
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

function yaopin_config_get($ypid){
    $ypid = (string)intval($ypid);
    $all = yaopin_config_all();

    return (isset($all[$ypid]) && is_array($all[$ypid])) ? $all[$ypid] : false;
}

function hydrate_yaopin_config_to_object($object, $config){
    if (!$config || !is_array($config)) {
        return $object;
    }

    foreach ($config as $key => $value) {
        $object->$key = $value;
    }

    return $object;
}

function yaopin_config_rows_by_ids($ids){
    $rows = array();

    if (is_string($ids)) {
        $ids = explode(',', $ids);
    }

    if (!is_array($ids)) {
        return $rows;
    }

    foreach ($ids as $id) {
        $id = intval(trim($id));
        if ($id <= 0) {
            continue;
        }

        $config = yaopin_config_get($id);
        if ($config) {
            $rows[] = $config;
        }
    }

    return $rows;
}
