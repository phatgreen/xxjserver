<?php
/**
 * test_player_stats.php — Kiểm tra "tính toán dữ liệu người chơi".
 *
 * Tính lại ĐỘC LẬP chỉ số tổng (Công/Thủ/Khí huyết/Bạo kích/Hút máu/Chiến lực/EXP cần)
 * từ dữ liệu thô (game1 + playerzhuangbei + các bảng hệ thống) và file config JSON,
 * rồi so với \player\getplayer() (class/player.php). In chênh lệch từng chỉ số.
 *
 * 3 cấu hình trên 1 nhân vật test riêng (tự tạo, tự dọn):
 *   A. Trắng        : chỉ chỉ số gốc theo cấp/cảnh giới (lên cấp bằng đúng đường đột phá).
 *   B. Trang bị     : full 15 ô trang bị + cường hóa + ngũ hành (+ Thần Trang).
 *   C. Full hệ thống: B + võ công + công pháp + linh căn + thể chất (+Nhục Thân) + huyết mạch
 *                     (+Mạch) + thiên phú + tông môn + linh sủng.
 * Thêm các kiểm tra hồi quy cho lỗi phát hiện được (ngữ cảnh $GLOBALS['player'], cache võ công).
 *
 * Chạy: php tests/test_player_stats.php            (dọn nhân vật sau khi chạy)
 *       php tests/test_player_stats.php --keep     (giữ nhân vật để soi)
 *       php tests/test_player_stats.php --clean    (chỉ dọn)
 * PHP 5.6 tương thích.
 */
require_once __DIR__ . '/e2e_full_lib.php';
$db = $dblj;

$UNAME = 'E2EStats';
$TOKEN = 'e2efull' . md5('stats-token-2026-10-29');
$SID   = e2e_sid_for($UNAME, $TOKEN);
$LEVEL = 130;

if (in_array('--clean', $argv)) {
    echo 'Đã dọn ' . e2e_cleanup($db, $SID) . " dòng.\n";
    exit(0);
}

$fails = 0;
$report = array(); // cấu hình => [chỉ số => [kỳ vọng, thực tế]]

function st_cfg($name) {
    static $c = array();
    if (!isset($c[$name])) {
        $c[$name] = json_decode(file_get_contents(dirname(__DIR__) . '/class/config/' . $name), true);
    }
    return $c[$name];
}

/**
 * Tính lại độc lập. $scaleCtx = jingjieValue mà Thể chất / Huyết mạch dùng (xem ghi chú lỗi #STAT-1:
 * code đọc $GLOBALS['player']->jingjieValue — trong 1 request mới của game.php giá trị này = 0).
 * Trả mảng chỉ số + chi tiết từng nguồn.
 */
