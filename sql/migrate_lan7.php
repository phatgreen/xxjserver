<?php
/**
 * migrate_lan7.php — Làn 7 (cấp 3001–3500): 10 khu (qy 87–96), 80 map id cố định 6001–6080, 10 boss khu vực.
 * Dữ liệu: sql/lan7_data.json (sinh bởi tools/gen/lan7_build.py). Quái: class/config/guaiwu_config.json (mã 1150–1319).
 * Cùng logic với sql/migrate_lan2.php (chạy lại không trùng, id map cố định, dừng nếu id bị map khác chiếm).
 * Cần chạy SAU sql/migrate_lan6.php (cửa vào là map 5080 Tinh Khung Chí Cao Điện).
 *
 *   php sql/migrate_lan7.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan7.php --apply      ghi thật
 */
$LAN_DATA_FILE = __DIR__ . '/lan7_data.json';
require __DIR__ . '/migrate_lan2.php';
