<?php
/**
 * test_linhcan_element.php — Test CLI core Vong Ngu Hanh (phan A = ham doc thuan tuy).
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_linhcan_element.php
 * Phan B (train/calc_bonuses) them o Task 3; phan C/D (break/wash) o Task 4/7.
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
require_once __DIR__ . '/../class/player.php'; // player.php tu require function/linhcan.php (:16); can adddj/player_cache_clear cho node_set + give_stone
$TESTSID = str_repeat('f', 32);

// A1: config
$d = \player\linhcan_element_defs();
tassert('A1 defs du 5 hanh', count($d), 5);
tassert('A1 kim atk/lv', \player\linhcan_element_def('kim')['per_lv']['atk'], 2);
tassert('A1 khong ton tai', \player\linhcan_element_def(' phong'), false);
tassert('A1 stage 4 cap 90', \player\linhcan_stage_cap(4), 90);
tassert('A1 stage 5 khong co', \player\linhcan_stage_cap(5), 0);
tassert('A1 da_map', \player\linhcan_da_id('kim') . ',' . \player\linhcan_da_id('hoa'), '35,32');
tassert('A1 dao nguoc ten da', \player\linhcan_da_id('tho'), 36);
tassert('A1 binh anh', \player\linhcan_binh_anh_id(), 37);
tassert('A1 train lv0', \player\linhcan_train_cost(0), array('stone'=>1,'linh_thach'=>100));
tassert('A1 train lv89', \player\linhcan_train_cost(89), array('stone'=>6,'linh_thach'=>1435));

// A2: node docsong (nhan vat chua tung mo he)
$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
$n = \player\linhcan_node_rows($TESTSID, $db);
tassert('A2 du 5 key', count($n), 5);
tassert('A2 thieu row = 0/0', $n['kim'], array('level'=>0,'stage'=>0));

// A3: node_set / get
tassert('A3 set ok', \player\linhcan_node_set($TESTSID, 'hoa', 7, 0, $db), true);
tassert('A3 get lai', \player\linhcan_node_get($TESTSID, 'hoa', $db), array('level'=>7,'stage'=>0));

// A4: dominant — level -> stage -> thu tu co dinh kim>moc>thuy>hoa>tho
$n0 = \player\linhcan_node_rows($TESTSID, $db); // hoa=7
tassert('A4 co lv', \player\linhcan_dominant($n0), 'hoa');
$zz = array('kim'=>array('level'=>0,'stage'=>0),'moc'=>array('level'=>0,'stage'=>0),'thuy'=>array('level'=>0,'stage'=>0),'hoa'=>array('level'=>0,'stage'=>0),'tho'=>array('level'=>0,'stage'=>0));
tassert('A4 tong 0 = rong', \player\linhcan_dominant($zz), '');
$eq = $zz; $eq['thuy']['level']=5; $eq['moc']['level']=5;
tassert('A4 hoa giai theo thu tu', \player\linhcan_dominant($eq), 'moc');
$st = $zz; $st['tho']['level']=5; $st['tho']['stage']=2; $st['kim']['level']=5; $st['kim']['stage']=1;
tassert('A4 stage cao hon thang', \player\linhcan_dominant($st), 'tho');

// A5: title — tung hinh thai (doi tu A3: hoa=7, con 0 → tong 7 <25 = Pham Can)
$t1 = \player\linhcan_title($n0); tassert('A5 tong 7 → Pham Can', $t1['id'], 'pham_can');
$a = $zz; foreach ($a as $k=>$v) { $a[$k]=array('level'=>30,'stage'=>1); }
tassert('A5 all 30 → Vien Man', \player\linhcan_title($a)['id'], 'vien_man');
$b = $a; foreach ($b as $k=>$v) { $b[$k]=array('level'=>70,'stage'=>3); }
tassert('A5 all 70 → Vo Cuc', \player\linhcan_title($b)['id'], 'vo_cuc');
$c = $zz; $c['kim']=array('level'=>40,'stage'=>1);
tassert('A5 chan linh', \player\linhcan_title($c)['id'], 'chan_linh');
tassert('A5 chan linh ten kem hanh', \player\linhcan_title($c)['name'], 'Chân Linh Căn · Kim');
$e2 = $zz; $e2['hoa']=array('level'=>30,'stage'=>1); $e2['tho']=array('level'=>35,'stage'=>1);
tassert('A5 song nghi', \player\linhcan_title($e2)['id'], 'song_nghi');
$f = $zz; $f['kim']=array('level'=>10,'stage'=>0); $f['thuy']=array('level'=>9,'stage'=>0);
tassert('A5 tong 19 van Pham Can (rule 5 cuoi rule 4)', \player\linhcan_title($f)['id'], 'pham_can');

// A6: milestone (percent KIEU FLOAT vi code dung floatval — plan ghi int, da doi test)
tassert('A6 tong 0', \player\linhcan_milestone(0)['percent'], 0.0);
tassert('A6 tong 59', \player\linhcan_milestone(59)['percent'], 0.0);
tassert('A6 tong 60', \player\linhcan_milestone(60)['percent'], 5.0);
tassert('A6 tong 399 next 400', \player\linhcan_milestone(399)['next_total'], 400);
tassert('A6 tong 400 het', \player\linhcan_milestone(400)['next_total'], 0);

$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
echo "\n" . ($fails === 0 ? "ALL PASS\n" : "SO FAIL = " . $fails . "\n");
exit($fails === 0 ? 0 : 1);
