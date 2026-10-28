<?php
/**
 * test_wugong_dedup.php — cqwg() không tạo dòng võ công trùng (sql/migrate_wugong_dedup.php phải chạy trước).
 * Dùng nhân vật e2e, võ công thử wgid 2 (xóa lại sau khi chạy). Chạy: php tests/test_wugong_dedup.php
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once __DIR__ . '/../class/player.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$db = $dblj;
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else { $fails++; echo "FAIL  $name | got=" . var_export($got, true) . " exp=" . var_export($exp, true) . "\n"; }
}
$sid = str_repeat('e2', 16);
$wgid = 2;
$orig = $db->prepare("SELECT * FROM playerwugong WHERE sid = ? AND wgid = ?");
$orig->execute(array($sid, $wgid));
$origRows = $orig->fetchAll(PDO::FETCH_ASSOC);
$db->prepare("DELETE FROM playerwugong WHERE sid = ? AND wgid = ?")->execute(array($sid, $wgid));

\player\wgcx($wgid, $sid, $db);           // cache tĩnh ghi "chưa có" (tình huống gây trùng trước đây)
\player\cqwg($wgid, $sid, $db);
\player\cqwg($wgid, $sid, $db);
\player\cqwg($wgid, $sid, $db);
$st = $db->prepare("SELECT COUNT(*), MAX(CAST(wgsum AS SIGNED)) FROM playerwugong WHERE sid = ? AND wgid = ?");
$st->execute(array($sid, $wgid));
$r = $st->fetch(PDO::FETCH_NUM);
tassert('D1 3 lan nhan cung request: 1 dong', intval($r[0]), 1);
tassert('D1 bi tich cong don = 3', intval($r[1]), 3);

$st = $db->query("SELECT COUNT(*) FROM (SELECT sid, wgid FROM playerwugong GROUP BY sid, wgid HAVING COUNT(*) > 1) t");
tassert('D2 toan bang khong con nhom trung', intval($st->fetchColumn()), 0);

$db->prepare("DELETE FROM playerwugong WHERE sid = ? AND wgid = ?")->execute(array($sid, $wgid));
foreach ($origRows as $row) {
    $cols = array_keys($row);
    $db->prepare("INSERT INTO playerwugong (`" . implode('`,`', $cols) . "`) VALUES (" . rtrim(str_repeat('?,', count($cols)), ',') . ")")
       ->execute(array_values($row));
}
echo $fails ? "\n$fails FAIL\n" : "\nALL PASS\n";
exit($fails ? 1 : 0);
