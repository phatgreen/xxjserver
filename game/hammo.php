<?php
if (!isset($sid) || $sid == '') {
    exit('Missing sid');
}

function hm_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function hm_state($sid, $dblj)
{
    $stmt = $dblj->prepare("SELECT * FROM mine_player WHERE sid = ? LIMIT 1");
    $stmt->execute(array($sid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $stmt = $dblj->prepare("INSERT INTO mine_player(sid, today_digs, last_dig_date, total_digs, total_yxb, total_czb, updated_at) VALUES(?, 0, CURDATE(), 0, 0, 0, NOW())");
        $stmt->execute(array($sid));
        $row = array('sid' => $sid, 'today_digs' => 0, 'last_dig_date' => date('Y-m-d'), 'total_digs' => 0, 'total_yxb' => 0, 'total_czb' => 0);
    }

    if ($row['last_dig_date'] != date('Y-m-d')) {
        $stmt = $dblj->prepare("UPDATE mine_player SET today_digs = 0, last_dig_date = CURDATE(), updated_at = NOW() WHERE sid = ?");
        $stmt->execute(array($sid));
        $row['today_digs'] = 0;
        $row['last_dig_date'] = date('Y-m-d');
    }

    return $row;
}

function hm_roll_reward($player)
{
    $lv = isset($player->ulv) ? max(1, intval($player->ulv)) : 1;
    $lvBonus = min(20, intval(floor(sqrt($lv))));
    $roll = mt_rand(1, 10000);
    $yxb = 0;
    $czb = 0;
    $item = null;
    $note = 'Đá vụn';

    if ($roll <= 50) {
        $czb = 1;
        $yxb = mt_rand(25, 55) + $lvBonus;
        $note = 'Mạch ma thạch nhỏ';
    } elseif ($roll <= 150) {
        $yxb = mt_rand(900, 1600) + ($lvBonus * 2);
        $note = 'Linh thạch tinh';
    } elseif ($roll <= 550) {
        $yxb = mt_rand(350, 750) + $lvBonus;
        $note = 'Linh thạch nguyên khối';
    } elseif ($roll <= 3550) {
        $yxb = mt_rand(80, 220) + $lvBonus;
        $note = 'Linh thạch thô';
    } elseif ($roll <= 5550) {
        $yxb = mt_rand(20, 80) + intval(floor($lvBonus / 2));
        $commonHerbs = array(50,51,52,53,54,55,56,57,58,59);
        $rareHerbs = array(60,61,62,63,64,65,66,67,68,69);
        $herbId = (mt_rand(1, 100) <= 85) ? $commonHerbs[array_rand($commonHerbs)] : $rareHerbs[array_rand($rareHerbs)];
        $item = array('id' => $herbId, 'count' => 1);
        $herbCfg = \player\daoju_config_get($herbId);
        $note = 'Mảnh linh quặng + ' . (isset($herbCfg['djname']) ? $herbCfg['djname'] : 'Linh dược');
    } elseif ($roll <= 7500) {
        $count = mt_rand(2, 3);
        $yxb = mt_rand(10, 30);
        $commonHerbs = array(50,51,52,53,54,55,56,57,58,59);
        $rareHerbs = array(60,61,62,63,64,65,66,67,68,69);
        $herbId = (mt_rand(1, 100) <= 85) ? $commonHerbs[array_rand($commonHerbs)] : $rareHerbs[array_rand($rareHerbs)];
        $item = array('id' => $herbId, 'count' => $count);
        $herbCfg = \player\daoju_config_get($herbId);
        $note = (isset($herbCfg['djname']) ? $herbCfg['djname'] : 'Linh dược') . ' x' . $count;
    } elseif ($roll <= 8700) {
        $count = mt_rand(1, 2);
        $yxb = mt_rand(10, 30);
        $item = array('id' => 1, 'count' => $count);
        $note = 'Cường hóa thạch x' . $count;
    } elseif ($roll <= 9300) {
        $elementIds = array(32, 33, 34, 35, 36);
        $randEl = $elementIds[array_rand($elementIds)];
        $item = array('id' => $randEl, 'count' => 1);
        $note = 'Đá nguyên tố';
    } elseif ($roll <= 9600) {
        $count = mt_rand(2, 3);
        $item = array('id' => 1, 'count' => $count);
        $note = 'Cường hóa thạch x' . $count;
    }

    return array('yxb' => $yxb, 'czb' => $czb, 'item' => $item, 'note' => $note);
}

function hm_give_item($sid, $player, $dblj, $djid, $amount)
{
    $stmt = $dblj->prepare("SELECT djsum FROM playerdaoju WHERE sid = ? AND djid = ? LIMIT 1");
    $stmt->execute(array($sid, $djid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $stmt = $dblj->prepare("UPDATE playerdaoju SET djsum = djsum + ? WHERE sid = ? AND djid = ?");
        $stmt->execute(array($amount, $sid, $djid));
    } else {
        $stmt = $dblj->prepare("INSERT INTO playerdaoju (sid, uid, djid, djsum) SELECT sid, uid, ?, ? FROM game1 WHERE sid = ? LIMIT 1"); // kèm uid (khóa chính uid+djid)
        $stmt->execute(array($djid, $amount, $sid));
    }

    $djConfig = \player\daoju_config_get($djid);
    return isset($djConfig['djname']) ? $djConfig['djname'] : 'Vật phẩm';
}

function hm_recent_logs($sid, $dblj)
{
    $stmt = $dblj->prepare("SELECT * FROM mine_log WHERE sid = ? ORDER BY id DESC LIMIT 8");
    $stmt->execute(array($sid));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$player = \player\getplayer($sid, $dblj);
$state = hm_state($sid, $dblj);
$dailyLimit = 30;
$freeDigs = 5;
$digCost = 5;
$notice = '';
$resultHtml = '';

if (isset($canshu) && ($canshu == 'dig' || $canshu == 'dig5')) {
    $times = ($canshu == 'dig5') ? 5 : 1;
    $left = max(0, $dailyLimit - intval($state['today_digs']));
    $times = min($times, $left);
    $digsDone = intval($state['today_digs']);
    $freeLeft = max(0, $freeDigs - $digsDone);
    $paidTimes = max(0, $times - $freeLeft);
    $affordableTimes = intval(floor(intval($player->uczb) / $digCost));
    $paidTimes = min($paidTimes, $affordableTimes);
    $times = min($times, $freeLeft + $paidTimes);

    if ($times <= 0) {
        if ($left <= 0) {
            $notice = '<div class="hm-notice fail">Hôm nay đã hết lượt đào.</div>';
        } else {
            $notice = '<div class="hm-notice fail">Không đủ Ma thạch để trả phí đào.</div>';
        }
    } else {
        $totalYxb = 0;
        $totalCzb = 0;
        $actualFree = min($times, $freeLeft);
        $actualPaid = $times - $actualFree;
        $totalCost = $actualPaid * $digCost;
        $itemsHtml = '';
        $insertLog = $dblj->prepare("INSERT INTO mine_log(sid, yxb_reward, czb_reward, note, created_at) VALUES(?, ?, ?, ?, NOW())");

        for ($i = 1; $i <= $times; $i++) {
            $reward = hm_roll_reward($player);
            $totalYxb += intval($reward['yxb']);
            $totalCzb += intval($reward['czb']);

            $logNote = $reward['note'];
            if ($reward['item']) {
                $itemName = hm_give_item($sid, $player, $dblj, $reward['item']['id'], $reward['item']['count']);
                $logNote .= ' +' . $reward['item']['count'] . ' ' . $itemName;
            }

            $insertLog->execute(array($sid, intval($reward['yxb']), intval($reward['czb']), $logNote));

            $line = hm_h($reward['note']);
            if ($reward['yxb'] > 0) {
                $line .= ' +'.intval($reward['yxb']).' Linh thạch';
            }
            if ($reward['czb'] > 0) {
                $line .= ' +'.intval($reward['czb']).' Ma thạch';
            }
            if ($reward['item']) {
                $itemName = \player\daoju_config_get($reward['item']['id']);
                $itemLabel = isset($itemName['djname']) ? $itemName['djname'] : 'Vật phẩm';
                $line .= ' +' . $reward['item']['count'] . ' ' . $itemLabel;
            }
            $itemsHtml .= '<div class="hm-result-row"><span>Lần '.$i.'</span><b>'.$line.'</b></div>';
        }

        $stmt = $dblj->prepare("UPDATE game1 SET uyxb = uyxb + ?, uczb = uczb - ? + ? WHERE sid = ? AND uczb >= ?");
        $stmt->execute(array($totalYxb, $totalCost, $totalCzb, $sid, $totalCost));

        $stmt = $dblj->prepare("UPDATE mine_player SET today_digs = today_digs + ?, total_digs = total_digs + ?, total_yxb = total_yxb + ?, total_czb = total_czb + ?, last_dig_date = CURDATE(), updated_at = NOW() WHERE sid = ?");
        $stmt->execute(array($times, $times, $totalYxb, $totalCzb, $sid));

        $costMsg = '';
        if ($actualFree > 0 && $actualPaid > 0) {
            $costMsg = ' ('.$actualFree.' miễn phí, -'.$totalCost.' MT)';
        } elseif ($actualFree > 0) {
            $costMsg = ' (miễn phí)';
        } else {
            $costMsg = ' (-'.$totalCost.' MT)';
        }
        $notice = '<div class="hm-notice win">Đào '.$times.' lượt'.$costMsg.': +'.number_format($totalYxb, 0, ',', '.').' Linh thạch, +'.number_format($totalCzb, 0, ',', '.').' Ma thạch.</div>';
        $resultHtml = '<section class="hm-panel"><div class="hm-kicker">Kết quả vừa đào</div>'.$itemsHtml.'</section>';
        $state = hm_state($sid, $dblj);
        $player = \player\getplayer($sid, $dblj);
    }
}

$digCmd = $encode->encode("cmd=hammo&canshu=dig&sid=$sid");
$dig5Cmd = $encode->encode("cmd=hammo&canshu=dig5&sid=$sid");
$homeCmd = $encode->encode("cmd=gomid&newmid=$player->nowmid&sid=$sid");
$leftDigs = max(0, $dailyLimit - intval($state['today_digs']));
$safeName = hm_h($player->uname);
$walletYxb = number_format(intval($player->uyxb), 0, ',', '.');
$walletCzb = number_format(intval($player->uczb), 0, ',', '.');
$totalYxb = number_format(intval($state['total_yxb']), 0, ',', '.');
$totalCzb = number_format(intval($state['total_czb']), 0, ',', '.');
$canDig = ($leftDigs > 0 && (intval($state['today_digs']) < $freeDigs || intval($player->uczb) >= $digCost));
$freeLeft = max(0, $freeDigs - intval($state['today_digs']));
$digLabel = $freeLeft > 0 ? 'Đào 1 lần · Miễn phí' : 'Đào 1 lần · '.$digCost.' Ma';

$digButton = $canDig
    ? '<a class="hm-btn dig" href="?cmd='.$digCmd.'">'.$digLabel.'</a>'
    : '<span class="hm-btn disabled">Không thể đào</span>';
$dig5Button = $canDig
    ? '<a class="hm-btn deep" href="?cmd='.$dig5Cmd.'">Đào 5 lần</a>'
    : '<span class="hm-btn disabled">Không thể đào</span>';

$logHtml = '';
foreach (hm_recent_logs($sid, $dblj) as $log) {
    $rewardLine = '+'.number_format(intval($log['yxb_reward']), 0, ',', '.').' Linh thạch';
    if (intval($log['czb_reward']) > 0) {
        $rewardLine .= ' +'.intval($log['czb_reward']).' Ma thạch';
    }
    $logHtml .= '<div class="hm-log-row"><span>'.hm_h($log['created_at']).'</span><b>'.hm_h($log['note']).'</b><em>'.$rewardLine.'</em></div>';
}
if ($logHtml == '') {
    $logHtml = '<div class="hm-empty">Chưa có lượt đào nào.</div>';
}

$maChance = '0.5%';
$linhChance = 'khoảng 81.5%';
$nguyenKhoiChance = '4%';
$linhTinhChance = '1%';
$thaoDuocChance = '39.5%';
$cuongHoaChance = '15%';
$daNguyenToChance = '6%';
$digCostText = number_format($digCost, 0, ',', '.');
$mineBannerFile = __DIR__ . '/../images/bg/hammo.png';
$mineBanner = './images/bg/hammo.png';
if (is_file($mineBannerFile)) {
    $mineBanner .= '?v=' . filemtime($mineBannerFile);
}

echo <<<HTML
<div class="hm-page">
    <section class="hm-hero hm-banner-hero">
        <div class="hm-banner"><img src="$mineBanner" alt="Hầm Mỏ Linh Khoáng"></div>
        <div class="hm-pk-ribbon" onclick="document.getElementById('hm-rate-modal').classList.add('show')" style="cursor:pointer">KHAI KHOÁNG</div>
        <div class="hm-hero-content">
        <div class="hm-floor-mark">Miễn phí {$freeDigs} lượt / ngày</div>
        <div class="hm-kicker">Hoạt động hằng ngày</div>
        <h1>Hầm Mỏ Linh Khoáng</h1>
        <p>Đào sâu trong mạch linh khoáng để tìm Linh thạch, nguyên liệu luyện đan và cường hóa. {$freeDigs} lượt đầu miễn phí mỗi ngày.</p>
        <div class="hm-stats">
            <div class="hm-stat"><span>Đạo hữu</span><b>$safeName</b></div>
            <div class="hm-stat"><span>Lượt đào</span><b>$leftDigs / $dailyLimit</b></div>
            <div class="hm-stat"><span>Linh thạch</span><b>$walletYxb</b></div>
            <div class="hm-stat"><span>Ma thạch</span><b>$walletCzb</b></div>
        </div>
        <div class="hm-actions">
            $digButton
            $dig5Button
            <a class="hm-btn home" href="?cmd=$homeCmd">Đại Sảnh</a>
        </div>
        $notice
        </div>
    </section>

    <div class="hm-layout">
        <div>
            $resultHtml
            <section class="hm-panel">
                <div class="hm-kicker">Lịch sử đào gần đây</div>
                $logHtml
            </section>
        </div>
    </div>
</div>

<div id="hm-rate-modal" class="vdd-reward-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="vdd-reward-popup">
        <div class="vdd-reward-popup-head">
            <div class="vdd-reward-popup-icon">⛏️</div>
            <h3>Tỉ lệ Hầm Mỏ</h3>
            <p>Thông tin chi tiết tỉ lệ đào khoáng</p>
        </div>
        <div class="vdd-reward-popup-list">
            <div class="hm-rule" style="padding:8px 0">
                <div>Miễn phí: <b>{$freeDigs} lượt/ngày</b>, không tốn Ma thạch.</div>
                <div>Phí đào: <b>{$digCost} Ma thạch/lượt</b> (sau {$freeDigs} lượt miễn phí).</div>
                <div>Linh thạch: <b>$linhChance</b> mỗi lượt, số lượng nhỏ và chỉ tăng rất nhẹ theo cấp.</div>
                <div>Linh thạch nguyên khối: <b>$nguyenKhoiChance</b>, thưởng nhiều hơn Linh thạch thô.</div>
                <div>Linh thạch tinh: <b>$linhTinhChance</b>, rất hiếm và thưởng cao hơn.</div>
                <div>Ma thạch: <b>$maChance</b> mỗi lượt, rất hiếm khi xuất hiện.</div>
                <div>Thảo dược linh đan: <b>$thaoDuocChance</b>, 20 loại tên riêng (Linh Chi, Huyết Sâm, Tuyết Liên...).</div>
                <div>Cường hóa thạch: <b>$cuongHoaChance</b>, nguyên liệu cường hóa trang bị.</div>
                <div>Đá nguyên tố: <b>$daNguyenToChance</b>, dùng trong luyện đan và chế tạo.</div>
                <div>Giới hạn: <b>$dailyLimit lượt/ngày</b>, tự làm mới vào ngày mới.</div>
                <div>Tổng đã đào: <b>{$state['total_digs']}</b> lượt.</div>
                <div>Tổng nhận: <b>$totalYxb</b> Linh thạch, <b>$totalCzb</b> Ma thạch.</div>
            </div>
        </div>
        <div class="vdd-reward-popup-foot">
            <a class="vdd-reward-popup-close" href="javascript:void(0)" onclick="document.getElementById('hm-rate-modal').classList.remove('show')">Đóng</a>
        </div>
    </div>
</div>
HTML;
?>
