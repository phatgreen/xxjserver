<?php
namespace player;

/**
 * Phiên bản ảnh trang bị. Đổi số này khi thay ảnh trong ./images/ hoặc
 * ./images/slot/ để trình duyệt (nhất là trên điện thoại) không hiện ảnh cũ trong cache.
 */
const ZB_IMG_VER = '2026100406';

function zhuangbei_img_ver($path){
    if ($path === '' || strpos($path, '?') !== false) {
        return $path;
    }
    return $path . '?v=' . ZB_IMG_VER;
}

/**
 * Đọc cấu hình trang bị từ JSON.
 * Bảng zhuangbei là cấu hình tĩnh nên chuyển sang JSON.
 * Bảng playerzhuangbei vẫn giữ trong database để lưu trang bị riêng của từng người chơi.
 */
function zhuangbei_config_path(){
    $paths = array(
        // File này thường nằm ở: /class/function/zhuangbei.php
        // Config thường nằm ở: /class/config/zhuangbei_config.json
        __DIR__ . '/../config/zhuangbei_config.json',
        __DIR__ . '/../../config/zhuangbei_config.json',
        __DIR__ . '/zhuangbei_config.json'
    );

    foreach ($paths as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return $paths[0];
}

function zhuangbei_config_all(){
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $file = zhuangbei_config_path();
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

function zhuangbei_config_get($zbid){
    $zbid = (string)intval($zbid);
    $all = zhuangbei_config_all();

    return (isset($all[$zbid]) && is_array($all[$zbid])) ? $all[$zbid] : false;
}

function hydrate_zhuangbei_config_to_object($object, $config){
    if (!$config || !is_array($config)) {
        return $object;
    }

    foreach ($config as $key => $value) {
        if ($key == 'zbtool') {
            $object->tool = $value;
        }
        $object->$key = $value;
    }

    return $object;
}

function zhuangbei_config_row($zbid){
    $config = zhuangbei_config_get($zbid);
    return $config ? $config : false;
}

function zhuangbei_config_rows_by_ids($ids){
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

        $row = zhuangbei_config_row($id);
        if ($row) {
            $rows[] = $row;
        }
    }

    return $rows;
}

/**
 * Map phẩm chất (rarity) trang bị: 10 bậc từ Phàm → Đạo.
 * Đồng bộ với wugong/linhcan/thechat/huyetmach.
 */
function zhuangbei_rarity_map(){
    return array(
        1 => array('name' => 'Phàm',  'color' => '#9e9e9e', 'mult' => 1.0),
        2 => array('name' => 'Linh',  'color' => '#4caf50', 'mult' => 1.15),
        3 => array('name' => 'Huyền', 'color' => '#2196f3', 'mult' => 1.3),
        4 => array('name' => 'Địa',  'color' => '#795548', 'mult' => 1.5),
        5 => array('name' => 'Thiên', 'color' => '#ff9800', 'mult' => 1.8),
        6 => array('name' => 'Thánh', 'color' => '#ffeb3b', 'mult' => 2.2),
        7 => array('name' => 'Tiên',  'color' => '#e040fb', 'mult' => 2.8),
        8 => array('name' => 'Thần',  'color' => '#f44336', 'mult' => 3.5),
        9 => array('name' => 'Đế',    'color' => '#b71c1c', 'mult' => 5.0),
        10 => array('name' => 'Đạo',  'color' => '#d4af37', 'mult' => 8.0),
        11 => array('name' => 'Hỗn Độn', 'color' => '#7e57c2', 'mult' => 12.0),  // làn 3: bậc 1000 / 1250
        12 => array('name' => 'Thái Sơ', 'color' => '#26c6da', 'mult' => 18.0),  // làn 4–5: bậc 1500 / 2000
        13 => array('name' => 'Vĩnh Hằng', 'color' => '#ffb300', 'mult' => 26.0), // làn 6–7: bậc 2500 / 3000
        14 => array('name' => 'Chí Tôn', 'color' => '#ff1744', 'mult' => 38.0)    // làn 8: bậc 3500
    );
}

/**
 * Tên phẩm chất trang bị theo rarity (1-10).
 */
function zhuangbei_rarity_name($rarity){
    $map = zhuangbei_rarity_map();
    $rarity = intval($rarity);
    return isset($map[$rarity]) ? $map[$rarity]['name'] : 'Phàm';
}

/**
 * Màu phẩm chất trang bị theo rarity (1-10).
 */
function zhuangbei_rarity_color($rarity){
    $map = zhuangbei_rarity_map();
    $rarity = intval($rarity);
    return isset($map[$rarity]) ? $map[$rarity]['color'] : '#9e9e9e';
}

/**
 * Hệ số nhân chỉ số theo rarity.
 */
function zhuangbei_rarity_mult($rarity){
    $map = zhuangbei_rarity_map();
    $rarity = intval($rarity);
    return isset($map[$rarity]) ? floatval($map[$rarity]['mult']) : 1.0;
}

/**
 * Map nguyên tố ngũ hành (đồng bộ linhcan).
 */
function zhuangbei_element_map(){
    return array(
        'kim'  => array('name' => 'Kim', 'color' => '#C0C0C0', 'icon' => '🪙'),
        'moc'  => array('name' => 'Mộc', 'color' => '#228B22', 'icon' => '🌿'),
        'thuy' => array('name' => 'Thủy', 'color' => '#1E90FF', 'icon' => '💧'),
        'hoa'  => array('name' => 'Hỏa', 'color' => '#FF4500', 'icon' => '🔥'),
        'tho'  => array('name' => 'Thổ', 'color' => '#8B4513', 'icon' => '🏔'),
        'all'  => array('name' => 'Ngũ Hành', 'color' => '#FFD700', 'icon' => '✨'),
        'none' => array('name' => 'Vô Hành', 'color' => '#8B0000', 'icon' => '🌀')
    );
}

function zhuangbei_element_name($element){
    $map = zhuangbei_element_map();
    return isset($map[$element]) ? $map[$element]['name'] : 'Vô Hành';
}

function zhuangbei_element_color($element){
    $map = zhuangbei_element_map();
    return isset($map[$element]) ? $map[$element]['color'] : '#8B0000';
}

/**
 * Bản màu SÁNG của ngũ hành để đọc được trên nền tối ( Mộc #228B22 / Thổ #8B4513
 * quá tối trong zhuangbei_element_map). Dùng cho text/badge, không dùng để so màu config.
 */
function zhuangbei_element_color_ui($element){
    $map = array(
        'kim'  => '#E2E8F0',
        'moc'  => '#4ADE80',
        'thuy' => '#38BDF8',
        'hoa'  => '#FB923C',
        'tho'  => '#D6A863',
        'all'  => '#FFD86B',
        'none' => '#94A3B8'
    );
    return isset($map[$element]) ? $map[$element] : '#94A3B8';
}

function zhuangbei_element_icon($element){
    $map = zhuangbei_element_map();
    return isset($map[$element]) ? $map[$element]['icon'] : '🌀';
}

/**
 * Quy tắc tương khắc ngũ hành (đồng bộ linhcan).
 * Kim khắc Mộc, Mộc khắc Thổ, Thổ khắc Thủy, Thủy khắc Hỏa, Hỏa khắc Kim.
 */
function zhuangbei_element_khac($attacker, $defender){
    $khac = array(
        'kim' => 'moc',
        'moc' => 'tho',
        'tho' => 'thuy',
        'thuy' => 'hoa',
        'hoa' => 'kim'
    );
    return isset($khac[$attacker]) && $khac[$attacker] === $defender;
}

/**
 * Tính hệ số tương khắc cho trang bị.
 * Trang bị có element riêng → ưu tiên dùng element trang bị thay linh căn.
 * - Cùng element: 1.0
 * - Khắc mục tiêu: 1.2 (+20%)
 * - Bị khắc: 0.85 (-15%)
 */
function zhuangbei_get_element_multiplier($zbElement, $targetElement){
    if (empty($zbElement) || $zbElement === 'none' || empty($targetElement)) {
        return 1.0;
    }
    if ($zbElement === $targetElement) {
        return 1.0;
    }
    if (zhuangbei_element_khac($zbElement, $targetElement)) {
        return 1.2;
    }
    if (zhuangbei_element_khac($targetElement, $zbElement)) {
        return 0.85;
    }
    return 1.0;
}

/**
 * Chọn hệ trong set đã nạp mà KHẮC mục tiêu (ưu tiên theo thứ tự chuẩn kim,moc,thuy,hoa,tho).
 * Trả '' khi không hệ nào khắc — caller tự fallback zb_element đại diện / linh căn.
 */
function zhuangbei_pick_element($set, $monsterElement) {
    if (!is_array($set) || empty($set) || $monsterElement === '' || $monsterElement === null) return '';
    foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $el) {
        if (in_array($el, $set, true) && zhuangbei_element_khac($el, $monsterElement)) return $el;
    }
    return '';
}

