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
// ===== B: train =====
$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
$db->exec("DELETE FROM playerdaoju WHERE sid='" . $TESTSID . "' AND djid=35");
$db->exec("INSERT INTO playerdaoju (sid, uid, djid, djsum) VALUES ('" . $TESTSID . "', 990112, 35, 30)");
$db->exec("UPDATE game1 SET uyxb = 100000 WHERE sid='" . $TESTSID . "'");
\player\linhcan_node_set($TESTSID, 'kim', 0, 0, $db);
$r = \player\linhcan_train($TESTSID, $db, 'kim');
tassert('B1 train lv0 ok', $r['ok'], true);
tassert('B1 lv=1', $r['lv'], 1);
$pj = \player\getplayerdaoju($TESTSID, 35, $db);
tassert('B1 tru 1 da', intval($pj->djsum), 29);
$db->exec("UPDATE game1 SET uyxb = 50 WHERE sid='" . $TESTSID . "'");
$r2 = \player\linhcan_train($TESTSID, $db, 'kim');
tassert('B2 thieu LT fail', $r2['ok'], false);
$pj2 = \player\getplayerdaoju($TESTSID, 35, $db);
tassert('B2 da khong bi truu oan', intval($pj2->djsum), 29);
\player\linhcan_node_set($TESTSID, 'kim', 15, 0, $db);
$db->exec("UPDATE game1 SET uyxb = 100000 WHERE sid='" . $TESTSID . "'");
$r3 = \player\linhcan_train($TESTSID, $db, 'kim');
tassert('B3 chan tran stage0', $r3['ok'], false);
$tuTrain = \player\linhcan_train_cost_sum(0, 15);
tassert('B4 cost sum 0..14 stone', $tuTrain['stone'], 15);
tassert('B4 cost sum 0..14 lt', $tuTrain['linh_thach'], 1500 + 15 * (0+1+2+3+4+5+6+7+8+9+10+11+12+13+14));

// ===== C: hop dong calc_bonuses =====
$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
\player\linhcan_node_set($TESTSID, 'kim', 30, 1, $db);
\player\linhcan_node_set($TESTSID, 'thuy', 20, 0, $db);
$B = \player\linhcan_calc_bonuses($TESTSID, $db);
$keys = array('absorb_bonus','exp_bonus','quality','quality_mult','stats','pet_atk_bonus',
  'pet_hp_bonus','pet_exp_bonus','pet_crit_bonus','element','supported_cp_element',
  'penalty_atk_percent','penalty_def_percent','penalty_maxhp_percent','penalty_crit_percent','penalty_absorb_percent');
$missing = array();
foreach ($keys as $k) if (!array_key_exists($k, $B)) $missing[] = $k;
tassert('C1 du 16 key hop dong', implode(',', $missing), '');
tassert('C2 stats du 5 key', implode(',', array_keys($B['stats'])), 'atk,def,hp,crit,lifesteal');
// tong Lv 50 <60 -> milestone 0; kim stage1 -> atkMult 1.005; atk = 2*30*1.005 = 60.3 -> 60
tassert('C3 atk 60', $B['stats']['atk'], 60);
tassert('C4 absorb 0.18*20', round($B['absorb_bonus'], 4), 3.6);
tassert('C5 exp 0.08*20', round($B['exp_bonus'], 4), 1.6);
tassert('C6 element = chu dao', $B['element'], 'kim');
tassert('C7 quality = maxstage+1', $B['quality'], 2);
tassert('C8 khong nerf', $B['penalty_atk_percent'], 0);
tassert('C8b khong dat ten ham cu', function_exists('\player\linhcan_get_absorb'), true);
// tong 60 -> milestone 5%: dat them moc 10 lv (30+20+10=60)
\player\linhcan_node_set($TESTSID, 'moc', 10, 0, $db);
$B2 = \player\linhcan_calc_bonuses($TESTSID, $db);
// atk = round(60 * 1.05) = 63; hp = round(14*10*1.05)=147; absorb = round(3.6*1.05,4)=3.78
tassert('C9 milestone 5% nhan atk', $B2['stats']['atk'], 63);
tassert('C9 milestone 5% nhan hp', $B2['stats']['hp'], 147);
tassert('C9 milestone 5% nhan absorb', round($B2['absorb_bonus'], 4), 3.78);
$db->exec("DELETE FROM player_linhcan_node WHERE sid='" . $TESTSID . "'");
$db->exec("DELETE FROM playerdaoju WHERE sid='" . $TESTSID . "' AND djid=35");

