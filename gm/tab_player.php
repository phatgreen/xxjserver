<?php
if (!defined('GM_PAGE')) exit;   // chỉ chạy qua gm/gm.php
// Tab Nhân vật: tìm theo tên / uid / sid · xem chỉ số, túi đồ, trang bị, Tân Thủ · sửa chỉ số / số lượng đạo cụ (ghi nhật ký trước → sau)
$EDIT_COLS = array('ulv' => 'Cấp', 'uexp' => 'Tu vi', 'uyxb' => 'Linh thạch', 'uczb' => 'Ma thạch', 'ugj' => 'Công',
    'ufy' => 'Thủ', 'umaxhp' => 'HP tối đa', 'uhp' => 'HP hiện tại', 'ustamina' => 'Thể lực', 'vip' => 'VIP');
$sid = gm_req('sid');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sid !== '') {
    $p0 = $db->prepare("SELECT * FROM game1 WHERE sid = ? LIMIT 1");
    $p0->execute(array($sid));
    $before = $p0->fetch(PDO::FETCH_ASSOC);
    if (!$before) {
        $flash[] = array('bad', 'Không tìm thấy nhân vật.');
    } elseif (gm_req('act') === 'edit') {
        $changes = array();
        foreach ($EDIT_COLS as $col => $label) {
            $v = gm_req('v_' . $col);
            if ($v === '') continue;
            $mode = gm_req('m_' . $col, 'set');
            $num = floatval($v);
            $new = $mode === 'add' ? floatval($before[$col]) + $num : $num;
            if ($new < 0) { $flash[] = array('bad', gm_h($label) . ': không được âm.'); continue; }
            if ($col === 'ulv' && ($new < 1 || $new > 4300)) { $flash[] = array('bad', 'Cấp phải trong 1–4300.'); continue; }
            $db->prepare("UPDATE game1 SET `$col` = ? WHERE sid = ?")->execute(array($new, $sid));
            $changes[$col] = array($before[$col], $new);
        }
        \player\player_cache_clear($sid);
        if ($changes) {
            gm_log($db, 'player_edit', $before['uname'] . ' (' . $before['uid'] . ')', $changes);
            $t = array(); foreach ($changes as $c => $ab) $t[] = gm_h($EDIT_COLS[$c]) . ': ' . gm_num($ab[0]) . ' → ' . gm_num($ab[1]);
            $flash[] = array('ok', 'Đã sửa ' . gm_h($before['uname']) . ': ' . implode(' · ', $t));
        }
    } elseif (gm_req('act') === 'djset') {
        $djid = intval(gm_req('djid')); $n = max(0, intval(gm_req('n')));
        $st = $db->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ?");
        $st->execute(array($sid, $djid)); $old = intval($st->fetchColumn());
        if ($n === 0) $db->prepare("DELETE FROM playerdaoju WHERE sid = ? AND djid = ?")->execute(array($sid, $djid));
        else $db->prepare("UPDATE playerdaoju SET djsum = ? WHERE sid = ? AND djid = ?")->execute(array($n, $sid, $djid));
        gm_log($db, 'player_item', $before['uname'] . ' (' . $before['uid'] . ')', array('djid' => $djid, 'name' => gm_name('dj', $djid), 'from' => $old, 'to' => $n));
        $flash[] = array('ok', gm_h(gm_name('dj', $djid)) . ': ' . $old . ' → ' . $n);
    } elseif (gm_req('act') === 'ypset') {
        $ypid = intval(gm_req('ypid')); $n = max(0, intval(gm_req('n')));
        $st = $db->prepare("SELECT ypsum FROM playeryaopin WHERE sid = ? AND ypid = ?");
        $st->execute(array($sid, $ypid)); $old = intval($st->fetchColumn());
        if ($n === 0) $db->prepare("DELETE FROM playeryaopin WHERE sid = ? AND ypid = ?")->execute(array($sid, $ypid));
        else $db->prepare("UPDATE playeryaopin SET ypsum = ? WHERE sid = ? AND ypid = ?")->execute(array($n, $sid, $ypid));
        gm_log($db, 'player_item', $before['uname'] . ' (' . $before['uid'] . ')', array('ypid' => $ypid, 'name' => gm_name('yp', $ypid), 'from' => $old, 'to' => $n));
        $flash[] = array('ok', gm_h(gm_name('yp', $ypid)) . ': ' . $old . ' → ' . $n);
    } elseif (gm_req('act') === 'zbdel') {
        $zn = intval(gm_req('zbnowid'));
        $worn = false;
        for ($i = 1; $i <= 16; $i++) if (intval($before['tool' . $i]) === $zn) $worn = true;
        if ($worn) {
            $flash[] = array('bad', 'Trang bị đang mặc — tháo trong game trước khi xoá.');
        } else {
            $z = \player\getzb($zn, $db);
            $db->prepare("DELETE FROM playerzhuangbei WHERE zbnowid = ? AND sid = ?")->execute(array($zn, $sid));
            \player\zhuangbei_cache_clear();
            gm_log($db, 'player_zbdel', $before['uname'] . ' (' . $before['uid'] . ')', array('zbnowid' => $zn, 'name' => $z ? $z->zbname : ''));
            $flash[] = array('ok', 'Đã xoá trang bị #' . $zn . ($z ? ' ' . gm_h($z->zbname) : '') . '.');
        }
    }
}

