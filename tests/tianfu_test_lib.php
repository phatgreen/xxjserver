<?php
/**
 * tianfu_test_lib.php — helper seed nhan vat test cho he Thien Phu.
 * Dung chung voi tests/test_tianfu_bonuses.php, _lvup, _slot, _gates.
 * Khong in URL, khong tao trang bi — chi game1 + player_tianfu + playerdaoju.
 */
if (!function_exists('tf_test_db')) {
    function tf_test_db() {
        static $db = null;
        if ($db === null) {
            $db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        return $db;
    }
}

if (!function_exists('tf_seed_game1')) {
    /** Tu sinh dong game1 du cot NOT NULL khong default (le cua tests/e2e_fixture.php:42-67). */
    function tf_seed_game1($db, $sid, $uid, $uname, $ulv, $tf = 0) {
        $colsNN = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = 'game' AND TABLE_NAME = 'game1'
                  AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
                  AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
        $names = array(); $vals = array();
        foreach ($colsNN as $c) {
            $n = $c['COLUMN_NAME'];
            $names[] = $n;
            if ($n === 'sid')     { $vals[] = $db->quote($sid); continue; }
            if ($n === 'token')   { $vals[] = $db->quote('tfetest'); continue; }
            if ($n === 'endtime') { $vals[] = "DATE_ADD(NOW(), INTERVAL 6 HOUR)"; continue; }
            $isStr = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext'));
            $vals[] = $isStr ? "''" : "0";
        }
        foreach (array('uid' => intval($uid), 'uname' => $db->quote($uname), 'ulv' => intval($ulv),
                       'uyxb' => 5000000, 'uczb' => 500000, 'tf' => intval($tf),
                       'shenfen' => 1, 'sfzx' => 1) as $k => $v) {
            if (!in_array($k, $names, true)) { $names[] = $k; $vals[] = $v; }
            else {
                // cot da co trong NOT NULL list thi ep gia tri bang UPDATE phia duoi
            }
        }
        $db->exec("INSERT INTO game1 (" . implode(', ', $names) . ") VALUES (" . implode(', ', $vals) . ")");
        $db->prepare("UPDATE game1 SET uid = ?, uname = ?, ulv = ?, tf = ?, uyxb = 5000000, uczb = 500000
                      WHERE sid = ?")->execute(array(intval($uid), $uname, intval($ulv), intval($tf), $sid));
    }
}

if (!function_exists('tf_give_daoju')) {
    /** playerdaoju co PK (uid, djid) — bat buoc ghi ro uid. */
    function tf_give_daoju($db, $sid, $uid, $djid, $sum) {
        $db->prepare("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES (?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE djsum = ?")->execute(
            array($sid, intval($uid), intval($djid), intval($sum), intval($sum)));
    }
}

if (!function_exists('tf_ins_owned')) {
    /** Nhap 1 thien phu da so huu. $slot = 0 -> dang nam trong Phu Cac, chua bat. */
    function tf_ins_owned($db, $sid, $id, $quality, $lv, $slot = 0) {
        $db->prepare("INSERT INTO player_tianfu (sid, tianfu_id, quality, lv, slot, obtained_at)
                      VALUES (?, ?, ?, ?, ?, NOW())
                      ON DUPLICATE KEY UPDATE quality = VALUES(quality), lv = VALUES(lv),
                                              slot = VALUES(slot)")->execute(
            array($sid, intval($id), intval($quality), intval($lv), intval($slot)));
    }
}

if (!function_exists('tf_cleanup')) {
    function tf_cleanup($db, $sid) {
        $db->prepare("DELETE FROM player_tianfu WHERE sid = ?")->execute(array($sid));
        $db->prepare("DELETE FROM player_tianfu_log WHERE sid = ?")->execute(array($sid));
        $db->prepare("DELETE FROM playerdaoju WHERE sid = ?")->execute(array($sid));
        $db->prepare("DELETE FROM game1 WHERE sid = ?")->execute(array($sid));
        if (function_exists('player\\player_cache_clear')) {
            \player\player_cache_clear($sid);
        }
    }
}
