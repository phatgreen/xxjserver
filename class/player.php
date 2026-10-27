<?php 
namespace player ;
// D:\XXJserver\phpstudy_pro\WWW\class\function
require_once __DIR__ . '/function/renwu.php';
require_once __DIR__ . '/function/yaodan.php';
require_once __DIR__ . '/function/yaopin.php';
require_once __DIR__ . '/function/wugong.php';
require_once __DIR__ . '/function/zhuangbei.php';
require_once __DIR__ . '/function/itemtip.php';
require_once __DIR__ . '/function/taozhuang.php';
require_once __DIR__ . '/function/daoju.php';
require_once __DIR__ . '/function/jineng.php';
require_once __DIR__ . '/function/npc.php';
require_once __DIR__ . '/function/guaiwu.php';
require_once __DIR__ . '/function/congphap.php';
require_once __DIR__ . '/function/linhcan.php';
require_once __DIR__ . '/function/linhcan_skill.php';
require_once __DIR__ . '/function/linhcan_quest.php';
require_once __DIR__ . '/function/linhcan_boss.php';
require_once __DIR__ . '/function/luonden.php';
require_once __DIR__ . '/function/chetao.php';
require_once __DIR__ . '/function/nap_hanh.php';
require_once __DIR__ . '/function/thechat.php';
require_once __DIR__ . '/function/huyetmach.php';
require_once __DIR__ . '/function/huyetmach_boss.php';
require_once __DIR__ . '/function/nhiemvu.php';
require_once __DIR__ . '/function/chatmsg.php';
require_once __DIR__ . '/function/tianfu.php';
require_once __DIR__ . '/function/combat.php';
require_once __DIR__ . '/function/conghuong.php';


class player
{
    var $uname;// Biệt danh
    var $uid;
    var $sid;//sid 
    var $ulv;// Đẳng cấp
    var $uyxb;// Tiền trò chơi
    var $uczb;// Nạp tiền tệ
    var $uexp;// Kinh nghiệm
    var $umaxexp;// Kinh nghiệm hạn mức cao nhất
    var $uhp;// Sinh mệnh
    var $umaxhp;// Sinh mệnh
    var $ustamina;// Thể lực
    var $umaxstamina;// Giới hạn thể lực
    var $ugj;// Công kích
    var $ufy;// Phòng ngự
    var $ubj;// Bạo kích
    var $uxx;// Hút máu
    var $uwx;// Ngũ Hành
    var $usex;// Giới tính
    var $vip;//vip
    var $nowmid;// Trước mắt địa đồ
    var $endtime;
    var $stamina_time;// Thời điểm hồi thể lực gần nhất
    var $tool1;
    var $tool2;
    var $tool3;
    var $tool4;
    var $tool5;
    var $tool6;
    var $tool7;
	var $tool8;
	var $tool9;
	var $tool10;
	var $tool11;
	var $tool12;
	var $tool13;
	var $tool14;
	var $tool15;
	var $tool16;
    var $jingjie;
	var $jingjieValue;
    var $sfxl;
    var $sfzx;
    var $xiuliantime;
    var $yp1;
    var $yp2;
    var $yp3;
    var $cw;
    var $jn1;
    var $jn2;
    var $jn3;
    var $ispvp;
    var $cengci;
	var $jingjieCengci;
	var $yd1;
	var $yd2;
	var $yd3;
	var $shenfen;//Danh tính
	var $dhvip;// Hối đoái vip Trang bị phán đoán
	var $dhvip1;// Hối đoái vip Trang bị phán đoán
	var $tf;// Thiên phú
	var $tfgj;// Thiên phú công kích
	var $tfxy;// Thiên phú nhỏ may mắn
	var $tfsb;// Né tránh
	var $tfxx;// Hút máu
	var $tfhp;
	var $tffy;
	var $tfbj;
	var $wugong;
	var $congphap;
	var $tuposjs;
	var $tupoexp;

    // Thần Trang: thuộc tính tạm tính khi getplayer(), không lưu trực tiếp vào DB.
    var $shenzhuang_hp_percent;
    var $shenzhuang_gj_percent;
    var $shenzhuang_fy_percent;
    var $shenzhuang_total_percent;
    var $shenzhuang_power;
    var $shenzhuang_detail;

    // Linh Căn: thuộc tính tạm tính khi getplayer()
    var $linhcan_id;
    var $linhcan_name;
    var $linhcan_element;
    var $linhcan_color;
    var $linhcan_tier;
    var $linhcan_absorb;
    var $linhcan_exp_bonus;
    var $linhcan_stats;
    var $linhcan_pet_bonus;
    var $linhcan_icon;
    var $linhcan_title_id;
    var $linhcan_total_lv;

    // Thể Chất: thuộc tính tạm tính khi getplayer()
    var $thechat_id;
    var $thechat_quality;
    var $thechat_awaken_stage;
    var $thechat_name;
    var $thechat_element;
    var $thechat_color;
    var $thechat_icon;
    var $thechat_stats;
    var $thechat_category_id;
    var $thechat_category_name;
    var $thechat_passive_exp;
    var $thechat_passive_regen;
    var $thechat_passive_resist;
    var $thechat_passive_reduce;

    // Huyết Mạch: thuộc tính tạm tính khi getplayer()
    var $huyetmach_id;
    var $huyetmach_quality;
    var $huyetmach_awaken_stage;
    var $huyetmach_name;
    var $huyetmach_element;
    var $huyetmach_color;
    var $huyetmach_icon;
    var $huyetmach_stats;
    var $huyetmach_vessel;
    var $huyetmach_element_dmg;
    var $huyetmach_passive_element;
    var $huyetmach_passive_crit;
    var $huyetmach_passive_exp;
    var $huyetmach_passive_drop;
    var $huyetmach_can_transform;
    // Thien Phu: thuoc tinh tam tinh khi getplayer()
    var $tianfu_id;
    var $tianfu_quality;
    var $tianfu_lv;
    var $tianfu_power;
    var $tianfu_name;
    var $tianfu_color;
    var $tianfu_icon;
    var $tianfu_bonus;
}

//$rangeslv = array(0, 50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 550, 600, 650, 700, 750, 800, 850, 900, 950, 1000, 1050, 1100, 1150, 1200, 1250, 1300, 1350); // Lên 260
$rangeslv = range(0, 4300, 50); // code nâng cấp

include "rangesjj.php";

$rangesjd = [];
$colors = [
    0=>['#C7C7C7','#D7D7D7'], 1=>['#C7C7C7','#D7D7D7'], 2=>['#78CAC6','#75C7C3'],
    3=>['#A78104','#A36208'], 4=>['#A78104','#A36208'], 5=>['#F49477','#F19174'],
    6=>['#F49477','#F19174'], 7=>['#EB1A21','#E8171E'], 8=>['#9D0F36','#4C0148'],
    9=>['#9D0F36','#4C0148'], 10=>['#770035','#740044']
];

for ($i = 0; $i <= 100; $i++) {
    $g = $i % 10;
    if ($i > 0 && $g == 0) $g = 10;
    
    $c = $colors[$g];
    if ($g == 10) {
        $rangesjd[$i] = "<font color='{$c[0]}'>Đỉnh </font><font color='{$c[1]}'>Phong</font>";
    } else {
        $rangesjd[$i] = "<font color='{$c[0]}'>$g </font><font color='{$c[1]}'>Giai</font>";
    }
}
include "jingjieColor.php";


/* =========================
   CẤU HÌNH VÕ CÔNG BẰNG JSON
   - Bảng wugong là cấu hình tĩnh, chuyển sang file JSON.
   - Bảng playerwugong vẫn giữ trong DB để lưu dữ liệu riêng của người chơi.
========================= */
function wugong_config_path(){
    $paths = array(
        __DIR__ . '/config/wugong_config.json',
        __DIR__ . '/wugong_config.json'
    );

    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return $paths[0];
}

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

function hydrate_wugong_config_to_object($object, $config){
    if (!$config || !is_array($config)) {
        return $object;
    }

    foreach ($config as $key => $value) {
        $object->$key = $value;
    }

    return $object;
}

function merge_wugong_config_to_row($row){
    if (!$row || !is_array($row)) {
        return $row;
    }

    $wgid = isset($row['wgid']) ? intval($row['wgid']) : 0;
    $config = wugong_config_get($wgid);

    if ($config) {
        foreach ($config as $key => $value) {
            if (in_array($key, array('wgdj', 'wgsum', 'wgxl', 'wgxlmax', 'xlzt', 'xlsj', 'sid', 'uid'), true)) {
                continue;
            }
            $row[$key] = $value;
        }
    }

    return $row;
}



/* =========================
   HÀM HỖ TRỢ TỐI ƯU TRUY VẤN
========================= */
function hydrate_object_from_row($object, $row){
    if (!$row || !is_array($row)) return false;
    foreach ($row as $key => $value) {
        $object->$key = $value;
    }
    return $object;
}

/**
 * Anh avatar theo gioi tinh: usex 1 = nam, 2 = nu.
 * Khong duoc dung "images/{usex}.png" vi do la icon trang bi zbid 1 va 2.
 */
function player_avatar_img($usex){
    return intval($usex) === 2 ? 'images/avatar_nu.png' : 'images/avatar_nam.png';
}

function player_allowed_columns(){
    return array(
        'uname','uid','sid','ulv','uyxb','uczb','uexp','uhp','umaxhp','ustamina','umaxstamina','ugj','ufy','ubj','uxx','uwx','usex','vip','nowmid','endtime','stamina_time',
        'tool1','tool2','tool3','tool4','tool5','tool6','tool7','tool8','tool9','tool10','tool11','tool12','tool13','tool14','tool15','tool16',
        'sfxl','sfzx','xiuliantime','yp1','yp2','yp3','cw','jn1','jn2','jn3','ispvp','dhvip','dhvip1','tf','tfgj','tfxy','tfsb','tfxx','tfhp','tffy','tfbj',
        'wugong','congphap','shenfen','linhcan_id','linhcan_quality',
        'thechat_id','thechat_quality','thechat_awaken_stage',
        'huyetmach_id','huyetmach_quality','huyetmach_awaken_stage','huyetmach_transform_cd',
        'tianfu_id','tianfu_quality','tianfu_lv','tianfu_power'
    );
}

function game1_update_allowed_columns(){
    return array(
        'uname','ulv','uyxb','uczb','uexp','uhp','umaxhp','ustamina','umaxstamina','ugj','ufy','ubj','uxx','uwx','vip','nowmid','endtime',
        'sfxl','sfzx','xiuliantime','yp1','yp2','yp3','cw','jn1','jn2','jn3','ispvp','dhvip','dhvip1','tf','tfgj','tfxy','tfsb','tfxx','tfhp','tffy','tfbj',
        'wugong','congphap','shenfen'
    );
}

/*
| Hồi Thể lực theo thời gian thực — NƠI DUY NHẤT chứa công thức.
|
| Mỗi 5 phút hồi 1 điểm. `ustamina` là giá trị gốc tại mốc `stamina_time`.
| Thien Phu cong 6 co the rut chu ky (san 30 giay) — truyen $staminaPct, xem tianfu_stamina_interval().
| Hàm này tính Thể lực "sống" (live) dựa trên thời gian trôi qua, có thể:
|   - chỉ đọc (không ghi DB) để hiển thị nhanh mọi nơi,
|   - hoặc "settle" (nhích mốc stamina_time + tùy chọn ghi DB) để DB luôn tươi.
|
| @return array: array('value'=>int Thể lực sống,
|                     'mark'=>int timestamp mốc mới sau khi hồi,
|                     'steps'=>int số điểm hồi được)
|
| KHÔNG phụ thuộc cron/task nền -> chạy được trên mọi hệ điều hành,
| offline vẫn hồi vì thời gian luôn trôi.
*/
function settle_stamina($ustamina, $umaxstamina, $stamina_time, $nowTs = null, $intervalSec = 300, $staminaPct = 0)
{
    $nowTs = ($nowTs === null) ? time() : intval($nowTs);
    if (function_exists(__NAMESPACE__ . '\tianfu_stamina_interval')) {
        $intervalSec = tianfu_stamina_interval($staminaPct, $intervalSec);
    }
    $base  = intval($ustamina);
    $max   = intval($umaxstamina);
    if ($max <= 0) {
        return array('value' => $base, 'mark' => $nowTs, 'steps' => 0);
    }
    if ($base >= $max) {
        // Đầy Thể lực: mốc giữ nguyên (đồng hồ đứng), không hồi thêm.
        return array('value' => $base, 'mark' => $nowTs, 'steps' => 0);
    }

    $lastTs = 0;
    if ($stamina_time && $stamina_time != '0000-00-00 00:00:00') {
        $lastTs = strtotime($stamina_time);
        if ($lastTs === false) {
            $lastTs = 0;
        }
    }
    // Chưa từng có mốc -> ghi mốc hiện tại, KHÔNG hồi (tránh catch-up khổng lồ).
    if ($lastTs <= 0) {
        return array('value' => $base, 'mark' => $nowTs, 'steps' => 0);
    }

    $elapsed   = $nowTs - $lastTs;
    $steps     = intval(floor($elapsed / $intervalSec));
    if ($steps < 0) {
        $steps = 0;
    }
    $value = $base + $steps;
    if ($value > $max) {
        $value = $max;
    }
    // Mốc mới chỉ nhích đúng số khoảng đã hưởng (giữ nguyên phần dư ở mốc cũ).
    $mark = $lastTs + ($steps * $intervalSec);

    return array('value' => $value, 'mark' => $mark, 'steps' => $steps);
}


function player_cache_get($sid){
    if (!isset($GLOBALS['__player_runtime_cache'])) {
        $GLOBALS['__player_runtime_cache'] = array();
    }
    $key = (string)$sid;
    return isset($GLOBALS['__player_runtime_cache'][$key]) ? clone $GLOBALS['__player_runtime_cache'][$key] : false;
}

function player_cache_set($sid, $player){
    if (!isset($GLOBALS['__player_runtime_cache'])) {
        $GLOBALS['__player_runtime_cache'] = array();
    }
    $GLOBALS['__player_runtime_cache'][(string)$sid] = clone $player;
}

function player_cache_clear($sid = null){
    if (!isset($GLOBALS['__player_runtime_cache'])) return;
    if ($sid === null) {
        $GLOBALS['__player_runtime_cache'] = array();
    } else {
        unset($GLOBALS['__player_runtime_cache'][(string)$sid]);
    }
}

function zhuangbei_cache_clear($zbnowid = null){
    if (!isset($GLOBALS['__zhuangbei_runtime_cache'])) return;
    if ($zbnowid === null) {
        $GLOBALS['__zhuangbei_runtime_cache'] = array();
    } else {
        unset($GLOBALS['__zhuangbei_runtime_cache'][(int)$zbnowid]);
    }
}

function combat_power($player){
    if (!is_object($player)) return 0;

    $weights = array(
        'ugj'                     => 10,
        'ufy'                     => 8,
        'ubj'                     => 30,
        'uxx'                     => 25,
        'tianfu_power'            => 1,
        'thechat_quality'         => 200,
        'thechat_awaken_stage'    => 500,
        'huyetmach_quality'       => 250,
        'huyetmach_awaken_stage'  => 600
    );

    $power  = intval(isset($player->umaxhp) ? $player->umaxhp : 0);
    $power += intval(isset($player->shenzhuang_power) ? $player->shenzhuang_power : 0);
    foreach ($weights as $col => $weight) {
        $power += intval(isset($player->$col) ? $player->$col : 0) * $weight;
    }

    return $power;
}

function zhuangbei_columns(){
    // playerzhuangbei là dữ liệu RIÊNG của người chơi.
    // Các cột tên/chỉ số/màu/bậc vẫn đọc để tương thích DB cũ và giữ phần cường hóa đã cộng vào DB.
    return array('zbid','uid','zbnowid','sid','qianghua','zbgj','zbfy','zbhp','zbbj','zbxx','element');
}

function zhuangbei_int($value){
    if ($value === '' || $value === null) return 0;
    return intval($value);
}

