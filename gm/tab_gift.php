<?php
if (!defined('GM_PAGE')) exit;   // chỉ chạy qua gm/gm.php
// Tab Phát quà: 1 / nhiều / tất cả nhân vật — bước 1 xem trước (danh sách + phần thưởng), bước 2 xác nhận mới phát.
// Phát bằng đúng hàm game như giftcode (adddj / addyaopin / addzb / changeyxb / changeczb), ghi nhật ký từng lần phát.
$act = gm_req('act');
$errors = array();
$to = gm_req('to');
$all = gm_req('all') === '1';
$rw = array('lt' => max(0, intval(gm_req('lt'))), 'mt' => max(0, intval(gm_req('mt'))), 'exp' => max(0, intval(gm_req('exp'))),
    'dj' => array(), 'yp' => array(), 'zb' => array());
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rw['dj'] = gm_parse_rows('dj', 'dj', true, $errors);
    $rw['yp'] = gm_parse_rows('yp', 'yp', true, $errors);
    $rw['zb'] = gm_parse_rows('zb', 'zb', true, $errors);
}
$note = gm_req('note');

// Danh sách nhân vật đích
$targets = array(); $missing = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($all) {
        $targets = $db->query("SELECT sid, uid, uname, ulv FROM game1 ORDER BY uid")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        foreach (preg_split('/[\r\n,;]+/', $to) as $k) {
            $k = trim($k);
            if ($k === '') continue;
            $p = gm_find_player($db, $k);
            if ($p) $targets[$p['sid']] = $p; else $missing[] = $k;
        }
        $targets = array_values($targets);
    }
    if (!$targets) $errors[] = 'Chưa có nhân vật nào hợp lệ.';
    if (gm_reward_text($rw) === '(trống)') $errors[] = 'Chưa chọn phần thưởng.';
}

if ($act === 'send' && !$errors) {
    foreach ($targets as $t) gm_grant($db, $t['sid'], $rw);
    $names = array(); foreach ($targets as $t) $names[] = $t['uname'];
    gm_log($db, 'gift', count($targets) . ' nhân vật' . ($all ? ' (tất cả)' : ''), array('to' => $names, 'reward' => $rw, 'note' => $note));
    $flash[] = array('ok', 'Đã phát cho <b>' . count($targets) . '</b> nhân vật: ' . gm_reward_text($rw));
    $act = ''; $to = ''; $all = false; $note = '';
    $rw = array('lt' => 0, 'mt' => 0, 'exp' => 0, 'dj' => array(), 'yp' => array(), 'zb' => array());
}

if ($act === 'preview' && !$errors) {
    echo '<form class="card" method="post" data-confirm="Phát quà cho ' . count($targets) . ' nhân vật?">' . gm_csrf_field()
        . '<input type="hidden" name="act" value="send"><h2>Xác nhận phát quà</h2>';
    // giữ nguyên dữ liệu form cho bước gửi
    echo '<input type="hidden" name="to" value="' . gm_h($to) . '"><input type="hidden" name="all" value="' . ($all ? '1' : '') . '"><input type="hidden" name="note" value="' . gm_h($note) . '">';
    foreach (array('lt', 'mt', 'exp') as $k) echo '<input type="hidden" name="' . $k . '" value="' . intval($rw[$k]) . '">';
    foreach (array('dj', 'yp', 'zb') as $k) foreach ($rw[$k] as $r) echo '<input type="hidden" name="' . $k . '_id[]" value="' . intval($r[0]) . '"><input type="hidden" name="' . $k . '_n[]" value="' . intval($r[1]) . '">';
    echo '<p><b>Phần thưởng mỗi nhân vật:</b> ' . gm_reward_text($rw) . '</p>';
    if ($missing) echo '<div class="flash bad">Không tìm thấy (bỏ qua): ' . gm_h(implode(', ', $missing)) . '</div>';
    echo '<p><b>' . count($targets) . ' nhân vật:</b> ';
    $show = array_slice($targets, 0, 200);
    foreach ($show as $t) echo '<span class="tag">' . gm_h($t['uname']) . ' · cấp ' . intval($t['ulv']) . '</span>';
    if (count($targets) > 200) echo ' … và ' . (count($targets) - 200) . ' nhân vật khác';
    echo '</p><div class="flex"><button class="btn green">✔ Phát ngay</button><a class="btn ghost" href="' . gm_url(array('tab' => 'gift')) . '">Huỷ</a></div></form>';
    return;
}
foreach ($errors as $e) if ($act !== '') echo '<div class="flash bad">' . gm_h($e) . '</div>';

echo '<form class="card" method="post">' . gm_csrf_field() . '<input type="hidden" name="act" value="preview"><h2>Phát quà cho người chơi</h2>'
    . '<div class="grid" style="grid-template-columns:2fr 1fr"><label>Nhân vật nhận (tên, uid hoặc sid — mỗi dòng một người, hoặc cách nhau dấu phẩy)<textarea name="to">' . gm_h($to) . '</textarea></label>'
    . '<div><label class="flex" style="flex-direction:row;align-items:center;color:var(--txt)"><input type="checkbox" name="all" value="1"' . ($all ? ' checked' : '') . '> Phát cho <b>&nbsp;TẤT CẢ&nbsp;</b> nhân vật</label>'
    . '<label style="margin-top:8px">Ghi chú (lý do, lưu vào nhật ký)<input name="note" value="' . gm_h($note) . '" placeholder="vd. đền bù bảo trì"></label></div></div>'
    . '<h3>Tiền tệ / tu vi</h3><div class="grid"><label>Linh thạch<input type="number" min="0" name="lt" value="' . ($rw['lt'] ?: '') . '"></label>'
    . '<label>Ma thạch<input type="number" min="0" name="mt" value="' . ($rw['mt'] ?: '') . '"></label>'
    . '<label>Tu vi (cộng thẳng, không giới hạn thanh)<input type="number" min="0" name="exp" value="' . ($rw['exp'] ?: '') . '"></label></div>'
    . '<div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(320px,1fr));margin-top:10px">'
    . '<div><h3>Đạo cụ</h3>' . gm_reward_rows('dj', 'dj', $rw['dj']) . '</div>'
    . '<div><h3>Dược phẩm</h3>' . gm_reward_rows('yp', 'yp', $rw['yp'], 2) . '</div>'
    . '<div><h3>Trang bị (số lượng = số món)</h3>' . gm_reward_rows('zb', 'zb', $rw['zb'], 2) . '</div></div>'
    . '<p><button class="btn">Xem trước →</button></p></form>';
