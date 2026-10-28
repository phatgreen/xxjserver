<?php
/**
 * migrate_lan9.php — Làn 9 (cấp 4001–4300, làn cuối): 6 khu (qy 107–112), 48 map id cố định 8001–8048, 6 boss khu vực.
 * Dữ liệu: sql/lan9_data.json (sinh bởi tools/gen/lan9_build.py). Quái: class/config/guaiwu_config.json (mã 1490–1591).
 * Cùng logic với sql/migrate_lan2.php (chạy lại không trùng, id map cố định, dừng nếu id bị map khác chiếm).
 * Cần chạy SAU sql/migrate_lan8.php (cửa vào là map 7080 Vô Thượng Thánh Tọa).
 *
 *   php sql/migrate_lan9.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan9.php --apply      ghi thật
 */
$LAN_DATA_FILE = __DIR__ . '/lan9_data.json';
require __DIR__ . '/migrate_lan2.php';
