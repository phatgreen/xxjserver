<?php
/**
 * migrate_lan8_c.php — Làn 8 (L8-C): đặt 10 NPC nhiệm vụ vào map cửa vào các khu 97–106 (mid cố định 7001, 7009, …).
 * Dữ liệu: sql/lan8_c_data.json (sinh bởi tools/gen/lan8_c_build.py). Cùng logic sql/migrate_lan2_c.php.
 * Cần chạy SAU sql/migrate_lan8.php.
 *
 *   php sql/migrate_lan8_c.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan8_c.php --apply      ghi thật
 */
$LAN_C_DATA_FILE = __DIR__ . '/lan8_c_data.json';
require __DIR__ . '/migrate_lan2_c.php';
