<?php
/**
 * migrate_kt0_bigint.php — KT-0 (docs/KHUNG_TONG_500_4300.md mục 2): đổi các cột tiền / chỉ số có thể vượt 2.147.483.647
 * sang BIGINT (giới hạn ~9,2 tỉ tỉ). Đi cùng việc chuyển máy chủ sang PHP 7.4 bản 64-bit (PHP_INT_SIZE = 8).
 *
 *   php sql/migrate_kt0_bigint.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_kt0_bigint.php --apply      ghi thật
 *
 * Sao lưu trước (Windows cmd):
 *   "D:\XXJserver\phpstudy_pro\Extensions\MySQL5.5.29\bin\mysqldump.exe" -uroot -p game > D:\XXJserver\backup_kt0.sql
 * Idempotent: cột đã là BIGINT / không tồn tại thì bỏ qua. Giữ nguyên NULL / DEFAULT / UNSIGNED của cột cũ.
 * Mỗi bảng gộp 1 câu ALTER (bảng chỉ dựng lại 1 lần).
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

// bảng => cột. Tiền cộng dồn không trần + chỉ số / EXP cấp cao (EXP lên cấp 4300 = 2,03 tỉ, sát giới hạn INT).
$COLUMNS = array(
    'game1'         => array('uyxb', 'uczb', 'uexp', 'uhp', 'umaxhp', 'ugj', 'ufy'),
    'club'          => array('clubyxb', 'clubczb', 'clubexp'),
    'playerchongwu' => array('cwhp', 'cwmaxhp', 'cwgj', 'cwfy', 'cwexp'),
    'mine_player'   => array('total_yxb', 'total_czb'),
);

$db = $dblj->query("SELECT DATABASE()")->fetchColumn();
$st = $dblj->prepare("SELECT data_type, column_type, is_nullable, column_default FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?");
$done = 0; $skip = 0;
foreach ($COLUMNS as $t => $cols) {
    $parts = array();
    foreach ($cols as $c) {
        $st->execute(array($db, $t, $c));
        $col = $st->fetch(PDO::FETCH_ASSOC);
        if (!$col) { echo "[bo qua] $t.$c: khong co cot\n"; $skip++; continue; }
        if (strtolower($col['data_type']) === 'bigint') { echo "[bo qua] $t.$c: da la BIGINT\n"; $skip++; continue; }
        $def = 'BIGINT' . (stripos($col['column_type'], 'unsigned') !== false ? ' UNSIGNED' : '');
        $def .= $col['is_nullable'] === 'YES' ? ' NULL' : ' NOT NULL';
        if ($col['column_default'] !== null && strtoupper($col['column_default']) !== 'NULL') {
            $def .= ' DEFAULT ' . $dblj->quote(trim($col['column_default'], "'"));
        }
        echo "[doi] $t.$c: {$col['column_type']} -> $def\n";
        $parts[] = "MODIFY `$c` $def";
        $done++;
    }
    if ($parts && $APPLY) {
        $dblj->exec("ALTER TABLE `$t` " . implode(', ', $parts));
    }
}
echo "Doi: $done cot | bo qua: $skip\n";
echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
