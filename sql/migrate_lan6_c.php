<?php
/**
 * migrate_lan6_c.php — Làn 6 (L6-C): đặt 10 NPC nhiệm vụ vào map cửa vào các khu 77–86 (mid cố định 5001, 5009, …).
 * Dữ liệu: sql/lan6_c_data.json (sinh bởi tools/gen/lan6_c_build.py). Cùng logic sql/migrate_lan2_c.php.
 * Cần chạy SAU sql/migrate_lan6.php.
 *
 *   php sql/migrate_lan6_c.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan6_c.php --apply      ghi thật
 */
$LAN_C_DATA_FILE = __DIR__ . '/lan6_c_data.json';
require __DIR__ . '/migrate_lan2_c.php';
