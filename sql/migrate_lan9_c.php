<?php
/**
 * migrate_lan9_c.php — Làn 9 (L9-C): đặt 6 NPC nhiệm vụ vào map cửa vào các khu 107–112 (mid cố định 8001, 8009, …).
 * Dữ liệu: sql/lan9_c_data.json (sinh bởi tools/gen/lan9_c_build.py). Cùng logic sql/migrate_lan2_c.php.
 * Cần chạy SAU sql/migrate_lan9.php.
 *
 *   php sql/migrate_lan9_c.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan9_c.php --apply      ghi thật
 */
$LAN_C_DATA_FILE = __DIR__ . '/lan9_c_data.json';
require __DIR__ . '/migrate_lan2_c.php';
