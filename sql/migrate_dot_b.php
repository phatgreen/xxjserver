<?php
/**
 * migrate_dot_b.php — Đợt B: gắn quái vào map, nối map, tạo khu vực / map / boss mới tới cấp 500.
 * Dữ liệu: sql/dot_b_data.json (sinh bởi tools/gen/dot_b_build.py). Quái: class/config/guaiwu_config.json.
 *
 *   php sql/migrate_dot_b.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 * *   php sql/migrate_dot_b.php --apply      ghi thật
 *
 * Idempotent: map mới nhận diện theo (mname, mqy); khu theo qyid; boss theo bossname.
 * Chạy lại sẽ cập nhật đúng giá trị, không tạo trùng.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$data = json_decode(file_get_contents(__DIR__ . '/dot_b_data.json'), true);
if (!is_array($data)) { echo "Khong doc duoc sql/dot_b_data.json\n"; exit(2); }

$log = array();
function say($s) { global $log; $log[] = $s; echo $s . "\n"; }
function q1($sql, $args) {
    global $dblj;
    $st = $dblj->prepare($sql); $st->execute($args);
    return $st->fetchColumn();
}
function run($sql, $args) {
    global $dblj, $APPLY;
    if (!$APPLY) return 0;
    $st = $dblj->prepare($sql); $st->execute($args);
    return $st->rowCount();
}

$NEWMID = 900000; // id tạm cho map mới khi --dry-run (chưa ghi DB)
$regionMaps = array(); // qyid => danh sách mid theo thứ tự

foreach ($data['regions'] as $r) {
    $qyid = intval($r['qyid']);
    // ---- Khu vực ----
    $qyName = q1("SELECT qyname FROM qy WHERE qyid = ?", array($qyid));
    if ($qyName === false) {
        say("[qy] TAO khu $qyid '{$r['qyname']}'");
        run("INSERT INTO qy (qyid, qyname, mid) VALUES (?, ?, 0)", array($qyid, $r['qyname']));
    } elseif ($r['new'] || trim(strip_tags($qyName)) === '') {
        say("[qy] dat ten khu $qyid = '{$r['qyname']}'");
        run("UPDATE qy SET qyname = ? WHERE qyid = ?", array($r['qyname'], $qyid));
    }

    // ---- Map ----
    $mids = array();
    foreach ($r['maps'] as $m) {
        $mid = $m['mid'];
        if ($mid === null) {
            $mid = q1("SELECT mid FROM mid WHERE mname = ? AND mqy = ? LIMIT 1", array($m['mname'], $qyid));
            if ($mid === false) {
                say("[mid] TAO map '{$m['mname']}' (khu $qyid) quai {$m['mgid']}");
                if ($APPLY) {
                    run("INSERT INTO mid (mname, mgid, mnpc, mgtime, ms, midinfo, midboss, mup, mdown, mleft, mright, mqy, playerinfo, ispvp)
                         VALUES (?, ?, '', '2000-01-01 00:00:00', '0', ?, 0, 0, 0, 0, 0, ?, '', 0)",
                        array($m['mname'], $m['mgid'], $m['midinfo'], $qyid));
                    $mid = intval($dblj->lastInsertId());
                } else {
                    $mid = $NEWMID++;
                }
            } else {
                $mid = intval($mid);
                say("[mid] cap nhat map $mid '{$m['mname']}' quai {$m['mgid']}");
                run("UPDATE mid SET mgid = ?, midinfo = ? WHERE mid = ?", array($m['mgid'], $m['midinfo'], $mid));
            }
        } else {
            $mid = intval($mid);
            if (q1("SELECT mid FROM mid WHERE mid = ?", array($mid)) === false) {
                echo "LOI: map $mid '{$m['mname']}' khong ton tai trong DB\n"; exit(1);
            }
            say("[mid] gan quai map $mid '{$m['mname']}' = {$m['mgid']}");
            run("UPDATE mid SET mgid = ?, midinfo = IF(midinfo = '', ?, midinfo) WHERE mid = ?", array($m['mgid'], $m['midinfo'], $mid));
        }
        $mids[] = $mid;
    }
    $regionMaps[$qyid] = $mids;
}

// ---- Nối map ----
// Khu cũ 27/28/30 đã nối sẵn. Khu 31 (9 map rời) + 5 khu mới: xếp hình rắn, mỗi hàng 3 map (khu 31) hoặc 4 map (khu mới).
function link_pair($a, $dirA, $b, $dirB) {
    say("[noi] $a.$dirA -> $b , $b.$dirB -> $a");
    run("UPDATE mid SET $dirA = ? WHERE mid = ?", array($b, $a));
    run("UPDATE mid SET $dirB = ? WHERE mid = ?", array($a, $b));
}
function link_snake($mids, $perRow) {
    $n = count($mids);
    for ($i = 0; $i < $n - 1; $i++) {
        $row = intval($i / $perRow);
        $endOfRow = (($i + 1) % $perRow) === 0;
        if ($endOfRow) {
            link_pair($mids[$i], 'mdown', $mids[$i + 1], 'mup');
        } elseif ($row % 2 === 0) {
            link_pair($mids[$i], 'mright', $mids[$i + 1], 'mleft');
        } else {
            link_pair($mids[$i], 'mleft', $mids[$i + 1], 'mright');
        }
    }
}
$chainOrder = array(31);
foreach ($data['regions'] as $r) if ($r['new']) $chainOrder[] = intval($r['qyid']);
$prevLast = 359; // Tiên Hồ Cư (cuối Hồi Đầu Ngạn) -> cửa vào Hồng Hoang Cổ Địa
foreach ($chainOrder as $qyid) {
    $mids = $regionMaps[$qyid];
    link_snake($mids, $qyid === 31 ? 3 : 4);
    // cửa vào: map cuối khu trước (đi xuống) -> map đầu khu này
    link_pair($prevLast, 'mdown', $mids[0], 'mup');
    say("[qy] cua vao khu $qyid = map {$mids[0]}");
    run("UPDATE qy SET mid = ? WHERE qyid = ?", array($mids[0], $qyid));
    $prevLast = $mids[count($mids) - 1];
}

// ---- Boss khu vực ----
$qyByName = array();
foreach ($data['regions'] as $r) $qyByName[$r['qyname']] = intval($r['qyid']);
foreach ($data['bosses'] as $b) {
    $qyid = $qyByName[$b['region']];
    $at = intval($b['at_mid']);
    if ($at <= 0) { $m = $regionMaps[$qyid]; $at = $m[count($m) - 1]; }
    $bid = q1("SELECT bossid FROM boss WHERE bossname = ? LIMIT 1", array($b['bossname']));
    if ($bid === false) {
        say("[boss] TAO {$b['bossname']} cap {$b['bosslv']} tai map $at");
        if ($APPLY) {
            run("INSERT INTO boss (bossname, bossinfo, bosslv, bosshp, bossmaxhp, bossgj, bossfy, bossbj, bossxx, bosszb, dljv, bossdj, djjv, bs, bosstime, sid, bossyp, ypjv, bossexp)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '15', ?, '10', 0, NOW(), '', '2', '20', '0')",
                array($b['bossname'], $b['bossinfo'], $b['bosslv'], $b['bosshp'], $b['bosshp'], $b['bossgj'], $b['bossfy'],
                      $b['bossbj'], $b['bossxx'], $b['bosszb'], $b['bossdj']));
            $bid = intval($dblj->lastInsertId());
        } else {
            $bid = 0;
        }
    } else {
        $bid = intval($bid);
        say("[boss] da co {$b['bossname']} (id $bid) — giu nguyen chi so dang chay");
    }
    say("[boss] map $at midboss = $bid, ms = 600");
    run("UPDATE mid SET midboss = ?, ms = '600' WHERE mid = ?", array($bid, $at));
}

echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
