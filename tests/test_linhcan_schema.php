<?php
/**
 * test_linhcan_schema.php — kiem tra schema Vong Ngu Hanh da duoc ap vao DB local `game`.
 * Chay SAU khi: php.exe sql runner (xem plan Task 1 Step 5)
 *   hoac mysql -u root -p game < sql/sql_linhcan_element.sql
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

function col($db, $table, $column) {
    $st = $db->prepare("SELECT COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='game' AND TABLE_NAME=? AND COLUMN_NAME=?");
    $st->execute(array($table, $column));
    return $st->fetch(PDO::FETCH_ASSOC);
}
function tbl($db, $table) {
    $st = $db->prepare("SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA='game' AND TABLE_NAME=?");
    $st->execute(array($table));
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return intval($row['c']) === 1;
}

tassert('table player_linhcan_node', tbl($db, 'player_linhcan_node'), true);
tassert('table linhcan_boss_personal', tbl($db, 'linhcan_boss_personal'), true);
tassert('table player_linhcan_break_log', tbl($db, 'player_linhcan_break_log'), true);
tassert('table player_linhcan_skill', tbl($db, 'player_linhcan_skill'), true);
tassert('node sid varchar64', intval(col($db, 'player_linhcan_node', 'sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('node element varchar10', intval(col($db, 'player_linhcan_node', 'element')['CHARACTER_MAXIMUM_LENGTH']), 10);
tassert('node level default 0', col($db, 'player_linhcan_node', 'level')['COLUMN_DEFAULT'], '0');
tassert('node stage tinyint', strpos(col($db, 'player_linhcan_node', 'stage')['COLUMN_TYPE'], 'tinyint') === 0, true);
tassert('bpers sid varchar64', intval(col($db, 'linhcan_boss_personal', 'sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('bpers status default', col($db, 'linhcan_boss_personal', 'status')['COLUMN_DEFAULT'], 'active');
tassert('bpers to_stage tinyint', strpos(col($db, 'linhcan_boss_personal', 'to_stage')['COLUMN_TYPE'], 'tinyint') === 0, true);
tassert('skill skill_id varchar40', intval(col($db, 'player_linhcan_skill', 'skill_id')['CHARACTER_MAXIMUM_LENGTH']), 40);
exit($fails === 0 ? 0 : 1);
