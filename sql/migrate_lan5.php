<?php
/**
 * migrate_lan5.php — Làn 5 (cấp 2001–2500): 10 khu (qy 67–76), 80 map id cố định 4001–4080, 10 boss khu vực.
 * Dữ liệu: sql/lan5_data.json (sinh bởi tools/gen/lan5_build.py). Quái: class/config/guaiwu_config.json (mã 810–979).
 * Cùng logic với sql/migrate_lan2.php (chạy lại không trùng, id map cố định, dừng nếu id bị map khác chiếm).
 * Cần chạy SAU sql/migrate_lan4.php (cửa vào là map 3080 Tiên Tôn Chi Đỉnh).
 *
 *   php sql/migrate_lan5.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan5.php --apply      ghi thật
 */
$LAN_DATA_FILE = __DIR__ . '/lan5_data.json';
require __DIR__ . '/migrate_lan2.php';