function st_expected($db, $sid, $scaleCtx) {
    $row = e2e_row($db, $sid);
    $atk = intval($row['ugj']); $def = intval($row['ufy']); $hp = intval($row['umaxhp']);
    $crit = intval($row['ubj']); $ls = intval($row['uxx']);
    $src = array('goc' => array($atk, $def, $hp, $crit, $ls));

    // ---- 1. Trang bị: chỉ số = max(DB, JSON) (JSON gốc + phần cường hóa lưu ở DB) ----
    $zbCfg = st_cfg('zhuangbei_config.json');
    $equipped = array();
    $e = array(0, 0, 0, 0, 0);
    for ($t = 1; $t <= 16; $t++) {
        $id = intval($row['tool' . $t]);
        if ($id <= 0) continue;
        $st = $db->prepare("SELECT * FROM playerzhuangbei WHERE zbnowid = ?");
        $st->execute(array($id));
        $z = $st->fetch(PDO::FETCH_ASSOC);
        if (!$z) continue;
        $c = isset($zbCfg[(string)$z['zbid']]) ? $zbCfg[(string)$z['zbid']] : array();
        $v = array();
        foreach (array('zbgj', 'zbfy', 'zbhp', 'zbbj', 'zbxx') as $f) {
            $j = isset($c[$f]) ? intval($c[$f]) : intval($z[$f]);
            $v[$f] = max($j, intval($z[$f]));
        }
        $els = array();
        foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $el) {
            if (in_array($el, array_map('trim', explode(',', (string)$z['element'])), true)) $els[] = $el;
        }
        $equipped[] = array('v' => $v, 'els' => $els,
            'tz' => isset($c['taozhuang']) ? intval($c['taozhuang']) : intval($z['taozhuang']));
        $e[0] += $v['zbgj']; $e[1] += $v['zbfy']; $e[2] += $v['zbhp']; $e[3] += $v['zbbj']; $e[4] += $v['zbxx'];
    }
    $atk += $e[0]; $def += $e[1]; $hp += $e[2]; $crit += $e[3]; $ls += $e[4];
    $src['trang_bi'] = $e;

    // ---- 2. Võ công đang luyện: add_* × cấp võ công ----
    $w = array(0, 0, 0, 0, 0);
    if (intval($row['wugong']) > 0) {
        $st = $db->prepare("SELECT wgdj FROM playerwugong WHERE sid = ? AND wgid = ?");
        $st->execute(array($sid, intval($row['wugong'])));
        $dj = $st->fetchColumn();
        if ($dj !== false) {
            $dj = max(0, intval($dj));
            foreach (st_cfg('wugong_config.json') as $wc) {
                if (intval($wc['wgid']) !== intval($row['wugong'])) continue;
                $w = array(intval($wc['add_gj']) * $dj, intval($wc['add_fy']) * $dj, intval($wc['add_hp']) * $dj,
                           intval($wc['add_bj']) * $dj, intval($wc['add_xx']) * $dj);
                break;
            }
        }
    }
    $atk += $w[0]; $def += $w[1]; $hp += $w[2]; $crit += $w[3]; $ls += $w[4];
    $src['vo_cong'] = $w;

    // ---- 3. Thần Trang: gom theo bậc taozhuang, mốc 4/8/12/15(16) món, % = nền bậc + thêm theo mốc ----
    $tzCfg = st_cfg('taozhuang_config.json');
    $cnt = array();
    foreach ($equipped as $q) { if ($q['tz'] > 0) $cnt[$q['tz']] = isset($cnt[$q['tz']]) ? $cnt[$q['tz']] + 1 : 1; }
    $pct = array('hp' => 0, 'gj' => 0, 'fy' => 0, 'bj' => 0, 'xx' => 0);
    foreach ($cnt as $tier => $n) {
        $stage = $n >= 15 ? 16 : ($n >= 12 ? 12 : ($n >= 8 ? 8 : ($n >= 4 ? 4 : 0)));
        if ($stage === 0) continue;
        $bp = isset($tzCfg['tiers'][(string)$tier]['base_percent']) ? $tzCfg['tiers'][(string)$tier]['base_percent']
            : array('hp' => $tier, 'gj' => $tier);
        $ex = $tzCfg['stage_rules'][(string)$stage]['extra_percent'];
        foreach ($pct as $k => $vv) {
            $pct[$k] += (isset($bp[$k]) ? $bp[$k] : 0) + (isset($ex[$k]) ? $ex[$k] : 0);
        }
    }
    $tz = array(intval(round($atk * $pct['gj'] / 100)), intval(round($def * $pct['fy'] / 100)),
                intval(round($hp * $pct['hp'] / 100)), intval(round($crit * $pct['bj'] / 100)),
                intval(round($ls * $pct['xx'] / 100)));
    $atk += $tz[0]; $def += $tz[1]; $hp += $tz[2]; $crit += $tz[3]; $ls += $tz[4];
    $src['than_trang'] = $tz;
    $src['than_trang_pct'] = $pct;

    // ---- 4. Công pháp đang vận: phạt absorb_speed / exp_bonus, rồi % atk_bonus / def_bonus ----
    $cpCfg = st_cfg('congphap_config.json');
    $rules = st_cfg('wugong_skill_config.json');
    $ratio = $rules['rules']['congphap_ratio']; $cap = $rules['rules']['congphap_cap'];
    $st = $db->prepare("SELECT cpid, cpdj FROM playercongphap WHERE sid = ? AND cpequipped = 1");
    $st->execute(array($sid));
    $penA = 0; $penD = 0; $sum = array('atk_bonus' => 0, 'def_bonus' => 0);
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $cp) {
        if (!isset($cpCfg[(string)$cp['cpid']])) continue;
        $c = $cpCfg[(string)$cp['cpid']];
        $lv = max(1, intval($cp['cpdj'])); $ml = max(1, intval($c['max_level']));
        $val = $ml <= 1 ? floatval($c['effect_value_max'])
            : floatval($c['effect_value']) + ($lv - 1) * (floatval($c['effect_value_max']) - floatval($c['effect_value'])) / ($ml - 1);
        if ($c['effect_type'] === 'absorb_speed' || $c['effect_type'] === 'exp_bonus') {
            $penA += $val * floatval(isset($c['penalty_atk_percent']) ? $c['penalty_atk_percent'] : 0);
            $penD += $val * floatval(isset($c['penalty_def_percent']) ? $c['penalty_def_percent'] : 0);
        }
        if (isset($sum[$c['effect_type']])) $sum[$c['effect_type']] += $val;
    }
    $a0 = $atk; $d0 = $def;
    if ($penA > 0) $atk = max(1, intval($atk * (100 - min(80, $penA)) / 100));
    if ($penD > 0) $def = max(1, intval($def * (100 - min(80, $penD)) / 100));
    $pA = round(min(floatval($cap['atk_bonus']), $sum['atk_bonus'] * floatval($ratio['atk_bonus'])), 2);
    $pD = round(min(floatval($cap['def_bonus']), $sum['def_bonus'] * floatval($ratio['def_bonus'])), 2);
    if ($pA > 0) $atk = intval($atk * (100 + $pA) / 100);
    if ($pD > 0) $def = intval($def * (100 + $pD) / 100);
    $src['cong_phap'] = array($atk - $a0, $def - $d0, 0, 0, 0);

    // ---- 5. Cộng Hưởng (hàm thuần conghuong_eval — cấu hình nhóm phức tạp) ----
    $wgLv = array(); $cpLv = array();
    $st = $db->prepare("SELECT wgid, wgdj FROM playerwugong WHERE sid = ?"); $st->execute(array($sid));
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) $wgLv[intval($r['wgid'])] = intval($r['wgdj']);
    $st = $db->prepare("SELECT cpid, cpdj FROM playercongphap WHERE sid = ?"); $st->execute(array($sid));
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) $cpLv[intval($r['cpid'])] = intval($r['cpdj']);
    $ch = \player\conghuong_eval($wgLv, $cpLv);
    $a0 = $atk; $d0 = $def; $h0 = $hp;
    if ($ch['bonus']['atk'] > 0) $atk = intval($atk * (100 + $ch['bonus']['atk']) / 100);
    if ($ch['bonus']['def'] > 0) $def = intval($def * (100 + $ch['bonus']['def']) / 100);
    if ($ch['bonus']['hp'] > 0) $hp = intval($hp * (100 + $ch['bonus']['hp']) / 100);
    $src['cong_huong'] = array($atk - $a0, $def - $d0, $hp - $h0, 0, 0);

    // ---- 6. Tông môn: % theo cấp tông môn (chỉ thành viên đã duyệt uclv > 0) ----
    $st = $db->prepare("SELECT c.clublv FROM clubplayer cp JOIN club c ON c.clubid = cp.clubid WHERE cp.sid = ? AND cp.uclv > 0");
    $st->execute(array($sid));
    $clv = intval($st->fetchColumn());
    $tbl = array(1 => array(1, 0.5, 0.5), 2 => array(2, 1, 1), 3 => array(3, 1.5, 1.5), 4 => array(4, 2, 2), 5 => array(5, 2.5, 2.5),
                 6 => array(6, 3, 3), 7 => array(7, 3.5, 3.5), 8 => array(8, 4, 4), 9 => array(9, 4.5, 4.5), 10 => array(10, 5, 5));
    $a0 = $atk; $d0 = $def; $h0 = $hp;
    if ($clv > 0) {
        $r = $tbl[min(10, $clv)];
        $atk = intval($atk * (100 + $r[1]) / 100);
        $def = intval($def * (100 + $r[2]) / 100);
        $hp  = intval($hp * (100 + $r[0]) / 100);
    }
    $src['tong_mon'] = array($atk - $a0, $def - $d0, $hp - $h0, 0, 0);

    // ---- 7. Linh căn: Σ per_lv × Lv mỗi hành, × (1 + mốc tổng Lv %), Kim × (1 + 0.5% × bậc Kim) ----
    $lc = st_cfg('linhcan_element_config.json');
    $nodes = array();
    foreach (array('kim', 'moc', 'thuy', 'hoa', 'tho') as $k) $nodes[$k] = array('level' => 0, 'stage' => 0);
    $st = $db->prepare("SELECT element, level, stage FROM player_linhcan_node WHERE sid = ?"); $st->execute(array($sid));
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
        if (isset($nodes[$r['element']])) $nodes[$r['element']] = array('level' => intval($r['level']), 'stage' => intval($r['stage']));
    }
    $tot = 0; foreach ($nodes as $n) $tot += $n['level'];
    $msPct = 0;
    foreach ($lc['milestones'] as $m) { if ($tot >= intval($m['total_lv'])) $msPct = floatval($m['bonus_percent']); }
    $mult = 1 + $msPct / 100;
    $p = array('atk' => 0, 'def' => 0, 'hp' => 0, 'crit' => 0);
    foreach ($nodes as $k => $n) {
        foreach ($lc['elements'][$k]['per_lv'] as $stt => $per) { if (isset($p[$stt])) $p[$stt] += floatval($per) * $n['level']; }
    }
    $kimMult = 1 + floatval($lc['elements']['kim']['per_stage_atk_pct']) * $nodes['kim']['stage'] / 100;
    $l = array(intval(round($p['atk'] * $kimMult * $mult)), intval(round($p['def'] * $mult)),
               intval(round($p['hp'] * $mult)), intval(round($p['crit'] * $mult)), 0);
    $atk += $l[0]; $def += $l[1]; $hp += $l[2]; $crit += $l[3];
    $src['linh_can'] = $l;
    // Hành chủ đạo (tiêu chí của linhcan_dominant: Lv cao nhất, hòa thì bậc, hòa nữa theo thứ tự config)
    $dom = '';
    if ($tot > 0) { $dom = \player\linhcan_dominant($nodes); }

    // ---- 8. Nội tại Nạp Hành: N món hợp hệ linh căn -> % trên tổng chỉ số các món đó ----
    $nh = st_cfg('nguhanh_config.json');
    $g = array(0, 0, 0, 0, 0);
    if ($dom !== '') {
        $matched = array(); $elsIn = array();
        foreach ($equipped as $q) {
            if (in_array($dom, $q['els'], true)) {
                $matched[] = $q;
                foreach ($q['els'] as $el) { if (!in_array($el, $elsIn, true)) $elsIn[] = $el; }
            }
        }
        $ti = -1;
        foreach ($nh['tiers'] as $i => $need) { if (count($matched) >= intval($need)) $ti = $i; }
        if ($ti >= 0) {
            $pp = intval($nh['bonus_pct'][$ti]);
            $sums = array('zbgj' => 0, 'zbfy' => 0, 'zbhp' => 0, 'zbbj' => 0, 'zbxx' => 0);
            foreach ($matched as $q) foreach ($sums as $f => $vv) $sums[$f] += $q['v'][$f];
            $map = array('ugj' => array(0, 'zbgj'), 'ufy' => array(1, 'zbfy'), 'umaxhp' => array(2, 'zbhp'),
                         'ubj' => array(3, 'zbbj'), 'uxx' => array(4, 'zbxx'));
            foreach ($elsIn as $el) {
                $field = $nh['elements'][$el];
                $g[$map[$field][0]] += intval($sums[$map[$field][1]] * $pp / 100);
            }
        }
    }
    $atk += $g[0]; $def += $g[1]; $hp += $g[2]; $crit += $g[3]; $ls += $g[4];
    $src['ngu_hanh'] = $g;

    // ---- 9. Thể chất: base × phẩm × (1 + 0.15 × cảnh giới ngữ cảnh) × (1 + 0.2 × giác tỉnh) + Nhục Thân ----
    $bodyCfg = st_cfg('thechat_body_config.json');
    $st = $db->prepare("SELECT node_id, node_lv FROM player_thechat_node WHERE sid = ?"); $st->execute(array($sid));
    $bl = array(); $btot = 0;
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { $bl[intval($r['node_id'])] = max(0, intval($r['node_lv'])); $btot += max(0, intval($r['node_lv'])); }
    $bms = 0;
    foreach ($bodyCfg['milestones'] as $m) { if ($btot >= intval($m['total_lv'])) $bms = floatval($m['bonus_percent']); }
    $body = array('hp' => 0, 'atk' => 0, 'def' => 0, 'crit' => 0, 'lifesteal' => 0);
    foreach ($bodyCfg['nodes'] as $nd) {
        $lv = isset($bl[intval($nd['id'])]) ? $bl[intval($nd['id'])] : 0;
        if ($lv <= 0 || !isset($body[$nd['stat']])) continue;
        $gain = floatval($nd['per_lv']) * $lv * (1 + $bms / 100);
        $body[$nd['stat']] = in_array($nd['stat'], array('crit', 'lifesteal')) ? round($body[$nd['stat']] + $gain, 2) : $body[$nd['stat']] + round($gain);
    }
    $t = array($body['atk'], $body['def'], $body['hp'], $body['crit'], round(floatval($body['lifesteal'])));
    if (intval($row['thechat_id']) > 0) {
        $tc = st_cfg('thechat_config.json');
        $c = isset($tc[(string)$row['thechat_id']]) ? $tc[(string)$row['thechat_id']] : null;
        if ($c) {
            $k = intval($row['thechat_quality']) * (1 + $scaleCtx * 0.15) * (1 + intval($row['thechat_awaken_stage']) * 0.2);
            $bs = $c['base_stats'];
            $t[0] += round(floatval(isset($bs['atk']) ? $bs['atk'] : 0) * $k);
            $t[1] += round(floatval(isset($bs['def']) ? $bs['def'] : 0) * $k);
            $t[2] += round(floatval(isset($bs['hp']) ? $bs['hp'] : 0) * $k);
            $t[3] += round(floatval(isset($bs['crit']) ? $bs['crit'] : 0) * $k);
        }
    }
    $atk += $t[0]; $def += $t[1]; $hp += $t[2]; $crit += $t[3]; $ls += $t[4];
    $src['the_chat'] = $t;

    // ---- 10. Huyết mạch: base × phẩm × (1 + 0.18 × cảnh giới ngữ cảnh) × (1 + 0.25 × giác tỉnh) + Mạch dương ----
    $h = array(0, 0, 0, 0, 0);
    if (intval($row['huyetmach_id']) > 0) {
        $hc = st_cfg('huyetmach_config.json');
        $c = isset($hc[(string)$row['huyetmach_id']]) ? $hc[(string)$row['huyetmach_id']] : null;
        if ($c) {
            $k = intval($row['huyetmach_quality']) * (1 + $scaleCtx * 0.18) * (1 + intval($row['huyetmach_awaken_stage']) * 0.25);
            $bs = $c['base_stats'];
            $h[0] += round(floatval($bs['atk']) * $k); $h[2] += round(floatval($bs['hp']) * $k);
            $h[3] += round(floatval($bs['crit']) * $k); $h[4] += round(floatval($bs['lifesteal']) * $k);
        }
    }
    $vc = st_cfg('huyetmach_vessel_config.json');
    $st = $db->prepare("SELECT vessel_id, level FROM player_huyetmach_vessel WHERE sid = ?"); $st->execute(array($sid));
    $vl = array(); $vtot = 0;
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) { $vl[intval($r['vessel_id'])] = max(0, intval($r['level'])); $vtot += max(0, intval($r['level'])); }
    $vms = 0;
    foreach ($vc['milestones'] as $m) { if ($vtot >= intval($m['total_lv'])) $vms = floatval($m['bonus_percent']); }
    $ves = array('atk' => 0, 'hp' => 0, 'crit' => 0, 'lifesteal' => 0);
    foreach ($vc['vessels'] as $id => $vd) {
        if (!isset($ves[$vd['stat']])) continue; // 4 chỉ số âm (né/xuyên/giảm/vận) không cộng vào 5 chỉ số chính
        $lv = isset($vl[intval($id)]) ? $vl[intval($id)] : 0;
        $ves[$vd['stat']] += round(floatval($vd['per_lv']) * $lv * (1 + $vms / 100));
    }
    $h[0] += intval($ves['atk']); $h[2] += intval($ves['hp']); $h[3] += intval($ves['crit']); $h[4] += intval($ves['lifesteal']);
    $atk += $h[0]; $hp += $h[2]; $crit += $h[3]; $ls += $h[4];
    $src['huyet_mach'] = $h;

    // ---- Thiên phú / Linh sủng: KHÔNG cộng 5 chỉ số chính (thiết kế: chỉ tỉ lệ / chiến đấu riêng) ----
    $src['thien_phu'] = array(0, 0, 0, 0, 0);
    $src['linh_sung'] = array(0, 0, 0, 0, 0);

    // ---- Chiến lực (combat_power): HP + 10·Công + 8·Thủ + 30·Bạo + 25·Hút + tianfu_power
    //      + 200·phẩm TC + 500·giác tỉnh TC + 250·phẩm HM + 600·giác tỉnh HM (phẩm mặc định = 1) ----
    $tcQ = intval($row['thechat_id']) > 0 ? intval($row['thechat_quality']) : 1;
    $hmQ = intval($row['huyetmach_id']) > 0 ? intval($row['huyetmach_quality']) : 1;
    $tcA = intval($row['thechat_awaken_stage']);
    $hmA = intval($row['huyetmach_id']) > 0 ? intval($row['huyetmach_awaken_stage']) : 0;
    // tianfu_power: tổng lực thiên phú (đọc qua hàm tianfu_bonuses — nguồn dữ liệu bảng player_tianfu)
    $tfb = \player\tianfu_bonuses($sid, $db);
    $power = intval($hp) + intval($atk) * 10 + intval($def) * 8 + intval($crit) * 30 + intval($ls) * 25
        + intval($tfb['power']) + $tcQ * 200 + $tcA * 500 + $hmQ * 250 + $hmA * 600;

    $lvv = intval($row['ulv']) + 1;
    return array(
        'ugj' => $atk, 'ufy' => $def, 'umaxhp' => $hp, 'ubj' => $crit, 'uxx' => $ls,
        'power' => $power, 'umaxexp' => max(50, floor($lvv * $lvv * 110)),
        'jingjieValue' => min(intval(floor(intval($row['ulv']) / 50)), count($GLOBALS['rangeslv']) - 2) + 1,
        '_src' => $src,
    );
}

