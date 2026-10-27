<?php
namespace player;

/* =========================
   CẤU HÌNH KỸ NĂNG BẰNG JSON
   - Bảng jineng là cấu hình tĩnh, chuyển sang file JSON.
   - Bảng playerjineng vẫn giữ trong DB để lưu dữ liệu kỹ năng của người chơi.
========================= */

function jineng_config_path(){
    $paths = array(
        __DIR__ . '/../config/jineng_config.json',
    );

    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return $paths[0];
}

function jineng_config_all(){
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $file = jineng_config_path();
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

function jineng_config_get($jnid){
    $jnid = (string)intval($jnid);
    $all = jineng_config_all();

    return (isset($all[$jnid]) && is_array($all[$jnid])) ? $all[$jnid] : false;
}

function hydrate_jineng_config_to_object($object, $config){
    if (!$config || !is_array($config)) {
        return $object;
    }

    foreach ($config as $key => $value) {
        $object->$key = $value;
    }

    return $object;
}

function merge_jineng_config_to_row($row){
    if (!$row || !is_array($row)) {
        return $row;
    }

    $jnid = isset($row['jnid']) ? intval($row['jnid']) : 0;
    $config = jineng_config_get($jnid);

    if ($config) {
        foreach ($config as $key => $value) {
            $row[$key] = $value;
        }
    }

    return $row;
}

function jineng_config_exists($jnid){
    return jineng_config_get($jnid) ? true : false;
}

function jineng_config_name($jnid){
    $config = jineng_config_get($jnid);
    return $config && isset($config['jnname']) ? $config['jnname'] : '';
}
?>
