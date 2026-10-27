<?php
/**
 * test_conghuong.php — Cộng Hưởng (class/function/conghuong.php), phần tính thuần. KHÔNG cần DB.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_conghuong.php
 */
error_reporting(E_ALL & ~E_DEPRECATED);
// Nạp qua player.php như trong game (wugong_config_all bản chuẩn nằm ở player.php).
require_once __DIR__ . '/../class/player.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}

// Mọi võ công + công pháp đều thuộc đúng 1 nhóm
$mem = \player\conghuong_members();
$n = 0; foreach ($mem as $l) $n += count($l);
$wgN = 0; foreach (\player\wugong_config_all() as $w) if (intval($w['wgid']) > 0) $wgN++;
$cpN = count(\player\congphap_config_all());
tassert('H1 6 nhom', count($mem), 6);
tassert('H1 moi bo deu vao nhom', $n, $wgN + $cpN);

// Chưa học gì
$r = \player\conghuong_eval(array(), array());
tassert('H2 chua hoc = 0', $r['bonus'], array('atk' => 0.0, 'def' => 0.0, 'hp' => 0.0));

// Kim: 2 võ công Viên Mãn (cấp 10) + 1 chưa đủ cấp => mốc 2 bộ (+0.3% Công)
$r = \player\conghuong_eval(array(2 => 10, 3 => 12, 8 => 9), array());
tassert('H3 kim 2 bo vien man = +0.3 cong', $r['bonus']['atk'], 0.3);
tassert('H3 dem dung so bo vien man', $r['groups'][0]['done'], 2);

// Kim: 4 bộ (2 võ công + 2 công pháp cấp tối đa 10) => 0.3 + 0.5
$r = \player\conghuong_eval(array(2 => 10, 3 => 10), array(2 => 10, 10 => 10));
tassert('H4 kim 4 bo = +0.8 cong', $r['bonus']['atk'], 0.8);

// Toàn bộ mọi nhóm Viên Mãn => mỗi chỉ số 3%
$wgAll = array(); foreach (\player\wugong_config_all() as $w) if (intval($w['wgid']) > 0) $wgAll[intval($w['wgid'])] = 99;
$cpAll = array(); foreach (\player\congphap_config_all() as $c) $cpAll[intval($c['cpid'])] = 10;
$r = \player\conghuong_eval($wgAll, $cpAll);
tassert('H5 full = 3% moi chi so (<= 5%)', $r['bonus'], array('atk' => 3.0, 'def' => 3.0, 'hp' => 3.0));

echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
