<?php
/**
 * migrate_giftcode_tanthu2.php — chia giftcode tân thủ: TANTHU cũ cho 50 Tân Thủ Tu Vi Đan một lần ⇒ tách thành 5 mã, mỗi mã 10 viên
 * kèm ít dược phẩm / vật phẩm cơ bản + chút linh thạch. Tổng Tu Vi Đan vẫn 50 (khớp mô phỏng DESIGN_TANTHU_3NGAY.md), nhập đủ 5 mã mới đủ.
 * Xoá 5 mã cũ lỗi font: vip999, vip888, vip666, vip666888, vip666sss (giữ duihuan_log như nút Xoá của trang GM).
 * Mọi mã mới: ngày tuổi <= 3, cấp <= 300 (giống TANTHU / NHOMTELE). NHOMTELE giữ nguyên.
 *
 *   php sql/migrate_giftcode_tanthu2.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_giftcode_tanthu2.php --apply      ghi thật
 * Cần chạy sau sql/migrate_tanthu.php (cột dh_maxday / dh_maxlv). Idempotent: chạy lại chỉ cập nhật đúng nội dung dưới.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$OLD = array('vip999', 'vip888', 'vip666', 'vip666888', 'vip666sss');
// mã, tên, đạo cụ "id|n,…", dược phẩm "id|n,…", linh thạch
// 118 Tân Thủ Tu Vi Đan · 119 Túi Linh Thạch Tân Thủ · 1 Cường Hóa Thạch · 30 Thảo Dược Phổ Thông · 38 Thẻ Cường Hóa ATK +5
// yp 17 Thể Lực Đan · 6 Hoàn Nguyên Đan · 7 Hồi Huyết Tán · 8 Hồi Xuân Thang
$CODES = array(
    array('tanthu',   'Quà Tân Thủ',   '118|10,119|3',       '17|2,6|10', 2000),
    array('nhapmon',  'Quà Nhập Môn',  '118|10,1|10',        '17|2,6|20', 2000),
    array('luyenkhi', 'Quà Luyện Khí', '118|10,30|10',       '17|2,7|10', 2000),
    array('tuchan',   'Quà Tu Chân',   '118|10,1|10,38|1',   '17|2,7|10', 2000),
    array('daotam',   'Quà Đạo Tâm',   '118|10,1|20',        '17|2,8|10', 3000),
);

foreach ($OLD as $m) {
    $st = $dblj->prepare("SELECT COUNT(*) FROM duihuan WHERE dhm = ?");
    $st->execute(array($m));
    if (!intval($st->fetchColumn())) { echo "[bo qua] xoa $m: khong co\n"; continue; }
    echo "[xoa] giftcode cu $m\n";
    if ($APPLY) $dblj->prepare("DELETE FROM duihuan WHERE dhm = ?")->execute(array($m));
}
foreach ($CODES as $c) {
    list($m, $name, $dj, $yp, $lt) = $c;
    $st = $dblj->prepare("SELECT dhid FROM duihuan WHERE dhm = ?");
    $st->execute(array($m));
    $id = $st->fetchColumn();
    echo ($id ? "[sua] " : "[them] ") . "$m ($name): dao cu $dj, duoc pham $yp, $lt LT, ngay tuoi <= 3, cap <= 300\n";
    if (!$APPLY) continue;
    if ($id) {
        $dblj->prepare("UPDATE duihuan SET dhname = ?, dhzb = '', dhdj = ?, dhyp = ?, dhyxb = ?, dhczb = 0, dhexp = 0, dh_maxday = 3, dh_maxlv = 300 WHERE dhid = ?")
             ->execute(array($name, $dj, $yp, $lt, $id));
    } else {
        $dblj->prepare("INSERT INTO duihuan (dhm, dhzb, dhdj, dhyp, dhyxb, dhczb, dhname, dhexp, dh_maxday, dh_maxlv) VALUES (?, '', ?, ?, ?, 0, ?, 0, 3, 300)")
             ->execute(array($m, $dj, $yp, $lt, $name));
    }
}
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
