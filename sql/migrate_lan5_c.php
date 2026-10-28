<?php
/**
 * migrate_lan5_c.php — Làn 5 (L5-C): đặt 10 NPC nhiệm vụ vào map cửa vào các khu 67–76 (mid cố định 4001, 4009, …).
 * Dữ liệu: sql/lan5_c_data.json (sinh bởi tools/gen/lan5_c_build.py). Cùng logic sql/migrate_lan2_c.php.
 * Cần chạy SAU sql/migrate_lan5.php.
 *
 *   php sql/migrate_lan5_c.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan5_c.php --apply      ghi thật
 */
$LAN_C_DATA_FILE = __DIR__ . '/lan5_c_data.json';
require __DIR__ . '/migrate_lan2_c.php';
