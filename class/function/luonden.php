<?php
namespace player;

/**
 * luonden.php - Hệ Thống Luyện Đan Tông Môn
 *
 * Phiên bản mới với:
 * - Phân cấp luyện đan sư
 * - Hệ thống đan văn
 * - Đan Lôi (lôi kiếp)
 * - Đan Tượng (dị tượng)
 * - Đan Linh (tinh linh)
 * - Hệ thống đan độc
 * - Không kháng thuốc
 */

if (!function_exists(__NAMESPACE__ . '\\luonden_config_path')) {
    function luonden_config_path(){
        $paths = array(
            __DIR__ . '/../config/luonden_config.json',
            __DIR__ . '/luonden_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\luonden_config_all')) {
    function luonden_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $path = luonden_config_path();
        $json = @file_get_contents($path);
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\luonden_get_recipe')) {
    function luonden_get_recipe($recipeId){
        $config = luonden_config_all();
        $recipes = isset($config['recipes']) ? $config['recipes'] : array();
        $recipeId = (string)intval($recipeId);
        return (isset($recipes[$recipeId]) && is_array($recipes[$recipeId])) ? $recipes[$recipeId] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\luonden_get_all_recipes')) {
    function luonden_get_all_recipes(){
        $config = luonden_config_all();
        return isset($config['recipes']) ? $config['recipes'] : array();
    }
}

/**
 * Lấy thông tin luyện đan sư
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_alchemist')) {
    function luonden_get_alchemist($sid, $dblj){
        // Độc tính tự giảm 3 điểm/giờ (phản phệ + đan độc), tính bù khi đọc — không cần cron (cũ: luonden_reduce_toxin_hourly
        // không có chỗ gọi ⇒ độc tính không bao giờ giảm, cả đời chỉ ~115 lò — E5). updated_at ghi bằng NOW() của MySQL nên
        // tính giờ bằng NOW() luôn; SET chạy trái → phải nên 2 cột độc tính dùng updated_at cũ, phần lẻ < 1 giờ được giữ lại.
        $settle = $dblj->prepare("UPDATE player_alchemist SET
                total_toxin = GREATEST(0, total_toxin - 3 * FLOOR(TIMESTAMPDIFF(SECOND, updated_at, NOW()) / 3600)),
                pill_toxin = GREATEST(0, pill_toxin - 3 * FLOOR(TIMESTAMPDIFF(SECOND, updated_at, NOW()) / 3600)),
                updated_at = DATE_ADD(updated_at, INTERVAL FLOOR(TIMESTAMPDIFF(SECOND, updated_at, NOW()) / 3600) HOUR)
            WHERE sid = ? AND (total_toxin > 0 OR pill_toxin > 0) AND updated_at <= NOW() - INTERVAL 1 HOUR");
        $settle->execute(array($sid));
        $stmt = $dblj->prepare("SELECT * FROM player_alchemist WHERE sid = ?");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row) {
            // Tạo mới nếu chưa có
            $stmt2 = $dblj->prepare("INSERT INTO player_alchemist (sid, alch_rank, alch_exp, total_craft, total_toxin, created_at, updated_at) VALUES (?, 1, 0, 0, 0, NOW(), NOW())");
            $stmt2->execute(array($sid));
            
            return array(
                'alch_rank' => 1,
                'alch_exp' => 0,
                'total_craft' => 0,
                'total_toxin' => 0,
                'pill_toxin' => 0
            );
        }
        
        return $row;
    }
}

/**
 * Lấy tên cấp bậc luyện đan sư
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_rank_name')) {
    function luonden_get_rank_name($rank){
        $config = luonden_config_all();
        $ranks = isset($config['alchemist_ranks']) ? $config['alchemist_ranks'] : array();
        $rank = (string)intval($rank);
        return (isset($ranks[$rank]['name'])) ? $ranks[$rank]['name'] : 'Học Đồ';
    }
}

/**
 * Tính đan văn khi luyện
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_calc_dan_van')) {
    function luonden_calc_dan_van($alchRank, $recipeDifficulty, $elementBonus = 0, $dinhBonus = 0, $totalToxin = 0){
        $base = $alchRank * 2; // 1-18
        $diffPenalty = $recipeDifficulty * 1.5; // 0-15
        $total = $base - $diffPenalty + $elementBonus + $dinhBonus;
        
        // Phản phệ: giảm đan văn theo mức phản phệ
        $backlashPenalty = 0;
        if ($totalToxin > 300) {
            // Mỗi 200 điểm phản phệ trên 300 = -1 đan văn
            $backlashPenalty = intval(($totalToxin - 300) / 200);
            $backlashPenalty = min(3, $backlashPenalty); // Tối đa -3 đan văn
        }
        $total = $total - $backlashPenalty;
        
        $roll = mt_rand(1, 100);
        
        // Tỷ lệ xuất hiện đan văn cao (dựa trên tổng điểm)
        $chanceDanLinh = max(0.01, min(2, $total * 0.01));
        $chanceDanTuong = max(0.02, min(5, $total * 0.02));
        $chanceDanLoi = max(0.05, min(10, $total * 0.05));
        $chanceDanVan = max(0.1, min(15, $total * 0.1));
        
        $thresholdDanLinh = $chanceDanLinh;
        $thresholdDanTuong = $thresholdDanLinh + $chanceDanTuong;
        $thresholdDanLoi = $thresholdDanTuong + $chanceDanLoi;
        $thresholdDanVan = $thresholdDanLoi + $chanceDanVan;
        
        $rollPercent = ($roll / 100) * 100;
        
        if ($rollPercent <= $thresholdDanLinh) return 13; // Đan Linh
        if ($rollPercent <= $thresholdDanTuong) return 12; // Đan Tượng
        if ($rollPercent <= $thresholdDanLoi) return 11; // Đan Lôi
        if ($rollPercent <= $thresholdDanVan) return 10; // Đan Vân
        
        $maxVan = min(9, max(0, intval($total)));
        return $maxVan;
    }
}

/**
 * Tính cấp Đan Lôi
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_calc_dan_loi')) {
    function luonden_calc_dan_loi($danVan, $recipeGrade){
        // Chỉ trigger khi đan văn >= 10 (Đan Vân trở lên) VÀ phẩm cấp >= 7 (Thất phẩm)
        if ($danVan < 10 || $recipeGrade < 7) return 0;
        
        $roll = mt_rand(1, 100);
        
        if ($recipeGrade >= 11 && $roll <= 5) return 8; // Tử Tiêu Thần Lôi (5%)
        if ($recipeGrade >= 10 && $roll <= 10) return 7; // Cửu Thiên Huyền Lôi (5%)
        if ($recipeGrade >= 9 && $roll <= 20) return 6; // Cửu Sắc (10%)
        if ($recipeGrade >= 8 && $roll <= 40) return 5; // Lục Sắc (20%)
        if ($recipeGrade >= 8 && $roll <= 60) return 4; // Ngũ Sắc (20%)
        if ($recipeGrade >= 7 && $roll <= 80) return 3; // Tam Sắc (20%)
        if ($recipeGrade >= 7 && $roll <= 90) return 2; // Nhị Sắc (10%)
        if ($recipeGrade >= 7) return 1; // Nhất Sắc
        
        return 0;
    }
}

/**
 * Tính Đan Tượng
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_calc_dan_tuong')) {
    function luonden_calc_dan_tuong($danVan, $recipeGrade, $element = ''){
        // Chỉ trigger khi đan văn = 12 (Đan Tượng)
        if ($danVan != 12) return 0;
        
        // Phẩm cấp tối thiểu: Thất phẩm (7)
        if ($recipeGrade < 7) return 0;
        
        $roll = mt_rand(1, 100);
        
        // Tỷ lệ xuất hiện theo phẩm cấp
        if ($recipeGrade >= 11 && $roll <= 30) return 7; // Thiên Ma (30%)
        if ($recipeGrade >= 10 && $roll <= 50) return 6; // Lôi Đế (20%)
        if ($recipeGrade >= 9 && $roll <= 70) return 5;  // Hỏa Phượng (20%)
        if ($recipeGrade >= 8 && $roll <= 80) return 4;  // Băng Liên (10%)
        if ($recipeGrade >= 7 && $roll <= 85) return 3;  // Huyền Vũ (5%)
        if ($recipeGrade >= 6 && $roll <= 90) return 2;  // Phượng Hoàng (5%)
        if ($recipeGrade >= 5) return 1;                  // Long Tượng
        
        return 0;
    }
}

/**
 * Tính đan độc
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_calc_toxin')) {
    function luonden_calc_toxin($toxinBase, $danVan, $recipeGrade, $loiLevel = 0, $eventToxin = 0){
        $config = luonden_config_all();
        $vanThresholds = isset($config['dan_van_thresholds']) ? $config['dan_van_thresholds'] : array();
        
        // Hệ số đan văn
        $toxinMult = 1.0;
        $vanKey = (string)$danVan;
        if (isset($vanThresholds[$vanKey]['toxin_mult'])) {
            $toxinMult = floatval($vanThresholds[$vanKey]['toxin_mult']);
        }
        
        // Đan Lôi giảm đan độc
        $loiReduction = $loiLevel * 5;
        
        // Tính đan độc cơ bản
        $toxin = intval($toxinBase * $toxinMult * (1 + $recipeGrade * 0.1));
        $toxin = max(0, $toxin - $loiReduction + $eventToxin);
        
        return $toxin;
    }
}

/**
 * Tính hiệu ứng đan văn
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_van_effect')) {
    function luonden_get_van_effect($danVan){
        $config = luonden_config_all();
        $thresholds = isset($config['dan_van_thresholds']) ? $config['dan_van_thresholds'] : array();
        
        $vanKey = (string)$danVan;
        if (isset($thresholds[$vanKey])) {
            return $thresholds[$vanKey];
        }
        
        return array('quality' => 'Không văn', 'effect_percent' => 40, 'toxin_mult' => 1.5);
    }
}

/**
 * Tính tên cấp Đan Lôi
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_loi_name')) {
    function luonden_get_loi_name($loiLevel){
        $config = luonden_config_all();
        $levels = isset($config['dan_loi_levels']) ? $config['dan_loi_levels'] : array();
        
        $loiKey = (string)$loiLevel;
        if (isset($levels[$loiKey]['name'])) {
            return $levels[$loiKey]['name'];
        }
        
        return 'Không';
    }
}

/**
 * Tính tên Đan Tượng
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_tuong_name')) {
    function luonden_get_tuong_name($tuongType){
        $config = luonden_config_all();
        $types = isset($config['dan_tuong_types']) ? $config['dan_tuong_types'] : array();
        
        $tuongKey = (string)$tuongType;
        if (isset($types[$tuongKey]['name'])) {
            return $types[$tuongKey]['name'];
        }
        
        return 'Không';
    }
}

/**
 * Kiểm tra đan độc
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_toxin_level')) {
    function luonden_get_toxin_level($toxin){
        $config = luonden_config_all();
        $levels = isset($config['toxin_levels']) ? $config['toxin_levels'] : array();
        
        $levelNames = array(
            0 => 'Bình thường',
            1 => 'Nhẹ',
            2 => 'Trung bình',
            3 => 'Nặng',
            4 => 'Rất nặng',
            5 => 'Phản phệ'
        );
        
        foreach ($levels as $level => $info) {
            if ($toxin >= $info['min'] && $toxin <= $info['max']) {
                $levelName = isset($levelNames[intval($level)]) ? $levelNames[intval($level)] : 'Bình thường';
                return array('level' => intval($level), 'effect' => $info['effect'], 'level_name' => $levelName);
            }
        }
        
        return array('level' => 0, 'effect' => 'Không ảnh hưởng', 'level_name' => 'Bình thường');
    }
}

/**
 * Tính không kháng thuốc
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_calc_drug_resistance')) {
    function luonden_calc_drug_resistance($danVan, $useCount){
        $config = luonden_config_all();
        $resistance = isset($config['drug_resistance']) ? $config['drug_resistance'] : array();
        
        // Đan văn 9+ không bị lờn thuốc
        if ($danVan >= 9 && isset($resistance['van_9_plus']['no_resistance'])) {
            return 100; // Luôn 100% hiệu quả
        }
        
        // Đan văn thấp bị lờn thuốc
        $lowVan = isset($resistance['van_low']) ? $resistance['van_low'] : array();
        $useKey = min(4, max(1, $useCount));
        
        if (isset($lowVan[$useKey])) {
            return $lowVan[$useKey];
        }
        
        return 40; // Mặc định 40%
    }
}

/**
 * Tính tỷ lệ thành công thực tế
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_calc_success_rate')) {
    function luonden_calc_success_rate($player, $recipe, $dinhBonus = 0, $locationBonus = 0, $eventBonus = 0){
        $baseRate = isset($recipe['base_success_rate']) ? intval($recipe['base_success_rate']) : 50;
        $element = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $config = luonden_config_all();
        $elementBonus = isset($config['element_bonus']) ? $config['element_bonus'] : array();

        $bonus = 0;
        if (!empty($element) && isset($elementBonus[$element])) {
            $bonus = isset($elementBonus[$element]['success_bonus']) ? intval($elementBonus[$element]['success_bonus']) : 0;
        }

        // Phản phệ: giảm tỷ lệ thành công theo mức phản phệ
        $backlashPenalty = 0;
        $sid = isset($player->sid) ? $player->sid : 0;
        if ($sid > 0) {
            $alchemist = luonden_get_alchemist($sid, $GLOBALS['dblj']);
            $totalToxin = isset($alchemist['total_toxin']) ? intval($alchemist['total_toxin']) : 0;
            if ($totalToxin > 100) {
                // Mỗi 100 điểm phản phệ = -5% tỷ lệ thành công
                $backlashPenalty = intval(($totalToxin - 100) / 100) * 5;
                $backlashPenalty = min(50, $backlashPenalty); // Tối đa -50%
            }
        }

        $tpAlchemy = isset($player->tianfu_bonus['alchemy']) ? floatval($player->tianfu_bonus['alchemy']) : 0;
        $finalRate = min(95, max(5, $baseRate + $bonus + $dinhBonus + $locationBonus + $eventBonus + $tpAlchemy - $backlashPenalty));
        return $finalRate;
    }
}

/**
 * Kiểm tra player có đủ nguyên liệu không
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_check_materials')) {
    function luonden_check_materials($sid, $recipe, $dblj){
        $materials = isset($recipe['materials']) ? $recipe['materials'] : array();
        if (empty($materials)) return array('ok' => true, 'msg' => '');

        $stmt = $dblj->prepare("SELECT djid, djsum FROM playerdaoju WHERE sid = ?");
        $stmt->execute(array($sid));
        $inventory = array();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $inventory[intval($row['djid'])] = intval($row['djsum']);
        }

        foreach ($materials as $mat) {
            $matId = intval($mat['id']);
            $matAmount = intval($mat['amount']);
            $have = isset($inventory[$matId]) ? $inventory[$matId] : 0;
            if ($have < $matAmount) {
                $config = daoju_config_get($matId);
                $matName = isset($config['djname']) ? $config['djname'] : 'Vật liệu #' . $matId;
                return array('ok' => false, 'msg' => "Không đủ {$matName}: cần {$matAmount}, có {$have}");
            }
        }

        return array('ok' => true, 'msg' => '');
    }
}

/**
 * Trừ nguyên liệu từ inventory
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_consume_materials')) {
    function luonden_consume_materials($sid, $recipe, $partial, $dblj){
        $materials = isset($recipe['materials']) ? $recipe['materials'] : array();
        if (empty($materials)) return true;

        foreach ($materials as $mat) {
            $matId = intval($mat['id']);
            $matAmount = intval($mat['amount']);
            if ($partial) {
                $matAmount = max(1, intval($matAmount / 2));
            }
            $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $stmt->execute(array($matAmount, $sid, $matId, $matAmount));
            if ($stmt->rowCount() === 0) {
                return false;
            }
        }
        return true;
    }
}

/**
 * Thêm kết quả vào inventory
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_add_result')) {
    function luonden_add_result($sid, $recipe, $danVan, $toxin, $loiLevel, $tuongType, $dblj){
        $type = isset($recipe['type']) ? $recipe['type'] : 'yaopin';
        $resultId = isset($recipe['result_id']) ? intval($recipe['result_id']) : 0;
        $resultAmount = isset($recipe['result_amount']) ? intval($recipe['result_amount']) : 1;

        if ($resultId <= 0) return false;

        // Tính dược hiệu dựa trên đan văn
        $vanEffect = luonden_get_van_effect($danVan);
        $effectPercent = isset($vanEffect['effect_percent']) ? intval($vanEffect['effect_percent']) : 40;
        
        // Tính chất lượng (1-7)
        $quality = 1;
        if ($danVan >= 13) $quality = 7; // Đan Linh
        elseif ($danVan >= 12) $quality = 6; // Đan Tượng
        elseif ($danVan >= 11) $quality = 5; // Đan Lôi
        elseif ($danVan >= 10) $quality = 4; // Đan Vân
        elseif ($danVan >= 9) $quality = 4; // Cửu Văn
        elseif ($danVan >= 6) $quality = 3; // Thượng phẩm
        elseif ($danVan >= 3) $quality = 2; // Trung phẩm
        else $quality = 1; // Hạ phẩm

        if ($type === 'yaodan') {
            // Kiểm tra đã có đan dược này chưa (cùng đan văn)
            $stmt = $dblj->prepare("SELECT ydsum FROM playeryaodan WHERE sid = ? AND ydid = ? AND yd_van = ? LIMIT 1");
            $stmt->execute(array($sid, $resultId, $danVan));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                $stmt2 = $dblj->prepare("UPDATE playeryaodan SET ydsum = ydsum + ? WHERE sid = ? AND ydid = ? AND yd_van = ?");
                return $stmt2->execute(array($resultAmount, $sid, $resultId, $danVan));
            } else {
                $stmt2 = $dblj->prepare("INSERT INTO playeryaodan (sid, ydid, ydsum, yd_van, yd_toxin, yd_quality) VALUES (?, ?, ?, ?, ?, ?)");
                return $stmt2->execute(array($sid, $resultId, $resultAmount, $danVan, $toxin, $quality));
            }
        } elseif ($type === 'daoju') {
            $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
            $stmt->execute(array($sid, $resultId));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                $stmt2 = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + ? WHERE sid = ? AND djid = ?");
                return $stmt2->execute(array($resultAmount, $sid, $resultId));
            } else {
                $stmt2 = $dblj->prepare("INSERT INTO playerdaoju (sid, uid, djid, djsum) SELECT sid, uid, ?, ? FROM game1 WHERE sid = ? LIMIT 1"); // kèm uid (khóa chính uid+djid)
                return $stmt2->execute(array($resultId, $resultAmount, $sid));
            }
        } else {
            $stmt = $dblj->prepare("SELECT ypsum FROM playeryaopin WHERE sid = ? AND ypid = ? LIMIT 1");
            $stmt->execute(array($sid, $resultId));
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row) {
                $stmt2 = $dblj->prepare("UPDATE playeryaopin SET ypsum = ypsum + ? WHERE sid = ? AND ypid = ?");
                return $stmt2->execute(array($resultAmount, $sid, $resultId));
            } else {
                $stmt2 = $dblj->prepare("INSERT INTO playeryaopin (sid, ypid, ypsum) VALUES (?, ?, ?)");
                return $stmt2->execute(array($sid, $resultId, $resultAmount));
            }
        }
    }
}

/**
 * Cập nhật kinh nghiệm luyện đan
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_update_alchemist_exp')) {
    function luonden_update_alchemist_exp($sid, $expGain, $recipeGrade, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        
        $newExp = intval($alchemist['alch_exp']) + $expGain;
        $newCraft = intval($alchemist['total_craft']) + 1;
        
        // Tính cấp mới
        $config = luonden_config_all();
        $ranks = isset($config['alchemist_ranks']) ? $config['alchemist_ranks'] : array();
        $newRank = intval($alchemist['alch_rank']);
        
        foreach ($ranks as $rank => $info) {
            if ($newExp >= $info['exp_required']) {
                $newRank = intval($rank);
            }
        }
        
        // Cập nhật DB
        $stmt = $dblj->prepare("UPDATE player_alchemist SET alch_exp = ?, alch_rank = ?, total_craft = ?, updated_at = NOW() WHERE sid = ?");
        return $stmt->execute(array($newExp, $newRank, $newCraft, $sid));
    }
}

/**
 * Cập nhật phản phệ luyện đan (chỉ khi luyện đan)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_update_toxin')) {
    function luonden_update_toxin($sid, $toxinGain, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        $newToxin = intval($alchemist['total_toxin']) + $toxinGain;
        
        $stmt = $dblj->prepare("UPDATE player_alchemist SET total_toxin = ?, updated_at = NOW() WHERE sid = ?");
        return $stmt->execute(array($newToxin, $sid));
    }
}

/**
 * Cập nhật đan độc (chỉ khi sử dụng đan)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_update_pill_toxin')) {
    function luonden_update_pill_toxin($sid, $toxinGain, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        $newToxin = intval($alchemist['pill_toxin']) + $toxinGain;
        
        $stmt = $dblj->prepare("UPDATE player_alchemist SET pill_toxin = ?, updated_at = NOW() WHERE sid = ?");
        return $stmt->execute(array($newToxin, $sid));
    }
}

/**
 * Ghi log luyện đan
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_log_craft')) {
    function luonden_log_craft($sid, $recipeId, $success, $danVan, $toxin, $loiLevel, $expGain, $dblj){
        $stmt = $dblj->prepare("INSERT INTO player_alchemy_log (sid, recipe_id, success, dan_van, dan_toxin, dan_loi_level, alch_exp_gained, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        return $stmt->execute(array($sid, intval($recipeId), $success ? 1 : 0, $danVan, $toxin, $loiLevel, $expGain));
    }
}

/**
 * Hàm chính: Luyện đan (phiên bản mới)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_craft')) {
    function luonden_craft($sid, $recipeId, $dblj){
        $player = getplayer($sid, $dblj);
        if (!$player) {
            return array('ok' => false, 'msg' => 'Không tìm thấy nhân vật.');
        }

        $recipe = luonden_get_recipe($recipeId);
        if (!$recipe) {
            return array('ok' => false, 'msg' => 'Công thức không hợp lệ.');
        }

        // Kiểm tra level
        $levelRequired = isset($recipe['level_required']) ? intval($recipe['level_required']) : 1;
        if (intval($player->ulv) < $levelRequired) {
            return array('ok' => false, 'msg' => "Cần đạt cấp {$levelRequired} để luyện đan này.");
        }

        // Kiểm tra cấp luyện đan sư
        $alchRankRequired = isset($recipe['alch_rank_required']) ? intval($recipe['alch_rank_required']) : 1;
        $alchemist = luonden_get_alchemist($sid, $dblj);
        if (intval($alchemist['alch_rank']) < $alchRankRequired) {
            $rankName = luonden_get_rank_name($alchRankRequired);
            return array('ok' => false, 'msg' => "Cần đạt {$rankName} để luyện đan này.");
        }

        // Kiểm tra phản phệ炼 đan
        $totalToxin = isset($alchemist['total_toxin']) ? intval($alchemist['total_toxin']) : 0;
        if ($totalToxin > 1000) {
            return array('ok' => false, 'msg' => 'Phản phệ quá nặng! Không thể luyện đan. Hãy dùng đan giải độc.');
        }

        // Kiểm tra nguyên liệu
        $checkResult = luonden_check_materials($sid, $recipe, $dblj);
        if (!$checkResult['ok']) {
            return array('ok' => false, 'msg' => $checkResult['msg']);
        }

        // Lấy thông tin lò luyện đan
        $dinhBonus = 0;
        $dinhStmt = $dblj->prepare("SELECT dinh_type, dinh_level FROM player_dan_dinh WHERE sid = ?");
        $dinhStmt->execute(array($sid));
        $dinh = $dinhStmt->fetch(\PDO::FETCH_ASSOC);
        if ($dinh) {
            // Bonus từ loại lò
            $dinhType = intval($dinh['dinh_type']);
            $dinhBonus += ($dinhType - 1) * 5; // 0-40%
            
            // Bonus từ level nâng cấp
            $dinhLevel = intval($dinh['dinh_level']);
            $dinhBonus += $dinhLevel * 2; // 0-10%
        }

        // Tính tỷ lệ thành công
        $successRate = luonden_calc_success_rate($player, $recipe, $dinhBonus, 0, 0);
        $roll = mt_rand(1, 100);
        $success = ($roll <= $successRate);

        // Tính đan văn (dù thành công hay thất bại)
        $elementBonus = 0;
        $element = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $config = luonden_config_all();
        $elementConfig = isset($config['element_bonus'][$element]) ? $config['element_bonus'][$element] : array();
        $elementBonus = isset($elementConfig['van_bonus']) ? intval($elementConfig['van_bonus']) : 0;
        
        $recipeDifficulty = isset($recipe['difficulty']) ? intval($recipe['difficulty']) : 1;
        $totalToxin = isset($alchemist['total_toxin']) ? intval($alchemist['total_toxin']) : 0;
        $danVan = luonden_calc_dan_van(intval($alchemist['alch_rank']), $recipeDifficulty, $elementBonus, $dinhBonus, $totalToxin);

        // Tính Đan Lôi
        $recipeGrade = isset($recipe['grade']) ? intval($recipe['grade']) : 1;
        $loiLevel = luonden_calc_dan_loi($danVan, $recipeGrade);

        // Tính Đan Tượng
        $tuongType = luonden_calc_dan_tuong($danVan, $recipeGrade, $element);

        // Tính đan độc
        $toxinBase = isset($recipe['toxin_base']) ? intval($recipe['toxin_base']) : 0;
        $eventToxin = 0; // Từ sự kiện Khai Lò
        $toxin = luonden_calc_toxin($toxinBase, $danVan, $recipeGrade, $loiLevel, $eventToxin);

        // Tính exp nhận được
        $expGain = 0;
        if ($success) {
            $expGain = 10 + ($recipeGrade * 5) + ($danVan * 2);
        } else {
            $expGain = 3; // Exp an ủi khi thất bại
        }

        // Log
        luonden_log_craft($sid, $recipeId, $success, $danVan, $toxin, $loiLevel, $expGain, $dblj);

        // Cập nhật exp luyện đan sư
        luonden_update_alchemist_exp($sid, $expGain, $recipeGrade, $dblj);

        // Cập nhật đan độc
        if ($toxin > 0) {
            luonden_update_toxin($sid, $toxin, $dblj);
        }

        if ($success) {
            // Trừ nguyên liệu
            if (!luonden_consume_materials($sid, $recipe, false, $dblj)) {
                return array('ok' => false, 'msg' => 'Trừ nguyên liệu thất bại.');
            }

            // Thêm kết quả
            luonden_add_result($sid, $recipe, $danVan, $toxin, $loiLevel, $tuongType, $dblj);

            $itemName = '';
            $resultType = isset($recipe['type']) ? $recipe['type'] : 'yaopin';
            $resultId = isset($recipe['result_id']) ? intval($recipe['result_id']) : 0;
            
            if ($resultType === 'yaodan') {
                $ydConfig = yaodan_config_get($resultId);
                $itemName = isset($ydConfig['ydname']) ? $ydConfig['ydname'] : 'Đan dược';
            } elseif ($resultType === 'daoju') {
                $djConfig = daoju_config_get($resultId);
                $itemName = isset($djConfig['djname']) ? $djConfig['djname'] : 'Vật phẩm';
            } else {
                $ypConfig = yaopin_config_get($resultId);
                $itemName = isset($ypConfig['ypname']) ? $ypConfig['ypname'] : 'Thuốc';
            }

            $resultAmount = isset($recipe['result_amount']) ? intval($recipe['result_amount']) : 1;
            $recipeName = isset($recipe['name']) ? $recipe['name'] : $itemName;

            // Tạo thông báo kết quả
            $msg = "Luyện đan thành công! Nhận được <b>{$resultAmount}</b> {$recipeName}.";
            
            if ($danVan >= 10) {
                $vanEffect = luonden_get_van_effect($danVan);
                $qualityName = isset($vanEffect['quality']) ? $vanEffect['quality'] : '';
                $msg .= "<br>Đan văn: {$danVan} ({$qualityName})";
            }
            
            if ($loiLevel > 0) {
                $loiName = luonden_get_loi_name($loiLevel);
                $msg .= "<br>Đan Lôi: {$loiName}";
            }
            
            if ($tuongType > 0) {
                $tuongName = luonden_get_tuong_name($tuongType);
                $msg .= "<br>Đan Tượng: {$tuongName}";
            }
            
            if ($toxin > 0) {
                $toxinInfo = luonden_get_toxin_level(intval($alchemist['total_toxin']));
                $msg .= "<br>Đan độc: +{$toxin} (Tổng: " . intval($alchemist['total_toxin']) . ")";
            }

            return array(
                'ok' => true,
                'success' => true,
                'msg' => $msg,
                'item_name' => $itemName,
                'amount' => $resultAmount,
                'dan_van' => $danVan,
                'dan_loi' => $loiLevel,
                'dan_tuong' => $tuongType,
                'toxin' => $toxin,
                'exp_gain' => $expGain
            );
        } else {
            // Thất bại vẫn trừ 1/2 nguyên liệu
            luonden_consume_materials($sid, $recipe, true, $dblj);
            
            $msg = "Luyện đan thất bại! Thử lại lần sau.";
            
            return array(
                'ok' => true,
                'success' => false,
                'msg' => $msg,
                'dan_van' => $danVan,
                'toxin' => $toxin,
                'exp_gain' => $expGain
            );
        }
    }
}

/**
 * Lấy text hiển thị tỷ lệ thành công
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_success_text')) {
    function luonden_success_text($player, $recipe){
        $dinhBonus = 0;
        $elementBonus = 0;
        
        $element = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $config = luonden_config_all();
        $elementConfig = isset($config['element_bonus'][$element]) ? $config['element_bonus'][$element] : array();
        $elementBonus = isset($elementConfig['success_bonus']) ? intval($elementConfig['success_bonus']) : 0;
        
        $rate = luonden_calc_success_rate($player, $recipe, $dinhBonus, 0, 0);
        $baseRate = isset($recipe['base_success_rate']) ? intval($recipe['base_success_rate']) : 50;

        $text = $rate . '%';
        if ($elementBonus > 0) {
            $text .= " <span style='color:#4CAF50'>(+{$elementBonus}% linh căn)</span>";
        } elseif ($elementBonus < 0) {
            $text .= " <span style='color:#f44336'>({$elementBonus}% linh căn)</span>";
        }
        return $text;
    }
}

/**
 * Lấy thông tin đan dược (bao gồm đan văn, đan độc)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_pill_info')) {
    function luonden_get_pill_info($sid, $ydid, $dblj){
        $stmt = $dblj->prepare("SELECT * FROM playeryaodan WHERE sid = ? AND ydid = ? LIMIT 1");
        $stmt->execute(array($sid, $ydid));
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}

/**
 * Lấy đan dược theo đan văn
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_pills_by_van')) {
    function luonden_get_pills_by_van($sid, $ydid, $minVan, $dblj){
        $stmt = $dblj->prepare("SELECT * FROM playeryaodan WHERE sid = ? AND ydid = ? AND yd_van >= ? ORDER BY yd_van DESC");
        $stmt->execute(array($sid, $ydid, $minVan));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Lấy bảng xếp hạng luyện đan sư
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_alchemist_ranking')) {
    function luonden_get_alchemist_ranking($limit = 20, $dblj){
        $stmt = $dblj->prepare("SELECT pa.*, g.uname FROM player_alchemist pa JOIN game1 g ON pa.sid = g.sid ORDER BY pa.alch_rank DESC, pa.alch_exp DESC LIMIT ?");
        $stmt->execute(array($limit));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

// ============================================================
// PHASE 2: QUY TRÌNH LUYỆN ĐAN (4 GIAI ĐOẠN)
// ============================================================

/**
 * Giai đoạn 1: Khai Lò - Random sự kiện
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_khai_lo_event')) {
    function luonden_khai_lo_event($alchRank, $dinhType, $recipeGrade){
        $events = array();
        $roll = mt_rand(1, 100);
        
        // Tính tỷ lệ sự kiện xấu giảm theo cấp lò
        $badEventChance = max(5, 25 - ($dinhType * 2));
        $goodEventChance = min(40, 10 + ($alchRank * 2) + ($dinhType * 3));
        
        if ($roll <= $badEventChance) {
            // Sự kiện xấu
            $badRoll = mt_rand(1, 100);
            if ($badRoll <= 40) {
                $events[] = array(
                    'type' => 'bad',
                    'name' => 'Lò Bị Rò Rỉ',
                    'desc' => 'Đan đỉnh bị rò rỉ linh khí, giảm tỷ lệ thành công.',
                    'success_mod' => -15,
                    'toxin_mod' => 5,
                    'van_mod' => 0
                );
            } elseif ($badRoll <= 70) {
                $events[] = array(
                    'type' => 'bad',
                    'name' => 'Địa Hỏa Bất Ổn',
                    'desc' => 'Nguồn lửa dưới đất bị nhiễu loạn, nhiệt độ không ổn định.',
                    'success_mod' => -20,
                    'toxin_mod' => 10,
                    'van_mod' => -1
                );
            } else {
                $events[] = array(
                    'type' => 'bad',
                    'name' => 'Gió Độc',
                    'desc' => 'Gió độc thổi vào lò luyện, tạp chất lẫn vào đan dược.',
                    'success_mod' => -10,
                    'toxin_mod' => 15,
                    'van_mod' => -2
                );
            }
        } elseif ($roll <= $badEventChance + $goodEventChance) {
            // Sự kiện tốt
            $goodRoll = mt_rand(1, 100);
            if ($goodRoll <= 35) {
                $events[] = array(
                    'type' => 'good',
                    'name' => 'Linh Khí Dồi Dào',
                    'desc' => 'Không khí tràn ngập linh khí, đan dược hấp thu được nhiều năng lượng.',
                    'success_mod' => 15,
                    'toxin_mod' => -3,
                    'van_mod' => 1
                );
            } elseif ($goodRoll <= 65) {
                $events[] = array(
                    'type' => 'good',
                    'name' => 'Địa Hỏa Tinh Thuần',
                    'desc' => 'Nguồn lửa dưới đất bất ngờ trở nên tinh thuần, nhiệt độ hoàn hảo.',
                    'success_mod' => 10,
                    'toxin_mod' => -5,
                    'van_mod' => 2
                );
            } else {
                $events[] = array(
                    'type' => 'good',
                    'name' => 'Mạch Nguyên Thạch',
                    'desc' => 'Phát hiện mạch nguyên thạch ẩn giấu, tăng chất lượng đan dược.',
                    'success_mod' => 5,
                    'toxin_mod' => -2,
                    'van_mod' => 3
                );
            }
        } else {
            // Bình thường
            $events[] = array(
                'type' => 'normal',
                'name' => 'Bình Thường',
                'desc' => 'Quá trình khai lò diễn ra bình thường.',
                'success_mod' => 0,
                'toxin_mod' => 0,
                'van_mod' => 0
            );
        }
        
        return $events;
    }
}

/**
 * Giai đoạn 2: Dung Đan - Tính toán quá trình luyện
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_dung_dan')) {
    function luonden_dung_dan($player, $recipe, $alchemist, $dinhBonus, $khaiLoEvents){
        $config = luonden_config_all();
        
        // Tổng hợp bonus từ sự kiện Khai Lò
        $eventSuccessMod = 0;
        $eventToxinMod = 0;
        $eventVanMod = 0;
        
        foreach ($khaiLoEvents as $event) {
            $eventSuccessMod += intval($event['success_mod']);
            $eventToxinMod += intval($event['toxin_mod']);
            $eventVanMod += intval($event['van_mod']);
        }
        
        // Tính tỷ lệ thành công cuối cùng
        $baseRate = isset($recipe['base_success_rate']) ? intval($recipe['base_success_rate']) : 50;
        $element = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $elementConfig = isset($config['element_bonus'][$element]) ? $config['element_bonus'][$element] : array();
        $elementBonus = isset($elementConfig['success_bonus']) ? intval($elementConfig['success_bonus']) : 0;
        
        $finalSuccessRate = min(95, max(5, $baseRate + $elementBonus + $dinhBonus + $eventSuccessMod));
        
        // Rolling thành công
        $roll = mt_rand(1, 100);
        $success = ($roll <= $finalSuccessRate);
        
        // Tính đan văn
        $elementVanBonus = isset($elementConfig['van_bonus']) ? intval($elementConfig['van_bonus']) : 0;
        $recipeDifficulty = isset($recipe['difficulty']) ? intval($recipe['difficulty']) : 1;
        $totalToxin = isset($alchemist['total_toxin']) ? intval($alchemist['total_toxin']) : 0;
        $danVan = luonden_calc_dan_van(intval($alchemist['alch_rank']), $recipeDifficulty, $elementVanBonus + $eventVanMod, $dinhBonus, $totalToxin);
        
        // Giới hạn đan văn theo phẩm cấp công thức
        $recipeGrade = isset($recipe['grade']) ? intval($recipe['grade']) : 1;
        $maxVanByGrade = min(9, $recipeGrade + 2);
        if ($recipeGrade >= 7) $maxVanByGrade = 12; // Thất phẩm trở lên có thể đạt Đan Tượng
        if ($recipeGrade >= 10) $maxVanByGrade = 13; // Tiên phẩm có thể đạt Đan Linh
        
        $danVan = min($danVan, $maxVanByGrade);
        
        // Tính Đan Lôi
        $loiLevel = luonden_calc_dan_loi($danVan, $recipeGrade);
        
        // Tính Đan Tượng
        $element = isset($player->linhcan_element) ? $player->linhcan_element : '';
        $tuongType = luonden_calc_dan_tuong($danVan, $recipeGrade, $element);
        
        // Tính đan độc
        $toxinBase = isset($recipe['toxin_base']) ? intval($recipe['toxin_base']) : 0;
        $toxin = luonden_calc_toxin($toxinBase, $danVan, $recipeGrade, $loiLevel, $eventToxinMod);
        
        return array(
            'success' => $success,
            'final_success_rate' => $finalSuccessRate,
            'dan_van' => $danVan,
            'loi_level' => $loiLevel,
            'tuong_type' => $tuongType,
            'toxin' => $toxin,
            'event_success_mod' => $eventSuccessMod,
            'event_toxin_mod' => $eventToxinMod,
            'event_van_mod' => $eventVanMod
        );
    }
}

/**
 * Giai đoạn 3: Ngưng Đan - Hoàn thành và nhận kết quả
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_ngung_dan')) {
    function luonden_ngung_dan($sid, $recipe, $dungDanResult, $dblj){
        $success = $dungDanResult['success'];
        $danVan = $dungDanResult['dan_van'];
        $loiLevel = $dungDanResult['loi_level'];
        $tuongType = $dungDanResult['tuong_type'];
        $toxin = $dungDanResult['toxin'];
        
        // Tính exp
        $recipeGrade = isset($recipe['grade']) ? intval($recipe['grade']) : 1;
        $expGain = 0;
        if ($success) {
            $expGain = 10 + ($recipeGrade * 5) + ($danVan * 2);
        } else {
            $expGain = 3;
        }
        
        // Log
        luonden_log_craft($sid, intval($recipe['id']), $success, $danVan, $toxin, $loiLevel, $expGain, $dblj);
        
        // Cập nhật exp
        luonden_update_alchemist_exp($sid, $expGain, $recipeGrade, $dblj);
        
        // Cập nhật đan độc
        if ($toxin > 0) {
            luonden_update_toxin($sid, $toxin, $dblj);
        }
        
        if ($success) {
            // Trừ nguyên liệu
            if (!luonden_consume_materials($sid, $recipe, false, $dblj)) {
                return array('ok' => false, 'msg' => 'Trừ nguyên liệu thất bại.');
            }
            
            // Thêm kết quả
            luonden_add_result($sid, $recipe, $danVan, $toxin, $loiLevel, $tuongType, $dblj);
            
            // Lấy tên item
            $itemName = '';
            $resultType = isset($recipe['type']) ? $recipe['type'] : 'yaopin';
            $resultId = isset($recipe['result_id']) ? intval($recipe['result_id']) : 0;
            
            if ($resultType === 'yaodan') {
                $ydConfig = yaodan_config_get($resultId);
                $itemName = isset($ydConfig['ydname']) ? $ydConfig['ydname'] : 'Đan dược';
            } elseif ($resultType === 'daoju') {
                $djConfig = daoju_config_get($resultId);
                $itemName = isset($djConfig['djname']) ? $djConfig['djname'] : 'Vật phẩm';
            } else {
                $ypConfig = yaopin_config_get($resultId);
                $itemName = isset($ypConfig['ypname']) ? $ypConfig['ypname'] : 'Thuốc';
            }
            
            $resultAmount = isset($recipe['result_amount']) ? intval($recipe['result_amount']) : 1;
            $recipeName = isset($recipe['name']) ? $recipe['name'] : $itemName;
            
            // Tạo thông báo
            $msg = "Luyện đan thành công! Nhận được <b>{$resultAmount}</b> {$recipeName}.";
            
            if ($danVan >= 3) {
                $vanEffect = luonden_get_van_effect($danVan);
                $qualityName = isset($vanEffect['quality']) ? $vanEffect['quality'] : '';
                $msg .= "<br>Đan văn: {$danVan} ({$qualityName})";
            }
            
            if ($loiLevel > 0) {
                $loiName = luonden_get_loi_name($loiLevel);
                $msg .= "<br>⚡ Đan Lôi: {$loiName}";
            }
            
            if ($tuongType > 0) {
                $tuongName = luonden_get_tuong_name($tuongType);
                $msg .= "<br>✨ Đan Tượng: {$tuongName}";
            }
            
            if ($toxin > 0) {
                $msg .= "<br>☠️ Đan độc: +{$toxin}";
            }
            
            return array(
                'ok' => true,
                'success' => true,
                'msg' => $msg,
                'item_name' => $itemName,
                'amount' => $resultAmount,
                'dan_van' => $danVan,
                'dan_loi' => $loiLevel,
                'dan_tuong' => $tuongType,
                'toxin' => $toxin,
                'exp_gain' => $expGain
            );
        } else {
            // Thất bại
            luonden_consume_materials($sid, $recipe, true, $dblj);
            
            return array(
                'ok' => true,
                'success' => false,
                'msg' => "Luyện đan thất bại! Nguyên liệu bị hủy một nửa.",
                'dan_van' => $danVan,
                'toxin' => $toxin,
                'exp_gain' => $expGain
            );
        }
    }
}

/**
 * Hàm chính: Luyện đan phiên bản mới (4 giai đoạn)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_craft_v2')) {
    function luonden_craft_v2($sid, $recipeId, $dblj){
        $player = getplayer($sid, $dblj);
        if (!$player) {
            return array('ok' => false, 'msg' => 'Không tìm thấy nhân vật.');
        }

        $recipe = luonden_get_recipe($recipeId);
        if (!$recipe) {
            return array('ok' => false, 'msg' => 'Công thức không hợp lệ.');
        }

        // Kiểm tra level
        $levelRequired = isset($recipe['level_required']) ? intval($recipe['level_required']) : 1;
        if (intval($player->ulv) < $levelRequired) {
            return array('ok' => false, 'msg' => "Cần đạt cấp {$levelRequired} để luyện đan này.");
        }

        // Kiểm tra cấp luyện đan sư
        $alchRankRequired = isset($recipe['alch_rank_required']) ? intval($recipe['alch_rank_required']) : 1;
        $alchemist = luonden_get_alchemist($sid, $dblj);
        if (intval($alchemist['alch_rank']) < $alchRankRequired) {
            $rankName = luonden_get_rank_name($alchRankRequired);
            return array('ok' => false, 'msg' => "Cần đạt {$rankName} để luyện đan này.");
        }

        // Kiểm tra phản phệ炼 đan
        $totalToxin = isset($alchemist['total_toxin']) ? intval($alchemist['total_toxin']) : 0;
        if ($totalToxin > 1000) {
            return array('ok' => false, 'msg' => 'Phản phệ quá nặng! Không thể luyện đan. Hãy dùng đan giải độc.');
        }

        // Kiểm tra nguyên liệu
        $checkResult = luonden_check_materials($sid, $recipe, $dblj);
        if (!$checkResult['ok']) {
            return array('ok' => false, 'msg' => $checkResult['msg']);
        }

        // Lấy thông tin lò luyện đan
        $dinhBonus = 0;
        $dinhType = 1;
        $dinhStmt = $dblj->prepare("SELECT dinh_type, dinh_level FROM player_dan_dinh WHERE sid = ?");
        $dinhStmt->execute(array($sid));
        $dinh = $dinhStmt->fetch(\PDO::FETCH_ASSOC);
        if ($dinh) {
            $dinhType = intval($dinh['dinh_type']);
            $dinhBonus += ($dinhType - 1) * 5;
            $dinhLevel = intval($dinh['dinh_level']);
            $dinhBonus += $dinhLevel * 2;
        }

        // Giai đoạn 1: Khai Lò
        $khaiLoEvents = luonden_khai_lo_event(intval($alchemist['alch_rank']), $dinhType, isset($recipe['grade']) ? intval($recipe['grade']) : 1);

        // Giai đoạn 2: Dung Đan
        $dungDanResult = luonden_dung_dan($player, $recipe, $alchemist, $dinhBonus, $khaiLoEvents);

        // Giai đoạn 3: Ngưng Đan
        $result = luonden_ngung_dan($sid, $recipe, $dungDanResult, $dblj);
        
        // Thêm thông tin sự kiện vào kết quả
        $result['khai_lo_events'] = $khaiLoEvents;
        $result['dung_dan'] = $dungDanResult;

        return $result;
    }
}

// ============================================================
// PHASE 3: HỆ THỐNG ĐAN Độc
// ============================================================

/**
 * Áp dụng hiệu ứng đan độc lên player (dựa trên pill_toxin - dùng đan)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_apply_toxin_effects')) {
    function luonden_apply_toxin_effects($sid, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        $pillToxin = intval($alchemist['pill_toxin']);
        
        if ($pillToxin <= 0) return array();
        
        $effects = array();
        
        // Hiệu ứng đan độc (pill_toxin)
        // 1. Giảm stats: mỗi 10 điểm = -0.1% (tối đa -50%)
        if ($pillToxin > 100) {
            $statPenalty = min(50, intval($pillToxin / 10) * 0.1);
            $effects[] = array('type' => 'stat_penalty', 'value' => $statPenalty, 'desc' => "-{$statPenalty}% tất cả thuộc tính");
        }
        
        // 2. Giảm hiệu quả đan dược: >500 điểm = -1% mỗi 10 điểm (tối đa -50%)
        if ($pillToxin > 500) {
            $pillPenalty = min(50, intval(($pillToxin - 500) / 10));
            $effects[] = array('type' => 'pill_penalty', 'value' => $pillPenalty, 'desc' => "-{$pillPenalty}% hiệu quả đan dược");
        }
        
        // 3. Giảm tỷ lệ đột phá: >1000 điểm = -1% mỗi 100 điểm (tối đa -10%)
        if ($pillToxin > 1000) {
            $breakPenalty = min(10, intval(($pillToxin - 1000) / 100));
            $effects[] = array('type' => 'breakthrough_penalty', 'value' => $breakPenalty, 'desc' => "-{$breakPenalty}% tỷ lệ đột phá");
        }
        
        // 4. Khóa level: >2000 điểm
        if ($pillToxin > 2000) {
            $effects[] = array('type' => 'level_lock', 'value' => 1, 'desc' => 'Không thể lên cấp');
        }
        
        return $effects;
    }
}

/**
 * Giảm phản phệ luyện đan (total_toxin)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_reduce_toxin')) {
    function luonden_reduce_toxin($sid, $amount, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        $currentToxin = intval($alchemist['total_toxin']);
        
        $newToxin = max(0, $currentToxin - $amount);
        
        $stmt = $dblj->prepare("UPDATE player_alchemist SET total_toxin = ?, updated_at = NOW() WHERE sid = ?");
        $stmt->execute(array($newToxin, $sid));
        
        return array(
            'old_toxin' => $currentToxin,
            'new_toxin' => $newToxin,
            'reduced' => $currentToxin - $newToxin
        );
    }
}

/**
 * Giảm đan độc (pill_toxin) - qua vật phẩm giải độc
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_reduce_pill_toxin')) {
    function luonden_reduce_pill_toxin($sid, $amount, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        $currentToxin = intval($alchemist['pill_toxin']);
        
        $newToxin = max(0, $currentToxin - $amount);
        
        $stmt = $dblj->prepare("UPDATE player_alchemist SET pill_toxin = ?, updated_at = NOW() WHERE sid = ?");
        $stmt->execute(array($newToxin, $sid));
        
        return array(
            'old_toxin' => $currentToxin,
            'new_toxin' => $newToxin,
            'reduced' => $currentToxin - $newToxin
        );
    }
}

/**
 * Lấy phần trăm giảm stats từ đan độc (pill_toxin)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_stat_penalty')) {
    function luonden_get_stat_penalty($sid, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        $pillToxin = intval($alchemist['pill_toxin']);
        
        if ($pillToxin <= 100) return 0;
        
        // Mỗi 10 điểm = -0.1% (tối đa -50%)
        return min(50, intval($pillToxin / 10) * 0.1);
    }
}

/**
 * Kiểm tra player có bị khóa level không (dựa trên pill_toxin)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_is_level_locked')) {
    function luonden_is_level_locked($sid, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        $pillToxin = intval($alchemist['pill_toxin']);
        
        return $pillToxin > 2000;
    }
}

/**
 * Sử dụng đan giải độc
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_use_detox_pill')) {
    function luonden_use_detox_pill($sid, $dblj){
        // Kiểm tra có đan giải độc không (item_id = 8)
        $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = 8");
        $stmt->execute(array($sid));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row || intval($row['djsum']) <= 0) {
            return array('ok' => false, 'msg' => 'Không có đan giải độc.');
        }
        
        // Trừ đan giải độc
        $stmt2 = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - 1 WHERE sid = ? AND djid = 8 AND djsum >= 1");
        $stmt2->execute(array($sid));
        
        if ($stmt2->rowCount() === 0) {
            return array('ok' => false, 'msg' => 'Trừ đan giải độc thất bại.');
        }
        
        // Giảm đan độc (giảm 50-100 điểm)
        $reduceAmount = mt_rand(50, 100);
        $result = luonden_reduce_pill_toxin($sid, $reduceAmount, $dblj);
        
        return array(
            'ok' => true,
            'msg' => "Sử dụng đan giải độc thành công! Đan độc giảm {$result['reduced']} điểm.",
            'old_toxin' => $result['old_toxin'],
            'new_toxin' => $result['new_toxin'],
            'reduced' => $result['reduced']
        );
    }
}

/**
 * Giảm đan độc theo thời gian (gọi mỗi giờ)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_reduce_toxin_hourly')) {
    function luonden_reduce_toxin_hourly($dblj){
        // Giảm cả phản phệ và đan độc mỗi giờ
        $stmt = $dblj->prepare("SELECT sid, total_toxin, pill_toxin FROM player_alchemist WHERE total_toxin > 0 OR pill_toxin > 0");
        $stmt->execute();
        
        $reduced = 0;
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $sid = intval($row['sid']);
            $reduceAmount = mt_rand(1, 5);
            
            $updates = array();
            $params = array();
            
            // Giảm phản phệ (total_toxin)
            $currentTotal = intval($row['total_toxin']);
            if ($currentTotal > 0) {
                $newTotal = max(0, $currentTotal - $reduceAmount);
                $updates[] = "total_toxin = ?";
                $params[] = $newTotal;
            }
            
            // Giảm đan độc (pill_toxin)
            $currentPill = intval($row['pill_toxin']);
            if ($currentPill > 0) {
                $newPill = max(0, $currentPill - $reduceAmount);
                $updates[] = "pill_toxin = ?";
                $params[] = $newPill;
            }
            
            if (count($updates) > 0) {
                $params[] = $sid;
                $stmt2 = $dblj->prepare("UPDATE player_alchemist SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE sid = ?");
                $stmt2->execute($params);
                $reduced++;
            }
        }
        
        return $reduced;
    }
}

/**
 * Kiểm tra player có bị effects của đan độc không
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_has_toxin_penalty')) {
    function luonden_has_toxin_penalty($sid, $dblj){
        $alchemist = luonden_get_alchemist($sid, $dblj);
        $pillToxin = intval($alchemist['pill_toxin']);
        
        return $pillToxin > 100; // Bắt đầu có hiệu ứng từ 101
    }
}

/**
 * Lấy thông tin đan dược (bao gồm đan văn, đan độc)
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_pill_info')) {
    function luonden_get_pill_info($sid, $ydid, $dblj){
        $stmt = $dblj->prepare("SELECT * FROM playeryaodan WHERE sid = ? AND ydid = ? LIMIT 1");
        $stmt->execute(array($sid, $ydid));
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}

/**
 * Lấy đan dược theo đan văn
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_pills_by_van')) {
    function luonden_get_pills_by_van($sid, $ydid, $minVan, $dblj){
        $stmt = $dblj->prepare("SELECT * FROM playeryaodan WHERE sid = ? AND ydid = ? AND yd_van >= ? ORDER BY yd_van DESC");
        $stmt->execute(array($sid, $ydid, $minVan));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/**
 * Lấy bảng xếp hạng luyện đan sư
 */
if (!function_exists(__NAMESPACE__ . '\\luonden_get_alchemist_ranking')) {
    function luonden_get_alchemist_ranking($limit = 20, $dblj){
        $stmt = $dblj->prepare("SELECT pa.*, g.uname FROM player_alchemist pa JOIN game1 g ON pa.sid = g.sid ORDER BY pa.alch_rank DESC, pa.alch_exp DESC LIMIT ?");
        $stmt->execute(array($limit));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

?>
