<?php
namespace player;

/**
 * linhcan_quest.php - Quest hàng tuần Linh Căn
 */

if (!function_exists(__NAMESPACE__ . '\\linhcan_quest_config_path')) {
    function linhcan_quest_config_path(){
        $paths = array(
            __DIR__ . '/../config/linhcan_quest_config.json',
            __DIR__ . '/linhcan_quest_config.json'
        );
        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        return $paths[0];
    }
}

if (!function_exists(__NAMESPACE__ . '\\linhcan_quest_config_all')) {
    function linhcan_quest_config_all(){
        static $cache = null;
        if ($cache !== null) return $cache;
        $path = linhcan_quest_config_path();
        $json = @file_get_contents($path);
        $cache = $json ? json_decode($json, true) : array();
        return $cache;
    }
}

/**
 * Lấy tuần hiện tại (YYYYWW format).
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_quest_current_week')) {
    function linhcan_quest_current_week(){
        return date('Y') . str_pad(date('W'), 2, '0', STR_PAD_LEFT);
    }
}

/**
 * Lấy progress quest hiện tại.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_quest_get_progress')) {
    function linhcan_quest_get_progress($sid, $dblj){
        $week = linhcan_quest_current_week();
        $stmt = $dblj->prepare("SELECT * FROM player_linhcan_quest WHERE sid = ? AND quest_week = ? LIMIT 1");
        $stmt->execute(array($sid, $week));
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            // Tạo quest mới cho tuần này
            $progress = array();
            $config = linhcan_quest_config_all();
            $quests = isset($config['weekly_quests']) ? $config['weekly_quests'] : array();
            foreach ($quests as $qid => $q) {
                $progress[$qid] = 0;
            }

            $insertStmt = $dblj->prepare("INSERT INTO player_linhcan_quest (sid, quest_week, progress, completed) VALUES (?, ?, ?, 0)");
            $insertStmt->execute(array($sid, $week, json_encode($progress)));

            return array(
                'progress' => $progress,
                'completed' => false
            );
        }

        $progress = json_decode($row['progress'], true);
        if (!is_array($progress)) $progress = array();

        return array(
            'progress' => $progress,
            'completed' => intval($row['completed']) === 1
        );
    }
}

/**
 * Cập nhật progress quest.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_quest_update')) {
    function linhcan_quest_update($sid, $questType, $amount, $dblj){
        $config = linhcan_quest_config_all();
        $quests = isset($config['weekly_quests']) ? $config['weekly_quests'] : array();
        $current = linhcan_quest_get_progress($sid, $dblj);

        if ($current['completed']) return false; // Đã hoàn thành tuần này

        $updated = false;
        foreach ($quests as $qid => $q) {
            if ($q['type'] === $questType) {
                if (!isset($current['progress'][$qid])) $current['progress'][$qid] = 0;
                $current['progress'][$qid] = intval($current['progress'][$qid]) + intval($amount);
                $updated = true;
            }
        }

        if ($updated) {
            $week = linhcan_quest_current_week();
            $stmt = $dblj->prepare("UPDATE player_linhcan_quest SET progress = ? WHERE sid = ? AND quest_week = ?");
            $stmt->execute(array(json_encode($current['progress']), $sid, $week));
        }

        return $updated;
    }
}

/**
 * Kiểm tra và nhận thưởng quest.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_quest_claim')) {
    function linhcan_quest_claim($sid, $dblj){
        $config = linhcan_quest_config_all();
        $quests = isset($config['weekly_quests']) ? $config['weekly_quests'] : array();
        $current = linhcan_quest_get_progress($sid, $dblj);

        if ($current['completed']) {
            return array('ok' => false, 'msg' => 'Đã nhận thưởng tuần này rồi.');
        }

        // Kiểm tra tất cả quest đã hoàn thành chưa
        $allComplete = true;
        foreach ($quests as $qid => $q) {
            $progress = isset($current['progress'][$qid]) ? intval($current['progress'][$qid]) : 0;
            if ($progress < intval($q['target'])) {
                $allComplete = false;
                break;
            }
        }

        if (!$allComplete) {
            return array('ok' => false, 'msg' => 'Chưa hoàn thành tất cả nhiệm vụ.');
        }

        // Tinh + cap thuong THAT (LT vao game1, Binh Anh vao playerdaoju qua give_stone)
        $totalLinhThach = intval(isset($config['total_reward_linhthach']) ? $config['total_reward_linhthach'] : 500);
        $baMin = intval(isset($config['total_reward_binh_anh_min']) ? $config['total_reward_binh_anh_min'] : 1);
        $baMax = intval(isset($config['total_reward_binh_anh_max']) ? $config['total_reward_binh_anh_max'] : 2);
        $binhAnh = mt_rand($baMin, max($baMin, $baMax));

        $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ? WHERE sid = ?");
        $stmt->execute(array($totalLinhThach, $sid));
        // Luu y: give_stone hook lai receive_stone nen progress tuan nay co the tang nhe.
        // Khong hai — `completed` (khong phai progress) moi la chan claim lap lai.
        linhcan_give_stone($sid, linhcan_binh_anh_id(), $binhAnh, $dblj);

        // Danh dau da nhan
        $week = linhcan_quest_current_week();
        $stmt = $dblj->prepare("UPDATE player_linhcan_quest SET completed = 1 WHERE sid = ? AND quest_week = ?");
        $stmt->execute(array($sid, $week));

        return array(
            'ok' => true,
            'msg' => "Nhận thưởng thành công! +{$totalLinhThach} linh thạch, +{$binhAnh} Bình Anh Tinh Thạch",
            'linhthach' => $totalLinhThach,
            'binh_anh' => $binhAnh
        );
    }
}

/**
 * Lấy text progress quest.
 */
if (!function_exists(__NAMESPACE__ . '\\linhcan_quest_progress_text')) {
    function linhcan_quest_progress_text($sid, $dblj){
        $config = linhcan_quest_config_all();
        $quests = isset($config['weekly_quests']) ? $config['weekly_quests'] : array();
        $current = linhcan_quest_get_progress($sid, $dblj);

        $html = '';
        foreach ($quests as $qid => $q) {
            $progress = isset($current['progress'][$qid]) ? intval($current['progress'][$qid]) : 0;
            $target = intval($q['target']);
            $done = min($progress, $target);
            $pct = $target > 0 ? intval($done * 100 / $target) : 0;
            $color = $done >= $target ? '#4CAF50' : '#FFD700';
            $checkmark = $done >= $target ? '✅' : '⬜';

            $html .= "<div style='padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.1);'>";
            $html .= "{$checkmark} <span style='color:{$color}'>{$q['icon']} {$q['name']}</span>";
            $html .= " <span style='float:right;color:#888'>{$done}/{$target}</span>";
            $html .= "</div>";
        }

        return $html;
    }
}
?>
