<?php
/**
 * migrate_lan7_c.php — Làn 7 (L7-C): đặt 10 NPC nhiệm vụ vào map cửa vào các khu 87–96 (mid cố định 6001, 6009, …).
 * Dữ liệu: sql/lan7_c_data.json (sinh bởi tools/gen/lan7_c_build.py). Cùng logic sql/migrate_lan2_c.php.
 * Cần chạy SAU sql/migrate_lan7.php.
 *
 *   php sql/migrate_lan7_c.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan7_c.php --apply      ghi thật
 */
$LAN_C_DATA_FILE = __DIR__ . '/lan7_c_data.json';
require __DIR__ . '/migrate_lan2_c.php';
