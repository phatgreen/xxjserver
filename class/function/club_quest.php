<?php
namespace player;

/**
 * Nhiệm Vụ Tông Môn — Club Quest System
 * PHP 5.6 compatible
 */

/* === CONFIG === */
function club_quest_config_path() {
    $path = __DIR__ . '/../config/club_quest_config.json';
    if (is_file($path)) return $path;
    return false;
}

function club_quest_config_load() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $path = club_quest_config_path();
    if (!$path) { $cache = array(); return $cache; }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    $cache = is_array($data) ? $data : array();
    return $cache;
}

/* === LẤY TẤT CẢ QUEST CONFIGS TỪ DB === */
function club_quest_config_all($dblj) {
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = $dblj->prepare("SELECT id, quest_type, quest_tier, quest_name, quest_desc, target_value, reward_exp, donate_item_type, donate_item_id, donate_item_amount FROM club_quest_config ORDER BY sort_order ASC");
    $stmt->execute();
    $cache = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    return $cache;
}

/* === RANDOM QUEST CHO PLAYER MỖI NGÀY === */
function club_quest_daily_generate($sid, $clubid, $dblj) {
    $today = date('Y-m-d');

    /* Kiểm tra đã có quest hôm nay chưa */
    $check = $dblj->prepare("SELECT COUNT(*) FROM club_player_quest WHERE sid = ? AND quest_date = ?");
    $check->execute(array($sid, $today));
    if (intval($check->fetchColumn()) > 0) return false;

    /* Load config */
    $cfg = club_quest_config_load();
    $allQuests = club_quest_config_all($dblj);
    if (empty($cfg) || empty($allQuests)) return false;

    $minQ = isset($cfg['daily_min']) ? intval($cfg['daily_min']) : 4;
    $maxQ = isset($cfg['daily_max']) ? intval($cfg['daily_max']) : 6;

    /* Group quests by type */
    $byType = array();
    foreach ($allQuests as $q) {
        $t = $q['quest_type'];
        if (!isset($byType[$t])) $byType[$t] = array();
        $byType[$t][] = $q;
    }

    /* Shuffle types, pick N, then pick random tier per type */
    $types = array_keys($byType);
    shuffle($types);
    $numQuests = mt_rand($minQ, min($maxQ, count($types)));
    $selected = array();
    for ($i = 0; $i < $numQuests; $i++) {
        $t = $types[$i];
        $tiers = $byType[$t];
        $pick = $tiers[mt_rand(0, count($tiers) - 1)];
        $selected[] = $pick;
    }

    /* Insert vào DB */
    $stmt = $dblj->prepare("INSERT IGNORE INTO club_player_quest (sid, clubid, quest_config_id, quest_date, current_value, claimed, created_at) VALUES (?, ?, ?, ?, 0, 0, NOW())");
    foreach ($selected as $q) {
        $stmt->execute(array($sid, $clubid, intval($q['id']), $today));
    }

    return $selected;
}

