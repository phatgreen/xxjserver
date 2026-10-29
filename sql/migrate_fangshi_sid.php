<?php
/**
 * migrate_fangshi_sid.php — đổi fangshi_zb.sid / fangshi_sd.sid từ INT sang VARCHAR(32) (BUG-05, docs/BAO_CAO_TEST_TOAN_BO_2026-10-29.md).
 *
 * Nguyên nhân: sid là chuỗi hex 32 ký tự; cột INT => strict báo 1265 (treo bán hỏng), không strict thì sid bị cắt thành số rác.
 * Cột này chỉ được ghi (game/zbinfo.php treo bán), không nơi nào đọc lại => đổi kiểu không ảnh hưởng chỗ khác.
 *
 *   php sql/migrate_fangshi_sid.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_fangshi_sid.php --apply      ghi thật
 *
 * Idempotent: cột đã là varchar thì bỏ qua.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$db = $dblj->query("SELECT DATABASE()")->fetchColumn();
foreach (array('fangshi_zb', 'fangshi_sd') as $t) {
    $st = $dblj->prepare("SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'sid'");
    $st->execute(array($db, $t));
    $type = $st->fetchColumn();
    if ($type === false) { echo "[bo qua] $t khong co cot sid\n"; continue; }
    if (strtolower($type) === 'varchar') { echo "[bo qua] $t.sid da la varchar\n"; continue; }
    echo "[sua] $t.sid ($type) => VARCHAR(32) NOT NULL DEFAULT ''\n";
    if ($APPLY) $dblj->exec("ALTER TABLE `$t` MODIFY `sid` VARCHAR(32) NOT NULL DEFAULT ''");
}
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
