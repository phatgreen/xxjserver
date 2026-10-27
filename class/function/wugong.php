<?php
namespace player;

/**
 * wugong.php - bản dùng JSON an toàn.
 *
 * Mục tiêu:
 * - Không SELECT/JOIN bảng wugong nữa.
 * - Chỉ đọc cấu hình tĩnh từ wugong_config.json.
 * - Vẫn giữ bảng playerwugong để lưu dữ liệu riêng của người chơi.
 * - Tránh lỗi Cannot redeclare khi player.php đã khai báo sẵn các hàm JSON.
 */

if (!function_exists(__NAMESPACE__ . '\\wugong_config_path')) {
    function wugong_config_path(){
		//D:\XXJserver\phpstudy_pro\WWW\class\config
        $paths = array(
            __DIR__ . '../../config/wugong_config.json',      // class/function -> class/config
        );

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\wugong_config_all')) {
    function wugong_config_all(){
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $file = wugong_config_path();
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

if (!function_exists(__NAMESPACE__ . '\\wugong_config_get')) {
    function wugong_config_get($wgid){
        $wgid = intval($wgid);
        $all = wugong_config_all();

        if (isset($all[$wgid]) && is_array($all[$wgid]) && isset($all[$wgid]['wgid']) && intval($all[$wgid]['wgid']) === $wgid) {
            return $all[$wgid];
        }

        foreach ($all as $item) {
            if (isset($item['wgid']) && intval($item['wgid']) === $wgid) {
                return $item;
            }
        }

        return false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\hydrate_wugong_config_to_object')) {
    function hydrate_wugong_config_to_object($object, $config){
        if (!$config || !is_array($config)) {
            return $object;
        }

        foreach ($config as $key => $value) {
            if (isset($object->sid) && in_array($key, array('wgdj', 'wgsum', 'wgxl', 'wgxlmax', 'xlzt', 'xlsj', 'sid', 'uid'), true)) {
                continue;
            }
            $object->$key = $value;
        }

        return $object;
    }
}

if (!function_exists(__NAMESPACE__ . '\\wugong_config_rows_by_ids')) {
    function wugong_config_rows_by_ids($ids){
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

            $cfg = wugong_config_get($id);
            if ($cfg) {
                $rows[] = $cfg;
            }
        }

        return $rows;
    }
}

if (!function_exists(__NAMESPACE__ . '\\wgcx')) {
    /**
     * Lấy võ công người chơi:
     * - playerwugong: cấp, exp, số lượng, dữ liệu sở hữu.
     * - wugong_config.json: tên, mô tả, màu, hệ, thuộc tính cộng thêm, hiệu ứng.
     */
    function wgcx($wgid, $sid, $dblj, $forceRefresh = false){
        static $cache = array();

        $wgid = intval($wgid);
        if ($wgid <= 0 || $sid === '') {
            return false;
        }

        $cacheKey = $sid . ':' . $wgid;
        if (!$forceRefresh && isset($cache[$cacheKey])) {
            return $cache[$cacheKey] ? clone $cache[$cacheKey] : false;
        }

        $stmt = $dblj->prepare('SELECT * FROM playerwugong WHERE wgid = ? AND sid = ? LIMIT 1');
        $stmt->execute(array($wgid, $sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $cache[$cacheKey] = false;
            return false;
        }

        $wgcx = new wg();
        hydrate_object_from_row($wgcx, $row);

        $config = wugong_config_get($wgid);
        hydrate_wugong_config_to_object($wgcx, $config);

        $cache[$cacheKey] = clone $wgcx;
        return $wgcx;
    }
}

if (!function_exists(__NAMESPACE__ . '\\cqmj')) {
    /**
     * Lấy thông tin bí tịch/võ công từ JSON theo wgid.
     */
    function cqmj($wgid, $dblj = null){
        $miji = new miji();

        $config = wugong_config_get($wgid);
        if (!$config) {
            return $miji;
        }

        hydrate_wugong_config_to_object($miji, $config);
        return $miji;
    }
}

if (!function_exists(__NAMESPACE__ . '\\mjcx')) {
    /**
     * Kiểm tra võ công có tồn tại trong JSON không.
     */
    function mjcx($cqsjs, $dblj = null){
        $config = wugong_config_get($cqsjs);
        if (!$config) {
            return false;
        }

        $miji = new miji();
        hydrate_wugong_config_to_object($miji, $config);
        return $miji;
    }
}

if (!function_exists(__NAMESPACE__ . '\\cqwg')) {
    /**
     * Cấp/nhặt/học võ công.
     * Nếu đã có: tăng wgsum.
     * Nếu chưa có: thêm vào playerwugong bằng cấu hình lấy từ JSON.
     */
    function cqwg($cqsjs, $sid, $dblj){
        $wgsum = 1;
        $wgid = intval($cqsjs);

        if ($wgid <= 0) {
            return false;
        }

        $wg = wgcx($wgid, $sid, $dblj);
        if ($wg) {
            $stmt = $dblj->prepare('UPDATE playerwugong SET wgsum = wgsum + ? WHERE wgid = ? AND sid = ?');
            return $stmt->execute(array($wgsum, $wgid, $sid));
        }

        $mj = mjcx($wgid);
        if (!$mj) {
            return false;
        }

        $player = getplayer($sid, $dblj);

        $stmt = $dblj->prepare('
            INSERT INTO playerwugong
                (wgid, sid, uid, wgsum)
            VALUES
                (?, ?, ?, ?)
        ');

        return $stmt->execute(array(
            isset($mj->wgid) ? $mj->wgid : $wgid,
            $sid,
            isset($player->uid) ? $player->uid : '',
            $wgsum
        ));
    }
}

if (!function_exists(__NAMESPACE__ . '\\wgsl')) {
    /**
     * Tổng số cấu hình võ công trong JSON.
     */
    function wgsl($dblj = null){
        return count(wugong_config_all());
    }
}

if (!function_exists(__NAMESPACE__ . '\\wugongcs')) {
    /**
     * Lấy một võ công ngẫu nhiên từ JSON.
     */
    function wugongcs($dblj = null){
        $all = wugong_config_all();
        if (empty($all)) {
            return false;
        }

        $keys = array_keys($all);
        $key = $keys[array_rand($keys)];

        $miji = new miji();
        hydrate_wugong_config_to_object($miji, $all[$key]);
        return $miji;
    }
}
