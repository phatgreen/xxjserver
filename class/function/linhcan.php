<?php
namespace player;

/**
 * linhcan.php - Hệ thống Linh Căn
 *
 * Linh Căn ảnh hưởng:
 * - Absorb linh khí (tốc độ tu luyện)
 * - Exp bonus (% exp nhận được)
 * - Thuộc tính nhân vật (ATK/DEF/HP/Crit)
 * - Thuộc tính linh thú (ATK/HP/Exp/Crit pet)
 * - Tương tác công pháp (element match)
 */

/**
 * Ten bac dot pha cua hanh (0..4) — spec 3.1.
 * Nghia CU ("chat luong" 22 can, 1..5) da bi xoa cung duong random o Task 7/11.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_quality_name')) {
    function linhcan_quality_name($stage){
        $s = linhcan_stage_def(intval($stage));
        return (isset($s['name']) && $s['name'] !== '') ? $s['name'] : 'Phàm Căn';
    }
}

/**
 * Tính tổng bonus từ linh căn.
 * Trả về array với các key:
 * - absorb_bonus: % hấp thụ linh khí
 * - exp_bonus: % exp tu vi
 * - stats: array(atk, def, hp, crit, lifesteal)
 * - pet_atk_bonus, pet_hp_bonus, pet_exp_bonus, pet_crit_bonus
 * - element: element string
 * - supported_cp_element: element tương thích công pháp
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_calc_bonuses')) {
    /** HOP DONG 16 KEY — cam doi ten/kieu (Global Constraints). */
    function linhcan_calc_bonuses($sid, $dblj){
        $nodes = linhcan_node_rows($sid, $dblj);
        $defs = linhcan_element_defs();
        $total = 0;
        foreach ($nodes as $n) { $total += intval($n['level']); }
        $ms = linhcan_milestone($total);
        $mult = 1 + floatval($ms['percent']) / 100;

        $p = array('atk'=>0,'def'=>0,'hp'=>0,'crit'=>0,'absorb_pct'=>0,'exp_pct'=>0,'pet_atk_pct'=>0,'pet_hp_pct'=>0);
        $maxStage = 0;
        foreach ($nodes as $k => $n) {
            $lv = intval($n['level']); $stg = intval($n['stage']);
            if ($stg > $maxStage) $maxStage = $stg;
            if (!isset($defs[$k]) || $lv <= 0) continue;
            $perLv = isset($defs[$k]['per_lv']) ? $defs[$k]['per_lv'] : array();
            foreach ($perLv as $stat => $per) {
                if (isset($p[$stat])) $p[$stat] += floatval($per) * $lv;
            }
        }
        // Kim cuc hard: +0.5% Cong Kich moi bac Kim (nhan vao thanh phan atk cua vong)
        $atkMult = 1;
        if (isset($defs['kim']['per_stage_atk_pct'])) {
            $atkMult = 1 + (floatval($defs['kim']['per_stage_atk_pct']) * intval($nodes['kim']['stage']) / 100);
        }

        $stats = array(
            'atk'       => intval(round($p['atk'] * $atkMult * $mult)),
            'def'       => intval(round($p['def'] * $mult)),
            'hp'        => intval(round($p['hp'] * $mult)),
            'crit'      => intval(round($p['crit'] * $mult)),
            'lifesteal' => 0
        );
        $dom = linhcan_dominant($nodes);
        return array(
            'absorb_bonus' => $p['absorb_pct'] * $mult,
            'exp_bonus'    => $p['exp_pct'] * $mult,
            'quality'      => $maxStage + 1,
            'quality_mult' => 1.0,
            'stats'        => $stats,
            'pet_atk_bonus'=> $p['pet_atk_pct'] * $mult,
            'pet_hp_bonus' => $p['pet_hp_pct'] * $mult,
            'pet_exp_bonus'=> 0,
            'pet_crit_bonus'=> 0,
            'element'      => $dom,
            'supported_cp_element' => $dom,
            'penalty_atk_percent'    => 0,
            'penalty_def_percent'    => 0,
            'penalty_maxhp_percent'  => 0,
            'penalty_crit_percent'   => 0,
            'penalty_absorb_percent' => 0
        );
    }
}

