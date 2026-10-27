<?php
namespace player;

/**
 * chetao.php - Hệ Thống Chế Tạo
 *
 * Cho phép người chơi chế tạo trang bị/vật phẩm từ nguyên liệu.
 * Tỷ lệ thành công bị ảnh hưởng bởi element linh căn.
 */

if (!function_exists(__NAMESPACE__ . '\\chetao_config_path')) {
    function chetao_config_path(){
        $paths = array(
            __DIR__ . '/../config/chetao_config.json',
            __DIR__ . '/chetao_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\chetao_config_all')) {
    function chetao_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $path = chetao_config_path();
        $json = @file_get_contents($path);
        $cache = $json ? json_decode($json, true) : array('recipes' => array());
        return $cache;
    }
}

if (!function_exists(__NAMESPACE__ . '\\chetao_get_recipe')) {
    function chetao_get_recipe($recipeId){
        $config = chetao_config_all();
        $recipes = isset($config['recipes']) ? $config['recipes'] : array();
        $recipeId = (string)intval($recipeId);
        return (isset($recipes[$recipeId]) && is_array($recipes[$recipeId])) ? $recipes[$recipeId] : false;
    }
}

if (!function_exists(__NAMESPACE__ . '\\chetao_get_all_recipes')) {
    function chetao_get_all_recipes(){
        $config = chetao_config_all();
        return isset($config['recipes']) ? $config['recipes'] : array();
    }
}

/**
 * Tính tỷ lệ thành công thực tế.
 */
if (!function_exists(__NAMESPACE__ . '\\chetao_calc_success_rate')) {
    function chetao_calc_success_rate($player, $recipe){
        $baseRate = isset($recipe['base_success_rate']) ? intval($recipe['base_success_rate']) : 50;
        $element = isset($player->linhcan_element) ? $player->linhcan_element : '';

        $bonus = 0;
        if ($element === 'hoa') $bonus = 10;
        elseif ($element === 'kim') $bonus = 15;
        elseif ($element === 'moc') $bonus = 5;
        elseif ($element === 'tho') $bonus = 8;
        elseif ($element === 'all') $bonus = 10;

        $finalRate = min(95, max(5, $baseRate + $bonus));
        return $finalRate;
    }
}

/**
 * Kiểm tra player có đủ nguyên liệu không.
 */
