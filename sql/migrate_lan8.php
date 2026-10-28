<?php
/**
 * migrate_lan8.php — Làn 8 (cấp 3501–4000): 10 khu (qy 97–106), 80 map id cố định 7001–7080, 10 boss khu vực.
 * Dữ liệu: sql/lan8_data.json (sinh bởi tools/gen/lan8_build.py). Quái: class/config/guaiwu_config.json (mã 1320–1489).
 * Cùng logic với sql/migrate_lan2.php (chạy lại không trùng, id map cố định, dừng nếu id bị map khác chiếm).
 * Cần chạy SAU sql/migrate_lan7.php (cửa vào là map 6080 Trận Tâm Chí Cao Đài).
 *
 *   php sql/migrate_lan8.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan8.php --apply      ghi thật
 */
$LAN_DATA_FILE = __DIR__ . '/lan8_data.json';
require __DIR__ . '/migrate_lan2.php';
