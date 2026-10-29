<?php
if (!defined('GM_PAGE')) exit;   // chỉ chạy qua gm/gm.php
// Tab Nhật ký: mọi thao tác GM (đăng nhập, sửa nhân vật, phát quà, giftcode) — bảng gm_log.
$labels = array('login' => 'Đăng nhập', 'login_fail' => 'Sai mã GM', 'logout' => 'Đăng xuất', 'player_edit' => 'Sửa chỉ số', 'player_item' => 'Sửa túi đồ',
    'player_zbdel' => 'Xoá trang bị', 'gift' => 'Phát quà', 'code_new' => 'Tạo giftcode', 'code_edit' => 'Sửa giftcode', 'code_delete' => 'Xoá giftcode', 'code_setup' => 'Tạo mã GM', 'code_change' => 'Đổi mã GM');
$q = gm_req('q');
$a = gm_req('a');
gm_log_ready($db);
$sql = "SELECT * FROM gm_log WHERE 1"; $args = array();
if ($a !== '') { $sql .= " AND action = ?"; $args[] = $a; }
if ($q !== '') { $sql .= " AND (target LIKE ? OR detail LIKE ?)"; $args[] = '%' . $q . '%'; $args[] = '%' . $q . '%'; }
$st = $db->prepare($sql . " ORDER BY id DESC LIMIT 300");
$st->execute($args);
echo '<div class="card"><form class="flex" method="get"><input type="hidden" name="tab" value="log"><label>Loại<select name="a"><option value="">Tất cả</option>';
foreach ($labels as $k => $v) echo '<option value="' . $k . '"' . ($k === $a ? ' selected' : '') . '>' . $v . '</option>';
echo '</select></label><label style="flex:1">Tìm (nhân vật, mã, nội dung)<input name="q" value="' . gm_h($q) . '"></label><button class="btn">Lọc</button></form></div>';
echo '<div class="card"><h2>Nhật ký GM (300 dòng mới nhất)</h2><div class="tbl"><table><tr><th>Lúc</th><th>IP</th><th>Thao tác</th><th>Đối tượng</th><th>Chi tiết</th></tr>';
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
    $d = json_decode($r['detail'], true);
    $det = is_array($d) ? json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string)$r['detail'];
    echo '<tr><td class="mut" style="white-space:nowrap">' . gm_h($r['created_at']) . '</td><td class="mut">' . gm_h($r['ip']) . '</td><td><span class="tag' . ($r['action'] === 'login_fail' ? ' red' : '') . '">'
        . gm_h(isset($labels[$r['action']]) ? $labels[$r['action']] : $r['action']) . '</span></td><td>' . gm_h($r['target']) . '</td><td><details><summary class="mut">xem</summary><pre style="white-space:pre-wrap;max-width:640px;margin:4px 0">'
        . gm_h($det) . '</pre></details></td></tr>';
}
echo '</table></div></div>';
