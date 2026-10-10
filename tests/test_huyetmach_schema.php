<?php
/**
 * test_huyetmach_schema.php — kiem tra schema Mang Mach Huyet da duoc ap vao DB local `game`.
 * Chay SAU khi: "...php.exe" -r "pdo->exec(file)" (xem Step 4)
 *   hoac mysql -u root -p... game < sql/sql_huyetmach_vessel.sql
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
    return intval($st->fetch(PDO::FETCH_ASSOC)['c']) === 1;
}

tassert('table player_huyetmach_vessel', tbl($db, 'player_huyetmach_vessel'), true);
tassert('table huyetmach_boss', tbl($db, 'huyetmach_boss'), true);
tassert('table player_huyetmach_upgrade_log', tbl($db, 'player_huyetmach_upgrade_log'), true);

$v = col($db, 'player_huyetmach_vessel', 'sid');
tassert('vessel sid varchar64', intval($v['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('vessel vessel_id tinyint', col($db, 'player_huyetmach_vessel', 'vessel_id')['COLUMN_TYPE'], 'tinyint(4)');
tassert('vessel level default 0', col($db, 'player_huyetmach_vessel', 'level')['COLUMN_DEFAULT'], '0');
tassert('boss sid varchar64', intval(col($db, 'huyetmach_boss', 'sid')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('boss cfg_key varchar64', intval(col($db, 'huyetmach_boss', 'cfg_key')['CHARACTER_MAXIMUM_LENGTH']), 64);
tassert('boss status default', col($db, 'huyetmach_boss', 'status')['COLUMN_DEFAULT'], 'active');
tassert('log from_quality int', col($db, 'player_huyetmach_upgrade_log', 'from_quality')['COLUMN_TYPE'], 'int(11)');

// game1 khong them cot moi nao cho mach — cot stage/pham/cd da co tu truooc
tassert('game1 huyetmach_awaken_stage ton tai', col($db, 'game1', 'huyetmach_awaken_stage') !== false, true);
tassert('game1 huyetmach_transform_cd ton tai', col($db, 'game1', 'huyetmach_transform_cd') !== false, true);

echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