function merge_player_zhuangbei_with_config($row){
    if (!$row || !is_array($row)) return false;

    $zbid = isset($row['zbid']) ? intval($row['zbid']) : 0;
    $config = zhuangbei_config_get($zbid);

    // Nếu JSON thiếu cấu hình thì fallback về dữ liệu DB để tránh trắng trang.
    if (!$config) {
        $config = array();
    }

    $zb = new zhuangbei();

    // 1) Nạp thông tin gốc từ JSON trước: tên, mô tả, chỉ số, cấp, màu, loại slot, thần trang...
    if (!empty($config)) {
        hydrate_zhuangbei_config_to_object($zb, $config);
    }

    // 2) Giữ dữ liệu riêng của người chơi từ DB.
    $zb->zbid    = $zbid;
    $zb->uid     = isset($row['uid']) ? intval($row['uid']) : 0;
    $zb->sid     = isset($row['sid']) ? $row['sid'] : '';
    $zb->zbnowid = isset($row['zbnowid']) ? intval($row['zbnowid']) : 0;
    $zb->qianghua = isset($row['qianghua']) ? intval($row['qianghua']) : 0;

    // 3) Tương thích dữ liệu cũ: nếu JSON chưa có field nào thì lấy DB cũ.
    $textFields = array('zbname','zbinfo','zbys');
    foreach ($textFields as $field) {
        if ((!isset($zb->$field) || $zb->$field === '') && isset($row[$field])) {
            $zb->$field = $row[$field];
        }
    }

    $normalFields = array('zblv','tuposjs','tupoexp','taozhuang');
    foreach ($normalFields as $field) {
        if (!isset($zb->$field) || $zb->$field === '' || $zb->$field === null) {
            $zb->$field = isset($row[$field]) ? zhuangbei_int($row[$field]) : 0;
        }
    }

    if (!isset($zb->tool) || $zb->tool === '' || $zb->tool === null) {
        $zb->tool = isset($row['zbtool']) ? zhuangbei_int($row['zbtool']) : 0;
    }

    // 4) Chỉ số cuối = chỉ số JSON mới + phần tăng thêm do cường hóa trong DB cũ.
    // Ví dụ DB cũ zbgj = 376, JSON gốc = 350 => bonus giữ lại 26.
    $attrFields = array('zbgj','zbfy','zbhp','zbbj','zbxx');
    foreach ($attrFields as $field) {
        $jsonValue = isset($config[$field]) ? zhuangbei_int($config[$field]) : (isset($row[$field]) ? zhuangbei_int($row[$field]) : 0);
        $dbValue   = isset($row[$field]) ? zhuangbei_int($row[$field]) : $jsonValue;
        $bonus     = ($dbValue > $jsonValue) ? ($dbValue - $jsonValue) : 0;
        $zb->$field = $jsonValue + $bonus;
    }

    // element là dữ liệu RIÊNG theo món (nạp ngũ hành) → giá trị DB đè config.
    // Config luôn 'none' (Plan Z), DB mới là nguồn hệ thật.
    $dbElemRaw = isset($row['element']) ? trim((string)$row['element']) : '';
    if ($dbElemRaw !== '' && $dbElemRaw !== 'none' && zhuangbei_elements_parse($dbElemRaw) === array()) {
        error_log('nguhanh: element la, ep ve none (zbnowid=' . (isset($zb->zbnowid) ? $zb->zbnowid : 0) . ', value=' . $dbElemRaw . ')');
    }
    $zb->element = zhuangbei_elements_join(zhuangbei_elements_parse($dbElemRaw));
    if (!isset($zb->rarity) || $zb->rarity === '' || $zb->rarity === null) {
        $zb->rarity = 1;
    }
    if (!isset($zb->rarity_name) || $zb->rarity_name === '') {
        $zb->rarity_name = \player\zhuangbei_rarity_name($zb->rarity);
    }

    return $zb;
}

function getzbsByIds($ids, $dblj){
    if (!isset($GLOBALS['__zhuangbei_runtime_cache'])) {
        $GLOBALS['__zhuangbei_runtime_cache'] = array();
    }
    $cache =& $GLOBALS['__zhuangbei_runtime_cache'];

    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), function($id){ return $id > 0; })));
    $result = array();
    $needLoad = array();

    foreach ($ids as $id) {
        if (isset($cache[$id])) {
            $result[$id] = clone $cache[$id];
        } else {
            $needLoad[] = $id;
        }
    }

    if (!empty($needLoad)) {
        $placeholders = implode(',', array_fill(0, count($needLoad), '?'));
        $cols = implode(',', zhuangbei_columns());
        $stmt = $dblj->prepare("SELECT $cols FROM playerzhuangbei WHERE zbnowid IN ($placeholders)");
        $stmt->execute($needLoad);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $zb = merge_player_zhuangbei_with_config($row);
            if (!$zb) continue;

            $cache[(int)$zb->zbnowid] = clone $zb;
            $result[(int)$zb->zbnowid] = $zb;
        }
    }

    return $result;
}

function getplayer($sid,$dblj){
    $cachedPlayer = player_cache_get($sid);
    if ($cachedPlayer) {
        return $cachedPlayer;
    }

    $player = new player();
    $cols = implode(',', player_allowed_columns());
    $stmt = $dblj->prepare("SELECT $cols FROM game1 WHERE sid = ? LIMIT 1");
    $stmt->execute(array($sid));
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        return $player;
    }

    hydrate_object_from_row($player, $row);
    $player->tuposjs = 0;
    $player->tupoexp = 0;


    // Gom toàn bộ trang bị đang mặc và lấy bằng 1 truy vấn IN thay vì gọi getzb() 16 lần.
    $toolIds = array();
    for ($i = 1; $i <= 16; $i++) {
        $toolKey = 'tool' . $i;
        $toolId = isset($player->$toolKey) ? intval($player->$toolKey) : 0;
        if ($toolId > 0) {
            $toolIds[] = $toolId;
        }
    }

    // Lưu danh sách trang bị đang mặc để tính Thần Trang sau khi cộng chỉ số cơ bản.
    $equippedZbs = array();
    // Hệ đã nạp trên trang bị: gom set toàn bộ 16 ô; zb_element giữ "hệ đại diện"
    // (hệ đầu theo thứ tự chuẩn của món rarity cao nhất có hệ) để tương thích cũ.
    $zbElementAttack = '';
    $zbElementBestRarity = 0;
    $zbElementSet = array();

    if (!empty($toolIds)) {
        $zbs = getzbsByIds($toolIds, $dblj);
        foreach ($toolIds as $toolId) {
            if (isset($zbs[$toolId]) && $zbs[$toolId]) {
                $zhuangbei = $zbs[$toolId];
                $equippedZbs[] = $zhuangbei;

                $player->ugj    += (int)$zhuangbei->zbgj;
                $player->ufy    += (int)$zhuangbei->zbfy;
                $player->ubj    += (int)$zhuangbei->zbbj;
                $player->uxx    += (int)$zhuangbei->zbxx;
                $player->umaxhp += (int)$zhuangbei->zbhp;
                $player->tuposjs += (int)$zhuangbei->tuposjs;
                $player->tupoexp += (int)$zhuangbei->tupoexp;

                $zbEls = zhuangbei_elements_parse(isset($zhuangbei->element) ? $zhuangbei->element : '');
                if (!empty($zbEls)) {
                    foreach ($zbEls as $el) {
                        if (!in_array($el, $zbElementSet, true)) $zbElementSet[] = $el;
                    }
                    $zbRa = isset($zhuangbei->rarity) ? intval($zhuangbei->rarity) : 1;
                    if ($zbRa > $zbElementBestRarity) {
                        $zbElementBestRarity = $zbRa;
                        $zbElementAttack = $zbEls[0];
                    }
                }
            }
        }
    }

    // Element tấn công trang bị (dùng trong combat tương khắc, ưu tiên hơn linh căn).
    $player->zb_element = $zbElementAttack;
    $player->zb_element_set = $zbElementSet;

    // Cộng thuộc tính võ công đang trang bị vào thuộc tính tổng của nhân vật.
    $wgid = isset($player->wugong) ? intval($player->wugong) : 0;
    if ($wgid > 0) {
        $cxwg = wgcx($wgid, $player->sid, $dblj);
        if ($cxwg) {
            $wgdj = max(0, intval($cxwg->wgdj));
            $player->ugj    += intval($cxwg->add_gj) * $wgdj;
            $player->ufy    += intval($cxwg->add_fy) * $wgdj;
            $player->umaxhp += intval($cxwg->add_hp) * $wgdj;
            $player->ubj    += intval($cxwg->add_bj) * $wgdj;
            $player->uxx    += intval($cxwg->add_xx) * $wgdj;
        }
    }

    // Cộng buff Thần Trang theo cấu hình JSON.
    // Buff chỉ áp dụng vào object runtime, không lưu thẳng vào game1 để tránh cộng dồn sai.
    if (function_exists(__NAMESPACE__ . '\\taozhuang_apply_buff_to_player')) {
        taozhuang_apply_buff_to_player($player, $equippedZbs);
    }

    global $rangesjj;
    global $rangesjd;
    global $rangeslv;
    global $jingjieColor;

    $rangesexp = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55);
    $playernextlv = (int)$player->ulv + 1;

    // rangeslv đang tăng đều 50 cấp nên tính trực tiếp, không cần duyệt toàn bộ mảng.
    $i = (int)floor(max(0, (int)$player->ulv) / 50);
    if ($i >= count($rangeslv) - 1) {
        $i = count($rangeslv) - 2;
    }

    $djc = (int)$player->ulv - $rangeslv[$i];
    $jds = ($rangeslv[$i+1] - $rangeslv[$i]) / 50;
    $jieduan = (int)floor($djc / $jds);
    if ($jieduan < 0) $jieduan = 0;
    if ($jieduan > 100) $jieduan = 100;

    $jd = isset($rangesjd[$jieduan]) ? $rangesjd[$jieduan] : '';
    $player->jingjieValue = $i + 1;
    $player->jingjie = getJingJieHtml((int)$player->ulv, $rangesjj, $jingjieColor);
    $player->cengci = ($jieduan == 0 && (int)$player->ulv > 1) ? '' : $jd;
    $player->jingjieCengci = ($jieduan == 0 && (int)$player->ulv > 1) ? $player->cengci . $player->jingjie : $player->jingjie . $player->cengci;

    // ===== BUFF CÔNG PHÁP (Passive) =====
    // Công pháp buff trạng thái bị động, áp dụng SAU TẤT CẢ buffs khác.
    // absorb_speed có trade-off: giảm %công và %thủ.
    $player->cp_absorb_speed = 0;
    $player->cp_effects = array();
    $cp_penalty_atk = 0;
    $cp_penalty_def = 0;

    if (function_exists(__NAMESPACE__ . '\\congphap_calc_buffs')) {
        $cpBuffs = congphap_calc_buffs($player->sid, $dblj);
        $player->cp_absorb_speed = $cpBuffs['absorb_speed'];
        $player->cp_effects = $cpBuffs['effects'];
        $cp_penalty_atk = $cpBuffs['penalty_atk'];
        $cp_penalty_def = $cpBuffs['penalty_def'];
    }

    // Áp dụng penalty từ absorb_speed (giảm %công và %thủ)
    if ($cp_penalty_atk > 0) {
        $player->ugj = max(1, intval($player->ugj * (100 - min(80, $cp_penalty_atk)) / 100));
    }
    if ($cp_penalty_def > 0) {
        $player->ufy = max(1, intval($player->ufy * (100 - min(80, $cp_penalty_def)) / 100));
    }
    // Công pháp atk_bonus / def_bonus (% công, % thủ) — hệ số + trần trong wugong_skill_config.json.
    if (function_exists(__NAMESPACE__ . '\\combat_cp_value')) {
        $cpAtkPct = combat_cp_value($player, 'atk_bonus');
        $cpDefPct = combat_cp_value($player, 'def_bonus');
        if ($cpAtkPct > 0) $player->ugj = intval($player->ugj * (100 + $cpAtkPct) / 100);
        if ($cpDefPct > 0) $player->ufy = intval($player->ufy * (100 + $cpDefPct) / 100);
    }
    // Cộng Hưởng (Tàng Kinh Các): % Công / Phòng / Khí huyết theo số bộ Viên Mãn.
    $player->conghuong_bonus = array('atk' => 0, 'def' => 0, 'hp' => 0);
    if (function_exists(__NAMESPACE__ . '\\conghuong_progress')) {
        $chRes = conghuong_progress($player->sid, $dblj);
        $player->conghuong_bonus = $chRes['bonus'];
        if ($chRes['bonus']['atk'] > 0) $player->ugj = intval($player->ugj * (100 + $chRes['bonus']['atk']) / 100);
        if ($chRes['bonus']['def'] > 0) $player->ufy = intval($player->ufy * (100 + $chRes['bonus']['def']) / 100);
        if ($chRes['bonus']['hp'] > 0) $player->umaxhp = intval($player->umaxhp * (100 + $chRes['bonus']['hp']) / 100);
    }

    // ===== BUFF LINH CĂN (Passive) =====
    // Linh căn buff thuộc tính + pet bonus, áp dụng SAU congphap.
    $player->linhcan_absorb = 0;
    $player->linhcan_exp_bonus = 0;
    $player->linhcan_stats = array('atk' => 0, 'def' => 0, 'hp' => 0, 'crit' => 0, 'lifesteal' => 0);
    $player->linhcan_pet_bonus = array('atk' => 0, 'hp' => 0, 'exp' => 0, 'crit' => 0);
    $player->linhcan_name = '';
    $player->linhcan_element = '';
    $player->linhcan_color = '';
    $player->linhcan_tier = 0;

    if (function_exists(__NAMESPACE__ . '\\linhcan_calc_bonuses')) {
        $lcB = linhcan_calc_bonuses($player->sid, $dblj);
        $player->linhcan_absorb = $lcB['absorb_bonus'] - $lcB['penalty_absorb_percent'];
        $player->linhcan_exp_bonus = $lcB['exp_bonus'];
        $player->linhcan_stats = $lcB['stats'];
        $player->linhcan_pet_bonus = array(
            'atk' => $lcB['pet_atk_bonus'],
            'hp' => $lcB['pet_hp_bonus'],
            'exp' => $lcB['pet_exp_bonus'],
            'crit' => $lcB['pet_crit_bonus']
        );

        // Áp dụng stats từ linh căn
        $player->ugj    += $lcB['stats']['atk'];
        $player->ufy    += $lcB['stats']['def'];
        $player->umaxhp += $lcB['stats']['hp'];
        $player->ubj    += $lcB['stats']['crit'];
        $player->uxx    += $lcB['stats']['lifesteal'];

        // Áp dụng penalty từ linh căn
        if ($lcB['penalty_atk_percent'] > 0) {
            $player->ugj = max(1, intval($player->ugj * (100 - min(80, $lcB['penalty_atk_percent'])) / 100));
        }
        if ($lcB['penalty_def_percent'] > 0) {
            $player->ufy = max(1, intval($player->ufy * (100 - min(80, $lcB['penalty_def_percent'])) / 100));
        }
        if ($lcB['penalty_maxhp_percent'] > 0) {
            $player->umaxhp = max(1, intval($player->umaxhp * (100 - min(80, $lcB['penalty_maxhp_percent'])) / 100));
        }
        if ($lcB['penalty_crit_percent'] > 0) {
            $player->ubj = max(0, intval($player->ubj * (100 - min(80, $lcB['penalty_crit_percent'])) / 100));
        }

        // Thong tin hien thi — nguon node Ngu Hanh (khong doc config 22 can ngau nhien nua).
        $lcNodes = linhcan_node_rows($player->sid, $dblj);
        $lcTitle = linhcan_title($lcNodes);
        $lcTotalLv = 0;
        foreach ($lcNodes as $ndLc) { $lcTotalLv += intval($ndLc['level']); }
        $player->linhcan_name     = $lcTitle['name'];
        $player->linhcan_color    = $lcTitle['color'];
        $player->linhcan_icon     = $lcTitle['icon'];
        $player->linhcan_title_id = $lcTitle['id'];
        $player->linhcan_total_lv = $lcTotalLv;
        $player->linhcan_tier     = 0;
        $player->linhcan_element  = $lcB['element'];
        if ($lcB['element'] !== '') {
            $player->linhcan_tier = intval($lcNodes[$lcB['element']]['stage']);
        }
    }

    // ===== NỘI TẠI NẠP HÀNH (spec DESIGN_NGUHANH_KICHHOAT.md §4) =====
    // Đọc linhcan_element vừa gán ở khối linh căn trên; cộng % chỉ số trang bị theo nấc hợp căn.
    if (function_exists(__NAMESPACE__ . '\\nguhanh_apply_passive')) {
        nguhanh_apply_passive($player, $equippedZbs);
    }

    // ===== BUFF THỂ CHẤT (Passive) =====
    // LƯU Ý: KHÔNG reset $player->thechat_id về 0 ở đây, vì các trang hiển thị
    // (thechat.php, thechat_upgrade.php) và hàm nâng phẩm đọc $player->thechat_id
    // để biết người chơi đã có Thể Chất hay chưa.
    // thechat_calc_bonuses() tự đọc từ DB nên việc giữ nguyên thechat_id (từ player_allowed_columns) là an toàn.
    $player->thechat_quality = 1;
    $player->thechat_awaken_stage = 0;
    $player->thechat_name = '';
    $player->thechat_element = '';
    $player->thechat_color = '#9e9e9e';
    $player->thechat_icon = '🧑';
    $player->thechat_stats = array('hp' => 0, 'atk' => 0, 'def' => 0, 'crit' => 0);
        $player->thechat_passive_exp = 0;
    $player->thechat_passive_regen = 0;
    $player->thechat_passive_resist = 0;
    $player->thechat_passive_reduce = 0;
    $player->thechat_category_id = 0;
    $player->thechat_category_name = '';

    if (function_exists(__NAMESPACE__ . '\\thechat_calc_bonuses')) {
        $tcB = thechat_calc_bonuses($player->sid, $dblj);
        $player->thechat_quality = $tcB['quality'];
        $player->thechat_awaken_stage = $tcB['awaken_stage'];
        $player->thechat_stats = $tcB['stats'];
        $player->thechat_passive_exp = $tcB['passive_exp'];
        $player->thechat_passive_regen = $tcB['passive_regen'];
        $player->thechat_passive_resist = $tcB['passive_resist'];
        $player->thechat_passive_reduce = $tcB['passive_reduce'];
        $player->thechat_category_id = $tcB['category_id'];
        $player->thechat_category_name = $tcB['category_name'];
        $player->thechat_name = $tcB['name'];
        $player->thechat_element = $tcB['element'];
        $player->thechat_color = $tcB['color'];
        $player->thechat_icon = $tcB['icon'];

        $player->umaxhp += $tcB['stats']['hp'];
        $player->ugj    += $tcB['stats']['atk'];
        $player->ufy    += $tcB['stats']['def'];
        $player->ubj    += $tcB['stats']['crit'];
        $player->uxx    += round(floatval($tcB['stats']['lifesteal']));
    }

    // ===== BUFF HUYẾT MẠCH (Passive) =====
    // KHÔNG reset $player->huyetmach_id về 0 (tương tự Thể Chất) để trang hiển thị đọc đúng.
    $player->huyetmach_quality = 1;
    $player->huyetmach_awaken_stage = 0;
    $player->huyetmach_name = '';
    $player->huyetmach_element = '';
    $player->huyetmach_color = '#9e9e9e';
    $player->huyetmach_icon = '🩸';
    $player->huyetmach_stats = array('atk' => 0, 'hp' => 0, 'crit' => 0, 'lifesteal' => 0);
    $player->huyetmach_vessel = array();
    $player->huyetmach_element_dmg = 0;
    $player->huyetmach_passive_element = 0;
    $player->huyetmach_passive_crit = 0;
    $player->huyetmach_passive_exp = 0;
    $player->huyetmach_passive_drop = 0;
    $player->huyetmach_can_transform = false;

    if (function_exists(__NAMESPACE__ . '\\huyetmach_calc_bonuses')) {
        $hmB = huyetmach_calc_bonuses($player->sid, $dblj);
        $player->huyetmach_quality = $hmB['quality'];
        $player->huyetmach_awaken_stage = $hmB['awaken_stage'];
        $player->huyetmach_stats = $hmB['stats'];
        $player->huyetmach_vessel = isset($hmB['vessel']) ? $hmB['vessel'] : array();
        $player->huyetmach_element_dmg = $hmB['element_dmg'];
        $player->huyetmach_passive_element = $hmB['passive_element'];
        $player->huyetmach_passive_crit = $hmB['passive_crit'];
        $player->huyetmach_passive_exp = $hmB['passive_exp'];
        $player->huyetmach_passive_drop = $hmB['passive_drop'];
        $player->huyetmach_name = $hmB['name'];
        $player->huyetmach_element = $hmB['element'];
        $player->huyetmach_color = $hmB['color'];
        $player->huyetmach_icon = $hmB['icon'];
        $player->huyetmach_can_transform = $hmB['can_transform'];

        $player->ugj    += $hmB['stats']['atk'];
        $player->umaxhp += $hmB['stats']['hp'];
        $player->ubj    += $hmB['stats']['crit'];
        $player->uxx    += $hmB['stats']['lifesteal'];

        // Mang mach huyet: cong don 4 chi so duong, chi so am ap o Task 11
        if (!empty($hmB['vessel']) && is_array($hmB['vessel'])) {
            $player->ugj    += intval($hmB['vessel']['atk']);
            $player->umaxhp += intval($hmB['vessel']['hp']);
            $player->ubj    += intval($hmB['vessel']['crit']);
            $player->uxx    += intval($hmB['vessel']['lifesteal']);
        }
    }
    // Thien Phu: bonus 7 cong + anh xa chu phu. KHONG cong ugj/ufy/umaxhp/ubj/uxx
    // (ranh gioi cua 3 he truoc; thien phu chi lam viec o ti le, xem spec §1).
    $player->tianfu_bonus = array();
    $player->tianfu_name  = '';
    $player->tianfu_color = '#9e9e9e';
    $player->tianfu_icon  = '';
    if (function_exists(__NAMESPACE__ . '\\tianfu_bonuses')) {
        $tfB = tianfu_bonuses($player->sid, $dblj);
        $player->tianfu_bonus = $tfB;

        // Tu canh minh TRUOC khi lay ten: ban sao game1 lech so voi player_tianfu
        // (thua tu he cu, ghi that bai giua chung, hoac lan dau sau migration)
        // -> viet lai roi moi doc chu phu, neu khong UI se thay ten trong cay.
        if ($tfB['power'] !== intval($player->tianfu_power)) {
            $mirror = tianfu_recalc_mirror($player->sid, $dblj);
            $player->tianfu_id      = $mirror['tianfu_id'];
            $player->tianfu_quality = $mirror['tianfu_quality'];
            $player->tianfu_lv      = $mirror['tianfu_lv'];
            $player->tianfu_power   = $mirror['tianfu_power'];
        }

        $tfMaster = intval($player->tianfu_id);
        if ($tfMaster > 0) {
            $tfDef = tianfu_def($tfMaster);
            if ($tfDef) {
                // Ten lay cua thien phu, mau + icon lay theo PHAM CHAT (giong cach
                // thechat/huyetmach doi mau pham chat), khong lay mau cua nhom.
                $player->tianfu_name  = $tfDef['name'];
                $player->tianfu_color = tianfu_quality_color(intval($player->tianfu_quality));
                $player->tianfu_icon  = tianfu_quality_icon(intval($player->tianfu_quality));
            }
        }
    }

    $expRateIndex = min($i, count($rangesexp) - 1);
    // ===== EXP FORMULA - cap^2 x 110 =====
    // Cap 91 = 910,910 EXP (~5.6 ngay tu luyen free)
    $nlv = (int)$playernextlv;
    $player->umaxexp = max(50, floor($nlv * $nlv * 110));

    // Reconcile stored HP with the real max (base + all buffs above).
    // Stored HP can exceed it after gear is sold or unequipped.
    $realMaxHp = intval($player->umaxhp);
    if ($realMaxHp > 0 && intval($player->uhp) > $realMaxHp) {
        $player->uhp = $realMaxHp;
        $reconcile = $dblj->prepare("UPDATE game1 SET uhp = ? WHERE sid = ?");
        $reconcile->execute(array($realMaxHp, $sid));
    }

    player_cache_set($sid, $player);
    return $player;
}
function getplayer1($uid,$dblj){
    $stmt = $dblj->prepare("SELECT sid FROM game1 WHERE uid = ? LIMIT 1");
    $stmt->execute(array($uid));
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$row) {
        return false;
    }
    return getplayer($row['sid'], $dblj);
}