$q = gm_req('q');
echo '<div class="card"><form class="flex" method="get"><input type="hidden" name="tab" value="player">'
    . '<label style="flex:1">Tìm nhân vật (tên, uid, sid — để trống = 50 nhân vật mới hoạt động gần nhất)<input name="q" value="' . gm_h($q) . '"></label>'
    . '<button class="btn">Tìm</button></form></div>';

if ($sid === '') {
    if ($q !== '') {
        $st = $db->prepare("SELECT sid, uid, uname, ulv, uyxb, uczb, endtime FROM game1 WHERE uname LIKE ? OR sid = ? OR uid = ? ORDER BY endtime DESC LIMIT 50");
        $st->execute(array('%' . $q . '%', $q, ctype_digit($q) ? intval($q) : -1));
    } else {
        $st = $db->query("SELECT sid, uid, uname, ulv, uyxb, uczb, endtime FROM game1 ORDER BY endtime DESC LIMIT 50");
    }
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    echo '<div class="card"><h2>Nhân vật (' . count($rows) . ')</h2><div class="tbl"><table><tr><th>UID</th><th>Tên</th><th class="num">Cấp</th><th class="num">Linh thạch</th><th class="num">Ma thạch</th><th>Hoạt động</th><th>Tân thủ</th><th></th></tr>';
    foreach ($rows as $r) {
        $d = \player\tanthu_active_day($r['sid'], $db);
        echo '<tr><td>' . intval($r['uid']) . '</td><td>' . gm_h($r['uname']) . '</td><td class="num">' . intval($r['ulv']) . '</td><td class="num">' . gm_num($r['uyxb'])
            . '</td><td class="num">' . gm_num($r['uczb']) . '</td><td class="mut">' . gm_h($r['endtime']) . '</td><td>' . ($d > 0 ? '<span class="tag green">ngày ' . $d . '</span>' : '')
            . '</td><td><a class="btn sm" href="' . gm_url(array('tab' => 'player', 'sid' => $r['sid'])) . '">Xem / sửa</a></td></tr>';
    }
    echo '</table></div></div>';
    return;
}

$st = $db->prepare("SELECT * FROM game1 WHERE sid = ? LIMIT 1");
$st->execute(array($sid));
$p = $st->fetch(PDO::FETCH_ASSOC);
if (!$p) { echo '<div class="flash bad">Không tìm thấy nhân vật.</div>'; return; }
$pl = \player\getplayer($sid, $db);
$tt = \player\tanthu_row($sid, $db);
$ttDay = \player\tanthu_active_day($sid, $db);

echo '<div class="card"><h2>' . gm_h($p['uname']) . ' <span class="mut">UID ' . intval($p['uid']) . ' · sid ' . gm_h($sid) . '</span></h2><div class="kv">';
$kv = array('Cấp' => $p['ulv'], 'Tu vi' => gm_num($p['uexp']) . ' / ' . gm_num($pl->umaxexp), 'Linh thạch' => gm_num($p['uyxb']), 'Ma thạch' => gm_num($p['uczb']),
    'Công (gốc)' => gm_num($p['ugj']), 'Thủ (gốc)' => gm_num($p['ufy']), 'HP' => gm_num($p['uhp']) . ' / ' . gm_num($p['umaxhp']), 'Thể lực' => $p['ustamina'] . ' / ' . intval($pl->umaxstamina),
    'VIP' => $p['vip'], 'Bản đồ' => $p['nowmid'], 'Hoạt động' => $p['endtime'],
    'Tân thủ' => $ttDay > 0 ? ('ngày ' . $ttDay . '/3 · túi ' . gm_num($tt['tanthu_lt']) . ' LT' . (!empty($tt['tanthu_x2_until']) ? ' · ×2 tới ' . $tt['tanthu_x2_until'] : '')) : (($tt && !empty($tt['created_at'])) ? 'hết (tạo ' . substr($tt['created_at'], 0, 10) . ')' : 'không (nhân vật cũ)'));
foreach ($kv as $k => $v) echo '<div><span class="mut">' . gm_h($k) . '</span><b>' . gm_h($v) . '</b></div>';
echo '</div></div>';

echo '<form class="card" method="post" data-confirm="Lưu thay đổi chỉ số cho ' . gm_h($p['uname']) . '?">' . gm_csrf_field()
    . '<input type="hidden" name="act" value="edit"><h2>Sửa chỉ số</h2><p class="mut">Để trống = không đổi. "Cộng" = cộng thêm (số âm để trừ).</p><div class="grid">';
