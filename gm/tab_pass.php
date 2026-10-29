<?php
if (!defined('GM_PAGE')) exit;   // chỉ chạy qua gm/gm.php
// Tab Mã GM: đổi mã đăng nhập (cần mã hiện tại). Mã lưu bảng gm_setting.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && gm_req('do') === 'change') {
    $old = isset($_POST['old']) ? (string)$_POST['old'] : '';
    $c1 = isset($_POST['newcode']) ? (string)$_POST['newcode'] : '';
    $c2 = isset($_POST['newcode2']) ? (string)$_POST['newcode2'] : '';
    $err = '';
    if (GM_CODE_HASH !== '') $err = 'Mã đang cố định trong gm/config.php (GM_CODE_HASH) — xoá trống hằng đó để đổi mã tại đây.';
    elseif (!password_verify($old, gm_code_hash($db))) $err = 'Mã hiện tại không đúng.';
    else $err = gm_code_rule_err($c1, $c2);
    if ($err === '') {
        gm_save_code($db, $c1);
        gm_log($db, 'code_change', '', 'đổi mã GM');
        $flash[] = array('ok', 'Đã đổi mã GM. Lần đăng nhập sau dùng mã mới.');
    } else {
        $flash[] = array('bad', gm_h($err));
    }
}
echo '<form method="post" class="card" style="max-width:420px"><h2>Đổi mã GM</h2><div class="rows">' . gm_csrf_field()
    . '<input type="hidden" name="tab" value="pass"><input type="hidden" name="do" value="change">'
    . '<label>Mã hiện tại<input type="password" name="old" autocomplete="current-password"></label>'
    . '<label>Mã mới (ít nhất 6 ký tự)<input type="password" name="newcode" autocomplete="new-password"></label>'
    . '<label>Nhập lại mã mới<input type="password" name="newcode2" autocomplete="new-password"></label>'
    . '<button class="btn">Đổi mã</button>'
    . '<div class="mut">Quên mã: chạy <code>php gm/reset_code.php</code> trên máy chủ rồi mở lại trang tại localhost để tạo mã mới.</div></div></form>';
