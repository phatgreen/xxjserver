<?php
/**
 * migrate_default_cols.php — gắn giá trị mặc định cho cột NOT NULL thiếu DEFAULT (BUG-01, docs/BAO_CAO_TEST_TOAN_BO_2026-10-29.md).
 *
 * Nguyên nhân: nhiều câu INSERT (addzb, adddj, addyaodan, tạo nhân vật, chat, linh sủng, sinh quái, luyện đan…) không truyền
 * các cột NOT NULL không có DEFAULT. MySQL bật STRICT_TRANS_TABLES (mặc định từ 5.7) từ chối cả câu => trừ tiền mà không có đồ.
 *
 * Quy tắc (giữ nguyên kiểu, bảng mã, comment; KHÔNG đổi dữ liệu đang có):
 *   - số (int, bigint, decimal, float…)      => NOT NULL DEFAULT 0
 *   - char / varchar / enum / set            => NOT NULL DEFAULT ''
 *   - text / blob / date / time / datetime   => NULL (MySQL 5.5 không cho DEFAULT với kiểu này)
 *   - BỎ QUA cột thuộc PRIMARY KEY và cột auto_increment (khóa phải do code truyền).
 *
 *   php sql/migrate_default_cols.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_default_cols.php --apply      ghi thật
 *
 * Sao lưu trước (Windows cmd): mysqldump.exe -uroot -p game > D:\XXJserver\backup.sql
 * Idempotent: chạy lại không còn cột nào cần sửa.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$db = $dblj->query("SELECT DATABASE()")->fetchColumn();
$st = $dblj->prepare("SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, DATA_TYPE, CHARACTER_SET_NAME, COLLATION_NAME, COLUMN_COMMENT
                      FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = ? AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
                        AND COLUMN_KEY <> 'PRI' AND EXTRA NOT LIKE '%auto_increment%'
                      ORDER BY TABLE_NAME, ORDINAL_POSITION");
$st->execute(array($db));
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

$num  = array('tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'decimal', 'numeric', 'float', 'double', 'real', 'bit');
$str  = array('char', 'varchar', 'enum', 'set', 'binary', 'varbinary');
// game1 đã chạm giới hạn kích thước dòng InnoDB (lỗi 1118 khi MODIFY); 3 cột này câu tạo nhân vật (game.php cjplayer) luôn truyền.
$keep = array('game1.sid', 'game1.token', 'game1.endtime');
$done = 0; $skip = 0;
foreach ($rows as $r) {
    if (in_array($r['TABLE_NAME'] . '.' . $r['COLUMN_NAME'], $keep, true)) continue;
    $t = strtolower($r['DATA_TYPE']);
    if (in_array($t, $num, true))      $tail = "NOT NULL DEFAULT 0";
    elseif (in_array($t, $str, true))  $tail = "NOT NULL DEFAULT ''";
    else                               $tail = "NULL";   // text / blob / ngày giờ
    $cs = $r['CHARACTER_SET_NAME'] ? ' CHARACTER SET ' . $r['CHARACTER_SET_NAME'] . ' COLLATE ' . $r['COLLATION_NAME'] : '';
    $cm = $r['COLUMN_COMMENT'] !== '' ? ' COMMENT ' . $dblj->quote($r['COLUMN_COMMENT']) : '';
    $sql = 'ALTER TABLE `' . $r['TABLE_NAME'] . '` MODIFY `' . $r['COLUMN_NAME'] . '` ' . $r['COLUMN_TYPE'] . $cs . ' ' . $tail . $cm;
    echo "[sua] {$r['TABLE_NAME']}.{$r['COLUMN_NAME']} ({$r['COLUMN_TYPE']}) => $tail\n";
    if ($APPLY) {
        try { $dblj->exec($sql); $done++; }
        catch (Exception $e) { $skip++; echo "  [LOI] " . $e->getMessage() . "\n"; }
    } else {
        $done++;
    }
}
if (!$rows) echo "[bo qua] khong con cot NOT NULL thieu DEFAULT\n";
echo "Tong: $done cot" . ($skip ? ", loi $skip" : '') . "\n";
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
