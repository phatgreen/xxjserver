<?php
/**
 * check_thonguyen.php — kiểm tra sau khi cập nhật Tuổi & Thọ Nguyên (docs/DESIGN_THONGUYEN.md):
 * tạo bảng player_thonguyen nếu chưa có, kiểm config + 3 đan (121/122/123), in số nhân vật đã có mốc tuổi.
 *   php sql/check_thonguyen.php
 * Chỉ tạo bảng, KHÔNG sửa dữ liệu nhân vật. Chạy lại nhiều lần không sao.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$err = 0;
$cfg = \player\thonguyen_config();
if (empty($cfg['realm_base'])) { echo "[LOI] class/config/thonguyen_config.json khong doc duoc\n"; $err++; }
else echo "[OK] config tho nguyen: " . $cfg['hours_per_year'] . " gio = 1 tuoi, goc " . $cfg['realm_base'][0] . " nam\n";

foreach (array('dientho', 'daitinh', 'cuuchuyen') as $k) {
    $id = isset($cfg['items'][$k]) ? intval($cfg['items'][$k]) : 0;
    $d = $id ? \player\daoju_config_get($id) : false;
    if (!$d) { echo "[LOI] thieu dao cu $k (djid $id) trong daoju_config.json\n"; $err++; }
    else echo "[OK] dao cu $id: " . $d['djname'] . "\n";
}

if (!\player\thonguyen_ready($dblj)) { echo "[LOI] khong tao duoc bang player_thonguyen\n"; $err++; }
else {
    $n = intval($dblj->query("SELECT COUNT(*) FROM player_thonguyen")->fetchColumn());
    $all = intval($dblj->query("SELECT COUNT(*) FROM game1")->fetchColumn());
    echo "[OK] bang player_thonguyen: $n / $all nhan vat da co moc tuoi (nhan vat con lai bat dau 0 tuoi o lan vao game ke tiep)\n";
}
echo $err ? "CO $err LOI - xem dong [LOI] o tren\n" : "XONG - Tho Nguyen da san sang\n";
exit($err ? 1 : 0);