/** getplayer() mới (xóa mọi cache), mô phỏng request mới của game.php: $GLOBALS['player'] = player rỗng. */
function st_actual($db, $sid, $globalCtx) {
    \player\player_cache_clear();
    \player\zhuangbei_cache_clear();
    if (function_exists('player\\thechat_body_levels_flush')) \player\thechat_body_levels_flush();
    $GLOBALS['player'] = $globalCtx;
    $p = \player\getplayer($sid, $db);
    return $p;
}

function st_compare($label, $exp, $p) {
    global $fails, $report;
    echo "\n=== Cấu hình $label ===\n";
    printf("  %-12s %14s %14s %10s\n", 'Chỉ số', 'Kỳ vọng', 'getplayer()', 'Lệch');
    $act = array('ugj' => $p->ugj, 'ufy' => $p->ufy, 'umaxhp' => $p->umaxhp, 'ubj' => $p->ubj, 'uxx' => $p->uxx,
        'power' => \player\combat_power($p), 'umaxexp' => $p->umaxexp, 'jingjieValue' => $p->jingjieValue);
    $names = array('ugj' => 'Công', 'ufy' => 'Thủ', 'umaxhp' => 'Khí huyết', 'ubj' => 'Bạo kích', 'uxx' => 'Hút máu',
        'power' => 'Chiến lực', 'umaxexp' => 'EXP cần', 'jingjieValue' => 'Cảnh giới');
    foreach ($names as $k => $nm) {
        $d = round(floatval($act[$k]) - floatval($exp[$k]), 2);
        $ok = abs($d) < 0.0001;
        if (!$ok) $fails++;
        $report[$label][$k] = array($exp[$k], $act[$k]);
        printf("  %-12s %14s %14s %10s  %s\n", $nm, $exp[$k], $act[$k], $d, $ok ? 'PASS' : 'FAIL');
    }
    echo "  Nguồn cộng (Công,Thủ,HP,Bạo,Hút):\n";
    foreach ($exp['_src'] as $k => $v) {
        if ($k === 'than_trang_pct') { echo "    $k: " . json_encode($v) . "\n"; continue; }
        echo "    " . str_pad($k, 12) . ' ' . implode(', ', $v) . "\n";
    }
}

