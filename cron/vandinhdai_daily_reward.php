<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$root = dirname(__DIR__);
require_once $root . '/pdo.php';
require_once $root . '/class/function/vandinhdai_reward.php';

$force = in_array('--force', isset($argv) ? $argv : array(), true);
$result = vdd_issue_daily_rewards($dblj, $force);

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

$line = date('Y-m-d H:i:s')
    . ' issued=' . ($result['issued'] ? '1' : '0')
    . ' reason=' . $result['reason']
    . ' count=' . intval($result['count'])
    . PHP_EOL;

file_put_contents($logDir . '/vandinhdai_daily_reward.log', $line, FILE_APPEND);
echo $line;
?>
