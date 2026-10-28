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
            __DIR__ . '/../config/wugong_config.json',      // class/function -> class/config
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

        // INSERT IGNORE + khóa duy nhất (sid, wgid): không thêm dòng trùng (xem class/player.php cqwg)
        $stmt = $dblj->prepare('
            INSERT IGNORE INTO playerwugong
                (wgid, sid, uid, wgsum)
            VALUES
                (?, ?, ?, ?)
        ');
        $stmt->execute(array(
            isset($mj->wgid) ? $mj->wgid : $wgid,
            $sid,
            isset($player->uid) ? $player->uid : '',
            $wgsum
        ));
        if ($stmt->rowCount() < 1) {
            $stmt = $dblj->prepare('UPDATE playerwugong SET wgsum = wgsum + ? WHERE wgid = ? AND sid = ?');
            $stmt->execute(array($wgsum, $wgid, $sid));
        }
        wgcx($wgid, $sid, $dblj, true);
        return true;
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

// Đợt D: cấu hình Tiên Đạo Tàn Quyển (djid, số quyển đổi 1 bộ, % rơi theo cấp boss) — class/config/tiendao_config.json
if (!function_exists(__NAMESPACE__ . '\\tiendao_config')) {
    function tiendao_config(){
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $file = __DIR__ . '/../config/tiendao_config.json';
        $data = is_file($file) ? json_decode(file_get_contents($file), true) : null;
        $cache = is_array($data) ? $data : array('djid' => 0, 'need' => 0, 'wgid' => array(), 'drop_pct' => array());
        return $cache;
    }
}

// Loại Tàn Quyển (djid) dùng để đổi võ công $wgid: làn 5 trở đi có quyển riêng (djid_by_wgid), mặc định Tiên Đạo Tàn Quyển.
if (!function_exists(__NAMESPACE__ . '\\tiendao_djid_for')) {
    function tiendao_djid_for($wgid){
        $cfg = tiendao_config();
        if (isset($cfg['djid_by_wgid'][(string)intval($wgid)])) {
            return intval($cfg['djid_by_wgid'][(string)intval($wgid)]);
        }
        return intval($cfg['djid']);
    }
}

// Loại Tàn Quyển boss cấp $bosslv rơi ra (drop_djid_by_lv), mặc định Tiên Đạo Tàn Quyển.
if (!function_exists(__NAMESPACE__ . '\\tiendao_drop_djid')) {
    function tiendao_drop_djid($bosslv){
        $cfg = tiendao_config();
        if (isset($cfg['drop_djid_by_lv'][(string)intval($bosslv)])) {
            return intval($cfg['drop_djid_by_lv'][(string)intval($bosslv)]);
        }
        return intval($cfg['djid']);
    }
}

// Giá 1 bí tịch khi "Mua ngay" ở trang Võ Công = giá bán bộ đó ở Thiên Cơ Các (mua thêm ở shop cũng chỉ +1 bí tịch).
// Trả array(loại, số lượng, djid): 'lt' Linh thạch / 'mt' Ma thạch / 'tq' Tàn Quyển (djid); array('', 0, 0) = không mua được.
// Bộ shop không bán (vd phần thưởng sự kiện) => lấy giá thấp nhất của các bộ cùng mốc cảnh giới, không có thì mốc gần nhất bên dưới.
if (!function_exists(__NAMESPACE__ . '\\wugong_book_price')) {
    function wugong_book_price($wgid){
        $wgid = intval($wgid);
        $own = function ($cfg) {
            $id = intval($cfg['wgid']);
            $need = tiendao_need($id);
            if ($need > 0) return array('tq', $need, tiendao_djid_for($id));
            if (intval($cfg['price_linhthong']) > 0) return array('lt', intval($cfg['price_linhthong']), 0);
            if (intval($cfg['price_masthach']) > 0) return array('mt', intval($cfg['price_masthach']), 0);
            return array('', 0, 0);
        };
        $cfg = wugong_config_get($wgid);
        if (!$cfg || $wgid <= 0) return array('', 0, 0);
        $p = $own($cfg);
        if ($p[1] > 0) return $p;

        $realm = intval($cfg['realm_require']);
        $best = array('', 0, 0);
        $bestRealm = -1;
        foreach (wugong_config_all() as $c) {
            if (intval($c['wgid']) <= 0 || intval($c['realm_require']) > $realm) continue;
            $q = $own($c);
            if ($q[1] <= 0) continue;
            $r = intval($c['realm_require']);
            // Ưu tiên mốc cao nhất <= mốc của bộ; cùng mốc thì lấy giá thấp nhất
            if ($r > $bestRealm || ($r == $bestRealm && $q[1] < $best[1])) {
                $best = $q;
                $bestRealm = $r;
            }
        }
        return $best;
    }
}

// Công pháp cảnh giới >= 30 đổi bằng Tàn Quyển (cp_need_by_cpid / cp_djid_by_cpid): trả array(djid, số quyển), 0 quyển = mua bằng tiền.
if (!function_exists(__NAMESPACE__ . '\\tiendao_cp_cost')) {
    function tiendao_cp_cost($cpid){
        $cfg = tiendao_config();
        $k = (string)intval($cpid);
        if (!isset($cfg['cp_need_by_cpid'][$k]) || !isset($cfg['cp_djid_by_cpid'][$k])) {
            return array(0, 0);
        }
        return array(intval($cfg['cp_djid_by_cpid'][$k]), intval($cfg['cp_need_by_cpid'][$k]));
    }
}

// Số Tàn Quyển cần để đổi võ công $wgid; 0 = bộ này không đổi bằng Tàn Quyển.
if (!function_exists(__NAMESPACE__ . '\\tiendao_need')) {
    function tiendao_need($wgid){
        $cfg = tiendao_config();
        if (intval($cfg['djid']) <= 0 || !in_array(intval($wgid), array_map('intval', $cfg['wgid']), true)) {
            return 0;
        }
        // Bộ có số riêng (vd Tiên Đạo CG 36 của làn 4 cần 15) — không có thì dùng số chung
        if (isset($cfg['need_by_wgid'][(string)intval($wgid)])) {
            return intval($cfg['need_by_wgid'][(string)intval($wgid)]);
        }
        return intval($cfg['need']);
    }
}
