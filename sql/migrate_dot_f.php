<?php
/**
 * migrate_dot_f.php — Đợt F: trang bị.
 *  1) Cập nhật máu / công 9 boss khu vực đợt B theo cân bằng mới (sql/dot_f_data.json).
 *  2) Đồ đã cường hóa trong playerzhuangbei: tính lại theo chỉ số gốc mới.
 *     Số lần cường hóa vào mỗi chỉ số ước từ bản cũ (lũy tiến 8%/lần, gốc cũ = tools/gen/data/zhuangbei_dot_f_src.json),
 *     rồi áp công thức mới: gốc mới + 8% gốc mới × số lần.
 *
 *   php sql/migrate_dot_f.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_dot_f.php --apply      ghi thật
 *
 * Chạy SAU migrate_dot_b.php. Idempotent: boss ghi giá trị tuyệt đối; đồ chỉ sửa khi còn theo gốc cũ
 * (giá trị DB < gốc mới), chạy lại không cộng thêm.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$data   = json_decode(file_get_contents(__DIR__ . '/dot_f_data.json'), true);
$oldCfg = json_decode(file_get_contents($ROOT . '/tools/gen/data/zhuangbei_dot_f_src.json'), true);
$newCfg = json_decode(file_get_contents($ROOT . '/class/config/zhuangbei_config.json'), true);
if (!is_array($data) || !is_array($oldCfg) || !is_array($newCfg)) { echo "Khong doc duoc du lieu dot F\n"; exit(2); }

// 1) Boss
foreach ($data['bosses'] as $b) {
    $st = $dblj->prepare("SELECT bossid, bossmaxhp, bossgj FROM boss WHERE bossname = ? LIMIT 1");
    $st->execute(array($b['bossname']));
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo "[boss] chua co {$b['bossname']} — chay migrate_dot_b.php --apply truoc\n"; continue; }
    echo "[boss] {$b['bossname']}: mau {$row['bossmaxhp']} -> {$b['bosshp']}, cong {$row['bossgj']} -> {$b['bossgj']}\n";
    if ($APPLY) {
        $up = $dblj->prepare("UPDATE boss SET bossmaxhp = ?, bosshp = LEAST(bosshp, ?), bossgj = ? WHERE bossid = ?");
        $up->execute(array($b['bosshp'], $b['bosshp'], $b['bossgj'], $row['bossid']));
    }
}

// 2) Đồ đã cường hóa
$stats = array('zbgj', 'zbfy', 'zbhp');
$n = 0;
$rows = $dblj->query("SELECT zbnowid, zbid, qianghua, zbgj, zbfy, zbhp FROM playerzhuangbei WHERE qianghua > 0")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $k = (string)intval($r['zbid']);
    if (!isset($oldCfg[$k]) || !isset($newCfg[$k])) continue;
    $set = array(); $log = array();
    foreach ($stats as $s) {
        $old = intval($oldCfg[$k][$s]); $new = intval($newCfg[$k][$s]); $db = intval($r[$s]);
        if ($old <= 0 || $new <= $old || $db >= $new) continue;   // không đổi gốc / đã tính lại
        $times = $db > $old ? intval(round(log($db / $old) / log(1.08))) : 0;
        $val = $new + max(1, intval(round($new * 0.08))) * $times;
        $set[$s] = $val;
        $log[] = "$s $db->$val (+$times lan)";
    }
    if (!$set) continue;
    $n++;
    echo "[do] #{$r['zbnowid']} (zbid {$r['zbid']}, +{$r['qianghua']}): " . implode(', ', $log) . "\n";
    if ($APPLY) {
        $sql = array(); $args = array();
        foreach ($set as $s => $v) { $sql[] = "`$s` = ?"; $args[] = $v; }
        $args[] = intval($r['zbnowid']);
        $dblj->prepare("UPDATE playerzhuangbei SET " . implode(', ', $sql) . " WHERE zbnowid = ?")->execute($args);
    }
}
echo "Do cuong hoa can tinh lai: $n\n";
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
