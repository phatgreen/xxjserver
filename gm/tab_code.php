<?php
if (!defined('GM_PAGE')) exit;   // chỉ chạy qua gm/gm.php
// Tab Giftcode: danh sách (phần thưởng hiện tên, điều kiện, số lượt nhập) · tạo / sửa / nhân bản / xoá · xem ai đã nhập.
// Bảng duihuan (game/duihuan.php đọc): dhm (chữ thường), dhname, dhyxb LT, dhczb MT, dhexp, dhdj "id|n,…", dhyp "id|n,…", dhzb "id,…",
// dh_maxday / dh_maxlv (sql/migrate_tanthu.php; 0 = không giới hạn). Mỗi nhân vật nhập 1 lần (duihuan_log).
$hasCond = gm_has_col($db, 'duihuan', 'dh_maxday');
$act = gm_req('act');
$id = intval(gm_req('id'));
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $act === 'save') {
    $dhm = strtolower(trim(gm_req('dhm')));
    $f = array('dhm' => $dhm, 'dhname' => trim(gm_req('dhname')), 'dhyxb' => max(0, intval(gm_req('lt'))), 'dhczb' => max(0, intval(gm_req('mt'))),
        'dhexp' => max(0, min(2147483647, intval(gm_req('exp')))));
    $f['dhdj'] = gm_rows_to_str(gm_parse_rows('dj', 'dj', true, $errors));
    $f['dhyp'] = gm_rows_to_str(gm_parse_rows('yp', 'yp', true, $errors));
    $zbRows = gm_parse_rows('zb', 'zb', false, $errors);
    $f['dhzb'] = gm_rows_to_str($zbRows, false);
    if ($hasCond) { $f['dh_maxday'] = max(0, intval(gm_req('maxday'))); $f['dh_maxlv'] = max(0, intval(gm_req('maxlv'))); }
    if (!preg_match('/^[a-z0-9_-]{3,32}$/', $dhm)) $errors[] = 'Mã chỉ gồm chữ thường, số, _ hoặc -, dài 3–32 ký tự (giống trang nhập Giftcode).';
    if ($f['dhname'] === '') $errors[] = 'Chưa có tên gói quà.';
    $st = $db->prepare("SELECT dhid FROM duihuan WHERE dhm = ? AND dhid <> ?");
    $st->execute(array($dhm, $id));
    if ($st->fetchColumn()) $errors[] = 'Mã "' . $dhm . '" đã tồn tại.';
    if (!$errors) {
        $old = false;
        if ($id > 0) { $st = $db->prepare("SELECT * FROM duihuan WHERE dhid = ?"); $st->execute(array($id)); $old = $st->fetch(PDO::FETCH_ASSOC); }
        $cols = array_keys($f);
        if ($old) {
            $set = array(); foreach ($cols as $c) $set[] = "`$c` = ?";
            $vals = array_values($f); $vals[] = $id;
            $db->prepare("UPDATE duihuan SET " . implode(', ', $set) . " WHERE dhid = ?")->execute($vals);
            gm_log($db, 'code_edit', $dhm, array('before' => $old, 'after' => $f));
            $flash[] = array('ok', 'Đã lưu giftcode <b>' . gm_h($dhm) . '</b>.');
        } else {
            $db->prepare("INSERT INTO duihuan (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', array_fill(0, count($cols), '?')) . ")")->execute(array_values($f));
            gm_log($db, 'code_new', $dhm, $f);
            $flash[] = array('ok', 'Đã tạo giftcode <b>' . gm_h($dhm) . '</b>.');
        }
        $act = '';
    } else {
        $act = 'form';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $act === 'delete' && $id > 0) {
    $st = $db->prepare("SELECT * FROM duihuan WHERE dhid = ?"); $st->execute(array($id)); $old = $st->fetch(PDO::FETCH_ASSOC);
    if ($old) {
        $db->prepare("DELETE FROM duihuan WHERE dhid = ?")->execute(array($id));
        gm_log($db, 'code_delete', $old['dhm'], $old);
        $flash[] = array('ok', 'Đã xoá giftcode <b>' . gm_h($old['dhm']) . '</b> (lịch sử nhập vẫn giữ trong duihuan_log).');
    }
    $act = '';
}

// ── Form tạo / sửa ────────────────────────────────────────────
if ($act === 'form') {
    $c = array('dhm' => '', 'dhname' => '', 'dhyxb' => 0, 'dhczb' => 0, 'dhexp' => 0, 'dhdj' => '', 'dhyp' => '', 'dhzb' => '', 'dh_maxday' => 0, 'dh_maxlv' => 0);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {   // lưu lỗi ⇒ hiện lại đúng dữ liệu vừa nhập
        $e2 = array();
        $c = array('dhm' => gm_req('dhm'), 'dhname' => gm_req('dhname'), 'dhyxb' => intval(gm_req('lt')), 'dhczb' => intval(gm_req('mt')), 'dhexp' => intval(gm_req('exp')),
            'dhdj' => gm_rows_to_str(gm_parse_rows('dj', 'dj', true, $e2)), 'dhyp' => gm_rows_to_str(gm_parse_rows('yp', 'yp', true, $e2)),
            'dhzb' => gm_rows_to_str(gm_parse_rows('zb', 'zb', false, $e2), false), 'dh_maxday' => intval(gm_req('maxday')), 'dh_maxlv' => intval(gm_req('maxlv')));
    } elseif ($id > 0 || intval(gm_req('copy')) > 0) {
        $st = $db->prepare("SELECT * FROM duihuan WHERE dhid = ?"); $st->execute(array($id > 0 ? $id : intval(gm_req('copy'))));
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) { $c = array_merge($c, $row); if ($id <= 0) { $c['dhm'] = $c['dhm'] . '-2'; } }
    }
    foreach ($errors as $e) echo '<div class="flash bad">' . gm_h($e) . '</div>';
    echo '<form class="card" method="post">' . gm_csrf_field() . '<input type="hidden" name="act" value="save"><input type="hidden" name="id" value="' . $id . '">'
        . '<h2>' . ($id > 0 ? 'Sửa giftcode' : 'Tạo giftcode mới') . '</h2><div class="grid">'
        . '<label>Mã (người chơi gõ, không phân biệt hoa thường)<input name="dhm" value="' . gm_h($c['dhm']) . '" required pattern="[a-zA-Z0-9_\-]{3,32}"></label>'
        . '<label>Tên gói quà<input name="dhname" value="' . gm_h($c['dhname']) . '" required></label>'
        . '<label>Linh thạch<input type="number" min="0" name="lt" value="' . (intval($c['dhyxb']) ?: '') . '"></label>'
        . '<label>Ma thạch<input type="number" min="0" name="mt" value="' . (intval($c['dhczb']) ?: '') . '"></label>'
        . '<label>Tu vi<input type="number" min="0" name="exp" value="' . (intval($c['dhexp']) ?: '') . '"></label>';
    if ($hasCond) {
        echo '<label>Chỉ nhân vật trong N ngày đầu (0 = không giới hạn)<input type="number" min="0" name="maxday" value="' . intval($c['dh_maxday']) . '"></label>'
            . '<label>Cấp tối đa (0 = không giới hạn)<input type="number" min="0" name="maxlv" value="' . intval($c['dh_maxlv']) . '"></label>';
    }
    echo '</div><div class="grid" style="grid-template-columns:repeat(auto-fill,minmax(320px,1fr));margin-top:10px">'
        . '<div><h3>Đạo cụ</h3>' . gm_reward_rows('dj', 'dj', gm_str_to_rows($c['dhdj'])) . '</div>'
        . '<div><h3>Dược phẩm</h3>' . gm_reward_rows('yp', 'yp', gm_str_to_rows($c['dhyp']), 2) . '</div>'
        . '<div><h3>Trang bị (mỗi dòng 1 món)</h3>' . gm_reward_rows('zb', 'zb', gm_str_to_rows($c['dhzb'], false), 2, false) . '</div></div>'
        . '<div class="flex" style="margin-top:12px"><button class="btn">Lưu giftcode</button><a class="btn ghost" href="' . gm_url(array('tab' => 'code')) . '">Huỷ</a></div></form>';
    return;
}