/* === LẤY QUEST CỦA PLAYER HÔM NAY === */
function club_quest_daily_get($sid, $dblj) {
    $today = date('Y-m-d');
    $stmt = $dblj->prepare("
        SELECT pq.id, pq.quest_config_id, pq.current_value, pq.claimed,
               qc.quest_type, qc.quest_tier, qc.quest_name, qc.quest_desc, qc.target_value, qc.reward_exp,
               qc.donate_item_type, qc.donate_item_id, qc.donate_item_amount
        FROM club_player_quest pq
        JOIN club_quest_config qc ON pq.quest_config_id = qc.id
        WHERE pq.sid = ? AND pq.quest_date = ?
        ORDER BY qc.sort_order ASC
    ");
    $stmt->execute(array($sid, $today));
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/* === CẬP NHẬT TIẾN ĐỘ === */
function club_quest_update($sid, $action, $value, $dblj) {
    $today = date('Y-m-d');

    /* Map action → quest_type */
    $typeMap = array(
        'donate_lt'    => 'donate_lt',
        'boss_kill'    => 'boss_kill',
        'boss_attack'  => 'boss_attack',
        'boss_damage'  => 'boss_damage',
        'meditate'     => 'meditate',
        'pve_win'      => 'pve_win',
        'online'       => 'online',
        'alchemy'      => 'alchemy',
        'alchemy_craft'      => 'alchemy_craft',
        'alchemy_high_van'   => 'alchemy_high_van',
        'alchemy_no_toxin'   => 'alchemy_no_toxin',
        'detox'              => 'detox',
        'alchemy_rank_up'    => 'alchemy_rank_up',
        'alchemy_donate'     => 'alchemy_donate',
    );

    if (!isset($typeMap[$action])) return false;
    $questType = $typeMap[$action];

    /* Tìm quest chưa claim của type này hôm nay */
    $stmt = $dblj->prepare("
        SELECT pq.id, pq.current_value, qc.target_value
        FROM club_player_quest pq
        JOIN club_quest_config qc ON pq.quest_config_id = qc.id
        WHERE pq.sid = ? AND pq.quest_date = ? AND qc.quest_type = ? AND pq.claimed = 0
        LIMIT 1
    ");
    $stmt->execute(array($sid, $today, $questType));
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$row) return false;

    $newVal = intval($row['current_value']) + intval($value);
    $target = intval($row['target_value']);
    if ($newVal > $target) $newVal = $target;

    $upd = $dblj->prepare("UPDATE club_player_quest SET current_value = ? WHERE id = ?");
    $upd->execute(array($newVal, intval($row['id'])));

    return array('new_value' => $newVal, 'target' => $target, 'complete' => $newVal >= $target);
}

/* === CLAIM REWARD === */
function club_quest_claim($sid, $pqId, $dblj) {
    $today = date('Y-m-d');

    /* Lấy quest + check chưa claim */
    $stmt = $dblj->prepare("
        SELECT pq.id, pq.clubid, pq.claimed, qc.reward_exp, qc.quest_name,
               qc.quest_type, qc.donate_item_type, qc.donate_item_id, qc.donate_item_amount
        FROM club_player_quest pq
        JOIN club_quest_config qc ON pq.quest_config_id = qc.id
        WHERE pq.id = ? AND pq.sid = ? AND pq.quest_date = ?
    ");
    $stmt->execute(array(intval($pqId), $sid, $today));
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$row) return array('ok' => false, 'msg' => 'Nhiệm vụ không tồn tại');

    if (intval($row['claimed']) == 1) {
        return array('ok' => false, 'msg' => 'Đã nhận thưởng nhiệm vụ này');
    }

    /* Check hoàn thành */
    $prog = $dblj->prepare("SELECT current_value, claimed FROM club_player_quest WHERE id = ?");
    $prog->execute(array(intval($pqId)));
    $pData = $prog->fetch(\PDO::FETCH_ASSOC);
    $qc = $dblj->prepare("SELECT target_value FROM club_quest_config WHERE id = (SELECT quest_config_id FROM club_player_quest WHERE id = ?)");
    $qc->execute(array(intval($pqId)));
    $qData = $qc->fetch(\PDO::FETCH_ASSOC);

    if (!$pData || !$qData) return array('ok' => false, 'msg' => 'Lỗi dữ liệu');
    if (intval($pData['current_value']) < intval($qData['target_value'])) {
        return array('ok' => false, 'msg' => 'Chưa hoàn thành nhiệm vụ');
    }

    /* Xử lý donate_item: chuyển vật phẩm từ túi người chơi vào bảo khố tông môn */
    $donateItemType = $row['donate_item_type'];
    $donateItemId = intval($row['donate_item_id']);
    $donateItemAmount = intval($row['donate_item_amount']);
    $clubId = intval($row['clubid']);

    if ($donateItemType && $donateItemId > 0 && $donateItemAmount > 0 && $clubId > 0) {
        /* Kiểm tra người chơi có đủ vật phẩm không */
        $hasEnough = false;
        if ($donateItemType === 'yaodan') {
            $check = $dblj->prepare("SELECT ydsum FROM playeryaodan WHERE sid = ? AND ydid = ? LIMIT 1");
            $check->execute(array($sid, $donateItemId));
            $r = $check->fetch(\PDO::FETCH_ASSOC);
            $hasEnough = ($r && intval($r['ydsum']) >= $donateItemAmount);
        } elseif ($donateItemType === 'daoju') {
            $check = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
            $check->execute(array($sid, $donateItemId));
            $r = $check->fetch(\PDO::FETCH_ASSOC);
            $hasEnough = ($r && intval($r['djsum']) >= $donateItemAmount);
        } elseif ($donateItemType === 'yaopin') {
            $check = $dblj->prepare("SELECT ypsum FROM playeryaopin WHERE sid = ? AND ypid = ? LIMIT 1");
            $check->execute(array($sid, $donateItemId));
            $r = $check->fetch(\PDO::FETCH_ASSOC);
            $hasEnough = ($r && intval($r['ypsum']) >= $donateItemAmount);
        }

        if (!$hasEnough) {
            /* Lấy tên vật phẩm để báo lỗi */
            $itemName = 'vật phẩm';
            if ($donateItemType === 'yaodan') {
                $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/yaodan_config.json'), true);
                if (isset($cfg[$donateItemId])) $itemName = $cfg[$donateItemId]['ydname'];
            } elseif ($donateItemType === 'daoju') {
                $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/daoju_config.json'), true);
                if (isset($cfg[$donateItemId])) $itemName = $cfg[$donateItemId]['djname'];
            } else {
                $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/yaopin_config.json'), true);
                if (isset($cfg[$donateItemId])) $itemName = $cfg[$donateItemId]['ypname'];
            }
            return array('ok' => false, 'msg' => 'Không đủ ' . $itemName . ' trong túi! Cần ' . $donateItemAmount . ' ' . $itemName . ' để đóng góp.');
        }

        /* Xóa vật phẩm khỏi túi người chơi */
        if ($donateItemType === 'yaodan') {
            $del = $dblj->prepare("UPDATE playeryaodan SET ydsum = ydsum - ? WHERE sid = ? AND ydid = ? AND ydsum >= ?");
            $del->execute(array($donateItemAmount, $sid, $donateItemId, $donateItemAmount));
        } elseif ($donateItemType === 'daoju') {
            $del = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum - ? WHERE sid = ? AND djid = ? AND djsum >= ?");
            $del->execute(array($donateItemAmount, $sid, $donateItemId, $donateItemAmount));
        } elseif ($donateItemType === 'yaopin') {
            $del = $dblj->prepare("UPDATE playeryaopin SET ypsum = ypsum - ? WHERE sid = ? AND ypid = ? AND ypsum >= ?");
            $del->execute(array($donateItemAmount, $sid, $donateItemId, $donateItemAmount));
        }

        /* Thêm vật phẩm vào bảo khố tông môn */
        club_treasury_add($clubId, $donateItemType, $donateItemId, $donateItemAmount, $sid, $dblj);

        /* Lấy tên vật phẩm để hiển thị */
        $itemName = 'vật phẩm';
        if ($donateItemType === 'yaodan') {
            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/yaodan_config.json'), true);
            if (isset($cfg[$donateItemId])) $itemName = $cfg[$donateItemId]['ydname'];
        } elseif ($donateItemType === 'daoju') {
            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/daoju_config.json'), true);
            if (isset($cfg[$donateItemId])) $itemName = $cfg[$donateItemId]['djname'];
        } else {
            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/yaopin_config.json'), true);
            if (isset($cfg[$donateItemId])) $itemName = $cfg[$donateItemId]['ypname'];
        }
    }

    /* Mark claimed */
    $upd = $dblj->prepare("UPDATE club_player_quest SET claimed = 1 WHERE id = ?");
    $upd->execute(array(intval($pqId)));

    /* Cộng EXP tông môn */
    $reward = intval($row['reward_exp']);
    if ($reward > 0 && $clubId > 0) {
        $expUpd = $dblj->prepare("UPDATE club SET clubexp = clubexp + ? WHERE clubid = ?");
        $expUpd->execute(array($reward, $clubId));
    }

    $msg = 'Hoàn thành! +' . $reward . ' EXP Tông Môn';
    if ($donateItemType && $donateItemId > 0 && $donateItemAmount > 0) {
        $msg .= ' | Đã đóng góp ' . $donateItemAmount . 'x ' . $itemName . ' vào bảo khố';
    }

    return array('ok' => true, 'msg' => $msg, 'reward_exp' => $reward, 'quest_name' => $row['quest_name']);
}

/* === CHECK COMPLETION BONUS === */
function club_quest_check_completion($sid, $dblj) {
    $today = date('Y-m-d');
    $cfg = club_quest_config_load();
    $bonusExp = isset($cfg['completion_bonus_exp']) ? intval($cfg['completion_bonus_exp']) : 500;

    /* Đếm số quest hoàn thành (claimed) */
    $stmt = $dblj->prepare("SELECT COUNT(*) FROM club_player_quest WHERE sid = ? AND quest_date = ? AND claimed = 1");
    $stmt->execute(array($sid, $today));
    $claimed = intval($stmt->fetchColumn());

    /* Đếm tổng số quest hôm nay */
    $stmt2 = $dblj->prepare("SELECT COUNT(*) FROM club_player_quest WHERE sid = ? AND quest_date = ?");
    $stmt2->execute(array($sid, $today));
    $total = intval($stmt2->fetchColumn());

    if ($total <= 0) return array('done' => false, 'claimed' => 0, 'total' => 0, 'bonus_exp' => $bonusExp);
    if ($claimed < $total) return array('done' => false, 'claimed' => $claimed, 'total' => $total, 'bonus_exp' => $bonusExp);

    /* Kiểm tra đã nhận bonus chưa (dùng session) */
    $bonusKey = 'club_quest_bonus_' . $sid . '_' . $today;
    if (isset($_SESSION[$bonusKey]) && $_SESSION[$bonusKey] == 1) {
        return array('done' => true, 'claimed_bonus' => true, 'claimed' => $claimed, 'total' => $total, 'bonus_exp' => $bonusExp);
    }

    return array('done' => true, 'claimed_bonus' => false, 'claimed' => $claimed, 'total' => $total, 'bonus_exp' => $bonusExp);
}

/* === CLAIM COMPLETION BONUS === */
function club_quest_claim_completion($sid, $clubid, $dblj) {
    $info = club_quest_check_completion($sid, $dblj);
    if (!$info['done']) return array('ok' => false, 'msg' => 'Chưa hoàn thành tất cả nhiệm vụ');
    if (isset($info['claimed_bonus']) && $info['claimed_bonus']) {
        return array('ok' => false, 'msg' => 'Đã nhận thưởng hoàn thành hôm nay');
    }

    $bonusExp = intval($info['bonus_exp']);
    if ($bonusExp > 0 && $clubid > 0) {
        $upd = $dblj->prepare("UPDATE club SET clubexp = clubexp + ? WHERE clubid = ?");
        $upd->execute(array($bonusExp, $clubid));
    }

    $bonusKey = 'club_quest_bonus_' . $sid . '_' . date('Y-m-d');
    $_SESSION[$bonusKey] = 1;

    return array('ok' => true, 'msg' => 'Hoàn thành tất cả! +' . $bonusExp . ' EXP Tông Môn', 'bonus_exp' => $bonusExp);
}
