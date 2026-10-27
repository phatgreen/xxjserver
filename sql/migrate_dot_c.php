<?php
/**
 * migrate_dot_c.php — Đợt C: đặt 9 NPC nhiệm vụ vào map cửa vào của các khu 27, 28, 30–36.
 * Dữ liệu: sql/dot_c_data.json (sinh bởi tools/gen/dot_c_build.py). NPC / nhiệm vụ nằm trong
 * class/config/npc_config.json + renwu_config.json (không cần ghi DB).
 *
 *   php sql/migrate_dot_c.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_dot_c.php --apply      ghi thật
 *
 * Cần chạy SAU sql/migrate_dot_b.php. Idempotent: NPC đã có trong mid.mnpc thì bỏ qua.
 * Bảng mới của đợt C (thưởng trực tuyến / bế quan) tự tạo khi game chạy lần đầu.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$data = json_decode(file_get_contents(__DIR__ . '/dot_c_data.json'), true);
if (!is_array($data)) { echo "Khong doc duoc sql/dot_c_data.json\n"; exit(2); }

$warn = 0;
foreach ($data['npcs'] as $n) {
    $st = $dblj->prepare("SELECT q.mid, m.mnpc FROM qy q JOIN mid m ON m.mid = q.mid WHERE q.qyid = ?");
    $st->execute(array(intval($n['qyid'])));
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo "LOI: khu {$n['qyid']} chua co cua vao — hay chay sql/migrate_dot_b.php --apply truoc\n"; exit(1); }
    $ids = array_filter(array_map('trim', explode(',', $row['mnpc'])), 'strlen');
    if (in_array((string)$n['nid'], $ids, true)) {
        echo "[npc] {$n['nname']} (#{$n['nid']}) da o map {$row['mid']} — bo qua\n";
        continue;
    }
    $ids[] = (string)$n['nid'];
    echo "[npc] dat {$n['nname']} (#{$n['nid']}) vao map {$row['mid']} (khu {$n['qyid']}) -> mnpc = " . implode(',', $ids) . "\n";
    if ($APPLY) {
        $up = $dblj->prepare("UPDATE mid SET mnpc = ? WHERE mid = ?");
        $up->execute(array(implode(',', $ids), intval($row['mid'])));
    }
}

// Kiểm tra map đích của nhiệm vụ NPC (renwu.rwqy, dùng cho nút Truyền tống) khớp tên map trong DB
foreach ($data['task_maps'] as $t) {
    $st = $dblj->prepare("SELECT mid FROM mid WHERE mname = ? AND mqy = ? LIMIT 1");
    $st->execute(array($t['mname'], intval($t['qyid'])));
    $mid = intval($st->fetchColumn());
    if ($mid !== intval($t['mid'])) {
        $warn++;
        echo "CANH BAO: nhiem vu #{$t['rwid']}: map '{$t['mname']}' trong DB la $mid, config ghi {$t['mid']} — sua rwqy trong class/config/renwu_config.json\n";
    }
}
echo $warn ? "Co $warn canh bao map (chi anh huong nut Truyen tong).\n" : "Map nhiem vu NPC: khop.\n";
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