class guaiwu
{
    var $gname;// Biệt danh
    var $ginfo;
    var $gsex;
    var $gzb;// Trang bị
    var $dljv;// Trang bị tỉ lệ
    var $gdj;// Đạo cụ
    var $djjv;// Đạo cụ tỉ lệ
    var $gyp;// Dược phẩm
    var $ypjv;// Dược phẩm tỉ lệ
    var $gid;
    var $sid;
    var $glv;
    var $gexp;// Kinh nghiệm
    var $ghp;// Sinh mệnh
    var $gmaxhp;
    var $ggj;// Công kích
    var $gfy;// Phòng ngự
    var $gbj;// Bạo kích
    var $gxx;// Hút máu
    var $gyid;
    var $jingjie;
    var $element = '';// Ngũ hành element
}

function getguaiwu($gid,$dblj){//bắt quái vật
    $guaiwu = new guaiwu();

    $sql = "select * from midguaiwu where id = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array(intval($gid)));

    $cxjg->bindColumn('gname',$guaiwu->gname);
    $cxjg->bindColumn('id',$guaiwu->gid);
    $cxjg->bindColumn('sid',$guaiwu->sid);
    $cxjg->bindColumn('glv',$guaiwu->glv);
    $cxjg->bindColumn('gexp',$guaiwu->gexp);
    $cxjg->bindColumn('ghp',$guaiwu->ghp);
    $cxjg->bindColumn('gmaxhp',$guaiwu->gmaxhp);
    $cxjg->bindColumn('ggj',$guaiwu->ggj);
    $cxjg->bindColumn('gfy',$guaiwu->gfy);
    $cxjg->bindColumn('gbj',$guaiwu->gbj);
    $cxjg->bindColumn('gxx',$guaiwu->gxx);
    $cxjg->bindColumn('gyid',$guaiwu->gyid);
    $cxjg->fetch(\PDO::FETCH_ASSOC);

    // Load element từ config nếu có
    if (function_exists(__NAMESPACE__ . '\\guaiwu_config_get') && !empty($guaiwu->gyid)) {
        $gcfg = guaiwu_config_get($guaiwu->gyid);
        if ($gcfg && isset($gcfg['element'])) {
            $guaiwu->element = $gcfg['element'];
        }
    }

    global $rangeslv;
    global $rangesjj; // Cảnh giới
	
    for ($i=0;$i < count($rangeslv) - 1;$i++){
        if ($guaiwu->glv >= $rangeslv[$i] && $guaiwu->glv < $rangeslv[$i+1]){
            $rangesjd = array(' 1',' 2',' 3',' 4',' 5',' 6',' 7',' 8',' 9',' 10');
            $djc = $guaiwu->glv - $rangeslv[$i];
            $jds = ($rangeslv[$i+1]-$rangeslv[$i])/10;
            $jieduan = floor($djc/$jds);
            $jd = $rangesjd[$jieduan];
			$guaiwu->jingjieClass = $rangesjj[$i];
            $guaiwu->jingjie = $rangesjj[$i].$jd.' Tầng';
            break;
        }
    }

    return $guaiwu;
}
function getyguaiwu($gyid,$dblj){// Lấy cấu hình quái vật từ JSON
    $guaiwu = new guaiwu();
    $config = guaiwu_config_get($gyid);

    if ($config) {
        return hydrate_guaiwu_config_to_object($guaiwu, $config);
    }

    // Dự phòng: nếu file JSON thiếu dữ liệu thì vẫn thử đọc DB để tránh trắng trang.
    $gyid = intval($gyid);
    $sql = "select * from guaiwu where id = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array($gyid));
    if ($cxjg){
        $cxjg->bindColumn('gname',$guaiwu->gname);
        $cxjg->bindColumn('ginfo',$guaiwu->ginfo);
        $cxjg->bindColumn('gsex',$guaiwu->gsex);
        $cxjg->bindColumn('glv',$guaiwu->glv);
        $cxjg->bindColumn('gzb',$guaiwu->gzb);
        $cxjg->bindColumn('gdj',$guaiwu->gdj);
        $cxjg->bindColumn('ghp',$guaiwu->ghp);
        $cxjg->bindColumn('ggj',$guaiwu->ggj);
        $cxjg->bindColumn('gfy',$guaiwu->gfy);
        $cxjg->bindColumn('gbj',$guaiwu->gbj);
        $cxjg->bindColumn('gxx',$guaiwu->gxx);
        $cxjg->bindColumn('gyp',$guaiwu->gyp);
        $cxjg->bindColumn('dljv',$guaiwu->dljv);
        $cxjg->bindColumn('ypjv',$guaiwu->ypjv);
        $cxjg->bindColumn('djjv',$guaiwu->djjv);
        $cxjg->bindColumn('gexp',$guaiwu->gexp);
        $cxjg->bindColumn('ms',$guaiwu->ms);
        $cxjg->fetch(\PDO::FETCH_ASSOC);
    }

    $guaiwu->gid = $gyid;
    $guaiwu->gyid = $gyid;

    if (!isset($guaiwu->gexp) || $guaiwu->gexp === null) {
        $guaiwu->gexp = 0;
    }

    return $guaiwu;
}

function getyboss($bossid,$dblj){//获取怪物库boss,boss,加药
    $boss = new boss();
    $sql = "select * from boss where id = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array(intval($bossid)));
    if ($cxjg){
        $cxjg->bindColumn('bossname',$boss->bossname);
        $cxjg->bindColumn('bossinfo',$boss->bossinfo);
        // $cxjg->bindColumn('bosssex',$boss->bosssex);
        $cxjg->bindColumn('bosslv',$boss->glv);
        $cxjg->bindColumn('bosszb',$boss->bosszb);
        $cxjg->bindColumn('bossdj',$boss->bossdj);
        $cxjg->bindColumn('bosshp',$boss->bosshp);
        $cxjg->bindColumn('bossgj',$boss->bossgj);
        $cxjg->bindColumn('bossfy',$boss->bossfy);
        $cxjg->bindColumn('bossbj',$boss->bossbj);
        $cxjg->bindColumn('bossxx',$boss->bossxx);
		$cxjg->bindColumn('dllv',$boss->dllv);
		$cxjg->bindColumn('djlv',$boss->djlv);
		$cxjg->bindColumn('sid',$boss->sid);
        $cxjg->bindColumn('bossyp',$boss->bossyp);
       $cxjg->bindColumn('dljv',$boss->dljv);
        $cxjg->bindColumn('ypjv',$boss->ypjv);
        $cxjg->bindColumn('djjv',$boss->djjv);
        $cxjg->fetch(\PDO::FETCH_ASSOC);
    }
    return $boss;
}



class clmid
{
    var $mname;//
    var $mgid;
    var $mid;//
    var $mnpc;//经验
    var $upmid;
    var $downmid;
    var $leftmid;
    var $rightmid;
    var $mgtime;
    var $midboss;
    var $ms;
    var $midinfo;
    var $mqy;
    var $playerinfo;
    var $ispvp;
}