/**
 * Tính % bonus-absorb thực tế từ linh căn (đã bao gồm penalty).
 * Dùng trong xiulian.php.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_get_absorb')) {
    function linhcan_get_absorb($sid, $dblj){
        $bonuses = linhcan_calc_bonuses($sid, $dblj);
        $base = $bonuses['absorb_bonus'];
        $penalty = $bonuses['penalty_absorb_percent'];
        return $base - $penalty;
    }
}

/**
 * Tính % bonus-exp thực tế từ linh căn.
 * Dùng trong xiulian.php.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_get_exp_bonus')) {
    function linhcan_get_exp_bonus($sid, $dblj){
        $bonuses = linhcan_calc_bonuses($sid, $dblj);
        return $bonuses['exp_bonus'];
    }
}

/**
 * Lấy element name từ element code.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_name')) {
    function linhcan_element_name($element){
        $map = array(
            'kim' => 'Kim',
            'moc' => 'Mộc',
            'thuy' => 'Thủy',
            'hoa' => 'Hỏa',
            'tho' => 'Thổ',
            'all' => 'Ngũ Hành',
            'none' => 'Vô Hành'
        );
        return isset($map[$element]) ? $map[$element] : 'Không rõ';
    }
}

/**
 * Lấy element color từ element code.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_color')) {
    function linhcan_element_color($element){
        $map = array(
            'kim' => '#C0C0C0',
            'moc' => '#228B22',
            'thuy' => '#1E90FF',
            'hoa' => '#FF4500',
            'tho' => '#8B4513',
            'all' => '#FFD700',
            'none' => '#8B0000'
        );
        return isset($map[$element]) ? $map[$element] : '#FFFFFF';
    }
}

/**
 * Quy tắc tương khắc ngũ hành:
 * Kim khắc Mộc, Mộc khắc Thổ, Thổ khắc Thủy, Thủy khắc Hỏa, Hỏa khắc Kim
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_khac')) {
    function linhcan_element_khac($attacker, $defender){
        $khac = array(
            'kim' => 'moc',
            'moc' => 'tho',
            'tho' => 'thuy',
            'thuy' => 'hoa',
            'hoa' => 'kim'
        );
        return isset($khac[$attacker]) && $khac[$attacker] === $defender;
    }
}

/**
 * Tính multiplier damage dựa trên element linh căn vs element mục tiêu.
 * - Cùng element: 1.0
 * - Linh căn khắc mục tiêu: 1.2 (+20% damage)
 * - Mục tiêu khắc linh căn: 0.85 (-15% damage)
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_get_element_multiplier')) {
    function linhcan_get_element_multiplier($linhcanElement, $targetElement){
        if (empty($linhcanElement) || empty($targetElement)) {
            return 1.0;
        }
        if ($linhcanElement === $targetElement) {
            return 1.0;
        }
        if (linhcan_element_khac($linhcanElement, $targetElement)) {
            return 1.2;
        }
        if (linhcan_element_khac($targetElement, $linhcanElement)) {
            return 0.85;
        }
        return 1.0;
    }
}

/**
 * Lấy element damage bonus text để hiển thị.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_get_element_bonus_text')) {
    function linhcan_get_element_bonus_text($linhcanElement, $targetElement){
        if (empty($linhcanElement) || empty($targetElement)) {
            return '';
        }
        $mult = linhcan_get_element_multiplier($linhcanElement, $targetElement);
        if ($mult > 1.0) {
            $pct = round(($mult - 1.0) * 100);
            return "+{$pct}% sát thương";
        } elseif ($mult < 1.0) {
            $pct = round((1.0 - $mult) * 100);
            return "-{$pct}% sát thương";
        }
        return '';
    }
}

/**
 * Lấy element icon từ element code.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_icon')) {
    function linhcan_element_icon($element){
        $map = array(
            'kim' => '🪙',
            'moc' => '🌿',
            'thuy' => '💧',
            'hoa' => '🔥',
            'tho' => '🏔',
            'none' => '🌀'
        );
        return isset($map[$element]) ? $map[$element] : '🌀';
    }
}

// ============================================================
// VONG NGU HANH (2026-10-10) — phan doc thuan tuy, doc tu
// class/config/linhcan_element_config.json.
// (Duong cu 22 can + upgrade RNG da xoa het — xem docs/UI_LINHCAN.md Task 11.)
// ============================================================
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_config_path')) {
    function linhcan_element_config_path(){
        return __DIR__ . '/../config/linhcan_element_config.json';
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_config_all')) {
    function linhcan_element_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $json = @file_get_contents(linhcan_element_config_path());
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_defs')) {
    function linhcan_element_defs(){
        $c = linhcan_element_config_all();
        return isset($c['elements']) ? $c['elements'] : array();
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_element_def')) {
    function linhcan_element_def($key){
        $d = linhcan_element_defs();
        return isset($d[$key]) ? $d[$key] : false;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_stage_def')) {
    function linhcan_stage_def($stage){
        $c = linhcan_element_config_all();
        $k = strval(intval($stage));
        return isset($c['stages'][$k]) ? $c['stages'][$k] : false;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_stage_cap')) {
    function linhcan_stage_cap($stage){
        $t = linhcan_stage_def($stage);
        return $t === false ? 0 : intval($t['lv_cap']);
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_stage_max')) {
    function linhcan_stage_max(){ return 4; }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_da_id')) {
    function linhcan_da_id($element){
        $c = linhcan_element_config_all();
        return isset($c['da_map'][$element]) ? intval($c['da_map'][$element]) : 0;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_binh_anh_id')) {
    function linhcan_binh_anh_id(){
        $c = linhcan_element_config_all();
        return isset($c['da_map']['binh_anh']) ? intval($c['da_map']['binh_anh']) : 37;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_material_name')) {
    /** Ten da hien thi — lay tu config (master), khong dung key noi bo 'hoa'. */
    function linhcan_material_name($djid){
        $c = linhcan_element_config_all();
        $djid = intval($djid);
        if (isset($c['material_names'][$djid])) return $c['material_names'][$djid];
        foreach ($c['elements'] as $k => $e) {
            if (intval($c['da_map'][$k]) === $djid) return $e['name'] . ' Tinh Thạch';
        }
        return 'Vật phẩm';
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_train_cost')) {
    function linhcan_train_cost($lv){
        $c = linhcan_element_config_all();
        $t = $c['train_cost'];
        $stone = intval($t['stone_base']) + intval(floor(intval($lv) / intval($t['stone_per_lv'])));
        if ($stone > intval($t['stone_cap'])) $stone = intval($t['stone_cap']);
        return array('stone' => $stone, 'linh_thach' => intval($t['lt_base']) + intval($lv) * intval($t['lt_per_lv']));
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_node_rows')) {
    function linhcan_node_rows($sid, $dblj){
        $rows = array();
        foreach (array('kim','moc','thuy','hoa','tho') as $k) {
            $rows[$k] = array('level' => 0, 'stage' => 0);
        }
        $stmt = $dblj->prepare("SELECT element, level, stage FROM player_linhcan_node WHERE sid = ?");
        $stmt->execute(array($sid));
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $el = $r['element'];
            if (isset($rows[$el])) {
                $rows[$el] = array('level' => intval($r['level']), 'stage' => intval($r['stage']));
            }
        }
        return $rows;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_node_get')) {
    function linhcan_node_get($sid, $element, $dblj){
        $rows = linhcan_node_rows($sid, $dblj);
        return isset($rows[$element]) ? $rows[$element] : array('level'=>0,'stage'=>0);
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_node_set')) {
    function linhcan_node_set($sid, $element, $lv, $stage, $dblj){
        $stmt = $dblj->prepare("INSERT INTO player_linhcan_node (sid, element, level, stage, updated_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE level = VALUES(level), stage = VALUES(stage), updated_at = NOW()");
        $ok = $stmt->execute(array($sid, $element, intval($lv), intval($stage)));
        if ($ok) player_cache_clear($sid);
        return $ok;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_dominant')) {
    /** Hanh hoat dong: Lv cao nhat → stage cao nhat → thu tu co dinh (key mang theo thu tu). */
    function linhcan_dominant($nodes){
        $best = ''; $bestLv = -1; $bestSt = -1;
        foreach (array('kim','moc','thuy','hoa','tho') as $k) {
            if (!isset($nodes[$k])) continue;
            $lv = intval($nodes[$k]['level']); $st = intval($nodes[$k]['stage']);
            if ($lv + $st <= 0) continue;
            if ($lv > $bestLv || ($lv === $bestLv && $st > $bestSt)) {
                $best = $k; $bestLv = $lv; $bestSt = $st;
            }
        }
        return $best; // tong 0 → '' (he chua mo, nhu linh căn chưa có)
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_title')) {
    function linhcan_title($nodes){
        $c = linhcan_element_config_all();
        $keys = array('kim','moc','thuy','hoa','tho');
        $levels = array(); $total = 0;
        foreach ($keys as $k) {
            $lv = isset($nodes[$k]) ? intval($nodes[$k]['level']) : 0;
            $levels[] = $lv; $total += $lv;
        }
        $dom = linhcan_dominant($nodes);
        $domName = $dom !== '' && isset($c['elements'][$dom]) ? $c['elements'][$dom]['name'] : '';
        foreach ($c['title_rules'] as $rule) {
            $rq = isset($rule['require']) ? $rule['require'] : array();
            $ok = true;
            if (isset($rq['all_lv_min']))  { foreach ($levels as $lv) if ($lv < intval($rq['all_lv_min'])) $ok = false; }
            elseif (isset($rq['one_lv_min'])) {
                $hit = 0; $othersOk = true;
                foreach ($levels as $lv) {
                    if ($lv >= intval($rq['one_lv_min'])) $hit++;
                    elseif ($lv > intval($rq['others_lv_max'])) $othersOk = false;
                }
                if ($hit !== 1 || !$othersOk) $ok = false;
            }
            elseif (isset($rq['two_lv_min'])) {
                $hit = 0; foreach ($levels as $lv) if ($lv >= intval($rq['two_lv_min'])) $hit++;
                if ($hit < 2) $ok = false;
            }
            elseif (isset($rq['total_lv_max'])) { if ($total > intval($rq['total_lv_max'])) $ok = false; }
            if ($ok) {
                $name = $rule['name'];
                if ($rule['id'] === 'chan_linh' && $domName !== '') $name .= ' · ' . $domName;
                return array('id'=>$rule['id'],'name'=>$name,'icon'=>$rule['icon'],'color'=>$rule['color'],'desc'=>$domName);
            }
        }
        return array('id'=>'mac_dinh','name'=>'Linh Căn Đã Chắc Lọc','icon'=>'🌿','color'=>'#e2e8f0','desc'=>$domName);
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_milestone')) {
    function linhcan_milestone($total){
        $c = linhcan_element_config_all();
        $ms = isset($c['milestones']) ? $c['milestones'] : array();
        ksort($ms); // JSON object khong dam bao thu tu tang dan
        $out = array('percent'=>0.0,'name'=>'','next_percent'=>0.0,'next_total'=>0,'next_name'=>'');
        foreach ($ms as $m) {
            if ($total >= intval($m['total_lv'])) {
                $out['percent'] = floatval($m['bonus_percent']);
                $out['name'] = $m['name'];
            } else {
                $out['next_percent'] = floatval($m['bonus_percent']);
                $out['next_total'] = intval($m['total_lv']);
                $out['next_name'] = $m['name'];
                break;
            }
        }
        return $out;
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_give_stone')) {
    /** Moi noi TANG da/linh thach vat ly phai đi qua day (landmine 1.2). */
    function linhcan_give_stone($sid, $djid, $amount, $dblj){
        $amount = intval($amount);
        if ($amount <= 0) return false;
        adddj($sid, intval($djid), $amount, $dblj);
        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($sid, 'receive_stone', $amount, $dblj);
        }
        return true;
    }
}

// ============================================================
// Task 3 — Luyen hanh (100% thanh cong, khong may rui) +
// tong chi phi (Task 7 hoan Tay Linh Can dung).
// ============================================================
if (!function_exists(__NAMESPACE__ . '\\linhcan_train_cost_sum')) {
    /** Tong chi phi tu Lv $fromLv den $toLv-1 (Task 7 hoan Tay Linh Can dung). */
    function linhcan_train_cost_sum($fromLv, $toLv){
        $stone = 0; $lt = 0;
        for ($i = intval($fromLv); $i < intval($toLv); $i++) {
            $c = linhcan_train_cost($i);
            $stone += $c['stone']; $lt += $c['linh_thach'];
        }
        return array('stone' => $stone, 'linh_thach' => $lt);
    }
}
if (!function_exists(__NAMESPACE__ . '\\linhcan_train')) {
    function linhcan_train($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return array('ok'=>false,'msg'=>'Hành không tồn tại.','lv'=>0);
        $node = linhcan_node_get($sid, $element, $dblj);
        $cap = linhcan_stage_cap($node['stage']);
        if (intval($node['level']) >= $cap) {
            $t = linhcan_stage_def($node['stage']);
            return array('ok'=>false,'msg'=>'Đã chạm trần bậc ' . $t['name'] . ' (Lv ' . $cap . '). Cần đột phá.','lv'=>intval($node['level']));
        }
        $cost = linhcan_train_cost($node['level']);
        $stoneId = linhcan_da_id($element);
        $have = getplayerdaoju($sid, $stoneId, $dblj);
        $haveSum = $have ? intval($have->djsum) : 0;
        if ($haveSum < $cost['stone']) {
            return array('ok'=>false,'msg'=>'Thiếu ' . $cost['stone'] . ' ' . linhcan_material_name($stoneId) . ' (đang có ' . $haveSum . ').','lv'=>intval($node['level']));
        }
        $player = getplayer($sid, $dblj);
        if (!$player || intval($player->uyxb) < $cost['linh_thach']) {
            return array('ok'=>false,'msg'=>'Thiếu ' . $cost['linh_thach'] . ' linh thạch.','lv'=>intval($node['level']));
        }
        // Thu tu an toan: tru LT truoc (co rowCount kiem tra), roi tru da, roi ghi node.
        $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
        $stmt->execute(array($cost['linh_thach'], $sid, $cost['linh_thach']));
        if ($stmt->rowCount() === 0) {
            return array('ok'=>false,'msg'=>'Trừ linh thạch thất bại.','lv'=>intval($node['level']));
        }
        deledjsum($stoneId, $cost['stone'], $sid, $dblj);
        $newLv = intval($node['level']) + 1;
        linhcan_node_set($sid, $element, $newLv, intval($node['stage']), $dblj);
        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($sid, 'train_element', 1, $dblj); // hook Task 6; chua upgrade thi false an toan
        }
        return array('ok'=>true,'msg'=>'Luyện ' . $def['name'] . ' lên Lv ' . $newLv . '.','lv'=>$newLv);
    }
}

// ==================== DOT PHA BAC HANH ====================
// 100% thanh cong, khong quay RNG (bai hoc `linhcan_upgrade` cu: success_rate 20%
// + truu tien TRUOC khi check dieu kien → fatat giua chung mat oan. Duoi day thu
// check-truoc-sau-tru, moi buoc truu that bai hoan lai cac buoc da truu.)

if (!function_exists(__NAMESPACE__ . '\\linhcan_break_requirements')) {
    function linhcan_break_requirements($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return array('ok' => false, 'to_stage' => 0, 'rows' => array());
        $player = getplayer($sid, $dblj);
        if (!$player) return array('ok' => false, 'to_stage' => 0, 'rows' => array());
        $node = linhcan_node_get($sid, $element, $dblj);
        $stage = intval($node['stage']);
        if ($stage >= linhcan_stage_max()) {
            return array('ok' => false, 'to_stage' => $stage, 'rows' => array(
                array('key' => 'max', 'label' => 'Đã đạt bậc tối đa (Thánh Căn)',
                      'cur' => $stage, 'need' => $stage, 'ok' => true)));
        }
        $toStage = $stage + 1;
        $sd = linhcan_stage_def($toStage);
        $bc = (isset($sd['break']) && is_array($sd['break'])) ? $sd['break'] : array();

        $rows = array();
        $cap = linhcan_stage_cap($stage);
        $lvCur = intval($node['level']);
        $rows[] = array('key' => 'lv_cap', 'label' => 'Lv ' . $def['name'] . ' chạm trần bậc ' . $stage,
                        'cur' => $lvCur, 'need' => $cap, 'ok' => ($lvCur >= $cap));

        $bossNeed = isset($bc['boss_to_stage']) ? intval($bc['boss_to_stage']) : $toStage;
        $bossOk = lcboss_killed($sid, $dblj, $element, $bossNeed);
        $rows[] = array('key' => 'boss', 'label' => 'Thắng boss đột phá ' . $def['name'],
                        'cur' => $bossOk ? 1 : 0, 'need' => 1, 'ok' => $bossOk);

        $baNeed = isset($bc['binh_anh']) ? intval($bc['binh_anh']) : 0;
        $baRow = $baNeed > 0 ? getplayerdaoju($sid, linhcan_binh_anh_id(), $dblj) : false;
        $baCur = $baRow ? intval($baRow->djsum) : 0;
        $rows[] = array('key' => 'binh_anh', 'label' => linhcan_material_name(linhcan_binh_anh_id()),
                        'cur' => $baCur, 'need' => $baNeed, 'ok' => ($baCur >= $baNeed));

        $ltNeed = isset($bc['linh_thach']) ? intval($bc['linh_thach']) : 0;
        $ltCur = intval($player->uyxb);
        $rows[] = array('key' => 'linh_thach', 'label' => 'Linh thạch',
                        'cur' => $ltCur, 'need' => $ltNeed, 'ok' => ($ltCur >= $ltNeed));

        $uxNeed = isset($bc['uexp']) ? intval($bc['uexp']) : 0;
        $uxCur = intval($player->uexp);
        $rows[] = array('key' => 'uexp', 'label' => 'Linh khí (tu vi)',
                        'cur' => $uxCur, 'need' => $uxNeed, 'ok' => ($uxCur >= $uxNeed));

        $ok = true;
        foreach ($rows as $r) { if (!$r['ok']) { $ok = false; } }
        return array('ok' => $ok, 'to_stage' => $toStage, 'rows' => $rows);
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_break')) {
    function linhcan_break($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return array('ok' => false, 'msg' => 'Hành không hợp lệ.', 'stage' => 0);
        $req = linhcan_break_requirements($sid, $dblj, $element);
        if (!$req['ok']) {
            $thieu = array();
            foreach ($req['rows'] as $r) { if (!$r['ok']) { $thieu[] = $r['label']; } }
            return array('ok' => false, 'msg' => 'Chưa đủ điều kiện: ' . implode(', ', $thieu) . '.', 'stage' => 0);
        }
        $node = linhcan_node_get($sid, $element, $dblj);
        $stage = intval($node['stage']);
        $toStage = intval($req['to_stage']);
        $sd = linhcan_stage_def($toStage);
        $bc = isset($sd['break']) ? $sd['break'] : array();
        $ltNeed = intval($bc['linh_thach']);
        $uxNeed = intval($bc['uexp']);
        $baNeed = intval($bc['binh_anh']);
        $baId = linhcan_binh_anh_id();

        $st = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
        $st->execute(array($ltNeed, $sid, $ltNeed));
        if ($st->rowCount() === 0) {
            return array('ok' => false, 'msg' => 'Trừ linh thạch thất bại, chưa đột phá.', 'stage' => 0);
        }
        if ($uxNeed > 0) {
            $st2 = $dblj->prepare("UPDATE game1 SET uexp = uexp - ? WHERE sid = ? AND uexp >= ?");
            $st2->execute(array($uxNeed, $sid, $uxNeed));
            if ($st2->rowCount() === 0) {
                $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($ltNeed, $sid));
                return array('ok' => false, 'msg' => 'Trừ linh khí thất bại, chưa đột phá.', 'stage' => 0);
            }
        }
        if ($baNeed > 0) {
            if (!deledjsum($baId, $baNeed, $sid, $dblj)) {
                $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($ltNeed, $sid));
                if ($uxNeed > 0) {
                    $dblj->prepare("UPDATE game1 SET uexp = uexp + ? WHERE sid = ?")->execute(array($uxNeed, $sid));
                }
                return array('ok' => false, 'msg' => 'Trừ Bình Anh Tinh Thạch thất bại, chưa đột phá.', 'stage' => 0);
            }
        }

        linhcan_node_set($sid, $element, intval($node['level']), $toStage, $dblj);
        $dblj->prepare("INSERT INTO player_linhcan_break_log (sid, element, from_stage, to_stage, created_at)
            VALUES (?, ?, ?, ?, NOW())")->execute(array($sid, $element, $stage, $toStage));
        if (function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
            linhcan_quest_update($sid, 'break_once', 1, $dblj); // hook Task 6
        }
        player_cache_clear($sid);

        $newName = $sd['name'];
        $newColor = $sd['color'];
        return array('ok' => true,
            'msg' => 'Đột phá thành công! ' . $def['name'] . " đạt <font color='{$newColor}'>{$newName}</font> — trần Lv mới " . linhcan_stage_cap($toStage) . '.',
            'stage' => $toStage);
    }
}

// ==================== TAY LINH DAN (dj 111) ====================
// Spec 2.5: chon 1 hanh, reset ve Lv 0 NHUNG GIU BAC (kill boss + break van con),
// hoan 80% da + 100% LT da nan (chi phan LUYEN, khong hoan nguyen lieu dot pha).
// Dung adddj truc tiep, KHONG dung linhcan_give_stone — hoan lai khong duoc tinh
// quest receive_stone (ne bot farm: luyen -> tay -> dem quest).

if (!function_exists(__NAMESPACE__ . '\\linhcan_wash')) {
    function linhcan_wash($sid, $dblj, $element){
        $def = linhcan_element_def($element);
        if (!$def) return array('ok' => false, 'msg' => 'Hành không hợp lệ.', 'stone_back' => 0, 'lt_back' => 0, 'stage' => 0);
        $node = linhcan_node_get($sid, $element, $dblj);
        $lv = intval($node['level']);
        if ($lv <= 0) {
            return array('ok' => false, 'msg' => $def['name'] . ' chưa luyện gì để mà tẩy.', 'stone_back' => 0, 'lt_back' => 0, 'stage' => intval($node['stage']));
        }
        $pill = getplayerdaoju($sid, 111, $dblj);
        if (!$pill || intval($pill->djsum) < 1) {
            return array('ok' => false, 'msg' => 'Ngươi không có Tẩy Linh Đan.', 'stone_back' => 0, 'lt_back' => 0, 'stage' => intval($node['stage']));
        }

        $cost = linhcan_train_cost_sum(0, $lv);
        $stoneBack = intval(floor($cost['stone'] * 0.8));
        $ltBack = intval($cost['linh_thach']);

        if (!deledjsum(111, 1, $sid, $dblj)) {
            return array('ok' => false, 'msg' => 'Tiêu hao đan thất bại, chưa tẩy.', 'stone_back' => 0, 'lt_back' => 0, 'stage' => intval($node['stage']));
        }
        $stoneId = linhcan_da_id($element);
        if ($stoneBack > 0 && $stoneId > 0) adddj($sid, $stoneId, $stoneBack, $dblj);
        if ($ltBack > 0) {
            $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($ltBack, $sid));
        }
        linhcan_node_set($sid, $element, 0, intval($node['stage']), $dblj);
        player_cache_clear($sid);

        return array('ok' => true,
            'msg' => 'Tẩy thành công! ' . $def['name'] . " về Lv 0 (giữ bậc " . intval($node['stage']) . "), hoàn {$stoneBack} đá + {$ltBack} linh thạch.",
            'stone_back' => $stoneBack, 'lt_back' => $ltBack, 'stage' => intval($node['stage']));
    }
}
