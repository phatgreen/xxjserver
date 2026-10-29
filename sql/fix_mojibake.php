<?php
/**
 * fix_mojibake.php — quét & sửa chữ lỗi font kiểu "Thiáº¿t bá»‹" (UTF-8 bị mã hoá 2 lần qua latin1, thường do nhập .sql
 * hoặc ghi DB bằng kết nối không SET NAMES utf8). Chỉ xét cột chữ có charset utf8/utf8mb4.
 *
 * Chỉ sửa giá trị CHẮC CHẮN lỗi: mọi ký tự đều đổi ngược được về 1 byte latin1 VÀ dãy byte thu được là UTF-8 hợp lệ có dấu.
 * Chữ Việt đúng (vd "Phát Đạt", "Tiên") không bao giờ thoả điều kiện này ⇒ không bị đụng.
 *
 *   php sql/fix_mojibake.php --dry-run              liệt kê sẽ sửa gì, KHÔNG ghi DB
 *   php sql/fix_mojibake.php --apply                sửa thật, lưu bản cũ vào runtime/mojibake_backup_*.json
 *   php sql/fix_mojibake.php --restore=<file.json>  trả lại giá trị cũ từ file sao lưu
 *
 * Sao lưu thêm trước khi --apply (Windows cmd): mysqldump.exe -uroot -p --default-character-set=utf8mb4 game > D:\XXJserver\backup.sql
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj (SET NAMES utf8mb4)
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
$RESTORE = '';
foreach ($argv as $a) if (strpos($a, '--restore=') === 0) $RESTORE = substr($a, 10);

if ($RESTORE !== '') {
    $data = json_decode((string)@file_get_contents($RESTORE), true);
    if (!is_array($data)) { echo "Khong doc duoc file $RESTORE\n"; exit(2); }
    $n = 0;
    foreach ($data as $r) {
        $st = $dblj->prepare("UPDATE `{$r['t']}` SET `{$r['c']}` = ? WHERE BINARY `{$r['c']}` = ?");
        $st->execute(array($r['old'], $r['new']));
        $n += $st->rowCount();
    }
    echo "Da tra lai $n dong tu $RESTORE\n";
    exit(0);
}
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply (hoac --restore=file)\n"; exit(2); }

// Bảng ngược "ký tự → byte" theo latin1 của MySQL (= cp1252, riêng 0x81 0x8D 0x8F 0x90 0x9D giữ nguyên U+0081…)
$CP = array(0x80 => 0x20AC, 0x82 => 0x201A, 0x83 => 0x0192, 0x84 => 0x201E, 0x85 => 0x2026, 0x86 => 0x2020, 0x87 => 0x2021, 0x88 => 0x02C6,
    0x89 => 0x2030, 0x8A => 0x0160, 0x8B => 0x2039, 0x8C => 0x0152, 0x8E => 0x017D, 0x91 => 0x2018, 0x92 => 0x2019, 0x93 => 0x201C,
    0x94 => 0x201D, 0x95 => 0x2022, 0x96 => 0x2013, 0x97 => 0x2014, 0x98 => 0x02DC, 0x99 => 0x2122, 0x9A => 0x0161, 0x9B => 0x203A,
    0x9C => 0x0153, 0x9E => 0x017E, 0x9F => 0x0178);
$REV = array();
for ($b = 0; $b < 256; $b++) $REV[isset($CP[$b]) ? $CP[$b] : $b] = chr($b);

function mj_ord($ch) {
    $c = array_values(unpack('C*', $ch));
    $n = count($c);
    if ($n === 1) return $c[0];
    if ($n === 2) return (($c[0] & 0x1F) << 6) | ($c[1] & 0x3F);
    if ($n === 3) return (($c[0] & 0x0F) << 12) | (($c[1] & 0x3F) << 6) | ($c[2] & 0x3F);
    return (($c[0] & 0x07) << 18) | (($c[1] & 0x3F) << 12) | (($c[2] & 0x3F) << 6) | ($c[3] & 0x3F);
}
// Trả chuỗi đã sửa, hoặc false nếu không phải lỗi mã hoá 2 lần
function mj_fix_once($s, $REV) {
    if (!preg_match('/[\x80-\xFF]/', $s) || !preg_match('//u', $s)) return false;
    $out = '';
    foreach (preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
        $o = mj_ord($ch);
        if (!isset($REV[$o])) return false;   // có ký tự ngoài latin1 (vd "ạ", "Đ") ⇒ chữ đúng, không sửa
        $out .= $REV[$o];
    }
    if ($out === $s || !preg_match('/[\x80-\xFF]/', $out) || !preg_match('//u', $out)) return false;
    return $out;
}
function mj_fix($s, $REV) {
    $cur = $s;
    for ($i = 0; $i < 3; $i++) { $f = mj_fix_once($cur, $REV); if ($f === false) break; $cur = $f; }   // có thể bị mã hoá 2–3 lần
    return $cur === $s ? false : $cur;
}

$db = $dblj->query("SELECT DATABASE()")->fetchColumn();
$st = $dblj->prepare("SELECT c.table_name, c.column_name FROM information_schema.columns c
    JOIN information_schema.tables t ON t.table_schema = c.table_schema AND t.table_name = c.table_name AND t.table_type = 'BASE TABLE'
    WHERE c.table_schema = ? AND c.data_type IN ('char','varchar','tinytext','text','mediumtext','longtext')
      AND c.character_set_name IN ('utf8','utf8mb4') ORDER BY c.table_name, c.ordinal_position");
$st->execute(array($db));
$cols = $st->fetchAll(PDO::FETCH_NUM);

$backup = array();
$total = 0;
foreach ($cols as $tc) {
    list($t, $c) = $tc;
    $vals = $dblj->query("SELECT DISTINCT CAST(`$c` AS BINARY) FROM `$t` WHERE `$c` IS NOT NULL AND LENGTH(`$c`) <> CHAR_LENGTH(`$c`)")->fetchAll(PDO::FETCH_COLUMN);
    $fix = array();
    foreach ($vals as $v) { $f = mj_fix((string)$v, $REV); if ($f !== false) $fix[] = array($v, $f); }
    if (!$fix) continue;
    $rows = 0;
    foreach ($fix as $p) {
        $q = $dblj->prepare("SELECT COUNT(*) FROM `$t` WHERE BINARY `$c` = ?");
        $q->execute(array($p[0]));
        $rows += intval($q->fetchColumn());
    }
    $total += $rows;
    echo "[$t.$c] " . count($fix) . " gia tri / $rows dong\n";
    foreach (array_slice($fix, 0, 3) as $p) echo "    " . mb_substr($p[0], 0, 60, 'UTF-8') . "  =>  " . mb_substr($p[1], 0, 60, 'UTF-8') . "\n";
    if ($APPLY) {
        foreach ($fix as $p) {
            $dblj->prepare("UPDATE `$t` SET `$c` = ? WHERE BINARY `$c` = ?")->execute(array($p[1], $p[0]));
            $backup[] = array('t' => $t, 'c' => $c, 'old' => $p[0], 'new' => $p[1]);
        }
    }
}
if ($total === 0) { echo "Khong co chu loi font nao. DB sach.\n"; exit(0); }
if ($APPLY) {
    $dir = $ROOT . '/runtime';
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
    $file = $dir . '/mojibake_backup_' . date('Ymd_His') . '.json';
    file_put_contents($file, json_encode($backup, JSON_UNESCAPED_UNICODE));
    echo "DA SUA $total dong. Ban cu luu tai: $file  (tra lai: php sql/fix_mojibake.php --restore=$file)\n";
} else {
    echo "Tong $total dong se duoc sua. Chay lai voi --apply de sua that.\n";
}
