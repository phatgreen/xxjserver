<?php
// Dùng đạo cụ có use_effect
$useNotice = '';
$useType = 'ok';

if (isset($djid) && intval($djid) > 0) {
    $config = \player\daoju_config_get(intval($djid));
    if (!$config || !isset($config['use_effect'])) {
        $useNotice = 'Vật phẩm này không thể sử dụng.';
        $useType = 'bad';
    } else {
        $effect = $config['use_effect'];
        $effectType = isset($effect['type']) ? $effect['type'] : '';

        // Kiểm tra còn đủ số lượng
        $playerDj = \player\getplayerdaoju($sid, intval($djid), $dblj);
        if (!$playerDj || intval($playerDj->djsum) < 1) {
            $useNotice = 'Bạn không còn vật phẩm này.';
            $useType = 'bad';
        } else {
            // Giới hạn 2 lần dùng trong đời cho các đan reroll
            if (in_array($effectType, array('reroll_thechat', 'reroll_huyetmach'))) {
                $djused = isset($playerDj->djused) ? intval($playerDj->djused) : 0;
                if ($djused >= 2) {
                    $useNotice = 'Đạo cụ này đã dùng tối đa 2 lần, không thể dùng tiếp.';
                    $useType = 'bad';
                    header("Location: game.php?cmd=" . $encode->encode("cmd=getbagdj&sid=$sid") . "&use_notice=" . urlencode($useNotice) . "&use_type=" . $useType);
                    exit;
                }
            }

            if ($effectType === 'reroll_thechat') {
                // Tẩy Tủy - Random lại Thể Chất (phẩm chất >= hiện tại)
                $stmt = $dblj->prepare("SELECT thechat_quality FROM game1 WHERE sid = ?");
                $stmt->execute(array($sid));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $minQ = $row ? max(1, intval($row['thechat_quality'])) : 1;

                $roll = \player\thechat_roll_min($minQ);
                if ($roll) {
                    \player\deledjsum(intval($djid), 1, $sid, $dblj);
                    // Tăng djused
                    $stmt = $dblj->prepare("UPDATE playerdaoju SET djused = djused + 1 WHERE sid = ? AND djid = ?");
                    $stmt->execute(array($sid, intval($djid)));

                    \player\thechat_assign($sid, $dblj, $roll['type']['id'], $roll['quality']);
                    $tcName = isset($roll['type']['name']) ? $roll['type']['name'] : 'Không rõ';
                    $tcIcon = isset($roll['type']['icon']) ? $roll['type']['icon'] : '?';
                    $tcColor = isset($roll['type']['color']) ? $roll['type']['color'] : '#fff';
                    $tcQName = \player\thechat_quality_name($roll['quality']);
                    $djName = isset($config['djname']) ? $config['djname'] : 'Vật phẩm';
                    $remain = 2 - ($djused + 1);
                    $useNotice = "Sử dụng {$djName} thành công! Nhận được: <font color={$tcColor}>{$tcIcon} {$tcName} - {$tcQName}</font> (Còn {$remain} lần)";
                } else {
                    $useNotice = 'Tẩy tủy thất bại, thử lại sau.';
                    $useType = 'bad';
                }

            } elseif ($effectType === 'reroll_huyetmach') {
                // Tố Thể Đan - Random lại Huyết Mạch (phẩm chất >= hiện tại)
                $stmt = $dblj->prepare("SELECT huyetmach_quality FROM game1 WHERE sid = ?");
                $stmt->execute(array($sid));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $minQ = $row ? max(1, intval($row['huyetmach_quality'])) : 1;

                $roll = \player\huyetmach_roll_min($minQ);
                if ($roll) {
                    \player\deledjsum(intval($djid), 1, $sid, $dblj);
                    $stmt = $dblj->prepare("UPDATE playerdaoju SET djused = djused + 1 WHERE sid = ? AND djid = ?");
                    $stmt->execute(array($sid, intval($djid)));

                    \player\huyetmach_assign($sid, $dblj, $roll['type']['id'], $roll['quality']);
                    $hmName = isset($roll['type']['name']) ? $roll['type']['name'] : 'Không rõ';
                    $hmIcon = isset($roll['type']['icon']) ? $roll['type']['icon'] : '?';
                    $hmColor = isset($roll['type']['color']) ? $roll['type']['color'] : '#fff';
                    $hmQName = \player\huyetmach_quality_name($roll['quality']);
                    $djName = isset($config['djname']) ? $config['djname'] : 'Vật phẩm';
                    $remain = 2 - ($djused + 1);
                    $useNotice = "Sử dụng {$djName} thành công! Nhận được: <font color={$hmColor}>{$hmIcon} {$hmName} - {$hmQName}</font> (Còn {$remain} lần)";
                } else {
                    $useNotice = 'Tố thể thất bại, thử lại sau.';
                    $useType = 'bad';
                }

            } elseif (strpos($effectType, 'tanthu_') === 0) {
                // Quà Tân Thủ (Tu Vi Đan / Túi Linh Thạch / Thẻ ×2) — class/function/tanthu.php; tatca=1: mở hết túi
                list($ttOk, $useNotice) = \player\tanthu_use_item($sid, intval($djid), $effectType, isset($tatca) && intval($tatca) === 1, $dblj);
                $useType = $ttOk ? 'ok' : 'bad';
            } elseif ($effectType === 'linhcan_wash') {
                // Tẩy Linh Đan: khong reroll o day; dan mo trang chon hanh (Task 7)
                $washCmd = $encode->encode("cmd=linhcan_wash&sid=$sid");
                header("Location: game.php?cmd=$washCmd");
                exit;
            } elseif ($effectType === 'tianfu_open') {
                // Khai Phu Dan: KHONG reroll o day — dan mo trang KHAI PHU (Task 10)
                // de nguoi choi xem bang ty le va nhan ket qua co hieu ung.
                $openCmd = $encode->encode("cmd=tianfu_open&sid=$sid");
                header("Location: game.php?cmd=$openCmd");
                exit;
            } else {
                // Stat boost items (cũ)
                $stat = isset($effect['stat']) ? $effect['stat'] : '';
                $amount = isset($effect['amount']) ? intval($effect['amount']) : 0;

                $validStats = array('ugj', 'ufy', 'umaxhp', 'ubj', 'uxx');
                if (!in_array($stat, $validStats) || $amount <= 0) {
                    $useNotice = 'Hiệu quả vật phẩm không hợp lệ.';
                    $useType = 'bad';
                } else {
                    // Trừ 1 vật phẩm
                    \player\deledjsum(intval($djid), 1, $sid, $dblj);

                    // Tăng stat
                    $stmt = $dblj->prepare("UPDATE game1 SET {$stat} = {$stat} + ? WHERE sid = ?");
                    $stmt->execute(array($amount, $sid));

                    \player\player_cache_clear($sid);

                    $statNames = array(
                        'ugj' => 'Công Kích',
                        'ufy' => 'Phòng Thủ',
                        'umaxhp' => 'Sinh Mệnh',
                        'ubj' => 'Bạo Kích',
                        'uxx' => 'Hút Máu'
                    );
                    $statName = isset($statNames[$stat]) ? $statNames[$stat] : $stat;
                    $djName = isset($config['djname']) ? $config['djname'] : 'Vật phẩm';
                    $useNotice = "Sử dụng {$djName} thành công! {$statName} +{$amount}";
                }
            }
        }
    }

    // Quay lại trang túi đồ
    header("Location: game.php?cmd=" . $encode->encode("cmd=getbagdj&sid=$sid") . "&use_notice=" . urlencode($useNotice) . "&use_type=" . $useType);
    exit;
}
?>
