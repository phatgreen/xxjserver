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

if (!function_exists(__NAMESPACE__ . '\\linhcan_config_path')) {
    function linhcan_config_path(){
        $paths = array(
            __DIR__ . '/../config/linhcan_config.json',
            __DIR__ . '/linhcan_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_config_all')) {
    function linhcan_config_all(){
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        $file = linhcan_config_path();
        if (!is_file($file)) {
            $cache = array();
            return $cache;
        }
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            $cache = array();
            return $cache;
        }
        $cache = $data;
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_config_get')) {
    function linhcan_config_get($id){
        $id = (string)intval($id);
        $all = linhcan_config_all();
        return (isset($all[$id]) && is_array($all[$id])) ? $all[$id] : false;
    }
}

/**
 * Random linh căn theo trọng số (weight).
 * Trả về array config của linh căn được chọn.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_random')) {
    function linhcan_random(){
        $all = linhcan_config_all();
        if (empty($all)) {
            return false;
        }

        $totalWeight = 0;
        foreach ($all as $lc) {
            $totalWeight += floatval($lc['weight']);
        }

        $roll = mt_rand(0, intval($totalWeight * 100)) / 100;
        $cumulative = 0;

        foreach ($all as $lc) {
            $cumulative += floatval($lc['weight']);
            if ($roll <= $cumulative) {
                return $lc;
            }
        }

        // Fallback: trả về phần tử cuối
        $last = end($all);
        return $last;
    }
}

/**
 * Tên bậc phẩm chất linh căn.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_quality_name')) {
    function linhcan_quality_name($quality){
        $map = array(
            1 => 'Tạp',
            2 => 'Chân',
            3 => 'Địa',
            4 => 'Thiên',
            5 => 'Tiên Thiên'
        );
        return isset($map[$quality]) ? $map[$quality] : 'Tạp';
    }
}

/**
 * Random linh căn có cả type + quality.
 * Roll quality từ minQuality đến maxQuality (mặc định 1-5).
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_random_with_quality')) {
    function linhcan_random_with_quality($minQuality = 1, $maxQuality = 5){
        $type = linhcan_random();
        if (!$type) return false;

        $minQuality = max(1, intval($minQuality));
        $maxQuality = min(5, max($minQuality, intval($maxQuality)));

        $qualityChances = array(
            1 => 50.0,
            2 => 25.0,
            3 => 15.0,
            4 => 8.0,
            5 => 2.0
        );

        $filtered = array();
        $totalChance = 0;
        foreach ($qualityChances as $q => $chance) {
            if ($q >= $minQuality && $q <= $maxQuality) {
                $filtered[$q] = $chance;
                $totalChance += $chance;
            }
        }

        if (empty($filtered)) {
            $quality = $minQuality;
        } else {
            $roll = mt_rand(0, intval($totalChance * 100)) / 100;
            $cumulative = 0;
            $quality = $minQuality;
            foreach ($filtered as $q => $chance) {
                $cumulative += $chance;
                if ($roll <= $cumulative) {
                    $quality = $q;
                    break;
                }
            }
        }

        return array(
            'type' => $type,
            'quality' => $quality,
            'quality_mult' => isset($type['quality_mult']) ? floatval($type['quality_mult']) : 1.0
        );
    }
}

/**
 * Gán linh căn cho player.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_assign')) {
    function linhcan_assign($sid, $dblj, $linhcan_id, $quality = 1){
        $linhcan_id = intval($linhcan_id);
        $quality = max(1, min(5, intval($quality)));
        if ($linhcan_id <= 0) return false;

        $config = linhcan_config_get($linhcan_id);
        if (!$config) return false;

        $stmt = $dblj->prepare("UPDATE game1 SET linhcan_id = ?, linhcan_quality = ? WHERE sid = ?");
        $ok = $stmt->execute(array($linhcan_id, $quality, $sid));
        player_cache_clear($sid);

        // Doi he thi hoi mau day du theo max moi.
        // Linh Can la he thong duy nhat co the LAM GIAM max HP
        // (penalty_maxhp_percent, player.php:700). Max thuc = max co ban + buff
        // trang bi/vong cong, va getplayer() cat HP xuong bang max thuc do.
        // Neu khong hoi mau o day, nguoi choi doi sang he co phat se bi "nuot"
        // phan HP vat ly va khong tu hoi lai khi doi ve he khong phat.
        if ($ok) {
            $pl = getplayer($sid, $dblj);
            if ($pl) {
                changeplayersx('uhp', intval($pl->umaxhp), $sid, $dblj);
            }
        }
        return $ok;
    }
}

/**
 * Lấy thông tin linh căn của player.
 * Trả về array config hoặc false.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_info')) {
    function linhcan_info($sid, $dblj){
        $stmt = $dblj->prepare("SELECT linhcan_id, linhcan_quality FROM game1 WHERE sid = ? LIMIT 1");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || empty($row['linhcan_id'])) {
            return false;
        }
        $config = linhcan_config_get(intval($row['linhcan_id']));
        if ($config) {
            $config['_quality'] = isset($row['linhcan_quality']) ? intval($row['linhcan_quality']) : 1;
        }
        return $config;
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
 * Lấy tier name từ tier number.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_tier_name')) {
    function linhcan_tier_name($tier){
        $map = array(
            1 => 'Phàm',
            2 => 'Nhị',
            3 => 'Tam',
            4 => 'Tứ',
            5 => 'Tiên'
        );
        return isset($map[$tier]) ? $map[$tier] : 'Phàm';
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

/* =========================
   LINH CĂN UPGRADE SYSTEM
========================= */

if (!function_exists(__NAMESPACE__ . '\\linhcan_upgrade_config_path')) {
    function linhcan_upgrade_config_path(){
        $paths = array(
            __DIR__ . '/../config/linhcan_upgrade_config.json',
            __DIR__ . '/linhcan_upgrade_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_upgrade_config_all')) {
    function linhcan_upgrade_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $path = linhcan_upgrade_config_path();
        $json = @file_get_contents($path);
        $cache = $json ? json_decode($json, true) : array('upgrades'=>array());
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_get_upgrade_info')) {
    function linhcan_get_upgrade_info($currentTier){
        $config = linhcan_upgrade_config_all();
        $upgrades = isset($config['upgrades']) ? $config['upgrades'] : array();
        foreach ($upgrades as $key => $upgrade) {
            if (intval($upgrade['from_tier']) === intval($currentTier)) {
                return $upgrade;
            }
        }
        return false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_find_next_tier_id')) {
    function linhcan_find_next_tier_id($currentId, $currentTier){
        $all = linhcan_config_all();
        $currentConfig = linhcan_config_get($currentId);
        if (!$currentConfig) return false;

        $element = isset($currentConfig['element']) ? $currentConfig['element'] : '';
        $nextTier = intval($currentTier) + 1;

        foreach ($all as $id => $cfg) {
            if (intval($cfg['tier']) === $nextTier && isset($cfg['element']) && $cfg['element'] === $element) {
                return $id;
            }
        }
        return false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_upgrade')) {
    function linhcan_upgrade($sid, $dblj){
        $player = getplayer($sid, $dblj);
        if (!$player || empty($player->linhcan_id)) {
            return array('ok' => false, 'msg' => 'Ngươi chưa có linh căn.');
        }

        $currentId = intval($player->linhcan_id);
        $currentConfig = linhcan_config_get($currentId);
        if (!$currentConfig) {
            return array('ok' => false, 'msg' => 'Linh căn không hợp lệ.');
        }

        $currentTier = intval($currentConfig['tier']);
        if ($currentTier >= 5) {
            return array('ok' => false, 'msg' => 'Linh căn đã đạt cấp tối đa (Niết).');
        }

        $upgradeInfo = linhcan_get_upgrade_info($currentTier);
        if (!$upgradeInfo) {
            return array('ok' => false, 'msg' => 'Không tìm thấy thông tin nâng cấp.');
        }

        // Kiểm tra linh thạch
        $linhThachCost = intval($upgradeInfo['linh_thach_cost']);
        if (intval($player->uyxb) < $linhThachCost) {
            return array('ok' => false, 'msg' => "Cần {$linhThachCost} linh thạch để nâng cấp. Hiện có {$player->uyxb}.");
        }

        // Kiểm tra linh khí (tu vi)
        $uexpCost = isset($upgradeInfo['uexp_cost']) ? intval($upgradeInfo['uexp_cost']) : 0;
        if ($uexpCost > 0 && intval($player->uexp) < $uexpCost) {
            return array('ok' => false, 'msg' => "Cần {$uexpCost} linh khí (tu vi) để nâng cấp. Hiện có " . intval($player->uexp) . ".");
        }

        // Kiểm tra đá ngũ hành
        $daCost = intval($upgradeInfo['da_ngu_hanh_cost']);
        $element = isset($currentConfig['element']) ? $currentConfig['element'] : '';
        $daConfig = linhcan_upgrade_config_all();
        $daIds = isset($daConfig['da_ngu_hanh_ids']) ? $daConfig['da_ngu_hanh_ids'] : array();
        $daId = isset($daIds[$element]) ? intval($daIds[$element]) : 0;

        if ($daId <= 0) {
            return array('ok' => false, 'msg' => 'Không tìm thấy đá ngũ hành phù hợp.');
        }

        // Trừ linh thạch
        $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb - ? WHERE sid = ? AND uyxb >= ?");
        $stmt->execute(array($linhThachCost, $sid, $linhThachCost));
        if ($stmt->rowCount() === 0) {
            return array('ok' => false, 'msg' => 'Trừ linh thạch thất bại.');
        }

        // Trừ linh khí (tu vi)
        if ($uexpCost > 0) {
            $stmtUexp = $dblj->prepare("UPDATE game1 SET uexp = uexp - ? WHERE sid = ? AND uexp >= ?");
            $stmtUexp->execute(array($uexpCost, $sid, $uexpCost));
            if ($stmtUexp->rowCount() === 0) {
                // Hoàn lại linh thạch nếu trừ uexp thất bại
                $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($linhThachCost, $sid));
                return array('ok' => false, 'msg' => 'Trừ linh khí thất bại.');
            }
        }

        // Trừ đá ngũ hành
        $daOwned = \player\getplayerdaoju($sid, $daId, $dblj);
        $daHave = $daOwned ? intval($daOwned->djsum) : 0;
        if ($daHave < $daCost) {
            // Hoàn lại linh thạch + uexp nếu thiếu đá
            $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($linhThachCost, $sid));
            if ($uexpCost > 0) {
                $dblj->prepare("UPDATE game1 SET uexp = uexp + ? WHERE sid = ?")->execute(array($uexpCost, $sid));
            }
            return array('ok' => false, 'msg' => 'Không đủ đá ngũ hành. Cần '.$daCost.' đá, bạn chỉ có '.$daHave.'.');
        }
        $stmtDa = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE djid = ? AND sid = ? AND djsum >= ?");
        $stmtDa->execute(array($daCost, $daId, $sid, $daCost));
        if ($stmtDa->rowCount() === 0) {
            $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?")->execute(array($linhThachCost, $sid));
            if ($uexpCost > 0) {
                $dblj->prepare("UPDATE game1 SET uexp = uexp + ? WHERE sid = ?")->execute(array($uexpCost, $sid));
            }
            return array('ok' => false, 'msg' => 'Trừ đá ngũ hành thất bại.');
        }

        // Random tỷ lệ thành công
        $successRate = intval($upgradeInfo['success_rate']);
        $roll = mt_rand(1, 100);
        $success = ($roll <= $successRate);

        // Log upgrade
        $toId = $success ? linhcan_find_next_tier_id($currentId, $currentTier) : 0;
        $logStmt = $dblj->prepare("INSERT INTO player_linhcan_log (sid, from_id, to_id, success) VALUES (?, ?, ?, ?)");
        $logStmt->execute(array($sid, $currentId, $success ? $toId : $currentId, $success ? 1 : 0));

        if ($success && $toId) {
            // Nâng cấp thành công
            $updateStmt = $dblj->prepare("UPDATE game1 SET linhcan_id = ? WHERE sid = ?");
            $updateStmt->execute(array($toId, $sid));
            player_cache_clear($sid);

            $newConfig = linhcan_config_get($toId);
            $newName = isset($newConfig['name']) ? $newConfig['name'] : 'Linh căn mới';
            $newColor = isset($newConfig['color']) ? $newConfig['color'] : '#fff';
            return array(
                'ok' => true,
                'success' => true,
                'msg' => "Nâng cấp thành công! Linh căn mới: <font color='{$newColor}'>{$newName}</font>",
                'new_id' => $toId
            );
        } else {
            // Thất bại
            return array(
                'ok' => true,
                'success' => false,
                'msg' => "Nâng cấp thất bại! Linh căn vẫn giữ nguyên. Bạn mất {$linhThachCost} linh thạch."
            );
        }
    }
}


// ============================================================
// VONG NGU HANH (2026-10-10) — phan doc thuan tuy, doc tu
// class/config/linhcan_element_config.json. Ham cu ben tren van
// con song den khi Task 11 xoa.
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
        if (!linhcan_element_def($element)) return array('ok'=>false,'msg'=>'Hành không tồn tại.','lv'=>0);
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
            return array('ok'=>false,'msg'=>'Thiếu ' . $cost['stone'] . ' ' . $element . ' Tinh Thạch (đang có ' . $haveSum . ').','lv'=>intval($node['level']));
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
        return array('ok'=>true,'msg'=>'Luyện ' . $element . ' lên Lv ' . $newLv . '.','lv'=>$newLv);
    }
}
