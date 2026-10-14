<?php
/**
 * migrate_tianfu.php — hoan 7 cot game1.tf* cu ve `tf`, phat 1 Khai Phu Dan (djid 114),
 * ghi player_tianfu_log(kind='migrate'), dong bo 4 cot game1.tianfu_*.
 *
 *   php sql/migrate_tianfu.php --dry-run            in bang, KHONG ghi DB
 *   php sql/migrate_tianfu.php --apply              ghi that
 *   php sql/migrate_tianfu.php --apply --sid=<hex>  chi 1 nhan vat (dung cho test)
 *
 * Idempotent: bo qua moi sid da co dong player_tianfu_log(kind='migrate'). Bat buoc,
 * vi upplayerlv() (class/player.php:1201, khoi UPDATE tf* tai :1232-1256) van cap 6 cot
 * tf* cu moi lan len cap — khong guard thi chay --apply lan 2 se hoan trung.
 * Mot nhan vat FAIL => rollBack nhan vat do va DUNG HET (spec §7.4).
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/function/tianfu.php';
require_once $ROOT . '/pdo.php';   // -> $dblj (global)

// pdo.php dat ATTR_ERRMODE = ERRMODE_WARNING: PDO khong nem exception, chi canh bao
// roi tra false => moi loi DB se bi NUOT IM. Bat buoc doi sang EXCEPTION de try/catch
// ben duoi that su bat duoc (le silent-failure).
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
$ONLY  = null;
foreach ($argv as $a) {
    if (strpos($a, '--sid=') === 0) { $ONLY = substr($a, 6); }
}
if ($APPLY === $DRY) {
    echo "DUNG: chon dung mot co — --dry-run hoac --apply\n";
    exit(2);
}

$LEGACY    = array('tfhp', 'tfgj', 'tffy', 'tfbj', 'tfxx', 'tfxy', 'tfsb');
$PILL_DJID = 114;

echo $APPLY ? "=== migrate_tianfu.php --apply (GHI DB) ===\n"
            : "=== migrate_tianfu.php --dry-run (KHONG ghi DB) ===\n";

// ---- 0) Schema phai co san (Task 2) ----
foreach (array('player_tianfu', 'player_tianfu_log') as $t) {
    $c = $dblj->query("SELECT COUNT(*) FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $dblj->quote($t))->fetchColumn();
    if (intval($c) !== 1) {
        echo "LOI: bang $t chua ton tai — chay sql/sql_tianfu.sql truoc (Task 2)\n";
        exit(3);
    }
}
foreach (array('tianfu_id', 'tianfu_quality', 'tianfu_lv', 'tianfu_power') as $col) {
    $c = $dblj->query("SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'game1'
              AND COLUMN_NAME = " . $dblj->quote($col))->fetchColumn();
    if (intval($c) !== 1) {
        echo "LOI: game1.$col chua ton tai — chay sql/sql_tianfu.sql truoc (Task 2)\n";
        exit(3);
    }
}
$pill = \player\getdaoju($PILL_DJID, $dblj);
if (!$pill) {
    echo "LOI: daoju $PILL_DJID (Khai Phu Dan) chua co trong class/config/daoju_config.json — Task 4\n";
    exit(3);
}
echo "Schema OK: 2 bang + 4 cot game1 + daoju $PILL_DJID ({$pill->djname})\n\n";

// ---- 1) Danh sach nhan vat ----
$sql = "SELECT sid, uname, ulv, tf, " . implode(',', $LEGACY) . " FROM game1";
if ($ONLY !== null) { $sql .= " WHERE sid = " . $dblj->quote($ONLY); }
$sql .= " ORDER BY ulv DESC, sid ASC";
$rows = $dblj->query($sql)->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo "Khong tim thay nhan vat nao" . ($ONLY !== null ? " voi sid=$ONLY" : "") . "\n";
    exit(0);
}

// ---- 2) Guard idempotent ----
$done = array();
foreach ($dblj->query("SELECT DISTINCT sid FROM player_tianfu_log WHERE kind = 'migrate'")
             ->fetchAll(PDO::FETCH_COLUMN) as $s) {
    $done[(string)$s] = true;
}

printf("%-34s %-16s %5s %8s %7s %8s  %s\n",
    'sid', 'uname', 'ulv', 'tf_cu', 'hoan', 'tf_moi', '7 cot cu');
$ok = 0; $skip = 0; $totHoan = 0; $totPill = 0;

foreach ($rows as $r) {
    $sid   = (string)$r['sid'];
    $hoan  = 0;
    $parts = array();
    foreach ($LEGACY as $c) {
        $v = intval($r[$c]);
        $hoan += $v;
        $parts[] = $c . '=' . $v;
    }
    if (isset($done[$sid])) {
        $skip++;
        printf("%-34s %-16s %5s %8s %7s %8s  SKIP (da co log kind=migrate)\n",
            $sid, $r['uname'], $r['ulv'], $r['tf'], '-', '-');
        continue;
    }
    $totHoan += $hoan;
    $totPill++;
    printf("%-34s %-16s %5s %8s %7d %8d  %s\n",
        $sid, $r['uname'], $r['ulv'], $r['tf'], $hoan, intval($r['tf']) + $hoan,
        implode(' ', $parts));

    if (!$APPLY) { continue; }

    // ---- 3) Ghi that: mot transaction cho mot nhan vat ----
    try {
        $dblj->beginTransaction();
        \player\player_cache_clear($sid);
        $dblj->prepare("UPDATE game1 SET tf = tf + ?,
                tfhp = 0, tfgj = 0, tffy = 0, tfbj = 0, tfxx = 0, tfxy = 0, tfsb = 0
                WHERE sid = ?")->execute(array($hoan, $sid));
        \player\tianfu_log($sid, 'migrate', 0, 0, 0,
            'hoan ' . $hoan . ' diem tu 7 cot cu + 1 Khai Phu Dan 114', $dblj);
        \player\adddj($sid, $PILL_DJID, 1, $dblj);
        \player\tianfu_recalc_mirror($sid, $dblj);
        $dblj->commit();
        \player\player_cache_clear($sid);
        $ok++;
    } catch (Exception $e) {
        if ($dblj->inTransaction()) { $dblj->rollBack(); }
        \player\player_cache_clear($sid);
        echo "\nFAIL $sid: " . $e->getMessage() . "\n";
        echo "DUNG HET (spec §7.4): OK=$ok FAIL=1 — khong chay tiep nhan vat con lai.\n";
        echo "Nhan vat nay da rollBack; cac nhan vat OK truoc do VAN da commit.\n";
        echo "Restore toan bo: mysql -u root -p game < bak/tianfu_pre_<nhan>.sql\n";
        exit(1);
    }
}

echo str_repeat('-', 100), "\n";
if ($APPLY) {
    echo "OK=$ok FAIL=0 SKIP=$skip TONG_HOAN=$totHoan TONG_DAN_114=$totPill\n";
} else {
    echo "SE_MIGRATE=$totPill SKIP=$skip TONG_HOAN=$totHoan TONG_DAN_114=$totPill (KHONG ghi DB)\n";
}

// ---- 4) Kiem tra sau (spec §7.5) ----
// --sid=<hex>: chi do trong pham vi 1 nhan vat; khong co --sid: toan DB.
$sc = ($ONLY !== null) ? ' AND sid = ' . $dblj->quote($ONLY) : '';
$c1 = intval($dblj->query("SELECT COUNT(*) FROM game1
        WHERE tfhp+tfgj+tffy+tfbj+tfxx+tfxy+tfsb > 0" . $sc)->fetchColumn());
$c2 = intval($dblj->query("SELECT COUNT(*) FROM game1 WHERE tianfu_power > 0" . $sc)->fetchColumn());
$c3 = intval($dblj->query("SELECT COUNT(*) FROM player_tianfu_log WHERE kind = 'migrate'" . $sc)->fetchColumn());
$c4 = intval($dblj->query("SELECT COUNT(*) FROM playerdaoju WHERE djid = " . intval($PILL_DJID) . $sc)->fetchColumn());
$c5 = intval($dblj->query("SELECT COUNT(*) FROM game1")->fetchColumn());
echo "\n=== Kiem tra sau ===\n";
printf("game1 co 7 cot cu > 0            : %d   (ky vong 0 NGAY SAU apply; len cap se lam no > 0 lai — binh thuong)\n", $c1);
printf("game1 co tianfu_power > 0         : %d   (ky vong 0 — chua ai bat Phu Vi)\n", $c2);
printf("player_tianfu_log kind=migrate    : %d   (ky vong = so nhan vat da migrate)\n", $c3);
printf("playerdaoju djid=114              : %d   (ky vong = so nhan vat da migrate)\n", $c4);
printf("tong nhan vat game1               : %d\n", $c5);
if ($APPLY && ($c1 !== 0 || $c2 !== 0 || $c3 !== $totPill + $skip || $c4 < $totPill)) {
    echo "LOI: kiem tra sau khong dat — dung ban giao, restore dump.\n";
    exit(1);
}
exit(0);
