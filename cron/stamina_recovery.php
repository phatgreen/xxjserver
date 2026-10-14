<?php
/**
 * Ấn định hồi Thể lực theo thời gian thực cho MỌI nhân vật.
 *
 * Chạy mỗi 5 phút (xem install_stamina_recovery_task.bat).
 * Với mỗi nhân vật có ustamina < umaxstamina, tính số Thể lực hồi được
 * dựa trên khoảng thời gian trôi qua kể từ stamina_time (mốc hồi gần nhất).
 * Cập nhật trực tiếp vào DB -> ngay cả khi nhân vật OFFLINE vẫn hồi.
 */
date_default_timezone_set('Asia/Ho_Chi_Minh');

$root = dirname(__DIR__);
require_once $root . '/pdo.php';
require_once $root . '/class/player.php'; // dùng chung hàm \player\settle_stamina

// Mỗi 5 phút hồi 1 điểm — chu kỳ gốc; Thiên Phú công 6 rút theo từng nhân vật
$interval = 300;

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

$nowdate = date('Y-m-d H:i:s');
$nowTs   = strtotime($nowdate);

$updated  = 0;
$full     = 0;
$skipped  = 0;
$errors   = 0;

try {
    // Chỉ xử lý nhân vật chưa đầy Thể lực
    $rows = $dblj->query(
        "SELECT sid, ustamina, umaxstamina, stamina_time
         FROM game1
         WHERE ustamina < umaxstamina"
    );

    if ($rows === false) {
        throw new Exception('Query thất bại');
    }

    $updateStmt = $dblj->prepare(
        "UPDATE game1
         SET ustamina = ?, stamina_time = ?
         WHERE sid = ?"
    );

    foreach ($rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $settle = \player\settle_stamina(
            $row['ustamina'],
            $row['umaxstamina'],
            $row['stamina_time'],
            $nowTs,
            $interval,
            \player\tianfu_bonus_pct($row['sid'], $dblj, 'stamina')
        );

        if ($settle['steps'] <= 0) {
            $skipped++;
            continue;
        }

        $updateStmt->execute(array(
            $settle['value'],
            date('Y-m-d H:i:s', $settle['mark']),
            $row['sid']
        ));

        if ($settle['value'] >= intval($row['umaxstamina'])) {
            $full++;
        }
        $updated++;
    }
} catch (Exception $e) {
    $errors++;
    $line = 'ERROR ' . date('Y-m-d H:i:s') . ' ' . $e->getMessage() . PHP_EOL;
    file_put_contents($logDir . '/stamina_recovery.log', $line, FILE_APPEND);
    echo $line;
    exit(1);
}

$line = date('Y-m-d H:i:s')
    . ' updated=' . $updated
    . ' filled=' . $full
    . ' skipped=' . $skipped
    . ' errors=' . $errors
    . PHP_EOL;

file_put_contents($logDir . '/stamina_recovery.log', $line, FILE_APPEND);
echo $line;
?>