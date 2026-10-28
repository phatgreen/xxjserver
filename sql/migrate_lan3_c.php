<?php
/**
 * migrate_lan3_c.php — Làn 3 (L3-C): đặt 10 NPC nhiệm vụ vào map cửa vào các khu 47–56 (mid cố định 2001, 2009, …).
 * Dữ liệu: sql/lan3_c_data.json (sinh bởi tools/gen/lan3_c_build.py). Cùng logic sql/migrate_lan2_c.php.
 * Cần chạy SAU sql/migrate_lan3.php.
 *
 *   php sql/migrate_lan3_c.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan3_c.php --apply      ghi thật
 */
$LAN_C_DATA_FILE = __DIR__ . '/lan3_c_data.json';
require __DIR__ . '/migrate_lan2_c.php';
