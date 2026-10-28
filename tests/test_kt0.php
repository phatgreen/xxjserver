<?php
/**
 * test_kt0.php — KT-0 (docs/KHUNG_TONG_500_4300.md): EXP quái cấp > 500, màu / tên cảnh giới, parse_str,
 * cột BIGINT (cần DB, sau khi chạy sql/migrate_kt0_bigint.php --apply), số 64-bit.
 * Chạy: php tests/test_kt0.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}

// ── E1. EXP quái: cấp ≤ 500 như cũ, > 500 ≈ 400 quái/cấp ──
tassert('E1 cấp 93 như cũ (93 × 107)', \player\guaiwu_exp(93, 107), 9951.0);
tassert('E1 cấp 500 như cũ', \player\guaiwu_exp(500, 107), 53500.0);
$kills = array();
foreach (array(501, 1000, 2000, 3000, 4300) as $lv) $kills[] = (int)round(110 * $lv * $lv / \player\guaiwu_exp($lv, 107));
tassert('E1 cấp 501–4300: 400 quái/cấp', $kills, array(400, 400, 400, 400, 400));
tassert('E1 cấp 4300 = 5.084.750', \player\guaiwu_exp(4300, 107), 5084750.0);
$r = \player\guaiwu_exp(1000);
tassert('E1 dao động ngẫu nhiên ±1%', $r >= 275000 * 106 / 107 - 1 && $r <= 275000 * 108 / 107 + 1, true);

// ── E2. Cảnh giới: đủ màu, màu đúng vị trí (từ Chu Thiên II trước bị lệch), tên đã sửa ──
include __DIR__ . '/../class/rangesjj.php';
include __DIR__ . '/../class/jingjieColor.php';
tassert('E2 số màu = số cảnh giới (86)', array(count($jingjieColor), count($rangesjj)), array(86, 86));
$bad = array();
foreach ($rangesjj as $i => $html) {
    preg_match_all('/color="?(#[0-9A-Fa-f]{6})/', $html, $m);
    if (!in_array(strtoupper($jingjieColor[$i]), array_map('strtoupper', $m[1]), true)) $bad[] = $i;
}
tassert('E2 màu khớp cảnh giới (trừ 2 sắc gần do người viết chọn: Kim Đan, Hư Vô Thánh Nhân)', $bad, array(8, 78));
$names = array();
foreach ($rangesjj as $html) $names[] = trim(strip_tags($html));
tassert('E2 "Sáng Thế" đúng chính tả', in_array('Sáng Thế', $names, true) && !in_array('Sáng Thê', $names, true), true);
$space = array(); foreach ($names as $n) if (strpos($n, '  ') !== false) $space[] = $n;
tassert('E2 không còn khoảng trắng đôi trong tên', $space, array());
tassert('E2 cấp 3701 (cảnh giới 74) hiện được', strpos(\player\getJingJieHtml(3701), 'Sáng') !== false, true);
tassert('E2 cấp 4299 hiện được', strpos(\player\getJingJieHtml(4299), 'Vô') !== false, true);

// ── E2b. "Thánh Nhân" không trùng (K6) ──
$cnt = array_count_values($names);
tassert('E2b mỗi tên cảnh giới chỉ một lần', array_keys(array_filter($cnt, function($n){ return $n > 1; })), array());
tassert('E2b cấp 3851 = Hỗn Độn Thánh Nhân', trim(strip_tags($rangesjj[77])), 'Hỗn Độn Thánh Nhân');

// ── E6. Đột Phá Đan (K2): đúng cảnh giới, +5%/viên, tối đa 3 · Sự kiện Thứ 7 +5%, Chủ Nhật +10% ──
$p = \player\tupo_pill_for_level(420);
tassert('E6 cấp 420 (Kim Đan) dùng Kim Đan Đột Phá Đan', array($p['djid'], $p['per_pill'], $p['max_pills']), array(21, 5.0, 3));
tassert('E6 cấp 400 (đột phá vào Kim Đan) cũng là đan Kim Đan', \player\tupo_pill_for_level(400)['djid'], 21);
tassert('E6 cấp 399 là đan Trúc Cơ', \player\tupo_pill_for_level(399)['djid'], 20);
tassert('E6 cấp 93 / 1000 không có đan', array(\player\tupo_pill_for_level(93), \player\tupo_pill_for_level(1000)), array(false, false));
$realms = array();
foreach (array(20, 21, 22, 23, 24, 25, 26) as $dj) { foreach (range(0, 85) as $r) { $q = \player\tupo_pill_for_level($r * 50); if ($q && $q['djid'] == $dj) $realms[$dj] = trim(strip_tags($rangesjj[$r])); } }
tassert('E6 tên đan khớp tên cảnh giới', $realms, array(20 => 'Trúc Cơ', 21 => 'Kim Đan', 22 => 'Nguyên Anh', 23 => 'Hóa Thần', 24 => 'Luyện Hư', 25 => 'Hợp Thể', 26 => 'Đại Thừa'));
tassert('E6 Thứ 4 không có sự kiện', \player\tupo_event_bonus(strtotime('2026-10-28'))[0], 0);
tassert('E6 Thứ 7 +5%', \player\tupo_event_bonus(strtotime('2026-10-31'))[0], 5.0);
tassert('E6 Chủ Nhật +10%', \player\tupo_event_bonus(strtotime('2026-11-01'))[0], 10.0);

// ── E3. Router không dùng parse_str 1 tham số (deprecated PHP 7.2, bỏ ở PHP 8) ──
$src = file_get_contents(__DIR__ . '/../game.php');
tassert('E3 game.php không còn parse_str 1 tham số', preg_match('/parse_str\(\s*\$\w+\s*\)/', $src), 0);

// ── E4. DB: cột tiền / chỉ số là BIGINT ──
$db = $dblj->query("SELECT DATABASE()")->fetchColumn();
$q = $dblj->prepare("SELECT data_type FROM information_schema.columns WHERE table_schema = ? AND table_name = 'game1' AND column_name = ?");
$notBig = array();
foreach (array('uyxb', 'uczb', 'uexp', 'uhp', 'umaxhp', 'ugj', 'ufy') as $c) { $q->execute(array($db, $c)); if (strtolower($q->fetchColumn()) !== 'bigint') $notBig[] = $c; }
if ($notBig) echo "GHI CHU E4: chua chay sql/migrate_kt0_bigint.php --apply ⇒ ";
tassert('E4 game1 tiền / EXP / chỉ số là BIGINT', $notBig, array());

// ── E5. PHP 64-bit (máy chủ thật cần PHP 7.4 x64) ──
tassert('E5 PHP_INT_SIZE = 8 (64-bit)', PHP_INT_SIZE, 8);
tassert('E5 intval 5 tỉ không bị kẹp', intval('5000000000'), 5000000000);

echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