// ── Ai đã nhập ────────────────────────────────────────────────
if ($act === 'used') {
    $dhm = strtolower(gm_req('dhm'));
    $st = $db->prepare("SELECT l.sid, l.created_at, g.uname, g.uid, g.ulv FROM duihuan_log l LEFT JOIN game1 g ON g.sid = l.sid WHERE l.dhm = ? ORDER BY l.id DESC LIMIT 500");
    $st->execute(array($dhm));
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    echo '<div class="card"><h2>Đã nhập mã <b>' . gm_h($dhm) . '</b>: ' . count($rows) . ' lượt</h2><div class="tbl"><table><tr><th>Lúc</th><th>Nhân vật</th><th>UID</th><th class="num">Cấp hiện tại</th></tr>';
    foreach ($rows as $r) {
        echo '<tr><td class="mut">' . gm_h(date('Y-m-d H:i:s', intval($r['created_at']))) . '</td><td>' . ($r['uname'] !== null ? '<a href="' . gm_url(array('tab' => 'player', 'sid' => $r['sid'])) . '">' . gm_h($r['uname']) . '</a>' : '<span class="mut">(đã xoá) ' . gm_h($r['sid']) . '</span>')
            . '</td><td>' . intval($r['uid']) . '</td><td class="num">' . intval($r['ulv']) . '</td></tr>';
    }
    echo '</table></div><p><a class="btn ghost" href="' . gm_url(array('tab' => 'code')) . '">← Danh sách</a></p></div>';
    return;
}

