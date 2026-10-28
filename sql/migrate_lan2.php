<?php
/**
 * migrate_lan2.php — Làn 2 (cấp 501–1000): 10 khu (qy 37–46), 80 map nối chuỗi, 10 boss khu vực.
 * Dữ liệu: sql/lan2_data.json (sinh bởi tools/gen/lan2_build.py). Quái: class/config/guaiwu_config.json (mã 300–469).
 *
 *   php sql/migrate_lan2.php --dry-run    in việc sẽ làm, KHÔNG ghi DB
 *   php sql/migrate_lan2.php --apply      ghi thật
 *
 * Sao lưu trước (Windows cmd):
 *   "D:\XXJserver\phpstudy_pro\Extensions\MySQL5.5.29\bin\mysqldump.exe" -uroot -p game > D:\XXJserver\backup_lan2.sql
 * Idempotent (như migrate_dot_b.php): khu theo qyid; map theo (mname, mqy) với id cố định 1001–1080; boss theo bossname.
 * Chạy lại không tạo trùng. Id map cố định để nút Truyền tống của nhiệm vụ NPC (renwu.rwqy) đúng trên mọi máy.
 * Cửa vào: map cuối làn 1 (entry_from_mid = 408 Thiên Ngoại Thiên Đỉnh) đi xuống -> map đầu khu 37.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/pdo.php';   // -> $dblj
$dblj->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$APPLY = in_array('--apply', $argv, true);
$DRY   = in_array('--dry-run', $argv, true);
if ($APPLY === $DRY) { echo "DUNG: chon dung mot co --dry-run hoac --apply\n"; exit(2); }

$data = json_decode(file_get_contents(__DIR__ . '/lan2_data.json'), true);
if (!is_array($data)) { echo "Khong doc duoc sql/lan2_data.json\n"; exit(2); }

function say($s) { echo $s . "\n"; }
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

// ---- Kiểm tra cửa vào ----
$entry = intval($data['entry_from_mid']);
$down = q1("SELECT mdown FROM mid WHERE mid = ?", array($entry));
if ($down === false) { echo "LOI: khong co map cua vao $entry (chay migrate_dot_b.php truoc)\n"; exit(1); }

$regionMaps = array();
foreach ($data['regions'] as $r) {
    $qyid = intval($r['qyid']);
    $qyName = q1("SELECT qyname FROM qy WHERE qyid = ?", array($qyid));
    if ($qyName === false) {
        say("[qy] TAO khu $qyid '{$r['qyname']}' ({$r['band']})");
        run("INSERT INTO qy (qyid, qyname, mid) VALUES (?, ?, 0)", array($qyid, $r['qyname']));
    } elseif ($qyName !== $r['qyname']) {
        say("[qy] dat ten khu $qyid = '{$r['qyname']}'");
        run("UPDATE qy SET qyname = ? WHERE qyid = ?", array($r['qyname'], $qyid));
    }
    $mids = array();
    foreach ($r['maps'] as $m) {
        $mid = q1("SELECT mid FROM mid WHERE mname = ? AND mqy = ? LIMIT 1", array($m['mname'], $qyid));
        if ($mid === false) {
            // Id cố định (lan2_data.json) — đã có map khác chiếm id này thì dừng, không ghi đè
            $mid = intval($m['mid']);
            $taken = q1("SELECT mname FROM mid WHERE mid = ?", array($mid));
            if ($taken !== false) { echo "LOI: mid $mid da duoc map '$taken' dung — khong tao duoc '{$m['mname']}'\n"; exit(1); }
            say("[mid] TAO map $mid '{$m['mname']}' (khu $qyid) quai {$m['mgid']}");
            run("INSERT INTO mid (mid, mname, mgid, mnpc, mgtime, ms, midinfo, midboss, mup, mdown, mleft, mright, mqy, playerinfo, ispvp)
                 VALUES (?, ?, ?, '', '2000-01-01 00:00:00', '0', ?, 0, 0, 0, 0, 0, ?, '', 0)",
                array($mid, $m['mname'], $m['mgid'], $m['midinfo'], $qyid));
        } elseif (intval($mid) !== intval($m['mid'])) {
            echo "LOI: map '{$m['mname']}' da co voi mid $mid, khac id co dinh {$m['mid']} — kiem tra lai\n"; exit(1);
        } else {
            $mid = intval($mid);
            say("[mid] cap nhat map $mid '{$m['mname']}' quai {$m['mgid']}");
            run("UPDATE mid SET mgid = ?, midinfo = ? WHERE mid = ?", array($m['mgid'], $m['midinfo'], $mid));
        }
        $mids[] = $mid;
    }
    $regionMaps[$qyid] = $mids;
}

// ---- Nối map: mỗi khu hình rắn 4 map/hàng; map cuối khu trước đi xuống -> map đầu khu sau ----
function link_pair($a, $dirA, $b, $dirB) {
    say("[noi] $a.$dirA -> $b , $b.$dirB -> $a");
    run("UPDATE mid SET $dirA = ? WHERE mid = ?", array($b, $a));
    run("UPDATE mid SET $dirB = ? WHERE mid = ?", array($a, $b));
}
function link_snake($mids, $perRow) {
    $n = count($mids);
    for ($i = 0; $i < $n - 1; $i++) {
        $row = intval($i / $perRow);
        if ((($i + 1) % $perRow) === 0) {
            link_pair($mids[$i], 'mdown', $mids[$i + 1], 'mup');
        } elseif ($row % 2 === 0) {
            link_pair($mids[$i], 'mright', $mids[$i + 1], 'mleft');
        } else {
            link_pair($mids[$i], 'mleft', $mids[$i + 1], 'mright');
        }
    }
}
$prevLast = $entry;
foreach ($data['regions'] as $r) {
    $qyid = intval($r['qyid']);
    $mids = $regionMaps[$qyid];
    link_snake($mids, 4);
    if ($prevLast === $entry && intval($down) !== 0 && intval($down) !== $mids[0]) {
        echo "LOI: map $entry da co loi di xuong toi map $down — kiem tra lai truoc khi noi\n"; exit(1);
    }
    link_pair($prevLast, 'mdown', $mids[0], 'mup');
    say("[qy] cua vao khu $qyid = map {$mids[0]}");
    run("UPDATE qy SET mid = ? WHERE qyid = ?", array($mids[0], $qyid));
    $prevLast = $mids[count($mids) - 1];
}

// ---- Boss khu vực (map cuối mỗi khu, hồi sinh 600 giây) ----
foreach ($data['bosses'] as $b) {
    $m = $regionMaps[intval($b['qyid'])];
    $at = $m[count($m) - 1];
    $bid = q1("SELECT bossid FROM boss WHERE bossname = ? AND sid = '' LIMIT 1", array($b['bossname']));
    if ($bid === false) {
        say("[boss] TAO {$b['bossname']} cap {$b['bosslv']} mau {$b['bosshp']} tai map $at");
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
        say("[boss] cap nhat {$b['bossname']} (id $bid) theo du lieu moi");
        // bosshp gán TRƯỚC bossmaxhp (MySQL tính trái sang phải): boss đang đầy máu nhận luôn máu mới, đang bị đánh thì giữ nguyên
        run("UPDATE boss SET bosshp = IF(bosshp >= bossmaxhp, ?, bosshp), bossinfo = ?, bosslv = ?, bossmaxhp = ?, bossgj = ?, bossfy = ?, bossbj = ?, bossxx = ?, bosszb = ?, bossdj = ? WHERE bossid = ?",
            array($b['bosshp'], $b['bossinfo'], $b['bosslv'], $b['bosshp'], $b['bossgj'], $b['bossfy'], $b['bossbj'], $b['bossxx'], $b['bosszb'], $b['bossdj'], $bid));
    }
    say("[boss] map $at midboss = $bid, ms = 600");
    run("UPDATE mid SET midboss = ?, ms = '600' WHERE mid = ?", array($bid, $at));
}

echo $APPLY ? "XONG (--apply)\n" : "DRY-RUN: chua ghi gi. Chay lai voi --apply\n";
