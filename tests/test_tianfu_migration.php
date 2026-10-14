<?php
/**
 * test_tianfu_migration.php — kiem migrate_tianfu.php: hoan diem, phat dan 114, idempotent.
 *   "...php.exe" tests/test_tianfu_migration.php
 * Tu tao nhan vat gia (sid MIGTP...), tu xoa khi xong. KHONG dung nhan vat that.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/pdo.php';
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
function ttrue($name, $cond, $note) {
    global $fails;
    if ($cond) { echo "PASS $name ($note)\n"; }
    else { $fails++; echo "FAIL $name ($note)\n"; }
}

$P   = defined('PHP_BIN') ? PHP_BIN : 'D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe';
$SID = 'MIGTP' . substr(md5(uniqid('', true)), 0, 27);
$LEG = array('tfhp' => 7, 'tfgj' => 5, 'tffy' => 3, 'tfbj' => 2, 'tfxx' => 4, 'tfxy' => 6, 'tfsb' => 1);
$HOAN = array_sum($LEG);   // 28

// ---- A: don dep + tao fixture ----
function cleanup($db, $sid) {
    $db->exec("DELETE FROM player_tianfu      WHERE sid = " . $db->quote($sid));
    $db->exec("DELETE FROM player_tianfu_log  WHERE sid = " . $db->quote($sid));
    $db->exec("DELETE FROM playerdaoju        WHERE sid = " . $db->quote($sid));
    $db->exec("DELETE FROM game1              WHERE sid = " . $db->quote($sid));
    \player\player_cache_clear($sid);
}
cleanup($dblj, $SID);

$cols = array('sid', 'uname', 'ulv', 'tf', 'shenfen');
$vals = array($dblj->quote($SID), $dblj->quote('TP_MIGTEST'), 40, 12, 1);
foreach ($LEG as $c => $v) { $cols[] = $c; $vals[] = intval($v); }
$dblj->exec("INSERT INTO game1 (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")");
tassert('fixture da chen', intval($dblj->query("SELECT COUNT(*) FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 1);

function mig($P, $ROOT, $args) {
    $cmd = '"' . $P . '" "' . $ROOT . '/sql/migrate_tianfu.php" ' . $args . ' 2>&1';
    $out = array(); $code = 0;
    exec($cmd, $out, $code);
    return array('code' => $code, 'out' => implode("\n", $out));
}

// ---- B: khong co co nao => exit 2 ----
$r = mig($P, $ROOT, '');
tassert('B: thieu co => exit 2', $r['code'], 2);
ttrue('B: bao chon dung mot co', strpos($r['out'], 'chon dung mot co') !== false, $r['out']);

// ---- C: dry-run khong ghi DB ----
$r = mig($P, $ROOT, '--dry-run --sid=' . $SID);
tassert('C: dry-run exit 0', $r['code'], 0);
ttrue('C: in ra SE_MIGRATE', strpos($r['out'], 'SE_MIGRATE=') !== false, 'co dong tong ket');
ttrue('C: tinh dung hoan=' . $HOAN, strpos($r['out'], 'TONG_HOAN=' . $HOAN) !== false, $r['out']);
tassert('C: tf khong doi', intval($dblj->query("SELECT tf FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 12);
tassert('C: tfgj khong bi xoa', intval($dblj->query("SELECT tfgj FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 5);
tassert('C: chua co log migrate', intval($dblj->query("SELECT COUNT(*) FROM player_tianfu_log
    WHERE sid = " . $dblj->quote($SID) . " AND kind = 'migrate'")->fetchColumn()), 0);

// ---- D: apply that ----
$r = mig($P, $ROOT, '--apply --sid=' . $SID);
tassert('D: apply exit 0', $r['code'], 0);
tassert('D: tf = 12 + ' . $HOAN, intval($dblj->query("SELECT tf FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 12 + $HOAN);
$zero = intval($dblj->query("SELECT tfhp+tfgj+tffy+tfbj+tfxx+tfxy+tfsb FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn());
tassert('D: 7 cot cu = 0', $zero, 0);
tassert('D: co 1 log migrate', intval($dblj->query("SELECT COUNT(*) FROM player_tianfu_log
    WHERE sid = " . $dblj->quote($SID) . " AND kind = 'migrate'")->fetchColumn()), 1);
tassert('D: co Khai Phu Dan 114 = 1', intval($dblj->query("SELECT djsum FROM playerdaoju
    WHERE sid = " . $dblj->quote($SID) . " AND djid = 114")->fetchColumn()), 1);
tassert('D: mirror tianfu_id = 0 (chua khai phu)', intval($dblj->query("SELECT tianfu_id
    FROM game1 WHERE sid = " . $dblj->quote($SID))->fetchColumn()), 0);

// ---- E: chay lan 2 phai SKIP, khong hoan trung ----
$tfTruoc = intval($dblj->query("SELECT tf FROM game1 WHERE sid = " . $dblj->quote($SID))->fetchColumn());
$r = mig($P, $ROOT, '--apply --sid=' . $SID);
tassert('E: apply lan 2 exit 0', $r['code'], 0);
ttrue('E: bao SKIP', strpos($r['out'], 'SKIP (da co log kind=migrate)') !== false, 'co chu SKIP');
tassert('E: tf khong doi lan 2', intval($dblj->query("SELECT tf FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), $tfTruoc);
tassert('E: van chi 1 log migrate', intval($dblj->query("SELECT COUNT(*) FROM player_tianfu_log
    WHERE sid = " . $dblj->quote($SID) . " AND kind = 'migrate'")->fetchColumn()), 1);
tassert('E: dan 114 van = 1', intval($dblj->query("SELECT djsum FROM playerdaoju
    WHERE sid = " . $dblj->quote($SID) . " AND djid = 114")->fetchColumn()), 1);

// ---- F: sid khong ton tai => exit 0, khong crash ----
$r = mig($P, $ROOT, '--apply --sid=00000000000000000000000000000000');
tassert('F: sid la exit 0', $r['code'], 0);
ttrue('F: bao khong tim thay', strpos($r['out'], 'Khong tim thay nhan vat nao') !== false, $r['out']);

// ---- G: don dep ----
cleanup($dblj, $SID);
tassert('G: fixture da xoa', intval($dblj->query("SELECT COUNT(*) FROM game1 WHERE sid = "
    . $dblj->quote($SID))->fetchColumn()), 0);

echo "\n" . ($fails === 0 ? "TAT CA PASS" : "CO $fails FAIL") . "\n";
exit($fails === 0 ? 0 : 1);
