<?php
// Dùng: php tools/cloud/enc.php "cmd=...&sid=..."  -> in URL đã mã hoá cho server thử
error_reporting(0);
require __DIR__ . '/../../class/encode.php';
$e = new \encode\encode(); echo 'http://127.0.0.1:8091/game.php?cmd=' . $e->encode($argv[1]);
