<?php
/**
 * migrate_tanthu.php — Quà Tân Thủ 3 ngày (docs/DESIGN_TANTHU_3NGAY.md):
 *   1) bảng player_tanthu (InnoDB, cùng engine game1 để trừ phí đột phá 2 bảng trong 1 câu): sid, created_at (ngày tạo nhân vật),
 *      lt (Túi Linh Thạch Tân Thủ — chỉ trả phí đột phá), x2_until (hạn Thẻ ×2 Hấp Thụ). KHÔNG thêm cột vào game1: game1 InnoDB
 *      Compact đã sát trần cỡ dòng (ALTER báo "Row size too large"). Nhân vật cũ không có dòng ⇒ không nhận quà tân thủ.
 *   2) duihuan.dh_maxday / dh_maxlv (điều kiện giftcode: ngày tuổi tối đa / cấp tối đa; 0 = không giới hạn)
 *   3) bảng player_tanthu_claim (điểm danh + mốc trực tuyến tân thủ, chống nhận lặp)
 *   4) 2 giftcode: TANTHU (50 Tu Vi Đan + 3 Túi LT), NHOMTELE (Thẻ ×2 + 30 Tu Vi Đan + 2 Túi LT) — ngày tuổi <= 3, cấp <= 300.
 *      Đổi tên mã: sửa cột dhm trong bảng duihuan (chữ thường).
 *
 *   php sql/migrate_tanthu.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_tanthu.php --apply      ghi thật
 *
 * Sao lưu trước (Windows cmd): mysqldump.exe -uroot -p game > D:\XXJserver\backup.sql
 * Idempotent: cột / bảng / mã đã có thì bỏ qua.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$COLUMNS = array(
    array('duihuan', 'dh_maxday', "INT(11) NOT NULL DEFAULT 0"),
    array('duihuan', 'dh_maxlv', "INT(11) NOT NULL DEFAULT 0"),
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

$TABLES = array(
    'player_tanthu' => "CREATE TABLE player_tanthu (
        sid VARCHAR(32) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00',
        lt BIGINT(20) NOT NULL DEFAULT 0,
        x2_until DATETIME DEFAULT NULL,
        PRIMARY KEY (sid)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
    'player_tanthu_claim' => "CREATE TABLE player_tanthu_claim (
        sid VARCHAR(32) NOT NULL DEFAULT '',
        day TINYINT(4) NOT NULL DEFAULT 0,
        kind VARCHAR(8) NOT NULL DEFAULT '',
        created_at DATETIME DEFAULT NULL,
        PRIMARY KEY (sid, day, kind)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8",
);
foreach ($TABLES as $t => $ddl) {
    $st = $dblj->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
    $st->execute(array($db, $t));
    if (intval($st->fetchColumn())) { echo "[bo qua] bang $t: da co\n"; continue; }
    echo "[tao] bang $t\n";
    if ($APPLY) $dblj->exec($ddl);
}
echo "[ghi chu] " . intval($dblj->query("SELECT COUNT(*) FROM game1")->fetchColumn()) . " nhan vat dang co la nhan vat cu: khong co dong player_tanthu => khong nhan qua tan thu\n";

$CODES = array(
    array('tanthu', 'Quà Tân Thủ', '118|50,119|3'),
    array('nhomtele', 'Quà Nhóm Tele', '120|1,118|30,119|2'),
);
foreach ($CODES as $c) {
    $st = $dblj->prepare("SELECT COUNT(*) FROM duihuan WHERE dhm = ?");
    $st->execute(array($c[0]));
    if (intval($st->fetchColumn())) { echo "[bo qua] giftcode {$c[0]}: da co\n"; continue; }
    echo "[them] giftcode {$c[0]} ({$c[1]}): {$c[2]}, ngay tuoi <= 3, cap <= 300\n";
    if ($APPLY) {
        $dblj->prepare("INSERT INTO duihuan (dhm, dhzb, dhdj, dhyp, dhyxb, dhczb, dhname, dhexp, dh_maxday, dh_maxlv) VALUES (?, '', ?, '', 0, 0, ?, 0, 3, 300)")
             ->execute(array($c[0], $c[2], $c[1]));
    }
}
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