function st_check($name, $ok, $detail) {
    global $fails;
    if (!$ok) $fails++;
    echo ($ok ? 'PASS  ' : 'FAIL  ') . $name . ($detail !== '' ? " | $detail" : '') . "\n";
}

// ============================ DỰNG NHÂN VẬT ============================
e2e_cleanup($db, $SID);
$UID = e2e_insert_game1($db, $SID, $TOKEN, $UNAME, array());
e2e_level_to($db, $SID, $LEVEL);
$row = e2e_row($db, $SID);
echo "Nhân vật test: uid=$UID sid=$SID cấp={$row['ulv']} (gốc: Công {$row['ugj']}, Thủ {$row['ufy']}, HP {$row['umaxhp']})\n";
$emptyCtx = new \player\player(); // giống game.php: $player = new \player\player() trước getplayer() đầu tiên

// Kiểm tra chỉ số gốc sau lên cấp = công thức đột phá (upplayerlv + 4+lv/1.2 HP, 2+lv/2.5 Công, 3+lv/2 Thủ).
// Bảng upplayerlv có số lẻ (2.5 / 7.5) cộng vào cột INT => mỗi lần cộng MySQL làm tròn (nửa-về-chẵn theo giá trị hiện tại),
// nên chỉ số thật lệch tối đa 0.5 × số lần cộng số lẻ so với thiết kế. Kiểm tra trong dung sai đó + in lệch.
$gj = 12.0; $fy = 5.0; $hpb = 35; $halfSteps = 0;
$rangeslv = $GLOBALS['rangeslv'];
$tblHp = array(30, 50, 70, 90, 110, 130, 170, 210, 250, 300, 350, 400, 450, 500, 550);
$tblGj = array(2.5, 5, 7.5, 10, 12.5, 15, 17.5, 20, 25, 30, 35, 40, 45, 50, 55);
for ($lv = 1; $lv < $LEVEL; $lv++) {
    $next = min($lv + 1, $rangeslv[count($rangeslv) - 1] - 1);
    $i = 0;
    for ($k = 0; $k < count($rangeslv) - 1; $k++) { if ($next >= $rangeslv[$k] && $next < $rangeslv[$k + 1]) { $i = $k; break; } }
    $add = $tblGj[min($i, 14)];
    if ($add != floor($add)) $halfSteps++;
    $hpb += $tblHp[min($i, 14)] + (4 + round($lv / 1.2));
    $gj  += $add + (2 + round($lv / 2.5));
    $fy  += $add + (3 + round($lv / 2));
}
$dGj = intval($row['ugj']) - $gj; $dFy = intval($row['ufy']) - $fy;
st_check("A0 chỉ số gốc cấp $LEVEL = công thức đột phá (dung sai làm tròn ±0.5 × $halfSteps lần cộng số lẻ)",
    abs($dGj) <= 0.5 * $halfSteps && abs($dFy) <= 0.5 * $halfSteps && intval($row['umaxhp']) === intval($hpb),
    "Công {$row['ugj']} (thiết kế $gj, lệch $dGj) Thủ {$row['ufy']} (thiết kế $fy, lệch $dFy) HP {$row['umaxhp']}/" . intval($hpb));

