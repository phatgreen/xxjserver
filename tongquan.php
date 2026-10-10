<?php
/**
 * TONG QUAN HE THONG - TAM TIEN KY (Dao Hieu)
 * Mo: http://localhost/tongquan.php
 * Trang chi DOC file config JSON + DB game, khong ghi bat ki du lieu nao.
 * Muon xem du lieu moi nhat: chi can tai lai trang.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', '0');
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

define('TQ_ROOT', __DIR__);
define('TQ_TIME', date('H:i:s d/m/Y'));

/* ---------- Ham tro ---------- */
function tq_cfg($name){
    static $cache = array();
    if (!isset($cache[$name])){
        $f = TQ_ROOT . '/class/config/' . $name . '.json';
        $cache[$name] = is_file($f) ? json_decode(file_get_contents($f), true) : null;
    }
    return $cache[$name];
}
function tq_esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function tq_txt($s){ return trim(preg_replace('/\s+/u', ' ', strip_tags((string)$s))); }
function tq_show($s){ return tq_esc(tq_txt($s)); }
function tq_cut($s, $n){ $t = tq_txt($s); return tq_esc(mb_strlen($t, 'UTF-8') > $n ? mb_substr($t, 0, $n, 'UTF-8') . '...' : $t); }
function tq_hex($s){ return preg_match('/#[0-9A-Fa-f]{3,8}/', (string)$s, $m) ? $m[0] : '#9a9a9a'; }
function tq_n($v){
    if ($v === null || $v === '' || !is_numeric($v)) return '';
    return number_format((float)$v, ((float)$v == (int)(float)$v) ? 0 : 2);
}
function tq_elem($e){
    $m = array('kim'=>'Kim','moc'=>'Mộc','thuy'=>'Thủy','hoa'=>'Hỏa','tho'=>'Thổ','none'=>'—','all'=>'Đủ hành');
    if (is_array($e)) return '—';
    $e = strtolower((string)$e);
    return isset($m[$e]) ? $m[$e] : tq_show($e);
}
function tq_h($s){ return tq_show(ucfirst(str_replace('_ ', ' ', str_replace('_', ' ', (string)$s)))); }
function tq_dot($hex){ return '<span class="dot" style="background:' . tq_esc($hex) . '"></span>'; }
/* Làm sáng màu quá tối để đọc được trên nền đen */
function tq_light($hex){
    if (!preg_match('/^#([0-9a-f]{6})$/i', (string)$hex, $m)) return '#e8d5b5';
    $c = hexdec($m[1]); $r = ($c >> 16) & 255; $g = ($c >> 8) & 255; $b = $c & 255;
    $lum = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    if ($lum >= 0.55) return $hex;
    $t = (0.55 - $lum) / 0.5; if ($t > 1) $t = 1;
    $r = (int)($r + (255 - $r) * $t); $g = (int)($g + (255 - $g) * $t); $b = (int)($b + (255 - $b) * $t);
    return sprintf('#%02X%02X%02X', $r, $g, $b);
}

/* Mảng key=>value sang dòng [key,label] */
function tq_list($data){
    $out = array();
    if (!is_array($data)) return $out;
    foreach ($data as $k => $v){
        if (is_array($v)) $out[] = array($k, isset($v['name']) ? $v['name'] : json_encode($v));
    }
    return $out;
}

