<?php
if (!defined('GM_PAGE')) exit;   // chỉ chạy qua gm/gm.php
// Tab Vật phẩm: tra cứu đạo cụ / dược phẩm / trang bị — id, tên, giá, cờ (không bán tiệm, khóa giao dịch, giá bán NPC, hiệu ứng dùng),
// nguồn rơi (quái: guaiwu_config gdj/gyp/gzb · boss: bảng boss bossdj/bossyp/bosszb) và giftcode đang phát món đó.
$kind = gm_req('k', 'dj');
if (!in_array($kind, array('dj', 'yp', 'zb'), true)) $kind = 'dj';
$q = gm_req('q');
$filter = gm_req('f');
$page = max(1, intval(gm_req('p', 1)));
$per = 100;

// Nguồn rơi: id => danh sách "Tên quái (cấp)"
$src = array();
$mobKey = array('dj' => 'gdj', 'yp' => 'gyp', 'zb' => 'gzb');
$gw = \player\guaiwu_config_all();
$gw = isset($gw['guaiwu']) ? $gw['guaiwu'] : $gw;
foreach ($gw as $g) {
    if (!is_array($g)) continue;
    $f = $mobKey[$kind];
    foreach (explode(',', isset($g[$f]) ? (string)$g[$f] : '') as $x) {
        $x = intval($x); if ($x <= 0) continue;
        $src[$x][] = array('q', (isset($g['gname']) ? $g['gname'] : '?') . ' (' . intval(isset($g['glv']) ? $g['glv'] : 0) . ')');
    }
}
$bossKey = array('dj' => 'bossdj', 'yp' => 'bossyp', 'zb' => 'bosszb');
foreach ($db->query("SELECT bossname, bosslv, bossdj, bossyp, bosszb FROM boss")->fetchAll(PDO::FETCH_ASSOC) as $b) {
    foreach (explode(',', (string)$b[$bossKey[$kind]]) as $x) {
        $x = intval($x); if ($x <= 0) continue;
        $src[$x][] = array('b', $b['bossname'] . ' (' . intval($b['bosslv']) . ')');
    }
}
// Giftcode đang phát: id => [mã]
$inCode = array();
foreach ($db->query("SELECT dhm, dhdj, dhyp, dhzb FROM duihuan")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    $rows = $kind === 'dj' ? gm_str_to_rows($c['dhdj']) : ($kind === 'yp' ? gm_str_to_rows($c['dhyp']) : gm_str_to_rows($c['dhzb'], false));
    foreach ($rows as $r) $inCode[$r[0]][] = $c['dhm'];
}

$all = $kind === 'dj' ? gm_daoju_all() : ($kind === 'yp' ? gm_yaopin_all() : gm_zb_all());
$list = array();
foreach ($all as $id => $d) {
    if (!is_array($d)) continue;
    $id = intval($id);
    $name = $kind === 'dj' ? $d['djname'] : ($kind === 'yp' ? $d['ypname'] : $d['zbname']);
    $info = $kind === 'dj' ? (isset($d['djinfo']) ? $d['djinfo'] : '') : ($kind === 'zb' ? (isset($d['zbinfo']) ? $d['zbinfo'] : '') : '');
    if ($q !== '' && stripos($id . ' ' . $name . ' ' . $info, $q) === false) continue;
    $hasSrc = !empty($src[$id]);
    if ($filter === 'drop' && !$hasSrc) continue;
    if ($filter === 'nodrop' && $hasSrc) continue;
    if ($filter === 'code' && empty($inCode[$id])) continue;
    if ($filter === 'lock' && !($kind === 'dj' && (!empty($d['no_trade']) || \player\daoju_sell_price($id, isset($d['djyxb']) ? $d['djyxb'] : 0) <= 0))) continue;
    $list[$id] = array($name, $info, $d);
}
ksort($list);
$total = count($list);
$pages = max(1, intval(ceil($total / $per)));
$page = min($page, $pages);
$list = array_slice($list, ($page - 1) * $per, $per, true);

$tabsK = array('dj' => 'Đạo cụ', 'yp' => 'Dược phẩm', 'zb' => 'Trang bị');
$filters = array('' => 'Tất cả', 'drop' => 'Có nguồn rơi', 'nodrop' => 'Không rơi ở đâu', 'code' => 'Có trong giftcode');
if ($kind === 'dj') $filters['lock'] = 'Khóa / không bán NPC';
echo '<div class="card"><form class="flex" method="get"><input type="hidden" name="tab" value="items"><label>Loại<select name="k">';
foreach ($tabsK as $k => $v) echo '<option value="' . $k . '"' . ($k === $kind ? ' selected' : '') . '>' . $v . '</option>';
echo '</select></label><label>Lọc<select name="f">';
foreach ($filters as $k => $v) echo '<option value="' . $k . '"' . ($k === $filter ? ' selected' : '') . '>' . $v . '</option>';
echo '</select></label><label style="flex:1">Tìm (id, tên, mô tả)<input name="q" value="' . gm_h($q) . '"></label><button class="btn">Xem</button></form></div>';