/**
 * Tên ô trang bị theo zbtool trong zhuangbei_config.json.
 * Thứ tự này là chuẩn duy nhất: zbtool của mỗi trang bị phải trỏ đúng ô ở đây,
 * và zhuangbei_slot_icons() phải cùng thứ tự.
 * 1 Vũ Khí, 2 Thiên Thuẫn, 3 Thiên Quan, 4 Giáp Bào, 5 Hộ Thủ, 6 Giới Chỉ, 7 Yêu Đai,
 * 8 Phi Phong, 9 Linh Phù, 10 Ngọc Nhãn, 11 Cổ Ngọc, 12 Vai Khôi, 13 Linh Đai,
 * 14 Linh Bội, 15 Tọa Kỵ, 16 Bảo Vật.
 */
function zhuangbei_tool_names(){
    return array(
        0  => 'Không giới hạn',
        1  => 'Vũ Khí',
        2  => 'Thiên Thuẫn',
        3  => 'Thiên Quan',
        4  => 'Giáp Bào',
        5  => 'Hộ Thủ',
        6  => 'Giới Chỉ',
        7  => 'Yêu Đai',
        8  => 'Phi Phong',
        9  => 'Linh Phù',
        10 => 'Ngọc Nhãn',
        11 => 'Cổ Ngọc',
        12 => 'Vai Khôi',
        13 => 'Linh Đai',
        14 => 'Linh Bội',
        15 => 'Tọa Kỵ',
        16 => 'Bảo Vật'
    );
}

