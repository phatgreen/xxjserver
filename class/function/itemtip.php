<?php
namespace player;

/**
 * Du lieu hop thong tin trang bi (tooltip) cho 4 noi: trang bi, tui do,
 * Phuong thi, cua hang NPC. Mot payload JSON duy nhat duoc nanh vao
 * data-tip luc render, khong goi them request khi di chuot.
 */

/**
 * Bon chi so co ban + hai chi so dac biet, thu tu hien thi.
 * Trung voi bang "Thuoc tinh" trong game/zbinfo.php.
 */
function itemtip_stat_labels(){
    return array(
        array('zbgj',   'Công kích',    ''),
        array('zbfy',   'Phòng ngự',    ''),
        array('zbhp',   'Khí huyết',    ''),
        array('zbbj',   'Bạo kích',     '%'),
        array('zbxx',   'Hút máu',      '%'),
        array('tuposjs','Đột phá',      '%'),
        array('tupoexp','Tu luyện',     '%'),
    );
}

/**
 * Doc field tu ca config array (zhuangbei_config.json) lan
 * zhuangbei object (playerzhuangbei da merge config).
 */
function itemtip_field($src, $key, $default = 0){
    if (is_array($src)) {
        return array_key_exists($key, $src) ? $src[$key] : $default;
    }
    if (is_object($src)) {
        return property_exists($src, $key) || isset($src->$key) ? $src->$key : $default;
    }
    return $default;
}

function itemtip_slot_of($src){
    $tool = intval(itemtip_field($src, 'tool', null));
    if (!$tool) {
        $tool = intval(itemtip_field($src, 'zbtool', 0));
    }
    return $tool;
}

function itemtip_value_of($src, $key){
    return intval(itemtip_field($src, $key, 0));
}

/**
 * 16 mon dang mac, index theo o trang bi.
 * getzb() da co cache trong request nen goi lai khong them cau SQL.
 */
function itemtip_worn_map($player, $dblj){
    static $cache = array();
    $uid = is_object($player) ? intval($player->uid) : 0;
    if ($uid <= 0) {
        return array();
    }
    if (isset($cache[$uid])) {
        return $cache[$uid];
    }

    $map = array();
    for ($i = 1; $i <= 16; $i++) {
        $id = intval(itemtip_field($player, 'tool' . $i, 0));
        if ($id > 0) {
            $zb = getzb($id, $dblj);
            if ($zb) {
                $map[$i] = $zb;
            }
        }
    }
    $cache[$uid] = $map;
    return $map;
}

/**
 * Mon dang mac cung o voi mon can hoi, hoac null neu o trong.
 * Neu chinh mon do dang mac thi khong co doi thu (tra ve null).
 */
function itemtip_rival($zb, $wornMap){
    $slot = itemtip_slot_of($zb);
    if ($slot < 1 || $slot > 16 || !isset($wornMap[$slot])) {
        return null;
    }
    $worn = $wornMap[$slot];
    $mine = intval(itemtip_field($zb, 'zbnowid', 0));
    if ($mine > 0 && $mine === intval(itemtip_field($worn, 'zbnowid', 0))) {
        return null;
    }
    return $worn;
}

/**
 * Payload day du cho mot hop thong tin.
 */
