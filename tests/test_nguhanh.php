<?php
/**
 * test_nguhanh.php — CLI test tính năng Nạp Ngũ Hành, chạy trên DB local `game`.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_nguhanh.php
 * Dùng sid giả $TESTSID (32 ký tự f) — không đụng nhân vật thật.
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$ROOT = dirname(__DIR__);
require_once $ROOT . '/class/player.php';
require_once $ROOT . '/class/encode.php';
require_once $ROOT . '/class/function/nap_hanh.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== A: ham thuan =====
tassert('parse none',  \player\zhuangbei_elements_parse('none'),        array());
tassert('parse junk',  \player\zhuangbei_elements_parse('foo,bar'),     array());
tassert('parse order', \player\zhuangbei_elements_parse('thuy,kim'),    array('kim', 'thuy'));
tassert('parse dup',   \player\zhuangbei_elements_parse('kim,kim,moc'), array('kim', 'moc'));
tassert('join empty',  \player\zhuangbei_elements_join(array()),        'none');
tassert('join order',  \player\zhuangbei_elements_join(array('tho', 'kim')), 'kim,tho');
tassert('join junk',   \player\zhuangbei_elements_join(array('nuoc', 'kim')), 'kim');

$cfg = \player\nguhanh_cfg();
tassert('cfg tiers', count($cfg['tiers']), 5);
tassert('cfg pct',   count($cfg['bonus_pct']), 5);

// cost: thach_base 500 * rarity * (1 + n); đá = 1 + n viên
tassert('cost n0', \player\nap_hanh_cost('none', 3, 'thuy'),
    array('stone_djid' => 33, 'stone_qty' => 1, 'linhthach' => 1500, 'n' => 0));
tassert('cost n1', \player\nap_hanh_cost('thuy', 2, 'hoa'),
    array('stone_djid' => 32, 'stone_qty' => 2, 'linhthach' => 2000, 'n' => 1));
tassert('cost bad he', \player\nap_hanh_cost('none', 1, 'nuoc'),
    array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => 0));
tassert('cost dup he', \player\nap_hanh_cost('thuy', 1, 'thuy'),
    array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => 1));

// rate: 90 - 10n + bonus linh can, san 40 tran 95
tassert('rate n0',        \player\nap_hanh_rate('none', ''),               90);
tassert('rate n0 kim lc', \player\nap_hanh_rate('none', 'kim'),            95);
tassert('rate n1 hoa lc', \player\nap_hanh_rate('kim', 'hoa'),             90);
tassert('rate n4 moc lc', \player\nap_hanh_rate('kim,moc,thuy,hoa', 'moc'), 55);

// ===== A2: nguhanh_apply_passive tren object gia =====
function fakeZb($element, $gj, $fy, $hp, $bj, $xx) {
    $o = new stdClass();
    $o->element = $element; $o->zbgj = $gj; $o->zbfy = $fy;
    $o->zbhp = $hp; $o->zbbj = $bj; $o->zbxx = $xx;
    return $o;
}
function fakePlayer($lc) {
    $o = new stdClass();
    $o->linhcan_element = $lc;
    $o->ugj = 1000; $o->ufy = 1000; $o->umaxhp = 1000; $o->ubj = 1000; $o->uxx = 1000;
    $o->uyxb = 5000; $o->uid = 12345;
    return $o;
}
$zbsTest = array(
    fakeZb('thuy', 100, 0, 0, 0, 400),      // hop (chua thuy)
    fakeZb('kim,thuy', 50, 0, 0, 0, 0),     // hop (chua thuy)
    fakeZb('kim', 999, 0, 0, 0, 0),         // khong hop voi thuy
    fakeZb('none', 500, 0, 0, 0, 0)         // khong he
);
$plThuy = fakePlayer('thuy');
tassert('match thuy=2',  \player\nguhanh_match_count($plThuy, $zbsTest), 2);
\player\nguhanh_apply_passive($plThuy, $zbsTest);
// bac 1 (n=2 < 3): +3% tren chi so trang bi dong gop; elsInPlay = {thuy, kim}
tassert('passive uxx +12', $plThuy->uxx, 1012); // (400 + 0) * 3% = 12
tassert('passive ugj +4',  $plThuy->ugj, 1004); // (100 + 50) * 3% = 4 (int)
tassert('info tier', $plThuy->nguhanh_info['tier'], 1);
tassert('info pct',  $plThuy->nguhanh_info['pct'], 3);

$plAll = fakePlayer('all');
tassert('match all=3', \player\nguhanh_match_count($plAll, $zbsTest), 3);

$plNone = fakePlayer('none');
tassert('match none=0', \player\nguhanh_match_count($plNone, $zbsTest), 0);
\player\nguhanh_apply_passive($plNone, $zbsTest);
tassert('none khong cong', $plNone->ugj, 1000);

// ===== B: tich hop DB (nhan vat gia TESTSID) =====
$db = new PDO('mysql:host=localhost;dbname=game;charset=utf8', 'root', 'www.aae.ink',
    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
$TESTSID = 'ffffffffffffffffffffffffffffffff';
$TESTUID = 987654;

function nh_cleanup($db, $TESTSID) {
    $db->exec("DELETE FROM playerzhuangbei WHERE sid = '$TESTSID'");
    $db->exec("DELETE FROM playerdaoju WHERE sid = '$TESTSID'");
    $db->exec("DELETE FROM player_chetao_log WHERE sid = '$TESTSID'");
    $db->exec("DELETE FROM game1 WHERE sid = '$TESTSID'");
    \player\player_cache_clear($TESTSID);
    \player\zhuangbei_cache_clear();
}
nh_cleanup($db, $TESTSID);

// game1: tu sinh INSERT du moi cot NOT NULL khong co default (doc truc tiep
// information_schema de khong lech so cot khi schema doi — tool7 co default nen bo qua).
$colsNN = $db->query("SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'game' AND TABLE_NAME = 'game1'
          AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
          AND EXTRA NOT LIKE '%auto_increment%'")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array(); $colVals = array();
foreach ($colsNN as $c) {
    $n = $c['COLUMN_NAME'];
    $colNames[] = $n;
    if ($n === 'sid')         { $colVals[] = $db->quote($TESTSID); continue; }
    if ($n === 'uid')         { $colVals[] = intval($TESTUID); continue; }
    if ($n === 'token')       { $colVals[] = $db->quote('test'); continue; }
    if ($n === 'uname')       { $colVals[] = $db->quote('test_nguhanh'); continue; }
    if ($n === 'endtime')     { $colVals[] = $db->quote(date('Y-m-d H:i:s', time() + 86400)); continue; }
    $isStr = in_array($c['DATA_TYPE'], array('varchar', 'text', 'char', 'mediumtext'));
    $colVals[] = $isStr ? "''" : "0";
}
$colNames[] = 'uyxb'; $colVals[] = '100000';
$colNames[] = 'ulv';  $colVals[] = '100';
// uid co default trong DB nen khong nam trong danh sach NOT NULL-phai-nap; phai ghi them
// neu khong thi $player->uid = 0 va cac phep kiem tra chu so hinh ($owner) sai het.
if (!in_array('uid', $colNames, true)) { $colNames[] = 'uid'; $colVals[] = intval($TESTUID); }
if (!in_array('uname', $colNames, true)) { $colNames[] = 'uname'; $colVals[] = $db->quote('test_nguhanh'); }
$db->exec("INSERT INTO game1 (" . implode(', ', $colNames) . ") VALUES (" . implode(', ', $colVals) . ")");
// Neu dong này FAIL nghĩa là fixture chưa ghi duoc uid → moi kiem tra chu so hieu ($owner) deu sai.
tassert('fixture uid', intval($db->query("SELECT uid FROM game1 WHERE sid = '$TESTSID'")->fetchColumn()), $TESTUID);

$insZb = $db->prepare("INSERT INTO playerzhuangbei (zbid, uid, sid, qianghua, zblv, zbtool,
    zbname, zbinfo, zbgj, zbfy, zbhp, zbbj, zbxx)
    VALUES (1, ?, ?, 0, 0, 0, 'Kiem Test Ngu Hanh', '', 100, 50, 300, 10, 20)");
$insZb->execute(array($TESTUID, $TESTSID));
$zbnowid = intval($db->lastInsertId());
$insDj = $db->prepare("INSERT INTO playerdaoju (djid, djsum, sid, uid, djname, djzl, djinfo)
    VALUES (33, 10, ?, ?, 'Thuy Tinh Thach', 1, 'test')");
$insDj->execute(array($TESTSID, $TESTUID));

$zbCfg1 = \player\getzbkzb(1, $db);
$expLinh1 = 500 * max(1, intval($zbCfg1->rarity)) * 1; // lan 1: n=0, 1 da

// 1) Ep roll FAIL (91 > rate 90): van mat 1 da + linh thach, element khong doi
$GLOBALS['nguhanh_force_roll'] = 91;
$r = \player\nap_hanh_do($TESTSID, $zbnowid, 'thuy', $db);
tassert('do fail ok=false',   $r['ok'], false);
tassert('do fail element',    $r['element'], 'none');
tassert('do fail rate 90',    $r['rate'], 90);
tassert('fail van mat 1 da',
    intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=33")->fetchColumn()), 9);
tassert('fail van tru LT',
    intval($db->query("SELECT uyxb FROM game1 WHERE sid='$TESTSID'")->fetchColumn()), 100000 - $expLinh1);

// 2) Ep roll SUCCESS
$GLOBALS['nguhanh_force_roll'] = 50;
$r = \player\nap_hanh_do($TESTSID, $zbnowid, 'thuy', $db);
tassert('do success ok=true', $r['ok'], true);
tassert('do success element', $r['element'], 'thuy');
tassert('db element raw',
    (string)$db->query("SELECT element FROM playerzhuangbei WHERE zbnowid = $zbnowid")->fetchColumn(), 'thuy');

// 3) Nap trung he: tu choi, KHONG tieu hao them (da van la 8 sau 2 lan burn)
$r = \player\nap_hanh_do($TESTSID, $zbnowid, 'thuy', $db);
tassert('dup reject', $r['ok'], false);
tassert('dup msg',    strpos($r['msg'], 'đã mang hệ') !== false, true);
tassert('dup khong mat da',
    intval($db->query("SELECT djsum FROM playerdaoju WHERE sid='$TESTSID' AND djid=33")->fetchColumn()), 8);

// 4) He la + quyen so
$r = \player\nap_hanh_do($TESTSID, $zbnowid, 'nuoc', $db);
tassert('bad he msg', strpos($r['msg'], 'không hợp lệ') !== false, true);
$r = \player\nap_hanh_do(str_repeat('0', 32), $zbnowid, 'kim', $db);
tassert('not owner msg', strpos($r['msg'], 'không thuộc về') !== false, true);

// 5) Log nap_hanh (1 fail + 1 success)
tassert('log rows',
    intval($db->query("SELECT COUNT(*) FROM player_chetao_log WHERE sid='$TESTSID' AND log_type='nap_hanh'")->fetchColumn()), 2);

// ===== (Task 3) element DB de config qua merge =====
$db->exec("UPDATE playerzhuangbei SET element = 'kim,thuy' WHERE zbnowid = $zbnowid");
\player\zhuangbei_cache_clear($zbnowid);
tassert('merge kim,thuy', \player\getzb($zbnowid, $db)->element, 'kim,thuy');

$db->exec("UPDATE playerzhuangbei SET element = 'lum' WHERE zbnowid = $zbnowid");
\player\zhuangbei_cache_clear($zbnowid);
tassert('merge junk -> none', \player\getzb($zbnowid, $db)->element, 'none');

// ===== (Task 3) zb_element_set + dai dien khi dang mac =====
$db->exec("UPDATE playerzhuangbei SET element = 'thuy' WHERE zbnowid = $zbnowid");
$db->exec("UPDATE game1 SET tool1 = $zbnowid WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);
\player\zhuangbei_cache_clear($zbnowid);
$plT3 = \player\getplayer($TESTSID, $db);
tassert('set thuy',   $plT3->zb_element_set, array('thuy'));
tassert('dai dien',   $plT3->zb_element, 'thuy');
tassert('info ton tai', isset($plT3->nguhanh_info), true);
tassert('info match 0 (linh can rong)', intval($plT3->nguhanh_info['match_count']), 0);
$db->exec("UPDATE game1 SET tool1 = 0 WHERE sid = '$TESTSID'");
\player\player_cache_clear($TESTSID);

// ===== (Task 3) zhuangbei_pick_element =====
tassert('pick khac',          \player\zhuangbei_pick_element(array('kim', 'thuy'), 'moc'), 'kim');
tassert('pick thu tu chuan',  \player\zhuangbei_pick_element(array('thuy', 'hoa'), 'hoa'), 'thuy');
tassert('pick sau khi quet',  \player\zhuangbei_pick_element(array('kim', 'hoa'), 'kim'), 'hoa');
tassert('pick khong he nao',  \player\zhuangbei_pick_element(array('kim'), 'tho'), '');
tassert('pick set rong',      \player\zhuangbei_pick_element(array(), 'hoa'), '');
tassert('pick mc rong',       \player\zhuangbei_pick_element(array('kim'), ''), '');

// ===== (Task 6) itemtip payload: elem la danh sach tag, khong con 1 he duy nhat =====
$tipMulti = \player\itemtip_payload(fakeZb('kim,thuy', 10, 0, 0, 0, 0), null, null);
tassert('tip 2 tag',        count($tipMulti['elem']), 2);
tassert('tip tag0 ten',     $tipMulti['elem'][0][1], \player\zhuangbei_element_name('kim'));
tassert('tip tag1 ten',     $tipMulti['elem'][1][1], \player\zhuangbei_element_name('thuy'));
tassert('tip tag0 icon',    $tipMulti['elem'][0][0], \player\zhuangbei_element_icon('kim'));
tassert('tip tag1 mau',     $tipMulti['elem'][1][2], \player\zhuangbei_element_color_ui('thuy'));
$tipNone = \player\itemtip_payload(fakeZb('none', 0, 0, 0, 0, 0), null, null);
tassert('tip none rong',    count($tipNone['elem']), 0);
$tipJunk = \player\itemtip_payload(fakeZb('khi' . chr(244) . ',hoa', 0, 0, 0, 0, 0), null, null);
tassert('tip junk bi loc',  count($tipJunk['elem']), 1);

// ===== (Task 6b) section "Thuộc tính Ngũ Hành": moi hanh mot dong, hop linh can duoc danh dau =====
$nhEnc = new \encode\encode();
$nhZb  = fakeZb('thuy', 0, 0, 0, 0, 0);
$nhZb->zbnowid = 999001; $nhZb->rarity = 3;
$nhPl  = fakePlayer('kim');
$nhHtml = \player\nap_hanh_section_html($nhZb, $nhPl, $TESTSID, $db, $nhEnc);
tassert('nh title',      strpos($nhHtml, 'Thuộc tính Ngũ Hành') !== false, true);
tassert('nh 4 dong',     substr_count($nhHtml, 'class="nh-row"'), 4);
tassert('nh chip hanh hop linh can', substr_count($nhHtml, 'nh-chip'), 1);
tassert('nh da co hanh',   strpos($nhHtml, 'Đã có 1/5 hành') !== false, true);
$nhFull = \player\nap_hanh_section_html(fakeZb('kim,moc,thuy,hoa,tho', 0, 0, 0, 0, 0), $nhPl, $TESTSID, $db, $nhEnc);
tassert('nh du 5 het nut', substr_count($nhFull, 'class="nh-row"'), 0);
tassert('nh du 5 notice',  strpos($nhFull, 'Đã đủ 5 hành') !== false, true);
// nut "Kích hoạt" (không còn "Nạp 95%"), tỉ lệ chuyển xuống dòng chi phí
tassert('nh nut kich hoat', substr_count($nhHtml, '>Kích hoạt</a>'), 4);
tassert('nh het nut nap %', substr_count($nhHtml, '>Nạp '), 0);
tassert('nh ti le gan vao cost', substr_count($nhHtml, 'class="nh-rate"'), 4);

// ===== (Task 8b) popup kết quả rèn =====
$nhOutWin = array('ok' => true, 'msg' => 'Kích hoạt hành Kim thành công!', 'rate' => 95,
                  'element' => 'kim,thuy', 'he' => 'kim',
                  'cost' => array('stone_djid' => 35, 'stone_qty' => 2, 'linhthach' => 3000, 'n' => 1),
                  'zbname' => 'Kiếm Thanh Phong', 'zbys' => '#4ade80');
$nhWin = \player\nap_hanh_result_html($nhOutWin, 999001, fakePlayer('kim'), $TESTSID, $db, $nhEnc);
tassert('res win title',   strpos($nhWin, 'Rèn Ngũ Hành — Kim') !== false, true);
tassert('res win status',  strpos($nhWin, 'pve-result-status win') !== false, true);
tassert('res win text',    strpos($nhWin, 'Kích hoạt hành Kim thành công!') !== false, true);
tassert('res win zbname',  strpos($nhWin, 'Kiếm Thanh Phong') !== false, true);
tassert('res win khong raw kim', strpos($nhWin, '>kim<') === false, true);
tassert('res win 2 badge', substr_count($nhWin, 'class="nh-badge"'), 2);
tassert('res win hop linh can', strpos($nhWin, 'trùng linh căn') !== false, true);
tassert('res win back btn', strpos($nhWin, 'Xem lại trang bị') !== false, true);

$nhOutLose = $nhOutWin; $nhOutLose['ok'] = false;
$nhOutLose['msg'] = 'Kích hoạt hành Kim thất bại — đã mất nguyên liệu.'; $nhOutLose['element'] = 'thuy';
$nhLose = \player\nap_hanh_result_html($nhOutLose, 999001, fakePlayer('moc'), $TESTSID, $db, $nhEnc);
tassert('res lose status', strpos($nhLose, 'pve-result-status lose') !== false, true);
tassert('res lose hint',   strpos($nhLose, 'chỉ mất nguyên liệu') !== false, true);
tassert('res lose 1 badge', substr_count($nhLose, 'class="nh-badge"'), 1);

$nhOutBlock = array('ok' => false, 'msg' => 'Linh thạch không đủ: cần 3000.', 'rate' => 0,
                   'element' => 'none', 'he' => 'hoa',
                   'cost' => array('stone_djid' => 0, 'stone_qty' => 0, 'linhthach' => 0, 'n' => 0));
$nhBlock = \player\nap_hanh_result_html($nhOutBlock, 999001, fakePlayer('none'), $TESTSID, $db, $nhEnc);
tassert('res block status', strpos($nhBlock, 'pve-result-status block') !== false, true);
tassert('res block khong co dong ti le', strpos($nhBlock, 'Tỉ lệ rèn') === false, true);
tassert('res block empty elems', strpos($nhBlock, 'Chưa có hành nào') !== false, true);

unset($GLOBALS['nguhanh_force_roll']);
nh_cleanup($db, $TESTSID);
echo $fails === 0 ? "ALL PASS\n" : "FAILS: $fails\n";
exit($fails === 0 ? 0 : 1);
