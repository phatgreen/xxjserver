<?php
/**
 * migrate_daoju_uid.php — sửa dòng playerdaoju có uid = 0 (BUG-02, docs/BAO_CAO_TEST_TOAN_BO_2026-10-29.md).
 *
 * Nguyên nhân: Hầm Mỏ, Tỏa Yêu Tháp, Sào Thú, Chế Tạo, Luyện Đan, kho Tông Môn INSERT playerdaoju không kèm uid,
 * khóa chính là (uid, djid) => cả server chỉ có 1 dòng (0, djid); người nhận sau bị trùng khóa, mất đồ. Code đã sửa (kèm uid).
 *
 * Việc làm: mỗi dòng uid = 0 có sid khớp nhân vật trong game1:
 *   - chủ chưa có dòng (uid thật, djid)  => gán uid thật
 *   - chủ đã có dòng (uid thật, djid)    => cộng djsum vào dòng đó, xóa dòng uid = 0
 * Dòng có sid không còn nhân vật => giữ nguyên, chỉ in ra.
 *
 *   php sql/migrate_daoju_uid.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_daoju_uid.php --apply      ghi thật
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

$rows = $dblj->query("SELECT d.djid, d.djsum, d.sid, g.uid AS real_uid
                      FROM playerdaoju d LEFT JOIN game1 g ON g.sid = d.sid
                      WHERE d.uid = 0")->fetchAll(PDO::FETCH_ASSOC);
$fix = 0; $merge = 0; $orphan = 0;
foreach ($rows as $r) {
    $djid = intval($r['djid']);
    $sum  = intval($r['djsum']);
    if ($r['real_uid'] === null) {
        $orphan++;
        echo "[bo qua] djid $djid: sid " . substr($r['sid'], 0, 8) . "... khong con nhan vat\n";
        continue;
    }
    $uid = intval($r['real_uid']);
    $st = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE uid = ? AND djid = ?");
    $st->execute(array($uid, $djid));
    $have = $st->fetchColumn();
    if ($have === false) {
        echo "[gan uid] djid $djid ($sum cai) -> uid $uid\n";
        if ($APPLY) {
            $dblj->prepare("UPDATE playerdaoju SET uid = ? WHERE uid = 0 AND djid = ? AND sid = ?")->execute(array($uid, $djid, $r['sid']));
        }
        $fix++;
    } else {
        echo "[gop] djid $djid: uid $uid co $have + $sum cai (dong uid 0) => " . (intval($have) + $sum) . "\n";
        if ($APPLY) {
            $dblj->beginTransaction();
            $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + ? WHERE uid = ? AND djid = ?")->execute(array($sum, $uid, $djid));
            $dblj->prepare("DELETE FROM playerdaoju WHERE uid = 0 AND djid = ? AND sid = ?")->execute(array($djid, $r['sid']));
            $dblj->commit();
        }
        $merge++;
    }
}
if (!$rows) echo "[bo qua] khong co dong uid = 0\n";
echo "Gan uid $fix, gop $merge, mo coi $orphan\n";
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