if (!function_exists(__NAMESPACE__ . '\\chetao_check_materials')) {
    function chetao_check_materials($sid, $recipe, $dblj){
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
 * Trừ nguyên liệu.
 */
if (!function_exists(__NAMESPACE__ . '\\chetao_consume_materials')) {
    function chetao_consume_materials($sid, $recipe, $dblj){
        $materials = isset($recipe['materials']) ? $recipe['materials'] : array();
        if (empty($materials)) return true;

        foreach ($materials as $mat) {
            $matId = intval($mat['id']);
            $matAmount = intval($mat['amount']);
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
 * Hàm chính: Chế tạo.
 */
if (!function_exists(__NAMESPACE__ . '\\chetao_craft')) {
    function chetao_craft($sid, $recipeId, $dblj){
        $player = getplayer($sid, $dblj);
        if (!$player) {
            return array('ok' => false, 'msg' => 'Không tìm thấy nhân vật.');
        }

        $recipe = chetao_get_recipe($recipeId);
        if (!$recipe) {
            return array('ok' => false, 'msg' => 'Công thức không hợp lệ.');
        }

        // Kiểm tra level
        $levelRequired = isset($recipe['level_required']) ? intval($recipe['level_required']) : 1;
        if (intval($player->ulv) < $levelRequired) {
            return array('ok' => false, 'msg' => "Cần đạt cấp {$levelRequired} để chế tạo.");
        }

        // Kiểm tra nguyên liệu
        $checkResult = chetao_check_materials($sid, $recipe, $dblj);
        if (!$checkResult['ok']) {
            return array('ok' => false, 'msg' => $checkResult['msg']);
        }

        // Tính tỷ lệ thành công
        $successRate = chetao_calc_success_rate($player, $recipe);
        $roll = mt_rand(1, 100);
        $success = ($roll <= $successRate);

        // Log
        $logStmt = $dblj->prepare("INSERT INTO player_chetao_log (sid, recipe_id, success, created_at) VALUES (?, ?, ?, NOW())");
        $logStmt->execute(array($sid, intval($recipeId), $success ? 1 : 0));

        if ($success) {
            // Trừ nguyên liệu CHỈ KHI thành công
            if (!chetao_consume_materials($sid, $recipe, $dblj)) {
                return array('ok' => false, 'msg' => 'Trừ nguyên liệu thất bại.');
            }
            $type = isset($recipe['type']) ? $recipe['type'] : 'equipment';
            $recipeName = isset($recipe['name']) ? $recipe['name'] : 'Vật phẩm';

            if ($type === 'equipment') {
                // Chế tạo trang bị
                $resultZbid = isset($recipe['result_zbid']) ? intval($recipe['result_zbid']) : 0;
                if ($resultZbid <= 0) {
                    return array('ok' => false, 'msg' => 'Công thức không hợp lệ.');
                }
                $newZbnowid = addzb($sid, $resultZbid, $dblj);
                if (!$newZbnowid) {
                    return array('ok' => false, 'msg' => 'Chế tạo trang bị thất bại.');
                }
                $zbConfig = getzbkzb($resultZbid, $dblj);
                $zbName = isset($zbConfig->zbname) ? $zbConfig->zbname : 'Trang bị';
                return array(
                    'ok' => true,
                    'success' => true,
                    'rarity' => isset($zbConfig->rarity) ? intval($zbConfig->rarity) : 0,
                    'zbtool' => isset($zbConfig->zbtool) ? intval($zbConfig->zbtool) : 0,
                    "msg" => "Chế tạo thành công! Nhận được <b>{$zbName}</b>."
                );
            } elseif ($type === 'special') {
                // Thêm vật phẩm đặc biệt vào inventory
                $resultDaojuId = isset($recipe['result_daoju_id']) ? intval($recipe['result_daoju_id']) : 0;
                $resultAmount = isset($recipe['result_amount']) ? intval($recipe['result_amount']) : 1;

                if ($resultDaojuId > 0) {
                    $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
                    $stmt->execute(array($sid, $resultDaojuId));
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($row) {
                        $stmt2 = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + ? WHERE sid = ? AND djid = ?");
                        $stmt2->execute(array($resultAmount, $sid, $resultDaojuId));
                    } else {
                        $stmt2 = $dblj->prepare("INSERT INTO playerdaoju (sid, djid, djsum) VALUES (?, ?, ?)");
                        $stmt2->execute(array($sid, $resultDaojuId, $resultAmount));
                    }

                    $djConfig = daoju_config_get($resultDaojuId);
                    $djName = isset($djConfig['djname']) ? $djConfig['djname'] : 'Vật phẩm';

                    return array(
                        'ok' => true,
                        'success' => true,
                        'msg' => "Chế tạo thành công! Nhận được <b>{$resultAmount}</b> {$djName}."
                    );
                }
            }

            return array(
                'ok' => true,
                'success' => true,
                'msg' => "Chế tạo thành công!"
            );
        } else {
            return array(
                'ok' => true,
                'success' => false,
                'msg' => "Chế tạo thất bại! Thử lại lần sau."
            );
        }
    }
}

/**
 * Lấy text hiển thị tỷ lệ thành công.
 */
if (!function_exists(__NAMESPACE__ . '\\chetao_success_text')) {
    function chetao_success_text($player, $recipe){
        $rate = chetao_calc_success_rate($player, $recipe);
        $baseRate = isset($recipe['base_success_rate']) ? intval($recipe['base_success_rate']) : 50;
        $element = isset($player->linhcan_element) ? $player->linhcan_element : '';

        $bonus = 0;
        if ($element === 'hoa') $bonus = 10;
        elseif ($element === 'kim') $bonus = 15;
        elseif ($element === 'moc') $bonus = 5;
        elseif ($element === 'tho') $bonus = 8;
        elseif ($element === 'all') $bonus = 10;

        $text = $rate . '%';
        if ($bonus > 0) {
            $text .= " <span style='color:#4CAF50'>(+{$bonus}% linh căn)</span>";
        }
        return $text;
    }
}

?>