// ============================ A. TRẮNG ============================
st_compare('A_trang', st_expected($db, $SID, 0), st_actual($db, $SID, $emptyCtx));

// ============================ B. FULL TRANG BỊ ============================
$zbCfg = st_cfg('zhuangbei_config.json');
$best = array();
foreach ($zbCfg as $zbid => $c) {
    $t = intval($c['zbtool']);
    if (intval($c['zblv']) > $LEVEL) continue;
    if (!isset($best[$t]) || intval($c['rarity']) > intval($zbCfg[$best[$t]]['rarity'])) $best[$t] = $zbid;
}
$upd = array();
$elemPlan = array(1 => 'kim', 2 => 'kim,hoa', 3 => 'kim', 4 => 'tho', 5 => 'kim,moc,thuy');
foreach ($best as $t => $zbid) {
    $el = isset($elemPlan[$t]) ? $elemPlan[$t] : 'none';
    $nid = e2e_add_zb($db, $SID, $UID, $zbid, 0, $el);
    $upd[] = "tool$t = $nid";
}
// Cường hóa vũ khí +5 bằng đúng hàm upzbsx (lặp tới khi đủ, cấp tiền/đá)
e2e_set_dj($db, $SID, $UID, 1, 100000);
$db->exec("UPDATE game1 SET " . implode(', ', $upd) . " WHERE sid = " . $db->quote($SID));
$weapon = intval(e2e_row($db, $SID)['tool1']);
for ($tries = 0; $tries < 300; $tries++) {
    $q = intval($db->query("SELECT qianghua FROM playerzhuangbei WHERE zbnowid = $weapon")->fetchColumn());
    if ($q >= 5) break;
    \player\upzbsx($weapon, 'zbgj', $SID, $db);
}
$zr = $db->query("SELECT zbid, zbgj, qianghua FROM playerzhuangbei WHERE zbnowid = $weapon")->fetch(PDO::FETCH_ASSOC);
$baseGj = intval($zbCfg[(string)$zr['zbid']]['zbgj']);
st_check('B0 cường hóa +5: mỗi cấp +8% chỉ số gốc config', intval($zr['zbgj']) === $baseGj + 5 * max(1, intval(round($baseGj * 0.08))),
    "zbgj DB={$zr['zbgj']} gốc=$baseGj qh={$zr['qianghua']}");
