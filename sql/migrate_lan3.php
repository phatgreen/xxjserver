<?php
/**
 * migrate_lan3.php — Làn 3 (cấp 1001–1500): 10 khu (qy 47–56), 80 map id cố định 2001–2080, 10 boss khu vực.
 * Dữ liệu: sql/lan3_data.json (sinh bởi tools/gen/lan3_build.py). Quái: class/config/guaiwu_config.json (mã 470–639).
 * Cùng logic với sql/migrate_lan2.php (chạy lại không trùng, id map cố định, dừng nếu id bị map khác chiếm).
 * Cần chạy SAU sql/migrate_lan2.php (cửa vào là map 1080 Thiên Cung Chính Điện).
 *
 *   php sql/migrate_lan3.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan3.php --apply      ghi thật
 */
$LAN_DATA_FILE = __DIR__ . '/lan3_data.json';
require __DIR__ . '/migrate_lan2.php';
