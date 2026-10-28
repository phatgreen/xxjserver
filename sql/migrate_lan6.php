<?php
/**
 * migrate_lan6.php — Làn 6 (cấp 2501–3000): 10 khu (qy 77–86), 80 map id cố định 5001–5080, 10 boss khu vực.
 * Dữ liệu: sql/lan6_data.json (sinh bởi tools/gen/lan6_build.py). Quái: class/config/guaiwu_config.json (mã 980–1149).
 * Cùng logic với sql/migrate_lan2.php (chạy lại không trùng, id map cố định, dừng nếu id bị map khác chiếm).
 * Cần chạy SAU sql/migrate_lan5.php (cửa vào là map 4080 Thần Tôn Chính Điện).
 *
 *   php sql/migrate_lan6.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan6.php --apply      ghi thật
 */
$LAN_DATA_FILE = __DIR__ . '/lan6_data.json';
require __DIR__ . '/migrate_lan2.php';