function zhuangbei_tool_name($tool){
    $names = zhuangbei_tool_names();
    $tool = intval($tool);
    return isset($names[$tool]) ? $names[$tool] : 'Không rõ';
}

/**
 * Ảnh mặc định theo ô trang bị (dùng khi trang bị chưa có ảnh riêng).
 * Ảnh đặt trong ./images/slot/zb_slot_XX.png với XX là số ô, cùng thứ tự zhuangbei_tool_names().
 */
function zhuangbei_slot_icons(){
    $icons = array(
        1  => './images/slot/zb_slot_01.png',   // Vũ Khí
        2  => './images/slot/zb_slot_02.png',   // Thiên Thuẫn
        3  => './images/slot/zb_slot_03.png',   // Thiên Quan
        4  => './images/slot/zb_slot_04.png',   // Giáp Bào
        5  => './images/slot/zb_slot_05.png',   // Hộ Thủ
        6  => './images/slot/zb_slot_06.png',   // Giới Chỉ
        7  => './images/slot/zb_slot_07.png',   // Yêu Đai
        8  => './images/slot/zb_slot_08.png',   // Phi Phong
        9  => './images/slot/zb_slot_09.png',   // Linh Phù
        10 => './images/slot/zb_slot_10.png',   // Ngọc Nhãn
        11 => './images/slot/zb_slot_11.png',   // Cổ Ngọc
        12 => './images/slot/zb_slot_12.png',   // Vai Khôi
        13 => './images/slot/zb_slot_13.png',   // Linh Đai
        14 => './images/slot/zb_slot_14.png',   // Linh Bội
        15 => './images/slot/zb_slot_15.png',   // Tọa Kỵ
        16 => './images/slot/zb_slot_16.png'    // Bảo Vật
    );
    foreach ($icons as $i => $path) {
        $icons[$i] = zhuangbei_img_ver($path);
    }
    return $icons;
}

