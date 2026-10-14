<?php
/**
 * test_tianfu_gates.php — 4 helper cua 7 cong + (voi --live) 7 cong gan that vao code.
 * Chay: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_tianfu_gates.php
 *       them --live sau khi Step 3..9 da gan het 7 cong (can DB + nhan vat f*32)
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$fails = 0;
$ROOT  = dirname(__DIR__);
$LIVE  = in_array('--live', array_slice($argv, 1), true);
require_once $ROOT . '/class/player.php';

function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS $name\n"; }
    else {
        $fails++;
        echo "FAIL $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}
function tsection($t) { echo "\n--- $t ---\n"; }

// ===== 1) tianfu_luck_gate — cong 3 (drop): nhan tiep nguong roi =====
tsection('tianfu_luck_gate');
tassert('luck pct 0 giu nguyen',        \player\tianfu_luck_gate(0, 120), 120);
tassert('luck pct 30 gate 100',         \player\tianfu_luck_gate(30, 100), 130);
tassert('luck clamp 30 (truyen 99)',    \player\tianfu_luck_gate(99, 100), 130);
tassert('luck pct am -> coi nhu 0',     \player\tianfu_luck_gate(-5, 100), 100);
tassert('luck gate 0 -> 0',             \player\tianfu_luck_gate(30, 0), 0);
tassert('luck gate am -> giu nguyen',   \player\tianfu_luck_gate(30, -3), -3);
tassert('luck lam tron 15%/120 = 138',  \player\tianfu_luck_gate(15, 120), 138);
// Cong don toi da: Huyet Mach 15% (tran cua no) roi Thien Phu 30% -> spec §5 dong 3: +49.5%
tassert('luck ghep huyet mach 15%',
    \player\tianfu_luck_gate(30, \player\huyetmach_combat_luck_gate(15, 120)), 179);

// ===== 2) tianfu_forge_cost — cong 5: giam Linh Thach, san 1 =====
tsection('tianfu_forge_cost');
tassert('forge pct 0 giu nguyen',       \player\tianfu_forge_cost(0, 12345), 12345);
tassert('forge pct 30 / 1000 = 700',    \player\tianfu_forge_cost(30, 1000), 700);
tassert('forge clamp 30 (truyen 80)',   \player\tianfu_forge_cost(80, 1000), 700);
tassert('forge san 1 (cost 1)',         \player\tianfu_forge_cost(30, 1), 1);
tassert('forge san 1 (cost 2 -> 1.4)',  \player\tianfu_forge_cost(30, 2), 1);
tassert('forge cost 0 -> 0',            \player\tianfu_forge_cost(30, 0), 0);
tassert('forge cost am -> giu nguyen',  \player\tianfu_forge_cost(30, -5), -5);
tassert('forge lam tron 15%/1001 = 851', \player\tianfu_forge_cost(15, 1001), 851);

// ===== 3) tianfu_stamina_interval — cong 6: rut chu ky hoi, san 30s =====
tsection('tianfu_stamina_interval');
tassert('stamina pct 0 giu 300',        \player\tianfu_stamina_interval(0, 300), 300);
tassert('stamina pct 30 / 300 = 210',   \player\tianfu_stamina_interval(30, 300), 210);
tassert('stamina clamp 30 (truyen 75)', \player\tianfu_stamina_interval(75, 300), 210);
tassert('stamina mac dinh 300',         \player\tianfu_stamina_interval(30), 210);
tassert('stamina san 30 (iv 40 -> 28)', \player\tianfu_stamina_interval(30, 40), 30);
tassert('stamina iv 0 -> 0',            \player\tianfu_stamina_interval(30, 0), 0);

// ===== 4) settle_stamina tham so thu 6 — HAM THUAN, khong can DB =====
// [CHOT] cong 6: goi helper BEN TRONG settle_stamina de web (game.php:452) va cron
// (cron/stamina_recovery.php:51) di qua mot cho duy nhat, ma van giu ham thuan.
tsection('settle_stamina + staminaPct');
$now = 1700000000;
$t420 = date('Y-m-d H:i:s', $now - 420);
$A = \player\settle_stamina(0, 100, $t420, $now, 300);
tassert('khong pct: 420s/300 = 1 buoc', $A['steps'], 1);
tassert('khong pct: moc = lastTs+300',  $A['mark'], $now - 120);
$B = \player\settle_stamina(0, 100, $t420, $now, 300, 30.0);
tassert('pct 30: 420s/210 = 2 buoc',    $B['steps'], 2);
tassert('pct 30: gia tri = 2',          $B['value'], 2);
tassert('pct 30: moc = lastTs+420',     $B['mark'], $now);
$C = \player\settle_stamina(99, 100, $t420, $now, 300, 30.0);
tassert('pct 30: 99/100 + 2 = 100 (tran)', $C['value'], 100);
$D = \player\settle_stamina(0, 0, $t420, $now, 300, 30.0);
tassert('max 0: khong hoi, khong fatal', $D['steps'], 0);

echo "\n" . ($fails === 0 ? "ALL PASS (thuan)\n" : "FAILS: $fails\n");
if (!$LIVE) { exit($fails === 0 ? 0 : 1); }

// =====================================================================
//  PHAN --live: 7 cong gan THAT vao code. Chi chay sau khi Step 3..9 xong.
//  Can: MySQL local + dev server 8091 (doi cong bang bien moi truong TP_BASE).
//  Nhan vat test = sid 'f'x32 / uid 990112 (le cua he Thien Phu, khong dung
//  fixture e2e chung de khoi lam lech test cua Huyết Mạch / Linh Căn).
// =====================================================================
require_once $ROOT . '/tests/tianfu_test_lib.php';
require_once $ROOT . '/class/encode.php';

$db      = tf_test_db();
$TESTSID = str_repeat('f', 32);
$TESTUID = 990112;
tf_cleanup($db, $TESTSID);
tf_seed_game1($db, $TESTSID, $TESTUID, 'TFGATE', 1000, 500);

// ----- L1: tianfu_bonus_pct doc DB dung cong, clamp dung tran -----
tsection('L1 tianfu_bonus_pct (DB)');
// Chup TRUOC khi seed: nhan vat tf_seed_game1() co ugj/umaxhp = 0, nen khong the assert
// "> 0" — chi co the assert "KHONG DOI" (ranh gioi spec §1: thien phu khong cong 5 chi so).
$tpPl0  = \player\getplayer($TESTSID, $db);
$tpUgj0 = intval($tpPl0->ugj);
$tpHp0  = intval($tpPl0->umaxhp);
// id 16 = Than Suat Qua Nhan, gate stamina, per_lv 0.25; q10 mult 3.50; lv 60 = tran pham.
// raw = 60*0.25*3.50 = 52.5 ; tong lv 60 -> milestone 1 (+5%) -> 55.125 -> clamp cap_percent 30.
tf_ins_owned($db, $TESTSID, 16, 10, 60, 1);
\player\player_cache_clear($TESTSID);
tassert('L1 stamina cham tran 30.0', \player\tianfu_bonus_pct($TESTSID, $db, 'stamina'), 30.0);
tassert('L1 cong la tra 0.0',        \player\tianfu_bonus_pct($TESTSID, $db, 'khong_ton_tai'), 0.0);
tassert('L1 exp_cultivate van 0.0',  \player\tianfu_bonus_pct($TESTSID, $db, 'exp_cultivate'), 0.0);
$tpPl = \player\getplayer($TESTSID, $db);
tassert('L1 getplayer hydrate stamina', $tpPl->tianfu_bonus['stamina'], 30.0);
tassert('L1 khong cong ugj',            intval($tpPl->ugj),    $tpUgj0);
tassert('L1 khong cong umaxhp',         intval($tpPl->umaxhp), $tpHp0);

// ----- L2: settle_stamina lay pct that tu DB -----
tsection('L2 settle_stamina (DB)');
$tpNow  = 1700000000;
$tpT420 = date('Y-m-d H:i:s', $tpNow - 420);
$tpPct  = \player\tianfu_bonus_pct($TESTSID, $db, 'stamina');
$tpS    = \player\settle_stamina(0, 100, $tpT420, $tpNow, 300, $tpPct);
tassert('L2 pct 30: 420s/210 = 2 buoc', $tpS['steps'], 2);
tassert('L2 moc nhich dung 420s',       $tpS['mark'],  $tpNow);
$tpS0 = \player\settle_stamina(0, 100, $tpT420, $tpNow, 300, 0);
tassert('L2 khong pct van 1 buoc',      $tpS0['steps'], 1);

// ----- L3: luyen dan cong alchemy, tran 95 van giu -----
tsection('L3 luonden_calc_success_rate');
// KHONG dat ->sid: luonden.php:337-339 chi tinh phan phe khi $sid > 0, ma sid hex
// ep kieu int = 0 nen nhanh do luon bo qua. Dung stdClass khong sid de ket qua xac
// dinh va khong can $GLOBALS['dblj'].
// floatval() boc ngoai vi $tpAlchemy la float: 50 + 30.0 = 80.0 (float), tassert dung ===.
$tpAl = new stdClass();
$tpAl->linhcan_element = '';
$tpAl->tianfu_bonus    = array('alchemy' => 30.0);
$tpR50 = array('base_success_rate' => 50);
$tpR70 = array('base_success_rate' => 70);
tassert('L3 base 50 + 30 = 80',       floatval(\player\luonden_calc_success_rate($tpAl, $tpR50)), 80.0);
tassert('L3 base 70 + 30 -> tran 95', floatval(\player\luonden_calc_success_rate($tpAl, $tpR70)), 95.0);
$tpAl->tianfu_bonus = array();
tassert('L3 khong cong: base 50 = 50', floatval(\player\luonden_calc_success_rate($tpAl, $tpR50)), 50.0);
$tpAl2 = new stdClass();
tassert('L3 khong co tianfu_bonus van chay', floatval(\player\luonden_calc_success_rate($tpAl2, $tpR50)), 50.0);

// ----- L5: HTTP doi chieu TRUOC / SAU tren 2 trang that -----
tsection('L5 HTTP xiulian + tupo');
$enc  = new \encode\encode();
$base = getenv('TP_BASE');
if (!is_string($base) || $base === '') { $base = 'http://127.0.0.1:8091'; }
// Blob cmd DUNG MOT LAN (game.php chong replay) -> encode lai moi lan goi.
$tpget = function ($plain) use ($enc, $base, $TESTSID) {
    return (string) shell_exec('curl -s ' . escapeshellarg($base . '/game.php?cmd=' . $enc->encode($plain . '&sid=' . $TESTSID)));
};
// \x27 = dau nhay don trong PCRE: chuoi PHP nay la single-quote nen khong dung ' truc tiep.
$tpReadXl = function ($html) {
    // game/xiulian.php:507  put('xlexp', (totalSec / 60 * <exp_per_min>).toLocaleString(...
    return preg_match('/put\(\x27xlexp\x27, \(totalSec \/ 60 \* ([0-9.]+)\)/', $html, $m) ? floatval($m[1]) : -1.0;
};
$tpReadTp = function ($html) {
    // game/tupo.php:353  <div class="break-ring-inner"><b>NN%</b>
    return preg_match('/break-ring-inner"><b>([0-9]+)%/', $html, $m) ? intval($m[1]) : -1;
};

// L5a — exp_cultivate: xoa sach -> do TRUOC; seed id 1 q10 lv60 -> do SAU (ky vong +30%)
$db->prepare("DELETE FROM player_tianfu WHERE sid = ?")->execute(array($TESTSID));
// [do that 2026-10-14] Dong put('xlexp', ...) nam trong `if ($running)` (game/xiulian.php:487),
// ma $running = sfxl > 0 (:235) -> phai bat tu luyen truoc khi do.
// xiuliantime PHAI ghi bang dong ho PHP (date()): MySQL NOW() lech +7/+8 so voi date() cua
// game.php, neu dung DATE_SUB(NOW()) thi diff ~56 phut >= $real_max_min (30) -> trang tu
// dong ket thuc (sfxl = 0) va block chay cua $running khong bao gio render.
$db->prepare("UPDATE game1 SET sfxl = 1, xiuliantime = ? WHERE sid = ?")->execute(array(date('Y-m-d H:i:s', time() - 60), $TESTSID));
\player\player_cache_clear($TESTSID);
// Route THAT trong game.php la 'goxiulian' (:316) — 'xiulian' khong ton tai, bi roi ve trang lang.
$htmlA0 = $tpget('cmd=goxiulian');
tassert('L5a trang xiulian tra ve that', strlen($htmlA0) > 500, true);
$xlBefore = $tpReadXl($htmlA0);
tassert('L5a doc duoc exp_per_min', $xlBefore > 0, true);

tf_ins_owned($db, $TESTSID, 1, 10, 60, 1);
\player\player_cache_clear($TESTSID);
$htmlA1 = $tpget('cmd=goxiulian');
$xlAfter = $tpReadXl($htmlA1);
tassert('L5a exp_per_min TANG', $xlAfter > $xlBefore, true);
// Hai lan round (exp_per_sec 4 so, exp_per_min 2 so) -> dung dai +/-10%, khong dung ===
tassert('L5a tang ~30% (>=1.25)', $xlAfter >= $xlBefore * 1.25, true);
tassert('L5a tang ~30% (<=1.35)', $xlAfter <= $xlBefore * 1.35, true);

// L5b — breakthrough: TRUOC roi SAU voi id 13 q10 lv60 (+30 diem %, tran 99)
$db->prepare("DELETE FROM player_tianfu WHERE sid = ?")->execute(array($TESTSID));
\player\player_cache_clear($TESTSID);
$htmlB0 = $tpget('cmd=tupo');
tassert('L5b trang tupo tra ve that', strlen($htmlB0) > 500, true);
$tpBefore = $tpReadTp($htmlB0);
tassert('L5b doc duoc ty le vong tron', $tpBefore >= 0, true);

tf_ins_owned($db, $TESTSID, 13, 10, 60, 1);
\player\player_cache_clear($TESTSID);
$htmlB1 = $tpget('cmd=tupo');
$tpAfter = $tpReadTp($htmlB1);
tassert('L5b ty le KHONG giam', $tpAfter >= $tpBefore, true);
tassert('L5b tran 99 duoc ton trong', $tpAfter <= 99, true);
tassert('L5b co o Thien Phu trong .break-bless',
    strpos($htmlB1, '<span>Thiên Phú</span><b>+30%</b>') !== false, true);
tassert('L5b bang giai thich khong thua o', substr_count($htmlB1, '<span>Thiên Phú</span>'), 1);

// ----- L9: don dep — tra lai DB sach cho lan chay ke tiep -----
tsection('L9 don dep');
tf_cleanup($db, $TESTSID);
$cnt = $db->prepare("SELECT COUNT(*) FROM player_tianfu WHERE sid = ?");
$cnt->execute(array($TESTSID));
tassert('L9 player_tianfu sach', intval($cnt->fetchColumn()), 0);

echo "\n" . ($fails === 0 ? "ALL PASS (thuan + live)\n" : "FAILS: $fails\n");
exit($fails === 0 ? 0 : 1);
