<?php
namespace player;

/**
 * Bảo Khố Tông Môn (Sect Treasury)
 * Stores crafted items donated by sect members
 * PHP 5.6 compatible
 */

/* === THÊM VẬT PHẨM VÀO BẢO KHỐ === */
if (!function_exists(__NAMESPACE__ . '\\club_treasury_add')) {
    function club_treasury_add($clubid, $itemType, $itemId, $itemAmount, $donatedBy, $dblj) {
        $stmt = $dblj->prepare("INSERT INTO club_treasury (clubid, item_type, item_id, item_amount, donated_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        return $stmt->execute(array(intval($clubid), $itemType, intval($itemId), intval($itemAmount), intval($donatedBy)));
    }
}

/* === LẤY DANH SÁCH VẬT PHẨM CHƯA NHẬN === */
if (!function_exists(__NAMESPACE__ . '\\club_treasury_list')) {
    function club_treasury_list($clubid, $dblj) {
        $stmt = $dblj->prepare("
            SELECT t.id, t.item_type, t.item_id, t.item_amount, t.donated_by, t.claimed_by, t.created_at, t.claimed_at,
                   g.uname AS donor_name
            FROM club_treasury t
            LEFT JOIN game1 g ON t.donated_by = g.sid
            WHERE t.clubid = ? AND t.claimed_by IS NULL
            ORDER BY t.created_at DESC
        ");
        $stmt->execute(array(intval($clubid)));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/* === LẤY LỊCH SỬ ĐÃ NHẬN === */
if (!function_exists(__NAMESPACE__ . '\\club_treasury_history')) {
    function club_treasury_history($clubid, $limit, $dblj) {
        $limit = max(1, min(50, intval($limit)));
        $stmt = $dblj->prepare("
            SELECT t.id, t.item_type, t.item_id, t.item_amount, t.donated_by, t.claimed_by, t.created_at, t.claimed_at,
                   g1.uname AS donor_name, g2.uname AS claimer_name
            FROM club_treasury t
            LEFT JOIN game1 g1 ON t.donated_by = g1.sid
            LEFT JOIN game1 g2 ON t.claimed_by = g2.sid
            WHERE t.clubid = ? AND t.claimed_by IS NOT NULL
            ORDER BY t.claimed_at DESC
            LIMIT $limit
        ");
        $stmt->execute(array(intval($clubid)));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

/* === NHẬN VẬT PHẨM TỪ BẢO KHỐ === */
if (!function_exists(__NAMESPACE__ . '\\club_treasury_claim')) {
    function club_treasury_claim($treasuryId, $claimerSid, $dblj) {
        /* Lock and claim atomically */
        $upd = $dblj->prepare("UPDATE club_treasury SET claimed_by = ?, claimed_at = NOW() WHERE id = ? AND claimed_by IS NULL");
        $upd->execute(array(intval($claimerSid), intval($treasuryId)));
        if ($upd->rowCount() === 0) {
            return array('ok' => false, 'msg' => 'Vật phẩm không tồn tại hoặc đã được nhận');
        }

        /* Retrieve item info after claiming */
        $stmt = $dblj->prepare("SELECT * FROM club_treasury WHERE id = ?");
        $stmt->execute(array(intval($treasuryId)));
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);

        /* Thêm vào túi người chơi */
        $itemType = $item['item_type'];
        $itemId = intval($item['item_id']);
        $itemAmount = intval($item['item_amount']);

        if ($itemType === 'yaodan') {
            $check = $dblj->prepare("SELECT ydsum FROM playeryaodan WHERE sid = ? AND ydid = ? LIMIT 1");
            $check->execute(array(intval($claimerSid), $itemId));
            $row = $check->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $upd2 = $dblj->prepare("UPDATE playeryaodan SET ydsum = ydsum + ? WHERE sid = ? AND ydid = ?");
                $upd2->execute(array($itemAmount, intval($claimerSid), $itemId));
            } else {
                $ins = $dblj->prepare("INSERT INTO playeryaodan (sid, ydid, ydsum) VALUES (?, ?, ?)");
                $ins->execute(array(intval($claimerSid), $itemId, $itemAmount));
            }
        } elseif ($itemType === 'daoju') {
            $check = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
            $check->execute(array(intval($claimerSid), $itemId));
            $row = $check->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $upd2 = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + ? WHERE sid = ? AND djid = ?");
                $upd2->execute(array($itemAmount, intval($claimerSid), $itemId));
            } else {
                $ins = $dblj->prepare("INSERT INTO playerdaoju (sid, djid, djsum) VALUES (?, ?, ?)");
                $ins->execute(array(intval($claimerSid), $itemId, $itemAmount));
            }
        } else {
            $check = $dblj->prepare("SELECT ypsum FROM playeryaopin WHERE sid = ? AND ypid = ? LIMIT 1");
            $check->execute(array(intval($claimerSid), $itemId));
            $row = $check->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $upd2 = $dblj->prepare("UPDATE playeryaopin SET ypsum = ypsum + ? WHERE sid = ? AND ypid = ?");
                $upd2->execute(array($itemAmount, intval($claimerSid), $itemId));
            } else {
                $ins = $dblj->prepare("INSERT INTO playeryaopin (sid, ypid, ypsum) VALUES (?, ?, ?)");
                $ins->execute(array(intval($claimerSid), $itemId, $itemAmount));
            }
        }

        /* Lấy tên vật phẩm */
        $itemName = 'Vật phẩm';
        if ($itemType === 'yaodan') {
            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/yaodan_config.json'), true);
            if (isset($cfg[$itemId])) $itemName = $cfg[$itemId]['ydname'];
        } elseif ($itemType === 'daoju') {
            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/daoju_config.json'), true);
            if (isset($cfg[$itemId])) $itemName = $cfg[$itemId]['djname'];
        } else {
            $cfg = @json_decode(@file_get_contents(__DIR__ . '/../config/yaopin_config.json'), true);
            if (isset($cfg[$itemId])) $itemName = $cfg[$itemId]['ypname'];
        }

        return array('ok' => true, 'msg' => 'Nhận thành công ' . $itemAmount . 'x ' . $itemName, 'item_name' => $itemName, 'amount' => $itemAmount);
    }
}

/* === ĐẾM SỐ VẬT PHẨM CHƯA NHẬN === */
if (!function_exists(__NAMESPACE__ . '\\club_treasury_count')) {
    function club_treasury_count($clubid, $dblj) {
        $stmt = $dblj->prepare("SELECT COUNT(*) FROM club_treasury WHERE clubid = ? AND claimed_by IS NULL");
        $stmt->execute(array(intval($clubid)));
        return intval($stmt->fetchColumn());
    }
}

?>