/* ---------- Kết nối DB (tùy chọn, hỏng cũng không sao) ---------- */
$tqDb = null; $midName = array(); $qyOfMid = array(); $qyName = array();
try {
    $dsn = 'mysql:host=localhost;dbname=game;charset=utf8mb4';
    $tqDb = new PDO($dsn, 'root', 'www.aae.ink', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (Exception $e) { $tqDb = null; }
if ($tqDb){
    try { foreach ($tqDb->query('SELECT mid,mname FROM mid') as $r) $midName[(int)$r['mid']] = tq_txt($r['mname']); } catch (Exception $e) {}
    try { foreach ($tqDb->query('SELECT * FROM qy') as $r){
        $qn = isset($r['qyname']) ? tq_txt($r['qyname']) : '';
        if (isset($r['mid'])) $qyOfMid[(int)$r['mid']] = $qn;
        if ($qn !== '') $qyName[] = $qn;
    } } catch (Exception $e) {}
}
function tq_mid($id){ global $midName; $id=(int)$id; return isset($midName[$id]) ? $midName[$id] : ('Ô bản đồ #' . $id); }

/* ---------- Cảnh giới ---------- */
$rangesjj = array();
include TQ_ROOT . '/class/rangesjj.php';
$tqRealms = array();
foreach ($rangesjj as $i => $html){
    $tqRealms[$i] = array('name' => tq_txt($html), 'color' => tq_hex($html));
}
function tq_realm_name($idx){ global $tqRealms; $idx=(int)$idx; if ($idx<=0) return '—'; if (!isset($tqRealms[$idx-1])) return 'Cảnh #' . $idx; return $tqRealms[$idx-1]['name']; }
// realm_require của trang bị là NGƯỠNG ulv (50 cấp/cảnh, rangeslv), không phải chỉ số rangesjj.
function tq_realm_req($ulvReq){ global $tqRealms; $ulvReq=(int)$ulvReq; if ($ulvReq<=0) return '—';
    $idx=(int)ceil($ulvReq/50); if ($idx>count($tqRealms)) return 'Cấp '.$ulvReq;
    return tq_realm_name($idx) . ' (cấp ' . $ulvReq . ')'; }
$tqRealmGroups = array(
    array('Phàm & Nhập Môn', 0, 5, ''),
    array('Luyện Khí → Nguyên Anh', 6, 10, 'fx-mist'),
    array('Cao Giai (Tụ Thần → Đại Thừa)', 11, 16, 'fx-ember'),
    array('Thánh & Đế Cảnh', 17, 24, 'fx-sky'),
    array('Phi Thăng', 25, 25, 'fx-pulse'),
    array('Tiên Giới', 26, 34, 'fx-jade'),
    array('Kim Tiên → Tiên Đế', 35, 45, 'fx-gold'),
    array('Thần Giới', 46, 53, 'fx-thunder'),
    array('Hồng Hoang', 54, 55, 'fx-cosmos'),
    array('Thiên Đạo & Sáng Thế', 56, 74, 'fx-cosmos'),
    array('Thánh Nhân', 75, 82, 'fx-blood'),
    array('Đạo Tổ (tối cao)', 83, 85, 'fx-to'),
);

/* ---------- Đăng ký khu vực (section) + thẻ sơ đồ ---------- */
$GLOBALS['TQ_GROUPS'] = array(
    'tien-trinh' => array('Tiến trình & Cảnh giới', '⛰'),
    'nuoi-duong' => array('Nuôi dưỡng nhân vật', '✦'),
    'chiendau'   => array('Chiến đấu & Phó bản', '⚔'),
    'kinhte'     => array('Kinh tế & Sản xuất', '🏮'),
    'nhiemvu'    => array('Nhiệm vụ & Thành tựu', '📜'),
    'thegioi'    => array('Thế giới & Xã hội', '🗺'),
);
$GLOBALS['TQ_SECS'] = array();
function tq_sec($group, $id, $icon, $title, $count, $desc, $rels, $inner){
    $GLOBALS['TQ_SECS'][$group][] = array(
        'id' => $id, 'icon' => $icon, 'title' => $title,
        'count' => $count, 'desc' => $desc, 'rels' => $rels, 'html' => $inner,
    );
}
function tq_table($cols, $rows){
    $h = '<div class="tbl-wrap"><table class="tbl"><thead><tr>';
    foreach ($cols as $c) $h .= '<th>' . tq_esc($c) . '<span class="arr"></span></th>';
    $h .= '</tr></thead><tbody>';
    foreach ($rows as $r){
        $h .= '<tr>';
        foreach ($cols as $i => $c) $h .= '<td>' . (isset($r[$i]) ? $r[$i] : '') . '</td>';
        $h .= '</tr>';
    }
    return $h . '</tbody></table></div>';
}
function tq_searchable($id, $cols, $rows, $note){
    $h = '<div class="tbl-tools"><input class="q" type="search" data-for="' . tq_esc($id) . '" placeholder="Tìm trong bảng này... (' . count($rows) . ' dòng)"></div>';
    $h .= $note !== '' ? '<p class="note">' . $note . '</p>' : '';
    return $h . tq_table($cols, $rows);
}

/* ---------- Bảng tra tên chéo ---------- */
$zbAll  = tq_cfg('zhuangbei_config');
$djAll  = tq_cfg('daoju_config');
$ydAll  = tq_cfg('yaodan_config');
$ypAll  = tq_cfg('yaopin_config');
$wgAll  = tq_cfg('wugong_config');
$cpAll  = tq_cfg('congphap_config');
$petAll = tq_cfg('pet_config');
function tq_name_of($data, $id, $nameKey){
    if (!is_array($data)) return 'Món #' . (int)$id;
    foreach ($data as $k => $v){
        if (is_array($v) && isset($v['id']) && (int)$v['id'] === (int)$id) return tq_txt($v[$nameKey]);
        if ((int)$k === (int)$id && isset($v[$nameKey])) return tq_txt($v[$nameKey]);
    }
    return 'Món #' . (int)$id;
}
function tq_zb_name($id){ return tq_name_of($GLOBALS['zbAll'], $id, 'zbname'); }
function tq_dj_name($id){ return tq_name_of($GLOBALS['djAll'], $id, 'djname'); }
function tq_yd_name($id){ return tq_name_of($GLOBALS['ydAll'], $id, 'ydname'); }
function tq_yp_name($id){ return tq_name_of($GLOBALS['ypAll'], $id, 'ypname'); }
function tq_mat_names($materials, $lookup){
    $out = array();
    foreach ((array)$materials as $m){
        if (!isset($m['id'])) continue;
        $nm = $lookup ? call_user_func($lookup, $m['id']) : ('#' . $m['id']);
        $out[] = $nm . ' ×' . (int)$m['amount'];
    }
    return tq_esc(implode(', ', $out));
}
$GLOBALS['TQ_SLOTS'] = array(0=>'—',1=>'Vũ Khí',2=>'Thiên Thuẫn',3=>'Thiên Quan',4=>'Giáp Bào',5=>'Hộ Thủ',
    6=>'Giới Chỉ',7=>'Yêu Đai',8=>'Phi Phong',9=>'Linh Phù',10=>'Ngọc Nhãn',11=>'Cổ Ngọc',
    12=>'Vai Khôi',13=>'Linh Đai',14=>'Linh Bội',15=>'Tọa Kỵ',16=>'Bảo Vật');
$GLOBALS['TQ_WGCAT'] = array('khinh_cong'=>'Khinh Công','ngoai_cong'=>'Ngoại Công','noi_cong'=>'Nội Công','tap_ky'=>'Tạp Kỹ');
$GLOBALS['TQ_WGRAR'] = array('nhap_mon'=>'Nhập Môn','trung_cap'=>'Trung Cấp','thuong_thua'=>'Thường Thừa','than_cong'=>'Thần Công');
$GLOBALS['TQ_CPEFF'] = array('absorb_speed'=>'Hút linh khí','exp_bonus'=>'EXP tu luyện','hp_regen'=>'Hồi máu',
    'drop_rate'=>'Tỉ lệ rơi','dmg_reduce'=>'Giảm sát thương','heal_bonus'=>'Tăng trị liệu','def_bonus'=>'Tăng phòng',
    'mining_bonus'=>'Khai khoáng','atk_bonus'=>'Tăng công','crit_dmg'=>'Bạo thương','stone_bonus'=>'Đá quý');
function tq_mapv($tbl, $k){ return isset($tbl[$k]) ? $tbl[$k] : tq_h($k); }

/* ================= TIẾN TRÌNH & CẢNH GIỚI ================= */
$band = '';
foreach ($GLOBALS['TQ_GROUPS'] as $gid => $g) if (!isset($GLOBALS['TQ_SECS'][$gid])) $GLOBALS['TQ_SECS'][$gid] = array();
foreach ($tqRealmGroups as $g){
    $cls = isset($g[3]) && $g[3] !== '' ? ' ' . $g[3] : '';
    $band .= '<div class="rgroup"><div class="rgname' . $cls . '">' . tq_esc($g[0]) . '</div><div class="chips">';
    for ($i = $g[1]; $i <= $g[2] && $i < count($tqRealms); $i++){
        $ccls = $cls;
        if (isset($g[3]) && $g[3] === 'fx-to') $ccls = ' fx-to-' . ($i - $g[1] + 1);
        $band .= '<span class="rchip' . $ccls . '" style="color:' . tq_esc(tq_light($tqRealms[$i]['color'])) . '">'
              . (isset($tqRealms[$i]['name']) ? tq_esc($tqRealms[$i]['name']) : '')
              . '<i>' . ($i + 1) . '</i></span>';
    }
    $band .= '</div></div>';
}
tq_sec('tien-trinh', 'canh-gioi', '⛰', 'Cảnh giới', count($tqRealms),
    'Toàn bộ ' . count($tqRealms) . ' cảnh giới từ Nhập Môn đến Vô Ngã, chia ' . count($tqRealmGroups) . ' giai đoạn lớn.',
    array('Đột phá qua tupo.php', 'Mỗi cảnh có nhiều tầng, 50 cấp / cảnh', 'Trang bị & võ công đòi hỏi cảnh giới tối thiểu'),
    $band . '<p class="note">Nguồn: <code>class/rangesjj.php</code>. Con số nhỏ ở góc mỗi thẻ là thứ tự cảnh giới (1=' . tq_esc($tqRealms[0]['name']) . '). Cảnh cao cần đột phá lớn (lôi kiếp) - xem khối Đột phá bên dưới.</p>');

tq_sec('tien-trinh', 'dot-pha', '🌀', 'Đột phá & Thiên phú', '2',
    'Đột phá nhỏ (tăng tầng) và đột phá lớn (vượt cảnh giới, có lôi kiếp). Thành công được điểm Thiên phú để cộng chỉ số.',
    array('Thành công → +Thiên phú', 'Trang bị + Bùa làm tăng tỉ lệ', 'Thất bại → mất thể lực/tài nguyên'),
    '<p class="note">Logic chính: <code>game/tupo.php</code> + <code>class/player.php</code>. Tỉ lệ = cơ bản − phạt theo cấp − phạt theo cảnh + cộng từ trang bị và bùa. Mỗi cảnh giới yêu cầu <b>cảnh giới tối thiểu</b> ghi trong bảng Trang bị / Võ công / Công pháp.</p>');

tq_sec('tien-trinh', 'tu-luyen', '🧘', 'Tu luyện (AFK)', '1',
    'Hấp thu linh khí khi offline/online, tốc độ phụ thuộc Công pháp đang tu.',
    array('Công pháp → tốc độ hút linh khí', 'Linh căn → hệ số hành', 'Thiên Thư / khai khoáng ăn theo công pháp'),
    '<p class="note">File: <code>game/xiulian.php</code>. Linh khí quy đổi ra EXP và Linh Thạch. Công pháp hiệu ứng "Hút linh khí" cộng thẳng tốc độ này.</p>');

/* ================= NUÔI DƯỠNG NHÂN VẬT ================= */
$rows = array();
foreach ((array)$zbAll as $e){
    $tool = intval($e['zbtool']);
    $rows[] = array(
        tq_dot(tq_hex($e['zbys'])) . tq_show($e['zbname']),
        tq_show($e['rarity_name']),
        tq_mapv($GLOBALS['TQ_SLOTS'], $tool),
        tq_n($e['zblv']), tq_n($e['zbhp']), tq_n($e['zbgj']), tq_n($e['zbfy']), tq_n($e['zbbj']), tq_n($e['zbxx']),
        tq_realm_req($e['realm_require']),
        intval($e['taozhuang']) ? ('Set ' . intval($e['taozhuang'])) : '',
    );
}
tq_sec('nuoi-duong', 'trang-bi', '🗡', 'Trang bị', count($rows),
    '180 món trên 16 ô (Vũ Khí → Bảo Vật), bậc Phàm → Linh → Huyền → Địa → Thiên.',
    array('← Đạo cụ: Cường Hóa Thạch lên cấp', '→ Set (Thần Trang) cộng % toàn thân', 'Quái rơi theo bản đồ, mua ở Cửa hàng', 'Cảnh giới tối thiểu = cột cuối'),
    tq_searchable('tbl-zb', array('Tên', 'Bậc', 'Ô lắp', 'Cấp', 'Máu', 'Công', 'Thủ', 'Bạo', 'Tốc', 'Cảnh yêu cầu', 'Set'), $rows,
    'Chấm màu trước tên = màu phẩm chất trong game. Nguồn: <code>zhuangbei_config.json</code>.'));

$tqNhName  = array('kim'=>'Kim','moc'=>'Mộc','thuy'=>'Thủy','hoa'=>'Hỏa','tho'=>'Thổ');
$tqNhColor = array('kim'=>'#E2E8F0','moc'=>'#4ADE80','thuy'=>'#38BDF8','hoa'=>'#FB923C','tho'=>'#D6A863');
$tqNhCount = array('kim'=>0,'moc'=>0,'thuy'=>0,'hoa'=>0,'tho'=>0);
$tqNhItems = 0;
if ($tqDb){
    try { $tqNhRes = $tqDb->query("SELECT element FROM playerzhuangbei WHERE element <> 'none'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tqNhRes as $tqNhRow) {
        $tqNhItems++;
        foreach (explode(',', $tqNhRow['element']) as $tqElOne) {
            $tqElOne = trim($tqElOne);
            if (isset($tqNhCount[$tqElOne])) $tqNhCount[$tqElOne]++;
        }
    } } catch (Exception $e) {}
}
$tqNhRows = array();
foreach ($tqNhCount as $tqElKey => $tqElCnt) {
    $tqNhRows[] = array('<span style="color:' . $tqNhColor[$tqElKey] . '">● ' . $tqNhName[$tqElKey] . '</span>', tq_n($tqElCnt));
}
tq_sec('nuoi-duong', 'nap-hanh', '☯', 'Thuộc tính Ngũ Hành (đang bật)', $tqNhItems,
    'Số món trang bị đã nạp hệ trong DB (playerzhuangbei.element, cột mới Task 1).',
    array('→ Rèn từ popup trang bị: nút Nạp [hệ]', 'Hợp linh căn: +3..22% chỉ số theo nấc 1/3/6/9/12 món', 'Thất bại chỉ mất nguyên liệu'),
    tq_searchable('tbl-nh', array('Hệ', 'Số món đã nạp'), $tqNhRows, ''));

$rows = array();
foreach ((array)$wgAll as $e){
    $st = array();
    foreach (array('add_gj'=>'Công','add_fy'=>'Thủ','add_hp'=>'Máu','add_bj'=>'Bạo','add_xx'=>'Tốc') as $k => $lb)
        if (!empty($e[$k])) $st[] = $lb . ' +' . tq_n($e[$k]);
    $rows[] = array(
        tq_show($e['wgname']),
        tq_mapv($GLOBALS['TQ_WGCAT'], $e['category']),
        tq_mapv($GLOBALS['TQ_WGRAR'], $e['rarity']),
        tq_elem($e['element']),
        implode(', ', $st),
        tq_cut($e['wginfo'], 70),
        tq_n($e['price_linhthong']),
        tq_realm_name($e['realm_require']),
    );
}
tq_sec('nuoi-duong', 'vo-cong', '👊', 'Võ công', count($rows),
    '30 tuyệt kỹ phân nhóm Khinh Công / Ngoại Công / Nội Công / Tạp Kỹ.',
    array('Học từ Bí kíp (đạo cụ) hoặc cửa hàng', 'Cộng chỉ số khi trang bị', 'Có giới hạn cảnh giới'),
    tq_searchable('tbl-wg', array('Tên', 'Nhóm', 'Độ hiếm', 'Hành', 'Cộng chỉ số', 'Mô tả', 'Giá LT', 'Cảnh yêu cầu'), $rows, ''));

$rows = array();
foreach ((array)$cpAll as $e){
    $eff = tq_mapv($GLOBALS['TQ_CPEFF'], $e['effect_type']);
    $effv = tq_n($e['effect_value']) . '%' . (!empty($e['effect_value_max']) ? ' → ' . tq_n($e['effect_value_max']) . '%' : '');
    $rows[] = array(
        tq_show($e['cpname']), tq_show($e['cpsort']), tq_elem($e['element']),
        $eff, $effv, tq_n($e['max_level']),
        'Phẩm ' . tq_n($e['quality']) . ' · Bậc ' . tq_n($e['tier']),
        tq_n($e['price_linhthong']) . ($e['price_masthach'] ? ' / MT ' . tq_n($e['price_masthach']) : ''),
        tq_realm_name($e['realm_require']),
    );
}
tq_sec('nuoi-duong', 'cong-phap', '📖', 'Công pháp', count($rows),
    '28 công pháp: Nội Công, Tâm Pháp, Huyễn Thuật... quyết định tốc độ tu luyện và hiệu ứng bị động.',
    array('→ Tăng tốc Tu luyện AFK (hút linh khí)', 'Bị phạt công/thủ khi chưa luyện tới', 'Học từ sách bán ở cửa hàng / rớt từ quái'),
    tq_searchable('tbl-cp', array('Tên', 'Thể loại', 'Hành', 'Hiệu quả', 'Cường độ', 'Level max', 'Phẩm/Bậc', 'Giá', 'Cảnh yêu cầu'), $rows, ''));

$lcAll = tq_cfg('linhcan_config');
$lcUp  = tq_cfg('linhcan_upgrade_config');
$tierNames = (isset($lcUp['tier_names']) && is_array($lcUp['tier_names'])) ? $lcUp['tier_names'] : array();
$rows = array();
foreach ((array)$lcAll as $e){
    $st = array();
    foreach ((array)$e['stats'] as $k => $v) if ($v) $st[] = tq_h($k) . ' +' . tq_n($v);
    $rows[] = array(
        tq_dot($e['color']) . tq_show($e['name']),
        tq_elem($e['element']),
        tq_mapv($tierNames, $e['tier']),
        'Phẩm ' . tq_n($e['quality']) . ' (×' . tq_n($e['quality_mult']) . ')',
        implode(', ', $st),
        tq_cut($e['description'], 80),
    );
}
tq_sec('nuoi-duong', 'linh-can', '🔮', 'Linh căn (Ngũ hành)', count($rows),
    '22 linh căn Kim–Mộc–Thủy–Hỏa–Thổ, nhận bằng đan hoặc boss linh căn; khắc chế hành khi đánh nhau.',
    array('← Đan ngũ hành / phần thưởng boss', '→ Thắng/linh thạch khi thăng bậc', 'Nối với Huyết mạch & Công pháp cùng hành'),
    tq_searchable('tbl-lc', array('Tên', 'Hành', 'Bậc', 'Phẩm chất', 'Chỉ số', 'Mô tả'), $rows, 'Nguồn: <code>linhcan_config.json</code>.'));

$rows = array();
foreach ((array)(isset($lcUp['upgrades']) ? $lcUp['upgrades'] : array()) as $e){
    $rows[] = array($e['from_name'] . ' → ' . $e['to_name'], tq_n($e['linh_thach_cost']), tq_n($e['da_ngu_hanh_cost']), tq_n($e['uexp_cost']), tq_n($e['success_rate']) . '%', tq_cut($e['description'], 90));
}
tq_sec('nuoi-duong', 'linh-can-len', '⤴', 'Linh căn - bậc thăng', count($rows),
    'Lộ trình nâng bậc linh căn bằng Linh Thạch + Đá Ngũ Hành.',
    array('Đá ngũ hành: boss linh căn, nhiệm vụ', 'EXP upgrade (uexp) từ đánh quái'),
    tq_searchable('tbl-lcu', array('Nâng từ → lên', 'Linh thạch', 'Đá ngũ hành', 'uEXP', 'Tỉ lệ', 'Ghi chú'), $rows, ''));

$lcSk = tq_cfg('linhcan_skill_config');
$rows = array();
foreach ((array)(isset($lcSk['skills']) ? $lcSk['skills'] : array()) as $k => $e){
    $rows[] = array(tq_show($e['name']), tq_elem($e['element']), tq_h($e['type']), tq_cut($e['description'], 80),
        tq_n($e['multiplier']) . '× ATK', tq_n($e['mp_cost_percent']) . '% MP', tq_n($e['cooldown_minutes']) . ' phút', 'Cấp ' . tq_n($e['unlock_level']));
}
tq_sec('nuoi-duong', 'linh-can-ky-nang', '✨', 'Linh căn - kỹ năng hành', count($rows),
    'Kỹ năng chủ động theo hành của linh căn (Hỏa Diệm Chưởng, ...).',
    array('Chỉ dùng khi mang linh căn đúng hành', 'Khóa theo cấp nhân vật'),
    tq_searchable('tbl-lcs', array('Kỹ năng', 'Hành', 'Loại', 'Mô tả', 'Sát thương', 'Tiêu hao', 'Hồi kỹ năng', 'Mở ở'), $rows, ''));

$tcAll = tq_cfg('thechat_config');
$rows = array();
foreach ((array)$tcAll as $e){
    $rows[] = array(tq_dot($e['color']) . $e['icon'] . ' ' . tq_show($e['name']), tq_show($e['category_name']), tq_elem($e['element']),
        tq_show($e['rarity_name']), tq_n($e['weight']), tq_cut($e['description'], 90));
}
tq_sec('nuoi-duong', 'the-chat', '🧬', 'Thệ chất', count($rows),
    '30 thể chất quý hiếm: Phàm Thể → Linh → Vương → Thánh/Tiên → Thần → Nghịch Thiên.',
    array('Nhận khi đột phá (r ngẫu nhiên theo trọng số)', 'Thăng phẩm bằng Linh Thạch + đan', 'Phẩm chất chỉ là hệ song song — Nhục Thân tu được dù chưa có thệ chất'),
    tq_searchable('tbl-tc', array('Tên', 'Nhóm', 'Hành', 'Phẩm', 'Trọng số rơi', 'Mô tả'), $rows, 'Trọng số = độ hiếm khi random lúc đột phá.'));

$tcUp = tq_cfg('thechat_upgrade_config');
$rows = array();
foreach ((array)(isset($tcUp['upgrades']) ? $tcUp['upgrades'] : array()) as $e){
    $rows[] = array($e['from_name'] . ' → ' . $e['to_name'], tq_n($e['linh_thach_cost']), tq_dj_name($e['material_id']) . ' ×' . tq_n($e['material_amount']), tq_n($e['success_rate']) . '%', tq_cut($e['description'], 90));
}
tq_sec('nuoi-duong', 'the-chat-len', '⤴', 'Thệ chất - thăng phẩm', count($rows),
    'Bảng chi phí thăng phẩm thệ chất (Phàm → Linh → Vương → Thánh/Tiên → Thần → Nghịch Thiên).',
    array('Chỉ có thể thăng phẩm khi nhân vật ĐÃ có thệ chất', 'Tốn Linh Thạch + đạo cụ đặc chủng', 'UI: game/thechat_upgrade.php'),
    tq_searchable('tbl-tcu', array('Nâng từ → lên', 'Linh thạch', 'Vật liệu', 'Tỉ lệ', 'Ghi chú'), $rows, ''));

$tcBody = tq_cfg('thechat_body_config');
$rowsN = array();
foreach ((array)(isset($tcBody['nodes']) ? $tcBody['nodes'] : array()) as $e){
    $rowsN[] = array(tq_n($e['id']) . '. ' . tq_show($e['name']) . ' (' . tq_show($e['short']) . ')',
        tq_show($tcBody['stat_labels'][$e['stat']]), '+' . tq_n($e['per_lv']), tq_show($e['slot']), tq_cut($e['desc'], 90));
}
$rowsT = array();
foreach ((array)(isset($tcBody['tiers']) ? $tcBody['tiers'] : array()) as $e){
    $mat = intval($e['material_id']) > 0 ? (tq_show($tcBody['material_names'][$e['material_id']]) . ' ×' . tq_n($e['material_amount'])) : '—';
    $rowsT[] = array('Bậc ' . tq_n($e['stage']) . ': ' . tq_show($e['name']), '+' . tq_n($e['lv_cap']),
        tq_n($e['total_lv_require']), tq_realm_req($e['realm_min_level']), $mat, tq_n($e['linh_thach_cost']), tq_cut($e['desc'], 70));
}
$rowsM = array();
foreach ((array)(isset($tcBody['milestones']) ? $tcBody['milestones'] : array()) as $e){
    $rowsM[] = array(tq_show($e['name']), tq_n($e['total_lv']), '+' . tq_n($e['bonus_percent']) . '%', '');
}
$r = $tcBody['refine'];
tq_sec('nuoi-duong', 'the-chat-nhuc-than', '🧎', 'Nhục Thân (6 bộ phận + Cốt Cách)', count($rowsN) + count($rowsT) + count($rowsM),
    'Đường tu luyện thân thể: tôi 6 bộ phận (Bì Phủ/Cơ Nhục/Cân Cốt/Cốt Tủy/Huyết Khí/Thần Thức), mỗi bộ phận cộng một chỉ số riêng; đột phá Cốt Cách để nâng trần cấp.',
    array('Không cần có thệ chất vẫn tu được', 'Luyện hóa tốn Tinh Thể Đá + Linh Thạch', 'Đột phá Cốt Cách cần tổng cấp + cảnh giới + vật liệu', 'UI: game/thechat.php (cmd=thechat)', 'Số liệu: player_thechat_node'),
    tq_searchable('tbl-nbn', array('Bộ phận', 'Chỉ số', 'Mỗi cấp', 'Vị trí', 'Mô tả'), $rowsN,
        '<b>Luyện hóa:</b> mỗi lần +1 cấp tốn ' . tq_n($r['material_base']) . ' Tinh Thể Đá (cứ ' . tq_n($r['material_per_lv']) . ' cấp tăng thêm 1) + '
        . tq_n($r['linh_thach_base']) . ' Linh Thạch (cộng thêm ' . tq_n($r['linh_thach_per_lv']) . '/cấp).')
    . tq_searchable('tbl-nbt', array('Cốt Cách', 'Trần mỗi bộ phận', 'Cần tổng cấp', 'Cần cảnh giới', 'Vật liệu', 'Linh Thạch', 'Mô tả'), $rowsT, '<b>Cốt Cách (đột phá):</b>')
    . tq_searchable('tbl-nbm', array('Mốc', 'Tổng cấp 6 bộ phận', 'Chỉ số Nhục Thân', ''), $rowsM, '<b>Mốc tổng cấp:</b>'));

$hmAll = tq_cfg('huyetmach_config');
$rows = array();
foreach ((array)$hmAll as $e){
    if (!isset($e['name'])) continue;
    $st = array();
    foreach ((array)(isset($e['base_stats']) ? $e['base_stats'] : array()) as $k => $v) if (is_numeric($v) && $v) $st[] = tq_h($k) . ' +' . tq_n($v);
    $rows[] = array(tq_dot($e['color']) . $e['icon'] . ' ' . tq_show($e['name']), tq_elem($e['element']), tq_show($e['rarity_name']), tq_n($e['weight']), implode(', ', $st), tq_cut($e['description'], 90));
}
$hmAw = tq_cfg('huyetmach_awakening_config'); $hmTf = tq_cfg('huyetmach_transform_config');
$rows2 = array();
foreach ((array)(isset($hmAw['stages']) ? $hmAw['stages'] : array()) as $e){
    $rows2[] = array($e['from_name'] . ' → ' . $e['to_name'], tq_show($e['realm_require']) . ' (cấp ' . tq_n($e['realm_min_level']) . ')',
        '+' . tq_n(($e['stat_bonus_mult'] - 1) * 100) . '% chỉ số', '+ ' . tq_n($e['element_dmg_add']) . '% sát thương hành', tq_cut($e['passive_description'], 80));
}
$rows3 = array();
foreach ((array)(isset($hmTf['transforms']) ? $hmTf['transforms'] : array()) as $e){
    $rows3[] = array($e['icon'] . ' ' . tq_show($e['name']), tq_zb_name(0) === '' ? '' : tq_show(tq_name_of($hmAll, $e['bloodline_id'], 'name')),
        tq_cut($e['description'], 90), tq_n($e['hp_cost_percent']) . '% HP', tq_n($e['duration_turns']) . ' lượt', tq_n($e['cooldown_minutes']) . ' phút');
}
tq_sec('nuoi-duong', 'huyet-mach', '🩸', 'Huyết mạch', count($rows) + count($rows2) + count($rows3),
    '13 dòng huyết mạch (Chân Long, Chân Phượng, Kỳ Lân...) kèm thức tỉnh và biến hóa.',
    array('Random khi đột phá lớn (trọng số)', 'Thức tỉnh cần boss + nhiệm vụ riêng', 'Biến hóa: tiêu hao % HP, có thời gian hồi tính bằng phút'),
    tq_searchable('tbl-hm', array('Huyết mạch', 'Hành', 'Phẩm', 'Trọng số', 'Chỉ số nền', 'Mô tả'), $rows, '<b>Danh sách:</b>')
    . tq_searchable('tbl-hma', array('Giai đoạn', 'Điều kiện', 'Thưởng chỉ số', 'Thưởng hành', 'Bị động'), $rows2, '<b>Thức tỉnh:</b>')
    . tq_searchable('tbl-hmt', array('Form biến hóa', 'Dòng máu', 'Hiệu quả', 'Tiêu hao', 'Thời lượng', 'Hồi'), $rows3, '<b>Biến hóa:</b>'));
$hmV = tq_cfg('huyetmach_vessel_config');
$rowsV = array();
if (is_array($hmV)) {
    $labels = (array)(isset($hmV['stat_labels']) ? $hmV['stat_labels'] : array());
    $caps   = (array)(isset($hmV['stat_cap']) ? $hmV['stat_cap'] : array());
    foreach ((array)(isset($hmV['vessels']) ? $hmV['vessels'] : array()) as $e) {
        $stat = $e['stat'];
        $cap  = isset($caps[$stat]) ? ' (trần ' . tq_n($caps[$stat]) . '%)' : '';
        $rowsV[] = array(tq_show($e['name']), tq_show($e['short']),
            tq_show(isset($labels[$stat]) ? $labels[$stat] : tq_h($stat)) . $cap,
            '+' . tq_n($e['per_lv']) . '/cấp', tq_show($e['group']), tq_show($e['slot']),
            tq_cut($e['desc'], 90));
    }
    $rowsT4 = array();
    foreach ((array)(isset($hmV['tiers']) ? $hmV['tiers'] : array()) as $e) {
        $mat = ($e['material_id'] > 0 && isset($hmV['material_names'][$e['material_id']]))
            ? tq_show($hmV['material_names'][$e['material_id']]) . ' x' . tq_n($e['material_amount']) : '—';
        $rowsT4[] = array(tq_dot($e['color']) . $e['icon'] . ' ' . tq_show($e['name']),
            '+' . tq_n($e['lv_cap']) . '/mạch', tq_n($e['total_lv_require']),
            tq_realm_req($e['realm_min_level']), $mat, tq_n($e['linh_thach_cost']),
            tq_n($e['boss_require']) . ' boss mạch huyết', tq_cut($e['desc'], 70));
    }
    $rowsM4 = array();
    foreach ((array)(isset($hmV['milestones']) ? $hmV['milestones'] : array()) as $e) {
        $rowsM4[] = array(tq_show($e['name']), tq_n($e['total_lv']), '+' . tq_n($e['bonus_percent']) . '%');
    }
    $rv = $hmV['refine'];
    tq_sec('nuoi-duong', 'mach-huyet', '❤', 'Mạch Huyết (8 mạch luyện được)', count($rowsV) + count($rowsT4) + count($rowsM4),
        'Lớp chỉ số thứ 4 của nhân vật: 8 mạch (4 mạch dương cộng chỉ số sẵn có, 4 mạch âm can thiệp phần trăm trực tiếp lúc đánh nhau).',
        array('UI: game/huyetmach.php (cmd=huyetmach)', 'Boss riêng cho từng người chơi: bảng huyetmach_boss',
            'Chỉ số âm: Né Tránh / Xuyên Giảm / Giảm Vật Lý / Vận May',
            'Xuyên Giảm KHÔNG áp dụng khi PvP (đo pen 12% +9,32%, pen 15% +29,59%, ngưỡng 8%)',
            'Số liệu: player_huyetmach_vessel (sid, vessel_id, level)'),
        tq_searchable('tbl-mh', array('Mạch', 'Viết tắt', 'Chỉ số', 'Mỗi cấp', 'Nhóm', 'Vị trí ảnh', 'Mô tả'), $rowsV,
            '<b>Luyện mạch:</b> mỗi cấp +1 tốn ' . tq_n($rv['material_base']) . ' Huyết Thạch (cứ ' . tq_n($rv['material_per_lv'])
            . ' cấp tăng thêm 1) + ' . tq_n($rv['linh_thach_base']) . ' Linh Thạch (cộng ' . tq_n($rv['linh_thach_per_lv']) . '/cấp).')
        . tq_searchable('tbl-mht', array('Thức tỉnh', 'Trần mỗi mạch', 'Cần tổng cấp', 'Cần cảnh giới', 'Vật liệu', 'Linh Thạch', 'Boss', 'Mô tả'), $rowsT4, '<b>5 bậc thức tỉnh:</b>')
        . tq_searchable('tbl-mhm', array('Mốc', 'Tổng cấp 8 mạch', 'Nhân chỉ số'), $rowsM4, '<b>Mốc tổng cấp:</b>'));
} else {
    tq_sec('nuoi-duong', 'mach-huyet', '❤', 'Mạch Huyết (chưa cài đặt)', 0,
        'class/config/huyetmach_vessel_config.json chưa tồn tại — Task 1 chưa chạy.',
        array('Chưa deploy'), '');
}

$petAllC = tq_cfg('pet_config');
$rows = array();
foreach ((array)$petAllC as $e){
    $rows[] = array(tq_show($e['name']), tq_elem($e['element']), 'Tier ' . tq_n($e['quality_tier']), tq_n($e['base_hp']), tq_n($e['base_atk']), tq_n($e['base_def']), tq_n($e['base_crit']), tq_cut($e['desc'], 70));
}
$ytAll = tq_cfg('yeuthu_config');
$rows2 = array();
$buaCfg = tq_cfg('bua_config');
foreach ((array)(isset($ytAll['maps']) ? $ytAll['maps'] : array()) as $m){
    $buaName = tq_name_of($buaCfg, $m['bua_id'], 'name');
    $rows2[] = array(tq_show($m['name']), $m['level_range'][0] . ' - ' . $m['level_range'][1], 'Tier ' . tq_n($m['quality_tier']), $buaName, tq_n($m['mt_cost']) . ' MT', count((array)$m['monsters']) . ' loài');
}
tq_sec('nuoi-duong', 'sung-vat', '🐾', 'Sủng vật & bắt yêu thú', count($rows) + count($rows2),
    '30 sủng vật nuôi được; 5 bản đồ bắt yêu thú cần đúng loại bùa thu phục.',
    array('Bùa thu phục: mua bằng Linh Thạch theo tầng map', 'Chi phí bắt = Ma Thạch mỗi lần tung bùa', 'Chất lượng sủng random theo tier vùng'),
    tq_searchable('tbl-pet', array('Tên', 'Hành', 'Tier', 'Máu', 'Công', 'Thủ', 'Bạo', 'Mô tả'), $rows, '<b>Danh sách sủng vật:</b>')
    . tq_searchable('tbl-yt', array('Vùng bắt', 'Cấp yêu thú', 'Tier chất lượng', 'Bùa cần dùng', 'Chi phí/lượt', 'Số loài có thể bắt'), $rows2, '<b>Bản đồ thu phục:</b>'));

$jnAll = tq_cfg('jineng_config');
$rows = array();
foreach ((array)$jnAll as $k => $e){
    $rows[] = array(tq_show($e['jnname']), tq_n($e['jngj']), tq_n($e['jnfy']), tq_n($e['jnbj']), tq_n($e['jnxx']), tq_n($e['jndj']), tq_n($e['djcount']), tq_n($e['xiaohao']));
}
$tzAll = tq_cfg('taozhuang_config');
$rows2 = array();
foreach ((array)(isset($tzAll['tiers']) ? $tzAll['tiers'] : array()) as $e){
    $rows2[] = array(tq_dot($e['color']) . tq_show($e['name']), tq_cut($e['info'], 100));
}
$rows3 = array();
foreach ((array)(isset($tzAll['stage_rules']) ? $tzAll['stage_rules'] : array()) as $k => $e){
    $pe = array();
    foreach ((array)$e['extra_percent'] as $kk => $v) if ($v) $pe[] = strtoupper($kk) . ' +' . tq_n($v) . '%';
    $rows3[] = array(tq_n($k) . ' món', tq_show($e['title']), implode(', ', $pe));
}
tq_sec('nuoi-duong', 'ky-nang-set', '🎖', 'Kỹ năng chiến đấu & Thần Trang', count($rows) + count($rows2) + count($rows3),
    '6 kỹ năng chủ động (Tụ Linh Trảm...) và set Thần Trang cộng % theo số món.',
    array('Đủ món cùng set → kích hoạt giai đoạn (Sơ thành → Hoàn chỉnh)', 'Kỹ năng học bằng bí kíp kỹ năng'),
    tq_searchable('tbl-jn', array('Kỹ năng', 'Công', 'Thủ', 'Bạo', 'Tốc', 'Đẳng cấp', 'Cần đếm', 'Tiêu hao'), $rows, '<b>Kỹ năng:</b>')
    . tq_searchable('tbl-tz', array('Set', 'Mô tả'), $rows2, '<b>Thần Trang:</b>')
    . tq_searchable('tbl-tzs', array('Số món', 'Giai đoạn', 'Cộng thêm'), $rows3, '<b>Quy tắc cộng theo số món:</b>'));

/* ================= KINH TẾ & SẢN XUẤT ================= */
$rows = array();
foreach ((array)$djAll as $k => $e){
    $rows[] = array((int)$k, tq_show($e['djname']), tq_cut($e['djinfo'], 90), tq_n($e['djyxb']), $e['djjg'] ? tq_n($e['djjg']) : '');
}
tq_sec('kinhte', 'dao-cu', '🎒', 'Đạo cụ (vật phẩm)', count($rows),
    '75 đạo cụ: đá cường hóa, phù lục, bí kíp, mảnh thần khí, nguyên liệu chế tạo...',
    array('Cường Hóa Thạch → nâng cấp trang bị (game/trangbi.php)', 'Bùa thu phục → bắt sủng vật', 'Nguyên liệu → Luyện đan / Chế tạo', 'Bán lấy Linh Thạch ở túi đồ'),
    tq_searchable('tbl-dj', array('ID', 'Tên', 'Công dụng', 'Bán ra LT', 'Giá mua'), $rows, 'ID dùng để tham chiếu ở bảng Luyện đan / Chế tạo.'));

$rows = array();
foreach ((array)$ydAll as $e){
    $st = array();
    foreach (array('ydgj'=>'Công','ydfy'=>'Thủ','ydhp'=>'Máu','ydbj'=>'Bạo','ydxx'=>'Tốc') as $k => $lb)
        if (!empty($e[$k])) $st[] = $lb . ' +' . tq_n($e[$k]);
    $rows[] = array(tq_dot($e['ydys']) . tq_show($e['ydname']), implode(', ', $st), tq_n($e['ydjg']), tq_n($e['ydjgm']), tq_n($e['toxin_base']), '');
}
$ypRows = array();
foreach ((array)$ypAll as $e){
    $st = array();
    foreach (array('ypgj'=>'Công','ypfy'=>'Thủ','yphp'=>'Máu','ypbj'=>'Bạo','ypxx'=>'Tốc') as $k => $lb)
        if (!empty($e[$k])) $st[] = $lb . ' +' . tq_n($e[$k]);
    $ypRows[] = array(tq_show($e['ypname']), implode(', ', $st), tq_n($e['ypjg']));
}
tq_sec('kinhte', 'dan-pham', '💊', 'Yêu đan & Dược phẩm', count($rows) + count($ypRows),
    '16 yêu đan cộng chỉ số vĩnh viễn (có độc tính) + 8 dược phẩm hồi phục.',
    array('Sản xuất từ Luyện đan', 'Mua bằng Linh Thạch hoặc Ma Thạch ở Cửa hàng', 'Độc tính tích lũy → cần đan giải độc'),
    tq_searchable('tbl-yd', array('Tên đan', 'Cộng chỉ số', 'Giá LT', 'Giá MT', 'Độc tính'), $rows, '<b>Yêu đan:</b>')
    . tq_searchable('tbl-yp', array('Dược phẩm', 'Hiệu quả', 'Giá LT'), $ypRows, '<b>Dược phẩm:</b>'));

$ldAll = tq_cfg('luonden_config');
$rankNames = array();
foreach ((array)(isset($ldAll['alchemist_ranks']) ? $ldAll['alchemist_ranks'] : array()) as $k => $r) $rankNames[(int)$k] = tq_txt($r['name']);
$lookups = array('daoju' => 'tq_dj_name', 'yaodan' => 'tq_yd_name', 'yaopin' => 'tq_yp_name');
$rows = array();
foreach ((array)(isset($ldAll['recipes']) ? $ldAll['recipes'] : array()) as $e){
    $lk = isset($lookups[$e['type']]) ? $lookups[$e['type']] : null;
    $res = $lk ? call_user_func($lk, $e['result_id']) : ('#' . $e['result_id']);
    $rows[] = array(tq_show($e['name']), tq_h($e['type']), 'Phẩm ' . tq_n($e['grade']), $res . ' ×' . tq_n($e['result_amount']),
        tq_mat_names($e['materials'], 'tq_dj_name'), tq_n($e['base_success_rate']) . '%', 'Cấp ' . tq_n($e['level_required']),
        (isset($rankNames[$e['alch_rank_required']]) ? $rankNames[$e['alch_rank_required']] : 'Hạng ' . tq_n($e['alch_rank_required'])),
        tq_n($e['toxin_base']), tq_n($e['sell_price']));
}
$rows2 = array();
foreach ((array)(isset($ldAll['alchemist_ranks']) ? $ldAll['alchemist_ranks'] : array()) as $k => $r){
    $rows2[] = array((int)$k, tq_show($r['name']), tq_n($r['exp_required']), 'Phẩm đan tối đa: ' . tq_n($r['max_grade']));
}
$tuongRows = array();
foreach ((array)(isset($ldAll['dan_tuong_types']) ? $ldAll['dan_tuong_types'] : array()) as $k => $t){
    if ($k == 0) continue;
    $tuongRows[] = array(tq_show($t['name']), tq_cut($t['effect'], 80));
}
tq_sec('kinhte', 'luyen-dan', '🔥', 'Luyện đan', count($rows) + count($rows2) + count($tuongRows),
    '17 công thức đan + 9 cấp Đan Sư + đan tướng (long tượng) + bảng độc tính.',
    array('Nguyên liệu: đạo cụ (thảo dược) mua/đánh quái', 'Kết quả: yêu đan / dược phẩm / đạo cụ', 'Cấp Đan Sư lên bằng EXP luyện đan', 'Độc tính tích lũy, cần kiểm soát'),
    tq_searchable('tbl-ld', array('Công thức', 'Kết quả loại', 'Phẩm đan', 'Thành phẩm', 'Nguyên liệu', 'Tỉ lệ', 'Cấp người', 'Cấp Đan sư', 'Độc', 'Giá bán'), $rows, '<b>Công thức:</b>')
    . tq_searchable('tbl-ldr', array('Hạng', 'Tên cấp Đan Sư', 'EXP cần', 'Quyền hạn'), $rows2, '<b>Cấp Đan Sư:</b>')
    . tq_searchable('tbl-ldt', array('Đan Tướng', 'Hiệu quả'), $tuongRows, '<b>Đan tướng kèm theo:</b>'));

$ctAll = tq_cfg('chetao_config');
$rows = array();
foreach ((array)(isset($ctAll['recipes']) ? $ctAll['recipes'] : array()) as $e){
    $res = !empty($e['result_zbid']) ? tq_zb_name($e['result_zbid']) : ('Đạo cụ đặc biệt: ' . tq_show(tq_dj_name($e['result_daoju_id'])));
    $rows[] = array($res, tq_h($e['type']),
        tq_mat_names($e['materials'], 'tq_dj_name'), tq_n($e['base_success_rate']) . '%', 'Cấp ' . tq_n($e['level_required']));
}
tq_sec('kinhte', 'che-tao', '🔨', 'Chế tạo', count($rows),
    '8 công thức chế tạo trang bị/đạo cụ từ nguyên liệu.',
    array('Nguyên liệu: đạo cụ mua từ phương thị/quái', 'Sản phẩm: trang bị (tra theo ID trang bị)'),
    tq_searchable('tbl-ct', array('Sản phẩm', 'Loại', 'Nguyên liệu', 'Tỉ lệ', 'Cấp yêu cầu'), $rows, ''));

$buaRows = array();
foreach ((array)$buaCfg as $k => $e){
    $buaRows[] = array((int)$k, tq_show($e['name']), tq_n($e['lt_cost']), tq_mid($e['map']), tq_cut($e['desc'], 80));
}
tq_sec('kinhte', 'bua', '🪙', 'Bùa thu phục', count($buaRows),
    '10 loại bùa, mỗi vùng yêu thú cần đúng tầng bùa.',
    array('Mua bằng Linh Thạch', 'Dùng ở thu phục yêu thú (sauthu.php)'),
    tq_searchable('tbl-bua', array('ID', 'Tên bùa', 'Giá LT', 'Vùng dùng', 'Mô tả'), $buaRows, ''));

$desc = '<div class="mini-grid">'
  . '<div class="mini"><b>Cửa hàng</b><br>Yêu đan, đạo cụ, bí kíp; trả bằng Linh Thạch hoặc Ma Thạch. <code>game/shangdian.php</code></div>'
  . '<div class="mini"><b>Phương Thị (chợ người chơi)</b><br>Mua bán trao đổi giữa các nhân vật. <code>game/fangshi.php</code></div>'
  . '<div class="mini"><b>Hầm Mô (khai khoáng)</b><br>Đào đá ra Linh Thạch/đá quý, ăn theo công pháp Khai Mỏ. <code>game/hammo.php</code> + bảng <code>mine_player</code></div>'
  . '<div class="mini"><b>Đổi Quà</b><br>Giftcode/NPC Thần Bí. <code>game/duihuan.php</code> + bảng <code>duihuan</code></div>'
  . '<div class="mini"><b>Quy Đổi</b><br>Đổi tài nguyên chéo (linh thạch ↔ ma thạch...). <code>game/quydoi.php</code></div>'
  . '<div class="mini"><b>Nạp tiền</b><br>Webhook thanh toán cộng Linh Thạch. <code>pay/index.php</code></div>'
  . '<div class="mini"><b>Cron thể lực</b><br>Hồi thể lực theo thời gian. <code>cron/</code> + bảng <code>stamina_recovery</code></div>'
  . '</div>';
tq_sec('kinhte', 'dich-vu', '🏪', 'Cửa hàng & dịch vụ', 7,
    'Các luồng tiền tệ: mua bán, khai khoáng, đổi quà, nạp tiền.',
    array('Linh Thạch = tiền mềm (chủ lực)', 'Ma Thạch = tiền cấp cao (shop riêng)', 'EXP/Nguyên Bảo từ nhiệm vụ & đột phá'),
    $desc);

/* ================= CHIẾN ĐẤU & PHÓ BẢN ================= */
$gwAll = tq_cfg('guaiwu_config');
$rows = array();
foreach ((array)$gwAll as $k => $e){
    $rows[] = array((int)$k, tq_show($e['gname']), tq_n($e['glv']), tq_elem($e['element']),
        tq_n($e['ghp']), tq_n($e['ggj']), tq_n($e['gfy']), tq_n($e['gxx']), tq_show($e['gsex']),
        $e['gzb'] ? tq_show($e['gzb']) : '', tq_cut($e['ginfo'], 60));
}
tq_sec('chiendau', 'quai-vat', '👹', 'Quái vật', count($rows),
    '87 loại quái phân bố theo bản đồ; rơi trang bị/đạo cụ/linh thạch.',
    array('Đánh thường → EXP + vật phẩm', 'Cấp quái = cấp cảnh giới vùng', 'Mẫu rơi (cột "rơi") trỏ ô trang bị'),
    tq_searchable('tbl-gw', array('ID', 'Tên', 'Cấp', 'Hành', 'Máu', 'Công', 'Thủ', 'Tốc', 'Giới tính', 'Mẫu rơi (gzb)', 'Mô tả'), $rows, ''));

$rwAll = tq_cfg('renwu_config');
$rows = array();
foreach ((array)$rwAll as $k => $e){
    $lx = array('1' => 'Phó bản', '2' => 'Sự kiện/Nạp', '3' => 'Chuỗi');
    $rows[] = array((int)$e['rwid'], tq_show($e['rwname']),
        isset($lx[$e['rwlx']]) ? $lx[$e['rwlx']] : tq_h($e['rwlx']),
        tq_mid($e['rwqy']), tq_n($e['rwzl']), tq_n($e['rwcount']),
        tq_n($e['rwexp']), tq_n($e['rwyxb']), tq_n($e['rwyq']),
        tq_cut($e['rwinfo'], 80));
}
tq_sec('chiendau', 'pho-ban', '🏰', 'Phó bản / nhiệm vụ thưởng lớn', count($rows),
    '44 phó bản & nhiệm vụ sự kiện: EXP, Linh Thạch, trang bị thưởng.',
    array('Vượt tại game/phoban.php (chuỗi) hoặc task.php', 'Điều kiện vào: cấp / trang bị (rwzl)', 'Thưởng: EXP + Linh Thạch + kỹ năng/đan'),
    tq_searchable('tbl-rw', array('ID', 'Tên', 'Loại', 'Bản đồ', 'Chỉ số rwzl', 'Lượt', 'EXP', 'Linh Thạch', 'Thưởng MT', 'Mô tả'), $rows, 'rwzl = ngưỡng sức mạnh/điều kiện vào phó bản.'));

$lbCfg = tq_cfg('linhcan_boss_config');
$bossDesc = '<div class="mini-grid">';
if (is_array($lbCfg) && isset($lbCfg['boss_config'])){
    $b = $lbCfg['boss_config'];
    $bs = (array)$b['base_stats']; $ls = (array)$b['level_scaling']; $dr = (array)$b['drops'];
    $bossDesc .= '<div class="mini"><b>Boss Linh Căn</b><br>Mốc 120 phút xuất hiện. Máu ' . tq_n($bs['hp']) . ' + ' . tq_n($ls['hp_per_level']) . '/cấp; Công ' . tq_n($bs['atk']) . ' + ' . tq_n($ls['atk_per_level']) . '/cấp; Thủ ' . tq_n($bs['def']) . ' + ' . tq_n($ls['def_per_level']) . '/cấp. Drop: ' . tq_n($dr['linh_thach_min']) . '-' . tq_n($dr['linh_thach_max']) . ' Linh Thạch + ' . tq_n($dr['da_ngu_hanh_count']) . ' Đá Ngũ Hành. <code>game/linhcan_boss.php</code></div>';
}
$bossDesc .= '<div class="mini"><b>Boss Tông Môn</b><br>Boss riêng của từng tông môn, cộng damage thành viên, thưởng theo đóng góp. <code>game/club_boss.php</code> + bảng <code>club_boss</code></div>'
  . '<div class="mini"><b>Thập Yêu Tháp</b><br>Leo tháp 10 tầng yêu thú, thưởng theo tầng. <code>game/toayeuthap.php</code> + bảng <code>player_toayeuthap</code></div>'
  . '<div class="mini"><b>PvP / Arena</b><br>Khiêu chiến người chơi, bảng xếp hạng arena + thưởng ngày. <code>game/pvp.php</code> + bảng <code>arena_player</code>, <code>arena_daily_reward</code></div>'
  . '<div class="mini"><b>Diễn Võ Đài</b><br>Đài thi đấu sự kiện, cron phát thưởng. <code>game/vandinhdai.php</code> + <code>cron/</code></div>'
  . '<div class="mini"><b>Sửa Tồn (boss world)</b><br>Boss thế giới mốc thời gian. <code>game/boss.php</code>, <code>game/bossinfo.php</code> + bảng <code>boss</code></div>'
  . '</div>';
tq_sec('chiendau', 'boss', '🐲', 'Boss & tháp', 6,
    'Các tuyến chiến đấu BOSS: linh căn boss, tông môn boss, Thập Yêu Tháp, arena, diễn võ đài.',
    array('Boss → Đá Ngũ Hành (nâng linh căn)', 'Tháp/arena → trang bị + danh vọng', 'Tất cả tính thưởng qua cron/db'),
    $bossDesc);

/* ================= NHIỆM VỤ & THÀNH TỰU ================= */
$mqAll = tq_cfg('nhiemvu_main_config');
$rows = array();
foreach ((array)(isset($mqAll['chapters']) ? $mqAll['chapters'] : array()) as $c){
    foreach ((array)$c['quests'] as $q){
        $rows[] = array(tq_show($c['name']), tq_show($q['id']), tq_show($q['name']), tq_h($q['type']),
            (isset($q['target']) ? tq_n($q['target']) : (isset($q['target_monster_min']) ? $q['target_monster_min'] . '-' . $q['target_monster_max'] : '')),
            tq_n($q['exp']), tq_n($q['lt']), tq_n($q['mt']));
    }
}
$range = array();
foreach ((array)(isset($mqAll['chapters']) ? $mqAll['chapters'] : array()) as $c) $range[] = tq_txt($c['name']) . ' (' . implode('-', (array)$c['level_range']) . ' cấp, ~' . tq_n($c['est_days']) . ' ngày)';
tq_sec('nhiemvu', 'nv-chinh', '🧭', 'Nhiệm vụ chính', count($rows),
    'Cốt truyện chia chương: ' . implode(' → ', $range) . '.',
    array('Dẫn dắt qua NPC (taskguide.php)', 'Thưởng EXP + Linh Thạch + mở tính năng'),
    tq_searchable('tbl-mq', array('Chương', 'ID', 'Nhiệm vụ', 'Loại', 'Mục tiêu', 'EXP', 'LT', 'MT'), $rows, ''));

function tq_quest_rows($cfg, $key, $extraDay = false){
    $rows = array();
    $list = isset($cfg[$key]) ? $cfg[$key] : $cfg;
    foreach ((array)$list as $q){
        if (!is_array($q) || !isset($q['name'])) continue;
        $rows[] = array(tq_show($q['id']), tq_show($q['name']), tq_h($q['type']),
            $extraDay && isset($q['day']) ? tq_n($q['day']) : '',
            tq_n($q['target']), tq_n($q['exp']), tq_n($q['lt']), tq_n($q['mt']));
    }
    return $rows;
}
$dq = tq_cfg('daily_quests'); $wq = tq_cfg('weekly_quests'); $moq = tq_cfg('monthly_quests'); $hq = tq_cfg('hidden_quests');
$r1 = tq_quest_rows($dq, 'daily_quest_pool'); $r2 = tq_quest_rows($wq, 'weekly_quests', true); $r3 = tq_quest_rows($moq, 'monthly_quests'); $r4 = tq_quest_rows($hq, 'hidden_quests');
$bonus = isset($dq['daily_bonus']) ? tq_show(json_encode($dq['daily_bonus'])) : '';
tq_sec('nhiemvu', 'nv-dwh', '🗓', 'Nhiệm vụ Ngày / Tuần / Tháng / Ẩn', count($r1) + count($r2) + count($r3) + count($r4),
    'Vòng nhiệm vụ lặp lại; phần thưởng phụ (hoàn bộ daily): ' . $bonus . '.',
    array('Nhận tại game/nhiemvu.php', 'Theo dõi tiến trình tự động qua cron/db'),
    tq_searchable('tbl-dq', array('ID', 'Tên', 'Loại', 'Mục tiêu', 'Số lượng', 'EXP', 'LT', 'MT'), $r1, '<b>Hàng ngày (' . count($r1) . ' đề xuất random ' . tq_n($dq['daily_count']) . '):</b>')
    . tq_searchable('tbl-wq', array('ID', 'Tên', 'Loại', 'Thứ', 'Số lượng', 'EXP', 'LT', 'MT'), $r2, '<b>Hàng tuần:</b>')
    . tq_searchable('tbl-moq', array('ID', 'Tên', 'Loại', '', 'Số lượng', 'EXP', 'LT', 'MT'), $r3, '<b>Hàng tháng:</b>')
    . tq_searchable('tbl-hq', array('ID', 'Tên', 'Loại', '', 'Số lượng', 'EXP', 'LT', 'MT'), $r4, '<b>Nhiệm vụ ẩn:</b>'));

$cqAll = tq_cfg('club_quest_config');
$rows = array();
foreach ((array)(isset($cqAll['types']) ? $cqAll['types'] : array()) as $k => $t){
    foreach ((array)$t['tiers'] as $tr){
        $rows[] = array($t['icon'] . ' ' . tq_show($t['name']), tq_show($tr['name']), tq_n($tr['target']), tq_n($tr['reward']), tq_h($t['track_action']));
    }
}
$achAll = tq_cfg('achievements');
$rows2 = array();
foreach ((array)(isset($achAll['achievements']) ? $achAll['achievements'] : array()) as $k => $a){
    foreach ((array)$a['tiers'] as $tr){
        $rows2[] = array($a['icon'] . ' ' . tq_show($a['name']), tq_show($tr['name']), tq_n($tr['target']), tq_n($tr['exp']), tq_n($tr['lt']), tq_n($tr['mt']));
    }
}
$lqAll = tq_cfg('linhcan_quest_config');
$rows3 = array();
foreach ((array)(isset($lqAll['weekly_quests']) ? $lqAll['weekly_quests'] : array()) as $q){
    $rows3[] = array(tq_show($q['icon']) . ' ' . tq_show($q['name']), tq_cut($q['description'], 60), tq_h($q['type']), tq_n($q['target']), tq_n($q['reward_linhthach']), tq_n($q['reward_da_ngu_hanh']));
}
tq_sec('nhiemvu', 'nv-khac', '🎯', 'Tông môn / Thành tựu / Linh căn tuần', count($rows) + count($rows2) + count($rows3),
    'Nhiệm vụ tông môn theo 3 độ khó, thành tựu 10 mảng nhiều bậc, nhiệm vụ tuần riêng cho linh căn.',
    array('Tông môn → quyên Linh Thạch, đánh boss', 'Thành tựu → EXP + LT vĩnh viễn', 'Linh căn tuần → Đá Ngũ Hành'),
    tq_searchable('tbl-cq', array('Nhiệm vụ tông môn', 'Độ khó', 'Mục tiêu', 'Thưởng', 'Theo dõi'), $rows, '<b>Tông môn (' . tq_n($cqAll['daily_min']) . '-' . tq_n($cqAll['daily_max']) . ' cái/ngày, thưởng hoàn ' . tq_n($cqAll['completion_bonus_exp']) . ' EXP):</b>')
    . tq_searchable('tbl-ach', array('Thành tựu', 'Bậc', 'Mục tiêu', 'EXP', 'LT', 'MT'), $rows2, '<b>Thành tựu:</b>')
    . tq_searchable('tbl-lcq', array('Nhiệm vụ', 'Mô tả', 'Loại', 'Mục tiêu', 'Linh thạch', 'Đá ngũ hành'), $rows3, '<b>Tuần linh căn:</b>'));

/* ================= THẾ GIỚI & XÃ HỘI ================= */
$npcAll = tq_cfg('npc_config');
$rows = array();
foreach ((array)$npcAll as $k => $e){
    $rows[] = array((int)$k, tq_show($e['nname']), tq_show($e['nsex']), tq_cut($e['ninfo'], 100), tq_show($e['muban']), $e['rwqy'] ? tq_mid($e['rwqy']) : '');
}
tq_sec('thegioi', 'npc', '🧑‍🦳', 'NPC', count($rows),
    '26 nhân vật dẫn chuyện: trưởng làng, sư phụ, Thần Bí Nhân...',
    array('Giao diện qua npc/muban/*.php', 'Gắn với nhiệm vụ chính (rwqy = bản đồ NPC đứng)'),
    tq_searchable('tbl-npc', array('ID', 'Tên', 'Giới tính', 'Mô tả', 'Mẫu hội thoại', 'Bản đồ'), $rows, ''));

if ($midName){
    $rows = array();
    foreach ($midName as $id => $nm){
        $rows[] = array($id, tq_esc($nm), isset($qyOfMid[$id]) ? tq_esc($qyOfMid[$id]) : '');
    }
    tq_sec('thegioi', 'ban-do', '🗺', 'Bản đồ game', count($rows),
        'Toàn bộ địa danh trong DB (bảng mid + qy) - thứ tự chính là đường đi của nhân vật.',
        array('Di chuyển bằng allmap.php / đi bộ 4 hướng', 'Mỗi map gắn quái + NPC'),
        tq_searchable('tbl-mid', array('ID map', 'Tên bản đồ', 'Khu vực'), $rows, ''));
} else {
    tq_sec('thegioi', 'ban-do', '🗺', 'Bản đồ', 0,
        'Không kết nối được DB nên không đọc được danh sách bản đồ (bảng mid). Mở trang khi MySQL phpStudy đang chạy để thấy đủ.',
        array(), '<p class="note">Bật MySQL trong phpStudy rồi tải lại trang.</p>');
}

$xaDesc = '<div class="mini-grid">'
  . '<div class="mini"><b>Tông Môn (phái)</b><br>Người chơi tự lập phái: cấp phái, thành viên, kho phái (treasury), boss & nhiệm vụ riêng. <code>game/club.php</code>, <code>game/clublist.php</code>, <code>game/treasury_page.php</code></div>'
  . '<div class="mini"><b>Chat & Kết bạn</b><br>Xử hữu, chat riêng/liên server. <code>game/im.php</code>, <code>game/liaotian.php</code> + bảng <code>im</code>, <code>ggliaotian</code></div>'
  . '<div class="mini"><b>Xếp hạng</b><br>Bảng phong loan theo lực chiến/cảnh giới. <code>game/paihang.php</code></div>'
  . '<div class="mini"><b>Tạo nhân vật</b><br>Chọn tên - giới tính - tiền trình (Chiến Sĩ / Thiếu Hiệp / Ẩn Sĩ). <code>game/cj.php</code></div>'
  . '<div class="mini"><b>Trạng thái & chỉ số</b><br>Xem nhân vật: <code>game/zhuangtai.php</code>, <code>game/ginfo.php</code></div>'
  . '<div class="mini"><b>GM Panel</b><br>Nick GM điều hành: <a href="./gm/gm.php">gm/gm.php</a> (gửi vật phẩm, tra người chơi, lệnh).</div>'
  . '<div class="mini"><b>Thiên Cơ Các / Khí Tượng</b><br>Sự kiện thời tiết phần thưởng. <code>game/thiencoccac.php</code>, <code>game/phoban.php</code></div>'
  . '<div class="mini"><b>Quyển Nguyên Đại Trận</b><br>Bản đồ kho báu sự kiện. <code>game/qydt.php</code></div>'
  . '</div>';
tq_sec('thegioi', 'xa-hoi', '🏘', 'Xã hội & vận hành', 8,
    'Tông môn, kết bạn, xếp hạng, diễn đàn sự kiện và khu GM.',
    array('Mọi tính năng đi qua game.php (cmd + sid, chống spam 1s)', 'Session lưu bảng game1'),
    $xaDesc);

/* ================= HIỂN THỊ TRANG ================= */
$tqOnly = isset($_GET['nhom']) ? (string)$_GET['nhom'] : '';
if ($tqOnly !== '' && !isset($GLOBALS['TQ_GROUPS'][$tqOnly])) $tqOnly = '';
$tqTotSys = 0; $tqTotRows = 0;
foreach ($GLOBALS['TQ_SECS'] as $g => $list) $tqTotSys += count($list);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tổng quan hệ thống - Tam Tiên Kỳ</title>
<style>
:root{--bg:#171009;--panel:#221a10;--line:#41301c;--ink:#f3e5d0;--sub:#c9b18a;--acc:#f59e0b;--acc2:#ea580c;--red:#dc2626;}
*{box-sizing:border-box}
body{font-family:'Segoe UI',system-ui,Roboto,Arial,sans-serif;background:var(--bg);color:var(--ink);font-size:17px;margin:0;line-height:1.55}
.wrap{max-width:1240px;margin:0 auto;padding:0 14px 90px}
header.top{background:linear-gradient(180deg,#2b1d0e,#171009);border-bottom:2px solid var(--acc2);padding:26px 14px 16px;text-align:center}
header.top h1{margin:0 0 6px;color:var(--acc);font-size:30px;letter-spacing:1px}
header.top p{margin:0;color:var(--sub);font-size:15.5px}
nav.sticky{position:sticky;top:0;z-index:9;background:#1d150cf2;backdrop-filter:blur(4px);border-bottom:1px solid var(--line);display:flex;gap:8px;overflow-x:auto;padding:10px 12px;white-space:nowrap}
nav.sticky a{color:var(--ink);text-decoration:none;background:#2e2110;border:1px solid var(--line);padding:8px 14px;border-radius:20px;font-size:15.5px}
nav.sticky a:hover,nav.sticky a:active{background:var(--acc2);color:#fff}
h2.grp{color:var(--acc2);font-size:24px;margin:34px 0 12px;border-bottom:1px solid var(--line);padding-bottom:6px}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px}
.card{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:15px 16px}
.card h3{margin:0 0 6px;font-size:19px}
.card h3 a{color:var(--ink);text-decoration:none}
.card h3 a:hover{color:var(--acc)}
.badge{display:inline-block;background:var(--acc2);color:#fff;border-radius:12px;padding:1px 10px;font-size:14px;margin-left:6px;vertical-align:2px}
.card p{margin:0 0 8px;color:var(--sub);font-size:15px}
.rel{display:block;background:#140e07;border-left:3px solid var(--acc);border-radius:6px;padding:5px 9px;margin:4px 0;font-size:14.5px;color:#ffe0b8}
section.sys{margin-top:26px;background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:18px 18px 14px;scroll-margin-top:64px}
section.sys h2{margin:0 0 4px;font-size:23px;color:var(--acc)}
section.sys .sdesc{margin:0 0 10px;color:var(--sub)}
.tbl-tools{margin:12px 0 6px}
input.q{width:100%;padding:11px 14px;font-size:16px;background:#120d08;border:1px solid var(--line);color:var(--ink);border-radius:10px}
input.q:focus{outline:2px solid var(--acc)}
.tbl-wrap{overflow-x:auto;border:1px solid var(--line);border-radius:10px;margin:6px 0 14px;max-height:75vh;overflow-y:auto}
table.tbl{width:100%;border-collapse:collapse;font-size:15.5px;min-width:640px}
.tbl thead th{background:#33240f;color:var(--acc);text-align:left;padding:9px 10px;cursor:pointer;position:sticky;top:0;font-size:15px;user-select:none}
.tbl thead th:active{background:var(--acc2);color:#fff}
.tbl td{padding:8px 10px;border-top:1px solid #2e2213;vertical-align:top}
.tbl tbody tr:nth-child(even){background:#1b140b}
.dot{width:11px;height:11px;border-radius:50%;display:inline-block;margin-right:7px;border:1px solid #00000088;vertical-align:-1px}
.rgroup{margin:12px 0}
.rgname{color:var(--acc);font-weight:700;margin-bottom:6px}
.chips{display:flex;flex-wrap:wrap;gap:7px}
.rchip{background:#120d08;border:1px solid var(--line);padding:7px 12px;border-radius:10px;font-weight:600;font-size:16px}
.rchip i{font-style:normal;opacity:.45;font-size:12px;margin-left:6px}
/* Đạo Tổ → Chí Tôn → Vô Ngã: cầu vồng nhanh dần, viền + quang mang mạnh dần */
.rgname.fx-to{background:linear-gradient(90deg,#ff2d2d,#ff9f1a,#ffe81a,#3bff6e,#1ad9ff,#7a3bff,#ff1ad9,#ff2d2d);background-size:400% 100%;-webkit-background-clip:text;background-clip:text;color:transparent!important;animation:tqrain4 2.8s linear infinite}
.rchip.fx-to-1{background:linear-gradient(90deg,#ff2d2d,#ff9f1a,#ffe81a,#3bff6e,#1ad9ff,#7a3bff,#ff1ad9,#ff2d2d);background-size:400% 100%;-webkit-background-clip:text;background-clip:text;color:transparent!important;animation:tqrain4 4s linear infinite;border-color:#ffffff44}
.rchip.fx-to-2{background:linear-gradient(90deg,#ff2d2d,#ff9f1a,#ffe81a,#3bff6e,#1ad9ff,#7a3bff,#ff1ad9,#ff2d2d);background-size:400% 100%;-webkit-background-clip:text;background-clip:text;color:transparent!important;animation:tqrain4 2.8s linear infinite;border-color:#ffffff66;box-shadow:0 0 14px #a855f755,0 0 6px #22d3ee44}
.rchip.fx-to-3{background:linear-gradient(90deg,#ff2d2d,#ff9f1a,#ffe81a,#3bff6e,#1ad9ff,#7a3bff,#ff1ad9,#ff2d2d);background-size:400% 100%;-webkit-background-clip:text;background-clip:text;color:transparent!important;animation:tqrain4 1.8s linear infinite,fxto3 2.4s ease-in-out infinite;border-color:#ffffffaa}
@keyframes fxto3{0%,100%{box-shadow:0 0 10px #a855f788,0 0 4px #22d3ee66}50%{box-shadow:0 0 24px #a855f7,0 0 10px #22d3ee}}
.fx-to i,.fx-to-1 i,.fx-to-2 i,.fx-to-3 i{opacity:.6}
/* Hiệu ứng phân tầng: MỖI CHIP giữ màu riêng của cảnh giới trong rangesjj.php,
   chỉ thêm quang mang (glow) mạnh dần theo bậc. Gradient chỉ dành cho tiêu đề nhóm.
   Lưu ý: background: shorthand phải đi TRƯỚC background-clip:text cùng rule. */
.rgname.fx-mist{background:linear-gradient(90deg,#9fd8ef,#ffe9b3);-webkit-background-clip:text;background-clip:text;color:transparent!important}
.rgname.fx-ember{background:linear-gradient(90deg,#ffab91,#ce93d8);-webkit-background-clip:text;background-clip:text;color:transparent!important}
.rgname.fx-sky{background:linear-gradient(90deg,#0d47a1,#64b5f6,#e0f7ff,#00e5ff,#0d47a1);background-size:300% 100%;animation:tqrain 8s linear infinite;-webkit-background-clip:text;background-clip:text;color:transparent!important}
.rgname.fx-jade{background:linear-gradient(90deg,#0e7a53,#69f0ae,#e0fff0,#00ffa8,#0e7a53);background-size:300% 100%;animation:tqrain 7s linear infinite;-webkit-background-clip:text;background-clip:text;color:transparent!important}
.rgname.fx-gold{background:linear-gradient(90deg,#7a4a00,#ffd700,#fff3b0,#ffb300,#7a4a00);background-size:300% 100%;animation:tqrain 5.5s linear infinite;-webkit-background-clip:text;background-clip:text;color:transparent!important}
.rgname.fx-cosmos{background:linear-gradient(90deg,#4a148c,#e1bee7,#ffffff,#7c4dff,#4a148c);background-size:300% 100%;animation:tqrain 4.5s linear infinite;-webkit-background-clip:text;background-clip:text;color:transparent!important}
.rgname.fx-blood{background:linear-gradient(90deg,#7f0000,#ff5252,#ffd700,#ff5252,#7f0000);background-size:300% 100%;animation:tqrain 3.6s linear infinite;-webkit-background-clip:text;background-clip:text;color:transparent!important}
.rgname.fx-thunder{background:linear-gradient(90deg,#7f0000,#ff1744,#ff8a80,#ffffff,#ff1744,#7f0000);background-size:300% 100%;animation:tqrain 5s linear infinite;-webkit-background-clip:text;background-clip:text;color:transparent!important}
.rchip.fx-thunder{animation:fxglow2 5s ease-in-out infinite}
.rgname.fx-pulse{animation:fxglow 5s ease-in-out infinite}
.rchip.fx-mist{text-shadow:0 0 6px currentColor}
.rchip.fx-ember{text-shadow:0 0 9px currentColor}
.rchip.fx-sky{animation:fxglow 8s ease-in-out infinite}
.rchip.fx-pulse{animation:fxglow 5s ease-in-out infinite}
.rchip.fx-jade{animation:fxglow 7s ease-in-out infinite}
.rchip.fx-gold{animation:fxglow2 5.5s ease-in-out infinite}
.rchip.fx-cosmos{animation:fxglow2 4.5s ease-in-out infinite}
.rchip.fx-blood{animation:fxglow3 3.6s ease-in-out infinite}
.rgname{display:inline-block}
@keyframes tqrain{to{background-position:-300% 0}}
@keyframes tqrain4{to{background-position:-400% 0}}
@keyframes fxglow{0%,100%{text-shadow:0 0 3px currentColor}50%{text-shadow:0 0 12px currentColor}}
@keyframes fxglow2{0%,100%{text-shadow:0 0 4px currentColor}50%{text-shadow:0 0 16px currentColor;box-shadow:0 0 12px #00000000,inset 0 0 8px currentColor}}
@keyframes fxglow3{0%,100%{text-shadow:0 0 5px currentColor}50%{text-shadow:0 0 20px currentColor,0 0 8px currentColor;box-shadow:0 0 16px currentColor}}
@media (prefers-reduced-motion:reduce){.rgname.fx-to,.rchip.fx-to-1,.rchip.fx-to-2,.rchip.fx-to-3,.rgname.fx-sky,.rgname.fx-jade,.rgname.fx-gold,.rgname.fx-cosmos,.rgname.fx-blood,.rgname.fx-thunder,.rgname.fx-pulse,.rchip.fx-sky,.rchip.fx-pulse,.rchip.fx-jade,.rchip.fx-thunder,.rchip.fx-gold,.rchip.fx-cosmos,.rchip.fx-blood{animation:none}}
.mini-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:10px;margin-top:10px}
.mini{background:#1d150c;border:1px solid var(--line);border-radius:12px;padding:13px 14px;font-size:15.5px;color:#ecdcc0}
.mini b{color:var(--acc)}
.note{color:var(--sub);font-size:15px}
code{background:#00000066;padding:2px 6px;border-radius:6px;font-size:14px;color:#ffd7a8}
a{color:var(--acc)}
.top-btn{position:fixed;right:16px;bottom:16px;background:var(--acc2);color:#fff;border:none;border-radius:50%;width:52px;height:52px;font-size:22px;z-index:9;cursor:pointer}
footer{margin-top:40px;color:var(--sub);font-size:14.5px;text-align:center}
@media(max-width:700px){body{font-size:18px}.tbl-wrap{max-height:none}table.tbl{min-width:820px}header.top h1{font-size:24px}}
</style>
</head>
<body>
<header class="top">
<h1>⛰ TAM TIÊN KỲ - Bản đồ hệ thống</h1>
<p><?php echo $tqTotSys; ?> hệ thống con · <?php echo count($tqRealms); ?> cảnh giới · đọc trực tiếp config + DB lúc <?php echo TQ_TIME; ?></p>
<p style="font-size:14px">Nguồn: <code>class/config/*.json</code>, <code>class/rangesjj.php</code>, DB <code>game</code><?php echo $tqDb ? '' : ' (DB đang TẮT - bản đồ/NPC theo map hiển thị ID)'; ?></p>
</header>
<nav class="sticky">
<a href="#sodo">Sơ đồ</a>
<?php foreach ($GLOBALS['TQ_GROUPS'] as $gid => $g): ?><a href="#grp-<?php echo $gid; ?>"><?php echo $g[1] . ' ' . $g[0]; ?></a><?php endforeach; ?>
</nav>
<div class="wrap">

<h2 class="grp" id="sodo">🧩 Sơ đồ tổng quan - bấm vào khối để xem chi tiết</h2>
<?php foreach ($GLOBALS['TQ_GROUPS'] as $gid => $g): if ($tqOnly !== '' && $gid !== $tqOnly) continue; $secs = $GLOBALS['TQ_SECS'][$gid]; if (!$secs) continue; ?>
<h2 class="grp" id="grp-<?php echo $gid; ?>"><?php echo $g[1] . ' ' . $g[0]; ?> <span class="badge"><?php echo count($secs); ?></span></h2>
<div class="cards">
<?php foreach ($secs as $s): ?>
<div class="card">
<h3><a href="#sec-<?php echo $s['id']; ?>"><?php echo $s['icon']; ?> <?php echo tq_esc($s['title']); ?></a></h3>
<span class="badge"><?php echo tq_n($s['count']); ?></span>
<p><?php echo tq_esc($s['desc']); ?></p>
<?php foreach ((array)$s['rels'] as $r): ?><span class="rel">↳ <?php echo tq_esc($r); ?></span><?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php foreach ($GLOBALS['TQ_SECS'] as $gid => $secs): if ($tqOnly !== '' && $gid !== $tqOnly) continue; foreach ($secs as $s): ?>
<section class="sys" id="sec-<?php echo $s['id']; ?>">
<h2><?php echo $s['icon']; ?> <?php echo tq_esc($s['title']); ?> <span class="badge"><?php echo tq_n($s['count']); ?></span></h2>
<p class="sdesc"><?php echo tq_esc($s['desc']); ?></p>
<?php foreach ((array)$s['rels'] as $r): ?><span class="rel">↳ <?php echo tq_esc($r); ?></span><?php endforeach; ?>
<?php echo $s['html']; ?>
</section>
<?php endforeach; endforeach; ?>

<footer>Sinh lúc <?php echo TQ_TIME; ?> · chỉ đọc, không sửa dữ liệu game · <a href="#sodo">về đầu trang</a></footer>
</div>
<button class="top-btn" onclick="scrollTo({top:0,behavior:'smooth'})" aria-label="Lên đầu trang">↑</button>
<script>
(function(){
 document.querySelectorAll('input.q').forEach(function(inp){
  inp.addEventListener('input',function(){
   var sec=inp.closest('section'),q=inp.value.trim().toLowerCase();
   sec.querySelectorAll('table.tbl tbody tr').forEach(function(tr){
    tr.style.display=(!q||tr.textContent.toLowerCase().indexOf(q)>-1)?'':'none';
   });
  });
 });
 document.querySelectorAll('table.tbl thead th').forEach(function(th){
  th.addEventListener('click',function(){
   var tb=th.closest('table').tBodies[0];if(!tb)return;
   var i=Array.prototype.indexOf.call(th.parentNode.children,th);
   var dir=th.dataset.d==='asc'?-1:1;
   document.querySelectorAll('table.tbl thead th').forEach(function(o){o.querySelector('.arr').textContent='';});
   th.querySelector('.arr').textContent=dir===1?' ▲':' ▼';
   var rows=Array.prototype.slice.call(tb.rows);
   rows.sort(function(a,b){
    var x=a.cells[i]?a.cells[i].textContent.trim():'',y=b.cells[i]?b.cells[i].textContent.trim():'';
    var nx=parseFloat(x.replace(/[^\d.-]/g,'')),ny=parseFloat(y.replace(/[^\d.-]/g,''));
    var cmp=(!isNaN(nx)&&!isNaN(ny)&&x!==''&&y!=='')?nx-ny:x.localeCompare(y,'vi');
    return cmp*dir;
   });
   rows.forEach(function(r){tb.appendChild(r);});
  });
 });
})();
</script>
</body>
</html>
<?php
?>






