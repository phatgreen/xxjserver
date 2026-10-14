<?php
/**
 * tianfu_columns.php — them 4 cot Thien Phu vao game1, idempotent.
 * Chay: "...php.exe" sql/tianfu_columns.php         (chi in ke hoach)
 *       "...php.exe" sql/tianfu_columns.php --apply  (thuc su ALTER)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$apply = in_array('--apply', $argv, true);
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

$want = array(
    'tianfu_id'     => "ADD COLUMN `tianfu_id` TINYINT(4) NOT NULL DEFAULT 0",
    'tianfu_quality'=> "ADD COLUMN `tianfu_quality` TINYINT(4) NOT NULL DEFAULT 0",
    'tianfu_lv'     => "ADD COLUMN `tianfu_lv` INT(11) NOT NULL DEFAULT 0",
    'tianfu_power'  => "ADD COLUMN `tianfu_power` INT(11) NOT NULL DEFAULT 0",
);

$moved = 0;
foreach ($want as $col => $ddl) {
    $st = $db->prepare("SELECT COUNT(*) c FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA='game' AND TABLE_NAME='game1' AND COLUMN_NAME=?");
    $st->execute(array($col));
    $has = intval($st->fetch(PDO::FETCH_ASSOC)['c']) === 1;
    if ($has) { echo "CO SAN   $col\n"; continue; }
    echo $apply ? "ALTER      $col\n" : "SE ADD     $col\n";
    if ($apply) { $db->exec("ALTER TABLE game1 $ddl"); }
    $moved++;
}
echo $apply
    ? ("XONG: da them $moved cot.\n")
    : ("DRY-RUN: $moved cot se duoc them. Chay lai voi --apply.\n");
