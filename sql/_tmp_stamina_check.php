<?php
error_reporting(E_ALL & ~E_DEPRECATED);
$root = dirname(__DIR__);
require_once $root . '/pdo.php';

$sql = "SELECT sid, uname, ustamina, umaxstamina, stamina_time FROM game1 ORDER BY sid";
$rows = $dblj->query($sql);
if ($rows === false) { echo "QUERY FAIL: " . print_r($dblj->errorInfo(), true); exit; }
$all = $rows->fetchAll(PDO::FETCH_ASSOC);
foreach ($all as $r) {
    echo $r['sid'] . " | " . $r['uname'] . " | ustamina=" . $r['ustamina']
       . " | umaxstamina=" . $r['umaxstamina']
       . " | stamina_time=" . $r['stamina_time'] . "\n";
}
echo "total=" . count($all) . "\n";