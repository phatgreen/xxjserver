<?php
/**
 * test_tianfu_schema.php — schema Thien Phu (Phu Cac) da ap vao DB local `game` chua.
 * Chay SAU khi: php sql/tianfu_columns.php --apply  VA  mysql ... game < sql/sql_tianfu.sql
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else { $fails++; echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n"; }
}
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

function col($db, $table, $column) {
    $st = $db->prepare("SELECT COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='game' AND TABLE_NAME=? AND COLUMN_NAME=?");
    $st->execute(array($table, $column));
    return $st->fetch(PDO::FETCH_ASSOC);
}
function tbl($db, $table) {
    $st = $db->prepare("SELECT COUNT(*) c FROM information_schema.TABLES WHERE TABLE_SCHEMA='game' AND TABLE_NAME=?");
    $st->execute(array($table));
    return intval($st->fetch(PDO::FETCH_ASSOC)['c']) === 1; }
function idx($db, $table, $key) {
    $st = $db->prepare("SELECT COUNT(*) c FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA='game' AND TABLE_NAME=? AND INDEX_NAME=?");
    $st->execute(array($table, $key));
    return intval($st->fetch(PDO::FETCH_ASSOC)['c']) > 0; }

tassert('bang player_tianfu', tbl($db, 'player_tianfu'), true);
tassert('bang player_tianfu_log', tbl($db, 'player_tianfu_log'), true);
tassert('sid la VARCHAR(64)', intval(col($db, 'player_tianfu', 'sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('log sid VARCHAR(64)', intval(col($db, 'player_tianfu_log', 'sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('tianfu_id tinyint', col($db, 'player_tianfu', 'tianfu_id')['COLUMN_TYPE'], 'tinyint(4)');
tassert('quality default 1', col($db, 'player_tianfu', 'quality')['COLUMN_DEFAULT'], '1');
tassert('lv default 0', col($db, 'player_tianfu', 'lv')['COLUMN_DEFAULT'], '0');
tassert('slot default 0', col($db, 'player_tianfu', 'slot')['COLUMN_DEFAULT'], '0');
tassert('obtained_at cho NULL', col($db, 'player_tianfu', 'obtained_at')['COLUMN_DEFAULT'], null);
tassert('PK (sid,tianfu_id)', idx($db, 'player_tianfu', 'PRIMARY'), true);
tassert('KEY idx_sid_slot', idx($db, 'player_tianfu', 'idx_sid_slot'), true);
tassert('log kind varchar20', col($db, 'player_tianfu_log', 'kind')['COLUMN_TYPE'], 'varchar(20)');
tassert('log id auto_inc', strpos(col($db, 'player_tianfu_log', 'id')['COLUMN_TYPE'], 'int') !== false, true);

foreach (array('tianfu_id','tianfu_quality','tianfu_lv','tianfu_power') as $c) {
    tassert('game1.' . $c . ' ton tai', col($db, 'game1', $c) !== false, true);
    tassert('game1.' . $c . ' default 0', col($db, 'game1', $c)['COLUMN_DEFAULT'], '0');
}

// LANDMINE: getplayer() chi SELECT cac cot trong whitelist nay.
$cols = \player\player_allowed_columns();
foreach (array('tianfu_id','tianfu_quality','tianfu_lv','tianfu_power') as $c) {
    tassert('whitelist SELECT co ' . $c, in_array($c, $cols, true), true);
}
tassert('whitelist UPDATE khong mo rong', in_array('tianfu_power', \player\game1_update_allowed_columns(), true), false);

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
