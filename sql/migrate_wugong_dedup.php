<?php
/**
 * migrate_wugong_dedup.php — gộp dòng võ công trùng trong playerwugong + chặn trùng về sau.
 *
 * Nguyên nhân: bảng không có khóa duy nhất (sid, wgid); cqwg() "đọc rồi mới thêm" nên mỗi lần đọc trượt
 * (2 request mua cùng lúc / cache tĩnh của wgcx() còn giữ "chưa có") lại thêm 1 dòng mới. Sau đó mọi UPDATE
 * "WHERE wgid = ? AND sid = ?" (nâng cấp, trừ bí tịch) chạy trên TẤT CẢ dòng trùng => bí tịch bị trừ âm.
 *
 * Gộp mỗi nhóm (sid, wgid): giữ dòng cấp cao nhất (rồi kinh nghiệm, rồi Id nhỏ nhất);
 * bí tịch = max(0, giữ) + tổng phần dương của các dòng bị xóa. Sau đó: bí tịch âm còn sót => 0,
 * thêm UNIQUE KEY uniq_sid_wgid (sid(32), wgid).
 *
 *   php sql/migrate_wugong_dedup.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_wugong_dedup.php --apply      ghi thật
 *
 * Sao lưu trước (Windows cmd): mysqldump.exe -uroot -p game > D:\XXJserver\backup.sql
 * Idempotent: chạy lại không đổi gì thêm.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$groups = $dblj->query("SELECT sid, wgid, COUNT(*) n FROM playerwugong GROUP BY sid, wgid HAVING n > 1")->fetchAll(PDO::FETCH_ASSOC);
$merged = 0; $deleted = 0;
foreach ($groups as $g) {
    $st = $dblj->prepare("SELECT Id, wgdj, wgxl, wgsum FROM playerwugong WHERE sid = ? AND wgid = ?
                          ORDER BY CAST(wgdj AS SIGNED) DESC, CAST(wgxl AS SIGNED) DESC, Id ASC");
    $st->execute(array($g['sid'], $g['wgid']));
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    $keep = array_shift($rows);
    $sum = max(0, intval($keep['wgsum']));
    $ids = array();
    foreach ($rows as $r) {
        $sum += max(0, intval($r['wgsum']));
        $ids[] = intval($r['Id']);
    }
    echo "[gop] sid " . substr($g['sid'], 0, 8) . "... wgid {$g['wgid']}: giu Id {$keep['Id']} (cap {$keep['wgdj']}), xoa Id "
        . implode(',', $ids) . ", bi tich {$keep['wgsum']} -> $sum\n";
    if ($APPLY) {
        $dblj->prepare("UPDATE playerwugong SET wgsum = ? WHERE Id = ?")->execute(array($sum, intval($keep['Id'])));
        $dblj->exec("DELETE FROM playerwugong WHERE Id IN (" . implode(',', $ids) . ")");
    }
    $merged++; $deleted += count($ids);
}
if (!$groups) echo "[bo qua] khong co dong trung\n";

$neg = intval($dblj->query("SELECT COUNT(*) FROM playerwugong WHERE CAST(wgsum AS SIGNED) < 0")->fetchColumn());
echo $neg ? "[sua] $neg dong bi tich am -> 0\n" : "[bo qua] khong co bi tich am\n";
if ($APPLY && $neg) $dblj->exec("UPDATE playerwugong SET wgsum = 0 WHERE CAST(wgsum AS SIGNED) < 0");

$db = $dblj->query("SELECT DATABASE()")->fetchColumn();
$st = $dblj->prepare("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = ? AND table_name = 'playerwugong' AND index_name = 'uniq_sid_wgid'");
$st->execute(array($db));
if (intval($st->fetchColumn())) {
    echo "[bo qua] da co UNIQUE uniq_sid_wgid\n";
} else {
    echo "[them] UNIQUE KEY uniq_sid_wgid (sid(32), wgid)\n";
    if ($APPLY) $dblj->exec("ALTER TABLE playerwugong ADD UNIQUE KEY uniq_sid_wgid (sid(32), wgid)");
}
echo "Gop $merged nhom, xoa $deleted dong\n";
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