foreach ($EDIT_COLS as $col => $label) {
    echo '<label>' . gm_h($label) . ' <span class="mut">(hiện ' . gm_num($p[$col]) . ')</span><span class="flex"><select name="m_' . $col . '"><option value="set">Đặt</option><option value="add">Cộng</option></select>'
        . '<input name="v_' . $col . '" type="number" step="any" style="flex:1"></span></label>';
}
echo '</div><p><button class="btn">Lưu</button></p></form>';

// Túi đồ: đạo cụ + dược phẩm
$st = $db->prepare("SELECT djid, djsum, djused FROM playerdaoju WHERE sid = ? AND djsum > 0 ORDER BY djid");
$st->execute(array($sid));
echo '<div class="card"><h2>Đạo cụ</h2><div class="tbl"><table><tr><th>ID</th><th>Tên</th><th class="num">Số lượng</th><th>Đặt số lượng (0 = xoá)</th></tr>';
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo '<tr><td>' . intval($r['djid']) . '</td><td>' . gm_h(gm_name('dj', $r['djid'])) . '</td><td class="num">' . gm_num($r['djsum']) . '</td><td><form class="flex" method="post">' . gm_csrf_field()
        . '<input type="hidden" name="act" value="djset"><input type="hidden" name="djid" value="' . intval($r['djid']) . '"><input type="number" min="0" name="n" value="' . intval($r['djsum']) . '" style="width:110px"><button class="btn sm">Lưu</button></form></td></tr>';
}
echo '</table></div>';
$st = $db->prepare("SELECT ypid, ypsum FROM playeryaopin WHERE sid = ? AND ypsum > 0 ORDER BY ypid");
$st->execute(array($sid));
$yps = $st->fetchAll(PDO::FETCH_ASSOC);
if ($yps) {
    echo '<h3>Dược phẩm</h3><div class="tbl"><table><tr><th>ID</th><th>Tên</th><th class="num">Số lượng</th><th>Đặt số lượng (0 = xoá)</th></tr>';
    foreach ($yps as $r) {
        echo '<tr><td>' . intval($r['ypid']) . '</td><td>' . gm_h(gm_name('yp', $r['ypid'])) . '</td><td class="num">' . gm_num($r['ypsum']) . '</td><td><form class="flex" method="post">' . gm_csrf_field()
            . '<input type="hidden" name="act" value="ypset"><input type="hidden" name="ypid" value="' . intval($r['ypid']) . '"><input type="number" min="0" name="n" value="' . intval($r['ypsum']) . '" style="width:110px"><button class="btn sm">Lưu</button></form></td></tr>';
    }
    echo '</table></div>';
}
echo '</div>';

// Trang bị
$worn = array();
for ($i = 1; $i <= 16; $i++) if (intval($p['tool' . $i]) > 0) $worn[intval($p['tool' . $i])] = $i;
$st = $db->prepare("SELECT zbnowid, zbid, zbname, qianghua FROM playerzhuangbei WHERE sid = ? ORDER BY zbnowid");
$st->execute(array($sid));
$zbs = $st->fetchAll(PDO::FETCH_ASSOC);
$zcfg = gm_zb_all();
echo '<div class="card"><h2>Trang bị (' . count($zbs) . ')</h2><div class="tbl"><table><tr><th>#</th><th>Tên</th><th>Mã</th><th>Phẩm</th><th class="num">Cường hóa</th><th>Trạng thái</th><th></th></tr>';
foreach ($zbs as $z) {
    $c = isset($zcfg[(string)$z['zbid']]) ? $zcfg[(string)$z['zbid']] : array();
    $isWorn = isset($worn[intval($z['zbnowid'])]);
    echo '<tr><td>' . intval($z['zbnowid']) . '</td><td>' . gm_h($z['zbname']) . '</td><td>' . intval($z['zbid']) . '</td><td>' . gm_h(isset($c['rarity_name']) ? $c['rarity_name'] : '') . ' ' . intval(isset($c['rarity']) ? $c['rarity'] : 0)
        . '</td><td class="num">+' . intval($z['qianghua']) . '</td><td>' . ($isWorn ? '<span class="tag green">đang mặc ô ' . $worn[intval($z['zbnowid'])] . '</span>' : '<span class="tag">trong túi</span>') . '</td><td>'
        . ($isWorn ? '' : '<form method="post" data-confirm="Xoá trang bị #' . intval($z['zbnowid']) . '?">' . gm_csrf_field() . '<input type="hidden" name="act" value="zbdel"><input type="hidden" name="zbnowid" value="' . intval($z['zbnowid']) . '"><button class="btn sm red">Xoá</button></form>')
        . '</td></tr>';
}
echo '</table></div></div>';
echo '<p><a class="btn ghost" href="' . gm_url(array('tab' => 'gift', 'to' => $p['uname'])) . '">🎁 Phát quà cho nhân vật này</a> <a class="btn ghost" href="' . gm_url(array('tab' => 'log', 'q' => $p['uname'])) . '">📜 Nhật ký GM của nhân vật</a></p>';
