<?php
/**
 * One-time reset: nhân vật bị ustamina > umaxstamina (do bug addplayersx
 * cộng đôi) sẽ được đưa về đúng umaxstamina.
 *
 * Chạy: php sql/_reset_stamina_overflow.php
 */
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
require_once $root . '/pdo.php';

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

try {
    // Tìm mọi nhân vật có thể lực vượt quá giới hạn tự nhiên.
    $rows = $dblj->query(
        "SELECT sid, uname, ustamina, umaxstamina FROM game1 WHERE ustamina > umaxstamina"
    );
    $affected = $rows->fetchAll(PDO::FETCH_ASSOC);

    $updateStmt = $dblj->prepare(
        "UPDATE game1 SET ustamina = umaxstamina WHERE sid = ? AND ustamina > umaxstamina"
    );

    $count = 0;
    foreach ($affected as $r) {
        $updateStmt->execute(array($r['sid']));
        if ($updateStmt->rowCount() > 0) {
            $count++;
            $line = sprintf(
                "[%s] side=%s uname=%s | ustamina %d -> %d (umaxstamina=%d)",
                date('Y-m-d H:i:s'),
                $r['sid'],
                $r['uname'],
                $r['ustamina'],
                $r['umaxstamina'],
                $r['umaxstamina']
            );
            echo $line . "\n";
            file_put_contents($logDir . '/reset_stamina_overflow.log', $line . "\n", FILE_APPEND);
        }
    }

    $line = date('Y-m-d H:i:s') . " total_overflow=" . count($affected) . " reset=" . $count . "\n";
    echo $line;
    file_put_contents($logDir . '/reset_stamina_overflow.log', $line, FILE_APPEND);
} catch (Exception $e) {
    $line = 'ERROR ' . date('Y-m-d H:i:s') . ' ' . $e->getMessage() . "\n";
    file_put_contents($logDir . '/reset_stamina_overflow.log', $line, FILE_APPEND);
    echo $line;
    exit(1);
}
