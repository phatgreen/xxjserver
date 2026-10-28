<?php
/**
 * migrate_index_sid.php — thêm chỉ mục cột sid cho các bảng hay truy vấn theo nhân vật nhưng chưa có chỉ mục
 * (game1 chỉ có PRIMARY uid ⇒ mọi trang quét toàn bảng). Danh sách đo từ code: số câu SQL lọc "sid =" mỗi bảng.
 *
 *   php sql/migrate_index_sid.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_index_sid.php --apply      ghi thật
 *
 * Sao lưu trước (Windows cmd): mysqldump.exe -uroot -p game > D:\XXJserver\backup.sql
 * Idempotent: bảng đã có chỉ mục bắt đầu bằng cột sid thì bỏ qua. Dùng chỉ mục 32 ký tự đầu (sid luôn 32 ký tự hex;
 * cột TEXT bắt buộc khai báo độ dài; varchar(255) utf8mb4 vượt giới hạn khóa 1000 byte của MyISAM).
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

// bảng => số câu SQL lọc theo sid trong code (đo 2026-10-28), xếp theo mức dùng
$TABLES = array(
    'game1' => 90, 'playerdaoju' => 55, 'playerzhuangbei' => 15, 'playeryaodan' => 14, 'playerwugong' => 12,
    'playeryaopin' => 10, 'playerrenwu' => 10, 'boss' => 9, 'midguaiwu' => 5, 'playerjineng' => 4,
    'playerchongwu' => 3, 'im' => 3, 'clubplayer' => 3,
);

$db = $dblj->query("SELECT DATABASE()")->fetchColumn();
$done = 0; $skip = 0;
foreach ($TABLES as $t => $uses) {
    $st = $dblj->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = 'sid'");
    $st->execute(array($db, $t));
    if (!intval($st->fetchColumn())) { echo "[bo qua] $t: khong co bang / cot sid\n"; $skip++; continue; }

    $st = $dblj->prepare("SELECT index_name FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND column_name = 'sid' AND seq_in_index = 1 LIMIT 1");
    $st->execute(array($db, $t));
    $have = $st->fetchColumn();
    if ($have) { echo "[bo qua] $t: da co chi muc '$have' tren sid\n"; $skip++; continue; }

    $rows = intval($dblj->query("SELECT COUNT(*) FROM `$t`")->fetchColumn());
    $long = intval($dblj->query("SELECT COUNT(*) FROM `$t` WHERE CHAR_LENGTH(sid) > 32")->fetchColumn());
    echo "[them] $t: ADD INDEX idx_sid (sid(32)) — $rows dong, $uses cau SQL dung" . ($long ? ", CANH BAO $long dong sid > 32 ky tu (van dung duoc, chi kem chon loc)" : '') . "\n";
    if ($APPLY) {
        $dblj->exec("ALTER TABLE `$t` ADD INDEX idx_sid (sid(32))");
    }
    $done++;
}
echo "Them: $done | bo qua: $skip\n";
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