function getmid($mid,$dblj){
    $clmid = new clmid();
    $sql = "select * from mid where mid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array($mid));
    $cxjg->bindColumn('mname',$clmid->mname);
    $cxjg->bindColumn('mgid',$clmid->mgid);
    $cxjg->bindColumn('mid',$clmid->mid);
    $cxjg->bindColumn('mup',$clmid->upmid);
    $cxjg->bindColumn('mdown',$clmid->downmid);
    $cxjg->bindColumn('mleft',$clmid->leftmid);
    $cxjg->bindColumn('mright',$clmid->rightmid);
    $cxjg->bindColumn('mnpc',$clmid->mnpc);
    $cxjg->bindColumn('mgtime',$clmid->mgtime);
    $cxjg->bindColumn('midboss',$clmid->midboss);
    $cxjg->bindColumn('ms',$clmid->ms);
    $cxjg->bindColumn('midinfo',$clmid->midinfo);
    $cxjg->bindColumn('mqy',$clmid->mqy);
    $cxjg->bindColumn('playerinfo',$clmid->playerinfo);
    $cxjg->bindColumn('ispvp',$clmid->ispvp);
    $cxjg->fetch(\PDO::FETCH_ASSOC);
    return $clmid;
}
function istupo($sid,$dblj){
	global $rangesjj; // Cảnh giới
	global $rangesjd; // Cấp bậc
	global $rangeslv; // Khoảng cách cấp bậc mỗi cảnh giới
	global $jingjieColor;

	$player = getplayer($sid,$dblj);
    $playernextlv = $player->ulv + 1;
	
    for ($i=0;$i < count($rangeslv) - 1;$i++){
        if ($playernextlv >= $rangeslv[$i] && $playernextlv < $rangeslv[$i+1]){
			// Kiểm tra cảnh giới
            if($player->jingjie != getJingJieHtml($player->ulv + 1)){
                return 1;//Đột Phá Cảnh Giới
            }
			
            $djc = $playernextlv - $rangeslv[$i];
            $jds = ($rangeslv[$i+1]-$rangeslv[$i])/50;
            $jieduan = floor($djc/$jds);
			
            $jd = $rangesjd[$jieduan];
            if($player->cengci != $jd){ // Code cũ $jd.' Tầng'
                return 2;//Cấp độ đột phá
            }
            return 0;
        }
    }
}
function updateAttr($sid, $dblj, $col, $value, $mode = 'set'){
    $allowedCols = array('uexp','ulv');
    if (!in_array($col, $allowedCols, true)) return false;

    switch ($mode) {
        case 'add':
            $sql = "UPDATE game1 SET `$col` = `$col` + :val WHERE sid = :sid";
            break;

        case 'sub':
            $sql = "UPDATE game1 SET `$col` = `$col` - :val WHERE sid = :sid";
            break;

        default:
            $sql = "UPDATE game1 SET `$col` = :val WHERE sid = :sid";
            break;
    }

    $stmt = $dblj->prepare($sql);
    $res = $stmt->execute(array(
        ':val' => $value,
        ':sid' => $sid
    ));
    player_cache_clear($sid);
    return $res;
}
function changeexp($sid,$dblj,$exp){
    if (istupo($sid,$dblj) != 9){
        $player = getplayer($sid,$dblj);
        if($player->uexp >= $player->umaxexp){
            return;
        }
    }
    $stmt = $dblj->prepare("UPDATE game1 SET uexp = uexp + :exp WHERE sid = :sid");
    $stmt->execute(array(':exp' => $exp, ':sid' => $sid));
    player_cache_clear($sid);
    if (istupo($sid,$dblj) == 9){
        upplayerlv($sid,$dblj);
    }
}



// if($player->shenfen == 1){
// $zs=1 ;
// }else{$zs=0;}
// if($player->shenfen == 2){
// $xs=1 ;
// }else{$xs=0;}
// if($player->shenfen == 3){
// $ys=1 ;
// }else{$ys=0;}

//超过30级以后默认加成 突破加成
function upplayerlv($sid,$dblj){
    $player = getplayer($sid,$dblj);
    
    // Kiểm tra level lock do đan độc (>2000 toxin)
    if (\player\luonden_is_level_locked($sid, $dblj)) {
        return false; // Không thể lên cấp
    }
    
if($player->shenfen == 1){
$zs=1 ;
}else{$zs=0;}
if($player->shenfen == 2){
$xs=1 ;
}else{$xs=0;}
if($player->shenfen == 3){
$ys=1 ;
}else{$ys=0;}

    if ($player->uexp >= $player->umaxexp){
        $stmt = $dblj->prepare("UPDATE game1 SET uexp = uexp - :maxexp WHERE sid = :sid");
        $stmt->execute(array(':maxexp' => $player->umaxexp, ':sid' => $sid));
        player_cache_clear($sid);

        global $rangeslv;
        $rangesgj = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55);
        $rangesfy = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55);
        $rangeswx = array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34);
        $rangeshp = array(30, 50, 70, 90, 110, 130, 170, 210, 250, 300, 350, 400, 450, 500, 550);
		$rangesbj = array($zs, $zs, $zs, $zs, $zs, $zs*2, $zs*2, $zs*3, $zs*3, $zs*4, $zs*4, $zs*5, $zs*5, $zs*6, $zs*6);
		$rangesxx = array($xs, $xs, $xs, $xs, $xs*1.5, $xs*1.5, $xs*1.5, $xs*2, $xs*2, $xs*3, $xs*3, $xs*4, $xs*4, $xs*5, $xs*5);
		$rangesxy = array($ys, $ys, $ys, $ys, $ys*1.5, $ys*1.5, $ys*1.5, $ys*2, $ys*2, $ys*3, $ys*3, $ys*4, $ys*4, $ys*5, $ys*5);
		$tfhp1 	  = array($ys*2, $ys*3, $ys*3, $ys*4, $ys*4, $ys*5, $ys*10, $ys*15, $ys*15, $ys*20, $ys*20, $ys*25, $ys*25, $ys*30);
		$tffy1    = array($xs, $xs, $xs, $xs, $xs*1.5, $xs*1.5, $xs*1.5, $xs*2, $xs*2, $xs*3, $xs*3, $xs*4, $xs*4, $xs*5, $xs*5);
		$tfgj1    = array($zs, $zs, $zs, $zs, $zs*1.5, $zs*1.5, $zs*1.5, $zs*2, $zs*2, $zs*3, $zs*3, $zs*4, $zs*4, $zs*5, $zs*5);
        $playernextlv = $player->ulv + 1;

        for ($i=0;$i<count($rangeslv)-1;$i++){
            if ($playernextlv >= $rangeslv[$i] && $playernextlv < $rangeslv[$i+1]){
                // $rangeslv co 87 phan tu (0..4300) trong khi cac bang thuoc tinh chi
                // co 14-17 phan tu. Tu cap ~750 tro len, $bang[$i] khong ton tai =>
                // bind NULL => "umaxhp = umaxhp + NULL" set ca cot thanh NULL.
                $hi  = min($i, count($rangeshp) - 1);
                $gi  = min($i, count($rangesgj) - 1);
                $fi  = min($i, count($rangesfy) - 1);
                $bi  = min($i, count($rangesbj) - 1);
                $xi  = min($i, count($rangesxx) - 1);
                $yi  = min($i, count($rangesxy) - 1);
                $thi = min($i, count($tfhp1) - 1);
                $tfi = min($i, count($tffy1) - 1);
                $tgi = min($i, count($tfgj1) - 1);
                $wi  = min($i, count($rangeswx) - 1);
                $stmt = $dblj->prepare("UPDATE game1 SET ulv = ulv + 1,
                                           umaxhp = umaxhp + :hp,
                                           ugj = ugj + :gj,
                                           ufy = ufy + :fy,
                                           tfbj = tfbj + :bj,
                                           tfxx = tfxx + :xx,
                                           tfxy = tfxy + :xy,
                                           tfhp = tfhp + :tfhp,
                                           tffy = tffy + :tffy,
                                           tfgj = tfgj + :tfgj,
                                           uwx = uwx + :wx
                                           WHERE sid = :sid");
                $stmt->execute(array(
                    ':hp' => $rangeshp[$hi], ':gj' => $rangesgj[$gi], ':fy' => $rangesfy[$fi],
                    ':bj' => $rangesbj[$bi], ':xx' => $rangesxx[$xi], ':xy' => $rangesxy[$yi],
                    ':tfhp' => $tfhp1[$thi], ':tffy' => $tffy1[$tfi], ':tfgj' => $tfgj1[$tgi],
                    ':wx' => $rangeswx[$wi], ':sid' => $sid
                ));
                break;
            }
        }
        player_cache_clear($sid);
    }
}


class zhuangbei{
    var $zbname;
    var $zbinfo;
    var $zbgj;
    var $zbfy;
    var $zbbj;
    var $zbxx;
    var $zbid;
    var $uid;
    var $zbnowid;
    var $qianghua;
    var $zbhp;
    var $zblv;
    var $tool;
	var $zbys;
	var $tuposjs;
	var $tupoexp;
    var $taozhuang;
}
function getzb($zbnowid,$dblj){
    $zbnowid = intval($zbnowid);
    if ($zbnowid <= 0) return false;

    $zbs = getzbsByIds(array($zbnowid), $dblj);
    return isset($zbs[$zbnowid]) ? $zbs[$zbnowid] : false;
}
function getzbkzb($zbid, $dblj = null){
    $zbid = intval($zbid);
    if ($zbid <= 0) return false;

    $config = zhuangbei_config_get($zbid);
    if (!$config) {
        return false;
    }

    $zb = new zhuangbei();
    hydrate_zhuangbei_config_to_object($zb, $config);
    return $zb;
}

