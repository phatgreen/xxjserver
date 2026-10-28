<?php
/**
 * migrate_lan4.php — Làn 4 (cấp 1501–2000): 10 khu (qy 57–66), 80 map id cố định 3001–3080, 10 boss khu vực.
 * Dữ liệu: sql/lan4_data.json (sinh bởi tools/gen/lan4_build.py). Quái: class/config/guaiwu_config.json (mã 640–809).
 * Cùng logic với sql/migrate_lan2.php (chạy lại không trùng, id map cố định, dừng nếu id bị map khác chiếm).
 * Cần chạy SAU sql/migrate_lan3.php (cửa vào là map 2080 Mẫu Thụ Hạ).
 *
 *   php sql/migrate_lan4.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan4.php --apply      ghi thật
 */
$LAN_DATA_FILE = __DIR__ . '/lan4_data.json';
require __DIR__ . '/migrate_lan2.php';