// ===== D: tay linh dan (Task 7) =====
$db->exec("DELETE FROM player_linhcan_node WHERE sid = '" . $TESTSID . "'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '" . $TESTSID . "' AND djid IN (35, 111)");
$db->exec("UPDATE game1 SET uyxb = 0 WHERE sid = '" . $TESTSID . "'");
\player\linhcan_node_set($TESTSID, 'kim', 10, 1, $db);
\player\player_cache_clear($TESTSID);

// D1: chua co dan → fail, khong mat gi
$w0 = \player\linhcan_wash($TESTSID, $db, 'kim');
tassert('D1 khong co dan fail', $w0['ok'], false);
$n0 = \player\linhcan_node_get($TESTSID, 'kim', $db);
tassert('D1 node giu nguyen', $n0['level'] . '/' . $n0['stage'], '10/1');

// D2: du dan → reset Lv ve 0, GIU bac, hoan 80% da + 100% LT (ty le tinh tren cost_sum(0,10)=10 da/1675 LT)
\player\adddj($TESTSID, 111, 1, $db);
$w1 = \player\linhcan_wash($TESTSID, $db, 'kim');
tassert('D2 wash ok', $w1['ok'], true);
tassert('D2 stone_back = floor(10*0.8)', intval($w1['stone_back']), 8);
tassert('D2 lt_back = 1675', intval($w1['lt_back']), 1675);
$n1 = \player\linhcan_node_get($TESTSID, 'kim', $db);
tassert('D2 lv ve 0', intval($n1['level']), 0);
tassert('D2 stage VAN giu 1 (spec 2.5)', intval($n1['stage']), 1);
$da = \player\getplayerdaoju($TESTSID, 35, $db);
tassert('D2 da hoan vao inventory', intval($da->djsum), 8);
$lt = intval($db->query("SELECT uyxb FROM game1 WHERE sid = '" . $TESTSID . "'")->fetchColumn());
tassert('D2 LT hoan 100%', $lt, 1675);
$pill = $db->query("SELECT djsum FROM playerdaoju WHERE sid = '" . $TESTSID . "' AND djid = 111")->fetchColumn();
tassert('D2 dan bi tieu hao', intval($pill), 0);

// D3: hanh chua luyen → fail; hanh la → fail
$w2 = \player\linhcan_wash($TESTSID, $db, 'moc');
tassert('D3 moc lv0 fail', $w2['ok'], false);
\player\adddj($TESTSID, 111, 1, $db);
$w3 = \player\linhcan_wash($TESTSID, $db, 'khong_phai_hanh');
tassert('D3 hanh la fail', $w3['ok'], false);
$pill3 = $db->query("SELECT djsum FROM playerdaoju WHERE sid = '" . $TESTSID . "' AND djid = 111")->fetchColumn();
tassert('D3 fail khong truu dan', intval($pill3), 1);

$db->exec("DELETE FROM player_linhcan_node WHERE sid = '" . $TESTSID . "'");
$db->exec("DELETE FROM playerdaoju WHERE sid = '" . $TESTSID . "' AND djid IN (35, 111)");
echo "\n" . ($fails === 0 ? "ALL PASS\n" : "SO FAIL = " . $fails . "\n");
exit($fails === 0 ? 0 : 1);
