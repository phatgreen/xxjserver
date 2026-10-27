<?php
namespace player;

/**
 * Class NPC
 * Giữ lại để tương thích với code cũ:
 * $npc = new \player\npc();
 */
class npc
{
    public $id = 0;
    public $nname = '';
    public $nsex = '';
    public $ninfo = '';
    public $muban = '';
    public $taskid = '';
    public $mz = '';
    public $rwqy = '';
}

/**
 * Đường dẫn file cấu hình NPC
 */
function npc_config_path()
{
    return __DIR__ . '/../config/npc_config.json';
}

/**
 * Cache dữ liệu NPC
 */
function npc_all()
{
    static $npcCache = null;

    if ($npcCache !== null) {
        return $npcCache;
    }

    $file = npc_config_path();

    if (!file_exists($file)) {
        $npcCache = array();
        return $npcCache;
    }

    $json = file_get_contents($file);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        $data = array();
    }

    $npcCache = $data;

    return $npcCache;
}

/**
 * Lấy NPC theo ID
 */
function getnpc($id)
{
    $allNpc = npc_all();

    $id = (string)intval($id);

    if (!isset($allNpc[$id])) {
        return false;
    }

    $row = $allNpc[$id];

    $npc = new npc();

    foreach ($row as $key => $value) {
        $npc->$key = $value;
    }

    return $npc;
}

/**
 * Kiểm tra NPC tồn tại
 */
function npc_exists($id)
{
    $allNpc = npc_all();

    return isset($allNpc[(string)intval($id)]);
}

/**
 * Lấy toàn bộ NPC
 */
function getallnpc()
{
    return npc_all();
}

/**
 * Tìm NPC theo tên
 */
function getnpcbyname($name)
{
    $allNpc = npc_all();

    foreach ($allNpc as $row) {
        if (isset($row['nname']) && $row['nname'] == $name) {

            $npc = new npc();

            foreach ($row as $key => $value) {
                $npc->$key = $value;
            }

            return $npc;
        }
    }

    return false;
}