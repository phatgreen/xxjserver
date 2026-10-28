<?php
/**
 * migrate_tongmon.php — Tông Môn (docs/DESIGN_TONGMON_CANBANG.md mục 3.1):
 *   1) thêm cột lưu mốc chống nhận lặp (trước đây nằm trong $_SESSION, đăng nhập lại là nhận lại được):
 *      clubplayer.online_claim_at (mốc nhận EXP online), clubplayer.quest_bonus_date (thưởng hoàn thành nhiệm vụ ngày),
 *      club.boss_date (boss tông môn 1 lần/ngày/tông môn)
 *   2) đặt lại clubexp = 0 cho tông môn dính lỗi online lần đầu (mốc 0 => cộng floor(time()/3600) × 50 ≈ 24,8 triệu EXP).
 *      Nhận diện: clubexp >= 10.000.000 (luật cũ không thể đạt hợp lệ khi game chưa mở).
 *
 *   php sql/migrate_tongmon.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_tongmon.php --apply      ghi thật
 *
 * Sao lưu trước (Windows cmd): mysqldump.exe -uroot -p game > D:\XXJserver\backup.sql
 * Idempotent: cột đã có thì bỏ qua; chạy lại không đổi gì thêm.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$EXP_BUG_MIN = 10000000;
$COLUMNS = array(
    array('clubplayer', 'online_claim_at', "INT(11) NOT NULL DEFAULT 0"),
    array('clubplayer', 'quest_bonus_date', "VARCHAR(10) NOT NULL DEFAULT ''"),
    array('club', 'boss_date', "VARCHAR(10) NOT NULL DEFAULT ''"),
);

$db = $dblj->query("SELECT DATABASE()")->fetchColumn();
foreach ($COLUMNS as $c) {
    list($t, $col, $def) = $c;
    $st = $dblj->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
    $st->execute(array($db, $t, $col));
    if (intval($st->fetchColumn())) { echo "[bo qua] $t.$col: da co\n"; continue; }
    echo "[them] $t.$col $def\n";
    if ($APPLY) $dblj->exec("ALTER TABLE `$t` ADD COLUMN `$col` $def");
}

$st = $dblj->prepare("SELECT clubid, clubname, clublv, clubexp FROM club WHERE clubexp >= ?");
$st->execute(array($EXP_BUG_MIN));
$bad = $st->fetchAll(PDO::FETCH_ASSOC);
foreach ($bad as $r) {
    echo "[dat lai] clubid {$r['clubid']} ({$r['clubname']}, cap {$r['clublv']}): clubexp {$r['clubexp']} -> 0\n";
}
if (!$bad) echo "[bo qua] khong co tong mon nao clubexp >= $EXP_BUG_MIN\n";
if ($APPLY && $bad) {
    $up = $dblj->prepare("UPDATE club SET clubexp = 0 WHERE clubexp >= ?");
    $up->execute(array($EXP_BUG_MIN));
}
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