function itemtip_payload($zb, $rival = null, $player = null){
    if (!$zb) {
        return null;
    }

    $zbid  = intval(itemtip_field($zb, 'zbid', 0));
    $name  = trim((string) itemtip_field($zb, 'zbname', ''));
    if ($name === '') {
        $name = 'Trang bị #' . $zbid;
    }

    $color = trim((string) itemtip_field($zb, 'zbys', ''));
    if ($color === '' || !preg_match('/^#[0-9a-fA-F]{3,8}$/', $color)) {
        $color = '#cbd5e1';
    }

    $element = (string) itemtip_field($zb, 'element', 'none');
    $elemTags = array();
    foreach (zhuangbei_elements_parse($element) as $el) {
        $elemTags[] = array(
            zhuangbei_element_icon($el),
            zhuangbei_element_name($el),
            zhuangbei_element_color_ui($el)
        );
    }
    $rarity = intval(itemtip_field($zb, 'rarity', 1));
    $slot   = itemtip_slot_of($zb);
    $req    = intval(itemtip_field($zb, 'realm_require', 0));
    if (!$req) {
        $req = intval(itemtip_field($zb, 'zblv', 0));
    }

    $stats = array();
    foreach (itemtip_stat_labels() as $def) {
        $key    = $def[0];
        $label  = $def[1];
        $suffix = $def[2];
        $value  = itemtip_value_of($zb, $key);
        $delta  = null;
        if ($rival) {
            $delta = $value - itemtip_value_of($rival, $key);
            if ($delta === 0) {
                $delta = null;
            }
        }
        $stats[] = array($label, $value, $suffix, $delta);
    }

    $text = trim(strip_tags((string) itemtip_field($zb, 'zbinfo', '')));

    $payload = array(
        'name'   => $name,
        'color'  => $color,
        'qh'     => intval(itemtip_field($zb, 'qianghua', 0)),
        'slot'   => $slot ? zhuangbei_tool_name($slot) : 'Chưa xác định ô',
        'lv'     => intval(itemtip_field($zb, 'zblv', 0)),
        'req'    => $req,
        'elem'   => $elemTags,
        'rar'    => array(zhuangbei_rarity_name($rarity), zhuangbei_rarity_color($rarity)),
        'stats'  => $stats,
        'text'   => $text,
        'img'    => zhuangbei_image_path(is_object($zb) ? $zb : (object) array('zbid' => $zbid),
                        zhuangbei_slot_icon($slot)),
        'versus' => null
    );

    if ($rival) {
        $rn = trim((string) itemtip_field($rival, 'zbname', ''));
        $payload['versus'] = array(
            'name' => $rn !== '' ? $rn : 'Trang bị đang mặc',
            'qh'   => intval(itemtip_field($rival, 'qianghua', 0))
        );
    }

    if (is_object($player)) {
        // zhuangbei_realm_ok() chi dung duoc voi object (no doc $zb->realm_require),
        // con tin Phuong thi la array, nen so sanh truc tiep tu $req da chuan hoa.
        $payload['canWear'] = $req <= intval(itemtip_field($player, 'ulv', 0));
    }

    return $payload;
}

/**
 * Thuoc tinh data-tip de noi vao the <a>.
 * JSON_hex_* lo bo ky tu < > & ' " nen an toan trong thuoc tinh HTML.
 */
function itemtip_attr($zb, $rival = null, $player = null){
    $payload = itemtip_payload($zb, $rival, $player);
    if (!$payload) {
        return '';
    }
    $json = json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    if ($json === false || $json === '') {
        return '';
    }
    return ' data-tip="' . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . '"';
}

/**
 * Goi tat: lay doi thu cung o roi tra ve thuoc tinh data-tip.
 */
function itemtip_attr_for($zb, $player, $dblj){
    if (!$zb) {
        return '';
    }
    $rival = itemtip_rival($zb, itemtip_worn_map($player, $dblj));
    return itemtip_attr($zb, $rival, $player);
}

/**
 * Tin treo o Phuong thi luu ten + chi so trong fangshi_zb nhung khong luu
 * he, pham chat, o lap, yeu cau canh gioi. Do gop lai cau hinh JSON,
 * chi so cua tin treo duu tien vi no da tinh phan cuong hoa.
 */
function itemtip_merge_config($row){
    $zbid = intval(itemtip_field($row, 'zbid', 0));
    $config = $zbid ? zhuangbei_config_get($zbid) : false;
    $merged = is_array($config) ? $config : array();
    if (!is_array($row) && !is_object($row)) {
        return $merged;
    }
    foreach ($row as $key => $value) {
        if ($value === '' || $value === null) {
            continue;
        }
        $merged[$key] = $value;
    }
    return $merged;
}

function itemtip_attr_for_listing($row, $player, $dblj){
    $merged = itemtip_merge_config($row);
    if (empty($merged)) {
        return '';
    }
    return itemtip_attr_for($merged, $player, $dblj);
}