function addzb($sid, $zbid, $dblj){
    $player = getplayer($sid, $dblj);
    $zb = getzbkzb($zbid, $dblj);

    if (!$zb) {
        return false;
    }

    $stmt = $dblj->prepare("
        INSERT INTO playerzhuangbei
            (zbid, uid, sid, qianghua, zbgj, zbfy, zbhp, zbbj, zbxx)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute(array(
        $zb->zbid,
        $player->uid,
        $sid,
        0,
        $zb->zbgj,
        $zb->zbfy,
        $zb->zbhp,
        $zb->zbbj,
        $zb->zbxx
    ));

    return $dblj->lastInsertId();
}


class daoju{
    var $djname;
    var $djzl;
    var $djinfo;
    var $djid;
    var $djsum;//playerdaoju
    var $djyxb;
    var $djjg;
}
function adddj($sid,$djid,$djsum,$dblj){
    $player = getplayer($sid,$dblj);
    $stmt = $dblj->prepare("SELECT djid,djsum,sid,uid FROM playerdaoju WHERE sid = :sid AND djid = :djid");
    $stmt->execute(array(':sid' => $sid, ':djid' => $djid));
    $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($ret){
        $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + :djsum WHERE sid = :sid AND djid = :djid");
        $stmt->execute(array(':djsum' => $djsum, ':sid' => $sid, ':djid' => $djid));
    }else{
        $daoju = getdaoju($djid,$dblj);
        if ($daoju){
            $stmt = $dblj->prepare("INSERT INTO playerdaoju(djid,djsum,sid,uid) VALUES (:djid, :djsum, :sid, :uid)");
            $stmt->execute(array(':djid' => $daoju->djid, ':djsum' => $djsum, ':sid' => $sid, ':uid' => $player->uid));
        }
    }
    changerwyq1(1,$djid,$djsum,$sid,$dblj);

    if (intval($djsum) > 0 && function_exists('player\\nhiemvu_update_all')) {
        \player\nhiemvu_update_all($sid, 'collect', intval($djsum), $dblj, array('item_id' => intval($djid)));
    }
}

function changerwyq1($rwzl,$rwyq,$gaibian,$sid,$dblj){
    $tasks = getplayerrenwu($sid, $dblj);
    if (!$tasks) {
        return false;
    }

    foreach ($tasks as $task) {
        if (!isset($task['rwid']) || !isset($task['rwzt']) || intval($task['rwzt']) != 1) {
            continue;
        }
        if (!isset($task['rwzl']) || intval($task['rwzl']) != intval($rwzl)) {
            continue;
        }
        if (!isset($task['rwyq']) || intval($task['rwyq']) != intval($rwyq)) {
            continue;
        }

        $rwid = intval($task['rwid']);
        $need = isset($task['rwcount']) ? intval($task['rwcount']) : 0;

        if (intval($rwzl) == 1) {
            $daoju = getplayerdaoju($sid, $rwyq, $dblj);
            $now = $daoju ? intval($daoju->djsum) : 0;
            $stmt = $dblj->prepare("UPDATE playerrenwu SET rwnowcount = ? WHERE sid = ? AND rwid = ? AND rwzt = 1");
            $stmt->execute(array($now, $sid, $rwid));
        } else {
            $stmt = $dblj->prepare("UPDATE playerrenwu SET rwnowcount = rwnowcount + ? WHERE sid = ? AND rwid = ? AND rwzt = 1");
            $stmt->execute(array(intval($gaibian), $sid, $rwid));
            $now = isset($task['rwnowcount']) ? intval($task['rwnowcount']) + intval($gaibian) : intval($gaibian);
        }

        if ($need > 0 && $now >= $need) {
            $stmt = $dblj->prepare("UPDATE playerrenwu SET rwzt = 2 WHERE sid = ? AND rwid = ? AND rwzt = 1");
            $stmt->execute(array($sid, $rwid));
        }
    }
    return true;
}

function changerwyq($rwid,$gaibian,$sid,$dblj){
    $rwid = intval($rwid);
    $gaibian = intval($gaibian);
    $stmt = $dblj->prepare("UPDATE playerrenwu SET rwnowcount = ? WHERE sid = ? AND rwid = ? AND rwzt = 1");
    $stmt->execute(array($gaibian, $sid, $rwid));

    $task = getplayerrenwuonce($sid, $rwid, $dblj);
    if ($task && intval($task->rwcount) > 0 && intval($task->rwnowcount) >= intval($task->rwcount)) {
        $stmt2 = $dblj->prepare("UPDATE playerrenwu SET rwzt = 2 WHERE sid = ? AND rwid = ? AND rwzt = 1");
        $stmt2->execute(array($sid, $rwid));
    }
}



function getplayerdaoju($sid,$djid,$dblj){
    $daoju = new daoju();
    $sql = "select djid,djsum,djused from playerdaoju where djid = ? AND sid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array(intval($djid), $sid));
    $cxjg->bindColumn('djid',$daoju->djid);
    $cxjg->bindColumn('djsum',$daoju->djsum);
    $cxjg->bindColumn('djused',$daoju->djused);
    $ret = $cxjg->fetch(\PDO::FETCH_ASSOC);
    
    if ($ret){
        hydrate_daoju_config_to_object($daoju, daoju_config_get($daoju->djid));
        return $daoju;
    }else{
        return false;
    }

}

function deledjsum($djid,$djsum,$sid,$dblj){
    $daoju = \player\getplayerdaoju($sid,$djid,$dblj);

    if ($daoju){
        if ($daoju->djsum >= $djsum){
            $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - :djsum WHERE sid = :sid AND djid = :djid AND djsum >= :djsum2");
            $stmt->execute(array(':djsum' => $djsum, ':djsum2' => $djsum, ':sid' => $sid, ':djid' => $djid));
            return $stmt->rowCount() > 0;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

function getdaoju($djid,$dblj = null){
    $config = daoju_config_get($djid);
    if (!$config) {
        return false;
    }

    $daoju = new daoju();
    hydrate_daoju_config_to_object($daoju, $config);
    return $daoju;
}
function changezbsx($upsx,$changeint,$zbnowid,$dblj){
    $allowedUpsx = array('zbgj','zbfy','zbhp','zbbj','zbxx','qianghua');
    if (!in_array($upsx, $allowedUpsx, true)) return false;
    $zbnowid = intval($zbnowid);
    if ($zbnowid <= 0) return false;
    $stmt = $dblj->prepare("UPDATE playerzhuangbei SET `$upsx` = `$upsx` + :val WHERE zbnowid = :zbnowid");
    $stmt->execute(array(':val' => $changeint, ':zbnowid' => $zbnowid));
    zhuangbei_cache_clear($zbnowid);
    return $stmt->rowCount() > 0;
}

function upzbsx($zbnowid,$upsx,$sid,$dblj){
    $allowedUpsx = array('zbgj','zbfy','zbhp','zbbj','zbxx');
    if (!in_array($upsx, $allowedUpsx, true)) {
        return -1;
    }

    $zbnowid = intval($zbnowid);
    $stmt = $dblj->prepare("select pz.$upsx as zbsx, pz.qianghua, g.ulv from playerzhuangbei pz inner join game1 g on pz.sid = g.sid WHERE pz.zbnowid=? AND pz.sid=?");
    $stmt->execute(array($zbnowid, $sid));
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$row) {
        return -1;
    }

    $zbsx = intval($row['zbsx']);
    $zbqh = intval($row['qianghua']);
    $playerlv = intval($row['ulv']);
    if ($playerlv <= 0 || $zbqh >= $playerlv) {
        return -2;
    }

    $djsum = $zbqh *3+1;
    $ret = \player\deledjsum(1,$djsum,$sid,$dblj);
    if ($ret){
        $upint = round($zbsx*0.08);//强化成长，强化加成
        if ($upint<1){
            $upint = 1;
        }
        $sjs = mt_rand(1,35);
        if ($sjs <= $zbqh){
            return 0;//失败
        }
        $sjs = mt_rand(1,30);
        if ($zbqh <= $sjs){
            \player\changezbsx($upsx,$upint,$zbnowid,$dblj);
            changezbsx('qianghua',1,$zbnowid,$dblj);
            player_cache_clear($sid);
            return 1;
        }else{
            return 0;//失败
        }
    }else{
        return -1;//不足
    }
}
class yaopin{
    var $ypname;
    var $ypid;
    var $yphp;
    var $ypgj;
    var $ypfy;
    var $ypjg;
    var $ypbj;
    var $ypxx;
    var $ypsum;
}
class yaodan{
    var $ydname;
    var $ydid;
    var $ydhp;
    var $ydgj;
    var $ydfy;
    var $ydjg;
    var $ydbj;
    var $ydxx;
    var $ydsum;
	var $ydys;
	var $ydjgm;
}
function getyaopin($dblj = null){
    $all = yaopin_config_all();
    return array_values($all);
}
function getyaodan($dblj = null){
    $all = yaodan_config_all();
    return array_values($all);
}

function getyaopinonce($ypid, $dblj = null){
    $config = yaopin_config_get($ypid);
    if (!$config) {
        return false;
    }

    $yaopin = new yaopin();
    hydrate_yaopin_config_to_object($yaopin, $config);
    return $yaopin;
}
function getyaodanonce($ydid, $dblj = null){
    $config = yaodan_config_get($ydid);
    if (!$config) {
        return false;
    }

    $yaodan = new yaodan();
    hydrate_yaodan_config_to_object($yaodan, $config);
    return $yaodan;
}

function getplayeryaopin($ypid,$sid,$dblj){
    $yaopin = new yaopin();
    $sql = "select ypid,ypsum from playeryaopin WHERE ypid = ? AND sid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array(intval($ypid), $sid));
    $cxjg->bindColumn('ypid',$yaopin->ypid);
    $cxjg->bindColumn('ypsum',$yaopin->ypsum);
    $ret = $cxjg->fetch(\PDO::FETCH_ASSOC);
    if ($ret){
        hydrate_yaopin_config_to_object($yaopin, yaopin_config_get($yaopin->ypid));
        return $yaopin;
    }else{
        return false;
    }
}

function getplayeryaodan($ydid,$sid,$dblj){
    $yaodan = new yaodan();
    $sql = "select ydid,ydsum from playeryaodan WHERE ydid = ? AND sid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array(intval($ydid), $sid));
    $cxjg->bindColumn('ydid',$yaodan->ydid);
    $cxjg->bindColumn('ydsum',$yaodan->ydsum);
    $ret = $cxjg->fetch(\PDO::FETCH_ASSOC);
    if ($ret){
        hydrate_yaodan_config_to_object($yaodan, yaodan_config_get($yaodan->ydid));
        return $yaodan;
    }else{
        return false;
    }
}

function getplayeryaopinall($sid,$dblj){
    $sql = "select ypid,ypsum,sid from playeryaopin WHERE sid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array($sid));
    if ($cxjg){
        $ret = $cxjg->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($ret as $key => $row) {
            $config = yaopin_config_get(isset($row['ypid']) ? $row['ypid'] : 0);
            if ($config) {
                $ret[$key] = array_merge($config, $row);
            }
        }
        return $ret;
    }else{
        return false;
    }

}

function getplayeryaodanall($sid,$dblj){
    $sql = "select ydid,ydsum,sid from playeryaodan WHERE sid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array($sid));
    if ($cxjg){
        $ret = $cxjg->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($ret as $key => $row) {
            $config = yaodan_config_get(isset($row['ydid']) ? $row['ydid'] : 0);
            if ($config) {
                $ret[$key] = array_merge($config, $row);
            }
        }
        return $ret;
    }else{
        return false;
    }

}

function addyaopin($sid, $ypid, $ypsum, $dblj){
    $yaopin = getplayeryaopin($ypid, $sid, $dblj);
    if ($yaopin){
        $stmt = $dblj->prepare("UPDATE playeryaopin SET ypsum = ypsum + ? WHERE ypid = ? AND sid = ?");
        return $stmt->execute(array($ypsum, $ypid, $sid));
    }

    $yaopin = getyaopinonce($ypid);
    if (!$yaopin) {
        return false;
    }

    $stmt = $dblj->prepare("
        INSERT INTO playeryaopin
            (ypid, ypsum, sid)
        VALUES
            (?, ?, ?)
    " );

    return $stmt->execute(array(
        $yaopin->ypid,
        $ypsum,
        $sid
    ));
}

function addyaodan($sid,$ydid,$ydsum,$dblj){
    $yaodan = getplayeryaodan($ydid,$sid,$dblj);
    if ($yaodan){
        $stmt = $dblj->prepare("UPDATE playeryaodan SET ydsum = ydsum + ? WHERE ydid = ? AND sid = ?");
        return $stmt->execute(array($ydsum, $ydid, $sid));
    }

    $yaodan = getyaodanonce($ydid);
    if (!$yaodan) {
        return false;
    }

    $stmt = $dblj->prepare("
        INSERT INTO playeryaodan
            (ydid, ydsum, sid)
        VALUES
            (?, ?, ?)
    ");

    return $stmt->execute(array(
        $yaodan->ydid,
        $ydsum,
        $sid
    ));
}

function deleyaopin($sid,$ypid,$ypsum,$dblj){
    $yaopin = getplayeryaopin($ypid,$sid,$dblj);
    if ($yaopin){
        if ($yaopin->ypsum>=$ypsum){
            $stmt = $dblj->prepare("UPDATE playeryaopin SET ypsum = ypsum - :ypsum WHERE ypid = :ypid AND sid = :sid AND ypsum >= :ypsum2");
            $stmt->execute(array(':ypsum' => $ypsum, ':ypsum2' => $ypsum, ':ypid' => $ypid, ':sid' => $sid));
            return $stmt->rowCount() > 0;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

function deleyaodan($sid,$ydid,$ydsum,$dblj){
    $yaodan = getplayeryaodan($ydid,$sid,$dblj);
    if ($yaodan){
        if ($yaodan->ydsum>=$ydsum){
            $stmt = $dblj->prepare("UPDATE playeryaodan SET ydsum = ydsum - :ydsum WHERE ydid = :ydid AND sid = :sid AND ydsum >= :ydsum2");
            $stmt->execute(array(':ydsum' => $ydsum, ':ydsum2' => $ydsum, ':ydid' => $ydid, ':sid' => $sid));
            return $stmt->rowCount() > 0;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

function changeplayersx($sx,$gaibian,$sid,$dblj){
    if (!in_array($sx, game1_update_allowed_columns(), true)) return false;

    // Chi clamp chan DUOI (0). Chan TREN khong the lam trong SQL vi game1.umaxhp
    // la max CO BAN, con max thuc te dung hien thi = max co ban + buff trang bi
    // / vo cong / than trang (chi ton tai trong object tra ve boi getplayer()).
    // Neu dung LEAST(umaxhp, ...) o day se triet tieu moi luc hoi mau cua nguoi
    // choi mac do trang bi (vi du 35/1088 bi ep ve 35/35).
    $valueSql = $sx === 'uhp' ? 'GREATEST(0, :val)' : ':val';
    $sql = "UPDATE game1 SET `$sx` = $valueSql WHERE sid = :sid";
    $stmt = $dblj->prepare($sql);
    $res = $stmt->execute(array(
        ':val' => $gaibian,
        ':sid' => $sid
    ));
    player_cache_clear($sid);
    return $res;
}

/**
 * Trừ HP sủng vật theo cwid và CHO PHÉP HP ÂM.
 *
 * Vì sao cho phép âm?
 * - HP âm thể hiện sủng vật bị trọng thương.
 * - Khi trọng thương, sủng vật sẽ không được cộng sát thương trong chiến đấu.
 * - Người chơi phải cho sủng vật nghỉ ngơi / hồi phục rồi mới xuất chiến lại.
 *
 * Tuyệt đối không dùng WHERE sid tại đây, vì sẽ trừ máu toàn bộ sủng vật của người chơi.
 */
function subchongwuhp($cwid, $damage, $dblj){
    $cwid = intval($cwid);
    $damage = max(0, intval($damage));

    if ($cwid <= 0 || $damage <= 0) {
        return false;
    }

    $stmt = $dblj->prepare("UPDATE playerchongwu SET cwhp = cwhp - :damage WHERE cwid = :cwid");
    return $stmt->execute(array(
        ':damage' => $damage,
        ':cwid'   => $cwid
    ));
}


/**
 * Cho sủng vật nghỉ ngơi để hồi phục.
 *
 * Quy tắc:
 * - Hồi cwhp về cwmaxhp.
 * - Nếu sủng vật này đang xuất chiến thì tự tháo khỏi ô xuất chiến game1.cw = 0.
 * - Sau khi nghỉ ngơi xong, người chơi phải bấm Xuất chiến lại nếu muốn dùng tiếp.
 */
function restchongwu($cwid, $sid, $dblj){
    $cwid = intval($cwid);
    if ($cwid <= 0) return false;

    $stmt = $dblj->prepare("UPDATE playerchongwu SET cwhp = cwmaxhp WHERE cwid = ? AND sid = ?");
    $ok = $stmt->execute(array($cwid, $sid));

    if ($ok) {
        $stmt2 = $dblj->prepare("UPDATE game1 SET cw = 0 WHERE sid = ? AND cw = ?");
        $stmt2->execute(array($sid, $cwid));
        player_cache_clear($sid);
    }

    return $ok;
}

/**
 * Nếu sủng vật đang xuất chiến đã trọng thương thì tự cho nghỉ ngơi.
 * Hàm trả về:
 * - false: không có sủng vật trọng thương cần xử lý.
 * - object: thông tin sủng vật vừa bị tháo và hồi phục.
 */
function auto_rest_dead_chongwu($sid, $dblj){
    $player = getplayer($sid, $dblj);
    $cwid = isset($player->cw) ? intval($player->cw) : 0;

    if ($cwid <= 0) {
        return false;
    }

    $cw = getchongwu($cwid, $dblj);
    if (!$cw || intval($cw->cwhp) > 0) {
        return false;
    }

    restchongwu($cwid, $sid, $dblj);
    return $cw;
}


function addplayersx($sx,$gaibian,$sid,$dblj){
    if (!in_array($sx, game1_update_allowed_columns(), true)) return false;

    // Lower bound only: game1.umaxhp is the BASE max, the real max includes
    // runtime gear/martial-art/set buffs, so clamping to the column in SQL
    // would silently wipe healed HP of geared players. See changeplayersx().
    $deltaSql = $sx === 'uhp'
        ? "GREATEST(0, `$sx` + :val)"
        : "`$sx` + :val";
    $sql = "UPDATE game1 SET `$sx` = $deltaSql WHERE sid = :sid";
    $stmt = $dblj->prepare($sql);
    $res = $stmt->execute(array(
        ':val' => $gaibian,
        ':sid' => $sid
    ));
    player_cache_clear($sid);
    return $res;
}
// Linh Thạch
function changeyxb($lx,$gaibian,$sid,$dblj){
    // So am o mode 1 se tru tien nguoc, nen chan ngay dau ham.
    if (!is_numeric($gaibian) || $gaibian < 0) { return false; }
    if ($lx==1){
        $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + :val WHERE sid = :sid");
        $stmt->execute(array(':val' => $gaibian, ':sid' => $sid));
        player_cache_clear($sid);
        return true;
    }elseif($lx==2){
        // UPDATE voi :val = 0 khong doi dong nao => rowCount() = 0 => ham tung bao
        // "khong du Linh Thach" cho mot han dong mien phi.
        if ($gaibian == 0) { return true; }
        $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - :val WHERE sid = :sid AND uyxb >= :val2");
        $stmt->execute(array(':val' => $gaibian, ':val2' => $gaibian, ':sid' => $sid));
        player_cache_clear($sid);
        return $stmt->rowCount() > 0;
    }
}
// Ma Thạch
function changeczb($lx,$gaibian,$sid,$dblj){
    // So am o mode 1 se tru tien nguoc, nen chan ngay dau ham.
    if (!is_numeric($gaibian) || $gaibian < 0) { return false; }
    if ($lx==1){
        $stmt = $dblj->prepare("UPDATE game1 SET uczb = uczb + :val WHERE sid = :sid");
        $stmt->execute(array(':val' => $gaibian, ':sid' => $sid));
        player_cache_clear($sid);
        return true;
    }elseif($lx==2){
        // UPDATE voi :val = 0 khong doi dong nao => rowCount() = 0 => ham tung bao
        // "khong du Ma Thach" cho mot han dong mien phi.
        if ($gaibian == 0) { return true; }
        $stmt = $dblj->prepare("UPDATE game1 SET uczb = uczb - :val WHERE sid = :sid AND uczb >= :val2");
        $stmt->execute(array(':val' => $gaibian, ':val2' => $gaibian, ':sid' => $sid));
        player_cache_clear($sid);
        return $stmt->rowCount() > 0;
    }
}

function changeyd($lx,$gaibian,$sid,$dblj){
    $player = getplayer($sid,$dblj);
    if ($lx==1){
        $stmt = $dblj->prepare("UPDATE game1 SET ugj = ugj + :val WHERE sid = :sid");
        $stmt->execute(array(':val' => $gaibian, ':sid' => $sid));
        player_cache_clear($sid);
        return true;
    }elseif($lx==2){
        if ($player->ugj - $gaibian >= 0){
            $stmt = $dblj->prepare("UPDATE game1 SET ugj = ugj - :val WHERE sid = :sid");
            $stmt->execute(array(':val' => $gaibian, ':sid' => $sid));
            player_cache_clear($sid);
            return true;
        }else{
            return false;
        }
    }
}

class task{
    var $rwname;
    var $rwinfo;
    var $rwid;
    var $rwzl;
    var $rwyq;
    var $rwdj;
    var $rwzb;
    var $rwexp;
    var $rwyxb;
    var $sid;
    var $rwzt;//状态
    var $rwcount;
    var $rwyp;
    var $rwlx;
    var $rwnowcount;
    var $lastrwid;
    var $rwqy;
    var $rwjineng;
    var $nid;
}
function getplayerrenwu($sid,$dblj){
    $stmt = $dblj->prepare("SELECT rwid, sid, rwzt, rwnowcount, `data`, nid FROM playerrenwu WHERE sid = :sid");
    $stmt->execute(array(':sid' => $sid));
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($rows as $key => $row) {
        $config = renwu_config_get(isset($row['rwid']) ? $row['rwid'] : 0);
        if ($config) {
            $rows[$key] = array_merge($config, $row);
        }
    }
    return $rows;
}
function gettask($rwid, $dblj = null){
    $task = new task();
    $config = renwu_config_get($rwid);

    if (!$config) {
        return $task;
    }

    hydrate_renwu_config_to_object($task, $config);
    return $task;
}
function getplayerrenwuonce($sid,$rwid,$dblj){
    $task = new task();
    $stmt = $dblj->prepare("SELECT rwid, sid, rwzt, rwnowcount, `data`, nid FROM playerrenwu WHERE sid = :sid AND rwid = :rwid");
    $stmt->execute(array(':sid' => $sid, ':rwid' => $rwid));
    $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$ret){
        return false;
    }

    $config = renwu_config_get($rwid);
    if ($config) {
        foreach ($config as $key => $value) {
            $task->$key = $value;
        }
    }
    foreach ($ret as $key => $value) {
        $task->$key = $value;
    }
    return $task;
}
class boss{
    var $bossname;
    var $bosslv;
    var $bossid;
    var $bosstime;
    var $bs;
    var $bossinfo;
    var $bosshp;
    var $bossmaxhp;
    var $bossgj;
    var $bossfy;
    var $bossbj;
    var $bossxx;
    var $bossdj;
    var $bosszb;
	var $djjv;
	var $dljv;
	var $sid;
	var $ypjv;
	var $bossyp;
	var $element = '';// Ngũ hành element
}
function getboss($bossid,$dblj){
    $boss = new boss();
    $stmt = $dblj->prepare("SELECT * FROM boss WHERE bossid = :bossid");
    $stmt->execute(array(':bossid' => $bossid));
    $cxjg = $stmt;
    $cxjg->bindColumn('bossname',$boss->bossname);
    $cxjg->bindColumn('bossid',$boss->bossid);
    $cxjg->bindColumn('bosstime',$boss->bosstime);
    $cxjg->bindColumn('bs',$boss->bs);
    $cxjg->bindColumn('bossinfo',$boss->bossinfo);
    $cxjg->bindColumn('bosshp',$boss->bosshp);
	$cxjg->bindColumn('bossmaxhp',$boss->bossmaxhp);
    $cxjg->bindColumn('bosslv',$boss->bosslv);
    $cxjg->bindColumn('bossgj',$boss->bossgj);
    $cxjg->bindColumn('bossfy',$boss->bossfy);
    $cxjg->bindColumn('bossbj',$boss->bossbj);
    $cxjg->bindColumn('bossxx',$boss->bossxx);
    $cxjg->bindColumn('bossdj',$boss->bossdj);
    $cxjg->bindColumn('bosszb',$boss->bosszb);
	$cxjg->bindColumn('bossyp',$boss->bossyp);
	$cxjg->bindColumn('ypjv',$boss->ypjv);
	$cxjg->bindColumn('dljv',$boss->dljv);
	$cxjg->bindColumn('djjv',$boss->djjv);
	$cxjg->bindColumn('sid',$boss->sid);
	
    $row = $cxjg->fetch(\PDO::FETCH_ASSOC);

    // Load element từ row nếu column tồn tại
    if ($row && isset($row['element'])) {
        $boss->element = $row['element'];
    }

    return $boss;
}
function useyaopin($ypid,$ypsum,$sid,$dblj){
	$player = getplayer($sid,$dblj);
	
	// Máu nhỏ <= 0 thì không cho dùng thuốc
    if ($player->uhp<=0){
        return false;
    }
    $ret = deleyaopin($sid,$ypid,$ypsum,$dblj);
    if ($ret){
        // Giảm hiệu quả đan dược do đan độc (>500 toxin = -10% tối đa -50%)
        $pillPenalty = 0;
        $toxinEffects = \player\luonden_apply_toxin_effects($sid, $dblj);
        foreach ($toxinEffects as $eff) {
            if ($eff['type'] === 'pill_penalty') {
                $pillPenalty = $eff['value'];
                break;
            }
        }
        $multiplier = (100 - $pillPenalty) / 100;
        
        $hpc = $player->umaxhp - $player->uhp;
        $yaopin = getyaopinonce($ypid,$dblj);
        $healAmount = intval($yaopin->yphp * $multiplier);
        // Công pháp heal_bonus: chỉ tăng lượng hồi máu, không tăng chỉ số cộng vĩnh viễn của dược.
        if (function_exists(__NAMESPACE__ . '\\combat_cp_value')) {
            $healAmount = intval($healAmount * (100 + combat_cp_value($player, 'heal_bonus')) / 100);
        }
        if ($healAmount >= $hpc){
            addplayersx('uhp',$hpc,$sid,$dblj);
        }else{
            addplayersx('uhp',$healAmount,$sid,$dblj);
        }
        addplayersx('ugj', intval($yaopin->ypgj * $multiplier), $sid, $dblj);
        addplayersx('ufy', intval($yaopin->ypfy * $multiplier), $sid, $dblj);
        addplayersx('ubj', intval($yaopin->ypbj * $multiplier), $sid, $dblj);
        addplayersx('uxx', intval($yaopin->ypxx * $multiplier), $sid, $dblj);

        // Hồi Thể lực (cho phép vượt giới hạn tự nhiên, tối đa 2x umaxstamina) - dùng cho Thể Lực Đan
        if (isset($yaopin->ypstamina) && intval($yaopin->ypstamina) > 0) {
            $newStamina = intval($player->ustamina) + intval($yaopin->ypstamina);
            $maxStamina = intval($player->umaxstamina) * 2;
            if ($newStamina > $maxStamina) {
                $newStamina = $maxStamina;
            }
            changeplayersx('ustamina', $newStamina, $sid, $dblj);
        }
    }
    return $ret;
}

/**
 * Dùng đạo cụ đặc biệt (đan): Tẩy Tủy Đan / Tố Thể Đan / Tẩy Linh Đan.
 * Chỉ dùng được khi player CHƯA có hệ tương ứng.
 * Roll ra hệ với phẩm chất giới hạn 1-3 (thường ~ tốt 1 chút).
 * Trả về array('ok' => bool, 'msg' => string).
 */
function usedaoju($djid, $djsum, $sid, $dblj){
    $djid = intval($djid);
    $djsum = intval($djsum);
    if ($djsum <= 0) {
        return array('ok' => false, 'msg' => 'Không có đạo cụ này.');
    }

    $daoju = getdaoju($djid, $dblj);
    if (!$daoju || !isset($daoju->djuse) || $daoju->djuse == '') {
        return array('ok' => false, 'msg' => 'Đạo cụ này không thể sử dụng.');
    }

    $player = getplayer($sid, $dblj);

    // getplayer() reset thechat_id/linhcan_id/huyetmach_id về 0 (chỉ để hiển thị buff),
    // nên phải đọc giá trị thật từ DB để kiểm tra "đã có hệ chưa".
    $stmt = $dblj->prepare("SELECT thechat_id, linhcan_id, huyetmach_id FROM game1 WHERE sid = ?");
    $stmt->execute(array($sid));
    $realIds = $stmt->fetch(\PDO::FETCH_ASSOC);
    $realThechat = $realIds ? intval($realIds['thechat_id']) : 0;
    $realLinhcan = $realIds ? intval($realIds['linhcan_id']) : 0;
    $realHuyetmach = $realIds ? intval($realIds['huyetmach_id']) : 0;

    // Giới hạn số lần dùng: tối đa 2 lần mỗi loại đan
    $stmt = $dblj->prepare("SELECT djused FROM playerdaoju WHERE sid = ? AND djid = ?");
    $stmt->execute(array($sid, $djid));
    $usedRow = $stmt->fetch(\PDO::FETCH_ASSOC);
    $usedTimes = $usedRow ? intval($usedRow['djused']) : 0;
    if ($usedTimes >= 2) {
        return array('ok' => false, 'msg' => 'Đan này đã dùng tối đa 2 lần, không thể dùng tiếp.');
    }

    $use = $daoju->djuse;
    // Giới hạn phẩm chất: chỉ roll ra phẩm 1-3 (Phàm/Linh/Huyền), không quá 3
    $maxQuality = 3;

    $assigned = false;
    $quality = 1;

    if ($use == 'thechat') {
        if ($realThechat > 0) {
            return array('ok' => false, 'msg' => 'Ngươi đã có Thể Chất, không thể dùng Tẩy Tủy Đan.');
        }
        $roll = \player\thechat_roll();
        if (!$roll) return array('ok' => false, 'msg' => 'Roll Thể Chất thất bại.');
        $type = $roll['type'];
        $quality = min(intval($roll['quality']), $maxQuality);
        $id = intval($type['id']);
        $name = isset($type['name']) ? $type['name'] : 'Thể Chất';
        $assigned = \player\thechat_assign($sid, $dblj, $id, $quality);
    }
    elseif ($use == 'linhcan') {
        return array('ok' => false, 'msg' => 'Linh Căn không còn gán ngẫu nhiên. Mở trang Linh Căn để tự luyện từng hành.');
    }
    elseif ($use == 'huyetmach') {
        if ($realHuyetmach > 0) {
            return array('ok' => false, 'msg' => 'Ngươi đã có Huyết Mạch, không thể dùng Tẩy Linh Đan.');
        }
        $roll = \player\huyetmach_roll();
        if (!$roll) return array('ok' => false, 'msg' => 'Roll Huyết Mạch thất bại.');
        $type = $roll['type'];
        $quality = min(intval($roll['quality']), $maxQuality);
        $id = intval($type['id']);
        $name = isset($type['name']) ? $type['name'] : 'Huyết Mạch';
        $assigned = \player\huyetmach_assign($sid, $dblj, $id, $quality);
    }
    else {
        return array('ok' => false, 'msg' => 'Đạo cụ này không thể sử dụng.');
    }

    if (!$assigned) {
        return array('ok' => false, 'msg' => 'Gán hệ thất bại, đan không bị tiêu hao. Vui lòng thử lại.');
    }

    $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - 1, djused = djused + 1 WHERE sid = ? AND djid = ? AND djsum >= 1");
    $stmt->execute(array($sid, $djid));
    player_cache_clear($sid);

    $qName = \player\thechat_quality_name($quality);
    return array('ok' => true, 'msg' => "May mắn! Ngươi đạt được $name ($qName).", 'name' => $name, 'quality' => $quality);
}

function useyaodan($ydid,$ydsum,$sid,$dblj){
    $player = getplayer($sid,$dblj);//获取名称，没有的去第一行左右添加
    if ($player->ugj<=0){//判断条件执行下面
        return false;
    }
    $ret = deleyaodan($sid,$ydid,$ydsum,$dblj);
    if ($ret){

        $yaodan = getyaodanonce($ydid);//Dược phẩm thuộc tính
        if (!$yaodan) {
            return false;
        }

        addplayersx('ugj',$yaodan->ydgj,$sid,$dblj);
        addplayersx('ufy',$yaodan->ydfy,$sid,$dblj);
        addplayersx('ubj',$yaodan->ydbj,$sid,$dblj);
        addplayersx('uxx',$yaodan->ydxx,$sid,$dblj);
		addplayersx('uhp',$yaodan->ydhp,$sid,$dblj);

		// Hồi Thể lực (cho phép vượt giới hạn tự nhiên, tối đa 2x umaxstamina) - dùng cho Thể Lực Đan
		if (isset($yaodan->ydstamina) && intval($yaodan->ydstamina) > 0) {
			$newStamina = intval($player->ustamina) + intval($yaodan->ydstamina);
			$maxStamina = intval($player->umaxstamina) * 2;
			if ($newStamina > $maxStamina) {
				$newStamina = $maxStamina;
			}
			changeplayersx('ustamina', $newStamina, $sid, $dblj);
		}
    }
    return $ret;
}

class chongwu{
    var $cwname;
    var $cwlv;
    var $cwexp;
    var $cwmaxexp;
    var $cwhp;
    var $cwmaxhp;
    var $cwgj;
    var $cwfy;
    var $cwbj;
    var $cwxx;
    var $uphp;
    var $upgj;
    var $upfy;
    var $cwpz;
    var $tool1;
    var $tool2;
    var $tool3;
    var $tool4;
    var $tool5;
    var $tool6;
    var $tool7;
	/*
	var $tool8;
	var $tool9;
	var $tool10;
	var $tool11;
	var $tool12;
	var $tool13;
	var $tool14;
	var $tool15;
	var $tool16;
	*/
}

/**
 * @param $cwid
 * @param $dblj
 * @return chongwu//Sủng vật tăng thêm tính toán, ta cảm giác kinh nghiệm thu hoạch được tăng thêm cùng uexp Đồng dạng, quá nhiều, khó
 */
function getchongwu($cwid, $dblj){
    $chongwu = new chongwu();
    global $rangeslv; // Lên 120
    $rangesgj = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55); // Lên 20
    $rangesfy = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55); // Lên 20
    $rangeshp = array(30, 50, 70, 90, 110, 130, 170, 210, 250, 300, 350, 400, 450, 500, 550); // Lên 210
    $rangesexp = array(2, 4, 6, 9, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55); // Lên 20
    $sql = "select * from playerchongwu WHERE cwid = ? ";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array(intval($cwid)));
    $cxjg->bindColumn("cwname",$chongwu->cwname);
    $cxjg->bindColumn("cwlv",$chongwu->cwlv);
    $cxjg->bindColumn("cwexp",$chongwu->cwexp);
    $cxjg->bindColumn("cwhp",$chongwu->cwhp);
    $cxjg->bindColumn("cwmaxhp",$chongwu->cwmaxhp);
    $cxjg->bindColumn("cwgj",$chongwu->cwgj);
    $cxjg->bindColumn("cwfy",$chongwu->cwfy);
    $cxjg->bindColumn("cwbj",$chongwu->cwbj);
    $cxjg->bindColumn("cwxx",$chongwu->cwxx);
    $cxjg->bindColumn("uphp",$chongwu->uphp);
    $cxjg->bindColumn("upgj",$chongwu->upgj);
    $cxjg->bindColumn("upfy",$chongwu->upfy);
    $cxjg->bindColumn("cwpz",$chongwu->cwpz);
    $cxjg->bindColumn("tool1",$chongwu->tool1);
    $cxjg->bindColumn("tool2",$chongwu->tool2);
    $cxjg->bindColumn("tool3",$chongwu->tool3);
    $cxjg->bindColumn("tool4",$chongwu->tool4);
    $cxjg->bindColumn("tool5",$chongwu->tool5);
    $cxjg->bindColumn("tool6",$chongwu->tool6);
    $cxjg->bindColumn("tool7",$chongwu->tool7);
	/*
	$cxjg->bindColumn("tool8",$chongwu->tool8);
	$cxjg->bindColumn("tool9",$chongwu->tool9);
	$cxjg->bindColumn("tool10",$chongwu->tool10);
	$cxjg->bindColumn("tool11",$chongwu->tool11);
	$cxjg->bindColumn("tool12",$chongwu->tool12);
	$cxjg->bindColumn("tool13",$chongwu->tool13);
	$cxjg->bindColumn("tool14",$chongwu->tool14);
	$cxjg->bindColumn("tool15",$chongwu->tool15);
	$cxjg->bindColumn("tool16",$chongwu->tool16);
	*/

    $chongwu->ugj = $chongwu->cwgj;
    $chongwu->ufy = $chongwu->cwfy;
    $chongwu->ubj = $chongwu->cwbj;
    $chongwu->uxx = $chongwu->cwxx;
    $chongwu->umaxhp = $chongwu->cwmaxhp;

    for ($slot = 1; $slot <= 7; $slot++) {
        if ($slot == 6) continue;
        $toolVal = isset($chongwu->{"tool$slot"}) ? $chongwu->{"tool$slot"} : 0;
        if ($toolVal != 0) {
            $zhuangbei = getzb($toolVal, $dblj);
            if ($zhuangbei) {
                $chongwu->ugj += $zhuangbei->zbgj;
                $chongwu->ufy += $zhuangbei->zbfy;
                $chongwu->ubj += $zhuangbei->zbbj;
                $chongwu->uxx += $zhuangbei->zbxx;
                $chongwu->umaxhp += $zhuangbei->zbhp;
            }
        }
    }
	/*
	if ($chongwu->tool8!=0){
        $zhuangbei = getzb($chongwu->tool8,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	if ($chongwu->tool9!=0){
        $zhuangbei = getzb($chongwu->tool9,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	if ($chongwu->tool10!=0){
        $zhuangbei = getzb($chongwu->tool10,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	if ($chongwu->tool11!=0){
        $zhuangbei = getzb($chongwu->tool11,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	if ($chongwu->tool12!=0){
        $zhuangbei = getzb($chongwu->tool12,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	if ($chongwu->tool13!=0){
        $zhuangbei = getzb($chongwu->tool13,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	if ($chongwu->tool14!=0){
        $zhuangbei = getzb($chongwu->tool14,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	if ($chongwu->tool15!=0){
        $zhuangbei = getzb($chongwu->tool15,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	if ($chongwu->tool16!=0){
        $zhuangbei = getzb($chongwu->tool16,$dblj);
        $chongwu->ugj = $chongwu->cwgj + $zhuangbei->zbgj;
        $chongwu->ufy = $chongwu->cwfy + $zhuangbei->zbfy;
        $chongwu->ubj = $chongwu->cwbj + $zhuangbei->zbbj;
        $chongwu->uxx = $chongwu->cwxx + $zhuangbei->zbxx;
        $chongwu->umaxhp = $chongwu->cwmaxhp + $zhuangbei->zbhp;


    }
	*/
    $ret = $cxjg->fetch(\PDO::FETCH_ASSOC);
    $rangesCount = is_array($rangeslv) ? count($rangeslv) : intval($rangeslv);
    for ($i=0; $i < $rangesCount - 1; $i++){
        if ($chongwu->cwlv>=$rangeslv[$i] && $chongwu->cwlv<$rangeslv[$i+1]){
            $cwnextlv = $chongwu->cwlv + 1;
            $chongwu->cwmaxexp = $cwnextlv *($cwnextlv+round($cwnextlv/2))*50*$rangesexp[min($i, count($rangesexp) - 1)]+$cwnextlv;//宠物升级cwmaxexp升级计算，我改成50倍默认10倍
            break;
        }
    }

    // Linh căn pet bonus
    if (function_exists(__NAMESPACE__ . '\\linhcan_calc_bonuses') && !empty($chongwu->sid)) {
        $lcB = linhcan_calc_bonuses($chongwu->sid, $dblj);
        if ($lcB) {
            $petAtkBonus = floatval(isset($lcB['pet_atk_bonus']) ? $lcB['pet_atk_bonus'] : 0);
            $petHpBonus = floatval(isset($lcB['pet_hp_bonus']) ? $lcB['pet_hp_bonus'] : 0);
            $petCritBonus = floatval(isset($lcB['pet_crit_bonus']) ? $lcB['pet_crit_bonus'] : 0);

            if ($petAtkBonus != 0) {
                $chongwu->ugj = intval(intval($chongwu->ugj) * (100 + $petAtkBonus) / 100);
                $chongwu->cwgj = intval(intval($chongwu->cwgj) * (100 + $petAtkBonus) / 100);
            }
            if ($petHpBonus != 0) {
                $chongwu->umaxhp = intval(intval($chongwu->umaxhp) * (100 + $petHpBonus) / 100);
                $chongwu->cwmaxhp = intval(intval($chongwu->cwmaxhp) * (100 + $petHpBonus) / 100);
            }
            if ($petCritBonus != 0) {
                $chongwu->ubj = intval(intval($chongwu->ubj) * (100 + $petCritBonus) / 100);
                $chongwu->cwbj = intval(intval($chongwu->cwbj) * (100 + $petCritBonus) / 100);
            }
        }
    }

    return $chongwu;
}

function getchongwuall($sid,$dblj){
    $sql = "select * from playerchongwu WHERE sid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array($sid));
    if ($cxjg){
        $ret = $cxjg->fetchAll(\PDO::FETCH_ASSOC);
        return $ret;
    }else{
        return false;
    }
}
class wg1{
    var $wgname;
    var $wgid;
    var $wgdj;
    var $uid;
    var $sid;
    var $wglx;
	var $wgys;
	var $wgsum;
	var $wginfo;
	var $wgxl;
	var $wgxlmax;
	var $xlzt;
	var $xlsj;
	// Thuộc tính cộng thêm theo mỗi cấp võ công, lấy từ bảng wugong
	var $add_gj;
	var $add_fy;
	var $add_hp;
	var $add_bj;
	var $add_xx;
	var $effect_type;
	var $effect_value;
}
function wgsl($sid,$dblj){
    $stmt = $dblj->prepare("SELECT * FROM playerwugong WHERE sid = ?");
    $stmt->execute(array($sid));
    $ret = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    if (!$ret) {
        return false;
    }

    foreach ($ret as $key => $row) {
        $ret[$key] = merge_wugong_config_to_row($row);
    }

    return $ret;
}

function addchongwu($sid,$dblj){
    $cw4 = array('Linh lỵ thử','Bổn bổn ngưu','Uy uy hổ', 'Khiêu khiêu thố','Khốc khốc long','Hoa hoa xà','Lưu lưu mã','Mị mị dương','Soái soái hầu','Đản đản kê','Quai quai cẩu','Chiêu tài trư');
    $uphp = mt_rand(8,25);
    $upgj = mt_rand(2,5);
    $upfy = mt_rand(3,8);
    $cwpz = mt_rand(0,500);
    if ($cwpz<200){
        $cwpz=0;
    }elseif ($cwpz<350){
        $cwpz=1;
    }elseif ($cwpz<430){
        $cwpz=2;
    }elseif ($cwpz<470){
        $cwpz=3;
    }elseif ($cwpz<495){
        $cwpz=4;
    }elseif ($cwpz<600){
        $cwpz=5;
    }
    $sjs1 = mt_rand(0,15);
    $cwlv = 1;
    $cwmaxhp = 100;
    $cwhp = 100;
    $cwgj = 6+$sjs1;
    $cwfy = $sjs1;
    $sjs = mt_rand(0,11);
    $cwmaxexp = 100;
    $cwname = $cw4[$sjs];
    $stmt = $dblj->prepare("INSERT INTO playerchongwu(cwname,cwlv,cwhp,cwmaxhp,cwgj,cwfy,uphp,upgj,upfy,cwpz,sid,cwmaxexp) VALUES (:cwname,:cwlv,:cwhp,:cwmaxhp,:cwgj,:cwfy,:uphp,:upgj,:upfy,:cwpz,:sid,:cwmaxexp)");
    $stmt->execute(array(':cwname'=>$cwname,':cwlv'=>$cwlv,':cwhp'=>$cwhp,':cwmaxhp'=>$cwmaxhp,':cwgj'=>$cwgj,':cwfy'=>$cwfy,':uphp'=>$uphp,':upgj'=>$upgj,':upfy'=>$upfy,':cwpz'=>$cwpz,':sid'=>$sid,':cwmaxexp'=>$cwmaxexp));
}



function delechongwu($cwid,$sid,$dblj){
    $cwid = intval($cwid);
    if ($cwid <= 0) return false;
    $stmt = $dblj->prepare("DELETE FROM playerchongwu WHERE cwid = :cwid AND sid = :sid");
    $stmt->execute(array(':cwid' => $cwid, ':sid' => $sid));
}




function changecwexp($cwid,$exp,$dblj){
    $stmt = $dblj->prepare("UPDATE playerchongwu SET cwexp = cwexp + :exp WHERE cwid = :cwid");
    $stmt->execute(array(':exp' => $exp, ':cwid' => $cwid));
    $cw = getchongwu($cwid,$dblj);
    if ($cw->cwexp >= $cw->cwmaxexp){
        $stmt = $dblj->prepare("UPDATE playerchongwu SET cwexp = cwexp - :maxexp WHERE cwid = :cwid");
        $stmt->execute(array(':maxexp' => $cw->cwmaxexp, ':cwid' => $cwid));

        global $rangeslv;
        $playernextlv = $cw->cwlv + 1;
        for ($i=0;$i<count($rangeslv)-1;$i++){
            if ($playernextlv >= $rangeslv[$i] && $playernextlv < $rangeslv[$i+1]){

                $uphp = $cw->uphp * (1 + ($cw->cwpz/10));
                $upgj = $cw->upgj * (1 + ($cw->cwpz/10));
                $upfy = $cw->upfy * (1 + ($cw->cwpz/10));
                $stmt = $dblj->prepare("UPDATE playerchongwu SET cwlv = cwlv + 1,
                                           cwmaxhp = cwmaxhp + :hp,
                                           cwgj = cwgj + :gj,
                                           cwfy = cwfy + :fy
                                           WHERE cwid = :cwid");
                $stmt->execute(array(':hp' => $uphp, ':gj' => $upgj, ':fy' => $upfy, ':cwid' => $cwid));
                break;
            }
        }
    }
}

class jineng{
    var $jnname;
    var $jnid;
    var $jngj;
    var $jnfy;
    var $jnbj;
    var $jnxx;
    var $jndj;
    var $djcount;
    var $xiaohao;
    var $jncount;
}

function getjineng_all($dblj = null){
    return array_values(jineng_config_all());
}
function getplayerjineng_all($sid,$dblj){
    $sql = "select jnid,jncount,sid from playerjineng WHERE sid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array($sid));
    $retjn = $cxjg->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($retjn as $key => $row) {
        $config = jineng_config_get(isset($row['jnid']) ? $row['jnid'] : 0);
        if ($config) {
            $retjn[$key] = array_merge($config, $row);
        }
    }
    return $retjn;
}
function getjineng_once($jnid, $dblj = null){
    $config = jineng_config_get($jnid);

    if (!$config) {
        return false;
    }

    $jineng = new jineng();
    return hydrate_jineng_config_to_object($jineng, $config);
}

function getplayerjineng($jnid,$sid,$dblj){
    $jineng = new jineng();
    $sql = "select jnid,jncount from playerjineng WHERE jnid = ? AND sid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array(intval($jnid), $sid));

    $cxjg->bindColumn("jnid",$jineng->jnid);
    $cxjg->bindColumn("jncount",$jineng->jncount);
    $ret = $cxjg->fetch(\PDO::FETCH_ASSOC);

    if ($ret){
        hydrate_jineng_config_to_object($jineng, jineng_config_get($jineng->jnid));
        return $jineng;
    }
    return $ret;
}

function addjineng($jnid,$jncount,$sid,$dblj){
    $jineng = getjineng_once($jnid,$dblj);

    if (!$jineng) {
        return false;
    }

    $ret = getplayerjineng($jnid,$sid,$dblj);
    if ($ret){
        $stmt = $dblj->prepare("UPDATE `playerjineng` SET jncount = jncount + ? WHERE jnid = ? AND sid = ?");
        $stmt->execute(array(intval($jncount), intval($jnid), $sid));
    }else{
        $stmt = $dblj->prepare("INSERT INTO `playerjineng`(jnid, sid, jncount) VALUES (?, ?, ?)");
        $stmt->execute(array(intval($jineng->jnid), $sid, intval($jncount)));
    }
    return $stmt->rowCount() > 0;
}

function delejnsum($jnid,$jnsum,$sid,$dblj){
    $jnneng = getplayerjineng($jnid,$sid,$dblj);
    if ($jnneng){
        if ($jnneng->jncount >= $jnsum && $jnsum > 0){
            $stmt = $dblj->prepare("UPDATE playerjineng SET jncount = jncount - ? WHERE sid = ? AND jnid = ?");
            $stmt->execute(array(intval($jnsum), $sid, intval($jnid)));
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}
class mqy{
    var $qyname;
    var $qyid;
    var $mid;
}

function getqy($qyid,$dblj){
    $qy = new mqy();
    $sql = "select * from `qy` WHERE qyid = ?";
    $cxjg = $dblj->prepare($sql);
    $cxjg->execute(array(intval($qyid)));
    $cxjg->bindColumn('qyname',$qy->qyname);
    $cxjg->bindColumn('qyid',$qy->qyid);
    $cxjg->bindColumn('mid',$qy->mid);
    $cxjg->fetch(\PDO::FETCH_ASSOC);
    return $qy;
}
function getqy_all($dblj){
    $sql = "select * from `qy`";
    $cxjg = $dblj->query($sql);
    $ret = $cxjg->fetchAll(\PDO::FETCH_ASSOC);
    return $ret;
}
function getqy_dt($dblj){
    $sql = "select * from `mid`";
    $cxjg = $dblj->query($sql);
    $ret = $cxjg->fetchAll(\PDO::FETCH_ASSOC);
    return $ret;
}
class gameconfig{
    var $firstmid;
}
function getgameconfig($dblj){
    $gameconfig = new gameconfig();
    $sql = "select * from `gameconfig`";
    $cxjg = $dblj->query($sql);
    $cxjg->bindColumn('firstmid',$gameconfig->firstmid);
    $ret = $cxjg->fetch(\PDO::FETCH_ASSOC);
    return $gameconfig;
}

class fangshi_dj{
    var $djid;
    var $djname;
    var $pay;
    var $uid;
    var $djcount;
    var $payid;
    var $djinfo;
}

/**
 * @property  djid
 */
class fangshi_zb{
    var $zbnowid;
    var $pay;
    var $payid;
    var $uid;
}
function getfangshi_once($lx,$payid,$dblj){
    switch ($lx){
        case "daoju":
            $fsdj = new fangshi_dj();
            $sql = "select `fangshi_dj`.* from `fangshi_dj`
                    inner join `game1` on `game1`.`uid` = `fangshi_dj`.`uid`
                    WHERE `fangshi_dj`.`payid` = ?";
            $redj = $dblj->prepare($sql);
            $redj->execute(array(intval($payid)));
            $redj->bindColumn('djid',$fsdj->djid);
            $redj->bindColumn('djname',$fsdj->djname);
            $redj->bindColumn('pay',$fsdj->pay);
            $redj->bindColumn('uid',$fsdj->uid);
            $redj->bindColumn('djcount',$fsdj->djcount);
            $redj->bindColumn('payid',$fsdj->payid);
            $redj->bindColumn('djinfo',$fsdj->djinfo);
            $dj = $redj->fetch(\PDO::FETCH_ASSOC);
            if ($dj){
                return $fsdj;
            }
            return $dj;
        case "zhuangbei":
            $fszb = new fangshi_zb();
            $sql = "select `fangshi_zb`.* from `fangshi_zb`
                    inner join `playerzhuangbei` on `playerzhuangbei`.`zbnowid` = `fangshi_zb`.`zbnowid`
                    inner join `game1` on `game1`.`uid` = `fangshi_zb`.`uid`
                    WHERE `fangshi_zb`.`payid` = ? ";
            $redj = $dblj->prepare($sql);
            $redj->execute(array(intval($payid)));
            $redj->bindColumn('zbnowid',$fszb->zbnowid);
            $redj->bindColumn('payid',$fszb->payid);
            $redj->bindColumn('uid',$fszb->uid);
            $redj->bindColumn("pay",$fszb->pay);
            $zb = $redj->fetch(\PDO::FETCH_ASSOC);
            if ($zb){
                return $fszb;
            }
            return $zb;
		case "shangdian":
            $fszb = new fangshi_zb();
            $sql = "select `fangshi_sd`.* from `fangshi_sd`
                    inner join `playerzhuangbei` on `playerzhuangbei`.`zbnowid` = `fangshi_sd`.`zbnowid`
                    inner join `game1` on `game1`.`uid` = `fangshi_sd`.`uid`
                    WHERE `fangshi_sd`.`payid` = ?";
            $redj = $dblj->prepare($sql);
            $redj->execute(array(intval($payid)));
            $redj->bindColumn('zbnowid',$fszb->zbnowid);
            $redj->bindColumn('payid',$fszb->payid);
            $redj->bindColumn('uid',$fszb->uid);
            $redj->bindColumn("pay",$fszb->pay);
            $zb = $redj->fetch(\PDO::FETCH_ASSOC);
            if ($zb){
                return $fszb;
            }
            return $zb;
    }

}

/**
 * @param $lx
 * @param $dblj
 * @return mixed
 */
function getfangshi_all($lx, $dblj){
    switch ($lx){
        case "daoju":
            $sql = "select * from `fangshi_dj`";
            $redj = $dblj->query($sql);
            $dj = $redj->fetchAll(\PDO::FETCH_ASSOC);
            return $dj;
        case "zhuangbei":
            // Bỏ qua các tin treo bán chết: vật phẩm đã bị xoá khỏi playerzhuangbei
            // hoặc người treo bán không còn trong game1 (dữ liệu import lại).
            $sql = "select `fangshi_zb`.* from `fangshi_zb`
                    inner join `playerzhuangbei` on `playerzhuangbei`.`zbnowid` = `fangshi_zb`.`zbnowid`
                    inner join `game1` on `game1`.`uid` = `fangshi_zb`.`uid`";

            $redj = $dblj->query($sql);
            $dj = $redj->fetchAll(\PDO::FETCH_ASSOC);
            return $dj;
		case "shangdian":
            $sql = "select `fangshi_sd`.* from `fangshi_sd`
                    inner join `playerzhuangbei` on `playerzhuangbei`.`zbnowid` = `fangshi_sd`.`zbnowid`
                    inner join `game1` on `game1`.`uid` = `fangshi_sd`.`uid`";

            $redj = $dblj->query($sql);
            $dj = $redj->fetchAll(\PDO::FETCH_ASSOC);
            return $dj;
    }

}

class club{
    var $clubname;
    var $clubid;
    var $clublv;
    var $clubexp;
    var $clubno1;
    var $clubinfo;
    var $clubyxb;
    var $clubczb;
    var $clubdiamond;
    var $clubskill;
}
function getclub($clubid,$dblj){

    $clubid = intval($clubid);

    if($clubid <= 0){
        return false;
    }

    $club = new club();

    $stmt = $dblj->prepare("
        SELECT *
        FROM club
        WHERE clubid = ?
        LIMIT 1
    ");

    $stmt->execute(array($clubid));

    $stmt->bindColumn('clubname',$club->clubname);
    $stmt->bindColumn('clubinfo',$club->clubinfo);
    $stmt->bindColumn('clublv',$club->clublv);
    $stmt->bindColumn('clubexp',$club->clubexp);
    $stmt->bindColumn('clubid',$club->clubid);
    $stmt->bindColumn('clubno1',$club->clubno1);
    $stmt->bindColumn('clubyxb',$club->clubyxb);
    $stmt->bindColumn('clubczb',$club->clubczb);
    $stmt->bindColumn('clubdiamond',$club->clubdiamond);
    $stmt->bindColumn('clubskill',$club->clubskill);

    if($stmt->fetch(\PDO::FETCH_ASSOC)){
        return $club;
    }

    return false;
}

function getclub_all($dblj){
    $sql = "select * from `club`";
    $retc = $dblj->query($sql);
    $club = $retc->fetchAll(\PDO::FETCH_ASSOC);
    return $club;
}

class clubplayer{
    var $clubid;
    var $uid;
    var $sid;
    var $uclv;
}

/**
 * @param $sid
 * @param $dblj
 * @return clubplayer
 */
function getclubplayer_once($sid, $dblj){
    $clubplayer = new clubplayer();
    $stmt = $dblj->prepare("SELECT * FROM clubplayer WHERE sid = :sid");
    $stmt->execute(array(':sid' => $sid));
    $retc = $stmt;
    $retc->bindColumn('clubid',$clubplayer->clubid);
    $retc->bindColumn('uid',$clubplayer->uid);
    $retc->bindColumn('uclv',$clubplayer->uclv);
    $ret = $retc->fetch(\PDO::FETCH_ASSOC);
    if (!$ret){
        return $ret;
    }
    return $clubplayer;
}

class duihuan{
    var $dhid;
    var $dhm;
    var $dhzb;
    var $dhdj;
    var $dhyp;
    var $dhyxb;
    var $dhczb;
    var $dhname;
    var $dhexp;
}

function getduihuan($dhm,$dblj){
    $duihuan = new duihuan();
    $stmt = $dblj->prepare("SELECT * FROM duihuan WHERE dhm = :dhm");
    $stmt->execute(array(':dhm' => $dhm));
    $ret = $stmt;
    $ret->bindColumn('dhid',$duihuan->dhid);
    $ret->bindColumn('dhm',$duihuan->dhm);
    $ret->bindColumn('dhzb',$duihuan->dhzb);
    $ret->bindColumn('dhdj',$duihuan->dhdj);
    $ret->bindColumn('dhyp',$duihuan->dhyp);
    $ret->bindColumn('dhyxb',$duihuan->dhyxb);
    $ret->bindColumn('dhczb',$duihuan->dhczb);
    $ret->bindColumn('dhname',$duihuan->dhname);
    $ret->bindColumn('dhexp',$duihuan->dhexp);
    $ret = $ret->fetch(\PDO::FETCH_ASSOC);
    if ($ret){
        return $duihuan;
    }
    return $ret;
}
class im{
    var $imuid;
    var $uid;
    var $sid;
}
function isim($uid,$sid,$dblj){
    $stmt = $dblj->prepare("SELECT imuid FROM im WHERE imuid = :uid AND sid = :sid");
    $stmt->execute(array(':uid' => $uid, ':sid' => $sid));
    return $stmt->rowCount();
}
class wg{
    var $wgname;
    var $wgid;
    var $wgdj;
    var $uid;
    var $sid;
    var $wglx;
	var $wgys;
	var $wgsum;
	var $wginfo;
	var $wgxl;
	var $wgxlmax;
	var $xlzt;
	var $xlsj;
    // Thuộc tính cộng thêm theo mỗi cấp, lấy từ JSON wugong_config.json.
    var $add_gj;
    var $add_fy;
    var $add_hp;
    var $add_bj;
    var $add_xx;
    var $effect_type;
    var $effect_value;
}
function wgcx($wgid,$sid,$dblj,$forceRefresh = false){
    static $cache = array();

    $wgid = intval($wgid);
    $cacheKey = $sid . ':' . $wgid;

    if (!$forceRefresh && isset($cache[$cacheKey])) {
        return $cache[$cacheKey] ? clone $cache[$cacheKey] : false;
    }

    $stmt = $dblj->prepare("SELECT * FROM playerwugong WHERE wgid = ? AND sid = ? LIMIT 1");
    $stmt->execute(array($wgid, $sid));
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    if (!$row) {
        $cache[$cacheKey] = false;
        return false;
    }

    $row = merge_wugong_config_to_row($row);

    $wgcx = new wg();
    hydrate_object_from_row($wgcx, $row);

    $cache[$cacheKey] = clone $wgcx;
    return $wgcx;
}

// Xóa / giảm số lượng võ công trong túi người chơi.
function delewugong($wgid,$sid,$dblj){
    $wgid = intval($wgid);
    $cx = wgcx($wgid,$sid,$dblj);

    if (!$cx) {
        return false;
    }

    if ((int)$cx->wgsum <= 1) {
        $stmt = $dblj->prepare("DELETE FROM playerwugong WHERE wgid = ? AND sid = ?");
        return $stmt->execute(array($wgid, $sid));
    }

    $stmt = $dblj->prepare("UPDATE playerwugong SET wgsum = wgsum - 1 WHERE wgid = ? AND sid = ?");
    return $stmt->execute(array($wgid, $sid));
}


class miji{
    var $wgname;
    var $wginfo;
    var $wgys;
	var $wgid;
	var $wglx;
}

function cqmj($wgid,$dblj = null){
    $miji = new miji();

    $config = wugong_config_get($wgid);
    if (!$config) {
        return $miji;
    }

    hydrate_wugong_config_to_object($miji, $config);
    return $miji;
}

function mjcx($cqsjs,$dblj = null){
    $config = wugong_config_get($cqsjs);
    if (!$config) {
        return false;
    }

    $miji = new miji();
    hydrate_wugong_config_to_object($miji, $config);
    return $miji;
}

// Rút / nhận bí tịch võ công.
// Cấu hình lấy từ JSON, dữ liệu sở hữu vẫn lưu trong playerwugong.
function cqwg($cqsjs,$sid,$dblj){
	$wgsum = 1;
    $wgid = intval($cqsjs);

	$wg = wgcx($wgid,$sid,$dblj);

	if ($wg) {
        $stmt = $dblj->prepare("UPDATE playerwugong SET wgsum = wgsum + ? WHERE wgid = ? AND sid = ?");
        return $stmt->execute(array($wgsum, $wgid, $sid));
    }

    $mj = mjcx($wgid);
    if (!$mj) {
        return false;
    }

    $player = getplayer($sid,$dblj);
    $uid = isset($player->uid) ? $player->uid : 0;

    $stmt = $dblj->prepare("
        INSERT INTO playerwugong
            (wgid, sid, uid, wgsum)
        VALUES
            (?, ?, ?, ?)
    ");

    return $stmt->execute(array(
        $mj->wgid,
        $sid,
        $uid,
        $wgsum
    ));
}


function addim($imuid,$sid,$dblj){
    $player = getplayer($sid,$dblj);
    $stmt = $dblj->prepare("INSERT INTO im(imuid, sid, uid) VALUES (:imuid, :sid, :uid)");
    $stmt->execute(array(':imuid' => $imuid, ':sid' => $sid, ':uid' => $player->uid));
}


function getJingJieHtml($level, $rangesjj = null, $jingjieColor = null) {
	global $rangesjj; // Cảnh giới
	global $jingjieColor;

    $perJingjieLv = 50;
    $jingjieIndex = floor($level / $perJingjieLv);

    if (!isset($rangesjj[$jingjieIndex])) {
        return '';
    }

    $baseColor = $jingjieColor[$jingjieIndex];
    $innerLv   = $level % 50;

	if ($innerLv == 0){
		$stage = 'Pháp Tắc Hộ Thuẫn Nhập Cảnh - Tỉ Lệ Đột Phá +10%';
        $opacity = 1;
		
		return $rangesjj[$jingjieIndex - 1] . 
				' <span style="color:'.$jingjieColor[$jingjieIndex - 1].';opacity:'.$opacity.'"> Viên Mãn </span> <br/> ( <span class="lightning-text" style="opacity:1;font-size: 13px;">'
        . $stage
        . '</span> ) ';
		
    } elseif ($innerLv < 10) {
		if($level <= 50){
			$stage = 'Cơ Bản';
		}else{
			$stage = 'Bán bộ';	
		}
        $opacity = 0.6;
    } elseif ($innerLv < 20) {
        $stage = 'Sơ kỳ';
        $opacity = 0.65;
    } elseif ($innerLv < 30) {
        $stage = 'Trung kỳ';
        $opacity = 0.7;
    } elseif ($innerLv < 40) {
        $stage = 'Hậu kỳ';
        $opacity = 0.85;
    } else {
        $stage = 'Viên Mãn';
        $opacity = 1;
    }

    return $rangesjj[$jingjieIndex]
        . ' <span style="color:'.$baseColor.';opacity:'.$opacity.'">'
        . $stage
        . '</span>';
}

// =============================================================================
// SAO THU - PHU BAN SAN YEU THU
// =============================================================================

function getpet_config_all() {
    static $config = null;
    if ($config === null) {
        $file = __DIR__ . '/config/pet_config.json';
        if (is_file($file)) {
            $config = json_decode(file_get_contents($file), true);
        } else {
            $config = array();
        }
    }
    return $config;
}

function getpet_config_one($id) {
    $config = getpet_config_all();
    return isset($config[$id]) ? $config[$id] : null;
}

function addchongwu_from_pet($sid, $pet_id, $quality, $dblj) {
    $petConfig = getpet_config_one($pet_id);
    if (!$petConfig) {
        return false;
    }

    $pet_config_id = intval($pet_id);

    $quality = max(0, min(5, intval($quality)));
    $multiplier = 1 + ($quality * 0.15);

    $cwname = $petConfig['name'];
    $cwlv = 1;
    $cwmaxhp = max(1, intval($petConfig['base_hp'] * $multiplier));
    $cwhp = $cwmaxhp;
    $cwgj = max(1, intval($petConfig['base_atk'] * $multiplier));
    $cwfy = max(1, intval($petConfig['base_def'] * $multiplier));
    $cwbj = max(0, intval($petConfig['base_crit'] * $multiplier));
    $uphp = max(1, intval($petConfig['base_hp'] * 0.1));
    $upgj = max(1, intval($petConfig['base_atk'] * 0.1));
    $upfy = max(1, intval($petConfig['base_def'] * 0.1));
    $cwpz = $quality;
    $cwmaxexp = 100;

    $stmt = $dblj->prepare("INSERT INTO playerchongwu(cwname, cwlv, cwhp, cwmaxhp, cwgj, cwfy, cwxx, uphp, upgj, upfy, cwpz, sid, cwmaxexp, pet_config_id) VALUES (:cwname, :cwlv, :cwhp, :cwmaxhp, :cwgj, :cwfy, :cwxx, :uphp, :upgj, :upfy, :cwpz, :sid, :cwmaxexp, :pet_config_id)");
    return $stmt->execute(array(
        ':cwname' => $cwname,
        ':cwlv' => $cwlv,
        ':cwhp' => $cwhp,
        ':cwmaxhp' => $cwmaxhp,
        ':cwgj' => $cwgj,
        ':cwfy' => $cwfy,
        ':cwxx' => $cwbj,
        ':uphp' => $uphp,
        ':upgj' => $upgj,
        ':upfy' => $upfy,
        ':cwpz' => $cwpz,
        ':sid' => $sid,
        ':cwmaxexp' => $cwmaxexp,
        ':pet_config_id' => $pet_config_id
    ));
}

function sauthu_state($sid, $dblj) {
    $stmt = $dblj->prepare("SELECT * FROM player_sauthu WHERE sid = ? LIMIT 1");
    $stmt->execute(array($sid));
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($row) {
        $row['current_map'] = max(1, min(10, intval($row['current_map'])));
        $row['best_map'] = max(0, intval($row['best_map']));
        $row['total_fights'] = max(0, intval($row['total_fights']));
        $row['total_captures'] = max(0, intval($row['total_captures']));
        return $row;
    }

    $stmt = $dblj->prepare("INSERT INTO player_sauthu(sid, current_map, best_map, total_fights, total_captures, updated_at) VALUES(?, 1, 0, 0, 0, NOW())");
    $stmt->execute(array($sid));
    return array('sid' => $sid, 'current_map' => 1, 'best_map' => 0, 'total_fights' => 0, 'total_captures' => 0, 'updated_at' => date('Y-m-d H:i:s'));
}

function sauthu_get_monsters($map_id) {
    $yeuthuConfig = null;
    $file = __DIR__ . '/config/yeuthu_config.json';
    if (is_file($file)) {
        $yeuthuConfig = json_decode(file_get_contents($file), true);
    }

    if (!$yeuthuConfig || !isset($yeuthuConfig['maps'][$map_id])) {
        return array();
    }

    $mapConfig = $yeuthuConfig['maps'][$map_id];
    $monsters = isset($mapConfig['monsters']) ? $mapConfig['monsters'] : array();
    $countRange = isset($mapConfig['monster_count']) ? $mapConfig['monster_count'] : array(3, 5);
    $minCount = max(1, intval($countRange[0]));
    $maxCount = max($minCount, intval($countRange[1]));
    $monsterCount = mt_rand($minCount, $maxCount);

    $totalWeight = 0;
    foreach ($monsters as $m) {
        $totalWeight += floatval($m['weight']);
    }

    if ($totalWeight <= 0) {
        return array();
    }

    $result = array();
    for ($i = 0; $i < $monsterCount; $i++) {
        $randInt = mt_rand(0, intval($totalWeight * 100) - 1);
        $cumulative = 0;
        foreach ($monsters as $m) {
            $cumulative += intval(round(floatval($m['weight']) * 100));
            if ($randInt < $cumulative) {
                $petConfig = getpet_config_one($m['pet_config_id']);
                if ($petConfig) {
                    $result[] = array(
                        'pet_config_id' => $m['pet_config_id'],
                        'name' => $petConfig['name'],
                        'element' => $petConfig['element'],
                        'hp' => $petConfig['base_hp'],
                        'atk' => $petConfig['base_atk'],
                        'def' => $petConfig['base_def'],
                        'crit' => $petConfig['base_crit']
                    );
                }
                break;
            }
        }
    }

    return $result;
}

function sauthu_battle($player, $monster) {
    $playerHp = max(1, intval($player->uhp));
    $playerMaxHp = max(1, intval($player->umaxhp));
    $monsterHp = max(1, intval($monster['hp']));
    $monsterMaxHp = $monsterHp;
    $rounds = array();
    $win = false;
    $round = 0;

    while ($round < 30 && $playerHp > 0 && $monsterHp > 0) {
        $round++;
        $playerAtk = max(1, intval($player->ugj));
        $monsterDef = max(0, intval($monster['def']));
        $damage = max(1, intval(round($playerAtk - ($monsterDef * 0.6))));
        if ($damage < intval($playerAtk * 0.12)) {
            $damage = max(1, intval(round($playerAtk * 0.12)));
        }
        if (mt_rand(1, 100) <= min(75, max(0, intval($player->ubj)))) {
            $damage = intval(round($damage * 1.65));
        }
        $monsterHp -= $damage;
        $heal = max(0, intval(round($damage * max(0, intval($player->uxx)) / 220)));
        $playerHp = min($playerMaxHp, $playerHp + $heal);

        $monsterDamage = 0;
        if ($monsterHp > 0) {
            $monsterDamage = max(1, intval(round(intval($monster['atk']) - (intval($player->ufy) * 0.65))));
            if ($monsterDamage < intval($monster['atk'] * 0.12)) {
                $monsterDamage = max(1, intval(round($monster['atk'] * 0.12)));
            }
            if (mt_rand(1, 100) <= intval($monster['crit'])) {
                $monsterDamage = intval(round($monsterDamage * 1.5));
            }
            $playerHp -= $monsterDamage;
        }

        $rounds[] = array(
            'round' => $round,
            'damage' => $damage,
            'heal' => $heal,
            'monster_damage' => $monsterDamage,
            'player_hp' => max(0, $playerHp),
            'monster_hp' => max(0, $monsterHp)
        );
    }

    if ($monsterHp <= 0 && $playerHp > 0) {
        $win = true;
    }

    return array(
        'win' => $win,
        'player_hp' => max(0, $playerHp),
        'monster_hp' => max(0, $monsterHp),
        'round' => $round,
        'rounds' => $rounds
    );
}

function sauthu_get_capture_rate($quality_tier) {
    $rates = array(0 => 80, 1 => 65, 2 => 50, 3 => 35, 4 => 20, 5 => 5);
    return isset($rates[$quality_tier]) ? $rates[$quality_tier] : 50;
}

function sauthu_get_quality_name($quality_tier) {
    $names = array(0 => 'Binh Thuong', 1 => 'Uu Tu', 2 => 'Trac Viet', 3 => 'Phi Pham', 4 => 'Hoan My', 5 => 'Nghich Thien');
    return isset($names[$quality_tier]) ? $names[$quality_tier] : 'Binh Thuong';
}
