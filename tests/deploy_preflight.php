<?php
/**
 * deploy_preflight.php — CHI DOC: kiem tra schema mot DB co dang chay
 * sql/deploy_production_20261004.sql hay khong. Khong ghi, khong ALTER.
 *
 * Chay local:  "...php.exe" tests/deploy_preflight.php
 * Chay production (chu server tu nhap thong note):
 *   "...php.exe" tests/deploy_preflight.php --host=HOST --db=DBNAME --user=USER --pass=PASS
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$opt = array('host' => 'localhost', 'db' => 'game', 'user' => 'root', 'pass' => 'www.aae.ink');
foreach ($argv as $a) {
    if (preg_match('/^--(host|db|user|pass)=(.*)$/', $a, $m)) $opt[$m[1]] = $m[2];
}

$NEED_ELEMENT = array('playerzhuangbei', 'fangshi_zb');
$NEED_LOGTYPE = 'player_chetao_log';
$SID_VARCHAR = array(
    'player_linhcan_quest'          => 'sid',
    'player_alchemist'              => 'sid',
    'player_dan_dinh'               => 'sid',
    'player_huyetmach_boss'         => 'sid',
    'player_huyetmach_quest'        => 'sid',
    'player_alchemy_log'            => 'sid',
    'player_thechat_log'            => 'sid',
    'player_thechat_awaken_log'     => 'sid',
    'player_huyetmach_awaken_log'   => 'sid',
    'player_chetao_log'             => 'sid',
    'linhcan_boss'                  => 'killer_sid',
    'player_dan_thu'                => 'sid',
    'player_dan_linh'               => 'sid',
    'player_alchemy_formation'      => 'sid',
    'player_luonden_log'            => 'sid',
);

try {
    $db = new PDO('mysql:host=' . $opt['host'] . ';dbname=' . $opt['db'] . ';charset=utf8',
        $opt['user'], $opt['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (Exception $e) {
    echo "KHONG ket noi duoc DB: " . $e->getMessage() . "\n";
    exit(2);
}
echo "== Preflight " . $opt['host'] . '/' . $opt['db'] . " (chi doc) ==\n";

function col($db, $dbn, $t, $c) {
    $s = $db->prepare("SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_DEFAULT
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :dbn
          AND TABLE_NAME = :t AND COLUMN_NAME = :c");
    $s->execute(array(':dbn' => $dbn, ':t' => $t, ':c' => $c));
    $r = $s->fetch(PDO::FETCH_ASSOC);
    return $r ? $r : false;
}

$todo = 0;
foreach ($NEED_ELEMENT as $t) {
    $r = col($db, $opt['db'], $t, 'element');
    printf("%-22s element  %s\n", $t, $r ? 'CO (' . $r['DATA_TYPE'] . ')' : 'THIEU -> can ALTER');
    if (!$r) $todo++;
}
$r = col($db, $opt['db'], $NEED_LOGTYPE, 'log_type');
printf("%-22s log_type %s\n", $NEED_LOGTYPE, $r ? 'CO (' . $r['DATA_TYPE'] . ')' : 'THIEU -> can ALTER');
if (!$r) $todo++;

foreach ($SID_VARCHAR as $t => $c) {
    $r = col($db, $opt['db'], $t, $c);
    if (!$r) { printf("%-28s %-11sBang khong ton tai -> kiem tra lai\n", $t, $c); $todo++; continue; }
    $isTxt = ($r['DATA_TYPE'] === 'varchar' || $r['DATA_TYPE'] === 'char');
    printf("%-28s %-11s%s (%s%s)\n", $t, $c,
        $isTxt ? 'DA NONG' : 'VAN LA SO -> can ALTER',
        $r['DATA_TYPE'], $r['CHARACTER_MAXIMUM_LENGTH'] ? '(' . $r['CHARACTER_MAXIMUM_LENGTH'] . ')' : '');
    if (!$isTxt) $todo++;
}

$s = $db->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?");
$s->execute(array($opt['db']));
echo "\nTong so bang trong DB: " . $s->fetchColumn() . "\n";
echo "Hang schema con thieu/sai: $todo\n";
echo $todo === 0
    ? "San cho chay sql/deploy_production_20261004.sql (hoac no da chay xong).\n"
    : "CHUA san sang. Xem lai tung dong o tren.\n";
echo "LUU Y: file SQL production khong co cau DELETE nao. 5 bang co PK chua sid\n"
   . "     van giu nguyen dong cu (sid bi cat gon). Do la quyet dinh rieng.\n";
exit($todo === 0 ? 0 : 1);