/**
 * Hai cot trang bi trang "Trang bi" va "Trang thai".
 * Trai = do mac len nguoi, phai = vu khi + phu + phap bao.
 * Moi cot 8 o, du 16 o cua zhuangbei_tool_names().
 */
function zhuangbei_equip_groups(){
    return array(
        'Trang bị'                => array(2, 3, 4, 5, 12, 8, 7, 13),
        'Vũ khí – Phù – Pháp bảo' => array(1, 9, 16, 6, 10, 11, 14, 15),
    );
}
function zhuangbei_slot_icon($tool){
    $icons = zhuangbei_slot_icons();
    $tool = intval($tool);
    return isset($icons[$tool]) ? $icons[$tool] : './images/slot/zb_slot_01.png';
}

/**
 * Cấp tối thiểu (yêu cầu cảnh giới) của trang bị.
 */
function zhuangbei_realm_require($zhuangbei){
    if (!$zhuangbei) {
        return 0;
    }
    if (isset($zhuangbei->realm_require)) {
        return intval($zhuangbei->realm_require);
    }
    return isset($zhuangbei->zblv) ? intval($zhuangbei->zblv) : 0;
}

/**
 * Kiểm tra người chơi đủ cảnh giới để mặc trang bị.
 */
function zhuangbei_realm_ok($zhuangbei, $player){
    $need = zhuangbei_realm_require($zhuangbei);
    $now = isset($player->ulv) ? intval($player->ulv) : 0;
    return $need <= $now;
}

/**
 * Điểm sức mạnh dùng cho "Trang bị nhanh".
 */
function zhuangbei_quick_score($zhuangbei){
    if (!$zhuangbei) {
        return -1;
    }

    return intval($zhuangbei->zbgj) * 10
        + intval($zhuangbei->zbfy) * 8
        + intval($zhuangbei->zbhp)
        + intval($zhuangbei->zbbj) * 30
        + intval($zhuangbei->zbxx) * 25
        + intval($zhuangbei->tuposjs) * 50
        + intval($zhuangbei->tupoexp) * 20
        + intval($zhuangbei->qianghua) * 80;
}

/**
 * Ô trang bị tương ứng với loại trang bị (zbtool).
 */
function zhuangbei_quick_slot($zhuangbei){
    if (!$zhuangbei || !isset($zhuangbei->tool)) {
        return 0;
    }

    $tool = intval($zhuangbei->tool);
    return ($tool >= 1 && $tool <= 16) ? $tool : 0;
}

/**
 * Đường dẫn ảnh trang bị.
 * Ưu tiên trường ảnh trong cấu hình, nếu không thì tìm ./images/{zbid}.png
 * (mỗi trang bị có một ảnh riêng đặt tên theo zbid).
 */
function zhuangbei_image_path($zhuangbei, $fallback = './images/slot/zb_slot_01.png'){
    if (!$zhuangbei) {
        return zhuangbei_img_ver($fallback);
    }

    $fields = array('zbimg', 'zbpic', 'zbtp', 'icon', 'img', 'image', 'pic');
    foreach ($fields as $field) {
        if (isset($zhuangbei->$field) && trim($zhuangbei->$field) != '') {
            $img = trim($zhuangbei->$field);
            if (preg_match('/^(https?:)?\/\//i', $img) || strpos($img, './') === 0 || strpos($img, '/') === 0) {
                return htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
            }
            return zhuangbei_img_ver('./images/' . htmlspecialchars(basename($img), ENT_QUOTES, 'UTF-8'));
        }
    }

    $zbid = isset($zhuangbei->zbid) ? intval($zhuangbei->zbid) : 0;
    if ($zbid > 0) {
        foreach (array('png', 'jpg', 'jpeg', 'gif', 'webp') as $ext) {
            if (is_file(__DIR__ . '/../../images/' . $zbid . '.' . $ext)) {
                return zhuangbei_img_ver('./images/' . $zbid . '.' . $ext);
            }
        }
    }

    return zhuangbei_img_ver($fallback);
}