st_compare('B_trang_bi', st_expected($db, $SID, 0), st_actual($db, $SID, $emptyCtx));

// ============================ C. FULL HỆ THỐNG ============================
// Võ công 20 (Bát Quái Quyền) cấp 6
$db->prepare("INSERT INTO playerwugong (wgid, sid, uid, wgsum, wgdj, wgxl, wgxlmax, wginfo) VALUES (20, ?, ?, 1, 6, 0, 100, '')")->execute(array($SID, $UID));
$db->prepare("UPDATE game1 SET wugong = 20 WHERE sid = ?")->execute(array($SID));
// Công pháp: 29 atk_bonus cấp 10, 30 def_bonus cấp 4, 1 absorb_speed cấp 5 (có phạt công/thủ)
foreach (array(array(29, 10), array(30, 4), array(1, 5)) as $cp) {
    $db->prepare("INSERT INTO playercongphap (cpid, sid, cpdj, cpexp, cpexpmax, cpequipped) VALUES (?, ?, ?, 0, 100, 1)")->execute(array($cp[0], $SID, $cp[1]));
}
// Linh căn: Kim 40 bậc 2, Hỏa 30 bậc 1, Thổ 20, Mộc 10, Thủy 5 (tổng 105 -> mốc 60 = +5%)
foreach (array('kim' => array(40, 2), 'hoa' => array(30, 1), 'tho' => array(20, 1), 'moc' => array(10, 0), 'thuy' => array(5, 0)) as $el => $ls) {
    $db->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage) VALUES (?, ?, ?, ?)")->execute(array($SID, $el, $ls[0], $ls[1]));
}
// Thể chất 2 phẩm 3 giác tỉnh 1 + Nhục Thân 6 bộ phận
$db->prepare("UPDATE game1 SET thechat_id = 2, thechat_quality = 3, thechat_awaken_stage = 1 WHERE sid = ?")->execute(array($SID));
foreach (array(1 => 8, 2 => 7, 3 => 6, 4 => 5, 5 => 4, 6 => 3) as $nid => $lv) {
    $db->prepare("INSERT INTO player_thechat_node (sid, node_id, node_lv) VALUES (?, ?, ?)")->execute(array($SID, $nid, $lv));
}
// Huyết mạch 3 phẩm 2 giác tỉnh 1 + Mạch (đủ 8 mạch, tổng 40 -> mốc 32 = +5%)
$db->prepare("UPDATE game1 SET huyetmach_id = 3, huyetmach_quality = 2, huyetmach_awaken_stage = 1 WHERE sid = ?")->execute(array($SID));
for ($v = 1; $v <= 8; $v++) {
    $db->prepare("INSERT INTO player_huyetmach_vessel (sid, vessel_id, level) VALUES (?, ?, 5)")->execute(array($SID, $v));
}
// Tông môn cấp 5 (thành viên đã duyệt)
$db->prepare("INSERT INTO club (clubname, clubinfo, clublv, clubexp, clubno1, clubyxb, clubczb, clubdiamond) VALUES ('E2EF_Stats', '', 5, 0, ?, 0, 0, 0)")->execute(array($UID));
$cid = intval($db->lastInsertId());
$db->prepare("INSERT INTO clubplayer (clubid, uid, sid, uclv) VALUES (?, ?, ?, 1)")->execute(array($cid, $UID, $SID));
// Thiên phú (không cộng 5 chỉ số chính) — 1 thiên phú
$tfCfg = st_cfg('tianfu_config.json');
$db->prepare("INSERT INTO player_tianfu (sid, tianfu_id, quality, lv, slot, obtained_at) VALUES (?, 1, 2, 3, 1, NOW())")->execute(array($SID));
// Linh sủng xuất chiến (không cộng chỉ số người chơi)
$db->prepare("INSERT INTO playerchongwu (cwname, cwhp, cwmaxhp, cwgj, cwfy, cwbj, cwxx, cwlv, cwexp, tool1, tool2, tool3, tool4, tool5, tool6, sid, uphp, upgj, upfy, cwpz)
    VALUES ('E2E Pet', 100, 100, 50, 20, 0, 0, 1, 0, 0,0,0,0,0,0, ?, 10, 2, 2, 1)")->execute(array($SID));
$db->prepare("UPDATE game1 SET cw = ? WHERE sid = ?")->execute(array(intval($db->lastInsertId()), $SID));

$expC = st_expected($db, $SID, 0);
$pC = st_actual($db, $SID, $emptyCtx);
st_compare('C_full', $expC, $pC);

// ======================== KIỂM TRA HỒI QUY LỖI ========================
echo "\n=== Kiểm tra thêm ===\n";
// STAT-1: chỉ số Thể chất / Huyết mạch phụ thuộc $GLOBALS['player'] (người đang xem), không phải nhân vật được tính.
$ctx = clone $pC; // ngữ cảnh: chính nhân vật đã nạp (giống lần getplayer() thứ 2 trong cùng request)
$pC2 = st_actual($db, $SID, $ctx);
st_check('STAT-1 getplayer() cho cùng 1 nhân vật không phụ thuộc $GLOBALS[player]',
    intval($pC2->ugj) === intval($pC->ugj) && intval($pC2->umaxhp) === intval($pC->umaxhp),
    'request mới: Công ' . $pC->ugj . ' HP ' . $pC->umaxhp . ' | lần gọi 2 cùng request: Công ' . $pC2->ugj . ' HP ' . $pC2->umaxhp);
$expScaled = st_expected($db, $SID, intval($pC->jingjieValue));
st_check('STAT-1b lần gọi 2 = công thức với cảnh giới thật của nhân vật (jingjieValue=' . $pC->jingjieValue . ')',
    intval($pC2->ugj) === intval($expScaled['ugj']) && intval($pC2->umaxhp) === intval($expScaled['umaxhp']),
    'kỳ vọng Công ' . $expScaled['ugj'] . ' HP ' . $expScaled['umaxhp']);
// STAT-2: cache tĩnh của wgcx() làm getplayer() dùng cấp võ công cũ sau khi nâng cấp trong cùng request
\player\wgcx(20, $SID, $db, true); // nạp cache (giống game/wugong.php dòng 10)
$db->prepare("UPDATE playerwugong SET wgdj = wgdj + 1 WHERE sid = ? AND wgid = 20")->execute(array($SID));
\player\player_cache_clear($SID);
$pW = \player\getplayer($SID, $db);
// $GLOBALS['player'] lúc này = $ctx (cảnh giới thật) -> kỳ vọng tính cùng ngữ cảnh đó
$expW = st_expected($db, $SID, intval($ctx->jingjieValue));
st_check('STAT-2 getplayer() thấy cấp võ công mới ngay sau khi nâng (cùng request)', intval($pW->ugj) === intval($expW['ugj']),
    'getplayer Công ' . $pW->ugj . ' / kỳ vọng ' . $expW['ugj'] . ' (lệch = add_gj Bát Quái Quyền × 1 cấp)');
// STAT-3: Chiến lực bảng xếp hạng vs Trạng thái
$rankPow = intval($pC->ugj) * 10 + intval($pC->ufy) * 8 + intval($pC->umaxhp) + intval($pC->ubj) * 30 + intval($pC->uxx) * 25
    + intval($pC->tianfu_power);
st_check('STAT-3 Chiến lực bảng xếp hạng (game/paihang.php) = Chiến lực trang Trạng thái (combat_power)',
    $rankPow === \player\combat_power($pC), 'xếp hạng ' . $rankPow . ' / trạng thái ' . \player\combat_power($pC));
// STAT-4: Bạo kích là số nguyên (cột INT, hiển thị) — Nhục Thân Cốt Tủy 0.2/cấp làm ubj thành số thực
st_check('STAT-4 ubj là số nguyên', floor(floatval($pC->ubj)) == floatval($pC->ubj), 'ubj = ' . var_export($pC->ubj, true));
// STAT-5: Mạch Xung (Bạo kích 0.2/cấp) — 1-2 cấp làm tròn về 0
$db->prepare("UPDATE player_huyetmach_vessel SET level = 2 WHERE sid = ? AND vessel_id = 3")->execute(array($SID));
$vb = \player\huyetmach_vessel_bonuses($SID, $db);
st_check('STAT-5 Xung Mạch cấp 2 (0.2/cấp) cho Bạo kích > 0', floatval($vb['crit']) > 0, 'crit = ' . var_export($vb['crit'], true));

// ============================ DỌN ============================
if (!in_array('--keep', $argv)) {
    $db->prepare("DELETE FROM clubplayer WHERE clubid = ?")->execute(array($cid));
    echo "\nĐã dọn " . e2e_cleanup($db, $SID) . " dòng của nhân vật test.\n";
}
echo $fails === 0 ? "\nALL PASS\n" : "\nFAILED: $fails\n";
exit($fails === 0 ? 0 : 1);
