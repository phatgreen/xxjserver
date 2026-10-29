<?php
// Quên mã GM: chạy trên máy chủ  php gm/reset_code.php  → xoá mã trong bảng gm_setting, mở lại gm/gm.php tại localhost để tạo mã mới.
// Chỉ chạy được bằng dòng lệnh.
if (PHP_SAPI !== 'cli') { header('HTTP/1.1 404 Not Found'); exit; }
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dblj->exec("CREATE TABLE IF NOT EXISTS gm_setting (k VARCHAR(32) NOT NULL, v VARCHAR(255) NOT NULL DEFAULT '', PRIMARY KEY (k)) ENGINE=MyISAM DEFAULT CHARSET=utf8");
$dblj->exec("DELETE FROM gm_setting WHERE k = 'code_hash'");
echo "Da xoa ma GM. Mo http://localhost/gm/gm.php tren may chu de tao ma moi.\n";
if (GM_CODE_HASH !== '') echo "LUU Y: gm/config.php dang dien GM_CODE_HASH -> ma do van duoc dung. Xoa trang no neu muon tao ma moi.\n";
