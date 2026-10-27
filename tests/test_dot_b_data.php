<?php
/**
 * test_dot_b_data.php — Kiểm tra dữ liệu đợt B (quái + bản đồ). KHÔNG cần DB.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_dot_b_data.php
 */
error_reporting(E_ALL & ~E_DEPRECATED);
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
$gw = json_decode(file_get_contents(__DIR__ . '/../class/config/guaiwu_config.json'), true);
$zb = json_decode(file_get_contents(__DIR__ . '/../class/config/zhuangbei_config.json'), true);
$data = json_decode(file_get_contents(__DIR__ . '/../sql/dot_b_data.json'), true);
$zbLv = array(); foreach ($zb as $z) $zbLv[intval($z['zbid'])] = intval($z['zblv']);

// Q1: key = id, id không trùng, tên không có dấu nháy (nowmid.php chèn tên thẳng vào SQL)
$bad = array(); $ids = array();
foreach ($gw as $k => $g) {
    $ids[] = intval($g['id']);
    if ((string)$k !== (string)$g['id']) $bad[] = "key $k != id {$g['id']}";
    if (strpos($g['gname'], "'") !== false || strpos($g['gname'], '"') !== false) $bad[] = "{$g['id']} ten co dau nhay";
}
tassert('Q1 171 quai (87 cu + 84 moi)', count($gw), 171);
tassert('Q1 id khong trung, ten an toan', array($bad, count($ids) === count(array_unique($ids))), array(array(), true));

// Q2: mọi trang bị rơi đều có bậc ≤ cấp quái
$over = array();
foreach ($gw as $g) {
    if ($g['gzb'] === '') continue;
    foreach (explode(',', $g['gzb']) as $zid) {
        $zid = intval($zid);
        if (!isset($zbLv[$zid])) $over[] = "{$g['id']}: zb $zid khong ton tai";
        // Đợt F: quái cấp ≥ 450 được rơi đồ bậc 500 (bậc cuối dải 1–500)
        elseif ($zbLv[$zid] > intval($g['glv']) && !($zbLv[$zid] === 500 && intval($g['glv']) >= 450)) $over[] = "{$g['id']} lv{$g['glv']}: zb $zid lv{$zbLv[$zid]}";
    }
}
tassert('Q2 trang bi roi khong vuot cap quai', $over, array());

// Q3: map trong dot_b_data chỉ dùng quái có thật; cấp quái tăng dần trong mỗi khu
$miss = array(); $order = array();
foreach ($data['regions'] as $r) {
    $prev = 0;
    foreach ($r['maps'] as $m) {
        foreach (explode(',', $m['mgid']) as $part) {
            $p = explode('|', $part);
            if (!isset($gw[$p[0]])) { $miss[] = $p[0]; continue; }
            $lv = intval($gw[$p[0]]['glv']);
            if ($lv < $prev) $order[] = "{$r['qyname']}: {$m['mname']} lv$lv < $prev";
            $prev = $lv;
        }
    }
}
tassert('Q3 map chi dung quai co that', $miss, array());
tassert('Q3 cap quai tang dan theo duong di', $order, array());

// Q4: phủ đủ dải 78 → 500, không hở quá 6 cấp
$lvs = array(); foreach ($gw as $g) if (intval($g['id']) >= 200) $lvs[] = intval($g['glv']);
sort($lvs);
// Khoảng hở tính trên toàn bộ quái (cũ + mới) — dải 92–105 do Hoa Quả Sơn có sẵn đảm nhận
$all = array(); foreach ($gw as $g) if (intval($g['glv']) >= 78) $all[] = intval($g['glv']);
sort($all); $gap = 0; for ($i = 1; $i < count($all); $i++) $gap = max($gap, $all[$i] - $all[$i - 1]);
tassert('Q4 quai moi tu cap 78 toi 498', array($lvs[0], $lvs[count($lvs) - 1]), array(78, 498));
tassert('Q4 khong ho qua 6 cap', $gap <= 6, true);
tassert('Q5 9 boss khu vuc', count($data['bosses']), 9);

echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
