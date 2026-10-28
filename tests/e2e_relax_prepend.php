<?php
/**
 * e2e_relax_prepend.php — CHỈ dùng cho server phụ của tests/test_e2e_full.php --relax.
 * auto_prepend_file: mở trước kết nối PDO persistent của bản chạy (cùng pdo.php => game.php
 * include_once sẽ bỏ qua) rồi đặt sql_mode KHÔNG strict cho phiên đó, giả lập MySQL production
 * không bật STRICT_TRANS_TABLES. Không đổi sql_mode toàn cục, không ảnh hưởng server 8091.
 */
if (isset($_SERVER['DOCUMENT_ROOT']) && is_file($_SERVER['DOCUMENT_ROOT'] . '/pdo.php')) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/pdo.php';
    if (isset($dblj) && $dblj) {
        $dblj->exec("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");
    }
}
