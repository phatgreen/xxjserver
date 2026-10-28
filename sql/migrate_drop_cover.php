<?php
/**
 * migrate_drop_cover.php — Boss bậc >= 60 chỉ rơi 6 ô quý (8/13/14 tu luyện · 9/10/11 đột phá) của bậc trang bị <= cấp boss.
 * Cùng quy tắc tools/gen/drop_cover_build.py (quái rơi 9 ô thường nằm trong class/config/guaiwu_config.json, không cần ghi DB).
 *
 *   php sql/migrate_drop_cover.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_drop_cover.php --apply      ghi thật
 *
 * Idempotent: boss đã đúng danh sách thì bỏ qua.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$MIN_TIER = 60;
$SPECIAL = array(8, 9, 10, 11, 13, 14);
$zb = json_decode(file_get_contents($ROOT . '/class/config/zhuangbei_config.json'), true);
if (!is_array($zb)) { echo "Khong doc duoc zhuangbei_config.json\n"; exit(2); }
usort($zb, function ($a, $b) { return intval($a['zbid']) - intval($b['zbid']); });
$tiers = array();
foreach ($zb as $v) $tiers[intval($v['zblv'])] = true;
$tiers = array_keys($tiers);
sort($tiers);

$changed = 0; $same = 0;
foreach ($dblj->query("SELECT bossid, bossname, bosslv, bosszb FROM boss WHERE bosslv >= $MIN_TIER ORDER BY bosslv") as $b) {
    $T = 0;
    foreach ($tiers as $t) if ($t <= intval($b['bosslv'])) $T = $t;
    $want = array();
    foreach ($zb as $v) {
        if (intval($v['zblv']) == $T && in_array(intval($v['zbtool']), $SPECIAL, true)) $want[] = intval($v['zbid']);
    }
    $want = implode(',', $want);
    if ($b['bosszb'] === $want) { $same++; continue; }
    echo "[boss] #{$b['bossid']} {$b['bossname']} cap {$b['bosslv']} (bac $T): {$b['bosszb']} -> $want\n";
    if ($APPLY) {
        $up = $dblj->prepare("UPDATE boss SET bosszb = ? WHERE bossid = ?");
        $up->execute(array($want, intval($b['bossid'])));
    }
    $changed++;
}
echo ($APPLY ? 'DA GHI' : 'DRY-RUN') . ": $changed boss doi, $same boss da dung\n";
