<?php
/**
 * migrate_lan4_c.php — Làn 4 (L4-C): đặt 10 NPC nhiệm vụ vào map cửa vào các khu 57–66 (mid cố định 3001, 3009, …).
 * Dữ liệu: sql/lan4_c_data.json (sinh bởi tools/gen/lan4_c_build.py). Cùng logic sql/migrate_lan2_c.php.
 * Cần chạy SAU sql/migrate_lan4.php.
 *
 *   php sql/migrate_lan4_c.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan4_c.php --apply      ghi thật
 */
$LAN_C_DATA_FILE = __DIR__ . '/lan4_c_data.json';
require __DIR__ . '/migrate_lan2_c.php';