echo '<div class="card"><h2>' . $tabsK[$kind] . ': ' . $total . ' món</h2><div class="tbl"><table><tr><th>ID</th><th>Tên</th>';
if ($kind === 'dj') echo '<th class="num">Giá tiệm</th><th class="num">Bán NPC</th><th>Cờ</th>';
if ($kind === 'yp') echo '<th class="num">Giá</th><th>Tác dụng</th>';
if ($kind === 'zb') echo '<th>Phẩm / cấp</th><th>Chỉ số</th>';
echo '<th>Nguồn rơi</th><th>Giftcode</th></tr>';
foreach ($list as $id => $it) {
    list($name, $info, $d) = $it;
    echo '<tr><td>' . $id . '</td><td><b>' . gm_h($name) . '</b>' . ($info !== '' ? '<div class="mut" style="max-width:420px">' . gm_h(mb_substr($info, 0, 160, 'UTF-8')) . '</div>' : '') . '</td>';
    if ($kind === 'dj') {
        $price = intval(isset($d['djyxb']) ? $d['djyxb'] : 0);
        $sell = \player\daoju_sell_price($id, $price);
        $tags = '';
        if (!empty($d['no_shop'])) $tags .= '<span class="tag">không bán tiệm</span>';
        if (!empty($d['no_trade'])) $tags .= '<span class="tag red">khóa giao dịch</span>';
        if ($sell <= 0) $tags .= '<span class="tag red">không bán NPC</span>'; elseif ($sell < $price) $tags .= '<span class="tag yel">bán NPC ' . round($sell * 100 / max(1, $price)) . '%</span>';
        if (!empty($d['use_effect']['type'])) $tags .= '<span class="tag green">dùng: ' . gm_h($d['use_effect']['type']) . '</span>';
        echo '<td class="num">' . gm_num(max(50, $price)) . '</td><td class="num">' . gm_num($sell) . '</td><td>' . $tags . '</td>';
    }
    if ($kind === 'yp') {
        $eff = array(); foreach (array('yphp' => 'HP', 'ypgj' => 'Công', 'ypfy' => 'Thủ', 'ypbj' => 'Bạo', 'ypxx' => 'Hút') as $k => $l) if (!empty($d[$k])) $eff[] = $l . ' +' . $d[$k];
        echo '<td class="num">' . gm_num(isset($d['ypjg']) ? $d['ypjg'] : 0) . '</td><td>' . gm_h(implode(', ', $eff)) . '</td>';
    }
    if ($kind === 'zb') {
        $st = array(); foreach (array('zbgj' => 'Công', 'zbfy' => 'Thủ', 'zbhp' => 'HP', 'zbbj' => 'Bạo', 'zbxx' => 'Hút', 'tuposjs' => '%ĐP', 'tupoexp' => '%TL') as $k => $l) if (!empty($d[$k])) $st[] = $l . ' ' . $d[$k];
        echo '<td>' . gm_h(isset($d['rarity_name']) ? $d['rarity_name'] : '') . ' ' . intval(isset($d['rarity']) ? $d['rarity'] : 0) . ' · cấp ' . intval(isset($d['realm_require']) ? $d['realm_require'] : 0) . ' · ô ' . intval(isset($d['zbtool']) ? $d['zbtool'] : 0)
            . '</td><td>' . gm_h(implode(', ', $st)) . '</td>';
    }
    $s = isset($src[$id]) ? $src[$id] : array();
    $sb = array(); $sq = array(); foreach ($s as $x) { if ($x[0] === 'b') $sb[] = $x; else $sq[] = $x; }
    $s = array_merge($sb, $sq);   // boss lên trước quái
    $sh = '';
    foreach (array_slice($s, 0, 4) as $x) $sh .= '<span class="tag' . ($x[0] === 'b' ? ' yel' : '') . '">' . ($x[0] === 'b' ? 'Boss ' : '') . gm_h($x[1]) . '</span>';
    if (count($s) > 4) $sh .= '<span class="mut">+' . (count($s) - 4) . '</span>';
    echo '<td>' . ($sh !== '' ? $sh : '<span class="mut">—</span>') . '</td><td>';
    if (!empty($inCode[$id])) foreach (array_unique($inCode[$id]) as $c) echo '<a class="tag green" href="' . gm_url(array('tab' => 'code', 'q' => $c)) . '">' . gm_h($c) . '</a>';
    echo '</td></tr>';
}
echo '</table></div>';
if ($pages > 1) {
    echo '<div class="pager">';
    for ($i = 1; $i <= $pages; $i++) echo $i === $page ? '<span>' . $i . '</span>' : '<a href="' . gm_url(array('tab' => 'items', 'k' => $kind, 'f' => $filter, 'q' => $q, 'p' => $i)) . '">' . $i . '</a>';
    echo '</div>';
}
echo '</div>';
