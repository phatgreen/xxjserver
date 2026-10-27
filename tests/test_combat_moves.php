<?php
/**
 * test_combat_moves.php — Thế võ (class/function/combat.php) + 2 file config. KHÔNG cần DB.
 * Chạy: "D:/XXJserver/phpstudy_pro/Extensions/php/php5.6.9nts/php.exe" tests/test_combat_moves.php
 */
error_reporting(E_ALL);
require_once __DIR__ . '/../class/function/combat.php';

$fails = 0;
function tassert($name, $got, $exp) {
    global $fails;
    if ($got === $exp) { echo "PASS  $name\n"; }
    else {
        $fails++;
        echo "FAIL  $name\n  got:  " . var_export($got, true) . "\n  want: " . var_export($exp, true) . "\n";
    }
}

// ===== C1: config đủ 3 thế cho mọi võ công trong wugong_config.json =====
$wgAll = json_decode(file_get_contents(__DIR__ . '/../class/config/wugong_config.json'), true);
$sk = \player\combat_cfg('wugong_skill_config.json');
tassert('C1 config the vo doc duoc', isset($sk['moves']) && isset($sk['rules']), true);
$missing = array();
$okEff = array('', 'pierce', 'crit', 'stun', 'guard', 'heal', 'lifesteal', 'burn');
$badEff = array();
foreach ($wgAll as $w) {
    if (intval($w['wgid']) <= 0) continue;
    $m = isset($sk['moves'][(string)$w['wgid']]) ? $sk['moves'][(string)$w['wgid']] : array();
    if (count($m) !== 3) $missing[] = $w['wgid'];
    foreach ($m as $mv) {
        if (!in_array($mv['effect'], $okEff, true)) $badEff[] = $w['wgid'] . ':' . $mv['effect'];
        if (strpos($mv['text'], '{mob}') === false) $badEff[] = $w['wgid'] . ':thieu {mob}';
    }
}
tassert('C1 moi vo cong co du 3 the', $missing, array());
tassert('C1 effect hop le + van co {mob}', $badEff, array());

// ===== C2: hệ số theo cấp võ công =====
$wg = new stdClass();
$wg->wgid = 2; $wg->wgname = 'Thập Tự Phong Đao'; $wg->rarity = 'nhap_mon'; $wg->wgdj = 0;
$mv = \player\combat_wugong_moves($wg);
tassert('C2 cap 0: Vo Ky mo', $mv[1]['unlocked'], true);
tassert('C2 cap 0: Tuyet Ky khoa', $mv[2]['unlocked'], false);
tassert('C2 cap 0: Tuyet Chieu khoa', $mv[3]['unlocked'], false);
tassert('C2 Vo Ky x1.0', $mv[1]['mult'], 1.0);
$wg->wgdj = 5;  $mv = \player\combat_wugong_moves($wg);
tassert('C2 cap 5: Tuyet Ky mo, x1.25', array($mv[2]['unlocked'], $mv[2]['mult']), array(true, 1.25));
$wg->wgdj = 10; $mv = \player\combat_wugong_moves($wg);
tassert('C2 cap 10: Tuyet Ky x1.425', $mv[2]['mult'], 1.43);
tassert('C2 cap 10: Tuyet Chieu mo, x1.8', array($mv[3]['unlocked'], $mv[3]['mult']), array(true, 1.8));
$wg->wgdj = 99; $mv = \player\combat_wugong_moves($wg);
tassert('C2 chan tran Tuyet Ky 1.6', $mv[2]['mult'], 1.6);
tassert('C2 chan tran Tuyet Chieu 2.6', $mv[3]['mult'], 2.6);
$wg->rarity = 'than_cong'; $mv = \player\combat_wugong_moves($wg);
tassert('C2 than cong +0.15', $mv[3]['mult'], 2.75);
tassert('C2 the luc x2 cho Tuyet Chieu', $mv[3]['stamina_mult'], 2.0);

// ===== C3: hồi chiêu theo lượt =====
// Dùng Tuyệt Chiêu (cd 4) ở lượt 3 => cd3 = 3 + 4 + 1 = 8; lượt 4..7 chờ, lượt 8 dùng được.
$st = array('ok' => true, 'turn' => 3, 'cd2' => 0, 'cd3' => 8);
tassert('C3 ngay sau khi dung: con 4 luot', \player\combat_move_wait($st, 3), 4);
$st['turn'] = 6;
tassert('C3 sau 3 luot: con 1', \player\combat_move_wait($st, 3), 1);
$st['turn'] = 7;
tassert('C3 luot 8 dung lai duoc', \player\combat_move_wait($st, 3), 0);
tassert('C3 Vo Ky khong hoi chieu', \player\combat_move_wait($st, 1), 0);

// ===== C4: bị động mạnh dần theo cấp =====
tassert('C4 cap 0 = 30%', \player\combat_passive_scale(0), 0.3);
tassert('C4 cap 10 = 100%', \player\combat_passive_scale(10), 1.0);
tassert('C4 cap 50 van 100%', \player\combat_passive_scale(50), 1.0);

// ===== C5: chiêu quái =====
$okMob = true;
foreach (array('none', 'kim', 'moc', 'thuy', 'hoa', 'tho', 'khong_co_he') as $el) {
    for ($i = 0; $i < 30; $i++) {
        $m = \player\combat_mob_move($el);
        if (!isset($m['text']) || $m['text'] === '' || $m['mult'] < 1.0) $okMob = false;
    }
}
tassert('C5 chieu quai moi he deu co van', $okMob, true);
tassert('C5 combat_text thay {mob}', \player\combat_text('{mob} ngã', 'Hồ Yêu'), 'Hồ Yêu ngã');
tassert('C5 mo ta hieu ung', \player\combat_effect_text('stun', 40), '40% làm địch mất lượt phản kích');

echo $fails === 0 ? "ALL PASS\n" : "FAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