// ── Danh sách ─────────────────────────────────────────────────
$q = gm_req('q');
$used = array();
foreach ($db->query("SELECT dhm, COUNT(*) n FROM duihuan_log GROUP BY dhm")->fetchAll(PDO::FETCH_ASSOC) as $u) $used[$u['dhm']] = intval($u['n']);
$rows = $db->query("SELECT * FROM duihuan ORDER BY dhid DESC")->fetchAll(PDO::FETCH_ASSOC);
echo '<div class="card"><div class="flex" style="justify-content:space-between"><h2>Giftcode (' . count($rows) . ')</h2>'
    . '<form class="flex" method="get"><input type="hidden" name="tab" value="code"><input name="q" value="' . gm_h($q) . '" placeholder="Lọc theo mã / tên / vật phẩm"><button class="btn ghost">Lọc</button></form>'
    . '<a class="btn" href="' . gm_url(array('tab' => 'code', 'act' => 'form')) . '">+ Tạo giftcode</a></div>'
    . '<div class="tbl"><table><tr><th>Mã</th><th>Tên</th><th>Phần thưởng</th>' . ($hasCond ? '<th>Điều kiện</th>' : '') . '<th class="num">Đã nhập</th><th></th></tr>';
foreach ($rows as $r) {
    $rw = array('lt' => intval($r['dhyxb']), 'mt' => intval($r['dhczb']), 'exp' => intval($r['dhexp']),
        'dj' => gm_str_to_rows($r['dhdj']), 'yp' => gm_str_to_rows($r['dhyp']), 'zb' => gm_str_to_rows($r['dhzb'], false));
    $text = gm_reward_text($rw);
    if ($q !== '' && stripos($r['dhm'] . ' ' . $r['dhname'] . ' ' . strip_tags($text), $q) === false) continue;
    $cond = array();
    if ($hasCond && intval($r['dh_maxday']) > 0) $cond[] = '<span class="tag yel">≤ ' . intval($r['dh_maxday']) . ' ngày tuổi</span>';
    if ($hasCond && intval($r['dh_maxlv']) > 0) $cond[] = '<span class="tag yel">cấp ≤ ' . intval($r['dh_maxlv']) . '</span>';
    $n = isset($used[$r['dhm']]) ? $used[$r['dhm']] : 0;
    echo '<tr><td><b>' . gm_h($r['dhm']) . '</b></td><td>' . gm_h($r['dhname']) . '</td><td>' . $text . '</td>' . ($hasCond ? '<td>' . ($cond ? implode('', $cond) : '<span class="mut">—</span>') . '</td>' : '')
        . '<td class="num"><a href="' . gm_url(array('tab' => 'code', 'act' => 'used', 'dhm' => $r['dhm'])) . '">' . $n . '</a></td><td style="white-space:nowrap">'
        . '<a class="btn sm" href="' . gm_url(array('tab' => 'code', 'act' => 'form', 'id' => $r['dhid'])) . '">Sửa</a> '
        . '<a class="btn sm ghost" href="' . gm_url(array('tab' => 'code', 'act' => 'form', 'copy' => $r['dhid'])) . '">Nhân bản</a> '
        . '<form method="post" style="display:inline" data-confirm="Xoá giftcode ' . gm_h($r['dhm']) . '?">' . gm_csrf_field() . '<input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="' . intval($r['dhid']) . '"><button class="btn sm red">Xoá</button></form>'
        . '</td></tr>';
}
echo '</table></div></div>';
